<?php
require_once 'config.php';

try {
    // GET SESSION INFO
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SESSION['status'] == STATUS_AUTHENTICATED) {
        $cpf = $_SESSION['cpf'];
        $query = sql("SELECT * FROM users WHERE cpf = ?", 's', [$cpf]) -> fetch_assoc();

        responseHandler(200, "",[
            'cpf' => $cpf,
            'name' => $query['full_name'],
            'money' => number_format($query['balance'], 2, ',', '.'),
            'email' => $query['email'],
            'phone' => $query['phone'],
            'hasImage' => !empty($query['image_name']),
            'transfers' => sql("SELECT * FROM transfers WHERE cpf_src = ? OR cpf_dst = ? ORDER BY date DESC", 'ss', [$cpf, $cpf]) -> fetch_all(MYSQLI_ASSOC)
        ]);
    
    // LOGOUT
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        sessionDestroy();

    // INVALID REQUEST/SESSION
    } else {
        responseHandler(500, UNEXPECTED_ERROR_MSG);
    }

} catch (\Throwable $th) {
    responseHandler(500, UNEXPECTED_ERROR_MSG);
}
