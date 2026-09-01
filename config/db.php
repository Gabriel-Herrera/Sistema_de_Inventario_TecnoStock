<?php
/**
 * Conexión a la Base de Datos mediante PDO
 * Sistema de Inventario TecnoStock
 */

$host = '127.0.0.1';
$db   = 'tecnostock_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Si la BD no existe aún, intentar conectar sin dbname para sugerir o crear
    try {
        $dsnNoDb = "mysql:host=$host;charset=$charset";
        $pdoTemp = new PDO($dsnNoDb, $user, $pass, $options);
        // BD no creada aún
        die("Error de conexión: La base de datos '$db' no ha sido creada aún. Por favor, importa el archivo 'database/tecnostock.sql' en phpMyAdmin o MySQL Workbench.");
    } catch (PDOException $e2) {
        die("Error crítico de conexión a la base de datos MySQL: " . $e->getMessage());
    }
}
