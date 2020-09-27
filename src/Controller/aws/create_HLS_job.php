<?php
namespace aws;

use Aws\ElasticTranscoder\ElasticTranscoderClient;

function create_hls_job($transcoder_client, $file_data) {
  $input = array('Key' => $file_data['media_key']);

  $output_key = $file_data['media_key'];

  // Specify the outputs based on the hls presets array specified.
  $outputs = array();

  if ($file_data['is_audio']) { 
    foreach ($file_data['hls_audio_presets'] as $prefix => $preset_id) {
      array_push($outputs,
        array('Key' => "$prefix/$output_key",
        'PresetId' => $preset_id,
        'SegmentDuration' => $file_data['segment_duration'],
      ));
    }
  }

  if ($file_data['is_video']) {
    foreach ($file_data['hls_video_presets'] as $prefix => $preset_id) {
      array_push($outputs,
        array('Key' => "$prefix/$output_key",
        'PresetId' => $preset_id,
        'SegmentDuration' => $file_data['segment_duration'],
        'ThumbnailPattern' => $file_data['thumbnail_pattern']
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
        'PipelineId' => $file_data['pipeline_id'], 
        'Input' => $input, 
        'Outputs' => $outputs, 
        'OutputKeyPrefix' => $file_data['output_key_prefix'] . $output_key . "/", 
        'Playlists' => array($playlist)
  );

  $create_job_result = $transcoder_client->createJob($create_job_request)->toArray();
}   

function convert_to_HLS($file_data) {
  global $AWSRegion, $AWSPipelineId;

  $transcoder_client = ElasticTranscoderClient::factory(array('region' => $AWSRegion, 'default_caching_config' => '/tmp', 'version' => 'latest'));

  $file_data['pipeline_id'] = $AWSPipelineId;
  //All outputs will have this prefix pre-pended to their output key.
  $file_data['output_key_prefix'] = 'elastic-transcoder/hls/';

  // HLS Presets that will be used to create an adaptive bitrate playlist.
  $hls_64k_audio_preset_id = '1351620000001-200071';
  $hls_0600k_preset_id     = '1351620000001-200040';
  $hls_1000k_preset_id     = '1351620000001-200030';
  $hls_1500k_preset_id     = '1351620000001-200020';

  $file_data['hls_audio_presets'] = array('hlsAudio' => $hls_64k_audio_preset_id);
  $file_data['hls_video_presets'] = array(
    'hls0600k' => $hls_0600k_preset_id,
    'hls1000k' => $hls_1000k_preset_id,
    'hls1500k' => $hls_1500k_preset_id,
  );
  // HLS Segment duration that will be targeted.
  $file_data['segment_duration'] = '2';
  $file_data['thumbnail_pattern'] ='{count}';

  return create_hls_job($transcoder_client, $file_data);
}