<?
/**
 * Wrapper class around the Twitter Search API for PHP
 * Based on the class originally developed by David Billingham
 * and accessible at http://twitter.slawcup.com/twitter.class.phps
 * @author Ryan Faerman <ryan.faerman@gmail.com>
 * @version 0.2
 * @package PHPTwitterSearch
 */
class PlaywireClient {

    const GET     = 'GET';
    const POST    = 'POST';
    const PUT     = 'PUT';
    const DELETE  = 'DELETE';

    /**
     * API Token
     * @var boolean
     */
    var $token = '';


  /**
   * Can be set to JSON (requires PHP 5.2 or the json pecl module) or XML - json|xml
   * @var string
   */
  var $type = 'json';

  /**
   * @var array
   */
  var $headers = array(
    'X-Playwire-Client: PHPPlaywireClient',
    'X-Playwire-Client-Version: 0.1',
    'X-Playwire-Client-URL: http://www.playwire.com'
    );

  /**
   * Recommend setting a user-agent so Playwire knows how to contact you in case of abuse. Include your email
   * @var string
   */
  var $user_agent = '';

  /**
   * @var array
   */
  var $responseInfo = array();

  /**
   * @var string
   */
  var $baseURL = 'http://phoenix.playwire.com/api';

  /**
   * @var string - GET|POST|PUT|DELETE
   */
  var $method = PlaywireClient::GET;

  /**
   * The number of results to return per page, max 100
   * @var integer
   */
  var $per;

  /**
   * The page number to return
   * @var integer
   */
  var $page;

  /**
   * cURL should be verbose
   * @var integer 1|0
   */
  var $verbose = 0;


  /**
   * Accepts a variable number of arguments to initialize the Client.
   * A single argument is assumed to be the API Token, two arguments are
   * assumed to be the login and password (in that order). All arguments
   * here are optional and authentication can be performed later.
   *
   * @param string $token optional
   * OR
   * @param string $login     optional
   * @param string $password  optional
   */
  public function PlaywireClient() {
    switch(func_num_args()) {
      case 1:
        # assume it's the token
        $this->set_token(func_get_arg(0));
        break;
      case 2:
        # assume it's a login/password
        $this->authenticate(func_get_arg(0), func_get_arg(1));
        break;
      default:
        # do nothing
        break;
    }
  }

  /**
  * @param string $login    required
  * @param string $password required
  */
  public function authenticate($login, $password) {
    $response = $this->post('/users/login', array('login' => $login, 'password' => $password));
    $this->set_token($response->token);
    return $this;
  }

  /**
   * List videos in the account.
   *
   * When provided with the optional `$id`, it will return the
   * video data for that `$id`.
   *
   * @param string $id optional
   * @return array(object) OR object
   */
  public function videos($id = false) {
    $endpoint = '/videos';
    if($id) {
      $endpoint .= '/'.$id;
    }
    return $this->get($endpoint);
  }

  /**
   * Retrieve a single video.
   *
   * This is a wrapper for clarity.
   *
   * @param string $id required
   * @return object
   */
  public function video($id) {
    return $this->videos($id);
  }

  /**
   * Create a video.
   *
   * The `$source_url` should be a direct, publically accessible URL
   * that resolves directly to the video file.
   *
   * @param string  $name            required
   * @param string  $source_url      required
   * @param integer $category_id     required
   * @param array   $optional        optional   Refer to: http://kb.intergi.com/kb/PHX3146/
   * @return object
   */
  public function create_video($name, $source_url, $category_id, $optional=array()) {
    if (isset($optional['bypass_encoding']) && $optional['bypass_encoding'] == 'true') {
      $data = array_merge($optional, array('name' => $name, 'category_id' => $category_id));
    } else {
      $data = array_merge($optional, array('name' => $name, 'source_url' => $source_url, 'category_id' => $category_id));
    }
    return $this->post('/videos', $this->wrap_keys($data, 'video'));
  }

  /**
   * Create a bypassed video.
   *
   * @param string  $name        required
   * @param integer $category_id required
   * @param array   $optional    optional   Refer to: http://kb.intergi.com/kb/PHX3146/
   * @return object
   */
  public function create_bypass_video($name, $category_id, $optional=array()) {
    $data = array_merge($optional, array('name' => $name, 'category_id' => $category_id, 'bypass_encoding' => true));
    return $this->post('/videos', $this->wrap_keys($data, 'video'));
  }

  /**
   * Create a bypassed video version.
   *
   * @param integer $video_id   required
   * @param string  $source_url required
   * @param string  $resolution required
   * @return object
   */
  public function create_bypass_video_version($video_id, $source_url, $resolution) {
    $data = array('source_url' => $source_url, 'resolution' => $resolution);
    return $this->post('/videos/'.$video_id.'/versions', $this->wrap_keys($data, 'version'));
  }

  /**
   * Create a bypassed video poster.
   *
   * @param integer $video_id   required
   * @param string  $poster_url required
   * @return object
   */
  public function create_bypass_video_poster($video_id, $poster_url) {
    $data = array('poster_url' => $poster_url);
    return $this->post('/videos/'.$video_id.'/posters', $this->wrap_keys($data, 'poster'));
  }

  /**
   * Update a video.
   *
   * Updates a video with the data from `$changes` and returns the video object.
   *
   * @param integer $id      required
   * @param array   $changes optional   Refer to: http://kb.intergi.com/kb/PHX3146/
   * @return object
   */
  public function update_video($id, $changes=array()) {
    return $this->put("/videos/$id", $this->wrap_keys($changes, 'video'));
  }


