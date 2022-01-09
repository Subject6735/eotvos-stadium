<?php
session_start();
include('storage.php');
include('auth.php');

function redirect($page)
{
    header("Location: ${page}");
    exit();
}

$userStorage = new Storage(new JsonIO('json/users.json'));
$auth = new Auth($userStorage);

if (!isset($_SESSION['user'])) {
    exit();
}

$auth->logout();
redirect('index.php');
