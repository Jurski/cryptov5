<?php

namespace App\Models;

use JsonSerializable;

class Cryptocurrency implements jsonSerializable
{
    private string $name;
    private string $symbol;
    private float $price;

    public function __construct(string $name, string $symbol, float $price)
    {
        $this->name = $name;
        $this->symbol = $symbol;
        $this->price = $price;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'symbol' => $this->symbol,
            'price' => $this->price
        ];
    }
}