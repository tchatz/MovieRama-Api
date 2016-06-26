<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit78a0ac4dc75fc146c938d2c848300f53
{
    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'Emarref\\Jwt\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Emarref\\Jwt\\' => 
        array (
            0 => __DIR__ . '/..' . '/emarref/jwt/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit78a0ac4dc75fc146c938d2c848300f53::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit78a0ac4dc75fc146c938d2c848300f53::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
