<?php

/*=======================================================================================
 *																						*
 *									tags.inc.php										*
 *																						*
 *======================================================================================*/

/**
 *	<h4>Global default tag definitions.</h4>
 *
 * This file contains default global tag definitions, it features the default property
 * offsets that comprise the data dictionary and the core objects of this library. These
 * offsets are used as field names in the database and their definition can be found in the
 * data dictionary.
 *
 * This set of tags represents the subset of properties that comprise the core objects in
 * this library, they are defined here to allow their use in absence of a database.
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
 * <h4>Namespace reference.</h4>
 *
 * The property holds the <em>reference to the object</em> that represents the current
 * object's <em>namespace</em>, expressed as a <em>document identifier</em>.
 *
 * Type: <tt>mixed</tt>.
 */
const kTAG_NS				= '@1';

/**
 * <h4>Local identifier.</h4>
 *
 * The property holds the object <em>local</em> identifier, that is, the code that uniquely
 * identifies the object <em>within its namespace</em>.
 *
 * Type: <tt>string</tt>.
 */
const kTAG_LID				= '@2';

/**
 * <h4>Global identifier.</h4>
 *
 * The property holds the object <em>global</em> identifier, that is, the code that uniquely
 * identifies the term <em>among all namespaces</em>.
 *
 * In general, this code is computed by concatenating the global identifier of the object
 * representing the namespace with the local identifier of the current object, separated by
 * the {@link kTOKEN_NAMESPACE_SEPARATOR} token.
 *
 * Type: <tt>string</tt>.
 */
const kTAG_GID				= '@3';

/**
 * <h4>Name.</h4>
 *
 * The property holds the object's <em>name</em> or <em>label</em>, it represents a short
 * description that can be used as a label and that should give a rough idea of what the
 * object represents.
 *
 * This property is an associative array with the <em>language code as key</em> and the
 * <em>name as value</em>.
 *
 * Type: <tt>associative array</tt>.
 */
const kTAG_NAME				= '@4';

/**
 * <h4>Description.</h4>
 *
 * The property holds the object's <em>description</em> or <em>definition</em>, it
 * represents a text that <em>describes in detail</em> the current object.
 *
 * Type: <tt>associative array</tt>.
 */
const kTAG_DESCRIPTION		= '@5';

/*=======================================================================================
 *	NODE PROPERTIES																		*
 *======================================================================================*/

/**
 * <h4>Node type.</h4>
 *
 * The property holds an enumerated set of values defining the type of node.
 *
 * Type: <tt>array</tt>.
 */
const kTAG_NODE_TYPE		= '@6';


?>
