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
class News extends ActiveRecordBase implements ContentItem
{
	public static function ContentType()
	{
		return 1;
	}
	// TODO: $object::const only works in PHP5.3 -> use public var as alternative
	const ContentType = 1;
	public $ContentType = 1;

	protected $primaryKey = 'news_id';
	protected $definition = array('title' => 'required', 'text' => 'html',
		'user_id' => 'user', 'time' => 'time', 'fancyurl' => 'fancyurl');

	public function __get($aVarName)
	{
		switch($aVarName)
		{
			case 'comments':
				if(!isset($this->data['comments']))
					$this->data['comments'] =
						new CommentIterator($this->id, self::ContentType);
				return $this->data['comments'];
			break;
			case 'tags':
				if(!isset($this->data['tags']))
					$this->data['tags'] = 
						Tags::getTagsForContent($this->id, self::ContentType);
				return $this->data['tags'];
			break;
			default:
				return parent::__get($aVarName);
		}
	}
	public function __set($aVarName, $aValue)
	{
		if($aVarName == 'tags')
			$this->data['tags'] = $aValue;
		else
			return parent::__set($aVarName, $aValue);
	}
	public function delete($aUseTransaction = false)
	{
		// TODO: make use of $aUseTransaction
		Core::$db->beginTransaction();
		Rating::deleteFor($this);
		Tags::deleteTagsForContent($this->id, self::ContentType);
		$comments = new CommentIterator($this->id, self::ContentType);
		$comments->delete();
		$ret = parent::delete();
		Core::$db->commit();
		return $ret;
	}
	public function save($aUseTransaction = false)
	{
		// TODO: make use of $aUseTransaction
		Core::$db->beginTransaction();
		$this->data['fancyurl'] = $fancyUrl = Utils::fancyUrl($this->data['title']);
		$statement = Core::$db->prepare('
		SELECT COUNT(news_id) FROM '.Core::$prefix.'news
		WHERE fancyurl=:fancyurl;');
		$statement->bindValue(':fancyurl', $fancyUrl, PDO::PARAM_STR);
		$statement->execute();
		$urlconflict = $statement->fetchColumn();
		if(!empty($urlconflict))
			$this->data['fancyurl'] = microtime();

		$id = parent::save();

		if(!empty($urlconflict))
		{
			$this->data['fancyurl'] = $fancyUrl.'-'.$id;
			$statement = Core::$db->prepare('
			UPDATE '.Core::$prefix.'news
			SET fancyurl=:fancyurl
			WHERE news_id=:newsId;');
			$statement->bindValue(':fancyurl', $this->data['fancyurl'], PDO::PARAM_STR);
			$statement->bindValue(':newsId', (int)$id, PDO::PARAM_INT);
			$statement->execute();
		}

		// link with tags
		$this->data['tags'] =
			Tags::setTagsForContent($id, self::ContentType, $this->data['tags']);

		Core::$db->commit();
		return $id;
	}
	public function toJSON($aEncode = true, $aIncludeChildren = false)
	{
		// TODO: make use of $aEncode and $aIncludeChildren
		if(empty($this->id))
			return 'false';
		$displayArray = array('id' => $this->id);
		foreach($this->definition as $column => $option)
		{
			if($option == 'user')
			{
				$user = Users::getUser($this->data[$column]);
				$displayArray['user'] = array('id' => $user->id, 'nick' => $user->nick);
			}
			else
				$displayArray[$column] = $this->data[$column];
		}
		$displayArray['tags'] = $this->data['tags'];
		return json_encode($displayArray);
	}
}

/*
 * News Iterator Base
 */
abstract class NewsIterator implements Iterator
{
	protected $elements = array();
	protected $position = 0;

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

/*
 * News Iterator for a Range
 */
class NewsRange extends NewsIterator implements Countable
{
	private static $countStmt;
	private static $selectStmt;

	public function __construct($aStart = 0, $aLimit = 10)
	{
		if(empty(self::$selectStmt))
		{
			self::$selectStmt = Core::$db->prepare(
				'SELECT news.* FROM '.Core::$prefix.'news AS news
				ORDER BY news.time DESC LIMIT :start, :limit;');
		}
		if(empty(self::$countStmt))
		{
			self::$countStmt = Core::$db->prepare('
				SELECT COUNT(*) as num FROM '.Core::$prefix.'news;');
		}
		$statement = self::$selectStmt;
		$statement->bindValue(':start', (int)$aStart, PDO::PARAM_INT);
		$statement->bindValue(':limit', (int)$aLimit, PDO::PARAM_INT);
		$statement->execute();
		$statement->setFetchMode(PDO::FETCH_CLASS, 'News');
		$this->elements = $statement->fetchAll();
	}

	// Countable Interface:
	public function count()
	{
		$statement = self::$countStmt;
		$statement->execute();
		return $statement->fetchColumn();
	}
}

/*
 * News Iterator for News with a specific tag
 */
class NewsWithTag extends NewsIterator implements Countable
{
	protected $tag;

	private static $selectStmt;

	public function __construct($aTag, $aStart = 0, $aLimit = 10)
	{
		$this->tag = $aTag;
		if(empty(self::$selectStmt))
		{
			// TODO: is there something equivalent to IN(...) using only prepared
			// statements?
			self::$selectStmt = Core::$db->prepare(
				'SELECT news.* FROM '.Core::$prefix.'news AS news
				LEFT JOIN '.Core::$prefix.'tagstocontent AS tagstocontent ON
				news.news_id = tagstocontent.content_id LEFT JOIN '.
				Core::$prefix.'tags AS tags ON tags.tag_id = tagstocontent.tag_id
				WHERE tags.name IN ("'.implode('","',Tags::cleanTags($aTag)).'") AND
				tagstocontent.content_type='.News::ContentType.'
				ORDER BY news.time DESC LIMIT :start, :limit;');
		}
		$statement = self::$selectStmt;
		$statement->bindValue(':start', (int)$aStart, PDO::PARAM_INT);
		$statement->bindValue(':limit', (int)$aLimit, PDO::PARAM_INT);
		$statement->execute();
		$statement->setFetchMode(PDO::FETCH_CLASS, 'News');
		$this->elements = $statement->fetchAll();
	}

	// Countable Interface:
	public function count()
	{
		return Tags::getContentCount($this->tag, News::ContentType);
	}
}

class NewsDispatcher extends REST
{
	// other REST method should be sufficient
	public static function doGET(RESTDispatcher $dispatcher)
	{
		$current = $dispatcher->current();
		if(empty($current['id']))
			throw new UseTemplateException('index'); // listing per index page
		else if($current['id'] == 'tags')
		{
			$child = $dispatcher->next();
			// a little quirky: the tag is the resource of the child
			Core::$tpl->assign('requestTag', $child['resource']);
			throw new UseTemplateException('index');
		}
		return parent::GET($dispatcher); // make parent handle the rest
	}
	public static function doPOST(RESTDispatcher $dispatcher)
	{
		if(!($child = $dispatcher->next()))
		{
			if(!Core::$user->hasRight('news'))
				throw new UnauthorizedException();
		}
		else
			$dispatcher->previous();
		$obj = parent::POST($dispatcher);
		if(get_class($obj) == 'News')
			$obj = $obj->toJSON();
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
		{
			if(!Core::$user->hasRight('news'))
				throw new UnauthorizedException();
		}
		else
			$dispatcher->previous();
		return parent::DELETE($dispatcher);
	}
}
?>
