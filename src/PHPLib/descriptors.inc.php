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
 *	GLOBAL PROPERTIES																	*
 *======================================================================================*/

/**
 * <h4>Creation stamp.</h4><p />
 *
 * The property holds the object's <em>creation time stamp</em> expressed as the result of
 * the {@link microtime()} function as float.
 *
 * Type: <tt>{@link kTYPE_FLOAT}</tt>.
 * Kind: <tt>{@link kKIND_DISCRETE}}</tt>
 */
const kTAG_CREATION			= ':cre';

/**
 * <h4>Modification stamp.</h4><p />
 *
 * The property holds the object's <em>modification time stamp</em> expressed as the result
 * of the {@link microtime()} function as float.
 *
 * Type: <tt>{@link kTYPE_FLOAT}</tt>.
 * Kind: <tt>{@link kKIND_DISCRETE}</tt>
 */
const kTAG_MODIFICATION		= ':mod';

/*=======================================================================================
 *	TERM PROPERTIES																		*
 *======================================================================================*/

/**
 * <h4>Namespace reference.</h4><p />
 *
 * The property holds the <em>reference to the object</em> that represents the current
 * object's <em>namespace</em>, expressed as a <em>document identifier</em>.
 *
 * Type: <tt>{@link kTYPE_STRING}</tt>
 * Kind: <tt>{@link kKIND_DISCRETE}</tt>
 */
const kTAG_NS				= ':ns';

/**
 * <h4>Local identifier.</h4><p />
 *
 * The property holds the object <em>local</em> identifier, that is, the code that uniquely
 * identifies the object <em>within its namespace</em>.
 *
 * Type: <tt>{@link kTYPE_STRING}</tt>.
 * Kind: <tt>{@link kKIND_DISCRETE} {@link kKIND_REQUIRED}</tt>
 */
const kTAG_LID				= ':lid';

/**
 * <h4>Global identifier.</h4><p />
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
const kTAG_GID				= ':gid';

/**
 * <h4>Name.</h4><p />
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
const kTAG_NAME				= ':name';

/**
 * <h4>Description.</h4><p />
 *
 * The property holds the object's <em>description</em> or <em>definition</em>, it
 * represents a text that <em>describes in detail</em> the current object.
 *
 * Type: <tt>{@link kTYPE_LANG_STRING}</tt>.
 * Kind: <tt>{@link kKIND_DISCRETE}</tt>
 */
const kTAG_DESCRIPTION		= ':descr';

/*=======================================================================================
 *	DESCRIPTOR PROPERTIES																*
 *======================================================================================*/

/**
 * <h4>Symbol.</h4><p />
 *
 * The property holds the object <em>symbol</em> or <em>constant<em>, which is a string that
 * serves as a variable name for the object; the symbol should be unique within a context.
 *
 * Type: <tt>{@link kTYPE_STRING}</tt>.
 * Kind: <tt>{@link kKIND_DISCRETE}</tt>
 */
const kTAG_SYMBOL			= ':sym';

/**
 * <h4>Synonyms.</h4><p />
 *
 * The property holds a list of symbols which refer to <em>synonyms of the current
 * descriptor</em>, the property is structured as a list of strings.
 *
 * Type: <tt>{@link kTYPE_STRING}</tt>.
 * Kind: <tt>{@link kKIND_LIST}</tt>
 */
const kTAG_SYNONYMS			= ':syn';

/**
 * <h4>Data type.</h4><p />
 *
 * The property holds an <em>enumerated set of values</em> belonging to a controlled
 * vocabulary which defines the <em>type</em> or <em>nature</em> of data. It is generally
 * used to indicate the primitive data type of a descriptor.
 *
 * Type: <tt>{@link kTYPE_ENUM}</tt>.
 * Kind: <tt>{@link kKIND_CATEGORICAL}</tt>
 */
const kTAG_DATA_TYPE		= ':type:data';

/**
 * <h4>Data kind.</h4><p />
 *
 * The property holds an <em>enumerated set of values</em> belonging to a controlled
 * vocabulary which defines the <em>kind</em> or <em>function</em> of the data, it should
 * describe the <em>kind<em> of object.
 *
 * Type: <tt>{@link kTYPE_ENUM_SET}</tt>.
 * Kind: <tt>{@link kKIND_CATEGORICAL}</tt>
 */
const kTAG_DATA_KIND		= ':kind:data';

/**
 * <h4>Reference count.</h4><p />
 *
 * The property holds an <em>number of objects that reference the current one</em>, when
 * inserted for the first time the value is <tt>0</tt>, the object cannot be deleted if this
 * value is greater than <tt>0</tt>.
 *
 * Type: <tt>{@link kTYPE_INT}</tt>.
 * Kind: <tt>{@link kKIND_QUANTITATIVE} {@link kKIND_PRIVATE_MODIFY}</tt>
 */
const kTAG_REF_COUNT		= ':refs';

/**
 * <h4>Minimum value.</h4><p />
 *
 * The property holds a number representing the <em>minimum value</em> for instances of the
 * current descriptor.
 *
 * Type: <tt>{@link kTYPE_FLOAT}</tt>.
 * Kind: <tt>{@link kKIND_QUANTITATIVE}</tt>
 */
const kTAG_MIN_VAL			= ':min';

/**
 * <h4>Maximum value.</h4><p />
 *
 * The property holds a number representing the <em>maximum value</em> for instances of the
 * current descriptor.
 *
 * Type: <tt>{@link kTYPE_FLOAT}</tt>.
 * Kind: <tt>{@link kKIND_QUANTITATIVE}</tt>
 */
const kTAG_MAX_VAL			= ':max';

/**
 * <h4>Pattern.</h4><p />
 *
 * The property holds a string representing the <em>expected pattern of the string</em>
 * descriptor value, this is used to validate strings.
 *
 * Type: <tt>{@link kTYPE_STRING}</tt>.
 * Kind: <tt>{@link kKIND_DISCRETE}</tt>
 */
const kTAG_PATTERN			= ':grep';

/**
 * <h4>Minimum expected value.</h4><p />
 *
 * The property holds a number representing the <em>lowest valid value</em> for this
 * descriptor.
 *
 * Type: <tt>{@link kTYPE_FLOAT}</tt>.
 * Kind: <tt>{@link kKIND_QUANTITATIVE}</tt>
 */
const kTAG_MIN_VAL_EXPECTED	= ':low';

/**
 * <h4>Maximum expected value.</h4><p />
 *
 * The property holds a number representing the <em>highest valid value</em> for this
 * descriptor.
 *
 * Type: <tt>{@link kTYPE_FLOAT}</tt>.
 * Kind: <tt>{@link kKIND_QUANTITATIVE}</tt>
 */
const kTAG_MAX_VAL_EXPECTED	= ':high';

/*=======================================================================================
 *	PREDICATE PROPERTIES																*
 *======================================================================================*/

/**
 * <h4>Predicate term.</h4><p />
 *
 * The property holds the edge predicate term reference in the form of the term document
 * key.
 *
 * Type: <tt>{@link kTYPE_REF_TERM}</tt>.
 * Kind: <tt>{@link kKIND_DISCRETE}</tt>
 */
const kTAG_PREDICATE_TERM	= ':pred';


?>
