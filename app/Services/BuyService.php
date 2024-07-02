<?php

namespace App\Services;

use App\Exceptions\InsufficientFundsException;
use App\Models\Cryptocurrency;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Repositories\WalletRepository;
use App\Repositories\TransactionRepository;
use InvalidArgumentException;

class BuyService
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
        $price = $cryptocurrency->getPrice();

        $totalCost = $amount * $price;

        if ($totalCost <= $this->walletRepository->getBalanceUsd($walletId)) {
            $newBalance = $this->walletRepository->getBalanceUsd($walletId) - $totalCost;

            $this->walletRepository->updateBalance($walletId, $newBalance);

            $currentHoldings = $this->walletRepository->getHoldings($walletId);

            if (isset($currentHoldings[$symbol])) {
                $currentHoldings[$symbol] += $amount;
            } else {
                $currentHoldings[$symbol] = $amount;
            }

            $this->walletRepository->setHoldings($walletId, $currentHoldings);

            $transaction = new Transaction(
                Carbon::now('UTC'),
                'purchase',
                $amount,
                $symbol,
                $price
            );

            $this->transactionRepository->saveTransaction($transaction, $walletId);

        } else {
            throw new InsufficientFundsException();
        }

    }
}