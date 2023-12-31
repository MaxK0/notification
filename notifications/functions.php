<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

function getPDO(array $dbConfig) : PDO {   
    try {
        return new PDO('mysql:host=' . $dbConfig['host'] . ';port=' . $dbConfig['port'] . ';charset=utf8;dbname=' . $dbConfig['name'], $dbConfig['username'], $dbConfig['password']);
    } 
    catch (PDOException $e) {
        die($e->getMessage());
    }
}

function sendEmail(array $mailConfig, string $to, string $subject, string $body) {
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = 2;
        $mail->isSMTP();
        $mail->Host = $mailConfig['host'];
        $mail->SMTPAuth = $mailConfig['SMTPauth'];
        $mail->Username = $mailConfig['username'];
        $mail->Password = $mailConfig['password'];
        $mail->SMTPSecure = $mailConfig['secure'];
        $mail->Port = $mailConfig['port'];
        $mail->CharSet = $mailConfig['charset'];

        $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
        $mail->addAddress($to);

        $mail->isHTML($mailConfig['is_html']);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        echo 'Сообщение отправлено';

    } catch (Exception $e) {
        echo "Сообщение не отправлено. Ошибка: {$mail->ErrorInfo}";
        return false;
    }
}

function sendTg(string $url, int $chatId, string $text) {
    $con = curl_init();

    $msg=$url."sendMessage?chat_id=".$chatId."&text=$text";
    
    curl_setopt($con, CURLOPT_URL, $msg);
    curl_setopt($con, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($con, CURLOPT_HEADER, 0);

    curl_exec($con);

    curl_close($con);    
}


function getDataFromDB(PDO $pdo, string $query) : mixed {
    $stmt = $pdo->query($query);
    return $stmt->fetch()[0];
}