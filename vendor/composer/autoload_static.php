<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit88bb505ac0957b5d6d39da1d5a3db942 {

    public static $prefixLengthsPsr4 = array(
        'I' =>
        array(
            'Inc\\' => 4,
        ),
    );
    public static $prefixDirsPsr4 = array(
        'Inc\\' =>
        array(
            0 => __DIR__ . '/../..' . '/inc',
        ),
    );
    public static $classMap = array(
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader) {
        return \Closure::bind(function () use ($loader) {
                    $loader->prefixLengthsPsr4 = ComposerStaticInit88bb505ac0957b5d6d39da1d5a3db942::$prefixLengthsPsr4;
                    $loader->prefixDirsPsr4 = ComposerStaticInit88bb505ac0957b5d6d39da1d5a3db942::$prefixDirsPsr4;
                    $loader->classMap = ComposerStaticInit88bb505ac0957b5d6d39da1d5a3db942::$classMap;
                }, null, ClassLoader::class);
    }

}
