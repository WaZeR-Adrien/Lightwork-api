<?php
setlocale (LC_TIME, 'fr_FR.utf8','fra');

require '../vendor/autoload.php';

$router = new \Kernel\Router\Router($_GET['url']);

// Auth in POST Method. Response : token
// INFO : To use API with needToken, you need to send the response token in header of all requests
$router->add('POST', '/auth', "Auth#check", "auth.check");

// Simple GET Method
$router->add('GET', '/examples', "Example#index", "examples.index");

// GET Method with params
$router->add('GET', '/examples/:slug-:id', "Example#index2", "examples.index2", true)
    ->with('slug', '[a-zA-Z0-9]+')->with('id', '[0-9]+');

// PUT (UPDATE) Method with param
$router->add('PUT', '/examples/:id', "Example#update", "examples.update", true)->with('id', '[0-9]+');

// POST (INSERT) Method
$router->add('POST', '/examples', "Example#add", "examples.add", true);

// DELETE Method
$router->add('DELETE', '/examples/:id', "Example#delete", "examples.delete", true)->with('id', '[0-9]+');

$router->run();