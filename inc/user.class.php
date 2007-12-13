<?php
/*
 * dWing - a nextgen CMS built on PHP5, MySQLi and Ajax
 * Copyright (C) 2004-2007 Arpad Borsos <arpad.borsos@googlemail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * GenericUser class
 *
 * This class provides functions to handle users who are displayed via the page
 */
class GenericUser extends Module
{
	/**
	 * the users id
	 *
	 * @var int $mUserId
	 **/
	protected $mUserId = null;
	/**
	 * the users data
	 *
	 * @var array $mUserData
	 **/
	protected $mUserData = null;

	/**
	 * constructor
	 *
	 * @param int $aUserId the user id
	 * @return void
	 **/
	public function __construct($aUserId)
	{
		$this->mUserId = $aUserId;
	}

	/**
	 * fetch the users data into $mUserData
	 *
	 * @return void
	 **/
	private function _fetchData()
	{
		if(empty($this->mUserData))
		{
			$this->mUserData = self::$_db->queryFirst('SELECT * FROM '.self::$_db->pref.'user WHERE user_id='.$this->mUserId.';');
			// if the user does not exist, still fill in a nick
			if(empty($this->mUserData))
			{
				$this->mUserData = array('nick' => l10n::_('[Deleted]'), 'ugroup_id' => 1);
			}
		}
	}

	/**
	 * getter
	 *
	 * @param int $aVarName what kind of info we want to get from the user
	 * @return mixed
	 **/
	public function __get($aVarName)
	{
		$this->_fetchData();
		return isset($this->mUserData[$aVarName]) ? $this->mUserData[$aVarName] : null;
	}
}

/**
 * CurrentUser class
 *
 * This class provides functions to handle the user which is currently
 * looking at the site
 */
class CurrentUser extends GenericUser
{
	/**
	 * constructor
	 * this just overrides the patent's constructor
	 *
	 * @return void
	 **/
	public function __construct()
	{}
	
	/**
	 * init
	 * handles the users sign in process
	 *
	 * @return void
	 **/
	public function init()
	{
		/*
		OpenID logic:

		1. beginLogin()

		2. completeLogin()
		 1. If the user is already known, write our users Id to the session
		 2. If the user is unknown, create a new user account with the provided
		  simple registration infos.
		 (3. A fallback form to fill out required user infos if the OpenID server
		  does not support the simple registration extension?)
		*/
		if(!empty($_POST['openid_url']))
		{
			return $this->beginLogin($_POST['openid_url']);
		}
		if(!empty($_GET['site']) && $_GET['site'] == 'completelogin') // catch the response from the OpenID server
		{
			return $this->completeLogin();
		}
		if(!empty($_COOKIE['openid_url']) && empty($_SESSION['uid']) &&
			(empty($_GET['site']) || $_GET['site'] != 'completelogin'))
		{
			return $this->beginLogin($_COOKIE['openid_url'], true);
		}
	}

	/**
	 * getter
	 *
	 * @param int $aVarName what kind of info we want to get from the user
	 * @return mixed
	 **/
	public function __get($aVarName)
	{
		$this->_fetchData();
		if($aVarName == 'authed')
		{
			return $this->mUserId != 0;
		}
		return isset($this->mUserData[$aVarName]) ? $this->mUserData[$aVarName] : null;
	}

	/**
	 * fetch the users data into $mUserData
	 *
	 * @return void
	 **/
	private function _fetchData()
	{
		if(empty($this->mUserData))
		{
			if(is_null($this->mUserId))
			{
				$this->checkLogin(); // check if the user is known or not
			}
			if($this->mUserId == 0) // user is a guest
			{
				$this->mUserData = array('nick' => l10n::_('[Guest]'), 'ugroup_id' => 1);
			}
			else
			{
				$this->mUserData = self::$_db->queryFirst('SELECT * FROM '.self::$_db->pref.'user WHERE user_id='.$this->mUserId.';');
				// if the user does not exist, still fill in a nick
				if(empty($this->mUserData))
				{
					$this->mUserData = array('nick' => l10n::_('[Deleted]'), 'ugroup_id' => 1);
				}
			}
		}
	}

	/**
	 * checks if the user has a certain right
	 *
	 * @param string $aRight the right for which to check
	 * @return bool
	 **/
	public function hasRight($aRight)
	{
		$this->_fetchData();
		$groupData = UserGroup::getGroup($this->mUserData['ugroup_id']);
		return !empty($groupData[$aRight]);
	}

	/**
	 * check if the user is logged in or not
	 *
	 * @return void
	 **/
	public function checkLogin()
	{
		if(!empty($_SESSION['uid']))
		{
			$this->mUserId = $_SESSION['uid'];
		}
		// is there a method to auto-login with OpenID?
		else
		{
			$this->mUserId = 0;
		}
	}

