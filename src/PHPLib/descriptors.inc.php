<?php

/*=======================================================================================
 *																						*
 *									descriptors.inc.php									*
 *																						*
 *======================================================================================*/

/**
 *	<h4>Global default data descriptor definitions.</h4>
 *
 * This file contains default descriptor definitions, it features the default property
 * offsets that comprise the data dictionary and the core objects of this library. These
 * offsets are used as field names in the database and their definition can be found in the
 * data dictionary.
 *
 * This set of indicators represents the subset of properties that comprise the core objects
 * in this library, they are defined here to allow their use in absence of a database.
 *
 *	@package	Core
 *	@subpackage	Definitions
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		14/03/2016
 */

/*=======================================================================================
 *	TERM PROPERTIES																		*
 *======================================================================================*/

/**
 * <h3>Namespace reference.</h3><p />
 *
 * The property holds the <em>reference to the object</em> that represents the current
 * object's <em>namespace</em>, expressed as a <em>document identifier</em>.
 *
 * Type: <tt>{@link kTYPE_STRING}</tt>
 * Kind: <tt>{@link kKIND_DISCRETE}</tt>
 */
const kTAG_NS				= '@1';

/**
 * <h3>Local identifier.</h3><p />
 *
 * The property holds the object <em>local</em> identifier, that is, the code that uniquely
 * identifies the object <em>within its namespace</em>.
 *
 * Type: <tt>{@link kTYPE_STRING}</tt>.
 * Kind: <tt>{@link kKIND_DISCRETE} {@link kKIND_REQUIRED}</tt>
 */
const kTAG_LID				= '@2';

/**
 * <h3>Global identifier.</h3><p />
 *
 * The property holds the object <em>global</em> identifier, that is, the code that uniquely
 * identifies the term <em>among all namespaces</em>.
 *
 * In general, this code is computed by concatenating the global identifier of the object
 * representing the namespace with the local identifier of the current object, separated by
 * the {@link kTOKEN_NAMESPACE_SEPARATOR} token.
 *
 * Type: <tt>{@link kTYPE_STRING}</tt>.
 * Kind: <tt>{@link kKIND_DISCRETE} {@link kKIND_REQUIRED}</tt>
 */
const kTAG_GID				= '@3';

/**
 * <h3>Type.</h3><p />
 *
 * The property holds an <em>enumerated set of values</em> belonging to a controlled
 * vocabulary which <em>defines the type of the object</em>, it should describe the
 * <em>nature</em> of the object.
 *
 * Type: <tt>{@link kTYPE_ENUM}</tt>.
 * Kind: <tt>{@link kKIND_CATEGORICAL}</tt>
 */
const kTAG_TYPE				= '@4';

/**
 * <h3>Kind.</h3><p />
 *
 * The property holds an <em>enumerated set of values</em> belonging to a controlled
 * vocabulary which <em>defines the function of the object</em>, it should describe the
 * <em>kind<em> of object.
 *
 * Type: <tt>{@link kTYPE_ENUM_SET}</tt>.
 * Kind: <tt>{@link kKIND_CATEGORICAL}</tt>
 */
const kTAG_KIND				= '@5';

/**
 * <h3>Name.</h3><p />
 *
 * The property holds the object's <em>name</em> or <em>label</em>, it represents a short
 * description that can be used as a label and that should give a rough idea of what the
 * object represents.
 *
 * This property is an associative array with the <em>language code as key</em> and the
 * <em>name as value</em>.
 *
 * Type: <tt>{@link kTYPE_LANG_STRING}</tt>.
 * Kind: <tt>{@link kKIND_DISCRETE} {@link kKIND_LOOKUP} {@link kKIND_REQUIRED}</tt>.
 */
const kTAG_NAME				= '@6';

/**
 * <h3>Description.</h3><p />
 *
 * The property holds the object's <em>description</em> or <em>definition</em>, it
 * represents a text that <em>describes in detail</em> the current object.
 *
 * Type: <tt>{@link kTYPE_LANG_STRING}</tt>.
 * Kind: <tt>{@link kKIND_DISCRETE}</tt>
 */
const kTAG_DESCRIPTION		= '@7';

