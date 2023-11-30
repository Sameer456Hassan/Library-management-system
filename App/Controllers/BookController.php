<?php

namespace App\Controllers;

use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Book;

class BookController
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

            $Books = Book::where('name', 'LIKE', "%$search%")
                ->offset($offset)
                ->limit($limit)
                ->orderBy($sort_by, $sort_order)
                ->get();

            $response->getBody()->write(json_encode($Books)); // Write JSON to the response body

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(['error', $e->getMessage()]); // Write JSON to the response body

            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public function start(Request $request, Response $response)
    {
        $response->getBody()->write('Server Is Live'); // Write JSON to the response body
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getBook(Request $request, Response $response, $args)
    {
        try {
            // Implement your get book by ID logic here
            // Make sure to handle authentication in the middleware

            $id = $args['id'];
            $book = Book::find($id);

            if (!$book) {
                return $response->withStatus(404)->withJson(['error' => 'Book not found']);
            }

            $response->getBody()->write(json_encode($book)); // Write JSON to the response body

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson(['error' => $e->getMessage()]);
        }
    }

    public function createBook(Request $request, Response $response)
    {
        try {
            // Implement your create book logic here
            // Make sure to handle authentication in the middleware

            $rawBody = (string) $request->getBody();
            $data = json_decode($rawBody, true);

            // Validate and create the book
            // You may want to use proper validation and error handling
            $book = new Book();
            $book->title = $data['title'];
            $book->author_id = $data['author_id']; // Assuming you have an 'author_id' field in the book table
            $book->save();

            return $response->withJson(['message' => 'Book created successfully']);
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson(['error' => $e->getMessage()]);
        }
    }

    public function updateBook(Request $request, Response $response, $args)
    {
        try {
            // Implement your update book logic here
            // Make sure to handle authentication in the middleware

            $id = $args['id'];
            $book = Book::find($id);

            if (!$book) {
                $response->getBody()->write(json_encode('No Book Found')); // Write JSON to the response body

                return $response->withHeader('Content-Type', 'application/json');
            }
            $rawBody = (string) $request->getBody();
            $data = json_decode($rawBody, true);
            $book->name = $data['name'];
            $book->author_id = $data['author_id']; // Assuming you have an 'author_id' field in the book table
            $book->save();

            $response->getBody()->write(json_encode('Book Updated Successfully')); // Write JSON to the response body

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode($e->getMessage())); // Write JSON to the response body

            return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public function deleteBook(Request $request, Response $response, $args)
    {
        try {
            // Implement your delete book logic here
            // Make sure to handle authentication in the middleware

            $id = $args['id'];
            $book = Book::find($id);

            if (!$book) {

                $response->getBody()->write(json_encode('Book Not Found')); // Write JSON to the response body

                return $response->withHeader('Content-Type', 'application/json');
            }

            $book->delete();


            $response->getBody()->write(json_encode('Book Deleted Successfully')); // Write JSON to the response body

            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson(['error' => $e->getMessage()]);
        }
    }
}
