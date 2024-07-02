<?php

namespace App\Exceptions;

class InsufficientFundsException extends \Exception {
    protected $message = "Insufficient funds!";
}