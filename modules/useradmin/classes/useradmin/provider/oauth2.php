<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Oauth 1.0 using Kohana's bundled OAuth module.
 *
 * Kohana's bundled OAuth module supports Twitter (and Google) as providers.
 * 
 */
abstract class Useradmin_Provider_OAuth2 extends Provider {


	protected $provider;
	protected $session;
	protected $token;
	protected $oauth;
	protected $provider_name;
	protected $consumer;
	protected $user_id;
	
	protected $config;

	public function __construct($provider)
	{
		

		$this->provider_name = $provider;
		$this->oauth = new OAuth2;
		
		$this->config = Kohana::$config->load('oauth2.' . $this->provider_name);
		//var_dump(Kohana::$config->load('oauth2.' . $this->provider_name));

		$this->consumer = OAuth2_Client::factory($this->config);
		$this->session = Session::instance();
		$this->provider = $this->oauth->provider($this->provider_name);
		if ($token = $this->session->get($this->key('access')))
		{
			$this->token = $token;
		}
	}

	/**
	 * Get the URL to redirect to.
	 * @return string
	 */
	public function redirect_url($return_url)
	{
		// Add the callback URL to the consumer
		$this->consumer->callback(URL::site($return_url, true));
		return $this->provider->authorize_url($this->consumer, $this->config['params']);
	}
	
	
	public function verify()
	{
    	if ($code = Arr::get($_REQUEST, 'code')){
	        $this->token = $this->provider->access_token($this->consumer, $code);
	        $this->session->set($this->key('access'), $this->token);
			$this->user_id=$this->provider->user_id;
    	}
		else{
			return false;
		}
	}
	
	public function key($name)
	{
		return "oauth2_{$this->provider->name}_{$name}";
	}
	
	public function token()
	{
		return $this->token;
	}

	public function user_id(){
		return $this->user_id;	
	}


	public function email(){
		
	}

	public function photo(){
		return  null;	
	}

	public function name(){
		
	}
}