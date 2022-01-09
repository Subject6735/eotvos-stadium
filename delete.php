<?php
session_start();
include('storage.php');
include('auth.php');

if ((isset($_SESSION['user']) && $_SESSION['user']['username'] !== 'admin') || !isset($_SESSION['user'])) {
    echo 'Unauthorized access!';
    exit();
}

function redirect($page)
{
    header("Location: ${page}");
    exit();
}

$commentStorage = new Storage(new JsonIO('json/comments.json'));
$comments = $commentStorage->findAll();

$commentID = $_GET['commentid'];
$commentStorage->delete($commentID);

$teamID = $_GET['teamid'];

redirect('team-details.php?id=' . $teamID);
