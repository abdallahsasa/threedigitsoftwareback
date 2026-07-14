<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cat1 = BlogCategory::create([
            'name' => ['en' => 'E-commerce', 'ar' => 'التجارة الإلكترونية'],
            'slug' => ['en' => 'e-commerce', 'ar' => 'e-commerce'],
        ]);

        $cat2 = BlogCategory::create([
            'name' => ['en' => 'Web Development', 'ar' => 'تطوير الويب'],
            'slug' => ['en' => 'web-development', 'ar' => 'web-development'],
        ]);

        $cat3 = BlogCategory::create([
            'name' => ['en' => 'AI & Automation', 'ar' => 'الذكاء الاصطناعي والأتمتة'],
            'slug' => ['en' => 'ai-automation', 'ar' => 'ai-automation'],
        ]);

        BlogPost::create([
            'title' => ['en' => 'The Future of Headless Commerce in 2026', 'ar' => 'مستقبل التجارة بدون رأس في عام 2026'],
            'slug' => ['en' => 'future-of-headless-commerce', 'ar' => 'future-of-headless-commerce'],
            'excerpt' => ['en' => 'Why decoupled architectures are defining the next generation of scalable online retail platforms.', 'ar' => 'لماذا تحدد البنى المنفصلة الجيل القادم من منصات التجزئة القابلة للتطوير عبر الإنترنت.'],
            'content' => ['en' => 'Full article content here...', 'ar' => 'محتوى المقال كاملاً هنا...'],
            'blog_category_id' => $cat1->id,
            'status' => 'published',
            'published_at' => now()->subDays(2),
        ]);

        BlogPost::create([
            'title' => ['en' => 'Optimizing Laravel APIs for Millions of Requests', 'ar' => 'تحسين واجهات برمجة تطبيقات Laravel لملايين الطلبات'],
            'slug' => ['en' => 'optimizing-laravel-apis', 'ar' => 'optimizing-laravel-apis'],
            'excerpt' => ['en' => 'Deep dive into caching strategies, query optimization, and queuing systems for high-traffic Laravel backends.', 'ar' => 'نظرة عميقة في استراتيجيات التخزين المؤقت، تحسين الاستعلام، وأنظمة قوائم الانتظار في Laravel.'],
            'content' => ['en' => 'Full article content here...', 'ar' => 'محتوى المقال كاملاً هنا...'],
            'blog_category_id' => $cat2->id,
            'status' => 'published',
            'published_at' => now()->subDays(5),
        ]);

        BlogPost::create([
            'title' => ['en' => 'Why AI Automation is the Next Step for B2B Platforms', 'ar' => 'لماذا تعتبر أتمتة الذكاء الاصطناعي الخطوة التالية لمنصات B2B'],
            'slug' => ['en' => 'ai-automation-b2b', 'ar' => 'ai-automation-b2b'],
            'excerpt' => ['en' => 'How integrating Large Language Models and automated workflows can drastically cut operational costs.', 'ar' => 'كيف يمكن لدمج النماذج اللغوية الكبيرة وسير العمل الآلي أن يقلل بشكل كبير من التكاليف التشغيلية.'],
            'content' => ['en' => 'Full article content here...', 'ar' => 'محتوى المقال كاملاً هنا...'],
            'blog_category_id' => $cat3->id,
            'status' => 'published',
            'published_at' => now()->subDays(10),
        ]);
    }
}
