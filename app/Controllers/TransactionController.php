<?php

namespace App\Controllers;

use App\Database;
use App\Repositories\TransactionRepository;
use App\Response;

class TransactionController
{
    private Database $database;
    private TransactionRepository $transactionRepository;

    public function __construct()
    {
        $this->database = Database::getInstance();

        $this->transactionRepository = new TransactionRepository($this->database);
    }

    public function index(): Response
    {
        $transactions = $this->transactionRepository->loadTransactions(2);

        return new Response(
            'transactions-index.twig',
            ['transactions' => $transactions]
        );
    }
}
