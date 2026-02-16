<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use App\Models\Transaction;
use Illuminate\Console\Command;

class SyncStripeTransactions extends Command
{
    protected $signature = 'stripe:sync-transactions {--merchant= : Specific merchant ID} {--dry-run : Show changes without applying}';
    protected $description = 'Sync transaction statuses from Stripe';

    public function handle()
    {
        $merchantId = $this->option('merchant');
        $dryRun = $this->option('dry-run');

        $merchants = $merchantId
            ? Merchant::where('id', $merchantId)->get()
            : Merchant::all();

        foreach ($merchants as $merchant) {
            $this->info("Syncing merchant: {$merchant->business_name}");
            $this->syncMerchant($merchant, $dryRun);
        }

        $this->info('Done!');
    }

    protected function syncMerchant(Merchant $merchant, bool $dryRun)
    {
        $stripe = new \Stripe\StripeClient(
            $merchant->test_mode
                ? config('services.stripe.test_secret')
                : config('services.stripe.secret')
        );

        $transactions = Transaction::where('merchant_id', $merchant->id)
            ->whereNotNull('processor_transaction_id')
            ->get();

        $this->info("  Found {$transactions->count()} transactions to check");

        $updated = 0;
        $errors = 0;

        foreach ($transactions as $txn) {
            try {
                // Try connected account first, then platform
                $pi = $this->retrievePaymentIntent($stripe, $txn->processor_transaction_id, $merchant);

                if (!$pi) {
                    $this->error("  X {$txn->reference}: Not found on Stripe");
                    $errors++;
                    continue;
                }

                $changes = [];

                $newStatus = $this->mapStatus($pi);
                if ($txn->status !== $newStatus) {
                    $changes['status'] = $newStatus;
                }

                if ($newStatus === 'succeeded' && !$txn->captured_at) {
                    $changes['captured_at'] = now();
                }
                if ($newStatus === 'failed' && !$txn->failed_at) {
                    $changes['failed_at'] = now();
                    if ($pi->last_payment_error) {
                        $changes['failure_reason'] = $pi->last_payment_error->message ?? 'Unknown';
                        $changes['failure_code'] = $pi->last_payment_error->code ?? null;
                    }
                }

                // Get charge details
                if (!empty($pi->latest_charge)) {
                    try {
                        // Use same account where PI was found
                        $charge = $this->retrieveCharge($stripe, $pi->latest_charge, $merchant);

                        if ($charge) {
                            if ($charge->receipt_url && !$txn->receipt_url) {
                                $changes['receipt_url'] = $charge->receipt_url;
                            }
                            if ($charge->receipt_email && !$txn->receipt_email) {
                                $changes['receipt_email'] = $charge->receipt_email;
                            }
                            if ($charge->amount_refunded > 0) {
                                $changes['amount_refunded'] = $charge->amount_refunded;
                                if ($charge->amount_refunded >= $txn->amount) {
                                    $changes['status'] = 'refunded';
                                    if (!$txn->refunded_at) $changes['refunded_at'] = now();
                                }
                            }

                            $pm = $charge->payment_method_details;
                            if ($pm) {
                                if ($pm->type === 'card' && $pm->card) {
                                    $changes['payment_method_type'] = 'card';
                                    $changes['card_brand'] = $pm->card->brand;
                                    $changes['card_last_four'] = $pm->card->last4;
                                } elseif ($pm->type === 'card_present' && $pm->card_present) {
                                    $changes['payment_method_type'] = 'terminal';
                                    $changes['card_brand'] = $pm->card_present->brand;
                                    $changes['card_last_four'] = $pm->card_present->last4;
                                }
                            }
                        }
                    } catch (\Exception $e) {}
                }

                if ($pi->amount !== $txn->amount) {
                    $changes['amount'] = $pi->amount;
                }

                // Sync tip amount
                $tipAmount = $pi->amount_details->tip->amount ?? 0;
                if ($tipAmount > 0 && $txn->tip_amount != $tipAmount) {
                    $changes['tip_amount'] = $tipAmount;
                }

                if (!empty($changes)) {
                    $oldStatus = $txn->status;
                    $newStatusDisplay = $changes['status'] ?? $oldStatus;

                    if ($dryRun) {
                        $this->warn("  [DRY] {$txn->reference}: {$oldStatus} -> {$newStatusDisplay} | " . json_encode($changes));
                    } else {
                        $txn->update($changes);
                        $this->info("  OK {$txn->reference}: {$oldStatus} -> {$newStatusDisplay}");
                    }
                    $updated++;
                } else {
                    $this->line("  -- {$txn->reference}: in sync ({$txn->status})");
                }

            } catch (\Exception $e) {
                $this->error("  X {$txn->reference}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->info("  Summary: {$updated} updated, {$errors} errors");
    }

    protected function retrievePaymentIntent($stripe, string $piId, Merchant $merchant)
    {
        // Try connected account first
        if ($merchant->processor_account_id) {
            try {
                return $stripe->paymentIntents->retrieve($piId, null, [
                    'stripe_account' => $merchant->processor_account_id,
                ]);
            } catch (\Exception $e) {}
        }

        // Fallback to platform account
        try {
            return $stripe->paymentIntents->retrieve($piId);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function retrieveCharge($stripe, string $chargeId, Merchant $merchant)
    {
        if ($merchant->processor_account_id) {
            try {
                return $stripe->charges->retrieve($chargeId, null, [
                    'stripe_account' => $merchant->processor_account_id,
                ]);
            } catch (\Exception $e) {}
        }

        try {
            return $stripe->charges->retrieve($chargeId);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function mapStatus($paymentIntent): string
    {
        return match($paymentIntent->status) {
            'succeeded' => 'succeeded',
            'canceled' => 'canceled',
            'requires_payment_method' => 'failed',
            'requires_confirmation', 'requires_action', 'processing' => 'pending',
            'requires_capture' => 'requires_capture',
            default => 'pending',
        };
    }
}
