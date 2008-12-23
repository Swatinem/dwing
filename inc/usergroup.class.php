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
 * UserGroup module
 *
 * This class provides functions to handle usergroups and the usergroup cache
 */
class Usergroup extends Module
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
			self::$mGroupCache[$aGroupId] = self::$_db->queryFirst(
				'SELECT * FROM '.self::$_db->pref.'usergroup WHERE ugroup_id='.(int)$aGroupId.';');
		}
		return self::$mGroupCache[$aGroupId];
	}
}
?>
