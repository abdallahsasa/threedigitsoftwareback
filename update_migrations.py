import os
import re

migrations_dir = 'database/migrations'

schemas = {
    'create_languages_table.php': """
            $table->string('code')->unique();
            $table->string('name');
            $table->string('native_name')->nullable();
            $table->boolean('is_rtl')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
""",
    'create_translations_table.php': """
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('group')->default('*');
            $table->string('key');
            $table->text('value')->nullable();
""",
    'create_redirects_table.php': """
            $table->string('from_url')->unique();
            $table->string('to_url');
            $table->integer('status_code')->default(301);
            $table->boolean('is_active')->default(true);
""",
    'create_seo_metadata_table.php': """
            $table->morphs('model');
            $table->json('title')->nullable();
            $table->json('description')->nullable();
            $table->json('keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->boolean('noindex')->default(false);
            $table->string('og_image')->nullable();
""",
    'create_navigation_items_table.php': """
            $table->string('name');
            $table->string('url')->nullable();
            $table->string('route')->nullable();
            $table->integer('order')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('navigation_items')->nullOnDelete();
            $table->boolean('is_active')->default(true);
""",
    'create_pages_table.php': """
            $table->json('title');
            $table->json('slug');
            $table->boolean('is_published')->default(false);
""",
    'create_page_sections_table.php': """
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->json('content')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
""",
    'create_service_categories_table.php': """
            $table->json('name');
            $table->json('slug');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
""",
    'create_services_table.php': """
            $table->foreignId('service_category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('name');
            $table->json('slug');
            $table->json('overview')->nullable();
            $table->json('problems_solved')->nullable();
            $table->json('capabilities')->nullable();
            $table->json('process')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_published')->default(false);
""",
    'create_faqs_table.php': """
            $table->morphs('model'); // can belong to Service, Page, etc.
            $table->json('question');
            $table->json('answer');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
""",
    'create_project_categories_table.php': """
            $table->json('name');
            $table->json('slug');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
""",
    'create_industries_table.php': """
            $table->json('name');
            $table->json('slug');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
""",
    'create_technologies_table.php': """
            $table->string('name');
            $table->string('category')->nullable(); // frontend, backend, etc.
            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
""",
    'create_projects_table.php': """
            $table->json('name');
            $table->json('slug');
            $table->string('client_name')->nullable();
            $table->foreignId('project_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('industry_id')->nullable()->constrained()->nullOnDelete();
            $table->string('launch_year')->nullable();
            $table->string('live_url')->nullable();
            $table->json('overview')->nullable();
            $table->json('business_challenge')->nullable();
            $table->json('proposed_solution')->nullable();
            $table->json('engineering_challenges')->nullable();
            $table->json('results')->nullable();
            $table->string('main_image')->nullable();
            $table->string('desktop_mockup')->nullable();
            $table->string('mobile_mockup')->nullable();
            $table->string('preview_video')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status')->default('draft'); // draft, published, archived
            $table->integer('order')->default(0);
""",
    'create_project_media_table.php': """
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('type')->default('image'); // image, video
            $table->string('device')->default('desktop'); // desktop, mobile, generic
            $table->integer('order')->default(0);
""",
    'create_case_study_sections_table.php': """
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->json('content')->nullable();
            $table->string('image')->nullable();
            $table->integer('order')->default(0);
""",
    'create_testimonials_table.php': """
            $table->json('client_name');
            $table->json('company');
            $table->json('position')->nullable();
            $table->json('quote');
            $table->string('photo')->nullable();
            $table->string('company_logo')->nullable();
            $table->integer('rating')->default(5);
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('language')->nullable();
            $table->string('status')->default('draft');
""",
    'create_team_members_table.php': """
            $table->json('name');
            $table->json('position');
            $table->string('photo')->nullable();
            $table->string('status')->default('draft');
            $table->integer('order')->default(0);
""",
    'create_statistics_table.php': """
            $table->json('label');
            $table->json('value');
            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
""",
    'create_offices_table.php': """
            $table->json('name');
            $table->json('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('map_url')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
""",
    'create_social_links_table.php': """
            $table->string('platform');
            $table->string('url');
            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
""",
    'create_blog_categories_table.php': """
            $table->json('name');
            $table->json('slug');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
""",
    'create_tags_table.php': """
            $table->json('name');
            $table->json('slug');
            $table->boolean('is_active')->default(true);
""",
    'create_blog_posts_table.php': """
            $table->json('title');
            $table->json('slug');
            $table->json('excerpt')->nullable();
            $table->json('content')->nullable();
            $table->foreignId('blog_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('featured_image')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
""",
    'create_leads_table.php': """
            $table->string('full_name');
            $table->string('company_name')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->string('project_type')->nullable();
            $table->text('project_description')->nullable();
            $table->string('existing_website')->nullable();
            $table->text('required_features')->nullable();
            $table->string('preferred_launch_date')->nullable();
            $table->string('budget')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('referral_source')->nullable();
            $table->string('landing_page')->nullable();
            $table->string('selected_language')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->boolean('privacy_consent')->default(false);
""",
    'create_lead_attachments_table.php': """
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
""",
    'create_contact_messages_table.php': """
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status')->default('new'); // new, read, replied
""",
}

for filename in os.listdir(migrations_dir):
    for key, schema in schemas.items():
        if filename.endswith(key):
            filepath = os.path.join(migrations_dir, filename)
            with open(filepath, 'r') as f:
                content = f.read()
            
            # Replace the inside of Schema::create
            pattern = r"Schema::create\('.*?', function \(Blueprint \$table\) \{(.*?)\}\);"
            
            # Keep id and timestamps
            new_inner = f"\n            $table->id();{schema}            $table->timestamps();\n        "
            
            new_content = re.sub(pattern, lambda m: m.group(0).replace(m.group(1), new_inner), content, flags=re.DOTALL)
            
            with open(filepath, 'w') as f:
                f.write(new_content)
            print(f"Updated {filename}")

