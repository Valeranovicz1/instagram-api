<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller {
    use ApiResponse;

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'usuario' => 'required',
            'senha'   => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error(
                "Dados incompletos: usuário e senha são obrigatórios", 
                "DADOS_INCOMPLETOS", 
                400
            );
        }

        $credentials = [
            'usuario'  => $request->usuario,
            'password' => $request->senha 
        ];

        /** @var \Tymon\JWTAuth\JWTGuard $auth */
        $auth = auth('api');

        if (!$token = $auth->attempt($credentials)) {
            return $this->error(
                "Usuário ou senha inválidos", 
                "CREDENCIAIS_INVALIDAS", 
                401
            );
        }

        $user = auth('api')->user();
        
        return $this->success([
            'token' => $token,
            'usuario' => [
                'id'    => (string) $user->id,
                'nome'  => $user->nome,
                'email' => $user->email,
            ]
        ], "Login realizado com sucesso", "LOGIN_SUCESSO");
    }

    public function logout() {
    /** @var \Tymon\JWTAuth\JWTGuard $auth */
    $auth = auth('api');

    $auth->logout();
    
    return $this->success([], "Logout realizado com sucesso", "OPERACAO_SUCESSO");
}
}