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
 * <h4>Mixed.</h4><p />
 *
 * A mixed data type indicates that the referred property may take any data type.
 */
const kTYPE_MIXED = ':type:mixed';

/**
 * <h4>String.</h4><p />
 *
 * A string data type indicates that the referred property may hold UNICODE characters, this
 * type does not include binary data.
 */
const kTYPE_STRING = ':type:string';

/**
 * <h4>Integer.</h4><p />
 *
 * An integer data type indicates that the referred property may hold a 32 or 64 bit
 * integral numeric values.
 */
const kTYPE_INT = ':type:int';

/**
 * <h4>Float.</h4><p />
 *
 * A float data type indicates that the referred property may hold a floating point number,
 * also known as double or real. The precision of such value is not inferred, in general it
 * will be a 32 or 64 bit real.
 */
const kTYPE_FLOAT = ':type:float';

/**
 * <h4>Boolean.</h4><p />
 *
 * Boolean values can take one of two states: on or true, or off or false.
 */
const kTYPE_BOOLEAN = ':type:bool';

/*=======================================================================================
 *	DERIVED TYPES																		*
 *======================================================================================*/

/**
 * <h4>Link.</h4><p />
 *
 * A link data type indicates that the referred property is a string representing an URL
 * which is an internet link or network address.
 */
const kTYPE_URL = ':type:string:url';

/**
 * <h4>String date.</h4><p />
 *
 * This type defines a date in which the day and month may be omitted, it is a string
 * providing the date in <tt>YYYYMMDD</tt> format in which the day, or the day and month can
 * be omitted. All digits must be provided. This type can be used as a range and sorted.
 */
const kTYPE_STRING_DATE = ':type:string:date';

/**
 * <h4>String latitude.</h4><p />
 *
 * This type defines a latitude expressed in <tt>DD˚MM'SS.SSS"H</tt> where <tt>H</tt> is the
 * hemisphere (<tt>N</tt> or <tt>S</tt>), <tt>DD</tt> is the degrees, <tt>MM</tt> is the
 * minutes and <tt>SS.SSS</tt> represents the seconds as a floating point number or integer.
 * You may omit the seconds or the seconds and minutes, all digits must be provided. The
 * degrees must range between <tt>0</tt> to lower than <tt>90</tt>, the minutes and
 * seconds must range between  <tt>0</tt> to lower than <tt>60</tt>. This data type is
 * useful to calculate the maximum error of a coordinate.
 */
const kTYPE_STRING_LAT = ':type:string:lat';

/**
 * <h4>String longitude.</h4><p />
 *
 * This type defines a longitude expressed in <tt>DDD˚MM'SS.SSS"H</tt> where <tt>H</tt> is
 * the hemisphere (<tt>E</tt> or <tt>W</tt>), <tt>DDD</tt> is the degrees, <tt>MM</tt> is
 * the minutes and <tt>SS.SSS</tt> represents the seconds as a floating point number or
 * integer. You may omit the seconds or the seconds and minutes, all digits must be
 * provided. The degrees must range between <tt>0</tt> to lower than <tt>180</tt>, the
 * minutes and seconds must range between  <tt>0</tt> to lower than <tt>60</tt>. This data
 * type is useful to calculate the maximum error of a coordinate.
 */
const kTYPE_STRING_LON = ':type:string:lon';

/*=======================================================================================
 *	REFERENTIAL TYPES																	*
 *======================================================================================*/

/**
 * <h4>Object reference.</h4><p />
 *
 * This type indicates that the property references another object, the value will contain
 * the name of the collection in which the reference object resides and the object key, the
 * format in which this value is expressed depends on the specific database.
 */
const kTYPE_REF = ':type:ref';

/**
 * <h4>Term reference.</h4><p />
 *
 * This type indicates that the property references a term object, the value will contain
 * the key of the referenced term.
 */
const kTYPE_REF_TERM = ':type:ref-term';

/*=======================================================================================
 *	LOCALISED TYPES																		*
 *======================================================================================*/

/**
 * <h4>Date.</h4><p />
 *
 * Date in the native database format.
 */
const kTYPE_DATE = ':type:date';

/**
 * <h4>Timestamp.</h4><p />
 *
 * Time stamp in the native database format.
 */
const kTYPE_TIMESTAMP = ':type:time-stamp';

/*=======================================================================================
 *	CATEGORICAL TYPES																	*
 *======================================================================================*/

/**
 * <h4>Enumeration.</h4><p />
 *
 * An enumerated property may hold only one value selected from a controlled vocabulary, in
 * general, the controlled vocabulary will be a set of terms and the selected value will be
 * the term's global identifier ({@link kTAG_GID}).
 */
const kTYPE_ENUM = ':type:enum';

/**
 * <h4>Enumerated set.</h4><p />
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
 * <h4>List.</h4><p />
 *
 * This data type defines a list of elements whose value type is not inferred. This data
 * type usually applies to arrays.
 */
const kTYPE_ARRAY = ':type:array';

/**
 * <h4>Structure.</h4><p />
 *
 * This data type defines a structure or an associative array in which the element key is
 * represented by an indicator identifier.
 */
const kTYPE_STRUCT = ':type:struct';

/**
 * <h4>Shape.</h4><p />
 *
 * This data type defines a geometric shape which is expressed as a GeoJSON construct, it is
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
 * <h4>Language string.</h4><p />
 *
 * This data type defines a list of strings expressed in different languages. The list
 * elements are composed by key/value pairs, the key is expressed as the language code and
 * the value is a single string in that language.
 */
const kTYPE_LANG_STRING = ':type:string:lang';

/**
 * <h4>Language strings.</h4><p />
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
 * <h4>Type.</h4><p />
 *
 * The <em>nature</em> of an object.
 */
const kTYPE_TYPE = ':type';

/**
 * <h4>Kind.</h4><p />
 *
 * The <em>function</em> or <em>context</em> of an object.
 */
const kTYPE_KIND = ':kind';

/**
 * <h4>Attribute.</h4><p />
 *
 * This data type defines an object that functionas as an attribute.
 */
const kTYPE_ATTRIBUTE = ':type:attribute';

/**
 * <h4>Property.</h4><p />
 *
 * This data type defines an object that functionas as a property.
 */
const kTYPE_PROPERTY = ':type:property';


?>
