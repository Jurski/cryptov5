<?php

namespace App\Controllers;

use App\Database;
use App\Models\WalletState;
use App\Repositories\Cryptocurrency\CmcCryptocurrencyRepository;
use App\Repositories\Cryptocurrency\CryptocurrencyRepositoryInterface;
use App\Repositories\TransactionRepository;
use App\Repositories\WalletRepository;
use App\Response;

class WalletController
{
    private Database $database;
    private CryptocurrencyRepositoryInterface $api;
    private WalletRepository $walletRepository;
    private TransactionRepository $transactionRepository;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->api = new CmcCryptocurrencyRepository();
        $this->walletRepository = new WalletRepository($this->database);
        $this->transactionRepository = new TransactionRepository($this->database);
    }

    public function show(): Response
    {

        $holdings = $this->walletRepository->getHoldings(2);

        $totalCurrentValue = 0;
        $profits = [];

        $transactions = $this->transactionRepository->loadTransactions(2);

        foreach ($holdings as $symbol => $amount) {
            $currentValue = $this->calculateCryptocurrencyValue($symbol, $amount);
            $totalCurrentValue += $currentValue;

            $relevantTransactions = $this->getRelevantTransactions($transactions, $symbol);

            $sumPurchasePrice = 0;

            foreach ($relevantTransactions as $relevantTransaction) {
                $purchasePrice = $relevantTransaction->getPurchasePrice();
                $sumPurchasePrice += $purchasePrice;
            }

            $averagePurchasePrice = count($relevantTransactions) > 0 ? $sumPurchasePrice / count($relevantTransactions) : 0;

            $purchaseValue = $averagePurchasePrice * $amount;


            $profit = $currentValue - $purchaseValue;

            $profits[$symbol] = number_format($profit, 8);
        }

        $cash = $this->walletRepository->getBalanceUsd(2);
        $totalBalance = $cash + $totalCurrentValue;


        $cashFormatted = number_format($cash, 2);
        $totalBalanceFormatted = number_format($totalBalance, 2);

        $walletState = new WalletState($cashFormatted, $totalBalanceFormatted, $profits, $holdings);

        return new Response(
            'wallet-state.twig',
            [
                'walletState' => $walletState
            ]
        );
    }

    private function getRelevantTransactions(array $transactions, string $symbol): array
    {
        $filteredTransactions = [];

        foreach ($transactions as $transaction) {
            if ($transaction->getCryptocurrency() === $symbol && $transaction->getType() === 'purchase') {
                $filteredTransactions[] = $transaction;
            }
        }
        return $filteredTransactions;
    }

    private function calculateCryptocurrencyValue(string $symbol, float $amount): float
    {
        $cryptocurrency = $this->api->getCryptoBySymbol($symbol);
        $price = $cryptocurrency->getPrice();
        return $price * $amount;
    }
}
