<?php

namespace App\Console\Commands;

use App\Models\Merchant;
use App\Models\Terminal;
use Illuminate\Console\Command;
use Stripe\StripeClient;

class SyncTerminalStatus extends Command
{
    protected $signature = 'terminals:sync-status';
    protected $description = 'Sync terminal online/offline status from Stripe';

    public function handle(): int
    {
        $merchants = Merchant::whereNotNull('processor_account_id')->get();
        $updated = 0;
        $total = 0;

        foreach ($merchants as $merchant) {
            $terminals = Terminal::where('merchant_id', $merchant->id)->get();
            if ($terminals->isEmpty()) continue;

            try {
                $stripe = new StripeClient(config('services.stripe.secret'));

                // Try connected account first
                $readers = collect();
                try {
                    $result = $stripe->terminal->readers->all(
                        ['limit' => 100],
                        ['stripe_account' => $merchant->processor_account_id]
                    );
                    $readers = collect($result->data);
                } catch (\Exception $e) {
                    // Connected account has no readers, try platform
                }

                // If no readers on connected account, check platform
                if ($readers->isEmpty()) {
                    try {
                        $result = $stripe->terminal->readers->all(['limit' => 100]);
                        $readers = collect($result->data);
                    } catch (\Exception $e) {
                        $this->warn("Failed to fetch platform readers: {$e->getMessage()}");
                        continue;
                    }
                }

                foreach ($terminals as $terminal) {
                    $total++;

                    // Match by processor_terminal_id or serial_number
                    $reader = $readers->first(function ($r) use ($terminal) {
                        return $r->id === $terminal->processor_terminal_id
                            || $r->serial_number === $terminal->serial_number;
                    });

                    if ($reader) {
                        $changed = $terminal->status !== $reader->status;
                        $terminal->update([
                            'status' => $reader->status,
                            'last_seen_at' => $reader->status === 'online' ? now() : $terminal->last_seen_at,
                        ]);
                        if ($changed) {
                            $updated++;
                            $this->info("{$terminal->name}: {$terminal->getOriginal('status')} → {$reader->status}");
                        }
                    } else {
                        // Reader not found in Stripe - mark as offline
                        if ($terminal->status !== 'offline') {
                            $terminal->update(['status' => 'offline']);
                            $updated++;
                            $this->info("{$terminal->name}: → offline (not found in Stripe)");
                        }
                    }
                }

            } catch (\Exception $e) {
                $this->error("Merchant {$merchant->business_name}: {$e->getMessage()}");
            }
        }

        $this->info("Synced {$total} terminals, {$updated} updated.");
        return self::SUCCESS;
    }
}
