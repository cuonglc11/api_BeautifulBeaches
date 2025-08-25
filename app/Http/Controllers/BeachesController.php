<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Beaches;
use App\Models\ImageBeaches;
use App\Services\ImageService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BeachesController extends Controller
{
    protected $response;
    protected $imgSevice;

    public function __construct(ResponseService $response, ImageService $imgSevice)
    {
        $this->middleware(['auth:sanctum', 'user.type:admin']);
        $this->response = $response;
        $this->imgSevice = $imgSevice;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            return $this->response->json(true, data: Beaches::with(['images', 'region'])->get(), status: 200);
        } catch (\Throwable $th) {
            return $this->response->json(false, errors: $th->getMessage(), status: 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:regions,name',
                'description' => 'required|string',
                'location' => 'required|string',
                'region_id' => 'required|integer|exists:regions,id',
                'images'   => 'required|array',
                'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
            ]);
            $beache = new Beaches();
            $beache->name  = $request->name;
            $beache->description  = $request->description;
            $beache->location  = $request->location;
            $beache->region_id  = $request->region_id;
            $beache->save();
            $beache_id  = $beache->id;
            $dataImage = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path  = $this->imgSevice->upload($file, 'beache');
                    $dataImage[] = [
                        'img_link' => $path,
                        'beach_id'     => $beache_id,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
            }
            ImageBeaches::insert($dataImage);
            return $this->response->json(
                true,
                'Add beache success',
                status: 200
            );
        } catch (ValidationException $th) {
            return $this->response->json(
                false,
                errors: $th->errors(),
                status: 422,
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            return $this->response->json(true, data: Beaches::with(['images', 'region'])->find($id), status: 200);
        } catch (\Throwable $th) {
            return $this->response->json(false, errors: $th->getMessage(), status: 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $beache =  Beaches::findOrFail($id);
        try {
            $request->validate([
                'name' => 'sometimes|string|unique:regions,name,' . $beache->id,
                'description' => 'sometimes|string',
                'location' => 'sometimes|string',
                'region_id' => 'sometimes|integer|exists:regions,id',
                'images'   => 'sometimes|array',
                'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
            ]);
            if ($request->has('name')) {
                $beache->name = $request->name;
            }
            if ($request->has('description')) {
                $beache->description = $request->description;
            }
            if ($request->has('location')) {
                $beache->location = $request->location;
            }
            if ($request->has('region_id')) {
                $beache->region_id = $request->region_id;
            }
            $beache->save();
            $beache_id  = $beache->id;
            $dataImage = [];
            if ($request->hasFile('images')) {
                $oldImages = ImageBeaches::where('beach_id', $beache_id)->get();
                foreach ($request->file('images') as $file) {
                    $path  = $this->imgSevice->upload($file, 'beache');
                    $dataImage[] = [
                        'img_link' => $path,
                        'beach_id'     => $beache_id,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
                ImageBeaches::insert($dataImage);
            }
            return $this->response->json(
                true,
                'Update beache success',
                status: 200
            );
        } catch (ValidationException $th) {
            return $this->response->json(
                false,
                errors: $th->errors(),
                status: 422,
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $beache =  Beaches::findOrFail($id);

        try {
            ImageBeaches::where('beach_id', $id)->delete();
            $beache->delete();
            return $this->response->json(
                true,
                'Delete region success',
                status: 200
            );
        } catch (\Throwable $th) {
            return $this->response->json(false, errors: $th->getMessage(), status: 500);
        }
    }
}