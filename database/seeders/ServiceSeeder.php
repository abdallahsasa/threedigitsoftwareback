<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => ['en' => 'Web Development', 'ar' => 'تطوير الويب'],
                'slug' => ['en' => 'web', 'ar' => 'web'],
                'overview' => ['en' => 'We build fast, secure, and scalable web applications tailored to your business needs, utilizing modern frameworks like React and Next.js alongside powerful backends like Laravel.', 'ar' => 'نقوم ببناء تطبيقات ويب سريعة وآمنة وقابلة للتطوير مصممة خصيصًا لتلبية احتياجات عملك.'],
                'problems_solved' => ['en' => ['Outdated digital presence', 'Slow load times', 'Poor mobile experience', 'Unscalable architectures'], 'ar' => ['التواجد الرقمي القديم', 'بطء أوقات التحميل', 'ضعف تجربة الجوال', 'هياكل غير قابلة للتطوير']],
                'capabilities' => ['en' => ['Custom Web Apps', 'Headless CMS', 'E-commerce Platforms', 'Progressive Web Apps'], 'ar' => ['تطبيقات الويب المخصصة', 'أنظمة إدارة المحتوى', 'منصات التجارة الإلكترونية', 'تطبيقات الويب التقدمية']],
                'process' => ['en' => ['1. Discovery & Strategy', '2. UI/UX Design', '3. Architecture & Development', '4. Testing & QA', '5. Deployment & Maintenance'], 'ar' => ['1. الاكتشاف والاستراتيجية', '2. تصميم واجهة المستخدم', '3. التطوير', '4. الاختبار', '5. النشر والصيانة']],
                'order' => 1,
                'is_published' => true,
            ],
            [
                'name' => ['en' => 'Mobile Apps', 'ar' => 'تطبيقات الجوال'],
                'slug' => ['en' => 'mobile', 'ar' => 'mobile'],
                'overview' => ['en' => 'Native and cross-platform mobile applications that deliver exceptional user experiences on both iOS and Android platforms using React Native.', 'ar' => 'تطبيقات جوال أصلية وعبر الأنظمة الأساسية تقدم تجارب مستخدم استثنائية على كل من منصتي iOS و Android.'],
                'problems_solved' => ['en' => ['Lack of mobile engagement', 'Fragmented codebases', 'Poor performance on mobile devices', 'Low conversion rates'], 'ar' => ['نقص التفاعل على الجوال', 'قواعد تعليمات برمجية مجزأة', 'ضعف الأداء', 'معدلات تحويل منخفضة']],
                'capabilities' => ['en' => ['iOS App Development', 'Android App Development', 'Cross-Platform Apps', 'App Store Optimization'], 'ar' => ['تطوير تطبيقات iOS', 'تطوير تطبيقات Android', 'تطبيقات عبر الأنظمة الأساسية', 'تحسين متجر التطبيقات']],
                'process' => ['en' => ['1. Concept & Prototyping', '2. Native/Cross-platform Dev', '3. Beta Testing', '4. App Store Submission'], 'ar' => ['1. المفهوم والنمذجة', '2. التطوير', '3. الاختبار التجريبي', '4. الإرسال إلى متجر التطبيقات']],
                'order' => 2,
                'is_published' => true,
            ],
            [
                'name' => ['en' => 'Business Platforms', 'ar' => 'منصات الأعمال'],
                'slug' => ['en' => 'platforms', 'ar' => 'platforms'],
                'overview' => ['en' => 'Comprehensive enterprise solutions, internal tools, dashboards, and automated workflows designed to streamline operations and boost productivity.', 'ar' => 'حلول مؤسسية شاملة وأدوات داخلية ولوحات معلومات وسير عمل آلي مصمم لتبسيط العمليات.'],
                'problems_solved' => ['en' => ['Inefficient manual workflows', 'Siloed data', 'Lack of business intelligence', 'High operational costs'], 'ar' => ['سير العمل اليدوي غير الفعال', 'البيانات المنعزلة', 'نقص ذكاء الأعمال', 'ارتفاع التكاليف التشغيلية']],
                'capabilities' => ['en' => ['ERP & CRM Systems', 'Custom Dashboards', 'SaaS Platforms', 'API Integrations'], 'ar' => ['أنظمة تخطيط موارد المؤسسات وإدارة علاقات العملاء', 'لوحات المعلومات المخصصة', 'منصات البرمجيات كخدمة', 'تكامل واجهة برمجة التطبيقات']],
                'process' => ['en' => ['1. Workflow Analysis', '2. System Architecture', '3. Secure Development', '4. Integration', '5. Training & Support'], 'ar' => ['1. تحليل سير العمل', '2. بنية النظام', '3. التطوير الآمن', '4. التكامل', '5. التدريب والدعم']],
                'order' => 3,
                'is_published' => true,
            ],
            [
                'name' => ['en' => 'AI & Automation', 'ar' => 'الذكاء الاصطناعي والأتمتة'],
                'slug' => ['en' => 'ai', 'ar' => 'ai'],
                'overview' => ['en' => 'Leverage the power of Artificial Intelligence to automate repetitive tasks, gain predictive insights, and build smart agents that drive business forward.', 'ar' => 'استفد من قوة الذكاء الاصطناعي لأتمتة المهام المتكررة واكتساب رؤى تنبؤية وبناء وكلاء أذكياء.'],
                'problems_solved' => ['en' => ['Repetitive manual tasks', 'Slow customer support', 'Lack of predictive analysis', 'Inefficient resource allocation'], 'ar' => ['المهام اليدوية المتكررة', 'بطء دعم العملاء', 'نقص التحليل التنبؤي', 'تخصيص الموارد غير الفعال']],
                'capabilities' => ['en' => ['LLM Integrations', 'Chatbots & Virtual Assistants', 'Predictive Analytics', 'Workflow Automation'], 'ar' => ['تكامل النماذج اللغوية الكبيرة', 'روبوتات الدردشة', 'التحليلات التنبؤية', 'أتمتة سير العمل']],
                'process' => ['en' => ['1. Data Audit', '2. Model Selection', '3. AI Integration & Training', '4. Automation Deployment', '5. Monitoring'], 'ar' => ['1. تدقيق البيانات', '2. اختيار النموذج', '3. تكامل الذكاء الاصطناعي والتدريب', '4. نشر الأتمتة', '5. المراقبة']],
                'order' => 4,
                'is_published' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['order' => $service['order']], $service);
        }
    }
}
