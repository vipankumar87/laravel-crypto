<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Services\CurrencyManager;
use App\Models\CurrencyExchangeRate;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencyManager = new CurrencyManager();

        // Sync currencies to database
        $currencyManager->syncToDatabase();

        $this->command->info('Currencies synced to database');

        // Add some sample exchange rates
        $exchangeRates = [
            // USDT as base currency
            ['from' => 'USDT', 'to' => 'BTC', 'rate' => 0.000015], // 1 USDT = 0.000015 BTC
            ['from' => 'BTC', 'to' => 'USDT', 'rate' => 66666.67], // 1 BTC = 66666.67 USDT

            ['from' => 'USDT', 'to' => 'ETH', 'rate' => 0.0004], // 1 USDT = 0.0004 ETH
            ['from' => 'ETH', 'to' => 'USDT', 'rate' => 2500.0], // 1 ETH = 2500 USDT

            ['from' => 'USDT', 'to' => 'SOL', 'rate' => 0.0067], // 1 USDT = 0.0067 SOL
            ['from' => 'SOL', 'to' => 'USDT', 'rate' => 149.25], // 1 SOL = 149.25 USDT

            ['from' => 'USDT', 'to' => 'DOGE', 'rate' => 8.33], // 1 USDT = 8.33 DOGE
            ['from' => 'DOGE', 'to' => 'USDT', 'rate' => 0.12], // 1 DOGE = 0.12 USDT

            // Cross rates
            ['from' => 'BTC', 'to' => 'ETH', 'rate' => 26.67], // 1 BTC = 26.67 ETH
            ['from' => 'ETH', 'to' => 'BTC', 'rate' => 0.0375], // 1 ETH = 0.0375 BTC

            ['from' => 'BTC', 'to' => 'SOL', 'rate' => 446.67], // 1 BTC = 446.67 SOL
            ['from' => 'SOL', 'to' => 'BTC', 'rate' => 0.00224], // 1 SOL = 0.00224 BTC

            ['from' => 'BTC', 'to' => 'DOGE', 'rate' => 555555.56], // 1 BTC = 555555.56 DOGE
            ['from' => 'DOGE', 'to' => 'BTC', 'rate' => 0.0000018], // 1 DOGE = 0.0000018 BTC

            ['from' => 'ETH', 'to' => 'SOL', 'rate' => 16.75], // 1 ETH = 16.75 SOL
            ['from' => 'SOL', 'to' => 'ETH', 'rate' => 0.0597], // 1 SOL = 0.0597 ETH

            ['from' => 'ETH', 'to' => 'DOGE', 'rate' => 20833.33], // 1 ETH = 20833.33 DOGE
            ['from' => 'DOGE', 'to' => 'ETH', 'rate' => 0.000048], // 1 DOGE = 0.000048 ETH

            ['from' => 'SOL', 'to' => 'DOGE', 'rate' => 1244.17], // 1 SOL = 1244.17 DOGE
            ['from' => 'DOGE', 'to' => 'SOL', 'rate' => 0.00080], // 1 DOGE = 0.00080 SOL
        ];

        foreach ($exchangeRates as $rate) {
            CurrencyExchangeRate::updateOrCreate(
                [
                    'from_currency' => $rate['from'],
                    'to_currency' => $rate['to'],
                ],
                [
                    'rate' => $rate['rate'],
                    'source' => 'seeder',
                    'is_active' => true,
                    'last_updated' => now(),
                ]
            );
        }

        $this->command->info('Exchange rates seeded successfully');
    }
}