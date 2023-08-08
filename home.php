<?php
require_once __DIR__ . "/init.php";
if (!isset($_SESSION['user_id'])) {
    header('Location: logout.php');
    exit;
}
$User = new User();
$the_user = $User->find_by_id($_SESSION['user_id']);
if (!$the_user) {
    header('Location: logout.php');
    exit;
}
require_once __DIR__ . "/nav.php";
$all_users = $User->find_all_except($the_user->id);

$Friend = new Friend();
$notification = $Friend->request_notification($the_user->id);
$total_friends = $Friend->get_all_friends($the_user->id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="./assets/style.css">
</head>

<body>

    <div class="container profile">
        <div class="profile">
            <div class="p_user d-flex">
                <img src="https://api.dicebear.com/6.x/bottts/png?seed=<?php echo $the_user->id; ?>" alt="<?php echo $the_user->name; ?>">
                <h2><?php echo $the_user->name; ?></h2>
                <?php the_nav($notification, $total_friends); ?>
            </div>
        </div>
        <?php if ($all_users && count($all_users)) : ?>
            <h2 class="p-title">👥 All Users</h2>
            <div class="all-users">
                <ul class="user-list d-flex">
                    <?php foreach ($all_users as $user) : ?>
                        <li><a href="./profile.php?id=<?php echo $user->id; ?>"><img src="https://api.dicebear.com/6.x/bottts/png?seed=<?php echo $user->id; ?>" alt=""><span><?php echo $user->name; ?></span></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>