	/**
	 * First step of OpenID login logic
	 * this method redirects the user to his OpenID Server
	 *
	 * @param string $aOpenID the OpenID url
	 * @param bool $aImmediate false, 
	 * @return void
	 **/
	private function beginLogin($aOpenID, $aImmediate = false)
	{
		if(stripos($_ENV['OS'], 'win') !== false) // windows
			define('Auth_OpenID_RAND_SOURCE', null);

		$oldErrorReporting = error_reporting();
		error_reporting(0); // this throws a few notices
		require_once "Auth/OpenID/Consumer.php";
		require_once "Auth/OpenID/FileStore.php";
		require_once "Auth/OpenID/SReg.php";

		$store = new Auth_OpenID_FileStore('./openidstore/');
		$consumer = new Auth_OpenID_Consumer($store);
		$authRequest = $consumer->begin($aOpenID);

		if(!$aImmediate)
		{
			$_GET['site'] = 'login'; // fake this get to display any login error
			if(!$authRequest)
			{
				error_reporting($oldErrorReporting);
				return new Exception(l10n::_('Authentication error: OpenId invalid'));
			}
			$sregRequest = Auth_OpenID_SRegRequest::build(array('nickname'), array());
			$authRequest->addExtension($sregRequest);
			// do not request these vars if the user is logged in via cookie.
		}
		elseif(!$authRequest)
		{
			error_reporting($oldErrorReporting);
			return;
		}

		header("Location: ".$authRequest->redirectURL(
			'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/',
			'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/?site=completelogin',
			$aImmediate));
		error_reporting($oldErrorReporting);
		exit;
	}

	/**
	 * second step of OpenID login logic
	 * this method anayses the answer from the OpenID Server and logs the user in
	 * it also creates user profiles on the fly if they don't exist already
	 *
	 * @return void
	 **/
	private function completeLogin()
	{
		if(stripos($_ENV['OS'], 'win') !== false) // windows
			define('Auth_OpenID_RAND_SOURCE', null);

		$oldErrorReporting = error_reporting();
		error_reporting(0); // this throws a few notices

		require_once "Auth/OpenID/Consumer.php";
		require_once "Auth/OpenID/FileStore.php";
		require_once "Auth/OpenID/SReg.php";

		$store = new Auth_OpenID_FileStore('./openidstore/');
		$consumer = new Auth_OpenID_Consumer($store);
		$returnUrl = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/?site=completelogin';
		$response = $consumer->complete($returnUrl);
		if(!$response)
		{
			error_reporting($oldErrorReporting);
			return new Exception(l10n::_('Consumer->complete failed'));
		}

		if($response->status == Auth_OpenID_SUCCESS)
		{
			// get the user ID from the DB or create a new user
			// write the user id to the session for login
			$userData = self::$_db->queryFirst('SELECT * FROM '.self::$_db->pref.'user
				WHERE openid="'.self::$_db->escape($response->identity_url).'";');
			if(!empty($userData))
			{
				// user already exists
				// TODO: sync our user profile with the data provided by the OpenID server?
				$this->mUserData = $userData;
				$this->mUserId = $userData['user_id'];
				$_SESSION['uid'] = $this->mUserId;

				if(empty($_COOKIE['openid_url']))
					setcookie('openid_url', $response->identity_url, time()+3600*24*365);
			}
			else
			{
				// on-the-fly account creation
				$nickname = $response->identity_url;
				
				$sregResponse = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
				$sreg = $sregResponse->contents();
				if(!empty($sreg['nickname']))
				{
					$checknick = self::$_db->queryFirst('SELECT user_id FROM '.self::$_db->pref.'user WHERE nick="'.self::$_db->escape($sreg['nickname']).'";');
					if(empty($checknick))
						$nickname = $sreg['nickname'];
				}

				self::$_db->query('
				INSERT INTO '.self::$_db->pref.'user
				SET
					nick="'.self::$_db->escape($nickname).'",
					openid="'.self::$_db->escape($response->identity_url).'",
					registered='.time().';');

				$this->mUserId = self::$_db->insert_id;;
				$_SESSION['uid'] = $this->mUserId;
				setcookie('openid_url', $response->identity_url, time()+3600*24*365);
			}
			if(!empty($_SESSION['returnto']))
			{
				header('Location: '.$_SESSION['returnto']);
				unset($_SESSION['returnto']);
				exit;
			}
			else
			{
				header('Location: ./');
				exit;
			}
		}
		else // OpenID invalid -> no login!
		{
			error_reporting($oldErrorReporting);
			if($response->status ==  Auth_OpenID_SETUP_NEEDED)
			{
				setcookie('openid_url', '', 1);
				return;
			}
			if($response->status == Auth_OpenID_CANCEL)
				$error = l10n::_('Verification cancelled.');
			elseif($response->status == Auth_OpenID_FAILURE)
				$error = sprintf(l10n::_('OpenID authentication failed: %s'), $response->message);
			$_GET['site'] = 'login';
			return new Exception($error);
		}
	}

	/**
	 * logs a user out
	 *
	 * @return void
	 **/
	public function logout()
	{
		$this->mUserData = null;
		$this->mUserId = null;
		unset($_SESSION['uid']);
		setcookie('openid_url', '', 1);
	}
}
?>