<?php
require '/var/www/vendor/autoload.php';

// Test 1: opcache
if (function_exists('opcache_get_status')) {
    $s = opcache_get_status();
    echo "opcache: " . ($s !== false ? "ON" : "OFF") . "\n";
} else {
    echo "opcache: function tidak ada\n";
}

// Test 2: Simulasi PERSIS alur ajukanPeminjaman
// Data yang dikirim JS: alat_id[]=5, jumlah[5]=9999
$postData = [
    'tgl_kembali_plan' => date('Y-m-d', strtotime('+1 day')),
    'alat_id' => ['5'],
    'jumlah'  => ['5' => '9999'],
];

$request = \Illuminate\Http\Request::create('/peminjam/peminjaman/ajukan', 'POST', $postData);

echo "\n=== SIMULASI VALIDASI MANUAL LOOP ===\n";
foreach ($request->alat_id as $alatId) {
    $val = $request->input("jumlah.{$alatId}");
    echo "alatId=$alatId, input('jumlah.$alatId') = " . var_export($val, true) . "\n";
    
    if (is_null($val) || $val === '') {
        echo "RESULT: CAUGHT - kosong\n";
        break;
    }
    
    $intVal = (int)$val;
    echo "intVal = $intVal\n";
    
    if ($intVal < 1) {
        echo "RESULT: CAUGHT - minimal 1\n";
        break;
    }
    
    // Simulasi stok = 2223
    $stok = 2223;
    if ($intVal > $stok) {
        echo "RESULT: CAUGHT - stok tidak cukup (diminta $intVal, stok $stok)\n";
        break;
    }
    
    echo "RESULT: LOLOS - BUG!\n";
}

// Test 3: format jumlah array yang diterima PHP dari form POST
echo "\n=== CEK FORMAT ARRAY JUMLAH ===\n";
echo "jumlah raw: ";
var_dump($request->input('jumlah'));
