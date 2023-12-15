<?php

// Программа будет срабатывать раз в сутки, проходясь по БД и рассылая уведомления,
// если срок истечения больше текущего на месяц, неделю или уже равен.

require_once 'helpers.php';

const SECONDSINDAY = 24 * 60 * 60;

$pdo = getPDO();

while (True) {
    $currentDate = date("YYYY-mm-dd");

    $query = "SELECT * FROM signatures";
    $stmt = $pdo->query($query);

    while ($row = $stmt->fetch()) {
        
        $difference = $row['expiry_date'] - $current_date;

        
        $days_remaining = floor($difference / SECONDSINDAY);

        
        if ($days_remaining == 7) {
            
        } 
    }
    
    sleep(SECONDSINDAY); //Дожидаемся следующих суток
}
