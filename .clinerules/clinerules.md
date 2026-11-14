# CodeIgniter 4 System Architecture Standard

## Part 0: Philosophy — Simple vs. Easy

> _"Conflating [Simple and Easy] is why we're drowning in complexity."_ — **Rich Hickey** (_"Simple Made Easy"_, 2011)

| Attribute         | Simple (Standard)                                                | Easy (Anti-Pattern)                                    |
| :---------------- | :--------------------------------------------------------------- | :----------------------------------------------------- |
| **Root / Nature** | _Simplex_ ("one fold/braid"); objective structure                | _Adjacens_ ("nearby/at hand"); subjective convenience  |
| **Focus**         | Single deterministic responsibility; disentangled                | Familiarity, proximity, copy-paste, global shortcuts   |
| **Cost**          | Deliberate upfront design, strict contracts, refactoring         | Zero initial friction; complected/braided dependencies |
| **Outcome**       | Long-term maintainability, deterministic debugging, linear scale | Rapid start, cascading failures, high defect debt      |

### CI4 Paradigm Mappings

- **Easy (FORBIDDEN):** `$this->request->getPost()` or `session()` inside Services/Models; inline `$db->table()` queries in Controllers/Views; `$var ?? 'default'` masking missing View payloads.
- **Simple (MANDATORY):** Typed scalars/DTOs/Entities passed into HTTP-agnostic Services; DB access strictly in constructor-injected Models; explicit View data contracts failing immediately on missing keys.
- **Core Mandate:** Disentanglement MUST NOT be sacrificed for developer comfort, brevity, or convenience.

---

## Part 1: Core Meta-Rules

- **Universal Scope:** Applies unconditionally to EVERY file, class, trait, helper, entity, view, and model. Zero exceptions.
- **Project-Agnostic:** Specs/examples MUST use generic placeholders (`DomainController`, `DomainService`, `DomainEntity`, `DomainModel`, `domain_id`, `[ModuleName]`).
- **Deterministic Language (RFC 2119):** Use ONLY `MUST`, `MUST NOT`, `REQUIRED`, `SHALL`, `SHALL NOT`, `MAY`, `FORBIDDEN`. Permissive words (_should_, _generally_, _recommended_, _best practice_) are **FORBIDDEN**.
- **Zero Fallbacks & Zero Suppression:** Unvalidated defaults, null-coalescing chains masking missing dependencies, silent exception swallows, and PHP's `@` error-suppression operator are **FORBIDDEN**. Operations MUST succeed deterministically or fail explicitly.
- **Pre-Task Alignment:** Load and align with this standard before any design, refactor, or implementation.
- **Runtime Verification & Log Audit:** Execute active self-verification across all routes (automated tests, CLI, cURL, local HTTP) and inspect `writable/logs/` for zero warnings/errors before declaring completion.
- **Static Analysis Gate:** CI and pre-commit hooks MUST enforce **PHPStan Level 8** and **PHPCS (`PSR-12`)**, failing on untyped boundaries, dynamic properties, unhandled exceptions, or permissive language.

---

## Part 2: Folder Structure & Modular Architecture

Enforces decoupled **Modular MVC-S** under `app/Modules/`. Root `app/` is reserved strictly for shared core infrastructure (base layouts, core filters, global events, core home routing).

### 2.1 Module Layout (`app/Modules/[ModuleName]/`)

Create ONLY directories containing files; `Config/Routes.php`, `Config/Services.php`, and `Database/Migrations/` are **REQUIRED**:

- `Commands/` (Spark CLI) | `Config/{Routes.php,Services.php}` | `Controllers/` (HTTP orchestration) | `Database/{Migrations/,Seeds/}` | `Entities/` (type-safe records) | `Helpers/` (`[name]_helper.php`) | `Libraries/` (Services—exclusive home of business logic) | `Models/` (DB access) | `Views/` (escaped Bootstrap 5.3 templates).

### 2.2 Registration Protocol

- **Scaffold:** `php spark make:module [ModuleName]` (or manual exact layout).
- **Namespace (`app/Config/Autoload.php`):** Register `'App\Modules\[ModuleName]' => APPPATH . 'Modules/[ModuleName]'` in `$psr4` (enables `php spark migrate --all` discovery).
- **Services (`Config/Services.php`):** Extend `CodeIgniter\Config\BaseService`; resolve via `service('domainService')` using `static::getSharedInstance('domainService')`. Cross-module shared services MUST reside in `app/Config/Services.php`.
- **Routes (`Config/Routes.php`):** Wrap inside `$routes->group('domain', ['namespace' => 'App\Modules\[ModuleName]\Controllers'], static function (RouteCollection $routes): void { ... });`.
- **Helpers:** Global helper autoloading in `app/Config/Autoload.php` is **FORBIDDEN**. Declare per-controller via `$helpers = ['App\Modules\[ModuleName]\Helpers\domain', 'form'];`.

---

## Part 3: Layer Responsibilities

### 3.1 Controllers (`Controllers/`)

- **MUST** extend `CodeIgniter\Controller` or `App\Controllers\BaseController`; declare return types `string|ResponseInterface` (or `DownloadResponse|ResponseInterface`).
- **MUST** validate all HTTP input via Validation service and retrieve ONLY via `$this->validator->getValidated()` with explicit scalar casting/normalization (`getPost()`/`getGet()` without prior validation is **FORBIDDEN**).
- **MUST** resolve Services via `service('domainService')` in `__construct()`. Accessing `$this->request`, `$this->response`, or `session()` in `__construct()` is **FORBIDDEN** (injected later via `initController()`; use `initController()` or actions).
- **MUST** return `ResponseInterface`/`redirect()`/`view()`, passing pre-formatted payloads (<=5 lines view logic) and SEO metadata: `pageTitle`, `metaDescription`, `metaKeywords`, `canonicalUrl`, `robotsTag` (`index, follow` public; `noindex, follow` private/auth), `metaImage`, `schemaJson` (`array|null`), `data`.
- **FORBIDDEN:** DB queries/Query Builder, `new` Model/Service/DB instantiation, business logic, direct filesystem drivers, inline HTML, leaking raw `$e->getMessage()` in HTTP/flash responses.

### 3.2 Services (`Libraries/`)

- **MUST** house 100% of business rules, external APIs, persistence workflows, transactions, and filesystem ops.
- **MUST** inject Models/sub-services via `__construct()`, declare all properties with visibility (`private`/`protected`) and native types at class top (dynamic properties **FORBIDDEN**), and obtain DB connections via `$this->domainModel->db` or DI (`\Config\Database::connect()` or `new` Models in methods/loops **FORBIDDEN**).
- **MUST** wrap multi-write mutations in transactions and return `Entity`, `array<int, Entity>`, or typed scalars.
- **FORBIDDEN:** Superglobals (`$_POST`, `$_GET`, `$_FILES`, `$_SERVER`), HTTP redirects, session flash data.
- **Topology:** Strictly vertical (`Controller -> MainService -> Model` or `Controller -> OrchestratorService -> SubService -> Model`). Triangular ping-pong (Controller calling both MainService and its internal SubService) is **FORBIDDEN**; MainService MUST act as Facade. Same-tier Brother-Services (`ModuleAService` <-> `ModuleBService`) MUST NOT call each other directly (use `OrchestratorService`).

