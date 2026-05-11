<?php
// ============================================================
//  PARKINGSURE - Modelo: Cliente
//  Un cliente es un personal con rol CLIENTE
//  La tabla cliente apunta a personal (id_personal)
// ============================================================

// Línea 8: Incluye el archivo de configuración de base de datos
require_once __DIR__ . '/../config/database.php';

// Línea 10: Define la clase ClienteModel para gestionar operaciones CRUD de clientes
class ClienteModel {

    // Línea 12: Propiedad privada para almacenar la conexión PDO
    private PDO $db;

    // Línea 14-16: Constructor - inicializa la conexión a la base de datos
    public function __construct() {
        $this->db = getDB();  // Obtiene la instancia de conexión PDO
    }

    /** Lista todos los clientes con sus datos de personal. */
    public function getAll(): array {
        // Líneas 21-26: Ejecuta consulta SQL para obtener todos los clientes
        $stmt = $this->db->query(
            'SELECT c.id_cliente, p.id_personal, p.cedula, p.nombre,    // Datos del cliente y personal
                    p.telefono, p.correo, p.usuario                        // Contacto y usuario
             FROM cliente c                                               // Tabla principal de clientes
             JOIN personal p ON c.id_personal = p.id_personal              // Relación con tabla personal
             ORDER BY p.nombre ASC'                                       // Ordena por nombre ascendente
        );
        // Línea 27: Retorna todos los resultados como array asociativo
        return $stmt->fetchAll();
    }

    /** Busca cliente por cédula (para autocompletar en formulario de vehículo). */
    public function findByCedula(string $cedula): ?array {
        // Líneas 33-37: Prepara consulta SQL para buscar cliente por cédula
        $stmt = $this->db->prepare(
            'SELECT c.id_cliente, p.cedula, p.nombre, p.telefono, p.correo   // Datos del cliente
             FROM cliente c                                                   // Tabla clientes
             JOIN personal p ON c.id_personal = p.id_personal                  // JOIN con personal
             WHERE p.cedula = ?'                                               // Filtro por cédula
        );
        // Línea 38: Ejecuta la consulta con la cédula
        $stmt->execute([$cedula]);
        // Línea 39: Obtiene una sola fila del resultado
        $row = $stmt->fetch();
        // Línea 40: Retorna la fila encontrada o null si no existe
        return $row ?: null;
    }

    /** Busca cliente por id_cliente. */
    public function findById(int $id): ?array {
        // Líneas 45-49: Prepara consulta SQL para buscar cliente por ID
        $stmt = $this->db->prepare(
            'SELECT c.id_cliente, p.id_personal, p.cedula, p.nombre, p.telefono, p.correo
             FROM cliente c
             JOIN personal p ON c.id_personal = p.id_personal
             WHERE c.id_cliente = ?'
        );
        // Línea 50: Ejecuta la consulta con el ID
        $stmt->execute([$id]);
        // Línea 51: Obtiene una sola fila del resultado
        $row = $stmt->fetch();
        // Línea 52: Retorna la fila encontrada o null si no existe
        return $row ?: null;
    }

