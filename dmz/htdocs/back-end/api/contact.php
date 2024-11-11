<?php
require_once 'config.php';

$contactReq = filter_input_array(INPUT_POST, [
    'name' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => REGEXP_NAME]
    ],
    'email' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => REGEXP_EMAIL]
    ],
    'subject' => [
        'filter' => FILTER_DEFAULT
    ],
    'message' => [
        'filter' => FILTER_DEFAULT
    ]
]);

try {
    $name = $contactReq['name'];
    $email = $contactReq['email'];
    $subject = substr($contactReq['subject'], 0, 255);
    $message = substr($contactReq['message'], 0, 255);

    // BAD REQUEST
    if (!isset($contactReq)) {
        responseHandler(400, "Erro ao enviar mensagem.");
        return;
    }
    foreach ($contactReq as $value) {
        if (!$value) {
            responseHandler(400, "Erro ao enviar mensagem.");
            return;
        }
    }

    $query = sql("INSERT INTO contact_requests(name, email, subject, message) VALUES(?, ?, ?, ?)", 'ssss', [$name, $email, $subject, $message]);
    if ($query) {
        responseHandler(200, 'Mensagem registrada.');
        return;
    }

    responseHandler(400, "Erro ao enviar mensagem.");

} catch (\Throwable $th) {
    responseHandler(500, UNEXPECTED_ERROR_MSG);
}
