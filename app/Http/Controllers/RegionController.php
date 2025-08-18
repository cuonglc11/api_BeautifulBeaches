<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Regions;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use function Laravel\Prompts\error;

class RegionController extends Controller
{
    protected $response;
    public function __construct(ResponseService $response)
    {
        $this->middleware('auth:sanctum');
        $this->response = $response;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            return $this->response->json(true, data: Regions::all(), status: 200);
        } catch (\Throwable $th) {
            return $this->response->json(false, errors: $th->getMessage(), status: 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        try {
            $request->validate([
                'name' => 'required|string|unique:regions,name'
            ]);
        } catch (ValidationException $th) {
            return $this->response->json(
                false,
                errors: $th->errors(),
                status: 422,
            );
        }
        $region = new Regions();
        $region->name  = $request->name;
        $region->save();
        return $this->response->json(
            true,
            'Add region success',
            status : 200
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            return $this->response->json(true, data:  Regions::findOrFail($id), status: 200);
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
                'name' => 'required|string|unique:regions,name,'.$region->id
            ]);
        } catch (ValidationException $th) {
            return $this->response->json(
                false,
                errors: $th->errors(),
                status: 422,
            );
        }
        $region->name  = $request->name;
        $region->save();
        return $this->response->json(
            true,
            'Update region success',
            status: 200
        );

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