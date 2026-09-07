# CodeIgniter 4 System Architecture Standard

## Part 0: Philosophy — Simple vs. Easy

> *"Conflating [Simple and Easy] is why we're drowning in complexity."*
> — **Rich Hickey**

This project adheres to a strict philosophical and architectural standard based on the 2011 talk *"Simple Made Easy"* by Rich Hickey (creator of Clojure). Every architectural decision is weighed against these objective definitions:

### Simple (Objective)

* **Etymology & Definition:** Derived from *simplex*, meaning "one fold" or "one braid." It describes objective system structure, concerns, tasks, and roles. It is defined by the total absence of entanglement.
* **Single Responsibility:** Each component does exactly one thing in complete isolation.
* **Disentangled:** Components are unbraided. They can be modified, refactored, or verified independently without triggering cascading side-effects. Requires deliberate design, but ensures absolute long-term reliability and maintainability. We reject short-term convenience in favor of unentangled, modular designs.
* **The Cost:** Demands deliberate upfront design, abstraction discipline, and continuous refactoring.
* **The Outcome:** Maximum long-term maintainability, deterministic debugging, and linear architectural scalability.

### Easy (Subjective)

* **Etymology & Definition:** Derived from *adjacens*, meaning "lying nearby" or "at hand." It describes developer comfort, familiarity, and immediate accessibility.
* **Near at Hand:** Familiar syntax, copy-paste snippets, adjacent files, and default monolithic shortcuts.
* **The Trap:** What is familiar and fast to type initially creates "complected" (tightly braided) dependencies that cause systemic brittleness over time.
* **The Cost:** Rapid initial velocity that decays into insurmountable technical debt and fragile release cycles.
* **The Outcome:** Fragile dependency graphs, cascading update failures, and high defect rates.

### Comparison Matrix

| Attribute | Simple (Architectural Standard) | Easy (Anti-Pattern) |
| :--- | :--- | :--- |
| **Nature** | Objective system structure | Subjective developer convenience |
| **Focus** | Single responsibility, disentangled | Familiarity, proximity, copy-paste |
| **Effort** | Deliberate upfront design and decoupling | Zero initial friction |
| **Outcome** | Long-term reliability and linear scalability | Rapid start, cascading long-term complexity trap |

### CodeIgniter 4 Paradigm Mappings

* **Anti-Pattern (Easy):** Accessing `$this->request->getPost()` or calling `session()` directly inside Services or Models because it is globally accessible via helpers.
* **Standard (Simple):** Passing explicit typed scalar parameters, DTOs, or Entities into Services. Services remain completely disentangled from HTTP transport protocols.
* **Anti-Pattern (Easy):** Executing database queries inline within Controllers or Views using the active record builder `$db->table()`.
* **Standard (Simple):** Confining database schema interactions strictly within Models and accessing them via constructor-injected dependencies inside Services.

> **Core Mandate:** Architectural purity and structural simplicity (disentanglement) MUST never be sacrificed for developer comfort, brevity, or temporary convenience.

---

## Part 1: Core Meta-Rules

* **Universal / Strict Applicability:** These architectural rules apply unconditionally to **EVERY** file, class, trait, helper, entity, view, and model across the workspace. Zero exceptions are permitted.
* **Project-Agnostic / Explicit Placeholders:** All architectural rules, templates, and examples **MUST** use generic domain tokens (`DomainController`, `DomainService`, `DomainEntity`, `DomainModel`, `domain_id`) rather than temporary project-specific identifiers or volatile domain files.
* **No Ambiguity / Deterministic Language:** All specifications **MUST** strictly adhere to RFC 2119 terminology. The keywords `MUST`, `MUST NOT`, `REQUIRED`, `SHALL`, `SHALL NOT`, `MAY`, and `FORBIDDEN` are mandatory. Permissive phrases (*"should"*, *"generally"*, *"recommended"*, *"best practice"*) are strictly **FORBIDDEN**.
* **Pre-Task Alignment:** This standard **MUST** be loaded and aligned with prior to initiating any design, refactoring, or implementation task.
* **Contextual Verification & Log-Auditing Compliance:** An active self-verification loop on all routing endpoints **MUST** be executed via automated tests, native CLI requests, cURL, or local HTTP commands, checking both front-end response states and writable log files to confirm zero warning or error traces in `writable/logs/` before finishing and declaring any task complete.
* **Automated Static Analysis & Gate Enforcement:** Continuous Integration (CI) pipelines and local pre-commit hooks **MUST** enforce zero-tolerance static analysis via PHPStan (Level 8 or higher) and PHP CodeSniffer (`PSR-12`). Any commit introducing untyped boundary parameters, dynamic properties, or forbidden permissive phrases **SHALL** fail the verification gate.

---

## Part 2: Folder Structure & Modular Architecture

The application strictly enforces a decoupled **Modular Model-View-Controller-Service (MVC-S)** architecture.

### 2.1 Directory Layout

All business features and domain capabilities **MUST** reside within self-contained modules under `app/Modules/`. The root `app/` directory is reserved strictly for shared core infrastructure (e.g., base layouts, core filters, global events).

A module directory **MUST** adhere to this structural layout:

```text
app/Modules/[ModuleName]/
├── Commands/       # Module-specific Spark CLI commands
├── Config/         # Module-specific configurations (Routes.php, Services.php)
│   ├── Routes.php  # REQUIRED: Manual named routes registration
│   └── Services.php# REQUIRED: Module Service Container bindings
├── Controllers/    # HTTP Request/Response orchestration
├── Database/
│   ├── Migrations/ # REQUIRED: Schema definitions and database modifications
│   └── Seeds/      # Initial database state records and data population
├── Entities/       # Type-safe entity records with property casts
├── Helpers/        # Module-specific standalone helper functions ([name]_helper.php)
├── Libraries/      # Services (The exclusive home and sole location of business logic)
├── Models/         # Database schema access, retrieval maps, and database access layer
└── Views/          # Clean presentation files with HTML escaping / templates
```

*All directories are OPTIONAL unless explicitly marked REQUIRED. Create ONLY directories that contain files.*

### 2.2 Module Registration Protocol

#### Module Generation

Modules **MUST** be scaffolded via workspace Spark generator commands or structured manually in exact compliance with Section 2.1:

```bash
php spark make:module [ModuleName]
```

#### Namespace Registration

The module's namespace **MUST** be registered in `app/Config/Autoload.php` within the `$psr4` array:

```php
public array $psr4 = [
    APP_NAMESPACE               => APPPATH,
    'App\Modules\[ModuleName]' => APPPATH . 'Modules/[ModuleName]',
];
```

*Note: Registering the module namespace in `app/Config/Autoload.php` allows `php spark migrate --all` to automatically discover and run migrations located within `app/Modules/[ModuleName]/Database/Migrations/`.*

#### Service Container Registration

Module services **MUST** be registered in `app/Modules/[ModuleName]/Config/Services.php` extending `CodeIgniter\Config\BaseService` to allow resolution via `service('domainService')`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\[ModuleName]\Config;

use App\Modules\[ModuleName]\Libraries\DomainService;
use App\Modules\[ModuleName]\Models\DomainModel;
use CodeIgniter\Config\BaseService;

/**
 * Class Services
 *
 * @package App\Modules\[ModuleName]\Config
 * @author  Senior Developer
 * @since   1.0.0
 */
