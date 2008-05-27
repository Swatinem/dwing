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
 * TODO:
 * hook this up with dwings template system, probably use the RESTDispatcher
 * as only method of answering requests
 * 1. check if there is a template file with the name of the requested resource
 * -> display it
 * 2. if the resource throws an exception or does not exist, display a error page
 * 3. check if there is a template file with the name "Resource.RequestType.Method"
 * -> 415 if there is a requested type without a corresponding template
 * 4. if there is no RequestType given:
 * -> fall back to Resource.xhtml.Method -> the resource may display different 
 *    xhtml representations depending on the Method (GET -> full page, POST -> fragment)
 * -> fall back to Resource.json
 * -> fall back to just call object->toJSON()
 *
 * Problems:
 * - Error Messages in XHTML or plaintext?
 * - object arrays?
 */

class NotFoundException extends Exception // 404
{
	public $httpCode = 404;
	protected $message = 'Ressource not found';
}
// 415 Unsupported Media Type ???
class UnauthorizedException extends Exception // 401
{
	public $httpCode = 401;
	protected $message = 'Unauthorized';
}
class NotImplementedException extends Exception // 501
{
	public $httpCode = 501;
	protected $message = 'Method not implemented';
}

interface RESTful
{
	public static function GET(RESTDispatcher $dispatcher);
	public static function POST(RESTDispatcher $dispatcher);
	public static function PUT(RESTDispatcher $dispatcher);
	public static function DELETE(RESTDispatcher $dispatcher);
	public function exists(); // wether the object points to a existing resource
	public function toJSON(); // return a JSON representation
}

class RESTDispatcher
{
	public $requestedType;
	protected $resources = array();
	protected $current = 0;
	
	public function __construct()
	{
		$requestURI = $_SERVER["REQUEST_URI"];
		$selfDir = dirname($_SERVER['PHP_SELF']).(dirname($_SERVER['PHP_SELF']) != '/' ? '/' : '');
		$webRoot = 'http://'.$_SERVER['SERVER_NAME'].$selfDir;

		$url = preg_replace('!^'.$selfDir.'([^?]*)(\?.*)?$!', '\1', $requestURI);
		$frags = explode('.', $url);
		if(!empty($frags[1]))
			$this->requestedType = $frags[1];
		$frags = explode('/', $frags[0]);
		for($i = 0; $i < count($frags); $i+=2)
		{
			if(empty($frags[$i]))
				break;
			$temp = array('resource' => strtolower($frags[$i]));
			if(!empty($frags[$i+1]))
				$temp['id'] = $frags[$i+1];
			array_push($this->resources, $temp);
		}
	}
	public function assignObject(/* REST ? */$aObj)
	{
		$this->resources[$this->current]['obj'] = $aObj;
	}
	public function current()
	{
		if(!empty($this->resources[$this->current]))
			return $this->resources[$this->current];
	}
	public function next()
	{
		if(!empty($this->resources[$this->current+1]))
		{
			$this->current++;
			return $this->resources[$this->current];
		}
	}
	public function previous()
	{
		if($this->current > 0)
		{
			$this->current--;
			return $this->resources[$this->current];
		}
	}
	public function dispatch()
	{
		if(empty($this->resources[$this->current]))
			return;
		try
		{
			$className = $this->resources[$this->current]['resource'];
			if(!class_exists($className) || !in_array('RESTful', class_implements($className)))
				throw new NotFoundException();

			$obj = call_user_func(array($className, $_SERVER['REQUEST_METHOD']), $this);
			if(is_null($obj))
				return;
			if($obj instanceof RESTful)
			{
				//var_dump($obj);
				$str = (string)$obj;
				if($str == 'false')
					throw new NotFoundException();
				echo $str;
				exit;
			}
			else
			{
				echo $obj;
				exit;
			}
		}
		catch(Exception $e)
		{
			if(!empty($e->httpCode))
			{
				// 401, 404 or 501...
				header(' ', true, $e->httpCode);
				echo $e->getMessage();
				exit;
			}
			else
			{
				// 500, internal error?
				header(' ', true, 500);
				echo $e->getMessage();
				exit;
			}
		}
	}
}

abstract class REST extends CRUD implements RESTful
{
	/*
	 * the abstract class cannot deal with parent-resources and fails.
	 * it automatically forwards the request to any child-resources.
	 * override the methods if other behavior is wanted.
	 **/
	/*
	 * the abstract class cannot deal with getting multiple objects.
	 * override this method to deal with that.
	 **/
	public static function GET(RESTDispatcher $dispatcher)
	{
		$current = $dispatcher->current();
		if($parent = $dispatcher->previous())
			throw new NotImplementedException(); // can't deal with parent-resources
		if(empty($current['id']))
			throw new NotImplementedException(); // listing not implemented
		$obj = new $current['resource']($current['id']);
		$dispatcher->assignObject($obj);

		$child = $dispatcher->next();
		if(!$child)
			return $obj;
		else
			return $dispatcher->dispatch();
	}
	public static function POST(RESTDispatcher $dispatcher)
	{
		$current = $dispatcher->current();
		if($parent = $dispatcher->previous())
			throw new NotImplementedException(); // can't deal with parent-resources
		$child = $dispatcher->next();
		if(!$child && !empty($current['id']))
			throw new NotImplementedException(); // can't POST to a existing resource
		if(!$child)
		{
			$obj = new $current['resource'](json_decode(file_get_contents('php://input'), true));
			$obj->save();
			return $obj;
		}
		// have $child
		$dispatcher->previous(); // goes back to current, needed for assignObject()
		$obj = new $current['resource']($current['id']);
		$dispatcher->assignObject($obj);

		$dispatcher->next(); // goes forward to the child again
		return $dispatcher->dispatch();
	}
	public static function PUT(RESTDispatcher $dispatcher)
	{
		$current = $dispatcher->current();
		if($parent = $dispatcher->previous())
			throw new NotImplementedException(); // can't deal with parent-resources
		if(empty($current['id']))
			throw new NotImplementedException(); // we need a existing resource

		$obj = new $current['resource']($current['id']);
		$dispatcher->assignObject($obj);

		$child = $dispatcher->next();
		if(!$child) // update the current resource
		{
			$obj->assignData(json_decode(file_get_contents('php://input'), true));
			$obj->save();
			return $obj;
		}
		// have $child
		return $dispatcher->dispatch(); // dispatch to child
	}
	public static function DELETE(RESTDispatcher $dispatcher)
	{
		$current = $dispatcher->current();
		if($parent = $dispatcher->previous())
			throw new NotImplementedException(); // can't deal with parent-resources
		if(empty($current['id']))
			throw new NotImplementedException(); // we need a existing resource

		$obj = new $current['resource']($current['id']);
		$dispatcher->assignObject($obj);

		$child = $dispatcher->next();
		if(!$child) // destroy the current resource
		{
			$obj->destroy();
			return true;
		}
		// have $child
		return $dispatcher->dispatch(); // dispatch to child
	}
	public function exists()
	{
		// resource should exist if it has an ID
		return !empty($this->id);
	}
	public function toJSON()
	{
		if(empty($this->id))
			return 'false';
		$displayArray = array($this->primaryKey => $this->id);
		foreach($this->definition as $column => $unused)
			$displayArray[$column] = $this->$column;
		return json_encode($displayArray);
	}
}
?>
