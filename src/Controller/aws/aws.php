<?php
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/create_HLS_job.php';
require_once __DIR__ . '/set_thumbnail_permission.php';
require_once __DIR__ . '/stream_media.php';
require_once dirname(__DIR__, 2) . '/config.php';

use Aws\S3\S3Client;

$s3Client = new S3Client([
  'version' => 'latest',
  'region'  => $AWSRegion,
  'scheme'=> 'http'
]);
