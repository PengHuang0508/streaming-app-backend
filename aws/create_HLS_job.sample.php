<?php
namespace aws;

use Aws\ElasticTranscoder\ElasticTranscoderClient;

function set_thumbnail_read_permission($output_key) {
  global $s3Client;

  // Give Elastic Transcoder 20s to process and create thumbnail file
  sleep(20);

  try {
    // INSERT HERE
    $result = $s3Client->putObjectAcl([
      'ACL'        => 'public-read',
      'Bucket'     => 'BUCKET NAME',
      'Key'        => 'PATH TO FILE',
      ]);
      return \helpers\json_response('200', 'Successfully set thumbnail permission to read.');
  } catch (Exception $e) {
    return \helpers\json_response('500', $e->getMessage());
  }
}

function create_hls_job($transcoder_client, $fileData) {
  $input = array('Key' => $fileData['input_key']);

  $output_key = $fileData['input_key'];

  // Specify the outputs based on the hls presets array specified.
  $outputs = array();

  if ($fileData['is_audio']) { 
    foreach ($fileData['hls_audio_presets'] as $prefix => $preset_id) {
      array_push($outputs,
        array('Key' => "$prefix/$output_key",
        'PresetId' => $preset_id,
        'SegmentDuration' => $fileData['segment_duration'],
      ));
    }
  }

  if ($fileData['is_video']) {
    foreach ($fileData['hls_video_presets'] as $prefix => $preset_id) {
      array_push($outputs,
        array('Key' => "$prefix/$output_key",
        'PresetId' => $preset_id,
        'SegmentDuration' => $fileData['segment_duration'],
        'ThumbnailPattern' => $fileData['thumbnail_pattern']
      ));
    }
  }

  // Setup master playlist which can be used to play using adaptive bitrate.
  $playlist = array(
    'Name' => 'hls_' . $output_key,
    'Format' => 'HLSv3',
    'OutputKeys' => array_map(function($x) { return $x['Key']; }, $outputs),
    'HlsContentProtection' => [
      'KeyStoragePolicy' => 'WithVariantPlaylists',
      'Method' => 'aes-128',
    ]
  );

  // Create the job.
  $create_job_request = array(
        'PipelineId' => $fileData['pipeline_id'], 
        'Input' => $input, 
        'Outputs' => $outputs, 
        'OutputKeyPrefix' => $fileData['output_key_prefix'] . $output_key . "/", 
        'Playlists' => array($playlist)
  );

  $create_job_result = $transcoder_client->createJob($create_job_request)->toArray();
  return set_thumbnail_read_permission($output_key);
}   

function convert_to_HLS($fileData) {
  $region = 'us-west-2';
  $transcoder_client = ElasticTranscoderClient::factory(array('region' => $region, 'default_caching_config' => '/tmp', 'version' => 'latest'));

  // INSERT HERE
  $fileData['pipeline_id'] = '';
  //All outputs will have this prefix pre-pended to their output key.
  $fileData['output_key_prefix'] = 'elastic-transcoder/hls/';

  // HLS Presets that will be used to create an adaptive bitrate playlist.
  $hls_64k_audio_preset_id = '1351620000001-200071';
  $hls_0600k_preset_id     = '1351620000001-200040';
  $hls_1000k_preset_id     = '1351620000001-200030';
  $hls_1500k_preset_id     = '1351620000001-200020';

  $fileData['hls_audio_presets'] = array('hlsAudio' => $hls_64k_audio_preset_id);
  $fileData['hls_video_presets'] = array(
    'hls0600k' => $hls_0600k_preset_id,
    'hls1000k' => $hls_1000k_preset_id,
    'hls1500k' => $hls_1500k_preset_id,
  );
  // HLS Segment duration that will be targeted.
  $fileData['segment_duration'] = '2';
  $fileData['thumbnail_pattern'] ='{count}';

  return create_hls_job($transcoder_client, $fileData);
}