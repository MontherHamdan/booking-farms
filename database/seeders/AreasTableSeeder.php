<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;
use App\Models\City;

class AreasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing areas first
        $this->clearExistingAreas();

        // Get all published cities
        $cities = City::published()->ordered()->get();

        // Define areas for each Jordanian city
        $cityAreas = [
            'Amman' => [
                ['name_en' => 'Abdali', 'name_ar' => 'العبدلي'],
                ['name_en' => 'Jabal Amman', 'name_ar' => 'جبل عمان'],
                ['name_en' => 'Shmeisani', 'name_ar' => 'الشميساني'],
                ['name_en' => 'Dabouq', 'name_ar' => 'دابوق'],
                ['name_en' => 'Khalda', 'name_ar' => 'خلدا'],
                ['name_en' => 'Marj Al Hamam', 'name_ar' => 'مرج الحمام'],
                ['name_en' => 'Sweifieh', 'name_ar' => 'السويفية'],
                ['name_en' => 'Tla Al Ali', 'name_ar' => 'تلاع العلي'],
                ['name_en' => 'Um Uthaina', 'name_ar' => 'أم أذينة'],
                ['name_en' => 'Wadi Seer', 'name_ar' => 'وادي السير'],
            ],
            'Al Salt' => [
                ['name_en' => 'Old Salt', 'name_ar' => 'السلط القديمة'],
                ['name_en' => 'Zay', 'name_ar' => 'زي'],
                ['name_en' => 'Mahis', 'name_ar' => 'ماحص'],
                ['name_en' => 'Fuheis', 'name_ar' => 'الفحيص'],
                ['name_en' => 'Al Arda', 'name_ar' => 'العارضة'],
            ],
            'Dead Sea' => [
                ['name_en' => 'Sweimeh', 'name_ar' => 'السويمة'],
                ['name_en' => 'Dead Sea Resort Area', 'name_ar' => 'منطقة منتجعات البحر الميت'],
                ['name_en' => 'Ghor Al Mazraa', 'name_ar' => 'غور المزرعة'],
                ['name_en' => 'South Shouneh', 'name_ar' => 'الشونة الجنوبية'],
            ],
            'Zarqa' => [
                ['name_en' => 'Zarqa City Center', 'name_ar' => 'وسط مدينة الزرقاء'],
                ['name_en' => 'Russeifa', 'name_ar' => 'الرصيفة'],
                ['name_en' => 'Hashimiyeh', 'name_ar' => 'الهاشمية'],
                ['name_en' => 'Azraq', 'name_ar' => 'الأزرق'],
                ['name_en' => 'Dulayl', 'name_ar' => 'دليل'],
            ],
            'Wadi Rum' => [
                ['name_en' => 'Wadi Rum Village', 'name_ar' => 'قرية وادي رم'],
                ['name_en' => 'Protected Area', 'name_ar' => 'المحمية'],
                ['name_en' => 'Desert Camps', 'name_ar' => 'المخيمات الصحراوية'],
                ['name_en' => 'Lawrence Spring', 'name_ar' => 'عين لورانس'],
            ],
            'Jerash' => [
                ['name_en' => 'Jerash City', 'name_ar' => 'مدينة جرش'],
                ['name_en' => 'Sakib', 'name_ar' => 'سكيب'],
                ['name_en' => 'Souf', 'name_ar' => 'صوف'],
                ['name_en' => 'Burqish', 'name_ar' => 'برقش'],
                ['name_en' => 'Ajloun Road', 'name_ar' => 'طريق عجلون'],
            ],
            'Irbid' => [
                ['name_en' => 'Downtown Irbid', 'name_ar' => 'وسط إربد'],
                ['name_en' => 'University Area', 'name_ar' => 'المنطقة الجامعية'],
                ['name_en' => 'Al Husn', 'name_ar' => 'الحصن'],
                ['name_en' => 'Ramtha', 'name_ar' => 'الرمثا'],
                ['name_en' => 'Koura', 'name_ar' => 'الكورة'],
                ['name_en' => 'Mafraq Road', 'name_ar' => 'طريق المفرق'],
            ],
            'Ajloun' => [
                ['name_en' => 'Ajloun City', 'name_ar' => 'مدينة عجلون'],
                ['name_en' => 'Anjara', 'name_ar' => 'عنجرة'],
                ['name_en' => 'Sakhra', 'name_ar' => 'صخرة'],
                ['name_en' => 'Forest Reserve', 'name_ar' => 'محمية الغابات'],
            ],
            'Aqaba' => [
                ['name_en' => 'Aqaba City Center', 'name_ar' => 'وسط مدينة العقبة'],
                ['name_en' => 'South Beach', 'name_ar' => 'الشاطئ الجنوبي'],
                ['name_en' => 'Tala Bay', 'name_ar' => 'خليج تالا'],
                ['name_en' => 'Marina', 'name_ar' => 'المارينا'],
                ['name_en' => 'Industrial Zone', 'name_ar' => 'المنطقة الصناعية'],
            ],
            'Balqa' => [
                ['name_en' => 'Salt District', 'name_ar' => 'قضاء السلط'],
                ['name_en' => 'Deir Alla', 'name_ar' => 'دير علا'],
                ['name_en' => 'Shouneh Shamaliyeh', 'name_ar' => 'الشونة الشمالية'],
                ['name_en' => 'Ain Ghazal', 'name_ar' => 'عين غزال'],
            ],
            'Karak' => [
                ['name_en' => 'Karak City', 'name_ar' => 'مدينة الكرك'],
                ['name_en' => 'Mutah', 'name_ar' => 'مؤتة'],
                ['name_en' => 'Qadisiyeh', 'name_ar' => 'القادسية'],
                ['name_en' => 'Mazar', 'name_ar' => 'المزار'],
                ['name_en' => 'Ghor Safi', 'name_ar' => 'غور الصافي'],
            ],
            'Maan' => [
                ['name_en' => 'Maan City', 'name_ar' => 'مدينة معان'],
                ['name_en' => 'Petra District', 'name_ar' => 'منطقة البتراء'],
                ['name_en' => 'Shobak', 'name_ar' => 'الشوبك'],
                ['name_en' => 'Al Jafr', 'name_ar' => 'الجفر'],
                ['name_en' => 'Desert Highway', 'name_ar' => 'الطريق الصحراوي'],
            ],
            'Madaba' => [
                ['name_en' => 'Madaba City', 'name_ar' => 'مدينة مادبا'],
                ['name_en' => 'Dhiban', 'name_ar' => 'ذيبان'],
                ['name_en' => 'Libb', 'name_ar' => 'لب'],
                ['name_en' => 'Machaerus', 'name_ar' => 'مخايروس'],
                ['name_en' => 'Mount Nebo', 'name_ar' => 'جبل نيبو'],
            ],
            'Mafraq' => [
                ['name_en' => 'Mafraq City', 'name_ar' => 'مدينة المفرق'],
                ['name_en' => 'Badia', 'name_ar' => 'البادية'],
                ['name_en' => 'Safawi', 'name_ar' => 'الصفاوي'],
                ['name_en' => 'Ruwaished', 'name_ar' => 'الرويشد'],
                ['name_en' => 'Umm Al Jimal', 'name_ar' => 'أم الجمال'],
            ],
            'Tafelah' => [
                ['name_en' => 'Tafelah City', 'name_ar' => 'مدينة الطفيلة'],
                ['name_en' => 'Hesa', 'name_ar' => 'الحسا'],
                ['name_en' => 'Busayra', 'name_ar' => 'بصيرا'],
                ['name_en' => 'Dana Reserve', 'name_ar' => 'محمية ضانا'],
                ['name_en' => 'Shobak Road', 'name_ar' => 'طريق الشوبك'],
            ],
        ];

        $totalAreasCreated = 0;

        foreach ($cities as $city) {
            if (isset($cityAreas[$city->name_en])) {
                $areas = $cityAreas[$city->name_en];
                
                $this->command->info("Creating areas for {$city->name_en}...");
                
                foreach ($areas as $index => $areaData) {
                    try {
                        Area::create([
                            'city_id' => $city->id,
                            'name_en' => $areaData['name_en'],
                            'name_ar' => $areaData['name_ar'],
                            'status' => Area::STATUS_PUBLISHED,
                            'order' => $index + 1,
                        ]);
                        
                        $totalAreasCreated++;
                        $this->command->info("  ✓ Created area: {$areaData['name_en']} ({$areaData['name_ar']})");
                        
                    } catch (\Exception $e) {
                        $this->command->error("  ✗ Error creating area {$areaData['name_en']}: " . $e->getMessage());
                    }
                }
            } else {
                $this->command->warn("No areas defined for city: {$city->name_en}");
            }
        }

        $this->command->info("Successfully created {$totalAreasCreated} areas across " . $cities->count() . " cities.");
    }

    /**
     * Clear existing areas
     */
    private function clearExistingAreas(): void
    {
        try {
            $this->command->info('Clearing existing areas...');
            
            // Update farms to remove area references if they exist
            \DB::table('farms')->update(['area_id' => null]);
            
            // Delete all areas
            Area::query()->delete();
            
            $this->command->info('Cleared all existing areas.');
            
        } catch (\Exception $e) {
            $this->command->error("Error clearing existing areas: " . $e->getMessage());
        }
    }
}