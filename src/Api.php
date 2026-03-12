<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('DB_SERVER')) {
    define('DB_SERVER', getenv('DB_SERVER') ?: 'localhost');
}
if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'phpagent');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') ?: 'test123');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'library_db');
}

function logErrorToFile(string $message, array $context = []): void
{
    $logDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
    $logFile = $logDir . DIRECTORY_SEPARATOR . 'app_errors.log';

    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }

    $entry = [
        'timestamp' => date('c'),
        'message' => $message,
        'method' => $_SERVER['REQUEST_METHOD'] ?? null,
        'path' => $_SERVER['REQUEST_URI'] ?? null,
        'context' => $context,
    ];

    @file_put_contents(
        $logFile,
        json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

function sendJson(int $status, array $payload): void
{
    http_response_code($status);

    if ($status >= 400) {
        $errorMessage = (string) ($payload['error'] ?? $payload['message'] ?? 'Unexpected error');
        logErrorToFile("HTTP {$status} {$errorMessage}", ['payload' => $payload]);
        $response = [
            'success' => false,
            'error' => $errorMessage,
        ];

        if (isset($payload['details']) && is_array($payload['details'])) {
            $response['details'] = $payload['details'];
        }
    } else {
        $response = [
            'success' => true,
            'message' => (string) ($payload['message'] ?? 'Operation completed.'),
        ];

        if (array_key_exists('data', $payload)) {
            $response['data'] = $payload['data'];
        } else {
            $extraData = $payload;
            unset($extraData['message']);
            $response['data'] = $extraData === [] ? [] : $extraData;
        }
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function db(): mysqli
{
    try {
        $database = new Database(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        return $database->connect();
    } catch (Throwable $exception) {
        logErrorToFile('Database connection failed', ['exception' => $exception->getMessage()]);
        sendJson(500, ['error' => 'Database connection failed']);
    }
}

function prepareOrFail(mysqli $conn, string $sql): mysqli_stmt
{
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        sendJson(500, ['error' => 'Failed to prepare statement']);
    }
    return $stmt;
}

function executeOrFail(mysqli_stmt $stmt, string $genericError): void
{
    if (!$stmt->execute()) {
        if ($stmt->errno === 1451) {
            sendJson(409, ['error' => 'Operation blocked by foreign key constraints']);
        }
        if ($stmt->errno === 1062) {
            sendJson(409, ['error' => 'Duplicate value violates unique constraint']);
        }
        sendJson(500, ['error' => $genericError]);
    }
}

function requestBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        sendJson(400, ['error' => 'Invalid JSON body']);
    }

    return $data;
}

function sanitizeStringInput(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function validateEmailInput(string $email, string $fieldName = 'email'): string
{
    $sanitized = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    $validated = filter_var($sanitized, FILTER_VALIDATE_EMAIL);
    if ($validated === false) {
        sendJson(400, ['error' => "Invalid {$fieldName}"]);
    }

    return $validated;
}

function sanitizePositiveIntInput(mixed $value, string $fieldName): int
{
    if (is_string($value)) {
        $value = trim($value);
    }

    $validated = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($validated === false) {
        sendJson(400, ['error' => "Invalid {$fieldName}"]);
    }

    return (int) $validated;
}

function pathSegments(): array
{
    $path = $_SERVER['PATH_INFO'] ?? '/';
    return array_values(array_filter(explode('/', trim($path, '/'))));
}

function parseId(?string $rawId, string $fieldName = 'ID'): int
{
    if ($rawId === null) {
        sendJson(400, ['error' => "Invalid {$fieldName}"]);
    }

    return sanitizePositiveIntInput($rawId, $fieldName);
}

function parseDate(string $value, string $fieldName): string
{
    $value = sanitizeStringInput($value);
    $date = DateTime::createFromFormat('Y-m-d', $value);
    $errors = DateTime::getLastErrors();

    if ($date === false || ($errors !== false && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0))) {
        sendJson(400, ['error' => "Invalid {$fieldName}. Expected YYYY-MM-DD"]);
    }

    return $date->format('Y-m-d');
}

function fetchAllAssoc(mysqli_result $result): array
{
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function parseRoleInput(string $role): string
{
    $normalized = strtolower(sanitizeStringInput($role));
    if ($normalized === 'admin') {
        return 'admin';
    }
    if ($normalized === 'user' || $normalized === 'regular') {
        return 'user';
    }

    sendJson(400, ['error' => 'userType must be user or admin']);
}

function appRoleToDbRole(string $role): string
{
    return $role === 'admin' ? 'admin' : 'regular';
}

function mapUserRoleForOutput(array $row): array
{
    return User::fromDatabaseRow($row)->toArray();
}

function validateBookInput(array $data): array
{
    $required = ['title', 'authorName', 'coverUrl', 'firstPublishYear', 'edition', 'format'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            sendJson(400, ['error' => "Missing field: {$field}"]);
        }
    }

    $title = sanitizeStringInput((string) $data['title']);
    $authorName = sanitizeStringInput((string) $data['authorName']);
    $coverUrl = sanitizeStringInput((string) $data['coverUrl']);
    $firstPublishYear = parseDate((string) $data['firstPublishYear'], 'firstPublishYear');
    $edition = sanitizePositiveIntInput($data['edition'], 'edition');
    $format = sanitizeStringInput((string) $data['format']);

    if ($title === '' || $authorName === '' || $coverUrl === '' || $edition <= 0) {
        sendJson(400, ['error' => 'Invalid book data']);
    }
    if (!in_array($format, ['Digital', 'Physical'], true)) {
        sendJson(400, ['error' => 'format must be Digital or Physical']);
    }

    return [$title, $authorName, $coverUrl, $firstPublishYear, $edition, $format];
}

function validateUserInput(array $data): array
{
    $required = ['userName', 'email', 'password', 'userType'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            sendJson(400, ['error' => "Missing field: {$field}"]);
        }
    }

    $userName = sanitizeStringInput((string) $data['userName']);
    $email = validateEmailInput((string) $data['email']);
    $password = (string) $data['password'];
    $userType = parseRoleInput((string) $data['userType']);

    if ($userName === '' || trim($password) === '') {
        sendJson(400, ['error' => 'Invalid user data']);
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    if ($passwordHash === false) {
        sendJson(500, ['error' => 'Failed to hash password']);
    }

    return [$userName, $email, $passwordHash, appRoleToDbRole($userType)];
}

function validateRegisterInput(array $data): array
{
    $required = ['userName', 'email', 'password'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            sendJson(400, ['error' => "Missing field: {$field}"]);
        }
    }

    $userName = sanitizeStringInput((string) $data['userName']);
    $email = validateEmailInput((string) $data['email']);
    $password = (string) $data['password'];
    $userType = parseRoleInput((string) ($data['userType'] ?? 'user'));

    if ($userName === '' || trim($password) === '') {
        sendJson(400, ['error' => 'Invalid registration data']);
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    if ($passwordHash === false) {
        sendJson(500, ['error' => 'Failed to hash password']);
    }

    return [$userName, $email, $passwordHash, appRoleToDbRole($userType)];
}

function validateLoginInput(array $data): array
{
    $required = ['email', 'password'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            sendJson(400, ['error' => "Missing field: {$field}"]);
        }
    }

    $email = validateEmailInput((string) $data['email']);
    $password = (string) $data['password'];
    if (trim($password) === '') {
        sendJson(400, ['error' => 'Invalid login data']);
    }

    return [$email, $password];
}

