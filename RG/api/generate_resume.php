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

// Read JSON body
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

// Get user's active resume text from DB
$stmt = $pdo->prepare("
    SELECT parsed_text, filename, id
    FROM resumes
    WHERE user_id = ? AND is_active = 1
    ORDER BY uploaded_at DESC
    LIMIT 1
");
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

// Build the prompt
$prompt = "You are a professional resume writer. A job seeker needs a tailored resume for a specific job posting.

Here is the candidate's current resume:
---
{$resumeText}
---

Here is the job they are applying for:
Job Title: {$jobTitle}
Company: {$jobCompany}
Location: {$jobLocation}
Employment Type: {$employmentType}
Job Description:
{$jobDescription}
---

Please rewrite and tailor the candidate's resume to best match this job posting. 
- Highlight relevant skills and experience that match the job requirements
- Use keywords from the job description naturally
- Keep the same truthful information but present it in the most relevant way
- Format it cleanly with clear sections: Summary, Experience, Skills, Education
- Keep it to one page worth of content

Return only the resume text, no extra commentary.";

// Call Claude API
$requestBody = json_encode([
    'model'      => 'claude-haiku-4-5-20251001',
    'max_tokens' => 1500,
    'messages'   => [
        ['role' => 'user', 'content' => $prompt]
    ]
]);

// Debug log incoming request
error_log('generate_resume called: user_id=' . ($_SESSION['user_id'] ?? 'null') . ' body=' . substr($requestBody, 0, 2000));

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
    error_log('Claude API request failed (resume): ' . $curlError . ' | curl_info: ' . json_encode($info));
    echo json_encode(['success' => false, 'error' => 'API request failed: ' . $curlError]);
    exit;
}

$data = json_decode($response, true);

// Support multiple possible response shapes from Anthropic/LLM providers
$generatedResume = null;
if (is_array($data)) {
    if (isset($data['content'][0]['text'])) {
        $generatedResume = $data['content'][0]['text'];
    } elseif (isset($data['completion'])) {
        $generatedResume = $data['completion'];
    } elseif (isset($data['choices'][0]['message']['content'])) {
        $generatedResume = $data['choices'][0]['message']['content'];
    } elseif (isset($data['choices'][0]['text'])) {
        $generatedResume = $data['choices'][0]['text'];
    } elseif (isset($data['output']) && is_string($data['output'])) {
        $generatedResume = $data['output'];
    }
}

if (empty($generatedResume)) {
    error_log('Unexpected Claude response (resume): ' . substr($response, 0, 4000));
    echo json_encode([
        'success'      => false,
        'error'        => 'No response from Claude or unexpected response format',
        'raw_response' => $data
    ]);
    exit;
}

// Find matching job listing in DB
$listingStmt = $pdo->prepare("
    SELECT id FROM job_listings
    WHERE user_id = ? AND title = ? AND company = ?
    LIMIT 1
");
$listingStmt->execute([$userId, $jobTitle, $jobCompany]);
$listing = $listingStmt->fetch();
$listingId = $listing ? $listing['id'] : null;

// Save to applications table
if ($listingId) {
    $appStmt = $pdo->prepare("
        INSERT INTO applications (user_id, listing_id, resume_variant)
        VALUES (?, ?, ?)
    ");
    $appStmt->execute([$userId, $listingId, $generatedResume]);
}

echo json_encode([
    'success'          => true,
    'job_title'        => $jobTitle,
    'company'          => $jobCompany,
    'generated_resume' => $generatedResume
]);
