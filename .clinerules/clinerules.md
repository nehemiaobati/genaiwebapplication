# CodeIgniter 4 System Architecture Standard

## Part 0: Philosophy — Simple vs. Easy

> *"Conflating [Simple and Easy] is why we're drowning in complexity."*
> — **Rich Hickey**

This architecture standard strictly enforces the principles articulated in the 2011 foundation talk *"Simple Made Easy"* by Rich Hickey. Every architectural decision is evaluated against these objective criteria:

### Simple (Objective)

* **Etymology & Definition:** Derived from *simplex*, meaning "one fold" or "one braid." It describes objective system structure, concerns, tasks, and roles. It is defined by the total absence of entanglement.
* **Single Responsibility:** Each component executes exactly one deterministic operation in complete isolation.
* **Disentangled:** Components are unbraided. They are modified, refactored, and verified independently without triggering cascading side-effects. Short-term convenience is rejected in favor of unentangled, modular designs.
* **The Cost:** Demands deliberate upfront design, abstraction discipline, strict contracts, and continuous refactoring.
* **The Outcome:** Maximum long-term maintainability, deterministic debugging, and linear architectural scalability.

### Easy (Subjective)

* **Etymology & Definition:** Derived from *adjacens*, meaning "lying nearby" or "at hand." It describes developer comfort, familiarity, and immediate accessibility.
* **Near at Hand:** Familiar syntax, copy-paste snippets, adjacent files, global helpers, and monolithic shortcuts.
* **The Trap:** Convenient access patterns create complected (tightly braided) dependencies that trigger systemic brittleness.
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

* **Anti-Pattern (Easy):** Accessing `$this->request->getPost()` or calling `session()` directly inside Services or Models via global functions.
* **Standard (Simple):** Passing explicit, strictly typed scalar parameters, DTOs, or Entities into Services. Services remain completely disentangled from HTTP transport protocols.
* **Anti-Pattern (Easy):** Executing database queries inline within Controllers or Views using the active record builder `$db->table()`.
* **Standard (Simple):** Confining database schema interactions strictly within Models and accessing them via constructor-injected dependencies inside Services.
* **Anti-Pattern (Easy):** Masking missing Controller payloads with fallback-coalescing operators (`$var ?? 'default'`) inside Views.
* **Standard (Simple):** Requiring Controllers to satisfy explicit data contracts for Views, ensuring missing data triggers immediate execution failures during verification.

> **Core Mandate:** Architectural purity and structural simplicity (disentanglement) MUST NOT be sacrificed for developer comfort, brevity, or temporary convenience.

---

## Part 1: Core Meta-Rules

* **Universal / Strict Applicability:** These architectural rules apply unconditionally to **EVERY** file, class, trait, helper, entity, view, and model across the workspace. Zero exceptions are permitted.
* **Project-Agnostic / Explicit Placeholders:** All architectural rules, templates, and examples **MUST** use generic domain tokens (`DomainController`, `DomainService`, `DomainEntity`, `DomainModel`, `domain_id`) rather than project-specific identifiers or volatile domain files.
* **No Ambiguity / Deterministic Language:** All specifications **MUST** strictly adhere to RFC 2119 terminology. The keywords `MUST`, `MUST NOT`, `REQUIRED`, `SHALL`, `SHALL NOT`, `MAY`, and `FORBIDDEN` are mandatory. Permissive phrases (*"should"*, *"generally"*, *"recommended"*, *"best practice"*) are strictly **FORBIDDEN**.
* **Zero Fallback Reliance:** Fallback-reliant code patterns—including unvalidated defaults, null-coalescing chains masking missing dependencies, and silent exception swallows—are strictly **FORBIDDEN**. Operations **MUST** either succeed deterministically or fail explicitly.
* **Pre-Task Alignment:** This standard **MUST** be loaded and aligned with prior to initiating any design, refactoring, or implementation task.
* **Contextual Verification & Log-Auditing Compliance:** An active self-verification loop on all routing endpoints **MUST** be executed via automated tests, native CLI requests, cURL, or local HTTP commands, checking both front-end response states and writable log files to confirm zero warning or error traces in `writable/logs/` before finishing and declaring any task complete.
* **Automated Static Analysis & Gate Enforcement:** Continuous Integration (CI) pipelines and local pre-commit hooks **MUST** enforce zero-tolerance static analysis via PHPStan (Level 8) and PHP CodeSniffer (`PSR-12`). Any commit introducing untyped boundary parameters, dynamic properties, unhandled exceptions, or forbidden permissive phrases **SHALL** fail the verification gate.

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
└── Views/          # Clean presentation files with HTML escaping / Bootstrap 5.3 templates
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

