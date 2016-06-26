<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs(
        array(
            $config->application->controllersDir,
            $config->application->modelsDir,
            $config->application->pluginsDir,
            $config->application->vendor
        )
);

$loader->registerClasses(
        array(
            "tokenGenerator" => __DIR__ . "/../classes/tokenGenerator.php",
        )
);

$loader->registerNamespaces(array(
    "Emarref\Jwt" => "vendor/emarref/jtw/src",
));

$loader->register();
