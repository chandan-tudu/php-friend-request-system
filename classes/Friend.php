<?php
require_once __DIR__ . "/Database.php";
class Friend extends Database
{
    protected $conn;
    protected $my_id;
    protected $user_id;
    function __construct()
    {
        $this->conn = parent::__construct();
    }

    protected function id_validation(array $ids)
    {

        foreach ($ids as $val) {
            if (!filter_var($val, FILTER_VALIDATE_INT)) {
                return false;
            }
        }
        return true;
    }

    protected function run_query($condition, $full_query = false, $table_name = "friend_requests")
    {
        if (!$this->id_validation([$this->my_id, $this->user_id])) return false;

        try {
            $sql = "SELECT * FROM `$table_name` WHERE $condition";
            if ($full_query) $sql = $full_query;
            $query = $this->conn->query($sql);
            if ($query->rowCount() === 0) return null;
            return true;
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit;
        }
    }

    protected function profile_redirect()
    {
        header('Location: profile.php?id=' . $this->user_id);
        exit;
    }

    function is_already_friends($my_id, $user_id)
    {
        $this->my_id = $my_id;
        $this->user_id = $user_id;
        $result = $this->run_query("(`user_one` = '$my_id' AND `user_two` = '$user_id') OR (`user_one` = '$user_id' AND `user_two` = '$my_id')", false, "friends");
        return $result;
    }

    function am_i_the_req($_, $my_id, $user_id)
    {
        $this->my_id = $my_id;
        $this->user_id = $user_id;
        if ($_ === "sender") {
            return $this->run_query("`sender` = '$my_id' AND `receiver` = '$user_id'");
        } elseif ($_ === "receiver") {
            return $this->run_query("`sender` = '$user_id' AND `receiver` = '$my_id'");
        }
        echo "Parameter must be 'sender' or 'receiver'";
        exit;
    }

    function is_request_already_sent($my_id, $user_id)
    {
        try {
            $this->my_id = $my_id;
            $this->user_id = $user_id;
            return $this->run_query("(sender = '$my_id' AND receiver = '$user_id') OR (sender = '$user_id' AND receiver = '$my_id')");
        } catch (PDOException $e) {
            $e->getMessage();
            exit;
        }
    }

    function pending_friends($my_id, $user_id)
    {
        try {
            $this->my_id = $my_id;
            $this->user_id = $user_id;
            $sql = "INSERT INTO `friend_requests`(`sender`, `receiver`) VALUES('$my_id','$user_id')";
            $this->run_query(NULL, $sql);
            $this->profile_redirect();
        } catch (PDOException $e) {
            $e->getMessage();
            exit;
        }
    }

    function cancel_or_ignore_friend_request($my_id, $user_id, $action)
    {
        $this->my_id = $my_id;
        $this->user_id = $user_id;

        if ($action == "cancel") {
            $sql = "DELETE FROM `friend_requests` WHERE `sender` = '$my_id' AND `receiver` = '$user_id'";
        } elseif ($action == "ignore") {
            $sql = "DELETE FROM `friend_requests` WHERE `sender` = '$user_id' AND `receiver` = '$my_id'";
        } else {
            $sql = "DELETE FROM `friend_requests` WHERE (`sender` = '$my_id' AND `receiver` = '$user_id') OR (`sender` = '$user_id' AND `receiver` = '$my_id')";
        }
        try {
            $this->run_query(NULL, $sql);
            $this->profile_redirect();
        } catch (PDOException $e) {
            $e->getMessage();
            exit;
        }
    }

    function make_friends($my_id, $user_id)
    {
        $this->my_id = $my_id;
        $this->user_id = $user_id;

        try {
            $sql = "DELETE FROM `friend_requests` WHERE (`sender` = '$my_id' AND `receiver` = '$user_id') OR (`sender` = '$user_id' AND `receiver` = '$my_id')";
            $result = $this->run_query(NULL, $sql);
            if ($result) {
                $sql = "INSERT INTO `friends`(`user_one`, `user_two`) VALUES('$my_id', '$user_id')";
                $this->run_query(NULL, $sql);
            }
            $this->profile_redirect();
        } catch (PDOException $e) {
            $e->getMessage();
            exit;
        }
    }

    function delete_friends($my_id, $user_id)
    {
        $this->my_id = $my_id;
        $this->user_id = $user_id;

        try {
            $sql = "DELETE FROM `friends` WHERE (`user_one` = '$my_id' AND `user_two` = '$user_id') OR (`user_one` = '$user_id' AND `user_two` = '$my_id')";
            $this->run_query(NULL, $sql);
            $this->profile_redirect();
        } catch (PDOException $e) {
            $e->getMessage();
            exit;
        }
    }

    function request_notification($my_id, $return_data = false)
    {
        if (!$this->id_validation([$my_id])) return false;
        try {

            $sql = "SELECT `sender`, `name`, users.id as `u_id` FROM `friend_requests` JOIN `users` ON friend_requests.sender = users.id WHERE `receiver` = '$my_id'";

            $result = $this->conn->query($sql);

            if ($return_data) {
                return $result->fetchAll(PDO::FETCH_OBJ);
            }
            return $result->rowCount();
        } catch (PDOException $e) {
            $e->getMessage();
            exit;
        }
    }

    function get_all_friends($my_id, $return_data = false)
    {

        if (!$this->id_validation([$my_id])) return false;

        try {
            $sql = "SELECT * FROM `friends` WHERE `user_one` = '$my_id' OR user_two = '$my_id'";
            $result = $this->conn->query($sql);
            if ($return_data) {
                $ids = [];
                $data = $result->fetchAll(PDO::FETCH_OBJ);
                if (count($data) === 0) return [];
                foreach ($data as $row) {
                    if ($row->user_one == $my_id) {
                        $ids[] = $row->user_two;
                        continue;
                    }
                    $ids[] = $row->user_one;
                }

                $sql = "SELECT `id`, `name` FROM `users` WHERE `id` IN (" . implode(',', array_map('intval', $ids)) . ")";
                return $this->conn->query($sql)->fetchAll(PDO::FETCH_OBJ);
            }
            return $result->rowCount();
        } catch (PDOException $e) {
            $e->getMessage();
            exit;
        }
    }
}
