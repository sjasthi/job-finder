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

// Must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'POST required']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$jobTitle       = trim($body['title']           ?? '');
$jobCompany     = trim($body['company']         ?? '');
$jobLocation    = trim($body['location']        ?? '');
$jobDescription = trim($body['description']     ?? '');
$employmentType = trim($body['employment_type'] ?? '');

if (empty($jobTitle) || empty($jobDescription)) {
    echo json_encode(['success' => false, 'error' => 'Job title and description are required']);
    exit;
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    "SELECT parsed_text, filename, id
     FROM resumes
     WHERE user_id = ? AND is_active = 1
     ORDER BY uploaded_at DESC
     LIMIT 1"
);
$stmt->execute([$userId]);
$resume = $stmt->fetch();

if (!$resume || empty($resume['parsed_text'])) {
    echo json_encode([
        'success' => false,
        'error'   => 'No resume found. Please upload your resume first.'
    ]);
    exit;
}

$resumeText = $resume['parsed_text'];
$anthropicKey = getenv('CLAUDE_API_KEY');

if (empty($anthropicKey)) {
    echo json_encode(['success' => false, 'error' => 'Server misconfiguration: missing Claude API key']);
    exit;
}

// Build the prompt for a cover letter
// Use HEREDOC for the prompt to avoid quoting issues and allow variable interpolation
$prompt = <<<EOT
You are a professional cover letter writer. Using the candidate's resume and the job posting below, write a concise, persuasive, and professional cover letter tailored to this job. Address it to the hiring manager (or Hiring Manager if unknown). Mention the company name and job title, summarize the candidate's most relevant strengths, include a strong opening and closing, and keep it to about one page.

Here is the candidate's resume:
---
{$resumeText}
---

Job Title: {$jobTitle}
Company: {$jobCompany}
Location: {$jobLocation}
Employment Type: {$employmentType}
Job Description:
{$jobDescription}
---

Return only the cover letter text, with natural spaced out paragraphs and no extra commentary.
EOT;

// Log prompt length for debugging (don't log full resume)
error_log('generate_cover_letter: user_id=' . $userId . ' prompt_len=' . strlen($prompt));

// Prepare request for Claude (Anthropic)
$requestBody = json_encode([
    'model'      => 'claude-haiku-4-5-20251001',
    'max_tokens' => 800,
    'messages'   => [
        ['role' => 'user', 'content' => $prompt]
    ]
]);

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL            => 'https://api.anthropic.com/v1/messages',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $requestBody,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-key: ' . $anthropicKey,
        'anthropic-version: 2023-06-01'
    ]
]);

$response = curl_exec($curl);
$curlErrno = curl_errno($curl);
$curlError = curl_error($curl);
$info = curl_getinfo($curl);
// curl_close deprecated no-op since 8.0; avoid warning on 8.5+
if (defined('PHP_VERSION_ID') && PHP_VERSION_ID < 80500) {
    curl_close($curl);
}

if ($response === false || $curlErrno) {
    error_log('Claude API request failed (cover): ' . $curlError . ' | curl_info: ' . json_encode($info));
    echo json_encode(['success' => false, 'error' => 'API request failed: ' . $curlError]);
    exit;
}

$data = json_decode($response, true);

$generatedCover = null;
if (is_array($data)) {
    if (isset($data['content'][0]['text'])) {
        $generatedCover = $data['content'][0]['text'];
    } elseif (isset($data['completion'])) {
        $generatedCover = $data['completion'];
    } elseif (isset($data['choices'][0]['message']['content'])) {
        $generatedCover = $data['choices'][0]['message']['content'];
    } elseif (isset($data['choices'][0]['text'])) {
        $generatedCover = $data['choices'][0]['text'];
    } elseif (isset($data['output']) && is_string($data['output'])) {
        $generatedCover = $data['output'];
    }
}

if (empty($generatedCover)) {
    error_log('Unexpected Claude response (cover): ' . substr($response, 0, 4000));
    echo json_encode([
        'success'      => false,
        'error'        => 'No response from Claude or unexpected response format',
        'raw_response' => $data
    ]);
    exit;
}

// Find matching job listing in DB
$listingStmt = $pdo->prepare(
    "SELECT id FROM job_listings WHERE user_id = ? AND title = ? AND company = ? LIMIT 1"
);
$listingStmt->execute([$userId, $jobTitle, $jobCompany]);
$listing = $listingStmt->fetch();
$listingId = $listing ? $listing['id'] : null;

// Save to applications table (store cover letter)
if ($listingId) {
    $appStmt = $pdo->prepare(
        "INSERT INTO applications (user_id, listing_id, cover_letter) VALUES (?, ?, ?)"
    );
    try {
        $appStmt->execute([$userId, $listingId, $generatedCover]);
    } catch (Exception $e) {
        // log but don't fail the response
        error_log('Failed to save cover letter: ' . $e->getMessage());
    }
}

echo json_encode([
    'success'                => true,
    'job_title'              => $jobTitle,
    'company'                => $jobCompany,
    'generated_cover_letter' => $generatedCover
]);
