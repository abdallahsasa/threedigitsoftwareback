<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Service extends Model
{
    use HasTranslations;

    protected $guarded = [];
    public $translatable = ['name', 'slug', 'overview', 'problems_solved', 'capabilities', 'process'];

    //
}
