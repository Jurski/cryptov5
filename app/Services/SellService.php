<?php

namespace App\Services;

use App\Exceptions\InsufficientHoldingsException;
use App\Models\Cryptocurrency;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Repositories\WalletRepository;
use App\Repositories\TransactionRepository;
use InvalidArgumentException;

class SellService
{
    private WalletRepository $walletRepository;
    private TransactionRepository $transactionRepository;

    public function __construct(WalletRepository $walletRepository, TransactionRepository $transactionRepository)
    {
        $this->walletRepository = $walletRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function execute(int $walletId, Cryptocurrency $cryptocurrency, float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Non-negative number expected!');
        }

        $symbol = $cryptocurrency->getSymbol();
        $holdings = $this->walletRepository->getHoldings($walletId);

        $availableCrypto = $holdings[$symbol];

        if ($availableCrypto >= $amount) {
            $price = $cryptocurrency->getPrice();
            $sellAmount = $amount * $price;

            $newBalance = $this->walletRepository->getBalanceUsd($walletId) + $sellAmount;
            $this->walletRepository->updateBalance($walletId, $newBalance);

            $updatedAvailableCrypto = $availableCrypto - $amount;
            if ($updatedAvailableCrypto <= 0) {
                unset($holdings[$symbol]);
            } else {
                $holdings[$symbol] = $updatedAvailableCrypto;
            }

            $this->walletRepository->setHoldings($walletId, $holdings);

            $transaction = new Transaction(
                Carbon::now("UTC"),
                "sell",
                $amount,
                $symbol,
                $price
            );

            $this->transactionRepository->saveTransaction($transaction, $walletId);
        } else {
            throw new InsufficientHoldingsException();
        }
    }
}