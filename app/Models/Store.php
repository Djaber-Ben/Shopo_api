<?php

namespace App\Models;

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
        'Instagram', 
        'tiktok'
    ];
}
