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
 * Article module
 *
 * This class provides functions to handle articles
 */
class Affiliate extends Module
{
	/**
	 * get the total number of affiliates
	 *
	 * @return int
	 **/
	public static function getTotal()
	{
		$total = self::$_db->queryfirst('SELECT COUNT(*) AS total FROM '.self::$_db->pref.'affiliates;');
		return $total['total'];
	}

	/**
	 * return the details of the specified affiliate
	 *
	 * @param int $aSiteId Id of the affiliate we want to get
	 * @return array
	 **/
	public static function getAffiliate($aSiteId)
	{
		return self::$_db->queryfirst('SELECT * FROM '.self::$_db->pref.'affiliates WHERE site_id='.(int)$aSiteId.';');
	}

	/**
	 * return the details of all affiliate ordered by incoming clicks
	 *
	 * @return array
	 **/
	public static function getAffiliates()
	{
		return self::$_db->queryAll('SELECT * FROM '.self::$_db->pref.'affiliates ORDER BY clicks_in DESC;');
	}

	/**
	 * return affiliates in alphabetic order which are set to display text links
	 *
	 * @return array
	 **/
	public static function getTextLinks()
	{
		return self::$_db->queryAll('SELECT * FROM '.self::$_db->pref.'affiliates WHERE textlink=1 ORDER BY name ASC;');
	}

	/**
	 * get one random affiliate which is set to display a button
	 *
	 * @return array
	 **/
	public static function getRandomButton()
	{
		return self::$_db->queryfirst('SELECT *, MD5(RAND()) AS rand FROM '.self::$_db->pref.'affiliates WHERE button=1 ORDER BY rand LIMIT 0,1;');
	}

	/**
	 * increments the views going in
	 *
	 * @param int $aSiteId Id of the affiliate
	 * @return void
	 **/
	public static function incrementViewsIn($aSiteId)
	{
		self::$_db->query('UPDATE '.self::$_db->pref.'affiliates SET views_in=views_in+1 WHERE site_id='.(int)$aSiteId.';');
	}

	/**
	 * increments the views going out
	 *
	 * @param int $aSiteId Id of the affiliate
	 * @return void
	 **/
	public static function incrementViewsOut($aSiteId)
	{
		self::$_db->query('UPDATE '.self::$_db->pref.'affiliates SET views_out=views_out+1 WHERE site_id='.(int)$aSiteId.';');
	}

	/**
	 * increments the clicks going in
	 *
	 * @param int $aSiteId Id of the affiliate
	 * @return void
	 **/
	public static function incrementClicksIn($aSiteId)
	{
		self::$_db->query('UPDATE '.self::$_db->pref.'affiliates SET clicks_in=clicks_in+1 WHERE site_id='.(int)$aSiteId.';');
	}

	/**
	 * increments the clicks going out
	 *
	 * @param int $aSiteId Id of the affiliate
	 * @return void
	 **/
	public static function incrementClicksOut($aSiteId)
	{
		self::$_db->query('UPDATE '.self::$_db->pref.'affiliates SET clicks_out=clicks_out+1 WHERE site_id='.(int)$aSiteId.';');
	}

	/**
	 * redirect to the affiliates url or to our index
	 *
	 * @param int $aSiteId Id of the affiliate
	 * @return void
	 **/
	public static function redirectTo($aSiteId = null)
	{
		if(empty($aSiteId))
		{
			header('Location: index.php');
			exit;
		}
		// exit -> no need for else
		$site = self::getAffiliate($aSiteId);
		header('Location: '.$site['site_url']);
		exit;
	}

	/**
	 * add an affiliate
	 *
	 * @return mixed Exception on failure, id on success
	 **/
	public static function addAffiliate()
	{
		try
		{
			if(empty($_POST['name'])) throw new Exception(l10n::_('No name defined.'));
			if(empty($_POST['site_url'])) throw new Exception(l10n::_('No url defined.'));
			self::$_db->query('
			INSERT INTO '.self::$_db->pref.'affiliates SET
				name="'.$_POST['name'].'",
				site_url="'.$_POST['site_url'].'",
				button_url="'.(!empty($_POST['button_url']) ? $_POST['button_url'] : '').'",
				text="'.$_POST['text'].'",
				textlink='.(!empty($_POST['textlink']) ? 1 : 0).',
				button='.(!empty($_POST['button']) ? 1 : 0).';');
			return self::$_db->insert_id;
		}
		catch(Exception $e)
		{
			return $e;
		}
	}

	/**
	 * edit the details of the specified affiliate
	 *
	 * @param int $aSiteId Id of the affiliate we want to edit
	 * @return mixed Exception on failure, true on success
	 **/
	public static function editAffiliate($aSiteId)
	{
		try
		{
			if(empty($_POST['name'])) throw new Exception(l10n::_('No name defined.'));
			if(empty($_POST['site_url'])) throw new Exception(l10n::_('No url defined.'));
			self::$_db->query('
			UPDATE '.self::$_db->pref.'affiliates SET
				name="'.$_POST['name'].'",
				site_url="'.$_POST['site_url'].'",
				button_url="'.$_POST['button_url'].'",
				text="'.$_POST['text'].'",'.
				/*views_in='.(int)$_POST['views_in'].',
				views_out='.(int)$_POST['views_out'].',
				clicks_in='.(int)$_POST['clicks_in'].',
				clicks_out='.(int)$_POST['clicks_out'].',*/
				'textlink='.(!empty($_POST['textlink']) ? 1 : 0).',
				button='.(!empty($_POST['button']) ? 1 : 0).'
			WHERE site_id='.(int)$aSiteId.';');
			return true;
		}
		catch(Exception $e)
		{
			return $e;
		}
	}

	/**
	 * delete the specified affiliate
	 *
	 * @param int $aSiteId Id of the affiliate we want to delete
	 * @return bool
	 **/
	public static function deleteAffiliate($aSiteId)
	{
		self::$_db->query('DELETE FROM '.self::$_db->pref.'affiliates WHERE site_id='.(int)$aSiteId.';');
		return true;
	}
}
?>