function normalizeRole(string $role): string
{
    return strtolower(sanitizeStringInput($role)) === 'admin' ? 'admin' : 'user';
}

function requireAuthenticatedUser(): array
{
    if (!isset($_SESSION['user_id'])) {
        sendJson(401, ['error' => 'Access denied. Login required']);
    }

    $userId = (int) $_SESSION['user_id'];
    if ($userId <= 0) {
        sendJson(401, ['error' => 'Invalid session']);
    }

    $role = normalizeRole((string) ($_SESSION['role'] ?? 'regular'));
    $_SESSION['role'] = $role;

    return [$userId, $role];
}

function requireAdmin(): void
{
    [, $role] = requireAuthenticatedUser();
    if ($role !== 'admin') {
        sendJson(403, ['error' => 'Access denied. Admin role required']);
    }
}

function requireOwner(int $ownerUserId): void
{
    [$sessionUserId] = requireAuthenticatedUser();
    if ($sessionUserId !== $ownerUserId) {
        sendJson(403, ['error' => 'Access denied. Only the owner can update this resource']);
    }
}

function resolveAuditUserId(?int $fallbackUserId = null): ?int
{
    if (isset($_SESSION['user_id'])) {
        $sessionUserId = (int) $_SESSION['user_id'];
        if ($sessionUserId > 0) {
            return $sessionUserId;
        }
    }

    if ($fallbackUserId !== null && $fallbackUserId > 0) {
        return $fallbackUserId;
    }

    return null;
}

