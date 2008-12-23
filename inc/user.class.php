<?php
/*
 * dWing - a cms aimed to be as bleeding edge as possible
 * Copyright (C) 2004-2008 Arpad Borsos <arpad.borsos@googlemail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * GenericUser class
 *
 * This class provides functions to handle users who are displayed via the page
 */
class GenericUser
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
	 * the OpenIDs of the user
	 *
	 * @var array $mOpenIDs
	 **/
	protected $mOpenIDs = null;

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
			$this->mUserData = Core::$db->queryFirst('SELECT * FROM '.Core::$db->pref.'user WHERE user_id='.$this->mUserId.';');
			// if the user does not exist, still fill in a nick
			if(empty($this->mUserData))
			{
				$this->mUserData = array('nick' => l10n::_('[Deleted]'), 'ugroup_id' => 1);
			}
		}
	}

	/**
	 * fetch the users OpenID URLs
	 *
	 * @return void
	 **/
	private function _fetchOpenIDs()
	{
		if($this->mOpenIDs == null)
		{
			$statement = Core::$db->prepare('SELECT openid FROM '.Core::$db->pref.'openids WHERE user_id=:userId ORDER BY openid ASC;');
			$statement->bindParam(':userId', $this->mUserId, PDO::PARAM_INT);
			$statement->execute();
			$this->mOpenIDs = $statement->fetchAll(PDO::FETCH_COLUMN);
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
		if($aVarName == 'openids')
		{
			$this->_fetchOpenIDs();
			return $this->mOpenIDs;
		}
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
		if($aVarName == 'openids')
		{
			$this->_fetchOpenIDs();
			return $this->mOpenIDs;
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
				$this->mUserData = Core::$db->queryFirst('SELECT * FROM '.Core::$db->pref.'user WHERE user_id='.$this->mUserId.';');
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
		if(!empty($_ENV['OS']) && stripos($_ENV['OS'], 'win') !== false) // windows
			define('Auth_OpenID_RAND_SOURCE', null);

		require_once "Auth/OpenID/Consumer.php";
		require_once "Auth/OpenID/MySQLStore.php";
		require_once "Auth/OpenID/SReg.php";

		Core::$db->connectParent();
		$store = new Auth_OpenID_MySQLStore(Core::$db);
		$store->createTables();
		$consumer = new Auth_OpenID_Consumer($store);
		$authRequest = $consumer->begin($aOpenID);
		Core::$db->setAttribute(PDO::ATTR_AUTOCOMMIT, true);

		if(!$aImmediate)
		{
			$_GET['site'] = 'login'; // fake this get to display any login error
			if(!$authRequest)
			{
				throw new Exception(l10n::_('Authentication error: OpenId invalid'));
			}
			$sregRequest = Auth_OpenID_SRegRequest::build(array('nickname'), array());
			$authRequest->addExtension($sregRequest);
			// do not request these vars if the user is logged in via cookie.
		}
		elseif(!$authRequest)
			return;

		if(empty($_SESSION['returnto']))
			$_SESSION['returnto'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

		$webroot = $GLOBALS['webRoot'];
		header("Location: ".$authRequest->redirectURL(
			$webroot,
			$webroot.'?site=completelogin',
			$aImmediate));
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
		if(!empty($_ENV['OS']) && stripos($_ENV['OS'], 'win') !== false) // windows
			define('Auth_OpenID_RAND_SOURCE', null);

		require_once "Auth/OpenID/Consumer.php";
		require_once "Auth/OpenID/MySQLStore.php";
		require_once "Auth/OpenID/SReg.php";

		Core::$db->connectParent();
		$store = new Auth_OpenID_MySQLStore(Core::$db);
		$store->createTables();
		$consumer = new Auth_OpenID_Consumer($store);
		$returnUrl = $GLOBALS['webRoot'].'?site=completelogin';
		$response = $consumer->complete($returnUrl);
		Core::$db->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
		if(!$response)
			throw new Exception(l10n::_('Consumer->complete failed'));

		if($response->status == Auth_OpenID_SUCCESS)
		{
			$this->checkLogin();
			// get the user ID from the DB or create a new user
			// write the user id to the session for login
			$userData = Core::$db->queryFirst('SELECT * FROM '.Core::$db->pref.'user
				LEFT JOIN '.Core::$db->pref.'openids as openids USING (user_id)
				WHERE openids.openid="'.Core::$db->escape($response->identity_url).'";');
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
			elseif(!empty($this->mUserId))
			{
				// User was already logged in -> set this as a secondary OpenID
				$statement = Core::$db->prepare('
					INSERT INTO '.Core::$db->pref.'openids SET user_id=:userId, openid=:openID;');
				$statement->bindParam(':userId', $this->mUserId, PDO::PARAM_INT);
				$statement->bindParam(':openID', $response->identity_url, PDO::PARAM_STR);
				$statement->execute();
			}
			else
			{
				// on-the-fly account creation
				$nickname = $response->identity_url;
				
				$sregResponse = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
				$sreg = $sregResponse->contents();
				if(!empty($sreg['nickname']))
				{
					$checknick = Core::$db->queryFirst('SELECT user_id FROM '.Core::$db->pref.'user WHERE nick="'.Core::$db->escape($sreg['nickname']).'";');
					if(empty($checknick))
						$nickname = $sreg['nickname'];
				}

				Core::$db->beginTransaction();
				Core::$db->query('
				INSERT INTO '.Core::$db->pref.'user
				SET
					nick="'.Core::$db->escape($nickname).'",
					registered='.time().';');
				
				$this->mUserId = Core::$db->insert_id;;
				
				$statement = Core::$db->prepare('
					INSERT INTO '.Core::$db->pref.'openid SET user_id=:userId, openid=:openID;');
				$statement->bindParam(':userId', $this->mUserId, PDO::PARAM_INT);
				$statement->bindParam(':openID', $response->identity_url, PDO::PARAM_STR);
				$statement->execute();
				Core::$db->commit();

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
			throw new Exception($error);
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

/**
 * Users class
 *
 * This class handles a cache of GenericUser objects
 */
class Users
{
	/**
	 * cache for the users
	 *
	 * @var array $mUserCache
	 **/
	private static $mUserCache = array();

	/**
	 * return a GenericUser object for the specified user
	 *
	 * @param int $aUserId user id of the user we want to get
	 * @return GenericUser
	 **/
	public static function getUser($aUserId)
	{
		if(empty(self::$mUserCache[$aUserId]))
		{
			self::$mUserCache[$aUserId] = new GenericUser($aUserId);
		}
		return self::$mUserCache[$aUserId];
	}
}

/**
 * Usergroup class
 *
 * This class provides functions to handle usergroups and the usergroup cache
 */
class Usergroup
{
	/**
	 * cache for the usergroups
	 *
	 * @var array $mGroupCache
	 **/
	private static $mGroupCache = array();

	/**
	 * returns the specified usergroup
	 *
	 * @param int $aGroupId
	 * @return array
	 **/
	public static function getGroup($aGroupId)
	{
		if(empty(self::$mGroupCache[$aGroupId]))
		{
			self::$mGroupCache[$aGroupId] = Core::$db->queryFirst(
				'SELECT * FROM '.Core::$db->pref.'usergroup WHERE ugroup_id='.(int)$aGroupId.';');
		}
		return self::$mGroupCache[$aGroupId];
	}
}
?>