*Registering the module namespace in `app/Config/Autoload.php` enables `php spark migrate --all` to automatically discover and execute migrations located within `app/Modules/[ModuleName]/Database/Migrations/`.*

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
    $routes->get('record', 'DomainController::index', ['as' => 'domain.record.index']);
    $routes->get('record/new', 'DomainController::new', ['as' => 'domain.record.new']);
    $routes->post('record', 'DomainController::create', ['as' => 'domain.record.create', 'filter' => 'throttle:5,60']);
});
```

#### Helper Loading

Globally loading module-specific helper files via `app/Config/Autoload.php` is strictly **FORBIDDEN**. Module-level helper files **MUST** follow the `[helper_name]_helper.php` naming convention and **MUST** be dynamically declared and loaded within the destination controller's `$helpers` array property:

```php
protected $helpers = ['App\Modules\[ModuleName]\Helpers\domain', 'form'];
```

---

## Part 3: Layer Responsibilities

### 3.1 Controllers (`app/Modules/[ModuleName]/Controllers/`)

Controllers act exclusively as lightweight HTTP request handlers and orchestrators. They **MUST NOT** execute database queries or perform business operations.

* **MUST** extend `CodeIgniter\Controller` or `App\Controllers\BaseController`.
* **MUST** declare return types as `string|ResponseInterface` (or specific `ResponseInterface` subtypes such as `DownloadResponse|ResponseInterface`) inside method signatures to satisfy strict typing rules under all outcome branches.
* **MUST** validate incoming HTTP requests using the Validation service. Validated inputs **MUST** be retrieved exclusively using `$this->validator->getValidated()` or explicitly cast to appropriate scalar types. Direct retrieval via `$this->request->getPost()` or `$this->request->getGet()` without prior validation is strictly **FORBIDDEN**.
* **MUST** invoke Service classes via the Service Container (`service('domainService')`) to process business operations. Direct model or database instantiation inside controllers is strictly **FORBIDDEN**.
* **MUST** use `__construct()` solely for service dependency resolution via `service()`. Access to `$this->request`, `$this->response`, or session state inside `__construct()` is strictly **FORBIDDEN** (since CodeIgniter 4 injects them post-instantiation via `initController()`). Any request-dependent state initialization **MUST** occur inside `initController()` or individual action methods.
* **MUST** return `ResponseInterface` objects (via `$this->response` or `redirect()`) or string values (via `view()`).
* **MUST** pass pre-formatted data (no processing logic exceeding 5 lines inside views) and complete SEO/structured metadata arrays in the view data array:

```php
return view('App\Modules\[ModuleName]\Views\record\index', [
    'pageTitle'       => 'Domain Records',
    'metaDescription' => 'Access and review domain records.',
    'metaKeywords'    => 'domain, record, entries',
    'canonicalUrl'    => url_to('domain.record.index'),
    'robotsTag'       => 'index, follow', // 'index, follow' for public pages; 'noindex, follow' for private/auth views
    'metaImage'       => base_url('assets/images/og-image.jpg'),
    'schemaJson'      => $jsonLdData,     // Targeted JSON-LD Schema associative array or null
    'data'            => $serviceResult,  // Pre-formatted presentation payload
]);
```

#### Forbidden in Controllers

* **MUST NOT** execute database queries or Query Builder calls directly.
* **MUST NOT** instantiate Models, Services, or Database connections directly using the `new` keyword inside constructors or methods.
* **MUST NOT** perform business calculations or algorithms.
* **MUST NOT** access `$this->request`, `$this->response`, or session inside `__construct()`:

```php
// FORBIDDEN: Accessing request/response before initController() triggers fatal error
public function __construct()
{
    $this->domainService = service('domainService');
    $this->userId        = $this->request->getGet('user_id'); // FATAL ERROR
}
```

* **MUST NOT** instantiate filesystem drivers directly (use Services).
* **MUST NOT** generate inline HTML markup.

---

### 3.2 Services (`app/Modules/[ModuleName]/Libraries/`)

Services contain 100% of the application's business rules, external integrations, persistence workflows, and filesystem operations.

* **MUST** be instantiated via the Service Container: `service('domainService')`.
* **MUST** be registered in the module-level `Config/Services.php` file. Shared services consumed across multiple modules **MUST** be registered in the global `app/Config/Services.php` file.
* **MUST** contain all business logic, external API integrations, filesystem operations, and transaction initializations.
* **MUST** use dependency injection through the constructor for required models, repositories, and sub-services.
* **MUST** explicitly declare all properties at the top of the class definition using native types and visibility modifiers (`private` or `protected`). Dynamic or undeclared properties are strictly **FORBIDDEN**.
* **MUST** obtain database connections via the encapsulated Model's `$this->domainModel->db` property or constructor injection. Instantiating models or calling `\Config\Database::connect()` directly inside business methods or action loops is strictly **FORBIDDEN**.
* **MUST** encapsulate database transaction lifecycles for all multi-step mutation procedures.
* **MUST** return standardized data structures (`Entity`, `array<int, Entity>`, or typed scalar payloads).
* **MUST NOT** access HTTP superglobals (`$_POST`, `$_GET`, `$_FILES`, `$_SERVER`) directly.
* **MUST NOT** manage HTTP redirects or session flash data directly.

#### Service Dependency Topology (Parallel vs. Braided)

Dependencies **MUST** flow strictly vertically without crossing boundaries horizontally:

* **Vertical Hierarchy:** `Controller -> Main Service -> Model` or `Controller -> Orchestrator Service -> Sub-Service -> Model`.
* **The Triangular "Ping-Pong" Prohibition:** Triangular dependencies are strictly **FORBIDDEN**. A Controller **MUST NOT** interact simultaneously with a Main Service AND that Main Service's internal Sub-Service.
* **Facade Pattern Mandate:** The Main Service **MUST** wrap required methods of internal dependencies to present a single unified interface to the Controller.
* **Brother-Service Isolation:** Services at the same tier (e.g., `ModuleAService` and `ModuleBService`) **MUST NOT** call each other directly. If cross-domain coordination is required, an `OrchestratorService` **MUST** be introduced at a higher layer.

---

### 3.3 Models (`app/Modules/[ModuleName]/Models/`)

Models are the persistence and database retrieval layer.

* **MUST** extend `CodeIgniter\Model`.
* **MUST** define the `$table` property specifying the database table name.
* **MUST** define the `$primaryKey` property.
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

Entities are type-safe data transfer and business domain objects representing database records with property casts and domain accessors.

* **MUST** extend `CodeIgniter\Entity\Entity`.
* **MUST** be used for all business data representation across layer boundaries. Passing raw associative arrays across layer boundaries to represent persistent data is strictly **FORBIDDEN**.
* **MUST** utilize the Entity `$casts` property to enforce native type safety:

```php
protected $casts = [
    'id'        => 'integer',
    'domain_id' => 'integer',
    'value'     => 'float',
    'is_active' => 'boolean',
    'metadata'  => '?json-array',
];
```

* **MUST** utilize the `$dates` array for timestamp properties. Properties in `$dates` are returned as `\CodeIgniter\I18n\Time|null`, NOT raw strings.
* **MUST** explicitly declare all `$dates` timestamp properties in the class PHPDoc block as `@property \CodeIgniter\I18n\Time|null $[propertyName]` to ensure full static analysis compatibility.
* **MUST** house data-shaping logic via accessors and mutators (e.g., `setPassword(string $pass)`).
* **MUST NOT** query the database.
* **MUST NOT** contain service-level operations or business workflows.
* **MUST NOT** depend on external services.

---

### 3.5 Views (`app/Modules/[ModuleName]/Views/`)

Views render Presentation Layer output using clean templates, Bootstrap 5.3 components, and strict HTML escaping.

* **MUST** extend the master layout file using: `<?= $this->extend('layouts/default') ?>`.
* **MUST** declare a top-level `@var` PHPDoc block at the beginning of the file defining all expected variables passed via the controller's `view()` data array (e.g., `/** @var array<int, DomainEntity> $data */`).
* **MUST** wrap structural content in: `<?= $this->section('content') ?> ... <?= $this->endSection() ?>`.
* **MUST** inject page-specific micro-styles via: `<?= $this->section('styles') ?> ... <?= $this->endSection() ?>`.
* **MUST** inject page-specific scripts via: `<?= $this->section('scripts') ?> ... <?= $this->endSection() ?>`.
* **MUST NOT** contain nested conditional statements (maximum nesting depth of 1 inside PHP templates).
* **MUST NOT** contain raw PHP code blocks exceeding 5 lines.
* **MUST** escape dynamic data rendered inside HTML elements or attributes using `esc($variable, 'html')` for body content, `esc($variable, 'attr')` for HTML attributes, or `esc($variable, 'js')` for JavaScript payloads.
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

* **Engine Mandate:** All tables **MUST** explicitly specify `ENGINE=InnoDB`.
* **Encoding & Collation:** All tables **MUST** use `utf8mb4` character set and `utf8mb4_unicode_ci` collation. `latin1` or standard 3-byte `utf8` are strictly **FORBIDDEN**.
* **Row Format:** Tables **MUST** use `ROW_FORMAT=DYNAMIC` to optimize storage for variable-length and off-page columns.

### 4.2 Primary Key & Identity Architecture

* **System-Generated Primary Keys:** Every table **MUST** declare a surrogate, system-generated primary key named `id`. Using mutable business data (such as emails, slugs, or tax IDs) as primary keys is strictly **FORBIDDEN**.
* **Key Sizing:**
  * For relational operational tables: Primary keys **MUST** be defined as unsigned integers (`INT UNSIGNED` up to 4.29 billion, or `BIGINT UNSIGNED` for hyper-scale event tables).
  * For distributed architectures: Primary keys **MUST** utilize binary-compact UUIDv7 or ULID (`BINARY(16)`). Storing UUIDs as raw 36-character text strings is strictly **FORBIDDEN**.
* **Immutable Foreign Keys:** Foreign keys **MUST** reference the parent primary key directly and **MUST** match the exact data type, length, and unsigned attribute of the referenced column.

### 4.3 Strict Column Typing & Right-Sized Storage

Columns **MUST** be defined using the narrowest data type capable of reliably accommodating the data domain:

* **Numbers & Counters:**
  * Quantities, counts, and IDs **MUST** use appropriate integer sizes: `TINYINT` (0–255), `SMALLINT` (0–65,535), `INT` (0–4.29B), or `BIGINT`.
  * All IDs and counts **MUST** be marked `UNSIGNED` unless negative values are mathematically valid.
* **Currencies & Precise Decimals:**
  * Currency values, financial amounts, and scientific metrics **MUST** use `DECIMAL(precision, scale)` (e.g., `DECIMAL(12,2)`).
  * Floating-point types (`FLOAT`, `DOUBLE`) are strictly **FORBIDDEN** for monetary numbers due to precision drift.
* **Boolean / Flags:**
  * State flags **MUST** be defined as `TINYINT(1)` with a strict default (`0` or `1`). Storing `"Y"`, `"N"`, `"true"`, or `"false"` as strings is strictly **FORBIDDEN**.
* **Dates & Timestamps:**
  * All operational temporal fields **MUST** use `DATETIME` or `TIMESTAMP`.
  * Storing dates or times as UNIX integers (`INT`) or raw text strings (`VARCHAR`) is strictly **FORBIDDEN**.
* **Strings & Text:**
  * Predictable text lengths (hashes, country codes, postal codes) **MUST** use fixed `CHAR(length)`.
  * Variable-length strings **MUST** declare realistic maximum boundaries via `VARCHAR(N)` (e.g., `VARCHAR(255)`). Unbounded `TEXT` or `LONGTEXT` for short strings is strictly **FORBIDDEN**.
  * Use `TEXT` or `JSON` **ONLY** for arbitrary payloads or document metadata.

### 4.4 Nullability, Constraints & Data Integrity

Data integrity **MUST** be enforced at the database engine layer:

* **Explicit Nullability (`NOT NULL` by Default):**
  * All columns **MUST** be declared `NOT NULL` unless a `NULL` state represents genuine, unrecorded business reality.
  * Columns with default values **MUST** be marked `NOT NULL DEFAULT [value]`.
* **Unique Constraints:** Any business attribute that must be unique within a system (e.g., identity hashes, external reference codes) **MUST** have an explicit database-level `UNIQUE` constraint declared in migrations.
* **Foreign Key Constraints:**
  * Relational links **MUST** define explicit integrity actions on delete and update (e.g., `ON DELETE RESTRICT` or `ON DELETE CASCADE`).
  * Dangling orphan records without enforced relationships are strictly **FORBIDDEN**.

### 4.5 Indexing Architecture & Retrieval Paths

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
* **Boolean Flags:** Column names **MUST** start with a state verb prefix (`is_active`, `has_access`, `must_notify`).
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

* **No Table Scans:** `SELECT *` is strictly **FORBIDDEN**. All data retrieval queries **MUST** explicitly specify column names:

```php
$this->select('id, domain_id, value, is_active, created_at');
```

* **Raw Queries Restricted:** Raw SQL execution via `$db->query()` is **FORBIDDEN** except for complex aggregate reporting queries reviewed and approved by architectural review.

### 4.9 Transaction Integrity

Any execution chain containing more than one database write operation (`INSERT`, `UPDATE`, `DELETE`) **MUST** be wrapped in an exception-driven transaction with explicit rollback guarantees:

```php
$db = $this->domainModel->db;

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

### 4.10 Migrations & Seeders Protocol

