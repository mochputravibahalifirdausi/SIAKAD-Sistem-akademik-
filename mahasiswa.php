<?php
// File: mahasiswa.php
require_once 'user.php';
require_once 'cetaklaporan.php';

class Mahasiswa extends User implements CetakLaporan {
    private $nim;
    private $jurusan;
    private $daftarNilai = [];
    private $ipk = 0;

    public function __construct($nim, $nama, $jurusan) {
        parent::__construct($nim, $nama);
        $this->nim = $nim;
        $this->jurusan = $jurusan;
    }

    public function getPeran() { return "Mahasiswa"; }

    public function tambahNilai($kode, $matkul, $sks, $hurufMutu, $angkaMutu) {
        $mutu = $sks * $angkaMutu;
        $this->daftarNilai[] = [
            'kode' => $kode, 'matkul' => $matkul, 'sks' => $sks, 
            'hurufMutu' => $hurufMutu, 'angkaMutu' => $angkaMutu, 'mutu' => $mutu
        ];
    }

    private function hitungIPK() {
        $totalSKS = 0; $totalMutu = 0;
        foreach ($this->daftarNilai as $nilai) {
            $totalSKS += $nilai['sks'];
            $totalMutu += $nilai['mutu'];
        }
        if ($totalSKS > 0) {
            $this->ipk = $totalMutu / $totalSKS;
        }
        return ['totalSKS' => $totalSKS, 'totalMutu' => $totalMutu, 'ipk' => $this->ipk];
    }

    public function cetakKHS() {
        $hasil = $this->hitungIPK();
        
        $html = "<div class='khs-box'>";
        $html .= "<h2 style='text-align:center; color: #FFF8E7; font-family: serif;'>Kartu Hasil Studi (KHS)</h2>";
        $html .= "<hr style='border-color: #8CC1E9; margin-bottom: 20px;'>";
        $html .= "<p><strong>NAMA</strong> : " . $this->getNama() . "<br>";
        $html .= "<strong>NIM</strong> : " . $this->nim . "<br>";
        $html .= "<strong>JURUSAN</strong> : " . $this->jurusan . "</p>";
        
        $html .= "<table>";
        $html .= "<tr><th>Kode</th><th>Mata Kuliah</th><th>HM</th><th>AM</th><th>K</th><th>M</th></tr>";
        
        foreach ($this->daftarNilai as $n) {
            $html .= "<tr>";
            $html .= "<td>" . $n['kode'] . "</td>";
            $html .= "<td style='text-align:left;'>" . $n['matkul'] . "</td>";
            $html .= "<td>" . $n['hurufMutu'] . "</td>";
            $html .= "<td>" . $n['angkaMutu'] . "</td>";
            $html .= "<td>" . $n['sks'] . "</td>";
            $html .= "<td>" . $n['mutu'] . "</td>";
            $html .= "</tr>";
        }
        
        $html .= "<tr style='font-weight:bold; background: rgba(0, 85, 160, 0.4);'>";
        $html .= "<td colspan='4'>Jumlah</td>";
        $html .= "<td>" . $hasil['totalSKS'] . "</td>";
        $html .= "<td>" . $hasil['totalMutu'] . "</td>";
        $html .= "</tr>";
        $html .= "</table>";
        
        $html .= "<h3 style='text-align:right; margin-top:20px; color:#8CC1E9;'>Nilai Mutu Rata-Rata (IPK): " . number_format($hasil['ipk'], 2) . "</h3>";
        $html .= "</div>";

        return $html;
    }
}
?>