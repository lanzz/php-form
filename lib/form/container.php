<?php
/**
 * @copyright Copyright (c) 2012, Idea 112 Ltd., All rights reserved.
 * @author Mihail Milushev <lanzz@idea112.com>
 * @package form
 *
 * Form_Container class definition
 */

/**
 * Abstract form element container base class
 * @package libs
 */
abstract class Form_Container implements ArrayAccess, Countable, Iterator {

	/**
	 * Form instance that the container belongs to
	 * @var Form
	 */
	protected $form;

	/**
	 * Root name of the container
	 * @var string
	 */
	protected $name = '';

	/**
	 * Default value for this container
	 * @var mixed
	 */
	protected $default = null;

	/**
	 * Value of this container
	 * @var mixed
	 */
	protected $value = null;

	/**
	 * Storage for the child elements
	 * @var array
	 */
	protected $children = array();

	/**
	 * Keys of all child elements in container (= unique keys of both values and default values)
	 * @var array
	 */
	protected $keys = array();

	/**
	 * Iterable cursor
	 * @var int
	 */
	protected $current = 0;

	/**
	 * Error message container
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Set the submitted values of the container
	 * @param mixed $value
	 * @return Form_Container $this
	 */
	protected function set_value($value) {
		$this->value = $value;
		$this->keys = array_keys(array_merge(is_array($this->value)? $this->value: array(), is_array($this->default)? $this->default: array()));
		return $this;
	}

	/**
	 * Set default value for the container
	 * @param mixed $default
	 * @return Form_Container $this
	 */
	protected function set_default($default) {
		$this->default = $default;
		$this->keys = array_keys(array_merge(is_array($this->value)? $this->value: array(), is_array($this->default)? $this->default: array()));
		return $this;
	}

	/**
	 * Implement Countable
	 * @return int
	 */
	public function count() {
		return count($this->keys);
	}

	/**
	 * Implement Iterator::current
	 * @return mixed
	 */
	public function current() {
		$key = $this->key();
		return $this->__get($key);
	}

	/**
	 * Implement Iterator::key
	 * @return scalar
	 */
	public function key() {
		return $this->keys[$this->current];
	}

	/**
	 * Implement Iterator::next
	 */
	public function next() {
		$this->current++;
	}

	/**
	 * Implement Iterator::rewind
	 */
	public function rewind() {
		$this->current = 0;
	}

	/**
	 * Implement Iterator::valid
	 * @return bool
	 */
	public function valid() {
		return $this->current < count($this->keys);
	}

	/**
	 * Merge two arrays recursively
	 * @param array $base
	 * @param array $override
	 * @return array
	 */
	static protected function merge(array $base, array $override) {
		// remove scalar and indexed elements from the base
		foreach ($base as $key => $value) {
			if (is_int($key) && !array_key_exists($key, $override)) {
				unset($base[$key]);
			}
		}
		// merge overrides into base
		foreach ($override as $key => $value) {
			if (array_key_exists($key, $base) && is_array($value) && is_array($base[$key])) {
				$base[$key] = self::merge($base[$key], $value);
			} else {
				$base[$key] = $value;
			}
		}
		return $base;
	}

	/**
	 * Child instance factory, useful to override in children classes
	 * @param Form $form
	 * @param string $name
	 * @param mixed $value
	 * @param mixed $default_value
	 * @return FormElement
	 */
	protected function create_child($name) {
		if (isset($this->children[$name])) {
			return $this->children[$name];
		}
		$value = isset($this->value[$name])? $this->value[$name]: null;
		$default_value = isset($this->default[$name])? $this->default[$name]: null;
		$this->children[$name] = new Form_Element($this->form, strlen($this->name)? $this->name.'['.$name.']': $name, $value, $default_value);
		return $this->children[$name];
	}

	/**
	 * Return keys of child elements
	 * @return array
	 */
	public function keys() {
		return $this->keys;
	}

	/**
	 * Wrap child values in FormElement instances
	 * @param string $key
	 * @return FormElement
	 */
	public function __get($name) {
		return $this->create_child($name);
	}

