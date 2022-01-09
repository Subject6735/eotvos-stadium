<?php
session_start();
include('storage.php');

$teamStorage = new Storage(new JsonIO('json/teams.json'));
$teams = $teamStorage->findAll();

$matchStorage = new Storage(new JsonIO('json/matches.json'));
$matches = $matchStorage->findAll();

$filteredMatches = array_filter($matches, fn ($m) => isset($m['home']['score']));

function compare($a, $b)
{
    return $a['date'] < $b['date'];
}

usort($filteredMatches, "compare");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <title>Eötvös Loránd Stadium</title>
</head>

<body>
    <header>
        <div class="title"><a href="index.php">Eötvös Loránd Stadium</a></div>

        <a class="register button" href="register.php">Register</a>

        <?php if (isset($_SESSION['user'])) : ?>
            <div class="logout">
                <a class="logout button" href="logout.php">Logout</a>
                <span>Logged in as '<?= $_SESSION['user']['username'] ?>'</span>
            </div>
        <?php else : ?>
            <a class="login button" href="login.php">Login</a>
        <?php endif ?>
    </header>

    <main>
        <div class="intro">
            <h1>Eötvös Loránd Stadium</h1>
            <p>
                This page contains the teams of Eötvös Loránd Stadium, and which matches they played.
                The teams and the last five matches are listed below. You can see the team details by clicking 'details'.
                To leave a comment, you have to be logged in. If you aren't registered yet, click 'register'. To log in, click 'login'.
            </p>
        </div>

        <div class="teams">
            <h2>Teams</h2>
            <ul>
                <?php foreach ($teams as $team) : ?>
                    <li><?= $team['name'] ?> | <a href="team-details.php?id=<?= $team['id'] ?>">Details</a></li>
                <?php endforeach ?>
            </ul>
        </div>

        <div class="matches">
            <h2>Matches</h2>
            <ul>
                <?php if (count($filteredMatches) <= 5) : ?>
                    <?php foreach ($filteredMatches as $match) : ?>
                        <li><?= $match['id'] ?> (<?= $match['date'] ?>)</li>
                    <?php endforeach ?>
                <?php else : ?>
                    <?php for ($i = 0; $i < 5; ++$i) : ?>
                        <li><?= $filteredMatches[array_keys($filteredMatches)[$i]]['id'] ?> (<?= $filteredMatches[array_keys($filteredMatches)[$i]]['date'] ?>)</li>
                    <?php endfor ?>
                <?php endif ?>
            </ul>
        </div>
    </main>

    <footer>
        © ELTE IK Web programming 2021.1 - PHP Assignment | Maráki Deme (CRMAL9)
    </footer>
</body>

</html>