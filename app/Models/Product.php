<?php

namespace App\Models;

use App\Models\Store;
use App\Models\Images;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'store_id', 
        'title', 
        'slug', 
        'description', 
        'short_description', 
        'shipping_returns',
        'related_products', 
        'price', 
        'compare_price', 
        'image', 
        'category_id', 
        'subcategory_id',
        'is_featured', 
        'qty', 
        'track_qty', 
        'status'
    ];

    public function images()
    {
        return $this->hasMany(Images::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
