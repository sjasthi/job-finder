<?php
session_start();
header("Content-Type: application/json");
require_once __DIR__ . '/../config/db.php';

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["error" => "Only GET requests are allowed"]);
    exit;
}

$query  = $_GET["query"] ?? "software developer in Minneapolis";
$page   = $_GET["page"]  ?? 1;
$userId = $_SESSION['user_id'] ?? null;

$apiKey = getenv('JSEARCH_API_KEY');

// Use the PDO configured in config/db.php (ensures options/charset are consistent)
// and enforce connection charset just in case.
$pdo->exec("SET NAMES 'utf8mb4'");

if (empty($apiKey)) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Server misconfiguration: missing JSearch API key"]);
    exit;
}

$url = "https://jsearch.p.rapidapi.com/search-v2?query="
     . urlencode($query)
     . "&num_pages=1&country=us&date_posted=all";

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING       => "",
    CURLOPT_MAXREDIRS      => 10,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST  => "GET",
    CURLOPT_HTTPHEADER     => [
        "Content-Type: application/json",
        "x-rapidapi-host: jsearch.p.rapidapi.com",
        "x-rapidapi-key: " . $apiKey
    ],
]);

$response = curl_exec($curl);
$error    = curl_error($curl);

unset($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $error]);
    exit;
}

$data = json_decode($response, true);

// search-v2 returns jobs under $data['data']['jobs']
if (!isset($data["data"]["jobs"]) || !is_array($data["data"]["jobs"])) {
    echo json_encode([
        "success"      => false,
        "message"      => "No job data returned",
        "raw_response" => $data
    ]);
    exit;
}

$jobs = [];

foreach ($data["data"]["jobs"] as $job) {
    $jobRow = [
        "title"           => $job["job_title"]                  ?? "",
        "company"         => $job["employer_name"]              ?? "",
        "location"        => $job["job_location"]               ?? trim(($job["job_city"] ?? "") . ", " . ($job["job_state"] ?? "")),
        "country"         => $job["job_country"]                ?? "",
        "description"     => $job["job_description"]            ?? "",
        "source"          => $job["job_publisher"]              ?? "",
        "employment_type" => $job["job_employment_type"]        ?? "",
        "apply_url"       => $job["job_apply_link"]             ?? "",
        "posted_at"       => $job["job_posted_at_datetime_utc"] ?? "",
        "is_remote"       => $job["job_is_remote"]              ?? false,
        "external_id"     => $job["job_id"]                     ?? "",
    ];

    // Save to job_listings if user is logged in
    if ($userId) {
        $stmt = $pdo->prepare("
            INSERT INTO job_listings
                (user_id, external_id, source_platform, title, company, location, url,
                 employment_type, is_remote, description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE fetched_at = NOW()
        ");
        $desc = function_exists('mb_substr') ? mb_substr($jobRow['description'], 0, 1000, 'UTF-8') : substr($jobRow['description'], 0, 1000);

        $stmt->execute([
            $userId,
            $jobRow['external_id'],
            $jobRow['source'],
            $jobRow['title'],
            $jobRow['company'],
            $jobRow['location'],
            $jobRow['apply_url'],
            $jobRow['employment_type'],
            $jobRow['is_remote'] ? 1 : 0,
            $desc,
        ]);

        $jobRow['listing_id'] = $pdo->lastInsertId();
    }

    $jobs[] = $jobRow;
}

echo json_encode([
    "success" => true,
    "query"   => $query,
    "count"   => count($jobs),
    "jobs"    => $jobs
], JSON_PRETTY_PRINT);