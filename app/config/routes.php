<?php

use Phalcon\Mvc\Router;

$router = new Router();

//////******User********//////
$router->addPost("/login", array(//apidoc done
    'controller' => 'User',
    'action' => 'login'
));

$router->addPost("/signup", array(//apidoc done
    'controller' => 'User',
    'action' => 'signup'
));

//////******Movies********//////
$router->addPost("/addMovie", array(//apidoc done
    'controller' => 'Movies',
    'action' => 'addMovie'
));

$router->addGet("/getMovies/{id:[0-9]*}", array(//apidoc done
    'controller' => 'Movies',
    'action' => 'getAllMovies'
));

//$router->addGet("/getMovies", array(//apidoc done
//    'controller' => 'Movies',
//    'action' => 'getAllMovies'
//));

$router->addPost("/voteMovie", array(//apidoc done
    'controller' => 'Movies',
    'action' => 'voteMovie'
));

return $router;