/**
 * <h3>Creation stamp.</h3><p />
 *
 * The property holds the object's <em>creation time stamp</em>.
 *
 * Type: <tt>{@link kTYPE_TIMESTAMP}</tt>.
 * Kind: <tt>{@link kKIND_DISCRETE}</tt>
 */
const kTAG_CREATION			= '@8';

/**
 * <h3>Modification stamp.</h3><p />
 *
 * The property holds the object's <em>modification time stamp</em>.
 *
 * Type: <tt>{@link kTYPE_TIMESTAMP}</tt>.
 * Kind: <tt>{@link kKIND_DISCRETE}</tt>
 */
const kTAG_MODIFICATION		= '@9';

/*=======================================================================================
 *	DESCRIPTOR PROPERTIES																*
 *======================================================================================*/

/**
 * <h3>Symbol.</h3><p />
 *
 * The property holds the object <em>symbol</em> or <em>constant<em>, which is a string that
 * serves as a variable name for the object; the symbol should be unique within a context.
 *
 * Type: <tt>{@link kTYPE_STRING}</tt>.
 * Kind: <tt>{@link kKIND_DISCRETE}</tt>
 */
const kTAG_SYMBOL			= '@A';

/**
 * <h3>Synonyms.</h3><p />
 *
 * The property holds a list of symbols which refer to <em>synonyms of the current
 * descriptor</em>, the property is structured as a list of strings.
 *
 * Type: <tt>{@link kTYPE_STRING}</tt>.
 * Kind: <tt>{@link kKIND_LIST}</tt>
 */
const kTAG_SYNONYMS			= '@B';

/**
 * <h3>Reference count.</h3><p />
 *
 * The property holds an <em>number of objects that reference the current one</em>, when
 * inserted for the first time the value is <tt>0</tt>, the object cannot be deleted if this
 * value is greater than <tt>0</tt>.
 *
 * Type: <tt>{@link kTYPE_INT}</tt>.
 * Kind: <tt>{@link kKIND_QUANTITATIVE}</tt>
 */
const kTAG_REF_COUNT		= '@C';

/**
 * <h3>Minimum value.</h3><p />
 *
 * The property holds a number representing the <em>minimum value</em> for instances of the
 * current descriptor.
 *
 * Type: <tt>{@link kTYPE_FLOAT}</tt>.
 * Kind: <tt>{@link kKIND_QUANTITATIVE}</tt>
 */
const kTAG_MIN_VAL			= '@D';

/**
 * <h3>Maximum value.</h3><p />
 *
 * The property holds a number representing the <em>maximum value</em> for instances of the
 * current descriptor.
 *
 * Type: <tt>{@link kTYPE_FLOAT}</tt>.
 * Kind: <tt>{@link kKIND_QUANTITATIVE}</tt>
 */
const kTAG_MAX_VAL			= '@E';

/**
 * <h3>Pattern.</h3><p />
 *
 * The property holds a string representing the <em>expected pattern of the string</em>
 * descriptor value, this is used to validate strings.
 *
 * Type: <tt>{@link kTYPE_STRING}</tt>.
 * Kind: <tt>{@link kKIND_DISCRETE}</tt>
 */
const kTAG_PATTERN			= '@F';

/**
 * <h3>Minimum expected value.</h3><p />
 *
 * The property holds a number representing the <em>lowest valid value</em> for this
 * descriptor.
 *
 * Type: <tt>{@link kTYPE_FLOAT}</tt>.
 * Kind: <tt>{@link kKIND_QUANTITATIVE}</tt>
 */
const kTAG_MIN_VAL_EXPECTED	= '@10';

/**
 * <h3>Maximum expected value.</h3><p />
 *
 * The property holds a number representing the <em>highest valid value</em> for this
 * descriptor.
 *
 * Type: <tt>{@link kTYPE_FLOAT}</tt>.
 * Kind: <tt>{@link kKIND_QUANTITATIVE}</tt>
 */
const kTAG_MAX_VAL_EXPECTED	= '@11';

/*=======================================================================================
 *	AVAILABLE PROPERTIES																*
 *======================================================================================*/

/**
 * <h3>Available serial identifier.</h3><p />
 *
 * This value holds the next available serial identifier for client defined descriptors.
 */
const kNEXT_TAG = 100;


?>
