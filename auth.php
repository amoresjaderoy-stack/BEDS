<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        "httponly" => true,
        "samesite" => "Lax",
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    ]);
    session_start();
}

function beds_current_user(array $db): ?array
{
    $userId = $_SESSION["beds_user_id"] ?? null;
    return $userId === null
        ? null
        : beds_find($db["users"], "user_id", $userId);
}

function beds_password_is_hashed(string $password): bool
{
    return password_get_info($password)["algoName"] !== "unknown";
}

function beds_password_matches(string $stored, string $password): bool
{
    if (beds_password_is_hashed($stored)) {
        return password_verify($password, $stored);
    }
    // Legacy seeded accounts still use plain text passwords. They are
    // automatically upgraded to a secure hash on their next successful login.
    return hash_equals($stored, $password);
}

function beds_find_login_user(array $db, string $login): ?int
{
    $login = trim($login);
    if ($login === "") {
        return null;
    }
    foreach ($db["users"] as $index => $user) {
        $email = trim((string) ($user["email"] ?? ""));
        if (
            strcasecmp((string) $user["username"], $login) === 0 ||
            ($email !== "" && strcasecmp($email, $login) === 0)
        ) {
            return $index;
        }
    }
    return null;
}

function beds_attempt_login(
    array &$db,
    string $login,
    string $password,
): ?array {
    $index = beds_find_login_user($db, $login);
    if ($index === null) {
        return null;
    }
    $storedPassword = (string) $db["users"][$index]["password"];
    if (!beds_password_matches($storedPassword, $password)) {
        return null;
    }
    if (
        !beds_password_is_hashed($storedPassword) ||
        password_needs_rehash($storedPassword, PASSWORD_DEFAULT)
    ) {
        $db["users"][$index]["password"] = password_hash(
            $password,
            PASSWORD_DEFAULT,
        );
        beds_save_db($db);
    }

    session_regenerate_id(true);
    $_SESSION["beds_user_id"] = $db["users"][$index]["user_id"];
    return $db["users"][$index];
}

function beds_register_user(
    array &$db,
    string $fullName,
    string $username,
    string $email,
    string $contactNo,
    int $roleId,
    string $password,
    string $confirmPassword,
): array {
    $fullName = trim((string) preg_replace("/\s+/", " ", $fullName));
    $username = trim($username);
    $email = trim($email);
    $contactNo = trim($contactNo);

    if ($fullName === "" || $username === "" || $password === "") {
        return [
            "user" => null,
            "error" => "Please complete the full name, username, and password fields.",
        ];
    }
    if (strlen($fullName) < 2) {
        return ["user" => null, "error" => "Please enter your full name."];
    }
    if (!preg_match('/^[A-Za-z0-9._-]{3,30}$/', $username)) {
        return [
            "user" => null,
            "error" =>
                "Username must be 3-30 characters and may only contain letters, numbers, dots, underscores, or hyphens.",
        ];
    }
    if (beds_find_login_user($db, $username) !== null) {
        return [
            "user" => null,
            "error" =>
                "That username is already taken. Please choose another one.",
        ];
    }
    if ($email !== "") {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                "user" => null,
                "error" => "Please enter a valid email address.",
            ];
        }
        foreach ($db["users"] as $user) {
            if (
                trim((string) ($user["email"] ?? "")) !== "" &&
                strcasecmp((string) $user["email"], $email) === 0
            ) {
                return [
                    "user" => null,
                    "error" =>
                        "That email address is already registered to another account.",
                ];
            }
        }
    }
    if (beds_find($db["roles"], "role_id", $roleId) === null) {
        return ["user" => null, "error" => "Please select a valid role."];
    }
    if (strlen($password) < 6) {
        return [
            "user" => null,
            "error" => "Password must be at least 6 characters long.",
        ];
    }
    if (!hash_equals($password, $confirmPassword)) {
        return ["user" => null, "error" => "The passwords do not match."];
    }

    $user = [
        "user_id" => beds_next_id($db, "user_id"),
        "role_id" => $roleId,
        "full_name" => $fullName,
        "username" => $username,
        "password" => password_hash($password, PASSWORD_DEFAULT),
        "contact_no" => $contactNo,
        "email" => $email,
        "date_created" => date("Y-m-d"),
    ];
    $db["users"][] = $user;

    return ["user" => $user, "error" => null];
}

function beds_logout(): void
{
    unset($_SESSION["beds_user_id"]);
    session_regenerate_id(true);
}
