<?php
header("Content-Type: application/json");

// Allow only GET requests
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["error" => "Only GET requests are allowed"]);
    exit;
}

// Get search query from URL
$query = $_GET["query"] ?? "software developer in Minneapolis";
$page = $_GET["page"] ?? 1;

// Your RapidAPI key
$apiKey = "06a2983509msh6e79e5b3b33fa82p1f7165jsnc9a64793b429";

$url = "https://jsearch.p.rapidapi.com/search?query=" . urlencode($query) . "&page=" . urlencode($page) . "&num_pages=1";

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        "X-RapidAPI-Key: " . $apiKey,
        "X-RapidAPI-Host: jsearch.p.rapidapi.com"
    ]
]);

$response = curl_exec($curl);
$error = curl_error($curl);

if ($error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => $error
    ]);
    exit;
}

$data = json_decode($response, true);

if (!isset($data["data"])) {
    echo json_encode([
        "success" => false,
        "message" => "No job data returned",
        "raw_response" => $data
    ]);
    exit;
}

$jobs = [];

foreach ($data["data"] as $job) {
    $jobs[] = [
        "title" => $job["job_title"] ?? "",
        "company" => $job["employer_name"] ?? "",
        "location" => trim(($job["job_city"] ?? "") . ", " . ($job["job_state"] ?? "")),
        "country" => $job["job_country"] ?? "",
        "description" => $job["job_description"] ?? "",
        "source" => $job["job_publisher"] ?? "",
        "employment_type" => $job["job_employment_type"] ?? "",
        "apply_url" => $job["job_apply_link"] ?? "",
        "posted_at" => $job["job_posted_at_datetime_utc"] ?? "",
        "is_remote" => $job["job_is_remote"] ?? false
    ];
}

echo json_encode([
    "success" => true,
    "query" => $query,
    "count" => count($jobs),
    "jobs" => $jobs
], JSON_PRETTY_PRINT);