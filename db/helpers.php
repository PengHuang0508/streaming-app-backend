<?php
namespace helpers;

require_once dirname(__DIR__, 1) . '/getid3/getid3.php';

function mediaParser($fileToUpload) {  

  $getID3 = new \getID3;
  $file = $getID3->analyze($fileToUpload['tmp_name']);

  $fileData = [
    'width' => $file['video']['resolution_x'],
    'height' => $file['video']['resolution_y'],
    'duration' => $file['playtime_string']
  ];

  return $fileData;
}