function logAuditAction(mysqli $conn, string $action, string $entity, int $entityId, ?int $actorUserId = null): void
{
    $normalizedAction = strtoupper(sanitizeStringInput($action));
    if (!in_array($normalizedAction, ['CREATE', 'UPDATE', 'DELETE'], true)) {
        sendJson(500, ['error' => 'Invalid audit action']);
    }

    $sanitizedEntity = strtolower(sanitizeStringInput($entity));
    if ($sanitizedEntity === '') {
        sendJson(500, ['error' => 'Invalid audit entity']);
    }

    $entityId = sanitizePositiveIntInput($entityId, 'entity_id');
    $resolvedUserId = resolveAuditUserId($actorUserId);

    $insertWithoutUser = static function () use ($conn, $normalizedAction, $sanitizedEntity, $entityId): bool {
        $stmt = prepareOrFail(
            $conn,
            'INSERT INTO audit_logs (user_id, action, entity, entity_id, `timestamp`) VALUES (NULL, ?, ?, ?, NOW())'
        );
        $stmt->bind_param('ssi', $normalizedAction, $sanitizedEntity, $entityId);
        return $stmt->execute();
    };

    if ($resolvedUserId === null) {
        if (!$insertWithoutUser()) {
            logErrorToFile('Failed to write audit log', [
                'action' => $normalizedAction,
                'entity' => $sanitizedEntity,
                'entity_id' => $entityId,
                'actor_user_id' => null,
            ]);
        }
        return;
    }

    $stmt = prepareOrFail(
        $conn,
        'INSERT INTO audit_logs (user_id, action, entity, entity_id, `timestamp`) VALUES (?, ?, ?, ?, NOW())'
    );
    $stmt->bind_param('issi', $resolvedUserId, $normalizedAction, $sanitizedEntity, $entityId);

    if ($stmt->execute()) {
        return;
    }

    if ($stmt->errno === 1452 && $insertWithoutUser()) {
        return;
    }

    logErrorToFile('Failed to write audit log', [
        'action' => $normalizedAction,
        'entity' => $sanitizedEntity,
        'entity_id' => $entityId,
        'actor_user_id' => $resolvedUserId,
        'db_errno' => $stmt->errno,
        'db_error' => $stmt->error,
    ]);
}

function validateLoanInput(array $data): array
{
    $required = ['userID', 'bookID', 'dueDate', 'Status'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            sendJson(400, ['error' => "Missing field: {$field}"]);
        }
    }

    $userID = sanitizePositiveIntInput($data['userID'], 'userID');
    $bookID = sanitizePositiveIntInput($data['bookID'], 'bookID');
    $dueDate = parseDate((string) $data['dueDate'], 'dueDate');
    $status = sanitizeStringInput((string) $data['Status']);

    if ($userID <= 0 || $bookID <= 0) {
        sendJson(400, ['error' => 'userID and bookID must be positive integers']);
    }
    if (!in_array($status, ['In progress', 'Finished'], true)) {
        sendJson(400, ['error' => 'Status must be In progress or Finished']);
    }

    return [$userID, $bookID, $dueDate, $status];
}

function handleRegister(mysqli $conn, string $method): void
{
    if ($method !== 'POST') {
        sendJson(405, ['error' => 'Method not allowed for register']);
    }

    [$userName, $email, $passwordHash, $userType] = validateRegisterInput(requestBody());
    $createdAt = time();
    $updateAt = $createdAt;

    $stmt = prepareOrFail($conn, 'INSERT INTO users (userName, email, paswordHash, createdAt, updateAt, userType) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssiss', $userName, $email, $passwordHash, $createdAt, $updateAt, $userType);
    executeOrFail($stmt, 'Failed to register user');
    $newUserId = (int) $stmt->insert_id;
    logAuditAction($conn, 'CREATE', 'users', $newUserId, $newUserId);

    sendJson(201, ['message' => 'User registered', 'userID' => $newUserId]);
}

