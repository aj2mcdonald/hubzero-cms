<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Sam Wilson <samwilson@purdue.edu>
 * @copyright Copyright 2005-2015 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Hubzero\Filesystem;

use Hubzero\Base\Object;
use Hubzero\Filesystem\Util\Icon;
use Hubzero\Error\Exception\BadMethodCallException;
use Hubzero\Error\Exception\RuntimeException;

/**
 * Filesystem entity
 */
class Entity extends Object
{
	/**
	 * The filesystem adapter use for actually interacting with the entity
	 *
	 * @var  object
	 **/
	protected $adapter = null;

	/**
	 * Constructs the class
	 *
	 * @param   mixed  $properties  Either and associative array or another
	 *                              object to set the initial properties of the object.
	 */
	public function __construct($properties = null)
	{
		parent::__construct($properties);

		if (func_num_args() == 2)
		{
			$this->setAdapter(func_get_arg(1));
		}
	}

	/**
	 * Creates a new object using a path as our starting point
	 *
	 * Don't forget, paths are relative to the adapter root
	 *
	 * @param   string  $path     The path to use to build a new entity from
	 * @param   object  $adapter  The filesystem adapter to use for interaction with the entity
	 * @return  static
	 **/
	public static function fromPath($path, $adapter = null)
	{
		// If the entity exists, grab its metadata
		if (isset($adapter) && $adapter->has($path))
		{
			$metadata = $adapter->getMetadata($path);
		}
		else
		{
			// Otherwise, we'll make our best guess at the appropriate data
			$path = trim($path, '/');
			$bits = explode('/', $path);
			$end  = end($bits);

			// The minimum required data is a path and a type
			$metadata = [
				'type' => (strpos($end, '.') !== false) ? 'file' : 'dir',
				'path' => $path
			];
		}

		return self::getSpecialized($metadata, $adapter);
	}

	/**
	 * Creates a new object from metadata
	 *
	 * This is very similar to just creating a new object, except you could
	 * instantiate an entity and not even worry about whether its a dir
	 * or file at the time of creation.
	 *
	 * @param   array   $properties  The properties to use to build a new entity from
	 * @param   object  $adapter     The filesystem adapter to use for interaction with the entity
	 * @return  static
	 **/
	public static function fromMetadata($properties, $adapter = null)
	{
		return self::getSpecialized($properties, $adapter);
	}

	/**
	 * Gets the most specialized object for a given entity type
	 *
	 * @param   array   $properties  The properties to use to build a new entity from
	 * @param   object  $adapter     The filesystem adapter to use for interaction with the entity
	 * @return  static
	 **/
	private static function getSpecialized($properties, $adapter)
	{
		// If it's a directory, stop there
		if ($properties['type'] == 'dir')
		{
			return new Directory($properties, $adapter);
		}

		if (!isset($properties['extension']))
		{
			$bits = explode('.', $properties['path']);
			$properties['extension'] = end($bits);
		}

		// If it's a file, do we have a more specialized class?
		$class = __NAMESPACE__ . '\\Type\\' . ucfirst($properties['extension']);
		if (class_exists($class))
		{
			return new $class($properties, $adapter);
		}

		return new File($properties, $adapter);
	}

	/**
	 * Calls undefined functions
	 *
	 * This is a compatibility helper. Basically, we're trying to map
	 * unnamed functions to their get* equivalent.
	 *
	 * @param   string  $name       The function name being called
	 * @param   array   $arguments  The arguments to be passed to the function
	 * @return  mixed
	 **/
	public function __call($name, $arguments)
	{
		static $methods = [];

		if (empty($methods))
		{
			$reflection = with(new \ReflectionClass($this))->getMethods(\ReflectionMethod::IS_PUBLIC);

			foreach ($reflection as $method)
			{
				$methods[] = $method->name;
			}
		}

		$alt = 'get' . ucfirst($name);

		if (in_array($alt, $methods))
		{
			return call_user_func_array([$this, $alt], $arguments);
		}

		throw new BadMethodCallException("'{$name}' method does not exist.", 500);
	}

	/**
	 * Sets the adapter on the entity
	 *
	 * @return  $this
	 **/
	public function setAdapter($adapter)
	{
		$this->adapter = $adapter;

		return $this;
	}

	/**
	 * Gets the filesystem adapter
	 *
	 * @return  object
	 **/
	public function getAdapter()
	{
		return $this->adapter;
	}

