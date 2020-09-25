<?php
require_once __DIR__ . '/user_db.php';
require_once __DIR__ . '/media_db.php';

$con =  connectDB();

function connectDB() {
  // INSERT HERE
  $host = ""; 
  $user = ""; 
  $password = ""; 
  $dbname = ""; 
  
   // create connection
   $con = mysqli_connect($host, $user, $password);

   // check connection
   if (!$con) {
     die("Connection failed: " . mysqli_connect_error());
   }
   
   // create database if not exist
   $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
 
   if(mysqli_query($con, $sql)) {
      $con = mysqli_connect($host, $user, $password, $dbname);

      user\createTable($con);
      media\createTable($con);

      return $con;
   } else {  
     return \helpers\json_response('500', mysqli_error($con));
   }
}