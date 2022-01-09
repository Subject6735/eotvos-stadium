<?php
session_start();
include('storage.php');

if ((isset($_SESSION['user']) && $_SESSION['user']['username'] !== 'admin') || !isset($_SESSION['user'])) {
    echo 'Unauthorized access!';
    exit();
}

$matchStorage = new Storage(new JsonIO('json/matches.json'));
$matches = $matchStorage->findAll();

$currentMatch = null;
$currentID = '';

$teamID = $_GET['teamid'];

if (isset($_GET['matchid'])) {
    $currentID = $_GET['matchid'];
    $currentMatch = $matchStorage->findById($currentID) ?? $matches[array_keys($matches)[0]];
} else {
    $currentID = $matches[array_keys($matches)[0]]['id'];
    $currentMatch = $matches[array_keys($matches)[0]];
}

function redirect($page)
{
    header("Location: ${page}");
    exit();
}

function validate($post, &$data, &$errors)
{
    //if (!isset($post['homeid']) || trim($post['homeid']) === '') $errors['homeid_err'] = 'Home team ID is mandatory!';
    if ((isset($post['homescore']) && trim($post['homescore']) !== '') && (!filter_var($post['homescore'], FILTER_VALIDATE_INT) || intval($post['homescore']) < 0)) 'Home team score must be an integer greater than 0 or equal to 0.';

    //if (!isset($post['awayid']) || trim($post['awayid']) === '') $errors['awayid_err'] = 'Away team ID is mandatory!';
    if ((isset($post['awayscore']) && trim($post['awayscore']) !== '') && (!filter_var($post['awayscore'], FILTER_VALIDATE_INT) || intval($post['awayscore']) < 0)) 'Away team score must be an integer greater than 0 or equal to 0.';

    if (!isset($post['date']) || trim($post['date']) === '') $errors['date_err'] = 'Date is mandatory!';

    $data = $post;

    return count($errors) === 0;
}

$data = [];
$errors = [];

if ($_POST) {
    if (validate($_POST, $data, $errors)) {
        $newdata;

        if (trim($_POST['homescore']) === '' || trim($_POST['awayscore']) === '') {
            $newdata = [
                'id' => $currentID,
                'home' => [
                    'id' => $currentMatch['home']['id'] // $data['homeid']
                ],
                'away' => [
                    'id' => $currentMatch['away']['id'] //$data['awayid']
                ],
                'date' => $data['date']
            ];
        } else {
            $newdata = [
                'id' => $currentID,
                'home' => [
                    'id' => $currentMatch['home']['id'],
                    'score' => intval($data['homescore'])
                ],
                'away' => [
                    'id' => $currentMatch['away']['id'],
                    'score' => intval($data['awayscore'])
                ],
                'date' => $data['date']
            ];
        }

        if (isset($_POST['delete'])) {
            unset($currentMatch['home']['score']);
            unset($currentMatch['away']['score']);
            $matchStorage->update($currentID, $currentMatch);
            redirect('team-details.php?id=' . $teamID);
        } else if (isset($_POST['edit'])) {
            $matchStorage->update($currentID, $newdata);
            redirect('team-details.php?id=' . $teamID);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/form.css">
    <title>Edit match</title>
</head>

<body>
    <header>
        Eötvös Loránd Stadium
    </header>
    <main>
        <h1>Edit match</h1>
        <form action="" method="post" novalidate>
            <h2>Home team</h2>

            <div class="formdiv homescore">
                <span class="text">Score:</span> <input type="text" name="homescore" id="homescore" value="<?= $currentMatch['home']['score'] ?? $_POST['home']['score'] ?? '' ?>">
                <?php if (isset($errors['homescore_err'])) : ?>
                    <span class="error"><?= $errors['homescore_err'] ?></span>
                <?php endif ?>
            </div>

            <h2>Away team</h2> <br>

            <div class="formdiv awayscore">
                <span class="text">Score:</span> <input type="text" name="awayscore" id="awayscore" value="<?= $currentMatch['away']['score'] ?? $_POST['away']['score'] ?? '' ?>">
                <?php if (isset($errors['awayscore_err'])) : ?>
                    <span class="error"><?= $errors['awayscore_err'] ?></span>
                <?php endif ?>
            </div>

            <h2>Date</h2>

            <div class="formdiv date">
                <span class="text">Date:</span> <input type="text" name="date" id="date" value="<?= $currentMatch['date'] ?? $_POST['date'] ?? '' ?>">
                <?php if (isset($errors['date_err'])) : ?>
                    <span class="error"><?= $errors['date_err'] ?></span>
                <?php endif ?>
            </div>

            <button type="submit" name="delete">Delete result</button>
            <button type="submit" name="edit">Save changes</button>
        </form>
    </main>
    <footer>
        © ELTE IK Web programming 2021.1 - PHP Assignment | Maráki Deme (CRMAL9)
    </footer>
</body>

</html>