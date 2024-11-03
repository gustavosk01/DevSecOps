<?php
require_once 'config.php';

$updateReq = filter_input_array(INPUT_POST, [
    'email' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => REGEXP_EMAIL]
    ],
    'phone' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => REGEXP_PHONE]
    ]
]);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $query = sql("DELETE FROM users WHERE cpf = ?", 's', [$_SESSION['cpf']]);
        if ($query) {
            sessionDestroy();
            responseHandler(200, "Conta excluída com sucesso.");
            return;
        }
        responseHandler(400, "Falha ao excluir conta.");
        return;
    }

    $email = $updateReq['email'];
    $phone = $updateReq['phone'];

    // BAD REQUEST
    if (!isset($updateReq)) {
        responseHandler(400, UNEXPECTED_ERROR_MSG);
        return;
    }
    foreach ($updateReq as $value) {
        if (!$value) {
            responseHandler(400, UNEXPECTED_ERROR_MSG);
            return;
        }
    }

    // AVOID DUPLICATED DATA
    $query = sql("SELECT email, phone FROM users WHERE (email = ? OR phone = ?) AND cpf != ?", 'sss', [$email, $phone, $_SESSION['cpf']]) -> fetch_assoc();
    if ($query) {
        responseHandler(400, 'Dados já cadastrados.');
        return;
    }

    $query = sql("UPDATE users SET email = ?, phone = ? WHERE cpf = ?", 'sss', [$email, $phone, $_SESSION['cpf']]);
    if ($query) {
        // SUCCESS
        $_SESSION['email'] = $email;
        $_SESSION['phone'] = $phone;
        responseHandler(200, "Dados atualizados com sucesso.", [
            'email' => $email,
            'phone' => $phone
        ]);
        return;
    }

    responseHandler(400, "Erro ao atualizar os dados.");

} catch (\Throwable $th) {
    responseHandler(500, UNEXPECTED_ERROR_MSG);
}