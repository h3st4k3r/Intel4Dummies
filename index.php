<?php

function sanitizeUrl($url) {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $sanitizedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

    return $sanitizedUrl;
}

if (isset($_POST["urls"])) {

    $rawUrls = $_POST["urls"];
    $rawUrls = explode("\n", $rawUrls);
    $rawUrls = array_map('trim', $rawUrls);

    $sanitizedUrls = array_filter(array_map('sanitizeUrl', $rawUrls));

    $response = "OK.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Intel4dummies</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 62%;
            margin: 0 auto;
            margin-top: 100px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center">Intel4dummies: Search domains</h1>
    <form action="./classes/process.php" method="post">
        <div class="mb-3">
            <label for="urls" class="form-label">URLs (One per line):</label>
            <textarea id="urls" name="urls" rows="5" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Check</button>
    </form>
</div>
</body>
</html>
