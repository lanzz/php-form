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
	 * Resolve data context within an array of submitted data
	 * @param array $vars
	 * @param string $context
	 */
	static protected function resolve_context(array $submission, $context) {
		$keys = strlen($context)? explode('.', $context): array();
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
	 * Form an element name from a context path
	 * @param string $context
	 */
	static protected function context_name($context) {
		$keys = strlen($context)? explode('.', $context): array();
		$name = '';
		foreach ($keys as $key) {
			$name = strlen($name)? $name.'['.$key.']': $key;
		}
		return $name;
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
		$name = Form::context_name($context);
		return Form::from_array($submission, $name);
	}

	/**
	 * Instantiate a Form from POST data
	 * @param string|null $context
	 */
	static public function from_post($context = null) {
		$submission = Form::resolve_context($_POST, $context);
		$name = Form::context_name($context);
		return Form::from_array($submission, $name);
	}

	/**
	 * Instantiate a Form from both GET and POST data
	 * @param string|null $context
	 */
	static public function from_request($context = null) {
		$submission = array_merge_recursive($_GET, $_POST);
		$submission = Form::resolve_context($submission, $context);
		$name = Form::context_name($context);
		return Form::from_array($submission, $name);
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
		return $this;
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
		return http_build_query(strlen($this->name)? array($this->name => $this->value): $this->value);
	}

}