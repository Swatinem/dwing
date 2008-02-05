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
Needs to be initialized with CRUD::init($db) first!

Used like this:

class News extends CRUD
{
	//protected $tableName = 'news';
	protected $primaryKey = 'news_id';
	protected $definition = array('title' => 'required', 'text' => 'html',
		'user_id' => 'userId', 'time' => 'time', 'fancyurl' => true);
}
class Comment extends CRUD
{
	protected $tableName = 'comments';
	protected $definition = array('text' => 'html', 'user_id' => 'userId',
		'time' => 'time', 'content_id' => 'required', 'content_type' => 'required');
}
*/
abstract class CRUD
{
	private static $statements = array();
	private static $db;
	protected $primaryKey = 'id';
	protected $data = array();
	protected $className;
	protected $tableName;
	public $id;
	
	public function __construct($aData = null)
	{
		$this->className = get_class($this);
		if(empty($this->tableName))
			$this->tableName = strtolower($this->className);
		
		if(is_array($aData))
		{
			$this->data = $aData;
			if(!empty($aData[$this->primaryKey]))
			{
				$this->id = $aData[$this->primaryKey];
				if(count($aData) == 1)
					$this->data = array();
			}
		}
		if((string)(int)$aData == $aData)
		{
			$this->id = $aData;
		}
	}
	public static function init($aDb)
	{
		self::$db = $aDb;
	}
	public function __get($aVarName)
	{
		$childClass = $this->className;
		if(empty(self::$statements[$childClass]['read']))
		{
			self::$statements[$childClass]['read'] =
				self::$db->prepare('SELECT * FROM '.$this->tableName.' WHERE '.
					$this->primaryKey.'=:id;');
		}
		if(empty($this->data))
		{
			$statement = self::$statements[$childClass]['read'];
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
			$statement->execute();
			$this->data = $statement->fetch(PDO::FETCH_ASSOC);
		}
		return isset($this->data[$aVarName]) ? $this->data[$aVarName] : null;
	}
	public function __set($aVarName, $aValue)
	{
		if(empty($this->data))
			$this->$aVarName; // fake a __get to get all data
		$this->data[$aVarName] = $aValue;
	}
	public function save()
	{
		$childClass = $this->className;
		if(empty($this->id))
		{
			if(empty(self::$statements[$childClass]['create']))
			{
				$query = 'INSERT INTO '.$this->tableName.' SET ';
				$colDefs = array();
				foreach($this->definition as $column => $options)
				{
					$colDefs[] = $column.'=:'.$column;
				}
				$query.= implode(', ', $colDefs).';';
				self::$statements[$childClass]['create'] = self::$db->prepare($query);
			}
			$statement = self::$statements[$childClass]['create'];
		}
		else
		{
			if(empty(self::$statements[$childClass]['update']))
			{
				$query = 'UPDATE '.$this->tableName.' SET ';
				$colDefs = array();
				foreach($this->definition as $column => $options)
				{
					$colDefs[] = $column.'=:'.$column;
				}
				$query.= implode(', ', $colDefs).' WHERE '.$this->primaryKey.'=:id;';
				self::$statements[$childClass]['update'] = self::$db->prepare($query);
			}
			$statement = self::$statements[$childClass]['update'];
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
		}
		foreach($this->definition as $column => $options)
		{
			switch($options)
			{
				case 'userId':
				case 'time':
					$statement->bindValue(':'.$column, isset($this->data[$column]) ? 
						$this->data[$column] : 0, PDO::PARAM_INT);
				break;
				case 'required':
					if(empty($this->data[$column]))
						throw new Exception($column.' was empty');
				default:
					$statement->bindValue(':'.$column, isset($this->data[$column]) ? 
						$this->data[$column] : '', PDO::PARAM_STR);
			}
		}
		if(!$statement->execute())
			throw new Exception($statement->errorInfo[2]);
		if(empty($this->id))
			return self::$db->lastInsertId();
		else
			return true;
	}
	public function delete()
	{
		$childClass = $this->className;
		if(empty(self::$statements[$childClass]['delete']))
		{
			self::$statements[$childClass]['delete'] =
				self::$db->prepare('DELETE FROM '.$this->tableName.' WHERE '.
					$this->primaryKey.'=:id;');
		}
		$statement = self::$statements[$childClass]['delete'];
		$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
		return $statement->execute();
	}
}
?>