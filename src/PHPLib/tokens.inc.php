<?php

/*=======================================================================================
 *																						*
 *									Tokens.inc.php										*
 *																						*
 *======================================================================================*/

/**
 *	<h4>Global default token definitions.</h4>
 *
 * This file contains default token definitions used in this library, these represent the
 * characters used to compile codes and special strings.
 *
 *	@package	Core
 *	@subpackage	Definitions
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		14/03/2016
 */

/*=======================================================================================
 *	TOKENS																				*
 *======================================================================================*/

/**
 * Tag prefix token.
 *
 * This token is prepended to the serial number to compile tags.
 */
const kTOKEN_TAG_PREFIX = '@';

/**
 * Namespace separator token.
 *
 * This is used to separate namespaces from codes.
 */
const kTOKEN_NAMESPACE_SEPARATOR = ':';

/*=======================================================================================
 *	STANDARD OPTION TOKENS																*
 *======================================================================================*/

/**
 * Single or multiple.
 *
 * This token represents a boolean flag which indicates whether to process one or more
 * elements: if <tt>TRUE</tt>, it means that more than one element is to be processed; if
 * <tt>FALSE</tt>, it means that only one element is to be processed.
 */
const kTOKEN_OPT_MANY = '$doAll';

/**
 * Native format.
 *
 * This token represents a boolean flag which indicates whether to return a result in native
 * format or not: if <tt>TRUE</tt>, it means we expect the result of the operation to be in
 * driver native format; if <tt>FALSE</tt>, it means that we want the result in the standard
 * driver-agnostic format.
 */
const kTOKEN_OPT_NATIVE = '$doNative';

/**
 * Skip.
 *
 * This token represents the number of elements to skip before returning a list of values.
 */
const kTOKEN_OPT_SKIP = '$skip';

/**
 * Limit.
 *
 * This token represents the number of elements to select from a list of values.
 */
const kTOKEN_OPT_LIMIT = '$limit';


?>
