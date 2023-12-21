<?php

require_once '../functions.php';


//Получение данных с формы, если нет, то null.
$name = $_POST['name'] ?? null;
$surname = $_POST['surname'] ?? null;
$lastname = $_POST['lastname'] ?? null;
$releaseDate = $_POST['release_date'] ?? null;
$expiryDate = $_POST['expiry_date'] ?? null;
$email = $_POST['email'] ?? null;
$telegramNick = $_POST['telegram_nick'] ?? null;

// Добавление значений переменных в сессию для последующего изъятия.
addOldValue(key: 'name', value: $name);
addOldValue(key: 'surname', value: $surname);
addOldValue(key: 'lastname', value: $lastname);
addOldValue(key: 'release_date', value: $releaseDate);
addOldValue(key: 'expiry_date', value: $expiryDate);
addOldValue(key: 'email', value: $email);
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
if (empty($releaseDate) || $releaseDate > date('Y-m-d')) {
    addValidationError(fieldName: 'release_date', message: 'Недопустимая дата');
}

// Проверка даты истечения на пустоту и, чтобы она была не меньше, чем текущая дата.
// Можно ограничить эту дату и по максимально возможному значению, если нужно.
if (empty($expiryDate) || $expiryDate < date('Y-m-d')) {
    addValidationError(fieldName: 'expiry_date', message: 'Недопустимая дата');
}

// Проверка на то, чтобы был указан хотя бы один контакт для уведомлений.
if (empty($email) && empty($telegramNick)) {
    addValidationError(fieldName: 'contact', message: 'Не указан ни один контакт');
}

// Если указана почта, то проверка по встроенному фильтру.
if (!empty($email) && !filter_var(value: $email, filter: FILTER_VALIDATE_EMAIL)) {
    addValidationError(fieldName: 'email', message: 'Указана неверная почта');
}

// Если указан никнейм, то проверка, чтобы ник начинался только с буквы, а дальше от 3 до 31 знака либо буквы, либо цифры, либо _ с любым регистром.
if (!empty($telegramNick) && !preg_match('/^[a-z]{1}[a-z\d_]{3,31}$/i', $telegramNick)) {
    addValidationError(fieldName: 'telegram_nick', message: 'Неверный ник');
}

// Если обнаружены неправильные поля, то снова пользователя отправляют заполнять данные, не давая идти программе дальше.
if (!empty($_SESSION['validation'])) {
    redirect('../../layout/index.php');
}

// Присваивание null к необязательным полям, если пустые.
// Нужно, т.к. прошлые значения содеражт "", а не null.
// В итоге, в БД будут пустые значения, а нужно null.
if (empty($lastname)) $lastname = null;
if (empty($email)) $email = null;
if (empty($telegramNick)) $telegramNick = null;



// Добавление данных в БД.



// Получение экземпляра БД.
$pdo = getPDO();

// Получение id, если в таблицах уже есть вводимые данные, иначе null.
$userId = getIdIfDataExists(pdo: $pdo, table: 'user', elementDB: 'surname', elementProgram: $surname);
$emailId = getIdIfDataExists(pdo: $pdo, table: 'email', elementDB: 'email', elementProgram: $email);
$telegramId = getIdIfDataExists(pdo: $pdo, table: 'telegram', elementDB: 'nick', elementProgram: $telegramNick);


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

// Добавление телеграмм ника
if (!empty($telegramNick) && !$telegramId) {
    $query = "INSERT INTO telegrams (nick) VALUES (:nick)";
    $params = [
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

    // Добавление в сессию для отображения, что информация сохранилась.
    addValidationError(fieldName: 'submit', message: 'Информация сохранена'); 

    // Очищение прошлых значений в полях.
    $_SESSION['old'] = [];
}

// Перенаправление обратно на странцицу ввода данных.
redirect('../../layout/index.php'); 

