<?php
namespace streamer;

use Aws\CloudFront\CloudFrontClient;
use Aws\Kms\KmsClient;
use Aws\Exception\AwsException;

function stream_media($media_key) {
	function signPrivateDistributionPolicy($cloudFrontClient, $resourceKey, $customPolicy, $privateKey, $keyPairId) {
			try {
					$result = $cloudFrontClient->getSignedUrl([
							'url' => $resourceKey,
							'policy' => $customPolicy,
							'private_key' => $privateKey,
							'key_pair_id' => $keyPairId
					]);

					return $result;

			} catch (AwsException $e) {
					return 'Error: ' . $e->getAwsErrorMessage();
			}
	}               

	// example: https://{key}.cloudfront.net/
	$cloudFrontURL = '';
	$playlistURL = 'elastic-transcoder/hls/' . $media_key . '/hls_' . $media_key . '.m3u8';
	$resourceKey = $cloudFrontURL.$playlistURL;
	$expires = time() + 300; // 5 minutes (5 * 60 seconds) from now.
	$customPolicy = <<<POLICY
	{
			"Statement": [
					{
							"Resource": "{$resourceKey}",
							"Condition": {
									"IpAddress": {"AWS:SourceIp": "{$_SERVER['REMOTE_ADDR']}/32"},
									"DateLessThan": {"AWS:EpochTime": {$expires}}
							}
					}
			]
	}
	POLICY;
	// path	 to key (.pem file)
	$privateKey = dirname(__DIR__) . '';
	// KMS key pair id
	$keyPairId = '';
	$cloudFrontClient = new CloudFrontClient([
			'region' => 'us-west-2',
			'version' => 'latest'
	]);
			
	$result =  signPrivateDistributionPolicy($cloudFrontClient, $resourceKey, $customPolicy, $privateKey, $keyPairId);

	return json_encode($result);
}