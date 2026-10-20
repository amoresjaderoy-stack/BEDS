-- ============================================================================
--  BEDS — MySQL Transactions Demo (Midterm Topic)
--  Concepts: Transactions · ACID · AUTOCOMMIT · COMMIT · ROLLBACK · SAVEPOINT
--
--  Base sa videos:
--    Video 1 (RuchiCodes) — START TRANSACTION, COMMIT, ROLLBACK, SAVEPOINT
--    Video 2 (Bro Code)   — AUTOCOMMIT ON/OFF, COMMIT, ROLLBACK
--
--  PAANO I-RUN (phpMyAdmin):
--    1. Open http://localhost/phpmyadmin
--    2. Click the `barangay_emergency_db` database on the left
--    3. SQL tab -> paste this WHOLE file -> Go   (pwede pod ang Import tab)
--    4. I-run as ONE batch, dili statement-by-statement, kay ang
--       SET autocommit ug transactions kay per-session (per-connection).
--
--  PAANO I-RUN (terminal):
--    C:\xampp\mysql\bin\mysql.exe -u root barangay_emergency_db < database\transactions_demo.sql
--
--  SAFE NI SIYA: tanan changes sa live BEDS tables gi-ROLLBACK ra,
--  ug ang demo bank table gi-DROP sa katapusan (Section 10).
-- ============================================================================

-- Kung lahi ang pangalan sa imong database (e.g. InfinityFree), ilisi kini:
USE barangay_emergency_db;

-- ----------------------------------------------------------------------------
-- SECTION 0 — Current session settings
-- ----------------------------------------------------------------------------
-- ACID = Atomicity, Consistency, Isolation, Durability.
-- Transactions implement niini; InnoDB ang engine nga mo-support (MyISAM wala).
SELECT @@autocommit AS autocommit_by_default;   -- 1 = ON (default)

-- ============================================================================
-- SECTION 1 — BEDS data BEFORE (baseline)
-- ============================================================================
SELECT 'BEDS STATE - BEFORE' AS phase;
SELECT COUNT(*) AS dispatch_rows FROM dispatch_assignment;                            -- expected: 4
SELECT status AS incident_3_status FROM incidents WHERE incident_id = 3;              -- Verified
SELECT availability_status AS responder_3_status FROM responders WHERE responder_id = 3; -- Available
SELECT status AS resource_5_status FROM resources WHERE resource_id = 5;              -- Maintenance
SELECT COUNT(*) AS status_log_rows FROM incident_status_log;                          -- expected: 14

-- ============================================================================
-- SECTION 2 — AUTOCOMMIT = ON (default): every statement is saved INSTANTLY
-- ============================================================================
-- Demo "bank" table. InnoDB is REQUIRED for transactions.
-- (NOTE: DDL like DROP/CREATE causes an implicit COMMIT — mao nga sa gawas
--  kini sa transaction.)
DROP TABLE IF EXISTS transactions_demo_accounts;
CREATE TABLE transactions_demo_accounts (
  account_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  owner_name VARCHAR(100) NOT NULL,
  balance DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (account_id)
) ENGINE = InnoDB;

INSERT INTO transactions_demo_accounts (owner_name, balance) VALUES
  ('Juan Dela Cruz', 10000.00),
  ('Maria Cortez',   10000.00);

SELECT @@autocommit AS autocommit_before;   -- 1 = ON

-- With AUTOCOMMIT=ON, kining UPDATE gi-save dayon (auto-COMMIT) pag-run:
UPDATE transactions_demo_accounts SET balance = balance - 5000.00 WHERE account_id = 1;

-- Sulayi ug undo...
ROLLBACK;   -- ...pero WALAY mahitabo: wala may active transaction.

-- Juan is STILL 5,000.00 — permanent na ang change. Mao ni ang danger nga
-- gi-explain sa Bro Code video (e.g. DELETE without WHERE = goodbye data).
SELECT account_id, owner_name, balance FROM transactions_demo_accounts;

-- I-restore ang demo data para sa sunod nga section:
UPDATE transactions_demo_accounts SET balance = 10000.00 WHERE account_id = 1;

