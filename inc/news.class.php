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

/*
 * News Object
 */
class News extends CRUD
{
	//protected $tableName = 'news';
	protected $primaryKey = 'news_id';
	protected $definition = array('title' => 'required', 'text' => 'html',
		'user_id' => 'user', 'time' => 'time', 'fancyurl' => 'value');

	public function __get($aVarName)
	{
		switch($aVarName)
		{
			case 'comments':
				throw new Exception('Not Implemented');
			break;
			case 'tags':
				if(!isset($this->data['tags']))
					$this->data['tags'] = 
						Tags::getTagsForContent($this->id, ContentType::NEWS);
				return $this->data['tags'];
			break;
			case 'rating':
				throw new Exception('Not Implemented');
			break;
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

// TODO: need something better than the Module parent class
/*
 * News Iterator for a Range
 */
class NewsRange extends Module implements Iterator, Countable
{
	protected $elements = array();
	protected $position = 0;

	private static $countStmt;
	private static $selectStmt;

	public function __construct($aStart = 0, $aLimit = 10)
	{
		if(empty(self::$selectStmt))
		{
			self::$selectStmt = self::$_db->prepare(
				'SELECT news.* FROM '.self::$_db->pref.'news AS news
				ORDER BY news.time DESC LIMIT :start, :limit;');
		}
		$statement = self::$selectStmt;
		$statement->bindValue(':start', (int)$aStart, PDO::PARAM_INT);
		$statement->bindValue(':limit', (int)$aLimit, PDO::PARAM_INT);
		$statement->execute();
		$statement->setFetchMode(PDO::FETCH_CLASS, 'News');
		$this->elements = $statement->fetchAll();
		//var_dump($this->elements);
	}

	// Countable Interface:
	public function count()
	{
		// TODO: count the total news available
	}

	// Iterator Interface:
	public function current()
	{
		return $this->elements[$this->position];
	}
	public function key()
	{
		return $this->elements[$this->position]->fancyurl;
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
		return isset($this->elements[$this->position]);
	}
}
?>
