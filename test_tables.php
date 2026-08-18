<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$db = Illuminate\Support\Facades\DB::connection();
$tables = $db->select("SELECT name FROM sqlite_master WHERE type='table' AND name IN ('komponen_iot', 'komponen_jaringan')");
foreach ($tables as $table) {
    echo "Table exists: " . $table->name . PHP_EOL;
    $cols = $db->select("PRAGMA table_info(" . $table->name . ")");
    foreach ($cols as $col) {
        echo "  - " . $col->name . PHP_EOL;
    }
}