-- ============================================================================
-- SECTION 3 — AUTOCOMMIT = OFF: changes stay TEMPORARY until COMMIT
-- ============================================================================
SET autocommit = 0;
SELECT @@autocommit AS autocommit_now;   -- 0 = OFF

UPDATE transactions_demo_accounts SET balance = balance - 5000.00 WHERE account_id = 1;

-- Inside this session, Juan shows 5,000.00 (temporary pa lang):
SELECT account_id, owner_name, balance FROM transactions_demo_accounts;

-- Now the undo button WORKS:
ROLLBACK;

-- Juan is back to 10,000.00 — wala na-save ang temporary change:
SELECT account_id, owner_name, balance FROM transactions_demo_accounts;

SET autocommit = 1;   -- Balik sa default/normal mode.
-- NOTE: kung mo-close ka sa connection nga wala pa ka-COMMIT, MySQL
-- automatic nga mo-ROLLBACK sa pending changes.

-- ============================================================================
-- SECTION 4 — START TRANSACTION + ROLLBACK (failed bank transfer)
-- ============================================================================
START TRANSACTION;   -- nag-pause sa autocommit until COMMIT or ROLLBACK

-- Transfer attempt: Juan -> Maria, PHP 10,000
UPDATE transactions_demo_accounts SET balance = balance - 10000.00 WHERE account_id = 1;
UPDATE transactions_demo_accounts SET balance = balance + 10000.00 WHERE account_id = 2;

-- Inside the transaction, morag successful na gayud:
SELECT account_id, owner_name, balance FROM transactions_demo_accounts;

-- Pero gi-simulate nato nga ni-fail ang transfer (validation error / crash):
ROLLBACK;

-- Balik sa original balances — walay kwarta nga nawala o ni-doble:
SELECT account_id, owner_name, balance FROM transactions_demo_accounts;

-- ============================================================================
-- SECTION 5 — COMMIT (successful bank transfer)
-- ============================================================================
START TRANSACTION;

-- Transfer attempt: Juan -> Maria, PHP 3,000
UPDATE transactions_demo_accounts SET balance = balance - 3000.00 WHERE account_id = 1;
UPDATE transactions_demo_accounts SET balance = balance + 3000.00 WHERE account_id = 2;

SELECT account_id, owner_name, balance FROM transactions_demo_accounts;   -- temporary pa

COMMIT;   -- PERMANENTLY saved — dili na ma-undo sa ROLLBACK

SELECT account_id, owner_name, balance FROM transactions_demo_accounts;   -- 7,000 / 13,000 permanent

-- ============================================================================
-- SECTION 6 — SAVEPOINT (partial rollback inside one transaction)
-- ============================================================================
START TRANSACTION;

-- Step 1: service fee — CORRECT, gusto nato i-keep.
UPDATE transactions_demo_accounts SET balance = balance - 500.00 WHERE account_id = 1;

SAVEPOINT after_fee;   -- checkpoint

-- Step 2: wrong transfer amount — gusto nato i-undo KINI ra.
UPDATE transactions_demo_accounts SET balance = balance - 2000.00 WHERE account_id = 1;

SELECT account_id, owner_name, balance FROM transactions_demo_accounts;   -- 4,500 (both steps applied)

ROLLBACK TO SAVEPOINT after_fee;   -- undo Step 2 only — Step 1 stays

SELECT account_id, owner_name, balance FROM transactions_demo_accounts;   -- 6,500 (fee only)

COMMIT;   -- i-save ang nahabilin (ang fee)

SELECT account_id, owner_name, balance FROM transactions_demo_accounts;   -- final: 6,500 / 13,000

