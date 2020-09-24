<?php
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/create_HLS_job.php';
require_once __DIR__ . '/stream_media.php';

require dirname(__DIR__, 1) . '/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$s3Client = new S3Client([
    'version' => 'latest',
    'region'  => 'us-west-2',
    'scheme'=> 'http'
]);