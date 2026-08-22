<?php
header('Content-Type: application/json');
if (AiRouter::available()) AiRouter::warmAsync();
echo json_encode(['ok' => true]);
