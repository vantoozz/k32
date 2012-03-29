<?php defined('SYSPATH') or die('No direct access allowed.');

class Useradmin_Provider_Odnoklassniki extends Provider_OAuth2 {


	private $me = null;

	public function __construct()
	{
		parent::__construct('odnoklassniki');
	}


	private function od_sign_server_server(array $request_params, $secret_key, $token) {
      ksort($request_params);
      $params = '';
      foreach ($request_params as $key => $value) {
        $params .= "$key=$value";
      }
      return md5($params . md5($token.$secret_key));
    }

	public function verify($redirect_url='/user/provider_return/odnoklassniki')
	{
    	if ($code = Arr::get($_REQUEST, 'code')){
    		
			
			$this->redirect_url($redirect_url);
			
			//var_dump($this->consumer);
			//exit($code);
			
	        $this->token = $this->provider->access_token($this->consumer, $code);
			
			
			
			
	        $this->session->set($this->key('access'), $this->token);
			//$this->user_id=$this->provider->user_id;
			
			$params=array('application_key'=>$this->config['public'], 'method'=>'users.getCurrentUser');
			$params['sig']=$this->od_sign_server_server($params, $this->config['secret'], $this->token);
			
			
			$request = OAuth2_Request::factory('credentials', 'GET', 'http://api.odnoklassniki.ru/fb.do?access_token='.$this->token, $params);
			
			try{
				$data=json_decode($request->execute(), true);	
				if(null !== $data){
					$this->me=$data;
					$this->user_id=$data['uid'];
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
		$params=array('application_key'=>$this->config['public'], 'method'=>'users.setStatus', 'status' => $name.' '.$link);
		$params['sig']=$this->od_sign_server_server($params, $this->config['secret'], $token);
		$request = OAuth2_Request::factory('credentials', 'GET', 'http://api.odnoklassniki.ru/fb.do?access_token='.$token, $params);
		try{
			//$request->headers('Content-Type', 'application/json');
		//	$request->headers('Accept', 'application/json');
			$data=json_decode($request->execute(array(CURLOPT_HTTPHEADER=>array('Content-Type: application/x-www-form-urlencoded'))), true);
			if(null !== $data){
				return $data;	
			}
		}
		catch (exception $e){
			return false;	
		}
		
		return null;
		
	}
	
		
	public function friends(){
		$params=array('application_key'=>$this->config['public'], 'method'=>'friends.get');
		$params['sig']=$this->od_sign_server_server($params, $this->config['secret'], $this->token);
		$request = OAuth2_Request::factory('credentials', 'GET', 'http://api.odnoklassniki.ru/fb.do?access_token='.$this->token, $params);
		try{
			//$request->headers('Content-Type', 'application/json');
		//	$request->headers('Accept', 'application/json');
			$data=json_decode($request->execute(array(CURLOPT_HTTPHEADER=>array('Content-Type: application/x-www-form-urlencoded'))), true);
			if(null !== $data){
				return $data;	
			}
		}
		catch (exception $e){
			return false;	
		}
		
		return null;
	}	
		
	public function email(){
		return null;	
	}
	
	public function photo(){
		return (isset($this->me['pic_2'])) ? $this->me['pic_2'] : null;	
	}
	
	
	public function screenname()
	{
		return '';
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
	
	
	public function info()
	{
		return ($this->me) ? $this->me : array();
	}
		
}