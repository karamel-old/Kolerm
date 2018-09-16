<?php
namespace Karamel\DB\Interfaces;
interface IDB{
    public function connection();
    public function query($query);
    public function fetch_object() ;
    public function fetch_assoc();
    public function num_rows() ;
    public function inserted_Id() ;
}