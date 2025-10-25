<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Images extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'product_id', 
        'image', 
        'is_primary'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
