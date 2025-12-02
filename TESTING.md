# Manual Testing Guide

This document outlines the manual testing procedures for the Multi-Database Migration Subsystem. Since the system interacts with multiple real MySQL databases, manual verification is recommended to ensure connection stability, fault tolerance, and schema integrity.

## 🧪 Test Cases

### 1. Normal Migration (Success Path)
**Goal**: Verify that schema propagates to all configured departments.
*   **Pre-condition**: All department databases exist in MySQL and are empty (no `tickets` table).
*   **Command**: `php artisan migrate:departments`
*   **Expected Result**:
    *   Console outputs "SUCCESS" for each connection.
    *   The `tickets` table is created in **all** department databases.
    *   The `migrations` table is created in **all** department databases.

### 2. Normal Rollback (Success Path)
**Goal**: Verify that schema is removed from all departments.
*   **Pre-condition**: Tables exist from Step 1.
*   **Command**: `php artisan migrate:rollback-departments`
*   **Expected Result**:
    *   Console outputs "SUCCESS" for each connection.
    *   The `tickets` table is dropped from **all** department databases.
    *   The `migrations` table records the rollback (batch rolled back).

### 3. Targeted Migration (Single DB)
**Goal**: Verify isolation of the `--db` flag.
*   **Pre-condition**: No tables exist in the database connection.
*   **Command**: `php artisan migrate:departments --db=technical_issues_department`
*   **Expected Result**:
    *   The `tickets` table is created **ONLY** in `technical_issues_department`.
    *   All other department databases remain empty.

### 4. Targeted Rollback (Single DB)
**Goal**: Verify isolation of rollback.
*   **Pre-condition**: Tables exist in `technical_issues_department`.
*   **Command**: `php artisan migrate:rollback-departments --db=technical_issues_department`
*   **Expected Result**:
    *   The `tickets` table is dropped **ONLY** from `technical_issues_department`.
    *   Other databases are untouched.

### 5. Invalid Database Connection
**Goal**: Verify validation logic.
*   **Command**: `php artisan migrate:departments --db=invalid_name`
*   **Expected Result**:
    *   Command fails with Exit Code 1.
    *   Error message: `ERROR: The connection 'invalid_name' is not a valid department connection.`

### 6. Connection Failure (Fault Tolerance)
**Goal**: Verify that one failure does not stop the entire process.
*   **Pre-condition**:
    *   Change the `.env` password or port for **one** department (e.g., `account_billing`) to be incorrect.
    *   Keep other credentials correct.
*   **Command**: `php artisan migrate:departments`
*   **Expected Result**:
    *   `technical_issues`: **Success**.
    *   `account_billing`: **CONNECTION ERROR** (logged in console), skipped.
    *   `product_service`: **Success** (process continues despite billing failure).

### 7. Idempotency (Re-run)
**Goal**: Verify that running migration twice doesn't break anything.
*   **Pre-condition**: Tables already exist.
*   **Command**: `php artisan migrate:departments` (Run immediately after a successful migration).
*   **Expected Result**:
    *   Laravel outputs "Nothing to migrate" for each database.
    *   No SQL errors (e.g., "Table already exists").

---

## 🔄 Configuration Change Scenarios

### 8. Adding a New Department
**Goal**: Verify dynamic scalability.
1.  **Action**: Add a new connection block (e.g., `hr_department`) to `config/database.php`.
2.  **Action**: Add corresponding credentials to `.env`.
3.  **Action**: Create the new empty database in MySQL.
4.  **Command**: `php artisan migrate:departments`
5.  **Expected Result**:
    *   The command automatically detects `hr_department`.
    *   It migrates the new database.
    *   (If others are already migrated, it says "Nothing to migrate" for them).

### 9. Removing a Department
**Goal**: Verify system stability after removal.
1.  **Action**: Remove a connection block (e.g., `hr_department`) from `config/database.php`.
2.  **Command**: `php artisan migrate:departments`
3.  **Expected Result**:
    *   The command **skips** the removed department entirely.
    *   It does **not** error out trying to find it.
    *   Other departments are processed normally.

