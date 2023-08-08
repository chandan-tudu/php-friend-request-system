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
if (!isset($_GET['id']) || $_GET['id'] === $the_user->id) {
    header('Location: home.php');
    exit;
}
$x_user = $User->find_by_id($_GET['id']);
if (!$x_user) {
    header('Location: home.php');
    exit;
}
require_once __DIR__ . "/nav.php";

$Friend = new Friend();

$notification = $Friend->request_notification($the_user->id);
$total_friends = $Friend->get_all_friends($the_user->id);

$req_receiver = $Friend->am_i_the_req("receiver", $the_user->id, $x_user->id);
$req_sender = $Friend->am_i_the_req("sender", $the_user->id, $x_user->id);
$already_friends = $Friend->is_already_friends($the_user->id, $x_user->id);
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
                <img src="https://api.dicebear.com/6.x/bottts/png?seed=<?php echo $x_user->id; ?>" alt="<?php echo $the_user->name; ?>">
                <h2><?php echo $x_user->name; ?></h2>
                <?php the_nav($notification, $total_friends); ?>
            </div>
        </div>
        <div class="actions">
            <?php
            $id = $x_user->id;
            if ($already_friends) {
                echo '<a href="./actions.php?id=' . $id . '&action=unfriend" class="btn btn-2">Unfriend</a>';
            } elseif ($req_sender) {
                echo '<a href="./actions.php?id=' . $id . '&action=cancel" class="btn btn-2">Cancel Request</a>';
            } elseif ($req_receiver) {
                echo '<a href="./actions.php?id=' . $id . '&action=ignore" class="btn btn-2">Ignore</a> &nbsp;<a href="./actions.php?id=' . $id . '&action=accept" class="btn btn-1">Accept</a>';
            } else {
                echo '<a href="./actions.php?id=' . $id . '&action=send" class="btn btn-1">Send Request</a>';
            }
            ?>
        </div>
    </div>
</body>

</html>