<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Vskstudio\Takt\Symfony\Twig\TaktTwigExtension;

return static function (ContainerConfigurator $c): void {
    $c->services()
        ->set(TaktTwigExtension::class)
        ->autowire()
        ->public()
        ->tag('twig.extension');
};
