<?php
/**
 * @copyright Copyright (c) 2012, Idea 112 Ltd., All rights reserved.
 * @author Mihail Milushev <lanzz@idea112.com>
 * @package form
 *
 * Form_Element class definition
 */

/**
 * Form element class
 * @package libs
 */
class Form_Element extends Form_Container {

	/**
	 * Parent form
	 * @var Form
	 */
	protected $form;

	/**
	 * Construct a form element
	 * @param Form $form
	 * @param string $name
	 */
	public function __construct(Form $form, $name, $value, $default_value) {
		$this->form = $form;
		$this->name = $name;
		$this->set_value($value);
		$this->set_default($default_value);

		// pre-instantiate all children
		foreach ($this->keys as $name) {
			$this->create_child($name);
		}
	}

	/**
	 * Get the element's name
	 * @return mixed
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the element's default value
	 * @return mixed
	 */
	public function get_default() {
		return $this->default;
	}

	/**
	 * Get the element's submitted value
	 * @return mixed
	 */
	public function get_submitted() {
		return $this->value;
	}

	/**
	 * Get the element's calculated value (submitted value or the default value if not submitted)
	 * @return mixed
	 */
	public function get_value() {
		return is_null($this->value)? $this->default: $this->value;
	}

	/**
	 * Get the type of the element's calculated value
	 * @return string
	 */
	public function get_type() {
		return gettype($this->get_value());
	}

	/**
	 * Return the element's name mangled into an identifier, with optional prefix and suffix
	 * @param string|null $prefix
	 * @param string|null $suffix
	 * @return string
	 */
	public function get_id($prefix = null, $suffix = null) {
		$id = $prefix.$this->name.$suffix;
		$id = preg_replace('/[^a-zA-Z0-9]+/', '-', $id);
		return trim($id, '-');
	}

	/**
	 * Get string value of the element
	 * @return string
	 * @throws Form_Exception
	 */
	public function as_string() {
		$value = $this->get_value();
		if (is_null($value) || is_scalar($value)) {
			return (string)$value;
		}
		throw new Form_Exception('Non-scalar form element value encountered');
	}

	/**
	 * Get HTML-encoded value
	 * @return string
	 */
	public function as_html() {
		return htmlspecialchars($this->as_string());
	}

	/**
	 * Get URL-encoded value
	 * @return string
	 */
	public function as_url() {
		return rawurlencode($this->as_string());
	}

	/**
	 * Return an id="..." attribute, with optional prefix and suffix
	 * @param string|null $prefix
	 * @param string|null $suffix
	 * @return string
	 */
	public function id($prefix = null, $suffix = null) {
		return ' id="'.htmlspecialchars($this->get_id($prefix, $suffix)).'" ';
	}

	/**
	 * Return a name="..." attribute
	 * @return string
	 */
	public function name() {
		return ' name="'.htmlspecialchars($this->name).(is_array($this->get_value())? '[]': '').'" ';
	}

	/**
	 * Return both id="..." and name="..." attributes, with optional prefix and suffix for the id
	 * @param string|null $prefix
	 * @param string|null $suffix
	 * @return string
	 */
	public function id_name($prefix = null, $suffix = null) {
		return $this->id($prefix, $suffix).$this->name();
	}

	/**
	 * Return a value="..." attribute
	 * @return string
	 */
	public function value() {
		return ' value="'.$this->as_html().'" ';
	}

	/**
	 * Return a checked attribute if value matches a target
	 * @param string $value
	 * @return string
	 */
	public function checked($value) {
		$current_value = $this->get_value();
		$checked = is_array($current_value)? in_array($value, $current_value): ($value == $current_value);
		return $checked? ' checked ': '';
	}

	/**
	 * Return a selected attribute if value matches a target
	 * @param string $value
	 * @return string
	 */
	public function selected($value) {
		$current_value = $this->get_value();
		$checked = is_array($current_value)? in_array($value, $current_value): ($value == $current_value);
		return $checked? ' selected ': '';
	}

	/**
	 * Return a hidden input representation of the element or multiple inputs for non-scalar values
	 * @return string
	 */
	public function hidden() {
		$fields = array();
		$value = $this->get_value();
		if (!is_null($value) && !is_array($value)) {
			$fields[] = '<input type="hidden"'.$this->name().$this->value().'>';
		}
		foreach ($this->keys as $name) {
			$fields[] = $this->__get($name)->hidden();
		}
		return join("\n", $fields);
	}

	/**
	 * Render the element as a query string
	 * @return string
	 */
	public function query() {
		return http_build_query(array($this->name => $this->get_value()));
	}

	/**
	 * Return a label tag, with optional value to append to the element id
	 * @param string $label
	 * @param string|null $value
	 * @return string
	 */
	public function label($label, $value = null) {
		return '<label for="'.htmlspecialchars($this->get_id(null, isset($value)? '-'.$value: '')).'">'.htmlspecialchars($label).'</label>';
	}

	/**
	 * Render a text input tag
	 * @param string|null $attributes
	 * @return string
	 */
	public function input($attributes = null) {
		return '<input type="text" '.$this->id_name().$this->value().' '.$attributes.'>';
	}

	/**
	 * Render a password input tag
	 * @param string|null $attributes
	 * @return string
	 */
	public function password($attributes = null) {
		return '<input type="password" '.$this->id_name().' '.$attributes.'>';
	}

	/**
	 * Render a textarea tag
	 * @param string|null $attributes
	 * @return string
	 */
	public function textarea($attributes = null) {
		return '<textarea '.$this->id_name().' '.$attributes.'>'.$this->as_html().'</textarea>';
	}

	/**
	 * Render a checkbox input tag
	 * @param string $value
	 * @param string|null $attributes
	 * @return string
	 */
	public function checkbox($value, $attributes = null) {
		return '<input type="checkbox" '.$this->id(null, '-'.$value).$this->name().$this->checked($value).' value="'.htmlspecialchars($value).'" '.$attributes.'>';
	}

	/**
	 * Render a radio button tag
	 * @param string $value
	 * @param string|null $attributes
	 * @return string
	 */
	public function radio($value, $attributes = null) {
		return '<input type="radio" '.$this->id(null, '-'.$value).$this->name().$this->checked($value).' value="'.htmlspecialchars($value).'" '.$attributes.'>';
	}

	/**
	 * Render a select box
	 * @param array $values
	 * @param string|null $attributes
	 * @return string
	 */
	public function select(array $values, $attributes = null) {
		$options = array();
		foreach ($values as $value => $label) {
			$options[] = $this->option($value, $label);
		}
		return '<select '.$this->id_name().' '.$attributes.'>'.join("\n", $options).'</select>';
	}

	/**
	 * Render a single option for select box
	 * @param string $value
	 * @param string $label
	 * @param string|null $attributes
	 * @return string
	 */
	public function option($value, $label, $attributes = null) {
		return '<option '.$this->id(null, '-'.$value).' value="'.htmlspecialchars($value).'" '.$this->selected($value).'>'.htmlspecialchars($label).'</option>';
	}

	/**
	 * Render a submit button tag
	 * @param string $label
	 * @param string|null $attributes
	 * @return string
	 */
	public function submit($label, $attributes = null) {
		return '<input type="submit" '.$this->id_name().' value="'.htmlspecialchars($label).'" '.$attributes.'>';
	}


}