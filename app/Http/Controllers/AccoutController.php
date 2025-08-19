<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use App\Services\ResponseService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;


class AccoutController extends Controller
{
    protected $response;
    public function __construct(ResponseService $response)
    {
        $this->response = $response;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            return $this->response->json(true, data: Account::all(), status: 200);
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
                'full_name' => 'required|string',
                'email' => 'required|string|unique:accounts,email',
                'username' => 'required|string|unique:accounts,username',
                'phone' => 'required|string|unique:accounts,phone',
                'password' => 'required|string|min:6',
                'birthday'  => 'required|date|before:today',
            ]);
            $account = new Account();
            $account->full_name = $request->full_name;
            $account->email = $request->email;
            $account->username = $request->username;
            $account->phone = $request->phone;
            $account->birthday = $request->birthday;
            $account->password = Hash::make($request->password);
            $account->save();
            return $this->response->json(
                true,
                'Add account success',
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
            return $this->response->json(true, data: Account::find($id), status: 200);
        } catch (\Throwable $th) {
            return $this->response->json(false, errors: $th->getMessage(), status: 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
