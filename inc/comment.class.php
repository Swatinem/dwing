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
class Comment extends CRUD
{
	const ContentType = 4;

	protected $tableName = 'comments';
	protected $primaryKey = 'comment_id';
	protected $definition = array('text' => 'html', 'user_id' => 'userId',
		'time' => 'time', 'content_id' => 'required', 'content_type' => 'required');
	public function __get($aVarName)
	{
		switch($aVarName)
		{
			case 'user':
				return parent::__get('user_id');
			break;
			case 'user_id':
				return null;
			break;
			default:
				return parent::__get($aVarName);
		}
	}
}

/*
 * Comment Iterator for a Content Item
 */
// TODO: maybe manage deleting all Comments of one Content Item through this
// Iterator?
class CommentIterator implements Iterator, Countable
{
	protected $elements = null;
	protected $position = 0;

	protected $contentId;
	protected $contentType;

	private static $countStmt;
	private static $selectStmt;

	public function __construct($aContentId, $aContentType)
	{
		if(empty(self::$selectStmt))
		{
			self::$selectStmt = Core::$db->prepare(
				'SELECT comments.* FROM '.Core::$db->pref.'comments AS comments
				WHERE content_id=:contentId AND content_type=:contentType
				ORDER BY time ASC;');
		}
		if(empty(self::$countStmt))
		{
			self::$countStmt = Core::$db->prepare('
				SELECT COUNT(*) as commentnum FROM '.Core::$db->pref.'comments
				WHERE content_id=:contentId AND content_type=:contentType;');
		}
		$this->contentId = (int)$aContentId;
		$this->contentType = (int)$aContentType;
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
?>
