<?php

/**
 * tWrapper.php
 *
 * This file contains the definition of the {@link tWrapper} trait.
 */

namespace Milko\PHPLib;

/*=======================================================================================
 *																						*
 *									tWrapper.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Wrapper trait.</h4>
 *
 * This trait implements a data repository and ontology derived from the {@link Database}
 * class. It aggregates the functionality for implementing an ontology based data repository
 * stored in its inherited database.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		20/04/2016
 */
trait tWrapper
{
	/**
	 * <h4>Wrapper cache.</h4>
	 *
	 * This data member holds the <i>wrapper cache</i>, it is the memcached instance serving
	 * as global cache.
	 *
	 * @var \Memcached
	 */
	protected $mCache = NULL;




/*=======================================================================================
 *																						*
 *										MAGIC											*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	__construct																		*
	 *==================================================================================*/

	/**
	 * <h4>Instantiate class.</h4>
	 *
	 * We overload the inherited constructor to initialise the cache and, if necessary,
	 * initialise the data dictionary.
	 *
	 * @param Server				$theServer			Server.
	 * @param string				$theDatabase		Database name.
	 * @param array					$theOptions			Native driver options.
	 *
	 * @uses initCache()
	 * @uses initDataDictionary()
	 */
	public function __construct( Server $theServer, $theDatabase, $theOptions = NULL )
	{
		//
		// Call parent constructor.
		//
		parent::__construct( $theServer, $theDatabase, $theOptions );

		//
		// Open cache.
		//
		$this->initCache();

		//
		// Opena data dictionary.
		//
		$this->initDataDictionary();

	} // Constructor.



/*=======================================================================================
 *																						*
 *								PUBLIC DESCRIPTOR INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewDescriptorKey																*
	 *==================================================================================*/

	/**
	 * <h4>Return a new descriptor key.</h4>
	 *
	 * This method should return a new descriptor key, it should be called whenever a new
	 * {@link Descriptor} instance is inserted in the database.
	 *
	 * The method should return the current drscriptor serial counter value and increment it
	 * in the database, this means that you should call it only when needed.
	 *
	 * The method must be implemented in derived concrete classes.
	 *
	 * @return string				New descriptor key.
	 */
	abstract public function NewDescriptorKey();



/*=======================================================================================
 *																						*
 *							PROTECTED INITIALISATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	initCache																		*
	 *==================================================================================*/

	/**
	 * <h4>Initialise cache.</h4>
	 *
	 * This method will initialise the wrapper cache by connecting to the Memcached
	 * service.
	 *
	 * @throws \RuntimeException
	 */
	protected function initCache()
	{
		//
		// Init resource.
		//
		$this->mCache = new \Memcached( kSESSION_CACHE_ID );

		//
		// Init cache.
		//
		if( ! count( $this->mCache->getServerList() ) )
		{
			//
			// Set default server.
			//
			$result = $this->mCache->addServer( kSESSION_CACHE_HOST, kSESSION_CACHE_PORT );
			if( $result === FALSE )
				throw new \RuntimeException(
					$this->mCache->getResultMessage(),
					$this->mCache->getResultCode() );							// !@! ==>

		} // Not initialised.

	} // initCache.


	/*===================================================================================
	 *	initDataDictionary																*
	 *==================================================================================*/

	/**
	 * <h4>Initialise data dictionary.</h4>
	 *
	 * This method will erase all existing data and load the data dictionary.
	 *
	 * @throws \RuntimeException
	 *
	 * @uses NewTermsCollection()
	 * @uses NewResourcesCollection()
	 * @uses NewDescriptorsCollection()
	 * @uses eraseDataDictionary()
	 * @uses buildDataDictionary()
	 */
	protected function initDataDictionary()
	{
		//
		// Create or get default collections.
		//
		$terms = $this->NewTermsCollection();
		$resources = $this->NewResourcesCollection();
		$descriptors = $this->NewDescriptorsCollection();

		//
		// Build data dictionary.
		//
		if( ! $resources->Count() )
		{
			//
			// Erase database and cache.
			//
			$this->eraseDataDictionary();

			//
			// Build data dictionary.
			//
			$this->buildDataDictionary();

		} // Empty resources collection.

	} // initDataDictionary.


