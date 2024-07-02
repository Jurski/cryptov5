<?php

require "vendor/autoload.php";

use App\Database;
use App\Repositories\Cryptocurrency\CmcCryptocurrencyRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use App\Services\BuyService;
use App\Services\SellService;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

session_start();

$flashMessage = isset($_SESSION["flash_message"]) ? $_SESSION["flash_message"] : null;
$flashType = isset($_SESSION["flash_type"]) ? $_SESSION["flash_type"] : null;

if ($flashMessage !== null) {
    unset($_SESSION["flash_message"]);
    unset($_SESSION["flash_type"]);
}

$database = new Database();
$api = new CmcCryptocurrencyRepository();

$walletRepository = new WalletRepository($database);
$transactionRepository = new TransactionRepository($database);
$userRepository = new UserRepository($database);

$buyService = new BuyService($walletRepository, $transactionRepository);
$sellService = new SellService($walletRepository, $transactionRepository);


$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', ['App\Controllers\CryptocurrencyController', 'index']);
    $r->addRoute('GET', '/index', ['App\Controllers\CryptocurrencyController', 'index']);
    $r->addRoute('GET', '/cryptocurrencies/{symbol}', ['App\Controllers\CryptocurrencyController', 'show']);
    $r->addRoute('POST', '/cryptocurrencies/{symbol}/buy', ['App\Controllers\CryptocurrencyController', 'buy']);
    $r->addRoute('POST', '/cryptocurrencies/{symbol}/sell', ['App\Controllers\CryptocurrencyController', 'sell']);
    $r->addRoute('GET', '/transactions', ['App\Controllers\TransactionController', 'index']);
    $r->addRoute('GET', '/wallet', ['App\Controllers\WalletController', 'show']);
    $r->addRoute('GET', '/error', ['App\Controllers\ErrorController', 'show']);
});


$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        $_SESSION["flash_message"] = "Route not found";
        $_SESSION["flash_type"] = "danger";
        header("Location: /error");
        exit();
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        $_SESSION["flash_message"] = "Method on this route not allowed!";
        $_SESSION["flash_type"] = "danger";
        header("Location: /error");
        exit();
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        [$controller, $method] = $handler;

        $response = (new $controller)->{$method}(...array_values($vars));

        $loader = new FilesystemLoader('templates');

        $twig = new Environment($loader);

        try {
            echo $twig->render($response->getTemplate(), [
                "data" => $response->getData(),
                "session" => [
                    "flash_message" => $flashMessage,
                    "flash_type" => $flashType,
                ]
            ]);
        } catch (\Twig\Error\LoaderError|\Twig\Error\SyntaxError|\Twig\Error\RuntimeError $e) {
            echo "Error occured while loading template: " . $e->getMessage();
        }

        break;
}