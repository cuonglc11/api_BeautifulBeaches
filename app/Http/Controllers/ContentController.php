<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contents;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContentController extends Controller
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
    public function index()
    {
        try {
            return $this->response->json(true, data: Contents::with('beaches')->get(), status: 200);
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
                'title' => 'required | string',
                'body' => 'required | string',
                'beach_id' => 'required|integer|exists:beaches,id',
            ]);
            $content  = new Contents();
            $content->title = $request->title;
            $content->body = $request->body;
            $content->beach_id = $request->beach_id;
            $content->save();
            return $this->response->json(
                true,
                'Add content success',
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
            return $this->response->json(true, data: Contents::with('beaches')->find($id), status: 200);
        } catch (\Throwable $th) {
            return $this->response->json(false, errors: $th->getMessage(), status: 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $content =  Contents::findOrFail($id);

        try {
            $request->validate([
                'title' => 'sometimes | string',
                'body' => 'sometimes | string',
                'beach_id' => 'sometimes|integer|exists:beaches,id',
            ]);
            if ($request->has('title')) {
                $content->title = $request->title;
            }
            if ($request->has('body')) {
                $content->body = $request->body;
            }
            if ($request->has('beach_id')) {
                $content->beach_id = $request->beach_id;
            }
            $content->save();
            return $this->response->json(
                true,
                'Update content success',
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
        $content =  Contents::findOrFail($id);
        try {
            $content->delete();
            return $this->response->json(
                true,
                'Delete content success',
                status: 200
            );
        } catch (\Throwable $th) {
            return $this->response->json(false, errors: $th->getMessage(), status: 500);
        }
    }
}
