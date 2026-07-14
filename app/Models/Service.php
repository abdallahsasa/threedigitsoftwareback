<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Service extends Model
{
    use HasTranslations;

    protected $guarded = [];
    public $translatable = ['name', 'slug', 'short_description', 'full_description', 'features'];

    //
}
