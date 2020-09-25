<?php
namespace aws;

use Aws\S3\S3Client;
use Aws\S3\ObjectUploader;
use Aws\Exception\AwsException;

$s3Client = new S3Client([
    'version' => 'latest',
    'region'  => 'us-west-2',
    'scheme'=> 'http'
]);

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
  
  try {
      $result = $uploader->upload();
      $result_json = [];
      
      if ($result["@metadata"]["statusCode"] == '200') {
        $result_json['media_key'] = $key;
        
        return json_encode($result_json);
      } else {
        
        return helpers\json_response('500', 'Something went wrong while uploading to S3.');
      }

  } catch (MultipartUploadException $e) {
      rewind($source);
      $uploader = new MultipartUploader($s3Client, $source, [
          'state' => $e->getState(),
      ]);
  }
}