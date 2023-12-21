<?php

function getPDO(array $dbConfig) : PDO {   
    try {
        return new PDO('mysql:host=' . $dbConfig['host'] . ';port=' . $dbConfig['port'] . ';charset=utf8;dbname=' . $dbConfig['name'], $dbConfig['username'], $dbConfig['password']);
    } 
    catch (PDOException $e) {
        die($e->getMessage());
    }
}

function sendMessage(string $url, int $chatId, string $text) {
    $con = curl_init();
    //Формируем строку запроса – отправка пользователю сообщения «hello»
    $msg=$url."sendMessage?chat_id=".$chatId."&text=$text";
    //Настраиваем запрос
    curl_setopt($con, CURLOPT_URL, $msg);
    curl_setopt($con, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($con, CURLOPT_HEADER, 0);
    //Выполняем запрос
    $output = curl_exec($con);
    //Закрываем запрос
    curl_close($con);
    // $apiUrl = "https://api.telegram.org/bot$botToken/sendMessage";
    //     $params = [
    //         'chatId' => $chatId,
    //         'text' => $text
    //     ];
    //     file_get_contents($apiUrl . '?' . http_build_query($params));
}