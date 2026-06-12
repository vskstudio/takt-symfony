<?php

namespace Vskstudio\Takt\Symfony\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Vskstudio\Takt\Symfony\DependencyInjection\TaktExtension;
use Vskstudio\Takt\SnippetRenderer;
use Vskstudio\Takt\Takt;

final class TaktExtensionTest extends TestCase
{
    private function compile(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->registerExtension($ext = new TaktExtension());
        $ext->load([$config], $container);
        $container->compile();

        return $container;
    }

    public function test_registers_services_from_config(): void
    {
        $c = $this->compile(['domain' => 'example.com', 'endpoint' => 'https://takt.example.com', 'api_key' => 'k_test', 'mode' => 'cdn']);
        $this->assertInstanceOf(SnippetRenderer::class, $c->get(SnippetRenderer::class));
        $this->assertInstanceOf(Takt::class, $c->get(Takt::class));
    }

    public function test_defaults_apply_with_empty_config(): void
    {
        $c = $this->compile([]);
        $html = $c->get(SnippetRenderer::class)->render();
        $this->assertInstanceOf(SnippetRenderer::class, $c->get(SnippetRenderer::class));
        $this->assertInstanceOf(Takt::class, $c->get(Takt::class));
        // default mode is inline
        $this->assertStringContainsString('var takt=', $html);
    }

    public function test_snippet_renderer_uses_domain(): void
    {
        $c = $this->compile(['domain' => 'example.com', 'mode' => 'cdn']);
        $this->assertStringContainsString('data-domain="example.com"', $c->get(SnippetRenderer::class)->render());
    }

    public function test_inline_mode_embeds_bundle(): void
    {
        $c = $this->compile(['domain' => 'example.com', 'mode' => 'inline']);
        $html = $c->get(SnippetRenderer::class)->render();
        $this->assertStringContainsString('var takt=', $html);
        $this->assertStringNotContainsString(' src=', $html);
    }

    public function test_cdn_mode_uses_jsdelivr(): void
    {
        $c = $this->compile(['domain' => 'example.com', 'mode' => 'cdn']);
        $html = $c->get(SnippetRenderer::class)->render();
        $this->assertStringContainsString('src="https://cdn.jsdelivr.net', $html);
    }

    public function test_asset_mode_uses_self_hosted_path(): void
    {
        $c = $this->compile(['domain' => 'example.com', 'mode' => 'asset']);
        $this->assertStringContainsString('src="/takt/takt.js"', $c->get(SnippetRenderer::class)->render());
    }

    public function test_outbound_and_files_attrs(): void
    {
        $c = $this->compile(['domain' => 'example.com', 'mode' => 'cdn', 'outbound' => true, 'files' => true]);
        $html = $c->get(SnippetRenderer::class)->render();
        $this->assertStringContainsString('data-outbound', $html);
        $this->assertStringContainsString('data-files', $html);
    }

    public function test_exclude_localhost_false_emits_attr(): void
    {
        $c = $this->compile(['domain' => 'example.com', 'mode' => 'cdn', 'exclude_localhost' => false]);
        $this->assertStringContainsString('data-exclude-localhost="false"', $c->get(SnippetRenderer::class)->render());
    }

    public function test_exclude_localhost_default_true_omits_attr(): void
    {
        $c = $this->compile(['domain' => 'example.com', 'mode' => 'cdn']);
        $this->assertStringNotContainsString('data-exclude-localhost', $c->get(SnippetRenderer::class)->render());
    }

    public function test_endpoint_flows_into_snippet(): void
    {
        $c = $this->compile(['domain' => 'example.com', 'endpoint' => '/collect', 'mode' => 'cdn']);
        $this->assertStringContainsString('data-endpoint="/collect"', $c->get(SnippetRenderer::class)->render());
    }

    public function test_services_are_public(): void
    {
        $c = $this->compile(['domain' => 'example.com']);
        $this->assertTrue($c->getDefinition(SnippetRenderer::class)->isPublic());
        $this->assertTrue($c->getDefinition(Takt::class)->isPublic());
    }

    public function test_twig_extension_registered_and_tagged(): void
    {
        $c = $this->compile(['domain' => 'example.com']);
        $def = $c->getDefinition(\Vskstudio\Takt\Symfony\Twig\TaktTwigExtension::class);
        $this->assertTrue($def->hasTag('twig.extension'));
    }
}
