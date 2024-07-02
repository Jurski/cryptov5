<?php

namespace App;

use SQLite3;

class Database
{
    private static ?Database $instance = null;
    private SQLite3 $database;

    public function __construct()
    {
        $this->database = new SQLite3(__DIR__ . "/../storage/database.sqlite");
        $this->initializeDatabase();
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function prepare($query)
    {
        return $this->database->prepare($query);
    }

    private function initializeDatabase(): void
    {
        $this->database->exec("
            CREATE TABLE IF NOT EXISTS wallets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                balance_usd REAL,
                holdings TEXT,
                FOREIGN KEY(user_id) REFERENCES users(id)
            );
        ");

        $this->database->exec("
            CREATE TABLE IF NOT EXISTS transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                wallet_id INTEGER,
                date TEXT,
                type TEXT,
                amount REAL,
                cryptocurrency TEXT,
                purchase_price REAL,
                FOREIGN KEY(wallet_id) REFERENCES wallets(id)
            );
        ");
    }

    public function __destruct()
    {
        $this->database->close();
    }
}