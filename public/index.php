<?php
declare(strict_types=1);
require __DIR__ . '/../app/Core/App.php';
$app = new \App\Core\App(__DIR__ . '/..');
$app->bootstrap();
$app->run();
