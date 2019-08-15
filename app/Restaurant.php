<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $fillable = ['name', 'image_url', 'order', 'is_open'];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public static function boot() {
        parent::boot();

        static::deleting(function($restaurant) {
            $restaurant->menus()->delete();
        });
    }
}
