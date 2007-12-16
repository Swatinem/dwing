<?php
/*
 * dWing - a nextgen CMS built on PHP5, MySQLi and Ajax
 * Copyright (C) 2006-2007 Arpad Borsos <arpad.borsos@googlemail.com>
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
 * Updater
 *
 * updater to change the db layout when necessary
 */
class Updater extends Module
{
	/**
	 * the new version
	 *
	 * @var string $version
	 **/
	public $version;
	/**
	 * the old version
	 *
	 * @var string $oldversion
	 **/
	public $oldversion;
	/**
	 * all available versions
	 *
	 * @var array $_versions
	 **/
	private $_versions;
	/**
	 * all the available languages
	 *
	 * @var array $_langs
	 **/
	private $_langs;

	/**
	 * constructor
	 *
	 * @param string $aCurrentVersion
	 * @return void
	 **/
	public function __construct()
	{
		$this->_versions = array('0.0.1', '0.0.2', '0.0.3', '0.0.4', '0.0.5',
			'0.0.6', '0.0.7');
		$this->_langs = array('en', 'de');
		$this->version = $this->_versions[count($this->_versions)-1];
		$this->oldversion = self::$_cfg['version'];
	}

	/**
	 * update the language files
	 *
	 * @return void
	 **/
	private function _updatelang()
	{
		foreach($this->_langs as $lang)
		{
			if(!file_exists('./lang/'.$lang.'.php')) // new language
			{
				copy('./lang/'.$lang.'_inst.php','./lang/'.$lang.'.php');
			}
			elseif(!file_exists('./lang/'.$lang.'_inst.php'))
			{
				continue;
			}
			else // language already exists
			{
				$langtbl = l10n::mergeLang($lang, $lang.'_inst');
				file_put_contents('./lang/'.$lang.'.php', "<?php\n\$__LANG = ".var_export($langtbl, true).";\n?>");
			}
		}
	}

	/**
	 * save the config with the new version
	 *
	 * @param string $aVersion the version string to write to the settings
	 *  uses the latest version if empty
	 * @return void
	 **/
	public function updatecfg($aVersion = null)
	{
		self::$_cfg['version'] = !empty($aVersion) ? $aVersion : $this->version;
		file_put_contents('inc/settings.php',"<?php\n\$_cfg = ".var_export(self::$_cfg, true).";\n?>");
	}

	/**
	 * converts a version number to a int
	 *
	 * @param string $version
	 * @return int
	 **/
	private function _intversion($version)
	{
		$version = explode('.',$version);
		return 10000*(int)$version[0]+100*(int)$version[1]+(int)$version[2];
	}

	/**
	 * automatically do all updates we need
	 *
	 * @return bool
	 **/
	public function update()
	{
		$this->_updatelang();
		// problem: the updater itself could require string changes
		for($i = array_search($this->oldversion,$this->_versions), $max = count($this->_versions)-1; $i < $max; $i++)
		{
			$fromversion = $this->_versions[$i];
			$toversion = $this->_versions[$i+1];
			$methodname = '_'.$this->_intversion($fromversion).'to'.$this->_intversion($toversion);
			if(method_exists($this, $methodname))
			{
				if(!call_user_func(array($this,$methodname)))
				{
					// we successfully passed older updates so lets at least write that version to settings.
					$this->updatecfg($fromversion);
					return false;
				}
			}
		}
		// all updates done, write the new version to the settings
		$this->updatecfg();
		return true;
	}

	/**
	 * Update from x.y.z to x.y.z
	 * 
	 * @return bool
	 **/
	/* This is just an example
	private function _xyztoxyz()
	{
		return true;
	}*/

	/**
	 * Update from 0.0.1 to 0.0.2
	 * 
	 * @return bool
	 **/
	private function _1to2()
	{
		// add ratings table
		self::$_db->query('CREATE TABLE `'.self::$_db->pref.'ratings` (
			`user_id` INT UNSIGNED NOT NULL ,
			`content_id` INT UNSIGNED NOT NULL ,
			`content_type` TINYINT UNSIGNED NOT NULL ,
			`rating` TINYINT UNSIGNED NOT NULL ,
			PRIMARY KEY ( `user_id` , `content_id` , `content_type` )
			) ENGINE = innodb;');
		return true;
	}

