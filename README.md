# Multi-Database Ticket System

A robust, scalable support ticket system built with Laravel, designed to handle data segregation across multiple department databases. This project implements a **fully dynamic, professional migration subsystem** to manage schema consistency across isolated database environments.

> **Note:** This project is a **proof-of-concept (POC)** developed for an assignment. The architectural decisions—including the **OOP practices**, **Dependency Injection patterns**, and the **custom migration subsystem**—are specifically tailored to meet the assignment's requirements using Laravel's out-of-the-box capabilities.
>
> **Key Constraints & Scope:**
> *   **Database Support:** The system is designed for **MySQL/relational databases** supported natively by Laravel. Scaling to **NoSQL** or unsupported drivers would require significant architectural changes, as the current implementation relies on specific Eloquent behaviors and strict model type-hinting.
> *   **Implementation Decisions:** Certain patterns, such as specific Model type-hints and strictly typed Dependency Injection, were chosen to fulfill the assignment's criteria for demonstrating OOP within a relational context.
> *   **Focus:** The primary goal is to demonstrate a working **Multi-Database** architecture with uniform schema replication in a relational environment.

---

## 🏗️ Architecture & Migration Subsystem

The core of this application is its ability to route data to specific databases based on the department (e.g., Technical Support, Billing). To support this, a custom migration subsystem was engineered to ensure schema synchronization across all department databases without manual intervention or code duplication.

### Architectural Decisions & Principles

This subsystem was designed with **SOLID principles**, **Twelve-Factor App methodologies**, and **Fault Isolation** at its core.

