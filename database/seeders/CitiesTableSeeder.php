<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\City;
use Illuminate\Support\Facades\DB;

class CitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing cities and their S3 images
        $this->clearExistingCities();

        // List of all Jordanian governorates with image filenames
        $cities = [
            ['name_en' => 'Amman',  'name_ar' => 'عمان',    'file' => 'amman.jpg'],
            ['name_en' => 'ِِAl Salt','name_ar' => 'السلط', 'file' => 'al-salt.jpeg'],
            ['name_en' => 'Dead Sea','name_ar' => 'البحر الميت', 'file' => 'dead-sea.jpeg'],
            ['name_en' => 'Zarqa',  'name_ar' => 'الزرقاء', 'file' => 'zarqa.jpg'],
            ['name_en' => 'Wadi Rum','name_ar' => 'وادي رم', 'file' => 'wadi-rum.jpeg'],
            ['name_en' => 'Jerash', 'name_ar' => 'جرش',     'file' => 'jerash.jpg'],
            ['name_en' => 'Irbid',  'name_ar' => 'إربد',    'file' => 'irbid.jpg'],
            ['name_en' => 'Ajloun', 'name_ar' => 'عجلون',   'file' => 'ajloun.jpg'],
            ['name_en' => 'Aqaba',  'name_ar' => 'العقبة',  'file' => 'aqaba.jpg'],
            ['name_en' => 'Balqa',  'name_ar' => 'البلقاء', 'file' => 'balqa.jpg'],
            ['name_en' => 'Karak',  'name_ar' => 'الكرك',   'file' => 'karak.jpg'],
            ['name_en' => 'Maan',   'name_ar' => 'معان',    'file' => 'maan.jpg'],
            ['name_en' => 'Madaba', 'name_ar' => 'مادبا',   'file' => 'madaba.jpg'],
            ['name_en' => 'Mafraq', 'name_ar' => 'المفرق',  'file' => 'mafraq.jpg'],
            ['name_en' => 'Tafelah','name_ar' => 'الطفيلة', 'file' => 'tafelah.jpg'],
        ];

        foreach ($cities as $index => $attrs) {
            try {
                // Upload image to S3
                $s3Url = $this->uploadImageToS3($attrs['file'], $attrs['name_en']);

                // Create city record
                City::create([
                    'name_en' => $attrs['name_en'],
                    'name_ar' => $attrs['name_ar'],
                    'status'  => City::STATUS_PUBLISHED,
                    'order'   => $index + 1,
                    'image'   => $s3Url,
                ]);

                $this->command->info("Created city: {$attrs['name_en']}");

            } catch (\Exception $e) {
                $this->command->error("Error creating city {$attrs['name_en']}: " . $e->getMessage());
            }
        }

        $this->command->info('Seeded all Jordanian cities.');
    }

    /**
     * Clear existing cities and delete their S3 images
     */
    private function clearExistingCities(): void
    {
        try {
            $this->command->info('Clearing existing cities and S3 images...');
            
            // Get all existing cities with images
            $existingCities = City::whereNotNull('image')->get();
            
            // Delete S3 images
            foreach ($existingCities as $city) {
                if ($city->image) {
                    $this->deleteImageFromS3($city->image, $city->name_en);
                }
            }
            
            // Update farms to remove city references
            DB::table('farms')->update(['city_id' => null]);
            
            // Delete all cities
            City::query()->delete();
            
            $this->command->info('Cleared all existing cities and their S3 images.');
            
        } catch (\Exception $e) {
            $this->command->error("Error clearing existing cities: " . $e->getMessage());
        }
    }

    /**
     * Delete image from S3
     */
    private function deleteImageFromS3(string $imageUrl, string $cityName): void
    {
        try {
            // Extract the S3 path from the full URL
            // URL format: https://bucket-name.s3.region.amazonaws.com/cities/filename.jpg
            $urlParts = parse_url($imageUrl);
            if (isset($urlParts['path'])) {
                // Remove leading slash and get the S3 path
                $s3Path = ltrim($urlParts['path'], '/');
                
                // Check if file exists on S3 before attempting to delete
                if (Storage::disk('s3')->exists($s3Path)) {
                    Storage::disk('s3')->delete($s3Path);
                    $this->command->info("Deleted S3 image for {$cityName}: {$s3Path}");
                } else {
                    $this->command->warn("S3 image not found for {$cityName}: {$s3Path}");
                }
            }
        } catch (\Exception $e) {
            $this->command->error("Error deleting S3 image for {$cityName}: " . $e->getMessage());
        }
    }

    /**
     * Upload image to S3 following the same pattern as the store method
     */
    private function uploadImageToS3(string $filename, string $cityNameEn): ?string
    {
        try {
            // Path to city images directory (matching your folder structure)
            $localImagePath = storage_path("app/city_images/{$filename}");
            
            // Check if local image exists
            if (!file_exists($localImagePath)) {
                $this->command->warn("Image not found: {$filename}. Skipping image upload for {$cityNameEn}");
                return null;
            }

            // Get file extension
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            
            // Create slug and filename following the same pattern as store method
            $slug = Str::slug($cityNameEn);
            $newFilename = "{$slug}-" . time() . ".{$ext}";

            // Read file content
            $fileContent = file_get_contents($localImagePath);

            // Upload to S3 under 'cities/' folder (same as store method)
            $s3Path = "cities/{$newFilename}";
            Storage::disk('s3')->put($s3Path, $fileContent);

            // Return the S3 URL (same as store method)
            return Storage::disk('s3')->url($s3Path);

        } catch (\Exception $e) {
            $this->command->error("Error uploading image for {$cityNameEn}: " . $e->getMessage());
            return null;
        }
    }
}