<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$dashboard = \App\Models\SharedDashboard::latest()->first();
echo json_encode($dashboard->configuration, JSON_PRETTY_PRINT);
