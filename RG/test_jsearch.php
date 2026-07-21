<?php

$apiKey = "";

$url = "https://jsearch.p.rapidapi.com/search-v2?query=software+developer+in+minneapolis&num_pages=1&country=us&date_posted=all";

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "x-rapidapi-host: jsearch.p.rapidapi.com",
        "x-rapidapi-key: $apiKey"
    ],
]);

$response = curl_exec($curl);
$error = curl_error($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

curl_close($curl);

echo "<h2>JSearch API Test</h2>";
echo "HTTP Code: " . $httpCode . "<br>";

if ($error) {
    echo "cURL Error: " . htmlspecialchars($error);
    exit;
}

$data = json_decode($response, true);

if (isset($data['data']['jobs']) && is_array($data['data']['jobs'])) {
    foreach ($data['data']['jobs'] as $job) {
        echo "<hr>";

        echo "<strong>Title:</strong> "
            . htmlspecialchars($job['job_title'] ?? 'N/A')
            . "<br>";

        echo "<strong>Company:</strong> "
            . htmlspecialchars($job['employer_name'] ?? 'N/A')
            . "<br>";

        echo "<strong>Location:</strong> "
            . htmlspecialchars($job['job_location'] ?? 'N/A')
            . "<br>";

        echo "<strong>Source:</strong> "
            . htmlspecialchars($job['job_publisher'] ?? 'N/A')
            . "<br>";

        echo "<strong>Apply:</strong> ";

        if (!empty($job['job_apply_link'])) {
            echo "<a href='"
                . htmlspecialchars($job['job_apply_link'])
                . "' target='_blank'>Apply Here</a>";
        } else {
            echo "N/A";
        }

        echo "<br>";
    }
} else {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

?>
