<?php
use OTPHP\TOTP;

require_once __DIR__ . '/../vendor/autoload.php';

define('REGEXP_CPF', "/^\d{3}\.\d{3}\.\d{3}-\d{2}$/");
define('REGEXP_NAME', "/^(([A-zÀ-ú']{2,})\s?)+$/");
define('REGEXP_DATE', "/^\d{2}\/\d{2}\/\d{4}$/");
define('REGEXP_PHONE', "/^\(\d{2}\) \d{5}-\d{4}$/");
define('REGEXP_EMAIL', "/^[^@ ]+@[^@ ]+\.[^@ ]+$/");
define('REGEXP_PASS', "/^[a-fA-F0-9]{64}$/");
define('REGEXP_MONEY', "/^\d{1,3}(.\d{3}){0,2}(,\d{2})/");
define('REGEXP_OTP', "/^\d{6}$/");

define('STATUS_LOGGED_OUT', "0");
define('STATUS_AUTHENTICATED', "1");
define('STATUS_AWAITING_MFA', "2");
define('STATUS_NO_MFA', "3");
define('STATUS_FORGOT_PASS', "4");
define('STATUS_CHANGING_PASS', "5");

define ('UNEXPECTED_ERROR_MSG', 'Ocorreu um erro inesperado, tente novamente mais tarde.');
define ('UNAUTHORIZED_ERROR_MSG', 'CPF e/ou senha inválido(s).');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['status'])) {
    session_unset();
    $_SESSION['status'] = STATUS_LOGGED_OUT;
}

function sessionDestroy() {
    setcookie(session_name(), 
        "",
        0,
        "/",
        "",
        true,
        true
    );
    session_unset();
    session_destroy();
}

function responseHandler($code, $message, $data = null) {
    http_response_code($code);
    $response = [
        'code' => $code,
        'message' => $message
    ];
    if (isset($data)) {
        $response['data'] = $data;
    }
    echo json_encode($response);
}

function getConn() {
    $dbHost = trim(file_get_contents(getenv('DB_HOST')));
    $dbUser = trim(file_get_contents(getenv('DB_USER')));
    $dbPass = trim(file_get_contents(getenv('DB_PASSWORD_FILE')));
    $dbTable = trim(file_get_contents(getenv('DB_TABLE')));
    return new mysqli($dbHost, $dbUser, $dbPass, $dbTable);
}

function sql($query, $type, $data) {
    try {
        $conn = getConn();
        $stmt = $conn -> prepare($query);
        $stmt -> bind_param($type, ...$data);
        $stmt -> execute();
        $result = $stmt -> get_result();

        if ($result) {
            return $result;
        } else {
            return $stmt -> errno == 0;
        }

    } catch (\Throwable $th) {
        print_r($th);
        return false;

    } finally {
        $conn -> close();
    }

}

function genTOTP() {
    $totp = TOTP::generate();
    $totp->setLabel('MintBank');
    $qrCodeUri = $totp -> getQrCodeUri(
        'https://api.qrserver.com/v1/create-qr-code/?data=[DATA]&size=300x300&ecc=M',
        '[DATA]'
    );
    $secret = $totp -> getSecret();
    return array(
        'qrCodeUri' => $qrCodeUri,
        'secret' => $secret
    );
}

function verifyTOTP($secret, $otp) {
    $totp = TOTP::createFromSecret($secret);
    return $totp -> verify($otp);
}
