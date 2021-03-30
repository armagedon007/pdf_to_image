<?php

//автозагрузка классов
spl_autoload_register(function ($class) {
    $class = ltrim($class, '\\');
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

$inbox = __DIR__ . '/in';
$outbox = __DIR__ . '/out';

set_time_limit(-1);

//сохрнить загруженный файл в папку с входящими 
$pathToFile = '';
clearDir($inbox);
if($_FILES) {  
    $pathToFile = $inbox . '/' .  $_FILES['file']['name'];
    $pathToFile = escapeshellcmd($pathToFile);
    move_uploaded_file($_FILES['file']['tmp_name'], $pathToFile);
}

function clearDir($dir) {
    //очистить папки
    array_map(function($file)use($dir){
        @unlink($dir . '/' . $file);
    }, array_diff(scandir($dir), ['.', '..']));
}

try {
    if(!($pathToFile && file_exists($pathToFile))) {
        throw new \Exception("File does not exist");
    }
    clearDir($outbox);

    $fileSource = new Armagedon007\Converters\Converter($pathToFile);

    $fileSource->saveAllPagesAsImages($outbox, 'slide_');

    clearDir($inbox);

    $items = '';
    foreach($fileSource->fileList() as $item) {
        $link = str_replace(__DIR__, '', $item);
        $items .= "<a href=\"{$link}\" target=\"_blank\"><img src=\"{$link}\" style=\"max-width:100px;\"></a>";
    }
    echo json_encode([
        'status' => 'success',
        'items' => $items,
    ]);
} catch(\Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Ошибка: ' . $e->getMessage()
    ]);
}


?>