<?php

session_start();

require_once 'config.php';

// Перенаправление на выбранную страницу.
function redirect(string $path) {
    header("Location: $path");
    die();
}

// Добавление поля в сессию непройденной валидации вместе с сообщением: по какой причине не прошло. 
function addValidationError(string $fieldName, string $message) {
    $_SESSION['validation'][$fieldName] = $message;    
}

// Проверка на то, есть ли у поля ошибки.
function hasValidationError($fieldName) : bool {
    return isset($_SESSION['validation'][$fieldName]);
}

// вывод ошибки в html и удаление из сессии непройденной валидации.
// Удаление нужно для того, чтобы, если пользователь введёт данные правильно после того, как он это сделал неправильно,
// то поле перестанет выделяться как ошибочное.
function validationErrorMessage(string $fieldName) {
    $message = $_SESSION['validation'][$fieldName] ?? '';
    unset($_SESSION['validation'][$fieldName]);
    echo $message; 
}

// Добавление свойства aria-invalid="true", если поле не прошло валидацию.
// Свойство нужно для того, чтобы выделить поле визуально.
function validationErrorAttr(string $fieldName) {
    echo isset($_SESSION['validation'][$fieldName]) ? 'aria-invalid="true"' : '';
}

// Добавление данных в сессию для последующего извлечения в input, если форма не пройдет валидацию.
// Это нужно, так как страница с формой перезагружается.
function addOldValue(string $key, mixed $value) {
    $_SESSION['old'][$key] = $value;
}

// Возвращает прошлое значение данных и сразу удаляет, т.к. больше не нужно.
function old(string $key) : string {
    $value = $_SESSION['old'][$key] ?? '';
    unset($_SESSION['old'][$key]);
    return $value;
}


// Работа с БД


// Получение экземпляра БД.
function getPDO() : PDO {
    try {
        return new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8;dbname=' . DB_NAME, DB_USERNAME, DB_PASSWORD);
    } 
    catch (PDOException $e) {
        die($e->getMessage());
    }
}

// Добавление данных в таблицу.
function addData(PDO $pdo, string $query, array $params) : int {
    $stmt = $pdo->prepare($query); // Приготовление к выполнению запроса.

    try {
        $stmt->execute($params); // Выполнение запроса. $params - массив, где ключ - название поля в БД, а значение - значение, которое пользователь ввел.
        return $pdo->lastInsertId(); // Возвращение id вставленной строчки, чтобы потом его вставить в общую таблицу.
    }
    catch (PDOException $e) {
        die($e->getMessage());
    }
}

// Если введенные значения уже существуют в таблице, то возвращается id этого поля.
function getIdIfDataExists(PDO $pdo, string $table, string $elementDB, $elementProgram) : mixed {
    try {
        $query = "SELECT * FROM {$table}s";
        $stmt = $pdo->query($query);

        while ($row = $stmt->fetch()) {
            if ($row[$elementDB] == $elementProgram) {
                // У фамилии дополнительная проверка на совпадение имени и отчества.
                if ($elementDB == 'surname') {
                    if ($row['name'] == $_POST['name'] && $row['lastname'] == $_POST['lastname']) return $row[$table . "_id"];
                }
                else return $row[$table . "_id"]; 
            }               
        }

        return null;
    }
    catch (PDOException $e) {
        die($e->getMessage());
    }
}