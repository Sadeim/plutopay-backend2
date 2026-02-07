<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\Payout;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PayoutSeeder extends Seeder
{
    public function run(): void
    {
        $merchant = Merchant::first();
        if (!$merchant) {
            $this->command->error('No merchant found.');
            return;
        }

        $statuses = ['paid', 'paid', 'paid', 'in_transit', 'pending', 'failed'];
        $destinations = ['1234', '5678', '9012'];

        for ($i = 0; $i < 15; $i++) {
            $status = $statuses[array_rand($statuses)];
            $amount = rand(5000, 250000);
            $fee = (int)($amount * 0.0025);
            $createdAt = now()->subDays(rand(1, 90));
            $arrival = $createdAt->copy()->addDays(rand(2, 5));

            Payout::create([
                'merchant_id' => $merchant->id,
                'reference' => 'po_' . Str::random(16),
                'amount' => $amount,
                'fee' => $fee,
                'net_amount' => $amount - $fee,
                'currency' => $merchant->default_currency ?? 'USD',
                'status' => $status,
                'destination_type' => 'bank_account',
                'destination_last_four' => $destinations[array_rand($destinations)],
                'processor_payout_id' => 'po_' . Str::random(24),
                'is_test' => true,
                'estimated_arrival_at' => $arrival,
                'arrived_at' => $status === 'paid' ? $arrival : null,
                'failed_at' => $status === 'failed' ? $createdAt->addDays(1) : null,
                'failure_reason' => $status === 'failed' ? 'Insufficient funds in Stripe account' : null,
                'created_at' => $createdAt,
            ]);
        }

        $this->command->info('15 test payouts created!');
    }
}
