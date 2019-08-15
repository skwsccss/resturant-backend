<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['name', 'restaurant_id', 'menu_id', 'image_url', 'order'];

    public function restaurant() {
        return $this->belongsTo(Restaurant::class);
    }

    public function items() {
        return $this->hasMany(Item::class);
    }

    public static function boot(){
        parent::boot();

        static::deleting(function ($menu) {
           $menu->items()->delete();
        });
    }
}
