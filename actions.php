<?php
require_once __DIR__ . "/init.php";
$actions = ["send", "unfriend", "cancel", "ignore", "accept"];
function redirect_to_home()
{
    header('Location: home.php');
    exit;
}

if (
    !isset($_SESSION['user_id']) ||
    !isset($_GET["id"]) ||
    !isset($_GET["action"]) ||
    !in_array($_GET["action"], $actions) ||
    !filter_var($_GET["id"], FILTER_VALIDATE_INT) ||
    $_GET["id"] == $_SESSION['user_id']
) redirect_to_home();

$action = $_GET["action"];
$User = new User();
$the_user = $User->find_by_id($_SESSION['user_id']);
$x_user = $User->find_by_id($_GET['id']);

if (!$the_user || !$x_user) redirect_to_home();
$my_id = $the_user->id;
$user_id = $x_user->id;
$Friend = new Friend();

if (
    $action === $actions[0] &&
    !($Friend->is_already_friends($my_id, $user_id) || $Friend->is_request_already_sent($my_id, $user_id))
) {

    $Friend->pending_friends($my_id, $user_id);
} elseif (
    in_array($action, [$actions[2], $actions[3]]) &&
    $Friend->is_request_already_sent($my_id, $user_id)
) {
    $Friend->cancel_or_ignore_friend_request($my_id, $user_id, $action);
} elseif (
    $action === $actions[4] &&
    !$Friend->is_already_friends($my_id, $user_id) &&
    $Friend->is_request_already_sent($my_id, $user_id)
) {
    $Friend->make_friends($my_id, $user_id);
} elseif (
    $action === $actions[1] &&
    $Friend->is_already_friends($my_id, $user_id)
) {
    $Friend->delete_friends($my_id, $user_id);
} else {
    redirect_to_home();
}
