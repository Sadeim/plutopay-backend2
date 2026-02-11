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
            : Merchant::whereNotNull('processor_account_id')->get();

        foreach ($merchants as $merchant) {
            $this->info("Syncing merchant: {$merchant->business_name} ({$merchant->processor_account_id})");
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

        $connectOpts = [];
        if ($merchant->processor_account_id) {
            $connectOpts['stripe_account'] = $merchant->processor_account_id;
        }

        $transactions = Transaction::where('merchant_id', $merchant->id)
            ->whereNotNull('processor_transaction_id')
            ->get();

        $this->info("  Found {$transactions->count()} transactions to check");

        $updated = 0;
        $errors = 0;

        foreach ($transactions as $txn) {
            try {
                $pi = $stripe->paymentIntents->retrieve(
                    $txn->processor_transaction_id,
                    null,
                    $connectOpts
                );

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

                if (!empty($pi->latest_charge)) {
                    try {
                        $charge = $stripe->charges->retrieve($pi->latest_charge, null, $connectOpts);

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
                    } catch (\Exception $e) {}
                }

                if ($pi->amount !== $txn->amount) {
                    $changes['amount'] = $pi->amount;
                }

                if (!empty($changes)) {
                    $oldStatus = $txn->status;
                    $newStatusDisplay = $changes['status'] ?? $oldStatus;

                    if ($dryRun) {
                        $this->warn("  [DRY RUN] {$txn->reference}: {$oldStatus} -> {$newStatusDisplay} | " . json_encode($changes));
                    } else {
                        $txn->update($changes);
                        $this->info("  ✅ {$txn->reference}: {$oldStatus} -> {$newStatusDisplay}");
                    }
                    $updated++;
                } else {
                    $this->line("  -- {$txn->reference}: already in sync ({$txn->status})");
                }

            } catch (\Stripe\Exception\InvalidRequestException $e) {
                $this->error("  X {$txn->reference}: {$e->getMessage()}");
                if (str_contains($e->getMessage(), 'No such payment_intent') && !$dryRun) {
                    $txn->update(['status' => 'canceled']);
                    $this->warn("    -> Marked as canceled");
                }
                $errors++;
            } catch (\Exception $e) {
                $this->error("  X {$txn->reference}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->info("  Summary: {$updated} updated, {$errors} errors");
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
