<?php
namespace aws;

use Aws\CloudFront\CloudFrontClient;
use Aws\Kms\KmsClient;
use Aws\Exception\AwsException;

function signPrivateDistributionPolicy($cloudFrontClient, $resourceKey, $customPolicy) {
	global $AWSPrivateKey, $AWSKeyPairId;
	try {
			$result = $cloudFrontClient->getSignedUrl([
					'url' => $resourceKey,
					'policy' => $customPolicy,
					'private_key' => $AWSPrivateKey,
					'key_pair_id' => $AWSKeyPairId
			]);

			return $result;

	} catch (AwsException $e) {
			return 'Error: ' . $e->getAwsErrorMessage();
	}
}      

function stream_media($media_key) {
	global $AWSRegion, $AWSCloudFrontURL;

	$playlistURL = 'elastic-transcoder/hls/' . $media_key . '/hls_' . $media_key . '.m3u8';
	$resourceKey = $AWSCloudFrontURL.$playlistURL;
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

	$cloudFrontClient = new CloudFrontClient([
			'region' => $AWSRegion,
			'version' => 'latest'
	]);
			
	$result =  signPrivateDistributionPolicy($cloudFrontClient, $resourceKey, $customPolicy);

	return json_encode($result);
}