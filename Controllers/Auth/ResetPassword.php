<?php

class ResetPassword extends Connection
{
    private $status;
    private $message;

    public function __construct()
    {
        parent::__construct();
    }

    public function resetPassword($token)
    {
        try {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $userID = $this->connection->query("SELECT user_id from reset_passwords where token = '$token'");
            if ($userID->num_rows) {
                $result = $userID->fetch_assoc();
                $userID = $result['user_id'];
                $deleteToken = $this->connection->query("DELETE from reset_passwords where user_id = $userID");
                $updatePassword = $this->connection->query("UPDATE users set password = '$password' where id = $userID");
                $this->status = 200;
                $this->message = "Password change successully!";
            } else {
                throw new Exception ("Invalid user!", 400);
            }
        } catch (Exception $error) {
            $this->status = $error->getCode();
            $this->message = $error->getMessage();
            $errorMessage = "[ " . date("F j, Y, g:i a") . " ], file: " . basename($_SERVER['PHP_SELF']) . " Code: " . $this->status . ", error: " . $this->message . ", Line: " . $error->getLine() . PHP_EOL;
            $errorFile = fopen("./../errors.log", 'a');
            fwrite($errorFile, $errorMessage);
            fclose($errorFile);
        } finally {
            $response = [
                'status' => $this->status,
                'message' => $this->message
            ];
            header("content-type: application/json");
            return json_encode($response);
        }
    }
}

?>