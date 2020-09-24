<?php
namespace uploader;

use Aws\S3\ObjectUploader;

function upload_to_S3() {
  global $_FILES, $s3Client;

  $bucketName ='hls-input.mellon.com';
  $key = hash('sha256', utf8_encode($_FILES["fileToUpload"]["name"])) . '_' . rand(0, 99999);
  $file_path = $_FILES['fileToUpload']['tmp_name'];
  $source = fopen($file_path, 'rb');
  
  $uploader = new ObjectUploader(
    $s3Client,
    $bucketName,
    $key,
    $source
  );
  
  do {
    try {
        $result = $uploader->upload();
        $result_json = [];

        if ($result["@metadata"]["statusCode"] == '200') {
          $result_json['message'] = 'File successfully uploaded to ' . $result["ObjectURL"];
          $result_json['media_key'] = $key;
        } else {
          $result_json['message'] = 'Something went wrong while uploading media file.';
        }

        return json_encode($result_json);
    } catch (MultipartUploadException $e) {
        rewind($source);
        
        $uploader = new MultipartUploader($s3Client, $source, [
            'state' => $e->getState(),
        ]);
    }
  } while (!isset($result));
}