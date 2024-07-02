<?php

namespace App\Exceptions;

class InsufficientHoldingsException extends \Exception {
    protected $message = "Insufficient holdings!";
}