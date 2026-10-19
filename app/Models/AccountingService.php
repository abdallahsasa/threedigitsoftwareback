<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingService extends Model
{
    protected $table = 'accounting_services';

    protected $fillable = [
        'name_ar',
        'name_en',
        'category',
        'description_ar',
        'description_en',
        'unit',
        'default_price',
        'is_active',
    ];

    protected $casts = [
        'default_price' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public const CATEGORIES = [
        'website_development' => 'Website Development / تطوير المواقع الإلكترونية',
        'software_development' => 'Software Development / تطوير البرمجيات والأنظمة',
        'e_commerce' => 'E-Commerce / حلول التجارة والمتاجر الإلكترونية',
        'social_media' => 'Social Media Management / إدارة وسائل التواصل الاجتماعي',
        'design' => 'UI/UX & Branding Design / التصميم والهوية البصرية',
        'photography' => 'Photography & Production / التصوير والإنتاج الإعلامي',
        'hosting' => 'Hosting & Cloud / الاستضافة والسيرفرات السحابية',
        'maintenance' => 'Maintenance & Support / الصيانة والدعم الفني الدوري',
    ];

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? ($this->name_ar ?: $this->name_en) : ($this->name_en ?: $this->name_ar);
    }
}
