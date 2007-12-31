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
 * Guestbook module
 *
 * This class provides functions to handle guestbook entries
 */
class Guestbook extends Module
{
	/**
	 * adds an entry
	 *
	 * @return mixed Exception on failure, id on success
	 **/
	public static function addEntry()
	{
		try
		{
			if(empty($_POST['nick'])) throw new Exception(l10n::_('No nickname defined.'));
			if(!preg_match('!^[_a-zA-Z0-9-.]+@[a-zA-Z0-9-.]+.[a-zA-Z]{2,4}$!', $_POST['email']))
			{
				throw new Exception(l10n::_('You must enter a valid E-Mail address.'));
			}
			// check entered url
			if(empty($_POST['url']))
			{
				$url = '';
			}
			else
			{
				// possible XSS?!?
				preg_match('!(http://)?([^\s]*)!', $_POST['url'], $urlmatch);
				if(empty($urlmatch[2]))
				{
					$url = '';
				}
				else
				{
					$url = 'http://'.$urlmatch[2];
				}
			}
			// check for multiposts
			$check = self::$_db->queryfirst('
				SELECT MAX(time) AS time
				FROM '.self::$_db->pref.'guestbook
				WHERE ip="'.$_SERVER['REMOTE_ADDR'].'";');
			if(!empty($check) && $check['time'] > time()-120)
			{
				throw new Exception(l10n::_('You may only make a post every 2 minutes.'));
			}
			self::$_db->query('
			INSERT INTO
				'.self::$_db->pref.'guestbook
			SET
				title = "'.(!empty($_POST['title']) ? self::$_db->escape($_POST['title']) : '').'",
				text = "'.self::$_db->escape(Utils::purify($_POST['text'])).'",
				time = '.time().',
				ip = "'.$_SERVER['REMOTE_ADDR'].'",
				nick = "'.self::$_db->escape($_POST['nick']).'",
				email = "'.self::$_db->escape($_POST['email']).'",
				url = "'.self::$_db->escape($url).'",
				icq = '.(!empty($_POST['icq']) ? (int)$_POST['icq'] : 0).';
			');
			return self::$_db->insert_id;
		}
		catch(Exception $e)
		{
			return $e;
		}
	}

	/**
	 * edit the details of a specified entry
	 *
	 * @param int $aGbId Id of the entry we want to edit
	 * @return mixed Exception on failure, true on success
	 **/
	public function editEntry($aGbId)
	{
		try
		{
			if(empty($_POST['nick'])) throw new Exception(l10n::_('No nickname defined.'));
			if(!preg_match('!^[_a-zA-Z0-9-.]+@[a-zA-Z0-9-.]+.[a-zA-Z]{2,4}$!', $_POST['email']))
			{
				throw new Exception(l10n::_('You must enter a valid E-Mail address.'));
			}
			// check entered url
			if(empty($_POST['url']))
			{
				$url = '';
			}
			else
			{
				preg_match('!(http://)?([^\s]*)!', $_POST['url'], $urlmatch);
				if(empty($urlmatch[2]))
				{
					$url = '';
				}
				else
				{
					$url = 'http://'.$urlmatch[2];
				}
			}
			self::$_db->query('
			UPDATE
				'.$_db->pref.'guestbook
			SET
				title="'.(!empty($_POST['title']) ? self::$_db->escape($_POST['title']) : '').'",
				nick="'.self::$_db->escape($_POST['nick']).'",
				email="'.self::$_db->escape($_POST['email']).'",
				url="'.self::$_db->escape($url).'",
				icq='.(!empty($_POST['icq']) ? (int)$_POST['icq'] :.0 ).',
				text="'.self::$_db->escape(Utils::purify($_POST['text'])).'"
			WHERE
				gb_id='.(int)$aGbId.';
			');
			return true;
		}
		catch(Exception $e)
		{
			return $e;
		}
	}

	/**
	 * return the total number of entries
	 *
	 * @return int
	 **/
	public static function getTotal()
	{
		$total = self::$_db->queryfirst('SELECT COUNT(*) AS total FROM '.self::$_db->pref.'guestbook;');
		return $total['total'];
	}

	/**
	 * return the details of X entries
	 *
	 * @param int $aPerPage how many entries to return
	 * @return array
	 **/
	public static function getEntries($aPerPage = null)
	{
		return self::$_db->queryAll('SELECT * FROM '.self::$_db->pref.'guestbook ORDER BY time DESC '.Utils::sqlLimit($aPerPage).';');
	}

	/**
	 * deletes the specified entry
	 *
	 * @param int $aGbId Id of the entry we want to delete
	 * @return bool
	 **/
	public static function deleteEntry($aGbId)
	{
		self::$_db->query('DELETE FROM '.self::$_db->pref.'guestbook WHERE gb_id='.(int)$aGbId.';');
		return true;
	}

	/**
	 * returns the details of the specified entry
	 *
	 * @param int $aGbId Id of the entry we want to get
	 * @return array
	 **/
	public static function getEntry($aGbId)
	{
		return self::$_db->queryfirst('SELECT * FROM '.self::$_db->pref.'guestbook WHERE gb_id='.(int)$aGbId.';');
	}
}
?>