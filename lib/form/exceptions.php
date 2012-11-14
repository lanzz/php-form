<?php
/**
 * Library to deal with processing and building HTML forms.
 *
 * @copyright Copyright (c) 2012, Idea 112 Ltd., All rights reserved.
 * @author Mihail Milushev <lanzz@idea112.com>
 * @package form\exceptions
 */

/**
 * Base form exception class.
 *
 * @package form\exceptions
 */
class Form_Exception extends Exception { }

/**
 * Type exception.
 *
 * Thrown when type-incompatible operation is attempted, e.g. adding a child element to a scalar-value parent.
 *
 * @package form\exceptions
 */
class Form_Type_Exception extends Form_Exception { }