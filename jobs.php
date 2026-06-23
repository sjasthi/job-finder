<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoleGenie | Job Search</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

    <style>
        body {
            background: #f8f7fc;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background: white;
            border-bottom: 1px solid #e8e8e8;
        }

        .brand {
            color: #3C3489;
            font-weight: bold;
            text-decoration: none;
            font-size: 22px;
        }

        .search-box {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-top: 40px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        }

        .job-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 18px;
            border: 1px solid #e8e8e8;
        }

        .job-title {
            color: #3C3489;
            font-weight: 700;
        }

        .source-badge {
            background: #EEEDFE;
            color: #534AB7;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .btn-purple {
            background: #534AB7;
            color: white;
            border: none;
        }

        .btn-purple:hover {
            background: #3C3489;
            color: white;
        }
    </style>
</head>
<body>

<nav class="navbar py-3">
    <div class="container">
        <a href="index.php" class="brand">RoleGenie</a>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">Back Home</a>
    </div>
</nav>

<div class="container">

    <div class="search-box">
        <h2>Search Jobs</h2>
        <p class="text-muted">Search real job listings using the JSearch API.</p>

        <div class="row g-2">
            <div class="col-md-9">
                <input 
                    type="text" 
                    id="jobQuery" 
                    class="form-control form-control-lg" 
                    placeholder="Example: software developer in Minneapolis"
                    value="software developer in Minneapolis"
                >
            </div>

            <div class="col-md-3">
                <button id="searchBtn" class="btn btn-purple btn-lg w-100">
                    Search
                </button>
            </div>
        </div>
    </div>

    <div class="mt-4" id="statusMessage"></div>

    <div class="mt-4" id="jobResults"></div>

</div>

<script>
$(document).ready(function () {

    $("#searchBtn").on("click", function () {
        searchJobs();
    });

    $("#jobQuery").on("keypress", function (e) {
        if (e.which === 13) {
            searchJobs();
        }
    });

    function searchJobs() {
        let query = $("#jobQuery").val().trim();

        if (query === "") {
            alert("Please enter a job search.");
            return;
        }

        $("#statusMessage").html(
            `<div class="alert alert-info">Searching jobs...</div>`
        );

        $("#jobResults").html("");

        $.ajax({
            url: "api/search_jobs.php",
            method: "GET",
            data: {
                query: query
            },
            dataType: "json",
            success: function (response) {

                if (!response.success) {
                    $("#statusMessage").html(
                        `<div class="alert alert-danger">No jobs found or API error.</div>`
                    );
                    return;
                }

                $("#statusMessage").html(
                    `<div class="alert alert-success">Found ${response.count} jobs for "${response.query}".</div>`
                );

                displayJobs(response.jobs);
            },
            error: function () {
                $("#statusMessage").html(
                    `<div class="alert alert-danger">Something went wrong while searching jobs.</div>`
                );
            }
        });
    }

    function displayJobs(jobs) {
        let output = "";

        jobs.forEach(function (job) {
            let description = job.description || "No description available.";

            if (description.length > 300) {
                description = description.substring(0, 300) + "...";
            }

            let remoteText = job.is_remote ? "Remote" : "On-site / Hybrid";

            output += `
                <div class="job-card">
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <div>
                            <h4 class="job-title">${escapeHtml(job.title)}</h4>
                            <p class="mb-1"><strong>Company:</strong> ${escapeHtml(job.company)}</p>
                            <p class="mb-1"><strong>Location:</strong> ${escapeHtml(job.location)}</p>
                            <p class="mb-1"><strong>Type:</strong> ${escapeHtml(job.employment_type || "N/A")}</p>
                            <p class="mb-1"><strong>Work Style:</strong> ${remoteText}</p>
                        </div>

                        <div>
                            <span class="source-badge">${escapeHtml(job.source || "Unknown")}</span>
                        </div>
                    </div>

                    <p class="mt-3 text-muted">${escapeHtml(description)}</p>

                    <div class="d-flex gap-2">
                        <a href="${job.apply_url}" target="_blank" class="btn btn-purple">
                            Apply
                        </a>

                        <button class="btn btn-outline-secondary" disabled>
                            Save Job
                        </button>
                    </div>
                </div>
            `;
        });

        $("#jobResults").html(output);
    }

    function escapeHtml(text) {
        return $("<div>").text(text).html();
    }

});
</script>

</body>
</html>