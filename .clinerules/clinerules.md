# Project Constitution: CodeIgniter 4 Development Standards

This document is the **single source of truth** for all architectural, coding, and security standards for this project. Its purpose is to ensure a streamlined, maintainable, and secure application. Adherence is mandatory for all contributors, human and AI.

### Guiding Principles
*   **Clarity over Cleverness:** Code must be simple, readable, and self-documenting.
*   **Security is Not Optional:** Every line of code must be written with security as a primary concern.
*   **Consistency is Key:** The framework and these rules provide one right way to build features. Follow it.
*   **Fat Services, Skinny Controllers:** Business logic belongs in services, not controllers.

---

### **Part 1: The Four Layers of the Application**

The project follows a strict **Model-View-Controller-Service (MVC-S)** architecture.

#### **1.1. Controllers (`app/Controllers/`)**
*   **DO:** Orchestrate the request-response cycle. Call Services and Models. Return a Response or a Redirect.
*   **DON'T:** Contain business logic. Access the database directly. Contain complex calculations.

#### **1.2. Models (`app/Models/`)**
*   **DO:** Handle all database interactions. Use the Query Builder and Entities.
*   **DON'T:** Contain business logic. Be called directly from a View.

#### **1.3. Services (`app/Libraries/`)**
*   **DO:** Contain all business logic (e.g., payment processing, API interactions). Be reusable. Be registered in `app/Config/Services.php`.
*   **DON'T:** Handle HTTP-specific tasks like reading `POST` data. That is the Controller's job.

#### **1.4. Views (`app/Views/`)**
*   **DO:** Display data passed from a Controller. Contain minimal presentation logic (loops, conditionals). Escape all output with `esc()`.
*   **DON'T:** Perform database queries. Contain business logic.

#### **1.5. Database (`app/Database/`)**
*   **Role:** Define and manage the database schema.
*   **Rules:**
    *   All schema changes MUST be managed via Migration files.
    *   Initial or test data MUST be handled by Seeder files.
    *   Directly altering the database schema outside of migrations is strictly FORBIDDEN.

#### **1.6. Helpers (`app/Helpers/`)**
*   **Role:** Contain simple, stateless, global procedural functions.
*   **Rules:**
    *   Helpers are for small, reusable tasks (e.g., formatting data, checking a specific condition) that are needed in multiple places (controllers, views).
    *   All new helpers MUST be registered in `app/Config/Autoload.php`.
    *   Helpers MUST NOT contain business logic, perform database queries, or interact with external services. Such logic belongs in a Service.

#### **1.7. Configuration (app/Config/)**
*   **Role:** This directory is intended for all application configuration files.
*   **Rules for Default Configurations:**
    *   Standard CodeIgniter configuration files (e.g., `app/Config/App.php`, `app/Config/Database.php`) should remain in their default locations.
    *   Sensitive or environment-specific settings MUST be managed via the `.env` file.
*   **Rules for Custom Configurations:**
    *   **Rule 1: Isolate Custom Files:** All custom configuration files MUST be placed in the `app/Config/Custom/` directory.
    *   **Rule 2: Correct Namespacing:** Custom configuration files MUST use the `Config\Custom` namespace. For example, a file named `YourConfigFileName.php` would have `namespace Config\Custom;`.
    *   **Rule 3: Accessing Custom Configs:** Custom configurations can be accessed using the `config()` helper with the fully qualified class name (e.g., `config('Config\Custom\YourConfigFileName')`) or by using a `use` statement at the top of your file (e.g., `use Config\Custom\YourConfigFileName;` followed by `new YourConfigFileName()`).
*   **Reasoning:** This separation ensures that custom configurations are distinct from framework defaults, improving organization, maintainability, and preventing conflicts during framework updates.

---

### **Part 2: The Request & Response Protocol**

This section defines the mandatory flow for user interactions.

#### **2.1. Routing: Named Routes are Law**
*   **Rule 1:** Every route in `app/Config/Routes.php` MUST be assigned a unique name (e.g., `['as' => 'users.profile']`).
*   **Rule 2:** All URLs in the application (views, redirects) MUST be generated using `url_to('route.name')`. Hardcoded URLs (`/users/profile`) are strictly **FORBIDDEN**.

