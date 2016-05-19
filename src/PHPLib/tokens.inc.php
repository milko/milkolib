<?php

/*=======================================================================================
 *																						*
 *									tokens.inc.php										*
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
 * <h4>Tag prefix token.</h4><p />
 *
 * This token is prepended to the serial number to compile tags.
 */
const kTOKEN_TAG_PREFIX = '@';

/**
 * <h4>Namespace separator token.</h4><p />
 *
 * This is used to separate namespaces from codes.
 */
const kTOKEN_NAMESPACE_SEPARATOR = ':';

/*=======================================================================================
 *	STANDARD OPTION TOKENS																*
 *======================================================================================*/

/**
 * <h4>Single or multiple.</h4><p />
 *
 * This token represents a boolean flag which indicates whether to process one or more
 * elements: if <tt>TRUE</tt>, it means that more than one element is to be processed; if
 * <tt>FALSE</tt>, it means that only one element is to be processed.
 */
const kTOKEN_OPT_MANY = '$doAll';

/**
 * <h4>Result format.</h4><p />
 *
 * This token represents a controlled vocabulary that determines the format in which query
 * retults should be returned:
 *
 * <ul>
 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_NATIVE}</tt>: Return the result in the database driver
 * 		native format.
 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_CONTAINER}</tt>: Return the result as
 * 		{@link Milko\PHPLib\Container} instances.
 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_DOCUMENT}</tt>: Return the result as
 * 		{@link Milko\PHPLib\Document} instances.
 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return the result as a set of document
 * 		handles.
 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return the result as a set of document keys.
 * </ul>
 */
const kTOKEN_OPT_FORMAT = '$doFormat';

/**
 * <h4>Array result format.</h4><p />
 *
 * This token represents a controlled vocabulary element of {@link kTOKEN_OPT_FORMAT} which
 * indicates that a query result should be returned as an array.
 */
const kTOKEN_OPT_FORMAT_ARRAY = 'A';

/**
 * <h4>Native result format.</h4><p />
 *
 * This token represents a controlled vocabulary element of {@link kTOKEN_OPT_FORMAT} which
 * indicates that a query result should be returned in the native database driver format.
 */
const kTOKEN_OPT_FORMAT_NATIVE = 'N';

/**
 * <h4>Container result format.</h4><p />
 *
 * This token represents a controlled vocabulary element of {@link kTOKEN_OPT_FORMAT} which
 * indicates that a query result should be returned as a set of
 * {@link Milko\PHPLib\Container} instances.
 */
const kTOKEN_OPT_FORMAT_CONTAINER = 'C';

/**
 * <h4>Document result format.</h4><p />
 *
 * This token represents a controlled vocabulary element of {@link kTOKEN_OPT_FORMAT} which
 * indicates that a query result should be returned as a set of
 * {@link Milko\PHPLib\Document} derived instances.
 */
const kTOKEN_OPT_FORMAT_DOCUMENT = 'D';

/**
 * <h4>Document handle result format.</h4><p />
 *
 * This token represents a controlled vocabulary element of {@link kTOKEN_OPT_FORMAT} which
 * indicates that a query result should be returned as a set of document handles.
 */
const kTOKEN_OPT_FORMAT_HANDLE = 'H';

/**
 * <h4>Document key result format.</h4><p />
 *
 * This token represents a controlled vocabulary element of {@link kTOKEN_OPT_FORMAT} which
 * indicates that a query result should be returned as a set of document keys.
 */
const kTOKEN_OPT_FORMAT_KEY = 'K';

/**
 * <h4>Skip.</h4><p />
 *
 * This token represents the number of elements to skip before returning a list of values.
 */
const kTOKEN_OPT_SKIP = '$skip';

/**
 * <h4>Limit.</h4><p />
 *
 * This token represents the number of elements to select from a list of values.
 */
const kTOKEN_OPT_LIMIT = '$limit';

/*=======================================================================================
 *	RELATIONSHIP DIRECTION TOKENS														*
 *======================================================================================*/

/**
 * <h4>Relationship direction.</h4><p />
 *
 * This token represents a controlled vocabulary that defines the direction of a
 * relationship:
 *
 * <ul>
 * 	<li><tt>{@link kTOKEN_OPT_DIRECTION_IN}</tt>: Incoming relationships.
 * 	<li><tt>{@link kTOKEN_OPT_DIRECTION_OUT}</tt>: Outgoing relationships.
 * 	<li><tt>{@link kTOKEN_OPT_DIRECTION_ANY}</tt>: Incoming and outgoing relationships.
 * </ul>
 */
const kTOKEN_OPT_DIRECTION = '$direction';

/**
 * <h4>Incoming relationships.</h4><p />
 *
 * This token indicates relationships directed towards the current vertex.
 */
const kTOKEN_OPT_DIRECTION_IN = 'in';

/**
 * <h4>Outgoing relationships.</h4><p />
 *
 * This token indicates relationships stemming from the current vertex.
 */
const kTOKEN_OPT_DIRECTION_OUT = 'out';

