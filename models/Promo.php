<?php
require_once "database.php";

class Promo {
    private $db;

    // public function __construct() {
    //     $this->db = new Database();
    // }
    public function __construct() {
        $database = new Database();     // Esto crea el objeto de tu clase
        $this->db = $database->connect(); // Esto guarda el PDO
    }

    // public function getUserByUsername($usuario) {
    //     $query = $this->db->connect()->prepare("SELECT * FROM users WHERE usuario = :usuario");
    //     $query->execute(["usuario" => $usuario]);
    //     return $query->fetch(PDO::FETCH_ASSOC);
    // }
    // public function registerPromo($titulo, $patrocinador, $estado, $imagen, $descripcion) {
        
    //     $query = $this->db->connect()->prepare("INSERT INTO `promos`( `pro_nombre`, `pro_patrocinador`, `pro_estado`, `pro_descripcion`, `pro_imagen`) VALUES (:titulo,:patrocinador,:estado,:descripcion,:imagen)");
    //     return $query->execute([
    //         "titulo" => $titulo,
    //         "patrocinador" => $patrocinador,
    //         "estado" => $estado,
    //         "imagen" => $imagen,
    //         "descripcion" => $descripcion
    //     ]);
    // }

    public function registerPromo($titulo, $patrocinador, $estado, $imagen, $descripcion) {
        $query = $this->db->prepare("INSERT INTO `promos`(`pro_nombre`, `pro_patrocinador`, `pro_estado`, `pro_descripcion`, `pro_imagen`) 
                                     VALUES (:titulo, :patrocinador, :estado, :descripcion, :imagen)");
        return $query->execute([
            "titulo" => $titulo,
            "patrocinador" => $patrocinador,
            "estado" => $estado,
            "imagen" => $imagen,
            "descripcion" => $descripcion
        ]);
    }
    public function updatePromo($id, $titulo, $patrocinador, $estado, $imagen, $descripcion) {
      
        $query = $this->db->connect()->prepare(" UPDATE users 
        SET usuario = :usuario, 
            password = :password, 
            pro_nombre = :titulo, 
            pro_patrocinador = :patrocinador, 
            pro_estado = :estado, 
            pro_descripcion = :descripcion 
            pro_imagen=:imagen
        WHERE id = :id");
        return $query->execute([
            "id" => $id,
            "titulo" => $titulo,
            "patrocinador" => $patrocinador,
            "estado" => $estado,
            "imagen" => $imagen,
            "descripcion" => $descripcion
        ]);
    }

    public function getAllPromos() {
        $stmt = $this->db->prepare("SELECT * FROM promociones");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function enviarPromo($idPromo, $descripcion, $imagen): array {
        $clientes = $this->getClientes();

        $resultados = [];

        foreach ($clientes as $cliente) {
            $telefono = $cliente['cli_telefono'];


        if (preg_match('/^\d{10}$/', $telefono)) {
            $url = "https://multilicoreschapinero.com/sistema/services/enviarWhatsapp.php";
            
            $data = [
                'telefono' => $telefono,
                'texto' => "$descripcion",
                'imagen1' => "$imagen", // opcional
                'plantilla' => 'promocion'
            ];

            $data_json = json_encode($data);

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data_json,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer Multilicoreslicor25'
                ],
            ]);

            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            $resultados[] = [
                'cliente' => $cliente['cli_nombre'],
                'telefono' => $telefono,
                'imagen'=>$imagen,
                'resultado' => $error ?: $response
            ];
        }

        }

        return $resultados;
    }
    
    public function getClientes() {
    $stmt = $this->db->prepare("SELECT * FROM clientes ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}



?>