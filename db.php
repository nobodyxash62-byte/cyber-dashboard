<?php
// Local SQLite is used here by default for a self-contained auth store.
// This avoids HTTP 500 errors when the local MySQL service is not available.

try {
    $sqliteFile = __DIR__ . '/database.sqlite';
    $pdo = new PDO("sqlite:$sqliteFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create the users table automatically if it doesn't exist yet.
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS vault_entries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            label TEXT,
            password TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id)
        );"
    );
} catch (PDOException $e) {
    die("Database Connection Failure: " . $e->getMessage());
}
?>