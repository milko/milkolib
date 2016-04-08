<?php

/*=======================================================================================
 *																						*
 *									types.inc.php										*
 *																						*
 *======================================================================================*/

/**
 *	<h4>Global data type definitions.</h4>
 *
 * This file contains default data type definitions used in this library.
 *
 *	@package	Core
 *	@subpackage	Definitions
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		06/04/2016
 */

/*=======================================================================================
 *	PRIMITIVE TYPES																		*
 *======================================================================================*/

/**
 * <h3>Mixed.</h3><p />
 *
 * A mixed data type indicates that the referred property may take any data type.
 */
const kTYPE_MIXED = ':type:mixed';

/**
 * <h3>String.</h3><p />
 *
 * A string data type indicates that the referred property may hold UNICODE characters, this
 * type does not include binary data.
 */
const kTYPE_STRING = ':type:string';

/**
 * <h3>Integer.</h3><p />
 *
 * An integer data type indicates that the referred property may hold a 32 or 64 bit
 * integral numeric values.
 */
const kTYPE_INT = ':type:int';

/**
 * <h3>Float.</h3><p />
 *
 * A float data type indicates that the referred property may hold a floating point number,
 * also known as double or real. The precision of such value is not inferred, in general it
 * will be a 32 or 64 bit real.
 */
const kTYPE_FLOAT = ':type:float';

/**
 * <h3>Boolean.</h3><p />
 *
 * Boolean values can take one of two states: on or true, or off or false.
 */
const kTYPE_BOOLEAN = ':type:bool';

/*=======================================================================================
 *	DERIVED TYPES																		*
 *======================================================================================*/

/**
 * <h3>Link.</h3><p />
 *
 * A link data type indicates that the referred property is a string representing an URL
 * which is an internet link or network address.
 */
const kTYPE_URL = ':type:string:url';

/**
 * <h3>String date.</h3><p />
 *
 * This type defines a date in which the day and month may be omitted, it is a string
 * providing the date in <tt>YYYYMMDD</tt> format in which the day, or the day and month can
 * be omitted. All digits must be provided. This type can be used as a range and sorted.
 */
const kTYPE_STRING_DATE = ':type:string:date';

/**
 * <h3>String latitude.</h3><p />
 *
 * This type defines a latitude expressed in <tt>HDDMMSS.SSSS</tt> where <tt>H</tt> is the
 * hemisphere (<tt>N</tt> or <tt>S</tt>), <tt>DD</tt> is the degrees, <tt>MM</tt> is the
 * minutes and <tt>SS.SSS</tt> represents the seconds as a floating point number or integer.
 * You may omit the seconds or the seconds and minutes, all digits must be provided. The
 * degrees must range between <tt>-90</tt> to lower than <tt>90</tt>, the minutes and
 * seconds must range between  <tt>0</tt> to lower than <tt>60</tt>. This data type is
 * useful to calculate the maximum error of a coordinate.
 */
const kTYPE_STRING_LAT = ':type:string:lat';

/**
 * <h3>String longitude.</h3><p />
 *
 * This type defines a longitude expressed in <tt>HDDDMMSS.SSSS</tt> where <tt>H</tt> is the
 * hemisphere (<tt>E</tt> or <tt>W</tt>), <tt>DDD</tt> is the degrees, <tt>MM</tt> is the
 * minutes and <tt>SS.SSS</tt> represents the seconds as a floating point number or integer.
 * You may omit the seconds or the seconds and minutes, all digits must be provided. The
 * degrees must range between <tt>-180</tt> to lower than <tt>180</tt>, the minutes and
 * seconds must range between  <tt>0</tt> to lower than <tt>60</tt>. This data type is
 * useful to calculate the maximum error of a coordinate.
 */
const kTYPE_STRING_LON = ':type:string:lon';

/*=======================================================================================
 *	REFERENTIAL TYPES																	*
 *======================================================================================*/

/**
 * <h3>Object reference.</h3><p />
 *
 * This type indicates that the property references another object, the value will contain
 * the name of the collection in which the reference object resides and the object key, the
 * format in which this value is expressed depends on the specific database.
 */
const kTYPE_REF = ':type:ref';

/**
 * <h3>Collection reference.</h3><p />
 *
 * This type indicates that the property references another object belonging to the same
 * collection, the value will contain the key of the referenced object.
 */
const kTYPE_REF_SELF = ':type:ref-self';

