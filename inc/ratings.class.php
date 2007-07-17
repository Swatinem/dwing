<?php
/*
 * dWing - a nextgen CMS built on PHP5, MySQLi and Ajax
 * Copyright (C) 2007 Arpad Borsos <arpad.borsos@googlemail.com>
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
 * Ratings module
 *
 * rate any type of content.
 */
class Ratings extends Module
{
	/**
	 * get the number of ratings and the average rating of a Content Item and
	 * whether the user has voted or not
	 *
	 * @param int $aContentId
	 * @param int $aContentType
	 * @return array
	 **/
	public static function getRating($aContentId, $aContentType)
	{
		$statement = self::$_db->prepare('
			SELECT COUNT(rating) as ratings, AVG(rating) as average,
				SUM(user_id=:userId) as voted
			FROM `'.self::$_db->pref.'ratings`
			WHERE content_id=:contentId AND	content_type=:contentType;');
		$statement->bindValue(':userId', (int)self::$_user->user_id, PDO::PARAM_INT);
		$statement->bindValue(':contentId', (int)$aContentId, PDO::PARAM_INT);
		$statement->bindValue(':contentType', (int)$aContentType, PDO::PARAM_INT);
		$statement->execute();
		return $statement->fetch(PDO::FETCH_ASSOC);
	}

	/**
	 * rate a Content Item
	 *
	 * @param int $aContentId
	 * @param int $aContentType
	 * @param int $aRating
	 * @return bool
	 **/
	public static function addRating($aContentId, $aContentType, $aRating)
	{
		if($aRating > 5 || $aRating < 1 || !self::$_user->authed)
			return false;
		$statement = self::$_db->prepare('
			REPLACE INTO `'.self::$_db->pref.'ratings`
			SET user_id=:userId, content_id=:contentId, content_type=:contentType,
				rating=:rating;');
		$statement->bindValue(':userId', (int)self::$_user->user_id, PDO::PARAM_INT);
		$statement->bindValue(':contentId', (int)$aContentId, PDO::PARAM_INT);
		$statement->bindValue(':contentType', (int)$aContentType, PDO::PARAM_INT);
		$statement->bindValue(':rating', (int)$aRating, PDO::PARAM_INT);
		$statement->execute();
		return true;
	}
}
?>