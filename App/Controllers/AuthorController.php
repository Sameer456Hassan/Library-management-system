<?php

namespace App\Controllers;

use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\ResponseFactory;
use App\Models\Author;

class AuthorController
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function index(Request $request, Response $response)
    {
        try {
            // Implement your author listing logic here
            // Make sure to handle authentication in the middleware

            $queryParams = $request->getQueryParams();

            // Pagination
            $page = isset($queryParams['page']) ? max(1, (int) $queryParams['page']) : 1;
            $limit = isset($queryParams['limit']) ? max(1, (int) $queryParams['limit']) : 10;
            $offset = ($page - 1) * $limit;

            // Search
            $search = isset($queryParams['search']) ? $queryParams['search'] : '';

            // Sort
            $sort_by = isset($queryParams['sort_by']) ? $queryParams['sort_by'] : 'name';
            $sort_order = isset($queryParams['sort_order']) && strtolower($queryParams['sort_order']) == 'desc' ? 'desc' : 'asc';

            $authors = Author::where('name', 'LIKE', "%$search%")
                ->offset($offset)
                ->limit($limit)
                ->orderBy($sort_by, $sort_order)
                ->get();

            $response->getBody()->write(json_encode($authors)); // Write JSON to the response body

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson(['error' => $e->getMessage()]);
        }
    }


    public function getAuthor(Request $request, Response $response, $args)
    {
        try {
            // Implement your get author by ID logic here
            // Make sure to handle authentication in the middleware

            $id = $args['id'];
            $author = Author::find($id);

            if (!$author) {
                $responseFactory = new ResponseFactory();
                $response = $responseFactory->createResponse();

                $response->getBody()->write(json_encode(['error' => 'Author Not Found!']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $response->getBody()->write(json_encode($author)); // Write JSON to the response body

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson(['error' => $e->getMessage()]);
        }
    }

    public function createAuthor(Request $request, Response $response)
    {
        try {
            // Implement your create author logic here
            // Make sure to handle authentication in the middleware

            // For simplicity, let's assume you have a user authentication logic here
            $rawBody = (string) $request->getBody();
            $data = json_decode($rawBody, true);

            // Validate and create the author
            // You may want to use proper validation and error handling
            $author = new Author();
            $author->name = $data['name'];
            $author->dob = $data['dob'];
            $author->save();


            $response->getBody()->write('Author Created Successfully'); // Write JSON to the response body

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }

    public function updateAuthor(Request $request, Response $response, $args)
    {
        try {
            // Implement your update author logic here
            // Make sure to handle authentication in the middleware

            $id = $args['id'];
            $author = Author::find($id);
            $rawBody = (string) $request->getBody();
            $data = json_decode($rawBody, true);

            if (!$author) {
                $response->getBody()->write(json_encode(['error' => 'Author Not Found!']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $author->name = $data['name'];
            $author->dob = $data['dob'];
            $author->save();

            // Prepare the data you want to include in the response
            $responseData = [
                'message' => 'Author updated successfully',
                'updated_author' => [
                    'id' => $author->id,
                    'name' => $author->name,
                    // Add other fields as needed
                ],
            ];

            $response->getBody()->write(json_encode($responseData)); // Write JSON to the response body

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }


    public function deleteAuthor(Request $request, Response $response, $args)
    {
        try {

            $id = $args['id'];
            $author = Author::find($id);

            if (!$author) {

                $response->getBody()->write(json_encode('Author Not Found')); // Write JSON to the response body

                return $response->withHeader('Content-Type', 'application/json');
            }

            $author->delete();


            $response->getBody()->write(json_encode('Author Deleted Successfully')); // Write JSON to the response body

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    }
}
