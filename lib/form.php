<?php
/**
 * @copyright Copyright (c) 2012, Idea 112 Ltd., All rights reserved.
 * @author Mihail Milushev <lanzz@idea112.com>
 * @package form
 *
 * Library to deal with processing and building HTML forms
 */

/**
 * Pull in subclass definitions
 */
require_once(__DIR__.'/form/exception.php');
require_once(__DIR__.'/form/container.php');
require_once(__DIR__.'/form/element.php');

/**
 * Form helper class
 * @package form
 */
class Form extends Form_Container {

	/**
	 * Flag indicating if form data has been received
	 * @var bool
	 */
	protected $submitted = false;

	/**
	 * Construct a new Form
	 * @param array $submission		Submitted values
	 * @param string|null $name		Root name of the form data
	 */
	protected function __construct(array $submission, $name = null) {
		$this->form = $this;
		$this->value = $submission;
		$this->name = strlen($name)? $name: '';
		$this->keys = array_keys($this->value);
		$this->submitted = (bool)count($this->keys);
	}

	/**
	 * Merge two arrays recursively
	 * @param array $base
	 * @param array $override
	 * @return array
	 */
	static protected function merge(array $base, array $override) {
		// remove indexed elements from the base
		foreach ($base as $key => $value) {
			if (is_int($key) && !array_key_exists($key, $override)) {
				unset($base[$key]);
			}
		}
		foreach ($override as $key => $value) {
			if (array_key_exists($key, $base) && is_array($value) && is_array($base[$key])) {
				$base[$key] = Form::merge($base[$key], $value);
			} else {
				$base[$key] = $value;
			}
		}
		return $base;
	}

	/**
	 * Resolve data context within an array of submitted data
	 * @param array $vars
	 * @param string $context
	 */
	static protected function resolve_context(array $submission, $context) {
		if (!strlen($context)) {
			return $submission;
		}
		parse_str($context.'=1', $path);
		$keys = array();
		while ($path !== '1') {
			$key = key($path);
			$keys[] = $key;
			$path = $path[$key];
		}
		foreach ($keys as $key) {
			if (array_key_exists($key, $submission)) {
				$submission = $submission[$key];
			} else {
				$submission = array();
				break;
			}
		}
		return $submission;
	}

	/**
	 * Instantiate a Form from custom submitted data
	 * @param array $vars
	 * @param string|null $root
	 */
	static public function from_array(array $submission, $name = null) {
		return new Form($submission, $name);
	}

	/**
	 * Instantiate a Form from GET data
	 * @param string|null $context
	 */
	static public function from_get($context = null) {
		$submission = Form::resolve_context($_GET, $context);
		return Form::from_array($submission, $context);
	}

	/**
	 * Instantiate a Form from POST data
	 * @param string|null $context
	 */
	static public function from_post($context = null) {
		$submission = Form::resolve_context($_POST, $context);
		return Form::from_array($submission, $context);
	}

	/**
	 * Instantiate a Form from both GET and POST data
	 * @param string|null $context
	 */
	static public function from_request($context = null) {
		$submission = Form::merge($_GET, $_POST);
		$submission = Form::resolve_context($submission, $context);
		return Form::from_array($submission, $context);
	}

	/**
	 * Return true if form has been submitted
	 * @return bool
	 */
	public function is_submitted() {
		return $this->submitted;
	}

	/**
	 * Set default values for the form
	 * @param array $defaults
	 * @return Form $this
	 */
	public function set_defaults(array $defaults) {
		$this->default = $defaults;
		$this->keys = array_keys(array_merge($this->value, $this->default));
		$this->merged = Form::merge($this->default, $this->value);
		return $this;
	}

	/**
	 * Get all form values as an array
	 * @return array
	 */
	public function get_values() {
		return $this->merged;
	}

	/**
	 * Render the entire form as hidden fields
	 * @return string
	 */
	public function hidden() {
		$fields = array();
		foreach ($this->keys as $name) {
			$fields[] = $this->__get($name)->hidden();
		}
		return join("\n", $fields);
	}

	/**
	 * Render the entire form as a query string
	 * @return string
	 */
	public function query() {
		return http_build_query(strlen($this->name)? array($this->name => $this->merged): $this->merged);
	}

}