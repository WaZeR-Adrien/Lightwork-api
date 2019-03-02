<?php
setlocale (LC_TIME, 'fr_FR.utf8','fra');

require '../vendor/autoload.php';
use Kernel\Router\Router;
use Kernel\Router\Group;

$router = new Router($_GET['url']);

// Documentation
$router->group('/docs', function (Group $group) {

    // Home page
    $group->add('GET', '', "Docs#homePage");

    // Routes page
    $group->add('GET', '/routes', "Docs#routesPage");

});

// Auth in POST Method. Response : token
// INFO : To use API with routes protected by token, you need to send the response token in header of all requests : "X-Auth-Token"
$router->add('POST', '/auth', "Auth#check");


// Group of routes
$router->group('/examples', function (Group $group) {

    // Simple GET Method
    $group->add('GET', '', "Example#getAll");

    // GET Method with params
    $group->add('GET', '/:slug-:id', "Example#index", [], [
        'slug' => 'String'
    ]);

    // PUT (UPDATE) Method with param
    $group->add('PUT', '/:id', "Example#update");

    // POST (INSERT) Method
    $group->add('POST', '', "Example#add", [], [
        '*email' => '',
        '*password' => 'String',
        'name' => 'String',
        'age' => 'Int',
    ]);

    // DELETE Method
    $group->add('DELETE', '/:id', "Example#delete");


}, [ // Arguments of routes in this group
    'id' => 'Int',
]);


$router->run();
