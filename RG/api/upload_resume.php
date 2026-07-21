<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

// Must be logged in
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

if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
    $codes = [
        UPLOAD_ERR_INI_SIZE  => 'File exceeds server upload limit',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit',
        UPLOAD_ERR_NO_FILE   => 'No file uploaded',
    ];
    $code = $_FILES['resume']['error'] ?? UPLOAD_ERR_NO_FILE;
    echo json_encode(['success' => false, 'error' => $codes[$code] ?? 'Upload error ' . $code]);
    exit;
}

$file = $_FILES['resume'];
$ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($ext !== 'pdf') {
    echo json_encode(['success' => false, 'error' => 'Only PDF files are supported']);
    exit;
}

// Verify MIME type from file content (first 4 bytes = %PDF)
$handle = fopen($file['tmp_name'], 'rb');
$header = fread($handle, 4);
fclose($handle);
if ($header !== '%PDF') {
    echo json_encode(['success' => false, 'error' => 'File does not appear to be a valid PDF']);
    exit;
}

$userId    = $_SESSION['user_id'];  // fixed: was getSessionUserId()
$uploadDir = __DIR__ . '/../../uploads/resumes/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$storedName   = $userId . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
$storedPath   = $uploadDir . $storedName;
$relativePath = 'uploads/resumes/' . $storedName;

if (!move_uploaded_file($file['tmp_name'], $storedPath)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file']);
    exit;
}

$parsedText = extractPdfText($storedPath);

// fixed: was getDb() — using $pdo from db.php directly
// Deactivate previous resumes for this user
$pdo->prepare('UPDATE resumes SET is_active = 0 WHERE user_id = ?')->execute([$userId]);

$stmt = $pdo->prepare('
    INSERT INTO resumes (user_id, filename, file_path, file_type, parsed_text, is_active)
    VALUES (?, ?, ?, ?, ?, 1)
');
$stmt->execute([$userId, $file['name'], $relativePath, 'pdf', $parsedText]);

$resumeId = (int) $pdo->lastInsertId();
$_SESSION['resume_id']   = $resumeId;
$_SESSION['resume_name'] = $file['name'];

echo json_encode([
    'success'      => true,
    'resume_id'    => $resumeId,
    'filename'     => $file['name'],
    'text_preview' => mb_substr($parsedText, 0, 200),
    'text_length'  => strlen($parsedText),
]);

// ── PDF text extraction ──────────────────────────────────────────────────────

function extractPdfText(string $path): string
{
    // Use smalot/pdfparser if available
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf    = $parser->parseFile($path);
            $text   = $pdf->getText();
            if (!empty(trim($text))) {
                return trim($text);
            }
        } catch (\Exception $e) {
            // fall through to regex fallback
        }
    }

    // Fallback: regex-based extraction from raw PDF bytes
    $raw  = file_get_contents($path);
    $text = '';

    preg_match_all('/\(([^)\\\\]*(?:\\\\.[^)\\\\]*)*)\)\s*Tj/s', $raw, $m1);
    foreach ($m1[1] as $chunk) {
        $text .= str_replace(['\\n', '\\r', '\\t'], ["\n", "\n", ' '], $chunk) . ' ';
    }

    preg_match_all('/\[([^\]]+)\]\s*TJ/s', $raw, $m2);
    foreach ($m2[1] as $block) {
        preg_match_all('/\(([^)]*)\)/', $block, $pieces);
        foreach ($pieces[1] as $p) {
            $text .= $p . ' ';
        }
    }

    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}