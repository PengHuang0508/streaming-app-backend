<?php
namespace converter;

use Aws\ElasticTranscoder\ElasticTranscoderClient;

function convert_to_HLS($input_key) {
  $region = 'us-west-2';
  $transcoder_client = ElasticTranscoderClient::factory(array('region' => $region, 'default_caching_config' => '/tmp', 'version' => 'latest'));

  // Elastic pipeline id
  $pipeline_id = '';

  //All outputs will have this prefix pre-pended to their output key.
  $output_key_prefix = 'elastic-transcoder/hls/';

  // HLS Presets that will be used to create an adaptive bitrate playlist.
  $hls_64k_audio_preset_id = '1351620000001-200071';
  $hls_0600k_preset_id     = '1351620000001-200040';
  $hls_1000k_preset_id     = '1351620000001-200030';
  $hls_1500k_preset_id     = '1351620000001-200020';

  $hls_audio_presets = array('hlsAudio' => $hls_64k_audio_preset_id);
  $hls_video_presets = array(
    'hls0600k' => $hls_0600k_preset_id,
    'hls1000k' => $hls_1000k_preset_id,
    'hls1500k' => $hls_1500k_preset_id,
  );
  // HLS Segment duration that will be targeted.
  $segment_duration = '2';

  $thumbnail_pattern ='{count}';

  function create_hls_job($transcoder_client, $pipeline_id, $input_key, $output_key_prefix, $hls_audio_presets, $hls_video_presets, $segment_duration, $thumbnail_pattern) {
    $input = array('Key' => $input_key);
  
    $output_key = $input_key;

    // Specify the outputs based on the hls presets array specified.
    $outputs = array();

    foreach ($hls_audio_presets as $prefix => $preset_id) {
      array_push($outputs,
        array('Key' => "$prefix/$output_key",
        'PresetId' => $preset_id,
        'SegmentDuration' => $segment_duration,
      ));
    }

    foreach ($hls_video_presets as $prefix => $preset_id) {
      array_push($outputs,
        array('Key' => "$prefix/$output_key",
        'PresetId' => $preset_id,
        'SegmentDuration' => $segment_duration,
        'ThumbnailPattern' => $thumbnail_pattern
      ));
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
          'PipelineId' => $pipeline_id, 
          'Input' => $input, 
          'Outputs' => $outputs, 
          'OutputKeyPrefix' => "$output_key_prefix$output_key/", 
          'Playlists' => array($playlist)
    );
    $create_job_result = $transcoder_client->createJob($create_job_request)->toArray();

    return $output_key;
  }   
  
  $result_json['media_key'] = create_hls_job($transcoder_client, $pipeline_id, $input_key, $output_key_prefix, $hls_audio_presets, $hls_video_presets, $segment_duration, $thumbnail_pattern);
  
  
  return  json_encode($result_json);
}