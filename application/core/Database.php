<?php

function dbConnect(){
    $dsn = 'mysql:host=localhost;dbname=d01d9005';
    $user = 'd01d9005';
    $password = 'c4X884aduZVLARVd';

    try{
        $dbh = new PDO($dsn, $user, $password);
        return $dbh;
    }catch(PDOException $e){
        print $e;
    }
}