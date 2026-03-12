# Postman Testing Artifacts

This folder stores Postman testing assets for this project.

## Structure

- `collections/`: exported Postman collections (`*.collection.json`)
- `environments/`: exported environments (`*.environment.json`)
- `runs/`: optional run reports (JSON/HTML screenshots, etc.)

## How To Save Postman Tabs

Postman tabs are temporary until you save them as requests.

1. In each tab, click `Save` and store the request in a collection (for example `Backend Library API`).
2. Export that collection (v2.1) and save it in `testing/postman/collections/`.
3. Export your environment and save:
   - versionable file (without secrets) in `testing/postman/environments/`
   - local file with secrets as `*.local.json` (ignored by Git)
4. If you run a collection, save report artifacts in `testing/postman/runs/`.

## Routine Test - Tab Dependencies (Recommended Execution)

Run tabs in order if you want consistent and repeatable results.

Data/session initializer tabs:

- `01 - Auth - Register Admin` -> sets `adminUserId`
- `02 - Auth - Register User` -> sets `userId`
- `03 - Auth - Login User` -> opens user session
- `11 - Auth - Login Admin` -> opens admin session
- `12 - Books - Create` -> sets `bookId`
- `13 - Users - Create (Extra User)` -> sets `extraUserId`
- `14 - Loans - Create` -> sets `loanId`

Dependent tabs:

- `04 - Auth - Profile (User)` requires `03`
- `05 - Stats (User Session)` requires `03`
- `06 - Users - Update Self (Owner OK)` requires `03` + `userId`
- `07 - Users - Update Other (Owner Forbidden)` requires `03` + `adminUserId`
- `08 - Books - Delete as User (Forbidden)` expects `403` only if user session exists (`03`)
- `09 - Loans - Delete as User (Forbidden)` expects `403` only if user session exists (`03`)
- `10 - Auth - Logout User` requires `03`
- `14 - Loans - Create` requires `userId` (`02`) + `bookId` (`12`)
- `16 - Books - Get By Id` requires `bookId`
- `17 - Books - Update` requires `bookId`
- `19 - Users - Get By Id` requires `extraUserId` (`13`)
- `21 - Loans - Get By Id` requires `loanId` (`14`)
- `23 - Loans - Update` requires `loanId` + valid `userId` and `bookId`
- `24 - Loans - Delete (Admin)` requires admin session (`11`) + `loanId`
- `25 - Books - Delete (Admin)` requires admin session (`11`) + `bookId`
- `26 - Users - Delete (Admin)` requires admin session (`11`) + `extraUserId`
- `27 - Stats (Admin Session)` requires admin session (`11`)
- `28 - Auth - Logout Admin` requires admin session (`11`)

Notes:

- Out-of-order execution can change expected status codes (example: `401` instead of `403` if there is no active session).
- Keep your environment variables updated after create/register tabs.

## Expected Errors (And Why)

Use this as a quick debugging guide during Postman runs.

- `401 Unauthorized` (`Access denied. Login required`)
  - Why: no active session cookie (`PHPSESSID`) for protected logic.
  - Common cases: running owner/admin checks before login; calling protected endpoints after logout.

- `403 Forbidden` (`Access denied. Admin role required`)
  - Why: endpoint is admin-only and current session role is `user`.
  - Common cases: `DELETE /books/{id}`, `DELETE /users/{id}`, `DELETE /loans/{id}` executed with user session.

- `403 Forbidden` (`Access denied. Only the owner can update this resource`)
  - Why: owner-only update check failed for `PUT /users/{id}`.
  - Common case: logged-in user tries to update another user's `{id}`.

- `404 Not Found` (`Route not found`)
  - Why: invalid route/path or missing endpoint segment.
  - Common cases: malformed URL, wrong base URL path, missing `/backend.php`.

- `404 Not Found` (entity-specific)
  - Why: target record does not exist.
  - Common cases: `GET /books/{id}`, `GET /users/{id}`, `GET /loans/{id}` with non-existent IDs.

- `409 Conflict` (`Duplicate value violates unique constraint`)
  - Why: DB unique constraint violation.
  - Common case: registering/creating a user with an email that already exists (`users.email_unique`).

- `409 Conflict` (`Operation blocked by foreign key constraints`)
  - Why: delete/update blocked by FK references.
  - Common case: deleting parent rows that are referenced by child rows (`loans`, `purchases`, or `audit_logs` relations).

- `400 Bad Request` (`Invalid JSON body`, `Missing field`, `Invalid <field>`)
  - Why: request validation failed.
  - Common cases: malformed JSON, missing required fields, invalid email/date/int values.

- `500 Internal Server Error` (`Database connection failed`)
  - Why: API cannot connect to MySQL.
  - Common cases: wrong DB credentials, MySQL service stopped, missing DB, missing user privileges.

## Recommended Naming

- `collections/backend-library.collection.json`
- `environments/backend-library.environment.json`
- `environments/backend-library.local.json` (ignored)
- `runs/2026-03-10-smoke-test.json`
