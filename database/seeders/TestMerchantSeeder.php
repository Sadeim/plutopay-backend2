<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\Merchant;
use App\Models\MerchantUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestMerchantSeeder extends Seeder
{
    public function run(): void
    {
        $merchant = Merchant::create([
            'business_name' => 'Test Store',
            'display_name' => 'My Test Store',
            'business_type' => 'company',
            'email' => 'test@plutopay.com',
            'phone' => '+1234567890',
            'country' => 'US',
            'default_currency' => 'USD',
            'status' => 'active',
            'kyc_status' => 'approved',
            'kyc_approved_at' => now(),
            'processor_type' => 'stripe',
            'test_mode' => true,
            'webhook_secret' => 'whsec_' . Str::random(32),
        ]);

        MerchantUser::create([
            'merchant_id' => $merchant->id,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@plutopay.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Test Secret Key
        $testSecretKey = 'sk_test_' . Str::random(32);
        ApiKey::create([
            'merchant_id' => $merchant->id,
            'name' => 'Test Secret Key',
            'type' => 'secret',
            'key' => $testSecretKey,
            'key_hash' => hash('sha256', $testSecretKey),
            'key_last_four' => substr($testSecretKey, -4),
            'is_test' => true,
        ]);

        // Test Publishable Key
        $testPubKey = 'pk_test_' . Str::random(32);
        ApiKey::create([
            'merchant_id' => $merchant->id,
            'name' => 'Test Publishable Key',
            'type' => 'publishable',
            'key' => $testPubKey,
            'key_hash' => hash('sha256', $testPubKey),
            'key_last_four' => substr($testPubKey, -4),
            'is_test' => true,
        ]);

        echo "✅ Test Merchant Created!\n";
        echo "📧 Email: test@plutopay.com\n";
        echo "🔑 Secret Key: {$testSecretKey}\n";
        echo "🔑 Publishable Key: {$testPubKey}\n";
    }
}
