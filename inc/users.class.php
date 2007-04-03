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
 * Users module
 *
 * This class provides functions to handle users and the user cache
 */
class Users extends Module
{
	/**
	 * cache for the users
	 *
	 * @var array $mUserCache
	 **/
	private static $mUserCache = array();

	/**
	 * return a user object for the specified user
	 *
	 * @param int $aUserId user id of the user we want to get
	 * @return user
	 **/
	public static function getUser($aUserId)
	{
		if(empty(self::$mUserCache[$aUserId]))
		{
			self::$mUserCache[$aUserId] = new GenericUser($aUserId);
		}
		return self::$mUserCache[$aUserId];
	}

	/**
	 * returns all registered users with matching first letter
	 *
	 * @param int $perpage
	 * @return array
	 **/
	/* TODO: rewrite!
	public function getusers($perpage)
	{
		return $this->_getusers($perpage, 'WHERE '.Utils::letterSelectSql('nick'));
	}*/

	/**
	 * returns all registered users in one group
	 *
	 * @param int $perpage
	 * @param int $gid
	 * @return array
	 **/
	/* TODO: rewrite!
	public function getusersbygroup($perpage, $gid)
	{
		return $this->_getusers($perpage, 'WHERE ugroup_id='.(int)$gid.' AND '.Utils::letterSelectSql('nick'));
	}*/

	/**
	 * returns the latest registered user
	 *
	 * @return user
	 **/
	/* TODO: rewrite!
	public function getnewest()
	{
		$user = $this->_db->queryfirst('SELECT * FROM '.$this->_db->pref.'user ORDER BY registered DESC LIMIT 0,1;');
		$userobj = $this->getuser($user['user_id']);
		$userobj->assigndata($user);
		return $userobj;
	}*/

	/**
	 * returns all users matching the WHERE
	 *
	 * @param int $perpage
	 * @param string $where a whrere clause for the mysql query
	 * @return int
	 **/
	/* TODO: rewrite!
	private function _getusers($perpage, $where = '', $orderby = 'nick ASC')
	{
		$users = array();
		$users_res = $this->_db->query('SELECT * FROM '.$this->_db->pref.'user '.$where.' ORDER BY '.$orderby.' '.Utils::sqlLimit($perpage).';');
		while($user = $users_res->fetch_assoc())
		{
			$userobj = $this->getuser($user['user_id']);
			$userobj->assigndata($user);
			$users[] = $userobj;
		}
		return $users;
	}*/

	/**
	 * returns the total number of registered users with matching first letter
	 *
	 * @return int
	 **/
	/* TODO: rewrite!
	public function gettotal()
	{
		return $this->_gettotal('WHERE '.Utils::letterSelectSql('nick'));
	}*/

	/**
	 * returns the total number of registered users in one group
	 *
	 * @param int $gid
	 * @return int
	 **/
	/* TODO: rewrite!
	public function gettotalbygroup($gid)
	{
		return $this->_gettotal('WHERE ugroup_id='.(int)$gid.' AND '.Utils::letterSelectSql('nick'));
	}*/

	/**
	 * returns the total number of registered users
	 *
	 * @return int
	 **/
	/* TODO: rewrite!
	public function getalltotal()
	{
		return $this->_gettotal();
	}*/

	/**
	 * returns the total number of users matching the WHERE
	 *
	 * @param string $where a whrere clause for the mysql query
	 * @return int
	 **/
	/* TODO: rewrite!
	private function _gettotal($where = '')
	{
		$total = $this->_db->queryfirst('SELECT COUNT(*) AS total FROM '.$this->_db->pref.'user '.$where.';');
		return $total['total'];
	}*/

	/**
	 * sets the users group to guest
	 *
	 * @param int $uid
	 * @return bool
	 **/
	/* TODO: rewrite!
	public function usertoguest($uid)
	{
		return $this->changeusersgroup($uid, 1);
	}*/

	/**
	 * change a users group
	 *
	 * @param int $uid
	 * @param int $gid
	 * @return bool
	 **/
	/* TODO: rewrite!
	public function changeusersgroup($uid,$gid)
	{
		$this->_db->query('UPDATE '.$this->_db->pref.'user SET ugroup_id='.(int)$gid.' WHERE user_id='.(int)$uid.';');
		return true;
	}*/

	/**
	 * edit a user
	 * this method is mainly used in the admin to edit all user details at once. the user class contains seperate
	 * functions to change various details
	 *
	 * @param int $uid
	 * @return bool
	 **/
	/* TODO: rewrite!
	public function edituser($uid)
	{
		try
		{
			// get the users old details
			$user = $this->getuser($uid);
			// check if the fields are filled out correctly
			if(empty($_POST['nick'])) throw new Exception('No nickname defined.');
			if(!preg_match('!^[_a-zA-Z0-9-.]+@[a-zA-Z0-9-.]+.[a-zA-Z]{2,4}$!', $_POST['email']))
			{
				throw new Exception('You must enter a valid E-Mail address.');
			}
			// only check for allready used nick/email if we want to change those
			if($_POST['email'] != $user->email)
			{
				$checkmail = $this->_db->queryfirst('SELECT user_id FROM '.$this->_db->pref.'user WHERE email="'.self::$_db->escape($_POST['email']).'";');
				if(!empty($checkmail['user_id'])) throw new Exception('This e-mail adress is allready taken.');
			}
			if($_POST['nick'] != $user->nick)
			{
				$checknick = $this->_db->queryfirst('SELECT user_id FROM '.$this->_db->pref.'user WHERE nick="'.self::$_db->escape($_POST['nick']).'";');
				if(!empty($checknick['user_id'])) throw new Exception('This nickname is allready taken.');
			}
			// only delete the template cache for usergroup wenn we change the users group
			if($_POST['gid'] != $user->gid)
			{
				$this->_snntpl->clear_cache_byobject('usergroup');
			}
			$this->_db->query('
			UPDATE '.$this->_db->pref.'user
			SET
				nick="'.self::$_db->escape($_POST['nick']).'",
				email="'.self::$_db->escape($_POST['email']).'",
				'.(!empty($_POST['password']) ? 'password="'.md5($_POST['password']).'",' : '').'
				ugroup_id='.(int)$_POST['gid'].'
			WHERE user_id='.(int)$uid.';');
			$this->_snntpl->clear_cache_byobject('users');
			return true;
		}
		catch(Exception $e)
		{
			$this->_snntpl->assignLang('error', $e->getMessage());
			return false;
		}
	}*/
}
?>