/**
 * <h4>Any direction.</h4><p />
 *
 * This token indicates both incoming and outgoing relationships.
 */
const kTOKEN_OPT_DIRECTION_ANY = 'any';

/*=======================================================================================
 *	COLLECTION TYPE TOKENS																*
 *======================================================================================*/

/**
 * <h4>Collection type.</h4><p />
 *
 * This token represents a controlled vocabulary that defines the type of a collection:
 *
 * <ul>
 * 	<li><tt>{@link kTOKEN_OPT_COLLECTION_TYPE_DOC}</tt>: Documents repository.
 * 	<li><tt>{@link kTOKEN_OPT_COLLECTION_TYPE_EDGE}</tt>: Edges repository.
 * </ul>
 */
const kTOKEN_OPT_COLLECTION_TYPE = '$collType';

/**
 * <h4>Document collection.</h4><p />
 *
 * This token indicates a collection of type <em>document</em>, collections of this type
 * store document records.
 */
const kTOKEN_OPT_COLLECTION_TYPE_DOC = 'docu';

/**
 * <h4>Edge collection.</h4><p />
 *
 * This token indicates a collection of type <em>edge</em>, collections of this type store
 * predicate documents which represent a directed relationship from a source document to
 * another destination document.
 */
const kTOKEN_OPT_COLLECTION_TYPE_EDGE = 'edge';

/*=======================================================================================
 *	INCREMENTAL SERIAL TOKENS															*
 *======================================================================================*/

/**
 * <h4>Serials offset.</h4><p />
 *
 * This token represents the key of the {@link Descriptor} serial number record in the
 * resources collection.
 */
const kTOKEN_SERIAL_OFFSET = 'serial';

/**
 * <h4>Descriptors serial key.</h4><p />
 *
 * This token represents the key of the {@link Descriptor} serial number record in the
 * resources collection.
 */
const kTOKEN_SERIAL_DESCRIPTOR = 'descriptor';

/*=======================================================================================
 *	GEOMETRY TOKENS																		*
 *======================================================================================*/

/**
 * Shape type
 *
 * This token is used as the offset for defining a shape type.
 */
define( "kTOKEN_TYPE",					'type' );

/**
 * Geometry
 *
 * This token represents the <em>geometry of a shape</em>, it is by default an array which
 * can be nested at several levels, depending on the type of geometry. It is used in
 * properties of type {@link kTYPE_SHAPE} to provide the shape geometry; incidentally, it
 * is named <tt>coordinates</tt> so that when used with the {@link kTAG_TYPE} tag it forms
 * a GeoJSON object.
 */
define( "kTOKEN_GEOMETRY",				'coordinates' );

/**
 * Radius
 *
 * This token represents the <em>radius of a circle shape</em> in meters, it is used in
 * conjuction with {@link kTOKEN_TYPE} and {@link kTOKEN_GEOMETRY} to create a GeoJSON
 * circle shape.
 */
define( "kTOKEN_RADIUS",				'radius' );

/*=======================================================================================
 *	ENUMERATED LIST TOKENS																*
 *======================================================================================*/

/**
 * Controlled vocabulary
 *
 * This token is used as the offset of the controlled vocabulary is, the value is an array
 * whose elements are stored as follows:
 *
 * <ul>
 * 	<li><tt>{@link kTOKEN_ENUM_TERM}</tt>: This element will contain the enumerated value as
 *		a term instance key; if the element is a category, this item will be omitted.
 * 	<li><tt>{@link kTOKEN_ENUM_PREFERRED}</tt>: This element will contain the preferred
 * 		enumerated value as a term instance key; if the element is a category, this item
 * 		will be omitted.
 * 	<li><tt>{@link kTOKEN_ENUM_CATEGORY}</tt>: This element will contain the instance key of
 *		the term that represents a category; if the element is an enumerated value, this
 *		item will be omitted, if the element is a category this item will be provided.
 * 	<li><tt>{@link kTOKEN_ENUM_NESTED}</tt>: If the current element has a nested list, it
 *		will be contained in this item.
 * </ul>
 */
define( "kTOKEN_ENUM_LIST",					'list' );

/**
 * Enumerated value
 *
 * This token is used as the offset where the enumerated value is provided, the value is a
 * term key.
 */
define( "kTOKEN_ENUM_TERM",					'enum' );

/**
 * Preferred enumerated value
 *
 * This token is used as the offset where the preferred enumerated value is provided, the
 * value is the term key that should be used in place of the {@link kTOKEN_ENUM_TERM} value.
 */
define( "kTOKEN_ENUM_PREFERRED",			'pref' );

/**
 * Enumerated category
 *
 * This token is used as the offset where the enumerated category is provided, the value is
 * the key of a term that represents a container or category, but not an enumerated value.
 */
define( "kTOKEN_ENUM_CATEGORY",				'cat' );

/**
 * Enumerated nested list
 *
 * This token is used as the offset where the eventual nested enumeration list is stored.
 */
define( "kTOKEN_ENUM_NESTED",				'nest' );


?>
