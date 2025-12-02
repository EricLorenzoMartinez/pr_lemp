<?php
namespace App\DB;
use PDO;
use Exception;
use PDOException;

final class Database
{
    //Instància única de la classe
    private static ?Database $instance = null;

    //Objecte de connexió PDO
    private PDO $connection;

    //Constructor privat (patró Singleton)
    private function __construct() {
        //Configuració de la base de dades (dins del constructor)
        $host = '127.0.0.1';
        $dbName = 'mydatabase';
        $user = 'root';
        $password = 'rootpassword';

        try {
            $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
            $this->connection = new PDO($dsn, $user, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    // Evita la clonació de la instancia
    private function __clone() {}

    // Evita la deserialització
    public function __wakeup() {
        throw new Exception("No se puede deserializar un singleton.");
    }

    // Mètode públic per obtenir la instància única
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Mètode per obtenir la connexió PDO
    public function getConnection(): PDO {
        return $this->connection;
    }
}
