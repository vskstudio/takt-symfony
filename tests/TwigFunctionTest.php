<?php

namespace Vskstudio\Takt\Symfony\Tests;

use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;
use Vskstudio\Takt\Mode;
use Vskstudio\Takt\Options;
use Vskstudio\Takt\SnippetRenderer;
use Vskstudio\Takt\Symfony\Twig\TaktTwigExtension;

final class TwigFunctionTest extends TestCase
{
    private function extension(): TaktTwigExtension
    {
        $renderer = new SnippetRenderer(new Options(domain: 'example.com', mode: Mode::Cdn));

        return new TaktTwigExtension($renderer);
    }

    public function test_exposes_takt_function_marked_html_safe(): void
    {
        $functions = $this->extension()->getFunctions();
        $this->assertCount(1, $functions);

        $fn = $functions[0];
        $this->assertInstanceOf(TwigFunction::class, $fn);
        $this->assertSame('takt', $fn->getName());
        $this->assertContains('html', $fn->getSafe(new \Twig\Node\Node()));
    }

    public function test_render_returns_snippet_string(): void
    {
        $html = $this->extension()->render();
        $this->assertIsString($html);
        $this->assertStringContainsString('data-domain="example.com"', $html);
    }
}