	/*===================================================================================
	 *	eraseDataDictionary																*
	 *==================================================================================*/

	/**
	 * <h4>Erase data dictionary.</h4>
	 *
	 * This method will erase the current database and invalidate the current cache.
	 *
	 * @throws \RuntimeException
	 *
	 * @uses ForgetWorkingCollections()
	 * @uses databaseName()
	 * @uses databaseCreate()
	 */
	protected function eraseDataDictionary()
	{
		//
		// Reset cache.
		//
		if( $this->mCache->flush() === FALSE )
			throw new \RuntimeException(
				$this->mCache->getResultMessage(),
				$this->mCache->getResultCode() );								// !@! ==>

		//
		// Erase database.
		//
		$server = $this->Server();
		$name = $this->databaseName();
		$this->Drop();
		$this->mConnection = $this->databaseCreate( $name );

		//
		// Clear working collections.
		//
		$this->ForgetWorkingCollections();

	} // eraseDataDictionary.


	/*===================================================================================
	 *	buildDataDictionary																*
	 *==================================================================================*/

	/**
	 * <h4>Build data dictionary.</h4>
	 *
	 * This method will load the data dictionary with the base data.
	 *
	 * @uses NewTermsCollection()
	 * @uses NewResourcesCollection()
	 * @uses NewDescriptorsCollection()
	 * @uses initResources()
	 * @uses initTerms()
	 */
	protected function buildDataDictionary()
	{
		//
		// Create default collections.
		//
		$terms = $this->NewTermsCollection();
		$resources = $this->NewResourcesCollection();
		$descriptors = $this->NewDescriptorsCollection();

		//
		// Initialise resources.
		//
		$this->initResources( $resources );

		//
		// Initialise terms.
		//
		$this->initTerms( $terms );

	} // buildDataDictionary.


	/*===================================================================================
	 *	initResources																	*
	 *==================================================================================*/

	/**
	 * <h4>Initialise resources.</h4>
	 *
	 * This method will load the default resources data.
	 *
	 * @param Collection			$theResources		Resources collection.
	 */
	protected function initResources( Collection $theResources )
	{
		//
		// Initialise descriptors serial counter.
		//
		$theResources->Insert(
			[ $theResources->KeyOffset() => kTOKEN_SERIAL_DESCRIPTOR,
			  kTOKEN_SERIAL_OFFSET => 1 ] );

	} // initResources.


	/*===================================================================================
	 *	initTerms																		*
	 *==================================================================================*/