function handleLogin(mysqli $conn, string $method): void
{
    if ($method !== 'POST') {
        sendJson(405, ['error' => 'Method not allowed for login']);
    }

    [$email, $password] = validateLoginInput(requestBody());

    $stmt = prepareOrFail($conn, 'SELECT userID, paswordHash, userType FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    executeOrFail($stmt, 'Failed to login');

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        sendJson(401, ['error' => 'Invalid credentials']);
    }

    $user = $result->fetch_assoc();
    $passwordHash = (string) ($user['paswordHash'] ?? '');

    if ($passwordHash === '' || !password_verify($password, $passwordHash)) {
        sendJson(401, ['error' => 'Invalid credentials']);
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['userID'];
    $_SESSION['role'] = normalizeRole((string) ($user['userType'] ?? 'regular'));

    sendJson(200, [
        'message' => 'Login successful',
        'data' => [
            'user_id' => (int) $_SESSION['user_id'],
            'role' => (string) $_SESSION['role'],
        ],
    ]);
}

function handleLogout(string $method): void
{
    if ($method !== 'POST') {
        sendJson(405, ['error' => 'Method not allowed for logout']);
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
    sendJson(200, ['message' => 'Logout successful']);
}

function handleProfile(mysqli $conn, string $method): void
{
    if ($method !== 'GET') {
        sendJson(405, ['error' => 'Method not allowed for profile']);
    }

    [$userId, $role] = requireAuthenticatedUser();

    $stmt = prepareOrFail($conn, 'SELECT userID, userName, email, createdAt, updateAt, userType FROM users WHERE userID = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    executeOrFail($stmt, 'Failed to fetch profile');
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendJson(404, ['error' => 'User not found for active session']);
    }

    sendJson(200, [
        'data' => mapUserRoleForOutput($result->fetch_assoc()),
        'session' => [
            'user_id' => $userId,
            'role' => $role,
        ],
    ]);
}

function handleBooks(mysqli $conn, string $method, array $segments): void
{
    $bookId = isset($segments[1]) ? parseId($segments[1], 'book ID') : null;

    switch ($method) {
        case 'GET':
            if ($bookId === null) {
                $stmt = prepareOrFail($conn, 'SELECT bookID, title, authorName, coverUrl, firstPublishYear, edition, format, createdAt, updatedAt FROM books ORDER BY bookID DESC');
                executeOrFail($stmt, 'Failed to list books');
                sendJson(200, ['data' => fetchAllAssoc($stmt->get_result())]);
            }

            $stmt = prepareOrFail($conn, 'SELECT bookID, title, authorName, coverUrl, firstPublishYear, edition, format, createdAt, updatedAt FROM books WHERE bookID = ?');
            $stmt->bind_param('i', $bookId);
            executeOrFail($stmt, 'Failed to fetch book');
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                sendJson(404, ['error' => 'Book not found']);
            }
            sendJson(200, ['data' => $result->fetch_assoc()]);
            break;

        case 'POST':
            if ($bookId !== null) {
                sendJson(400, ['error' => 'POST does not accept book ID in route']);
            }

            [$title, $authorName, $coverUrl, $firstPublishYear, $edition, $format] = validateBookInput(requestBody());
            $stmt = prepareOrFail($conn, 'INSERT INTO books (title, authorName, coverUrl, firstPublishYear, edition, format, createdAt, updatedAt) VALUES (?, ?, ?, ?, ?, ?, CURDATE(), CURDATE())');
            $stmt->bind_param('ssssis', $title, $authorName, $coverUrl, $firstPublishYear, $edition, $format);
            executeOrFail($stmt, 'Failed to create book');
            $newBookId = (int) $stmt->insert_id;
            logAuditAction($conn, 'CREATE', 'books', $newBookId);

            sendJson(201, ['message' => 'Book created', 'bookID' => $newBookId]);
            break;

        case 'PUT':
            if ($bookId === null) {
                sendJson(400, ['error' => 'Book ID is required for update']);
            }

            [$title, $authorName, $coverUrl, $firstPublishYear, $edition, $format] = validateBookInput(requestBody());
            $stmt = prepareOrFail($conn, 'UPDATE books SET title = ?, authorName = ?, coverUrl = ?, firstPublishYear = ?, edition = ?, format = ?, updatedAt = CURDATE() WHERE bookID = ?');
            $stmt->bind_param('ssssisi', $title, $authorName, $coverUrl, $firstPublishYear, $edition, $format, $bookId);
            executeOrFail($stmt, 'Failed to update book');

            if ($stmt->affected_rows < 1) {
                sendJson(404, ['error' => 'Book not found or no changes']);
            }
            logAuditAction($conn, 'UPDATE', 'books', $bookId);
            sendJson(200, ['message' => 'Book updated']);
            break;

        case 'DELETE':
            requireAdmin();

            if ($bookId === null) {
                sendJson(400, ['error' => 'Book ID is required for delete']);
            }

            $stmt = prepareOrFail($conn, 'DELETE FROM books WHERE bookID = ?');
            $stmt->bind_param('i', $bookId);
            executeOrFail($stmt, 'Failed to delete book');

            if ($stmt->affected_rows < 1) {
                sendJson(404, ['error' => 'Book not found']);
            }
            logAuditAction($conn, 'DELETE', 'books', $bookId);
            sendJson(200, ['message' => 'Book deleted']);
            break;

        default:
            sendJson(405, ['error' => 'Method not allowed for books']);
    }
}

function handleUsers(mysqli $conn, string $method, array $segments): void
{
    $userId = isset($segments[1]) ? parseId($segments[1], 'user ID') : null;

    switch ($method) {
        case 'GET':
            if ($userId === null) {
                $stmt = prepareOrFail($conn, 'SELECT userID, userName, email, createdAt, updateAt, userType FROM users ORDER BY userID DESC');
                executeOrFail($stmt, 'Failed to list users');
                $rows = array_map('mapUserRoleForOutput', fetchAllAssoc($stmt->get_result()));
                sendJson(200, ['data' => $rows]);
            }

            $stmt = prepareOrFail($conn, 'SELECT userID, userName, email, createdAt, updateAt, userType FROM users WHERE userID = ?');
            $stmt->bind_param('i', $userId);
            executeOrFail($stmt, 'Failed to fetch user');
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                sendJson(404, ['error' => 'User not found']);
            }
            sendJson(200, ['data' => mapUserRoleForOutput($result->fetch_assoc())]);
            break;

        case 'POST':
            if ($userId !== null) {
                sendJson(400, ['error' => 'POST does not accept user ID in route']);
            }

            [$userName, $email, $passwordHash, $userType] = validateUserInput(requestBody());
            $createdAt = time();
            $updateAt = $createdAt;

            $stmt = prepareOrFail($conn, 'INSERT INTO users (userName, email, paswordHash, createdAt, updateAt, userType) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssiss', $userName, $email, $passwordHash, $createdAt, $updateAt, $userType);
            executeOrFail($stmt, 'Failed to create user');
            $newUserId = (int) $stmt->insert_id;
            logAuditAction($conn, 'CREATE', 'users', $newUserId);

            sendJson(201, ['message' => 'User created', 'userID' => $newUserId]);
            break;

        case 'PUT':
            if ($userId === null) {
                sendJson(400, ['error' => 'User ID is required for update']);
            }

            requireOwner($userId);

            [$userName, $email, $passwordHash, $userType] = validateUserInput(requestBody());
            $updateAt = time();

            $stmt = prepareOrFail($conn, 'UPDATE users SET userName = ?, email = ?, paswordHash = ?, updateAt = ?, userType = ? WHERE userID = ?');
            $stmt->bind_param('sssisi', $userName, $email, $passwordHash, $updateAt, $userType, $userId);
            executeOrFail($stmt, 'Failed to update user');

            if ($stmt->affected_rows < 1) {
                sendJson(404, ['error' => 'User not found or no changes']);
            }
            logAuditAction($conn, 'UPDATE', 'users', $userId);
            sendJson(200, ['message' => 'User updated']);
            break;

        case 'DELETE':
            requireAdmin();

            if ($userId === null) {
                sendJson(400, ['error' => 'User ID is required for delete']);
            }

            $stmt = prepareOrFail($conn, 'DELETE FROM users WHERE userID = ?');
            $stmt->bind_param('i', $userId);
            executeOrFail($stmt, 'Failed to delete user');

            if ($stmt->affected_rows < 1) {
                sendJson(404, ['error' => 'User not found']);
            }
            logAuditAction($conn, 'DELETE', 'users', $userId);
            sendJson(200, ['message' => 'User deleted']);
            break;

        default:
            sendJson(405, ['error' => 'Method not allowed for users']);
    }
}

