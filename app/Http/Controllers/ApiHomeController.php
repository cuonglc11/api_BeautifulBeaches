<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Beaches;
use App\Models\Regions;
use Illuminate\Http\Request;
use App\Services\ResponseService;

class ApiHomeController extends Controller
{
    protected $response;
    protected $email;
    public function __construct(ResponseService $response)
    {
        $this->response = $response;
    }
    public function listBeaches(Request $request)
    {

        try {
            if ($request->query('keyword')) {
                $keyword = $request->query('keyword');
                $beaches = Beaches::with(['images', 'region'])
                    ->where(function ($query) use ($keyword) {
                        $query->where('name', 'like', "%{$keyword}%")
                            ->orWhere('location', 'like', "%{$keyword}%");
                    })
                    ->get();
                return $this->response->json(true, data: $beaches, status: 200);
            }
            if ($request->query('region')) {
                return $this->response->json(true, data: Beaches::with(['images', 'region'])->where('region_id', $request->query('region'))->get(), status: 200);
            }
            if ($request->query('region') && $request->query('keyword')) {
                $keyword = $request->query('keyword');
                $beaches = Beaches::with(['images', 'region'])
                    ->where(function ($query) use ($keyword) {
                        $query->where('name', 'like', "%{$keyword}%")
                            ->orWhere('location', 'like', "%{$keyword}%");
                    })->where('region_id', $request->query('region'))
                    ->get();
                return $this->response->json(true, data: $beaches, status: 200);
            }
            return $this->response->json(true, data: Beaches::with(['images', 'region'])->get(), status: 200);
        } catch (\Throwable $th) {
            return $this->response->json(false, errors: $th->getMessage(), status: 500);
        }
    }
    public function region()
    {
        try {
            return $this->response->json(true, data: Regions::all(), status: 200);
        } catch (\Throwable $th) {
            return $this->response->json(false, errors: $th->getMessage(), status: 500);
        }
    }
    public function show(Request $request)
    {
        $id = $request->query('id');
        try {
            return $this->response->json(true, data: Beaches::with(['images', 'region'])->find($id), status: 200);
        } catch (\Throwable $th) {
            return $this->response->json(false, errors: $th->getMessage(), status: 500);
        }
    }
}