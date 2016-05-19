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


	/*===================================================================================
	 *	SetDescriptor																	*
	 *==================================================================================*/

	/**
	 * <h4>Set a descriptor in the cache.</h4>
	 *
	 * This method will set the provided descriptor(s) in the cache, the method expects a
	 * {@link Descriptor} instance or an array of descriptors; on any error the method will
	 * raise an exception.
	 *
	 * @param Descriptor|array		$theDescriptor		Descriptor(s).
	 */
	public function SetDescriptor( $theDescriptor )
	{
		//
		// Convert to array.
		//
		if( ! is_array( $theDescriptor ) )
			$theDescriptor = [ $theDescriptor ];

		//
		// Iterate descriptors.
		//
		foreach( $theDescriptor as $descriptor )
			$this->CacheDescriptor( $descriptor );

	} // SetDescriptor.


	/*===================================================================================
	 *	GetDescriptor																	*
	 *==================================================================================*/

	/**
	 * <h4>Return a descriptor from the cache.</h4>
	 *
	 * This method will return the descriptor matching the provided key from the cache, the
	 * method expects the key to be either a string, in which case the method will return a
	 * scalar, or an array, in which case the method will return the list of descriptors.
	 *
	 * If the descriptor cannot be found, the method will return <tt>NULL</tt>; when
	 * providing a list of keys, the methos will omit missing descriptors.
	 *
	 * @param string|array			$theKey				Key(s).
	 * @return Descriptor|array		Found descriptor(s).
	 */
	public function GetDescriptor( $theKey )
	{
		//
		// Handle list of keys.
		//
		if( is_array( $theKey ) )
		{
			//
			// Get set.
			//
			$list = $this->mCache->getMulti( $theKey );
			if( $list !== FALSE )
				return $list;														// ==>

			throw new \RuntimeException(
				$this->mCache->getResultMessage(),
				$this->mCache->getResultCode() );								// !@! ==>
		}

		//
		// Get descriptor.
		//
		$descriptor = $this->mCache->get( $theKey );
		if( $descriptor !== FALSE )
			return $descriptor;														// ==>

		//
		// Handle not found.
		//
		if( $this->mCache->getResultCode() == \Memcached::RES_NOTFOUND )
			return NULL;															// ==>

		throw new \RuntimeException(
			$this->mCache->getResultMessage(),
			$this->mCache->getResultCode() );									// !@! ==>

	} // GetDescriptor.


	/*===================================================================================
	 *	DelDescriptor																	*
	 *==================================================================================*/

	/**
	 * <h4>Remove a descriptor from the cache.</h4>
	 *
	 * This method will remove the descriptor matching the provided key from the cache, the
	 * method expects the key to be either a string or an array of descriptor keys.
	 *
	 * @param string|array			$theKey				Key(s).
	 */
	public function DelDescriptor( $theKey )
	{
		//
		// Handle list of keys.
		//
		if( is_array( $theKey ) )
		{
			//
			// Delete set.
			//
			$result = $this->mCache->deleteMulti( $theKey );
			if( $result === FALSE )
				throw new \RuntimeException(
					$this->mCache->getResultMessage(),
					$this->mCache->getResultCode() );							// !@! ==>
		}

		//
		// Handle single key.
		//
		else
		{
			//
			// Delete key.
			//
			$result = $this->mCache->delete( $theKey );
			if( $result === FALSE )
				throw new \RuntimeException(
					$this->mCache->getResultMessage(),
					$this->mCache->getResultCode() );							// !@! ==>
		}

	} // DelDescriptor.


	/*===================================================================================
	 *	CheckEnumerations																*
	 *==================================================================================*/

	/**
	 * <h4>Check enumerations.</h4>
	 *
	 * This method will check whether the provided enumeration(s) are valid choices for the
	 * provided descriptor. The method expects two parameters: the descriptor key and the
	 * enumeration(s) to check; provide an array for more than one enumeration.
	 *
	 * The method will return the following values:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: The provided descriptor cannot be found.
	 * 	<li><tt>TRUE</tt>: All provided enumerations are supported.
	 * 	<li><tt>array</tt>: The list of enumerations which are not supported.
	 * </ul>
	 *
	 * @param string				$theDescriptor		Descriptor key.
	 * @param string|array		   &$theEnumeration		Enumeration(s).
	 * @return mixed				<tt>NULL</tt>, <tt>TRUE</tt> or <tt>array</tt>.
	 *
	 * @uses GetDescriptor()
	 * @uses validateEnumerations()
	 */
	public function CheckEnumerations( $theDescriptor, &$theEnumeration )
	{
		//
		// Get descriptor.
		//
		$descriptor = $this->GetDescriptor( $theDescriptor );
		if( $descriptor !== NULL )
		{
			//
			// Normalise enumerations.
			//
			$enums = ( is_array( $theEnumeration ) )
				? $theEnumeration
				: [ $theEnumeration ];

			//
			// Extract descriptor enumerations.
			//
			if( array_key_exists( kTOKEN_ENUM_LIST, $descriptor ) )
			{
				//
				// Validate enumerations.
				//
				$this->validateEnumerations( $descriptor[ kTOKEN_ENUM_LIST ], $enums );

				//
				// Handle valid.
				//
				if( ! count( $enums ) )
				{
					//
					// Set preferred enumerations.
					//
					$this->PreferredEnumerations( $descriptor, $theEnumeration );

					return TRUE;													// ==>

				} // All enumerations ae valid.

			} // Has controlled vocabulary.

			return $enums;															// ==>

		} // Gound descriptor.

		return NULL;																// ==>

	} // CheckEnumerations.


	/*===================================================================================
	 *	PreferredEnumerations															*
	 *==================================================================================*/

	/**
	 * <h4>Set preferred enumerations.</h4>
	 *
	 * This method will check whether the provided enumeration(s) have preferred values and
	 * will set them. The method expects two parameters: the descriptor key and the
	 * enumeration(s) to check; provide an array for more than one enumeration.
	 *
	 * @param array					$theDescriptor		Cached descriptor record.
	 * @param string|array		   &$theEnumeration		Enumeration(s).
	 */
	public function PreferredEnumerations( array $theDescriptor, &$theEnumeration )
	{
		//
		// Extract descriptor enumerations.
		//
		if( array_key_exists( kTOKEN_ENUM_LIST, $theDescriptor ) )
		{
			//
			// Handle many enumerations.
			//
			if( is_array( $theEnumeration ) )
			{
				//
				// Iterate enumerations.
				//
				$keys = array_keys( $theEnumeration );
				foreach( $keys as $key )
					$this->setPreferredEnumeration(
						$theDescriptor[ kTOKEN_ENUM_LIST ], $theEnumeration[ $key ] );

			} // Many enumerations.

			//
			// Handle single enumeration.
			//
			else
				$this->setPreferredEnumeration(
					$theDescriptor[ kTOKEN_ENUM_LIST ], $theEnumeration );

		} // Has controlled vocabulary.

	} // PreferredEnumerations.