	/**
	 * <h4>Initialise resources.</h4>
	 *
	 * This method will load the default resources data.
	 *
	 * @param Collection			$theTerms			Terms collection.
	 */
	protected function initTerms( Collection $theTerms )
	{
		//
		// Init local storage.
		//
		$key = $theTerms->KeyOffset();

		//
		// Create default namespace.
		//
		$term = new Term( $theTerms, [
			kTAG_LID => '', kTAG_GID => '',
			kTAG_NAME => [ 'en' => 'Default namespace' ],
			kTAG_DESCRIPTION => [ 'en' =>
				'This namespace groups all default or built-in terms of the ontology, ' .
				'these are the elements that will be used to build the ontology itself.' ] ]
		);
		$term->Store();
		$ns = $term[ $key ];

		//
		// Create type namespaces.
		//
		$nsp = $ns;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'type',
				kTAG_NAME => [ 'en' => 'Data type' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The type describes the nature or composition of an object.' ] ]
		);
		$term->Store();
		$ns_type = $term[ $key ];

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'kind',
				kTAG_NAME => [ 'en' => 'Data kind' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The kind describes the function or context of an object.' ] ]
		);
		$term->Store();
		$ns_kind = $term[ $key ];

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'predicate',
				kTAG_NAME => [ 'en' => 'Predicate' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'A predicate qualifies a directed graph relationship.' ] ]
		);
		$term->Store();
		$ns_predicate = $term[ $key ];

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'private',
				kTAG_NAME => [ 'en' => 'Private' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This term qualifies a resource as not public or freely accessible.' ] ]
		);
		$term->Store();
		$ns_private = $term[ $key ];

		//
		// Load primitive types.
		//
		$nsp = $ns_type;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'mixed', kTAG_SYMBOL => 'kTYPE_MIXED',
				kTAG_NAME => [ 'en' => 'Mixed' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'A mixed data type indicates that the referred property may take any ' .
					'data type.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'string', kTAG_SYMBOL => 'kTYPE_STRING',
				kTAG_NAME => [ 'en' => 'String' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'A string data type indicates that the referred property may hold ' .
					'UNICODE characters, this type does not include binary data.' ] ]
		);
		$term->Store();
		$ns_string = $term[ $key ];

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'int', kTAG_SYMBOL => 'kTYPE_INT',
				kTAG_NAME => [ 'en' => 'Integer' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'An integer data type indicates that the referred property may hold ' .
					'a 32 or 64 bit integral numeric values.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'float', kTAG_SYMBOL => 'kTYPE_FLOAT',
				kTAG_NAME => [ 'en' => 'Float' ],
				kTAG_DESCRIPTION => [ 'en' => 'A float data type indicates that the ' .
					'referred property may hold a floating point number, also known as ' .
					'double or real. The precision of such value is not inferred, in ' .
					'general it will be a 32 or 64 bit real.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'bool', kTAG_SYMBOL => 'kTYPE_BOOLEAN',
				kTAG_NAME => [ 'en' => 'Boolean' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'Boolean values can take one of two states: on or true, or off or ' .
					'false.' ] ]
		);
		$term->Store();

		//
		// Load derived types.
		//
		$nsp = $ns_string;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'url', kTAG_SYMBOL => 'kTYPE_URL',
				kTAG_NAME => [ 'en' => 'Link' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'A link data type indicates that the referred property is a string ' .
					'representing an URL which is an internet link or network address.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'date', kTAG_SYMBOL => 'kTYPE_STRING_DATE',
				kTAG_NAME => [ 'en' => 'String date' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This type defines a date in which the day and month may be omitted, ' .
					'it is a string providing the date in <tt>YYYYMMDD</tt> format in ' .
					'which the day, or the day and month can be omitted. All digits must ' .
					'be provided. This type can be used as a range and sorted.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'lat', kTAG_SYMBOL => 'kTYPE_STRING_LAT',
				kTAG_NAME => [ 'en' => 'String latitude' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This type defines a latitude expressed in <tt>HDDMMSS.SSSS</tt> ' .
					'where <tt>H</tt> is the hemisphere (<tt>N</tt> or <tt>S</tt>), ' .
					'<tt>DD</tt> is the degrees, <tt>MM</tt> is the minutes and ' .
					'<tt>SS.SSS</tt> represents the seconds as a floating point number ' .
					'or integer. You may omit the seconds or the seconds and minutes, ' .
					'all digits must be provided. The degrees must range between ' .
					'<tt>-90</tt> to lower than <tt>90</tt>, the minutes and seconds ' .
					'must range between  <tt>0</tt> to lower than <tt>60</tt>. This data ' .
					'type is useful to calculate the maximum error of a coordinate.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'lon', kTAG_SYMBOL => 'kTYPE_STRING_LON',
				kTAG_NAME => [ 'en' => 'String longitude' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This type defines a longitude expressed in <tt>HDDDMMSS.SSSS</tt> ' .
					'where <tt>H</tt> is the hemisphere (<tt>E</tt> or <tt>W</tt>), ' .
					'<tt>DDD</tt> is the degrees, <tt>MM</tt> is the minutes and ' .
					'<tt>SS.SSS</tt> represents the seconds as a floating point number ' .
					'or integer. You may omit the seconds or the seconds and minutes, ' .
					'all digits must be provided. The degrees must range between ' .
					'<tt>-180</tt> to lower than <tt>180</tt>, the minutes and seconds ' .
					'must range between  <tt>0</tt> to lower than <tt>60</tt>. This data ' .
					'type is useful to calculate the maximum error of a coordinate.' ] ]
		);
		$term->Store();

		//
		// Load referential types.
		//
		$nsp = $ns_type;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'ref', kTAG_SYMBOL => 'kTYPE_REF',
				kTAG_NAME => [ 'en' => 'Reference' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This type indicates that the property references another object, ' .
					'the value will contain the name of the collection in which the ' .
					'reference object resides and the object key, the format in which ' .
					'this value is expressed depends on the specific database.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'ref-self', kTAG_SYMBOL => 'kTYPE_REF_SELF',
				kTAG_NAME => [ 'en' => 'Collection reference' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This type indicates that the property references another object ' .
					'belonging to the same collection, the value will contain the key of ' .
					'the referenced object.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'ref-term', kTAG_SYMBOL => 'kTYPE_REF_TERM',
				kTAG_NAME => [ 'en' => 'Term reference' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This type indicates that the property references a term object, the ' .
					'value will contain the key of the referenced term.' ] ]
		);
		$term->Store();

		//
		// Load localised types.
		//
		$nsp = $ns_type;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'date', kTAG_SYMBOL => 'kTYPE_DATE',
				kTAG_NAME => [ 'en' => 'Date' ],
				kTAG_DESCRIPTION => [ 'en' => 'Date in the native database format.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'time-stamp', kTAG_SYMBOL => 'kTYPE_TIMESTAMP',
				kTAG_NAME => [ 'en' => 'Timestamp' ],
				kTAG_DESCRIPTION => [ 'en' => 'Time stamp in the native database format.' ] ]
		);
		$term->Store();

		//
		// Load categorical types.
		//
		$nsp = $ns_type;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'enum', kTAG_SYMBOL => 'kTYPE_ENUM',
				kTAG_NAME => [ 'en' => 'Enumeration' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'An enumerated property may hold only one value selected from a ' .
					'controlled vocabulary, in general, the controlled vocabulary will ' .
					'be a set of terms and the selected value will be the term\'s global ' .
					'identifier.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'enum-set', kTAG_SYMBOL => 'kTYPE_ENUM_SET',
				kTAG_NAME => [ 'en' => 'Enumerated set' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'An enumerated set property may hold one or more unique values ' .
					'selected from a controlled vocabulary, in general, the controlled ' .
					'vocabulary will be a set of terms and the selected values will be ' .
					'the term\'s global identifiers.' ] ]
		);
		$term->Store();

		//
		// Load structured types.
		//
		$nsp = $ns_type;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'array', kTAG_SYMBOL => 'kTYPE_ARRAY',
				kTAG_NAME => [ 'en' => 'List' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This data type defines a list of elements whose value type is not ' .
					'inferred. This data type usually applies to arrays.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'struct', kTAG_SYMBOL => 'kTYPE_STRUCT',
				kTAG_NAME => [ 'en' => 'Structure' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This data type defines a structure or an associative array in which ' .
					'the element key is represented by an indicator identifier.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'shape', kTAG_SYMBOL => 'kTYPE_SHAPE',
				kTAG_NAME => [ 'en' => 'Shape' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This data type defines a geometric shape  which is expressed as a ' .
					'GeoJSON construct, it is an array composed by two key/value ' .
					'elements: <ul><li><tt>type</tt>: The element indexed by this string ' .
					'contains the code indicating the type of the shape, these are the ' .
					'supported values: <ul><li><tt>Point</tt>: A point. ' .
					'<li><tt>LineString</tt>: A list of points. <li><tt>Polygon</tt>: A ' .
					'polygon, including its rings. </ul><li><tt>coordinates</tt>: The ' .
					'element indexed by this string contains the geometry of the shape, ' .
					'which has a structure which depends on the shape type: ' .
					'<ul><li><em>Point</em>: The point is an array of two floating point ' .
					'numbers, respectively the longitude and latitude. ' .
					'<li><em>LineString</em>: A line string is an array of points ' .
					'expressed in the <tt>Point</tt> geometry (longitude and latitude). ' .
					'<li><em>Polygon</em>: A polygon is a list of rings whose geometry ' .
					'is like the <tt>LineString</tt> geometry, except that the first and ' .
					'last point must match. The first ring represents the outer boundary ' .
					'of the polygon, the other rings are optional and represent holes in ' .
					'the polygon.</ul></ul>' ] ]
		);
		$term->Store();

		$nsp = $ns_string;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'lang', kTAG_SYMBOL => 'kTYPE_LANG_STRING',
				kTAG_NAME => [ 'en' => 'Language string' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This data type defines a list of strings expressed in different ' .
					'languages. The list elements are composed by key/value pairs, the ' .
					'key is expressed as the language code and the value is a single ' .
					'string in that language.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'langs', kTAG_SYMBOL => 'kTYPE_LANG_STRINGS',
				kTAG_NAME => [ 'en' => 'Language strings' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This data type defines a list of strings expressed in different ' .
					'languages. The list elements are composed by key/value pairs, the ' .
					'key is expressed as the language code and the value is a list of ' .
					'strings in that language.' ] ]
		);
		$term->Store();

		//
		// Load functional types.
		//
		$nsp = $ns_type;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'attribute', kTAG_SYMBOL => 'kTYPE_ATTRIBUTE',
				kTAG_NAME => [ 'en' => 'Attribute' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This data type defines an object that functionas as an attribute.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'property', kTAG_SYMBOL => 'kTYPE_PROPERTY',
				kTAG_NAME => [ 'en' => 'Property' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This data type defines an object that functionas as a property.' ] ]
		);
		$term->Store();

		//
		// Load domain kinds.
		//
		$nsp = $ns_kind;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'categorical', kTAG_SYMBOL => 'kKIND_CATEGORICAL',
				kTAG_NAME => [ 'en' => 'Categorical' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the property can take on one or more of a ' .
					'limited, and usually fixed, number of possible values. In general, ' .
					'properties which take their values from an enumerated set of ' .
					'choices are of this kind.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'quantitative', kTAG_SYMBOL => 'kKIND_QUANTITATIVE',
				kTAG_NAME => [ 'en' => 'Quantitative' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the property is one whose type of ' .
					'information is based on quantities or quantifiable data which is ' .
					'continuous. In general numerical values which can be aggregated in ' .
					'ranges fall under this category.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'discrete', kTAG_SYMBOL => 'kKIND_DISCRETE',
				kTAG_NAME => [ 'en' => 'Discrete' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the property is one which may take an ' .
					'indefinite number of values, which differentiates it from a ' .
					'categorical property, and whose values are not continuous, which ' .
					'differentiates it from a quantitative property.' ] ]
		);
		$term->Store();

		//
		// Load usage kinds.
		//
		$nsp = $ns_kind;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'recommended', kTAG_SYMBOL => 'kKIND_RECOMMENDED',
				kTAG_NAME => [ 'en' => 'Recommended' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the property is recommended, encouraged or ' .
					'important, but not necessarily required or mandatory.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'required', kTAG_SYMBOL => 'kKIND_REQUIRED',
				kTAG_NAME => [ 'en' => 'Required' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the property is required or mandatory.' ] ]
		);
		$term->Store();

		$nsp = $ns_private;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'display', kTAG_SYMBOL => 'kKIND_PRIVATE_DISPLAY',
				kTAG_NAME => [ 'en' => 'Private display' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the data property should not be displayed ' .
					'to clients.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'search', kTAG_SYMBOL => 'kKIND_PRIVATE_SEARCH',
				kTAG_NAME => [ 'en' => 'Private search' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the data property should not be available ' .
					'to clients for searching.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'modify', kTAG_SYMBOL => 'kKIND_PRIVATE_MODIFY',
				kTAG_NAME => [ 'en' => 'Private modify' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the data property is reserved by the ' .
					'object, which means that it is automatically managed by the class ' .
					'and should not be explicitly set or modified by clients.' ] ]
		);
		$term->Store();

		//
		// Load cardinality kinds.
		//
		$nsp = $ns_kind;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'list', kTAG_SYMBOL => 'kKIND_LIST',
				kTAG_NAME => [ 'en' => 'List' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the property is a list of values, each of ' .
					'the defined data type.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'summary', kTAG_SYMBOL => 'kKIND_SUMMARY',
				kTAG_NAME => [ 'en' => 'Summary' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the property can be used to group results ' .
					'in a summary.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'lookup', kTAG_SYMBOL => 'kKIND_LOOKUP',
				kTAG_NAME => [ 'en' => 'Lookup' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the property can be searched upon using ' .
					'auto-complete.' ] ]
		);
		$term->Store();

		//
		// Load vertex kinds.
		//
		$nsp = $ns_kind;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'root', kTAG_SYMBOL => 'kKIND_ROOT',
				kTAG_NAME => [ 'en' => 'Root' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'An entry point of a structure. Items of this kind represents a door ' .
					'or entry point of a structure, they can be either the element from ' .
					'which the whole structure originates from, or an element that ' .
					'represents a specific thematic entry point.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'type', kTAG_SYMBOL => 'kKIND_TYPE',
				kTAG_NAME => [ 'en' => 'Type' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'A type or definition. Items of this kind are used as a type ' .
					'definition or to define controlled vocabularies, they are used as ' .
					'proxies to the structure they hold. When traversing an enumerated ' .
					'set tree, elements of this kind will not be either displayed or ' .
					'made available for setting.' ] ]
		);
		$term->Store();

		//
		// Load aggregation predicates.
		//
		$nsp = $ns_predicate;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'SUBCLASS-OF', kTAG_SYMBOL => 'kPREDICATE_SUBCLASS_OF',
				kTAG_NAME => [ 'en' => 'Subclass of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship is a ' .
					'subclass of the object of the relationship, in other words, the ' .
					'subject is derived from the object.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'SUBRANK-OF', kTAG_SYMBOL => 'kPREDICATE_SUBRANK_OF',
				kTAG_NAME => [ 'en' => 'Subrank of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship ' .
					'belongs to the next lowest rank than the object of the ' .
					'relationship.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'SUBSET-OF', kTAG_SYMBOL => 'kPREDICATE_SUBSET_OF',
				kTAG_NAME => [ 'en' => 'Subset of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship ' .
					'represents a subset of the object of the relationship, in other ' .
					'words, the subject is contained by the object.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'PART-OF', kTAG_SYMBOL => 'kPREDICATE_PART_OF',
				kTAG_NAME => [ 'en' => 'Part of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship is a ' .
					'part or a component of the object of the relationship.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'TYPE-OF', kTAG_SYMBOL => 'kPREDICATE_TYPE_OF',
				kTAG_NAME => [ 'en' => 'Type of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship ' .
					'represents the type of the object of the relationship. This ' .
					'predicate can also be as a group and a proxy: it may define a ' .
					'formal group by collecting all elements that relate to it, and it ' .
					'acts as a proxy, because this relationship type implies that all ' .
					'the elements related to the group will relate directly to the ' .
					'object of the current relationship.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'FUNCTION-OF', kTAG_SYMBOL => 'kPREDICATE_FUNCTION_OF',
				kTAG_NAME => [ 'en' => 'Function of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship ' .
					'represents a function or trait group of the object of the ' .
					'relationship, in other words, the subject is a group of functions ' .
					'that can be applied to the object.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'COLLECTION-OF', kTAG_SYMBOL => 'kPREDICATE_COLLECTION_OF',
				kTAG_NAME => [ 'en' => 'Collection of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship is a ' .
					'collection belonging to the object of the relationship. This ' .
					'predicate is similar to the attribute of predicate, except that in ' .
					'the latter case the subject is a scalar item of the object, while, ' .
					'in this case, the subject is a template for the collection of ' .
					'elements that belong to the object.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'PROPERTY-OF', kTAG_SYMBOL => 'kPREDICATE_PROPERTY_OF',
				kTAG_NAME => [ 'en' => 'Property of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship is a ' .
					'property of the object of the relationship, this means that the ' .
					'subject of the relationship is a feature.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'ATTRIBUTE-OF', kTAG_SYMBOL => 'kPREDICATE_ATTRIBUTE_OF',
				kTAG_NAME => [ 'en' => 'Attribute of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship is ' .
					'an attribute of the object of the relationship, this means that ' .
					'the subject of the relationship belongs to the set of attributes ' .
					'of the object of the relationship.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'ENUM-OF', kTAG_SYMBOL => 'kPREDICATE_ENUM_OF',
				kTAG_NAME => [ 'en' => 'Enumeration of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate relates vertex elements of an enumerated set, it ' .
					'indicates that the subject of the relationship is an enumerated set ' .
					'item instance. If the object of the relationship is also an ' .
					'enumerated set item instance, this means that the subject is a ' .
					'subset of the object.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'INSTANCE-OF', kTAG_SYMBOL => 'kPREDICATE_INSTANCE_OF',
				kTAG_NAME => [ 'en' => 'Instance of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate relates a type to its instance, it indicates that ' .
					'the object of the relationship is an instance of the subject of the ' .
					'relationship.' ] ]
		);
		$term->Store();

		//
		// Load preference predicates.
		//
		$nsp = $ns_predicate;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'PREFERRED', kTAG_SYMBOL => 'kPREDICATE_PREFERRED',
				kTAG_NAME => [ 'en' => 'Preferred' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the object of the relationship is the ' .
					'preferred choice, in other words, if possible, one should use the ' .
					'object of the relationship in place of the subject. This predicate ' .
					'will be used in general by obsolete or deprecated items. The scope ' .
					'of this predicate is similar to the <em>valid</em> predicate, ' .
					'except that in this case the use of the subject of the relationship ' .
					'is only deprecated, while in the {@link kPREDICATE_VALID} predicate ' .
					'it is not valid.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'VALID', kTAG_SYMBOL => 'kPREDICATE_VALID',
				kTAG_NAME => [ 'en' => 'Valid' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the object of the relationship is the ' .
					'valid choice, in other words, the subject of the relationship is ' .
					'obsolete or not valid, and one should use the object of the ' .
					'relationship in its place. This predicate will be used in general ' .
					'to store the obsolete or deprecated versions. The scope of this ' .
					'predicate is similar to the {@link kPREDICATE_PREFERRED} predicate, ' .
					'except that in this case the use of the subject of the relationship ' .
					'is invalid, while in the {@link kPREDICATE_PREFERRED} predicate it ' .
					'is only deprecated.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'LEGACY', kTAG_SYMBOL => 'kPREDICATE_LEGACY',
				kTAG_NAME => [ 'en' => 'Legacy' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the object of the relationship is the ' .
					'former or legacy version, in other words, the object of the ' .
					'relationship is obsolete or not in use. This predicate will be used ' .
					'in general to record historical information. The scope of this ' .
					'predicate is similar to the <em>preferred</em> and <em>valid</em> ' .
					'predicates, except that in this case the legacy choice might not be ' .
					'invalid nor deprecated: it only means that the object of the ' .
					'relationship was used in the past and the subject of the ' .
					'relationship is currently used in its place.' ] ]
		);
		$term->Store();

		//
		// Load reference predicates.
		//
		$nsp = $ns_predicate;
		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'XREF', kTAG_SYMBOL => 'kPREDICATE_XREF',
				kTAG_NAME => [ 'en' => 'Cross-reference' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship is ' .
					'related to the object of the relationship. This predicate does not ' .
					'represent any specific type of relationship, other than what the ' .
					'edge object attributes may indicate. The scope of this predicate is ' .
					'similar to the {@link kPREDICATE_XREF-EXACT} predicate, except that ' .
					'the latter indicates that the object of the relationship can be ' .
					'used in place of the subject, while in this predicate this is not ' .
					'necessarily true.' ] ]
		);
		$term->Store();

		$term = new Term( $theTerms, [ kTAG_NS => $nsp,
				kTAG_LID => 'XREF-EXACT', kTAG_SYMBOL => 'kPREDICATE_XREF_EXACT',
				kTAG_NAME => [ 'en' => 'Exact cross-reference' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the object of the relationship can be ' .
					'used in place of the subject of the relationship. If the predicate ' .
					'is found in both directions, one could say that the two vertices ' .
					'are identical, except for their formal representation. The scope of ' .
					'this predicate is similar to the <em>cross-reference</em> ' .
					'predicate, except that the latter only indicates that both ' .
					'vertices are related, this predicate indicates that they are ' .
					'interchangeable.' ] ]
		);
		$term->Store();

	} // initTerms.



} // trait tWrapper.


?>
