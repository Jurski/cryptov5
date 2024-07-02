<?php

namespace App\Models;

class WalletState
{
    private string $cash;
    private string $totalBalance;
    private array $profits;
    private array $holdings;


   public function __construct(string $cash, string $totalBalance, array $profits, array $holdings) {
       $this->cash = $cash;
       $this->totalBalance = $totalBalance;
       $this->profits = $profits;
       $this->holdings = $holdings;
   }

   public function getCash(): string {
       return $this->cash;
   }

   public function getProfits(): array {
       return $this->profits;
   }

   public function getHoldings(): array {
       return $this->holdings;
   }

   public function getTotalBalance(): string {
       return $this->totalBalance;
   }
}