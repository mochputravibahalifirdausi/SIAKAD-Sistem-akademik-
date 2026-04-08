<?php
session_start();

// =========================================================================
// BAGIAN 1: LOGIKA OOP
// =========================================================================

interface CetakLaporan {
    public function cetakKHS();
}

abstract class User {
    protected $id;
    protected $nama;
    public function __construct($id, $nama) { $this->id = $id; $this->nama = $nama; }
    abstract public function getPeran();
    public function getNama() { return $this->nama; }
    public function getId() { return $this->id; }
}

class Dosen extends User {
    public function getPeran() { return "Dosen"; }
}

class Mahasiswa extends User implements CetakLaporan {
    private $jurusan;
    private $daftarNilai = [];
    private $dosenWali; 

    public function __construct($nim, $nama, $jurusan, $dosenWali = null, $nilai = []) {
        parent::__construct($nim, $nama);
        $this->jurusan = $jurusan;
        $this->dosenWali = $dosenWali; 
        $this->daftarNilai = $nilai;
    }

    public function getPeran() { return "Mahasiswa"; }

    private function hitungIPK() {
        $totalSKS = 0; $totalMutu = 0;
        foreach ($this->daftarNilai as $n) {
            $totalSKS += $n['sks'];
            $totalMutu += ($n['sks'] * $n['am']);
        }
        $ipk = ($totalSKS > 0) ? ($totalMutu / $totalSKS) : 0;
        return ['sks' => $totalSKS, 'mutu' => $totalMutu, 'ipk' => min($ipk, 4.0)];
    }

    public function cetakKHS() {
        $hasil = $this->hitungIPK();
        $namaDosenWali = ($this->dosenWali != null) ? $this->dosenWali->getNama() : "Belum Ditentukan";
        $nidnDosenWali = ($this->dosenWali != null) ? $this->dosenWali->getId() : "-";
        
        $html = "<div class='khs-print-area'>";
        $html .= "<div class='kop-surat' style='text-align:center; margin-bottom: 25px;'>";
        $html .= "<img src='https://publikasi.polije.ac.id/public/site/images/adhyatma/logo-gabung-putih.png' class='logo-print-khs'>";
        $html .= "<h2 style='margin:10px 0 0 0; color:black;'>POLITEKNIK NEGERI JEMBER</h2>";
        $html .= "</div>";

        $html .= "<div class='info-mhs-khs'><table>";
        $html .= "<tr><td>NAMA</td><td>: " . strtoupper($this->nama) . "</td><td style='text-align:right; width:40%;'><strong>SEMESTER GANJIL 2025/2026</strong></td></tr>";
        $html .= "<tr><td>NIM</td><td>: " . $this->id . "</td></tr>";
        $html .= "<tr><td>PROGRAM STUDI</td><td>: " . $this->jurusan . "</td></tr>";
        $html .= "<tr><td>DOSEN WALI</td><td>: " . strtoupper($namaDosenWali) . "</td></tr>";
        $html .= "</table></div>";
        
        $html .= "<table class='tabel-nilai-khs'><thead><tr><th>No</th><th>Kode</th><th>Mata Kuliah</th><th>HM</th><th>AM</th><th>K</th><th>M</th></tr></thead><tbody>";
        $no = 1;
        foreach ($this->daftarNilai as $n) {
            $m = $n['sks'] * $n['am'];
            $html .= "<tr><td>{$no}</td><td>{$n['kode']}</td><td style='text-align:left;'>{$n['matkul']}</td><td>{$n['hm']}</td><td>{$n['am']}</td><td>{$n['sks']}</td><td>{$m}</td></tr>";
            $no++;
        }
        $html .= "<tr><td colspan='5' style='text-align:center;'>Jumlah</td><td>{$hasil['sks']}</td><td>{$hasil['mutu']}</td></tr>";
        $html .= "<tr><td colspan='5' style='text-align:center; font-weight:bold;'>Nilai Mutu Rata-Rata (IPK)</td><td colspan='2' style='font-weight:bold;'>".number_format($hasil['ipk'], 2)."</td></tr>";
        $html .= "</tbody></table>";

        $html .= "<div class='footer-khs-print'>";
        $html .= "<div class='ttd'>Mengetahui,<br><strong>DOSEN WALI</strong><br><br><br><br>{$namaDosenWali}<br>NIDN. {$nidnDosenWali}</div>";
        $html .= "<div class='ttd'>Jember, 26-01-2026<br><strong>KETUA JURUSAN BISNIS</strong><br><br><br><br>Dessy Putri Andini, SE., MM.<br>NIP. 198001012005011001</div>";
        $html .= "</div></div>";

        $html .= "<div class='no-print' style='text-align:center; margin-top:20px;'><button onclick='window.print()' class='btn-print'>🖨️ Cetak PDF KHS Lengkap</button></div>";
        return $html;
    }
}

