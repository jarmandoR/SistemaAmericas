<?php
class Database {
    private $host = "localhost";
    private $db_name = "u885436177_americas";
    private $username = "u885436177_americas";
    private $password = "Americas2O2G";
    private $charset = "utf8mb4";

    // private $host = "localhost";
    // private $db_name = "multilicores";
    // private $username = "root";
    // private $password = "";
    // private $charset = "utf8mb4";

    public function connect() {
        try {
            $connection = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
            return new PDO($connection, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
?>