* **Migrations MANDATORY:** All database schema definitions and modifications **MUST** execute strictly via Migration classes. Direct table alterations via CLI tools, GUI clients, or ad-hoc SQL on any environment are strictly **FORBIDDEN**.
* **Incremental Development Lifecycle:**
  * During active feature development cycles, migrations **MUST** be implemented incrementally using non-breaking, forward-compatible schema definitions (`addColumn`, `addKey`).
  * If a schema defect, column omission, or architectural oversight is identified during active development, developers **MUST** revise the original table creation migration directly rather than stacking temporary patch migrations. The database **MUST** then be cleanly rebuilt using `php spark migrate:refresh --all`.
  * In deployed production environments where previous migration batches have already committed, schema updates **MUST** use new, non-breaking forward migrations.
* **On-Demand Unification & Squashing Protocol:**
  * Migration unification **MUST** be executed on-demand upon explicit architectural request.
  * Unification **MUST** involve merging all incremental alter migrations into their authoritative origin table creation migration, defining all final columns, data types, constraints, and composite indexes directly inside the initial `createTable()` call.
  * Once unified into the base table definition, all intermediate staged patch migrations (e.g., historical `addColumn`, `addKey` alters, and hotfix scripts) **MUST** be permanently purged from the codebase.
* **Redundancy & Safe Deletion of Staged Migrations:**
  * Staged alter migrations (e.g., incremental column additions, index alterations, or temporary schema patches) are **100% REDUNDANT** once their definitions are unified into the base table creation migrations.
  * In local, testing, and pre-production environments: Redundant staged migrations **MUST** be deleted immediately after unification, followed by a clean verification rebuild (`php spark migrate:refresh --all && php spark db:seed MainSeeder`).
  * In production environments with an existing migration registry (`migrations` table): Staged migration files **MUST NOT** be deleted without either executing a corresponding rollback down to the pre-staged batch or reconciling the migration registry table to match the unified baseline.
* **Strict DDL/DML Boundary:**
  * Migration classes **MUST** contain Data Definition Language (DDL) operations only (`createTable`, `dropTable`, `addColumn`, `dropColumn`, `addKey`).
  * Executing Data Manipulation Language (DML) queries (`INSERT`, `UPDATE`, `DELETE`) inside migration files—such as assigning records, updating states, or populating test rows—is strictly **FORBIDDEN**. All data population **MUST** reside exclusively in Seeder classes.
* **Rollback Completeness:** Every migration `down()` method **MUST** completely revert all modifications performed in `up()`. Multi-table drops **MUST** be ordered in strict reverse-dependency sequence and **MUST** disable foreign key checks prior to execution:

  ```php
  $this->db->disableForeignKeyChecks();
  $this->forge->dropTable('dependent_table', true);
  $this->forge->dropTable('parent_table', true);
  $this->db->enableForeignKeyChecks();
  ```

* **Seeder Protocol:**
  * Initial, baseline, and testing datasets **MUST** be provisioned exclusively through Seeder classes invoked via `php spark db:seed MainSeeder`.
  * Seeders **MUST** define data profiles independently and declaratively. Synthesizing records via iteration loops (`for`, `while`), modulo indexing (`%`), or pseudo-random generators (`rand()`) is strictly **FORBIDDEN**.
  * Seeders **MUST** guarantee idempotency and **MUST** populate all foreign keys and runtime-critical fields to ensure zero-error platform boot.
* **Database Provisioning Sequence:**
  1. `php spark migrate --all`
  2. If conflicts or unified squashes occur: `php spark migrate:refresh --all`
  3. `php spark db:seed MainSeeder`
  4. `php spark migrate:status`
  
---

## Part 5: Code Quality, Typing & Documentation

* **PSR-12 Compliance:** Every PHP file **MUST** be PSR-12 compliant.
* **Strict Typing:** The declaration `declare(strict_types=1);` **MUST** be the first statement in every PHP file:

```php
<?php

declare(strict_types=1);
```

* **Namespace Declaration:** Follows immediately after `declare()`, separated by exactly one blank line.
* **PHPDoc Standards:**
  * Every class **MUST** contain a PHPDoc block with `@package`, `@author`, and `@since` tags.
  * Every method **MUST** contain complete PHPDoc with `@param`, `@return`, and `@throws` where applicable.
  * Every property **MUST** specify type and description via PHPDoc in addition to native PHP typed declarations.
* **Naming Conventions:**
  * **Class Names / Controllers:** `PascalCase` (e.g., `DomainController`, `DomainService`, `DomainModel`).
  * **Method Names:** `camelCase` (e.g., `createRecord()`, `findActiveRecords()`).
  * **Class Properties & Local Variables:** `camelCase` (e.g., `$domainService`, `$domainModel`, `$savedEntity`).
  * **Database Columns & Array Keys:** `snake_case` (e.g., `domain_id`, `is_active`, `created_at`).
  * **Private Helpers:** **MUST** be prefixed with underscore (e.g., `private function _formatPayload()`).
  * Private helpers **MUST** be grouped beneath `// --- Helper Methods ---` and defined **BEFORE** public methods that invoke them.
* **Type Safety & Boundary Contracts:**
  * All methods **MUST** have explicit native return types (`: bool`, `: array`, `: RedirectResponse`, `: string|ResponseInterface`).
  * **Generics:** Any method returning an array **MUST** explicitly declare the internal element type using PHPStan generic notation:

```php
/**
 * @return array<int, DomainEntity>
 */
public function getAllRecords(): array;
```

---

## Part 6: Request, Response & Protocol

### 6.1 Routing Configuration

* **Auto-Routing DISABLED:** Set `public bool $autoRoute = false;` in `app/Config/Routing.php`.
* **No Route Closures:** Logic inside route closures (`$routes->get('/', function(){...})`) is strictly **FORBIDDEN**.
* **Named Routes MANDATORY:** Every defined route **MUST** have an explicit name using the `as` option:

```php
$routes->get('record', 'DomainController::index', ['as' => 'domain.record.index']);
$routes->post('record', 'DomainController::create', ['as' => 'domain.record.create']);
```

* **URL Generation:** All links, form actions, and redirects **MUST** use named route resolution via `url_to()`. Hardcoded URI strings are strictly **FORBIDDEN**.

### 6.2 Form Submission (Post/Redirect/Get Pattern)

All state mutations (`POST`, `PUT`, `PATCH`, `DELETE`) **MUST** implement the PRG pattern:

1. The POST controller **MUST NOT** return `view()` directly.
2. The controller validates input, retrieves validated fields via `$this->validator->getValidated()`, constructs an Entity/payload, invokes the Service, sets session flash data, and returns a `redirect()`.
3. The redirect target **MUST** be a named GET route.
4. The GET destination controller renders the view and displays flash messages.

```php
public function create(): string|ResponseInterface
{
    $rules = [
        'domain_id' => 'required|is_natural_no_zero',
        'value'     => 'required|decimal',
    ];

    if (! $this->validate($rules)) {
        return redirect()
            ->back()
            ->withInput()
            ->with('errors', $this->validator->getErrors());
    }

    /** @var array{domain_id: string, value: string} $valid */
    $valid = $this->validator->getValidated();

    try {
        $entity = new DomainEntity([
            'domain_id' => (int) $valid['domain_id'],
            'value'     => (float) $valid['value'],
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
    'result'     => $data,               // Data payload
    'errors'     => $validationErrors,   // Validation errors map or empty array
    'csrf_token' => csrf_hash(),         // Fresh CSRF token
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
* HTML forms **MUST** be created using `form_open()` (which automatically injects the synchronized CSRF hidden field). Manual duplication via `csrf_field()` alongside `form_open()` is strictly **FORBIDDEN**.
* Every JSON response (success, validation error, or exception) **MUST** include `csrf_token` with a fresh token using `csrf_hash()`.
* Layout templates **MUST** render CSRF `<meta>` tags for asynchronous client-side consumption.

### 7.2 Input Validation

* Controllers **MUST** validate **ALL** incoming input using the Validation service before passing data to domain services.
* Validation rules **MUST** be strictly typed.
* Validated data **MUST** be retrieved using `$this->validator->getValidated()` to ensure unvalidated fields are discarded.

### 7.3 Environment Secrets

* API keys, secrets, and credentials **MUST** reside **ONLY** in `.env`.
* `.env` file **MUST** be excluded from version control via `.gitignore`.
* Access secrets via `env('KEY_NAME')` or `service('settings')` **ONLY**. Hardcoded credentials in application configuration files are strictly **FORBIDDEN**.

### 7.4 Rate Limiting & Throttling

Routes **MUST** be protected against brute-force attacks and resource abuse using `App\Filters\ThrottleFilter` mapped to the `throttle` filter alias in `app/Config/Filters.php`.

#### Throttle Filter Implementation (`app/Filters/ThrottleFilter.php`)

```php
<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use InvalidArgumentException;

/**
 * Throttle Filter
 *
 * Implements rate limiting for routes using the Token Bucket algorithm.
 * Usage: 'filter' => 'throttle:5,60' (5 requests per 60 seconds)
 *
 * @package App\Filters
 * @author  Senior Developer
 * @since   1.0.0
 */
