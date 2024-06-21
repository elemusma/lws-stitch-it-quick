<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit10cf20af0f3c98262859fb3c00bfa2bc
{
    public static $files = array (
        '8d6350bdfc1019c8aef1b0878deb406b' => __DIR__ . '/..' . '/newfold-labs/wp-pls-utility/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'N' => 
        array (
            'NewfoldLabs\\WP\\PLS\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'NewfoldLabs\\WP\\PLS\\' => 
        array (
            0 => __DIR__ . '/..' . '/newfold-labs/wp-pls-utility/includes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit10cf20af0f3c98262859fb3c00bfa2bc::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit10cf20af0f3c98262859fb3c00bfa2bc::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit10cf20af0f3c98262859fb3c00bfa2bc::$classMap;

        }, null, ClassLoader::class);
    }
}