class Services extends BaseService
{
    /**
     * Factory method for DomainService.
     *
     * @param bool $getShared Whether to return a shared instance.
     *
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

#### Route Registration

Routes **MUST** be defined within `app/Modules/[ModuleName]/Config/Routes.php`. To prevent default namespace conflicts, routes **MUST** be wrapped inside a route group with an explicit, fully-qualified module namespace:

```php
<?php

declare(strict_types=1);

namespace App\Modules\[ModuleName]\Config;

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->group('domain', ['namespace' => 'App\Modules\[ModuleName]\Controllers'], static function (RouteCollection $routes): void {
    // Define all module routes here with explicit names
    $routes->get('record', 'DomainController::index', ['as' => 'domain.record.index']);
    $routes->get('record/new', 'DomainController::new', ['as' => 'domain.record.new']);
    $routes->post('record', 'DomainController::create', ['as' => 'domain.record.create', 'filter' => 'throttle:5,60']);
});
```

#### Helper Loading

Globally loading module-specific helper files via `app/Config/Autoload.php` is strictly **FORBIDDEN**. To preserve performance and isolate execution, module-level helper files **MUST** follow the `[helper_name]_helper.php` naming convention and **MUST** be dynamically declared and loaded within the destination controller's `$helpers` array property:

```php
protected $helpers = ['App\Modules\[ModuleName]\Helpers\domain', 'form'];
```

---

## Part 3: Layer Responsibilities

### 3.1 Controllers (`app/Modules/[ModuleName]/Controllers/`)

Controllers act as lightweight request handlers and HTTP request/response orchestrators. They **MUST NOT** execute database queries or perform complex calculations.

* **MUST** extend `CodeIgniter\Controller` or `App\Controllers\BaseController`.
* **MUST** declare return types as `string|ResponseInterface` inside method signatures to satisfy PHP strict typing rules under all outcome variations.
* **MUST** validate incoming HTTP requests using the Validation service. Validated inputs **MUST** be retrieved using `$this->validator->getValidated()` or explicitly cast to appropriate scalar types.
* **MUST** invoke Service classes via the Service Container (`service('domainService')`) to process business operations. Direct model or database instantiation inside controllers is strictly **FORBIDDEN**.
* **MUST** use `__construct()` solely for service dependency resolution via `service()`. Access to `$this->request`, `$this->response`, or session state inside `__construct()` is strictly **FORBIDDEN** (since CI4 injects them post-instantiation via `initController()`). Any request-dependent state initialization **MUST** occur inside `initController()` or action methods.
* **MUST** return `ResponseInterface` objects (via `$this->response` or `return redirect()`) or string values (via `return view()`).
* **MUST** pass pre-formatted data (no business/processing logic exceeding 5 lines inside views) and complete SEO/structured metadata arrays in the view data array:

```php
return view('App\Modules\[ModuleName]\Views\record\index', [
    'pageTitle'       => 'Domain Records',
    'metaDescription' => 'Access and review domain records.',
    'metaKeywords'    => 'domain, record, entries',
    'canonicalUrl'    => url_to('domain.record.index'),
    'robotsTag'       => 'index, follow', // 'index, follow' for public pages; 'noindex, follow' for private dashboards/auth forms
    'metaImage'       => 'https://example.com/assets/og-image.jpg',
    'schemaJson'      => $jsonLdData,     // Targeted JSON-LD Schema config block or null
    'data'            => $serviceResult,  // Pre-formatted presentation payload
]);
```

#### Forbidden in Controllers

* **MUST NOT** execute database queries or Query Builder calls directly.
* **MUST NOT** instantiate Models, Services, or Database connections directly using the `new` keyword inside constructors or methods.
* **MUST NOT** perform business calculations or logic algorithms.
* **MUST NOT** access `$this->request`, `$this->response`, or session inside `__construct()`:

```php
// FORBIDDEN: Accessing request/response before initController() triggers fatal error
public function __construct()
{
    $this->domainService = service('domainService');
    $this->userId        = $this->request->getGet('user_id'); // FATAL ERROR: Typed property BaseController::$request must not be accessed before initialization
}
```

* **MUST NOT** instantiate the file system directly (use Services).
* **MUST NOT** generate inline HTML markup.

---

### 3.2 Services (`app/Modules/[ModuleName]/Libraries/`)

Services contain 100% of the application's business rules, external integrations, persistence workflows, and filesystem operations.

* **MUST** be instantiated via the Service Container: `service('domainService')`.
* **MUST** be registered in the module-level `Config/Services.php` file. If a service is generic, shared, and consumed across multiple modules, it **MUST** be registered in the global `app/Config/Services.php` file.
* **MUST** contain all business logic, external API integrations, file system operations, and transaction initialization.
* **MUST** use dependency injection through the constructor for required models, repositories, and sub-services.
* **MUST** explicitly declare all properties (e.g., models, repositories, helper services) at the top of the class definition using native types and visibility modifiers (`private` or `protected`). Dynamic or undeclared properties are strictly **FORBIDDEN**.
* **MUST** use dependency injection through the class constructor (`__construct`) to receive and assign these properties. Instantiating models or calling `Database::connect()` directly inside business methods or action loops is strictly **FORBIDDEN**. Database connections **MUST** be obtained via the encapsulated Model's `$this->domainModel->db` property or constructor injection.
* **MUST** encapsulate database transaction lifecycles for all multi-step mutation procedures.
* **MUST** return standardized data structures (`Entity`, `array<int, Entity>`, or standardized scalar payloads).
* **MUST NOT** access HTTP superglobals (`$_POST`, `$_GET`, `$_FILES`, `$_SERVER`) directly.
* **MUST NOT** manage HTTP redirects or session flash data directly.

#### Service Dependency Topology (Parallel vs. Braided)

The architecture enforces clean, parallel execution stacks that do not "criss-cross" or braid together, ensuring ease of debugging and future scaling.

* **Parallel Structure / Topology:** Dependencies **MUST** flow strictly vertically:
  `Controller -> Main Service -> Model` or `Controller -> Orchestrator Service -> Sub-Service -> Model`.
* **The "Ping Pong" Prohibition:** Triangular dependencies are strictly **FORBIDDEN**.
  *Definition:* A Controller talking simultaneously to a Main Service AND that Main Service's internal dependency (Sub-Service).
  *Fix:* Use the **Facade Pattern**. The Main Service (`MainService`) **MUST** wrap the required methods of the Sub-Service (`SubService`) so the Controller has a single point of entry.
* **Brother-Service Isolation:** Services at the same level (e.g., `ModuleAService` and `ModuleBService`) **MUST NOT** call each other directly. If orchestration is needed, create a higher-level "Orchestrator Service" or handle coordination in the Controller.

---

### 3.3 Models (`app/Modules/[ModuleName]/Models/`)

Models are the database access and retrieval layer. They handle database schema access, data persistence, and retrieval.

* **MUST** extend `CodeIgniter\Model`.
* **MUST** define the `$table` property specifying the database table name.
* **MUST** define `$primaryKey` property (defaults to `id` if omitted).
* **MUST** define `$returnType` as a fully-qualified Entity class:

```php
protected $returnType = \App\Modules\[ModuleName]\Entities\DomainEntity::class;
```

* **MUST** define `$allowedFields` as an array of writable column names (**FORBIDDEN** to omit).
* **MUST** define timestamp properties explicitly:

```php
protected $useTimestamps = true;
protected $dateFormat    = 'datetime';
protected $createdField  = 'created_at';
protected $updatedField  = 'updated_at';
protected $deletedField  = 'deleted_at';
```

* **MUST NOT** use Model-level field casting (`$casts` property) when returning Entities, as model-level casts run in direct conflict with Entity property casting. Model-level casting is **FORBIDDEN**. Entity property casting **MUST** manage data transformation.
* **MUST NOT** contain business workflow calculations.
* **MUST NOT** be invoked directly from Views or Controllers.

---

### 3.4 Entities (`app/Modules/[ModuleName]/Entities/`)

Entities are strict, type-safe data objects that represent business records with property casts and domain accessors.

* **MUST** extend `CodeIgniter\Entity\Entity`.
* **MUST** be used for all business data representation across layer boundaries. Passing raw associative arrays between application layers for database representation is **FORBIDDEN**. Services **MUST** return strict Entities or arrays of Entities, and controllers **MUST** pass typed Entities or validated scalar structures to Services.
* **MUST** utilize the Entity `$casts` property to enforce type safety:

```php
protected $casts = [
    'id'        => 'integer',
    'domain_id' => 'integer',
    'value'     => 'float',
    'is_active' => 'boolean',
    'metadata'  => 'json-array',
];
```

* **MUST** utilize `$dates` array for timestamp properties. Properties in `$dates` are returned as `\CodeIgniter\I18n\Time|null`, NOT string.
* **MUST** explicitly declare all `$dates` timestamp properties in the class PHPDoc block as `@property \CodeIgniter\I18n\Time|null $[propertyName]` to ensure full static analysis compatibility and prevent IDE/linter type errors.
* **MUST** house data-shaping logic via accessors and mutators (e.g., `setPassword(string $pass)`).
* **MUST NOT** query the database.
* **MUST NOT** contain service-level operations.
* **MUST NOT** depend on external services.

---

### 3.5 Views (`app/Modules/[ModuleName]/Views/`)

Views render Presentation Layer output with clean presentation files and HTML escaping.

* **MUST** extend the master layout file using: `<?= $this->extend('layouts/default') ?>`.
* **MUST** declare a top-level `@var` PHPDoc block at the beginning of the file defining all expected variables injected via the controller's `view()` data array (e.g., `/** @var array<int, DomainEntity> $data */`), guaranteeing static analysis purity and zero undefined variable warnings.
* **MUST** wrap structural content in: `<?= $this->section('content') ?> ... <?= $this->endSection() ?>`.
* **MUST** inject page-specific micro-styles via: `<?= $this->section('styles') ?> ... <?= $this->endSection() ?>`.
* **MUST** inject page-specific scripts via: `<?= $this->section('scripts') ?> ... <?= $this->endSection() ?>`.
* **MUST NOT** contain nested conditional statements (maximum nesting depth of 1 inside PHP templates).
* **MUST NOT** contain any raw PHP code blocks exceeding 5 lines.
* **MUST** escape **ALL** dynamic data rendered inside HTML elements or attributes using `esc($variable, 'html')` for standard body content, `esc($variable, 'attr')` for HTML attribute values, or `esc($variable, 'js')` for JavaScript payloads.
* **MUST NOT** make database calls.
* **MUST NOT** instantiate Services or Models.

---

### 3.6 Helpers (`app/Helpers/` or `app/Modules/[ModuleName]/Helpers/`)

Helpers are small, stateless, reusable procedural functions.

* **MUST** be pure functions (no side effects).
* **MUST** reside in files matching the `[helper_name]_helper.php` convention.
* **MUST** be loaded dynamically when execution scope demands them.
* **MUST NOT** contain business logic (use Services).
* **MUST NOT** execute database queries (use Models/Services).
* **MUST NOT** perform stateful operations.

---

## Part 4: Database Schema & Management Standards

A high-performance SQL schema operates on a single principle: **make it impossible for the database to store bad data, and make it effortless for the engine to retrieve good data.**

### 4.1 Storage Engine, Charset & Collation

* **Engine Mandate:** All tables **MUST** explicitly specify `ENGINE=InnoDB`. Older engines (e.g., MyISAM) are strictly **FORBIDDEN**.
* **Encoding & Collation:** All tables **MUST** use `utf8mb4` character set and `utf8mb4_unicode_ci` collation. `latin1` or standard 3-byte `utf8` are strictly **FORBIDDEN**.
* **Row Format:** Tables **MUST** use `ROW_FORMAT=DYNAMIC` to optimize storage for variable-length and off-page columns.

### 4.2 Primary Key & Identity Architecture

* **System-Generated Primary Keys:** Every table **MUST** declare a surrogate, system-generated primary key named `id`. Using mutable business data (such as emails, slugs, or tax IDs) as primary keys is strictly **FORBIDDEN**.
* **Key Sizing:**
  * For single-instance, high-throughput relational tables: Primary keys **MUST** be defined as unsigned integers (`INT UNSIGNED` up to 4.29 billion, or `BIGINT UNSIGNED` for hyper-scale event tables).
  * For distributed architectures: Primary keys **MUST** utilize binary-compact UUIDv7 or ULID (`BINARY(16)`). Storing UUIDs as raw 36-character text strings is strictly **FORBIDDEN**.
* **Immutable Foreign Keys:** Foreign keys **MUST** reference the parent primary key directly and **MUST** match the exact data type, length, and unsigned attribute of the referenced column.

### 4.3 Strict Column Typing & Right-Sized Storage

Columns **MUST** be defined using the narrowest data type capable of reliably accommodating the data domain. Generic catch-all types degrade memory cache hit rates and are strictly **FORBIDDEN**.

* **Numbers & Counters:**
  * Quantities, counts, and IDs **MUST** use appropriate integer sizes: `TINYINT` (0–255), `SMALLINT` (0–65,535), `INT` (0–4.29B), or `BIGINT`.
  * All IDs and counts **MUST** be marked `UNSIGNED` unless negative values are mathematically valid.
* **Currencies & Precise Decimals:**
  * Currency values, financial amounts, and scientific metrics **MUST** use `DECIMAL(precision, scale)` (e.g., `DECIMAL(12,2)`).
  * Using floating-point types (`FLOAT`, `DOUBLE`) for financial or exact numbers is strictly **FORBIDDEN** due to IEEE 754 precision drift.
* **Boolean / Flags:**
  * State flags **MUST** be defined as `TINYINT(1)` with a strict default (`0` or `1`). Storing `"Y"`, `"N"`, `"true"`, or `"false"` as strings is strictly **FORBIDDEN**.
* **Dates & Timestamps:**
  * All operational temporal fields **MUST** use `DATETIME` or `TIMESTAMP`.
  * Storing dates or times as UNIX integers (`INT`) or raw text strings (`VARCHAR`) is strictly **FORBIDDEN**.
* **Strings & Text:**
  * Predictable text length (hashes, country codes, postal codes) **MUST** use fixed `CHAR(length)`.
  * Variable-length strings **MUST** declare realistic maximum boundaries via `VARCHAR(N)` (e.g., `VARCHAR(255)`). Unbounded `TEXT` or `LONGTEXT` for short strings is strictly **FORBIDDEN**.
  * Use `TEXT` or `JSON` **ONLY** for arbitrary payloads or document metadata.

### 4.4 Nullability, Constraints & Data Integrity

Data integrity **MUST** be enforced at the database engine layer, not deferred solely to application validation.

* **Explicit Nullability (`NOT NULL` by Default):**
  * All columns **MUST** be declared `NOT NULL` unless a `NULL` state represents genuine, unrecorded business reality.
  * Columns with default values **MUST** be marked `NOT NULL DEFAULT [value]`.
* **Unique Constraints:** Any business attribute that must be unique within a system (e.g., identity hashes, external reference codes) **MUST** have an explicit database-level `UNIQUE` constraint declared in migrations.
* **Foreign Key Constraints:**
  * Relational links **MUST** define explicit integrity actions on delete and update (e.g., `ON DELETE RESTRICT` or `ON DELETE CASCADE`).
  * Dangling orphan records without enforced relationships are strictly **FORBIDDEN**.

### 4.5 Indexing Architecture & Retrieval Paths

Indexes provide $O(\log N)$ access paths. Over-indexing creates write locks and memory bloat; under-indexing causes catastrophic full-table scans.

* **Mandatory Indexing (`addKey` in Migrations):** Indexes are **REQUIRED** for:
  1. All foreign key reference columns (`[entity]_id`).
  2. Frequently queried status/state filters (`is_active`, `status`).
  3. Unique identifier lookups (`email`, `slug`, `hash`).
  4. Timestamp sorting and range-filtering columns (`created_at`).
* **Composite (Multi-Column) Indexes:**
  * Frequently combined query filters **MUST** use composite indexes (e.g., `$this->forge->addKey(['domain_id', 'is_active']);`).
  * **Column Order Rule:** Multi-column indexes **MUST** place the highest-selectivity column (most unique values) first, followed by equality filters, with range/sort columns placed last.
* **Indexing Prohibitions:**
  * Columns with low selectivity (e.g., a single boolean column on a massive table) **MUST NOT** be indexed alone.
  * Indexing volatile columns updated on every single user request (e.g., `last_seen_at`) is **FORBIDDEN** unless proven critical for real-time aggregation.

### 4.6 Naming Standards & Structural Conventions

* **Casing:** Table names and column names **MUST** use lowercase `snake_case`. CamelCase or kebab-case are strictly **FORBIDDEN**.
* **Table Naming:** Tables **MUST** be plural nouns representing collections (`domains`, `users`, `audit_logs`). Pivot/junction tables **MUST** join the two singular nouns in alphabetical order (e.g., `domain_user`).
* **Primary Key:** The internal surrogate identifier **MUST** be named `id`.
* **Foreign Keys:** Reference columns **MUST** use the singular parent table name appended with `_id` (e.g., `domain_id`, `account_id`).
* **Boolean Flags:** Column names **MUST** start with a state verb prefix (`is_active`, `has_access`, `should_notify`).
* **Timestamps:** Lifecycle temporal markers **MUST** end with `_at` (e.g., `created_at`, `updated_at`, `deleted_at`, `published_at`).

### 4.7 Record Lifecycle & Archival (Soft Deletes)

* **Destructive Deletions:** Executing raw SQL `DELETE` operations on core operational or transactional entities is strictly **FORBIDDEN**.
* **Soft Delete Protocol:**
  * Tables requiring lifecycle tracking **MUST** declare a nullable `deleted_at DATETIME NULL` column.
  * A `NULL` value represents an active record; a populated timestamp indicates when the record was invalidated.
* **Audit Columns:** Every stateful table **MUST** include:
  * `created_at DATETIME NOT NULL`
  * `updated_at DATETIME NULL`
  * `deleted_at DATETIME NULL` (if soft deletes apply)

### 4.8 Querying Standards & Table Access Rules

* **No Table Scans:** `SELECT *` is strictly **FORBIDDEN**. All data retrieval queries **MUST** explicitly specify column names to minimize memory serialization overhead:

```php
$this->select('id, domain_id, value, is_active, created_at');
```

* **Raw Queries Restricted:** Raw SQL execution via `$db->query()` is **FORBIDDEN** except for complex aggregate reporting queries reviewed and approved by architectural review.

### 4.9 Transaction Integrity

Any execution chain containing more than one database write operation (`INSERT`, `UPDATE`, `DELETE`) **MUST** be wrapped in an exception-driven transaction with explicit rollback guarantees:

```php
$db = \Config\Database::connect();

try {
    $db->transException(true)->transStart();

    $this->modelA->insert($entityA);
    $this->modelB->update($id, $entityB);

    $db->transComplete();
} catch (\Throwable $e) {
    $db->transRollback();

    log_message('critical', 'Transaction failure in DomainService::createRecord', [
        'exception' => $e->getMessage(),
        'trace'     => $e->getTraceAsString(),
    ]);

    throw $e;
}
```

*All multi-write operations (creates, updates, transfers) MUST use transactions regardless of operation count.*

### 4.10 Migrations & Seeders Protocol

* **Migrations MANDATORY:** All schema modifications **MUST** use Migration classes. Direct table alteration via CLI or GUI tools on any environment is **FORBIDDEN**.
* **Rollback Completeness:** Every migration `down()` method **MUST** completely revert all modifications performed in `up()`.
* **Seeder Protocol:** Initial and test datasets **MUST** be provisioned exclusively through Seeder classes invoked via `php spark db:seed MainSeeder`.
* **Compression Protocol:** Consolidating migrations is permitted **ONLY** for fresh environment setup with documented Compression and Implementation Plans.
* **Database Provisioning Sequence:**
  1. Run all pending migrations via `php spark migrate --all`.
  2. If migration conflicts exist, run `php spark migrate:refresh --all` to rebuild all tables from a clean state.
  3. Seed initial data via `php spark db:seed MainSeeder`.
  4. Verify migration status via `php spark migrate:status`.

---

### Schema Architecture Compliance Checklist

| Focus Area | Standard Requirement | Direct Failure Mode Avoided |
| :--- | :--- | :--- |
| **Engine / Collation** | `InnoDB`, `utf8mb4`, `utf8mb4_unicode_ci`, `ROW_FORMAT=DYNAMIC` | Emoji corruption, table-level locks, cross-server collation conflicts. |
| **Primary Keys** | Surrogate `id` (`INT/BIGINT UNSIGNED` or `BINARY(16)` UUID) | Slow joins, fragmentation on inserts, unindexed clustered index scans. |
| **Data Types** | Narrowest feasible scalar type (strict `DECIMAL`, `DATETIME`, `TINYINT(1)`) | RAM cache exhaustion, floating-point math bugs, excessive disk I/O. |
| **Nullability** | `NOT NULL` by default; explicit defaults for all state flags | Unpredictable ternary boolean states (`TRUE`/`FALSE`/`UNKNOWN`), runtime NPEs. |
| **Indexing** | Foreign keys, composite filters, and lookup fields (`addKey`) | Full-table scans on high-traffic queries; write bottlenecks from over-indexing. |
| **Lifecycle** | State flags, `created_at`, `updated_at`, `deleted_at` | Broken relational dependencies, data loss, untraceable mutation history. |

---

## Part 5: Code Quality, Typing & Documentation

* **PSR-12 Compliance:** Every PHP file **MUST** be PSR-12 compliant.
* **Strict Typing:** The declaration `declare(strict_types=1);` **MUST** be the first statement in every PHP file:

```php
<?php

declare(strict_types=1);
```

* **Namespace Declaration:** The namespace declaration **MUST** follow immediately after `declare()`, with exactly one blank line before class declaration.
* **PHPDoc Standards:**
  * Every class **MUST** contain a descriptive PHPDoc block with `@package`, `@author`, and `@since` tags.
  * Every method **MUST** contain complete PHPDoc with `@param`, `@return`, and `@throws` where applicable.
  * Every property **MUST** specify type and description via PHPDoc in addition to native PHP typed declarations.
  * Entity PHPDoc properties **MUST** match the dynamic cast types returned by CodeIgniter.
* **Naming Conventions:**
  * **Class Names / Controllers:** `PascalCase` (e.g., `DomainController`, `DomainService`, `DomainModel`).
  * **Method Names:** `camelCase` (e.g., `createRecord()`, `findActiveRecords()`).
  * **Class Properties & Local Variables:** `camelCase` (e.g., `$domainService`, `$domainModel`, `$savedEntity`, `$invalidPayload`).
  * **Database Columns & Array Keys:** `snake_case` (e.g., `domain_id`, `is_active`, `created_at`).
  * **Entity Cast Attributes:** `snake_case` matching database columns (e.g., `$entity->domain_id`).
  * **Private Helpers:** **MUST** be prefixed with underscore (e.g., `private function _formatPayload()`).
  * Private helpers **MUST** be grouped beneath `// --- Helper Methods ---` and defined **BEFORE** public methods that invoke them.
* **Type Safety & Boundary Contracts:**
  * All methods **MUST** have explicit return types (`: bool`, `: array`, `: RedirectResponse`, `: string|ResponseInterface`).
  * **Boundary Array Generics:** Any method returning an array **MUST** explicitly declare the type of the elements inside that array using PHPStan/Psalm generic notation:

```php
/**
 * @return array<int, DomainEntity>
 */
public function getAllRecords(): array;
```

* **Consuming Layer Typing:** Consuming variables assigned from dynamic collections or framework methods **MUST** use inline PHPDoc type hints to preserve static type guarantees:

```php
/** @var array<int, \App\Modules\[ModuleName]\Entities\DomainEntity> $records */
$records = $this->domainService->getAllRecords();
```

---

## Part 6: Request, Response & Protocol

### 6.1 Routing Configuration

* **Auto-Routing DISABLED:** Auto-routing is **FORBIDDEN**. Set `public bool $autoRoute = false;` in `app/Config/Routing.php`.
* **No Route Closures:** Placing logic inside route closures (`$routes->get('/', function(){...})`) is strictly **FORBIDDEN**.
* **Named Routes MANDATORY:** Every defined route **MUST** have an explicit name using the `as` option:

```php
$routes->get('record', 'DomainController::index', ['as' => 'domain.record.index']);
$routes->post('record', 'DomainController::create', ['as' => 'domain.record.create']);
```

* **Route Groups:** Use `$routes->group()` to organize by feature or access level. Group callbacks **MUST** be `static function`.
* **URL Generation:** All links, form actions, and redirects **MUST** use named route resolution via `url_to()`. Hardcoded URI paths are strictly **FORBIDDEN**.

### 6.2 Form Submission (Post/Redirect/Get Pattern)

All POST, PUT, PATCH, and DELETE request handlers **MUST** implement the PRG pattern:

1. The POST handler controller **MUST NOT** return `view()` directly.
2. The controller **MUST** validate inputs, construct a typed Entity or validated payload, execute the Service call, set flash messages via `session()->setFlashdata()`, and return `redirect()`.
3. The redirect target **MUST** be a named GET route.
4. The GET destination controller renders the view and displays flash messages via `app/Views/partials/flash_messages.php`.

```php
public function create(): string|ResponseInterface
{
    $rules = [
        'domain_id' => 'required|integer',
        'value'     => 'required|decimal',
    ];

    if (! $this->validate($rules)) {
        return redirect()
            ->back()
            ->withInput()
            ->with('errors', $this->validator->getErrors());
    }

    try {
        $entity = new DomainEntity([
            'domain_id' => (int) $this->request->getPost('domain_id'),
            'value'     => (float) $this->request->getPost('value'),
            'is_active' => false,
            'metadata'  => ['source' => 'web_interface'],
        ]);

        $this->domainService->createRecord($entity);

        return redirect()
            ->to(url_to('domain.record.index'))
            ->with('success', 'Record created successfully.');
    } catch (\Throwable $e) {
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Failed to create record: ' . $e->getMessage());
    }
}
```

### 6.3 AJAX Responses

All JSON responses **MUST** return a uniform envelope format:

```php
return $this->response->setJSON([
    'status'     => 'success',           // 'success', 'error', or 'validation_error'
    'message'    => 'Operation completed successfully.',
    'result'     => $data,               // Payload object/array
    'errors'     => $validationErrors,   // Validation error array
    'csrf_token' => csrf_hash(),         // REQUIRED: Fresh CSRF token
]);
```

### 6.4 Server-Sent Events (SSE) Streaming

* The `Content-Type` header **MUST** be set to `text/event-stream`.
* Server-side buffering **MUST** be bypassed immediately with `ob_flush(); flush();` after each output event.
* `session_write_close()` **MUST** be called before entering streaming loops to prevent session locks.
* The first stream packet **MUST** transmit the fresh CSRF token (`csrf_hash()`).

---

## Part 7: Security Protocols

### 7.1 CSRF Enforcement

* CSRF protection **MUST** be enabled globally in `app/Config/Security.php`.
* HTML forms **MUST** be created using `form_open()` (which automatically injects the synchronized CSRF hidden field).
* Every JSON response (success, validation error, or exception) **MUST** include `csrf_token` with a fresh, synchronized CSRF token using `csrf_hash()`.
* Layout templates **MUST** render CSRF `<meta>` tags for asynchronous client-side consumption.
* Manual CSRF token passing via cookie headers is **FORBIDDEN**.

### 7.2 Input Validation

* Controllers **MUST** validate **ALL** incoming input using the Validation service before passing data to domain services.
* Validation rules **MUST** be strictly defined and reviewed with exact scalar type constraints.
* Controllers **MUST** retrieve validated data using `$this->validator->getValidated()` to ensure unvalidated fields are discarded.

### 7.3 Environment Secrets

* API keys, secrets, and credentials **MUST** reside **ONLY** in the `.env` file.
* `.env` file **MUST** be excluded from version control via `.gitignore`.
* Access secrets via `env('KEY_NAME')` or `service('settings')` **ONLY**.
* Custom config files for secrets or hardcoding credentials in config files is strictly **FORBIDDEN**.

### 7.4 Rate Limiting & Throttling

The application **MUST** implement rate limiting to protect routes against brute-force attacks and resource abuse using `App\Filters\ThrottleFilter` mapped to the `throttle` filter alias in `app/Config/Filters.php`.

#### 7.4.1 Filter Architecture (`app/Filters/ThrottleFilter.php`)

```php
<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Throttle Filter
 *
 * Implements rate limiting for routes to prevent brute-force attacks
 * and resource abuse. Uses cache to track request counts per IP.
 *
 * Usage: 'filter' => 'throttle:5,60' (5 requests per 60 seconds)
 *
 * @package App\Filters
 * @author  Senior Developer
 * @since   1.0.0
 */
class ThrottleFilter implements FilterInterface
{
    /**
     * Execute the filter before the controller.
     *
     * @param RequestInterface $request   The incoming request
     * @param array|null       $arguments Arguments passed to filter (limit, seconds)
     *
     * @return RequestInterface|ResponseInterface|string|null
     */
    public function before(RequestInterface $request, $arguments = null): RequestInterface|ResponseInterface|string|null
    {
        if (! $request instanceof IncomingRequest) {
            return null;
        }

        $throttler = service('throttler');

        // Default: 60 requests per minute
        $limit   = isset($arguments[0]) ? (int) $arguments[0] : 60;
        $seconds = isset($arguments[1]) ? (int) $arguments[1] : 60;

        $ip  = $request->getIPAddress();
        $uri = method_exists($request, 'getRoutePath') ? $request->getRoutePath() : $request->getPath();
        $key = 'throttle_' . md5($ip . $uri);

        // Use the native Throttler service (Token Bucket algorithm)
        if ($throttler->check($key, $limit, $seconds) === false) {
            if ($request->isAJAX() || str_contains($request->getHeaderLine('Accept'), 'application/json')) {
                return service('response')
                    ->setStatusCode(429)
                    ->setJSON([
                        'status'     => 'error',
                        'message'    => 'Too many requests. Please try again later.',
                        'csrf_token' => csrf_hash(),
                    ]);
            }

            // Otherwise, redirect back with a flash error message
            return redirect()->back()->with('error', 'Too many requests. Please wait a moment before trying again.');
        }

        return null;
    }

    /**
     * Execute the filter after the controller.
     *
     * @param RequestInterface  $request   The incoming request
     * @param ResponseInterface $response  The outgoing response
     * @param array|null        $arguments Arguments passed to filter
     *
     * @return ResponseInterface|null
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ?ResponseInterface
    {
        return null;
    }
}
```

#### 7.4.2 Route Definition Rules

* **Mandatory Application:** Throttling parameters **MUST** be explicitly defined at the route level for:
  * All authentication actions (e.g., login submissions, registrations, password resets).
  * All authorization endpoints (e.g., 2FA verify steps, API token requests).
  * High-resource consumption routes (e.g., AI generations, massive data exports).
* **Route Options Formatting:** Limit parameters **MUST** be mapped inside the route configuration using colon-separated parameters within the filter option:

```php
$routes->post('login/authenticate', 'AuthController::authenticate', [
    'as'     => 'login.authenticate',
    'filter' => 'throttle:5,60', // Max 5 requests per 60 seconds per IP
]);
```

### 7.5 XSS Prevention

* **ALL** View output **MUST** be escaped using `esc($var, 'html')` for standard body content, `esc($var, 'attr')` for HTML attributes, or `esc($var, 'js')` for JavaScript payloads.
* Unescaped output requires explicit security review and documented approval comments.

---

## Part 8: Stateless File Handling (The Unlink Pattern)

To maintain compatibility with stateless, containerized, and serverless environments:

* **Immediate Cleanup:** After a temporary file is read into memory or served to the client, `@unlink($path)` **MUST** be called immediately within the same request cycle.
* **Randomized Naming:** Temporary file names **MUST** use framework-generated random names (`$file->getRandomName()`).
* **Centralized Storage:** Uploads **MUST** be stored in `WRITEPATH . 'uploads/[type]/[userId]/'`.
* **No Persistent Local Storage:** Storing uploaded assets permanently on local disk is strictly **FORBIDDEN**. Offload to external object storage (S3, GCS, etc.) within the same request lifecycle.
* **Session Data Restrictions:** Saving binary data or raw files in session storage is **FORBIDDEN**. Store IDs **ONLY**. Use the `DatabaseHandler` session driver with the `data` column configured as `MEDIUMBLOB` or larger for serialized data.
* **Directory Creation:** Services **MUST** handle directory creation (`mkdir($path, 0755, true)`) and file moving.

---

## Part 9: Error Handling, Logging & Environment

### 9.1 Exception Handling

* Controllers **MUST** catch `\Throwable` in action methods to prevent stack trace leaks to clients.
* Service layer methods **MUST** bubble up domain-specific exceptions with context OR return standardized error arrays:

```php
['status' => 'error', 'message' => 'Human readable message']
```

* Unhandled exceptions in Production mode **MUST** display the static error layout from `app/Views/errors/html/production.php`.

### 9.2 Logging Standards

Logging **MUST** use `log_message($level, $message, $context)` with associative context arrays:

```php
log_message('error', 'Operation failed in DomainService::createRecord', [
    'domain_id' => $domainId,
    'user_id'   => $userId,
    'trace'     => $e->getTraceAsString(),
]);
```

* **Log Levels:**
  * `critical`: System unusable (DB down, disk full). Triggers immediate alert.
  * `error`: Runtime failures (transaction rollbacks, upload failures).
  * `info`: Key business events (user login, report generated).

### 9.3 User Feedback

* Flash messages **MUST** use `session()->setFlashdata()`.
* **UI Standard:** Use persistent Bootstrap Alerts for operation results (Success/Error/Warning). Transient Toasts are reserved for system connectivity issues **ONLY**.

### 9.4 Environment Configuration

| Environment | `CI_ENVIRONMENT` | `display_errors` | Debug Toolbar |
| :--- | :--- | :--- | :--- |
| **Development** | `development` | `1` | Enabled |
| **Staging / Testing** | `testing` | `0` | Disabled |
| **Production** | `production` | `0` | Disabled |

*Committing `d()`, `dd()`, `die()`, `var_dump()`, or `print_r()` statements to version control is strictly **FORBIDDEN**.*

---

## Part 10: Frontend Blueprints

### 10.1 Framework & Structure

* **Framework:** Bootstrap 5.3+ modular architecture.
* **Layouts:** All views **MUST** extend `layouts/default` (e.g., `<?= $this->extend('layouts/default') ?>`), which provides the global HTML skeleton, base sans-serif font rendering, Bootstrap 5 scripts/styles, CSRF meta elements, a responsive navigation system, dynamic role-based menus, and global flash messaging structures.
* **Partials:** Reusable UI chunks **MUST** be placed in `partials/` (e.g., `flash_messages.php`).
* **Structure (The Blueprint Method):**
  * Container: Wrap content in `<div class="container my-5">`.
  * Header: Use `<div class="blueprint-header">`.
  * Card: Use `<div class="card blueprint-card">`.
* Custom components **MUST** utilize the standard structural class `.blueprint-card` for container wrappers, which supports focus outline adjustments, light shadow elevations, and subtle border color transitions on hover.

### 10.2 Design System & Theme Awareness (Plug-and-Play Theme Architecture)

To support plug-and-play theme swapability, all custom stylesheets and component overrides **MUST** reference semantic theme variables rather than hardcoded hex values.

#### 10.2.1 Active Theme Configuration & Dark Mode Mappings

```css
/* --- Light Mode Configuration (Default Active Theme) --- */
:root,
[data-bs-theme="light"] {
    --theme-bg-light: #ffffff;   /* Canvas Base: Stark White background canvases & containers */
    --theme-primary: #000000;    /* Primary Tone: Stark Black typography, structure & borders */
    --theme-accent: #0d6efd;     /* Highlight Accent: Interactive Blue CTA highlights & active states */
    --theme-accent-rgb: 13, 110, 253; /* Highlight Accent RGB: Opacity-based masking */
    --theme-card-bg: #ffffff;    /* Standard Card Surface: Card container background */
}

/* --- Dark Mode Configuration (Auto-Swapped Roles) --- */
[data-bs-theme="dark"] {
    --theme-bg-light: #121212;   /* Canvas Base: Stark Dark background canvases & containers */
    --theme-primary: #ffffff;    /* Primary Tone: Stark White typography, structure & borders */
    --theme-accent: #0d6efd;     /* Highlight Accent: Interactive Blue highlight */
    --theme-accent-rgb: 13, 110, 253; /* Highlight Accent RGB: Opacity-based masking */
    --theme-card-bg: #1e1e1e;    /* Standard Card Surface: Dark Card container background */
}
```

#### 10.2.2 Theme Patterns & Palette Rules

* **Canvas & Backgrounds (`var(--theme-bg-light)`):** Reserved for main background canvases, card bodies, and containers where maximum legibility is REQUIRED.
* **Typography, Structure, & High-Contrast (`var(--theme-primary)`):** Used for primary body text, headers, framing borders, and neutral boundaries.
* **Interactivity & Focus Highlights (`var(--theme-accent)`):** Reserved strictly for active/hover highlights, active states, link hover text, custom SVG icon fills, focus rings, primary Call-To-Action (CTA) backgrounds, and success/status highlights.

#### 10.2.3 Variable Mapping & Strict Compliance

* Utilize native Bootstrap 5.3+ utilities as the primary styling mechanism.
* **NEVER** hardcode HEX values directly in style declarations. Map them strictly to semantic CSS variables.

### 10.3 UI Components & Form Handling

* **Inputs & Floating Labels:** All standard text inputs **MUST** implement Bootstrap 5 floating labels:

```html
<div class="form-floating mb-3">
    <input type="email" class="form-control" id="emailInput" placeholder="name@example.com" required />
    <label for="emailInput">Email Address</label>
</div>
```

* **Form CSRF Integration:** All forms **MUST** use `form_open(url_to('...'))` which automatically renders the synchronized CSRF hidden token field. Manual declaration of `csrf_field()` alongside `form_open()` is strictly **FORBIDDEN**.
* **AJAX CSRF Integration:** Fetch the active token via `window.getCSRFToken()` and update inputs on success using `window.updateCSRFToken(newHash)`.
* **Buttons:**
  * Primary Action (CTA / Active states / Blue accent background): `.btn-primary`
  * Secondary Action (Stark black/white outline): `.btn-outline-secondary`
  * Destructive Action (Actions like delete/destruction): `.btn-outline-danger` or `.btn-danger`
* **Accessibility (a11y):**
  * All interactive elements **MUST** support keyboard focus transitions using the layout's focus styling system:

```css
:focus-visible {
    outline: 2px solid var(--theme-accent) !important;
    outline-offset: 3px !important;
}
```

* Use correct ARIA landmark attributes if defining structural elements within your content section.

### 10.4 SEO & Structured Data

* **Metadata:** Controllers **MUST** pass these variables in view data, and templates **MUST** render them:
  * `pageTitle`, `metaDescription`, `metaKeywords`, `canonicalUrl`, `robotsTag`, `metaImage`
* **Indexing Strategy:**
  * Public Pages: USE `index, follow` (Marketing, informative, and public tool pages).
  * Private/Auth Pages: USE `noindex, follow` (Auth forms, User dashboards, Admin panels).
* **JSON-LD Schema:** Provide a targeted JSON-LD `<script type="application/ld+json">` configuration block relevant to the page context (e.g., `WebPage`, `Product`, `Organization`, or `BreadcrumbList`).
* **Social Sharing:** Layouts **MUST** include complete Open Graph AND Twitter Card meta tags:
  * Open Graph: `og:type`, `og:url`, `og:title`, `og:description`, `og:image`
  * Twitter Card: `twitter:card`, `twitter:site`, `twitter:title`, `twitter:description`, `twitter:image`, `twitter:image:alt`

---

## Part 11: Bootstrap 5 Pagination Template (Standard)

All pagination views **MUST** use this template structure:

```php
<?php
use CodeIgniter\Pager\PagerRenderer;

/**
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>
<nav aria-label="<?= lang('Pager.pageNavigation') ?>">
    <ul class="pagination" style="flex-wrap: wrap;">
        <!-- First & Previous Links -->
        <?php if ($pager->hasPreviousPage()) : ?>
            <li class="page-item">
                <a href="<?= $pager->getFirst() ?>" class="page-link" aria-label="<?= lang('Pager.first') ?>">
                    <span aria-hidden="true"><?= lang('Pager.first') ?></span>
                </a>
            </li>
            <li class="page-item">
                <a href="<?= $pager->getPreviousPage() ?>" class="page-link" aria-label="<?= lang('Pager.previous') ?>">
                    <span><?= lang('Pager.previous') ?></span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1" aria-disabled="true"><?= lang('Pager.first') ?></a>
            </li>
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1" aria-disabled="true"><?= lang('Pager.previous') ?></a>
            </li>
        <?php endif ?>

        <!-- Numbered Links -->
        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <a href="<?= $link['uri'] ?>" class="page-link" <?= $link['active'] ? 'aria-current="page"' : '' ?>>
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <!-- Next & Last Links -->
        <?php if ($pager->hasNextPage()) : ?>
            <li class="page-item">
                <a href="<?= $pager->getNextPage() ?>" class="page-link" aria-label="<?= lang('Pager.next') ?>">
                    <span><?= lang('Pager.next') ?></span>
                </a>
            </li>
            <li class="page-item">
                <a href="<?= $pager->getLast() ?>" class="page-link" aria-label="<?= lang('Pager.last') ?>">
                    <span aria-hidden="true"><?= lang('Pager.last') ?></span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1" aria-disabled="true"><?= lang('Pager.next') ?></a>
            </li>
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1" aria-disabled="true"><?= lang('Pager.last') ?></a>
            </li>
        <?php endif ?>
    </ul>
</nav>
```

---

## Part 12: Tooling, CLI Architecture & Custom Spark Commands

### 12.1 Custom Spark Commands Architecture

* Custom CLI commands **MUST** extend `CodeIgniter\CLI\BaseCommand` and reside in `app/Commands/` (or module `Commands/`).
* Commands **MUST** define `$group`, `$name`, `$description`, and `$usage` class properties with explicit PHPDoc comments:

```php
<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Class DomainSyncCommand
 *
 * Synchronizes domain catalog records via Spark CLI.
 *
 * @package App\Commands
 * @author  Senior Developer
 * @since   1.0.0
 */
class DomainSyncCommand extends BaseCommand
{
    /**
     * Command Group.
     *
     * @var string
     */
    protected $group = 'Domain';

    /**
     * Command Name.
     *
     * @var string
     */
    protected $name = 'domain:sync';

    /**
     * Command Description.
     *
     * @var string
     */
    protected $description = 'Synchronizes domain catalog records.';

    /**
     * Command Usage.
     *
     * @var string
     */
    protected $usage = 'domain:sync [options]';

    /**
     * Command Arguments.
     *
     * @var array<string, string>
     */
    protected $arguments = [];

    /**
     * Command Options.
     *
     * @var array<string, string>
     */
    protected $options = [
        '--dry-run' => 'Simulates synchronization without database modification.',
    ];

    /**
     * Execute the command.
     *
     * @param array<string, mixed> $params
     */
    public function run(array $params): int
    {
        $isDryRun = array_key_exists('dry-run', $params) || CLI::getOption('dry-run');

        CLI::write('Executing domain sync...', 'yellow');

        /** @var \App\Modules\Domain\Libraries\DomainService $service */
        $service = service('domainService');
        $service->syncRecords((bool) $isDryRun);

        CLI::write('Domain sync finished.', 'green');

        return EXIT_SUCCESS;
    }
}
```

### 12.2 Interactive Prompts & Signal Trapping

* **CLI Prompts:** Interactive user inputs **MUST** use `CLI::prompt()` or `CLI::promptByKey()` with strict validation callbacks.
* **CLI Signals (`SIGINT`, `SIGTERM`):** Long-running CLI loops or daemon workers **MUST** handle POSIX signals gracefully via `CLI::trap()` or signal listener loops to perform clean shutdown and database transaction rollbacks before process termination.

### 12.3 Asset Publishing Architecture (`Publisher`)

* Static frontend vendor assets, third-party libraries, or module distribution files **MUST** be deployed to `public/` using `CodeIgniter\Publisher\Publisher`.
* Direct manual copy-pasting of vendor assets into `public/` is strictly **FORBIDDEN**. Use automated Publisher classes extending `CodeIgniter\Publisher\Publisher` registered in `app/Publishers/`.

---

## Part 13: Advanced Architecture — View Cells, Events, CSP & Worker Mode

### 13.1 Modular View Cells & Decorators

* **View Cells:** Reusable, encapsulated UI widgets that require their own data fetching or processing logic **MUST** be implemented as View Cells (`view_cell('DomainCell::render', ['limit' => 5])`) extending `CodeIgniter\View\Cells\Cell`. Embedding service calls directly inside raw view files is strictly **FORBIDDEN**.
* **View Decorators:** Global view transformations (such as automatic minification or security header injection) **MUST** implement `CodeIgniter\View\ViewDecoratorInterface` and be registered in `Config\View::$decorators`.

### 13.2 Event-Driven Architecture (`Events`)

* Application domain events (e.g., `post_system`, `user_registered`, `transaction_completed`) **MUST** use `CodeIgniter\Events\Events::trigger('eventName', $payload)`.
* Event listeners **MUST** be registered in `app/Config/Events.php` or module-level config files.
* Direct synchronous coupling of non-essential secondary actions (like sending external transactional emails) inside primary controller flows is **FORBIDDEN**; dispatch them via events.

### 13.3 Content Security Policy (CSP) & CORS Enforcement

* **CSP Management:** Strict Content Security Policies **MUST** be enabled in `app/Config/ContentSecurityPolicy.php`. Nonce tokens generated via `$this->response->getCSP()->getContext('script')` **MUST** be applied to all inline `<script>` tags.
* **Cross-Origin Resource Sharing (CORS):** Cross-origin API routes **MUST** use `CodeIgniter\Filters\CorsFilter` with explicitly declared allowed origins, methods, and headers in `app/Config/Cors.php`. Wildcard (`*`) CORS headers in production environments are strictly **FORBIDDEN**.
* **Honeypot Bot Defense:** Public registration and contact forms **MUST** enable `CodeIgniter\Filters\Honeypot` via `app/Config/Honeypot.php` to prevent automated form submission spam without requiring intrusive CAPTCHAs.

### 13.4 Content Negotiation & RESTful Transformers

* API endpoints supporting multiple content formats **MUST** use Content Negotiation (`$this->request->negotiateMedia(['text/html', 'application/json', 'application/xml'])`).
* Standardized REST API data formatting **MUST** utilize `ResponseTrait` (`$this->respond()`, `$this->failNotFound()`, `$this->failValidationErrors()`) or dedicated Resource Transformers.

### 13.5 Worker Mode & Persistent Execution

* If running CodeIgniter 4 in Worker Mode (e.g., RoadRunner, FrankenPHP, Swoole, or `php spark worker`), services **MUST NOT** store mutable request-scoped state inside static properties or long-lived singleton instances.
* Request-dependent services **MUST** be reset between cycles via `Services::reset()` or registered as non-shared factory instances.

---

## Part 14: Testing Architecture & Quality Assurance Standards

### 14.1 Testing Mandate & Test Suite Foundations

* **Mandatory Coverage:** Automated testing is **REQUIRED** for every module, service, model, controller, filter, and Spark CLI command. No feature or endpoint is considered complete without automated tests.
* **Test Runner Tooling:** All tests **MUST** execute via PHPUnit (`php spark test` or `./vendor/bin/phpunit`).
* **Base Test Class:** All unit and feature tests **MUST** extend `CodeIgniter\Test\CIUnitTestCase`.
* **Setup & Teardown Protocol:** Subclasses overriding `setUp(): void` or `tearDown(): void` **MUST** call `parent::setUp()` and `parent::tearDown()` to ensure framework staging, database migrations/transactions, and global mock states are accurately initialized and cleaned.
* **Time Testing:** Time-dependent logic **MUST** utilize `Time::setTestNow($frozenTime)` to freeze timestamps during tests and **MUST** invoke `Time::setTestNow()` (with no arguments) in `tearDown()` or immediately following assertions to restore the real system clock.

### 14.2 Database Testing Standards (`DatabaseTestTrait`)

* **Trait Usage:** Any test interacting with or verifying database state **MUST** use `CodeIgniter\Test\DatabaseTestTrait`.
* **Database Staging Policies:**
  * `$migrate = true;` **MUST** be declared to automatically migrate the test database before tests execute.
  * `$namespace = 'App\Modules\[ModuleName]';` **MUST** be set to limit migrations to the relevant module context, or set to `null` to run all migrations across the entire workspace.
  * `$refresh = true;` **MAY** be used to rebuild database tables before each test method if strict data isolation is required.
  * Seeders **MUST** be configured via `$seed = 'MainSeeder';` (or `$seedOnce = true;` for suite-wide seeding) or explicitly invoked within `setUp()` via `$this->seed('DomainSeeder');`.
* **Transaction Rollback:** `DatabaseTestTrait` automatically encapsulates tests in transactions. Tests **MUST NOT** leave persistent dirty records in test databases across test executions.
* **Database Assertions:**
  * `$this->seeInDatabase('table_name', ['column' => $expectedValue]);` **MUST** be used to verify persisted rows.
  * `$this->dontSeeInDatabase('table_name', ['column' => $forbiddenValue]);` **MUST** be used to verify row deletions or invalidations.
  * `$this->seeNumRecords(int $expectedCount, 'table_name', array $where = []);` **MUST** be used to assert record quantities.
  * `$this->grabFromDatabase('table_name', 'column', array $where);` **MAY** be used to fetch a single column value for dynamic comparisons.
  * `$this->hasInDatabase('table_name', array $data);` **MAY** be used to insert a raw row directly for test setup.

### 14.3 Test Data Generation (`Fabricator`)

* **Model Compatibility:** `CodeIgniter\Test\Fabricator` **MUST** be used to generate test datasets and seed mock data dynamically using Faker. Target models **MUST** extend `CodeIgniter\Model` or implement `CodeIgniter\Test\Interfaces\FabricatorModel`.
* **Definition Formatters:** Custom fabricator definitions **MUST** reside within model classes via `fake(Generator &$faker): array` or dedicated formatters to define default dummy schemas.
* **Data Creation Standards:**
  * `$fabricator = new Fabricator(DomainModel::class);`
  * In-memory mock entity generation: `$fabricator->make();` or `$fabricator->make(int $count);`
  * Persisted database generation: `$fabricator->create();` or `$fabricator->create(int $count);`
* **Overrides & Custom Payloads:** Explicit test attributes **MUST** be injected via `$fabricator->setOverrides(['column' => 'specific_value'])` or `$fabricator->create(['column' => 'value'])` rather than manually crafting raw SQL inserts.

### 14.4 HTTP Feature Testing (`FeatureTestTrait`)

* **Trait Usage:** End-to-end HTTP controller and route verification **MUST** extend `CIUnitTestCase` and include both `DatabaseTestTrait` and `CodeIgniter\Test\FeatureTestTrait`.
* **Request Execution:**
  * HTTP GET: `$this->get(string $uri, array $params = []);`
  * HTTP POST: `$this->post(string $uri, array $params = []);`
  * HTTP PUT/PATCH/DELETE: `$this->put($uri, $params)`, `$this->patch($uri, $params)`, `$this->delete($uri, $params)`.
  * JSON Request Payload: `$this->withBodyFormat('json')->post($uri, $jsonArray);`
* **Request Context Configuration:**
  * Authenticated Session Simulation: `$this->withSession(['user_id' => 1, 'logged_in' => true])->get($uri);`
  * Custom HTTP Headers: `$this->withHeaders(['Accept' => 'application/json', 'Authorization' => 'Bearer token'])->get($uri);`
  * Bypass Events: `$this->skipEvents()->post($uri, $data);` when side-effect events (e.g., mail dispatching) **MUST** be silenced.
  * Custom Routes Injection: `$this->withRoutes($routes)->get($uri);` for isolated route collections.

### 14.5 Response Assertion Standards (`TestResponse`)

All feature test requests return an instance of `CodeIgniter\Test\TestResponse`. Assertions **MUST** be chained or called directly on this instance:

* **HTTP Status Assertions:**
  * `$result->assertOK();` (Status 200)
  * `$result->assertStatus(int $code);` (Exact HTTP status check)
  * `$result->assertRedirect();` and `$result->assertRedirectTo(string $uri);` (Post/Redirect/Get flow verification)
* **DOM & Content Assertions:**
  * `$result->assertSee(string $search, ?string $element = null);` (Verifies text presence within optional selector e.g. `'h1'`)
  * `$result->assertDontSee(string $search, ?string $element = null);` (Verifies text absence)
  * `$result->assertSeeElement(string $selector);` (Verifies presence of CSS element/tag)
* **JSON Response Assertions:**
  * `$result->assertJSONExact(array|string $json);` (Exact JSON equality check)
  * `$result->assertJSONFragment(array $subset);` (Verifies JSON structure contains specified key-value subset)
* **Session & Flash Data Assertions:**
  * `$result->assertSessionHas(string $key, $value = null);` (Verifies session state or flash key)
  * `$result->assertSessionMissing(string $key);` (Verifies session absence)
* **Header & Cookie Assertions:**
  * `$result->assertHeader(string $name, $value = null);`
  * `$result->assertCookie(string $name, $value = null);`

### 14.6 Isolated Controller & Filter Testing (`ControllerTestTrait` / `FilterTestTrait`)

* **Isolated Controller Testing:** When executing controller methods without full HTTP lifecycle overhead, use `CodeIgniter\Test\ControllerTestTrait`:
  * Initialize via `$this->withUri($uri)->controller(DomainController::class)->execute('index');`
  * Inject custom requests or dependencies via `$this->withRequest($request)->withResponse($response)->withConfig($config)`.
* **Filter Pipeline Testing:** Route filters **MUST** be tested in isolation using `CodeIgniter\Test\FilterTestTrait`:
  * Evaluate filter triggers: `$this->getFilterForRoute('domain/record', 'before');`
  * Validate before/after execution: `$this->caller->filter($uri, 'before');`

### 14.7 CLI Command Testing (`MockInputOutput` & `StreamFilterTrait`)

* **Testing Spark Commands:** All custom commands extending `CodeIgniter\CLI\BaseCommand` **MUST** be verified via unit tests.
* **Interactive Command Testing (`MockInputOutput`):**
  * Use `CodeIgniter\Test\Mock\MockInputOutput` to test commands that call `CLI::prompt()`, `CLI::input()`, or `CLI::wait()`.
  * Set input streams: `CITestStreamFilter::$buffer = '';` or inject inputs into `MockInputOutput`.
  * Assert CLI Output:

```php
$io = new MockInputOutput();
CLI::setInputOutput($io);
command('domain:process --flag');
$this->assertContains('Process completed successfully.', $io->getOutputs());
```

### 14.8 Mocking Services, Factories & External Dependencies

* **Service Injection:** External services, mailers, and cache layers **MUST** be mocked during unit testing to maintain complete test isolation.
* **Cache Mocking:**

```php
$mockCache = mock(\CodeIgniter\Cache\CacheFactory::class);
$mockCache->bypass(); // Disable actual caching or assert cache calls:
$mockCache->assertHas('cache_key');
```

* **Service Container Mock Injection:**

```php
$mockService = $this->createMock(ExternalApiService::class);
$mockService->method('fetchData')->willReturn(['status' => 'mocked']);
\Config\Services::injectMock('externalApi', $mockService);
```

* **Service Reset Protocol:** In multi-test suites or `tearDown()`, always invoke `\Config\Services::reset()` or `\Config\Services::resetSingle('serviceName')` to prevent contaminated mock state from leaking into subsequent test methods.
* **Factory Mocking:** Replace factory instances via `\CodeIgniter\Config\Factories::injectMock('models', DomainModel::class, $mockModel);`.

### 14.9 Session Testing Architecture (`ArrayHandler`)

* **In-Memory Session Driver:** Session-isolated unit tests **MUST** utilize `CodeIgniter\Session\Handlers\ArrayHandler` with `CodeIgniter\Session\Session` to test state manipulation entirely in-memory without persistent disk or database IO.
* **Standard PHPUnit Session Assertions:** Direct session object tests **MUST** use native assertions (`$this->assertSame($val, $session->get('key'))`, `$this->assertNull($session->get('removed_key'))`), while HTTP feature tests **MUST** use `$result->assertSessionHas()`.

### 14.10 Benchmarking & Debugging Performance Constraints

* **Execution Benchmarking:** Performance-critical routines and batch data operations **MUST** use the Benchmark Timer:

```php
$benchmark = \Config\Services::timer();
$benchmark->start('operation_key');
// ... execute domain operation ...
$benchmark->stop('operation_key');
$elapsedTime = $benchmark->getElapsedTime('operation_key');
```

* **Production Debug Hygiene:** Committing debug functions (`d()`, `dd()`, `var_dump()`, `print_r()`) or unfiltered Kint collectors to version control is strictly **FORBIDDEN**. Continuous integration pipelines **MUST** reject commits containing debug statements.

---

## Part 15: Boot & Deployment Standard Operating Procedure

### 15.1 Local Development Boot (3 Steps)

A developer **MUST** initialize the local workspace using exactly three sequential commands:

```bash
composer install
php spark migrate --all
php spark db:seed MainSeeder
```

### 15.2 Production Deployment Checklist

The deployment process **MUST** execute in this exact sequence:

1. **Environment Configuration:** Set `CI_ENVIRONMENT = production` and `display_errors = 0`.
2. **Dependency Installation:**

