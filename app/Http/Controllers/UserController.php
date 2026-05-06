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

            if ($erros->has('nome') || $erros->has('email') || $erros->has('usuario') || $erros->has('senha')) {
                if ($request->nome == null || $request->email == null || $request->usuario == null || $request->senha == null) {
                    return $this->error("Dados incompletos: verifique os campos obrigatórios", "DADOS_INCOMPLETOS", 400);
                }
            }

            return $this->error("Dados inválidos ou em formato incorreto", "DADOS_INVALIDOS", 405);
        }

        User::create([
            'nome'     => $request->nome,
            'email'    => $request->email,
            'usuario'  => $request->usuario,
            'password' => $request->senha,
        ]);

        return $this->success(null, "Usuário cadastrado com sucesso", "USUARIO_CRIADO", 201);
    }

    public function index(Request $request) {
        
        $limite = $request->query('limite', 10);
        $pagina = $request->query('pagina', 1);

        $usuariosPaginados = User::paginate($limite, ['*'], 'pagina', $pagina);

        if ($usuariosPaginados->isEmpty()) {
            return $this->error("Nenhum usuário encontrado", "LISTAGEM_VAZIA", 404);
        }

        $listaFormatada = collect($usuariosPaginados->items())->map(function ($user) {
            return [
                'id'      => (string) $user->id,
                'nome'    => $user->nome,
                'email'   => $user->email,
                'usuario' => $user->usuario,
            ];
        });

        return $this->success([
            'total'    => $usuariosPaginados->total(),
            'pagina'   => $usuariosPaginados->currentPage(),
            'limite'   => $usuariosPaginados->perPage(),
            'usuarios' => $listaFormatada
        ], "Usuários listados com sucesso", "LISTAGEM_SUCESSO");
    }

    public function update(Request $request, $id) {

        $user = User::find($id);

        if (!$user) {
            return $this->error("Usuário não encontrado", "USUARIO_NAO_ENCONTRADO", 404);
        }

        $validator = Validator::make($request->all(), [
            'nome'    => 'sometimes|string|max:255',
            'email'   => 'sometimes|email|unique:users,email,' . $id,
            'usuario' => 'sometimes|unique:users,usuario,' . $id,
            'biografia' => 'sometimes|max:150',
            'foto'    => 'sometimes',
            'senha'   => 'sometimes|min:3'
        ]);

        if ($validator->fails()) {
            return $this->error("Dados inválidos ou em formato incorreto", "DADOS_INVALIDOS", 400);
        }

        $dadosParaAtualizar = $request->all();

        if ($request->has('senha')) {
            $dadosParaAtualizar['password'] = $request->senha;
        }

        $user->fill($dadosParaAtualizar);
        $user->save();

        return $this->success([
            'id'      => (string) $user->id,
            'nome'    => $user->nome,
            'email'   => $user->email,
            'usuario' => $user->usuario
        ], "Usuário atualizado com sucesso", "USUARIO_ATUALIZADO");
    }

    public function show($id) {

        $user = User::find($id);


        if (!$user) {
            return $this->error("Usuário não encontrado", "USUARIO_NAO_ENCONTRADO", 404);
        }

        if (auth('api')->id() != $id) {
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

        $user = User::find($id);

        if (!$user) {
            return $this->error("Usuário não encontrado para exclusão", "USUARIO_NAO_ENCONTRADO", 404);
        }

        if (auth('api')->id() != $id) {
            return $this->error("Você não tem permissão para deletar este usuário", "ACESSO_NEGADO", 403);
        }

        $user->delete();

        return $this->success(new \stdClass(),  "Operação realizada com sucesso", "OPERACAO_SUCESSO");
    }
}