<?php
setlocale (LC_TIME, 'fr_FR.utf8','fra');

require '../vendor/autoload.php';
use Kernel\Router\Router;
use Kernel\Router\Group;

$router = new Router($_GET['url']);

// Documentation
$router->add('GET', '/docs', "Doc#index", "doc.index", null);


// Auth in POST Method. Response : token
// INFO : To use API with needToken, you need to send the response token in header of all requests : "X-Auth-Token"
$router->add('POST', '/auth', "Auth#check", "auth.check");

// Get all users
$router->add('GET', '/users', "User#getAll", "user.getall");


// Group of routes
$router->group('/examples', function (Group $group) {

    // Simple GET Method
    $group->add('GET', '', "Example#index", "examples.index");

    // GET Method with params
    $group->add('GET', '/:slug-:id', "Example#index2", "examples.index2", null, [
        'slug' => '[a-z]+'
    ]);

    // PUT (UPDATE) Method with param
    $group->add('PUT', '/:id', "Example#update", "examples.update", true);

    // POST (INSERT) Method
    $group->add('POST', '', "Example#add", "examples.add", true);

    // DELETE Method
    $group->add('DELETE', '/:id', "Example#delete", "examples.delete", true);


}, null, [ // Params of routes in this group
    'id' => '[0-9]+',
]);


$router->run();