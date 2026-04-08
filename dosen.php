<?php
// File: dosen.php
require_once 'user.php';

class Dosen extends User {
    private $nidn;
    
    public function __construct($nidn, $nama) {
        parent::__construct($nidn, $nama);
        $this->nidn = $nidn;
    }
    
    public function getPeran() { return "Dosen"; }
    
    public function inputNilaiMahasiswa($mahasiswa, $kode, $matkul, $sks, $huruf, $angka) {
        $mahasiswa->tambahNilai($kode, $matkul, $sks, $huruf, $angka);
    }
}
?>