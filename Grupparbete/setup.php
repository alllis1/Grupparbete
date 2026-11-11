<?php
require_once __DIR__ . '/include/bootstrap.php';

try {
    $pdo = getDB();

    
    $pdo->exec('PRAGMA foreign_keys = OFF;');

    
    $pdo->exec("DROP TABLE IF EXISTS favorites;");
    $pdo->exec("DROP TABLE IF EXISTS reviews;");
    $pdo->exec("DROP TABLE IF EXISTS places;");
    $pdo->exec("DROP TABLE IF EXISTS users;");
    $pdo->exec("DROP TABLE IF EXISTS weather_cache;");
    $pdo->exec("DROP TABLE IF EXISTS city;");
    $pdo->exec("DROP TABLE IF EXISTS category;");

    echo "Gamla tabeller raderade.
";

    
    $pdo->exec("
    CREATE TABLE users (
        user_id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_name TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'user' CHECK(role IN ('user', 'admin'))
    );
    ");
    echo "Tabell 'users' skapad.
";

    
    $pdo->exec("
    CREATE TABLE city (
        city_id INTEGER PRIMARY KEY AUTOINCREMENT,
        city_name TEXT NOT NULL UNIQUE
    );
    ");
    echo "Tabell 'city' skapad.
";

    
    $pdo->exec("
    CREATE TABLE category (
        category_id INTEGER PRIMARY KEY AUTOINCREMENT,
        type TEXT NOT NULL UNIQUE
    );
    ");
    echo "Tabell 'category' skapad.
";

    
    $pdo->exec("
    CREATE TABLE places (
        place_id INTEGER PRIMARY KEY AUTOINCREMENT,
        place_name TEXT NOT NULL,
        place_address TEXT,
        latitude REAL,
        longitude REAL,
        city_id INTEGER NOT NULL,
        category_id INTEGER,
        FOREIGN KEY(city_id) REFERENCES city(city_id) ON DELETE CASCADE,
        FOREIGN KEY(category_id) REFERENCES category(category_id) ON DELETE SET NULL
    );
    ");
    echo "Tabell 'places' skapad.
";

    
    $pdo->exec("
    CREATE TABLE reviews (
        review_id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        place_id INTEGER NOT NULL,
        rating INTEGER NOT NULL,
        review_text TEXT,
        image_path TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY(place_id) REFERENCES places(place_id) ON DELETE CASCADE
    );
    ");
    echo "Tabell 'reviews' skapad.
";

    
    $pdo->exec("
    CREATE TABLE favorites (
        user_id INTEGER NOT NULL,
        place_id INTEGER NOT NULL,
        PRIMARY KEY (user_id, place_id),
        FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY(place_id) REFERENCES places(place_id) ON DELETE CASCADE
    );
    ");
    echo "Tabell 'favorites' skapad.
";

    
    $pdo->exec("
    CREATE TABLE weather_cache (
        city TEXT PRIMARY KEY,
        data TEXT NOT NULL,
        fetched_at DATETIME NOT NULL
    );
    ");
    echo "Tabell 'weather_cache' skapad.
";

    
    $pdo->exec("INSERT INTO city (city_name) VALUES ('Stockholm'), ('Göteborg'), ('Malmö'), ('Uppsala');");
    echo "Grunddata för städer inlagd.
";
    $pdo->exec("INSERT INTO category (type) VALUES ('Restaurang'), ('Café'), ('Bar'), ('Okänd');");
    echo "Grunddata för kategorier inlagd.
";

    
    $pdo->exec('PRAGMA foreign_keys = ON;');

    echo "<h2>✅ Setup klar! Databasen är korrekt skapad.</h2>";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}