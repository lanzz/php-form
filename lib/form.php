<?php
/**
 * Library to deal with processing and building HTML forms.
 *
 * @copyright Copyright (c) 2012, Idea 112 Ltd., All rights reserved.
 * @author Mihail Milushev <lanzz@idea112.com>
 * @package form
 */

/**
 * Pull in subclass definitions.
 */
require_once(__DIR__.'/form/exceptions.php');
require_once(__DIR__.'/form/container.php');
require_once(__DIR__.'/form/element.php');

/**
 * Class representing the entire form.
 *
 * @package form
 */
class Form extends Form_Container {

	/**
	 * Flag indicating if form data has been received.
	 *
	 * @var bool
	 */
	protected $submitted = false;

	/**
	 * Construct a new Form.
	 *
	 * @param array $submission		The submitted values
	 * @param string $name			Form name (prefix for element names)
	 */
	protected function __construct(array $submission, $name) {
		$this->form = $this;
		$this->name = strlen($name)? $name: '';
		$this->set_value($submission);
		$this->submitted = (bool)count($submission);
	}

	/**
	 * Resolve data context within an array of submitted data.
	 *
	 * @param array $vars			Array of variables
	 * @param string $context		PHP-style lookup key (e.g. "foo[bar][baz]"")
	 * @return mixed				The resolved context
	 */
	static protected function resolve_context(array $vars, $context) {
		if (!strlen($context)) {
			return $vars;
		}
		parse_str($context.'=1', $path);
		$keys = array();
		while ($path !== '1') {
			$key = key($path);
			$keys[] = $key;
			$path = $path[$key];
		}
		foreach ($keys as $key) {
			if (array_key_exists($key, $vars)) {
				$vars = $vars[$key];
			} else {
				$vars = array();
				break;
			}
		}
		return $vars;
	}

	/**
	 * Instantiate a Form from custom submitted data.
	 *
	 * @param array $submission		The submitted values
	 * @param string|null $name		Form name (prefix for element names)
	 * @return self					A new Form instance
	 */
	static public function from_array(array $submission, $name = null) {
		return new Form($submission, $name);
	}

	/**
	 * Instantiate a Form from GET data.
	 *
	 * The $context parameter has two purposes:
	 * 1. Determines the sub-element of the $_GET array to use as submitted values;
	 * 2. Sets the Form name, which is used as prefix for the element names.
	 *
	 * @param string|null $context	PHP-style lookup key (e.g. "foo[bar][baz]")
	 * @return self					A new Form instance
	 */
	static public function from_get($context = null) {
		$submission = Form::resolve_context($_GET, $context);
		return Form::from_array($submission, $context);
	}

	/**
	 * Instantiate a Form from POST data.
	 *
	 * The $context parameter has two purposes:
	 * 1. Determines the sub-element of the $_POST array to use as submitted values;
	 * 2. Sets the Form name, which is used as prefix for the element names.
	 *
	 * @param string|null $context	PHP-style lookup key (e.g. "foo[bar][baz]")
	 * @return self					A new Form instance
	 */
	static public function from_post($context = null) {
		$submission = Form::resolve_context($_POST, $context);
		return Form::from_array($submission, $context);
	}

	/**
	 * Instantiate a Form from both GET and POST data.
	 *
	 * The $context parameter has two purposes:
	 * 1. Determines the sub-elements of the $_GET and $_POST arrays to use as submitted values;
	 * 2. Sets the Form name, which is used as prefix for the element names.
	 *
	 * POST values take precedence over GET values.
	 *
	 * @param string|null $context	PHP-style lookup key (e.g. "foo[bar][baz]")
	 * @return self					A new Form instance
	 */
	static public function from_request($context = null) {
		$submission = Form::merge($_GET, $_POST);
		$submission = Form::resolve_context($submission, $context);
		return Form::from_array($submission, $context);
	}

	/**
	 * Test if the form has been submitted.
	 *
	 * @return bool					True if form has been submitted, false otherwise
	 */
	public function is_submitted() {
		return $this->submitted;
	}

	/**
	 * Set default values for form elements that has not been submitted.
	 *
	 * The defaults are used for elements that weren't submitted with the form
	 * (e.g. checkboxes that weren't checked) and for default values before the form
	 * is initially submitted.
	 *
	 * @param array $defaults		The default values
	 * @return self					The form instance, for call chaining
	 */
	public function set_defaults(array $defaults) {
		$this->set_default($defaults);
		return $this;
	}

	/**
	 * Get the form elements' values.
	 *
	 * @return array				The elements' values
	 */
	public function get_values() {
		$values = array();
		foreach ($this->children as $name => $element) {
			$values[$name] = $element->get_value();
		}
		return $values;
	}

	/**
	 * Return `<input type="hidden">` HTML tags for all form elements.
	 *
	 * @return string				The generated HTML
	 */
	public function hidden() {
		$fields = array();
		foreach ($this->keys as $name) {
			$fields[] = $this->__get($name)->hidden();
		}
		return join("\n", $fields);
	}

	/**
	 * Return a query string containing all form elements' values.
	 *
	 * @return string				The generated query string
	 */
	public function query() {
		$values = array();
		foreach ($this->children as $name => $element) {
			$values[$name] = $element->get_value();
		}
		if (strlen($this->name)) {
			$values = array($this->name => $values);
		}
		return http_build_query($values);
	}

}