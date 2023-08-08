<?php
function the_nav(int $notification = 0, int $total_friends = 0, $page = null)
{
    $n_badge = $notification ? '<span class="badge active">' . $notification . '</span>' : '<span class="badge">' . $notification . '</span>';
    $f_badge = '<span class="badge">' . $total_friends . '</span>';
?>
    <ul class="p_list d-flex">
        <li><?php echo ($page == "home") ? '<span>Home</span>' : '<a href="./home.php" class="active">Home</a>'; ?></li>

        <li><?php echo ($page == "info") ? "<span>Notification $n_badge</span>" : '<a href="./notifications.php">Notification ' . $n_badge . '</a>'; ?></li>

        <li><?php echo ($page == "frnd") ? "<span>Friends $f_badge</span>" : '<a href="./friends.php" class="active">Friends ' . $f_badge . '</a>'; ?></li>
        <li><a href="./logout.php">Logout</a></li>
    </ul>
<?php } ?>