<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product';

    protected $fillable = [
        'id_category',
        'id_seller',
        'active',
        'already_selled',
        'identifier',
        'title',
        'author',
        'brief_desc',
        'person_desc',
        'price'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'id_category');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'id_seller');
    }
}
