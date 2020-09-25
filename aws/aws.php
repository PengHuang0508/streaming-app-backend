<?php
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/create_HLS_job.php';
require_once __DIR__ . '/stream_media.php';

use Aws\S3\S3Client;

$s3Client = new S3Client([
  'version' => 'latest',
  'region'  => 'us-west-2',
  'scheme'=> 'http'
]);
