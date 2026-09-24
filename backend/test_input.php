<?php
require '/var/www/vendor/autoload.php';

// Simulasi data POST persis seperti yang dikirim JS:
// alat_id[] = 5, jumlah[5] = 9999
$data = [
    'alat_id' => ['5'],
    'jumlah'  => ['5' => '9999'],
];

$request = \Illuminate\Http\Request::create('/test', 'POST', $data);

$val = $request->input('jumlah.5');
echo "input('jumlah.5') = " . var_export($val, true) . "\n";

$intVal = (int)$val;
echo "intVal = $intVal\n";
echo "intVal > 2223? " . ($intVal > 2223 ? "YES - should throw stok error" : "NO - BUG, lolos!") . "\n";
echo "intVal < 1? " . ($intVal < 1 ? "YES - should throw min error" : "NO") . "\n";

echo "\n--- Test key tidak ada ---\n";
$val2 = $request->input('jumlah.99');
echo "input('jumlah.99') = " . var_export($val2, true) . "\n";
echo "(int)null = " . (int)$val2 . "\n";

echo "\n--- Test jumlah minus ---\n";
$data3 = ['alat_id' => ['5'], 'jumlah' => ['5' => '-1']];
$request3 = \Illuminate\Http\Request::create('/test', 'POST', $data3);
$val3 = $request3->input('jumlah.5');
$intVal3 = (int)$val3;
echo "input('jumlah.5') = $val3, intVal = $intVal3\n";
echo "intVal3 < 1? " . ($intVal3 < 1 ? "YES - should throw min error" : "NO - BUG, lolos!") . "\n";
