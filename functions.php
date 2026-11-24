<?php
function bersihkan(string $teks): string {
    return htmlspecialchars(trim($teks), ENT_QUOTES, 'UTF-8');
}

function rupiah(float|int $angka): string {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function hitungTotalBayar(string $layanan, int $jumlah, bool $isMember = false): float {
    switch ($layanan) {
        case 'Print Hitam':  $tarif = 500;   break;
        case 'Print Warna':  $tarif = 1500;  break;
        case 'Fotokopi':     $tarif = 300;   break;
        case 'Jilid Spiral': $tarif = 10000; break;
        default:             $tarif = 0;
    }
    $subtotal = $tarif * $jumlah;
    $diskon = ($jumlah > 100) ? 0.10 : 0.0;
    if ($isMember) {
        $diskon += 0.05;
    }
    return $subtotal * (1 - $diskon);
}

function hitungRekap(array $daftarTransaksi): array {
    $jumlahTrx = count($daftarTransaksi);
    $totalLembar = 0;
    $totalOmzet = 0;
    foreach ($daftarTransaksi as $t) {
        $totalLembar += $t['jumlah'];
        $totalOmzet  += $t['total'];
    }
    return [
        'transaksi' => $jumlahTrx,
        'lembar'    => $totalLembar,
        'omzet'     => $totalOmzet,
        'rata'      => $jumlahTrx > 0 ? $totalOmzet / $jumlahTrx : 0,
    ];
}
