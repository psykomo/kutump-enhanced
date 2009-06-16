<?php
/**
 * @copyright Copyright 2007 Conduit Internet Technologies, Inc. (http://conduit-it.com)
 * @license Apache Licence, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package Apache
 * @subpackage Solr
 * @author Donovan Jimenez <djimenez@conduit-it.com>
 */

/**
 * Holds Key / Value pairs that represent a Solr Document. Field values can be accessed
 * by direct dereferencing such as:
 * <code>
 * ...
 * $document->title = 'Something';
 * echo $document->title;
 * ...
 * </code>
 *
 * Additionally, the field values can be iterated with foreach
 *
 * <code>
 * foreach ($document as $key => $value)
 * {
 * ...
 * }
 * </code>
 */
class Apache_Solr_Document implements IteratorAggregate 
{
	protected $_fields = array();

	/**
	 * Magic get for field values
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->_fields[$key];
	}

	/**
	 * Magic set for field values. Multi-valued fields should be set as arrays
	 * or instead use the setMultiValue(...) function which will automatically
	 * make sure the field is an array.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value)
	{
		$this->_fields[$key] = $value;
	}

	/**
	 * Magic isset for fields values.  Do no call directly. Allows usage:
	 *
	 * <code>
	 * isset($document->some_field);
	 * </code>
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function __isset($key)
	{
		return isset($this->_fields[$key]);
	}

	/**
	 * Magic unset for field values. Do no call directly. Allows usage:
	 *
	 * <code>
	 * unset($document->some_field);
	 * </code>
	 *
	 * @param string $key
	 */
	public function __unset($key)
	{
		unset($this->_fields[$key]);
	}

	/**
	 * Handle the array manipulation for a multi-valued field
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function setMultiValue($key, $value)
	{
		if (!isset($this->_fields[$key]))
		{
			$this->_fields[$key] = array();
		}

		if (!is_array($this->_fields[$key]))
		{
			$this->_fields[$key] = array($this->_fields[$key]);
		}

		$this->_fields[$key][] = $value;
	}

	/**
	 * Get the names of all fields in this document
	 *
	 * @return array
	 */
	public function getFieldNames()
	{
		return array_keys($this->_fields);
	}

	/**
	 * IteratorAggregate implementation function. Allows usage:
	 *
	 * <code>
	 * foreach ($document as $key => $value)
	 * {
	 * 	...
	 * }
	 * </code>
	 */
	public function getIterator()
	{
		$arrayObject = new ArrayObject($this->_fields);
		return $arrayObject->getIterator();
	}
}