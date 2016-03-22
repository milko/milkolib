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
 * Result format.
 *
 * This token represents a controlled vocabulary that determines the format in which query
 * retults should be returned:
 *
 * <ul>
 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_NATIVE}</tt>: Return the result in the database driver
 * 		native format.
 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_STANDARD}</tt>: Return the result as
 * 		{@link Milko\PHPLib\Document} instances.
 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return the result as a set of document
 * 		handles.
 * </ul>
 */
const kTOKEN_OPT_FORMAT = '$doFormat';

/**
 * Native result format.
 *
 * This token represents a controlled vocabulary element of {@link kTOKEN_OPT_FORMAT} which
 * indicates that a query result should be returned in the native database driver format.
 */
const kTOKEN_OPT_FORMAT_NATIVE = 'N';

/**
 * Standard result format.
 *
 * This token represents a controlled vocabulary element of {@link kTOKEN_OPT_FORMAT} which
 * indicates that a query result should be returned as a set of
 * {@link Milko\PHPLib\Document} instances.
 */
const kTOKEN_OPT_FORMAT_STANDARD = 'S';

/**
 * Document handle result format.
 *
 * This token represents a controlled vocabulary element of {@link kTOKEN_OPT_FORMAT} which
 * indicates that a query result should be returned as a set of document handles.
 */
const kTOKEN_OPT_FORMAT_HANDLE = 'H';

/**
 * Document key result format.
 *
 * This token represents a controlled vocabulary element of {@link kTOKEN_OPT_FORMAT} which
 * indicates that a query result should be returned as a set of document keys.
 */
const kTOKEN_OPT_FORMAT_KEY = 'K';

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
