<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Favorites;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;


class FavoritesController extends Controller
{
    protected $response;

    public function __construct(ResponseService $response)
    {
        $this->middleware(['auth:sanctum', 'user.type:customer']);
        $this->response = $response;
    }
    public function index()
    {
        $account  = Auth::user()->id;
        try {
            return $this->response->json(true, data: Favorites::with('beaches')->where('accout_id',  $account)->get(), status: 200);
        } catch (\Throwable $th) {
            return $this->response->json(false, errors: $th->getMessage(), status: 500);
        }
    }
    public function store(Request $request)
    {
        $account  = Auth::user()->id;

        try {
            $request->validate([
                'beach_id' => 'required|integer|exists:beaches,id',
            ]);
            $favorite = new Favorites();
            $favorite->accout_id = $account;
            $favorite->beach_id = $request->beach_id;
            $favorite->save();
            return $this->response->json(
                true,
                'Add favorite success',
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
}