// =========================================================================
// BAGIAN 2: LOGIKA CRUD SESSION (FIXED DELETE)
// =========================================================================

if (!isset($_SESSION['data_dosen'])) {
    $_SESSION['data_dosen'] = ['10102025' => ['nama' => 'Eka Yuniar, S.Kom., MMSI']];
}
if (!isset($_SESSION['data_matkul'])) {
    $_SESSION['data_matkul'] = ['BSD210801' => ['nama' => 'Agama', 'sks' => 2], 'BSD210809' => ['nama' => 'Pemrograman Basis Data', 'sks' => 4]];
}
if (!isset($_SESSION['data_mahasiswa'])) {
    $_SESSION['data_mahasiswa'] = ['143250380' => ['nama' => 'Ahmad Fauzen Alfarosi', 'jurusan' => 'Bisnis Digital', 'dosen_wali' => '10102025', 'nilai' => []]];
}

// LOGIKA HAPUS (DELETE)
if (isset($_GET['del_dosen'])) { unset($_SESSION['data_dosen'][$_GET['del_dosen']]); header("Location: ?page=dosen"); exit; }
if (isset($_GET['del_matkul'])) { unset($_SESSION['data_matkul'][$_GET['del_matkul']]); header("Location: ?page=matkul"); exit; }
if (isset($_GET['del_mhs'])) { unset($_SESSION['data_mahasiswa'][$_GET['del_mhs']]); header("Location: ?page=mahasiswa"); exit; }
if (isset($_GET['del_nilai'])) { 
    unset($_SESSION['data_mahasiswa'][$_GET['del_nilai']]['nilai'][$_GET['idx']]); 
    $_SESSION['data_mahasiswa'][$_GET['del_nilai']]['nilai'] = array_values($_SESSION['data_mahasiswa'][$_GET['del_nilai']]['nilai']); 
    header("Location: ?page=nilai"); exit; 
}

// LOGIKA TAMBAH (CREATE)
if (isset($_POST['tambah_dosen'])) { $_SESSION['data_dosen'][$_POST['nidn']] = ['nama' => $_POST['nama']]; header("Location: ?page=dosen"); exit; }
if (isset($_POST['tambah_matkul'])) { $_SESSION['data_matkul'][$_POST['kode']] = ['nama' => $_POST['nama_mk'], 'sks' => $_POST['sks']]; header("Location: ?page=matkul"); exit; }
if (isset($_POST['tambah_mhs'])) { $_SESSION['data_mahasiswa'][$_POST['nim']] = ['nama' => $_POST['nama'], 'jurusan' => $_POST['jurusan'], 'dosen_wali' => $_POST['dosen_wali'], 'nilai' => []]; header("Location: ?page=mahasiswa"); exit; }
if (isset($_POST['tambah_nilai'])) {
    $nim = $_POST['nim_target']; $kode = $_POST['kode_mk'];
    $_SESSION['data_mahasiswa'][$nim]['nilai'][] = ['kode' => $kode, 'matkul' => $_SESSION['data_matkul'][$kode]['nama'], 'sks' => $_SESSION['data_matkul'][$kode]['sks'], 'hm' => $_POST['hm'], 'am' => $_POST['am']];
    header("Location: ?page=nilai"); exit;
}
if (isset($_GET['reset_app'])) { session_destroy(); header("Location: index.php"); exit; }

