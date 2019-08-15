<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Restaurant as RestaurantResource;
use App\Restaurant;
use App\Http\Resources\Item as ItemResource;
use App\Item;

class Menu extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'order' => $this->order,
            'restaurant' => new RestaurantResource(Restaurant::find($this->restaurant_id)),
            'items' => $this->items,
            'image_url' => $this->image_url,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
