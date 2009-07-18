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

// TODO: get rid of this completely when migrating to "runtime/" config system

/**
 * dWing Database Class
 * 
 * Extends the PDO class with a few useful functions
 **/
class Database extends PDO
{
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
		parent::__construct($aDSN, $aUser, $aPassword);
		$this->exec('SET NAMES "utf8"');
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		Core::$prefix = $aPrefix;
	}
}

?>
