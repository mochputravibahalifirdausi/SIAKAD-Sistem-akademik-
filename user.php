<?php
// File: user.php
abstract class User {
    protected $id;
    protected $nama;

    public function __construct($id, $nama) {
        $this->id = $id;
        $this->nama = $nama;
    }

    abstract public function getPeran();

    public function getNama() {
        return $this->nama;
    }
}
?>