<?php
require_once 'config.php';

$forgotPassReq = filter_input_array(INPUT_POST, [
    'cpf' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => REGEXP_CPF]
    ],
    'pass' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => REGEXP_PASS]
    ]
]);

try {
    $cpf = $forgotPassReq['cpf'];
    $pass = $forgotPassReq['pass'];

    // CPF
    if ($cpf) {
        $query = sql("SELECT * FROM users WHERE cpf = ?", 's', [$cpf]) -> fetch_assoc();

        // CPF NOT FOUND / NO TOTP
        if (!isset($query['totp_secret'])) {
            responseHandler(401, 'CPF não encontrado.');
            return;
        }

        // SUCCESS
        $_SESSION['cpf'] = $query['cpf'];
        $_SESSION['status'] = STATUS_FORGOT_PASS;
        
        responseHandler(200, 'Realize a verificação em duas etapas.');

    // PASS
    } elseif ($pass) {
        if ($_SESSION['status'] != STATUS_CHANGING_PASS) {
            responseHandler(403, UNEXPECTED_ERROR_MSG);
            return;
        }
        
        // SUCCESS
        sql("UPDATE users SET pass = ? WHERE cpf = ?", 'ss', [$pass, $_SESSION['cpf']]);
        
        sessionDestroy();
        responseHandler(200, 'Senha alterada com sucesso.');

    // BAD REQUEST
    } else {
        responseHandler(400, UNEXPECTED_ERROR_MSG);
        return;
    }

} catch (\Throwable $th) {
    responseHandler(500, UNEXPECTED_ERROR_MSG);
}
