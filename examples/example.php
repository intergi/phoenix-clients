<?php

require('../php/playwire_client.php');

$playwire = new PlaywireClient();

$playwire->set_token('YOUR_TOKEN_HERE');

$playwire->create_video("dogs","http://static.bouncingminds.com/ads/15secs/dogs_600.flv", 1);

$playwire->create_video("Video Bypassed", "", 1, array('bypass_encoding' => 'true'));

$playwire->create_bypass_video_version('BYPASSED_VIDEO_ID_HERE', "http://techslides.com/demos/sample-videos/small.mp4", "sd");

$playwire->create_bypass_video_poster('BYPASSED_VIDEO_ID_HERE', "http://bellard.org/bpg/3.png");

print_r($playwire->videos());

?>