$page = $_GET['page'] ?? 'home';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SIAKAD MINI - FINAL VERSION</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
        body { margin: 0; padding: 20px; font-family: 'Poppins', sans-serif; min-height: 100vh; color: #FFF8E7; background: linear-gradient(-45deg, #12284B, #0055A0, #438BC4, #8CC1E9); background-size: 400% 400%; animation: ocean 15s ease infinite; }
        @keyframes ocean { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .glass-box { background: rgba(255, 248, 231, 0.05); backdrop-filter: blur(15px); border: 1px solid rgba(255, 248, 231, 0.2); border-radius: 20px; padding: 40px; width: 100%; max-width: 1150px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); margin: 20px auto; }
        .header-title { display: flex; align-items: center; justify-content: center; gap: 15px; font-size: 2.8rem; margin-bottom: 5px; }
        .logo-polije { height: 70px; filter: drop-shadow(2px 4px 6px rgba(0,0,0,0.3)); }
        .menu-nav { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-bottom: 30px; }
        .menu-nav a { text-decoration: none; color: white; padding: 10px 18px; background: rgba(0,85,160,0.5); border-radius: 10px; border: 1px solid #438BC4; font-size: 0.95rem; }
        .menu-nav a.active { background: #438BC4; }
        form { background: rgba(18, 40, 75, 0.4); padding: 25px; border-radius: 15px; margin-bottom: 20px; }
        input, select, button { width: 100%; padding: 12px; margin: 5px 0 15px 0; border-radius: 8px; border: none; font-family: inherit;}
        .btn-save { background: #0055A0; color: white; font-weight: bold; cursor: pointer; }
        .btn-print { background: #2ecc71; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .default-table { width: 100%; border-collapse: collapse; background: rgba(18, 40, 75, 0.6); border-radius: 10px; overflow: hidden; margin-top: 20px; }
        .default-table th, .default-table td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center; }
        .default-table th { background: rgba(0, 85, 160, 0.8); }
        .khs-print-area { background: white; color: black; padding: 40px; border-radius: 10px; font-family: 'Times New Roman', serif; }
        .logo-print-khs { height: 90px; filter: drop-shadow(0px 0px 1px black); }
        .tabel-nilai-khs { width: 100%; border-collapse: collapse; margin-top: 20px; color: black; }
        .tabel-nilai-khs th, .tabel-nilai-khs td { border: 1px solid black; padding: 8px; text-align: center; }
        .tabel-nilai-khs th { background: #d1d8e0; }
        .footer-khs-print { display: flex; justify-content: space-between; margin-top: 30px; color: black; }
        .ttd { text-align: center; width: 35%; }
        @media print {
            body { background: white; animation: none; padding: 0; }
            .no-print, .menu-nav, form, h2, h3, header { display: none !important; }
            .glass-box { background: none; border: none; padding: 0; width: 100%; box-shadow: none; }
            .khs-print-area { padding: 0; }
            .logo-print-khs { filter: none !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
<div class="glass-box">
    <header class="no-print">
        <h1 class="header-title"><img src="https://publikasi.polije.ac.id/public/site/images/adhyatma/logo-gabung-putih.png" class="logo-polije"> Bisnis Digital</h1>
        <p style="text-align: center;">Politeknik Negeri Jember Kampus 2 Bondowoso</p>
    </header>

    <div class="menu-nav no-print">
        <a href="?page=home" class="<?= $page=='home'?'active':'' ?>">🏠 Dashboard</a>
        <a href="?page=dosen" class="<?= $page=='dosen'?'active':'' ?>">👨‍🏫 CRUD Dosen</a>
        <a href="?page=matkul" class="<?= $page=='matkul'?'active':'' ?>">📚 CRUD Matkul</a>
        <a href="?page=mahasiswa" class="<?= $page=='mahasiswa'?'active':'' ?>">👥 CRUD Mahasiswa</a>
        <a href="?page=nilai" class="<?= $page=='nilai'?'active':'' ?>">📝 Input Nilai</a>
        <a href="?page=khs" class="<?= $page=='khs'?'active':'' ?>">📄 Cetak KHS</a>
    </div>

    <?php if ($page == 'dosen'): ?>
        <h2>Manajemen Dosen</h2>
        <form method="POST"><input type="text" name="nidn" placeholder="NIDN..." required><input type="text" name="nama" placeholder="Nama Dosen..." required><button type="submit" name="tambah_dosen" class="btn-save">+ Tambah Dosen</button></form>
        <table class="default-table"><tr><th>NIDN</th><th>Nama</th><th>Aksi</th></tr><?php foreach ($_SESSION['data_dosen'] as $id => $d): ?><tr><td><?= $id ?></td><td><?= $d['nama'] ?></td><td><a href="?page=dosen&del_dosen=<?= $id ?>" style="color:red;" onclick="return confirm('Hapus?')">Hapus</a></td></tr><?php endforeach; ?></table>

    <?php elseif ($page == 'matkul'): ?>
        <h2>Manajemen Mata Kuliah</h2>
        <form method="POST"><input type="text" name="kode" placeholder="Kode..." required><input type="text" name="nama_mk" placeholder="Matkul..." required><input type="number" name="sks" placeholder="SKS..." required><button type="submit" name="tambah_matkul" class="btn-save">+ Tambah Matkul</button></form>
        <table class="default-table"><tr><th>Kode</th><th>Nama</th><th>SKS</th><th>Aksi</th></tr><?php foreach ($_SESSION['data_matkul'] as $id => $m): ?><tr><td><?= $id ?></td><td><?= $m['nama'] ?></td><td><?= $m['sks'] ?></td><td><a href="?page=matkul&del_matkul=<?= $id ?>" style="color:red;" onclick="return confirm('Hapus?')">Hapus</a></td></tr><?php endforeach; ?></table>

    <?php elseif ($page == 'mahasiswa'): ?>
        <h2>Manajemen Mahasiswa</h2>
        <form method="POST"><input type="text" name="nim" required placeholder="NIM..."><input type="text" name="nama" required placeholder="Nama..."><input type="text" name="jurusan" required placeholder="Prodi..."><select name="dosen_wali" required><option value="">-- Pilih Dosen Wali --</option><?php foreach ($_SESSION['data_dosen'] as $id => $d): ?><option value="<?= $id ?>"><?= $d['nama'] ?></option><?php endforeach; ?></select><button type="submit" name="tambah_mhs" class="btn-save">+ Tambah Mahasiswa</button></form>
        <table class="default-table"><tr><th>NIM</th><th>Nama</th><th>Dosen Wali</th><th>Aksi</th></tr><?php foreach ($_SESSION['data_mahasiswa'] as $nim => $mhs): ?><tr><td><?= $nim ?></td><td><?= $mhs['nama'] ?></td><td><?= $_SESSION['data_dosen'][$mhs['dosen_wali']]['nama'] ?? '-' ?></td><td><a href="?page=mahasiswa&del_mhs=<?= $nim ?>" style="color:red;" onclick="return confirm('Hapus?')">Hapus</a></td></tr><?php endforeach; ?></table>

    <?php elseif ($page == 'nilai'): ?>
        <h2>Input Nilai</h2>
        <form method="POST"><select name="nim_target" required><option value="">-- Pilih Mhs --</option><?php foreach ($_SESSION['data_mahasiswa'] as $nim => $mhs): ?><option value="<?= $nim ?>"><?= $mhs['nama'] ?></option><?php endforeach; ?></select><select name="kode_mk" required><option value="">-- Pilih Matkul --</option><?php foreach ($_SESSION['data_matkul'] as $kode => $mk): ?><option value="<?= $kode ?>"><?= $mk['nama'] ?></option><?php endforeach; ?></select><div style="display:flex; gap:10px;"><input type="text" name="hm" placeholder="HM (A/B)" required><input type="number" step="0.1" max="4" name="am" placeholder="AM (Max 4.0)" required></div><button type="submit" name="tambah_nilai" class="btn-save">Simpan</button></form>
        <h3>Riwayat Nilai</h3>
        <table class="default-table"><tr><th>Mhs</th><th>Matkul</th><th>HM</th><th>AM</th><th>Aksi</th></tr><?php foreach ($_SESSION['data_mahasiswa'] as $nim => $mhs): foreach ($mhs['nilai'] as $idx => $n): ?><tr><td><?= $mhs['nama'] ?></td><td><?= $n['matkul'] ?></td><td><?= $n['hm'] ?></td><td><?= $n['am'] ?></td><td><a href="?page=nilai&del_nilai=<?= $nim ?>&idx=<?= $idx ?>" style="color:red;" onclick="return confirm('Hapus?')">Hapus</a></td></tr><?php endforeach; endforeach; ?></table>

    <?php elseif ($page == 'khs'): ?>
        <form method="GET" class="no-print"><input type="hidden" name="page" value="khs"><select name="nim_cetak" required><option value="">-- Pilih Mhs --</option><?php foreach ($_SESSION['data_mahasiswa'] as $nim => $mhs): ?><option value="<?= $nim ?>"><?= $mhs['nama'] ?></option><?php endforeach; ?></select><button type="submit" class="btn-save">Lihat KHS</button></form>
        <?php if (isset($_GET['nim_cetak'])): $d = $_SESSION['data_mahasiswa'][$_GET['nim_cetak']]; $obj = new Mahasiswa($_GET['nim_cetak'], $d['nama'], $d['jurusan'], new Dosen($d['dosen_wali'], $_SESSION['data_dosen'][$d['dosen_wali']]['nama']), $d['nilai']); echo $obj->cetakKHS(); endif; ?>
    <?php else: ?>
        <h2 style="text-align: center;">Selamat Datang di SIAKAD</h2>
        <div style="text-align:center; margin-top:30px;"><a href="?reset_app=true" style="color:orange;">🔄 Reset Data</a></div>
    <?php endif; ?>
</div>
</body>
</html>