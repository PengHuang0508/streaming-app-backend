<?php
namespace media;

function create_table($con) {
  global $createMediaTableQuery;

  if(!mysqli_query($con, $createMediaTableQuery)) {
    return "Error while creating media table" . mysqli_error($con);
  }
}

function get_media() {
  global $con;

  $sql = "SELECT *
    FROM media
    ORDER BY min_permission, created_at DESC;
  ";
  $sqlResult = mysqli_query($con, $sql);
  $resultInJson = array();

  while ($row = mysqli_fetch_assoc($sqlResult)) {
    $resultInJson[] = $row;
  }

  return json_encode($resultInJson);
}

function create_media($fileData) {
  global $con;

  $fileToUpload = $_FILES['fileToUpload'];
  $mediaInfo = \utils\media_parser($fileToUpload);
  $fileData['duration'] = $mediaInfo['duration'];
  $fileData['width'] = $mediaInfo['width'];
  $fileData['height'] = $mediaInfo['height']; 
  $fileData['created_at'] = date("Y/m/d h:i:s", time());

  $values = implode("','", $fileData);
  
  $sql = "INSERT INTO media (title, media_description, uploaded_by, min_permission, media_key, thumbnail_url, duration, width, height, created_at)
    VALUES ('$values');
  ";

  if(!mysqli_query($con, $sql)) {
    return \utils\json_response('500', mysqli_error($con));
  }
  
  return \utils\json_response('200', 'Successfully saved to database.');
}

function update_media($mediaInfo) {
  global $con;

  $sql = "
    UPDATE media
    SET title = '{$mediaInfo['title']}', media_description = '{$mediaInfo['media_description']}', min_permission = '{$mediaInfo['min_permission']}'
    WHERE media_key = '{$mediaInfo['mediaKey']}';
  ";

  if(!mysqli_query($con, $sql)) {
    return "Error while updating the media table. " . mysqli_error($con);
  };
}

function increase_view($mediaKey) {
  global $con;

  $sql = "UPDATE media
    SET view = view + 1
    WHERE media_key = '$mediaKey';
  ";

  if(!mysqli_query($con, $sql)) {
    return \utils\json_response('500', mysqli_error($con));
  };
}

function delete_media_row($mediaKey) {
  global $con;

  $sql = "DELETE FROM media
    WHERE media_key = '$mediaKey';
  ";

  if(!mysqli_query($con, $sql)) {
    return \utils\json_response('500', mysqli_error($con));
  };
}