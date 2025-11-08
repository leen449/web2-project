<?php
$servername="localhost";
$username="root";
$password="root";
$database="mindlydatabase";

$connection=new mysqli($servername , $username , $password ,$database);

if($connection ->connect_error){
    die("connection faild :" .$connection-> connect_error);
}





?>

