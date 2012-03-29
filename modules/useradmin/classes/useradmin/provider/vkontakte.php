<?php defined('SYSPATH') or die('No direct access allowed.');

class Useradmin_Provider_Vkontakte extends Provider_OAuth2 {


	private $me = null;
	protected $user_id = null;

	public function __construct()
	{
		parent::__construct('vkontakte');
	}


	private function get_access_token(OAuth2_Client $client, $code, array $params = NULL)
	{
		
		try{
			$response = file_get_contents('https://api.vkontakte.ru/oauth/access_token?client_id='.$client->id.'&client_secret='.$client->secret.'&code='.$code.'&grant_type=authorization_code');
		
			$response=json_decode($response, true);
		
			$this->user_id=$response['user_id'];
	
			return OAuth2_Token::factory('access', array(
				'token' => $response['access_token']
			));
		}
		catch(exception $e){
			return null;
		}
		
	}


	public function verify()
	{
    	if ($code = Arr::get($_REQUEST, 'code')){
	        $this->token = $this->get_access_token($this->consumer, $code);
	        $this->session->set($this->key('access'), $this->token);
			$this->user_id=$this->user_id;
			
			$request = OAuth2_Request::factory('credentials', 'GET', 'https://api.vkontakte.ru/method/getProfiles', array(     
				'uids' => $this->user_id,  
				'fields' =>  'uid, first_name, last_name, nickname, screen_name, sex, bdate (birthdate), city, country, timezone, photo, photo_medium, photo_big, has_mobile, rate, contacts, education, online, counters'      
			));
			
			try{
				$data=json_decode($request->execute(), true);	
				if(null !== $data){
					if(isset($data['response']) and !empty($data['response'])){
						$this->me=$data['response'][0];
					}
				}
				else{
					return false;
				}
			}
			catch (exception $e){
				return false;	
			}
			
			return true;
    	}
		else{
			return false;
		}
	}
	
	
	public function post($link, $name, $description, $picture, $token){
		
		
		$request = OAuth2_Request::factory('credentials', 'POST', 'https://api.vkontakte.ru/method/wall.post', array(     
				'timestamp' => time(),  
				'random' => rand(10000, 99999),
				'message'=>$description.' — '.$link,  
				'access_token'=>$token,
				      
			));
		$result=null;
		try{
			$data=json_decode($request->execute(), true);
			var_dump($data);
			if(isset($data['response'])){
				$result=$data['response'];
			}
		}
		catch (exception $e){
			return false;	
		}
		return $result;
	}

	public function friends(){
	//	return null;
		$request = OAuth2_Request::factory('credentials', 'GET', 'https://api.vkontakte.ru/method/friends.get', array(     
				'timestamp' => time(),  
				'random' => rand(10000, 99999),  
				'access_token'=>$this->token
				      
			));
			$friends=null;
		try{
			$data=json_decode($request->execute(), true);
			
			if($data['response']){
				foreach($data['response'] as $friend){
					$friends[$friend]=$friend;
				}
			}
		}
		catch (exception $e){
			return false;	
		}
		return $friends;
	}
		
	public function photo(){
		return isset($this->me['photo_big']) ? $this->me['photo_big'] : null;	
	}
		
		
	public function email(){
		return null;	
	}
	
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
	
	public function screenname()
	{
		if (isset($this->me['screen_name']))
		{
			return $this->me['screen_name'];
		}
		return '';
	}
	
	
	public function info()
	{
		return ($this->me) ? $this->me : array();
	}
		
}