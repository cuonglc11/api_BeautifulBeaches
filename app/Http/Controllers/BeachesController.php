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
    public function index(Request $request)
    {
        try {
            $search  = $request->query("search");
            $beaches = Beaches::with(['images', 'region'])
                ->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('location', 'LIKE', "%{$search}%")
                        ->orWhereHas('region', function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%{$search}%");
                        });
                })
                ->get();

            return $this->response->json(true, data: !$search ? Beaches::with(['images', 'region'])->get() : $beaches, status: 200);
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
                'latitude'    => 'required|numeric|between:-90,90',
                'longitude'   => 'required|numeric|between:-180,180',
                'region_id' => 'required|integer|exists:regions,id',
                'images'   => 'required|array |max:5|min:3',
                'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
            ]);
            $beache = new Beaches();
            $beache->name  = $request->name;
            $beache->description  = $request->description;
            $beache->location  = $request->location;
            $beache->latitude = $request->latitude;
            $beache->longitude = $request->longitude;
            $beache->region_id  = $request->region_id;
            $beache->save();
            $beache_id  = $beache->id;
            $dataImage = [];
            if ($request->hasFile('images')) {
                $files = $request->file('images');
                if (count($files) > 5) {
                    return $this->response->json(false, errors: 'Chỉ được phép upload tối đa 5 ảnh', status: 422);
                }
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
        $beache = Beaches::findOrFail($id);

        try {
            $request->validate([
                'name' => 'sometimes|string|unique:regions,name,' . $beache->id,
                'description' => 'sometimes|string',
                'location' => 'sometimes|string',
                'region_id' => 'sometimes|integer|exists:regions,id',
                'images'   => 'sometimes|array',
                'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
                'old_images' => 'sometimes|array',
                'old_images.*' => 'string',
                'latitude'    => 'sometimes|numeric|between:-90,90',
                'longitude'   => 'sometimes|numeric|between:-180,180',
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
            if($request->has('latitude')) {
                $beache->latitude = $request->latitude;
            }
            if ($request->has('longitude')) {
                $beache->longitude = $request->longitude;
            }
            $beache->save();

            $beache_id  = $beache->id;

            $oldImagesInDb = ImageBeaches::where('beach_id', $beache_id)->get();
            $keepImages = $request->old_images ?? [];
            foreach ($oldImagesInDb as $img) {
                if (!in_array($img->img_link, $keepImages)) {
                    $img->delete();
                }
            }
            if ($request->hasFile('images')) {
                $currentCount = ImageBeaches::where('beach_id', $beache_id)->count();
                $remainingSlots = 5 - $currentCount;
                if ($remainingSlots <= 0) {
                    return $this->response->json(
                        false,
                        'Không thể upload thêm ảnh. Tối đa 5 ảnh cho 1 beach.',
                        status: 400
                    );
                }

                $dataImage = [];
                foreach (array_slice($request->file('images'), 0, $remainingSlots) as $file) {
                    $path  = $this->imgSevice->upload($file, 'beache');
                    $dataImage[] = [
                        'img_link'   => $path,
                        'beach_id'   => $beache_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                ImageBeaches::insert($dataImage);
            }

            return $this->response->json(
                true,
                'Update beach success',
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
        $oldImages = ImageBeaches::where('beach_id', $beache->id)->get();

        try {
            ImageBeaches::where('beach_id', $id)->delete();
            $beache->delete();
            foreach ($oldImages as $old) {
                $this->imgSevice->delete($old->img_link);
                $old->delete();
            }
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
