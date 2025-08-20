<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Payroll;
use Carbon\Carbon;

class PayrollSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        if ($users->count() === 0) {
            $this->command->info('No users found. Please create some users first.');
            return;
        }

        // Create sample payroll data for each user
        foreach ($users as $user) {
            // Create 3-6 months of payroll history
            $months = rand(3, 6);
            
            for ($i = 0; $i < $months; $i++) {
                $date = Carbon::now()->subMonths($i);
                
                Payroll::create([
                    'user_id' => $user->id,
                    'gaji_bersih' => rand(5000000, 8000000), // 5-8 million IDR
                    'tanggal_dibayarkan' => $date->format('Y-m-d'),
                    'mdr_bws_bri' => rand(0, 200000),
                    'btn' => rand(0, 150000),
                    'twp' => rand(0, 100000),
                    'persit' => rand(0, 250000),
                    'ikka_persit' => rand(0, 100000),
                    'koperasi' => rand(0, 300000),
                    'barak' => rand(0, 150000),
                    'kowad' => rand(0, 100000),
                    'titipan' => rand(0, 200000),
                    'tenes' => rand(0, 100000),
                    'remaja' => rand(0, 150000),
                    'galon' => rand(0, 50000),
                    'sosial' => rand(0, 100000),
                    'pns' => rand(0, 200000),
                    'bel_wajib_kop' => rand(0, 150000),
                    'custom_1_name' => 'Asuransi',
                    'custom_1_value' => rand(0, 100000),
                    'custom_2_name' => 'Tabungan Wajib',
                    'custom_2_value' => rand(0, 200000),
                ]);
            }
        }

        $this->command->info('Sample payroll data created successfully!');
    }
}