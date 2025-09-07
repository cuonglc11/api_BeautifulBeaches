<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Beaches;
use App\Models\Comment;
use App\Models\Favorites;
use App\Models\ImageBanner;
use App\Models\Regions;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Redis;

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
                $beaches = Beaches::with(['images', 'region'])->withCount([
                    'comments as comments_count' => function ($query) {
                        $query->where('status', 1);
                    },
                    'favorites'
                ])
                    ->where(function ($query) use ($keyword) {
                        $query->where('name', 'like', "%{$keyword}%")
                            ->orWhere('location', 'like', "%{$keyword}%");
                    })
                    ->get();
                return $this->response->json(true, data: $beaches, status: 200);
            }
            if ($request->query('region')) {
                $region  = Regions::find($request->query('region'))->name;
                return $this->response->json(true , $region, data: Beaches::with(['images', 'region'])->withCount([
                    'comments as comments_count' => function ($query) {
                        $query->where('status', 1);
                    },
                    'favorites'
                ])->where('region_id', $request->query('region'))->get(), status: 200);
            }
            if ($request->query('region') && $request->query('keyword')) {
                $keyword = $request->query('keyword');
                $beaches = Beaches::with(['images', 'region'])->withCount([
                    'comments as comments_count' => function ($query) {
                        $query->where('status', 1);
                    },
                    'favorites'
                ])
                    ->where(function ($query) use ($keyword) {
                        $query->where('name', 'like', "%{$keyword}%")
                            ->orWhere('location', 'like', "%{$keyword}%");
                    })->where('region_id', $request->query('region'))
                    ->get();
                return $this->response->json(true, data: $beaches, status: 200);
            }
            return $this->response->json(true, data: Beaches::with(['images', 'region'])->withCount([
                'comments as comments_count' => function ($query) {
                    $query->where('status', 1);
                },
                'favorites'
            ])->get(), status: 200);
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
    public function countFavorite(Request $request)
    {
        try {
            $id_beaches =  $request->query('id');
            $count = Favorites::where('beach_id', $id_beaches)->count();
            $data = ['count' => $count];
            return $this->response->json(true, data: $data, status: 200);
        } catch (\Throwable $th) {
            return $this->response->json(false, errors: $th->getMessage(), status: 500);
        }
    }
    public function listImageBanner(Request $request)
    {
        $type = $request->query('type');

        try {
            return $this->response->json(true, data: ImageBanner::where('type', $type)->get(), status: 200);
        } catch (\Throwable $th) {
            return $this->response->json(false, errors: $th->getMessage(), status: 500);
        }
    }
    public function listComment(Request $request)
    {
        $id = $request->query('id');
        if ($id) {
            try {
                return $this->response->json(true, data: Comment::with('account')->where('beach_id', $id)->where('status', 1)->orderBy('id', 'desc')->get(), status: 200);
            } catch (\Throwable $th) {
                return $this->response->json(false, errors: $th->getMessage(), status: 500);
            }
        }
        try {
            return $this->response->json(true, data: Comment::with('account')->where('status', 1)->orderBy('id', 'desc')->limit(3)->get(), status: 200);
        } catch (\Throwable $th) {
            return $this->response->json(false, errors: $th->getMessage(), status: 500);
        }
    }
    public function listBeachesRegion(Request $request)
    {
        $idBeache = $request->query('id');
        $region = Beaches::find($idBeache);
        // return $region;
        try {
            return $this->response->json(true, data: Beaches::with(['images', 'region'])
                ->where('region_id', $region->region_id)->where('id', '!=', $idBeache)
                ->orderBy('id', 'desc')->limit(3)->get(), status: 200);
        } catch (\Throwable $th) {
            return $this->response->json(false, errors: $th->getMessage(), status: 500);
        }
    }
}