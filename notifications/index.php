<?php

// Программа будет срабатывать раз в сутки, проходясь по БД и рассылая уведомления,
// если срок истечения больше текущего на месяц, неделю или уже равен.
// Она будет смотреть: указана ли почта, либо id чата телеграмма пользователя, либо всё вместе.
// Если почта, то отправка с помощью библиотеки PHPMailer.
// Если тг, то отправка с помощью тг бота.


require_once 'functions.php';
require_once 'vendor/autoload.php';
$config = require_once 'config.php';

const SECONDSINDAY = 24 * 60 * 60;

$botToken = $config['bot']['token'];
$url = $config['bot']['url'] . $botToken . '/';

// Бесконечный цикл для регулярной проверки: нужно ли расслать уведомления.
while (true) {
    // Подключение к БД.
    $pdo = getPDO($config['db']);
    
    // Получаем текущую дату.
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $now->setTime(0, 0, 0); // Устанавливаем время на полночь.
    
    // Выбираем все записи из таблицы, где хранится информация об ЭП.
    $query = "SELECT * FROM signatures";
    $stmt = $pdo->query($query);
    
    // Проходим по каждой строке таблицы.
    while ($row = $stmt->fetch()) {        
        // Пропускаем записи, которые уже просрочены.
        if (strtotime(date('Y-m-d')) > strtotime($row['expiry_date'])) continue;
        
        // Получаем дату истечения ЭП из БД.
        $date = DateTime::createFromFormat("Y-m-d", $row['expiry_date']);
        // Получаем разницу в днях между текущей датой и датой истечения ЭП.
        $dateDiff = $date->diff($now);
        
        // Пропускаем записи, которым осталось больше месяца.
        if ($dateDiff->m > 1 || $dateDiff->y > 0) continue;
        
        // Инициализация переменных для уведомлений.

        // Для почты:
        $isMail = false;
        $to = null;
        $subject = 'Истечение срока ЭП';
        $body = '';
        
        // Для телеграмма:
        $isTg = false;
        $chatId = null;
        
        // Формирование текста уведомления в зависимости от оставшегося времени.
        if (date('Y-m-d') == $row['expiry_date']) {
            $isMail = !empty($row['email']);
            $isTg = !empty($row['telegram']);
            $body .= 'ваш срок действия ЭП истек.';
        } 
        elseif ($dateDiff->d == 7) {
            $isMail = !empty($row['email']);
            $isTg = !empty($row['telegram']);
            $body .= 'осталась 1 неделя до конца срока действия ЭП.';      
        } 
        elseif ($dateDiff->m == 1 && $dateDiff->d == 0) {
            $isMail = !empty($row['email']);
            $isTg = !empty($row['telegram']);
            $body .= 'остался 1 месяц до конца срока действия ЭП.';
        }
        
        // Получение адреса электронной почты, если есть в таблице.
        if ($isMail) {
            $query = "SELECT email FROM emails WHERE email_id = {$row['email']}";
            $to = getDataFromDB($pdo, $query);
        }
        
        // Получение id пользователя, если есть в таблице.
        if ($isTg) {
            $query = "SELECT chat_id FROM telegrams WHERE telegram_id = {$row['telegram']}";
            $chatId = getDataFromDB($pdo, $query);
        }
        
        // Отправка уведомлений.
        if (!empty($to)) sendEmail($config['mail'], $to, $subject, $body);
        if (!empty($chatId)) sendTg($url, $chatId, $body);
    }
    
    // Пауза перед следующей проверкой.
    sleep(SECONDSINDAY);
}

