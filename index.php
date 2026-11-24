<?php
require_once 'functions.php';

$layananList = ['Print Hitam', 'Print Warna', 'Fotokopi', 'Jilid Spiral'];

// Seed data (array multidimensi)
$seed = [
    ['Andi Saputra',  'Print Hitam',  40,  false],
    ['Siti Aminah',   'Print Warna',  25,  true],
    ['Budi Santoso',  'Fotokopi',     150, false],
    ['Citra Lestari', 'Jilid Spiral', 20,  true],
    ['Dewi Anggraini','Fotokopi',     60,  true],
];
$daftarTransaksi = [];
foreach ($seed as $i => $s) {
    $daftarTransaksi[] = [
        'id'        => $i + 1,
        'pelanggan' => $s[0],
        'layanan'   => $s[1],
        'jumlah'    => $s[2],
        'member'    => $s[3],
        'total'     => hitungTotalBayar($s[1], $s[2], $s[3]),
    ];
}

// Form POST + validasi
$errors = [];
$sukses = '';
$oldNama = '';
$oldLayanan = '';
$oldJumlah = '';
$oldMember = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldNama    = trim($_POST['nama'] ?? '');
    $oldLayanan = $_POST['layanan'] ?? '';
    $oldJumlah  = $_POST['jumlah'] ?? '';
    $oldMember  = isset($_POST['member']);

    if (strlen($oldNama) < 3) {
        $errors[] = 'Nama pelanggan minimal 3 karakter.';
    }
    if (!in_array($oldLayanan, $layananList)) {
        $errors[] = 'Jenis layanan tidak valid.';
    }
    if (!is_numeric($oldJumlah) || (int)$oldJumlah < 1) {
        $errors[] = 'Jumlah lembar minimal 1.';
    }

    if (empty($errors)) {
        $jumlah = (int)$oldJumlah;
        $daftarTransaksi[] = [
            'id'        => count($daftarTransaksi) + 1,
            'pelanggan' => $oldNama,
            'layanan'   => $oldLayanan,
            'jumlah'    => $jumlah,
            'member'    => $oldMember,
            'total'     => hitungTotalBayar($oldLayanan, $jumlah, $oldMember),
        ];
        $sukses = 'Transaksi atas nama ' . $oldNama . ' berhasil ditambahkan.';
        $oldNama = $oldLayanan = $oldJumlah = '';
        $oldMember = false;
    }
}

// Rekap statistik
$rekap = hitungRekap($daftarTransaksi);

// Filter GET
$fLayanan = $_GET['layanan'] ?? 'semua';
$fCari    = trim($_GET['q'] ?? '');
$tampil = [];
foreach ($daftarTransaksi as $t) {
    if ($fLayanan !== 'semua' && $t['layanan'] !== $fLayanan) continue;
    if ($fCari !== '' && stripos($t['pelanggan'], $fCari) === false) continue;
    $tampil[] = $t;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kasir Percetakan Kampus</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap">
    <h1>Kasir &amp; Rekap Transaksi Percetakan Kampus</h1>

    <div class="stats">
        <div class="card"><small>Total Transaksi</small><b><?= bersihkan((string)$rekap['transaksi']) ?></b></div>
        <div class="card"><small>Total Lembar</small><b><?= bersihkan((string)$rekap['lembar']) ?></b></div>
        <div class="card"><small>Total Pendapatan</small><b><?= bersihkan(rupiah($rekap['omzet'])) ?></b></div>
        <div class="card"><small>Rata-rata / Order</small><b><?= bersihkan(rupiah($rekap['rata'])) ?></b></div>
    </div>

    <div class="grid">
        <div class="box">
            <h2>Form Transaksi</h2>

            <?php if ($errors): ?>
                <div class="alert error"><ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= bersihkan($e) ?></li>
                    <?php endforeach; ?>
                </ul></div>
            <?php endif; ?>
            <?php if ($sukses): ?>
                <div class="alert ok"><?= bersihkan($sukses) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <label>Nama Pelanggan *</label>
                <input type="text" name="nama" value="<?= bersihkan($oldNama) ?>">

                <label>Jenis Layanan *</label>
                <select name="layanan">
                    <option value="">-- Pilih --</option>
                    <?php foreach ($layananList as $l): ?>
                        <option value="<?= bersihkan($l) ?>" <?= $oldLayanan === $l ? 'selected' : '' ?>>
                            <?= bersihkan($l) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Jumlah Lembar *</label>
                <input type="number" name="jumlah" value="<?= bersihkan((string)$oldJumlah) ?>">

                <label class="check">
                    <input type="checkbox" name="member" <?= $oldMember ? 'checked' : '' ?>>
                    Pelanggan Member (+5%)
                </label>

                <button type="submit">+ Proses &amp; Tambah Transaksi</button>
            </form>
        </div>

        <div class="box">
            <h2>Filter &amp; Rekapitulasi</h2>

            <form method="GET" action="index.php" class="filter">
                <select name="layanan">
                    <option value="semua">Semua</option>
                    <?php foreach ($layananList as $l): ?>
                        <option value="<?= bersihkan($l) ?>" <?= $fLayanan === $l ? 'selected' : '' ?>>
                            <?= bersihkan($l) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="q" placeholder="Cari nama..." value="<?= bersihkan($fCari) ?>">
                <button type="submit">Filter</button>
            </form>

            <table>
                <tr>
                    <th>No</th><th>Pelanggan</th><th>Layanan</th>
                    <th>Jumlah</th><th>Member</th><th>Total Bayar</th>
                </tr>
                <?php if (empty($tampil)): ?>
                    <tr><td colspan="6" class="center">Data tidak ditemukan.</td></tr>
                <?php endif; ?>
                <?php $no = 1; foreach ($tampil as $t): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= bersihkan($t['pelanggan']) ?></td>
                        <td><?= bersihkan($t['layanan']) ?></td>
                        <td><?= bersihkan((string)$t['jumlah']) ?></td>
                        <td><?= $t['member'] ? 'Ya (5%)' : 'Tidak' ?></td>
                        <td><?= bersihkan(rupiah($t['total'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>
