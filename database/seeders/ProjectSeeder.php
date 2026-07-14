<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectCategory;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Define Categories
        $categories = [
            'Business Platforms',
            'Corporate Websites',
            'E-Commerce',
            'Mobile Applications',
            'Real Estate',
            'Hospitality'
        ];

        // Seed Categories and store map
        $catMap = [];
        foreach ($categories as $cat) {
            $catMap[$cat] = ProjectCategory::firstOrCreate(
                ['slug->en' => str()->slug($cat)],
                [
                    'name' => ['en' => $cat, 'ar' => $cat],
                    'slug' => ['en' => str()->slug($cat), 'ar' => str()->slug($cat)],
                ]
            )->id;
        }

        // Define Projects
        $projects = [
            [
                'name' => 'MEC Germany',
                'url' => 'https://mecgermany.de/en',
                'cat' => 'Corporate Websites',
                'summary' => 'Designed and developed a multilingual corporate website for an international medical engineering company, showcasing laboratory equipment, healthcare solutions, global offices, and technical product catalogs. The platform delivers a professional digital presence while supporting international customers through optimized performance, responsive design, and SEO-ready architecture.',
                'image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?q=80&w=2069&auto=format&fit=crop',
            ],
            [
                'name' => 'HerAntalya',
                'url' => 'https://herantalya.com/en',
                'cat' => 'Business Platforms',
                'summary' => 'A comprehensive city discovery platform connecting residents and tourists with local businesses, events, restaurants, attractions, and services. The platform includes advanced search, business listings, user accounts, multilingual support, and scalable architecture for future expansion.',
                'image' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?q=80&w=2070&auto=format&fit=crop',
            ],
            [
                'name' => 'Tenable FEC',
                'url' => 'https://tenablefec.com/',
                'cat' => 'Corporate Websites',
                'summary' => 'Developed a modern corporate website that strengthens the company\'s online presence with a clean design, responsive layouts, and optimized user experience. The platform focuses on professionalism, credibility, and easy access to company information.',
                'image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=2070&auto=format&fit=crop',
            ],
            [
                'name' => 'Grocero',
                'url' => 'http://groceroapp.com/',
                'cat' => 'Mobile Applications',
                'summary' => 'Designed and developed a modern grocery management application that simplifies shopping through intelligent lists, personalized organization, and cross-platform mobile experiences. Built with scalability and user convenience in mind.',
                'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?q=80&w=2070&auto=format&fit=crop',
            ],
            [
                'name' => 'OpticB2B',
                'url' => 'https://opticb2b.com/',
                'cat' => 'Business Platforms',
                'summary' => 'A global B2B marketplace connecting optical manufacturers, suppliers, and buyers through product listings, quotation requests, factory profiles, and multilingual support. Built to streamline international business communication and product discovery.',
                'image' => 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?q=80&w=2064&auto=format&fit=crop',
            ],
            [
                'name' => 'Cadeaux Corner',
                'url' => 'https://cadeauxcorner.com/',
                'cat' => 'E-Commerce',
                'summary' => 'Developed a modern e-commerce platform specializing in international gift delivery. The website provides a seamless shopping experience with secure ordering, responsive design, and an optimized customer journey.',
                'image' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?q=80&w=2070&auto=format&fit=crop',
            ],
            [
                'name' => 'Syria Gifts',
                'url' => 'https://syriagiftsfront.threedigitsoftware.com/',
                'cat' => 'E-Commerce',
                'summary' => 'Built a digital gifting platform that enables customers worldwide to send gifts to loved ones in Syria through an intuitive shopping experience and efficient order management.',
                'image' => 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?q=80&w=2040&auto=format&fit=crop',
            ],
            [
                'name' => 'FRS Perfumes',
                'url' => 'https://frsperfumes.com/',
                'cat' => 'E-Commerce',
                'summary' => 'Created a premium online perfume store with elegant branding, responsive shopping experience, and optimized product presentation designed to increase customer trust and conversions.',
                'image' => 'https://images.unsplash.com/photo-1594035910387-fea47794261f?q=80&w=2000&auto=format&fit=crop',
            ],
            [
                'name' => 'Cashoran',
                'url' => 'https://cashoran.com/',
                'cat' => 'Business Platforms',
                'summary' => 'Designed and developed a professional financial platform focused on delivering modern digital experiences with secure architecture, clean interfaces, and scalable infrastructure.',
                'image' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?q=80&w=2000&auto=format&fit=crop',
            ],
            [
                'name' => 'Sham Grill Restaurant',
                'url' => 'https://www.shamgrill.restaurant/',
                'cat' => 'Hospitality',
                'summary' => 'Developed an elegant restaurant website showcasing the menu, dining experience, location, and online customer engagement while reflecting the restaurant\'s premium brand identity.',
                'image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=2070&auto=format&fit=crop',
            ],
            [
                'name' => 'Ballutte',
                'url' => 'https://ballutte.com/',
                'cat' => 'E-Commerce',
                'summary' => 'Built a modern online shopping experience with a clean user interface, responsive product catalog, and optimized customer journey focused on improving online sales.',
                'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=2070&auto=format&fit=crop',
            ],
            [
                'name' => 'Deco Ceram',
                'url' => 'https://deco-ceram.fr/',
                'cat' => 'Corporate Websites',
                'summary' => 'Developed a professional multilingual website presenting ceramic and construction products through modern layouts, product showcases, and business-focused content.',
                'image' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?q=80&w=2069&auto=format&fit=crop',
            ],
            [
                'name' => 'MAF Green',
                'url' => 'https://mafgreen.com/',
                'cat' => 'Corporate Websites',
                'summary' => 'Created a modern digital presence for an agricultural company, highlighting products, services, and sustainable business solutions through a responsive and SEO-friendly website.',
                'image' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?q=80&w=2070&auto=format&fit=crop',
            ],
            [
                'name' => 'Wishes Beauty Clinic',
                'url' => 'https://wishesbeautyclinic.com/',
                'cat' => 'Corporate Websites',
                'summary' => 'Designed and developed a premium beauty clinic website focused on presenting medical services, treatment information, patient trust, and appointment inquiries through a clean and modern interface.',
                'image' => 'https://images.unsplash.com/photo-1512290923902-8a9f81dc236c?q=80&w=2070&auto=format&fit=crop',
            ],
            [
                'name' => 'United World Real Estate',
                'url' => 'https://uwestate.com/',
                'cat' => 'Real Estate',
                'summary' => 'Developed a modern real estate platform featuring property listings, multilingual support, advanced search, and an optimized browsing experience for buyers and investors.',
                'image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?q=80&w=2073&auto=format&fit=crop',
            ],
            [
                'name' => 'Shahed Helmek',
                'url' => 'https://shahedhelmek.com/',
                'cat' => 'Real Estate',
                'summary' => 'Built a responsive real estate platform designed to showcase residential and commercial properties with intuitive navigation, advanced search capabilities, and SEO-focused architecture.',
                'image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?q=80&w=2070&auto=format&fit=crop',
            ],
            [
                'name' => 'Wujha',
                'url' => 'https://wujha.me/',
                'cat' => 'Business Platforms',
                'summary' => 'Developed a complete event management platform that enables organizers to publish events, manage attendees, generate QR tickets, and provide seamless registration experiences for visitors.',
                'image' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?q=80&w=2070&auto=format&fit=crop',
            ],
            [
                'name' => 'HK Car Trimming',
                'url' => 'https://hkcartrimming.com/en',
                'cat' => 'Corporate Websites',
                'summary' => 'Designed and developed a premium automotive website showcasing interior customization services, upholstery expertise, and craftsmanship through a visually engaging and responsive digital experience.',
                'image' => 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?q=80&w=2183&auto=format&fit=crop',
            ],
        ];

        // Seed Projects
        $order = 0;
        foreach ($projects as $project) {
            Project::updateOrCreate(
                ['slug->en' => str()->slug($project['name'])],
                [
                    'name' => ['en' => $project['name'], 'ar' => $project['name']],
                    'project_category_id' => $catMap[$project['cat']],
                    'status' => 'published',
                    'is_featured' => true,
                    'order' => $order++,
                    'live_url' => $project['url'],
                    'overview' => ['en' => $project['summary'], 'ar' => $project['summary']],
                    'main_image' => $project['image'],
                ]
            );
        }
    }
}
