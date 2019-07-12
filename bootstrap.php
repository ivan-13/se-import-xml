<?php 
use Symfony\Component\Dotenv\Dotenv;

require_once "vendor/autoload.php";
$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

try {
    $dbh = new PDO($_ENV['DB_DRIVER']. ':host=' . $_ENV['DB_SERVER'] . ';dbname=' . $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}