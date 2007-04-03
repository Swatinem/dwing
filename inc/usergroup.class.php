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
 * UserGroup module
 *
 * This class provides functions to handle usergroups and the usergroup cache
 */
class UserGroup extends Module
{
	/**
	 * cache for the usergroups
	 *
	 * @var array $mGroupCache
	 **/
	private static $mGroupCache = array();
	/**
	 * all the rights in the usergroup table
	 *
	 * @var array $_rights
	 **/
	//private static $_rights = array('admin', 'guestbook', 'news', 'templates', 'articles', 'users', 'downloads',
	//	'screenshots', 'affiliates', 'quotes');

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
			self::$mGroupCache[$aGroupId] = self::$_db->queryFirst(
				'SELECT * FROM '.self::$_db->pref.'usergroup WHERE ugroup_id='.(int)$aGroupId.';');
		}
		return self::$mGroupCache[$aGroupId];
	}

	/**
	 * gets all the usergroups
	 *
	 * @return array
	 **/
	/* TODO: rewrite!
	public static function getGroups()
	{
		$groups = array();
		$groups_res = $GLOBALS['_db']->query('SELECT ugroup_id, name FROM '.$GLOBALS['_db']->pref.'usergroup;');
		while($group = $groups_res->fetch_assoc())
		{
			$groups[$group['ugroup_id']] = $group['name'];
		}
		$groups_res->close();
		return $groups;
	}*/

	/**
	 * add a usergroup
	 *
	 * @return mixed false on failure, id on success
	 **/
	/* TODO: rewrite!
	public function addgroup()
	{
		try
		{
			if(empty($_POST['name'])) throw new Exception('No name defined.');
			$query = '
			INSERT INTO '.$GLOBALS['_db']->pref.'usergroup SET ';
			foreach($this->_rights as $right)
			{
				$query.= $right.'='.(!empty($_POST[$right]) ? 1 : 0).', ';
			}
			$query.= ' name="'.self::$_db->escape($_POST['name']).'";';
			$GLOBALS['_db']->query($query);
			$GLOBALS['_snntpl']->clear_cache_byobject('usergroup');
			return $GLOBALS['_db']->insert_id;
		}
		catch(Exception $e)
		{
			$GLOBALS['_snntpl']->assignLang('error', $e->getMessage());
			return false;
		}
	}*/

	/**
	 * edit a usergroup
	 *
	 * @param int $gid
	 * @return bool
	 **/
	/* TODO: rewrite!
	public function editgroup($gid)
	{
		try
		{
			if(empty($_POST['name'])) throw new Exception('No name defined.');
			$query = '
			UPDATE '.$GLOBALS['_db']->pref.'usergroup SET ';
			foreach($this->_rights as $right)
			{
				$query.= $right.'='.(!empty($_POST[$right]) ? 1 : 0).', ';
			}
			$query.= ' name="'.self::$_db->escape($_POST['name']).'" WHERE ugroup_id='.(int)$gid.';';
			$GLOBALS['_db']->query($query);
			$GLOBALS['_snntpl']->clear_cache_byobject('usergroup');
			return true;
		}
		catch(Exception $e)
		{
			$GLOBALS['_snntpl']->assignLang('error', $e->getMessage());
			return false;
		}
	}*/

	/**
	 * delete a usergroup
	 *
	 * @param int $gid
	 * @return bool
	 **/
	/* TODO: rewrite!
	public function deletegroup($gid)
	{
		try
		{
			if((int)$gid < 3) throw new Exception('This is a internal usergroup and cannot be deleted.');
			$users = $GLOBALS['_users']->gettotalbygroup($gid);
			if($users > 0) throw new Exception('There are still users in this usergroup.');
			$GLOBALS['_db']->query('DELETE FROM '.$GLOBALS['_db']->pref.'usergroup WHERE ugroup_id='.(int)$gid.';');
			$GLOBALS['_snntpl']->clear_cache_byobject('usergroup');
			return true;
		}
		catch(Exception $e)
		{
			$GLOBALS['_snntpl']->assignLang('error', $e->getMessage());
			return false;
		}
	}*/
}
?>