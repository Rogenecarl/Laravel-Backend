<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Categories extends Model
{
    /** @use HasFactory<\Database\Factories\CategoriesFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'is_active',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::creating(function (Categories $category) {
            $category->slug = Str::slug($category->name, '-');
        });

        static::updating(function (Categories $category) {
            if ($category->isDirty('name')) {
                $category->slug = Str::slug($category->name, '-');
            }
        });
    }
}

