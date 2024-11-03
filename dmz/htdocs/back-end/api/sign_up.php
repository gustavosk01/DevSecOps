<?php
require_once 'config.php';

$signUpReq = filter_input_array(INPUT_POST, [
    'cpf' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => REGEXP_CPF]
    ],
    'name' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => REGEXP_NAME]
    ],
    'birth' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => REGEXP_DATE]
    ],
    'phone' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => REGEXP_PHONE]
    ],
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
    $cpf = $signUpReq['cpf'];
    $name = $signUpReq['name'];
    $birth = $signUpReq['birth'];
    $phone = $signUpReq['phone'];
    $email = $signUpReq['email'];
    $pass = $signUpReq['pass'];

    // BAD REQUEST
    if (!isset($signUpReq)) {
        responseHandler(400, UNEXPECTED_ERROR_MSG);
        return;
    }
    foreach ($signUpReq as $value) {
        if (!$value) {
            responseHandler(400, UNEXPECTED_ERROR_MSG);
            return;
        }
    }

    $query = sql("SELECT cpf, email FROM users WHERE cpf = ? OR email = ?", 'ss', [$cpf, $email]) -> fetch_assoc();
    if ($query) {
        // CPF ALREADY EXISTS
        if ($query['cpf'] == $cpf) {
            responseHandler(200, 'CPF já cadastrado.');
            return;
        }
        // EMAIL ALREADY EXISTS
        if ($query['email'] == $email) {
            responseHandler(200, 'E-mail já cadastrado.');
            return;
        }
    }

    // SUCCESS
    sql("INSERT INTO users(cpf, full_name, birth_date, phone, email, pass) VALUES(?, ?, ?, ?, ?, ?)", 'ssssss', [$cpf, $name, $birth, $phone, $email, $pass]);
    
    $_SESSION['cpf'] = $cpf;
    $_SESSION['status'] = STATUS_NO_MFA;

    responseHandler(201, 'Conta criada com sucesso.');

} catch (\Throwable $th) {
    responseHandler(500, UNEXPECTED_ERROR_MSG);
}
