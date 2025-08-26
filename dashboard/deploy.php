<?php
// Security token check
$secret = "Beb8amQVGE6JaXtgs2u3MQVLQtBKc92f";  // যেকোনো random string দাও
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

// Pull latest code
$output = shell_exec("cd /home/username/public_html/dashboard && git pull origin main 2>&1");

echo "<pre>$output</pre>";