  /**
   * Delete a video.
   *
   * Deletes a video determined by the `$id` and returns the video object.
   *
   * @param integer $id      required
   * @return object
   */
  public function delete_video($id) {
    return $this->delete("/videos/$id");
  }

  /**
   * List videos from the Sandbox.
   *
   * When provided with the optional `$id`, it will return the
   * video data for that `$id`.
   *
   * @param integer $id optional
   * @return array(object) OR object
   */
  public function sandbox_videos($id = false) {
    if($id) {
      return $this->videos($id);
    } else {
      return $this->get('/sandbox/videos');
    }
  }

  /**
   * Import a video into your library from the sandbox.
   *
   * @param integer $id required
   * @return object
   */
  public function sandbox_import($id) {
    return $this->post('/videos/import', array('id' => $id));
  }

  /**
  * @param integer $n required
  * @return object
  */
  function per($n) {
    $this->per = $n;
    return $this;
  }

  /**
  * @param integer $n required
  * @return object
  */
  function page($n) {
    $this->page = $n;
    return $this;
  }

  /**
   * Set the token and add it to the headers
   *
   * @param string $token required
   */
  public function set_token($token) {
    $this->token = $token;
    if($this->token) {
      array_push($this->headers, "Intergi-Access-Token: $this->token");
    }
  }

  /**
   * Perform a POST request.
   *
   * @param string  $endpoint required
   * @param array   $data     optional
   */
  protected function post($endpoint, $data=false) {
    $this->method = PlaywireClient::POST;
    return $this->request($endpoint, $data);
  }

  /**
   * Perform a GET request.
   *
   * @param string  $endpoint required
   * @param array   $data     optional
   */
  protected function delete($endpoint, $data=false) {
    $this->method = PlaywireClient::DELETE;
    return $this->request($endpoint, $data);
  }

  /**
   * Perform a PUT request.
   *
   * @param string  $endpoint required
   * @param array   $data     optional
   */
  protected function put($endpoint, $data=false) {
    $this->method = PlaywireClient::PUT;
    return $this->request($endpoint, $data);
  }

  /**
   * Perform a GET request.
   *
   * @param string  $endpoint required
   * @param array   $data     optional
   */
  protected function get($endpoint, $data=false) {
    $paging = '';
    if($this->page || $this->per) {
      $paging = $this->urlify(array('page' => $this->page, 'per' => $this->per));
    }
    if($data) {
      $endpoint .= '/?'.$this->urlify($data).$paging;
    } else {
      $endpoint .= '/?'.$paging;
    }

    $this->method = PlaywireClient::GET;
    return $this->request($endpoint, $data);
  }

  /**
   * Perform a request.
   * This helper method wraps up some of the nitty-gritty
   * with the `baseURL` and encoding/decoding data in the
   * response and request.
   *
   * @param string  $endpoint required
   * @param array   $data     optional
   */
  protected function request($endpoint, $data=false) {
    $url = $this->baseURL.$endpoint;
    $post_data = $data ? $this->urlify($data) : false;
    return $this->objectify($this->process($url, $post_data, $this->method));
  }

  /**
   * Modify data to wrap array keys in a provided key.
   * This is used when the data needs to be pushed as
   * something like `video[some_key]` to the API. This
   * helps keep the other methods clearer when in use.
   *
   * @param array   $data     required
   * @param string  $wrapper  required  The new key to use as the parent
   * @return array
   */
  protected function wrap_keys($data, $wrapper) {
    $output = array();
    foreach($data as $key => $value) {
      $key = $wrapper.'['.$key.']';
      $output[$key] = $value;
    }
    return $output;
  }

  /**
   * Prepare array of data for transmission.
   * Each key is preserved while each value is URL encoded.
   *
   * @param array $data required
   * @return string
   */
  protected function urlify($data) {
    $urlified = array();
    foreach($data as $key => $value) {
      $encoded_value = urlencode($value);
      array_push($urlified, "$key=$encoded_value");
    }
    return implode("&", $urlified);
  }

  /**
   * Internal function where all the juicy curl fun takes place
   * this should not be called by anything external unless you are
   * doing something else completely then knock youself out.
   * @access private
   * @param string $url Required. API URL to request
   * @param string $postargs Optional. Urlencoded query string to append to the $url
   */
  private function process($url, $data=false) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);

    # Only need POSTFIELDS for methods that have bodies.
    if($data && in_array($this->method, array(PlaywireClient::POST, PlaywireClient::PUT))) {
      if(is_array($data)) {
        $data = $this->urlify($data);
      }

      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    curl_setopt($ch, CURLOPT_VERBOSE, $this->verbose);
    curl_setopt($ch, CURLOPT_NOBODY, 0);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

    $response = curl_exec($ch);

    $this->responseInfo=curl_getinfo($ch);
    curl_close($ch);

    return $response;
  }

  /**
   * Function to prepare data for return to client
   * @access private
   * @param string $data
   */
  private function objectify($data) {
    if( $this->type ==  'json' )
      return (object) json_decode($data);

    else if( $this->type == 'xml' ) {
      if( function_exists('simplexml_load_string') ) {
        $obj = simplexml_load_string( $data );

        $statuses = array();
        foreach( $obj->status as $status ) {
          $statuses[] = $status;
        }
        return (object) $statuses;
      }
      else {
        return $out;
      }
    }
    else
      return false;
  }
}
?>
