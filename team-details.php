<?php
session_start();

include('storage.php');
include('auth.php');

$userStorage = new Storage(new JsonIO('json/users.json'));
$auth = new Auth($userStorage);

$teamStorage = new Storage(new JsonIO('json/teams.json'));
$teams = $teamStorage->findAll();

$matchStorage = new Storage(new JsonIO('json/matches.json'));
$matches = $matchStorage->findAll();

$commentStorage = new Storage(new JsonIO('json/comments.json'));
$comments = $commentStorage->findAll();

$currentTeam = $_GET['id'] ?? $teamStorage->findById($_GET['id']) ?? $teams[array_keys($teams)[0]];

if (isset($_GET['id'])) {
    $currentTeam = $teamStorage->findById($_GET['id']) ?? $teams[array_keys($teams)[0]];
} else {
    $currentTeam = $teams[array_keys($teams)[0]];
}

$currentMatches = array_filter($matches, fn ($m) => $m['home']['id'] === $currentTeam['id'] || $m['away']['id'] === $currentTeam['id']);
$currentComments = array_filter($comments, fn ($c) => $c['teamid'] === $currentTeam['id']);

function result($match, $currentTeam)
{
    if (!isset($match['home']['score']) || !isset($match['away']['score'])) {
        return null;
    }

    if ($match['home']['id'] === $currentTeam['id']) {
        if ($match['home']['score'] > $match['away']['score']) return 'won';
        else if ($match['home']['score'] === $match['away']['score']) return 'tie';
        else return 'lost';
    } else if ($match['away']['id'] === $currentTeam['id']) {
        if ($match['away']['score'] > $match['home']['score']) return 'won';
        else if ($match['away']['score'] === $match['home']['score']) return 'tie';
        else return 'lost.';
    }
}

$newcomment = [];
$newcommenterror = null;

if (isset($_POST['newcomment'])) {
    if (trim($_POST['newcomment']) === '') {
        $newcommenterror = 'Comment cannot be empty!';
    } else {
        $newcomment['author'] = $_SESSION['user']['username'];
        $newcomment['text'] = $_POST['newcomment'];
        $newcomment['teamid'] = $currentTeam['id'];
        $newcomment['date'] = date('Y.m.d H:i');

        $commentStorage = new Storage(new JsonIO('json/comments.json'));
        $commentStorage->add($newcomment);
        $comments = $commentStorage->findAll();
        $currentComments = array_filter($comments, fn ($c) => $c['teamid'] === $currentTeam['id']);
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/team-details.css">
    <title>Team details</title>
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
        <h1>Details for <span style="color: cyan">'<?= $currentTeam['name'] ?>'</span></h1>

        <div class="matches">
            <h2>Matches</h2>
            <ul>
                <?php foreach ($currentMatches as $match) : ?>
                    <li>
                        <div><span class="text">Match:</span> <?= $match['id'] ?></div>
                        <div style="color: <?= $teams[$match['home']['id']]['name'] === $currentTeam['name'] ? 'cyan' : '' ?>"><span class="text">Home team:</span> <?= $teams[$match['home']['id']]['name'] ?></div>
                        <div style="color: <?= $teams[$match['away']['id']]['name'] === $currentTeam['name'] ? 'cyan' : '' ?>"><span class="text">Away team:</span> <?= $teams[$match['away']['id']]['name'] ?></div>
                        <div><span class="text">Date:</span> <?= $match['date'] ?></div>

                        <div>
                            <span class="text">Result:</span>
                            <?php if (result($match, $currentTeam) === 'won') : ?>
                                <span class="won">Match won.
                                    <?php if ($teams[$match['home']['id']]['name'] === $currentTeam['name']) : ?>
                                        (<?= $match['home']['score'] ?> - <?= $match['away']['score'] ?>)
                                    <?php else : ?>
                                        (<?= $match['away']['score'] ?> - <?= $match['home']['score'] ?>)
                                    <?php endif ?>
                                </span>
                            <?php elseif (result($match, $currentTeam) === 'tie') : ?>
                                <span class="tie">Match was a tie. (<?= $match['home']['score'] ?> - <?= $match['away']['score'] ?>)</span>
                            <?php elseif (result($match, $currentTeam) === 'lost') : ?>
                                <span class="lost">Match lost.
                                    <?php if ($teams[$match['home']['id']]['name'] === $currentTeam['name']) : ?>
                                        (<?= $match['home']['score'] ?> - <?= $match['away']['score'] ?>)
                                    <?php else : ?>
                                        (<?= $match['away']['score'] ?> - <?= $match['home']['score'] ?>)
                                    <?php endif ?>
                                </span>
                            <?php else : ?>
                                <span>No result yet.</span>
                            <?php endif ?>
                        </div>

                        <div>
                            <?php if ($auth->authorize(["admin"])) : ?>
                                <a href="edit.php?matchid=<?= $match['id'] ?>&teamid=<?= $currentTeam['id'] ?>">Edit match</a>
                            <?php endif ?>
                        </div>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>

        <div class="comments">
            <h2>Comments</h2>
            <ul>
                <?php foreach ($currentComments as $comment) : ?>
                    <li>
                        <?= $comment['text'] ?> (by: '<?= $comment['author'] ?>', on <?= $comment['date'] ?>)
                        <?php if ($auth->authorize(["admin"])) : ?>
                            <a href="delete.php?commentid=<?= $comment['id'] ?>&teamid=<?= $currentTeam['id'] ?>">Delete</a>
                        <?php endif ?>
                    </li>
                <?php endforeach ?>
            </ul>

            <form action="" method="post" novalidate>
                <div class="formdiv comment">
                    <span class="text">Comment:</span> <input type="text" name="newcomment" id="newcomment">
                    <?php if ($newcommenterror !== null) : ?>
                        <span class="error"><?= $newcommenterror ?></span>
                    <?php endif ?>
                </div>

                <div class="formdiv button">
                    <button type="submit" <?= !$auth->is_authenticated() ? 'disabled' : '' ?>>Add comment</button>
                    <?php if (!$auth->is_authenticated()) : ?>
                        <span class="error">You must log in to leave a comment!</span>
                    <?php endif ?>
                </div>
            </form>
        </div>
    </main>
    <footer>
        © ELTE IK Web programming 2021.1 - PHP Assignment | Maráki Deme (CRMAL9)
    </footer>
</body>

</html>