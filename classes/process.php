<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $urls = $_POST["urls"];

    $urlsArray = explode("\n", $urls);
    $results = [];

    foreach ($urlsArray as $url) {
        $url = trim($url);

        if (!empty($url)) {
            $ip = gethostbyname($url);
            $port = 80;
            $connection = @fsockopen($ip, $port, $errorCode, $errorMessage, 2);

            if ($connection) {
                $results[$url] = [
                    "ip" => $ip,
                    "port" => $port,
                    "status" => "open"
                ];
                fclose($connection);
            } else {
                $results[$url] = [
                    "ip" => $ip,
                    "port" => $port,
                    "status" => "close"
                ];
            }
        }
    }

    // Redirecciona a la página de éxito con los resultados
    header("Location: ../success.php?results=" . urlencode(serialize($results)));
    exit();
}
?>
