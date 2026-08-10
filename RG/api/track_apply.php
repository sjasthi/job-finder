<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST required']);
    exit;
}

$body      = json_decode(file_get_contents('php://input'), true);
$listingId = isset($body['listing_id']) ? (int) $body['listing_id'] : 0;
$userId    = $_SESSION['user_id'];

if (empty($listingId)) {
    echo json_encode(['success' => false, 'error' => 'listing_id is required']);
    exit;
}

// Confirm the listing belongs to this user before recording the click
$stmt = $pdo->prepare("SELECT id FROM job_listings WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->execute([$listingId, $userId]);

if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Job listing not found']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO job_applies (user_id, listing_id) VALUES (?, ?)");
$stmt->execute([$userId, $listingId]);

echo json_encode(['success' => true]);
