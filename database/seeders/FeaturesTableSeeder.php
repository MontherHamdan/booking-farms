<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Feature;

class FeaturesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1) Clear out existing features + S3 icons
        $this->clearExistingFeatures();

        // 2) Define your features and (English/Arabic) names
        //    Make sure the 'file' names match storage/app/feature_icons/*
        $features = [
            ['slug' => 'air-conditioner',     'name_en' => 'Air Conditioner',     'name_ar' => 'مكيف هواء',         'file' => 'air-conditioner.png'],
            ['slug' => 'balcony',             'name_en' => 'Balcony',            'name_ar' => 'شرفة',             'file' => 'balcony.png'],
            ['slug' => 'barbeque-area',       'name_en' => 'Barbeque Area',      'name_ar' => 'منطقة شواء',       'file' => 'barbeque-area.png'],
            ['slug' => 'barbeque-kit',        'name_en' => 'Barbeque Kit',       'name_ar' => 'عدة شواء',         'file' => 'barbeque-kit.png'],
            ['slug' => 'bluetooth-speakers',  'name_en' => 'Bluetooth Speakers', 'name_ar' => 'مكبرات بلوتوث',    'file' => 'bluetooth-speakers.png'],
            ['slug' => 'garage',              'name_en' => 'Garage',             'name_ar' => 'كراج',             'file' => 'garage.png'],
            ['slug' => 'garden',              'name_en' => 'Garden',             'name_ar' => 'حديقة',            'file' => 'garden.png'],
            ['slug' => 'guard',               'name_en' => 'Guard',              'name_ar' => 'حارس',             'file' => 'guard.png'],
            ['slug' => 'microwave',           'name_en' => 'Microwave',          'name_ar' => 'ميكروويف',         'file' => 'microwave.png'],
            ['slug' => 'near-service',        'name_en' => 'Near Service',       'name_ar' => 'قريب من الخدمات',  'file' => 'near-service.png'],
            ['slug' => 'washing-machine',     'name_en' => 'Washing Machine',    'name_ar' => 'غسالة',            'file' => 'washing-machine.png'],
            ['slug' => 'water-heater',        'name_en' => 'Water Heater',       'name_ar' => 'سخان ماء',         'file' => 'water-heater.png'],
        ];

        // 3) Seed each feature
        foreach ($features as $index => $f) {
            try {
                $s3Url = $this->uploadIconToS3($f['slug'], $f['file']);

                Feature::create([
                    'name_en' => $f['name_en'],
                    'name_ar' => $f['name_ar'],
                    'icon'    => $s3Url,
                    'order'   => $index + 1,
                ]);

                $this->command->info("Seeded feature: {$f['name_en']}");
            } catch (\Exception $e) {
                $this->command->error("Error seeding {$f['name_en']}: " . $e->getMessage());
            }
        }

        $this->command->info('All features seeded successfully.');
    }

    /**
     * Delete existing features and their S3 icons.
     */
    private function clearExistingFeatures(): void
    {
        $this->command->info('Clearing existing features and S3 icons...');

        $existing = Feature::whereNotNull('icon')->get();
        foreach ($existing as $feat) {
            // parse full URL to get S3 path
            $path = ltrim(parse_url($feat->icon, PHP_URL_PATH), '/');
            if (Storage::disk('s3')->exists($path)) {
                Storage::disk('s3')->delete($path);
                $this->command->info("Deleted S3 icon: {$path}");
            }
        }

        // drop all feature records
        Feature::query()->delete();
        $this->command->info('Existing features cleared.');
    }

    /**
     * Upload one icon file to S3 under 'features/'.
     */
    private function uploadIconToS3(string $slug, string $filename): ?string
    {
        $local = storage_path("app/feature_icons/{$filename}");
        if (! file_exists($local)) {
            $this->command->warn("Local file missing: {$filename}");
            return null;
        }

        $ext  = pathinfo($filename, PATHINFO_EXTENSION);
        $name = "{$slug}-" . time() . ".{$ext}";
        $content = file_get_contents($local);

        // store on S3
        $s3Path = "features/{$name}";
        Storage::disk('s3')->put($s3Path, $content);

        // return public URL
        return Storage::disk('s3')->url($s3Path);
    }
}
