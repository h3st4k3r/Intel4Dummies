<!DOCTYPE html>
<html>
<head>
    <title>Intel4dummies</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        .table {
            width: 95%;
            border-collapse: collapse;
            margin: 2% 2.5%;
        }

        th, td {
            padding: 4px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            vertical-align: middle !important;
        }

        th{
            text-align: center;
        }



        .whois-cell {
            white-space: pre-wrap;
            overflow: hidden;
            max-height: 100px;
            transition: max-height 0.5s ease;
        }

        .whois-cell.expanded {
            max-height: none;
        }

        .toggle-button {
            cursor: pointer;
            display: inline-block;
            font-size: 14px;
        }

        .toggle-button::after {
            content: '\25BC';
            display: inline-block;
            margin-left: 5px;
            transform: rotate(-90deg);
            transition: transform 0.3s ease;
        }

        .toggle-button.expanded::after {
            transform: rotate(0);
        }
        h2 {
            text-align: center;
            margin-top: 2%;
        }
    </style>
</head>
<body>
<?php

if (isset($_GET["results"])) {
    $results = unserialize(urldecode($_GET["results"]));

    if (!empty($results)) {
        echo "<h2>Results:</h2>";
        echo "<table class='table'>";
        echo "<thead><tr><th>URL</th><th>IP Address</th><th>Port 80</th><th>Status</th><th>DNS</th><th>Geolocation</th><th>Registrar</th><th>Whois</th></tr></thead>";
        echo "<tbody>";

        foreach ($results as $url => $result) {
            $ip = $result["ip"];
            $port = $result["port"];
            $status = $result["status"];

            echo "<tr>";
            echo "<td>{$url}</td>";
            echo "<td>{$ip}</td>";
            echo "<td>{$port}</td>";
            echo "<td>{$status}</td>";
            echo "<td>" . getDNSInfo($url) . "</td>";
            echo "<td>" . getGeolocation($ip) . "</td>";
            echo "<td>" . getRegistrar($url) . "</td>";
            echo "<td>";
            echo "<div class='whois-cell'>";
            echo "<pre>" . whois_query($url) . "</pre>";
            echo "</div>";
            echo "<div class='toggle-button' onclick='toggleWhois(this)'></div>";
            echo "</td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<p class='text-center'>We can't get results.</p>";
    }
}
?>

<script>
    function toggleWhois(button) {
        var cell = button.previousElementSibling;
        cell.classList.toggle("expanded");
        button.classList.toggle("expanded");
    }
</script>

<?php

function whois_query($domain) {
    $whoisServer = "whois.internic.net";

    $query = $domain."\r\n";

    $whoisSocket = fsockopen($whoisServer, 43);
    if (!$whoisSocket) {
        return false;
    }

    fputs($whoisSocket, $query);

    $response = "";
    while (!feof($whoisSocket)) {
        $response .= fgets($whoisSocket, 128);
    }

    fclose($whoisSocket);

    $whoisData = "<pre>" . $response . "</pre>";

    // Obtener el registrador del WHOIS
    $registrarStart = strpos($response, "Registrar:");
    if ($registrarStart !== false) {
        $registrarStart += strlen("Registrar:");
        $registrarEnd = strpos($response, "\n", $registrarStart);
        if ($registrarEnd !== false) {
            $registrar = substr($response, $registrarStart, $registrarEnd - $registrarStart);
            $whoisData .= "<br><strong>Registrar:</strong> " . trim($registrar);
        }
    }

    return $whoisData;
}

function getDNSInfo($url) {
    $dnsInfo = dns_get_record($url, DNS_ALL);
    $result = "";

    if (!empty($dnsInfo)) {
        foreach ($dnsInfo as $record) {
            $result .= "<strong>Type:</strong> " . $record["type"] . "<br>";
            $result .= "<strong>Target:</strong> " . $record["target"] . "<br>";
            $result .= "<strong>TTL:</strong> " . $record["ttl"] . "<br>";
            $result .= "<br>";
        }
    } else {
        $result = "No DNS records found";
    }

    return $result;
}


function getGeolocation($ip) {
    $geolocationInfo = file_get_contents("https://geolocation-db.com/jsonp/".$ip);
    $geolocationInfo = str_replace("callback(", "", $geolocationInfo);
    $geolocationInfo = str_replace("})", "}", $geolocationInfo);
    $geolocationData = json_decode($geolocationInfo, true);

    $result = "";
    if (!empty($geolocationData)) {
        $result .= "<strong>IP:</strong> " . $geolocationData["IPv4"] . "<br>";
        $result .= "<strong>Country:</strong> " . $geolocationData["country_name"] . "<br>";
        $result .= "<strong>Region:</strong> " . $geolocationData["state"] . "<br>";
        $result .= "<strong>City:</strong> " . $geolocationData["city"] . "<br>";
        $result .= "<strong>Latitude:</strong> " . $geolocationData["latitude"] . "<br>";
        $result .= "<strong>Longitude:</strong> " . $geolocationData["longitude"] . "<br>";
    } else {
        $result = $geolocationInfo;
    }

    return $result;
}

function getRegistrar($url) {
    $whoisInfo = whois_query($url);
    $result = "";

    if ($whoisInfo !== false) {
        preg_match("/Registrar: (.*?)\n/", $whoisInfo, $matches);
        if (isset($matches[1])) {
            $result = $matches[1];
        } else {
            $result = "Registrar information not found";
        }
    } else {
        $result = "Failed to retrieve registrar information";
    }

    return $result;
}
?>
</body>
</html>
