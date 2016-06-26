<?php

defined('APP_PATH') || define('APP_PATH', realpath('.'));
define('SECRET_PHRASE','Work@ble project!');
define('TOKEN_NAME','deftoken');
return new \Phalcon\Config(array(
    'database' => array(
        'adapter'     => 'Mysql',
        'host'        => 'localhost',
        'username'    => 'root',
        'password'    => 'tomtom',
        'dbname'      => 'MovieRamaDB',
        'charset'     => 'utf8',
    ),
    'application' => array(
        'controllersDir' => APP_PATH . '/app/controllers/',
        'modelsDir'      => APP_PATH . '/app/models/',
        'migrationsDir'  => APP_PATH . '/app/migrations/',
        'viewsDir'       => APP_PATH . '/app/views/',
        'pluginsDir'     => APP_PATH . '/app/plugins/',
        'libraryDir'     => APP_PATH . '/app/library/',
        'cacheDir'       => APP_PATH . '/app/cache/',
        'vendor' => APP_PATH . '/vendor/',
        'baseUri'        => '/MovieRama/',
        'debug' => true,
    )
));