class ThrottleFilter implements FilterInterface
{
    /**
     * Pre-controller execution hook.
     *
     * @param RequestInterface   $request   Incoming HTTP request
     * @param array<int, string>|null $arguments [limit, seconds]
     *
     * @return RequestInterface|ResponseInterface|string|null
     *
     * @throws InvalidArgumentException
     */
    public function before(RequestInterface $request, $arguments = null): RequestInterface|ResponseInterface|string|null
    {
        if (! $request instanceof IncomingRequest) {
            return null;
        }

        if ($arguments === null || count($arguments) < 2) {
            throw new InvalidArgumentException('ThrottleFilter requires limit and seconds arguments: throttle:[limit],[seconds]');
        }

        $throttler = service('throttler');

        $limit   = (int) $arguments[0];
        $seconds = (int) $arguments[1];

        $ip  = $request->getIPAddress();
        $uri = $request->getRoutePath();
        $key = 'throttle_' . md5($ip . $uri);

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

            return redirect()->back()->with('error', 'Too many requests. Please wait a moment before trying again.');
        }

        return null;
    }

    /**
     * Post-controller execution hook.
     *
     * @param RequestInterface   $request   Incoming HTTP request
     * @param ResponseInterface  $response  Outgoing HTTP response
     * @param array<int, string>|null $arguments Filter arguments
     *
     * @return ResponseInterface|null
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ?ResponseInterface
    {
        return null;
    }
}
```

#### Route Application

Throttling parameters **MUST** be explicitly applied to authentication actions, payment endpoints, and high-resource operations:

```php
$routes->post('login/authenticate', 'AuthController::authenticate', [
    'as'     => 'login.authenticate',
    'filter' => 'throttle:5,60', // 5 requests per 60 seconds
]);
```

### 7.5 XSS Prevention

* **ALL** view output **MUST** be escaped using `esc($var, 'html')` for standard body content, `esc($var, 'attr')` for HTML attributes, or `esc($var, 'js')` for JavaScript literals.
* Direct unescaped output is strictly **FORBIDDEN**.

---

## Part 8: Native File Handling, Storage & Streaming Architecture

All file interactions—including client uploads, storage staging, downstream object store offloading, and streaming downloads—**MUST** strictly leverage CodeIgniter 4's native `UploadedFile`, `Validation`, and `DownloadResponse` components. Accessing low-level PHP file globals (`$_FILES`), emitting manual file headers via `header()`, or reading entire files directly into memory via `file_get_contents()` or `readfile()` is strictly **FORBIDDEN**.

---

### 8.1 Directory Topologies & Storage Isolation Boundaries

File storage targets **MUST** be explicitly categorized by accessibility and privacy requirements. Mixing public web assets with sensitive, protected records inside the same directory is strictly **FORBIDDEN**.

| Storage Constant | Target Filesystem Path | Operational Context & Access Rule |
| :--- | :--- | :--- |
| `WRITEPATH` | `ROOTPATH . 'writable/'` | **MANDATORY** for all private, authenticated, or sensitive assets (invoices, identity records, internal exports). Direct web server HTTP access is blocked; assets **MUST** be accessed exclusively via authorized controller streaming. |
| `FCPATH` | `ROOTPATH . 'public/'` | **RESTRICTED** strictly to non-sensitive, unauthenticated public assets (site banners, shared stylesheets, public icons). Writing private or authenticated user assets directly into `FCPATH` is strictly **FORBIDDEN**. |

#### The Stateless Lifecycle & Ephemeral Cleanup Rule (The Unlink Pattern)

To ensure linear scalability across containerized nodes, ephemeral virtual machines, and serverless runtimes:

* **No Persistent Local Storage:** Storing uploaded or generated assets permanently on local application disk is strictly **FORBIDDEN**. All persistent user media and records **MUST** be offloaded to external object storage (e.g., S3, GCS) within the active request lifecycle.
* **Immediate Local Cleanup:** Immediately following downstream cloud transmission, processing, or memory-buffered hand-off, the local temporary file **MUST** be destroyed via `@unlink($filePath)` within the same execution cycle.
* **Session Memory Restrictions:** Storing raw binary file payloads or streaming resource handles in session storage is strictly **FORBIDDEN**. Session state **MUST** store string identifiers or database primary keys only.
* **Deterministic Directory Creation:** When staging files locally, Services **MUST** verify and generate necessary directory trees using `mkdir($directory, 0755, true)` prior to file transfers.

---

### 8.2 Native Upload Processing (`UploadedFile`) & Validation Pipeline

All incoming file uploads **MUST** be handled natively through CodeIgniter 4’s `CodeIgniter\HTTP\Files\UploadedFile` class.

#### 8.2.1 Native Request Retrieval

Direct access to PHP's superglobal `$_FILES` is strictly **FORBIDDEN**. Uploads **MUST** be retrieved exclusively through the incoming request instance:

* **Single File Ingestion:** `$file = $this->request->getFile('input_name');`
* **Multiple File Collection:** `$files = $this->request->getFileMultiple('input_name');`

#### 8.2.2 Native Upload Validation Engine

Validation of incoming files **MUST** be executed in the Controller via CodeIgniter 4's native Validation service prior to any filesystem mutation. Manual validation logic written with procedural PHP functions is strictly **FORBIDDEN**.

Controllers **MUST** enforce the following native upload validation rules:

| Validation Rule | Syntax Specification | Functional Contract |
| :--- | :--- | :--- |
| `uploaded` | `uploaded[input_name]` | Fails if no file was uploaded under the given form key. |
| `max_size` | `max_size[input_name,kilobytes]` | Fails if the file size exceeds the threshold in kilobytes. |
| `mime_in` | `mime_in[input_name,mime/type1,mime/type2]` | Validates real MIME types extracted from file headers. |
| `ext_in` | `ext_in[input_name,ext1,ext2]` | Fails if the file extension does not match the whitelist. |
| `is_image` | `is_image[input_name]` | Validates that the file is an image using native PHP image parsers. |
| `max_dims` | `max_dims[input_name,width,height]` | Fails if image dimensions exceed maximum pixel limits. |

#### 8.2.3 Native File Inspection & Cryptographic Randomization

Prior to executing any file move, the `UploadedFile` instance **MUST** be verified and its storage identifier isolated:

* **Integrity Verification:** `$file->isValid()` **MUST** return `true`, and `$file->hasMoved()` **MUST** return `false`.
* **MIME Detection:** Real MIME types **MUST** be extracted via `$file->getMimeType()`, which inspects internal file bytes directly. Trusting raw client extensions or user-declared headers is strictly **FORBIDDEN**.
* **Cryptographic Renaming Mandate:** Client-supplied filenames retrieved via `$file->getClientName()` **MUST NOT** be used to persist files to disk due to path traversal and collision vulnerabilities. All files stored to disk **MUST** utilize cryptographically randomized, non-colliding names generated via `$file->getRandomName()`.
* **Atomic Moving:** Files **MUST** be committed to disk exclusively via native `$file->move($targetDirectory, $randomizedName)`.

---

### 8.3 Multiple File Ingestion Protocol

When processing collections of files (`$this->request->getFileMultiple()`):

* Controllers **MUST** iterate over the returned collection and verify each individual file using `$file->isValid()`.
* Corrupted, partially uploaded, or missing files in the collection **MUST** trigger an immediate validation rollback using `$file->getErrorString()`.
* Validated files within the loop **MUST** each be assigned independent randomized names via `$file->getRandomName()` prior to delegation to the Service layer.

---

### 8.4 Service-Driven File Persistence & Decoupled Execution

Controllers **MUST NOT** execute filesystem operations directly. The Controller retrieves the verified `UploadedFile` instance and delegates it to the Service layer.

* **Layer Isolation:** The `DomainService` is exclusively responsible for staging files into `WRITEPATH . 'uploads/'`, synchronizing assets with external object stores, logging transactional errors, and calling `@unlink($path)` upon workflow completion.
* **Payload Passing:** Controllers **MUST** pass either the validated `UploadedFile` object or its temporary absolute path directly into the `DomainService` method.

---

### 8.5 Native Response Streaming & Download Delivery (`DownloadResponse`)

Serving files directly to clients using `readfile()`, `fpassthru()`, `file_get_contents()`, or custom HTTP headers is strictly **FORBIDDEN**. All file downloads and browser previews **MUST** return an instance of `CodeIgniter\HTTP\DownloadResponse` generated via `$this->response->download()`.

#### 8.5.1 Automated Capabilities of `$this->response->download()`

Under the hood, CodeIgniter 4's native `download()` method provides foundational protections that **MUST NOT** be reimplemented manually:

1. **Automatic MIME Type Resolution:** Automatically inspects the targeted file and dynamically attaches the correct HTTP `Content-Type` header (e.g., `application/pdf`, `image/jpeg`, `application/zip`).
2. **Security & Transfer Headers:**
   * Automatically calculates and binds `Content-Length` to facilitate accurate client-side download progress bars.
   * Sets `Content-Disposition` to prevent script execution inside browser contexts.
   * Configures `Content-Transfer-Encoding: binary` to prevent binary asset corruption during transmission.
3. **Chunked Memory-Safe Streaming:** Streams files natively from disk in memory-managed buffer chunks. Reading large multi-gigabyte files completely into server RAM is eliminated, preserving engine stability.
4. **Output Buffer Cleansing:** Safely purges existing output buffers before dispatching file bytes, preventing corrupting whitespaces, debug notices, or unhandled output from rendering inside binary files.

#### 8.5.2 Chained Download Modifiers

The response object returned by `$this->response->download()` allows chaining modification methods to control client delivery behavior:

```php
// Inline Preview (forces display inside the browser tab, e.g., viewing a PDF or previewing an image)
return $this->response->download($filePath, null)->inline();

// Masked Dynamic Renaming (masks internal server storage paths with an end-user friendly identifier)
return $this->response->download($filePath, null)->setFileName('customer_statement_2026.pdf');

// Combined Inline Preview with Masked Title
return $this->response->download($filePath, null)->setFileName('document_preview.pdf')->inline();
```

#### 8.5.3 Download Access Control Protocol

All file streaming endpoints **MUST** reside behind authenticated and authorized routes. Controllers **MUST** verify client read permissions and record status prior to invoking `$this->response->download()`. If the file does not exist on disk or access is denied, controllers **MUST** return an appropriate error redirect or a 404/403 HTTP response.

---

### 8.6 Presentation Layer Integration (`Views`)

All HTML forms handling file uploads **MUST** utilize CodeIgniter 4’s native `form_open_multipart()` helper function to guarantee automated injection of the `enctype="multipart/form-data"` attribute alongside synchronized CSRF tokens.

* Writing raw HTML `<form enctype="multipart/form-data">` markup manually is strictly **FORBIDDEN**.
* Upload views **MUST** implement standard Bootstrap 5.3 file input components combined with server-side validation error bindings.

```php
<?= form_open_multipart(url_to('domain.file.upload')) ?>
    <div class="mb-3">
        <label for="domainDocument" class="form-label">Upload Document</label>
        <input class="form-control <?= session('errors.document') ? 'is-invalid' : '' ?>" type="file" id="domainDocument" name="document" required>
        <?php if (session('errors.document')) : ?>
            <div class="invalid-feedback">
                <?= esc((string) session('errors.document'), 'html') ?>
            </div>
        <?php endif ?>
    </div>
    <button type="submit" class="btn btn-primary">Submit Upload</button>
<?= form_close() ?>
```

---

### 8.7 Complete Reference Implementation

#### Step 1: Controller Orchestration (`DomainController.php`)

```php
<?php

declare(strict_types=1);

namespace App\Modules\Domain\Controllers;

use App\Controllers\BaseController;
use App\Modules\Domain\Libraries\DomainService;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * Class DomainController
 *
 * @package App\Modules\Domain\Controllers
 * @author  Senior Developer
 * @since   1.0.0
 */
