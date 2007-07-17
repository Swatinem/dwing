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
		$news = self::$_db->queryfirst('SELECT * FROM '.self::$_db->pref.'news WHERE news_id='.(int)$aNewsId.';');
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

			$fancyUrl = Utils::fancyUrl($_POST['title']);
			$urlconflict = self::$_db->queryFirst('
				SELECT news_id FROM '.self::$_db->pref.'news
				WHERE fancyurl="'.self::$_db->escape($fancyUrl).'";');

			// insert the news
			self::$_db->query('
			INSERT INTO
				'.self::$_db->pref.'news
			SET
				title="'.self::$_db->escape($_POST['title']).'",
				time='.(int)$time.',
				user_id='.(int)self::$_user->user_id.',
				text="'.self::$_db->escape(Utils::purify($_POST['text'])).'",
				fancyurl="'.(!empty($urlconflict) ? time() : self::$_db->escape($fancyUrl)).'";');
			$insertId = self::$_db->insert_id;

			if(!empty($urlconflict))
				self::$_db->query('
				UPDATE '.self::$_db->pref.'news
				SET fancyurl="'.self::$_db->escape($fancyUrl.'-'.$insertId).'"
				WHERE news_id='.(int)$insertId.';');

			// link with tags
			Tags::setTagsForContent($insertId, ContentType::NEWS, $_POST['tags']);

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
		self::$_db->query('DELETE FROM '.self::$_db->pref.'news WHERE news_id='.(int)$aNewsId.';');
		// delete the associations with tags
		Tags::deleteTagsForContent((int)$aNewsId, ContentType::NEWS);
		return true;
	}
}
?>