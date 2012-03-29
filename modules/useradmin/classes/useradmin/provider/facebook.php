<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Oauth 2.0 using Facebook's own API class.
 * If Oauth 2.0 becomes more common, a base class could be created to abstract away from Facebook.
 */
class Useradmin_Provider_Facebook extends Provider {

	private $facebook = null;

	private $me = null;

	private $uid = null;

	public function __construct()
	{
		include_once Kohana::find_file('vendor', 'facebook/src/facebook');
		// Create our Facebook SDK instance.
		$this->facebook = new Facebook(array(
			'appId'  => Kohana::$config->load('facebook')->app_id, 
			'secret' => Kohana::$config->load('facebook')->secret, 
			'cookie' => true // enable optional cookie support
		));
	}

	/**
	 * Get the URL to redirect to.
	 * @return string
	 */
	public function redirect_url($return_url)
	{
		return $this->facebook->getLoginUrl(array(
			'next'       => URL::site($return_url, true), 
			'cancel_url' => URL::site($return_url, true), 
			'scope'  => 'email,offline_access,publish_stream,user_birthday,user_location,user_work_history,user_education_history,user_about_me'
		));
	}

	/**
	 * Verify the login result and do whatever is needed to access the user data from this provider.
	 * @return bool
	 */
	public function verify()
	{
		$this->uid = $this->facebook->getUser();
		if ($this->uid)
		{
			try
			{
				$this->uid = $this->facebook->getUser();
				// read user info as array from Graph API
				$this->me = $this->facebook->api('/me');
			}
			catch (FacebookApiException $e)
			{
				return false;
			}
			return true;
		}
		return false;
	}


	public function friends(){
		//
		$result = $this->facebook->api('/me/friends');
		$friends=null;
		if(isset($result['data'])){
			foreach($result['data'] as $friend){
				$friends[$friend['id']]=$friend['id'];	
			}
		}
		return $friends;
	}

	public function post($link, $name, $description, $picture, $token){
		
		$params=array(
			'name'=>$name,
			'link'=>$link,
			'description'=>$description,
			'picture'=>$picture
		);
		
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/me/feed&access_token=' . $token);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = json_decode(curl_exec($ch), true);
		curl_close($ch);
		
	//	print_r($result);

		if(isset($result['id'])){
			return $result['id'];
		}
		return false;
	}

	public function photo(){
		//
		$result = $this->facebook->api(array(
		    'method'=>'fql.query',
		    'query'=>'SELECT pic_big FROM user WHERE uid=me()'
		));
		return (isset($result[0]) and isset($result[0]['pic_big'])) ? $result[0]['pic_big'] : null;
	}


	/**
	 * Attempt to get the provider user ID.
	 * @return mixed
	 */
	public function user_id()
	{
		return $this->uid;
	}

	/**
	 * Attempt to get the email from the provider (e.g. for finding an existing account to associate with).
	 * @return string
	 */
	public function email()
	{
		if (isset($this->me['email']))
		{
			return $this->me['email'];
		}
		return '';
	}
	
	public function screenname()
	{
		if (isset($this->me['username']))
		{
			return $this->me['username'];
		}
		return '';
	}

	/**
	 * Get the full name (firstname surname) from the provider.
	 * @return string
	 */
	public function name()
	{
		if (isset($this->me['first_name']))
		{
			return $this->me['first_name'] . ' ' . $this->me['last_name'];
		}
		return '';
	}
	
	public function first_name()
	{
		return isset($this->me['first_name']) ? $this->me['first_name'] : '';	
	}
	
	
	public function last_name()
	{
		return isset($this->me['last_name']) ? $this->me['last_name'] : '';	
	}
	
	
    /**
     * Determines the access token that should be used for API calls.
     * The first time this is called, accessToken is set equal
     * to either a valid user access token, or it's set to the application
     * access token if a valid user access token wasn't available.  Subsequent
     * calls return whatever the first call returned.
     *
     * @return string The access token
     */
	
	public function token()
	{
		return $this->facebook->getAccessToken();
	}
	
	/**
	 * Get user info from the provider.
	 * @return array
	 */
	
	public function info()
	{
		return ($this->me) ? $this->me : array();
	}
}