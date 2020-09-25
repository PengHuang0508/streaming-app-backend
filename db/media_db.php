<?php
namespace media;

function createTable($con) {
  $tablename = 'media';

  $sql = "CREATE TABLE IF NOT EXISTS media(
      media_key VARCHAR(70) NOT NULL,
      thumbnail_url VARCHAR(250) NULL,
      title VARCHAR(50) NOT NULL,
      media_description VARCHAR(120) NULL,
      uploaded_by VARCHAR(50) DEFAULT 'Anonymous' NOT NULL,
      min_permission VARCHAR(20) DEFAULT 'free' NOT NULL,
      view INT(5) DEFAULT 0,
      duration TIME NOT NULL,
      width INT(5) NOT NULL,
      height INT(5) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (media_key, created_at)
    );
  ";

  if(!mysqli_query($con, $sql)) {
    return "Error while creating $tablename table" . mysqli_error($con);
  }
}

function getData() {
  global $con;

  $sql = "SELECT *
    FROM media
    ORDER BY min_permission, created_at DESC;
  ";
  $sql_result = mysqli_query($con, $sql);
  $result_json = array();

  while ($row = mysqli_fetch_assoc($sql_result)) {
    $result_json[] = $row;
  }

  return json_encode($result_json);
}

function createData($fileData) {
  global $_FILES, $con;

  $fileToUpload = $_FILES['fileToUpload'];
  $media_info = \helpers\media_parser($fileToUpload);
  $fileData['duration'] = $media_info['duration'];
  $fileData['width'] = $media_info['width'];
  $fileData['height'] = $media_info['height']; 
  $fileData['created_at'] = date("Y/m/d h:i:s", time());

  $values = implode("','", $fileData);  
  $sql = "INSERT INTO media (media_key, title, thumbnail_url, media_description, uploaded_by, min_permission, duration, width, height, created_at)
    VALUES ('$values');
  ";
  if(!mysqli_query($con, $sql)) {
    return \helpers\json_response('500', mysqli_error($con));
  }
}

function increaseView($primary_key) {
  global $con;

  $sql = "UPDATE media
    SET view = view + 1
    WHERE media_key = '{$primary_key['media_key']}' AND created_at = '{$primary['created_at']}';
  ";

  if(!mysqli_query($con, $sql)) {
    return \helpers\json_response('500', mysqli_error($con));
  };
}

// No routes assigned to it yet
// function updateData($primary_key) {
//   global $con;
  
//   $title = $_POST['title'];
//   $min_permission = $_POST['min_permission'];

//   $sql = "
//     UPDATE media
//     SET title = '$title', min_permission = '$min_permission'
//     WHERE media_key = ' {$primary_key["media_key"]} ' AND created_at = '{$primary_key['created_at']}';
//   ";

//   if(!mysqli_query($con, $sql)) {
//     return "Error while updating the media table. " . mysqli_error($con);
//   };
// }