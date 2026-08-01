<?php

namespace Vskstudio\Takt\Symfony;

use Vskstudio\Takt\Options;

/**
 * Normalise le réglage `endpoint`, qui accepte indifféremment l'origine du
 * service (`https://taktlytics.com`) ou l'URL complète de collecte
 * (`https://taktlytics.com/api/event`), vers la forme attendue par chaque
 * chemin : URL complète pour le snippet navigateur, origine nue pour l'envoi
 * serveur-à-serveur (Takt y ajoute lui-même `/api/event`).
 *
 * Une valeur portant un autre chemin (proxy première-partie type `/collect`)
 * est laissée telle quelle : c'est déjà l'URL de collecte voulue.
 */
final class Endpoint
{
    private const COLLECT_PATH = '/api/event';

    public static function collect(mixed $endpoint): string
    {
        $value = self::clean($endpoint);
        if ('' === $value) {
            return Options::HOSTED_ENDPOINT;
        }

        return self::isBareOrigin($value) ? $value.self::COLLECT_PATH : $value;
    }

    public static function origin(mixed $endpoint): string
    {
        $value = self::clean($endpoint);
        if ('' === $value) {
            return Options::HOSTED_ORIGIN;
        }
        if (!str_ends_with($value, self::COLLECT_PATH)) {
            return $value;
        }
        $stripped = substr($value, 0, -\strlen(self::COLLECT_PATH));

        return '' !== $stripped ? $stripped : $value;
    }

    private static function clean(mixed $endpoint): string
    {
        return is_scalar($endpoint) ? rtrim(trim((string) $endpoint), '/') : '';
    }

    private static function isBareOrigin(string $value): bool
    {
        return 1 === preg_match('#^[a-z][a-z0-9+.-]*://[^/]+$#i', $value);
    }
}
