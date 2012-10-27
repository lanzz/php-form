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
	 * Parent form
	 * @var Form
	 */
	protected $form;

	/**
	 * Root name of the form container
	 * @var string
	 */
	protected $name = '';

	/**
	 * Storage for the child elements
	 * @var array
	 */
	protected $children = array();

	/**
	 * Default value for this container's elements
	 * @var mixed
	 */
	protected $default = null;

	/**
	 * Value of this container's elements
	 * @var mixed
	 */
	protected $value = null;

	/**
	 * Keys of all elements in container (= unique keys of both values and default values)
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
	protected function merge(array $base, array $override) {
		// remove indexed elements from the base
		foreach ($base as $key => $value) {
			if (is_int($key) && !array_key_exists($key, $override)) {
				unset($base[$key]);
			}
		}
		foreach ($override as $key => $value) {
			if (array_key_exists($key, $base) && is_array($value) && is_array($base[$key])) {
				$base[$key] = $this->merge($base[$key], $value);
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
	protected function create_child(Form $form, $name, $value, $default_value) {
		return new Form_Element($form, $name, $value, $default_value);
	}

	/**
	 * Wrap child values in FormElement instances
	 * @param string $key
	 * @return FormElement
	 */
	public function __get($name) {
		if (!isset($this->children[$name])) {
			$value = (is_array($this->value) && isset($this->value[$name]))? $this->value[$name]: null;
			$default_value = (is_array($this->default) && isset($this->default[$name]))? $this->default[$name]: null;
			$child = $this->create_child($this->form, strlen($this->name)? $this->name.'['.$name.']': $name, $value, $default_value);
			$this->children[$name] = $child;
		}
		return $this->children[$name];
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
	 * Ignore value setting
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value) {}

	/**
	 * Ignore value setting
	 * @param mixed $index
	 * @param mixed $value
	 */
	public function offsetSet($index, $value) {}

	/**
	 * Ignore value unsetting
	 * @param string $name
	 */
	public function __unset($name) {}

	/**
	 * Ignore value unsetting
	 * @param mixed $index
	 */
	public function offsetUnset($index) {}

	/**
	 * Clear errors for the container or just a single code
	 * @param string|null $code
	 * @return FormContainer $this
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
	 * Set error for the container
	 * @param string $error
	 * @param string|null $code
	 * @return FormContainer $this
	 */
	public function set_error($error, $code = null) {
		$this->clear_errors();
		$this->add_error($error, $code);
		return $this;
	}

	/**
	 * Add an error for the container
	 * @param string $error
	 * @param string|null $code
	 * @return FormContainer $this
	 */
	public function add_error($error, $code = null) {
		if (is_null($code)) {
			$code = ':default';
		}
		$this->errors[$code][] = $error;
		return $this;
	}

	/**
	 * Check if error condition exists
	 * @param string|null $code
	 * @return bool
	 */
	public function has_error($code = null) {
		if (is_null($code)) {
			return (bool)count($this->errors);
		} else {
			return isset($this->errors[$code]);
		}
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
	 * Return a string based on error condition
	 * @param string $return
	 * @param string $error_code
	 * @return string
	 *
	 * Useful as <input <?php echo $form->field->if_error('class="error"') ?> ...>
	 */
	public function if_error($return, $code = null) {
		return $this->has_error($code)? $return: '';
	}

}
