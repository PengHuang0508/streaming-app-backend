<?php
require_once __DIR__ . '/Controller/db/mysql.php';
require_once __DIR__ . '/Controller/aws/aws.php';
require_once __DIR__ . '/utils.php';

use Pecee\SimpleRouter\SimpleRouter;
use Spatie\Async\Pool;

/////
// Testing routes
////
SimpleRouter::get('/api/', function() {
  return 'MELLON API READY';
});

SimpleRouter::post('/api/upload', function(){
  $fileData = utils\parse_and_save_file_data();

  if (empty($fileData)) {
    return utils\json_response('500', "Must provide all the required fields, including title, media_key, thumbnail_url and media file.");
  }

  function create_convert_HLS_job($media_key,$fileData) {
    $fileData['media_key'] = $media_key;
    $media_data = utils\add_video_and_audio_data($_FILES['fileToUpload']);
    $fileData = array_merge($fileData, $media_data);

    return aws\convert_to_HLS($fileData);
  };

  function save_media_to_database($media_key, $fileData) {
    $fileData['media_key'] = $media_key;
    $fileData['thumbnail_url'] = "https://s3-us-west-2.amazonaws.com/thumbnails.mellon.com/elastic-transcoder/hls/$media_key/00001.png";
   
    return media\create_media($fileData);
  }

  $pool = Pool::create();
  
  $pool->add(function () { 
    return aws\upload_to_S3();
  })->then(function ($media_key) use ($fileData)  {
    return create_convert_HLS_job($media_key, $fileData);
  })->then(function ($media_key) use ($fileData) {
    return save_media_to_database($media_key, $fileData);
  })->then(function($media_key) {
    return aws\set_thumbnail_permission($media_key);
  })->catch(function (Throwable $exception) {
    echo $exception;
    return utils\json_response('500', $exception);
  });
  
  $pool->wait();
});

/////
// Database routes
/////
// user routes
SimpleRouter::get('/api/database/user/{username}', function($username) {
  return user\get_data($username);
});

SimpleRouter::post('/api/database/user/add', function() {
  $userInfo['username'] = $_POST['username'];
  $userInfo['email'] = $_POST['email'];
  $userInfo['permission'] = utils\set_default_value('permission', 'free');

  return user\create_data($userInfo);
});

SimpleRouter::post('/api/database/user/update/{username}', function($username) {
  $newUserInfo['username']= $username;
  $newUserInfo['permission']= $_POST['permission'];

  return user\update_permission($newUserInfo);
});

// media routes
SimpleRouter::get('/api/database/media/', function() {
  return media\get_media();
});

SimpleRouter::post('/api/database/media/update/{mediaKey}', function($mediaKey) {
  if ($_POST['min_permission'] !== 'free' && $_POST['min_permission'] !== 'premium' ) {  
    return utils\json_response('500', "Minimum permission can only be 'free' or 'premium'.");
  }
  $mediaInfo['mediaKey'] = $mediaKey;
  $mediaInfo['title'] = $_POST['title'];
  $mediaInfo['media_description'] = $_POST['media_description'];
  $mediaInfo['min_permission'] = $_POST['min_permission'];

  return media\update_media($mediaInfo);
});

SimpleRouter::post('/api/database/media/update/{mediaKey}/view', function($mediaKey) {
  return media\increase_view($mediaKey);
});

SimpleRouter::delete('/api/database/media/delete/{mediaKey}', function($mediaKey) {
  return media\delete_media_row($mediaKey);
});


/////
// AWS routes
////
//streaming
SimpleRouter::get('/api/aws/stream/', function() {
   return utils\json_response('500', "Please provide a media key.");
});

SimpleRouter::get('/api/aws/stream/{media_key}', function($media_key) {
    return aws\stream_media($media_key);
});

SimpleRouter::start();