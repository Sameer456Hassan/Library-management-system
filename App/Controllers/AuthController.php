<?php

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Response;
use Firebase\JWT\JWT;
use App\Models\User;
use PasswordCompat\PasswordCompat;


class AuthController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function customAuth(Request $request, RequestHandler $handler): Response
    {
        $apiToken = $request->getHeaderLine('X-Api-Token');
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse();

        if ($apiToken !== 'your-secret-token') {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }

    public function basicAuth(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        $credentials = base64_decode(substr($authHeader, 6));
        list($email, $password) = explode(':', $credentials);

        if ($this->authenticateUser($email, $password)) {
            // If authentication succeeds, proceed with the request
            $response = $handler->handle($request);
            return $response;
        }

        // If authentication fails, return an unauthorized response
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse();
        $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }


    // Change this line
    public function bearerAuth(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        $token = substr($authHeader, 7);

        if ($this->validateBearerToken($token)) {
            // If $next is a Slim route, you can use it as a closure
            // If authentication succeeds, proceed with the request
            $response = $handler->handle($request);
            return $response;
        }

        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse();

        $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }



    public function login(Request $request, Response $response)
    {
        try {
            $data = $request->getParsedBody();
            $email = $data['email'];
            $password = $data['password'];

            if ($this->authenticateUser($email, $password)) {
                $secretKey = 'your-secret-key';
                $tokenPayload = [
                    'email' => $email,
                ];
                $token = JWT::encode($tokenPayload, $secretKey, 'HS256');

                $response = $response->withHeader('Content-Type', 'application/json');
                $response->getBody()->write(json_encode(['token' => $token]));

                return $response;
            } else {
                $response = $response->withStatus(401)->withHeader('Content-Type', 'application/json');
                $response->getBody()->write(json_encode(['error' => 'Invalid credentials']));

                return $response;
            }
        } catch (\Exception $e) {
            $response = $response->withStatus(500)->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));

            return $response;
        }
    }
    public function signup(Request $request, Response $response)
    {
        try {
            // Get user input from the request
            $data = $request->getParsedBody();
            $username = $data['username'];
            $role = $data['role'];
            $email = $data['email'];
            $password = $data['password'];

            // Check if the user already exists
            $existingUser = User::where('email', $email)->first();
            if ($existingUser) {
                $response = $response->withStatus(400)->withHeader('Content-Type', 'application/json');
                $response->getBody()->write(json_encode(['error' => 'User already exists']));
                return $response;
            }

            // Create a new user
            $user = new User();
            $user->name = $username;
            $user->role = $role;
            $user->email = $email;
            $user->password = $password; // Note: In a real application, you should hash and salt the password

            // Save the user to the database
            $user->save();

            // Generate and return a JWT token for the new user
            $secretKey = 'your-secret-key';
            $tokenPayload = [
                'email' => $email,
            ];
            $token = JWT::encode($tokenPayload, $secretKey, 'HS256');

            $response = $response->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode(['token' => $token]));

            return $response;
        } catch (\Exception $e) {
            $response = $response->withStatus(500)->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));

            return $response;
        }
    }



    private function authenticateUser($username, $password)
    {
        try {
            // Find the user by the given username
            $user = User::where('email', $username)->first();

            // Check if the user exists and the password is correct
            if ($user && $user->password === $password) {
                return true;
            }
        } catch (\Exception $e) {
            // Handle any exceptions, log, or return an error message based on your needs
            return false;
        }

        return false;
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



}
