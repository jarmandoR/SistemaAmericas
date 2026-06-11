<?php
require_once "database.php";

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getUserByUsername($usuario) {
        $query = $this->db->connect()->prepare("SELECT * FROM users WHERE usuario = :usuario");
        $query->execute(["usuario" => $usuario]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    public function registerUser($usuario, $password, $email, $telefono, $direccion, $nombre, $rol = 1) {
        $query = $this->db->connect()->prepare("INSERT INTO users (usuario, password, email, telefono, direccion, nombre, rol) VALUES (:usuario, :password, :email, :telefono, :direccion, :nombre, :rol)");
        return $query->execute([
            "usuario" => $usuario,
            "password" => $password,
            "email" => $email,
            "telefono" => $telefono,
            "direccion" => $direccion,
            "nombre" => $nombre,
            "rol" => $rol
        ]);
    }
    public function updateUser($id, $usuario, $password, $email, $telefono, $direccion, $nombre, $rol = null) {
        $query = $this->db->connect()->prepare(" UPDATE users 
        SET usuario = :usuario, 
            password = :password, 
            email = :email, 
            telefono = :telefono, 
            direccion = :direccion, 
            nombre = :nombre,
            rol = :rol
        WHERE id = :id");
        return $query->execute([
            "id" => $id,
            "usuario" => $usuario,
            "password" => $password,
            "email" => $email,
            "telefono" => $telefono,
            "direccion" => $direccion,
            "nombre" => $nombre,
            "rol" => $rol ?? 1
        ]);
    }
}


?>
