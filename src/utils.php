<?php
namespace utils;

function media_parser($fileToUpload) {  
  $getID3 = new \getID3;
  $file = $getID3->analyze($fileToUpload['tmp_name']);

  $media_info = [
    'width' => $file['video']['resolution_x'],
    'height' => $file['video']['resolution_y'],
    'duration' => $file['playtime_string'],
  ];

  return $media_info;
}

function add_video_and_audio_data($fileToUpload) {
  $getID3 = new \getID3;
  $file = $getID3->analyze($fileToUpload['tmp_name']);

  $file_data['is_video'] = !empty($file['video']['dataformat']);
  $file_data['is_audio'] = !empty($file['audio']['dataformat']);

  return $file_data;
}

function set_default_value($field_key, $default_value) {
  if (empty($_POST[$field_key])) {
    return $default_value;
  }
 
  return $_POST[$field_key];
}

function parse_and_save_file_data() {
  if (!empty($_POST["title"]) && !empty($_FILES['fileToUpload'])) {
    $file_data['title'] = $_POST['title'];
    // optional fields
    $file_data['media_description'] = set_default_value('media_description', 'No description.');
    $file_data['uploaded_by'] = set_default_value('uploaded_by', 'admin');
    $file_data['min_permission'] = set_default_value('min_permission', 'free');
  }
  
  return $file_data;
}

function json_response($code = 200, $message = null) {
  // clear the old headers
  header_remove();
  // set the actual code
  http_response_code($code);
  // set the header to make sure cache is forced
  header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
  // treat this as json
  header('Content-Type: application/json');
  $status = array(
      200 => '200 OK',
      400 => '400 Bad Request',
      403 => 'Forbidden',
      500 => '500 Internal Server Error'
      );
  // ok, validation error, or failure
  header('Status: '.$status[$code]);
  // return the encoded json
  return json_encode(array(
      'status' => $code < 300, // success or not?
      'message' => $message
      ));
}