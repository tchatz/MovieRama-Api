<?php

//ini_set('display_errors', '1');
//ini_set('displaystartuperrors', 1);
//error_reporting(E_ALL);

$listener = new \Phalcon\Debug();
$listener->listen(true, true);

define('APP_PATH', realpath('..'));

try {

    /**
     * Read the configuration
     */
    $config = include APP_PATH . "/app/config/config.php";

    /**
     * Read auto-loader
     */
    include APP_PATH . "/app/config/loader.php";

    /**
     * Read services
     */
    include APP_PATH . "/app/config/services.php";

    /**
     * Include composer autoloader
     */
    require APP_PATH . "/vendor/autoload.php";
    
    /**
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);

    echo $application->handle()->getContent();
} catch (\Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
