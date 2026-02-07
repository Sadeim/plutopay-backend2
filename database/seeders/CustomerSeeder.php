<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Merchant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $merchant = Merchant::first();

        if (!$merchant) {
            $this->command->error('No merchant found.');
            return;
        }

        $firstNames = ['Omar', 'Sara', 'Ahmad', 'Lina', 'Mohammed', 'Noor', 'Khaled', 'Dana', 'Youssef', 'Hana',
            'Ali', 'Fatima', 'Hassan', 'Reem', 'Ibrahim', 'Layla', 'Tariq', 'Mona', 'Sami', 'Dina'];
        $lastNames = ['Hassan', 'Ali', 'Mansour', 'Khalil', 'Nasser', 'Saleh', 'Ibrahim', 'Rashid', 'Bakr', 'Farid',
            'Hamdan', 'Qasim', 'Darwish', 'Younis', 'Abed', 'Haddad', 'Khoury', 'Masri', 'Jabari', 'Zahran'];
        $domains = ['gmail.com', 'outlook.com', 'company.com', 'business.net', 'mail.com', 'yahoo.com'];
        $countries = ['US', 'GB', 'AE', 'SA', 'JO', 'PS', 'DE', 'FR', null];
        $cities = ['New York', 'London', 'Dubai', 'Riyadh', 'Amman', 'Ramallah', 'Berlin', 'Paris', null];
        $phonePrefixes = ['+1', '+44', '+971', '+966', '+962', '+970', '+49', '+33'];

        $this->command->info("Creating 50 test customers for merchant: {$merchant->business_name}");

        for ($i = 0; $i < 50; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $hasEmail = rand(1, 10) > 1; // 90% have email
            $hasPhone = rand(1, 10) > 3; // 70% have phone
            $hasAddress = rand(1, 10) > 5; // 50% have address
            $countryIdx = array_rand($countries);

            Customer::create([
                'id' => Str::uuid(),
                'merchant_id' => $merchant->id,
                'external_id' => rand(1, 5) === 1 ? 'ext_' . Str::random(10) : null,
                'name' => $firstName . ' ' . $lastName,
                'email' => $hasEmail ? strtolower($firstName . '.' . $lastName . rand(1, 99)) . '@' . $domains[array_rand($domains)] : null,
                'phone' => $hasPhone ? $phonePrefixes[array_rand($phonePrefixes)] . rand(100000000, 999999999) : null,
                'address_line1' => $hasAddress ? rand(1, 999) . ' ' . ['Main St', 'Oak Ave', 'King Rd', 'First St', 'Market St'][array_rand(['Main St', 'Oak Ave', 'King Rd', 'First St', 'Market St'])] : null,
                'city' => $hasAddress ? $cities[$countryIdx] : null,
                'country' => $countries[$countryIdx],
                'postal_code' => $hasAddress ? (string) rand(10000, 99999) : null,
                'metadata' => rand(1, 3) === 1 ? json_encode(['source' => ['website', 'pos', 'import', 'api'][array_rand(['website', 'pos', 'import', 'api'])]]) : null,
                'created_at' => now()->subDays(rand(0, 180))->subHours(rand(0, 23)),
            ]);
        }

        $this->command->info('50 test customers created!');
    }
}
