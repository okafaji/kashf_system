<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel');

use App\Models\Signature;
use Illuminate\Database\Capsule\Manager as DB;

DB::connection('mysql');

$sigs = Signature::all();
echo "عدد التواقيع: " . count($sigs) . "\n";
echo "========================\n";

foreach($sigs as $sig) {
  echo "العنوان (Title): {$sig->title}\n";
  echo "الاسم (Name): {$sig->name}\n";
  echo "نشط (Active): " . ($sig->is_active ? 'نعم' : 'لا') . "\n";
  echo "------------------------\n";
}
