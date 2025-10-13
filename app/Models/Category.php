<?php

namespace App\Models;

use App\Models\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = ['name', 'slug', 'image', 'status', 'show'];

    public function stores()
    {
        return $this->hasMany(Store::class);
    }
}
