<?php
require_once __DIR__ . '/db/mysql.php';
require_once __DIR__ . '/aws/aws.php';
require_once __DIR__ . '/helpers.php';

use Pecee\SimpleRouter\SimpleRouter;

/////
// Testing routes
////
SimpleRouter::get('/api/', function() {
  return 'MELLON API READY';
});
SimpleRouter::post('/api/', function() {
  return 'MELLON API READY';
});

/////
// Database routes
/////
// user routes
SimpleRouter::get('/api/database/user/{username}', function($username) {
  return user\getData($username);
});
SimpleRouter::post('/api/database/user/add', function() {
  $username = $_POST['username'];

  return user\createData();
});
SimpleRouter::post('/api/database/user/update/{username}', function($username) {
  $permission = $_POST['permission'];

  return user\updatePermission($username, $permission);
});

// media routes
SimpleRouter::get('/api/database/media/', function() {
  return media\getData();
});
SimpleRouter::post('/api/database/media/create', function() {
  global $_FILES;

  if (!empty($_POST["title"]) && !empty($_POST["media_key"]) && !empty($_POST['thumbnail_url'] ) && !empty($_FILES['fileToUpload']['name'])) {
    $fileData = [];
    $fileData['media_key'] = $_POST['media_key'];
    $fileData['title'] = $_POST['title'];
    $fileData['thumbnail_url'] = $_POST['thumbnail_url'];
    // optional fields
    $fileData['media_description'] = helpers\setDefaultValue('media_description', 'No description.');
    $fileData['uploaded_by'] = helpers\setDefaultValue('uploaded_by', 'Anonymous');
    $fileData['min_permission'] = helpers\setDefaultValue('min_permission', 'free');

    return media\createData($fileData);
  } 
  else {
    return helpers\json_response('500', "Must provide all the required fields, including title, media_key, thumbnail_url and file.");
  }
});

SimpleRouter::post('/api/database/media/update/view/{media_key}', function($media_key) {
  if (!empty($_POST["created_at"])) {
    $primary_key['media_key'] = $media_key;
    $primary_key['created_at'] = $_POST['created_at'];

    return media\increaseView($primary_key);
  } else {
    return helpers\json_response('500', "Cannot find file.");
  }
});

/////
// AWS routes
////
SimpleRouter::get('/api/aws/stream/', function() {
   return helpers\json_response('500', "Please provide a media key.");
});

SimpleRouter::get('/api/aws/stream/{media_key}', function($media_key) {
    return aws\stream_media($media_key);
});

SimpleRouter::post('/api/aws/upload', function() {
  global $_FILES;

  if (!empty($_FILES['fileToUpload']['name'])) {
    return aws\upload_to_S3();
  } else {
    return helpers\json_response('500', "Cannot find file.");
  }
});

SimpleRouter::post('/api/aws/convertToHLS', function() {
  if (!empty($_POST['media_key'])) {
    $media_key = $_POST['media_key'];

    return aws\convert_to_HLS($media_key);
  } else {
    return helpers\json_response('500', "Cannot find media key.");
  }
});

SimpleRouter::start();