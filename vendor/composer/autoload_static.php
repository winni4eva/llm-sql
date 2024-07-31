<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit01db93d7694554c64352493de50ce796
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'Winnipass\\AiSql\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Winnipass\\AiSql\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit01db93d7694554c64352493de50ce796::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit01db93d7694554c64352493de50ce796::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit01db93d7694554c64352493de50ce796::$classMap;

        }, null, ClassLoader::class);
    }
}
