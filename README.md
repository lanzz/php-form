php-form
========

PHP library for dealing with forms



Class `Form_Container`  
`|`  
`+--` Implements `Countable`  
`|   +-- int ->count()`  
`|`  
`+--` Implements `Iterator`  
`|   |-- Form_Element ->current()`  
`|   |-- string ->key()`  
`|   |-- void ->next()`  
`|   |-- void ->rewind()`  
`|   +-- bool ->valid()`  
`|`  
`+--` Implements `ArrayAccess`  
`|   |-- FormElement ->offsetGet($index)`  
`|   |-- void ->offsetSet($index, $value)`  
`|   |-- bool ->offsetExists($index)`  
`|   +-- void ->ofsetUnset($index)`  
`|`  
`+--` Magic methods  
`|   |-- FormElement ->__get($name)`  
`|   |-- void ->__set($name, $value)`  
`|   |-- bool ->__isset($name)`  
`|   +-- void ->__unset($name)`  
`|`  
`+--` Instance methods  
`|   |-- $this ->clear_errors($code = null)`  
`|   |-- $this ->set_error($error, $code = null)`  
`|   |-- $this ->add_error($error, $code = null)`  
`|   |-- bool ->has_error($code = null)`  
`|   |-- array ->get_errors($code = null)`  
`|   +-- string ->if_error()`  
`|`  
`+--` Sub-classes  
`    |`  
`    +--` Class `Form`  
`    |   |`  
`    |   |--` Static methods  
`    |   |   |-- ::from_array(array $submission, $name = null)`  
`    |   |   |-- ::from_get($context = null)`  
`    |   |   |-- ::from_post($context = null)`  
`    |   |   +-- ::from_request($context = null)`  
`    |   |`  
`    |   +-- Instance methods  
`    |       |-- bool ->is_submitted()`  
`    |       |-- $this ->set_defaults(array $defaults)`  
`    |       |-- string ->hidden()`  
`    |       +-- string ->query()`  
`    |`  
`    +--` Class `Form_Element`  
`        |`  
`        +--` Instance methods  
`            |-- string ->get_name()`  
`            |-- string ->get_default()`  
`            |-- string ->get_submitted()`  
`            |-- string ->get_value()`  
`            |-- string ->get_id()`  
`            |-- string ->as_string()`  
`            |-- string ->as_html()`  
`            |-- string ->as_url()`  
`            |-- string ->name()`  
`            |-- string ->value()`  
`            |-- string ->id($prefix = null, $suffix = null)`  
`            |-- string ->checked($value)`  
`            |-- string ->selected($value)`  
`            |-- string ->hidden()`  
`            |-- string ->query()`  
`            |-- string ->input($attributes = null)`  
`            |-- string ->password($attributes = null)`  
`            |-- string ->textarea($attributes = null)`  
`            |-- string ->checkbox($value, $attributes = null)`  
`            |-- string ->radio($value, $attributes = null)`  
`            |-- string ->select(array $values, $attributes = null)`  
`            +-- string ->submit($label, $attributes = null)`