<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transaction';

    protected $fillable = [
        'product_id',
        'id_buyer',
        'status',
        'price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'id_buyer');
    }
}
