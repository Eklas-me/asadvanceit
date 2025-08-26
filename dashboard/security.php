<?php

/**
 * security.php
 * Restrict site access to specific company WiFi IPs only
 */

$allowedIps = [
  '118.179.87.84', // Office Static IP 1
  '127.0.0.1',    // Localhost IPv4
  '::1'           // Localhost IPv6
];

// Visitor এর IP বের করো
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

// Check করো allow list এ আছে কিনা
if (!in_array($clientIp, $allowedIps)) {
  header('HTTP/1.1 403 Forbidden');
  echo "<h2 style='color:red;'>Access Denied</h2>";
  echo "<p>This site is only accessible from company WiFi.</p>";
  echo "<p>Your IP: <b>" . htmlspecialchars($clientIp) . "</b></p>";
  exit();
}