function handleLoans(mysqli $conn, string $method, array $segments): void
{
    $loanId = isset($segments[1]) ? parseId($segments[1], 'loan ID') : null;

    switch ($method) {
        case 'GET':
            if ($loanId === null) {
                $stmt = prepareOrFail(
                    $conn,
                    'SELECT l.loansID, l.userID, u.userName, l.bookID, b.title, l.dueDate, l.Status, l.createdAt, l.updatedAt
                     FROM loans AS l
                     INNER JOIN users AS u ON l.userID = u.userID
                     INNER JOIN books AS b ON l.bookID = b.bookID
                     ORDER BY l.loansID DESC'
                );
                executeOrFail($stmt, 'Failed to list loans');
                sendJson(200, ['data' => fetchAllAssoc($stmt->get_result())]);
            }

            $stmt = prepareOrFail(
                $conn,
                'SELECT l.loansID, l.userID, u.userName, l.bookID, b.title, l.dueDate, l.Status, l.createdAt, l.updatedAt
                 FROM loans AS l
                 INNER JOIN users AS u ON l.userID = u.userID
                 INNER JOIN books AS b ON l.bookID = b.bookID
                 WHERE l.loansID = ?'
            );
            $stmt->bind_param('i', $loanId);
            executeOrFail($stmt, 'Failed to fetch loan');
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                sendJson(404, ['error' => 'Loan not found']);
            }
            sendJson(200, ['data' => $result->fetch_assoc()]);
            break;

        case 'POST':
            if ($loanId !== null) {
                sendJson(400, ['error' => 'POST does not accept loan ID in route']);
            }

            [$userID, $bookID, $dueDate, $status] = validateLoanInput(requestBody());
            $stmt = prepareOrFail($conn, 'INSERT INTO loans (userID, bookID, dueDate, Status, createdAt, updatedAt) VALUES (?, ?, ?, ?, CURDATE(), CURDATE())');
            $stmt->bind_param('iiss', $userID, $bookID, $dueDate, $status);
            executeOrFail($stmt, 'Failed to create loan');
            $newLoanId = (int) $stmt->insert_id;
            logAuditAction($conn, 'CREATE', 'loans', $newLoanId);

            sendJson(201, ['message' => 'Loan created', 'loansID' => $newLoanId]);
            break;

        case 'PUT':
            if ($loanId === null) {
                sendJson(400, ['error' => 'Loan ID is required for update']);
            }

            [$userID, $bookID, $dueDate, $status] = validateLoanInput(requestBody());
            $stmt = prepareOrFail($conn, 'UPDATE loans SET userID = ?, bookID = ?, dueDate = ?, Status = ?, updatedAt = CURDATE() WHERE loansID = ?');
            $stmt->bind_param('iissi', $userID, $bookID, $dueDate, $status, $loanId);
            executeOrFail($stmt, 'Failed to update loan');

            if ($stmt->affected_rows < 1) {
                sendJson(404, ['error' => 'Loan not found or no changes']);
            }
            logAuditAction($conn, 'UPDATE', 'loans', $loanId);
            sendJson(200, ['message' => 'Loan updated']);
            break;

        case 'DELETE':
            requireAdmin();

            if ($loanId === null) {
                sendJson(400, ['error' => 'Loan ID is required for delete']);
            }

            $stmt = prepareOrFail($conn, 'DELETE FROM loans WHERE loansID = ?');
            $stmt->bind_param('i', $loanId);
            executeOrFail($stmt, 'Failed to delete loan');

            if ($stmt->affected_rows < 1) {
                sendJson(404, ['error' => 'Loan not found']);
            }
            logAuditAction($conn, 'DELETE', 'loans', $loanId);
            sendJson(200, ['message' => 'Loan deleted']);
            break;

        default:
            sendJson(405, ['error' => 'Method not allowed for loans']);
    }
}

