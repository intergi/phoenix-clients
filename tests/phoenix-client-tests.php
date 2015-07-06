<?php

include('../php/playwire_client.php');
require(__DIR__.'phpunit.phar');

class PlaywireClientTest extends PHPUnit_Framework_TestCase
{
    protected static $playwire;
    protected static $video_id;

    public function setUp()
    {
        $this->playwire = new PlaywireClient();
        $this->playwire->set_token('onA3prusSB_Dcb2cTg_y');
        $video = (array)$this->playwire->create_video("dogs","http://static.bouncingminds.com/ads/15secs/dogs_600.flv", 1);
        $this->video_id = $video['id'];
    }

    public function tearDown()
    {
         $this->playwire = NULL;
    }

    function testClientCreation() {
       $this->assertNotNull($this->playwire);
    }

    function testSettingToken() {
      $this->assertContains('Intergi-Access-Token: onA3prusSB_Dcb2cTg_y', $this->playwire->headers );
    }

    function testVideoCreation() {
        $response = (array) $this->playwire->create_video("dogs","http://static.bouncingminds.com/ads/15secs/dogs_600.flv", 1);
        $this->video_id = $response['id'];
        $this->assertNotNull($response['name']);
    }

    function testBypassVideoCreation() {
        $response = (array) $this->playwire->create_video("dogs", "", 1, array('bypass_encoding' => 'true'));
        $this->video_id = $response['id'];
        $this->assertNotNull($response['name']);
    }

    function testBypassVideoVersionCreation() {
        $response = (array) $this->playwire->create_bypass_video_version($this->video_id, "http://techslides.com/demos/sample-videos/small.mp4", "sd");
        $this->assertTrue($response);
    }

    function testBypassVideoPosterCreation() {
        $response = (array) $this->playwire->create_bypass_video_poster($this->video_id , "http://bellard.org/bpg/3.png");
        $this->assertNotEmpty($response);
    }

    function testVideoUpdate() {
        $response = (array) $this->playwire->update_video($this->video_id, array('name' => 'updated'));
        $this->assertEquals($response['name'],'updated');
    }

    function testVideoGet() {
      $response = (array) $this->playwire->video($this->video_id);
      $this->assertEquals($response['id'],$this->video_id);
    }

    function testVideoPagesGet() {
      $response = (array) $this->playwire->page(3)->per(25)->videos();
      $this->assertNotNull($response);
    }

}

?>