| Component | Architectural Principle | Description |
| :--- | :--- | :--- |
| **1. Environment Variables (`.env`)** | **Twelve-Factor App (Config), SoC** | Database credentials are strictly separated from code, ensuring security and allowing environment-specific configurations without code changes. |
| **2. Configuration (`config/database.php`)** | **Laravel Hierarchy, Extensibility** | Leverages Laravel's native config structure to define connections dynamically. This maintains framework standards while providing the necessary isolation for each department. |
| **3. Single Migration Source** | **DRY (Don't Repeat Yourself)** | A dedicated directory (`database/migrations/departments/`) contains the schema definitions (e.g., `create_tickets_table.php`, `create_admin_notes_table.php`) used across all databases, eliminating duplication and ensuring consistency. |
| **4. Custom Artisan Commands** | **Separation of Concerns (SoC)** | Control logic (`MigrateDepartments`) and (`RollbackDepartments`) are separated from schema definition. The command handles the iteration and dynamic connection switching, while the migration file purely defines the structure. |
| **5. Database Isolation** | **Fault Tolerance & Autonomy** | Each department operates on its own isolated database. Issues in one database do not cascade to others, and migration tracking (`migrations` table) is decentralized within each database. |
| **6. Default System Database** | **Separation of Concerns (Data Scope)** | A dedicated, centralized database manages application-level entities (Admin Users, Sessions) and tracks its own migrations independently from department data. |
| **7. Repository Interface** | **Liskov Substitution, Dependency Inversion** | The `TicketRepositoryInterface` defines a contract for data access, ensuring that any implementation (e.g., Eloquent for MySQL, PostgreSQL) adheres to a consistent API. **Note:** This contract is specifically typed for Eloquent Models (`Ticket`), restricting implementation to relational databases supported by Laravel's ORM. |
| **8. Eloquent Connection Factory** | **Factory Pattern, Single Responsibility** | A dedicated `EloquentConnectionFactory` abstracts the complex logic of dynamic connection switching. It injects the correct database connection into the repository at runtime based on user input, keeping the Controller clean and unaware of infrastructure details. |
| **9. Frontend Architecture** | **Utility-First CSS, Modern UI** | The frontend leverages **Tailwind CSS** via CDN for rapid, responsive, and consistent styling. It avoids heavy build steps (no Vite/Webpack required for styles) while delivering a polished, mobile-ready experience with accessibility and performance best practices. |
| **10. Database Indexing** | **Performance Optimization** | Strategic indices (e.g., `status`, `customer_email`, `ticket_id`) are implemented on frequently queried columns to ensure fast lookup times and efficient relationship handling as the dataset grows. |

---

## 🔧 Technical Implementation

### 1. System vs. Department Data Strategy
The application utilizes a dual-strategy for data management:
*   **Default Database**: Stores system-critical data such as **Admin Users** and **Sessions**. It uses standard Laravel migrations (`database/migrations/`) and maintains its own `migrations` table.
*   **Department Databases**: Store business-logic data (Tickets, Notes). These are managed via the custom migration subsystem described below.

**Note on Assignment Scope:** As per the assignment requirements, this project is pre-configured to support **5 distinct department databases** (Technical Issues, Account & Billing, Product & Service, General Inquiry, Feedback & Suggestions), ensuring full data segregation from day one.

### 2. Configuration Layer
The system dynamically maps department names to database connections.
- **`config/departments.php`**: Maps user-facing ticket types to internal connection names.
- **`config/database.php`**: Defines the connection details (host, port, credentials) for each department, pulling values from the `.env` file.

**Naming Convention:**
To enable **auto-discovery**, all department connection names in `config/database.php` **must end with `_department`** (e.g., `technical_issues_department`). This convention allows the migration subsystem to automatically identify and iterate through all relevant databases without manual registration.

### 3. The Repository-Factory Architecture
This project employs a pragmatic, Laravel-centric adaptation of the Repository Pattern to handle multi-tenancy without over-engineering:

1.  **Interface (`TicketRepositoryInterface`)**: Defines the required operations (`create`, `findById`, `getCrossDatabaseQuery`, `updateWithNote`).
2.  **Implementation (`EloquentTicketRepository`)**: Implements the interface using Eloquent. It is designed to work with *any* supported connection by accepting a `Ticket` model instance.
3.  **Factory (`EloquentConnectionFactory`)**: The "brain" of the operation. It takes a department name, looks up the connection string, and **mutates** the Repository's model to use that connection dynamically (`setDynamicConnection`).
4.  **Controller (`TicketController`)**: It simply asks the Factory for a repository: `$factory->make('Billing')`. It doesn't know *how* the connection is swapped, adhering to **Separation of Concerns**.

> **Architectural Note:** A simpler approach (e.g., direct conditional logic within controllers) could suffice for a basic implementation. However, this architecture was deliberately chosen to demonstrate **Object-Oriented Programming (OOP)** solutions that strictly adhere to **SOLID principles**. By resolving dependencies through a dedicated Factory and Repository contract, the system ensures high **extensibility** and **testability**, proving that complex dependency resolution can be handled cleanly without tightly coupling the application to specific database implementations.

### 4. Cross-Database Aggregation (DataTables)
The system leverages **server-side processing** via DataTables to display a unified view of tickets from all department databases. This is achieved through a highly optimized **UNION ALL** query generated by the repository (`getCrossDatabaseQuery`).

*   **Mechanism**: The repository iterates through all configured department connections, builds a query for each, and combines them using `unionAll`.
*   **Performance**: Pagination, sorting, and filtering are handled at the database level *before* the results are sent to the frontend, ensuring the application remains performant even with thousands of tickets spread across multiple databases.
*   **Context**: The query injects the department name as a virtual column, allowing the frontend to identify the source of each ticket.

### 5. The "Single Migration Source" Strategy
Instead of duplicating migration files for each database, all department-specific migrations are located in a dedicated directory:
`database/migrations/departments/`

Key files include:
- `2025_11_28_171632_create_tickets_table.php`
- `2025_12_01_112010_create_admin_notes_table.php`

These files are idempotent (check `!Schema::hasTable`) to prevent errors during re-runs.

### 6. Custom Migration Logic & Extensibility
This custom subsystem—comprising the commands and decentralized tracking—serves as a necessary **workaround for Laravel's default migration behavior**. By default, Laravel assumes a centralized `migrations` table in the default database connection. This centralized approach prevents the dynamic addition or removal of department databases without complex, manual intervention.

The solution overrides this by forcing migration tracking to occur within *each* specific department database, ensuring true decoupling.

**Note on Extensibility (Proof of Concept):**
Currently, the subsystem includes basic `migrate` and `rollback` commands for demonstration purposes. However, because the logic is encapsulated in a base command class (or can be refactored to use a shared trait/service), **new custom commands equivalent to other Artisan migration commands (e.g., `migrate:reset`, `migrate:fresh`, `migrate:status`) can be easily added**. This design adheres to **Separation of Concerns (SoC)** and ensures the system remains **extensible and scalable** as requirements grow.

Two custom Artisan commands facilitate the multi-database operations:

- **`php artisan migrate:departments`**:
  1. Scans `config/database.php` for all connections ending in `_department`.
  2. Iterates through each connection.
  3. Dynamically executes the standard Laravel `migrate` command with the `--database` and `--path` flags pointing to the department migrations folder.
  4. **Result**: The `migrations` table is created inside *each* target database, tracking migration history independently.

- **`php artisan migrate:rollback-departments`**:
  - Performs the inverse operation, rolling back the migrations found in the department folder on all department databases.

---

## 🚀 Usage

### Prerequisites
Ensure your `.env` file is configured with the credentials for all department databases.

> **Security Note:** For security reasons (especially in production), do **not** use the `root` user. Create a dedicated database user for each department database with only the necessary privileges (SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER) required for the application and migrations to function correctly.

```env
DB_TECHNICAL_ISSUES_DEPARTMENT_DATABASE=technical_issues
DB_ACCOUNT_BILLING_DEPARTMENT_DATABASE=account_billing
# ... and so on for other departments
```

### Running Migrations
To deploy the schema to all department databases simultaneously:

```bash
php artisan migrate:departments
```

To migrate a **specific department database** only:

```bash
php artisan migrate:departments --db=technical_issues_department
```

Use the `--force-migration` flag for production environments:

```bash
php artisan migrate:departments --force-migration
```

### Rolling Back
To drop the tables from all department databases:

```bash
php artisan migrate:rollback-departments
```

To rollback a **specific department database** only:

```bash
php artisan migrate:rollback-departments --db=technical_issues_department
```

## 🌟 Key Benefits

*   **Scalability**: Adding a new department is as simple as adding a config entry and environment variables. The commands automatically pick it up.
*   **Maintainability**: Change the schema in **one place**, and it propagates to all databases.
*   **Security**: No credentials are hardcoded.
*   **Reliability**: If one database is down, the loop catches the exception, logs the error, and proceeds to the next department, ensuring the entire deployment doesn't fail.
