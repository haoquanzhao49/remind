<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $reminder = $_POST['reminder'] ? $_POST['reminder'] : null;
    $tags = $_POST['tags'];

    // Simulated AI logic (replace with OpenAI API for real AI)
    $ai_suggestion = "Based on your note, consider breaking this into smaller tasks.";
    $ai_summary = substr($content, 0, 50) . "..."; // Simple summary

    $stmt = $pdo->prepare("INSERT INTO notes (user_id, title, content, reminder, tags, ai_suggestion, ai_summary) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $content, $reminder, $tags, $ai_suggestion, $ai_summary]);

    header("Location: dashboard.php");
}
?>