    ```bash
    composer install --no-dev --optimize-autoloader --no-interaction
    ```

3. **Framework Optimization:**

    ```bash
    php spark optimize
    ```

    *This serializes and caches routing and configuration definitions.*

4. **Database Migration:**

    ```bash
    php spark migrate --all
    ```

5. **Web Server Configuration:**
   * Document root **MUST** point strictly to `/public`.
   * Access to `/app`, `/system`, `/writable`, `/vendor` is **FORBIDDEN**.
   * `mod_rewrite` or equivalent **MUST** be enabled.
   * The `writable/` directory **MUST** be writable by the web server user.

---

## Part 16: Contextual Flow — Endpoint Verification & Log-Auditing Protocol

Contextual self-verification and debugging cycles **MUST** be executed iteratively during task execution to guarantee route integrity, error containment, and zero runtime regressions.

### 16.1 The Endpoint Verification Standard

* **Direct Testing:** Verification of all route `php spark routes` URLs starting from the base URL (e.g., `http://localhost:8080` or target staging host) **MUST** be executed programmatically using a browser as the first-priority alternative or as fallback using raw local cURL commands (`curl <url>`, `curl -i <url>`, `curl -L <url>`) or local HTTP utility executions.
* **Route Validation:** Every target URL resolved via route configuration **MUST** be directly requested and validated. Hardcoding or assuming frontend page rendering behavior without direct execution is strictly **FORBIDDEN**.
* **Front-end Behavior Verification:** Verification **MUST** ensure that HTML renders as expected, returns proper status codes, and does not yield broken content wrappers or missing layout references.

### 16.2 Simultaneous Log Auditing

* **Simultaneous Reading:** During every route verification cycle, active system log files located in the `writable/logs/` directory **MUST** be simultaneously inspected and read.
* **Error Identification:** Logs **MUST** be parsed to locate CodeIgniter 4 exceptions, execution warnings, database driver failures, or runtime notices triggered during the request.

### 16.3 Iterative Repair and Log Clean-up Loop

If route failures or error entries are found in the log files, the following exact sequence of self-correction **MUST** be performed:

1. **Analyze logs:** Determine the root cause of the exception or warning from the parsed log file.
2. **Apply fix:** Modify files within standard modules or configuration parameters in strict accordance with Modular MVC-S limits.
3. **Purge baseline logs:** Delete or truncate the contents of active log files inside `writable/logs/` to establish a clean state.
4. **Re-trigger verification:** Execute the cURL command against the failed target URL once more.
5. **Confirm zero logs:** Re-read the log directory to confirm that the endpoint behaves correctly and that no new errors or warnings are created.
6. **Iterate:** Repeat steps 1–5 until all routes resolve cleanly and the system logs remain entirely clean.

### 16.4 Auxiliary Testing

Automated tests (`php spark test`) **MAY** be executed to isolate, reproduce, and resolve exceptions:

* Run a specific file: `php spark test --file tests/Controllers/DomainControllerTest.php` or `./vendor/bin/phpunit tests/Controllers/DomainControllerTest.php`
* Filter by test name: `php spark test --file tests/Controllers/DomainControllerTest.php --filter testCreateSavesToDatabaseAndRedirects`
* Run a specific directory: `php spark test --directory Controllers`

*All temporary testing files, mock inputs, or system-generated caches MUST be cleaned up and restored to a pristine state prior to project finalization.*

### 16.5 Completion Definition

The task is considered complete **ONLY** when all route URLs resolved relative to the base URL have been verified and confirmed to behave correctly, generating no framework exceptions and producing clean logs under sequential execution.

---

## Appendix: Robust CodeIgniter 4 MVC-S Domain Module (Reference Implementation)

### Step 1: Register Module Autoload Namespace

Map namespace in `app/Config/Autoload.php`:

```php
public array $psr4 = [
    APP_NAMESPACE               => APPPATH,
    'App\Modules\[ModuleName]' => APPPATH . 'Modules/[ModuleName]',
];
```

### Step 2: Register Module Service Container Binding

`app/Modules/[ModuleName]/Config/Services.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\[ModuleName]\Config;

use App\Modules\[ModuleName]\Libraries\DomainService;
use App\Modules\[ModuleName]\Models\DomainModel;
use CodeIgniter\Config\BaseService;

/**
 * Class Services
 *
 * @package App\Modules\[ModuleName]\Config
 * @author  Senior Developer
 * @since   1.0.0
 */
class Services extends BaseService
{
    /**
     * Factory method for DomainService.
     *
     * @param bool $getShared Whether to return a shared instance.
     *
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

### Step 3: Route Configuration

`app/Modules/[ModuleName]/Config/Routes.php`:

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
});
```

### Step 4: Database Schema Migration

`app/Modules/[ModuleName]/Database/Migrations/2026-09-05-000001_CreateDomainTable.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\[ModuleName]\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Class CreateDomainTable
 *
 * @package App\Modules\[ModuleName]\Database\Migrations
 * @author  Senior Developer
 * @since   1.0.0
 */
class CreateDomainTable extends Migration
{
    /**
     * Executes table creation.
     */
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'domain_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'value' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'metadata' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('domain_id');
        $this->forge->addKey(['domain_id', 'is_active']);
        $this->forge->addKey('created_at');

        $this->forge->createTable('domain_table', true, [
            'ENGINE'          => 'InnoDB',
            'CHARACTER SET'   => 'utf8mb4',
            'COLLATE'         => 'utf8mb4_unicode_ci',
            'ROW_FORMAT'      => 'DYNAMIC',
        ]);
    }

    /**
     * Reverts table creation.
     */
    public function down(): void
    {
        $this->forge->dropTable('domain_table', true);
    }
}
```

### Step 5: Entity Definition

`app/Modules/[ModuleName]/Entities/DomainEntity.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\[ModuleName]\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Class DomainEntity
 *
 * @package App\Modules\[ModuleName]\Entities
 * @author  Senior Developer
 * @since   1.0.0
 *
 * @property int $id
 * @property int $domain_id
 * @property float $value
 * @property bool $is_active
 * @property array<string, mixed> $metadata
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 * @property \CodeIgniter\I18n\Time|null $deleted_at
 */
class DomainEntity extends Entity
{
    /**
     * Defined cast types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id'        => 'integer',
        'domain_id' => 'integer',
        'value'     => 'float',
        'is_active' => 'boolean',
        'metadata'  => 'json-array',
    ];

    /**
     * Fields mutated into Time instances.
     *
     * @var array<int, string>
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
```

### Step 6: Model Layer

`app/Modules/[ModuleName]/Models/DomainModel.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\[ModuleName]\Models;

use App\Modules\[ModuleName]\Entities\DomainEntity;
use CodeIgniter\Model;

/**
 * Class DomainModel
 *
 * @package App\Modules\[ModuleName]\Models
 * @author  Senior Developer
 * @since   1.0.0
 *
 * @property \CodeIgniter\Database\BaseConnection $db
 */
class DomainModel extends Model
{
    /**
     * Target table name.
     *
     * @var string
     */
    protected $table = 'domain_table';

    /**
     * Primary key column.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Return type entity mapping.
     *
     * @var class-string
     */
    protected $returnType = DomainEntity::class;

    /**
     * Whitelist of insertable and updatable columns.
     *
     * @var array<int, string>
     */
    protected $allowedFields = [
        'domain_id',
        'value',
        'is_active',
        'metadata',
    ];

    /**
     * Timestamp management.
     *
     * @var bool
     */
    protected $useTimestamps  = true;

    /**
     * Timestamp format.
     *
     * @var string
     */
    protected $dateFormat     = 'datetime';

    /**
     * Created field name.
     *
     * @var string
     */
    protected $createdField   = 'created_at';

    /**
     * Updated field name.
     *
     * @var string
     */
    protected $updatedField   = 'updated_at';

    /**
     * Deleted field name.
     *
     * @var string
     */
    protected $deletedField   = 'deleted_at';

    /**
     * Soft delete management.
     *
     * @var bool
     */
    protected $useSoftDeletes = false;
}
```

### Step 7: Service Library Layer

`app/Modules/[ModuleName]/Libraries/DomainService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\[ModuleName]\Libraries;

use App\Modules\[ModuleName]\Entities\DomainEntity;
use App\Modules\[ModuleName]\Models\DomainModel;
use Exception;
use Throwable;

/**
 * Class DomainService
 *
 * Encapsulates all domain and business operations for the Module.
 *
 * @package App\Modules\[ModuleName]\Libraries
 * @author  Senior Developer
 * @since   1.0.0
 */
class DomainService
{
    /**
     * Injected Domain Model instance.
     *
     * @var DomainModel
     */
    protected DomainModel $domainModel;

    /**
     * DomainService constructor.
     * Uses explicit dependency injection through the constructor.
     *
     * @param DomainModel $domainModel Injected model dependency
     */
    public function __construct(DomainModel $domainModel)
    {
        $this->domainModel = $domainModel;
    }

    /**
     * Create record with transaction-safe encapsulation.
     *
     * @param DomainEntity $entity Unpersisted DomainEntity
     *
     * @return DomainEntity Persisted entity with database assigned ID
     *
     * @throws Throwable
     */
    public function createRecord(DomainEntity $entity): DomainEntity
    {
        $db = $this->domainModel->db;

        try {
            $db->transException(true)->transStart();

            $id = $this->domainModel->insert($entity);

            if ($id === false) {
                throw new Exception('Database insertion failure.');
            }

            /** @var DomainEntity|null $savedEntity */
            $savedEntity = $this->domainModel->find($id);

            if ($savedEntity === null) {
                throw new Exception('Failed to retrieve created record.');
            }

            $db->transComplete();

            return $savedEntity;
        } catch (Throwable $e) {
            $db->transRollback();

            log_message('error', 'Record creation transaction failed: ' . $e->getMessage(), [
                'entity_attributes' => $entity->toArray(),
                'trace'             => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Fetches all records from database.
     *
     * @return array<int, DomainEntity>
     */
    public function getAllRecords(): array
    {
        /** @var array<int, DomainEntity> $records */
        $records = $this->domainModel->findAll();

        return $records;
    }
}
```

### Step 8: Controller Implementation

`app/Modules/[ModuleName]/Controllers/DomainController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\[ModuleName]\Controllers;

use App\Controllers\BaseController;
use App\Modules\[ModuleName]\Entities\DomainEntity;
use App\Modules\[ModuleName]\Libraries\DomainService;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * Class DomainController
 *
 * Orchestrates HTTP requests and responses for Domain resources.
 *
 * @package App\Modules\[ModuleName]\Controllers
 * @author  Senior Developer
 * @since   1.0.0
 */
class DomainController extends BaseController
{
    /**
     * Dynamic helper registrations.
     *
     * @var array<int, string>
     */
    protected $helpers = ['form'];

    /**
     * Injected Domain Service instance.
     *
     * @var DomainService
     */
    protected DomainService $domainService;

    /**
     * DomainController constructor.
     * Resolves Service Container dependencies strictly via service().
     */
    public function __construct()
    {
        /** @var DomainService $service */
        $service = service('domainService');
        $this->domainService = $service;
    }

    /**
     * Renders record list view.
     *
     * @return string|ResponseInterface
     */
    public function index(): string|ResponseInterface
    {
        /** @var array<int, DomainEntity> $records */
        $records = $this->domainService->getAllRecords();

        return view('App\Modules\[ModuleName]\Views\record\index', [
            'pageTitle'       => 'Domain Records',
            'metaDescription' => 'Access and review your domain records and generated entries.',
            'metaKeywords'    => 'domain, record, entries, account',
            'canonicalUrl'    => url_to('domain.record.index'),
            'robotsTag'       => 'noindex, follow',
            'metaImage'       => base_url('assets/images/og-default.jpg'),
            'schemaJson'      => null,
            'data'            => $records,
        ]);
    }

    /**
     * Renders form creation view.
     *
     * @return string|ResponseInterface
     */
    public function new(): string|ResponseInterface
    {
        return view('App\Modules\[ModuleName]\Views\record\new', [
            'pageTitle'       => 'Create Domain Record',
            'metaDescription' => 'Submit a new domain record.',
            'metaKeywords'    => 'domain, record, create, new',
            'canonicalUrl'    => url_to('domain.record.new'),
            'robotsTag'       => 'noindex, follow',
            'metaImage'       => base_url('assets/images/og-default.jpg'),
            'schemaJson'      => null,
        ]);
    }

    /**
     * Process Record Submission (PRG Pattern).
     *
     * @return string|ResponseInterface
     */
    public function create(): string|ResponseInterface
    {
        $rules = [
            'domain_id' => 'required|integer',
            'value'     => 'required|decimal',
        ];

        if (! $this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        try {
            $entity = new DomainEntity([
                'domain_id' => (int) $this->request->getPost('domain_id'),
                'value'     => (float) $this->request->getPost('value'),
                'is_active' => false,
                'metadata'  => ['source' => 'web_interface'],
            ]);

            $this->domainService->createRecord($entity);

            return redirect()
                ->to(url_to('domain.record.index'))
                ->with('success', 'Record created successfully.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create record: ' . $e->getMessage());
        }
    }
}
```

### Step 9: View Layout Implementation

`app/Views/layouts/default.php`:

```php
<?php
/**
 * Master Default Layout Skeleton
 *
 * @var string|null $pageTitle
 * @var string|null $metaDescription
 * @var string|null $metaKeywords
 * @var string|null $robotsTag
 * @var string|null $canonicalUrl
 * @var string|null $metaImage
 * @var array<string, mixed>|null $schemaJson
 */
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
    <title><?= esc($pageTitle ?? 'Default Title', 'html') ?></title>
    <meta name="description" content="<?= esc($metaDescription ?? '', 'attr') ?>">
    <meta name="keywords" content="<?= esc($metaKeywords ?? '', 'attr') ?>">
    <meta name="robots" content="<?= esc($robotsTag ?? 'index, follow', 'attr') ?>">
    <link rel="canonical" href="<?= esc($canonicalUrl ?? current_url(), 'attr') ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= esc($pageTitle ?? '', 'attr') ?>">
    <meta property="og:description" content="<?= esc($metaDescription ?? '', 'attr') ?>">
    <meta property="og:url" content="<?= esc($canonicalUrl ?? current_url(), 'attr') ?>">
    <meta property="og:image" content="<?= esc($metaImage ?? '', 'attr') ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@AppDefault">
    <meta name="twitter:title" content="<?= esc($pageTitle ?? '', 'attr') ?>">
    <meta name="twitter:description" content="<?= esc($metaDescription ?? '', 'attr') ?>">
    <meta name="twitter:image" content="<?= esc($metaImage ?? '', 'attr') ?>">
    <meta name="twitter:image:alt" content="<?= esc($pageTitle ?? 'Preview image', 'attr') ?>">

    <!-- Framework Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <?php if (! empty($schemaJson)) : ?>
        <script type="application/ld+json">
            <?= json_encode($schemaJson, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
        </script>
    <?php endif ?>

    <style>
        :root, [data-bs-theme="light"] {
            --theme-bg-light: #ffffff;
            --theme-primary: #000000;
            --theme-accent: #0d6efd;
            --theme-accent-rgb: 13, 110, 253;
            --theme-card-bg: #ffffff;
        }
        [data-bs-theme="dark"] {
            --theme-bg-light: #121212;
            --theme-primary: #ffffff;
            --theme-accent: #0d6efd;
            --theme-accent-rgb: 13, 110, 253;
            --theme-card-bg: #1e1e1e;
        }
        body {
            background-color: var(--theme-bg-light);
            color: var(--theme-primary);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
        }
        :focus-visible {
            outline: 2px solid var(--theme-accent) !important;
            outline-offset: 3px !important;
        }
        .blueprint-card {
            background-color: var(--theme-card-bg);
            border: 1px solid var(--theme-primary);
            border-radius: 0.5rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .blueprint-card:hover {
            border-color: var(--theme-accent);
        }
        .blueprint-header {
            border-bottom: 2px solid var(--theme-primary);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <header class="navbar navbar-expand-lg border-bottom mb-4">
        <div class="container">
            <span class="navbar-brand fw-bold">Domain Manager</span>
        </div>
    </header>

    <main class="container">
        <?= $this->include('partials/flash_messages') ?>
        <?= $this->renderSection('content') ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        window.getCSRFToken = function() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        };
        window.updateCSRFToken = function(newToken) {
            document.querySelector('meta[name="csrf-token"]').setAttribute('content', newToken);
            document.querySelectorAll('input[name="<?= csrf_token() ?>"]').forEach(function(input) {
                input.value = newToken;
            });
        };
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
```

### Step 10: View Partials (Flash Messages)

`app/Views/partials/flash_messages.php`:

```php
<?php if (session()->has('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= esc(session('success'), 'html') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif ?>

<?php if (session()->has('error')) : ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= esc(session('error'), 'html') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif ?>

<?php if (session()->has('errors')) : ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach ((array) session('errors') as $error) : ?>
                <li><?= esc($error, 'html') ?></li>
            <?php endforeach ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif ?>
```

### Step 11: View Templates

`app/Modules/[ModuleName]/Views/record/index.php`:

```php
<?php
/**
 * @var array<int, \App\Modules\[ModuleName]\Entities\DomainEntity> $data
 */
?>
<?= $this->extend('layouts/default') ?>

<?= $this->section('content') ?>
<div class="container my-5">
    <div class="blueprint-header d-flex justify-content-between align-items-center">
        <h1 class="h2 mb-0">Domain Records</h1>
        <a href="<?= url_to('domain.record.new') ?>" class="btn btn-primary">Create Record</a>
    </div>

    <div class="card blueprint-card p-4 shadow-sm">
        <?php if (empty($data)) : ?>
            <p class="text-muted mb-0">No domain records found.</p>
        <?php else : ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Record ID</th>
                            <th scope="col">Domain ID</th>
                            <th scope="col">Value</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $record) : ?>
                            <tr>
                                <td><?= esc((string) $record->id, 'html') ?></td>
                                <td><?= esc((string) $record->domain_id, 'html') ?></td>
                                <td><?= esc(number_format((float) $record->value, 2), 'html') ?></td>
                                <td>
                                    <?php if ($record->is_active) : ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else : ?>
                                        <span class="badge bg-warning text-dark">Inactive</span>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif ?>
    </div>
</div>
<?= $this->endSection() ?>
```

`app/Modules/[ModuleName]/Views/record/new.php`:

```php
<?php
/**
 * View template for new domain record creation form.
 */
?>
<?= $this->extend('layouts/default') ?>

<?= $this->section('content') ?>
<div class="container my-5">
    <div class="blueprint-header">
        <h1 class="h2 mb-0">Create Domain Record</h1>
    </div>

    <div class="card blueprint-card p-4 shadow-sm">
        <?= form_open(url_to('domain.record.create')) ?>

        <div class="form-floating mb-3">
            <input type="number" name="domain_id" class="form-control" id="domainId" placeholder="10293" value="<?= old('domain_id') ?>" required>
            <label for="domainId">Domain Record ID</label>
        </div>

        <div class="form-floating mb-3">
            <input type="number" name="value" step="0.01" class="form-control" id="recordValue" placeholder="150.00" value="<?= old('value') ?>" required>
            <label for="recordValue">Value</label>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Submit Record</button>
            <a href="<?= url_to('domain.record.index') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>

        <?= form_close() ?>
    </div>
</div>
<?= $this->endSection() ?>
```

### Step 12: PHPUnit Integration and Feature Test Suite

`tests/Controllers/DomainControllerTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Controllers;

use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Class DomainControllerTest
 *
 * End-to-end integration and HTTP flow test suite for Domain module endpoints.
 *
 * @package Tests\Controllers
 * @author  Senior Developer
 * @since   1.0.0
 */
class DomainControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    /**
     * Automatically stage module migrations.
     *
     * @var bool
     */
    protected $migrate = true;

    /**
     * Target module namespace for database migration execution.
     *
     * @var string|null
     */
    protected $namespace = 'App\Modules\[ModuleName]';

    /**
     * Set up tests before execution.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Populate system routes specifically for Domain module testing
        /** @var RouteCollection $routes */
        $routes = service('routes');
        $routes->group('domain', ['namespace' => 'App\Modules\[ModuleName]\Controllers'], static function (RouteCollection $routes): void {
            $routes->get('record', 'DomainController::index', ['as' => 'domain.record.index']);
            $routes->get('record/new', 'DomainController::new', ['as' => 'domain.record.new']);
            $routes->post('record', 'DomainController::create', ['as' => 'domain.record.create']);
        });
    }

    /**
     * Asserts record list renders correctly and returns successful HTTP status.
     */
    public function testIndexViewRendersSuccessfully(): void
    {
        $response = $this->get('/domain/record');
        $response->assertStatus(200);
        $response->assertSee('Domain Records', 'h1');
        $response->assertSee('No domain records found.', 'p');
    }

    /**
     * Asserts record submission successfully persists and redirects (PRG Pattern).
     */
    public function testCreateSavesToDatabaseAndRedirects(): void
    {
        $payload = [
            'domain_id' => '1029',
            'value'     => '520.45',
        ];

        $response = $this->post('/domain/record', $payload);

        // Assert Post/Redirect/Get behavior
        $response->assertRedirectTo(url_to('domain.record.index'));

        // Assert record exists within database
        $this->seeInDatabase('domain_table', [
            'domain_id' => 1029,
            'is_active' => 0,
        ]);
    }

    /**
     * Asserts validation failure triggers back-redirection with form input.
     */
    public function testCreateFailsValidationOnMissingInput(): void
    {
        $invalidPayload = [];

        $response = $this->post('/domain/record', $invalidPayload);

        $response->assertRedirect();
        $response->assertSessionHas('errors');
        $this->seeNumRecords(0, 'domain_table');
    }
}
```