### 3.3 Models (`Models/`)

- **MUST** extend `CodeIgniter\Model` and define `$table`, `$primaryKey`, `$returnType = DomainEntity::class`, `$allowedFields` (**FORBIDDEN** to omit), `$useTimestamps = true`, `$dateFormat = 'datetime'`, `$createdField`, `$updatedField`, `$deletedField`, `$useSoftDeletes = true`.
- **FORBIDDEN:** Model-level `$casts` (conflicts with Entity casts), business workflows, direct invocation from Views/Controllers.

### 3.4 Entities (`Entities/`)

- **MUST** extend `CodeIgniter\Entity\Entity` and represent all persistent data across layer boundaries (passing raw associative arrays **FORBIDDEN**).
- **MUST** define `$casts` (`'id' => 'integer'`, `'domain_id' => 'integer'`, `'value' => 'string'`, `'is_active' => 'boolean'`, `'metadata' => '?json-array'`). `DECIMAL`/monetary columns MUST cast as `'string'` (NEVER `'float'`).
- **MUST** list timestamp fields in `$dates` (returned as `\CodeIgniter\I18n\Time|null`) and annotate all properties + `$dates` via `@property` PHPDoc. Accessors/mutators (e.g., `setPassword(string $pass)`) permitted.
- **FORBIDDEN:** DB queries, service dependencies, or workflows.

### 3.5 Views (`Views/`)

- **MUST** start with `<?= $this->extend('layouts/default') ?>` and a top-level `@var` PHPDoc block typing all controller view vars.
- **MUST** use `section('content')`, `section('styles')`, `section('scripts')`; max conditional nesting depth `1`; max raw PHP block `5` lines; escape all dynamic output via `esc($v, 'html'|'attr'|'js')`.
- **FORBIDDEN:** DB calls, Service/Model instantiation.

### 3.6 Helpers (`Helpers/`)

- Pure stateless functions in `[helper_name]_helper.php`, loaded dynamically. No business logic, DB queries, or state.

---

## Part 4: Database Schema & Management Standards

- **4.1 Engine/Charset:** `ENGINE=InnoDB`, `CHARACTER SET utf8mb4`, `COLLATE utf8mb4_unicode_ci` (`latin1`/3-byte `utf8` **FORBIDDEN**), `ROW_FORMAT=DYNAMIC`.
- **4.2 Keys:** Surrogate PK `id` (business PKs like email/slug/tax ID **FORBIDDEN**): `INT UNSIGNED` / `BIGINT UNSIGNED`, or `BINARY(16)` UUIDv7/ULID (36-char string UUIDs **FORBIDDEN**). FKs MUST match parent type, length, and unsigned attribute.
- **4.3 Column Types:** Narrowest fit: `TINYINT`/`SMALLINT`/`INT`/`BIGINT` (`UNSIGNED` for IDs/counts); `DECIMAL(precision,scale)` for money/science (`FLOAT`/`DOUBLE` in SQL and `float` casts in PHP Entities/Services **FORBIDDEN**); `TINYINT(1)` with strict `0|1` default (`"Y"/"N"/"true"/"false"` **FORBIDDEN**); `DATETIME`/`TIMESTAMP` for temporals (UNIX `INT`/strings **FORBIDDEN**); `CHAR(len)` fixed, `VARCHAR(N)` bounded (unbounded `TEXT`/`LONGTEXT` for short strings **FORBIDDEN**; `TEXT`/`JSON` for payloads/metadata only).
- **4.4 Integrity:** Explicit `'null' => false` (`NOT NULL`) by default (and on defaults); `UNIQUE` constraints on unique attributes; explicit `addForeignKey()` (`ON DELETE RESTRICT` or `CASCADE`; orphan records **FORBIDDEN**).
- **4.5 Indexes:** `addKey` REQUIRED on FKs (`[entity]_id`), status filters (`is_active`, `status`), unique lookups (`email`, `slug`, `hash`), temporal sort/range (`created_at`). Composite keys ordered: highest selectivity -> equality -> range/sort. Single-column low-selectivity boolean indexes and volatile per-request indexes (`last_seen_at`) **FORBIDDEN**.
- **4.6 Naming:** `snake_case` tables/columns (camelCase/kebab-case **FORBIDDEN**); plural table names; pivot tables alphabetical singular nouns (`domain_user`); PK `id`; FK `[parent]_id`; booleans `is_`/`has_`/`must_`; timestamps `*_at`.
- **4.7 Lifecycle/Soft Deletes:** Raw SQL `DELETE` on core entities **FORBIDDEN**. Stateful tables MUST include `created_at DATETIME NOT NULL`, `updated_at DATETIME NULL`, `deleted_at DATETIME NULL` (`NULL` = active).
- **4.8 Querying:** `SELECT *` **FORBIDDEN**—all queries (including Model queries) MUST specify columns via `->select(...)`. Raw `$db->query()` **FORBIDDEN** except approved complex aggregate reports.
- **4.9 Transactions:** >1 DB write MUST use `$db->transException(true)->transStart()`, `transComplete()`, `catch (\Throwable $e)` -> `transRollback()` + `log_message('critical', ...)` + rethrow `$e`.
- **4.10 Migrations & Seeders:**
  - Schema changes strictly via Migrations (manual DDL **FORBIDDEN**). Active dev defects revise the initial creation migration + `php spark migrate:refresh --all`; deployed production uses non-breaking forward migrations.
  - On-demand unification merges alters into the base `createTable()` and purges redundant staged alters (in prod, only after rollback or `migrations` registry reconciliation).
  - Migrations are DDL-only (DML `INSERT`/`UPDATE`/`DELETE` **FORBIDDEN**). `down()` MUST revert `up()` in reverse-dependency order wrapped in `$this->db->disableForeignKeyChecks()` / `enableForeignKeyChecks()`.
  - Seeders (`php spark db:seed MainSeeder`) MUST be declarative and idempotent with all FKs populated (`for`/`while`/`%`/`rand()` **FORBIDDEN**).
  - Provisioning: `php spark migrate --all` -> (if conflict) `php spark migrate:refresh --all` -> `php spark db:seed MainSeeder` -> `php spark migrate:status`.

---

## Part 5: Code Quality, Typing & Documentation

