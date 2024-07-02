<?php

namespace App\Repositories;

use App\Database;
use App\Models\Wallet;


class WalletRepository
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function loadWalletByUserId(int $userId): ?Wallet
    {
        $stmt = $this->database->prepare("SELECT * FROM wallets WHERE user_id = :userId");
        $stmt->bindValue(':userId', $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();

        $walletData = $result->fetchArray(SQLITE3_ASSOC);

        if (!$walletData) {
            return null;
        }

        $wallet = new Wallet($this->database);
        $wallet->setWalletId($walletData["id"]);
        $wallet->setUserId($walletData["user_id"]);

        return $wallet;
    }

    public function createWalletForDB(int $userId, float $balance = 1000, array $holdings = []): void
    {
        if ($this->loadWalletByUserId($userId) !== null) {
            return;
        }

        $stmt = $this->database->prepare("INSERT INTO wallets (user_id, balance_usd, holdings)
                                    VALUES (:userId, :balanceUsd, :holdings)");

        $stmt->bindValue(':userId', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':balanceUsd', $balance, SQLITE3_FLOAT);
        $stmt->bindValue(':holdings', json_encode($holdings), SQLITE3_TEXT);

        $stmt->execute();
    }

    public function updateWallet(int $walletId, float $balanceUsd, array $holdings): void
    {
        $query = "UPDATE wallets SET balance_usd = :balanceUsd, holdings = :holdings WHERE id = :walletId";
        $stmt = $this->database->prepare($query);

        $stmt->bindValue(':walletId', $walletId, SQLITE3_INTEGER);
        $stmt->bindValue(':balanceUsd', $balanceUsd, SQLITE3_FLOAT);
        $stmt->bindValue(':holdings', json_encode($holdings), SQLITE3_TEXT);

        $stmt->execute();
    }

    public function updateBalance(int $walletId, float $balanceUsd): void {
        $query = "UPDATE wallets SET balance_usd = :balanceUsd WHERE id = :walletId";
        $stmt = $this->database->prepare($query);

        $stmt->bindValue(':walletId', $walletId, SQLITE3_INTEGER);
        $stmt->bindValue(':balanceUsd', $balanceUsd, SQLITE3_FLOAT);

        $stmt->execute();
    }

    public function setHoldings(int $walletId, array $holdings): void {
        $query = "UPDATE wallets SET holdings = :holdings WHERE id = :walletId";

        $stmt = $this->database->prepare($query);
        $stmt->bindValue(':walletId', $walletId, SQLITE3_INTEGER);
        $stmt->bindValue(':holdings', json_encode($holdings), SQLITE3_TEXT);

        $stmt->execute();
    }

    public function getBalanceUsd(int $walletId): float
    {
        $stmt = $this->database->prepare("SELECT balance_usd FROM wallets WHERE id = :walletId");
        $stmt->bindValue(':walletId', $walletId, SQLITE3_INTEGER);
        $result = $stmt->execute();

        $row = $result->fetchArray(SQLITE3_ASSOC);

        return $row['balance_usd'] ?? 0.0;
    }

    public function getHoldings(int $walletId): array
    {
        $stmt = $this->database->prepare("SELECT holdings FROM wallets WHERE id = :walletId");
        $stmt->bindValue(':walletId', $walletId, SQLITE3_INTEGER);
        $result = $stmt->execute();

        $row = $result->fetchArray(SQLITE3_ASSOC);

        return json_decode($row['holdings'] ?? '[]', true);
    }


}
