<?php
namespace user;

function createTable($con) {
  $tablename = 'user';
  $sql = "CREATE TABLE IF NOT EXISTS user(
      username VARCHAR(50) NOT NULL PRIMARY KEY,
      permission VARCHAR(20)
    );
  ";

  if(!mysqli_query($con, $sql)) {
    return \helpers\json_response('500', mysqli_error($con));
  }
}

function getData($username) {
  global $con;

  $sql = "SELECT * FROM user WHERE username = '$username'";
  $result = mysqli_query($con, $sql);
  $json_array = array();

  while ($row = mysqli_fetch_assoc($result)) {
    $json_array[] = $row;
  }

  $json_result = json_encode($json_array);

  return $json_result;
}

function createData($username) {
  global $con;

  // by default, assign the lowest permission level to new users.
  $permission = 'free';

  $sql = "INSERT INTO user SET username = '$username', permission = '$permission';";

  if(!mysqli_query($con, $sql)) {
    return \helpers\json_response('500', mysqli_error($con));
  };
}

function updatePermission($username, $permission) {
  global $con;

  $sql = "UPDATE user
    SET permission = '$permission'
    WHERE username = '$username';
  ";

  if(!mysqli_query($con, $sql)) {
    return \helpers\json_response('500', mysqli_error($con));
  };
}