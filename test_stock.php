<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$raw = \App\Models\RawMaterial::first();
if($raw) {
    echo "Raw material before: " . $raw->stock . "\n";
    $raw->stock = 0;
    $raw->save();
    echo "Raw material after: " . $raw->stock . "\n";
    $menus = \App\Models\Menu::whereIn('id', \App\Models\Recipe::where('raw_material_id', $raw->id)->pluck('menu_id'))->get();
    foreach($menus as $menu) {
        echo "Menu " . $menu->name . " is_available: " . $menu->is_available . "\n";
    }
}