	/**
	 * Checks to see if entity is directory
	 *
	 * @return  bool
	 **/
	public function isDir()
	{
		$directory = __NAMESPACE__ . '\\Directory';
		return ($this instanceof $directory);
	}

	/**
	 * Checks to see if entity is a directory
	 *
	 * @return  bool
	 **/
	public function isDirectory()
	{
		return $this->isDir();
	}

	/**
	 * Checks to see if entity is a file
	 *
	 * @return  bool
	 **/
	public function isFile()
	{
		$file = __NAMESPACE__ . '\\File';
		return ($this instanceof $file);
	}

	/**
	 * Checks to see if entity is already on local filesystem
	 *
	 * @return  bool
	 **/
	public function isLocal()
	{
		$local = 'League\\Flysystem\\Adapter\\Local';
		return ($this->getAdapter()->getAdapter() instanceof $local);
	}

	/**
	 * Grabs the item name
	 *
	 * @return  string
	 **/
	public function getName()
	{
		if (isset($this->basename)) return $this->basename;
		if (isset($this->filename)) return $this->filename;

		if (isset($this->path))
		{
			$bits = explode('/', $this->path);
			return end($bits);
		}

		return '';
	}

	/**
	 * Grabs the parent element, if applicable
	 *
	 * @param   bool  $raw  Whether or not to return raw string or applicable object
	 * @return  string|object
	 **/
	public function getParent($raw = true)
	{
		$return = '';

		if (isset($this->dirname))
		{
			$return = $this->dirname;
		}
		else if ($path = $this->getPath())
		{
			$bits = explode('/', $path);
			array_pop($bits);

			if (count($bits) > 0)
			{
				$return = implode('/', $bits);
			}
		}

		return $raw ? $return : self::fromPath($return, $this->getAdapter());
	}

	/**
	 * Grabs the timestamp
	 *
	 * @return  string
	 **/
	public function getTimestamp()
	{
		if (!isset($this->timestamp))
		{
			$this->timestamp = $this->hasAdapterOrFail()->adapter->getTimestamp($this->getPath());
		}

		return $this->timestamp;
	}

	/**
	 * Grabs the item ownership
	 *
	 * @return  int
	 **/
	public function getOwner()
	{
		return (isset($this->owner)) ? $this->owner : 0;
	}

	/**
	 * Grabs the full path to the entity
	 *
	 * @return  string
	 **/
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Grabs the absolute path to the entity (not relative to instance root)
	 *
	 * @return  string
	 **/
	public function getAbsolutePath()
	{
		// Don't let this confuse you...we're getting the actual filesystem adapter from
		// our adapter variable, which is really the filesystem class itself...words can be confusing.
		return $this->hasAdapterOrFail()->adapter->getAdapter()->applyPathPrefix($this->getPath());
	}

	/**
	 * Checks for a proper filesystem adapter being set
	 *
	 * @return  $this
	 **/
	public function hasAdapterOrFail()
	{
		if (!isset($this->adapter)) throw new \Exception('No adapter set', 500);

		return $this;
	}

	/**
	 * Checks to see if entity exists on filesystem
	 *
	 * @return  bool
	 **/
	public function exists()
	{
		return $this->hasAdapterOrFail()->adapter->has($this->getPath());
	}

	/**
	 * Checks to see if entity exists on filesystem
	 *
	 * @return  bool
	 **/
	public function has()
	{
		return $this->exists();
	}

	/**
	 * Moves the entity
	 *
	 * @param   string  $to  Where to move the entity
	 * @return  bool
	 **/
	public function move($to)
	{
		return $this->hasAdapterOrFail()->adapter->rename($this->getPath(), $to . '/' . $this->getName());
	}

	/**
	 * Renames the entity
	 *
	 * @param   string  $to  What to rename the entity to
	 * @return  bool
	 **/
	public function rename($to)
	{
		return $this->hasAdapterOrFail()->adapter->rename($this->getPath(), $to);
	}

	/**
	 * Copies the entity
	 *
	 * @param   string|object  $to  What/where to copy the entity to
	 * @return  bool
	 **/
	public function copy($to)
	{
		if (is_string($to))
		{
			return $this->hasAdapterOrFail()->adapter->copy($this->getPath(), $to);
		}
		else
		{
			return Manager::copy($this, $to);
		}
	}
}