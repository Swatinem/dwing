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
class Rating implements ActiveRecord, ContentProviderSingle, JSONable
{
	/**
	 * The ContentItem this Rating corresponds to
	 */
	protected $contentId;
	protected $contentType;
	protected $myRating = null;

	protected $data = null;

	private static $selectStmt = null;
	private static $insertStmt = null;
	private static $deleteStmt = null;


	/**
	 * ActiveRecord Interface
	 */
	public function save($aUseTransaction = false)
	{
		if($this->myRating > 5 || $this->myRating < 1 || !Core::$user->authed)
			return false;
		if(is_null(self::$insertStmt))
		{
			self::$insertStmt = Core::$db->prepare('
				INSERT INTO `'.Core::$prefix.'ratings`
				SET user_id=:userId, content_id=:contentId, content_type=:contentType,
					rating=:rating
				ON DUPLICATE KEY UPDATE rating=VALUES(rating);');
		}
		// there is no sense in using a transaction for a 1-statement function
		$statement = self::$insertStmt;
		$statement->bindValue(':userId', (int)Core::$user->user_id, PDO::PARAM_INT);
		$statement->bindValue(':contentId', (int)$this->contentId, PDO::PARAM_INT);
		$statement->bindValue(':contentType', (int)$this->contentType, PDO::PARAM_INT);
		$statement->bindValue(':rating', (int)$this->myRating, PDO::PARAM_INT);
		return $statement->execute();
	}
	public function delete($aUseTransaction = false)
	{
		if(is_null(self::$deleteStmt))
		{
			self::$deleteStmt = Core::$db->prepare('
				DELETE FROM `'.Core::$prefix.'ratings`
				WHERE content_id=:contentId AND	content_type=:contentType;');
		}
		// there is no sense in using a transaction for a 1-statement function
		$statement = self::$deleteStmt;
		$statement->bindValue(':contentId', (int)$aContentId, PDO::PARAM_INT);
		$statement->bindValue(':contentType', (int)$aContentType, PDO::PARAM_INT);
		return $statement->execute();
	}
	public function assignData($aData)
	{
		$this->myRating = (int)$aData;
	}


	/**
	 * ContentProviderSingle Interface
	 */
	public static function getFor(ContentItem $aItem)
	{
		return new Rating($aItem);
	}
	public static function deleteFor(ContentItem $aItem, $aUseTransaction = false)
	{
		$rating = new Rating($aItem);
		$rating->delete($aUseTransaction);
	}


	/**
	 * JSONable Interface
	 */
	public function toJSON($aEncode = true, $aIncludeChildren = false)
	{
		$this->fetchData();
		return $aEncode ? json_encode($this->data) : $this->data;
	}


	public function __construct($aItem)
	{
		$this->contentId = $aItem->id;
		$this->contentType = $aItem->ContentType();
	}
	
	protected function fetchData()
	{
		if(!is_null($this->data))
			return;
		if(is_null(self::$selectStmt))
		{
			self::$selectStmt = Core::$db->prepare('
				SELECT COUNT(rating) as ratings, AVG(rating) as average,
					SUM(user_id=:userId) as voted
				FROM `'.Core::$prefix.'ratings`
				WHERE content_id=:contentId AND	content_type=:contentType;');
		}
		$statement = self::$selectStmt;
		$statement->bindValue(':userId', (int)Core::$user->user_id, PDO::PARAM_INT);
		$statement->bindValue(':contentId', (int)$this->contentId, PDO::PARAM_INT);
		$statement->bindValue(':contentType', (int)$this->contentType, PDO::PARAM_INT);
		$statement->execute();
		$this->data = $statement->fetch(PDO::FETCH_ASSOC);
	}
	
	public function __get($aVar)
	{
		$this->fetchData();
		return isset($this->data[$aVar]) ? $this->data[$aVar] : null;
	}
	public function __isset($aVar)
	{
		$this->fetchData();
		return isset($this->data[$aVar]);
	}
}

/**
 * TODO: use a modified version of this as basis for a better RESTDispatcher
 */
class RatingDispatcher implements RESTful
{
	public static function doGET(RESTDispatcher $dispatcher)
	{
		$child = $dispatcher->peekNext();
		$parent = $dispatcher->peekPrevious();
		if($child || !$parent || !($parent['obj'] instanceof ContentItem))
			throw new NotImplementedException();
		// we have a parent
		return Rating::getFor($parent['obj']);
	}
	public static function doPOST(RESTDispatcher $dispatcher)
	{
		$child = $dispatcher->peekNext();
		$parent = $dispatcher->peekPrevious();
		if($child || !$parent || !($parent['obj'] instanceof ContentItem))
			throw new NotImplementedException();
		// we have a parent
		$dispatcher->getJSON();
		$rating = Rating::getFor($parent['obj']);
		$rating->assignData($dispatcher->getJSON());
		if(!$rating->save(true))
			throw new UnauthorizedException();
		return $rating;
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
