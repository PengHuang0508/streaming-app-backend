<?php
namespace helpers;

function media_parser($fileToUpload) {  
  $getID3 = new \getID3;
  $file = $getID3->analyze($fileToUpload['tmp_name']);

  $media_info = [
    'width' => $file['video']['resolution_x'],
    'height' => $file['video']['resolution_y'],
    'duration' => $file['playtime_string']
  ];

  return $media_info;
}

function setDefaultValue($field_key, $default_value) {
  if (!empty($_POST[$field_key])) {
    return $_POST[$field_key];
  } else {
    return $default_value;
  }  
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