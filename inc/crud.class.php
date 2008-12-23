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
Used like this:

class News extends CRUD
{
	//protected $tableName = 'news';
	protected $primaryKey = 'news_id';
	protected $definition = array('title' => 'required', 'text' => 'html',
		'user_id' => 'userId', 'time' => 'time', 'fancyurl' => 'value');
}
class Comment extends CRUD
{
	protected $tableName = 'comments';
	protected $definition = array('text' => 'html', 'user_id' => 'userId',
		'time' => 'time', 'content_id' => 'required', 'content_type' => 'required');
}
*/

/*
 * TODO:
 * fetch data on __get and use IFNULL() to not overwrite the record with empty data
 */

abstract class CRUD
{
	private static $statements = array();
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
			// primary key set: fetch the old record and overwrite the data with the new one
			// problem: I want to create Objects from a fetchAll query that don't re-fetch
			// themselves
			if(!empty($aData[$this->primaryKey]))
			{
				$this->id = (int)$aData[$this->primaryKey];
				$this->fetchData();
				$this->data = array_merge($this->data, $aData);
				unset($this->data[$this->primaryKey]);
			}
			// else: write the data so it can be saved afterwards
			else
			{
				$this->data = $aData;
			}
		}
		if((string)(int)$aData == $aData)
		{
			$this->id = (int)$aData;
			$this->fetchData();
		}
	}
	protected function fetchData()
	{
		$childClass = $this->className;
		if(empty(self::$statements[$childClass]['read']))
		{
			self::$statements[$childClass]['read'] =
				Core::$db->prepare('SELECT * FROM '.$this->tableName.' WHERE '.
					$this->primaryKey.'=:id;');
		}
		if(empty($this->data))
		{
			$statement = self::$statements[$childClass]['read'];
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
			$statement->execute();
			$this->data = $statement->fetch(PDO::FETCH_ASSOC);
			if(empty($this->data))
				unset($this->id);
		}
	}
	public function assignData($aData)
	{
		$this->data = array_merge($this->data, $aData);
	}
	public function __get($aVarName)
	{
		if(!isset($this->definition[$aVarName]) || !isset($this->data[$aVarName]))
			return null;
		switch($this->definition[$aVarName])
		{
			case 'user':
				return Users::getUser($this->data[$aVarName]);
			break;
			default:
				return $this->data[$aVarName];
		}
	}
	public function __isset($aVarName)
	{
		return isset($this->data[$aVarName]);
	}
	public function __set($aVarName, $aValue)
	{
		if(!isset($this->definition[$aVarName]) && $aVarName != $this->primaryKey)
			return;
		if($aVarName == $this->primaryKey)
			$this->id = $aValue;
		else
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
				self::$statements[$childClass]['create'] = Core::$db->prepare($query);
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
					//$colDefs[] = $column.'=IFNULL(:'.$column.','.$column.')';
				}
				$query.= implode(', ', $colDefs).' WHERE '.$this->primaryKey.'=:id;';
				self::$statements[$childClass]['update'] = Core::$db->prepare($query);
			}
			$statement = self::$statements[$childClass]['update'];
			$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
		}
		foreach($this->definition as $column => $options)
		{
			switch($options)
			{
				case 'user':
					throw new Exception('Not Implemented');
				break;
				case 'time':
					$statement->bindValue(':'.$column, isset($this->data[$column]) ? 
						$this->data[$column] : time(), PDO::PARAM_INT);
				break;
				case 'html':
					$statement->bindValue(':'.$column, isset($this->data[$column]) ? 
						Utils::purify($this->data[$column]) : '', PDO::PARAM_STR);
				break;
				case 'required':
					if(empty($this->data[$column]))
						throw new Exception($column.' was empty');
					$statement->bindValue(':'.$column, isset($this->data[$column]) ? 
						$this->data[$column] : '', PDO::PARAM_STR);
				break;
				default:
					$statement->bindValue(':'.$column, isset($this->data[$column]) ? 
						$this->data[$column] : '', PDO::PARAM_STR);
			}
		}
		if(!$statement->execute())
			throw new Exception($statement->errorInfo[2]);
		if(empty($this->id))
			return ($this->id = Core::$db->lastInsertId());
		else
			return true;
	}
	public function destroy()
	{
		$childClass = $this->className;
		if(empty(self::$statements[$childClass]['delete']))
		{
			self::$statements[$childClass]['delete'] =
				Core::$db->prepare('DELETE FROM '.$this->tableName.' WHERE '.
					$this->primaryKey.'=:id;');
		}
		$statement = self::$statements[$childClass]['delete'];
		$statement->bindValue(':id', $this->id, PDO::PARAM_INT);
		if($return = $statement->execute())
		{
			$this->data = array();
			unset($this->id);
		}
		return $return;
	}
}
?>
