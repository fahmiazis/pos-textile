<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'code',
        'name',
        'parent_id',
        'is_active',
    ];

    // parent category
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // child categories
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
