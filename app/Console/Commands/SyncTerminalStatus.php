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
        $stripe = new StripeClient(config('services.stripe.secret'));
        $updated = 0;
        $total = 0;

        // Get ALL platform readers once
        $platformReaders = collect();
        try {
            $result = $stripe->terminal->readers->all(['limit' => 100]);
            $platformReaders = collect($result->data);
            $this->line("Platform readers: {$platformReaders->count()}");
        } catch (\Exception $e) {
            $this->warn("Failed to fetch platform readers: {$e->getMessage()}");
        }

        $merchants = Merchant::whereNotNull('processor_account_id')->get();

        foreach ($merchants as $merchant) {
            $terminals = Terminal::where('merchant_id', $merchant->id)->get();
            if ($terminals->isEmpty()) continue;

            // Get connected account readers
            $connectedReaders = collect();
            try {
                $result = $stripe->terminal->readers->all(
                    ['limit' => 100],
                    ['stripe_account' => $merchant->processor_account_id]
                );
                $connectedReaders = collect($result->data);
            } catch (\Exception $e) {
                // No readers on connected account
            }

            foreach ($terminals as $terminal) {
                $total++;

                // First check connected account, then platform
                $reader = $connectedReaders->first(function ($r) use ($terminal) {
                    return $r->id === $terminal->processor_terminal_id
                        || $r->serial_number === $terminal->serial_number;
                });

                if (!$reader) {
                    $reader = $platformReaders->first(function ($r) use ($terminal) {
                        return $r->id === $terminal->processor_terminal_id
                            || $r->serial_number === $terminal->serial_number;
                    });
                }

                if ($reader) {
                    $oldStatus = $terminal->status;
                    $newStatus = $reader->status;

                    $terminal->update([
                        'status' => $newStatus,
                        'last_seen_at' => $newStatus === 'online' ? now() : $terminal->last_seen_at,
                    ]);

                    if ($oldStatus !== $newStatus) {
                        $updated++;
                        $this->info("{$terminal->name}: {$oldStatus} -> {$newStatus}");
                    }
                } else {
                    if ($terminal->status !== 'offline') {
                        $terminal->update(['status' => 'offline']);
                        $updated++;
                        $this->info("{$terminal->name}: -> offline (not found)");
                    }
                }
            }
        }

        $this->info("Synced {$total} terminals, {$updated} changed.");
        return self::SUCCESS;
    }
}
