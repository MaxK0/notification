<?php

require_once '../helpers.php';

$name = $_POST['name'] ?? null;
$surname = $_POST['surname'] ?? null;
$lastname = $_POST['lastname'] ?? null;
$releaseDate = $_POST['release_date'] ?? null;
$expiryDate = $_POST['expiry_date'] ?? null;
$email = $_POST['email'] ?? null;
$telegramPhone = $_POST['telegram_phone'] ?? null;
$telegramNick = $_POST['telegram_nick'] ?? null;

//Добавление данных в сессию для последующего извлечения в input, если форма не пройдет валидацию
addOldValue(key: 'name', value: $name);
addOldValue(key: 'surname', value: $surname);
addOldValue(key: 'lastname', value: $lastname);
addOldValue(key: 'release_date', value: $releaseDate);
addOldValue(key: 'expiry_date', value: $expiryDate);
addOldValue(key: 'email', value: $email);
addOldValue(key: 'telegram_phone', value: $telegramPhone);
addOldValue(key: 'telegram_nick', value: $telegramNick);

//Валидация
$_SESSION['validation'] = [];

if (empty($name) || strlen($name) > 45) {
    addValidationError(fieldName: 'name', message: 'Неверное имя');
}

if (empty($surname) || strlen($surname) > 45) {
    addValidationError(fieldName: 'surname', message: 'Неверная фамилия');
}

if (!empty($lastname) && strlen($lastname) > 45) {
    addValidationError(fieldName: 'lastname', message: 'Неверное отчество');
}

if (empty($releaseDate) || $releaseDate > date('YYYY-mm-dd')) {
    addValidationError(fieldName: 'release_date', message: 'Недопустимая дата');
}

if (empty($expiryDate) || $expiryDate < date('YYYY-mm-dd')) {
    addValidationError(fieldName: 'expiry_date', message: 'Недопустимая дата');
}

if (empty($email) && empty($telegramPhone) && empty($telegramNick)) {
    addValidationError(fieldName: 'contact', message: 'Не указан ни один контакт');
}

if (!empty($email) && !filter_var(value: $email, filter: FILTER_VALIDATE_EMAIL)) {
    addValidationError(fieldName: 'email', message: 'Указана неверная почта');
}

if (!empty($telegramPhone) && !preg_match('/^\d{11}$/', $telegramPhone)) {
    addValidationError(fieldName: 'telegram_phone', message: 'Неверный номер телефона');
}

if (!empty($telegramNick) && !preg_match('/^[a-z]{1}[a-z\d_]{3,31}$/i', $telegramNick)) {
    addValidationError(fieldName: 'telegram_nick', message: 'Неверный ник');
}

if (!empty($_SESSION['validation'])) {
    redirect('/layout/index.php');
}

if (empty($lastname)) $lastname = null;
if (empty($email)) $email = null;
if (empty($telegramPhone)) $telegramPhone = null;
if (empty($telegramNick)) $telegramNick = null;

//Добавление данных в БД
$pdo = getPDO();

$userId = isDataExists(pdo: $pdo, table: 'user', elementDB: 'surname', elementProgram: $surname);
$emailId = isDataExists(pdo: $pdo, table: 'email', elementDB: 'email', elementProgram: $email);
$telegramId = null;

if (!empty($telegramPhone)) $telegramId = isDataExists(pdo: $pdo, table: 'telegram', elementDB: 'phone', elementProgram: $telegramPhone);
if (!$telegramId && !empty($telegramNick)) $telegramId = isDataExists(pdo: $pdo, table: 'telegram', elementDB: 'nick', elementProgram: $telegramNick);

if (!$userId) {
    $query = "INSERT INTO users (name, surname, lastname) VALUES (:name, :surname, :lastname)";
    $params = [
        'name' => $name,
        'surname' => $surname,
        'lastname' => $lastname,    
    ];
    $userId = addData($pdo, $query, $params);
}

if (!empty($email) && !$emailId) {
    $query = "INSERT INTO emails (email) VALUES (:email)";
    $params = [
        'email' => $email
    ];
    $emailId = addData($pdo, $query, $params);
}

if ((!empty($telegramNick) || !empty($telegramPhone)) && !$telegramId) {
    $query = "INSERT INTO telegrams (phone, nick) VALUES (:phone, :nick)";
    $params = [
        'phone' => $telegramPhone,
        'nick' => $telegramNick
    ];
    $telegramId = addData($pdo, $query, $params);
}

if (!empty($userId) && (!empty($emailId) || !empty($telegramId))) {
    $query = "INSERT INTO signatures (user, release_date, expiry_date, email, telegram) VALUES (:user, :release_date, :expiry_date, :email, :telegram)";
    $params = [
        'user' => $userId,
        'release_date' => $releaseDate,
        'expiry_date' => $expiryDate,
        'email' => $emailId,
        'telegram' => $telegramId
    ];
    addData($pdo, $query, $params);

    addValidationError(fieldName: 'submit', message: 'Информация сохранена');
    $_SESSION['old'] = [];
}

redirect('/layout/index.php');

