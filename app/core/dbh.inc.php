<?php

class Database
{
    private static $instance = null;
    private $conn;

    // Private constructor = cannot be called outside
    private function __construct()
    {
        date_default_timezone_set('Africa/Cairo');

        $servername = "localhost";
        $username = "root";
        $password = "";
        $DB = "padelup";

        $this->conn = mysqli_connect($servername, $username, $password, $DB);

        if (!$this->conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserializing
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize a singleton.");
    }

    // The SINGLE access point
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Get the actual connection
    public function getConnection()
    {
        return $this->conn;
    }
}
