<?php

require('../php/playwire_client.php');



$playwire = new PlaywireClient();

$playwire->set_token('YOUR_TOKEN_HERE');

playwire->create_video("dogs","http://static.bouncingminds.com/ads/15secs/dogs_600.flv", 1);

print_r($playwire->videos());


?>
