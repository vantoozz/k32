<?php defined('SYSPATH') or die('No direct access allowed.');

class Useradmin_Provider_Twitter extends Provider_OAuth {

	/**
	 * Data storage
	 * @var int
	 */
	private $uid = null;

	private $data = null;
	
	private $token = '';
	
	private $friends = null;

	public function __construct()
	{
		parent::__construct('twitter');
	}

	/**
	 * Verify the login result and do whatever is needed to access the user data from this provider.
	 * @return bool
	 */
	public function verify()
	{
		// create token
		$request_token = OAuth_Token::factory('request', array(
			'token' => Session::instance()->get('oauth_token'), 
			'secret' => Session::instance()->get('oauth_token_secret')
		));
		// Store the verifier in the token
		$request_token->verifier($_REQUEST['oauth_verifier']);
		// Exchange the request token for an access token
		$access_token = $this->provider->access_token($this->consumer, $request_token);
		if ($access_token and $access_token->name === 'access')
		{
			// @link  http://dev.twitter.com/doc/get/account/verify_credentials
			$request = OAuth_Request::factory('resource', 'GET', 'http://api.twitter.com/1/account/verify_credentials.json', array(
				'oauth_consumer_key' => $this->consumer->key, 
				'oauth_token' => $access_token->token
			));
			// Sign the request using only the consumer, no token is available yet
			$request->sign(new OAuth_Signature_HMAC_SHA1(), $this->consumer, $access_token);
			// decode and store data
			$data = json_decode($request->execute(), true);
			$this->uid = $data['id'];
			$this->data = $data;
			$this->token = serialize($access_token);
			
			
			$request = OAuth_Request::factory('resource', 'GET', 'http://api.twitter.com/1/friends/ids.json', array(
				'oauth_consumer_key' => $this->consumer->key, 
				'oauth_token' => $access_token->token
			));
			// Sign the request using only the consumer, no token is available yet
			$request->sign(new OAuth_Signature_HMAC_SHA1(), $this->consumer, $access_token);
			$request->sign(new OAuth_Signature_HMAC_SHA1(), $this->consumer, $access_token);
			// decode and store data
			$this->friends = json_decode($request->execute(), true);
			
			
			return true;
		}
		else
		{
			return false;
		}
	}

	public function post($link, $name, $description, $picture, $token){
		
		$access_token=unserialize($token);
		
		$request = OAuth_Request::factory('resource', 'POST', 'http://api.twitter.com/1/statuses/update.json', array(
			'oauth_consumer_key' => $this->consumer->key, 
			'oauth_token' => $access_token->token,
			'status' => $name.' '.$link
		));
		// Sign the request using only the consumer, no token is available yet
		$request->sign(new OAuth_Signature_HMAC_SHA1(), $this->consumer, $access_token);
		// decode and store data
		return json_decode($request->execute(), true);
		
	}

	public function friends(){
		if($this->friends and $this->friends['ids']){
			return $this->friends['ids'];	
		}
		else return null;
	}

	/**
	 * Attempt to get the provider user ID.
	 * @return mixed
	 */
	public function user_id()
	{
		return $this->uid;
	}
	
	public function photo(){
		return (isset($this->data['profile_image_url'])) ? $this->data['profile_image_url'] : null;	
	}

	/**
	 * Attempt to get the email from the provider (e.g. for finding an existing account to associate with).
	 * @return string
	 */
	public function email()
	{
		if (isset($this->data['email']))
		{
			return $this->data['email'];
		}
		return '';
	}
	
	public function screenname()
	{
		if (isset($this->data['screen_name']))
		{
			return $this->data['screen_name'];
		}
		return '';
	}

	/**
	 * Get the full name (firstname surname) from the provider.
	 * @return string
	 */
	public function name()
	{
		if (isset($this->data['name']))
		{
			return $this->data['name'];
		}
		else 
			if (isset($this->data['screen_name']))
			{
				return $this->data['screen_name'];
			}
		return '';
	}
	
	public function first_name()
	{
		if(isset($this->data['name'])){
			$name=explode(' ', $this->data['name']);
			if(isset($name[0]))return $name[0];
		}
		return '';
	}
	
	
	public function last_name()
	{
		if(isset($this->data['name'])){
			$name=explode(' ', $this->data['name']);
			if(isset($name[1]))return $name[1];
		}
		return '';
	}
	
	
	
	 /**
     * @return string The access token
     */
	
	public function token()
	{
		return $this->token;
	}
	
	/**
	 * Get user info from the provider.
	 * @return array
	 */
	
	public function info()
	{
		return ($this->data) ? $this->data : array(); 
	}
	
}
