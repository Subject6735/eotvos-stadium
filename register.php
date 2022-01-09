<?php
include('storage.php');
include('auth.php');

function validate($post, &$data, &$errors)
{
    if (!isset($post['username']) || trim($post['username']) === '') $errors['username_err'] = 'Username is mandatory!';

    if (!isset($post['email']) || trim($post['email']) === '') $errors['email_err'] = 'Email is mandatory!';
    else if (!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) $errors['email_err'] = 'Invalid email!';

    if (!isset($post['password']) || trim($post['password']) === '') $errors['password_err'] = 'Password is mandatory!';

    if (!isset($post['pwrepeat']) || trim($post['pwrepeat']) === '') $errors['pwrepeat_err'] = 'You must repeat the password!';
    else if ($post['password'] !== $post['pwrepeat']) $errors['pwrepeat_err'] = 'Password must match!';

    $data = $post;

    return count($errors) === 0;
}

function redirect($page)
{
    header("Location: ${page}");
    exit();
}

$userStorage = new Storage(new JsonIO('json/users.json'));
$auth = new Auth($userStorage);
$data = [];
$errors = [];

if (count($_POST) > 0) {
    if (validate($_POST, $data, $errors)) {
        if ($auth->user_exists($data['username'])) {
            $errors['global'] = 'User already exists!';
        } else {
            $auth->register($data);
            redirect('login.php');
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
    <title>Register</title>
</head>

<body>
    <header>
        <div class="title"><a href="index.php">Eötvös Loránd Stadium</a></div>
    </header>
    <main>
        <h1>Register</h1>

        <form action="register.php" method="post" novalidate>
            <?php if (isset($errors['global'])) : ?>
                <span class="error"><?= $errors['global'] ?></span> <br>
            <?php endif ?>

            <div class="formdiv username">
                <span class="text">Username:</span> <input type="text" name="username" id="username" value="<?= $_POST['username'] ?? '' ?>">
                <?php if (isset($errors['username_err'])) : ?>
                    <span class="error"><?= $errors['username_err'] ?></span>
                <?php endif ?>
            </div>

            <div class="formdiv email">
                <span class="text">Email:</span> <input type="email" name="email" id="email" value="<?= $_POST['email'] ?? '' ?>">
                <?php if (isset($errors['email_err'])) : ?>
                    <span class="error"><?= $errors['email_err'] ?></span>
                <?php endif ?>
            </div>

            <div class="formdiv pw">
                <span class="text">Password:</span> <input type="text" name="password" id="password" value="<?= $_POST['password'] ?? '' ?>">
                <?php if (isset($errors['password_err'])) : ?>
                    <span class="error"><?= $errors['password_err'] ?></span>
                <?php endif ?>
            </div>

            <div class="formdiv pwrep">
                <span class="text">Confirm password:</span> <input type="text" name="pwrepeat" id="pwrepeat" value="<?= $_POST['pwrepeat'] ?? '' ?>">
                <?php if (isset($errors['pwrepeat_err'])) : ?>
                    <span class="error"><?= $errors['pwrepeat_err'] ?></span>
                <?php endif ?>
            </div>

            <button type="submit">Register</button>
        </form>
    </main>
    <footer>
        © ELTE IK Web programming 2021.1 - PHP Assignment | Maráki Deme (CRMAL9)
    </footer>
</body>

</html>