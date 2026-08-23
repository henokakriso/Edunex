<?php
/**
 * Upload API: file upload to storage/uploads/api
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_auth.php';

$u = api_user();
api_rate_limit($u, 'upload', 10, 60); // 10 uploads/min
if ($_SERVER['REQUEST_METHOD'] !== 'POST') api_out(['ok' => false, 'error' => 'method'], 405);
if (empty($_FILES['file'])) api_out(['ok' => false, 'error' => 'no_file'], 400);

$safeExts = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','ppt','pptx','txt','csv','md','zip','mp3','wav','mp4','webm'];
$res = upload_file($_FILES['file'], 'api', $safeExts);
if ($res['error']) api_out(['ok' => false, 'error' => $res['error']], 400);
api_out(['ok' => true, 'path' => $res['path'], 'size' => $res['size'], 'url' => url('file&p=' . urlencode($res['path']))]);
