<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller {
    use ApiResponse;

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'nome'    => 'required',
            'email'   => 'required|email|unique:users,email',
            'usuario' => 'required|unique:users,usuario',
            'senha'   => 'required|min:3',
        ]);

        if ($validator->fails()) {
            $erros = $validator->errors();
            if ($erros->has('email') && str_contains($erros->first('email'), 'taken') || 
                $erros->has('usuario') && str_contains($erros->first('usuario'), 'taken')) {
                return $this->error("Usuário ou e-mail já cadastrado no sistema", "USUARIO_EXISTENTE", 409);
            }
            return $this->error("Dados inválidos ou em formato incorreto", "DADOS_INVALIDOS", 405);
        }

        if (strtolower($request->usuario) === 'admin') {
            return $this->error("Não é permitido criar uma conta com o nome de usuário 'admin'", "USUARIO_RESERVADO", 403);
        }

        User::create([
            'nome'     => $request->nome,
            'email'    => $request->email,
            'usuario'  => $request->usuario,
            'password' => bcrypt($request->senha), 
        ]);

        return $this->success(null, "Usuário cadastrado com sucesso", "USUARIO_CRIADO", 201);
    }

    public function index(Request $request) {
        $usuarioLogado = auth('api')->user();

        $todosUsuarios = User::all();

        if ($todosUsuarios->isEmpty()) {
            return $this->error("Nenhum usuário encontrado", "LISTAGEM_VAZIA", 404);
        }

        $listaFormatada = $todosUsuarios->map(function ($user) {
            return [
                'id'      => (string) $user->id,
                'nome'    => $user->nome,
                'email'   => $user->email,
                'usuario' => $user->usuario,
            ];
        });

        return $this->success([
            'usuarios' => $listaFormatada
        ], "Usuários listados com sucesso", "LISTAGEM_SUCESSO");
    }

    public function update(Request $request, $id) {
        $userAlvo = User::find($id);

        if (!$userAlvo) {
            return $this->error("Usuário não encontrado", "USUARIO_NAO_ENCONTRADO", 404);
        }

        $usuarioLogado = auth('api')->user();

        if ($usuarioLogado->usuario !== 'admin' && $usuarioLogado->id != $id) {
            return $this->error("Você não tem permissão para alterar dados de outro usuário", "ACESSO_NEGADO", 403);
        }

        if ($userAlvo->usuario === 'admin' && $request->has('usuario') && $request->usuario !== 'admin') {
            return $this->error("O username da conta 'admin' não pode ser alterado", "OPERACAO_BLOQUEADA", 403);
        }

        if ($request->has('usuario') && strtolower($request->usuario) === 'admin' && $userAlvo->usuario !== 'admin') {
            return $this->error("Você não pode alterar seu username para 'admin'", "OPERACAO_BLOQUEADA", 403);
        }

        $validator = Validator::make($request->all(), [
            'nome'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $id,
            'usuario'   => 'sometimes|unique:users,usuario,' . $id,
            'biografia' => 'sometimes|max:150',
            'senha'     => 'sometimes|min:3'
        ]);

        if ($validator->fails()) {
            return $this->error("Dados inválidos ou em formato incorreto", "DADOS_INVALIDOS", 400);
        }

        $dadosParaAtualizar = $request->only(['nome', 'email', 'usuario', 'biografia']);
        
        if ($request->has('senha')) {
            $dadosParaAtualizar['password'] = bcrypt($request->senha);
        }

        $userAlvo->update($dadosParaAtualizar);

        return $this->success([
            'id'      => (string) $userAlvo->id,
            'nome'    => $userAlvo->nome,
            'email'   => $userAlvo->email,
            'usuario' => $userAlvo->usuario
        ], "Usuário atualizado com sucesso", "USUARIO_ATUALIZADO");
    }

    public function show($id) {
        $user = User::find($id);

        if (!$user) {
            return $this->error("Usuário não encontrado", "USUARIO_NAO_ENCONTRADO", 404);
        }

        $usuarioLogado = auth('api')->user();

        if ($usuarioLogado->usuario !== 'admin' && $usuarioLogado->id != $id) {
            return $this->error("Você não tem permissão para acessar estes dados", "ACESSO_NEGADO", 403);
        }

        return $this->success([
            'id'        => (string) $user->id,
            'nome'      => $user->nome,
            'email'     => $user->email,
            'usuario'   => $user->usuario,
            'biografia' => $user->biografia
        ], "Dados do usuário recuperados", "USUARIO_ENCONTRADO");
    }

    public function destroy($id) {
        $userAlvo = User::find($id);

        if (!$userAlvo) {
            return $this->error("Usuário não encontrado para exclusão", "USUARIO_NAO_ENCONTRADO", 404);
        }

        $usuarioLogado = auth('api')->user();

        if ($usuarioLogado->usuario !== 'admin' && $usuarioLogado->id != $id) {
            return $this->error("Você não tem permissão para deletar este usuário", "ACESSO_NEGADO", 403);
        }

        if ($userAlvo->usuario === 'admin') {
            return $this->error("A conta administrador original não pode ser deletada", "OPERACAO_BLOQUEADA", 403);
        }

        $userAlvo->delete();

        return $this->success(new \stdClass(), "Operação realizada com sucesso", "OPERACAO_SUCESSO");
    }
}