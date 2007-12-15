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
 * dWign Database Class
 * 
 * Extends the PDO class with a few useful functions
 **/
class Database extends PDO
{
	/**
	 * are we already connected or not?
	 *
	 * @var bool $mConnected
	 **/
	private $mConnected = false;
	/**
	 * PDO DSN String
	 *
	 * @var string $mDSN
	 **/
	private $mDSN;
	/**
	 * Database user
	 *
	 * @var string $mUser
	 **/
	private $mUser;
	/**
	 * Database password
	 *
	 * @var string $mPassword
	 **/
	private $mPassword;
	/**
	 * table prefix
	 *
	 * @var string $pref
	 **/
	public $pref;

	/**
	 * constructor
	 *
	 * @param string $aDSN PDO DSN
	 * @param string $aUser Database user
	 * @param string $aPassword Database password
	 * @param string $aPrefix table prefix
	 * @return void
	 **/
	public function __construct($aDSN, $aUser, $aPassword, $aPrefix)
	{
		$this->mDSN = $aDSN;
		$this->mUser = $aUser;
		$this->mPassword = $aPassword;
		$this->pref = $aPrefix;
	}
	/**
	 * getter for use with insert_id
	 *
	 * @param string $aAttr Attribute to get
	 * @return mixed
	 **/
	private function __get($aAttr)
	{
		if($aAttr == 'insert_id')
			return parent::lastInsertId();
	}
	/**
	 * connect to the database
	 *
	 * @return void
	 **/
	private function connectParent()
	{
		if(!$this->mConnected)
		{
			parent::__construct($this->mDSN, $this->mUser, $this->mPassword);
			$this->mConnected = true;
			$this->exec('SET NAMES "utf8"');
		}
	}
	/**
	 * wrapper for query
	 *
	 * @param string $str
	 * @return PDOStatement
	 **/
	public function query($str)
	{
		$this->connectParent();
		return parent::query($str);
	}
	/**
	 * wrapper for prepare
	 *
	 * @param string $str
	 * @return PDOStatement
	 **/
	public function prepare($str)
	{
		$this->connectParent();
		return parent::prepare($str);
	}
	/**
	 * query the first result
	 *
	 * @param string $str
	 * @return array
	 **/
	public function queryFirst($str)
	{
		$result = $this->query($str);
		return $result->fetch(PDO::FETCH_ASSOC);
	}
	/**
	 * return all the results of the query as an array
	 *
	 * @param string $str
	 * @return array
	 **/
	public function queryAll($str)
	{
		$result = $this->query($str);
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}
	/**
	 * wrapper for quote
	 *
	 * @param string $str
	 * @return string
	 **/
	public function escape($str)
	{
		$this->connectParent(); // we need a connection to get the charset correct
		return substr($this->quote($str), 1, -1);
	}
}

/**
 * The old config.php Files use this class...
 **/
class extmysqli extends Database
{
	public function __construct($aHost, $aUser, $aPassword, $aDatabase, $aPrefix)
	{
		parent::__construct('mysql:dbname='.$aDatabase.';host='.$aHost, $aUser, $aPassword, $aPrefix);
	}
}

?>