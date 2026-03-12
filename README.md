# Library App

## Authors
- Diego - Product Owner & Developer
- Andrea - Scrum Master & Developer


## Overview
Library App Backend


## Project Description

Library App Backend is a PHP + MySQL API for a library domain. It supports:

- CRUD operations for `users`, `books`, and `loans`.
- Authentication with `register`, `login`, and `logout`.
- Session-based access control and role-based authorization (`admin` / `user`).
- Input validation, sanitization, and prepared statements for SQL safety.
- Audit trail for `CREATE`, `UPDATE`, and `DELETE`.
- File-based error logging for operational troubleshooting.


## Project Structure

- `public/index.php`: HTTP entry point.
- `src/Api.php`: route handling and endpoint logic.
- `src/Database.php`: database connection class.
- `src/User.php`: user domain class.
- `database/exported_database.sql`: schema + seed data.
- `docs/erd.md`: ERD documentation.
- `storage/logs/app_errors.log`: runtime error log file.
- `testing/postman/`: Postman collections, environments, and run artifacts.


## Database Setup Instructions

1. Create a MySQL database named `library_db`.
2. Import `database/exported_database.sql` into that database.
3. Confirm tables exist: `users`, `books`, `loans`, `purchases`, `audit_logs`.
4. Seeded users already include valid bcrypt hashes.
5. Seeded users use this test password: `Password123!`.


## Configuration Steps

1. Start Apache and MySQL (AmpPS).
2. Place this project under your web root (`www/backend-library`).
3. Use one of these URLs as entry point:
   - `http://localhost/backend-library/public/index.php`
   - `http://localhost/backend-library/backend.php` (compatibility entry point)
4. Configure DB credentials with environment variables if needed:
   - `DB_SERVER` (default: `localhost`)
   - `DB_USER` (default: `phpagent`)
   - `DB_PASS` (default: `test123`)
   - `DB_NAME` (default: `library_db`)
5. Ensure the app can write to `storage/logs/`.


## Testing Instructions (Postman)

1. Create a Postman collection and set a variable `baseUrl`, for example:
   - `http://localhost/backend-library/backend.php`
2. Add header `Content-Type: application/json` for POST/PUT requests.
3. Keep cookies enabled in Postman (same session is required).
4. Suggested authentication flow:
   - `POST {{baseUrl}}/register`
   - `POST {{baseUrl}}/login`
   - `GET {{baseUrl}}/profile`
   - `GET {{baseUrl}}/stats`
   - `POST {{baseUrl}}/logout`
5. CRUD testing examples:
   - Books: `{{baseUrl}}/books` and `{{baseUrl}}/books/{id}`
   - Users: `{{baseUrl}}/users` and `{{baseUrl}}/users/{id}`
   - Loans: `{{baseUrl}}/loans` and `{{baseUrl}}/loans/{id}`
6. Save your Postman tabs as requests inside a collection (`Save` in each tab).
7. Export and store Postman artifacts in this repo:
   - `testing/postman/collections/`
   - `testing/postman/environments/`
   - `testing/postman/runs/`


## Technical Implementations

### 1. Database Design & Constraints

- The schema is relational and includes linked entities: `users`, `books`, `loans`, `purchases`, and `audit_logs`.
- Core constraints are implemented in SQL:
  - `PRIMARY KEY` in each table.
  - `FOREIGN KEY` constraints to enforce relationships.
  - `NOT NULL` on required fields.
  - `AUTO_INCREMENT` on identity columns.
- Relationship integrity is enforced with referential actions (`ON DELETE` / `ON UPDATE`) in `database/exported_database.sql`.

### 2. CRUD & SQL Queries

- The API implements CRUD endpoints for main entities (`users`, `books`, and `loans`).
- SQL operations use prepared statements (`prepare` + `bind_param`) for secure query execution.
- Query coverage includes:
  - `WHERE` filters (single-record lookups/updates/deletes).
  - `JOIN` queries (loan details with related user/book data).
  - Aggregate functions (`COUNT`, `AVG`) in stats endpoints.
- API responses are returned in structured JSON format.

### 3. Authentication and Sessions

This API now includes:

- Roles: `admin` and `user`.
- Internally, DB role values are mapped for compatibility (`admin` -> `admin`, `user` -> `regular`).
- `POST /register`: creates a user and stores only hashed password (`password_hash`).
- `POST /login`: validates password with `password_verify` and stores:
  - `$_SESSION['user_id']`
  - `$_SESSION['role']`
- `POST /logout`: destroys session (`session_destroy`).
- `GET /profile`: protected endpoint, requires active session.
- `GET /stats`: protected endpoint, requires active session.
- All `DELETE` operations are restricted to `admin`.
- `PUT /users/{id}` is owner-only (the logged-in user must match `{id}`).

#### Example payloads

`POST /register`

```json
{
  "userName": "Diego",
  "email": "diego@example.com",
  "password": "SuperSecret123",
  "userType": "user"
}
```

`POST /login`

```json
{
  "email": "diego@example.com",
  "password": "SuperSecret123"
}
```

Important: use the same client session (cookie jar) between `/login`, `/profile`, `/stats` and `/logout`.

Note: sample rows in `database/exported_database.sql` already store valid bcrypt hashes. For those seeded users, the test password is `Password123!`.

### 4. Security: Prepared Statements and Validation

- All database operations use prepared statements (`prepare` + `bind_param`).
- Email validation is done with `filter_var(..., FILTER_VALIDATE_EMAIL)`.
- String inputs are sanitized with `htmlspecialchars()`.
- Numeric inputs are sanitized/validated with `filter_var(..., FILTER_VALIDATE_INT)`.
- No raw `$_POST` or `$_GET` values are used directly in SQL.

### 5. Error Handling and Logging

- A database operation uses `try-catch` in `GET /stats` (books count query).
- Error responses are structured in JSON with:
  - `success: false`
  - `error: "message"`
- Success responses are structured in JSON with:
  - `success: true`
  - `message: "Operation completed."` (or endpoint-specific message)
  - `data: ...`
- Errors are logged to `storage/logs/app_errors.log`.

Success example:

```json
{
  "success": true,
  "data": [],
  "message": "Operation completed."
}
```

Error example:

```json
{
  "success": false,
  "error": "Validation failed."
}
```

### 6. Audit Logs

- A table named `audit_logs` is defined with fields:
  - `id`
  - `user_id`
  - `action`
  - `entity`
  - `entity_id`
  - `timestamp`
- The API writes audit records for all successful:
  - `CREATE`
  - `UPDATE`
  - `DELETE`
- Audited entities include: `users`, `books`, and `loans`.

### 7. OOP Structure

- `src/Database.php`: `Database` class with:
  - `__construct(...)`
  - private properties for connection config and connection instance
  - public methods `connect()`, `prepare()`, `disconnect()`
- `src/User.php`: `User` domain class with:
  - `__construct(...)`
  - private properties (`id`, `userName`, `email`, `role`, etc.)
  - public methods such as `toArray()`, `isAdmin()`, `getId()`
- `src/Api.php` now uses:
  - `Database` for DB connection creation
  - `User::fromDatabaseRow(...)->toArray()` for user output mapping
