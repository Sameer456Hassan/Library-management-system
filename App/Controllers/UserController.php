<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\User;

class UserController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function index(Request $request, Response $response)
    {
        // Implement your user listing logic here
        // Make sure to handle authentication in the middleware

        $users = User::all();

        $response->getBody()->write(json_encode($users)); // Write JSON to the response body

        return $response->withHeader('Content-Type', 'application/json');
    }
}
