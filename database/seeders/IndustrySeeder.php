<?php

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $industries = [
            ['name' => ['en' => 'Healthcare', 'ar' => 'الرعاية الصحية'], 'slug' => ['en' => 'healthcare', 'ar' => 'healthcare'], 'order' => 1],
            ['name' => ['en' => 'Medical Engineering', 'ar' => 'الهندسة الطبية'], 'slug' => ['en' => 'medical-engineering', 'ar' => 'medical-engineering'], 'order' => 2],
            ['name' => ['en' => 'Real Estate', 'ar' => 'العقارات'], 'slug' => ['en' => 'real-estate', 'ar' => 'real-estate'], 'order' => 3],
            ['name' => ['en' => 'Tourism', 'ar' => 'السياحة'], 'slug' => ['en' => 'tourism', 'ar' => 'tourism'], 'order' => 4],
            ['name' => ['en' => 'Restaurants', 'ar' => 'المطاعم'], 'slug' => ['en' => 'restaurants', 'ar' => 'restaurants'], 'order' => 5],
            ['name' => ['en' => 'Retail', 'ar' => 'التجزئة'], 'slug' => ['en' => 'retail', 'ar' => 'retail'], 'order' => 6],
            ['name' => ['en' => 'Automotive', 'ar' => 'السيارات'], 'slug' => ['en' => 'automotive', 'ar' => 'automotive'], 'order' => 7],
            ['name' => ['en' => 'Construction', 'ar' => 'البناء'], 'slug' => ['en' => 'construction', 'ar' => 'construction'], 'order' => 8],
            ['name' => ['en' => 'Professional Services', 'ar' => 'الخدمات المهنية'], 'slug' => ['en' => 'professional-services', 'ar' => 'professional-services'], 'order' => 9],
            ['name' => ['en' => 'Education', 'ar' => 'التعليم'], 'slug' => ['en' => 'education', 'ar' => 'education'], 'order' => 10],
            ['name' => ['en' => 'Government', 'ar' => 'القطاع الحكومي'], 'slug' => ['en' => 'government', 'ar' => 'government'], 'order' => 11],
            ['name' => ['en' => 'Hospitality', 'ar' => 'الضيافة'], 'slug' => ['en' => 'hospitality', 'ar' => 'hospitality'], 'order' => 12],
            ['name' => ['en' => 'Manufacturing', 'ar' => 'التصنيع'], 'slug' => ['en' => 'manufacturing', 'ar' => 'manufacturing'], 'order' => 13],
            ['name' => ['en' => 'B2B', 'ar' => 'الأعمال للأعمال'], 'slug' => ['en' => 'b2b', 'ar' => 'b2b'], 'order' => 14],
            ['name' => ['en' => 'Technology', 'ar' => 'التكنولوجيا'], 'slug' => ['en' => 'technology', 'ar' => 'technology'], 'order' => 15],
        ];

        foreach ($industries as $industry) {
            Industry::updateOrCreate(['order' => $industry['order']], $industry);
        }
    }
}