#### **2.2. The 3 Steps of a Form Submission (Post/Redirect/Get)**
This **Post/Redirect/Get (PRG)** pattern is mandatory for all `POST` requests.

*   **Step 1: The `POST` Action (Controller):**
    *   The controller method processes the form data and calls the necessary services/models.
    *   The method **MUST NOT** return a `view()`.

*   **Step 2: The `Redirect` with Flash Data:**
    *   After processing, the controller method MUST store user-facing messages in the session as "flash data" (e.g., `session()->setFlashdata('success', 'Operation successful!')`).
    *   Standard keys are required: `success`, `error`, `warning`, `info`.
    *   The method MUST conclude by returning a `redirect()` response (e.g., `return redirect()->to(url_to('users.show', $id));`).

*   **Step 3: The `GET` Display:**
    *   The browser follows the redirect to a new URL.
    *   The corresponding controller method for the `GET` request renders a view.
    *   This view reads the flash data from the session and displays the message using the `flash_messages.php` partial.

#### **2.3. Filters: The Gatekeepers**
*   Filters (`app/Filters/`) MUST be used for all cross-cutting concerns, primarily for security and access control.
*   **Examples:** `AuthFilter` to protect logged-in areas, `AdminFilter` for admin-only pages, `BalanceFilter` to protect paid service routes.

#### **2.4. Global View Data: The `BaseController`**
*   **Rule:** For data required by the master layout or on every page (e.g., user consent status, notification counts), the data MUST be prepared and passed to the view system within `BaseController::initController()`. This centralizes logic and avoids repetition in individual controller methods.

---

### **Part 3: Code, Security, & Performance Mandates**

These are non-negotiable rules for all code.

#### **3.1. Code Quality & Documentation**
*   **PSR-12 & Strict Types:** All PHP files MUST be PSR-12 compliant and start with `declare(strict_types=1);`.
*   **PHPDoc Blocks:** Every class, property, and method MUST have a complete and accurate PHPDoc block. There are no exceptions. This includes `@param`, `@return`, and clear descriptions. For Entities, a full list of `@property` tags is required.

#### **3.2. Security**
*   **Output Escaping:** All dynamic data rendered in a view MUST be escaped with `esc()` to prevent XSS. Example: `<?= esc($user->name) ?>`.
*   **CSRF Protection:** CSRF protection MUST be enabled globally. All `POST` forms MUST include `csrf_field()`.
*   **Database Safety:** The Query Builder or Entities are the ONLY permitted methods for database interaction to prevent SQL injection.
*   **Input Validation:** All user-supplied data (`POST`, `GET`, etc.) MUST be validated using the Validation library before use.
*   **Throttler:** The Throttler MUST be enabled on authentication and password reset routes to prevent brute-force attacks.

