<?php

namespace App\Models;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends Model
{
    use HasFactory, SoftDeletes;
    
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
        'subscription_expires_at',
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

    public function subscriptions()
    {
        return $this->hasMany(StoreSubscription::class);
    }
}
