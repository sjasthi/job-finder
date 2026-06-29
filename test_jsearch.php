<?php

$apiKey = "";

$url = "https://jsearch.p.rapidapi.com/search?query=software+developer+in+minneapolis&page=1&num_pages=1";

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        "X-RapidAPI-Key: $apiKey",
        "X-RapidAPI-Host: jsearch.p.rapidapi.com"
    ]
]);

$response = curl_exec($curl);
$error = curl_error($curl);



if ($error) {
    echo "cURL Error: " . $error;
} else {

    $data = json_decode($response, true);

    echo "<h2>JSearch API Test</h2>";

    if (isset($data['data'])) {

        foreach ($data['data'] as $job) {

            echo "<hr>";

            echo "<strong>Title:</strong> "
                . htmlspecialchars($job['job_title'] ?? 'N/A')
                . "<br>";

            echo "<strong>Company:</strong> "
                . htmlspecialchars($job['employer_name'] ?? 'N/A')
                . "<br>";

            echo "<strong>Location:</strong> "
                . htmlspecialchars($job['job_city'] ?? 'N/A')
                . "<br>";

            echo "<strong>Source:</strong> "
                . htmlspecialchars($job['job_publisher'] ?? 'N/A')
                . "<br>";

            echo "<strong>Apply:</strong> ";

            if (!empty($job['job_apply_link'])) {
                echo "<a href='" .
                    htmlspecialchars($job['job_apply_link']) .
                    "' target='_blank'>Apply Here</a>";
            }

            echo "<br>";
        }

    } else {

        echo "<pre>";
        print_r($data);
        echo "</pre>";

    }
}
?>
