<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Interfaces\AuthInterface;
use App\Models\User;
use App\Responses\ApiResponse;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    private  AuthInterface $authInterface;

    public function __construct(AuthInterface $authInterface)
    {
        $this->authInterface = $authInterface;
    }

    public function register(RegisterRequest $registerRequest)
    {
        $data = [
            'name' => $registerRequest->name,
            'email' => $registerRequest->email,
            'password' => $registerRequest->password,
        ];

        DB::beginTransaction();

        try {
            $user =  $this->authInterface->register($data);
            DB::commit();

            return ApiResponse::sendResponse(
                true,
                [new UserResource($user)],
                'Operation effectue',
                201
            );
        } catch (\Throwable $th) {
            return ApiResponse::rollback($th);
        }
    }

    public function login(LoginRequest $loginRequest)
    {
        $data = [
            'email' => $loginRequest->email,
            'password' => $loginRequest->password,
        ];

        DB::beginTransaction();
        try {
            $user = $this->authInterface->login($data);

            DB::commit();

            if (!$user){
                return ApiResponse::sendResponse(
                    $user,
                    [],
                    'Identifiant invalide.',
                    200
                );
            }

            return ApiResponse::sendResponse(
                $user,
                [],
                'Opération effectuée.',
                200
            );
        } catch (\Throwable $th) {

            return ApiResponse::rollback($th);
        }
    }

    

    public function checkOtpCode(Request $request)
    {
        $data = [
            'email' => $request->email,
            'code' => $request->code,
        ];

        DB::beginTransaction();

        try {
            $user =  $this->authInterface->checkOtpCode($data);

            DB::commit();

            if (!$user) {
                return ApiResponse::sendResponse(
                    false,
                    [],
                    'Code de confirmation Invalide',
                    200
                );
            }

            return ApiResponse::sendResponse(
                true,
                [new UserResource($user)],
                'Operation effectue',
                201,
        
            );

        } catch (\Throwable $th) {
            return ApiResponse::rollback($th);
        }
    }

    public function logout()
    {
        $user = User::find(auth()->user()->getAuthIdentifier());
        $user->Tokens()->delete;

        return ApiResponse::sendResponse(
            true,
            [],
            'Utilisateur déconnecté.',
            200
        );
    }

}
