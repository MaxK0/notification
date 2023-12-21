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
    // Инициализация запроса.
    $con = curl_init();

    // Формируем строку запроса – отправка пользователю сообщения.
    $msg=$url."sendMessage?chat_id=".$chatId."&text=$text";

    // Настраиваем запрос.
    curl_setopt($con, CURLOPT_URL, $msg);
    curl_setopt($con, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($con, CURLOPT_HEADER, 0);

    // Выполняем запрос.
    curl_exec($con);

    // Закрываем запрос.
    curl_close($con);
    
}