-- ============================================================================
-- SECTION 7 — BEDS USE CASE: dispatch should be ATOMIC (all or nothing)
-- ============================================================================
-- Ang dispatch mo-touch ug 5 ka tables: dispatch_assignment, incidents,
-- incident_status_log, responders, resources. Kung mo-fail ang isa ka step,
-- kinahanglan ma-undo TANAN — dili pwede nga naay orphan nga row.
SELECT 'BEDS DISPATCH - BEFORE' AS phase;
SELECT COUNT(*) AS dispatch_rows FROM dispatch_assignment;                             -- 4
SELECT status AS incident_3_status FROM incidents WHERE incident_id = 3;               -- Verified
SELECT availability_status AS responder_3_status FROM responders WHERE responder_id = 3; -- Available
SELECT status AS resource_5_status FROM resources WHERE resource_id = 5;               -- Maintenance

START TRANSACTION;

INSERT INTO dispatch_assignment (incident_id, responder_id, resource_id, dispatch_time, arrival_time)
VALUES (3, 3, 5, NOW(), NULL);   -- incident 3 (Fire) -> responder 3 + resource 5

UPDATE incidents SET status = 'Dispatched' WHERE incident_id = 3;

INSERT INTO incident_status_log (incident_id, status, updated_by, timestamp)
VALUES (3, 'Dispatched', 1, NOW());

UPDATE responders SET availability_status = 'On Duty' WHERE responder_id = 3;
UPDATE resources SET status = 'In Use' WHERE resource_id = 5;

-- Inside the transaction, morag OK tanan:
SELECT COUNT(*) AS dispatch_rows_now FROM dispatch_assignment;                         -- 5
SELECT status AS incident_3_status_now FROM incidents WHERE incident_id = 3;           -- Dispatched

-- PROBLEM: resource 5 kay "Maintenance" man diay — dili dapat i-dispatch!
-- So i-abort ang tibuok dispatch:
ROLLBACK;

SELECT 'BEDS DISPATCH - AFTER ROLLBACK' AS phase;
SELECT COUNT(*) AS dispatch_rows FROM dispatch_assignment;                             -- balik sa 4 (no orphan row)
SELECT status AS incident_3_status FROM incidents WHERE incident_id = 3;               -- balik sa Verified
SELECT availability_status AS responder_3_status FROM responders WHERE responder_id = 3; -- balik sa Available
SELECT status AS resource_5_status FROM resources WHERE resource_id = 5;               -- balik sa Maintenance
SELECT COUNT(*) AS status_log_rows FROM incident_status_log;                           -- balik sa 14

-- TEACHING POINT: InnoDB does NOT roll back AUTO_INCREMENT counters.
-- Ang ID nga gigamit sa aborted INSERT (5) mawala forever = naay gap.

-- ============================================================================
-- SECTION 8 — BEDS USE CASE with SAVEPOINT (partial rollback)
-- ============================================================================
START TRANSACTION;

-- Correct dispatch ni: incident 3 -> responder 3 with resource 3 (Available)
INSERT INTO dispatch_assignment (incident_id, responder_id, resource_id, dispatch_time, arrival_time)
VALUES (3, 3, 3, NOW(), NULL);

SAVEPOINT after_assignment;

-- Karon i-update ang incident status ug i-log:
UPDATE incidents SET status = 'Dispatched' WHERE incident_id = 3;
INSERT INTO incident_status_log (incident_id, status, updated_by, timestamp)
VALUES (3, 'Dispatched', 1, NOW());

-- Change of plan: gusto nato i-keep ang assignment pero i-hold sa ang
-- incident status change... so undo ONLY the part after the savepoint:
ROLLBACK TO SAVEPOINT after_assignment;

SELECT COUNT(*) AS dispatch_rows_inside_txn FROM dispatch_assignment;                  -- 5 (assignment kept)
SELECT status AS incident_3_status_inside_txn FROM incidents WHERE incident_id = 3;    -- Verified (undone)

-- Decide the final outcome. Default: i-discard tanan para walay mabilin nga trace.
ROLLBACK;
-- IMPORTANT: kung gusto nimo i-COMMIT ang assignment, ILISI ang ROLLBACK sa babaw
-- og COMMIT (ayaw ibilin ang duha):
-- COMMIT;

