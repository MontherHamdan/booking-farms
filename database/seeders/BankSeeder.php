<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Bank;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            // Jordanian Banks
            [
                'name_en' => 'Arab Bank',
                'name_ar' => 'البنك العربي',
                'is_active' => true,
            ],
            [
                'name_en' => 'Jordan Islamic Bank',
                'name_ar' => 'البنك الإسلامي الأردني',
                'is_active' => true,
            ],
            [
                'name_en' => 'Cairo Amman Bank',
                'name_ar' => 'بنك القاهرة عمان',
                'is_active' => true,
            ],
            [
                'name_en' => 'Jordan Kuwait Bank',
                'name_ar' => 'بنك الأردن والكويت',
                'is_active' => true,
            ],
            [
                'name_en' => 'Housing Bank for Trade and Finance',
                'name_ar' => 'بنك الإسكان للتجارة والتمويل',
                'is_active' => true,
            ],
            [
                'name_en' => 'Jordan Commercial Bank',
                'name_ar' => 'البنك التجاري الأردني',
                'is_active' => true,
            ],
            [
                'name_en' => 'Bank of Jordan',
                'name_ar' => 'بنك الأردن',
                'is_active' => true,
            ],
            [
                'name_en' => 'Capital Bank of Jordan',
                'name_ar' => 'بنك كابيتال الأردن',
                'is_active' => true,
            ],
            [
                'name_en' => 'Jordan Ahli Bank',
                'name_ar' => 'البنك الأهلي الأردني',
                'is_active' => true,
            ],
            [
                'name_en' => 'Invest Bank Jordan',
                'name_ar' => 'بنك الاستثمار الأردني',
                'is_active' => true,
            ],
            [
                'name_en' => 'Union Bank',
                'name_ar' => 'بنك الاتحاد',
                'is_active' => true,
            ],
            [
                'name_en' => 'Société Générale Bank Jordan',
                'name_ar' => 'بنك سوسيتيه جنرال الأردن',
                'is_active' => true,
            ],
            [
                'name_en' => 'ABC Bank Jordan',
                'name_ar' => 'بنك ABC الأردن',
                'is_active' => true,
            ],
            [
                'name_en' => 'BLOM Bank Jordan',
                'name_ar' => 'بنك بلوم الأردن',
                'is_active' => true,
            ],
            [
                'name_en' => 'Bank Audi Jordan',
                'name_ar' => 'بنك عوده الأردن',
                'is_active' => true,
            ],
            [
                'name_en' => 'HSBC Bank Middle East Limited Jordan',
                'name_ar' => 'بنك إتش إس بي سي الشرق الأوسط المحدود الأردن',
                'is_active' => true,
            ],
            [
                'name_en' => 'Standard Chartered Bank Jordan',
                'name_ar' => 'بنك ستاندرد تشارترد الأردن',
                'is_active' => true,
            ],
            [
                'name_en' => 'Citibank Jordan',
                'name_ar' => 'سيتي بنك الأردن',
                'is_active' => true,
            ],
            [
                'name_en' => 'Rafidain Bank Jordan',
                'name_ar' => 'بنك الرافدين الأردن',
                'is_active' => true,
            ],
            [
                'name_en' => 'Egyptian Arab Land Bank Jordan',
                'name_ar' => 'البنك العقاري المصري العربي الأردن',
                'is_active' => true,
            ],

            // Regional Banks (for reference if needed)
            [
                'name_en' => 'Central Bank of Jordan',
                'name_ar' => 'البنك المركزي الأردني',
                'is_active' => true,
            ],
            [
                'name_en' => 'ProgressSoft Corporation',
                'name_ar' => 'شركة برجرس سوفت',
                'is_active' => true,
            ],
            [
                'name_en' => 'Islamic International Arab Bank',
                'name_ar' => 'البنك العربي الإسلامي الدولي',
                'is_active' => true,
            ],
            [
                'name_en' => 'Jordan Dubai Islamic Bank',
                'name_ar' => 'بنك الأردن دبي الإسلامي',
                'is_active' => true,
            ],
            [
                'name_en' => 'Safwa Islamic Bank',
                'name_ar' => 'بنك صفوة الإسلامي',
                'is_active' => true,
            ],
        ];

        foreach ($banks as $bank) {
            Bank::create($bank);
        }
    }
}