- **Format & Strictness:** `PSR-12`; `declare(strict_types=1);` as first statement, followed by one blank line and `namespace`.
- **PHPDoc:** Classes MUST have `@package`, `@author`, `@since`; methods MUST have `@param`, `@return`, `@throws`; properties MUST have PHPDoc type/description + native types.
- **Naming:** `PascalCase` classes; `camelCase` methods/properties/vars; `snake_case` DB columns/array keys; `_privateHelper()` prefixed with `_`, grouped under `// --- Helper Methods ---` BEFORE public callers.
- **Return Contracts:** Explicit native return types on all methods; PHPStan generics (`array<int, DomainEntity>`) on all array returns.

---

## Part 6: Request, Response & Protocol

- **6.1 Routing:** `public bool $autoRoute = false;` in `app/Config/Routing.php`. Route closures **FORBIDDEN**. Every route MUST be named (`'as' => ...`) and resolved via `url_to()` (hardcoded URIs **FORBIDDEN**).
- **6.2 PRG (`POST`/`PUT`/`PATCH`/`DELETE`):** Never return `view()` from POST. Validate -> `$this->validator->getValidated()` -> Entity -> Service -> flash -> `redirect()->to(url_to(...))`. On `\Throwable`, `log_message('error', ...)` and flash sanitized error (no `$e->getMessage()`).
- **6.3 AJAX Envelope:** `['status' => 'success'|'error'|'validation_error', 'message' => string, 'result' => mixed, 'errors' => array, 'csrf_token' => csrf_hash()]`.
- **6.4 SSE:** `Content-Type: text/event-stream`; `ob_flush(); flush();` after each packet; `session_write_close()` before loop; first packet sends `csrf_hash()`.

---

## Part 7: Security Protocols

- **7.1 CSRF:** Globally enabled in `app/Config/Security.php`. Forms MUST use `form_open()` (duplicate `csrf_field()` **FORBIDDEN**). All JSON responses MUST include `csrf_token`. Layout `<meta>` tags required.
- **7.2 Validation:** Validate all input strictly; retrieve exclusively via `$this->validator->getValidated()`.
- **7.3 Secrets:** Store ONLY in `.env` (`.gitignore`d); access ONLY via `env('KEY')` or `service('settings')` (hardcoded secrets **FORBIDDEN**).
- **7.4 Throttling:** Apply `App\Filters\ThrottleFilter` (`throttle` alias in `app/Config/Filters.php`), e.g., `'filter' => 'throttle:5,60'`, to auth, payment, and heavy endpoints.
- **7.5 XSS:** Escape ALL dynamic scalar values, URLs, tokens, and dates via `esc($v, 'html'|'attr'|'js')`. Exempt ONLY structural helpers: `form_open()`, `form_open_multipart()`, `form_close()`, `$this->include()`, `$this->renderSection()`, `csp_script_nonce()`, `csp_style_nonce()`, and `json_encode(..., JSON_THROW_ON_ERROR)` inside `<script type="application/ld+json">`.

---

## Part 8: Native File Handling, Storage & Streaming

- **Prohibited:** `$_FILES`, `header()`, `file_get_contents()`, `readfile()`.
- **8.1 Isolation & Stateless Cleanup:** `WRITEPATH` (`writable/`) for private/auth files (via authorized controller streaming only); `FCPATH` (`public/`) for public assets only. Persistent local storage and binary/resource handles in session are **FORBIDDEN**. Offload to cloud storage (S3/GCS), then destroy temp file via explicit `unlink($path)` (`@unlink()` **FORBIDDEN**; failure MUST throw `RuntimeException` and log `critical` on rollback). Create staging dirs via `mkdir($dir, 0755, true)` with explicit error check.
- **8.2–8.4 Uploads:** Single `$this->request->getFile('key')`, multiple `getFileMultiple('key')`. Validate in Controller (`uploaded`, `max_size`, `mime_in`, `ext_in`, `is_image`, `max_dims`), assert `$file instanceof UploadedFile && $file->isValid() && ! $file->hasMoved()` (rollback multiple loops on error via `$file->getErrorString()`), inspect bytes via `$file->getMimeType()` (never client ext/headers), generate `$file->getRandomName()` (never `getClientName()`), and `$file->move($dir, $name)` inside Service.
- **8.5 Downloads:** Use `$this->response->download($path, null)` (`CodeIgniter\HTTP\DownloadResponse` handles MIME, `Content-Length`, `Content-Disposition`, `Content-Transfer-Encoding: binary`, chunked streaming, buffer cleansing; chain `->inline()` / `->setFileName(...)`). Endpoints MUST sit behind auth routes and verify actor permissions/record status (`403`/`404`) before streaming.
- **8.6 Upload Views:** MUST use `form_open_multipart(url_to(...))` (raw `<form enctype=...>` **FORBIDDEN**) with Bootstrap 5.3 `.is-invalid`/`.invalid-feedback` bindings.

---

## Part 9: Error Handling, Logging & Environment

- **Exceptions:** Controllers catch `\Throwable`, log context, and return sanitized messages; Service layers bubble domain exceptions or `['status' => 'error', 'message' => ...]`. Production errors use `app/Views/errors/html/production.php`.
- **Logging:** `log_message('critical'|'error'|'info', $msg, $context)` (`critical`: DB/disk/cleanup failure; `error`: runtime/transaction/upload failure; `info`: business milestones).
- **Environments:** `development` (`CI_ENVIRONMENT=development`, `display_errors=1`, Toolbar On); `testing` (`testing`, `0`, Off); `production` (`production`, `0`, Off). Committing `d()`, `dd()`, `die()`, `var_dump()`, `print_r()` is **FORBIDDEN**.

---

## Part 10: Frontend Blueprints & Bootstrap 5.3

- **Framework:** Native Bootstrap 5.3+ utilities/components (`.container`, `.container-fluid`, `.row`, `.col-*`, `.vstack`, `.hstack`, `.gap-*`, `.form-floating`, `.btn-primary`, `.btn-outline-secondary`, `.btn-outline-danger`/`.btn-danger`, `.hstack.gap-2`). Custom component CSS, inline `style=""`, hardcoded hex colors, and third-party CSS frameworks are **FORBIDDEN** (only `--theme-*` Bootstrap CSS var mappings and `:focus-visible` in layout `<style <?= csp_style_nonce() ?>>` permitted).
- **Themes & Layouts:** `<html data-bs-theme="light|dark">`. Views MUST extend `layouts/default`; layout MUST NOT couple to module routes (inject module nav via `renderSection('nav_items')`).
- **SEO & JSON-LD:** Render `pageTitle`, `metaDescription`, `metaKeywords`, `canonicalUrl`, `robotsTag`, `metaImage`, `schemaJson`. Serialize `$schemaJson` ONLY via `json_encode($schemaJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)` (manual JSON concat or `esc(..., 'js')` **FORBIDDEN**).

---

## Part 11: Bootstrap 5.3 Pagination Template

`app/Views/Pagers/bs_full.php` (mandatory standard):

