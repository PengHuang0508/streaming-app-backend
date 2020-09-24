<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/db/mysql.php';
require_once __DIR__ . '/aws/aws.php';

use Pecee\SimpleRouter\SimpleRouter;

/////
// Testing route
////
SimpleRouter::post('/api/', function() {
  return 'MELLON API READY';
});

/////
// Database routes
/////
// user routes
SimpleRouter::get('/api/user/{username}', function($username) {
  return user\getData($username);
});
SimpleRouter::post('/api/user/add', function() {
  $username = $_POST['username'];

  return user\createData();
});
SimpleRouter::post('/api/user/update/{username}', function($username) {
  $permission = $_POST['permission'];

  return user\updatePermission($username, $permission);
});

// media routes
SimpleRouter::get('/api/media/', function() {
  return media\getData();
});
SimpleRouter::post('/api/media/upload', function() {
  global $_FILES;

  // optional fields
  if (!empty($_POST["media_description"])) {
    $media_description = $_POST['media_description'];
  } else {
    $media_description = 'No description.';
  }  
  
  if (!empty($_POST["uploaded_by"])) {
    $uploaded_by = $_POST['uploaded_by'];
  } else {
    $uploaded_by = 'Anonymous';
  }

  if (!empty($_POST["min_permission"])) {
    $min_permission = $_POST['min_permission'];
  } else {
    $min_permission = 'free';
  }

  // required fields
  if (!empty($_POST["title"]) && !empty($_POST["media_key"])&& !empty($_FILES['fileToUpload']['name'])) {
    $title = $_POST['title'];
    $media_key = $_POST['media_key'];

    return media\createData($media_key, $title, $media_description, $uploaded_by, $min_permission);
  } 
  else {
    $error['error'] = "Must provide all the required information, including title, media key and file.";

    return json_encode($error);
  }
});
SimpleRouter::post('/api/media/update/{media_key}', function($media_key) {
  if (!empty($_POST["created_at"])) {
    $created_at = $_POST['created_at'];

    return media\updateThumbnail($media_key, $created_at);

  } else {
    $error['error'] = "Must provide all the required information.";

    return json_encode($error);
  }
});

/////
// AWS routes
////
SimpleRouter::post('/api/aws/upload', function() {
  if (!empty($_FILES['fileToUpload']['name'])) {

    return uploader\upload_to_S3();
  } else {
    $error['error'] = "Cannot find file.";

    return json_encode($error);
  }

});

SimpleRouter::post('/api/aws/convertToHLS', function() {
  $media_key = $_POST['media_key'];
  
  return converter\convert_to_HLS($media_key);
});

SimpleRouter::get('/api/aws/stream/{media_key}', function($media_key) {
  return streamer\stream_media($media_key);
});

SimpleRouter::start();