-- ============================================================================
-- SECTION 9 (OPTIONAL) — COMMIT a real dispatch (commented out by default)
-- ============================================================================
-- Uncomment this whole block kung gusto nimo i-demo ang persisted change
-- sa system mismo. Human sa demo, i-run ang CLEANUP sa ubos.
--
-- START TRANSACTION;
-- INSERT INTO dispatch_assignment (incident_id, responder_id, resource_id, dispatch_time, arrival_time)
-- VALUES (3, 3, 3, NOW(), NULL);
-- UPDATE incidents SET status = 'Dispatched' WHERE incident_id = 3;
-- INSERT INTO incident_status_log (incident_id, status, updated_by, timestamp)
-- VALUES (3, 'Dispatched', 1, NOW());
-- UPDATE responders SET availability_status = 'On Duty' WHERE responder_id = 3;
-- UPDATE resources SET status = 'In Use' WHERE resource_id = 3;
-- COMMIT;
--
-- -- Prove nga ni-persist gyud siya:
-- SELECT status AS incident_3_status FROM incidents WHERE incident_id = 3;   -- Dispatched
--
-- -- CLEANUP (i-run pagkahuman sa demo para ma-restore ang original state):
-- DELETE FROM incident_status_log WHERE incident_id = 3 AND status = 'Dispatched';
-- DELETE FROM dispatch_assignment WHERE incident_id = 3;
-- UPDATE incidents SET status = 'Verified' WHERE incident_id = 3;
-- UPDATE responders SET availability_status = 'Available' WHERE responder_id = 3;
-- UPDATE resources SET status = 'Available' WHERE resource_id = 3;

-- ============================================================================
-- SECTION 10 — FINAL VERIFICATION + CLEANUP (leave no trace)
-- ============================================================================
SELECT 'BEDS STATE - FINAL (should match BEFORE)' AS phase;
SELECT COUNT(*) AS dispatch_rows FROM dispatch_assignment;                             -- 4
SELECT status AS incident_3_status FROM incidents WHERE incident_id = 3;               -- Verified
SELECT availability_status AS responder_3_status FROM responders WHERE responder_id = 3; -- Available
SELECT status AS resource_5_status FROM resources WHERE resource_id = 5;               -- Maintenance
SELECT COUNT(*) AS status_log_rows FROM incident_status_log;                           -- 14
SELECT @@autocommit AS autocommit_final;                                               -- 1 (back to default)

-- Ang aborted INSERTs nakabilin ug gaps sa AUTO_INCREMENT counters.
-- I-reset gamit ang MAX()+1 (safe kay walay row nga na-commit):
SET @dispatch_next = (SELECT COALESCE(MAX(assignment_id), 0) + 1 FROM dispatch_assignment);
SET @log_next = (SELECT COALESCE(MAX(log_id), 0) + 1 FROM incident_status_log);
SELECT @dispatch_next AS dispatch_next_id, @log_next AS log_next_id;

SET @sql = CONCAT('ALTER TABLE dispatch_assignment AUTO_INCREMENT = ', @dispatch_next);
PREPARE reset_dispatch FROM @sql;
EXECUTE reset_dispatch;
DEALLOCATE PREPARE reset_dispatch;

SET @sql = CONCAT('ALTER TABLE incident_status_log AUTO_INCREMENT = ', @log_next);
PREPARE reset_log FROM @sql;
EXECUTE reset_log;
DEALLOCATE PREPARE reset_log;

SELECT AUTO_INCREMENT AS dispatch_next_id FROM information_schema.TABLES
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dispatch_assignment';

-- I-remove ang demo bank table. (Comment out kining DROP kung gusto nimo
-- i-keep ang table para sa presentation o screenshots.)
DROP TABLE IF EXISTS transactions_demo_accounts;

-- ============================================================================
-- DONE! Summary sa concepts:
--   AUTOCOMMIT=ON   -> automatic COMMIT kada statement (Section 2)
--   AUTOCOMMIT=OFF  -> temporary changes, pwede ma-ROLLBACK (Section 3)
--   COMMIT          -> permanent save (Section 5)
--   ROLLBACK        -> undo sa tibuok transaction (Section 4, 7)
--   SAVEPOINT       -> partial undo (Section 6, 8)
-- ============================================================================
