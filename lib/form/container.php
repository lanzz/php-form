<?php
/**
 * Library to deal with processing and building HTML forms.
 * @copyright Copyright (c) 2012, Idea 112 Ltd., All rights reserved.
 * @author Mihail Milushev <lanzz@idea112.com>
 * @package form
 */

/**
 * Abstract base class representing a container for form elements and error messages.
 * @package form
 *
 * A container might be an entire form (the Form class is an instance of Form_Container),
 * or it could be a form element containing child elements.
 */
abstract class Form_Container implements ArrayAccess, Countable, Iterator {

	/**
	 * Form instance that the container belongs to.
	 * @var Form
	 */
	protected $form;

	/**
	 * Base element name of the container.
	 * @var string
	 */
	protected $name = '';

	/**
	 * Default value for the container.
	 * @var mixed
	 */
	protected $default = null;

	/**
	 * Submitted value for the container.
	 * @var mixed
	 */
	protected $value = null;

	/**
	 * Storage for the instantiated child elements.
	 * @var Form_Element[]
	 */
	protected $children = array();

	/**
	 * Keys of all child values in container.
	 * @var string[]
	 *
	 * Contains the unique keys of all submitted and default values, as well as
	 * manually created child elements.
	 */
	protected $keys = array();

	/**
	 * Cursor for the `Iterator` interface methods.
	 * @var int
	 * @see http://php.net/manual/en/class.iterator.php
	 */
	protected $current = 0;

	/**
	 * Storage for error messages.
	 * @var string[][]
	 */
	protected $errors = array();