class DomainController extends BaseController
{
    /** @var array<int, string> */
    protected $helpers = ['form'];

    /** @var DomainService */
    protected DomainService $domainService;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        /** @var DomainService $service */
        $service = service('domainService');
        $this->domainService = $service;
    }

    /**
     * Process single file upload via native validation and PRG pattern.
     *
     * @return string|ResponseInterface
     */
    public function upload(): string|ResponseInterface
    {
        $rules = [
            'document' => [
                'label' => 'Domain Document',
                'rules' => [
                    'uploaded[document]',
                    'max_size[document,5120]',
                    'mime_in[document,application/pdf,image/jpeg,image/png]',
                    'ext_in[document,pdf,jpg,jpeg,png]',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        /** @var UploadedFile $file */
        $file = $this->request->getFile('document');

        if (! $file->isValid()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $file->getErrorString());
        }

        try {
            $this->domainService->processUploadedDocument($file, 1029);

            return redirect()
                ->to(url_to('domain.record.index'))
                ->with('success', 'File uploaded and processed successfully.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'File processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Stream protected file to authorized client natively.
     *
     * @param int $domainId Record identifier
     *
     * @return DownloadResponse|ResponseInterface
     */
    public function download(int $domainId): DownloadResponse|ResponseInterface
    {
        try {
            $filePath = $this->domainService->getSecureDocumentPath($domainId);

            if (! is_file($filePath)) {
                return redirect()
                    ->to(url_to('domain.record.index'))
                    ->with('error', 'The requested file could not be found.');
            }

            $download = $this->response->download($filePath, null);

            if ($download === null) {
                return redirect()
                    ->to(url_to('domain.record.index'))
                    ->with('error', 'Unable to initialize file download stream.');
            }

            return $download->setFileName('domain_export_' . $domainId . '.pdf');
        } catch (Throwable $e) {
            return redirect()
                ->to(url_to('domain.record.index'))
                ->with('error', 'Unauthorized or invalid file request: ' . $e->getMessage());
        }
    }
}
```

#### Step 2: Service Layer Processing (`DomainService.php`)

```php
<?php

declare(strict_types=1);

namespace App\Modules\Domain\Libraries;

use App\Modules\Domain\Models\DomainModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;
use Throwable;

/**
 * Class DomainService
 *
 * @package App\Modules\Domain\Libraries
 * @author  Senior Developer
 * @since   1.0.0
 */
class DomainService
{
    /** @var DomainModel */
    protected DomainModel $domainModel;

    /**
     * Service Constructor.
     *
     * @param DomainModel $domainModel
     */
    public function __construct(DomainModel $domainModel)
    {
        $this->domainModel = $domainModel;
    }

    /**
     * Ingest, stage, and process an uploaded file using the Unlink Pattern.
     *
     * @param UploadedFile $file
     * @param int          $domainId
     *
     * @return string Final storage locator identifier
     *
     * @throws Throwable
     */
    public function processUploadedDocument(UploadedFile $file, int $domainId): string
    {
        $targetDirectory = WRITEPATH . 'uploads/domain/' . $domainId . '/';

        if (! is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0755, true);
        }

        $secureFilename = $file->getRandomName();
        $file->move($targetDirectory, $secureFilename);

        $stagedPath = $targetDirectory . $secureFilename;

        try {
            // Offload to external cloud storage
            $remoteLocator = 'cloud_bucket://domains/' . $domainId . '/' . $secureFilename;

            // Enforce Stateless Architecture (Unlink Pattern)
            if (! @unlink($stagedPath)) {
                throw new RuntimeException('Failed to purge ephemeral local file staging: ' . $stagedPath);
            }

            return $remoteLocator;
        } catch (Throwable $e) {
            if (is_file($stagedPath)) {
                @unlink($stagedPath);
            }

            log_message('error', 'DomainService::processUploadedDocument failure: ' . $e->getMessage(), [
                'domain_id' => $domainId,
                'file_name' => $secureFilename,
                'trace'     => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Resolve verified server file path for secure transmission.
     *
     * @param int $domainId
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function getSecureDocumentPath(int $domainId): string
    {
        $targetPath = WRITEPATH . 'uploads/domain/' . $domainId . '/statement.pdf';

        if (! is_file($targetPath)) {
            throw new RuntimeException('Secure asset does not exist on filesystem.');
        }

        return $targetPath;
    }
}
```

#### Step 3: Module Route Registration (`Routes.php`)

```php
<?php

declare(strict_types=1);

namespace App\Modules\Domain\Config;

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->group('domain', ['namespace' => 'App\Modules\Domain\Controllers'], static function (RouteCollection $routes): void {
    $routes->post('file/upload', 'DomainController::upload', [
        'as'     => 'domain.file.upload',
        'filter' => 'throttle:10,60',
    ]);
    $routes->get('file/download/(:num)', 'DomainController::download/$1', [
        'as'     => 'domain.file.download',
        'filter' => 'throttle:30,60',
    ]);
});
```

---

## Part 9: Error Handling, Logging & Environment

### 9.1 Exception Handling

* Controllers **MUST** catch `\Throwable` in action methods to prevent uncaught stack trace leaks to clients.
* Service layer methods **MUST** bubble up domain-specific exceptions with contextual data OR return standardized status arrays:

```php
['status' => 'error', 'message' => 'Action could not be completed.']
```

* Unhandled exceptions in Production mode **MUST** display the static error template from `app/Views/errors/html/production.php`.

### 9.2 Logging Standards

All logging **MUST** use `log_message($level, $message, $context)` with associative context arrays:

```php
log_message('error', 'Operation failed in DomainService::createRecord', [
    'domain_id' => $domainId,
    'user_id'   => $userId,
    'trace'     => $e->getTraceAsString(),
]);
```

* **Levels:**
  * `critical`: System unusable (DB down, disk full).
  * `error`: Runtime failures (transaction rollbacks, upload failures).
  * `info`: Key business milestones (user login, payment settled).

### 9.3 Environment Configuration

| Environment | `CI_ENVIRONMENT` | `display_errors` | Debug Toolbar |
| :--- | :--- | :--- | :--- |
| **Development** | `development` | `1` | Enabled |
| **Staging / Testing** | `testing` | `0` | Disabled |
| **Production** | `production` | `0` | Disabled |

*Committing `d()`, `dd()`, `die()`, `var_dump()`, or `print_r()` statements to version control is strictly **FORBIDDEN**.*

---

## Part 10: Frontend Blueprints & Bootstrap 5.3 Standards

### 10.1 Framework Architecture & Base Layout

* **Framework:** Bootstrap 5.3+ native architecture. Use Bootstrap utility classes, components, and layout systems. Absolutely no custom CSS, inline styles, or third-party styling frameworks.
* **Theme System:** Native Bootstrap 5.3 color modes **MUST** be used via `data-bs-theme="light"` or `data-bs-theme="dark"` on the root `<html>` element.
* **Layouts:** All views **MUST** extend `layouts/default` (`<?= $this->extend('layouts/default') ?>`), providing the base HTML skeleton, responsive container wrappers, CSRF headers, and standard flash messaging.
* **Structural Containers:** Views **MUST** leverage Bootstrap layout containers (`.container`, `.container-fluid`, `.row`, `.col-*`) and modern layout stacks (`.vstack`, `.hstack`, `.gap-*`).

### 10.2 Semantic CSS Variables & Design System

To ensure themes can be swapped seamlessly, hardcoded hex values in component styles are strictly **FORBIDDEN**. Views and custom styles **MUST** utilize Bootstrap 5.3 CSS variables:

```css
:root,
[data-bs-theme="light"] {
    --theme-bg-canvas: var(--bs-body-bg);
    --theme-card-bg: var(--bs-card-bg);
    --theme-text-primary: var(--bs-body-color);
    --theme-border-color: var(--bs-border-color);
    --theme-accent: var(--bs-primary);
}

[data-bs-theme="dark"] {
    --theme-bg-canvas: var(--bs-body-bg);
    --theme-card-bg: var(--bs-card-bg);
    --theme-text-primary: var(--bs-body-color);
    --theme-border-color: var(--bs-border-color);
    --theme-accent: var(--bs-primary);
}
```

* **Interactive Elements:** All custom focus states **MUST** leverage the Bootstrap focus ring helper (`.focus-ring`) or native outline rules:

```css
:focus-visible {
    outline: 2px solid var(--theme-accent) !important;
    outline-offset: 3px !important;
}
```

### 10.3 Form Controls & Validation States

* **Floating Labels:** Standard form inputs **MUST** use `.form-floating`:

```html
<div class="form-floating mb-3">
    <input type="text" name="domain_id" class="form-control <?= session('errors.domain_id') ? 'is-invalid' : '' ?>" id="domainId" placeholder="1029" value="<?= esc(old('domain_id', ''), 'attr') ?>" required>
    <label for="domainId">Domain ID</label>
    <?php if (session('errors.domain_id')) : ?>
        <div class="invalid-feedback">
            <?= esc((string) session('errors.domain_id'), 'html') ?>
        </div>
    <?php endif ?>
</div>
```

* **Form Declarations:** All forms **MUST** use `form_open()` to inject synchronized CSRF tokens automatically.
* **Buttons:**
  * Primary actions (Save, Submit, Confirm): `.btn.btn-primary`
  * Secondary actions (Cancel, Back): `.btn.btn-outline-secondary`
  * Destructive actions (Delete, Purge): `.btn.btn-outline-danger` or `.btn.btn-danger`
  * Stacks: Use `.hstack.gap-2` for horizontal button grouping.

### 10.4 SEO, Social Sharing & Structured Data

* **View Metadata:** Controllers **MUST** provide, and templates **MUST** render:
  * `pageTitle`, `metaDescription`, `metaKeywords`, `canonicalUrl`, `robotsTag`, `metaImage`, `schemaJson`
* **Robots Policy:**
  * Public endpoints: `index, follow`
  * Authenticated/Admin endpoints: `noindex, follow`
* **JSON-LD Schema Standard:**
  * Schema data **MUST** be passed as a PHP associative array (`$schemaJson`) or `null`.
  * Schemas **MUST** be serialized exclusively via `json_encode($schemaJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)`.
  * Manual string concatenation of JSON or using `esc($val, 'js')` inside `<script type="application/ld+json">` is strictly **FORBIDDEN**.

---

## Part 11: Bootstrap 5.3 Pagination Template

All pagination views **MUST** implement this standard template:

```php
<?php
use CodeIgniter\Pager\PagerRenderer;

/**
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>
<nav aria-label="<?= lang('Pager.pageNavigation') ?>">
    <ul class="pagination pagination-sm justify-content-center flex-wrap mb-0">
        <!-- First & Previous Links -->
        <?php if ($pager->hasPreviousPage()) : ?>
            <li class="page-item">
                <a href="<?= $pager->getFirst() ?>" class="page-link" aria-label="<?= lang('Pager.first') ?>">
                    <span aria-hidden="true">&laquo;&laquo;</span>
                </a>
            </li>
            <li class="page-item">
                <a href="<?= $pager->getPreviousPage() ?>" class="page-link" aria-label="<?= lang('Pager.previous') ?>">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link" aria-hidden="true">&laquo;&laquo;</span>
            </li>
            <li class="page-item disabled">
                <span class="page-link" aria-hidden="true">&laquo;</span>
            </li>
        <?php endif ?>

        <!-- Numbered Page Links -->
        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>" <?= $link['active'] ? 'aria-current="page"' : '' ?>>
                <a href="<?= $link['uri'] ?>" class="page-link">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <!-- Next & Last Links -->
        <?php if ($pager->hasNextPage()) : ?>
            <li class="page-item">
                <a href="<?= $pager->getNextPage() ?>" class="page-link" aria-label="<?= lang('Pager.next') ?>">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
            <li class="page-item">
                <a href="<?= $pager->getLast() ?>" class="page-link" aria-label="<?= lang('Pager.last') ?>">
                    <span aria-hidden="true">&raquo;&raquo;</span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link" aria-hidden="true">&raquo;</span>
            </li>
            <li class="page-item disabled">
                <span class="page-link" aria-hidden="true">&raquo;&raquo;</span>
            </li>
        <?php endif ?>
    </ul>
</nav>
```

---

## Part 12: Tooling, CLI Architecture & Custom Spark Commands

### 12.1 Spark Commands Architecture

* Custom CLI commands **MUST** extend `CodeIgniter\CLI\BaseCommand` and reside in `app/Commands/` or `app/Modules/[ModuleName]/Commands/`.
* Commands **MUST** define `$group`, `$name`, `$description`, and `$usage`:

```php
<?php

declare(strict_types=1);

namespace App\Commands;

use App\Modules\Domain\Libraries\DomainService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Class DomainSyncCommand
 *
 * @package App\Commands
 * @author  Senior Developer
 * @since   1.0.0
 */
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
    protected $options = [
        '--dry-run' => 'Simulates synchronization without database modification.',
    ];

    /**
     * Execute the command.
     *
     * @param array<string, mixed> $params
     *
     * @return int
     */
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

### 12.2 Interactive Prompts & Signal Trapping

* **CLI Prompts:** User inputs **MUST** use `CLI::prompt()` or `CLI::promptByKey()` with strict validation callbacks.
* **CLI Signals (`SIGINT`, `SIGTERM`):** Long-running daemon workers **MUST** handle POSIX signals gracefully via `CLI::trap()` to perform rollback and resource cleanup prior to process termination.

### 12.3 Asset Publishing Architecture (`Publisher`)

* Static frontend vendor assets **MUST** be deployed to `public/` using `CodeIgniter\Publisher\Publisher`. Manual copy-pasting of vendor assets into `public/` is strictly **FORBIDDEN**.

---

## Part 13: Advanced Architecture — View Cells, Events, CSP & Worker Mode

### 13.1 Modular View Cells & Decorators

* Reusable UI widgets that encapsulate distinct data fetching **MUST** be implemented as View Cells (`view_cell('DomainCell::render', ['limit' => 5])`) extending `CodeIgniter\View\Cells\Cell`. Embedding Service calls inside raw templates is strictly **FORBIDDEN**.
* Global template transforms **MUST** implement `CodeIgniter\View\ViewDecoratorInterface` and be registered in `Config\View::$decorators`.

### 13.2 Event-Driven Architecture (`Events`)

* Domain lifecycle hooks **MUST** use `CodeIgniter\Events\Events::trigger('eventName', $payload)`.
* Secondary operations (email dispatching, metric tracking) **MUST NOT** block main controller loops synchronously; trigger them via events.

### 13.3 Content Security Policy (CSP) & CORS Enforcement

* Strict CSP **MUST** be enabled in `app/Config/ContentSecurityPolicy.php`. Nonces generated via `$this->response->getCSP()->getContext('script')` **MUST** be applied to inline `<script>` tags.
* CORS headers **MUST** be managed via `CodeIgniter\Filters\CorsFilter`. Wildcard (`*`) CORS headers in production environments are strictly **FORBIDDEN**.

### 13.4 Worker Mode & Persistent Execution

* When running under persistent runtimes (RoadRunner, FrankenPHP, Swoole, or `php spark worker`), services **MUST NOT** store mutable request-scoped state inside static properties.
* Request-dependent services **MUST** be reset between cycles via `Services::reset()` or configured as non-shared factory instances.

---

## Part 14: Testing Architecture & Quality Assurance Standards

### 14.1 Testing Mandate & Test Suite Foundations

* Automated testing is **REQUIRED** for every module, service, model, controller, filter, and Spark command.
* Test execution **MUST** run via PHPUnit (`php spark test` or `./vendor/bin/phpunit`).
* All unit and feature tests **MUST** extend `CodeIgniter\Test\CIUnitTestCase`.
* Subclasses overriding `setUp(): void` or `tearDown(): void` **MUST** call `parent::setUp()` and `parent::tearDown()`.
* Time-dependent assertions **MUST** use `Time::setTestNow($frozenTime)` to freeze timestamps, restoring the clock via `Time::setTestNow()` in `tearDown()`.

### 14.2 Database Testing Standards (`DatabaseTestTrait`)

* Any test interacting with the database **MUST** use `CodeIgniter\Test\DatabaseTestTrait`.
* `$migrate = true;` **MUST** be declared on the test class.
* `$namespace = 'App\Modules\[ModuleName]';` **MUST** be declared to limit migrations to the relevant context, or set to `null` to run all migrations.
* Every database test runs wrapped in an auto-rollback transaction. Tests **MUST NOT** leave dirty records in test databases.
* Assertions **MUST** use `$this->seeInDatabase()`, `$this->dontSeeInDatabase()`, and `$this->seeNumRecords()`.

### 14.3 Test Data Generation (`Fabricator`)

* Mock entities and test records **MUST** be generated using `CodeIgniter\Test\Fabricator`.
* Target models **MUST** define default dummy schema via `fake(Generator &$faker): array`.
* Raw SQL insert scripts within test files are strictly **FORBIDDEN**.

### 14.4 HTTP Feature Testing (`FeatureTestTrait`)

* HTTP endpoint tests **MUST** combine `CIUnitTestCase`, `DatabaseTestTrait`, and `CodeIgniter\Test\FeatureTestTrait`.
* Requests **MUST** be executed using `$this->get()`, `$this->post()`, `$this->put()`, or `$this->delete()`.
* Responses return an instance of `TestResponse`. Assertions **MUST** verify HTTP status (`assertOK()`, `assertRedirectTo()`), session data (`assertSessionHas()`), and DOM contents (`assertSee()`).

### 14.5 Mocking Services & External Dependencies

* External APIs, mailers, and third-party integrations **MUST** be mocked during test runs.
* Container mocks **MUST** be registered via `\Config\Services::injectMock('serviceName', $mock)`.
* Mock state **MUST** be cleared in `tearDown()` using `\Config\Services::reset()`.

---

## Part 15: Boot & Deployment Standard Operating Procedure

### 15.1 Local Development Boot (3 Steps)

Local initialization **MUST** execute strictly via these three commands:

```bash
composer install
php spark migrate --all
php spark db:seed MainSeeder
```

### 15.2 Production Deployment Sequence

The deployment pipeline **MUST** execute in this exact sequence:

1. **Environment Config:** Set `CI_ENVIRONMENT = production` and `display_errors = 0`.
2. **Dependency Installation:**

   ```bash
   composer install --no-dev --optimize-autoloader --no-interaction
   ```

3. **Framework Optimization:**

   ```bash
   php spark optimize
   ```

4. **Database Migration:**

   ```bash
   php spark migrate --all
   ```

5. **Web Server Security:**
   * Document root **MUST** point strictly to `/public`.
   * Direct access to `/app`, `/system`, `/writable`, `/vendor` is strictly **FORBIDDEN**.
   * Web server user **MUST** have exclusive write permissions to `writable/`.

---

## Part 16: Contextual Flow — Endpoint Verification & Log-Auditing Protocol

Contextual self-verification **MUST** be executed iteratively to guarantee zero runtime regressions.

### 16.1 The Endpoint Verification Standard

* All registered routes (`php spark routes`) **MUST** be validated programmatically using headless browser testing or local HTTP cURL commands (`curl -i -L <url>`).
* Assuming template rendering correctness without explicit HTTP execution is strictly **FORBIDDEN**.

### 16.2 Simultaneous Log Auditing

* During every verification cycle, active log files in `writable/logs/` **MUST** be simultaneously inspected.
* Logs **MUST** be evaluated for uncaught exceptions, database warnings, or PHP notices.

### 16.3 Iterative Repair and Log Clean-up Loop

If route failures or log traces are discovered:

1. **Analyze logs:** Isolate the stack trace and root cause.
2. **Apply fix:** Correct the code in compliance with MVC-S standards.
3. **Purge logs:** Clear the contents of `writable/logs/`.
4. **Re-trigger verification:** Re-run the cURL command or test runner against the target route.
5. **Confirm zero logs:** Confirm that HTTP status returns correctly and `writable/logs/` remains pristine.
6. **Iterate:** Repeat until all endpoints resolve with zero log entries.

---

## Appendix: MVC-S Reference Implementation

### Step 1: Autoload Configuration

`app/Config/Autoload.php`:

```php
public array $psr4 = [
    APP_NAMESPACE               => APPPATH,
    'App\Modules\[ModuleName]' => APPPATH . 'Modules/[ModuleName]',
];
```

### Step 2: Service Container Binding

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
     * @param bool $getShared
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

### Step 3: Route Definitions

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

### Step 4: Database Migration

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
     * Create table schema.
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
     * Drop table schema.
     */
    public function down(): void
    {
        $this->forge->dropTable('domain_table', true);
    }
}
```

### Step 5: Entity Model

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
 * @property array<string, mixed>|null $metadata
 * @property \CodeIgniter\I18n\Time|null $created_at
 * @property \CodeIgniter\I18n\Time|null $updated_at
 * @property \CodeIgniter\I18n\Time|null $deleted_at
 */
class DomainEntity extends Entity
{
    /**
     * Cast properties.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id'        => 'integer',
        'domain_id' => 'integer',
        'value'     => 'float',
        'is_active' => 'boolean',
        'metadata'  => '?json-array',
    ];

    /**
     * Date properties mutated into Time instances.
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

### Step 6: Model Access Layer

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
    /** @var string */
    protected $table = 'domain_table';

    /** @var string */
    protected $primaryKey = 'id';

    /** @var class-string */
    protected $returnType = DomainEntity::class;

    /** @var array<int, string> */
    protected $allowedFields = [
        'domain_id',
        'value',
        'is_active',
        'metadata',
    ];

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

### Step 7: Service Layer

`app/Modules/[ModuleName]/Libraries/DomainService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\[ModuleName]\Libraries;

use App\Modules\[ModuleName]\Entities\DomainEntity;
use App\Modules\[ModuleName]\Models\DomainModel;
use RuntimeException;
use Throwable;

/**
 * Class DomainService
 *
 * @package App\Modules\[ModuleName]\Libraries
 * @author  Senior Developer
 * @since   1.0.0
 */
class DomainService
{
    /**
     * Domain model instance.
     *
     * @var DomainModel
     */
    protected DomainModel $domainModel;

    /**
     * Constructor.
     *
     * @param DomainModel $domainModel Injected model dependency
     */
    public function __construct(DomainModel $domainModel)
    {
        $this->domainModel = $domainModel;
    }

    /**
     * Persist record within a transaction boundary.
     *
     * @param DomainEntity $entity
     *
     * @return DomainEntity
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
                throw new RuntimeException('Database record insertion failure.');
            }

            /** @var DomainEntity|null $savedEntity */
            $savedEntity = $this->domainModel->find($id);

            if ($savedEntity === null) {
                throw new RuntimeException('Failed to retrieve newly created domain entity.');
            }

            $db->transComplete();

            return $savedEntity;
        } catch (Throwable $e) {
            $db->transRollback();

            log_message('error', 'DomainService::createRecord failed: ' . $e->getMessage(), [
                'entity' => $entity->toArray(),
                'trace'  => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Retrieve all active entities.
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

### Step 8: HTTP Controller

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
 * @package App\Modules\[ModuleName]\Controllers
 * @author  Senior Developer
 * @since   1.0.0
 */
class DomainController extends BaseController
{
    /** @var array<int, string> */
    protected $helpers = ['form'];

    /** @var DomainService */
    protected DomainService $domainService;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        /** @var DomainService $service */
        $service = service('domainService');
        $this->domainService = $service;
    }

    /**
     * Display record listing.
     *
     * @return string|ResponseInterface
     */
    public function index(): string|ResponseInterface
    {
        /** @var array<int, DomainEntity> $records */
        $records = $this->domainService->getAllRecords();

        return view('App\Modules\[ModuleName]\Views\record\index', [
            'pageTitle'       => 'Domain Records',
            'metaDescription' => 'Review active domain records.',
            'metaKeywords'    => 'domain, record, index',
            'canonicalUrl'    => url_to('domain.record.index'),
            'robotsTag'       => 'noindex, follow',
            'metaImage'       => base_url('assets/images/og-default.jpg'),
            'schemaJson'      => null,
            'data'            => $records,
        ]);
    }

    /**
     * Render creation form.
     *
     * @return string|ResponseInterface
     */
    public function new(): string|ResponseInterface
    {
        return view('App\Modules\[ModuleName]\Views\record\new', [
            'pageTitle'       => 'Create Domain Record',
            'metaDescription' => 'Submit a new domain entry.',
            'metaKeywords'    => 'domain, record, new',
            'canonicalUrl'    => url_to('domain.record.new'),
            'robotsTag'       => 'noindex, follow',
            'metaImage'       => base_url('assets/images/og-default.jpg'),
            'schemaJson'      => null,
        ]);
    }

    /**
     * Process creation submission (PRG Pattern).
     *
     * @return string|ResponseInterface
     */
    public function create(): string|ResponseInterface
    {
        $rules = [
            'domain_id' => 'required|is_natural_no_zero',
            'value'     => 'required|decimal',
        ];

        if (! $this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        /** @var array{domain_id: string, value: string} $valid */
        $valid = $this->validator->getValidated();

        try {
            $entity = new DomainEntity([
                'domain_id' => (int) $valid['domain_id'],
                'value'     => (float) $valid['value'],
                'is_active' => false,
                'metadata'  => ['source' => 'web_admin'],
            ]);

            $this->domainService->createRecord($entity);

            return redirect()
                ->to(url_to('domain.record.index'))
                ->with('success', 'Record created successfully.');
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to save record: ' . $e->getMessage());
        }
    }
}
```

### Step 9: Master Default Layout

`app/Views/layouts/default.php`:

```php
<?php
/**
 * Master Default Layout Skeleton
 *
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $metaKeywords
 * @var string $robotsTag
 * @var string $canonicalUrl
 * @var string $metaImage
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
    <title><?= esc($pageTitle, 'html') ?></title>
    <meta name="description" content="<?= esc($metaDescription, 'attr') ?>">
    <meta name="keywords" content="<?= esc($metaKeywords, 'attr') ?>">
    <meta name="robots" content="<?= esc($robotsTag, 'attr') ?>">
    <link rel="canonical" href="<?= esc($canonicalUrl, 'attr') ?>">

    <!-- Open Graph Metadata -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= esc($pageTitle, 'attr') ?>">
    <meta property="og:description" content="<?= esc($metaDescription, 'attr') ?>">
    <meta property="og:url" content="<?= esc($canonicalUrl, 'attr') ?>">
    <meta property="og:image" content="<?= esc($metaImage, 'attr') ?>">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= esc($pageTitle, 'attr') ?>">
    <meta name="twitter:description" content="<?= esc($metaDescription, 'attr') ?>">
    <meta name="twitter:image" content="<?= esc($metaImage, 'attr') ?>">

    <!-- Bootstrap 5.3 Framework -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <?php if ($schemaJson !== null) : ?>
        <script type="application/ld+json">
            <?= json_encode($schemaJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) ?>
        </script>
    <?php endif ?>

    <style>
        :focus-visible {
            outline: 2px solid var(--bs-primary) !important;
            outline-offset: 3px !important;
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body class="bg-body-tertiary text-body min-vh-100 d-flex flex-column">
    <header class="navbar navbar-expand-lg bg-body border-bottom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= url_to('domain.record.index') ?>">Domain Manager</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent" aria-controls="navContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url_to('domain.record.index') ?>">Records</a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main class="container my-4 flex-grow-1">
        <?= $this->include('partials/flash_messages') ?>
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="bg-body border-top py-3 mt-auto">
        <div class="container text-center text-body-secondary small">
            &copy; <?= date('Y') ?> Modular Architecture. All rights reserved.
        </div>
    </footer>

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

### Step 10: Flash Messages Partial

`app/Views/partials/flash_messages.php`:

```php
<?php if (session()->has('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= esc((string) session('success'), 'html') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif ?>

<?php if (session()->has('error')) : ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= esc((string) session('error'), 'html') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif ?>

<?php if (session()->has('errors')) : ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach ((array) session('errors') as $error) : ?>
                <li><?= esc((string) $error, 'html') ?></li>
            <?php endforeach ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif ?>
```

### Step 11: Views

`app/Modules/[ModuleName]/Views/record/index.php`:

```php
<?php
/**
 * @var array<int, \App\Modules\[ModuleName]\Entities\DomainEntity> $data
 */
?>
<?= $this->extend('layouts/default') ?>

<?= $this->section('content') ?>
<div class="vstack gap-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0">Domain Records</h1>
        <a href="<?= url_to('domain.record.new') ?>" class="btn btn-primary">Create Record</a>
    </div>

    <div class="card shadow-sm border">
        <div class="card-body p-0">
            <?php if (empty($data)) : ?>
                <div class="p-4 text-center text-body-secondary">
                    No domain records found.
                </div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3">ID</th>
                                <th scope="col">Domain Ref</th>
                                <th scope="col">Value</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="pe-3 text-end">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $record) : ?>
                                <tr>
                                    <td class="ps-3"><?= esc((string) $record->id, 'html') ?></td>
                                    <td><?= esc((string) $record->domain_id, 'html') ?></td>
                                    <td>$<?= esc(number_format((float) $record->value, 2), 'html') ?></td>
                                    <td>
                                        <?php if ($record->is_active) : ?>
                                            <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Active</span>
                                        <?php else : ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Inactive</span>
                                        <?php endif ?>
                                    </td>
                                    <td class="pe-3 text-end text-body-secondary small">
                                        <?= esc($record->created_at !== null ? $record->created_at->toLocalizedString('yyyy-MM-dd HH:mm') : '', 'html') ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            <?php endif ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
```

`app/Modules/[ModuleName]/Views/record/new.php`:

```php
<?php
/**
 * View template for new domain record creation.
 */
?>
<?= $this->extend('layouts/default') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm border">
            <div class="card-header bg-body py-3">
                <h1 class="h4 card-title mb-0">Create Domain Record</h1>
            </div>
            <div class="card-body p-4">
                <?= form_open(url_to('domain.record.create')) ?>

                <div class="form-floating mb-3">
                    <input type="number" name="domain_id" class="form-control <?= session('errors.domain_id') ? 'is-invalid' : '' ?>" id="domainId" placeholder="1029" value="<?= esc(old('domain_id', ''), 'attr') ?>" required>
                    <label for="domainId">Domain ID</label>
                    <?php if (session('errors.domain_id')) : ?>
                        <div class="invalid-feedback">
                            <?= esc((string) session('errors.domain_id'), 'html') ?>
                        </div>
                    <?php endif ?>
                </div>

                <div class="form-floating mb-4">
                    <input type="number" name="value" step="0.01" class="form-control <?= session('errors.value') ? 'is-invalid' : '' ?>" id="recordValue" placeholder="100.00" value="<?= esc(old('value', ''), 'attr') ?>" required>
                    <label for="recordValue">Monetary Value</label>
                    <?php if (session('errors.value')) : ?>
                        <div class="invalid-feedback">
                            <?= esc((string) session('errors.value'), 'html') ?>
                        </div>
                    <?php endif ?>
                </div>

                <div class="hstack gap-2 justify-content-end">
                    <a href="<?= url_to('domain.record.index') ?>" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Record</button>
                </div>

                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
```

### Step 12: Feature Integration Test

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
 * @package Tests\Controllers
 * @author  Senior Developer
 * @since   1.0.0
 */
class DomainControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    /** @var bool */
    protected $migrate = true;

    /** @var string|null */
    protected $namespace = 'App\Modules\[ModuleName]';

    /**
     * Staging setup before test execution.
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var RouteCollection $routes */
        $routes = service('routes');
        $routes->group('domain', ['namespace' => 'App\Modules\[ModuleName]\Controllers'], static function (RouteCollection $routes): void {
            $routes->get('record', 'DomainController::index', ['as' => 'domain.record.index']);
            $routes->get('record/new', 'DomainController::new', ['as' => 'domain.record.new']);
            $routes->post('record', 'DomainController::create', ['as' => 'domain.record.create']);
        });
    }

    /**
     * Asserts record index renders with proper markup.
     */
    public function testIndexViewRendersSuccessfully(): void
    {
        $response = $this->get('/domain/record');

        $response->assertStatus(200);
        $response->assertSee('Domain Records', 'h1');
        $response->assertSee('No domain records found.');
    }

    /**
     * Asserts record submission persists and follows PRG redirect.
     */
    public function testCreateSavesToDatabaseAndRedirects(): void
    {
        $payload = [
            'domain_id' => '1029',
            'value'     => '520.45',
        ];

        $response = $this->post('/domain/record', $payload);

        $response->assertRedirectTo(url_to('domain.record.index'));
        $this->seeInDatabase('domain_table', [
            'domain_id' => 1029,
            'is_active' => 0,
        ]);
    }

    /**
     * Asserts validation failure triggers back redirect with error state.
     */
    public function testCreateFailsValidationOnMissingInput(): void
    {
        $response = $this->post('/domain/record', []);

        $response->assertRedirect();
        $response->assertSessionHas('errors');
        $this->seeNumRecords(0, 'domain_table');
    }
}
```
