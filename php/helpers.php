<?php

session_start();

require_once 'config.php';

function redirect(string $path) {
    header("Location: $path");
    die();
}

function validationErrorAttr(string $fieldName) {
    echo isset($_SESSION['validation'][$fieldName]) ? 'aria-invalid="true"' : '';
}

function hasValidationError($fieldName) : bool {
    return isset($_SESSION['validation'][$fieldName]);
}

function validationErrorMessage(string $fieldName) {
    $message = $_SESSION['validation'][$fieldName] ?? '';
    unset($_SESSION['validation'][$fieldName]);
    echo $message; 
}

function addValidationError(string $fieldName, string $message) {
    $_SESSION['validation'][$fieldName] = $message;    
}

function addOldValue(string $key, mixed $value) {
    $_SESSION['old'][$key] = $value;
}

function old(string $key) : string {
    $value = $_SESSION['old'][$key] ?? '';
    unset($_SESSION['old'][$key]);
    return $value;
}

function getPDO() : PDO {
    try {
        return new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8;dbname=' . DB_NAME, DB_USERNAME, DB_PASSWORD);
    } 
    catch (PDOException $e) {
        die($e->getMessage());
    }
}

function addData(PDO $pdo, string $query, array $params) : int {
    $stmt = $pdo->prepare($query);

    try {
        $stmt->execute($params);
        return $pdo->lastInsertId();
    }
    catch (PDOException $e) {
        die($e->getMessage());
    }
}

function isDataExists(PDO $pdo, string $table, string $elementDB, $elementProgram) : mixed {
    try {
        $query = "SELECT * FROM {$table}s";
        $stmt = $pdo->query($query);
        while ($row = $stmt->fetch()) {
            if ($row[$elementDB] == $elementProgram) {
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