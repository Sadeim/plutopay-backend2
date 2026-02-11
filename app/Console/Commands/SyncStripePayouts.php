<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use App\Models\Payout;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncStripePayouts extends Command
{
    protected $signature = 'stripe:sync-payouts {--merchant= : Specific merchant ID} {--dry-run : Show without applying}';
    protected $description = 'Import and sync payouts from Stripe';

    public function handle()
    {
        $merchantId = $this->option('merchant');
        $dryRun = $this->option('dry-run');

        $merchants = $merchantId
            ? Merchant::where('id', $merchantId)->get()
            : Merchant::all();

        foreach ($merchants as $merchant) {
            $this->info("Syncing payouts for: {$merchant->business_name}");
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

        $imported = 0;
        $updated = 0;
        $errors = 0;

        // Try connected account first, then platform
        $accounts = [];
        if ($merchant->processor_account_id) {
            $accounts[] = ['stripe_account' => $merchant->processor_account_id];
        }
        $accounts[] = []; // platform account fallback

        foreach ($accounts as $opts) {
            try {
                $accountLabel = !empty($opts) ? "connected ({$merchant->processor_account_id})" : "platform";
                $this->line("  Checking {$accountLabel} account...");

                $hasMore = true;
                $startingAfter = null;

                while ($hasMore) {
                    $params = ['limit' => 100];
                    if ($startingAfter) {
                        $params['starting_after'] = $startingAfter;
                    }

                    $payouts = $stripe->payouts->all($params, $opts ?: null);

                    foreach ($payouts->data as $po) {
                        $existing = Payout::where('processor_payout_id', $po->id)
                            ->where('merchant_id', $merchant->id)
                            ->first();

                        if ($existing) {
                            // Update status if changed
                            $newStatus = $this->mapStatus($po);
                            $changes = [];

                            if ($existing->status !== $newStatus) {
                                $changes['status'] = $newStatus;
                            }
                            if ($newStatus === 'paid' && !$existing->arrived_at) {
                                $changes['arrived_at'] = $po->arrival_date
                                    ? \Carbon\Carbon::createFromTimestamp($po->arrival_date)
                                    : now();
                            }
                            if ($newStatus === 'failed' && !$existing->failed_at) {
                                $changes['failed_at'] = now();
                                $changes['failure_reason'] = $po->failure_message ?? 'Unknown';
                            }

                            if (!empty($changes)) {
                                if (!$dryRun) {
                                    $existing->update($changes);
                                }
                                $statusDisplay = $changes['status'] ?? $existing->status; $this->info("  UPD {$existing->reference}: -> {$statusDisplay}");
                                $updated++;
                            }
                        } else {
                            // Import new payout
                            $data = [
                                'merchant_id' => $merchant->id,
                                'reference' => 'po_' . Str::random(20),
                                'amount' => $po->amount,
                                'fee' => 0,
                                'net_amount' => $po->amount,
                                'currency' => strtoupper($po->currency),
                                'status' => $this->mapStatus($po),
                                'destination_type' => $po->type ?? 'bank_account',
                                'destination_last_four' => $this->getDestinationLast4($po),
                                'processor_payout_id' => $po->id,
                                'is_test' => $merchant->test_mode,
                                'estimated_arrival_at' => $po->arrival_date
                                    ? \Carbon\Carbon::createFromTimestamp($po->arrival_date)
                                    : null,
                                'arrived_at' => $po->status === 'paid' && $po->arrival_date
                                    ? \Carbon\Carbon::createFromTimestamp($po->arrival_date)
                                    : null,
                                'failed_at' => $po->status === 'failed' ? now() : null,
                                'failure_reason' => $po->failure_message,
                                'created_at' => \Carbon\Carbon::createFromTimestamp($po->created),
                                'updated_at' => now(),
                            ];

                            if ($dryRun) {
                                $status = $data['status'];
                                $amt = number_format($data['amount'] / 100, 2);
                                $this->warn("  [DRY] NEW: \${$amt} {$data['currency']} | {$status} | arrival: {$data['estimated_arrival_at']}");
                            } else {
                                Payout::create($data);
                            }
                            $imported++;
                        }

                        $startingAfter = $po->id;
                    }

                    $hasMore = $payouts->has_more;
                }

                // If we found payouts on this account, no need to check fallback
                if ($imported > 0 || $updated > 0) {
                    break;
                }

            } catch (\Exception $e) {
                $this->error("  Error on {$accountLabel}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->info("  Summary: {$imported} imported, {$updated} updated, {$errors} errors");
    }

    protected function mapStatus($payout): string
    {
        return match($payout->status) {
            'paid' => 'paid',
            'pending' => 'pending',
            'in_transit' => 'in_transit',
            'canceled' => 'canceled',
            'failed' => 'failed',
            default => 'pending',
        };
    }

    protected function getDestinationLast4($payout): ?string
    {
        if (isset($payout->destination) && is_string($payout->destination)) {
            return substr($payout->destination, -4);
        }
        return null;
    }
}
