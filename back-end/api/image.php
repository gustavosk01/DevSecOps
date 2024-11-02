<?php
require_once 'config.php';

try {
    // GET IMAGE
    if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_SESSION['status'] == STATUS_AUTHENTICATED) {
        // SUCCESS
        $imageName = sql("SELECT image_name FROM users WHERE cpf = ?", 's', [$_SESSION['cpf']]) -> fetch_column();
        sendImage("../images/$imageName");

    // CHANGE IMAGE
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image']) && $_SESSION['status'] == STATUS_AUTHENTICATED) {
        $cpf = $_SESSION['cpf'];
        $image = $_FILES['image'];
        $image_tmp = $_FILES['image']['tmp_name'];

        // INVALID FILE
        if ($image['error'] != 0 || $image['size'] <= 11) {
            responseHandler(400, 'Falha ao fazer o upload da imagem.');
            return;
        }

        // INVALID FILE TYPE
        $ext = validateType($image_tmp);
        if ($ext === false) {
            responseHandler(400, 'Formato inválido (Apenas JPEG e PNG).');
            return;
        }

        // FILE IS TOO LARGE
        if ($image['size'] > 2097152) {
            responseHandler(400, 'Imagem muito grande (Max. 2MB).');
            return;
        }

        // SUCCESS
        $new_file_name = sprintf('%s.%s', hash_file('sha256', $image_tmp), $ext);
        move_uploaded_file($image_tmp, "../images/$new_file_name");

        sql("UPDATE users SET image_name = ? WHERE cpf = ?", 'ss', [$new_file_name, $cpf]);

        responseHandler(200, 'Imagem alterada com sucesso.');

    // BAD REQUEST
    } else {
        responseHandler(400, UNEXPECTED_ERROR_MSG);
        return;
    }
    
} catch (\Throwable $th) {
    responseHandler(500, UNEXPECTED_ERROR_MSG);
}

function sendImage($filename) {
    if (file_exists($filename)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filename);
        finfo_close($finfo);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename=' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filename));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        flush();
        
        readfile($filename);
        exit;
    } else {
        responseHandler(404, 'Imagem não encontrada.');
    }
}

function validateType($img) {
    $validTypes = array(
        'jpeg' => IMAGETYPE_JPEG,
        'png' => IMAGETYPE_PNG
    );
    return array_search(
        exif_imagetype($img),
        $validTypes,
        true
    );
}
