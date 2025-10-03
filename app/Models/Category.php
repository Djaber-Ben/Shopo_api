<?php

namespace App\Models;

use App\Models\Store;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'image', 'status', 'show'];

    public function stores()
    {
        return $this->hasMany(Store::class);
    }
}
