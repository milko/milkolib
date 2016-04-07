<?php

/**
 * Initialise terms
 *
 * This script can be used to initialise terms.
 *
 *	@package	Batch
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		07/04/2016
 */


/*=======================================================================================
 *																						*
 *										FUNCTIONS										*
 *																						*
 *======================================================================================*/



/*===================================================================================
 *	loadTerms																		*
 *==================================================================================*/

/**
 * <h4>Load type terms.</h4>
 *
 * This method will load the type terms into the provided collection
 *
 * @param \Milko\PHPLib\Collection	$theCollection	Terms collection.
 */
function loadTerms( \Milko\PHPLib\Collection $theCollection )
{
	//
	// Create default namespace.
	//
	$ns =
		$theCollection->Insert(
			[ kTAG_LID => '', kTAG_GID => '',
				kTAG_NAME => [ 'en' => 'Default namespace' ],
				kTAG_DESCRIPTION => 'This namespace groups all default or built-in terms of the ontology, these are the elements that will be used to build the ontology itself.' ]
		);

	//
	// Create type namespaces.
	//
	$ns_type =
		$theCollection->Insert(
			[ kTAG_NS => $ns, kTAG_LID => 'type', kTAG_GID => ':type',
				kTAG_NAME => [ 'en' => 'Data type' ],
				kTAG_DESCRIPTION => 'The type describes the nature or composition of an object.' ]
		);
	$ns_kind =
		$theCollection->Insert(
			[ kTAG_NS => $ns, kTAG_LID => 'kind', kTAG_GID => ':kind',
				kTAG_NAME => [ 'en' => 'Data kind' ],
				kTAG_DESCRIPTION => 'The kind describes the function or context of an object.' ]
		);
	$ns_predicate =
		$theCollection->Insert(
			[ kTAG_NS => $ns, kTAG_LID => 'predicate', kTAG_GID => ':predicate',
				kTAG_NAME => [ 'en' => 'Kind' ],
				kTAG_DESCRIPTION => 'A predicate qualifies a directed graph relationship.' ]
		);

	//
	// Load primitive types.
	//
	$theCollection->Insert(
		[ kTAG_NS => $ns_type, kTAG_LID => 'mixed', kTAG_GID => kTYPE_MIXED,
			kTAG_NAME => [ 'en' => 'Mixed' ],
			kTAG_DESCRIPTION => 'A mixed data type indicates that the referred property may take any data type.' ]
	);
	$ns_string =
		$theCollection->Insert(
			[ kTAG_NS => $ns_type, kTAG_LID => 'string', kTAG_GID => kTYPE_STRING,
				kTAG_NAME => [ 'en' => 'String' ],
				kTAG_DESCRIPTION => 'A string data type indicates that the referred property may hold UNICODE characters, this type does not include binary data.' ]
	);
	$theCollection->Insert(
		[ kTAG_NS => $ns_type, kTAG_LID => 'int', kTAG_GID => kTYPE_INT,
			kTAG_NAME => [ 'en' => 'Integer' ],
			kTAG_DESCRIPTION => 'An integer data type indicates that the referred property may hold a 32 or 64 bit integral numeric values.' ]
	);
	$theCollection->Insert(
		[ kTAG_NS => $ns_type, kTAG_LID => 'float', kTAG_GID => kTYPE_FLOAT,
			kTAG_NAME => [ 'en' => 'Float' ],
			kTAG_DESCRIPTION => 'A float data type indicates that the referred property may hold a floating point number, also known as double or real. The precision of such value is not inferred, in general it will be a 32 or 64 bit real.' ]
	);
	$theCollection->Insert(
		[ kTAG_NS => $ns_type, kTAG_LID => 'bool', kTAG_GID => kTYPE_BOOLEAN,
			kTAG_NAME => [ 'en' => 'Boolean' ],
			kTAG_DESCRIPTION => 'Boolean values can take one of two states: on or true, or off or false.' ]
	);

	//
	// Load derived types.
	//
	$theCollection->Insert(
		[ kTAG_NS => $ns_string, kTAG_LID => 'url', kTAG_GID => kTYPE_URL,
			kTAG_NAME => [ 'en' => 'Link' ],
			kTAG_DESCRIPTION => 'A link data type indicates that the referred property is a string representing an URL which is an internet link or network address.' ]
	);
	$theCollection->Insert(
		[ kTAG_NS => $ns_string, kTAG_LID => 'date', kTAG_GID => kTYPE_STRING_DATE,
			kTAG_NAME => [ 'en' => 'String date' ],
			kTAG_DESCRIPTION => 'This type defines a date in which the day and month may be omitted, it is a string providing the date in <tt>YYYYMMDD</tt> format in which the day, or the day and month can be omitted. All digits must be provided. This type can be used as a range and sorted.' ]
	);
	$theCollection->Insert(
		[ kTAG_NS => $ns_string, kTAG_LID => 'lat', kTAG_GID => kTYPE_STRING_LAT,
			kTAG_NAME => [ 'en' => 'String latitude' ],
			kTAG_DESCRIPTION => 'This type defines a latitude expressed in <tt>HDDMMSS.SSSS</tt> where <tt>H</tt> is the hemisphere (<tt>N</tt> or <tt>S</tt>), <tt>DD</tt> is the degrees, <tt>MM</tt> is the minutes and <tt>SS.SSS</tt> represents the seconds as a floating point number or integer. You may omit the seconds or the seconds and minutes, all digits must be provided. The degrees must range between <tt>-90</tt> to lower than <tt>90</tt>, the minutes and seconds must range between  <tt>0</tt> to lower than <tt>60</tt>. This data type is useful to calculate the maximum error of a coordinate.' ]
	);
	$theCollection->Insert(
		[ kTAG_NS => $ns_string, kTAG_LID => 'lon', kTAG_GID => kTYPE_STRING_LON,
			kTAG_NAME => [ 'en' => 'String longitude' ],
			kTAG_DESCRIPTION => 'This type defines a longitude expressed in <tt>HDDDMMSS.SSSS</tt> where <tt>H</tt> is the hemisphere (<tt>E</tt> or <tt>W</tt>), <tt>DDD</tt> is the degrees, <tt>MM</tt> is the minutes and <tt>SS.SSS</tt> represents the seconds as a floating point number or integer. You may omit the seconds or the seconds and minutes, all digits must be provided. The degrees must range between <tt>-180</tt> to lower than <tt>180</tt>, the minutes and seconds must range between  <tt>0</tt> to lower than <tt>60</tt>. This data type is useful to calculate the maximum error of a coordinate.' ]
	);

	//
	// Load referential types.
	//
	$theCollection->Insert(
		[ kTAG_NS => $ns_type, kTAG_LID => 'ref', kTAG_GID => kTYPE_REF,
			kTAG_NAME => [ 'en' => 'Reference' ],
			kTAG_DESCRIPTION => 'This type indicates that the property references another object, the value will contain the name of the collection in which the reference object resides and the object key, the format in which this value is expressed depends on the specific database.' ]
	);
	$theCollection->Insert(
		[ kTAG_NS => $ns_type, kTAG_LID => 'ref-self', kTAG_GID => kTYPE_REF_SELF,
			kTAG_NAME => [ 'en' => 'Collection reference' ],
			kTAG_DESCRIPTION => 'This type indicates that the property references another object belonging to the same collection, the value will contain the key of the referenced object.' ]
	);
	$theCollection->Insert(
		[ kTAG_NS => $ns_type, kTAG_LID => 'ref-term', kTAG_GID => kTYPE_REF_TERM,
			kTAG_NAME => [ 'en' => 'Term reference' ],
			kTAG_DESCRIPTION => 'This type indicates that the property references a term object, the value will contain the key of the referenced term.' ]
	);

	//
	// Load localised types.
	//
	$theCollection->Insert(
		[ kTAG_NS => $ns_type, kTAG_LID => 'date', kTAG_GID => kTYPE_DATE,
			kTAG_NAME => [ 'en' => 'Date' ],
			kTAG_DESCRIPTION => 'Date in the native database format.' ]
	);
	$theCollection->Insert(
		[ kTAG_NS => $ns_type, kTAG_LID => 'time-stamp', kTAG_GID => kTYPE_TIMESTAMP,
			kTAG_NAME => [ 'en' => 'Timestamp' ],
			kTAG_DESCRIPTION => 'Time stamp in the native database format.' ]
	);

	//
	// Load categorical types.
	//
	$theCollection->Insert(
		[ kTAG_NS => $ns_type, kTAG_LID => 'enum', kTAG_GID => kTYPE_ENUM,
			kTAG_NAME => [ 'en' => 'Enumeration' ],
			kTAG_DESCRIPTION => 'An enumerated property may hold only one value selected from a controlled vocabulary, in general, the controlled vocabulary will be a set of terms and the selected value will be the term\'s global identifier.' ]
	);
	$theCollection->Insert(
		[ kTAG_NS => $ns_type, kTAG_LID => 'enum-set', kTAG_GID => kTYPE_ENUM_SET,
			kTAG_NAME => [ 'en' => 'Enumerated set' ],
			kTAG_DESCRIPTION => 'An enumerated set property may hold one or more unique values selected from a controlled vocabulary, in general, the controlled vocabulary will be a set of terms and the selected values will be the term\'s global identifiers.' ]
	);


} // loadTerms.


?>