	/**
	 * Merge two arrays recursively.
	 * @param array $base			Base values
	 * @param array $override		Values overriding the base values
	 * @return array				The merged array
	 */
	static protected function merge(array $base, array $override) {
		// remove scalar and indexed elements from the base values
		foreach ($base as $key => $value) {
			if (is_int($key) && !array_key_exists($key, $override)) {
				unset($base[$key]);
			}
		}
		// merge overrides onto base
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
	 * Keep the $keys property updated.
	 * @return self					The container instance, for call chaining
	 *
	 * Called when the submitted or the default value is changes, or when the list of
	 * instantiated children changes.
	 */
	protected function update_keys() {
		$this->keys = array_keys(array_merge(
			is_array($this->value)? $this->value: array(),
			is_array($this->default)? $this->default: array(),
			$this->children
		));
		return $this;
	}

	/**
	 * Set the submitted value for the container.
	 * @param mixed $value			The submitted value
	 * @return self					The container instance, for call chaining
	 */
	protected function set_value($value) {
		$this->value = $value;
		$this->update_keys();
		return $this;
	}

	/**
	 * Set the default value for the container.
	 * @param mixed $default		The default value
	 * @return self					The container instance, for call chaining
	 */
	protected function set_default($default) {
		$this->default = $default;
		$this->update_keys();
		return $this;
	}

	/**
	 * Implement `Countable::count`.
	 * @return int					Number of child values (both submitted and default)
	 * @see http://php.net/manual/en/class.countable.php
	 */
	public function count() {
		return count($this->keys);
	}

	/**
	 * Implement `Iterator::current`.
	 * @return Form_Element			The current element
	 * @see http://php.net/manual/en/class.iterator.php
	 * @see Form_Container::__get()
	 */
	public function current() {
		$key = $this->key();
		return $this->__get($key);
	}

	/**
	 * Implement `Iterator::key`.
	 * @return string				The current key
	 * @see http://php.net/manual/en/class.iterator.php
	 */
	public function key() {
		return $this->keys[$this->current];
	}

	/**
	 * Implement `Iterator::next`.
	 * @return void
	 * @see http://php.net/manual/en/class.iterator.php
	 */
	public function next() {
		$this->current++;
	}

	/**
	 * Implement `Iterator::rewind`.
	 * @return void
	 * @see http://php.net/manual/en/class.iterator.php
	 */
	public function rewind() {
		$this->current = 0;
	}

	/**
	 * Implement `Iterator::valid`.
	 * @return bool					True if the current element is valid
	 * @see http://php.net/manual/en/class.iterator.php
	 */
	public function valid() {
		return $this->current < count($this->keys);
	}

	/**
	 * Implement `ArrayAccess::offsetGet`.
	 * @param mixed $name			Name of the child element
	 * @return Form_Element			Child element instance
	 * @see http://php.net/manual/en/class.arrayaccess.php
	 * @see Form_Container::__get
	 */
	public function offsetGet($name) {
		return $this->__get($name);
	}

	/**
	 * Implement `ArrayAccess::offsetExists`.
	 * @param mixed $name			Name of the child element
	 * @return bool					True if value exists for the child element
	 * @see http://php.net/manual/en/class.arrayaccess.php
	 * @see Form_Container::__isset
	 */
	public function offsetExists($name) {
		return $this->__isset($name);
	}

	/**
	 * Implement `ArrayAccess::offsetSet`.
	 * @param mixed $name			Name of the child element
	 * @param mixed $value			New value for the child element
	 * @return void
	 * @see http://php.net/manual/en/class.arrayaccess.php
	 * @see Form_Container::__set
	 */
	public function offsetSet($name, $value) {
		$this->__set($name, $value);
	}

	/**
	 * Implement `ArrayAccess::offsetUnset`.
	 * @param mixed $name			Name of the child element
	 * @return void
	 * @see http://php.net/manual/en/class.arrayaccess.php
	 * @see Form_Container::__unset
	 */
	public function offsetUnset($name) {
		$this->__unset($name);
	}

	/**
	 * Child instance factory, useful to override in children classes.
	 * @param string $name			Name of the child element
	 * @return Form_Element			New `Form_Element` instance
	 */
	protected function create_child($name) {
		if (isset($this->children[$name])) {
			return $this->children[$name];
		}
		$element_name = strlen($this->name)? $this->name.'['.$name.']': $name;
		$value = isset($this->value[$name])? $this->value[$name]: null;
		$default_value = isset($this->default[$name])? $this->default[$name]: null;
		$this->children[$name] = new Form_Element(
			$this->form,
			$element_name,
			$value,
			$default_value
		);
		$this->update_keys();
		return $this->children[$name];
	}

	/**
	 * Return the names of all child elements.
	 * @return string[]				Keys of all child values
	 */
	public function children() {
		return $this->keys;
	}

	/**
	 * Return a child element instance.
	 * @param string $name			Name of the child element
	 * @return Form_Element			New Form_Element instance
	 */
	public function __get($name) {
		return $this->create_child($name);
	}

	/**
	 * Test if a child value exists.
	 * @param string $name			Name of the child element
	 * @return bool
	 */
	public function __isset($name) {
		return !is_null($this->__get($name)->get_value());
	}

	/**
	 * Set a new value for a child element.
	 * @param string $name			Name of the child element
	 * @param mixed $value			New value for the element
	 * @return void
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
	 * Delete a child value.
	 * @param string $name			Name of the child element
	 * @return void
	 */
	public function __unset($name) {
		unset($this->value[$name]);
		if (isset($this->children[$name])) {
			unset($this->children[$name]);
		}
	}

	/**
	 * Set an error for the container, clearing previous errors.
	 * @param string|null $error	Error message
	 * @param string|null $code		Error code
	 * @return self					The Form_Container instance, for call chaining
	 *
	 * All previous errors with the same `$code` will be cleared.
	 * The default error code is `:default` — if `$code` is omitted,
	 * only the messages for error code `:default` will be cleared
	 * and the new error will be added with a `:default` error code.
	 *
	 * If the `$error` parameter is `null`, only an error condition
	 * is raised for the specified error code, but no message is added.
	 */
	public function set_error($error = null, $code = null) {
		$this->clear_errors($code);
		$this->add_error($error, $code);
		return $this;
	}

	/**
	 * Add an error for the container.
	 * @param string|null $error	Error message
	 * @param string|null $code		Error code
	 * @return self					The Form_Container instance, for call chaining
	 *
	 * The default error code is `:default` — if $code is omitted,
	 * the message will be added with a `:default` error code.
	 *
	 * If the `$error` parameter is `null`, only an error condition
	 * is raised for the specified error code, but no message is added.
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
	 * Check if an error condition is raised for the container.
	 * @param string|null $code		Error code
	 * @return bool					True if there are errors raised
	 *
	 * If $code is omitted, `has_errors` will return `true` if _any_
	 * error code has been raised for the container. If you want to check
	 * specifically for an error condition on the `:default` error code,
	 * you need to call `has_errors(':default')` explicitly.
	 */
	public function has_errors($code = null) {
		if (is_null($code)) {
			return (bool)count($this->errors);
		} else {
			return isset($this->errors[$code]);
		}
	}

	/**
	 * Check if an error condition is raised for the container or any of its children.
	 * @param string|null $code		Error code
	 * @return bool					True if there are errors raised
	 *
	 * If $code is omitted, `contains_errors` will return `true` if _any_
	 * error code has been raised for the container. If you want to check
	 * specifically for an error condition on the `:default` error code,
	 * you need to call `contains_errors(':default')` explicitly.
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
	 * Get error messages for the container.
	 * @param string|null $code		Error code
	 * @return string[]				A list of error messages
	 *
	 * If $code is omitted, `get_errors` will return _all_ error messages
	 * for _all_ error codes raised for the container. If you want to get
	 * error messages specifically for the `:default` error code, you need
	 * to call `get_errors(':default')` explicitly.
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
	 * Get error codes raised for the element.
	 * @return string[]				A list of error codes
	 */
	public function get_error_codes() {
		return array_keys($this->errors);
	}

	/**
	 * Return a string if an error condition is raised for the container.
	 * @param string $string		String to return if condition is raised
	 * @param string $error_code	Error code
	 * @return string				$string or an empty string
	 *
	 * Intended usage: `<input <?php echo $form->field->if_errors('class="error"') ?> ...>`
	 */
	public function if_errors($string, $code = null) {
		return $this->has_errors($code)? $string: '';
	}

	/**
	 * Clear errors for the container.
	 * @param string|null $code		Error code to clear, or all errors if null
	 * @return self					The Form_Container instance, for call chaining
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
	 * Clear errors for the container and all its child elements.
	 * @param string|null $code		Error code to clear, or all errors if null
	 * @return self					The Form_Container instance, for call chaining
	 */
	public function clear_all_errors($code = null) {
		$this->clear_errors($code);
		foreach ($this->children as $element) {
			$element->clear_all_errors($code);
		}
		return $this;
	}

}