<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['city_id', 'name', 'image_url', 'order', 'is_open'];

    public function city() {
        return $this->belongsTo(City::class);
    }

    public function restaurants() {
        return $this->belongsToMany(Restaurant::class);
    }
}
