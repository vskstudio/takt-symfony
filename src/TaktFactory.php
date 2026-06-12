<?php

namespace Vskstudio\Takt\Symfony;

use Symfony\Component\HttpFoundation\RequestStack;
use Vskstudio\Takt\Takt;

final class TaktFactory
{
    public static function create(string $endpoint, string $domain, ?string $apiKey, RequestStack $stack): Takt
    {
        $takt = new Takt($endpoint, $domain, $apiKey);
        $request = $stack->getCurrentRequest();
        if ($request !== null) {
            $takt = $takt->withVisitor($request->getClientIp(), $request->headers->get('User-Agent'));
        }

        return $takt;
    }
}
