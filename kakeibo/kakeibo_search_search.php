<?php
require_once '../common/DbManager.php';

session_start();

$_SESSION['hiduke'] = $_GET['hiduke'];

$errors = [];

if ($_SESSION['hiduke'] === '') {
    $errors[] = '日付は必須入力です。';
}

if (count($errors) > 0) {
    $_SESSION['kakeibo_search_errors'] = $errors;
    header('Location: http://' . $_SERVER['HTTP_HOST']
        . dirname($_SERVER['PHP_SELF']). '/kakeibo_index.php');
    exit();
}