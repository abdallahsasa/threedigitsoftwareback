import os

models_dir = 'app/Models'

translatable_fields = {
    'Project.php': ['name', 'slug', 'overview', 'content', 'features', 'challenge', 'solution', 'metadata'],
    'ProjectCategory.php': ['name', 'slug', 'description'],
    'Service.php': ['name', 'slug', 'short_description', 'full_description', 'features'],
    'ServiceCategory.php': ['name', 'slug', 'description'],
    'BlogPost.php': ['title', 'slug', 'excerpt', 'content'],
    'BlogCategory.php': ['name', 'slug', 'description'],
    'Testimonial.php': ['quote', 'client_title'],
    'Faq.php': ['question', 'answer'],
    'Page.php': ['title', 'slug', 'metadata'],
    'PageSection.php': ['title', 'subtitle', 'content'],
}

def update_model(file_path, translatable_array):
    with open(file_path, 'r') as f:
        content = f.read()

    # Add use Spatie statement if not there
    if 'Spatie\\Translatable\\HasTranslations' not in content:
        content = content.replace('use Illuminate\\Database\\Eloquent\\Model;', "use Illuminate\\Database\\Eloquent\\Model;\nuse Spatie\\Translatable\\HasTranslations;")
    
    # Add trait and arrays
    if 'use HasTranslations;' not in content:
        fields_str = ', '.join([f"'{f}'" for f in translatable_array])
        class_body = f"""
    use HasTranslations;

    protected $guarded = [];
    public $translatable = [{fields_str}];
"""
        # Find position right after class opening brace
        class_pos = content.find('{')
        content = content[:class_pos+1] + class_body + content[class_pos+1:]

    with open(file_path, 'w') as f:
        f.write(content)

for model, fields in translatable_fields.items():
    file_path = os.path.join(models_dir, model)
    if os.path.exists(file_path):
        update_model(file_path, fields)

# Also update remaining models to have $guarded = []
for file in os.listdir(models_dir):
    if file.endswith('.php') and file not in translatable_fields:
        file_path = os.path.join(models_dir, file)
        with open(file_path, 'r') as f:
            content = f.read()
        if 'protected $guarded = [];' not in content:
            class_pos = content.find('{')
            content = content[:class_pos+1] + "\n    protected $guarded = [];\n" + content[class_pos+1:]
            with open(file_path, 'w') as f:
                f.write(content)

