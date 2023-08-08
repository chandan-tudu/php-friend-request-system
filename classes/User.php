<?php
require_once __DIR__ . "/Database.php";
class User extends Database
{
    protected $conn;
    function __construct()
    {
        $this->conn = parent::__construct();
    }

    protected function is_valid_email($email)
    {
        return (!preg_match(
            "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^",
            $email
        )) ? FALSE : TRUE;
    }

    protected function field_validation(array $fields)
    {
        $empty_fields = [];
        foreach ($fields as $key => $val) {
            if (empty($val)) {
                $empty_fields[$key] = "Must not be empty.";
            } elseif ($key === "email" && !$this->is_valid_email($val)) {
                $empty_fields[$key] = "Invalid email address";
            }
        }

        if (count($empty_fields) && (count($empty_fields) <= count($fields))) {
            return [
                "ok" => 0,
                "field_error" => $empty_fields
            ];
        }
        return false;
    }

    function register($name, $email, $password)
    {
        $data = [];
        $data["name"] = trim(htmlspecialchars($name));
        $data["email"] = trim($email);
        $data["password"] = trim($password);

        $errors = $this->field_validation($data);
        if ($errors) return $errors;

        try {
            $email = $data["email"];

            $query = $this->conn->query("SELECT * FROM `users` WHERE `email`= '$email'");

            if ($query->rowCount() !== 0) {
                return [
                    "ok" => 0,
                    "field_error" => ["email" => "This Email is already registered."]
                ];
            }

            $password = password_hash($data["password"], PASSWORD_DEFAULT);
            $sql = "INSERT INTO `users` (`name`, `email`, `password`) VALUES(?,?,?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(1, $data["name"], PDO::PARAM_STR);
            $stmt->bindParam(2, $data["email"], PDO::PARAM_STR);
            $stmt->bindParam(3, $password, PDO::PARAM_STR);
            $stmt->execute();
            return [
                "ok" => 1,
                "message" => "You have been registered successfully."
            ];
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit;
        }
    }

    function login($email, $password)
    {
        $data = [];
        $data["email"] = trim($email);
        $data["password"] = trim($password);

        $errors = $this->field_validation($data);
        if ($errors) return $errors;

        try {
            $query = $this->conn->query("SELECT * FROM `users` WHERE `email`= '{$data["email"]}'");

            if ($query->rowCount() === 0) {
                return [
                    "ok" => 0,
                    "field_error" => ["email" => "This email is not registered."]
                ];
            }
            $user = $query->fetch(PDO::FETCH_ASSOC);
            $password_match = password_verify($data["password"], $user['password']);
            if (!$password_match) {
                return [
                    "ok" => 0,
                    "field_error" => ["password" => "Incorrect Password."]
                ];
            }
            $_SESSION['user_id'] = $user["id"];
            header("Location: home.php");
            exit;
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit;
        }
    }

    function find_by_id($id)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            return false;
        }
        try {
            $query = $this->conn->query("SELECT `id`, `name`, `email` FROM `users` WHERE `id`= '$id'");
            if ($query->rowCount() === 0) return null;
            return $query->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit;
        }
    }

    function find_all_except($id)
    {
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            return false;
        }

        try {
            $query = $this->conn->query("SELECT `id`, `name`, `email` FROM `users` WHERE `id` != '$id' ORDER BY `id` DESC");
            if ($query->rowCount() === 0) return null;
            return $query->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit;
        }
    }
}
