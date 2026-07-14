import os
import re

migrations_dir = 'database/migrations'

schemas = {
    'create_project_technology_table.php': """
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technology_id')->constrained()->cascadeOnDelete();
            $table->primary(['project_id', 'technology_id']);
""",
    'create_project_service_table.php': """
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->primary(['project_id', 'service_id']);
""",
    'create_project_industry_table.php': """
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('industry_id')->constrained()->cascadeOnDelete();
            $table->primary(['project_id', 'industry_id']);
""",
}

for filename in os.listdir(migrations_dir):
    for key, schema in schemas.items():
        if filename.endswith(key):
            filepath = os.path.join(migrations_dir, filename)
            with open(filepath, 'r') as f:
                content = f.read()
            
            pattern = r"Schema::create\('.*?', function \(Blueprint \$table\) \{(.*?)\}\);"
            new_inner = f"\n{schema}        "
            new_content = re.sub(pattern, lambda m: m.group(0).replace(m.group(1), new_inner), content, flags=re.DOTALL)
            
            with open(filepath, 'w') as f:
                f.write(new_content)
            print(f"Updated {filename}")
