<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $merchant = Merchant::first();

        if (!$merchant) {
            $this->command->error('No merchant found. Please create a merchant first.');
            return;
        }

        $statuses = ['succeeded', 'succeeded', 'succeeded', 'pending', 'failed', 'refunded'];
        $methods = ['card', 'card', 'card', 'wallet', 'bank_transfer', 'terminal'];
        $currencies = ['USD', 'USD', 'EUR', 'GBP', 'ILS'];
        $cardBrands = ['visa', 'mastercard', 'amex', null];
        $sources = ['api', 'dashboard', 'terminal', 'payment_link'];

        $firstNames = ['Omar', 'Sara', 'Ahmad', 'Lina', 'Mohammed', 'Noor', 'Khaled', 'Dana', 'Youssef', 'Hana'];
        $lastNames = ['Hassan', 'Ali', 'Mansour', 'Khalil', 'Nasser', 'Saleh', 'Ibrahim', 'Rashid', 'Bakr', 'Farid'];
        $domains = ['gmail.com', 'outlook.com', 'company.com', 'business.net', 'mail.com'];

        $descriptions = [
            'Online purchase',
            'In-store payment',
            'Subscription payment',
            'Invoice payment',
            'Service fee',
            'Product order',
            'Monthly billing',
            'Consultation fee',
            'Membership renewal',
            'Event ticket',
        ];

        $this->command->info("Creating 75 test transactions for merchant: {$merchant->business_name}");

        for ($i = 0; $i < 75; $i++) {
            $status = $statuses[array_rand($statuses)];
            $method = $methods[array_rand($methods)];
            $currency = $currencies[array_rand($currencies)];
            $amount = rand(500, 500000); // $5 - $5,000 in cents
            $isCard = $method === 'card';
            $cardBrand = $isCard ? collect(['visa', 'mastercard', 'amex'])->random() : null;
            $createdAt = now()->subDays(rand(0, 90))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            $email = strtolower($firstNames[array_rand($firstNames)] . '.' . $lastNames[array_rand($lastNames)] . rand(1, 99)) . '@' . $domains[array_rand($domains)];

            Transaction::create([
                'id' => Str::uuid(),
                'merchant_id' => $merchant->id,
                'reference' => 'txn_' . Str::random(16),
                'type' => 'payment',
                'amount' => $amount,
                'amount_refunded' => $status === 'refunded' ? $amount : 0,
                'currency' => $currency,
                'status' => $status,
                'payment_method_type' => $method,
                'card_brand' => $cardBrand,
                'card_last_four' => $isCard ? str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) : null,
                'card_exp_month' => $isCard ? rand(1, 12) : null,
                'card_exp_year' => $isCard ? rand(2025, 2030) : null,
                'source' => $sources[array_rand($sources)],
                'receipt_email' => $email,
                'description' => $descriptions[array_rand($descriptions)],
                'metadata' => json_encode(['source' => 'seeder', 'test' => true]),
                'processor_type' => 'stripe',
                'processor_transaction_id' => 'pi_' . Str::random(24),
                'is_test' => true,
                'captured_at' => $status === 'succeeded' ? $createdAt->copy()->addSeconds(rand(1, 30)) : null,
                'failed_at' => $status === 'failed' ? $createdAt->copy()->addSeconds(rand(1, 10)) : null,
                'refunded_at' => $status === 'refunded' ? $createdAt->copy()->addDays(rand(1, 7)) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->command->info('75 test transactions created successfully!');
    }
}