/*=======================================================================================
 *																						*
 *								PUBLIC CACHING INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	CacheDataDictionary																*
	 *==================================================================================*/

	/**
	 * <h4>Cache data dictionary.</h4>
	 *
	 * This method will load the data dictionary into the cache.
	 *
	 * @uses NewTermsCollection()
	 * @uses NewResourcesCollection()
	 * @uses NewDescriptorsCollection()
	 */
	public function CacheDataDictionary()
	{
		//
		// Get default collections.
		//
		$terms = $this->NewTermsCollection();
		$resources = $this->NewResourcesCollection();
		$descriptors = $this->NewDescriptorsCollection();

		//
		// Get descriptors.
		//
		$list = $descriptors->Find();
		foreach( $list as $descriptor )
			$this->CacheDescriptor( $descriptor );

	} // CacheDataDictionary.


	/*===================================================================================
	 *	CacheDescriptor																	*
	 *==================================================================================*/

	/**
	 * <h4>Cache descriptor.</h4>
	 *
	 * This method will load the descriptor into the cache.
	 *
	 * @param Descriptor			$theDescriptor		Descriptor instance.
	 */
	public function CacheDescriptor( Descriptor $theDescriptor )
	{
		//
		// Convert document.
		//
		$data = $theDescriptor->toArray();

		//
		// Get key.
		//
		$key = $data[ $theDescriptor->Collection()->KeyOffset() ];
		unset( $data[ $theDescriptor->Collection()->KeyOffset() ] );

		//
		// Handle enumerated types.
		//
		if( ($theDescriptor[ kTAG_DATA_TYPE ] == kTYPE_ENUM)
		 || ($theDescriptor[ kTAG_DATA_TYPE ] == kTYPE_ENUM_SET) )
			$data[ kTOKEN_ENUM_LIST ] = $theDescriptor->GetEnumerations();

		//
		// cache descriptor.
		//
		$result = $this->mCache->set( $key, $data );
		if( $result === FALSE )
			throw new \RuntimeException(
				$this->mCache->getResultMessage(),
				$this->mCache->getResultCode() );								// !@! ==>

	} // CacheDescriptor.



