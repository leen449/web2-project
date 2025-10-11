<?php
$servername="localhost";
$username="root";
$password="root";
$database="mindlydatabase";

$connection=new mysqli($servername , $username , $password ,$database);

if($connection ->connect_error){
    di("connection faild :" .$connection-> connect_error);
}





?>

