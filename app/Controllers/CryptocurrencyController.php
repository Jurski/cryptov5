<?php

namespace App\Controllers;

use App\Database;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\InsufficientHoldingsException;
use App\Models\Cryptocurrency;
use App\Repositories\Cryptocurrency\CmcCryptocurrencyRepository;
use App\Repositories\Cryptocurrency\CryptocurrencyRepositoryInterface;
use App\Repositories\TransactionRepository;
use App\Repositories\WalletRepository;
use App\Response;
use App\Services\BuyService;
use App\Services\SellService;
use InvalidArgumentException;

session_start();

class CryptocurrencyController
{
    private CryptocurrencyRepositoryInterface $api;
    private Database $database;
    private WalletRepository $walletRepository;
    private TransactionRepository $transactionRepository;
    private BuyService $buyService;
    private SellService $sellService;

    public function __construct()
    {
        $this->api = new CmcCryptocurrencyRepository();
        $this->database = Database::getInstance();

        $this->walletRepository = new WalletRepository($this->database);
        $this->transactionRepository = new TransactionRepository($this->database);
        $this->buyService = new BuyService(
            $this->walletRepository,
            $this->transactionRepository,
        );
        $this->sellService = new SellService(
            $this->walletRepository,
            $this->transactionRepository,
        );
    }

    public function index(): Response
    {
        $cryptocurrencies = ($this->api->getTopCryptos());
        return new Response(
            'index.twig',
            ['cryptocurrencies' => $cryptocurrencies]
        );
    }

    public function show(string $symbol): Response
    {
        $cryptocurrency = $this->api->getCryptoBySymbol($symbol);

        return new Response(
            'show.twig',
            ['cryptocurrency' => $cryptocurrency]
        );
    }

    public function buy(string $symbol): void
    {
        $amount = (float)$_POST['amount'];

        $result = $this->api->getCryptoBySymbol($symbol);

        $cryptocurrency = new Cryptocurrency(
            $result->getName(),
            $symbol,
            $result->getPrice()
        );

        try {
            $this->buyService->execute(
                2,
                $cryptocurrency,
                $amount
            );
            $_SESSION['flash_message'] = 'Cryptocurrency purchased successfully.';
            $_SESSION['flash_type'] = 'success';
        } catch (InvalidArgumentException|InsufficientFundsException $e) {
            $_SESSION['flash_message'] = $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }

        header('Location: /cryptocurrencies/' . $symbol);
    }

    public function sell(string $symbol): void
    {
        $amount = (float)$_POST['amount'];

        $result = $this->api->getCryptoBySymbol($symbol);

        $cryptocurrency = new Cryptocurrency(
            $result->getName(),
            $symbol,
            $result->getPrice()
        );

        try {
            $this->sellService->execute(
                2,
                $cryptocurrency,
                $amount
            );
            $_SESSION['flash_message'] = 'Cryptocurrency sold successfully.';
            $_SESSION['flash_type'] = 'success';
        } catch (InvalidArgumentException|InsufficientHoldingsException $e) {
            $_SESSION['flash_message'] = $e->getMessage();
            $_SESSION['flash_type'] = 'danger';
        }

        header('Location: /cryptocurrencies/' . $symbol);
    }
}