#### **3.3. Transactional Integrity: All or Nothing**
*   **Rule 1:** All operations involving multiple database `write` actions (INSERT, UPDATE, DELETE) that are logically connected MUST be wrapped in a database transaction.
*   **Rule 2:** All operations involving financial data (e.g., updating a user's `balance`) MUST be wrapped in a transaction, even if it is a single database call. This ensures atomicity and future-proofs the code for potential additions like audit logging.
*   **Rule 3:** A transaction's status MUST be checked after completion. On failure, a `critical` log entry MUST be created, and a generic, safe error message MUST be shown to the user.

#### **3.4. Performance**
*   **Auto-Routing:** Auto-routing MUST be disabled (`$autoRoute = false`) in `app/Config/Routing.php`.
*   **Efficient Queries:** Use pagination (`paginate()`) for lists. Avoid `findAll()` on large tables. Select only the columns needed.
*   **Optimization Command:** The deployment script MUST run `php spark optimize`.

#### **3.5. Error Handling & Logging**
*   **Production Errors:** Detailed error reporting MUST be disabled in the production `.env` file (`CI_ENVIRONMENT = production`).
*   **Dual Logging Strategy:**
    *   **Developer Logs:** Use `log_message('level', 'message')` for system events and errors. These are for developers only.
    *   **User Notifications:** Use `session()->setFlashdata()` to communicate the outcome of actions to the user. These are rendered via the `flash_messages.php` partial.

---

### **Part 4: Frontend & UI Mandates**

*   **Bootstrap 5:** The project MUST use Bootstrap 5 as the sole CSS framework for consistency.
*   **Master Layout:** All pages MUST extend the master layout file at `app/Views/layouts/default.php`.
*   **Reusable Partials:** Common UI elements MUST be created as partial views in `app/Views/partials/`.
    *   **Flash Messages:** All status messages MUST be rendered via `app/Views/partials/flash_messages.php`.
    *   **Custom Components:** Sitewide components like pagination MUST have a custom view (e.g., `app/Views/pagers/bootstrap5_pagination.php`) and be configured as the default in `app/Config/Pager.php`.
*   **Legal and Compliance:** The Privacy Policy page (`privacy.php`) MUST contain a clear, up-to-date section detailing all first-party and third-party cookies used by the application, including their purpose.
*   **URL Generation: `route_to()` vs. `url_to()`**
    *   **For JavaScript Background Requests:** All URLs used within `<script>` blocks for background requests (e.g., AJAX, `fetch`) MUST be generated as relative paths using `route_to('route.name')`.
    *   **For HTML Full-Page Navigation:** All URLs used in standard HTML for full-page navigations (e.g., `<a>` tag `href` attributes, standard `<form>` `action` attributes, and controller redirects) MUST be generated as absolute paths using `url_to('route.name')`.
    *   **Reasoning:** This strict separation is the mandatory solution to prevent CORS policy errors. `route_to()` ensures same-origin requests for JavaScript, while `url_to()` ensures predictable, absolute paths for page loads and redirects.

---

### **Part 5: Environment & Deployment Checklist**

*   **Environment File:** All credentials and API keys MUST be in the `.env` file. The `.env` file MUST NOT be committed to version control.
*   **Production Mode:** The `CI_ENVIRONMENT` variable in `.env` MUST be set to `production`.
*   **Web Server Root:** The server's document root MUST point to the `/public` directory. **This is a critical security requirement.** The `app`, `system`, and `writable` directories must be located outside the web root.
*   **Composer for Production:** Deployments MUST run `composer install --no-dev --optimize-autoloader`.
*   **Clean Production Server:** Development directories (`tests/`) and files (`spark`, `phpunit.xml.dist`) MUST be removed from the production server.

---

### **Part 6: AI Agent Protocol**

This is the mandatory workflow for any AI agent modifying the codebase.

1.  **Acknowledge and Analyze:** State the user's request and break it down into a sequence of modifications that align with this constitution.
2.  **Declare Intent:** List all files that will be created or modified before generating any code.
3.  **Generate Full Files:** When modifying a file, provide the complete, updated file content. Partial snippets are FORBIDDEN.
4.  **Use Generators:** New boilerplate files (Controllers, Models, etc.) MUST be created using `php spark make:*` commands.
5.  **Confirm Compliance:** Conclude by confirming that all changes adhere to the rules outlined in this document.

---

### **Part 7: The Unified Frontend Workflow (The 'Blueprint' Method)**

This section enhances Part 4, providing a mandatory, step-by-step workflow for creating all user-facing views to ensure absolute consistency.

#### **7.1. The Blueprint Philosophy**
*   **Minimal:** Prioritize Bootstrap 5 utility classes over custom CSS. A new view should require little to no page-specific styling.
*   **Consistent:** All views are built from the same core components (The Container, The Card), ensuring a predictable user experience.
*   **Scalable:** The component-based approach allows for rapid, consistent development of new features.

#### **7.2. The Core Blueprint Components**
These are the foundational building blocks for every view.

*   **A. The Container:** Every page's primary content MUST be wrapped in a single `<div class="container my-5">`. This establishes consistent vertical and horizontal spacing sitewide.

*   **B. The Card (`.blueprint-card`):** All primary content, forms, and data displays MUST be placed within a "Blueprint Card." This is a standard Bootstrap card with a consistent, project-defined style.

    *   **Implementation:** `<div class="card blueprint-card">...</div>`
    *   **Mandatory Style (applied via sitewide CSS in `layouts/default.php`):**
        ```css
        .blueprint-card {
            background-color: var(--card-bg);
            border-radius: 0.75rem;
            border: 1px solid var(--border-color);
            /* Other base styles */
        }
        ```

*   **C. The Header (`.blueprint-header`):** All pages MUST have a clear header section.

    *   **Implementation:**
        ```html
        <div class="blueprint-header text-center mb-5">
            <h1 class="fw-bold">Page Title</h1>
            <p class="lead text-muted">A brief, helpful description of the page.</p>
        </div>
        ```

*   **D. The Color & Theme Palette: A Strict Hierarchy**
    *   **Principle:** All styling MUST be theme-aware. The application supports both light and dark modes, and all UI components must adapt correctly. Hardcoding colors is strictly **FORBIDDEN**.
    *   **Rule 1: Use Theme-Aware Bootstrap Utilities First.** Always prefer Bootstrap's built-in, theme-aware utility classes for backgrounds, text, and borders. These automatically adapt to the theme.
        *   **DO:** `class="bg-body-tertiary"`, `class="text-body-secondary"`, `class="border-subtle"`
        *   **DON'T:** `class="bg-light"`, `class="text-muted"` (unless the color must be fixed regardless of theme).
    *   **Rule 2: Use CSS Variables Second.** For custom components where a Bootstrap utility is not available, use the project's global CSS variables defined in `layouts/default.php`.
        *   **DO:** `background-color: var(--card-bg);`, `color: var(--text-heading);`
    *   **Rule 3: Hardcoded Colors are Prohibited.** Do not use explicit hex codes, `rgb()`, or color names in CSS for elements that should change with the theme.
        *   **FORBIDDEN:** `background-color: #ffffff;`, `color: black;`

#### **7.3. The View Creation Workflow**

1.  **Controller Preparation:** The controller MUST pass all necessary data to the view, including mandatory SEO variables: `pageTitle`, `metaDescription`, and `canonicalUrl`.

2.  **View Scaffolding:** Every new view file MUST follow the standard scaffolding structure of extending the default layout and defining content sections.

3.  **Component Implementation:**
    *   **Forms:** All text inputs MUST use the Bootstrap 5 "Floating labels" pattern (`<div class="form-floating">...</div>`).
    *   **Buttons:** Button usage MUST follow a strict hierarchy:
        *   **Primary Action:** One `btn-primary` per form/view.
        *   **Secondary Actions:** Use `btn-outline-secondary` or `btn-secondary`.
        *   **Destructive Actions:** Use `btn-danger` or `btn-outline-danger`.
    *   **Alerts/Messages:** All user feedback MUST be handled via the `partials/flash_messages.php` partial, which uses theme-aware Bootstrap alert classes.
# Todo List (Optional - Plan Mode)

While in PLAN MODE, if you've outlined concrete steps or requirements for the user, you may include a preliminary todo list using the task_progress parameter.

Reminder on how to use the task_progress parameter:


1. To create or update a todo list, include the task_progress parameter in the next tool call
2. Review each item and update its status:
   - Mark completed items with: - [x]
   - Keep incomplete items as: - [ ]
   - Add new items if you discover additional steps
3. Modify the list as needed:
		- Add any new steps you've discovered
		- Reorder if the sequence has changed
4. Ensure the list accurately reflects the current state

**Remember:** Keeping the todo list updated helps track progress and ensures nothing is missed.<environment_details>
# Visual Studio Code Visible Files
app/Views/auth/register.php

# Visual Studio Code Open Tabs
app/Config/Custom/AGI.php
app/Views/auth/login.php
app/Views/auth/register.php
app/Views/contact/contact_form.php
app/Config/Custom/Recaptcha.php
app/Libraries/EmbeddingService.php
app/Libraries/MemoryService.php
app/Libraries/RecaptchaService.php

# Current Time
05/11/2025, 2:08:40 pm (Africa/Nairobi, UTC+3:00)

# Context Window Usage
25,665 / 1,000K tokens used (3%)

# Current Mode
ACT MODE
