<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InventarisIoTJaringan;

$vb = new Illuminate\Support\ViewErrorBag();
$vb->put('default', new Illuminate\Support\MessageBag());
view()->share('errors', $vb);

$inventaris = InventarisIoTJaringan::with(['items.komponen'])->get();

try {
    $html = view('admin.inventaris-iot-jaringan.export-pdf', compact('inventaris'))->render();
    echo "PDF template render OK\n";
} catch (\Throwable $e) {
    echo "PDF template render FAIL: " . $e->getMessage() . "\n";
}
