<?php
$secret = "Beb8amQVGE6JaXtgs2u3MQVLQtBKc92f";
$payload = file_get_contents('php://input');
$headers = getallheaders();

if (!isset($headers['X-Hub-Signature-256'])) {
    http_response_code(403);
    die("No signature");
}

$signature = "sha256=" . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($signature, $headers['X-Hub-Signature-256'])) {
    http_response_code(403);
    die("Invalid signature");
}

// Deploy command
$cmd = "
cd /home/asadvanc/public_html/dashboard &&
git fetch --all &&
git reset --hard origin/main 2>&1
";
$output = shell_exec($cmd);

// Logging
file_put_contents("/home/asadvanc/deploy.log", date('Y-m-d H:i:s') . "\n" . $output . "\n---\n", FILE_APPEND);

echo "<pre>$output</pre>";
?>
