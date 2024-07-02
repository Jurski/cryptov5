<?php

namespace App\Controllers;

use App\Response;

class ErrorController
{
    public function show(): Response
    {
        return new Response(
            'error.twig'
        );
    }
}