```php
<?php

declare(strict_types=1);

use CodeIgniter\Pager\PagerRenderer;

/** @var PagerRenderer $pager */
$pager->setSurroundCount(2);
?>
<nav aria-label="<?= esc((string) lang('Pager.pageNavigation'), 'attr') ?>">
    <ul class="pagination pagination-sm justify-content-center flex-wrap mb-0">
        <?php if ($pager->hasPreviousPage()) : ?>
            <li class="page-item"><a href="<?= esc((string) $pager->getFirst(), 'attr') ?>" class="page-link" aria-label="<?= esc((string) lang('Pager.first'), 'attr') ?>"><span aria-hidden="true">&laquo;&laquo;</span></a></li>
            <li class="page-item"><a href="<?= esc((string) $pager->getPreviousPage(), 'attr') ?>" class="page-link" aria-label="<?= esc((string) lang('Pager.previous'), 'attr') ?>"><span aria-hidden="true">&laquo;</span></a></li>
        <?php else : ?>
            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&laquo;&laquo;</span></li>
            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&laquo;</span></li>
        <?php endif ?>
        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>" <?= $link['active'] ? 'aria-current="page"' : '' ?>><a href="<?= esc((string) $link['uri'], 'attr') ?>" class="page-link"><?= esc((string) $link['title'], 'html') ?></a></li>
        <?php endforeach ?>
        <?php if ($pager->hasNextPage()) : ?>
            <li class="page-item"><a href="<?= esc((string) $pager->getNextPage(), 'attr') ?>" class="page-link" aria-label="<?= esc((string) lang('Pager.next'), 'attr') ?>"><span aria-hidden="true">&raquo;</span></a></li>
            <li class="page-item"><a href="<?= esc((string) $pager->getLast(), 'attr') ?>" class="page-link" aria-label="<?= esc((string) lang('Pager.last'), 'attr') ?>"><span aria-hidden="true">&raquo;&raquo;</span></a></li>
        <?php else : ?>
            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&raquo;</span></li>
            <li class="page-item disabled"><span class="page-link" aria-hidden="true">&raquo;&raquo;</span></li>
        <?php endif ?>
    </ul>
</nav>
```

---

## Part 12: Tooling, CLI & Publisher

- **Spark Commands:** Extend `CodeIgniter\CLI\BaseCommand` in `app/Commands/` or module `Commands/`, defining `$group`, `$name`, `$description`, `$usage`, `$arguments`, `$options`, and `run(array $params): int` returning `EXIT_SUCCESS`.
- **Prompts & Signals:** Use `CLI::prompt()` / `CLI::promptByKey()` with validation callbacks. Daemons MUST use `CodeIgniter\CLI\SignalTrait` (`$this->registerSignals()`, `$this->isRunning()`) for `SIGINT`/`SIGTERM` cleanup.
- **Assets (`Publisher`):** Deploy vendor assets to `public/assets/vendor/` via `CodeIgniter\Publisher\Publisher`. Manual `public/` copy-paste and runtime external CDN links in layouts are **FORBIDDEN**.

---

## Part 13: Advanced Architecture

- **View Cells & Decorators:** Reusable data-fetching UI widgets MUST be `CodeIgniter\View\Cells\Cell` (`view_cell(...)`); service calls in raw views **FORBIDDEN**. Global transforms MUST implement `CodeIgniter\View\ViewDecoratorInterface` in `Config\View::$decorators`.
- **Events:** Non-blocking secondary actions (emails, metrics) MUST trigger via `CodeIgniter\Events\Events::trigger('eventName', $payload)`.
- **CSP & CORS:** Strict CSP in `app/Config/ContentSecurityPolicy.php` with `<?= csp_script_nonce() ?>` / `<?= csp_style_nonce() ?>` (`{csp-script-nonce}` / `{csp-style-nonce}`). Manage CORS via `CodeIgniter\Filters\Cors` (`cors` alias, `app/Config/Cors.php`); wildcard `*` origins in prod **FORBIDDEN**.
- **Worker Mode:** Under FrankenPHP (`php spark worker:install`), RoadRunner, or Swoole, services MUST NOT store mutable request state in static properties; reset request-scoped services via `Services::reset()` or non-shared factories.

---

## Part 14: Testing Architecture

- **Suite & Clock:** PHPUnit (`php spark test` / `./vendor/bin/phpunit`) REQUIRED for every module, service, model, controller, filter, and command. Extend `CodeIgniter\Test\CIUnitTestCase`, call `parent::setUp()`/`parent::tearDown()`, freeze time with `Time::setTestNow(...)`, and restore with `Time::setTestNow()`.
- **Database (`DatabaseTestTrait`):** Declare `$migrate = true;` and `$namespace = 'App\Modules\[ModuleName]';` (or `null`); auto-rollback keeps DB clean; assert via `seeInDatabase()`, `dontSeeInDatabase()`, `seeNumRecords()`.
- **Data (`Fabricator`):** Use `CodeIgniter\Test\Fabricator` with model `fake(Generator &$faker): array`. Raw SQL inserts in tests **FORBIDDEN**.
- **HTTP (`FeatureTestTrait`):** Execute `$this->get/post/put/delete()` (pass `csrf_token() => csrf_hash()` on mutations); assert `TestResponse` (`assertStatus()`, `assertOK()`, `assertRedirectTo()`, `assertSessionHas()`, `assertSee()`).
- **Mocks:** `\Config\Services::injectMock('name', $mock)`; reset in `tearDown()` via `\Config\Services::reset()`.

---

## Part 15: Boot & Deployment SOP

- **Local Boot:** `composer install` -> `php spark migrate --all` -> `php spark db:seed MainSeeder`.
- **Production Deploy:** (1) `CI_ENVIRONMENT = production`, `display_errors = 0`; (2) `composer install --no-dev --optimize-autoloader --no-interaction`; (3) `php spark optimize`; (4) `php spark migrate --all`; (5) Web root strictly `/public` (block `/app`, `/system`, `/writable`, `/vendor`; exclusive web user write to `writable/`).

---

## Part 16: Endpoint Verification & Log-Auditing Loop

1. Verify all `php spark routes` via headless browser or `curl -i -L <url>` (assuming template correctness without execution is **FORBIDDEN**).
2. Inspect `writable/logs/` simultaneously for exceptions, DB warnings, or PHP notices.
3. On failure: analyze trace -> fix per MVC-S -> purge `writable/logs/` -> re-test -> repeat until all routes pass with zero log entries.

---

## Appendix: Canonical Reference Implementation

### 1. `app/Config/Autoload.php`

```php
public array $psr4 = [
    APP_NAMESPACE              => APPPATH,
    'App\Modules\[ModuleName]' => APPPATH . 'Modules/[ModuleName]',
];
```

### 2. `app/Modules/[ModuleName]/Config/Services.php`

