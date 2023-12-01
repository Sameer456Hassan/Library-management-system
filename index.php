<?php

use Slim\Factory\AppFactory;
use DI\Container;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/database.php';

// Create a container
$container = new Container();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add error middleware to get more details about the error
$app->addErrorMiddleware(true, true, true);

// Specify the templates folder
$twig = Twig::create(__DIR__ . '/app/Views/templates');

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

// Register controllers
$container->set('BookController', new App\Controllers\BookController($container));
$container->set('AuthController', new App\Controllers\AuthController($container));
$container->set('UserController', new App\Controllers\UserController($container));
$container->set('AuthorController', new App\Controllers\AuthorController($container));
$container->set('HomeController', new App\Controllers\HomeController($twig)); // Add this line

// Define routes
$app->get('/', 'HomeController:index'); // Use the controller directly in the route
$app->get('/signup', 'HomeController:signup'); // Use the controller directly in the route
$app->get('/dashboard', 'HomeController:dashboard');
$app->get('/authors-dashboard', 'HomeController:authors');
$app->get('/logout', 'HomeController:logout');

$app->get('/books', 'BookController:index')->add([$container->get('AuthController'), 'customAuth']);
$app->get('/books/{id}', 'BookController:getBook')->add([$container->get('AuthController'), 'customAuth']);
$app->post('/login', 'AuthController:login');
$app->post('/signup', 'AuthController:signup');
$app->get('/users', 'UserController:index')->add('AuthController:basicAuth');
$app->get('/authors/{id}/books', 'BookController:getBooksByAuthor')->add('AuthController:bearerAu th');
$app->put('/books/{id}', 'BookController:updateBook')->add('AuthController:customAuth');

$app->get('/authors', 'AuthorController:index')->add([$container->get('AuthController'), 'customAuth']);
$app->get('/authors/{id}', 'AuthorController:getAuthor')->add('AuthController:basicAuth');
$app->post('/authors', 'AuthorController:createAuthor')->add([$container->get('AuthController'), 'bearerAuth']);
$app->put('/authors/{id}', 'AuthorController:updateAuthor')->add('AuthController:bearerAuth');

$app->delete('/authors/{id}', 'AuthorController:deleteAuthor')->add('AuthController:bearerAuth');
$app->delete('/books/{id}', 'BookController:deleteBook')->add('AuthController:bearerAuth');

// Run the app
$app->run();
