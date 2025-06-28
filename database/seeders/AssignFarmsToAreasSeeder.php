<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Farm;
use App\Models\Area;
use App\Models\City;

class AssignFarmsToAreasSeeder extends Seeder
{
    /**
     * Assign existing farms to areas
     */
    public function run(): void
    {
        $this->command->info('Starting to assign existing farms to areas...');

        // Get all existing farms
        $farms = Farm::with('city')->get();

        if ($farms->isEmpty()) {
            $this->command->warn('No farms found to assign to areas.');
            return;
        }

        $assignedCount = 0;
        $unassignedCount = 0;

        foreach ($farms as $farm) {
            try {
                // Find areas for this farm's city
                $areas = Area::where('city_id', $farm->city_id)->published()->get();

                if ($areas->isEmpty()) {
                    $this->command->warn("No areas found for city: {$farm->city->name_en} (Farm: {$farm->name_en})");
                    $unassignedCount++;
                    continue;
                }

                // Assign farm to a random area in its city
                $randomArea = $areas->random();
                
                $farm->update([
                    'area_id' => $randomArea->id
                ]);

                $assignedCount++;
                $this->command->info("✓ Assigned farm '{$farm->name_en}' to area '{$randomArea->name_en}' in {$farm->city->name_en}");

            } catch (\Exception $e) {
                $this->command->error("✗ Error assigning farm '{$farm->name_en}': " . $e->getMessage());
                $unassignedCount++;
            }
        }

        // Summary
        $totalFarms = $farms->count();
        $this->command->info("Assignment completed!");
        $this->command->info("Total farms: {$totalFarms}");
        $this->command->info("Successfully assigned: {$assignedCount}");
        $this->command->info("Unassigned: {$unassignedCount}");

        // Show detailed statistics
        $this->showAssignmentStatistics();
    }

    /**
     * Show detailed assignment statistics
     */
    private function showAssignmentStatistics(): void
    {
        $this->command->info("\n--- Assignment Statistics ---");

        // Get farms with areas by city
        $farmsByCity = Farm::with(['city', 'area'])
            ->whereNotNull('area_id')
            ->get()
            ->groupBy('city.name_en');

        foreach ($farmsByCity as $cityName => $farms) {
            $this->command->info("\n{$cityName}: {$farms->count()} farms");
            
            $farmsByArea = $farms->groupBy('area.name_en');
            foreach ($farmsByArea as $areaName => $areaFarms) {
                $farmNames = $areaFarms->pluck('name_en')->join(', ');
                $this->command->info("  └── {$areaName}: {$areaFarms->count()} farms ({$farmNames})");
            }
        }

        // Show unassigned farms if any
        $unassignedFarms = Farm::with('city')->whereNull('area_id')->get();
        if ($unassignedFarms->isNotEmpty()) {
            $this->command->warn("\nUnassigned farms: {$unassignedFarms->count()}");
            foreach ($unassignedFarms as $farm) {
                $cityName = $farm->city ? $farm->city->name_en : 'Unknown City';
                $this->command->warn("  └── {$farm->name_en} (City: {$cityName})");
            }
        }
    }
}