<?php
// ============================================================
//  PARKINGSURE — Modelo: Vehículo (completo)
// ============================================================

require_once __DIR__ . '/../config/database.php';

class VehiculoModel {

    private PDO $db;

    public function __construct($connection = null) {
        if ($connection) {
            $this->db = $connection;
        } else {
            $database = new Database();
            $this->db = $database->conectar();
        }
    }

    public function getAll(): array {
        $stmt = $this->db->query(
            'SELECT v.placa, v.marca, v.modelo, v.anio, v.color,
                    v.id_cliente,
                    u.nombre   AS nombre_cliente,
                    u.cedula   AS cedula_cliente,
                    u.telefono AS telefono_cliente
             FROM vehiculo v
             LEFT JOIN cliente c ON v.id_cliente = c.id_cliente
             LEFT JOIN users u ON c.cedula_users = u.cedula
             ORDER BY v.placa ASC'
        );
        return $stmt->fetchAll();
    }

    public function getTotalCount(): int {
        $stmt = $this->db->query('SELECT COUNT(*) as total FROM vehiculo');
        return (int)$stmt->fetch()['total'];
    }

    public function getAllPaginated(int $limit, int $offset): array {
        $stmt = $this->db->prepare(
            'SELECT v.placa, v.marca, v.modelo, v.anio, v.color,
                    v.id_cliente,
                    u.nombre   AS nombre_cliente,
                    u.cedula   AS cedula_cliente,
                    u.telefono AS telefono_cliente
             FROM vehiculo v
             LEFT JOIN cliente c ON v.id_cliente = c.id_cliente
             LEFT JOIN users u ON c.cedula_users = u.cedula
             ORDER BY v.placa ASC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByPlaca(string $placa): ?array {
        $stmt = $this->db->prepare(
            'SELECT v.placa, v.marca, v.modelo, v.anio, v.color, v.id_cliente,
                    u.nombre   AS nombre_cliente,
                    u.cedula   AS cedula_cliente,
                    u.telefono AS telefono_cliente,
                    u.correo   AS correo_cliente
             FROM vehiculo v
             LEFT JOIN cliente c ON v.id_cliente = c.id_cliente
             LEFT JOIN users u ON c.cedula_users = u.cedula
             WHERE v.placa = ?'
        );
        $stmt->execute([strtoupper(trim($placa))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function existePlaca(string $placa): bool {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM vehiculo WHERE placa = ?');
        $stmt->execute([strtoupper(trim($placa))]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare(
            'INSERT INTO vehiculo (placa, id_cliente, marca, modelo, anio, color)
             VALUES (:placa, :id_cliente, :marca, :modelo, :anio, :color)'
        );
        return $stmt->execute([
            ':placa'      => strtoupper(trim($data['placa'])),
            ':id_cliente' => (int) $data['id_cliente'],
            ':marca'      => trim($data['marca']  ?? '') ?: null,
            ':modelo'     => trim($data['modelo'] ?? '') ?: null,
            ':anio'       => !empty($data['anio']) ? (int)$data['anio'] : null,
            ':color'      => trim($data['color']  ?? '') ?: null,
        ]);
    }

    public function update(string $placa, array $data): bool {
        $stmt = $this->db->prepare(
            'UPDATE vehiculo SET marca=:marca, modelo=:modelo, anio=:anio, color=:color
             WHERE placa=:placa'
        );
        return $stmt->execute([
            ':marca'  => trim($data['marca']  ?? '') ?: null,
            ':modelo' => trim($data['modelo'] ?? '') ?: null,
            ':anio'   => !empty($data['anio']) ? (int)$data['anio'] : null,
            ':color'  => trim($data['color']  ?? '') ?: null,
            ':placa'  => strtoupper(trim($placa)),
        ]);
    }

    public function delete(string $placa): bool {
        $stmt = $this->db->prepare('DELETE FROM vehiculo WHERE placa=?');
        return $stmt->execute([strtoupper(trim($placa))]);
    }

    public function count(): int {
        return (int) $this->db->query('SELECT COUNT(*) FROM vehiculo')->fetchColumn();
    }
}