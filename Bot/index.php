<?php

require_once 'functions.php';
$config = require_once 'config.php';

$pdo = getPDO($config['db']);

$botToken = '6748061151:AAEeAYx13C62g7kX0C34_2IBxkHsaVLGXJw';
$url="https://api.telegram.org/bot$botToken/";

// Получаем данные из входящего запроса.
$update = json_decode(file_get_contents("php://input"), true);

if (isset($update['message'])) {
    // Если отправлено сообщение, то вводим данные в переменные.
    $message = $update['message'];
    $chatId = $message['chat']['id'];
    $text = $message['text'];
    $username = $message['from']['username'];

    // Проверяем, что получено сообщение "/start".
    if ($text == '/start') {
        // Проверяем, есть ли пользователь с таким ником в БД.
        $stmt = $pdo->prepare("SELECT * FROM telegrams WHERE nick = :nick");
        $params = ['nick' => $username];
        $stmt->execute($params);

        // Если пользователь найден и chatId у него пустой, вставляем в БД его chatId.
        if (($row = $stmt->fetch()) && empty($row['chat_id'])) {
            $tgId = $row['telegram_id'];
            $stmt = $pdo->prepare("UPDATE telegrams SET chat_id = :chatId WHERE telegram_id = :tgId");
            $params = [
                'chatId' => $chatId,
                'tgId' => $tgId
            ];
            $stmt->execute($params);

            sendMessage($url, $chatId, "Вы зарегистрированы в отправке уведомлений.");
        } 
        elseif (!empty($row['chat_id'])) sendMessage($url, $chatId, "К вашему аккаунту уже присвоено id.");
        else sendMessage($url, $chatId, "Ваш аккаунт не был найден в БД.");
    }
}
