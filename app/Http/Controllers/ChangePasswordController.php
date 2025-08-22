<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;

class ChangePasswordController extends Controller
{
    protected $response;
    public function __construct(ResponseService $response)
    {
        $this->response = $response;
    }
    public function sentOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:accounts,email',
            ]);
            $otp = rand(100000, 999999);
            Cache::put('otp_' . $otp , $request->email , now()->addMinutes(5));
            Mail::to($request->email)->send(new SendOtpMail($otp));
            return $this->response->json(
                true,
                message: 'OTP sent to email',
                status: 200,
            );
        } catch (ValidationException $th) {
            return $this->response->json(
                false,
                errors: $th->errors(),
                status: 422,
            );
        }
    }
    public function changePass(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|min:6',
                'otp' => 'required|numeric',
            ]);
            $email = Cache::get('otp_' . $request->otp);
            if (!$email) {
                return $this->response->json(
                    false,
                    errors: 'Invalid or expired OTP',
                    status: 400,
                );
            }
            $account  = Account::where('email',  $email)->first();
            $account->password = Hash::make($request->password);
            $account->save();
            Cache::forget('otp_' . $request->otp);
            return $this->response->json(
                true,
                message: 'Password changed successfully',
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