	/**
	 * Wrap array-accessed child values in FormElement instances
	 * @param mixed $index
	 * @return FormElement
	 */
	public function offsetGet($index) {
		return $this->__get($index);
	}

	/**
	 * Check if child element has been submitted
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name) {
		return isset($this->children[$name]) && !is_null($this->children[$name]->get_submitted());
	}

	/**
	 * Check if array-accessed child element has been submitted
	 * @param mixed $index
	 * @return bool
	 */
	public function offsetExists($index) {
		return $this->__isset($index);
	}

	/**
	 * Set value for a child element by property
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value) {
		$this->value[$name] = $value;
		if (isset($this->children[$name])) {
			// update value in child element
			$this->children[$name]->set_value($value);
		} else {
			// create new child element
			$this->children[$name] = $this->create_child($name);
		}
	}

	/**
	 * Set value for a child element by index
	 * @param mixed $index
	 * @param mixed $value
	 */
	public function offsetSet($index, $value) {
		$this->__set($index, $value);
	}

	/**
	 * Unset a child element by property
	 * @param string $name
	 */
	public function __unset($name) {
		unset($this->value[$name]);
		if (isset($this->children[$name])) {
			// drop child element
			unset($this->children[$name]);
		}
	}

	/**
	 * Unset a child element by property
	 * @param mixed $index
	 */
	public function offsetUnset($index) {
		$this->__unset($index);
	}

	/**
	 * Clear errors for the container or just a single code
	 * @param string|null $code
	 * @return Form_Container $this
	 */
	public function clear_errors($code = null) {
		if (is_null($code)) {
			$this->errors = array();
		} else {
			unset($this->errors[$code]);
		}
		return $this;
	}

	/**
	 * Clear errors for the container and all its child elements
	 * @param string|null $code
	 * @return Form_Container $this
	 */
	public function clear_all_errors($code = null) {
		$this->clear_errors($code);
		foreach ($this->children as $element) {
			$element->clear_all_errors($code);
		}
		return $this;
	}

	/**
	 * Set error for the container
	 * @param string $error
	 * @param string|null $code
	 * @return Form_Container $this
	 */
	public function set_error($error = null, $code = null) {
		$this->clear_errors($code);
		$this->add_error($error, $code);
		return $this;
	}

	/**
	 * Add an error for the container
	 * @param string|null $error
	 * @param string|null $code
	 * @return Form_Container $this
	 */
	public function add_error($error = null, $code = null) {
		if (is_null($code)) {
			$code = ':default';
		}
		if (!isset($this->errors[$code])) {
			$this->errors[$code] = array();
		}
		if (!is_null($error)) {
			$this->errors[$code][] = $error;
		}
		return $this;
	}

	/**
	 * Check if error code is set for the container
	 * @param string|null $code
	 * @return bool
	 */
	public function has_errors($code = null) {
		if (is_null($code)) {
			return (bool)count($this->errors);
		} else {
			return isset($this->errors[$code]);
		}
	}

	/**
	 * Check if error code is set for the container or for any child element
	 * @param string|null $code
	 * @return bool
	 */
	public function contains_errors($code = null) {
		if ($this->has_errors($code)) {
			return true;
		}
		foreach ($this->children as $element) {
			if ($element->contains_errors($code)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get errors
	 * @param string|null $code
	 * @return array
	 */
	public function get_errors($code = null) {
		if (!is_null($code)) {
			return isset($this->errors[$code])? $this->errors[$code]: array();
		}
		$all_errors = array();
		foreach ($this->errors as $code => $errors) {
			$all_errors = array_merge($all_errors, $errors);
		}
		return $all_errors;
	}

	/**
	 * Get error codes added for the element
	 * @return array
	 */
	public function get_error_codes() {
		return array_keys($this->errors);
	}

	/**
	 * Return a string based on error condition
	 * @param string $string
	 * @param string $error_code
	 * @return string
	 *
	 * Useful as <input <?php echo $form->field->if_errors('class="error"') ?> ...>
	 */
	public function if_errors($string, $code = null) {
		return $this->has_errors($code)? $string: '';
	}

}
