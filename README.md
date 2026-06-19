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
  script_origin: null   # first-party origin to dodge ad-blockers (see below)
  api_key: '%env(TAKT_API_KEY)%'
  mode: 'inline'   # inline | cdn | asset | sdk (sdk = full ES-module init(), needed for scrub_url)
  outbound: false
  files: false
  file_extensions: []   # e.g. ['pdf', 'zip']; empty keeps the tracker default list
  tagged: false         # track elements marked with data-takt-event
  not_found: false      # track 404 pageviews
  exclude_localhost: true
  nonce: null           # CSP nonce for the inline <script> (request-scoped)
  # Advanced options — null keeps the tracker defaults.
  sample_rate: null     # e.g. 0.5 keeps ~50% of hits
  track_query: null     # true keeps the raw query string + hash in URLs
  query_params: []      # params to keep when track_query is off, e.g. ['utm_source']
  respect_dnt: null     # false stops honoring the Do-Not-Track header
  enabled: null         # false disables tracking entirely (kill-switch)
  scrub_url: null       # raw JS fn to rewrite URLs; requires mode: sdk (dev-controlled only)
```

The `api_key` must be **ingest-scoped and domain-bound**. Keep it out of source
control via an environment variable.

`script_origin` is the first-party origin to serve the tracker + derive the
endpoint from (`{origin}/api/event`) — your Takt domain or a custom domain to
dodge ad-blockers (`endpoint` wins over it).

Autocapture is opt-in. `outbound`, `files`, `tagged` and `not_found` each add a
token to the single `data-auto` attribute read by the bundled tracker;
`file_extensions` narrows which downloads count.

The advanced options map to the engine's `sampleRate`, `trackQuery`,
`queryParams`, `respectDnt` and `enabled`. `scrub_url` is a **raw JS function**
injected verbatim, so it only works in `mode: sdk` (a full ES-module `init()`
render) — keep it dev-controlled and never build it from user input.

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
- `sdk` — a `<script type="module">` boots the full SDK via `init()`; required for `scrub_url`.

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
