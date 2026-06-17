<?php

namespace Vskstudio\Takt\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Vskstudio\Takt\Symfony\TaktFactory;
use Vskstudio\Takt\Options;
use Vskstudio\Takt\SnippetRenderer;
use Vskstudio\Takt\Takt;

final class TaktExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $optionsDef = new Definition(Options::class);
        $optionsDef->setFactory([Options::class, 'fromArray']);
        $optionsDef->setArguments([[
            'domain' => $config['domain'],
            'endpoint' => $config['endpoint'],
            'scriptOrigin' => $config['script_origin'],
            'mode' => $config['mode'],
            'outbound' => $config['outbound'],
            'files' => $config['files'],
            'excludeLocalhost' => $config['exclude_localhost'],
        ]]);
        $container->setDefinition(Options::class, $optionsDef);

        $rendererDef = new Definition(SnippetRenderer::class, [new Reference(Options::class)]);
        $rendererDef->setPublic(true);
        $container->setDefinition(SnippetRenderer::class, $rendererDef);

        $taktDef = new Definition(Takt::class);
        $taktDef->setFactory([TaktFactory::class, 'create']);
        $taktDef->setArguments([
            $config['endpoint'],
            $config['domain'],
            $config['api_key'],
            new Reference('request_stack'),
        ]);
        $taktDef->setPublic(true);
        $container->setDefinition(Takt::class, $taktDef);

        (new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config')))->load('services.php');
    }
}
