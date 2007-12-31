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
 * Quotes module
 *
 * This class provides functions to handle quotes
 */
class Quote extends Module
{
	/**
	 * gets all the quotes
	 *
	 * @return array
	 **/
	public static function getQuotes()
	{
		return self::$_db->queryAll('SELECT * FROM '.self::$_db->pref.'quotes;');
	}

	/**
	 * get one quote by id
	 *
	 * @param int $aQuoteId
	 * @return array
	 **/
	public static function getQuote($aQuoteId)
	{
		return self::$_db->queryfirst('SELECT * FROM '.self::$_db->pref.'quotes WHERE quote_id='.(int)$aQuoteId.';');
	}

	/**
	 * get a random quote
	 *
	 * @return array
	 **/
	public static function getRandom()
	{
		return self::$_db->queryfirst('SELECT *, MD5(RAND()) AS rand FROM '.self::$_db->pref.'quotes ORDER BY rand LIMIT 0,1;');
	}

	/**
	 * add a quote
	 *
	 * @return mixed Exception on failure, id on success
	 **/
	public static function addQuote()
	{
		try
		{
			if(empty($_POST['quote'])) throw new Exception(l10n::_('No quote defined.'));
			if(empty($_POST['source'])) throw new Exception(l10n::_('No source defined.'));
			self::$_db->query('
			INSERT INTO '.self::$_db->pref.'quotes SET
				quote="'.self::$_db->escape($_POST['quote']).'",
				source="'.self::$_db->escape($_POST['source']).'";');
			return self::$_db->insert_id;
		}
		catch(Exception $e)
		{
			return $e;
		}
	}

	/**
	 * edit a quote
	 *
	 * @param int $quote_id
	 * @return mixed Exception on failure, true on success
	 **/
	public static function editQuote($aQuoteId)
	{
		try
		{
			if(empty($_POST['quote'])) throw new Exception(l10n::_('No quote defined.'));
			if(empty($_POST['source'])) throw new Exception(l10n::_('No source defined.'));
			self::$_db->query('
			UPDATE '.self::$_db->pref.'quotes SET
				quote="'.self::$_db->escape($_POST['quote']).'",
				source="'.self::$_db->escape($_POST['source']).'"
			WHERE
				quote_id='.(int)$aQuoteId.';');
			return true;
		}
		catch(Exception $e)
		{
			return $e;
		}
	}

	/**
	 * deletes a quote
	 *
	 * @param int $quote_id
	 * @return bool
	 **/
	public static function deleteQuote($aQuoteId)
	{
		self::$_db->query('DELETE FROM '.self::$_db->pref.'quotes WHERE quote_id='.(int)$aQuoteId.';');
		return true;
	}

	/**
	 * get the total number of quotes
	 *
	 * @return int
	 **/
	public static function getTotal()
	{
		$total = self::$_db->queryfirst('SELECT COUNT(*) AS total FROM '.self::$_db->pref.'quotes;');
		return $total['total'];
	}
}
?>