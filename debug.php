<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$input = file_get_contents('php://input');
echo json_encode([
  'method' => $_SERVER['REQUEST_METHOD'],
  'uri' => $_SERVER['REQUEST_URI'],
  'input_length' => strlen($input),
  'input' => $input,
]);
