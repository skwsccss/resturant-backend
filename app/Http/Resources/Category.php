<?php

namespace App\Http\Resources;

use App\City;
use App\Http\Resources\City as CityResource;
use App\Http\Resources\Category as CategoryResource;
use Illuminate\Http\Resources\Json\JsonResource;

class Category extends JsonResource
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
            'city' => new CityResource(City::find($this->city_id)),
            'image_url' => $this->image_url,
            'is_open' => $this->is_open,
            'order' => $this->order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