```php
<?php

declare(strict_types=1);

namespace App\Modules\[ModuleName]\Config;

use App\Modules\[ModuleName]\Libraries\DomainService;
use App\Modules\[ModuleName]\Models\DomainModel;
use CodeIgniter\Config\BaseService;

/** @package App\Modules\[ModuleName]\Config @author Senior Developer @since 1.0.0 */
class Services extends BaseService
{
    /**
     * @param bool $getShared
     * @return DomainService
     */
    public static function domainService(bool $getShared = true): DomainService
    {
        if ($getShared) {
            /** @var DomainService $service */
            $service = static::getSharedInstance('domainService');
            return $service;
        }
        return new DomainService(new DomainModel());
    }
}
```

### 3. `app/Modules/[ModuleName]/Config/Routes.php`

```php
<?php

declare(strict_types=1);

namespace App\Modules\[ModuleName]\Config;

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->group('domain', ['namespace' => 'App\Modules\[ModuleName]\Controllers'], static function (RouteCollection $routes): void {
    $routes->get('record', 'DomainController::index', ['as' => 'domain.record.index']);
    $routes->get('record/new', 'DomainController::new', ['as' => 'domain.record.new']);
    $routes->post('record', 'DomainController::create', ['as' => 'domain.record.create', 'filter' => 'throttle:5,60']);
    $routes->post('file/upload', 'DomainController::upload', ['as' => 'domain.file.upload', 'filter' => ['auth', 'throttle:10,60']]);
    $routes->get('file/download/(:num)', 'DomainController::download/$1', ['as' => 'domain.file.download', 'filter' => ['auth', 'throttle:30,60']]);
});
```

### 4. `app/Filters/ThrottleFilter.php`

```php
<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use InvalidArgumentException;

/** @package App\Filters @author Senior Developer @since 1.0.0 */
class ThrottleFilter implements FilterInterface
{
    /**
     * @param RequestInterface        $request
     * @param array<int, string>|null $arguments [limit, seconds]
     * @return RequestInterface|ResponseInterface|string|null
     * @throws InvalidArgumentException
     */
    public function before(RequestInterface $request, $arguments = null): RequestInterface|ResponseInterface|string|null
    {
        if (! $request instanceof IncomingRequest) {
            return null;
        }
        if ($arguments === null || count($arguments) < 2) {
            throw new InvalidArgumentException('ThrottleFilter requires throttle:[limit],[seconds]');
        }
        $key = 'throttle_' . md5($request->getIPAddress() . $request->getRoutePath());
        if (service('throttler')->check($key, (int) $arguments[0], (int) $arguments[1]) === false) {
            if ($request->isAJAX() || str_contains($request->getHeaderLine('Accept'), 'application/json')) {
                return service('response')->setStatusCode(429)->setJSON([
                    'status' => 'error', 'message' => 'Too many requests. Please try again later.',
                    'result' => null, 'errors' => [], 'csrf_token' => csrf_hash(),
                ]);
            }
            return redirect()->back()->with('error', 'Too many requests. Please wait a moment before trying again.');
        }
        return null;
    }

    /**
     * @param RequestInterface        $request
     * @param ResponseInterface       $response
     * @param array<int, string>|null $arguments
     * @return ResponseInterface|null
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ?ResponseInterface
    {
        return null;
    }
}
```

### 5. `app/Modules/[ModuleName]/Database/Migrations/2026-09-05-000001_CreateDomainTable.php`

```php
<?php

declare(strict_types=1);

namespace App\Modules\[ModuleName]\Database\Migrations;

use CodeIgniter\Database\Migration;

/** @package App\Modules\[ModuleName]\Database\Migrations @author Senior Developer @since 1.0.0 */
class CreateDomainTable extends Migration
{
    /** @return void */
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'domain_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'value'      => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => '0.00', 'null' => false],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => false],
            'metadata'   => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('domain_id');
        $this->forge->addKey(['domain_id', 'is_active']);
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('domain_id', 'domains', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('domain_table', true, ['ENGINE' => 'InnoDB', 'CHARACTER SET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_unicode_ci', 'ROW_FORMAT' => 'DYNAMIC']);
    }

    /** @return void */
    public function down(): void
    {
        $this->db->disableForeignKeyChecks();
        $this->forge->dropTable('domain_table', true);
        $this->db->enableForeignKeyChecks();
    }
}
```

### 6. `app/Modules/[ModuleName]/Entities/DomainEntity.php` & `Models/DomainModel.php`

```php
<?php

declare(strict_types=1);

namespace App\Modules\[ModuleName]\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

/**
 * @package App\Modules\[ModuleName]\Entities @author Senior Developer @since 1.0.0
 * @property int $id @property int $domain_id @property string $value @property bool $is_active
 * @property array<string, mixed>|null $metadata @property Time|null $created_at @property Time|null $updated_at @property Time|null $deleted_at
 */
class DomainEntity extends Entity
{
    /** @var array<string, string> */
    protected $casts = ['id' => 'integer', 'domain_id' => 'integer', 'value' => 'string', 'is_active' => 'boolean', 'metadata' => '?json-array'];

    /** @var array<int, string> */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
```

```php
<?php

declare(strict_types=1);

namespace App\Modules\[ModuleName]\Models;

use App\Modules\[ModuleName]\Entities\DomainEntity;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Model;

/** @package App\Modules\[ModuleName]\Models @author Senior Developer @since 1.0.0 @property BaseConnection $db */
class DomainModel extends Model
{
    /** @var string */
    protected $table = 'domain_table';
    /** @var string */
    protected $primaryKey = 'id';
    /** @var class-string */
    protected $returnType = DomainEntity::class;
    /** @var array<int, string> */
    protected $allowedFields = ['domain_id', 'value', 'is_active', 'metadata'];
    /** @var bool */
    protected $useTimestamps = true;
    /** @var string */
    protected $dateFormat = 'datetime';
    /** @var string */
    protected $createdField = 'created_at';
    /** @var string */
    protected $updatedField = 'updated_at';
    /** @var string */
    protected $deletedField = 'deleted_at';
    /** @var bool */
    protected $useSoftDeletes = true;
}
```

### 7. `app/Modules/[ModuleName]/Libraries/DomainService.php`

