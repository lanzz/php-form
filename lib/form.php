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
 * Form class
 * @package form
 */
class Form extends Form_Container {

	/**
	 * Flag indicating if form data has been received
	 * @var bool
	 */
	protected $submitted = false;

	/**
	 * Store the merged submitted + default values
	 * @var array
	 */
	protected $merged = array();

	/**
	 * Construct a new Form
	 * @param array $submission		Submitted values
	 * @param string $name			Root name of the form data
	 * @param bool|null $submitted	Is the form to be considered "submitted"
	 *
	 * If $submitted is null, the form will be considered submitted only if $submission has
	 * any values.
	 */
	protected function __construct(array $submission, $name, $submitted = null) {
		$this->form = $this;
		$this->name = strlen($name)? $name: '';
		$this->set_value($submission);
		$this->merged = $this->value;
		$this->submitted = is_null($submitted)? (bool)count($this->value): $submitted;
	}

	/**
	 * Resolve data context within an array of submitted data
	 * @param array $vars
	 * @param string $context
	 *
	 * $form->resolve_context(array('foo' => array('bar' => 'baz')), 'foo[bar]')
	 * 	=> 'baz'
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
	 * @param bool|null $submitted	Is the form to be considered "submitted"
	 *
	 * Since a form instantiated from an array is not necessarily in "submitted" state,
	 * you can override the default behavior of checking if any value has been assigned
	 * by passing the $submitted override parameter.
	 */
	static public function from_array(array $submission, $name = null, $submitted = null) {
		return new Form($submission, $name, $submitted);
	}

	/**
	 * Instantiate a Form from GET data
	 * @param string|null $context
	 */
	static public function from_get($context = null) {
		$submission = self::resolve_context($_GET, $context);
		return self::from_array($submission, $context);
	}

	/**
	 * Instantiate a Form from POST data
	 * @param string|null $context
	 */
	static public function from_post($context = null) {
		$submission = self::resolve_context($_POST, $context);
		return self::from_array($submission, $context);
	}

	/**
	 * Instantiate a Form from both GET and POST data
	 * @param string|null $context
	 *
	 * POST data has precedence over GET data
	 */
	static public function from_request($context = null) {
		$submission = self::merge($_GET, $_POST);
		$submission = self::resolve_context($submission, $context);
		return self::from_array($submission, $context);
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
		$this->set_default($defaults);
		$this->merged = Form_Container::merge($this->default, $this->value);
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