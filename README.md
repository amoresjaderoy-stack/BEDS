# BEDS Dispatch System — PHP Version

This version is implemented entirely in PHP. The application uses PHP sessions for authentication, server-rendered forms for all interactions, and a MySQL/MariaDB database as its datastore. No JavaScript is required.

## Structure

```text
BEDS_Dispatch_System/
├── index.php                    # Lightweight PHP entry point and router
├── assets/css/styles.css        # Shared visual styling
├── backend/
│   ├── actions.php              # POST handlers for CRUD and dispatch actions
│   ├── auth.php                 # Session authentication, registration, password hashing
│   └── connection.php           # MySQL connection, seed data, and ID helpers
├── classes/
│   ├── helpers.php              # Escaping, lookups, statuses, and formatting
│   └── navigation.php           # Sidebar menu and role access rules
├── modals/                      # Separate PHP forms and detail templates
├── views/                       # Dashboard and feature pages
└── database/
    ├── barangay_emergency_db.sql            # Local schema + seed data
    ├── barangay_emergency_db_infinityfree.sql
    └── transactions_demo.sql                # MySQL transactions demo (midterm topic)
```

Run it with PHP’s built-in server from this directory:

```bash
php -S localhost:8000
```

Then open `http://localhost:8000`. Use **Create Account** on the login screen to register a new user, or sign in with an existing account. Passwords are stored using PHP's `password_hash()` and verified with `password_verify()`; legacy plain-text passwords are upgraded automatically on the next successful login.

## MySQL Transactions Demo (Midterm)

`database/transactions_demo.sql` is a runnable demo of **transactions, ACID, AUTOCOMMIT (ON/OFF), COMMIT, ROLLBACK, and SAVEPOINT** — based on the two videos: a banking scenario (RuchiCodes) plus a real BEDS dispatch scenario. All changes on the live BEDS tables are rolled back and the demo bank table is dropped, so it is safe to run anytime.

**How to run**

1. **phpMyAdmin:** select `barangay_emergency_db` on the left → **SQL** tab → paste/import the whole file → **Go**.
2. **Terminal:** `C:\xampp\mysql\bin\mysql.exe -u root barangay_emergency_db < database\transactions_demo.sql`
3. Run it as **one batch** (not statement-by-statement), because `SET autocommit` and transactions are per-session.

**Where the concept is covered**

| Concept | Demo section | In the PHP system |
| --- | --- | --- |
| Transaction (all-or-nothing) | Sections 4, 7 | `beds_save_db()` in `backend/connection.php` wraps every action in `beginTransaction()` … `commit()` |
| AUTOCOMMIT ON vs OFF | Sections 2, 3 | PDO default is ON; `beginTransaction()` pauses it until `commit()`/`rollBack()` |
| COMMIT | Sections 5, 9 | `$connection->commit()` after all tables are saved |
| ROLLBACK | Sections 4, 7 | automatic `rollBack()` inside `catch (Throwable)` when a save fails |
| SAVEPOINT | Sections 6, 8 | not needed by the app; can be used via `$pdo->exec("SAVEPOINT ...")` |
| Atomic multi-table action | Sections 7, 8 | Dispatch updates `dispatch_assignment`, `incidents`, `incident_status_log`, `responders`, and `resources` in one transaction |
