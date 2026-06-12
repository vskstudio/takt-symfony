# takt-symfony

Symfony bundle for [Takt](https://github.com/vskstudio) analytics. It wires the
Takt snippet into your templates through a `{{ takt() }}` Twig function and
exposes an autowired `Takt` service for server-side events.

## Installation

```bash
composer require vskstudio/takt-symfony
```

If you use [Symfony Flex](https://symfony.com/doc/current/setup/flex.html), the
bundle is enabled automatically. Otherwise add it manually to
`config/bundles.php`:

```php
return [
    // ...
    Vskstudio\Takt\Symfony\TaktBundle::class => ['all' => true],
];
```

## Configuration

Create `config/packages/takt.yaml`:

```yaml
takt:
  domain: 'example.com'
  endpoint: 'https://takt.example.com'
  api_key: '%env(TAKT_API_KEY)%'
  mode: 'inline'   # inline | cdn | asset
  outbound: false
  files: false
  exclude_localhost: true
```

The `api_key` must be **ingest-scoped and domain-bound**. Keep it out of source
control via an environment variable.

## Client-side tracking

Call the `takt()` Twig function inside the `<head>` of your base template:

```twig
<head>
    {# ... #}
    {{ takt() }}
</head>
```

### Modes

- `inline` — the tracking script is embedded directly in the page.
- `cdn` — a `<script>` tag pointing at the Takt CDN is rendered.
- `asset` — a `<script>` tag pointing at a self-hosted asset is rendered.

## Server-side events

Autowire the `Takt` service in any controller or service:

```php
use Vskstudio\Takt\Revenue;
use Vskstudio\Takt\Takt;

final class CheckoutController
{
    public function __construct(private readonly Takt $takt)
    {
    }

    public function complete(): Response
    {
        $this->takt->event('Signup', ['plan' => 'pro'], new Revenue('29.00', 'EUR'));
        $this->takt->pageview();

        // ...
    }
}
```

The autowired service is bound to the current request: it automatically
attributes events to the request's IP address and User-Agent.

> **Behind a proxy or load balancer?** The attributed IP comes from
> `Request::getClientIp()`. It only honours `X-Forwarded-For` when the request
> is trusted, so configure [`framework.trusted_proxies`](https://symfony.com/doc/current/deployment/proxies.html)
> for the real client IP. Without trusted proxies, clients can spoof the
> forwarded header — so never treat the attributed IP as authoritative.

## License

MIT — see [LICENSE](LICENSE).
