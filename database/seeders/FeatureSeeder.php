<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                'name_ar' => 'حمام سباحة',
                'name_en' => 'Swimming Pool',
            ],
            [
                'name_ar' => 'شواء',
                'name_en' => 'BBQ Area',
            ],
            [
                'name_ar' => 'ملعب أطفال',
                'name_en' => 'Kids Playground',
            ],
            [
                'name_ar' => 'واي فاي',
                'name_en' => 'WiFi',
            ],
            [
                'name_ar' => 'تكييف',
                'name_en' => 'Air Conditioning',
            ],
            [
                'name_ar' => 'مطبخ مجهز',
                'name_en' => 'Fully Equipped Kitchen',
            ],
            [
                'name_ar' => 'مواقف سيارات',
                'name_en' => 'Parking',
            ],
            [
                'name_ar' => 'تلفاز',
                'name_en' => 'TV',
            ],
        ];

        foreach ($features as $feature) {
            Feature::create($feature);
        }
    }
}