<?php
declare(strict_types=1);
require_once __DIR__ . "/backend/connection.php";
require_once __DIR__ . "/backend/auth.php";
require_once __DIR__ . "/backend/actions.php";
require_once __DIR__ . "/classes/helpers.php";
require_once __DIR__ . "/classes/navigation.php";

$db = beds_load_db();
$loginError = "";
$registerError = "";
$authMode =
    (string) ($_GET["mode"] ?? "") === "register" ? "register" : "login";
$registerValues = [
    "full_name" => "",
    "username" => "",
    "email" => "",
    "contact_no" => "",
    "role_id" => 2,
];
$authNotice = (string) ($_SESSION["beds_auth_notice"] ?? "");
unset($_SESSION["beds_auth_notice"]);
$flashMessage = (string) ($_SESSION["beds_flash"] ?? "");
unset($_SESSION["beds_flash"]);

if (isset($_GET["logout"])) {
    beds_logout();
    header("Location: index.php");
    exit();
}

$currentUser = beds_current_user($db);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $authAction = (string) ($_POST["action"] ?? "");
    if ($authAction === "login") {
        $currentUser = beds_attempt_login(
            $db,
            (string) ($_POST["username"] ?? ""),
            (string) ($_POST["password"] ?? ""),
        );
        if ($currentUser === null) {
            $loginError = "Invalid username/email or password.";
            $authMode = "login";
        } else {
            header("Location: index.php?page=dashboard");
            exit();
        }
    } elseif ($authAction === "register") {
        $authMode = "register";
        $registerValues = [
            "full_name" => trim((string) ($_POST["full_name"] ?? "")),
            "username" => trim((string) ($_POST["username"] ?? "")),
            "email" => trim((string) ($_POST["email"] ?? "")),
            "contact_no" => trim((string) ($_POST["contact_no"] ?? "")),
            "role_id" => (int) ($_POST["role_id"] ?? 2),
        ];
        $registration = beds_register_user(
            $db,
            $registerValues["full_name"],
            $registerValues["username"],
            $registerValues["email"],
            $registerValues["contact_no"],
            $registerValues["role_id"],
            (string) ($_POST["password"] ?? ""),
            (string) ($_POST["confirm_password"] ?? ""),
        );
        if ($registration["error"] !== null) {
            $registerError = (string) $registration["error"];
        } else {
            beds_save_db($db);
            $_SESSION["beds_auth_notice"] =
                "Account created successfully. Sign in with your new username.";
            header("Location: index.php");
            exit();
        }
    }
}

if (
    !$currentUser
) { ?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>BEDS — Barangay Emergency Dispatch System</title><link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet"><link rel="stylesheet" href="assets/css/styles.css"></head><body><?php include __DIR__ .
    "/views/login.php"; ?></body></html><?php exit();}

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") !== "login"
) {
    $result = beds_post_action($db, $currentUser);
    beds_save_db($db);
    $_SESSION["beds_flash"] = $result["message"];
    header("Location: " . $result["redirect"]);
    exit();
}

$page = (string) ($_GET["page"] ?? "dashboard");
if (
    !isset(BedsNavigation::pageTitles()[$page]) ||
    !BedsNavigation::canView($page, $currentUser)
) {
    $page = "dashboard";
}
$title = BedsNavigation::pageTitles()[$page];
$modal = (string) ($_GET["modal"] ?? "");
$id = (int) ($_GET["id"] ?? 0);
$viewFile = __DIR__ . "/views/" . $page . ".php";
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= BedsHelper::e(
    $title,
) ?> — BEDS</title><link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet"><link rel="stylesheet" href="assets/css/styles.css"></head><body><?php
 include __DIR__ . "/views/shell_start.php";
 if (is_file($viewFile)) {
     include $viewFile;
 }
 include __DIR__ . "/views/shell_end.php";
 ?></body></html>
