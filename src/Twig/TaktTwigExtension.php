<?php

namespace Vskstudio\Takt\Symfony\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Vskstudio\Takt\SnippetRenderer;

final class TaktTwigExtension extends AbstractExtension
{
    public function __construct(private readonly SnippetRenderer $renderer)
    {
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('takt', [$this, 'render'], ['is_safe' => ['html']])];
    }

    public function render(): string
    {
        return $this->renderer->render();
    }
}