/**
 * <h3>Term reference.</h3><p />
 *
 * This type indicates that the property references a term object, the value will contain
 * the key of the referenced term.
 */
const kTYPE_REF_TERM = ':type:ref-term';

/*=======================================================================================
 *	LOCALISED TYPES																		*
 *======================================================================================*/

/**
 * <h3>Date.</h3><p />
 *
 * Date in the native database format.
 */
const kTYPE_DATE = ':type:date';

/**
 * <h3>Timestamp.</h3><p />
 *
 * Time stamp in the native database format.
 */
const kTYPE_TIMESTAMP = ':type:time-stamp';

/*=======================================================================================
 *	CATEGORICAL TYPES																	*
 *======================================================================================*/

/**
 * <h3>Enumeration.</h3><p />
 *
 * An enumerated property may hold only one value selected from a controlled vocabulary, in
 * general, the controlled vocabulary will be a set of terms and the selected value will be
 * the term's global identifier ({@link kTAG_GID}).
 */
const kTYPE_ENUM = ':type:enum';

/**
 * <h3>Enumerated set.</h3><p />
 *
 * An enumerated set property may hold one or more unique values selected from a controlled
 * vocabulary, in general, the controlled vocabulary will be a set of terms and the selected
 * values will be the term's global identifiers ({@link kTAG_GID}).
 */
const kTYPE_ENUM_SET = ':type:enum-set';

/*=======================================================================================
 *	STRUCTURED TYPES																	*
 *======================================================================================*/

/**
 * <h3>List.</h3><p />
 *
 * This data type defines a list of elements whose value type is not inferred. This data
 * type usually applies to arrays.
 */
const kTYPE_ARRAY = ':type:array';

/**
 * <h3>Structure.</h3><p />
 *
 * This data type defines a structure or an associative array in which the element key is
 * represented by an indicator identifier.
 */
const kTYPE_STRUCT = ':type:struct';

/**
 * <h3>Shape.</h3><p />
 *
 * This data type defines a geometric shapewhich is expressed as a GeoJSON construct, it is
 * an array composed by two key/value elements:
 *
 * <ul>
 * 	<li><tt>type</tt>: The element indexed by this string contains the code indicating the
 * 		type of the shape, these are the supported values:
 * 	 <ul>
 * 		<li><tt>Point</tt>: A point.
 * 		<li><tt>LineString</tt>: A list of points.
 * 		<li><tt>Polygon</tt>: A polygon, including its rings.
 * 	 </ul>
 * 	<li><tt>coordinates</tt>: The element indexed by this string contains the geometry of
 * 		the shape, which has a structure which depends on the shape type:
 * 	 <ul>
 * 		<li><em>Point</em>: The point is an array of two floating point numbers,
 * 			respectively the longitude and latitude.
 * 		<li><em>LineString</em>: A line string is an array of points expressed in the
 * 			<tt>Point</tt> geometry (longitude and latitude).
 * 		<li><em>Polygon</em>: A polygon is a list of rings whose geometry is like the
 * 			<tt>LineString</tt> geometry, except that the first and last point must match.
 * 			The first ring represents the outer boundary of the polygon, the other rings are
 * 			optional and represent holes in the polygon.
 * 	 </ul>
 * </ul>
 */
const kTYPE_SHAPE = ':type:shape';

/**
 * <h3>Language string.</h3><p />
 *
 * This data type defines a list of strings expressed in different languages. The list
 * elements are composed by key/value pairs, the key is expressed as the language code and
 * the value is a single string in that language.
 */
const kTYPE_LANG_STRING = ':type:string:lang';

/**
 * <h3>Language strings.</h3><p />
 *
 * This data type defines a list of strings expressed in different languages. The list
 * elements are composed by key/value pairs, the key is expressed as the language code and
 * the value is a list of strings in that language.
 */
const kTYPE_LANG_STRINGS = ':type:string:langs';

/*=======================================================================================
 *	FUNCTIONAL TYPES																	*
 *======================================================================================*/

/**
 * <h3>Attribute.</h3><p />
 *
 * This data type defines an object that functionas as an attribute.
 */
const kTYPE_ATTRIBUTE = ':type:attribute';

/**
 * <h3>Property.</h3><p />
 *
 * This data type defines an object that functionas as a property.
 */
const kTYPE_PROPERTY = ':type:property';


?>
