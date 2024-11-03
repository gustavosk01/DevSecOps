<?php
require_once 'config.php';

$transferReq = filter_input_array(INPUT_POST, [
    'cpf' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => REGEXP_CPF]
    ],
    'value' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => REGEXP_MONEY]
    ],
    'message' => [
        'filter' => FILTER_DEFAULT
    ]
]);
$cpfDst = $transferReq['cpf'];
$value = $transferReq['value'];
$message = substr($transferReq['message'], 0, 255);

try {
    // BAD REQUEST
    if (!$cpfDst || !$value || $_SESSION['status'] != STATUS_AUTHENTICATED) {
        responseHandler(400, UNEXPECTED_ERROR_MSG);
        return;
    }
    $cpfSrc = $_SESSION['cpf'];
    $value = (double) str_replace(',', '.', str_replace('.', '', $value));

    // TRANSFER TO SELF
    if ($cpfSrc == $cpfDst) {
        responseHandler(400, 'Você não pode transferir para sí mesmo.');
        return;
    }

    // INVALID VALUE
    if ($value <= 0) {
        responseHandler(400, 'O valor mínimo para transferências é de R$0,01.');
        return;
    }

    // DESTINATION CPF NOT FOUND
    if (!sql("SELECT COUNT(*) FROM users WHERE cpf = ?", 's', [$cpfDst]) -> fetch_column()) {
        responseHandler(400, 'CPF não encontrado.');
        return;
    }

    $query = sql("SELECT balance FROM users WHERE cpf = ?", 's', [$cpfSrc]) -> fetch_assoc();

    // INSUFFICIENT BALANCE
    if ($query['balance'] < $value) {
        responseHandler(400, 'Dinheiro insuficiente.');
        return;
    }

    // SUCCESS
    if (transfer($cpfSrc, $cpfDst, $value, $message)) {
        responseHandler(200, 'Transferência realizada com sucesso.');
    }

} catch (\Throwable $th) {
    print_r($th -> getMessage());
    responseHandler(500, UNEXPECTED_ERROR_MSG);
}


function transfer($cpfSrc, $cpfDst, $value, $message) {
    try {
        $conn = getConn();
        $conn -> begin_transaction();

        $stmt = $conn -> prepare("INSERT INTO transfers(cpf_src, cpf_dst, amount, message) VALUES(?, ?, ?, ?)");
        $stmt -> bind_param('ssds', $cpfSrc, $cpfDst, $value, $message);
        $stmt -> execute();

        $stmt = $conn -> prepare("UPDATE users SET balance = balance - ? WHERE cpf = ?");
        $stmt -> bind_param('ds', $value, $cpfSrc);
        $stmt -> execute();

        $stmt = $conn -> prepare("UPDATE users SET balance = balance + ? WHERE cpf = ?");
        $stmt -> bind_param('ds', $value, $cpfDst);
        $stmt -> execute();

        $conn -> commit();
        return true;

    } catch (\Throwable $th) {
        $conn -> rollback();

    } finally {
        $conn -> close();
    }
}
