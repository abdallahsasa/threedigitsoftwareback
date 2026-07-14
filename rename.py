import os
import glob

migrations_dir = 'database/migrations'

order = {
    "languages": 10,
    "translations": 11,
    "redirects": 12,
    "seo_metadata": 13,
    "pages": 14,
    "page_sections": 15,
    "navigation_items": 16,
    "service_categories": 17,
    "services": 18,
    "faqs": 19,
    "project_categories": 20,
    "industries": 21,
    "technologies": 22,
    "projects": 23,
    "project_media": 24,
    "case_study_sections": 25,
    "project_technology": 26,
    "project_service": 27,
    "project_industry": 28,
    "testimonials": 29,
    "team_members": 30,
    "statistics": 31,
    "offices": 32,
    "social_links": 33,
    "blog_categories": 34,
    "tags": 35,
    "blog_posts": 36,
    "leads": 37,
    "lead_attachments": 38,
    "contact_messages": 39
}

for file in os.listdir(migrations_dir):
    if "_create_" in file and file.endswith(".php"):
        # e.g. 2026_07_15_000039_create_blog_categories_table.php
        parts = file.split("_create_")
        if len(parts) == 2:
            table_name = parts[1].replace("_table.php", "")
            if table_name in order:
                idx = order[table_name]
                new_name = f"2026_07_15_0000{idx}_create_{table_name}_table.php"
                os.rename(os.path.join(migrations_dir, file), os.path.join(migrations_dir, new_name))
