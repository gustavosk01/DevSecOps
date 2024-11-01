<?php
require_once 'config.php';

$otpReq = filter_input_array(INPUT_POST, [
    'otp' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => REGEXP_OTP]
    ]
]);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($_SESSION['cpf'])) {
            responseHandler(400, UNEXPECTED_ERROR_MSG);
            return;
        }
        $totpData = genTOTP();
        $totpSecret = $totpData['secret'];
        $qrCodeUri = $totpData['qrCodeUri'];
        $_SESSION['temp_totp_secret'] = $totpSecret;

        $data = [
            'qrCodeUri' => $qrCodeUri
        ];

        // TEST USER BACKDOOR
        if ($_SESSION['cpf'] == '000.000.000-00') {
            $_SESSION['status'] = STATUS_AUTHENTICATED;
            responseHandler(200, '');
            return;
        }

        responseHandler(202, '', $data);
    
    } elseif ($otpReq['otp']) {
        $otp = $otpReq['otp'];
        $cpf = $_SESSION['cpf'];
    
        // UNEXPECTED ERROR
        if (!isset($cpf)) {
            responseHandler(500, UNEXPECTED_ERROR_MSG);
            return;
        }

        if ($_SESSION['status'] == STATUS_NO_MFA) {
            $totp_secret = $_SESSION['temp_totp_secret'];

        } else {
            $totp_secret = sql("SELECT totp_secret FROM users WHERE cpf = ?", 's', [$cpf]) -> fetch_assoc()['totp_secret'];
        }
    
        // WRONG OTP
        if (!verifyTOTP($totp_secret, $otp)) {
            responseHandler(401, "Código inválido.");
            return;
        }
    
        // SUCCESS
        if ($_SESSION['status'] == STATUS_NO_MFA) {
            sql("UPDATE users SET totp_secret = ? WHERE cpf = ?", 'ss', [$totp_secret, $_SESSION['cpf']]);
        }

        if ($_SESSION['status'] == STATUS_FORGOT_PASS) {
            $_SESSION['status'] = STATUS_CHANGING_PASS;
            responseHandler(202, "Autenticação realizada com sucesso.");

        } else {
            $_SESSION['status'] = STATUS_AUTHENTICATED;
            responseHandler(200, "Autenticação realizada com sucesso.");

        }
        
    } else {
        // BAD REQUEST
        responseHandler(400, UNEXPECTED_ERROR_MSG);
    }
} catch (\Throwable $th) {
    responseHandler(500, UNEXPECTED_ERROR_MSG);
}
