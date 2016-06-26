<?php

/**
 * Services are globally registered in this file
 *
 * @var \Phalcon\Config $config
 */
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Dispatcher;

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();


/**
 * We register the events manager
 */
$di->set('dispatcher', function () use ($di) {

    $eventsManager = new EventsManager;

    /**
     * Check if the user is allowed to access certain action using the SecurityPlugin
     */
    $eventsManager->attach('dispatch:beforeDispatch', new SecurityPlugin);

    /**
     * Handle exceptions and not-found exceptions using NotFoundPlugin
     */
    $eventsManager->attach('dispatch:beforeException', new NotFoundPlugin);

    $dispatcher = new Dispatcher;
    $dispatcher->setEventsManager($eventsManager);

    return $dispatcher;
});


/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/**
 * Setting up the view component
 */
$di->setShared('view', function () use ($config) {

    $view = new View();

    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines(array(
        '.volt' => function ($view, $di) use ($config) {

            $volt = new VoltEngine($view, $di);

            $volt->setOptions(array(
                'compiledPath' => $config->application->cacheDir,
                'compiledSeparator' => '_'
            ));

            return $volt;
        },
                '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
            ));

            return $view;
        });

        /**
         * Database connection is created based in the parameters defined in the configuration file
         */
        $di->setShared('db', function () use ($config) {
            $dbConfig = $config->database->toArray();
            $adapter = $dbConfig['adapter'];
            unset($dbConfig['adapter']);

            $class = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;

            return new $class($dbConfig);
        });

        /**
         * If the configuration specify the use of metadata adapter use it or use memory otherwise
         */
        $di->setShared('modelsMetadata', function () {
            return new MetaDataAdapter();
        });

        /**
         * Register the session flash service with the Twitter Bootstrap classes
         */
        $di->set('flash', function () {
            return new Flash(array(
                'error' => 'alert alert-danger',
                'success' => 'alert alert-success',
                'notice' => 'alert alert-info',
                'warning' => 'alert alert-warning'
            ));
        });

        /**
         * Add routing capabilities
         */
        $di->set('router', function() {
            require __DIR__ . '/../../app/config/routes.php';
            return $router;
        });

        /**
         * Start the session the first time some component request the session service
         */
        $di->setShared('session', function () {
            $session = new SessionAdapter();
            $session->start();

            return $session;
        });

        /**
         * The default Phalcon uses the $2a$ salt prefix which has proven to have a weakness. 
         * This code will set the default prefix to $2y$ which is currently the recommended one. 
         * It also increases the work factor from 08 to 13.
         */
        $di->set('security', function() {
            $security = new Phalcon\Security();
            $security->setWorkFactor(13);
            $security->setDefaultHash(Phalcon\Security::CRYPT_BLOWFISH_Y);
            return $security;
        });


        $di->set('config', $config);
        