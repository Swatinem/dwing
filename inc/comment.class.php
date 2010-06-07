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

/*
 * Comment Object
 */
class Comment extends ActiveItem implements ContentItem, ContentProvider
{
	public static function ContentType()
	{
		return 4;
	}

	protected $tableName = 'comments';
	protected $primaryKey = 'comment_id';
	protected $definition = array('text' => 'html', 'user_id' => 'user',
		'time' => 'time', 'content_id' => 'required', 'content_type' => 'required');

	public function __construct($obj = null)
	{
		if(is_object($obj) && $obj instanceof ContentItem)
		{
			$this->data['content_id'] = $obj->id;
			$this->data['content_type'] = $obj->ContentType();
		}
		parent::__construct($obj);
	}

	/**
	 * ContentProvider Interface
	 */
	public static function addAllFor(ContentItem $aItem, $aItems, $aUseTransaction = false)
	{
		// this does not apply for Comment
		return false;
	}
	public static function getAllFor(ContentItem $aItem)
	{
		return new CommentIterator($aItem);
	}
	public static function deleteAllFor(ContentItem $aItem, $aUseTransaction = false)
	{
		if($aUseTransaction)
			Core::$db->beginTransaction();
		$all = new CommentIterator($aItem);
		$all->delete();
		if($aUseTransaction)
			Core::$db->commit();
		return true;
	}
}

/*
 * Comment Iterator for a Content Item
 */
class CommentIterator implements Iterator, Countable
{
	protected $elements = null;
	protected $position = 0;

	protected $contentId;
	protected $contentType;

	private static $countStmt;
	private static $selectStmt;

	public function __construct(ContentItem $aItem)
	{
		if(empty(self::$selectStmt))
		{
			self::$selectStmt = Core::$db->prepare(
				'SELECT comments.* FROM '.Core::$prefix.'comments AS comments
				WHERE content_id=:contentId AND content_type=:contentType
				ORDER BY time ASC;');
		}
		if(empty(self::$countStmt))
		{
			self::$countStmt = Core::$db->prepare('
				SELECT COUNT(*) as commentnum FROM '.Core::$prefix.'comments
				WHERE content_id=:contentId AND content_type=:contentType;');
		}
		$this->contentId = (int)$aItem->id;
		$this->contentType = (int)$aItem->ContentType();
	}
	protected function lazyFetch()
	{
		if($this->elements != null)
			return;
		$statement = self::$selectStmt;
		$statement->bindValue(':contentId', $this->contentId, PDO::PARAM_INT);
		$statement->bindValue(':contentType', $this->contentType, PDO::PARAM_INT);
		$statement->execute();
		$statement->setFetchMode(PDO::FETCH_CLASS, 'Comment');
		$this->elements = $statement->fetchAll();
	}
	public function delete()
	{
		$this->lazyFetch();
		foreach($this->elements as $element)
		{
			// the CRUD object can delete all the associated subobjects
			$element->delete();
		}
		return true;
	}

	// Countable Interface:
	public function count()
	{
		$statement = self::$countStmt;
		$statement->bindValue(':contentId', $this->contentId, PDO::PARAM_INT);
		$statement->bindValue(':contentType', $this->contentType, PDO::PARAM_INT);
		$statement->execute();
		return $statement->fetchColumn();
	}

	// Iterator Interface:
	public function current()
	{
		$this->lazyFetch();
		return $this->elements[$this->position];
	}
	public function key()
	{
		$this->lazyFetch();
		return $this->elements[$this->position]->id;
	}
	public function next()
	{
		++$this->position;
	}
	public function rewind()
	{
		$this->position = 0;
	}
	public function valid()
	{
		$this->lazyFetch();
		return isset($this->elements[$this->position]);
	}
}

class CommentDispatcher extends REST
{
	public static function doGET(RESTDispatcher $dispatcher)
	{
		$current = $dispatcher->current();
		if(empty($current['id']))
			throw new NotImplementedException(); // listing not implemented
		$obj = new $current['resource']($current['id']);
		$dispatcher->assignObject($obj);

		$child = $dispatcher->next();
		if(!$child)
			return $obj;
		else
			return $dispatcher->dispatch();
	}
	public static function doPOST(RESTDispatcher $dispatcher)
	{
		$current = $dispatcher->current();
		$child = $dispatcher->next();
		if($child)
		{
			$obj = new Comment($current['id']);
			$dispatcher->previous();
			$dispatcher->assignObject($obj);
			$dispatcher->next(); // assign the object to the right resource
			return $dispatcher->dispatch();
		}
		$parent = $dispatcher->previous();
		if(!$parent)
			throw new NotImplementedException();
		if(!Core::$user->authed)
			throw new UnauthorizedException();

		$obj = new Comment($parent['obj']); // so the Comment has info about the
		// parent Id and ContentType
		$obj->assignData($dispatcher->getJSON());
		$obj->save();
		$dispatcher->next(); // dispatcher has the right resource
		return $obj;
	}
	public static function doPUT(RESTDispatcher $dispatcher)
	{
		// TODO: implement
		throw new NotImplementedException();
	}
	public static function doDELETE(RESTDispatcher $dispatcher)
	{
		if(!($child = $dispatcher->next()))
			if(!Core::$user->hasRight('news'))
				throw new UnauthorizedException();
		else
			$dispatcher->previous();
		return parent::DELETE($dispatcher);
	}
}
?>
