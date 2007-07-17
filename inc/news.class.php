<?php
/*
 * dWing - a nextgen CMS built on PHP5, MySQLi and Ajax
 * Copyright (C) 2004-2007 Arpad Borsos <arpad.borsos@googlemail.com>
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
 * News module
 *
 * This class provides functions to handle news
 */
class News extends Module
{
	/**
	 * return the details of X news associated with either of the specified tags
	 *
	 * @param int $aPerPage how many news to return
	 * @param string $aTags
	 * @return array
	 **/
	public static function getNews($aPerPage, $aTags = null)
	{
		$query = '
		SELECT news.* FROM '.self::$_db->pref.'news AS news';
		if(!empty($aTags))
		{
			// problem: output of Tags::cleanTags is not escaped. lets hope we can trust it
			$query.= ' LEFT JOIN '.self::$_db->pref.'tagstocontent AS tagstocontent ON news.news_id = tagstocontent.content_id
			LEFT JOIN '.self::$_db->pref.'tags AS tags ON tags.tag_id = tagstocontent.tag_id
			WHERE tags.name IN ("'.implode('","',Tags::cleanTags($aTags)).'") AND tagstocontent.content_type='.ContentType::NEWS;
		}
		if(!empty($_GET['news_id']))
		{
			if(empty($aTagIds))
				$query.= ' WHERE 1';
			if((string)(int)$_GET['news_id'] == $_GET['news_id'])
				$query.= ' AND news.news_id='.(int)$_GET['news_id'];
			else
				$query.= ' AND news.fancyurl="'.self::$_db->escape($_GET['news_id']).'"';
		}
		$query.= ' ORDER BY news.time DESC '.Utils::sqlLimit($aPerPage).';';

		$news = array();
		$news_res = self::$_db->query($query);
		while($news_row = $news_res->fetch(PDO::FETCH_ASSOC))
		{
			$news_row['user'] = Users::getUser($news_row['user_id']);
			unset($news_row['user_id']);
			$news[] = $news_row;
		}
		unset($news_res);
		return $news;
	}

	/**
	 * return all details of one news item, including related links, associated tags and comments
	 *
	 * @param int $aNewsId Id of the news we want to get
	 * @return array
	 **/
	public static function getNewsAllDetails($aNewsId)
	{
		$statement = self::$_db->prepare('SELECT * FROM '.self::$_db->pref.'news WHERE news_id=:newsId;');
		$statement->bindValue(':newsId', (int)$aNewsId, PDO::PARAM_INT);
		$statement->execute();
		$news = $statement->fetch(PDO::FETCH_ASSOC);
		$news['user'] = Users::getUser($news['user_id']);
		$news['tags'] = Tags::getTagsForContent($aNewsId, ContentType::NEWS);
		return $news;
	}

	/**
	 * add a news
	 *
	 * @return mixed Exception on failure, id on success
	 **/
	public static function addNews()
	{
		try
		{
			if(empty($_POST['title'])) throw new Exception(l10n::_('No title defined.'));
			$time = (empty($_POST['time']) || !strtotime($_POST['time'])) ? time() : strtotime($_POST['time']);

			self::$_db->beginTransaction();
			$fancyUrl = Utils::fancyUrl($_POST['title']);
			$statement = self::$_db->prepare('
				SELECT COUNT(news_id) FROM '.self::$_db->pref.'news
				WHERE fancyurl=:fancyurl;');
			$statement->bindValue(':fancyurl', $fancyUrl, PDO::PARAM_STR);
			$statement->execute();
			$urlconflict = $statement->fetchColumn();

			// insert the news
			$statement = self::$_db->prepare('
			INSERT INTO
				'.self::$_db->pref.'news
			SET
				title=:title, time=:time, user_id=:userId, text=:text,
				fancyurl=:fancyurl;');
			$statement->bindValue(':title', $_POST['title'], PDO::PARAM_STR);
			$statement->bindValue(':time', (int)$time, PDO::PARAM_INT);
			$statement->bindValue(':userId', (int)self::$_user->user_id, PDO::PARAM_INT);
			$statement->bindValue(':text', Utils::purify($_POST['text']), PDO::PARAM_STR);
			$statement->bindValue(':fancyurl', (!empty($urlconflict) ? time() : self::$_db->escape($fancyUrl)), PDO::PARAM_STR);
			$statement->execute();
			$insertId = self::$_db->lastInsertId();

			if(!empty($urlconflict))
			{
				$statement = self::$_db->prepare('
				UPDATE '.self::$_db->pref.'news
				SET fancyurl=:fancyurl
				WHERE news_id=:newsId;');
				$statement->bindValue(':fancyurl', $fancyUrl.'-'.$insertId, PDO::PARAM_STR);
				$statement->bindValue(':newsId', (int)$insertId, PDO::PARAM_INT);
				$statement->execute();
			}

			// link with tags
			Tags::setTagsForContent($insertId, ContentType::NEWS, $_POST['tags']);

			self::$_db->commit();
			return $insertId;
		}
		catch(Exception $e)
		{
			return $e;
		}
	}

	/**
	 * edit the specified news item
	 *
	 * @param int $aNewsId Id of the news item we want to edit
	 * @return bool
	 **/
	/* TODO: rewrite this!
	public static function editNews($aNewsId)
	{
	}*/

	/**
	 * deletes the specified news item
	 *
	 * @param int $aNewsId Id of the news item we want to delete
	 * @return bool
	 **/
	public static function deleteNews($aNewsId)
	{
		// delete the Db entries
		$statement = self::$_db->prepare('DELETE FROM '.self::$_db->pref.'news WHERE news_id=:newsId;');
		$statement->bindValue(':newsId', (int)$aNewsId, PDO::PARAM_INT);
		$statement->execute();
		// delete the associations with tags
		Tags::deleteTagsForContent((int)$aNewsId, ContentType::NEWS);
		return true;
	}
}
?>