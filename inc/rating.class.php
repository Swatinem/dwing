<?php
/*
 * dWing - a cms aimed to be as bleeding edge as possible
 * Copyright (C) 2007-2008 Arpad Borsos <arpad.borsos@googlemail.com>
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
 * Ratings module
 *
 * rate any type of content.
 */
class Rating
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
		$statement = Core::$db->prepare('
			SELECT COUNT(rating) as ratings, AVG(rating) as average,
				SUM(user_id=:userId) as voted
			FROM `'.Core::$db->pref.'ratings`
			WHERE content_id=:contentId AND	content_type=:contentType;');
		$statement->bindValue(':userId', (int)Core::$user->user_id, PDO::PARAM_INT);
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
		if($aRating > 5 || $aRating < 1 || !Core::$user->authed)
			return false;
		$statement = Core::$db->prepare('
			REPLACE INTO `'.Core::$db->pref.'ratings`
			SET user_id=:userId, content_id=:contentId, content_type=:contentType,
				rating=:rating;');
		$statement->bindValue(':userId', (int)Core::$user->user_id, PDO::PARAM_INT);
		$statement->bindValue(':contentId', (int)$aContentId, PDO::PARAM_INT);
		$statement->bindValue(':contentType', (int)$aContentType, PDO::PARAM_INT);
		$statement->bindValue(':rating', (int)$aRating, PDO::PARAM_INT);
		$statement->execute();
		return true;
	}
}

class RatingDispatcher implements RESTful
{
	public static function GET(RESTDispatcher $dispatcher)
	{
		$child = $dispatcher->next();
		if($child)
			throw new NotImplementedException();
		$parent = $dispatcher->previous();
		// TODO: start using $obj::ContentType when we switch to PHP5.3
		if(!$parent || !isset($parent['obj']->id) ||
			!isset($parent['obj']->ContentType))
			throw new NotImplementedException();
		// we have a parent
		$dispatcher->next(); // so the dispatcher does not think we are the parent
		return json_encode(Rating::getRating($parent['obj']->id,
			$parent['obj']->ContentType));
	}
	public static function POST(RESTDispatcher $dispatcher)
	{
		$child = $dispatcher->next();
		if($child)
			throw new NotImplementedException();
		$parent = $dispatcher->previous();
		// TODO: start using $obj::ContentType when we switch to PHP5.3
		if(!$parent || !isset($parent['obj']->id) ||
			!isset($parent['obj']->ContentType))
			throw new NotImplementedException();
		// we have a parent
		$dispatcher->next(); // so the dispatcher does not think we are the parent
		if(!Rating::addRating($parent['obj']->id, $parent['obj']->ContentType,
			(int)file_get_contents('php://input')))
			throw new UnauthorizedException();
		return json_encode(Rating::getRating($parent['obj']->id,
			$parent['obj']->ContentType));
	}
	public static function PUT(RESTDispatcher $dispatcher)
	{
		throw new NotImplementedException();
	}
	public static function DELETE(RESTDispatcher $dispatcher)
	{
		throw new NotImplementedException();
	}
}
?>
