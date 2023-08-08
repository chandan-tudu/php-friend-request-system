<?php
require_once __DIR__ . "/init.php";
$result = NULL;
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
} else if (isset($_POST['email']) && isset($_POST['name']) && isset($_POST['password'])) {
    $User = new User();
    $result = $User->register($_POST['name'], $_POST['email'], $_POST['password']);
}
function old_val($field_name)
{
    global $result;
    if (isset($result["ok"]) && $result["ok"]) return "";
    if (isset($_POST[$field_name])) return htmlspecialchars($_POST[$field_name]);
    return "";
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="./assets/style.css">
</head>

<body>
    <div class="container form">
        <h1>Register</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" novalidate>
            <label for="name">Name: <span class="err-msg name"></span></label>
            <input class="form-input" type="text" name="name" id="name" placeholder="Your name" value="<?php echo old_val("name"); ?>">

            <label for="email">Email: <span class="err-msg email"></span></label>
            <input class="form-input" type="email" name="email" id="email" placeholder="Your email" value="<?php echo old_val("email"); ?>">

            <label for="pass">Password: <span class="err-msg password"></span></label>
            <input class="form-input" type="password" name="password" id="pass" placeholder="Password" value="<?php echo old_val("password"); ?>">
            <?php if (isset($result['ok']) && isset($result['message']) && $result['ok'] === 1) : ?>
                <p class="s-msg">✔️ <?php echo $result['message']; ?> You may <a href="./login.php">login</a> now.</p>
            <?php endif; ?>

            <input class="button" type="submit" value="Login">
        </form>
        <p class="link"><a href="./login.php">Login</a></p>
    </div>
    <?php if (isset($result["field_error"])) : ?>
        <script>
            let spanItem;
            let item;
            const errs = <?php echo json_encode($result["field_error"]); ?>;
            for (const property in errs) {
                spanItem = document.querySelector(`.err-msg.${property}`);
                item = document.querySelector(`[name="${property}"]`);
                item.classList.add('with-error');
                spanItem.innerText = errs[property];
            }
        </script>
    <?php endif; ?>
</body>

</html>