```php
<?php

declare(strict_types=1);

namespace App\Modules\[ModuleName]\Libraries;

use App\Modules\[ModuleName]\Entities\DomainEntity;
use App\Modules\[ModuleName]\Models\DomainModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;
use Throwable;

/** @package App\Modules\[ModuleName]\Libraries @author Senior Developer @since 1.0.0 */
class DomainService
{
    /** @var DomainModel */
    protected DomainModel $domainModel;

    /** @param DomainModel $domainModel */
    public function __construct(DomainModel $domainModel)
    {
        $this->domainModel = $domainModel;
    }

    /**
     * @param DomainEntity $entity
     * @return DomainEntity
     * @throws Throwable
     */
    public function createRecord(DomainEntity $entity): DomainEntity
    {
        $db = $this->domainModel->db;
        try {
            $db->transException(true)->transStart();
            $id = $this->domainModel->insert($entity);
            if ($id === false) {
                throw new RuntimeException('Database record insertion failure.');
            }
            /** @var DomainEntity|null $savedEntity */
            $savedEntity = $this->domainModel->select('id, domain_id, value, is_active, metadata, created_at, updated_at, deleted_at')->find($id);
            if (! $savedEntity instanceof DomainEntity) {
                throw new RuntimeException('Failed to retrieve newly created domain entity.');
            }
            $db->transComplete();
            return $savedEntity;
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('critical', 'DomainService::createRecord failed: ' . $e->getMessage(), ['entity' => $entity->toArray(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    /** @return array<int, DomainEntity> */
    public function getAllRecords(): array
    {
        /** @var array<int, DomainEntity> $records */
        $records = $this->domainModel->select('id, domain_id, value, is_active, created_at')->findAll();
        return $records;
    }

    /**
     * @param UploadedFile $file
     * @param int          $domainId
     * @param int          $actorId
     * @return string
     * @throws Throwable
     */
    public function processUploadedDocument(UploadedFile $file, int $domainId, int $actorId): string
    {
        $record = $this->domainModel->select('id, domain_id, is_active')->where('domain_id', $domainId)->first();
        if (! $record instanceof DomainEntity) {
            throw new RuntimeException('Target domain record not found or inaccessible.');
        }
        $dir = WRITEPATH . 'uploads/domain/' . $domainId . '/';
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw new RuntimeException('Failed to create staging directory: ' . $dir);
        }
        $secureName = $file->getRandomName();
        $file->move($dir, $secureName);
        $stagedPath = $dir . $secureName;
        try {
            $remoteLocator = 'cloud_bucket://domains/' . $domainId . '/' . $secureName;
            if (! unlink($stagedPath)) {
                throw new RuntimeException('Failed to purge ephemeral local staging: ' . $stagedPath);
            }
            log_message('info', 'DomainService::processUploadedDocument completed', ['domain_id' => $domainId, 'actor_id' => $actorId, 'locator' => $remoteLocator]);
            return $remoteLocator;
        } catch (Throwable $e) {
            if (is_file($stagedPath) && ! unlink($stagedPath)) {
                log_message('critical', 'Failed to purge staged file during rollback', ['staged_path' => $stagedPath, 'domain_id' => $domainId]);
            }
            log_message('error', 'DomainService::processUploadedDocument failed: ' . $e->getMessage(), ['domain_id' => $domainId, 'actor_id' => $actorId, 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    /**
     * @param int $domainId
     * @param int $actorId
     * @return string
     * @throws RuntimeException
     */
    public function getAuthorizedDocumentPath(int $domainId, int $actorId): string
    {
        $record = $this->domainModel->select('id, domain_id, is_active, metadata')->where('domain_id', $domainId)->where('is_active', 1)->first();
        if (! $record instanceof DomainEntity) {
            throw new RuntimeException('Unauthorized or inactive domain record for actor: ' . $actorId);
        }
        $meta = $record->metadata;
        if (! is_array($meta) || ! isset($meta['staged_filename']) || ! is_string($meta['staged_filename'])) {
            throw new RuntimeException('Document reference missing from domain metadata.');
        }
        $path = WRITEPATH . 'uploads/domain/' . $domainId . '/' . basename($meta['staged_filename']);
        if (! is_file($path)) {
            throw new RuntimeException('Secure asset does not exist on filesystem.');
        }
        return $path;
    }
}
```

### 8. `app/Modules/[ModuleName]/Controllers/DomainController.php`

```php
<?php

declare(strict_types=1);

namespace App\Modules\[ModuleName]\Controllers;

use App\Controllers\BaseController;
use App\Modules\[ModuleName]\Entities\DomainEntity;
use App\Modules\[ModuleName]\Libraries\DomainService;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/** @package App\Modules\[ModuleName]\Controllers @author Senior Developer @since 1.0.0 */
class DomainController extends BaseController
{
    /** @var array<int, string> */
    protected $helpers = ['form'];
    /** @var DomainService */
    protected DomainService $domainService;

    public function __construct()
    {
        /** @var DomainService $service */
        $service = service('domainService');
        $this->domainService = $service;
    }

    /** @return string|ResponseInterface */
    public function index(): string|ResponseInterface
    {
        return view('App\Modules\[ModuleName]\Views\record\index', [
            'pageTitle' => 'Domain Records', 'metaDescription' => 'Review active domain records.', 'metaKeywords' => 'domain, record, index',
            'canonicalUrl' => url_to('domain.record.index'), 'robotsTag' => 'noindex, follow',
            'metaImage' => base_url('assets/images/og-default.jpg'), 'schemaJson' => null, 'data' => $this->domainService->getAllRecords(),
        ]);
    }

    /** @return string|ResponseInterface */
    public function new(): string|ResponseInterface
    {
        return view('App\Modules\[ModuleName]\Views\record\new', [
            'pageTitle' => 'Create Domain Record', 'metaDescription' => 'Submit a new domain entry.', 'metaKeywords' => 'domain, record, new',
            'canonicalUrl' => url_to('domain.record.new'), 'robotsTag' => 'noindex, follow',
            'metaImage' => base_url('assets/images/og-default.jpg'), 'schemaJson' => null,
        ]);
    }

    /** @return string|ResponseInterface */
    public function create(): string|ResponseInterface
    {
        if (! $this->validate(['domain_id' => 'required|is_natural_no_zero', 'value' => 'required|decimal'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        /** @var array{domain_id: string, value: string} $valid */
        $valid = $this->validator->getValidated();
        try {
            $this->domainService->createRecord(new DomainEntity([
                'domain_id' => (int) $valid['domain_id'], 'value' => $valid['value'], 'is_active' => false, 'metadata' => ['source' => 'web_admin'],
            ]));
            return redirect()->to(url_to('domain.record.index'))->with('success', 'Record created successfully.');
        } catch (Throwable $e) {
            log_message('error', 'DomainController::create failed: ' . $e->getMessage(), ['domain_id' => (int) $valid['domain_id'], 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'Failed to save record. Please try again.');
        }
    }

    /** @return string|ResponseInterface */
    public function upload(): string|ResponseInterface
    {
        $rules = [
            'domain_id' => 'required|is_natural_no_zero',
            'document'  => ['label' => 'Domain Document', 'rules' => ['uploaded[document]', 'max_size[document,5120]', 'mime_in[document,application/pdf,image/jpeg,image/png]', 'ext_in[document,pdf,jpg,jpeg,png]']],
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        /** @var array{domain_id: string} $valid */
        $valid = $this->validator->getValidated();
        $file  = $this->request->getFile('document');
        if (! $file instanceof UploadedFile || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->back()->withInput()->with('error', 'The uploaded file is invalid or could not be processed.');
        }
        $actorId = (int) session('user_id');
        if ($actorId <= 0) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }
        try {
            $this->domainService->processUploadedDocument($file, (int) $valid['domain_id'], $actorId);
            return redirect()->to(url_to('domain.record.index'))->with('success', 'File uploaded and processed successfully.');
        } catch (Throwable $e) {
            log_message('error', 'DomainController::upload failed: ' . $e->getMessage(), ['domain_id' => (int) $valid['domain_id'], 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', 'File processing failed. Please try again.');
        }
    }

    /** @param int $domainId @return DownloadResponse|ResponseInterface */
    public function download(int $domainId): DownloadResponse|ResponseInterface
    {
        $actorId = (int) session('user_id');
        if ($actorId <= 0) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }
        try {
            $path = $this->domainService->getAuthorizedDocumentPath($domainId, $actorId);
            if (! is_file($path)) {
                return $this->response->setStatusCode(404, 'Requested file could not be found.');
            }
            $download = $this->response->download($path, null);
            return $download instanceof DownloadResponse ? $download->setFileName('domain_export_' . $domainId . '.pdf') : redirect()->to(url_to('domain.record.index'))->with('error', 'Unable to initialize download.');
        } catch (Throwable $e) {
            log_message('error', 'DomainController::download failed: ' . $e->getMessage(), ['domain_id' => $domainId, 'actor_id' => $actorId, 'trace' => $e->getTraceAsString()]);
            return $this->response->setStatusCode(403, 'Unauthorized or invalid file request.');
        }
    }
}
```

