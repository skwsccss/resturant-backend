<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\Category;
use App\Restaurant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Restaurant as RestaurantResource;
use Illuminate\Support\Facades\Storage;

class RestaurantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'perPage' => 'integer',
            'city' => 'integer',
        ]);

        $query = Restaurant::query();

        // if restaurant_name is selected
        if ($request->has('restaurant_name')) {
            $query = $query->where('name', 'LIKE', '%'.$request->restaurant_name.'%');
        }

        // If "is_open" filter is set
        if ($request->has('is_open')) {
            $query = $query->where('is_open', '=', $request->is_open);
        }

        // If city query is selected
        if ($request->has('city')) {
            $query = $query->whereHas('categories.city', function($q) use($request){
                $q->where('cities.id', '=', $request->city);
            });
        }

        // If category query is selected
        if ($request->has('category')) {
            $query = $query->whereHas('categories', function ($q) use($request) {
                $q->where('categories.id', '=', $request->category);
            });
        }

        // Order by 'order'
        $query = $query->orderBy('order');

        if ($request->has('page')) {
            $perPage = 5;
            if ($request->has('perPage')) {
                $perPage = $request->perPage;
            }
            return RestaurantResource::collection($query->paginate($perPage));
        } else {
            return RestaurantResource::collection($query->get());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'order' => 'integer|required'
        ]);

        // If file is set then validate file name and file type
        if ($request->filled('file')){
            $request->validate([
                'file_name' => 'required',
                'file_type'=> 'required'
            ]);
        }

        $image_url = null;
        // If file is set then validate file name and file type
        if ($request->has('file')) {

            $file = $request->file;
            if (preg_match('/^data:image\/(\w+);base64,/', $file)) {
                $data = substr($file, strpos($file, ',') + 1);
                $data = base64_decode($data);
                $file_type = $request->file_type;
                $extension = explode("/", $file_type)[1];
                $filename = $request->file_name;
                // Filename to store
                $fileNameToStore = $filename.'_'.time().'.'.$extension;
                // Upload Image
                Storage::disk('local')->put('public/restaurants/'.$fileNameToStore, $data);
                $image_url = Storage::url('public/restaurants/'.$fileNameToStore);
            }
        }

        $restaurant = Restaurant::create([
            'name' => $request->name,
            'image_url' => $image_url,
            'order' => $request->order,
            'is_open' => $request->is_open
        ]);

        if($request->has('category')) {
            $restaurant->categories()->attach($request->category);
        }

        return new RestaurantResource($restaurant);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Restaurant $restaurant)
    {
        return new RestaurantResource($restaurant);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'name' => 'required',
        ]);

        if ($request->filled('file')) {
            $image_url = null;
            $request->validate([
                'file_name' => 'required',
                'file_type' => 'required'
            ]);

            $file = $request->file;
            if (preg_match('/^data:image\/(\w+);base64,/', $file)) {
                $data = substr($file, strpos($file, ',') + 1);
                $data = base64_decode($data);
                $file_type = $request->file_type;
                $extension = explode("/", $file_type)[1];
                $filename = $request->file_name;
                // Filename to store
                $fileNameToStore = $filename.'_'.time().'.'.$extension;
                // Upload Image
                Storage::disk('local')->put('public/restaurants/'.$fileNameToStore, $data);
                $image_url = Storage::url('public/restaurants/'.$fileNameToStore);
            }
            $restaurant->update([
                'name' => $request->name,
                'image_url' => $image_url,
                'order' => $request->order,
                'is_open' => $request->is_open
            ]);
        } else {
            $restaurant->update([
                'name' => $request->name,
                'order' => $request->order,
                'is_open' => $request->is_open
            ]);
        }

        if ($request->has('category')) {
            $restaurant->categories()->sync($request->category);
        }

        return new RestaurantResource($restaurant);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Restaurant $restaurant)
    {
        $restaurant->categories()->detach();

        $restaurant->delete();
        return response()->json( null, 204);
    }
}
