<?php

namespace App\Http\Controllers\API;

use App\Category;
use App\Http\Resources\Category as CategoryResource;
use App\Http\Resources\City as CityResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
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

        $query = Category::query();

        // If category_name search is set
        if ($request->has('category_name')) {
            $query = $query->where('name', 'LIKE', '%'.$request->category_name.'%');
        }

        // If "is_open" filter is set
        if ($request->has('is_open')) {
            $query = $query->where('is_open', '=', $request->is_open);
        }

        // If city is set
        if ($request->has('city')) {
            $query = $query->whereHas('city', function ($q) use($request){
               $q->where('id', '=', $request->city);
            });
        }

        // If city_name is set
        if ($request->has('city_name')) {
            $query = $query->whereHas('city', function ($q) use($request){
               $q->where('name', 'LIKE', '%'.$request->city_name.'%');
            });
        }

        // Order by 'order'
        $query = $query->orderBy('order');

        if ($request->has('page')) {
            $perPage = 5;
            if ($request->has('perPage')) {
                $perPage = $request->perPage;
            }
            return CategoryResource::collection($query->paginate($perPage));
        } else {
            return CategoryResource::collection($query->get());
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
            'city_id' => 'required',
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
                Storage::disk('local')->put('public/categories/'.$fileNameToStore, $data);
                $image_url = Storage::url('public/categories/'.$fileNameToStore);
            }
        }

        $category = Category::create([
            'name' => $request->name,
            'city_id' => $request->city_id,
            'image_url' => $image_url,
            'order' => $request->order,
            'is_open' => $request->is_open
        ]);

        return new CategoryResource($category);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required',
            'city_id' => 'required',
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
                Storage::disk('local')->put('public/categories/'.$fileNameToStore, $data);
                $image_url = Storage::url('public/categories/'.$fileNameToStore);
            }
            $category->update([
                'name' => $request->name,
                'city_id' => $request->city_id,
                'image_url' => $image_url,
                'order' => $request->order,
                'is_open' => $request->is_open
            ]);
        } else {
            $category->update($request->only(['name', 'city_id', 'order', 'is_open']));
        }

        return new CategoryResource($category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        // Remove n to n relationship with restaurants
        $category->restaurants()->detach();

        $category->delete();

        return response()->json(null, 204);
    }
}
