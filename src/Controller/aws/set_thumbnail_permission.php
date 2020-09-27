<?php
namespace aws;

function set_thumbnail_permission($media_key) {
  global $s3Client, $AWSThumbnailBucket;

  // Give Elastic Transcoder 20s to process and create the thumbnail file
  sleep(20);

  try {
    $result = $s3Client->putObjectAcl([
      'ACL'        => 'public-read',
      'Bucket'     => $AWSThumbnailBucket,
      'Key'        => 'elastic-transcoder/hls/' . $media_key . '/00001.png',
      ]);
      return \utils\json_response('200', 'Successfully set thumbnail permission to read.');
  } catch (Exception $e) {
    return \utils\json_response('500', $e->getMessage());
  }
}
