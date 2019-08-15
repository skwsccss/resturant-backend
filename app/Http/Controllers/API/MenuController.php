<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Menu;
use App\Http\Resources\Menu as MenuResource;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
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
            'restaurant' => 'integer',
        ]);

        $query = Menu::query();

        // If 'restaurant' search required
        if ($request->has('restaurant')) {
            $query = $query->whereHas('restaurant', function($q) use($request) {
                $q->where('id', '=', $request->restaurant);
            });
        }

        // if 'menu name' search is set
        if ($request->has('menu_name') && $request->has('item_name')) {
            $query = $query->where(function ($rquery) use ($request) {
                $rquery = $rquery->orWhere('name', 'LIKE', '%'.$request->menu_name.'%');
                $rquery = $rquery->orWhereHas('items', function($q) use ($request) {
                    $q->where('items.name', 'LIKE', '%'.$request->item_name.'%');
                });
            });

        } else if ($request->has('menu_name')) {
            $query = $query->where('name', 'LIKE', '%'.$request->menu_name.'%');
        }

        // Order by 'order'
        $query = $query->orderBy('order');

        if ($request->has('page')) {
            $perPage = 5;
            if ($request->has('perPage')) {
                $perPage = $request->perPage;
            }
            return MenuResource::collection($query->paginate($perPage));
        } else {
            return MenuResource::collection($query->get());
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
            'restaurant_id' => 'required',
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
                Storage::disk('local')->put('public/menus/'.$fileNameToStore, $data);
                $image_url = Storage::url('public/menus/'.$fileNameToStore);
            }
        }

        $menu = Menu::create([
            'name' => $request->name,
            'restaurant_id' => $request->restaurant_id,
            'order' => $request->order,
            'image_url' => $image_url
        ]);

        return new MenuResource($menu);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Menu $menu)
    {
        return new MenuResource($menu);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'name' => 'required',
            'restaurant_id' => 'required',
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
            $menu->update([
                'name' => $request->name,
                'restaurant_id' => $request->restaurant_id,
                'image_url' => $image_url,
                'order' => $request->order
            ]);
        } else {
            $menu->update([
                'name' => $request->name,
                'restaurant_id' => $request->restaurant_id,
                'order' => $request->order
            ]);
        }

        return new MenuResource($menu);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Menu $menu)
    {
        $menu->delete();

        return response()->json(null, 204);
    }
}
