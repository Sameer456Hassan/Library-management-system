<?php
// HomeController.php

namespace App\Controllers;

use Slim\Views\Twig;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Response;
use Firebase\JWT\JWT;
use App\Models\Book; // Assuming your Book model namespace

class HomeController
{
    private $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    public function index(Request $request, Response $response)
    {
        // Check if the token is present in local storage
        $token = $_COOKIE['token'] ?? null;

        if ($this->validateBearerToken($token)) {
            // Render the dashboard.twig template
            return $response->withHeader('Location', '/dashboard')->withStatus(302);

        } else {
            // Redirect to the login page if the token is not present or not valid
            return $this->view->render($response, 'login.twig');
        }
        // Render the index.twig template
    }

    public function signup(Request $request, Response $response)
    {
        // Render the index.twig template
        return $this->view->render($response, 'signup.twig');
    }



    public function dashboard(Request $request, Response $response)
    {
        // Check if the token is present in local storage
        $token = $_COOKIE['token'] ?? null;

        if ($this->validateBearerToken($token)) {

            // Render the dashboard.twig template with the books data
            return $this->view->render($response, 'dashboard.twig');
        } else {
            // Redirect to the login page if the token is not present or not valid
            return $response->withHeader('Location', '/')->withStatus(302);
        }
    }

    public function authors(Request $request, Response $response)
    {
        // Check if the token is present in local storage
        $token = $_COOKIE['token'] ?? null;

        if ($this->validateBearerToken($token)) {

            // Render the dashboard.twig template with the books data
            return $this->view->render($response, 'authors.twig');
        } else {
            // Redirect to the login page if the token is not present or not valid
            return $response->withHeader('Location', '/')->withStatus(302);
        }
    }

    private function validateBearerToken($token)
    {
        try {
            $secretKey = 'your-secret-key'; // Replace with your actual secret key

            // Decode the token to get the payload
            $decoded = JWT::decode($token, $secretKey, ['HS256']);

            // If decoding is successful, the token is valid
            return true;
        } catch (\Firebase\JWT\ExpiredException $e) {
            // Handle token expiration exception
            return false;
        } catch (\Firebase\JWT\BeforeValidException $e) {
            // Handle token not yet valid exception
            return false;
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            // Handle invalid token signature exception
            return false;
        } catch (\Firebase\JWT\UnexpectedValueException $e) {
            // Handle other unexpected value exceptions
            return false;
        } catch (\Exception $e) {
            // Handle generic exceptions
            return false;
        }
    }
    public function logout(Request $request, Response $response)
    {
        // Delete the token cookie
        $response = $response->withHeader('Set-Cookie', 'token=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT; HttpOnly; SameSite=Strict');

        // Redirect to the login page
        return $response->withHeader('Location', '/')->withStatus(302);
    }
}
