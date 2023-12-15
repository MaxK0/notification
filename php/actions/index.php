<?php

require_once '../helpers.php';

//Получение данных с формы, если нет, то null.
$name = $_POST['name'] ?? null;
$surname = $_POST['surname'] ?? null;
$lastname = $_POST['lastname'] ?? null;
$releaseDate = $_POST['release_date'] ?? null;
$expiryDate = $_POST['expiry_date'] ?? null;
$email = $_POST['email'] ?? null;
$telegramPhone = $_POST['telegram_phone'] ?? null;
$telegramNick = $_POST['telegram_nick'] ?? null;

// Добавление значений переменных в сессию для последующего изъятия.
addOldValue(key: 'name', value: $name);
addOldValue(key: 'surname', value: $surname);
addOldValue(key: 'lastname', value: $lastname);
addOldValue(key: 'release_date', value: $releaseDate);
addOldValue(key: 'expiry_date', value: $expiryDate);
addOldValue(key: 'email', value: $email);
addOldValue(key: 'telegram_phone', value: $telegramPhone);
addOldValue(key: 'telegram_nick', value: $telegramNick);


// Валидация

// Проверка имени на пустоту и длину.
if (empty($name) || strlen($name) > 45) {
    addValidationError(fieldName: 'name', message: 'Неверное имя');
}

// Проверка фамилии на пустоту и длину.
if (empty($surname) || strlen($surname) > 45) {
    addValidationError(fieldName: 'surname', message: 'Неверная фамилия');
}

// Проверка имени на длину. Это поле может быть пустым, т.к. отчество не обязательно.
if (!empty($lastname) && strlen($lastname) > 45) {
    addValidationError(fieldName: 'lastname', message: 'Неверное отчество');
}

// Проверка даты выпуска на пустоту и, чтобы она была не больше, чем текущая дата.
// Можно ограничить эту дату и по минимально возможному значению, если нужно.
if (empty($releaseDate) || $releaseDate > date('YYYY-mm-dd')) {
    addValidationError(fieldName: 'release_date', message: 'Недопустимая дата');
}

// Проверка даты истечения на пустоту и, чтобы она была не меньше, чем текущая дата.
// Можно ограничить эту дату и по максимально возможному значению, если нужно.
if (empty($expiryDate) || $expiryDate < date('YYYY-mm-dd')) {
    addValidationError(fieldName: 'expiry_date', message: 'Недопустимая дата');
}

// Проверка на то, чтобы был указан хотя бы один контакт для уведомлений.
if (empty($email) && empty($telegramPhone) && empty($telegramNick)) {
    addValidationError(fieldName: 'contact', message: 'Не указан ни один контакт');
}

// Если указана почта, то проверка по встроенному фильтру.
if (!empty($email) && !filter_var(value: $email, filter: FILTER_VALIDATE_EMAIL)) {
    addValidationError(fieldName: 'email', message: 'Указана неверная почта');
}

// Если указан телефон, то проверка, чтобы количество знаков было только 11 и это были цифры.
if (!empty($telegramPhone) && !preg_match('/^\d{11}$/', $telegramPhone)) {
    addValidationError(fieldName: 'telegram_phone', message: 'Неверный номер телефона');
}

// Если указан никнейм, то проверка, чтобы ник начинался только с буквы, а дальше от 3 до 31 знака либо буквы, либо цифры, либо _ с любым регистром.
if (!empty($telegramNick) && !preg_match('/^[a-z]{1}[a-z\d_]{3,31}$/i', $telegramNick)) {
    addValidationError(fieldName: 'telegram_nick', message: 'Неверный ник');
}

// Если обнаружены неправильные поля, то снова пользователя отправляют заполнять данные, не давая идти программе дальше.
if (!empty($_SESSION['validation'])) {
    redirect('/layout/index.php');
}

// Присваивание null к необязательным полям, если пустые.
// Нужно, т.к. прошлые значения содеражт "", а не null.
// В итоге, в БД будут пустые значения, а нужно null.
if (empty($lastname)) $lastname = null;
if (empty($email)) $email = null;
if (empty($telegramPhone)) $telegramPhone = null;
if (empty($telegramNick)) $telegramNick = null;



// Добавление данных в БД.



// Получение экземпляра БД.
$pdo = getPDO();

// Получение id, если в таблицах уже есть вводимые данные, иначе null.
$userId = getIdIfDataExists(pdo: $pdo, table: 'user', elementDB: 'surname', elementProgram: $surname);
$emailId = getIdIfDataExists(pdo: $pdo, table: 'email', elementDB: 'email', elementProgram: $email);

$telegramId = null;
if (!empty($telegramPhone)) $telegramId = getIdIfDataExists(pdo: $pdo, table: 'telegram', elementDB: 'phone', elementProgram: $telegramPhone);
if (!$telegramId && !empty($telegramNick)) $telegramId = getIdIfDataExists(pdo: $pdo, table: 'telegram', elementDB: 'nick', elementProgram: $telegramNick);


// Добавление пользователя, если его нет в таблице.
if (!$userId) {
    $query = "INSERT INTO users (name, surname, lastname) VALUES (:name, :surname, :lastname)";
    $params = [
        'name' => $name,
        'surname' => $surname,
        'lastname' => $lastname,    
    ];
    $userId = addData($pdo, $query, $params);
}

// Добавление почты, если указана и нет в таблице
if (!empty($email) && !$emailId) {
    $query = "INSERT INTO emails (email) VALUES (:email)";
    $params = [
        'email' => $email
    ];
    $emailId = addData($pdo, $query, $params);
}

// Добавление телеграмм контактов, если хотя бы один указан и нет в таблице
if ((!empty($telegramNick) || !empty($telegramPhone)) && !$telegramId) {
    $query = "INSERT INTO telegrams (phone, nick) VALUES (:phone, :nick)";
    $params = [
        'phone' => $telegramPhone,
        'nick' => $telegramNick
    ];
    $telegramId = addData($pdo, $query, $params);
}

// Добавление данных в общую таблицу, если нужные id получены.
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

    addValidationError(fieldName: 'submit', message: 'Информация сохранена'); // Добавление в сессию для отображения, что информация сохранилась.
    $_SESSION['old'] = []; // Очищение прошлых значений в полях.
}

redirect('/layout/index.php'); // Перенаправление обратно на странцицу ввода данных.