    /**
     * Crea un nuevo cliente (primero inserta en personal, luego en cliente).
     * Retorna el id_cliente creado.
     */
    public function create(array $data): int {
        // Línea 61: Inicia una transacción para asegurar consistencia
        $this->db->beginTransaction();
        try {
            //  PASO 1: Insertar en personal con rol CLIENTE
            //  -------------------------------------------
            // Línea 64: Genera hash de contraseña (usa cédula como default)
            $hash = password_hash($data['password'] ?? $data['cedula'], PASSWORD_BCRYPT);
            // Líneas 65-68: Prepara consulta para insertar en tabla personal
            $stmtP = $this->db->prepare(
                'INSERT INTO personal (rol, cedula, nombre, telefono, correo, usuario, password_hash)
                 VALUES ("CLIENTE", :cedula, :nombre, :telefono, :correo, :usuario, :password_hash)'
            );
            // Líneas 69-76: Ejecuta inserción con los datos procesados
            $stmtP->execute([
                ':cedula'        => trim($data['cedula']),                           // Cédula limpia
                ':nombre'        => trim($data['nombre']),                           // Nombre limpio
                ':telefono'      => trim($data['telefono'] ?? ''),                   // Teléfono o vacío
                ':correo'        => trim($data['correo']   ?? ''),                   // Correo o vacío
                ':usuario'       => trim($data['usuario']  ?? $data['cedula']),     // Usuario o cédula como default
                ':password_hash' => $hash,                                          // Hash de contraseña
            ]);
            // Línea 77: Obtiene el ID generado de personal
            $idPersonal = (int) $this->db->lastInsertId();

            //  PASO 2: Insertar en cliente
            //  -------------------------
            // Líneas 80-82: Prepara consulta para insertar en tabla cliente
            $stmtC = $this->db->prepare(
                'INSERT INTO cliente (id_personal) VALUES (?)'
            );
            // Línea 83: Ejecuta inserción con el ID de personal
            $stmtC->execute([$idPersonal]);
            // Línea 84: Obtiene el ID generado de cliente
            $idCliente = (int) $this->db->lastInsertId();

            // Línea 86: Confirma la transacción
            $this->db->commit();
            // Línea 87: Retorna el ID del cliente creado
            return $idCliente;
        } catch (Exception $e) {
            // Línea 89: Si hay error, revierte la transacción
            $this->db->rollBack();
            // Línea 90: Lanza la excepción para manejo externo
            throw $e;
        }
    }

    /** Actualiza datos del cliente (actualiza la tabla personal). */
    public function update(int $idCliente, array $data): bool {
        // Líneas 97-101: Prepara consulta SQL para actualizar datos del cliente
        $stmt = $this->db->prepare(
            'UPDATE personal p
             JOIN cliente c ON p.id_personal = c.id_personal
             SET p.nombre=:nombre, p.telefono=:telefono, p.correo=:correo
             WHERE c.id_cliente=:id'
        );
        // Líneas 102-105: Ejecuta actualización con los datos procesados
        return $stmt->execute([
            ':nombre'   => trim($data['nombre']),                           // Nombre limpio
            ':telefono' => trim($data['telefono'] ?? ''),                   // Teléfono o vacío
            ':correo'   => trim($data['correo']   ?? ''),                   // Correo o vacío
            ':id'       => $idCliente,                                      // ID del cliente
        ]);
    }

    /** Elimina un cliente y su entrada en personal. */
    public function delete(int $idCliente): bool {
        //  PASO 1: Obtener id_personal primero
        //  ---------------------------------
        // Líneas 113-117: Prepara consulta para obtener ID de personal
        $stmt = $this->db->prepare(
            'SELECT p.id_personal FROM personal p
             JOIN cliente c ON p.id_personal = c.id_personal
             WHERE c.id_cliente = ?'
        );
        // Línea 118: Ejecuta consulta con ID de cliente
        $stmt->execute([$idCliente]);
        // Línea 119: Obtiene el resultado
        $row = $stmt->fetch();
        // Línea 120: Si no encuentra el cliente, retorna false
        if (!$row) return false;

        //  PASO 2: Eliminar en transacción
        //  ------------------------------
        // Línea 122: Inicia transacción para eliminación segura
        $this->db->beginTransaction();
        try {
            // Línea 124: Elimina primero de la tabla cliente (sin constraint)
            $this->db->prepare('DELETE FROM cliente WHERE id_cliente=?')->execute([$idCliente]);
            // Línea 125: Luego elimina de la tabla personal
            $this->db->prepare('DELETE FROM personal WHERE id_personal=?')->execute([$row['id_personal']]);
            // Línea 126: Confirma la transacción
            $this->db->commit();
            // Línea 127: Retorna true si todo fue exitoso
            return true;
        } catch (Exception $e) {
            // Línea 129: Si hay error, revierte la transacción
            $this->db->rollBack();
            // Línea 130: Lanza la excepción para manejo externo
            throw $e;
        }
    }
}