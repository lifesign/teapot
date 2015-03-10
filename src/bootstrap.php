<?php

if(file_exists(__DIR__.'/../vendor/autoload.php')) {
    require __DIR__.'/../vendor/autoload.php';
} else {
    require __DIR__.'/vendor/autoload.php';
}

$version = '1.0.0';

$app = new Symfony\Component\Console\Application('Teapot', $version);

$teapot = new \Teapot\Loader($app);

$teapot->run();
