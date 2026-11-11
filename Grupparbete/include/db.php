<?php

global $pdo;

try {
    
    $dbFile = __DIR__ . '/../db/minsida.db';

    error_log("DB FILE USED: " . realpath($dbFile));

    
    if (!is_dir(dirname($dbFile))) {
        mkdir(dirname($dbFile), 0777, true);
    }
    
    
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA journal_mode=WAL;");
    $pdo->exec("PRAGMA foreign_keys = ON;");

} catch (PDOException $e) {
    
    die("Kunde inte ansluta till databasen: " . $e->getMessage());
}


function getDB(): PDO {
    global $pdo;
    return $pdo;
}


function connect_to_db(): PDO {
    
    return getDB();
}