### 9. `app/Views/layouts/default.php` & `app/Views/partials/flash_messages.php`

```php
<?php

declare(strict_types=1);

/**
 * @var string $pageTitle @var string $metaDescription @var string $metaKeywords
 * @var string $robotsTag @var string $canonicalUrl @var string $metaImage @var array<string, mixed>|null $schemaJson
 */
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= esc(csrf_hash(), 'attr') ?>"><meta name="csrf-header" content="<?= esc(csrf_header(), 'attr') ?>">
    <title><?= esc($pageTitle, 'html') ?></title>
    <meta name="description" content="<?= esc($metaDescription, 'attr') ?>"><meta name="keywords" content="<?= esc($metaKeywords, 'attr') ?>"><meta name="robots" content="<?= esc($robotsTag, 'attr') ?>">
    <link rel="canonical" href="<?= esc($canonicalUrl, 'attr') ?>">
    <meta property="og:type" content="website"><meta property="og:title" content="<?= esc($pageTitle, 'attr') ?>"><meta property="og:description" content="<?= esc($metaDescription, 'attr') ?>"><meta property="og:url" content="<?= esc($canonicalUrl, 'attr') ?>"><meta property="og:image" content="<?= esc($metaImage, 'attr') ?>">
    <meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="<?= esc($pageTitle, 'attr') ?>"><meta name="twitter:description" content="<?= esc($metaDescription, 'attr') ?>"><meta name="twitter:image" content="<?= esc($metaImage, 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(base_url('assets/vendor/bootstrap/css/bootstrap.min.css'), 'attr') ?>">
    <?php if ($schemaJson !== null) : ?>
        <script type="application/ld+json"><?= json_encode($schemaJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) ?></script>
    <?php endif ?>
    <style <?= csp_style_nonce() ?>>
        :root,[data-bs-theme="light"],[data-bs-theme="dark"]{--theme-bg-canvas:var(--bs-body-bg);--theme-card-bg:var(--bs-card-bg);--theme-text-primary:var(--bs-body-color);--theme-border-color:var(--bs-border-color);--theme-accent:var(--bs-primary)}
        :focus-visible{outline:2px solid var(--theme-accent)!important;outline-offset:3px!important}
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body class="bg-body-tertiary text-body min-vh-100 d-flex flex-column">
    <header class="navbar navbar-expand-lg bg-body border-bottom"><div class="container">
        <a class="navbar-brand fw-bold" href="<?= esc(base_url('/'), 'attr') ?>">Application Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent" aria-controls="navContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navContent"><ul class="navbar-nav me-auto mb-2 mb-lg-0"><?= $this->renderSection('nav_items') ?></ul></div>
    </div></header>
    <main class="container my-4 flex-grow-1"><?= $this->include('partials/flash_messages') ?><?= $this->renderSection('content') ?></main>
    <footer class="bg-body border-top py-3 mt-auto"><div class="container text-center text-body-secondary small">&copy; <?= esc(date('Y'), 'html') ?> Modular Architecture.</div></footer>
    <script src="<?= esc(base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'), 'attr') ?>"></script>
    <script <?= csp_script_nonce() ?>>
        window.getCSRFToken=function(){const m=document.querySelector('meta[name="csrf-token"]');return m?m.getAttribute('content'):''};
        window.updateCSRFToken=function(t){const m=document.querySelector('meta[name="csrf-token"]');if(m){m.setAttribute('content',t)}document.querySelectorAll('input[name="<?= esc(csrf_token(), 'js') ?>"]').forEach(function(i){i.value=t})};
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
```

```php
<?php if (session()->has('success')) : ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?= esc((string) session('success'), 'html') ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif ?>
<?php if (session()->has('error')) : ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?= esc((string) session('error'), 'html') ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif ?>
<?php if (session()->has('errors')) : ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><ul class="mb-0"><?php foreach ((array) session('errors') as $error) : ?><li><?= esc((string) $error, 'html') ?></li><?php endforeach ?></ul><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif ?>
```

### 10. Module Views (`Views/record/{index,new}.php`) & Upload Form Snippet

```php
<?php declare(strict_types=1); /** @var array<int, \App\Modules\[ModuleName]\Entities\DomainEntity> $data */ ?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('nav_items') ?><li class="nav-item"><a class="nav-link" href="<?= esc(url_to('domain.record.index'), 'attr') ?>">Records</a></li><?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="vstack gap-4">
    <div class="d-flex justify-content-between align-items-center"><h1 class="h3 mb-0">Domain Records</h1><a href="<?= esc(url_to('domain.record.new'), 'attr') ?>" class="btn btn-primary">Create Record</a></div>
    <div class="card shadow-sm border"><div class="card-body p-0">
        <?php if ($data === []) : ?><div class="p-4 text-center text-body-secondary">No domain records found.</div>
        <?php else : ?>
            <div class="table-responsive"><table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light"><tr><th scope="col" class="ps-3">ID</th><th scope="col">Domain Ref</th><th scope="col">Value</th><th scope="col">Status</th><th scope="col" class="pe-3 text-end">Created</th></tr></thead>
                <tbody><?php foreach ($data as $record) : ?><tr>
                    <td class="ps-3"><?= esc((string) $record->id, 'html') ?></td><td><?= esc((string) $record->domain_id, 'html') ?></td><td>$<?= esc($record->value, 'html') ?></td>
                    <td><?php if ($record->is_active) : ?><span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Active</span><?php else : ?><span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Inactive</span><?php endif ?></td>
                    <td class="pe-3 text-end text-body-secondary small"><?= esc($record->created_at !== null ? $record->created_at->toLocalizedString('yyyy-MM-dd HH:mm') : '', 'html') ?></td>
                </tr><?php endforeach ?></tbody>
            </table></div>
        <?php endif ?>
    </div></div>
</div>
<?= $this->endSection() ?>
```

