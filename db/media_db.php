<?php
namespace media;

function createTable($con) {
  $tablename = 'media';

  $sql = "CREATE TABLE IF NOT EXISTS media(
      media_key VARCHAR(64) NOT NULL,
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

function createData($media_key, $title, $media_description, $uploaded_by, $min_permission) {
  global $_FILES, $con;

  $fileToUpload = $_FILES['fileToUpload'];
  $fileData = \helpers\mediaParser($fileToUpload);
  $duration = $fileData['duration'];
  $width = $fileData['width'];
  $height = $fileData['height']; 
  $created_at = date("Y/m/d h:i:s", time());

  $sql = "INSERT INTO media (media_key, title, media_description, uploaded_by, min_permission, duration, width, height, created_at)
    VALUES ('$media_key', '$title', '$media_description', '$uploaded_by', '$min_permission', '$duration', '$width', '$height', '$created_at');
  ";

  if(!mysqli_query($con, $sql)) {
    return "Error while inserting new data to media table. " . mysqli_error($con);
  }
}

// function updateThumbnail($media_key, $created_at) {
//   global $con;

//   $thumbnail_url = 'https://s3-us-west-2.amazonaws.com/thumbnails.mellon.com/elastic-transcoder/hls/' . $media_key . '/00001.png';

//   $sql = "
//     UPDATE media
//     SET thumbnail_url = '$thumbnail_url'
//     WHERE media_key = '$media_key' AND created_at = '$created_at';
//   ";

//   if(!mysqli_query($con, $sql)) {
//     return "Error while updating thumbnail_url. " . mysqli_error($con);
//   };
// }

// No routes assigned to it yet
function updateData($media_key, $created_at) {
  global $con;
  
  $title = $_POST['title'];
  $min_permission = $_POST['min_permission'];

  $sql = "
    UPDATE media
    SET title = '$title', min_permission = '$min_permission'
    WHERE media_key = '$media_key' AND created_at = '$created_at';
  ";

  if(!mysqli_query($con, $sql)) {
    return "Error while updating the media table. " . mysqli_error($con);
  };
}