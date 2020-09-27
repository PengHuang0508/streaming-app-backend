<?php
namespace aws;

use Aws\S3\ObjectUploader;

function upload_to_S3() {
  global $s3Client, $AWSInputBucket;

  $key = hash('sha256', utf8_encode($_FILES["fileToUpload"]["name"])) . '_' . rand(0, 99999);
  $file_path = $_FILES['fileToUpload']['tmp_name'];
  $source = fopen($file_path, 'rb');
  
  $uploader = new ObjectUploader(
    $s3Client,
    $AWSInputBucket,
    $key,
    $source
  );
  
  try {
      $result = $uploader->upload();
      $result_json = [];
      
      if ($result["@metadata"]["statusCode"] != '200') {
        return \utils\json_response('500', 'Something went wrong while uploading to S3.');
      }
      
      return $key;
  } catch (MultipartUploadException $e) {
      rewind($source);
      $uploader = new MultipartUploader($s3Client, $source, [
          'state' => $e->getState(),
      ]);
  }
}