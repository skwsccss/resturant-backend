<?php

namespace App\Http\Controllers\API;

use App\City;
use App\Http\Resources\City as CityResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'perPage' => 'integer'
        ]);

        $query = City::query();

        // If city_name search is set
        if ($request->has('city_name')) {
            $query = $query->where('name', 'LIKE', '%'.$request->city_name.'%');
        }

        // If "is_open" filter is set
        if ($request->has('is_open')) {
            $query = $query->where('is_open', '=', $request->is_open);
        }

        // Order by 'order' field
        $query = $query->orderBy('order');

        if ($request->has('page')) {
            $perPage = 5;
            if ($request->has('perPage')) {
                $perPage = $request->perPage;
            }
            return CityResource::collection($query->paginate($perPage));
        } else {
            return CityResource::collection($query->get());
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
        if($request->has('file')) {
            // Get file name with extensions

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
                Storage::disk('local')->put('public/cities/'.$fileNameToStore, $data);
                $image_url = Storage::url('public/cities/'.$fileNameToStore);
            }
        }

        $city = City::create([
            'name' => $request->name,
            'image_url' => $image_url,
            'order' => $request->order,
            'is_open' => $request->is_open
        ]);

        return new CityResource($city);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(City $city)
    {
        return new CityResource($city);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, City $city)
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

        if($request->filled('file')) {
            $image_url = null;
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
                Storage::disk('local')->put('public/cities/'.$fileNameToStore, $data);
                $image_url = Storage::url('public/cities/'.$fileNameToStore);
            }
            $city->update([
                'name' => $request->name,
                'image_url' => $image_url,
                'order' => $request->order,
                'is_open' => $request->is_open
            ]);
        } else {
            $city->update([
                'name' => $request->name,
                'order' => $request->order,
                'is_open' => $request->is_open
            ]);
        }

        return new CityResource($city);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(City $city)
    {
        $city->delete();

        return response()->json(null, 204);
    }

    public function insertMany(Request $request) {
        $data = $request->data;
        City::insert($data);
        return response()->json("City data array inserted successfully!", 200);
    }
}
