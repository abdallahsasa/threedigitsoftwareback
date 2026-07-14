#!/bin/bash
cd database/migrations

declare -A order=(
    ["languages"]=10
    ["translations"]=11
    ["redirects"]=12
    ["seo_metadata"]=13
    ["pages"]=14
    ["page_sections"]=15
    ["navigation_items"]=16
    ["service_categories"]=17
    ["services"]=18
    ["faqs"]=19
    ["project_categories"]=20
    ["industries"]=21
    ["technologies"]=22
    ["projects"]=23
    ["project_media"]=24
    ["case_study_sections"]=25
    ["project_technology"]=26
    ["project_service"]=27
    ["project_industry"]=28
    ["testimonials"]=29
    ["team_members"]=30
    ["statistics"]=31
    ["offices"]=32
    ["social_links"]=33
    ["blog_categories"]=34
    ["tags"]=35
    ["blog_posts"]=36
    ["leads"]=37
    ["lead_attachments"]=38
    ["contact_messages"]=39
)

for file in *; do
    if [[ $file == *"_create_"* ]]; then
        table_name=$(echo $file | sed -E 's/^[0-9_]+_create_(.*)_table.php/\1/')
        if [[ -n "${order[$table_name]}" ]]; then
            new_name="2026_07_15_0000${order[$table_name]}_create_${table_name}_table.php"
            mv "$file" "$new_name"
        fi
    fi
done