	/**
	 * Update from 0.0.2 to 0.0.3
	 * 
	 * @return bool
	 **/
	private function _2to3()
	{
		$newsRes = self::$_db->query('
			SELECT news_id, text, COUNT(links.link_id) as linknum
			FROM '.self::$_db->pref.'news_news
			LEFT JOIN '.self::$_db->pref.'news_links AS links USING (news_id)
			GROUP BY news_id HAVING linknum > 0');
		while($news = $newsRes->fetch(PDO::FETCH_ASSOC))
		{
			// hardcode german...
			$text = $news['text'].'<br /><br /><strong>Links zu dem Thema:</strong><ul>';
			$linksRes = self::$_db->query('SELECT * FROM '.self::$_db->pref.'news_links WHERE news_id='.(int)$news['news_id'].';');
			while($link = $linksRes->fetch(PDO::FETCH_ASSOC))
			{
				$text.= '<li><a href="'.$link['url'].'">'.$link['name'].'</a></li>';
			}
			$text.= '</ul>';
			self::$_db->query('
				UPDATE '.self::$_db->pref.'news_news
				SET text="'.self::$_db->escape($text).'"
				WHERE news_id='.(int)$news['news_id'].';');
		}
		// drop the links table
		self::$_db->query('DROP TABLE `'.self::$_db->pref.'news_links`;');
		// rename the news table
		self::$_db->query('RENAME TABLE `'.self::$_db->pref.'news_news`  TO `'.self::$_db->pref.'news`;');
		return true;
	}

	/**
	 * Update from 0.0.3 to 0.0.4
	 * 
	 * @return bool
	 **/
	private function _3to4()
	{
		self::$_db->query('ALTER TABLE `'.self::$_db->pref.'news` ADD `fancyurl` VARCHAR( 128 ) NOT NULL;');

		$cleanTitles = array();
		$newsRes = self::$_db->query('SELECT news_id, title FROM '.self::$_db->pref.'news;');
		while($news = $newsRes->fetch(PDO::FETCH_ASSOC))
		{
			$cleanTitle = Utils::fancyUrl($news['title']);
			if(in_array($cleanTitle, $cleanTitles))
				$cleanTitle.= '-'.$news['news_id'];
			$cleanTitles[] = $cleanTitle;
			self::$_db->query('
				UPDATE `'.self::$_db->pref.'news` SET fancyurl="'.self::$_db->escape($cleanTitle).'"
				WHERE news_id='.(int)$news['news_id'].';');
		}
		self::$_db->query('ALTER TABLE `'.self::$_db->pref.'news` ADD UNIQUE `fancyurl` ( `fancyurl` );');
		return true;
	}

	/**
	 * Update from 0.0.4 to 0.0.5
	 * 
	 * @return bool
	 **/
	private function _4to5()
	{
		// add comments table
		self::$_db->query('CREATE TABLE `'.self::$_db->pref.'comments` (
			`comment_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`user_id` INT UNSIGNED NOT NULL ,
			`time` INT UNSIGNED NOT NULL ,
			`text` TEXT NOT NULL ,
			`content_id` INT UNSIGNED NOT NULL ,
			`content_type` TINYINT UNSIGNED NOT NULL ,
			INDEX ( `content_id` , `content_type` )
			) ENGINE = innodb;');
		self::$_db->query('ALTER TABLE `'.self::$_db->pref.'comments` ADD INDEX ( `user_id` );');
		return true;
	}

	/**
	 * Update from 0.0.5 to 0.0.6
	 * 
	 * @return bool
	 **/
	private function _5to6()
	{
		$tagsRes = self::$_db->query('SELECT tag_id, name FROM '.self::$_db->pref.'tags;');
		while($tagRow = $tagsRes->fetch(PDO::FETCH_ASSOC))
		{
			self::$_db->query('
				UPDATE `'.self::$_db->pref.'tags` SET name="'.self::$_db->escape(Utils::fancyUrl($tagRow['name'])).'"
				WHERE tag_id='.(int)$tagRow['tag_id'].';');
		}
		return true;
	}

	/**
	 * Update from 0.0.6 to 0.0.7
	 * 
	 * @return bool
	 **/
	private function _6to7()
	{
		// add comments table
		self::$_db->query('CREATE TABLE `'.self::$_db->pref.'openids` (
			`openid` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
			`user_id` INT UNSIGNED NOT NULL ,
			PRIMARY KEY ( `openid` ) ,
			INDEX ( `user_id` )
			) ENGINE = InnoDB;');
		self::$_db->exec('INSERT INTO `'.self::$_db->pref.'openids` (SELECT 
		openid, user_id FROM `'.self::$_db->pref.'user`);');
		self::$_db->exec('ALTER TABLE `'.self::$_db->pref.'user` DROP openid;');
		return true;
	}
}
?>