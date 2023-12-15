<?php

session_start();

require_once '/config.php';

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

