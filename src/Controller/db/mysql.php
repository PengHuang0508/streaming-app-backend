<?php
require_once __DIR__ . '/user_db.php';
require_once __DIR__ . '/media_db.php';
require_once dirname(__DIR__,  2) . '/Model/db_schema.php';
require_once dirname(__DIR__, 2) . '/config.php';

$con =  connect_DB();

function connect_DB() {
  global $MySQLHostname, $MySQLUser, $MySQLPassword, $MySQLDatabaseName;
   // create connection
   $con = mysqli_connect($MySQLHostname, $MySQLUser, $MySQLPassword);

   // check connection
   if (!$con) {
     die("Connection failed: " . mysqli_connect_error());
   }
   
   // create database if not exist
   $sql = "CREATE DATABASE IF NOT EXISTS $MySQLDatabaseName";
 
   if(mysqli_query($con, $sql)) {
      $con = mysqli_connect($MySQLHostname, $MySQLUser, $MySQLPassword, $MySQLDatabaseName);

      user\create_table($con);
      media\create_table($con);

      return $con;
   } else {  
     return \helpers\json_response('500', mysqli_error($con));
   }
}