```php
<?php declare(strict_types=1); /** @var string $pageTitle */ ?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('nav_items') ?><li class="nav-item"><a class="nav-link" href="<?= esc(url_to('domain.record.index'), 'attr') ?>">Records</a></li><?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="row justify-content-center"><div class="col-12 col-md-8 col-lg-6"><div class="card shadow-sm border">
    <div class="card-header bg-body py-3"><h1 class="h4 card-title mb-0">Create Domain Record</h1></div>
    <div class="card-body p-4">
        <?= form_open(url_to('domain.record.create')) ?>
        <div class="form-floating mb-3">
            <input type="number" name="domain_id" class="form-control <?= session('errors.domain_id') ? 'is-invalid' : '' ?>" id="domainId" placeholder="1029" value="<?= esc((string) old('domain_id', ''), 'attr') ?>" required>
            <label for="domainId">Domain ID</label>
            <?php if (session('errors.domain_id')) : ?><div class="invalid-feedback"><?= esc((string) session('errors.domain_id'), 'html') ?></div><?php endif ?>
        </div>
        <div class="form-floating mb-4">
            <input type="number" name="value" step="0.01" class="form-control <?= session('errors.value') ? 'is-invalid' : '' ?>" id="recordValue" placeholder="100.00" value="<?= esc((string) old('value', ''), 'attr') ?>" required>
            <label for="recordValue">Monetary Value</label>
            <?php if (session('errors.value')) : ?><div class="invalid-feedback"><?= esc((string) session('errors.value'), 'html') ?></div><?php endif ?>
        </div>
        <div class="hstack gap-2 justify-content-end"><a href="<?= esc(url_to('domain.record.index'), 'attr') ?>" class="btn btn-outline-secondary">Cancel</a><button type="submit" class="btn btn-primary">Save Record</button></div>
        <?= form_close() ?>
    </div>
</div></div></div>
<?= $this->endSection() ?>
```

```php
<?= form_open_multipart(url_to('domain.file.upload')) ?>
    <div class="mb-3"><label for="domainIdInput" class="form-label">Domain ID</label><input class="form-control <?= session('errors.domain_id') ? 'is-invalid' : '' ?>" type="number" id="domainIdInput" name="domain_id" value="<?= esc((string) old('domain_id', ''), 'attr') ?>" required><?php if (session('errors.domain_id')) : ?><div class="invalid-feedback"><?= esc((string) session('errors.domain_id'), 'html') ?></div><?php endif ?></div>
    <div class="mb-3"><label for="domainDocument" class="form-label">Upload Document</label><input class="form-control <?= session('errors.document') ? 'is-invalid' : '' ?>" type="file" id="domainDocument" name="document" required><?php if (session('errors.document')) : ?><div class="invalid-feedback"><?= esc((string) session('errors.document'), 'html') ?></div><?php endif ?></div>
    <button type="submit" class="btn btn-primary">Submit Upload</button>
<?= form_close() ?>
```

### 11. `app/Commands/DomainSyncCommand.php` & `tests/Controllers/DomainControllerTest.php`

```php
<?php

declare(strict_types=1);

namespace App\Commands;

use App\Modules\Domain\Libraries\DomainService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/** @package App\Commands @author Senior Developer @since 1.0.0 */
class DomainSyncCommand extends BaseCommand
{
    /** @var string */
    protected $group = 'Domain';
    /** @var string */
    protected $name = 'domain:sync';
    /** @var string */
    protected $description = 'Synchronizes domain catalog records.';
    /** @var string */
    protected $usage = 'domain:sync [options]';
    /** @var array<string, string> */
    protected $arguments = [];
    /** @var array<string, string> */
    protected $options = ['--dry-run' => 'Simulates synchronization without database modification.'];

    /** @param array<string, mixed> $params @return int */
    public function run(array $params): int
    {
        $isDryRun = array_key_exists('dry-run', $params) || CLI::getOption('dry-run') !== null;
        CLI::write('Executing domain sync...', 'yellow');
        /** @var DomainService $service */
        $service = service('domainService');
        $service->syncRecords((bool) $isDryRun);
        CLI::write('Domain sync finished.', 'green');
        return EXIT_SUCCESS;
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Tests\Controllers;

use CodeIgniter\I18n\Time;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;

/** @package Tests\Controllers @author Senior Developer @since 1.0.0 */
class DomainControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    /** @var bool */
    protected $migrate = true;
    /** @var string|null */
    protected $namespace = 'App\Modules\[ModuleName]';

    protected function setUp(): void
    {
        parent::setUp();
        Time::setTestNow('2026-11-13 10:00:00');
        /** @var RouteCollection $routes */
        $routes = service('routes');
        $routes->group('domain', ['namespace' => 'App\Modules\[ModuleName]\Controllers'], static function (RouteCollection $routes): void {
            $routes->get('record', 'DomainController::index', ['as' => 'domain.record.index']);
            $routes->get('record/new', 'DomainController::new', ['as' => 'domain.record.new']);
            $routes->post('record', 'DomainController::create', ['as' => 'domain.record.create']);
        });
    }

    protected function tearDown(): void
    {
        Time::setTestNow();
        Services::reset();
        parent::tearDown();
    }

    public function testIndexViewRendersSuccessfully(): void
    {
        $response = $this->get('/domain/record');
        $response->assertStatus(200);
        $response->assertSee('Domain Records', 'h1');
        $response->assertSee('No domain records found.');
    }

    public function testCreateSavesToDatabaseAndRedirects(): void
    {
        $response = $this->post('/domain/record', ['domain_id' => '1029', 'value' => '520.45', csrf_token() => csrf_hash()]);
        $response->assertRedirectTo(url_to('domain.record.index'));
        $response->assertSessionHas('success');
        $this->seeInDatabase('domain_table', ['domain_id' => 1029, 'value' => '520.45', 'is_active' => 0]);
    }

    public function testCreateFailsValidationOnMissingInput(): void
    {
        $response = $this->post('/domain/record', [csrf_token() => csrf_hash()]);
        $response->assertRedirect();
        $response->assertSessionHas('errors');
        $this->seeNumRecords(0, 'domain_table');
    }
}
```
