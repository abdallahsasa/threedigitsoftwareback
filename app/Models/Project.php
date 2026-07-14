<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Project extends Model
{
    use HasTranslations;

    protected $guarded = [];
    public $translatable = ['name', 'slug', 'overview', 'business_challenge', 'proposed_solution', 'engineering_challenges', 'results'];

    public function category()
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }
}
