<?php
/**
 * Library to deal with processing and building HTML forms.
 *
 * @copyright Copyright (c) 2012, Idea 112 Ltd., All rights reserved.
 * @author Mihail Milushev <lanzz@idea112.com>
 * @package form
 */

/**
 * Class representing a form element.
 *
 * @package form
 */
class Form_Element extends Form_Container {

	/**
	 * Construct a form element.
	 *
	 * @param Form $form			Form instance the element belongs to
	 * @param string $name			Name of the element
	 */
	public function __construct(Form $form, $name) {
		$this->form = $form;
		$this->name = $name;
	}

	/**
	 * Get the element's name.
	 *
	 * @return string				Name of the element
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the element's default value.
	 *
	 * @return mixed				The default value
	 */
	public function get_default() {
		if ($this->is_array()) {
			$values = array();
			foreach ($this->children as $name => $element) {
				$values[$name] = $element->get_default();
			}
			return $values;
		}
		return $this->default;
	}

	/**
	 * Get the element's submitted value.
	 *
	 * @return mixed				The submitted value
	 */
	public function get_submitted() {
		if ($this->is_array()) {
			$values = array();
			foreach ($this->children as $name => $element) {
				$values[$name] = $element->get_submitted();
			}
			return $values;
		}
		return $this->value;
	}

	/**
	 * Get the element's value (the submitted value or the default value if not submitted).
	 *
	 * @return mixed				The element's value
	 */
	public function get_value() {
		if ($this->is_array()) {
			$values = array();
			foreach ($this->children as $name => $element) {
				$values[$name] = $element->get_value();
			}
			return $values;
		}
		return isset($this->value)? $this->value: $this->default;
	}

	/**
	 * Get the type of the element's value.
	 *
	 * @return string
	 */
	public function get_type() {
		return gettype($this->get_value());
	}

