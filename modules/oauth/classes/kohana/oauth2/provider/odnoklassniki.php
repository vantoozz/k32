<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Kohana_OAuth2_Provider_Odnoklassniki extends OAuth2_Provider {

	public $name = 'odnoklassniki';

	public function url_authorize()
	{
		return 'http://www.odnoklassniki.ru/oauth/authorize';
	}

	public function url_access_token()
	{
		return 'http://api.odnoklassniki.ru/oauth/token.do';
	}


}
