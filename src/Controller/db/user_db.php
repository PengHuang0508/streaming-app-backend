<?php
namespace user;

function create_table($con) {
  global $createUserTableQuery;

  if(!mysqli_query($con, $createUserTableQuery)) {
    return \utils\json_response('500', mysqli_error($con));
  }
}

function get_data($username) {
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

function create_data($userInfo) {
  global $con;

  $sql = "INSERT INTO user SET username = '{$userInfo['username']}', permission = '{$userInfo['permission']}', email = '{$userInfo['email']}';";

  if(!mysqli_query($con, $sql)) {
    return \utils\json_response('500', mysqli_error($con));
  };
}

function update_permission($newUserInfo) {
  global $con;

  $sql = "UPDATE user
    SET permission = '{$newUserInfo['permission']}'
    WHERE username = '{$newUserInfo['username']}';
  ";

  if(!mysqli_query($con, $sql)) {
    return \utils\json_response('500', mysqli_error($con));
  };
}