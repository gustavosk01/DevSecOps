<?php
require_once 'config.php';

$loginReq = filter_input_array(INPUT_POST, [
    'email' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => REGEXP_EMAIL]
    ],
    'pass' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => REGEXP_PASS]
    ]
]);

try {
    $email = $loginReq['email'];
    $pass = $loginReq['pass'];

    // BAD REQUEST
    if (!isset($loginReq)) {
        responseHandler(400, UNEXPECTED_ERROR_MSG);
        return;
    }
    foreach ($loginReq as $value) {
        if (!$value) {
            responseHandler(400, UNEXPECTED_ERROR_MSG);
            return;
        }
    }

    $query = sql("SELECT * FROM users WHERE email = ?", 's', [$email]) -> fetch_assoc();

    // EMAIL NOT FOUND
    if (!$query) {
        responseHandler(401, UNAUTHORIZED_ERROR_MSG);
        return;
    }

    // WRONG PASSWORD
    if ($query['pass'] != $pass) {
        sql("UPDATE users SET login_attempts = login_attempts + 1 WHERE email = ?", 's', [$email]);
        responseHandler(401, UNAUTHORIZED_ERROR_MSG);
        return;
    }

    // ACCOUNT WAS BLOCKED
    if ($query['login_attempts'] >= 3) {
        responseHandler(401, UNAUTHORIZED_ERROR_MSG);
        return;
    }

    // SUCCESS
    $_SESSION['cpf'] = $query['cpf'];
    $_SESSION['status'] = isset($query['totp_secret']) ? STATUS_AWAITING_MFA : STATUS_NO_MFA;

    if ($_SESSION['status'] == STATUS_NO_MFA) {
        responseHandler(202, 'Cadastre a verificação em duas etapas.');
        return;
    } else {
        responseHandler(200, 'Realize a verificação em duas etapas.');
        return;
    }

} catch (\Throwable $th) {
    responseHandler(500, UNEXPECTED_ERROR_MSG);
}