function handleLoanDetails(mysqli $conn, string $method): void
{
    if ($method !== 'GET') {
        sendJson(405, ['error' => 'Method not allowed for loan details']);
    }

    $stmt = prepareOrFail(
        $conn,
        'SELECT l.loansID, l.userID, u.userName, l.bookID, b.title, l.dueDate, l.Status, l.createdAt, l.updatedAt
         FROM loans AS l
         INNER JOIN users AS u ON l.userID = u.userID
         INNER JOIN books AS b ON l.bookID = b.bookID
         ORDER BY l.loansID DESC'
    );
    executeOrFail($stmt, 'Failed to list loan details');
    sendJson(200, ['data' => fetchAllAssoc($stmt->get_result())]);
}

function handleStats(mysqli $conn, string $method): void
{
    if ($method !== 'GET') {
        sendJson(405, ['error' => 'Method not allowed for stats']);
    }

    requireAuthenticatedUser();

    try {
        $stmtBooks = prepareOrFail($conn, 'SELECT COUNT(*) AS totalBooks FROM books');
        if (!$stmtBooks->execute()) {
            throw new RuntimeException($stmtBooks->error ?: 'Unknown SQL error while counting books');
        }
        $books = $stmtBooks->get_result()->fetch_assoc();
    } catch (Throwable $exception) {
        logErrorToFile('Failed to fetch books count', ['exception' => $exception->getMessage()]);
        sendJson(500, ['error' => 'Failed to fetch books count']);
    }

    $stmtUsers = prepareOrFail($conn, 'SELECT COUNT(*) AS totalUsers FROM users');
    executeOrFail($stmtUsers, 'Failed to fetch users count');
    $users = $stmtUsers->get_result()->fetch_assoc();

    $stmtLoans = prepareOrFail($conn, 'SELECT COUNT(*) AS totalLoans FROM loans');
    executeOrFail($stmtLoans, 'Failed to fetch loans count');
    $loans = $stmtLoans->get_result()->fetch_assoc();

    $stmtOpenLoans = prepareOrFail($conn, 'SELECT COUNT(*) AS inProgressLoans FROM loans WHERE Status = ?');
    $statusInProgress = 'In progress';
    $stmtOpenLoans->bind_param('s', $statusInProgress);
    executeOrFail($stmtOpenLoans, 'Failed to fetch in-progress loans count');
    $openLoans = $stmtOpenLoans->get_result()->fetch_assoc();

    $stmtAvgPrice = prepareOrFail($conn, 'SELECT AVG(Price) AS avgPurchasePrice FROM purchases');
    executeOrFail($stmtAvgPrice, 'Failed to fetch purchases average');
    $avgPrice = $stmtAvgPrice->get_result()->fetch_assoc();

    sendJson(200, [
        'data' => [
            'totalBooks' => (int) ($books['totalBooks'] ?? 0),
            'totalUsers' => (int) ($users['totalUsers'] ?? 0),
            'totalLoans' => (int) ($loans['totalLoans'] ?? 0),
            'inProgressLoans' => (int) ($openLoans['inProgressLoans'] ?? 0),
            'avgPurchasePrice' => isset($avgPrice['avgPurchasePrice']) ? (float) $avgPrice['avgPurchasePrice'] : null,
        ],
    ]);
}

$segments = pathSegments();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (count($segments) === 0) {
    sendJson(404, ['error' => 'Route not found']);
}

$conn = db();
$resource = $segments[0];

switch ($resource) {
    case 'register':
        handleRegister($conn, $method);
        break;

    case 'login':
        handleLogin($conn, $method);
        break;

    case 'logout':
        handleLogout($method);
        break;

    case 'profile':
        handleProfile($conn, $method);
        break;

    case 'books':
        handleBooks($conn, $method, $segments);
        break;

    case 'users':
        handleUsers($conn, $method, $segments);
        break;

    case 'loans':
        if (($segments[1] ?? '') === 'details') {
            handleLoanDetails($conn, $method);
        }
        handleLoans($conn, $method, $segments);
        break;

    case 'stats':
        handleStats($conn, $method);
        break;

    default:
        sendJson(404, ['error' => 'Route not found']);
}
?>
