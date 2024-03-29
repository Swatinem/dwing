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
	 * all previous nicknames of the user
	 **/
	protected $nicks = null;
	/**
	 * prepared statements to fetch user data or openids
	 */
	protected static $selectStmt = null;
	protected static $openIdStmt = null;
	protected static $nickStmt   = null;

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
	protected function fetchData()
	{
		if(empty($this->mUserData))
		{
			if(empty(self::$selectStmt))
			{
				self::$selectStmt = Core::$db->prepare('
					SELECT user_id, ugroup_id, n1.nick, n2.since AS registered
					FROM `'.Core::$prefix.'user` AS u
					JOIN '.Core::$prefix.'nicknames AS n1 USING ( user_id )
					JOIN '.Core::$prefix.'nicknames AS n2 USING ( user_id )
					WHERE n1.since = (SELECT max( since ) FROM '.Core::$prefix.'nicknames WHERE user_id = u.user_id )
					AND n2.since = (SELECT min( since ) FROM '.Core::$prefix.'nicknames WHERE user_id = u.user_id )
					AND u.user_id = :userId;
				');
			}
			$stmt = self::$selectStmt;
			$stmt->bindValue(':userId', (int)$this->mUserId, PDO::PARAM_INT);
			$stmt->execute();
			$this->mUserData = $stmt->fetch(PDO::FETCH_ASSOC);
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
	protected function fetchOpenIDs()
	{
		if($this->mOpenIDs == null)
		{
			if(empty(self::$openIdStmt))
			{
				self::$openIdStmt = Core::$db->prepare('SELECT openid FROM '.Core::$prefix.'openids WHERE user_id=:userId ORDER BY openid ASC;');
			}
			$statement = self::$openIdStmt;
			$statement->bindParam(':userId', $this->mUserId, PDO::PARAM_INT);
			$statement->execute();
			$this->mOpenIDs = $statement->fetchAll(PDO::FETCH_COLUMN);
		}
	}

	/**
	 * fetch the users nicknames
	 *
	 * @return void
	 **/
	protected function fetchNicks()
	{
		if($this->nicks == null)
		{
			if(empty(self::$nickStmt))
			{
				self::$nickStmt = Core::$db->prepare('SELECT nick, since FROM '.Core::$prefix.'nicknames WHERE user_id=:userId ORDER BY since DESC;');
			}
			$statement = self::$nickStmt;
			$statement->bindParam(':userId', $this->mUserId, PDO::PARAM_INT);
			$statement->execute();
			$this->nicks = $statement->fetchAll(PDO::FETCH_ASSOC);
		}
	}

	public function newNick($aNewNick)
	{
		$stmt = Core::$db->prepare('
			SELECT :nick
			IN (
				SELECT n.nick
				FROM `'.Core::$prefix.'user` AS u
				JOIN '.Core::$prefix.'nicknames AS n
				USING ( user_id )
				WHERE n.since = (
				SELECT max( since )
				FROM '.Core::$prefix.'nicknames
				WHERE user_id = u.user_id )
			) AS inuse;
		');
		$stmt->bindParam(':nick', $aNewNick, PDO::PARAM_STR);
		$stmt->execute();
		if($stmt->fetch(PDO::FETCH_COLUMN))
			return false;

		$stmt = Core::$db->prepare('INSERT INTO '.Core::$prefix.'nicknames SET nick=:nick, user_id=:userId, since=UNIX_TIMESTAMP();');
		$stmt->bindParam(':userId', $this->mUserId, PDO::PARAM_INT);
		$stmt->bindParam(':nick', $aNewNick, PDO::PARAM_STR);
		$stmt->execute();
		return true;
	}

	/**
	 * getter
	 *
	 * @param int $aVarName what kind of info we want to get from the user
	 * @return mixed
	 **/
	public function __get($aVarName)
	{
		$this->fetchData();
		if($aVarName == 'openids')
		{
			$this->fetchOpenIDs();
			return $this->mOpenIDs;
		}
		if($aVarName == 'nicks')
		{
			$this->fetchNicks();
			return $this->nicks;
		}
		if($aVarName == 'id')
		{
			return $this->mUserId;
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
		if(!empty($_GET['completelogin'])) // catch the response from the OpenID server
		{
			return $this->completeLogin();
		}
		if(!empty($_COOKIE['openid_url']) && empty($_SESSION['uid']))
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
		$this->fetchData();
		if($aVarName == 'authed')
		{
			return $this->mUserId != 0;
		}
		if($aVarName == 'openids')
		{
			$this->fetchOpenIDs();
			return $this->mOpenIDs;
		}
		if($aVarName == 'id')
		{
			return $this->mUserId;
		}
		return isset($this->mUserData[$aVarName]) ? $this->mUserData[$aVarName] : null;
	}

	/**
	 * fetch the users data into $mUserData
	 *
	 * @return void
	 **/
	protected function fetchData()
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
				parent::fetchData();
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
		$this->fetchData();
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
		require_once "Auth/OpenID/AX.php";

		$store = new Auth_OpenID_MySQLStore(Core::$db);
		$store->createTables();
		$consumer = new Auth_OpenID_Consumer($store);
		$authRequest = $consumer->begin($aOpenID);
		Core::$db->setAttribute(PDO::ATTR_AUTOCOMMIT, true);

		if(!$aImmediate)
		{
			if(!$authRequest)
			{
				throw new Exception(l10n::_('Authentication error: OpenID invalid'));
			}
			$axRequest = new Auth_OpenID_AX_FetchRequest();
			$axRequest->add(Auth_OpenID_AX_AttrInfo::make('http://schema.openid.net/namePerson/friendly', 1, true, 'nickoid'));
			$axRequest->add(Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/friendly', 1, true, 'nickax'));
			$authRequest->addExtension($axRequest);
			$sregRequest = Auth_OpenID_SRegRequest::build(array('nickname'), array());
			$authRequest->addExtension($sregRequest);
			//var_dump($authRequest);
			//exit;
		}
		elseif(!$authRequest)
			return;

		$webroot = Core::$webRoot;

		if(empty($_SESSION['returnto']))
			// the request url will be relative to script name anyway
			$_SESSION['returnto'] = $webroot.
			                        substr($_SERVER['REQUEST_URI'],
			                        strlen(dirname($_SERVER['SCRIPT_NAME'])));

		header("Location: ".$authRequest->redirectURL(
			$webroot,
			$webroot.'login?completelogin=1',
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
		require_once "Auth/OpenID/AX.php";

		$store = new Auth_OpenID_MySQLStore(Core::$db);
		$store->createTables();
		$consumer = new Auth_OpenID_Consumer($store);
		$returnUrl = Core::$webRoot.'login?completelogin=1';
		$response = $consumer->complete($returnUrl);
		Core::$db->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
		if(!$response)
			throw new Exception(l10n::_('Consumer->complete failed'));

		if($response->status == Auth_OpenID_SUCCESS)
		{
			$this->checkLogin();
			// get the user ID from the DB or create a new user
			// write the user id to the session for login
			$stmt = Core::$db->prepare('SELECT user_id FROM '.Core::$prefix.'user
				LEFT JOIN '.Core::$prefix.'openids as openids USING (user_id)
				WHERE openids.openid=:openID;');
			$stmt->bindValue(':openID', $response->identity_url, PDO::PARAM_STR);
			$stmt->execute();
			$userData = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!empty($userData))
			{
				// user already exists
				// TODO: sync our user profile with the data provided by the OpenID server?
				//$this->mUserData = $userData;
				$this->mUserId = $userData['user_id'];
				$_SESSION['uid'] = $this->mUserId;

				if(empty($_COOKIE['openid_url']))
					setcookie('openid_url', $response->identity_url, time()+3600*24*365);
			}
			elseif(!empty($this->mUserId))
			{
				// User was already logged in -> set this as a secondary OpenID
				$statement = Core::$db->prepare('
					INSERT INTO '.Core::$prefix.'openids SET user_id=:userId, openid=:openID;');
				$statement->bindParam(':userId', $this->mUserId, PDO::PARAM_INT);
				$statement->bindParam(':openID', $response->identity_url, PDO::PARAM_STR);
				$statement->execute();
			}
			else
			{
				// on-the-fly account creation
				$nickname = $response->identity_url;
				$axResponse = Auth_OpenID_AX_FetchResponse::fromSuccessResponse($response);
				$sregResponse = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
				if($axResponse)
				{
					$nickname = $axResponse->getSingle('http://schema.openid.net/namePerson/friendly', $nickname);
					$nickname = $axResponse->getSingle('http://axschema.org/namePerson/friendly', $nickname);
				}
				else
				{
					$sreg = $sregResponse->contents();
					$nickname = !empty($sreg['nickname']) ? $sreg['nickname'] : $nickname;
				}

				Core::$db->beginTransaction();
				$stmt = Core::$db->prepare('INSERT INTO '.Core::$prefix.'user VALUES (DEFAULT, DEFAULT);');
				$stmt->execute();
				$this->mUserId = Core::$db->lastInsertId();
				
				// if the nick is already taken, just use the identity url, it should be
				// unique in theory
				if(!$this->newNick($nickname))
					$this->newNick($response->identity_url);
				
				$statement = Core::$db->prepare('
					INSERT INTO '.Core::$prefix.'openids SET user_id=:userId, openid=:openID;');
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
		unset($_COOKIE['openid_url']);
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
	 * cached prepared statement
	 */
	private static $selectStmt = null;

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
			if(empty(self::$selectStmt))
			{
				self::$selectStmt = Core::$db->prepare('SELECT * FROM '.Core::$prefix.'usergroup WHERE ugroup_id=:groupId;');
			}
			$stmt = self::$selectStmt;
			$stmt->bindValue(':groupId', (int)$aGroupId, PDO::PARAM_INT);
			$stmt->execute();
			self::$mGroupCache[$aGroupId] = $stmt->fetch(PDO::FETCH_ASSOC);
		}
		return self::$mGroupCache[$aGroupId];
	}
}

class UserDispatcher implements RESTful
{
	public static function doGET(RESTDispatcher $dispatcher)
	{
		$current = $dispatcher->current();
		if($parent = $dispatcher->previous())
		{
			// parent resource may have a user assigned
			if($parent['obj']->user != null)
				return $parent['obj']->user;
			else
				throw new NotImplementedException();
		}
		if(empty($current['id']))
			throw new NotImplementedException(); // listing not implemented
		$obj = Users::getUser($current['id']);
		$dispatcher->assignObject($obj);

		$child = $dispatcher->next();
		if(!$child)
			return $obj;
		else
			throw new NotImplementedException(); // no subresources allowed
			//return $dispatcher->dispatch();
	}
	public static function doPOST(RESTDispatcher $dispatcher)
	{
		$current = $dispatcher->current();
		$user = Users::getUser($current['id']);
		$child = $dispatcher->next();
		if(!Core::$user->authed || !($current['id'] == Core::$user->id || Core::$user->hasRight('users')))
			throw new UnauthorizedException();
		if(!$child || $child['resource'] != 'nick')
			throw new NotImplementedException();
		return json_encode($user->newNick($dispatcher->getJSON()));
	}
	public static function doPUT(RESTDispatcher $dispatcher)
	{
		throw new NotImplementedException();
	}
	public static function doDELETE(RESTDispatcher $dispatcher)
	{
		throw new NotImplementedException();
	}
}
?>
