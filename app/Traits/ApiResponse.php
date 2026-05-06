<?php

namespace App\Traits;

trait ApiResponse {
    protected function success($dados = null, $mensagem = "Operação realizada com sucesso", $codigo = "OPERACAO_SUCESSO", $statusHttp = 200) {
        $resposta = [
            'status' => 'sucesso',
            'codigo' => $codigo,
            'mensagem' => $mensagem,
        ];

        if ($dados !== null) {
            $resposta['dados'] = $dados;
        }

        return response()->json($resposta, $statusHttp);
    }

    protected function error($mensagem = "Erro na operação", $codigo = "CODIGO_ERRO", $statusHttp = 400) {
        return response()->json([
            'status' => 'erro',
            'codigo' => $codigo,
            'mensagem' => $mensagem
        ], $statusHttp);
    }
}

