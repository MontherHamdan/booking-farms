<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\File;
use Illuminate\Support\Carbon;
use App\Models\Farm;
use App\Models\Feature;
use App\Models\City;
use App\Models\User;
use Faker\Factory as Faker;

class FarmsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // 1) Define your farms with slug for files and Arabic names
        $farms = [
            ['slug' => 'valancia',      'name_ar' => 'فالنسيا'],
            ['slug' => 'taj',           'name_ar' => 'تاج'],
            ['slug' => 'stars',         'name_ar' => 'نجوم'],
            ['slug' => 'serein',        'name_ar' => 'سيرين'],
            ['slug' => 'qemah',         'name_ar' => 'القمة'],
            ['slug' => 'mountain-view', 'name_ar' => 'الجبل-مطل'],
            ['slug' => 'monther',       'name_ar' => 'منذر'],
            ['slug' => 'logo',          'name_ar' => 'لوجو'],
            ['slug' => 'golden',        'name_ar' => 'الذهب'],
            ['slug' => 'alkaram',       'name_ar' => 'الكرم'],
        ];

        // 2) Preload cities & features for random assignment
        $cityIds = City::published()->ordered()->pluck('id')->all();
        $featureIds = Feature::orderBy('order')->pluck('id')->all();

        // 3) Get all user IDs for rating assignment (excluding admin user with ID 1)
        $userIds = User::where('id', '>', 1)->pluck('id')->all();

        foreach ($farms as $index => $info) {
            $slug = $info['slug'];
            $nameEn = Str::title(str_replace('-', ' ', $slug));
            $nameAr = $info['name_ar'];
            $descriptionEn = "Enjoy a luxurious stay at {$nameEn}, featuring modern amenities, scenic views, and personalized service.";
            $descriptionAr = "استمتع بإقامة فاخرة في {$nameAr}، مع وسائل الراحة الحديثة والإطلالات الخلابة والخدمة المميزة.";

            // 4) Create farm record
            $farm = Farm::create([
                'user_id'             => 1,
                'city_id'             => $cityIds[array_rand($cityIds)],
                'name_en'             => $nameEn,
                'name_ar'             => $nameAr,
                'description_en'      => $descriptionEn,
                'description_ar'      => $descriptionAr,
                'passengers_count'    => rand(4, 20),
                'not_available_dates' => [
                    Carbon::now()->addDays(rand(5, 15))->toDateString(),
                    Carbon::now()->addDays(rand(16, 30))->toDateString(),
                ],
            ]);

            // 5) Attach a random subset of features
            shuffle($featureIds);
            $farm->features()->attach(array_slice($featureIds, 0, rand(3, 6)));

            // 6) Create pricing entries
            $this->createPricing($farm);

            // 7) Optionally create an offer for variety
            if ($index % 2 === 0) {
                $start = Carbon::now();
                $end   = (clone $start)->addDays(rand(50, 100));
                $farm->offers()->create([
                    'percentage' => rand(5, 30),
                    'start_date' => $start->toDateString(),
                    'end_date'   => $end->toDateString(),
                    'is_active'  => true,
                ]);
            }

            // 8) Upload and attach images: main + gallery (1–10)
            $this->attachImages($farm, $slug);

            // 9) Add ratings for the first 5 farms only
            if ($index < 5) {
                $this->createRatings($farm, $userIds, $faker);
            }

            $this->command->info("Seeded farm: {$nameEn} ({$nameAr}) (ID: {$farm->id})");
        }

        $this->command->info('All farms seeded successfully.');
    }

    /** Create pricing for each type */
    protected function createPricing(Farm $farm): void
    {
        $types = ['day_use', 'night', 'full_day'];
        foreach ($types as $type) {
            $farm->pricing()->create([
                'price_type'     => $type,
                'start_time'     => $type === 'night' ? '18:00' : '08:00',
                'end_time'       => $type === 'day_use' ? '18:00' : ($type === 'night' ? '06:00' : '23:59'),
                'saturday_price' => rand(100, 300),
                'sunday_price'   => rand(100, 300),
                'monday_price'   => rand(100, 300),
                'tuesday_price'  => rand(100, 300),
                'wednesday_price'=> rand(100, 300),
                'thursday_price' => rand(100, 300),
                'friday_price'   => rand(100, 300),
            ]);
        }
    }

    /** Upload main and gallery images */
    protected function attachImages(Farm $farm, string $slug): void
    {
        // Use storage/app/farm_images instead of app_path
        $base = storage_path('app/farm_images');

        // Main image
        $this->uploadAndCreateImage($farm, "{$base}/{$slug}_main.jpg", true);

        // Gallery images 1–10
        for ($i = 1; $i <= 10; $i++) {
            $this->uploadAndCreateImage($farm, "{$base}/{$slug}_{$i}.jpg", false);
        }
    }

    /** Helper to upload and attach */
    protected function uploadAndCreateImage(Farm $farm, string $localPath, bool $isMain): void
    {
        if (! file_exists($localPath)) {
            $this->command->warn("Missing image: {$localPath}");
            return;
        }

        $ext      = pathinfo($localPath, PATHINFO_EXTENSION);
        $filename = Str::slug($farm->name_en) . '-' . ($isMain ? 'main' : uniqid()) . ".{$ext}";

        $file = new File($localPath);
        $path = Storage::disk('s3')->putFileAs('farms', $file, $filename);
        $url  = Storage::disk('s3')->url($path);

        $farm->images()->create([ 'image_path' => $url, 'is_main' => $isMain ]);
    }

    /** Create ratings for a farm */
    protected function createRatings(Farm $farm, array $userIds, $faker): void
    {
        // Number of ratings to create (5 to 10)
        $numberOfRatings = rand(5, 10);
        
        // Shuffle user IDs to get random users
        shuffle($userIds);
        
        // Take only the number of users we need (prevent duplicates)
        $selectedUsers = array_slice($userIds, 0, min($numberOfRatings, count($userIds)));
        
        // Possible rating values (1 to 5 with 0.5 increments)
        $possibleRatings = [1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5];
        
        foreach ($selectedUsers as $userId) {
            $rating = $possibleRatings[array_rand($possibleRatings)];
            
            // Generate a realistic review based on rating
            $review = $this->generateReview($rating, $farm->name_en, $faker);
            
            $farm->ratings()->create([
                'user_id' => $userId,
                'rating' => $rating,
                'review' => $review,
                'created_at' => Carbon::now()->subDays(rand(1, 30)), // Random date in past 30 days
                'updated_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);
        }
        
        $this->command->info("Added {$numberOfRatings} ratings for farm: {$farm->name_en}");
    }

    /** Generate realistic review based on rating */
    protected function generateReview(float $rating, string $farmName, $faker): string
    {
        $reviews = [];
        
        if ($rating >= 4.5) {
            $reviews = [
                "مكان رائع جداً! استمتعت بوقتي هناك وأنصح الجميع بزيارته.",
                "تجربة ممتازة، الخدمة والمرافق من الدرجة الأولى.",
                "أفضل مزرعة زرتها، كل شيء مثالي والمناظر خلابة.",
                "Amazing place! Everything was perfect and the staff was very helpful.",
                "Outstanding experience, will definitely come back again!",
            ];
        } elseif ($rating >= 3.5) {
            $reviews = [
                "مكان جميل ومريح، أعجبني التصميم والهدوء.",
                "تجربة جيدة بشكل عام، المرافق نظيفة والموقع مميز.",
                "استمتعت بالزيارة، المكان مناسب للعائلات.",
                "Good place for relaxation, nice facilities and friendly staff.",
                "Pleasant experience, comfortable and well-maintained.",
            ];
        } elseif ($rating >= 2.5) {
            $reviews = [
                "المكان عادي، يحتاج لبعض التحسينات.",
                "تجربة مقبولة ولكن التوقعات كانت أعلى.",
                "مكان جيد للأطفال ولكن الخدمة بطيئة قليلاً.",
                "Average experience, some areas need improvement.",
                "Decent place but could be better maintained.",
            ];
        } else {
            $reviews = [
                "لم تكن التجربة كما توقعت، يحتاج المكان لصيانة.",
                "الخدمة لم تكن على المستوى المطلوب.",
                "المكان يحتاج لتطوير أكثر.",
                "Not what I expected, needs significant improvements.",
                "Disappointing experience, poor maintenance.",
            ];
        }
        
        return $reviews[array_rand($reviews)];
    }
}