<?php

// Программа будет срабатывать раз в сутки, проходясь по БД и рассылая уведомления,
// если срок истечения больше текущего на месяц, неделю или уже равен.

require_once 'functions.php';
require_once 'vendor/autoload.php';
$config = require_once 'config.php';

const SECONDSINDAY = 24 * 60 * 60;

while (True) {
    $pdo = getPDO($config['db']);

    $now = new DateTime(); // Текущее время.

    $query = "SELECT * FROM signatures";
    $stmt = $pdo->query($query);

    while ($row = $stmt->fetch()) {
        if (strtotime(date('Y-m-d')) > strtotime($row['expiry_date'])) continue;    

        $date = DateTime::createFromFormat("Y-m-d", $row['expiry_date']); // Получаем дату истечения из БД.
        $dateDiff = $date->diff($now); // Получаем разницу дат.

        if (empty($row['email'])) continue;
        if ($dateDiff->m > 1 || $dateDiff->y > 0) continue;

        $to = null;
        $subject = 'Истечение срока ЭП';
        $body = null;

        if (date('Y-m-d') == $row['expiry_date']) {        
            $to = getEmailFromDB($pdo, $row);        
            $body = 'Ваш срок действия ЭП истек.';
        }
        elseif ($dateDiff->d == 7) {
            $to = getEmailFromDB($pdo, $row);
            $body = 'Осталась 1 неделя до конца срока действия ЭП.';
        }
        elseif ($dateDiff->m == 1 && $dateDiff->d == 0) {
            $to = getEmailFromDB($pdo, $row);
            $body = 'Остался 1 месяц до конца срока действия ЭП.';
        }

        if (!empty($to)) sendEmail($config, $to, $subject, $body);
    }
    
    sleep(SECONDSINDAY); //Дожидаемся следующих суток
}
