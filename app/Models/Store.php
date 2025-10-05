<?php

namespace App\Models;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'vendor_id', 
        'category_id', 
        'store_name', 
        'slug', 
        'description', 
        'logo', 
        'image', 
        'phone_number', 
        'address', 
        'latitude', 
        'longitude', 
        'status', 
        'subscription_timer',
        'whatsapp', 
        'facebook', 
        'instagram', 
        'tiktok'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