	/**
	 * Return the element's name mangled into an identifier.
	 *
	 * @param string|null $prefix	Prefix to prepend to the identifier
	 * @param string|null $suffix	Suffix to append to the identifier
	 * @return string				The identifier for the element
	 */
	public function get_id($prefix = null, $suffix = null) {
		$prefix = preg_replace('/[^a-zA-Z0-9-]+/', '-', $prefix);
		$suffix = preg_replace('/[^a-zA-Z0-9-]+/', '-', $suffix);
		$id = trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $this->name), '-');
		return $prefix.$id.$suffix;
	}

	/**
	 * Get the value of the element as a scalar.
	 *
	 * If the value is an array, `as_scalar` will return `null`.
	 *
	 * @return scalar|null			The value of the element
	 */
	public function as_scalar() {
		if ($this->is_array()) {
			return null;
		}
		return $this->get_value();
	}

	/**
	 * Get the value of the element as an array.
	 *
	 * If the value is a scalar, `as_array` will return it wrapped in an array.
	 *
	 * @return array				The value of the element
	 */
	public function as_array() {
		if ($this->is_scalar()) {
			return array($this->value);
		}
		return $this->get_value();
	}

	/**
	 * Get the value of the element as a HTML-safe string.
	 *
	 * @return string				The value with HTML special chars escaped
	 */
	public function as_html() {
		return htmlspecialchars($this->as_scalar());
	}

	/**
	 * Get the value of the element as a URL-safe string.
	 *
	 * @return string				The value with URL special chars escaped
	 */
	public function as_url() {
		return rawurlencode($this->as_scalar());
	}

	/**
	 * Return an `id="..."` HTML attribute for the element.
	 *
	 * @param string|null $prefix	Prefix to prepend to the identifier
	 * @param string|null $suffix	Suffix to append to the identifier
	 * @return string				The rendered attribute
	 */
	public function id($prefix = null, $suffix = null) {
		return ' id="'.htmlspecialchars($this->get_id($prefix, $suffix)).'" ';
	}

	/**
	 * Return a `name="..."` HTML attribute for the element.
	 *
	 * @return string				The rendered attribute
	 */
	public function name() {
		$name = $this->name;
		if (is_array($this->get_value())) {
			$name .= '[]';
		}
		return ' name="'.htmlspecialchars($name).'" ';
	}

	/**
	 * Return a `value="..."` HTML attribute.
	 *
	 * Some elements need to render a value different from their
	 * submitted value, so the actual value to render can be overridden
	 * using the $value parameter.
	 *
	 * @param string|null $value	Override the value to render
	 * @return string				The rendered attribute
	 */
	public function value($value = null) {
		if (is_null($value)) {
			$value = $this->as_html();
		}
		return ' value="'.$value.'" ';
	}

	/**
	 * Return an `id="..."` and a `name="..."` HTML attributes for the element.
	 *
	 * @param string|null $prefix	Prefix to prepend to the identifier
	 * @param string|null $suffix	Suffix to append to the identifier
	 * @return string				The rendered attributes
	 */
	public function id_name($prefix = null, $suffix = null) {
		return $this->id($prefix, $suffix).$this->name();
	}

	/**
	 * Return an `id="..."`, a `name="..."` and a `value="..."` HTML attributes.
	 *
	 * @param string|null $prefix	Prefix to prepend to the identifier
	 * @param string|null $suffix	Suffix to append to the identifier
	 * @return string				The rendered attributes
	 */
	public function id_name_value($prefix = null, $suffix = null) {
		return $this->id($prefix, $suffix).$this->name().$this->value();
	}

	/**
	 * Return a `checked` HTML attribute if the value matches.
	 *
	 * @param string $value			Value to compare agains the element's value
	 * @return string				` checked ` or an empty string
	 */
	public function checked($value) {
		$current_value = $this->get_value();
		if (is_array($current_value)) {
			$checked = in_array($value, $current_value);
		} else {
			$checked = $value == $current_value;
		}
		return $checked? ' checked ': '';
	}

	/**
	 * Return a `selected` HTML attribute if the value matches.
	 *
	 * @param string $value			Value to compare agains the element's value
	 * @return string				` selected ` or an empty string
	 */
	public function selected($value) {
		$current_value = $this->get_value();
		if (is_array($current_value)) {
			$checked = in_array($value, $current_value);
		} else {
			$checked = $value == $current_value;
		}
		return $checked? ' selected ': '';
	}

	/**
	 * Return `<input type="hidden">` HTML tags for the element and all its children.
	 *
	 * @return string				The rendered HTML tags
	 */
	public function hidden() {
		$fields = array();
		$value = $this->get_value();
		if (!is_null($value) && !is_array($value)) {
			$fields[] = '<input type="hidden"'.$this->id_name_value().'>';
		}
		foreach ($this->keys as $name) {
			$fields[] = $this->__get($name)->hidden();
		}
		return join("\n", $fields);
	}

	/**
	 * Return a query string containing the value of the element and all its children.
	 *
	 * @return string				The rendered query string
	 */
	public function query() {
		return http_build_query(array($this->name => $this->get_value()));
	}

	/**
	 * Return a `<label>` HTML tag for the element.
	 *
	 * Since some elements have multiple tags with different values in an
	 * HTML form (e.g., radio buttons with the same name but different
	 * values), they need to have different IDs based on their values; if
	 * you provide a `$value` parameter, it will be appended as `-$value`
	 * as a suffix to the element's ID.
	 *
	 * @param string $label			Label text
	 * @param string|null $value	Value of the element
	 * @return string				The rendered HTML tag
	 */
	public function label($label, $value = null) {
		$id = $this->get_id(null, isset($value)? '-'.$value: '');
		return '<label for="'
			. htmlspecialchars($id)
			. '">'
			. htmlspecialchars($label)
			. '</label>';
	}

	/**
	 * Render an `<input type="text">` HTML tag for the element.
	 *
	 * @param string|null $attr		Custom attributes to add to the tag
	 * @return string				The rendered HTML tag
	 */
	public function input($attr = null) {
		return '<input type="text"'
			. $this->id_name_value()
			. $attr.'>';
	}

	/**
	 * Render an `<input type="password">` HTML tag for the element.
	 *
	 * @param string|null $attr		Custom attributes to add to the tag
	 * @return string				The rendered HTML tag
	 */
	public function password($attr = null) {
		return '<input type="password"'
			. $this->id_name()
			. $attr.'>';
	}

	/**
	 * Render a `<textarea>` HTML tag for the element.
	 *
	 * @param string|null $attr		Custom attributes to add to the tag
	 * @return string				The rendered HTML tag
	 */
	public function textarea($attr = null) {
		return '<textarea'
			. $this->id_name()
			. $attr.'>'
			. $this->as_html()
			. '</textarea>';
	}

	/**
	 * Render an `<input type="checkbox">` HTML tag for the element.
	 *
	 * @param string $value			Value for the checkbox
	 * @param string|null $attr		Custom attributes to add to the tag
	 * @return string				The rendered HTML tag
	 */
	public function checkbox($value, $attr = null) {
		return '<input type="checkbox"'
			. $this->id_name_value(null, '-'.$value)
			. $this->checked($value)
			. $attr.'>';
	}

	/**
	 * Render an `<input type="radio">` HTML tag for the element.
	 *
	 * @param string $value			Value for the radio button
	 * @param string|null $attr		Custom attributes to add to the tag
	 * @return string				The rendered HTML tag
	 */
	public function radio($value, $attr = null) {
		return '<input type="radio"'
			. $this->id_name_value(null, '-'.$value)
			. $this->checked($value)
			. $attr.'>';
	}

	/**
	 * Render a single `<option>` HTML tag for the element.
	 *
	 * @param string $value			Value for the option
	 * @param string $label			Label for the option
	 * @param string|null $attr		Custom attributes to add to the tag
	 * @return string				The rendered HTML tag
	 */
	public function option($value, $label, $attr = null) {
		return '<option'
			. $this->id(null, '-'.$value)
			. $this->value($value)
			. $this->selected($value)
			. '>'
			. htmlspecialchars($label)
			. '</option>';
	}

	/**
	 * Render a `<select>` HTML tag containing a list of options.
	 *
	 * @param string[] $values		Values for the options
	 * @param string|null $attr		Custom attributes to add to the tag
	 * @return string				The rendered HTML
	 */
	public function select(array $values, $attr = null) {
		$options = array();
		foreach ($values as $value => $label) {
			$options[] = $this->option($value, $label);
		}
		return '<select'
			. $this->id_name()
			. $attr.'>'
			. join("\n", $options)
			. '</select>';
	}

	/**
	 * Render a `<input type="submit">` HTML tag for the element.
	 *
	 * @param string $label			Label for the submit button
	 * @param string|null $attr		Custom attributes to add to the tag
	 * @return string				The rendered HTML tag
	 */
	public function submit($label, $attr = null) {
		return '<input type="submit"'
			. $this->id_name()
			. $this->value($label)
			. $attr.'>';
	}

}