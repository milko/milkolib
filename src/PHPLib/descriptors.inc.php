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
 * Kind: <tt>{@link kKIND_DISCRETE}</tt>
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
 * Kind: <tt>{@link kKIND_DISCRETE}</tt>
 */
const kTAG_GID				= '@3';

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
 */
const kTAG_NAME				= '@4';

/**
 * <h3>Description.</h3><p />
 *
 * The property holds the object's <em>description</em> or <em>definition</em>, it
 * represents a text that <em>describes in detail</em> the current object.
 *
 * Type: <tt>{@link kTYPE_LANG_STRING}</tt>.
 */
const kTAG_DESCRIPTION		= '@5';

/*=======================================================================================
 *	NODE PROPERTIES																		*
 *======================================================================================*/

/**
 * <h3>Node type.</h3><p />
 *
 * The property holds an <em>enumerated set of values</em> belonging to a controlled
 * vocabulary which <em>defines the type and function of the node</em>.
 *
 * Type: <tt>{@link kTYPE_ENUM_SET}</tt>.
 * Kind: <tt>{@link kKIND_CATEGORICAL}</tt>
 */
const kTAG_NODE_TYPE		= '@6';


?>
