<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Regions;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;



class RegionController extends Controller
{
    protected $response;
    public function __construct(ResponseService $response)
    {
        $this->middleware(['auth:sanctum', 'user.type:admin']);
        $this->response = $response;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search  = $request->query("search");
        try {
            return $this->response->json(true, data: !$search ? Regions::all() : Regions::where('name', 'LIKE', '%' . $search . '%')->get() , status: 200);
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
                'name' => 'required|string|unique:regions,name'
            ]);
            $region = new Regions();
            $region->name  = $request->name;
            $region->save();
            return $this->response->json(
                true,
                'Add region success',
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
            return $this->response->json(true, data: Regions::findOrFail($id), status: 200);
        } catch (\Throwable $th) {
            return $this->response->json(false, errors: $th->getMessage(), status: 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $region =  Regions::findOrFail($id);
        try {
            $request->validate([
                'name' => 'required|string|unique:regions,name,' . $region->id
            ]);
            $region->name  = $request->name;
            $region->save();
            return $this->response->json(
                true,
                'Update region success',
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
        $region =  Regions::findOrFail($id);
        try {
            $region->delete();
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
