<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    protected $response;

    public function __construct(ResponseService $response)
    {
        $this->middleware(['auth:sanctum', 'user.type:customer']);
        $this->response = $response;
    }
    public function store(Request $request)
    {
        try {
            $request->validate([
                'beach_id' => 'required|integer|exists:beaches,id',
                // 'content_id' => 'required|integer|exists:contents,id',
                'message' => 'required|string',
            ]);
            $account  = Auth::user()->id;
            $comment  = new Comment();
            $comment->accout_id = $account;
            $comment->status = 1;
            $comment->message = $request->message;
            if ($request->has('content_id')) {
                $comment->content_id = $request->content_id;
            }
            $comment->beach_id = $request->beach_id;
            $comment->save();
            return $this->response->json(
                true,
                'Add comment success',
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
    public function update(Request $request, string $id)
    {
        $comment =  Comment::findOrFail($id);
        $account  = Auth::user()->id;
        if ($comment->accout_id != $account) {
            return $this->response->json(
                false,
                errors: 'No permission to edit comments',
                status: 403,
            );
        }
        try {
            $request->validate([
                'message' => 'sometimes|string',
            ]);
            if ($request->has('message')) {
                $comment->message = $request->message;
            }
            $comment->save();
            return $this->response->json(
                true,
                'update comment success',
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
    public function delete($id)
    {
        $comment =  Comment::findOrFail($id);
        $account  = Auth::user()->id;
        if ($comment->accout_id != $account) {
            return $this->response->json(
                false,
                errors: 'No permission to edit comments',
                status: 403,
            );
        }
        try {
            $comment->delete();
            return $this->response->json(
                true,
                'delete comment success',
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