/*=======================================================================================
 *																						*
 *							PROTECTED VALIDATION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	validateEnumerations															*
	 *==================================================================================*/

	/**
	 * <h4>Validate enumerations.</h4>
	 *
	 * This method will check whether the provided enumerations belong to the provided
	 * controlled vocabulary, each valid element will be removed from the provided
	 * enumerated list until the list is empty or the whole controlled vocabulary has been
	 * traversed.
	 *
	 * @param array					$theVocabulary		Controlled vocabulary.
	 * @param array				   &$theEnums			Enumerated list.
	 */
	protected function validateEnumerations( array $theVocabulary, array &$theEnums )
	{
		//
		// Check if all are validated.
		//
		if( count( $theEnums ) )
		{
			//
			// Iterate vocabulary.
			//
			foreach( $theVocabulary as $vocabulary )
			{
				//
				// Handle enumerated declaration.
				//
				if( array_key_exists( kTOKEN_ENUM_TERM, $vocabulary ) )
					$theEnums = array_diff( $theEnums, [ $vocabulary[ kTOKEN_ENUM_TERM ] ] );

				//
				// Stop if all are valid.
				//
				if( ! count( $theEnums ) )
					break;														// =>

				//
				// Handle nested enumerations.
				//
				if( array_key_exists( kTOKEN_ENUM_NESTED, $vocabulary ) )
					$this->validateEnumerations(
						$vocabulary[ kTOKEN_ENUM_NESTED ], $theEnums );

			} // Traversing controlled vocabulary.

		} // Enumerations left to validate.

	} // validateEnumerations.


	/*===================================================================================
	 *	setPreferredEnumeration															*
	 *==================================================================================*/

	/**
	 * <h4>Validate enumerations.</h4>
	 *
	 * This method will check whether the provided enumerations belong to the provided
	 * controlled vocabulary, each valid element will be removed from the provided
	 * enumerated list until the list is empty or the whole controlled vocabulary has been
	 * traversed.
	 *
	 * @param array					$theVocabulary		Controlled vocabulary.
	 * @param string			   &$theEnum			Enumerated value.
	 */
	protected function setPreferredEnumeration( array $theVocabulary, &$theEnum )
	{
		//
		// Iterate vocabulary.
		//
		foreach( $theVocabulary as $vocabulary )
		{
			//
			// Handle preferred value.
			//
			if( array_key_exists( kTOKEN_ENUM_TERM, $vocabulary )
			 && ($vocabulary[ kTOKEN_ENUM_TERM ] == $theEnum) )
			{
				//
				// Check preferred.
				//
				if( array_key_exists( kTOKEN_ENUM_PREFERRED, $vocabulary ) )
					$theEnum = $vocabulary[ kTOKEN_ENUM_PREFERRED ];

				//
				// Break loop.
				//
				break;

			} // Found matching enumeration.

			//
			// Handle nested enumerations.
			//
			if( array_key_exists( kTOKEN_ENUM_NESTED, $vocabulary ) )
				$this->setPreferredEnumeration(
					$vocabulary[ kTOKEN_ENUM_NESTED ], $theEnum );

		} // Traversing controlled vocabulary.

	} // setPreferredEnumeration.



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
	 * @uses NewResourcesCollection()
	 * @uses eraseDataDictionary()
	 * @uses buildDataDictionary()
	 */
	protected function initDataDictionary()
	{
		//
		// Build data dictionary.
		//
		if( ! $this->NewResourcesCollection()->Count() )
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
	 * @uses NewTypesCollection()
	 * @uses NewResourcesCollection()
	 * @uses NewDescriptorsCollection()
	 * @uses initDescriptors()
	 * @uses initResources()
	 * @uses initTerms()
	 * @uses initTypes()
	 */
	protected function buildDataDictionary()
	{
		//
		// Create default collections.
		//
		$terms = $this->NewTermsCollection();
		$types = $this->NewTypesCollection();
		$resources = $this->NewResourcesCollection();
		$descriptors = $this->NewDescriptorsCollection();

		//
		// Initialise terms.
		//
		$this->initTerms( $terms );

		//
		// Initialise resources.
		//
		$this->initResources( $resources );

		//
		// Initialise types.
		//
		$this->initTypes( $terms, $types );

		//
		// Initialise descriptors.
		//
		$this->initDescriptors( $terms, $types, $descriptors );

	} // buildDataDictionary.


	/*===================================================================================
	 *	initTerms																		*
	 *==================================================================================*/

	/**
	 * <h4>Initialise terms.</h4>
	 *
	 * This method will load the default terms.
	 *
	 * @param Collection			$theCollection		Terms collection.
	 */
	protected function initTerms( Collection $theCollection )
	{
		//
		// Init local storage.
		//
		$key = $theCollection->KeyOffset();

		//
		// Create default namespace.
		//
		$term = new Term( $theCollection, [
			kTAG_LID => '', kTAG_GID => '',
			kTAG_NAME => [ 'en' => 'Default namespace' ],
			kTAG_DESCRIPTION => [ 'en' =>
				'This namespace groups all default or built-in terms of the ontology, ' .
				'these are the elements that will be used to build the ontology itself.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );
		$ns = $term[ $key ];

		//
		// Create type namespaces.
		//
		$nsp = $ns;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'type', kTAG_SYMBOL => 'kTYPE_TYPE',
				kTAG_NAME => [ 'en' => 'Type' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The type describes the <em>nature</em> or <em>composition</em> ' .
					'of an object.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );
		$ns_type = $term[ $key ];

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'kind', kTAG_SYMBOL => 'kTYPE_KIND',
				kTAG_NAME => [ 'en' => 'Kind' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The kind describes the <em>function</em> or <em>context</em>em> ' .
					'of an object.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );
		$ns_kind = $term[ $key ];

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'predicate',
				kTAG_NAME => [ 'en' => 'Predicate' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'A predicate qualifies a directed graph relationship.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );
		$ns_predicate = $term[ $key ];

		$term = new Term( $theCollection, [ kTAG_NS => $ns_kind,
				kTAG_LID => 'private',
				kTAG_NAME => [ 'en' => 'Private' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This term qualifies a resource as not public or freely accessible.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );
		$ns_private = $term[ $key ];

		//
		// Load primitive types.
		//
		$nsp = $ns_type;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'mixed', kTAG_SYMBOL => 'kTYPE_MIXED',
				kTAG_NAME => [ 'en' => 'Mixed' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'A mixed data type indicates that the referred property may take any ' .
					'data type.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'string', kTAG_SYMBOL => 'kTYPE_STRING',
				kTAG_NAME => [ 'en' => 'String' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'A string data type indicates that the referred property may hold ' .
					'UNICODE characters, this type does not include binary data.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );
		$ns_string = $term[ $key ];

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'int', kTAG_SYMBOL => 'kTYPE_INT',
				kTAG_NAME => [ 'en' => 'Integer' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'An integer data type indicates that the referred property may hold ' .
					'a 32 or 64 bit integral numeric values.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'float', kTAG_SYMBOL => 'kTYPE_FLOAT',
				kTAG_NAME => [ 'en' => 'Float' ],
				kTAG_DESCRIPTION => [ 'en' => 'A float data type indicates that the ' .
					'referred property may hold a floating point number, also known as ' .
					'double or real. The precision of such value is not inferred, in ' .
					'general it will be a 32 or 64 bit real.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'bool', kTAG_SYMBOL => 'kTYPE_BOOLEAN',
				kTAG_NAME => [ 'en' => 'Boolean' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'Boolean values can take one of two states: on or true, or off or ' .
					'false.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		//
		// Load derived types.
		//
		$nsp = $ns_string;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'url', kTAG_SYMBOL => 'kTYPE_URL',
				kTAG_NAME => [ 'en' => 'Link' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'A link data type indicates that the referred property is a string ' .
					'representing an URL which is an internet link or network address.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'date', kTAG_SYMBOL => 'kTYPE_STRING_DATE',
				kTAG_NAME => [ 'en' => 'String date' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This type defines a date in which the day and month may be omitted, ' .
					'it is a string providing the date in <tt>YYYYMMDD</tt> format in ' .
					'which the day, or the day and month can be omitted. All digits must ' .
					'be provided. This type can be used as a range and sorted.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'lat', kTAG_SYMBOL => 'kTYPE_STRING_LAT',
				kTAG_NAME => [ 'en' => 'String latitude' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This type defines a latitude expressed in <tt>DD˚MM\'SS.SSS"H</tt> ' .
					'where <tt>H</tt> is the hemisphere (<tt>N</tt> or <tt>S</tt>), ' .
					'<tt>DD</tt> is the degrees, <tt>MM</tt> is the minutes and ' .
					'<tt>SS.SSS</tt> represents the seconds as a floating point number ' .
					'or integer. You may omit the seconds or the seconds and minutes, ' .
					'all digits must be provided. The degrees must range between ' .
					'<tt>0</tt> to lower than <tt>90</tt>, the minutes and seconds ' .
					'must range between  <tt>0</tt> to lower than <tt>60</tt>. This data ' .
					'type is useful to calculate the maximum error of a coordinate.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'lon', kTAG_SYMBOL => 'kTYPE_STRING_LON',
				kTAG_NAME => [ 'en' => 'String longitude' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This type defines a longitude expressed in ' .
					'<tt>DDD˚MM\'SS.SSS"H</tt> where <tt>H</tt> is the hemisphere ' .
					'(<tt>E</tt> or <tt>W</tt>), <tt>DDD</tt> is the degrees, ' .
					'<tt>MM</tt> is the minutes and <tt>SS.SSS</tt> represents the ' .
					'seconds as a floating point number or integer. You may omit ' .
					'the seconds or the seconds and minutes, all digits must be ' .
					'provided. The degrees must range between <tt>0</tt> to lower ' .
					'than <tt>180</tt>, the minutes and seconds must range between ' .
					'<tt>0</tt> to lower than <tt>60</tt>. This data type is useful ' .
					'to calculate the maximum error of a coordinate.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		//
		// Load referential types.
		//
		$nsp = $ns_type;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'ref', kTAG_SYMBOL => 'kTYPE_REF',
				kTAG_NAME => [ 'en' => 'Reference' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This type indicates that the property references another object, ' .
					'the value will contain the name of the collection in which the ' .
					'reference object resides and the object key, the format in which ' .
					'this value is expressed depends on the specific database.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'ref-term', kTAG_SYMBOL => 'kTYPE_REF_TERM',
				kTAG_NAME => [ 'en' => 'Term reference' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This type indicates that the property references a term object, the ' .
					'value will contain the key of the referenced term.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		//
		// Load localised types.
		//
		$nsp = $ns_type;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'date', kTAG_SYMBOL => 'kTYPE_DATE',
				kTAG_NAME => [ 'en' => 'Date' ],
				kTAG_DESCRIPTION => [ 'en' => 'Date in the native database format.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'time-stamp', kTAG_SYMBOL => 'kTYPE_TIMESTAMP',
				kTAG_NAME => [ 'en' => 'Timestamp' ],
				kTAG_DESCRIPTION => [ 'en' => 'Time stamp in the native database format.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		//
		// Load categorical types.
		//
		$nsp = $ns_type;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'enum', kTAG_SYMBOL => 'kTYPE_ENUM',
				kTAG_NAME => [ 'en' => 'Enumeration' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'An enumerated property may hold only one value selected from a ' .
					'controlled vocabulary, in general, the controlled vocabulary will ' .
					'be a set of terms and the selected value will be the term\'s global ' .
					'identifier.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'enum-set', kTAG_SYMBOL => 'kTYPE_ENUM_SET',
				kTAG_NAME => [ 'en' => 'Enumerated set' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'An enumerated set property may hold one or more unique values ' .
					'selected from a controlled vocabulary, in general, the controlled ' .
					'vocabulary will be a set of terms and the selected values will be ' .
					'the term\'s global identifiers.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		//
		// Load structured types.
		//
		$nsp = $ns_type;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'array', kTAG_SYMBOL => 'kTYPE_ARRAY',
				kTAG_NAME => [ 'en' => 'List' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This data type defines a list of elements whose value type is not ' .
					'inferred. This data type usually applies to arrays.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'struct', kTAG_SYMBOL => 'kTYPE_STRUCT',
				kTAG_NAME => [ 'en' => 'Structure' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This data type defines a structure or an associative array in which ' .
					'the element key is represented by an indicator identifier.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
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
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$nsp = $ns_string;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'lang', kTAG_SYMBOL => 'kTYPE_LANG_STRING',
				kTAG_NAME => [ 'en' => 'Language string' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This data type defines a list of strings expressed in different ' .
					'languages. The list elements are composed by key/value pairs, the ' .
					'key is expressed as the language code and the value is a single ' .
					'string in that language.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'langs', kTAG_SYMBOL => 'kTYPE_LANG_STRINGS',
				kTAG_NAME => [ 'en' => 'Language strings' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This data type defines a list of strings expressed in different ' .
					'languages. The list elements are composed by key/value pairs, the ' .
					'key is expressed as the language code and the value is a list of ' .
					'strings in that language.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		//
		// Load functional types.
		//
		$nsp = $ns_type;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'attribute', kTAG_SYMBOL => 'kTYPE_ATTRIBUTE',
				kTAG_NAME => [ 'en' => 'Attribute' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This data type defines an object that functionas as an attribute.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'property', kTAG_SYMBOL => 'kTYPE_PROPERTY',
				kTAG_NAME => [ 'en' => 'Property' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This data type defines an object that functionas as a property.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		//
		// Load domain kinds.
		//
		$nsp = $ns_kind;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'categorical', kTAG_SYMBOL => 'kKIND_CATEGORICAL',
				kTAG_NAME => [ 'en' => 'Categorical' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the property can take on one or more of a ' .
					'limited, and usually fixed, number of possible values. In general, ' .
					'properties which take their values from an enumerated set of ' .
					'choices are of this kind.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'quantitative', kTAG_SYMBOL => 'kKIND_QUANTITATIVE',
				kTAG_NAME => [ 'en' => 'Quantitative' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the property is one whose type of ' .
					'information is based on quantities or quantifiable data which is ' .
					'continuous. In general numerical values which can be aggregated in ' .
					'ranges fall under this category.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'discrete', kTAG_SYMBOL => 'kKIND_DISCRETE',
				kTAG_NAME => [ 'en' => 'Discrete' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the property is one which may take an ' .
					'indefinite number of values, which differentiates it from a ' .
					'categorical property, and whose values are not continuous, which ' .
					'differentiates it from a quantitative property.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		//
		// Load usage kinds.
		//
		$nsp = $ns_kind;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'recommended', kTAG_SYMBOL => 'kKIND_RECOMMENDED',
				kTAG_NAME => [ 'en' => 'Recommended' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the property is recommended, encouraged or ' .
					'important, but not necessarily required or mandatory.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'required', kTAG_SYMBOL => 'kKIND_REQUIRED',
				kTAG_NAME => [ 'en' => 'Required' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the property is required or mandatory.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$nsp = $ns_private;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'display', kTAG_SYMBOL => 'kKIND_PRIVATE_DISPLAY',
				kTAG_NAME => [ 'en' => 'Private display' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the data property should not be displayed ' .
					'to clients.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'search', kTAG_SYMBOL => 'kKIND_PRIVATE_SEARCH',
				kTAG_NAME => [ 'en' => 'Private search' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the data property should not be available ' .
					'to clients for searching.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'modify', kTAG_SYMBOL => 'kKIND_PRIVATE_MODIFY',
				kTAG_NAME => [ 'en' => 'Private modify' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the data property is reserved by the ' .
					'object, which means that it is automatically managed by the class ' .
					'and should not be explicitly set or modified by clients.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		//
		// Load cardinality kinds.
		//
		$nsp = $ns_kind;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'list', kTAG_SYMBOL => 'kKIND_LIST',
				kTAG_NAME => [ 'en' => 'List' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the property is a list of values, each of ' .
					'the defined data type.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'summary', kTAG_SYMBOL => 'kKIND_SUMMARY',
				kTAG_NAME => [ 'en' => 'Summary' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the property can be used to group results ' .
					'in a summary.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'lookup', kTAG_SYMBOL => 'kKIND_LOOKUP',
				kTAG_NAME => [ 'en' => 'Lookup' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This kind indicates that the property can be searched upon using ' .
					'auto-complete.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		//
		// Load vertex kinds.
		//
		$nsp = $ns_kind;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'root', kTAG_SYMBOL => 'kKIND_ROOT',
				kTAG_NAME => [ 'en' => 'Root' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'An entry point of a structure. Items of this kind represents a door ' .
					'or entry point of a structure, they can be either the element from ' .
					'which the whole structure originates from, or an element that ' .
					'represents a specific thematic entry point.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'type', kTAG_SYMBOL => 'kKIND_TYPE',
				kTAG_NAME => [ 'en' => 'Type' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'A type or definition. Items of this kind are used as a type ' .
					'definition or to define controlled vocabularies, they are used as ' .
					'proxies to the structure they hold. When traversing an enumerated ' .
					'set tree, elements of this kind will not be either displayed or ' .
					'made available for setting.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'category', kTAG_SYMBOL => 'kKIND_CATEGORY',
				kTAG_NAME => [ 'en' => 'Category' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'Items of this kind are used to group other items under a common ' .
					'category or division. In practice, when such elements are ' .
					'encountered in a graph path, they will be displayed and will in ' .
					'general feature a disclosure triangle, but will not be available ' .
					'for selection as the other elements of the path.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		//
		// Load aggregation predicates.
		//
		$nsp = $ns_predicate;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'SUBCLASS-OF', kTAG_SYMBOL => 'kPREDICATE_SUBCLASS_OF',
				kTAG_NAME => [ 'en' => 'Subclass of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship is a ' .
					'subclass of the object of the relationship, in other words, the ' .
					'subject is derived from the object.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'SUBRANK-OF', kTAG_SYMBOL => 'kPREDICATE_SUBRANK_OF',
				kTAG_NAME => [ 'en' => 'Subrank of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship ' .
					'belongs to the next lowest rank than the object of the ' .
					'relationship.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'SUBSET-OF', kTAG_SYMBOL => 'kPREDICATE_SUBSET_OF',
				kTAG_NAME => [ 'en' => 'Subset of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship ' .
					'represents a subset of the object of the relationship, in other ' .
					'words, the subject is contained by the object.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'PART-OF', kTAG_SYMBOL => 'kPREDICATE_PART_OF',
				kTAG_NAME => [ 'en' => 'Part of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship is a ' .
					'part or a component of the object of the relationship.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'TYPE-OF', kTAG_SYMBOL => 'kPREDICATE_TYPE_OF',
				kTAG_NAME => [ 'en' => 'Type of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship ' .
					'represents the type of the object of the relationship. This ' .
					'predicate can also act as a group and a proxy: it may define a ' .
					'formal group by collecting all elements that relate to it, and it ' .
					'acts as a proxy, because this relationship type implies that all ' .
					'the elements related to the group will relate directly to the ' .
					'object of the current relationship.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'CATEGORY-OF', kTAG_SYMBOL => 'kPREDICATE_CATEGORY_OF',
				kTAG_NAME => [ 'en' => 'Category of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship ' .
					'represents the category of the object of the relationship. This ' .
					'predicate defines a formal group which contains an underlining ' .
					'structure, but does not act as an element of that structure: it is ' .
					'used to collect a set of elements under a common category.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'FUNCTION-OF', kTAG_SYMBOL => 'kPREDICATE_FUNCTION_OF',
				kTAG_NAME => [ 'en' => 'Function of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship ' .
					'represents a function or trait group of the object of the ' .
					'relationship, in other words, the subject is a group of functions ' .
					'that can be applied to the object.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
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
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'PROPERTY-OF', kTAG_SYMBOL => 'kPREDICATE_PROPERTY_OF',
				kTAG_NAME => [ 'en' => 'Property of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship is a ' .
					'property of the object of the relationship, this means that the ' .
					'subject of the relationship is a feature.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'ATTRIBUTE-OF', kTAG_SYMBOL => 'kPREDICATE_ATTRIBUTE_OF',
				kTAG_NAME => [ 'en' => 'Attribute of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate indicates that the subject of the relationship is ' .
					'an attribute of the object of the relationship, this means that ' .
					'the subject of the relationship belongs to the set of attributes ' .
					'of the object of the relationship.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'ENUM-OF', kTAG_SYMBOL => 'kPREDICATE_ENUM_OF',
				kTAG_NAME => [ 'en' => 'Enumeration of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate relates vertex elements of an enumerated set, it ' .
					'indicates that the subject of the relationship is an enumerated set ' .
					'item instance. If the object of the relationship is also an ' .
					'enumerated set item instance, this means that the subject is a ' .
					'subset of the object.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
				kTAG_LID => 'INSTANCE-OF', kTAG_SYMBOL => 'kPREDICATE_INSTANCE_OF',
				kTAG_NAME => [ 'en' => 'Instance of' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This predicate relates a type to its instance, it indicates that ' .
					'the object of the relationship is an instance of the subject of the ' .
					'relationship.' ] ]
		);
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		//
		// Load preference predicates.
		//
		$nsp = $ns_predicate;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
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
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
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
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
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
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		//
		// Load reference predicates.
		//
		$nsp = $ns_predicate;
		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
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
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

		$term = new Term( $theCollection, [ kTAG_NS => $nsp,
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
		$term->PrepareInsert();
		$theCollection->Insert( $term->toArray() );

	} // initTerms.


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
	 *	initTypes																		*
	 *==================================================================================*/

	/**
	 * <h4>Initialise types.</h4>
	 *
	 * This method will load the default types and enumerations.
	 *
	 * @param Collection			$theTerms			Terms collection.
	 * @param Collection			$theTypes			Types collection.
	 */
	protected function initTypes( Collection $theTerms, Collection $theTypes )
	{
		//
		// Load default namespace.
		//
		$ns = Term::GetByGID( $theTerms, '' )[ $theTerms->KeyOffset() ];

		//
		// Load data types.
		//
		$this->initDataType( $ns, $theTerms, $theTypes );

		//
		// Load data kinds.
		//
		$this->initDataKind( $ns, $theTerms, $theTypes );

		//
		// Load node kinds.
		//
		$this->initNodeKind( $ns, $theTerms, $theTypes );

	} // initTypes.


	/*===================================================================================
	 *	initDescriptors																	*
	 *==================================================================================*/

	/**
	 * <h4>Initialise descriptors.</h4>
	 *
	 * This method will load the default descriptors.
	 *
	 * @param Collection			$theTerms			Terms collection.
	 * @param Collection			$theTypes			Types collection.
	 * @param Collection			$theCollection		Descriptors collection.
	 */
	protected function initDescriptors( Collection $theTerms,
										Collection $theTypes,
										Collection $theCollection )
	{
		//
		// Init local storage.
		//
		$key = $theCollection->KeyOffset();

		//
		// Get default namespace.
		//
		$ns = Term::GetByGID( $theTerms, '' );
		$ns = $ns[ $theTerms->KeyOffset() ];

		//
		// Create global properties.
		//
		// kTAG_CREATION
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'cre', kTAG_SYMBOL => 'kTAG_CREATION',
				kTAG_DATA_TYPE => kTYPE_FLOAT,
				kTAG_DATA_KIND => [ kKIND_DISCRETE ],
				kTAG_NAME => [ 'en' => 'Creation stamp' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds the object\'s <em>creation time stamp</em> ' .
					'expressed as the result of the <tt>microtime()</tt> function as ' .
					'float.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		//
		// kTAG_MODIFICATION
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'mod', kTAG_SYMBOL => 'kTAG_MODIFICATION',
				kTAG_DATA_TYPE => kTYPE_FLOAT,
				kTAG_DATA_KIND => [ kKIND_DISCRETE ],
				kTAG_NAME => [ 'en' => 'Modification stamp' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds the object\'s <em>modification time stamp</em> ' .
					'expressed as the result of the  <tt>microtime()</tt> function as ' .
					'float.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );

		//
		// Create term properties.
		//
		// kTAG_NS
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'ns', kTAG_SYMBOL => 'kTAG_NS',
				kTAG_DATA_TYPE => kTYPE_REF_TERM,
				kTAG_DATA_KIND => [ kKIND_DISCRETE ],
				kTAG_NAME => [ 'en' => 'Namespace reference' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds the <em>reference to the object</em> that ' .
					'represents the current object\'s <em>namespace</em>, expressed as ' .
					'a <em>document identifier</em>.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		//
		// kTAG_LID
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'lid', kTAG_SYMBOL => 'kTAG_LID',
				kTAG_DATA_TYPE => kTYPE_STRING,
				kTAG_DATA_KIND => [ kKIND_DISCRETE, kKIND_REQUIRED ],
				kTAG_NAME => [ 'en' => 'Local identifier' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds the object <em>local</em> identifier, that is, ' .
					'the code that uniquely identifies the object ' .
					'<em>within its namespace</em>.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		//
		// kTAG_GID
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'gid', kTAG_SYMBOL => 'kTAG_GID',
				kTAG_DATA_TYPE => kTYPE_STRING,
				kTAG_DATA_KIND => [ kKIND_DISCRETE, kKIND_REQUIRED ],
				kTAG_NAME => [ 'en' => 'Global identifier' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds the object <em>global</em> identifier, that is, ' .
					'the code that uniquely identifies the term <em>among all ' .
					'namespaces</em>.<p/> In general, this code is computed by ' .
					'concatenating the global identifier of the object representing the ' .
					'namespace with the local identifier of the current object, ' .
					'separated by the <tt>kTOKEN_NAMESPACE_SEPARATOR</tt> token.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		//
		// kTAG_NAME
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'name', kTAG_SYMBOL => 'kTAG_NAME',
				kTAG_DATA_TYPE => kTYPE_LANG_STRING,
				kTAG_DATA_KIND => [ kKIND_DISCRETE, kKIND_REQUIRED, kKIND_LOOKUP ],
				kTAG_NAME => [ 'en' => 'Name' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds the object\'s <em>name</em> or <em>label</em>, ' .
					'it represents a short description that can be used as a label and ' .
					'that should give a rough idea of what the object represents.<p/>' .
					'This property is an associative array with the <em>language code ' .
					'as key</em> and the <em>name as value</em>.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		//
		// kTAG_DESCRIPTION
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'descr', kTAG_SYMBOL => 'kTAG_DESCRIPTION',
				kTAG_DATA_TYPE => kTYPE_LANG_STRING,
				kTAG_DATA_KIND => [ kKIND_DISCRETE ],
				kTAG_NAME => [ 'en' => 'Description' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds the object\'s <em>description</em> or ' .
					'<em>definition</em>, it represents a text that <em>describes in ' .
					'detail</em> the current object.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );

		//
		// Create descriptor properties.
		//
		// kTAG_SYMBOL
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'sym', kTAG_SYMBOL => 'kTAG_SYMBOL',
				kTAG_DATA_TYPE => kTYPE_STRING,
				kTAG_DATA_KIND => [ kKIND_DISCRETE ],
				kTAG_NAME => [ 'en' => 'Symbol' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds the object <em>symbol</em> or <em>constant<em>, ' .
					'which is a string that serves as a variable name for the object; ' .
					'the symbol should be unique within a context.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		//
		// kTAG_SYNONYMS
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'syn', kTAG_SYMBOL => 'kTAG_SYNONYMS',
				kTAG_DATA_TYPE => kTYPE_STRING,
				kTAG_DATA_KIND => [ kKIND_LIST ],
				kTAG_NAME => [ 'en' => 'Synonyms' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds a list of symbols which refer to <em>synonyms of ' .
					'the current descriptor</em>, the property is structured as a list ' .
					'of strings.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		//
		// kTAG_DATA_TYPE
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => kTYPE_TYPE,
				kTAG_LID => 'data', kTAG_SYMBOL => 'kTAG_DATA_TYPE',
				kTAG_DATA_TYPE => kTYPE_ENUM,
				kTAG_DATA_KIND => [ kKIND_CATEGORICAL ],
				kTAG_NAME => [ 'en' => 'Data type' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds an <em>enumerated set of values</em> belonging ' .
					'to a controlled vocabulary which defines the <em>type</em> or ' .
					'<em>nature</em> of data. It is generally used to indicate the ' .
					'primitive data type of a descriptor.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		$src = $theTerms->BuildDocumentHandle( kTAG_DATA_TYPE );
		$dst = $theCollection->NewDocumentHandle( $desc );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_TYPE_OF, $src, $dst );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		//
		// kTAG_DATA_KIND
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => kTYPE_KIND,
				kTAG_LID => 'data', kTAG_SYMBOL => 'kTAG_DATA_KIND',
				kTAG_DATA_TYPE => kTYPE_ENUM_SET,
				kTAG_DATA_KIND => [ kKIND_CATEGORICAL ],
				kTAG_NAME => [ 'en' => 'Data kind' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds an <em>enumerated set of values</em> belonging ' .
					'to a controlled vocabulary which <em>defines the <em>function</em> ' .
					'or <em>context</em> of data, it is generally used to indicate the ' .
					'context in which a data type is used.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		$src = $theTerms->BuildDocumentHandle( kTAG_DATA_KIND );
		$dst = $theCollection->NewDocumentHandle( $desc );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_TYPE_OF, $src, $dst );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		//
		// kTAG_REF_COUNT
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'refs', kTAG_SYMBOL => 'kTAG_REF_COUNT',
				kTAG_DATA_TYPE => kTYPE_INT,
				kTAG_DATA_KIND => [ kKIND_QUANTITATIVE, kKIND_PRIVATE_MODIFY ],
				kTAG_NAME => [ 'en' => 'Reference count' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds the <em>number of objects that reference the ' .
					'current object</em>, when inserted for the first time the value is ' .
					'<tt>0</tt>, the object cannot be deleted if this value is greater ' .
					'than <tt>0</tt>.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		//
		// kTAG_MIN_VAL
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'min', kTAG_SYMBOL => 'kTAG_MIN_VAL',
				kTAG_DATA_TYPE => kTYPE_FLOAT,
				kTAG_DATA_KIND => [ kKIND_QUANTITATIVE, kKIND_PRIVATE_MODIFY ],
				kTAG_NAME => [ 'en' => 'Minimum value' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds a number representing the <em>minimum value</em> ' .
					'for instances of the current descriptor.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		//
		// kTAG_MAX_VAL
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'max', kTAG_SYMBOL => 'kTAG_MAX_VAL',
				kTAG_DATA_TYPE => kTYPE_FLOAT,
				kTAG_DATA_KIND => [ kKIND_QUANTITATIVE, kKIND_PRIVATE_MODIFY ],
				kTAG_NAME => [ 'en' => 'Maximum value' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds a number representing the <em>maximum value</em> ' .
					'for instances of the current descriptor.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		//
		// kTAG_PATTERN
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'grep', kTAG_SYMBOL => 'kTAG_PATTERN',
				kTAG_DATA_TYPE => kTYPE_STRING,
				kTAG_DATA_KIND => [ kKIND_DISCRETE ],
				kTAG_NAME => [ 'en' => 'Pattern' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds a string representing the <em>expected pattern ' .
					'of the string</em> descriptor value, this is used to validate ' .
					'strings.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		//
		// kTAG_MIN_VAL_EXPECTED
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'low', kTAG_SYMBOL => 'kTAG_MIN_VAL_EXPECTED',
				kTAG_DATA_TYPE => kTYPE_FLOAT,
				kTAG_DATA_KIND => [ kKIND_QUANTITATIVE ],
				kTAG_NAME => [ 'en' => 'Minimum expected value' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds a number representing the <em>lowest valid ' .
					'value</em> for this descriptor.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		//
		// kTAG_MAX_VAL_EXPECTED
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'high', kTAG_SYMBOL => 'kTAG_MAX_VAL_EXPECTED',
				kTAG_DATA_TYPE => kTYPE_FLOAT,
				kTAG_DATA_KIND => [ kKIND_QUANTITATIVE ],
				kTAG_NAME => [ 'en' => 'Maximum expected value' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds a number representing the <em>highest valid ' .
					'value</em> for this descriptor.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		//
		// kTAG_PREDICATE_TERM
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'pred', kTAG_SYMBOL => 'kTAG_PREDICATE_TERM',
				kTAG_DATA_TYPE => kTYPE_REF_TERM,
				kTAG_DATA_KIND => [ kKIND_DISCRETE ],
				kTAG_NAME => [ 'en' => 'Predicate term' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds the edge predicate term reference in the ' .
					'form of the term document key.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		//
		// kTAG_NODE_REF
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'node-ref', kTAG_SYMBOL => 'kTAG_NODE_REF',
				kTAG_DATA_TYPE => kTYPE_REF,
				kTAG_DATA_KIND => [ kKIND_DISCRETE ],
				kTAG_NAME => [ 'en' => 'Node alias' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds the document handle of the object for which ' .
					'the node acts as an alias.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		//
		// kTAG_NODE_KIND
		//
		$desc = new Descriptor( $theCollection, [ kTAG_NS => $ns,
				kTAG_LID => 'node-kind', kTAG_SYMBOL => 'kTAG_NODE_KIND',
				kTAG_DATA_TYPE => kTYPE_ENUM_SET,
				kTAG_DATA_KIND => [ kKIND_CATEGORICAL ],
				kTAG_NAME => [ 'en' => 'Node kind' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'The property holds an <em>enumerated set of values</em> belonging ' .
					'to a controlled vocabulary which defines the <em>kind</em> or ' .
					'<em>function</em> of the node.' ] ]
		);
		$desc[ $theCollection->KeyOffset() ] = $desc[ kTAG_GID ];
		$desc->PrepareInsert();
		$theCollection->Insert( $desc->toArray() );
		$src = $theTerms->BuildDocumentHandle( kTAG_NODE_KIND );
		$dst = $theCollection->NewDocumentHandle( $desc );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_TYPE_OF, $src, $dst );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );

	} // initDescriptors.



/*=======================================================================================
 *																						*
 *							PROTECTED ENUMERATIONS INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	initDataType																	*
	 *==================================================================================*/

	/**
	 * <h4>Init data type type.</h4>
	 *
	 * This method will load the data type type.
	 *
	 * @param string				$theNamespace		Namespace term key.
	 * @param Collection			$theTerms			Terms collection.
	 * @param Collection			$theTypes			Types collection.
	 */
	protected function initDataType( $theNamespace, Collection $theTerms,
									 Collection $theTypes )
	{
		//
		// kTAG_DATA_TYPE
		//
		$term = new Term( $theTerms, [ kTAG_NS => kTYPE_TYPE,
				kTAG_LID => 'data', kTAG_SYMBOL => 'kTAG_DATA_TYPE',
				kTAG_NODE_KIND => kKIND_TYPE,
				kTAG_NAME => [ 'en' => 'Data type' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This defines the data type of a descriptor.' ] ]
		);
		$term->PrepareInsert();
		$theTerms->Insert( $term->toArray() );
		$type = $theTerms->NewDocumentHandle( $term );

		// PRIMITIVES.
		$src = $theTerms->BuildDocumentHandle( kTYPE_MIXED );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_mixed = $src;

		$src = $theTerms->BuildDocumentHandle( kTYPE_STRING );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_string = $src;

		$src = $theTerms->BuildDocumentHandle( kTYPE_INT );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );

		$src = $theTerms->BuildDocumentHandle( kTYPE_FLOAT );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );

		$src = $theTerms->BuildDocumentHandle( kTYPE_BOOLEAN );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );

		// DERIVED.
		$src = $theTerms->BuildDocumentHandle( kTYPE_URL );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum_string );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );

		$src = $theTerms->BuildDocumentHandle( kTYPE_STRING_DATE );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );

		$src = $theTerms->BuildDocumentHandle( kTYPE_STRING_LAT );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum_string );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );

		$src = $theTerms->BuildDocumentHandle( kTYPE_STRING_LON );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum_string );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );

		// REFERENTIAL.
		$src = $theTerms->BuildDocumentHandle( kTYPE_REF );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum_mixed );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_ref = $src;

		$src = $theTerms->BuildDocumentHandle( kTYPE_REF_TERM );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum_ref );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );

		// LOCALISED.
		$src = $theTerms->BuildDocumentHandle( kTYPE_DATE );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );

		$src = $theTerms->BuildDocumentHandle( kTYPE_TIMESTAMP );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );

		// CATEGORICAL.
		$src = $theTerms->BuildDocumentHandle( kTYPE_ENUM );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum_string );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_enum = $src;

		$src = $theTerms->BuildDocumentHandle( kTYPE_ENUM_SET );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum_enum );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );

		// STRUCTURED.
		$src = $theTerms->BuildDocumentHandle( kTYPE_ARRAY );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );

		$src = $theTerms->BuildDocumentHandle( kTYPE_STRUCT );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_struct = $src;

		$src = $theTerms->BuildDocumentHandle( kTYPE_SHAPE );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum_struct );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );

		$src = $theTerms->BuildDocumentHandle( kTYPE_LANG_STRING );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum_struct );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_lang_string = $src;

		$src = $theTerms->BuildDocumentHandle( kTYPE_LANG_STRINGS );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum_lang_string );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );

	} // initDataType.


	/*===================================================================================
	 *	initDataKind																	*
	 *==================================================================================*/

	/**
	 * <h4>Init data kind type.</h4>
	 *
	 * This method will load the data kind type.
	 *
	 * @param string				$theNamespace		Namespace term key.
	 * @param Collection			$theTerms			Terms collection.
	 * @param Collection			$theTypes			Types collection.
	 */
	protected function initDataKind( $theNamespace, Collection $theTerms,
													Collection $theTypes )
	{
		//
		// kTAG_DATA_KIND
		//
		$term = new Term( $theTerms, [ kTAG_NS => kTYPE_KIND,
				kTAG_LID => 'data', kTAG_SYMBOL => 'kTAG_DATA_KIND',
				kTAG_NODE_KIND => kKIND_TYPE,
				kTAG_NAME => [ 'en' => 'Data kind' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This defines the data kind of a descriptor.' ] ]
		);
		$term->PrepareInsert();
		$theTerms->Insert( $term->toArray() );
		$type = $theTerms->NewDocumentHandle( $term );

		//
		// Domain.
		//
		$term = new Term( $theTerms, [ kTAG_NS => kTAG_DATA_KIND,
				kTAG_LID => 'domain',
				kTAG_NODE_KIND => kKIND_CATEGORY,
				kTAG_NAME => [ 'en' => 'Data domain' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This defines the domain of a descriptor, select one to define the ' .
					'data domain.' ] ]
		);
		$term->PrepareInsert();
		$theTerms->Insert( $term->toArray() );
		$category = $theTerms->NewDocumentHandle( $term );

		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_CATEGORY_OF, $category, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum = $category;

		$src = $theTerms->BuildDocumentHandle( kKIND_CATEGORICAL );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_string = $src;

		$src = $theTerms->BuildDocumentHandle( kKIND_QUANTITATIVE );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_string = $src;

		$src = $theTerms->BuildDocumentHandle( kKIND_DISCRETE );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_string = $src;

		//
		// Usage.
		//
		$term = new Term( $theTerms, [ kTAG_NS => kTAG_DATA_KIND,
				kTAG_LID => 'usage',
				kTAG_NODE_KIND => kKIND_CATEGORY,
				kTAG_NAME => [ 'en' => 'Data usage' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This defines the usage of a descriptor, select one to define the ' .
					'data usage.' ] ]
		);
		$term->PrepareInsert();
		$theTerms->Insert( $term->toArray() );
		$category = $theTerms->NewDocumentHandle( $term );

		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_CATEGORY_OF, $category, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum = $category;

		$src = $theTerms->BuildDocumentHandle( kKIND_RECOMMENDED );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_string = $src;

		$src = $theTerms->BuildDocumentHandle( kKIND_REQUIRED );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_string = $src;

		//
		// Access.
		//
		$term = new Term( $theTerms, [ kTAG_NS => kTAG_DATA_KIND,
				kTAG_LID => 'access',
				kTAG_NODE_KIND => kKIND_CATEGORY,
				kTAG_NAME => [ 'en' => 'Data access' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This defines access to a descriptor, select one or more to define ' .
					'the data access.' ] ]
		);
		$term->PrepareInsert();
		$theTerms->Insert( $term->toArray() );
		$category = $theTerms->NewDocumentHandle( $term );

		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_CATEGORY_OF, $category, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum = $category;

		$src = $theTerms->BuildDocumentHandle( kKIND_PRIVATE_DISPLAY );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_string = $src;

		$src = $theTerms->BuildDocumentHandle( kKIND_PRIVATE_SEARCH );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_string = $src;

		$src = $theTerms->BuildDocumentHandle( kKIND_PRIVATE_MODIFY );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_string = $src;

		//
		// Cardinality.
		//
		$term = new Term( $theTerms, [ kTAG_NS => kTAG_DATA_KIND,
				kTAG_LID => 'cardinality',
				kTAG_NODE_KIND => kKIND_CATEGORY,
				kTAG_NAME => [ 'en' => 'Cardinality' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This defines the cardinality of a descriptor, select one or more ' .
					'to define data cardinality.' ] ]
		);
		$term->PrepareInsert();
		$theTerms->Insert( $term->toArray() );
		$category = $theTerms->NewDocumentHandle( $term );

		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_CATEGORY_OF, $category, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum = $category;

		$src = $theTerms->BuildDocumentHandle( kKIND_LIST );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_string = $src;

		$src = $theTerms->BuildDocumentHandle( kKIND_SUMMARY );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_string = $src;

		$src = $theTerms->BuildDocumentHandle( kKIND_LOOKUP );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $enum );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_string = $src;

	} // initDataKind.


	/*===================================================================================
	 *	initNodeKind																	*
	 *==================================================================================*/

	/**
	 * <h4>Init node kind type.</h4>
	 *
	 * This method will load the node kind type.
	 *
	 * @param string				$theNamespace		Namespace term key.
	 * @param Collection			$theTerms			Terms collection.
	 * @param Collection			$theTypes			Types collection.
	 */
	protected function initNodeKind( $theNamespace, Collection $theTerms,
									 				Collection $theTypes )
	{
		//
		// kTAG_NODE_KIND
		//
		$term = new Term( $theTerms, [ kTAG_NS => $theNamespace,
				kTAG_LID => 'node-kind', kTAG_SYMBOL => 'kTAG_NODE_KIND',
				kTAG_NODE_KIND => kKIND_TYPE,
				kTAG_NAME => [ 'en' => 'Node kind' ],
				kTAG_DESCRIPTION => [ 'en' =>
					'This defines the kind or function of a graph node, select one ' .
					'or many elements.' ] ]
		);
		$term->PrepareInsert();
		$theTerms->Insert( $term->toArray() );
		$type = $theTerms->NewDocumentHandle( $term );

		$src = $theTerms->BuildDocumentHandle( kKIND_ROOT );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_mixed = $src;

		$src = $theTerms->BuildDocumentHandle( kKIND_TYPE );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_mixed = $src;

		$src = $theTerms->BuildDocumentHandle( kKIND_CATEGORY );
		$pred = Predicate::NewPredicate(
			$theTypes, kPREDICATE_ENUM_OF, $src, $type );
		$pred->PrepareInsert();
		$theTypes->Insert( $pred->toArray() );
		$enum_mixed = $src;

	} // initNodeKind.



} // trait tWrapper.


?>
