<?php
require_once "database.php";

class Promo {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function registerPromo($titulo, $descripcion, $codigo, $precioUnidad, $precioPaca, $actiUnidad, $imagen, $estado, $prioridad) {
        $query = $this->db->prepare("
            INSERT INTO promociones (
                titulo,
                descripcion,
                codigo,
                precio_unidad_producto,
                precio_paca_producto,
                acti_Unidad,
                imagen,
                estado,
                prioridad
            ) VALUES (
                :titulo,
                :descripcion,
                :codigo,
                :precio_unidad,
                :precio_paca,
                :acti_unidad,
                :imagen,
                :estado,
                :prioridad
            )
        ");

        return $query->execute([
            "titulo" => $titulo,
            "descripcion" => $descripcion,
            "codigo" => $codigo,
            "precio_unidad" => $precioUnidad,
            "precio_paca" => $precioPaca,
            "acti_unidad" => $actiUnidad,
            "imagen" => $imagen,
            "estado" => $estado,
            "prioridad" => $prioridad
        ]);
    }

    public function updatePromo($id, $titulo, $descripcion, $codigo, $precioUnidad, $precioPaca, $actiUnidad, $imagen, $estado, $prioridad) {
        $params = [
            "id" => $id,
            "titulo" => $titulo,
            "descripcion" => $descripcion,
            "codigo" => $codigo,
            "precio_unidad" => $precioUnidad,
            "precio_paca" => $precioPaca,
            "acti_unidad" => $actiUnidad,
            "estado" => $estado,
            "prioridad" => $prioridad
        ];

        $imagenSql = "";
        if (!empty($imagen)) {
            $imagenSql = ", imagen = :imagen";
            $params["imagen"] = $imagen;
        }

        $query = $this->db->prepare("
            UPDATE promociones
            SET titulo = :titulo,
                descripcion = :descripcion,
                codigo = :codigo,
                precio_unidad_producto = :precio_unidad,
                precio_paca_producto = :precio_paca,
                acti_Unidad = :acti_unidad,
                estado = :estado,
                prioridad = :prioridad
                $imagenSql
            WHERE id_promocion = :id
        ");

        return $query->execute($params);
    }

    public function deletePromo($id) {
        $query = $this->db->prepare("DELETE FROM promociones WHERE id_promocion = :id");
        return $query->execute(["id" => $id]);
    }

    public function updateEstadoPromo($id, $estado) {
        $query = $this->db->prepare("
            UPDATE promociones
            SET estado = :estado
            WHERE id_promocion = :id
        ");

        return $query->execute([
            "id" => $id,
            "estado" => $estado
        ]);
    }

    public function getAllPromos() {
        $stmt = $this->db->prepare("SELECT * FROM promociones ORDER BY prioridad DESC, creado_en DESC");
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
                    'imagen1' => "$imagen",
                    'plantilla' => 'promocion'
                ];

                $curl = curl_init();

                curl_setopt_array($curl, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($data),
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
                    'imagen' => $imagen,
                    'resultado' => $error ?: $response
                ];
            }
        }

        return $resultados;
    }

    public function getClientes() {
        $stmt = $this->db->prepare("SELECT * FROM clientes");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
