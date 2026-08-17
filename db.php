<?php
// Detect PDO drivers and create a PDO instance for the app.
// Supported drivers (in priority): sqlite, mysql.
$pdo = null;
if (class_exists('PDO')) {
    $drivers = PDO::getAvailableDrivers();
    try {
        if (in_array('sqlite', $drivers, true)) {
            $sqliteFile = __DIR__ . '/database.sqlite';
            $pdo = new PDO("sqlite:$sqliteFile");
        } elseif (in_array('mysql', $drivers, true)) {
            $host     = "sql212.infinityfree.com";
            $username = "if0_42638846";
            $password = "Ue0CSWBIHsrWFU";
            $database = "if0_42638846_shieldos";
            $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password);
        }
        if ($pdo) {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // If using SQLite, ensure required tables exist.
            if (in_array('sqlite', $drivers, true)) {
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
            }
        }
    } catch (PDOException $e) {
        // If PDO exists but connection failed, log and continue to error below.
        error_log('PDO connection error: ' . $e->getMessage());
        $pdo = null;
    }
}

if (!$pdo) {
    // Show a clear, actionable HTML error instead of letting PHP throw a fatal.
    http_response_code(500);
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Server configuration error</title></head><body style="font-family:system-ui,Segoe UI,Roboto,Helvetica,Arial,sans-serif;margin:40px">';
    echo '<h1>Server misconfiguration: PHP PDO driver not available</h1>';
    echo '<p>This application requires PHP with PDO and either the <strong>sqlite</strong> or <strong>mysql</strong> PDO driver enabled.</p>';
    echo '<p>To fix this on your server:</p><ul>';
    echo '<li>Enable the <code>pdo</code> extension and a driver (preferably <code>pdo_sqlite</code> for local use).</li>';
    echo '<li>On Debian/Ubuntu: <code>sudo apt install php-sqlite3 php-pdo</code> then restart Apache.</li>';
    echo '<li>On XAMPP: enable <code>extension=pdo_sqlite</code> in your <code>php.ini</code> and restart XAMPP.</li>';
    echo '</ul>';
    echo '<p>Server log (for administrators): check the PHP/Apache error log for details.</p>';
    echo '</body></html>';
    exit;
}

?>
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
<?php
// AwardSpace Database Connection Settings
$host     = 'fdb1030.awardspace.net';
$dbname   = '4783038_breachdetection';
$username = '4783038_breachdetection';
$password = 'Jude@39395527'; // Replace with the password you set for this database
$port     = 3306;

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>