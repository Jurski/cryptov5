<?php

namespace App\Models;

use JsonSerializable;
use App\Database;
use App\Repositories\WalletRepository;

class Wallet implements JsonSerializable
{
    private int $walletId;
    private int $userId;
    private Database $database;


    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }


    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }


    public function getWalletId(): int
    {
        return $this->walletId;
    }

    public function setWalletId(int $walletId): void
    {
        $this->walletId = $walletId;
    }

    public function jsonSerialize(): array
    {
        return [
            "walletId" => $this->walletId,
            "userId" => $this->userId,
        ];
    }

    public function loadWallet(int $userId): ?self
    {
        $walletRepository = new WalletRepository($this->database);
        return $walletRepository->loadWalletByUserId($userId);
    }
}