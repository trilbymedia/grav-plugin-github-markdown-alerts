<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit38543230c9c2a6e04776e942bfab4344
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'Grav\\Plugin\\GithubMarkdownAlerts\\' => 33,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Grav\\Plugin\\GithubMarkdownAlerts\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Grav\\Plugin\\GithubMarkdownAlertsPlugin' => __DIR__ . '/../..' . '/github-markdown-alerts.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit38543230c9c2a6e04776e942bfab4344::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit38543230c9c2a6e04776e942bfab4344::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit38543230c9c2a6e04776e942bfab4344::$classMap;

        }, null, ClassLoader::class);
    }
}
