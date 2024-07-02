<?php

namespace App\Repositories\Cryptocurrency;

use App\Models\Cryptocurrency;

interface CryptocurrencyRepositoryInterface
{
    public function getTopCryptos(): array;

    public function getCryptoBySymbol(string $symbol): ?Cryptocurrency;
}