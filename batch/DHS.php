<?php

/**
 * DHS.php
 *
 * This file contains the definition of the {@link DHS} class.
 */

/*
 * Global includes.
 */
require_once(dirname(__DIR__) . "/includes.local.php");

/*
 * Local includes.
 */
require_once(dirname(__DIR__) . "/defines.inc.php");

/*
 * Driver includes.
 */
if( kENGINE == "MONGO" )
	require_once(dirname(__DIR__) . "/mongo.local.php");
elseif( kENGINE == "ARANGO" )
	require_once(dirname(__DIR__) . "/arango.local.php");

/*=======================================================================================
 *																						*
 *										DHS.php											*
 *																						*
 *======================================================================================*/

/**
 * <h4>DHS object.</h4>
 *
 * This class implements a DHS data and metadata repository, it can be used to initialise
 * the data dictionary with DHS data provided by the
 * {@link http://api.dhsprogram.com/#/index.html} web services.
 *
 * The class features methods to initialise the metadata and import data.
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		13/05/2016
 */
class DHS
{
	/**
	 * <h4>URL read retries.</h4>
	 *
	 * This constant holds the <i>number of times to retry an URL</i>.
	 *
	 * @var int
	 */
	const kRETRIES = 10;

	/**
	 * <h4>URL read retries interval.</h4>
	 *
	 * This constant holds the <i>number of seconds between URL retries</i>.
	 *
	 * @var int
	 */
	const kRETRIES_INTERVAL = 10;

	/**
	 * <h4>DHS namespace key.</h4>
	 *
	 * This constant holds the <i>DHS namespace key</i>.
	 *
	 * @var string
	 */
	const kDHS_NAMESPACE = 'DHS';

	/**
	 * <h4>DHS descriptors file path.</h4>
	 *
	 * This constant holds the <i>DHS descriptors file path</i>.
	 *
	 * @var string
	 */
	const kDHS_PATH_INDICATORS =
		kPATH_LIBRARY_ROOT . 'data/indicators.dhs.csv';

	/**
	 * <h4>DHS data descriptors URL.</h4>
	 *
	 * This constant holds the <i>DHS data descriptors URL</i>.
	 *
	 * @var string
	 */
	const kDHS_URL_INDICATORS =
		'http://api.dhsprogram.com/rest/dhs/indicators?f=json';

	/**
	 * <h4>DHS surveys URL.</h4>
	 *
	 * This constant holds the <i>DHS surveys URL</i>.
	 *
	 * @var string
	 */
	const kDHS_URL_SURVEYS =
		'http://api.dhsprogram.com/rest/dhs/surveys?f=json';

	/**
	 * <h4>DHS data URL.</h4>
	 *
	 * This constant holds the <i>DHS data URL</i>.
	 *
	 * @var string
	 */
	const kDHS_URL_DATA =
		'http://api.dhsprogram.com/rest/dhs/data/';

	/**
	 * <h4>DHS country codes URL.</h4>
	 *
	 * This constant holds the <i>DHS country codes URL</i>.
	 *
	 * @var string
	 */
	const kDHS_URL_COUNTRY_CODES =
		'http://api.dhsprogram.com/rest/dhs/countries?f=json';

	/**
	 * <h4>DHS survey characteristics codes URL.</h4>
	 *
	 * This constant holds the <i>DHS survey characteristics codes URL</i>.
	 *
	 * @var string
	 */
	const kDHS_URL_SURVEY_CHARACTERISTICS =
		'http://api.dhsprogram.com/rest/dhs/surveycharacteristics?f=json';

	/**
	 * <h4>DHS tags URL.</h4>
	 *
	 * This constant holds the <i>DHS tags URL</i>.
	 *
	 * @var string
	 */
	const kDHS_URL_TAGS =
		'http://api.dhsprogram.com/rest/dhs/tags?f=json';

	/**
	 * <h4>Wrapper object.</h4>
	 *
	 * This data member holds the <i>database object</i> that contains data and metadata.
	 *
	 * @var \Milko\PHPLib\MongoDB\Wrapper|\Milko\PHPLib\ArangoDB\Wrapper
	 */
	protected $mDatabase = NULL;

	/**
	 * <h4>Namespace object.</h4>
	 *
	 * This data member holds the <i>DHS namespace term</i>.
	 *
	 * @var  \Milko\PHPLib\Term
	 */
	protected $mNamespace = NULL;

	/**
	 * <h4>Descriptors match table.</h4>
	 *
	 * This data member holds the <i>descriptors match table</i>, it is an array whose
	 * keys represent the DHS variable names and the values the descriptor offsets.
	 *
	 * @var array
	 */
	protected $mMatchTable = [];




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
	 * Instances of this class are instantiated by using global definitions:
	 * 
	 * <ul>
	 * 	<li><tt>{@link kENGINE}</tt>: The database engine:
	 * 	 <ul>
	 * 		<li><tt>MONGO</tt>: Use MongoDB.
	 * 		<li><tt>ARANGO</tt>: Use ArangoDB.
	 * 	 </ul>
	 * 	<li><tt>{@link kDSN}</tt>: The Data Source Name of the database server.
	 * 	<li><tt>{@link kDB}</tt>: The database name.
	 * 	<li><tt>{@link kSESSION_CACHE_ID}</tt>: The memcached persistent identifier.
	 * 	<li><tt>{@link kSESSION_CACHE_HOST}</tt>: The memcached default host.
	 * 	<li><tt>{@link kSESSION_CACHE_PORT}</tt>: The memcached default port.
	 * </ul>
	 *
	 * The provided parameter is a boolean switch that, if <tt>true</tt>, will drop and
	 * initialise the database, so be careful when overriding the default value.
	 *
	 * @param bool					$doInitDatabase		Initialise database.
	 * @throws RuntimeException
	 */
	public function __construct( $doInitDatabase = FALSE )
	{
		//
		// Instantiate server.
		//
		switch( kENGINE )
		{
			case "MONGO":
				$server = new \Milko\PHPLib\MongoDB\Server( kDSN );
				break;

			case "ARANGO":
				$server = new \Milko\PHPLib\ArangoDB\Server( kDSN );
				break;

			default:
				throw new RuntimeException(
					"Invalid database engine [" . kENGINE . "]." );				// !@! ==>

		} // Parsing engine.

		//
		// Drop database.
		//
		if( $doInitDatabase )
		{
			//
			// Instantiate database.
			//
			$tmp = $server->NewDatabase( kDB );
			$tmp->Drop();

		} // Initialise database.

		//
		// Instantiate wrapper.
		//
		$this->mDatabase = $server->NewWrapper( kDB );

		//
		// Cache data dictionary.
		//
		if( $doInitDatabase )
			$this->mDatabase->CacheDataDictionary();

	} // Constructor.



/*=======================================================================================
 *																						*
 *						PUBLIC DATA DICTIONARY INITIALISATION INTERFACE					*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	InitNamespaces																	*
	 *==================================================================================*/

	/**
	 * <h4>Initialise namespaces.</h4>
	 *
	 * This method will load the DHS namespaces.
	 */
	public function InitNamespaces()
	{
		//
		// Init local storage.
		//
		$terms = $this->mDatabase->NewTermsCollection();

		//
		// Instantiate DHS namespace.
		//
		$ns = new \Milko\PHPLib\Term( $terms );
		$ns[ kTAG_LID ] = self::kDHS_NAMESPACE;
		$ns[ kTAG_NAME ] = [ 'en' => "Demographic and Health Surveys (DHS) Program" ];
		$ns[ kTAG_DESCRIPTION ] = [ 'en' => "This namespace groups all metadata " .
			"regarding the USAID Demographic and Health " .
			"Surveys" ];
		$ns->Store();

	} // InitNamespaces.


	/*===================================================================================
	 *	InitDescriptors																	*
	 *==================================================================================*/

	/**
	 * <h4>Initialise base descriptors.</h4>
	 *
	 * This method will load the base descriptors and load the descriptor match table.
	 */
	public function InitDescriptors()
	{
		//
		// Init local storage.
		//
		$descriptors = $this->mDatabase->NewDescriptorsCollection();

		//
		// Open file.
		//
		$fp = fopen( self::kDHS_PATH_INDICATORS, "r" );
		if( $fp === FALSE )
			throw new RuntimeException(
				"Unable to open file [" . self::kDHS_PATH_INDICATORS . "]."
			);																	// !@! ==>
		
		//
		// Iterate descriptors.
		//
		$i = 1;
		while( ($data = fgetcsv( $fp, 4096, "," )) !== FALSE )
		{
			//
			// Check format.
			//
			if( count( $data ) == 4 )
			{
				//
				// Init descriptor.
				//
				$descriptor = new \Milko\PHPLib\Descriptor( $descriptors );

				//
				// Set identifiers.
				//
				$descriptor[ kTAG_NS ] = self::kDHS_NAMESPACE;
				$descriptor[ kTAG_LID ] = $data[ 0 ];
				$descriptor[ kTAG_SYMBOL ] = $data[ 0 ];

				//
				// Set types.
				//
				$descriptor[ kTAG_DATA_TYPE ] = $data[ 2 ];
				$descriptor[ kTAG_DATA_KIND ] = [];
				$tmp = [];
				foreach( explode( ',', $data[ 3 ] ) as $kind )
					$tmp[] = $kind;
				$descriptor[ kTAG_DATA_KIND ] = $tmp;

				//
				// Set names.
				//
				$descriptor[ kTAG_NAME ] = [ 'en' => $data[ 0 ] ];
				$descriptor[ kTAG_DESCRIPTION ] = [ 'en' => $data[ 1 ] ];

				//
				// Store descriptor.
				//
				$handle = $descriptor->Store();

				//
				// Set match table entry.
				//
				if( ! array_key_exists( strtolower( $data[ 0 ] ), $this->mMatchTable ) )
					$this->mMatchTable[ strtolower( $data[ 0 ] ) ]
						= $descriptor[ $descriptors->KeyOffset() ];

				//
				// Line counter.
				//
				$i++;

			} // Has 4 elements.

			else
				throw new RuntimeException(
					"Invalid data at line [$i]."
				);																// !@! ==>

		} // Iterating file.

		//
		// Close file.
		//
		fclose( $fp );

	} // InitDescriptors.


	/*===================================================================================
	 *	InitCountries																	*
	 *==================================================================================*/

	/**
	 * <h4>Initialise country codes.</h4>
	 *
	 * This method will load the country codes.
	 */
	public function InitCountries()
	{
		//
		// Init local storage.
		//
		$types = $this->mDatabase->NewTypesCollection();
		$terms = $this->mDatabase->NewTermsCollection();
		$descriptors = $this->mDatabase->NewDescriptorsCollection();
		$indicator = $this->mMatchTable[ strtolower( 'DHS_CountryCode' ) ];

		//
		// Instantiate DHS country code namespace.
		//
		$term = new \Milko\PHPLib\Term( $terms );
		$term[ kTAG_NS ] = self::kDHS_NAMESPACE;
		$term[ kTAG_LID ] = 'DHS_CountryCode';
		$term[ kTAG_NODE_KIND ] = [ kKIND_TYPE ];
		$term[ kTAG_NAME ] = [ 'en' => "DHS country code" ];
		$term->Store();
		$ns = $term[ kTAG_GID ];

		//
		// Get country code descriptor handle.
		//
		$descriptor = $descriptors->BuildDocumentHandle( $indicator );

		//
		// Read country codes.
		//
		$retries = self::kRETRIES;
		while( $retries-- )
		{
			$countries =
				json_decode( file_get_contents( self::kDHS_URL_COUNTRY_CODES ), TRUE )
				[ 'Data' ];
			if( $countries === FALSE )
				sleep( self::kRETRIES_INTERVAL );
			else
				break;															// =>

		} // Trying URL.

		//
		// Iterate codes.
		//
		foreach( $countries as $country )
		{
			//
			// Instantiate enumeration.
			//
			$term = new \Milko\PHPLib\Term( $terms );
			$term[ kTAG_NS ] = $ns;
			$term[ kTAG_LID ] = $country[ 'DHS_CountryCode' ];
			$term[ kTAG_NAME ] = [ 'en' => $country[ 'CountryName' ] ];
			$term[ $this->mMatchTable[ strtolower( 'RegionName' ) ] ]
				= $country[ 'RegionName' ];
			$term[ $this->mMatchTable[ strtolower( 'SubregionName' ) ] ]
				= $country[ 'SubregionName' ];
			$term[ $this->mMatchTable[ strtolower( 'RegionOrder' ) ] ]
				= $country[ 'RegionOrder' ];
			$term[ $this->mMatchTable[ strtolower( 'FIPS_CountryCode' ) ] ]
				= $country[ 'FIPS_CountryCode' ];
			$term[ $this->mMatchTable[ strtolower( 'ISO2_CountryCode' ) ] ]
				= $country[ 'ISO2_CountryCode' ];
			$term[ $this->mMatchTable[ strtolower( 'ISO3_CountryCode' ) ] ]
				= $country[ 'ISO3_CountryCode' ];
			$term[ $this->mMatchTable[ strtolower( 'UNAIDS_CountryCode' ) ] ]
				= $country[ 'UNAIDS_CountryCode' ];
			$term[ $this->mMatchTable[ strtolower( 'UNICEF_CountryCode' ) ] ]
				= $country[ 'UNICEF_CountryCode' ];
			$term[ $this->mMatchTable[ strtolower( 'UNSTAT_CountryCode' ) ] ]
				= $country[ 'UNSTAT_CountryCode' ];
			$term[ $this->mMatchTable[ strtolower( 'WHO_CountryCode' ) ] ]
				= $country[ 'WHO_CountryCode' ];
			$enum = $term->Store();

			//
			// Store predicate.
			//
			$pred =
				\Milko\PHPLib\Predicate::NewPredicate(
					$types,
					kPREDICATE_ENUM_OF,
					$enum,
					$descriptor );
			$pred->Store();

		} // Iterating country codes.

		//
		// Update descriptor in cache.
		//
		$this->mDatabase->SetDescriptor( $descriptors->FindByKey( $indicator ) );

	} // InitCountries.


	/*===================================================================================
	 *	InitMeasurementTypes															*
	 *==================================================================================*/

	/**
	 * <h4>Initialise measurement types.</h4>
	 *
	 * This method will load the measurement type enumerations.
	 */
	public function InitMeasurementTypes()
	{
		//
		// Init local storage.
		//
		$types = $this->mDatabase->NewTypesCollection();
		$terms = $this->mDatabase->NewTermsCollection();
		$descriptors = $this->mDatabase->NewDescriptorsCollection();
		$indicator = $this->mMatchTable[ strtolower( 'MeasurementType' ) ];
		$dst =
			$descriptors->FindByKey(
				$indicator,
				[ kTOKEN_OPT_MANY => FALSE,
				  kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ]
			);

		//
		// Instantiate DHS measurement type namespace.
		//
		$enum = new \Milko\PHPLib\Term( $terms );
		$enum[ kTAG_NS ] = self::kDHS_NAMESPACE;
		$enum[ kTAG_LID ] = 'MeasurementType';
		$enum[ kTAG_NODE_KIND ] = [ kKIND_TYPE ];
		$enum[ kTAG_NAME ] = [ 'en' => "Measurement type" ];
		$enum->Store();

		//
		// Set measurement type enumerations.
		//
		$enums = [
			'Mean' => 'Mean',
			'Median' => 'Median',
			'Number' => 'Number',
			'Percent' => 'Percent',
			'Rate' => 'Rate',
			'Ratio' => 'Ratio'
		];
		foreach( $enums as $key => $name )
		{
			$term = new \Milko\PHPLib\Term( $terms );
			$term[ kTAG_NS ] = $enum[ $terms->KeyOffset() ];
			$term[ kTAG_LID ] = $key;
			$term[ kTAG_NAME ] = [ 'en' => $name ];
			$src = $term->Store();
			$pred =
				\Milko\PHPLib\Predicate::NewPredicate(
					$types, kPREDICATE_ENUM_OF, $src, $dst );
			$pred->Store();
		}

		//
		// Update descriptor in cache.
		//
		$this->mDatabase->SetDescriptor( $descriptors->FindByKey( $indicator ) );

	} // InitMeasurementTypes.


	/*===================================================================================
	 *	InitIndicatorTypes																*
	 *==================================================================================*/

	/**
	 * <h4>Initialise measurement types.</h4>
	 *
	 * This method will load the measurement type enumerations.
	 */
	public function InitIndicatorTypes()
	{
		//
		// Init local storage.
		//
		$types = $this->mDatabase->NewTypesCollection();
		$terms = $this->mDatabase->NewTermsCollection();
		$descriptors = $this->mDatabase->NewDescriptorsCollection();
		$indicator = $this->mMatchTable[ strtolower( 'IndicatorType' ) ];
		$dst =
			$descriptors->FindByKey(
				$indicator,
				[ kTOKEN_OPT_MANY => FALSE,
				  kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ]
			);

		//
		// Instantiate DHS measurement type namespace.
		//
		$enum = new \Milko\PHPLib\Term( $terms );
		$enum[ kTAG_NS ] = self::kDHS_NAMESPACE;
		$enum[ kTAG_LID ] = 'IndicatorType';
		$enum[ kTAG_NODE_KIND ] = [ kKIND_TYPE ];
		$enum[ kTAG_NAME ] = [ 'en' => "Indicator type" ];
		$enum->Store();

		//
		// Set indicator type enumerations.
		//
		$enums = [
			'I' => 'Indicator',
			'D' => 'Weighted denominator',
			'U' => 'Unweighted denominator',
			'T' => 'Distribution total (100%)',
			'S' => 'Special answers (don\'t know/missing)',
			'E' => 'Sampling errors',
			'C' => 'Confidence interval',
			'N' => '???'
		];
		foreach( $enums as $key => $name )
		{
			$term = new \Milko\PHPLib\Term( $terms );
			$term[ kTAG_NS ] = $enum[ $terms->KeyOffset() ];
			$term[ kTAG_LID ] = $key;
			$term[ kTAG_NAME ] = [ 'en' => $name ];
			$src = $term->Store();
			$pred =
				\Milko\PHPLib\Predicate::NewPredicate(
					$types, kPREDICATE_ENUM_OF, $src, $dst );
			$pred->Store();
		}

		//
		// Update descriptor in cache.
		//
		$this->mDatabase->SetDescriptor( $descriptors->FindByKey( $indicator ) );

	} // InitIndicatorTypes.


	/*===================================================================================
	 *	InitSurveyCharacteristics														*
	 *==================================================================================*/

	/**
	 * <h4>Initialise survey characteristics.</h4>
	 *
	 * This method will load the survey characteristics.
	 */
	public function InitSurveyCharacteristics()
	{
		//
		// Init local storage.
		//
		$types = $this->mDatabase->NewTypesCollection();
		$terms = $this->mDatabase->NewTermsCollection();
		$descriptors = $this->mDatabase->NewDescriptorsCollection();
		$indicator = $this->mMatchTable[ strtolower( 'SurveyCharacteristicIds' ) ];

		//
		// Instantiate DHS survey characteristics namespace.
		//
		$term = new \Milko\PHPLib\Term( $terms );
		$term[ kTAG_NS ] = self::kDHS_NAMESPACE;
		$term[ kTAG_LID ] = 'SurveyCharacteristicIds';
		$term[ kTAG_NODE_KIND ] = [ kKIND_TYPE ];
		$term[ kTAG_NAME ] = [ 'en' => "DHS survey characteristics" ];
		$term->Store();
		$ns = $term[ kTAG_GID ];

		//
		// Get survey characteristics descriptor handle.
		//
		$descriptor = $descriptors->BuildDocumentHandle( $indicator );

		//
		// Read survey characteristics.
		//
		$retries = self::kRETRIES;
		while( $retries-- )
		{
			$list =
				json_decode( file_get_contents( self::kDHS_URL_SURVEY_CHARACTERISTICS ), TRUE )
				[ 'Data' ];
			if( $list === FALSE )
				sleep( self::kRETRIES_INTERVAL );
			else
				break;															// =>

		} // Trying URL.

		//
		// Iterate codes.
		//
		foreach( $list as $element )
		{
			//
			// Instantiate enumeration.
			//
			$term = new \Milko\PHPLib\Term( $terms );
			$term[ kTAG_NS ] = $ns;
			$term[ kTAG_LID ] = $element[ 'SurveyCharacteristicID' ];
			$term[ kTAG_NAME ] = [ 'en' => $element[ 'SurveyCharacteristicName' ] ];
			$enum = $term->Store();

			//
			// Store predicate.
			//
			$pred =
				\Milko\PHPLib\Predicate::NewPredicate(
					$types,
					kPREDICATE_ENUM_OF,
					$enum,
					$descriptor );
			$pred->Store();

		} // Iterating country codes.

		//
		// Update descriptor in cache.
		//
		$this->mDatabase->SetDescriptor( $descriptors->FindByKey( $indicator ) );

	} // InitSurveyCharacteristics.


	/*===================================================================================
	 *	InitTags																		*
	 *==================================================================================*/

	/**
	 * <h4>Initialise survey characteristics.</h4>
	 *
	 * This method will load the survey characteristics.
	 */
	public function InitTags()
	{
		//
		// Init local storage.
		//
		$types = $this->mDatabase->NewTypesCollection();
		$terms = $this->mDatabase->NewTermsCollection();
		$descriptors = $this->mDatabase->NewDescriptorsCollection();
		$indicator = $this->mMatchTable[ strtolower( 'TagIds' ) ];

		//
		// Instantiate DHS survey characteristics namespace.
		//
		$term = new \Milko\PHPLib\Term( $terms );
		$term[ kTAG_NS ] = self::kDHS_NAMESPACE;
		$term[ kTAG_LID ] = 'TagIds';
		$term[ kTAG_NODE_KIND ] = [ kKIND_TYPE ];
		$term[ kTAG_NAME ] = [ 'en' => "DHS tags" ];
		$term->Store();
		$ns = $term[ kTAG_GID ];

		//
		// Get survey characteristics descriptor handle.
		//
		$descriptor = $descriptors->BuildDocumentHandle( $indicator );

		//
		// Read tags.
		//
		$retries = self::kRETRIES;
		while( $retries-- )
		{
			$list =
				json_decode( file_get_contents( self::kDHS_URL_TAGS ), TRUE )
				[ 'Data' ];
			if( $list === FALSE )
				sleep( self::kRETRIES_INTERVAL );
			else
				break;															// =>

		} // Trying URL.

		//
		// Iterate codes.
		//
		foreach( $list as $element )
		{
			//
			// Instantiate enumeration.
			//
			$term = new \Milko\PHPLib\Term( $terms );
			$term[ kTAG_NS ] = $ns;
			$term[ kTAG_LID ] = $element[ 'TagID' ];
			$term[ kTAG_NAME ] = [ 'en' => $element[ 'TagName' ] ];
			$term[ $this->mMatchTable[ strtolower( 'TagType' ) ] ]
				= $element[ 'TagType' ];
			$term[ $this->mMatchTable[ strtolower( 'TagOrder' ) ] ]
				= $element[ 'TagOrder' ];
			$enum = $term->Store();

			//
			// Store predicate.
			//
			$pred =
				\Milko\PHPLib\Predicate::NewPredicate(
					$types,
					kPREDICATE_ENUM_OF,
					$enum,
					$descriptor );
			$pred->Store();

		} // Iterating country codes.

		//
		// Update descriptor in cache.
		//
		$this->mDatabase->SetDescriptor( $descriptors->FindByKey( $indicator ) );

	} // InitTags.


	/*===================================================================================
	 *	InitIndicators																	*
	 *==================================================================================*/

	/**
	 * <h4>Initialise indicators.</h4>
	 *
	 * This method will load the indicators.
	 */
	public function InitIndicators()
	{
		//
		// Init local storage.
		//
		$descriptors = $this->mDatabase->NewDescriptorsCollection();
		$page = 1;
		$lines = 100;
		$url = self::kDHS_URL_INDICATORS . "&page=$page&perpage=$lines";

		//
		// Read indicators.
		//
		$retries = self::kRETRIES;
		while( $retries-- )
		{
			$records = json_decode( file_get_contents( $url ), TRUE );
			if( $records === FALSE )
				sleep( self::kRETRIES_INTERVAL );
			else
				break;															// =>

		} // Trying URL.

		//
		// Iterate indicators.
		//
		$count = $records[ 'RecordCount' ];
		$counter = $total = ceil( $count/11 );
		while( count( $records[ 'Data' ] ) )
		{
			//
			// Get data reference.
			//
			$data = & $records[ 'Data' ];

			//
			// Iterate lines.
			//
			foreach( $data as $line )
			{
				//
				// Init descriptor.
				//
				$descriptor = new \Milko\PHPLib\Descriptor( $descriptors );

				//
				// Set identifiers.
				//
				$descriptor[ kTAG_NS ] = self::kDHS_NAMESPACE;
				$descriptor[ kTAG_LID ] = $line[ 'IndicatorId' ];
				$descriptor[ kTAG_SYMBOL ] = $line[ 'IndicatorId' ];

				//
				// Set data types.
				//
				if( $line[ 'NumberScale' ] )
					$descriptor[ kTAG_DATA_TYPE ] = kTYPE_FLOAT;
				else
					$descriptor[ kTAG_DATA_TYPE ] = kTYPE_INT;
				$descriptor[ kTAG_DATA_KIND ] = kKIND_QUANTITATIVE;

				//
				// Set names.
				//
				$descriptor[ kTAG_NAME ] = [ 'en' => $line[ 'Label' ] ];
				$descriptor[ kTAG_DESCRIPTION ] = [ 'en' => $line[ 'Definition' ] ];

				//
				// Set other data.
				//
				$fields = [
					'Level1', 'Level2', 'Level3',
					'IndicatorOrder', 'NumberScale', 'Denominator', 'ShortName',
					'MeasurementType', 'IndicatorType',
					'TagIds',
					'IsQuickStat', 'QuickStatOrder',
					'ByLabels', 'SDRID'
				];
				foreach( $fields as $field )
				{
					//
					// Skip empty data.
					//
					if( strlen( $value = trim( $line[ $field ] ) ) )
					{
						switch( strtolower( $field ) )
						{
							case strtolower( 'MeasurementType' ):
							case strtolower( 'IndicatorType' ):
								$value =
									self::kDHS_NAMESPACE . ':' . $field . ':' . $value;
								break;

							case strtolower( 'TagIds' ):
								$list = explode( ',', $value );
								$value = [];
								foreach( $list as $elm )
									$value[] =
										self::kDHS_NAMESPACE . ':' .
										$field . ':' . trim( $elm );
								break;
						}

						//
						// Set value.
						//
						$descriptor[ $this->mMatchTable[ strtolower( $field ) ] ] = $value;

					} // Has value.

				} // Iterating other fields.

				//
				// Save descriptor.
				//
				$descriptor->Store();

				//
				// Progress.
				//
				if( ! --$counter )
				{
					$counter = $total;
					echo( '.' );
				}

			} // Iterating lines.

			//
			// Get next.
			//
			$page++;
			$url = self::kDHS_URL_INDICATORS . "&page=$page&perpage=$lines";
			$retries = self::kRETRIES;
			while( $retries-- )
			{
				$records = json_decode( file_get_contents( $url ), TRUE );
				if( $records === FALSE )
					sleep( self::kRETRIES_INTERVAL );
				else
					break;															// =>

			} // Trying URL.

		} // Found indicators.

		//
		// Progress.
		//
		echo( '.' );

	} // InitIndicators.


	/*===================================================================================
	 *	InitSurveys																		*
	 *==================================================================================*/

	/**
	 * <h4>Initialise surveys.</h4>
	 *
	 * This method will load the surveys.
	 */
	public function InitSurveys()
	{
		//
		// Init local storage.
		//
		$surveys = $this->mDatabase->NewSurveysCollection();
		$page = 1;
		$lines = 100;
		$url = self::kDHS_URL_SURVEYS . "&page=$page&perpage=$lines";

		//
		// Read surveys.
		//
		$retries = self::kRETRIES;
		while( $retries-- )
		{
			$records = json_decode( file_get_contents( $url ), TRUE );
			if( $records === FALSE )
				sleep( self::kRETRIES_INTERVAL );
			else
				break;															// =>

		} // Trying URL.

		$count = $records[ 'RecordCount' ];
		$counter = $total = ceil( $count/14 );
		while( count( $records[ 'Data' ] ) )
		{
			//
			// Get data reference.
			//
			$data = & $records[ 'Data' ];

			//
			// Iterate lines.
			//
			foreach( $data as $line )
			{
				//
				// Init descriptor.
				//
				$document = new \Milko\PHPLib\Document( $surveys );

				//
				// Set identifiers.
				//
				$document[ $surveys->KeyOffset() ] = $line[ 'SurveyId' ];

				//
				// Set other data.
				//
				$fields = [
					'SurveyId', 'SurveyNum', 'DHS_CountryCode', 'SurveyYear', 'SurveyType',
					'SurveyYearLabel', 'IndicatorData', 'RegionName', 'SubregionName',
					'PublicationDate', 'ReleaseDate', 'SurveyCharacteristicIds',
					'FieldworkStart', 'FieldworkEnd', 'Footnotes', 'ImplementingOrg',
					'NumberOfSamplePoints', 'NumberofHouseholds',
					'UniverseOfWomen', 'NumberOfWomen', 'MinAgeWomen', 'MaxAgeWomen',
					'UniverseOfMen', 'NumberOfMen', 'MinAgeMen', 'MaxAgeMen',
					'NumberOfFacilities'
				];
				foreach( $fields as $field )
				{
					//
					// Skip empty data.
					//
					if( strlen( $value = trim( $line[ $field ] ) ) )
					{
						switch( strtolower( $field ) )
						{
							case strtolower( 'DHS_CountryCode' ):
								$value =
									self::kDHS_NAMESPACE . ':' . $field . ':' . $value;
								break;

							case strtolower( 'SurveyCharacteristicIds' ):
								$list = explode( ',', $value );
								$value = [];
								foreach( $list as $elm )
									$value[] =
										self::kDHS_NAMESPACE . ':' .
										$field . ':' . trim( $elm );
								break;
						}

						//
						// Set value.
						//
						$document[ $this->mMatchTable[ strtolower( $field ) ] ] = $value;

					} // Has value.

				} // Iterating other fields.
				
				//
				// Load survey data.
				//
				$data_points = $this->loadSurveyData( $line[ 'SurveyId' ] );
				if( count( $data_points ) )
					$document[ kTAG_DATA ] = $data_points;

				//
				// Save document.
				//
				$document->Store();

				//
				// Progress.
				//
				if( ! --$counter )
				{
					$counter = $total;
					echo( '.' );
				}

			} // Iterating lines.

			//
			// Get next.
			//
			$page++;
			$url = self::kDHS_URL_SURVEYS . "&page=$page&perpage=$lines";
			$retries = self::kRETRIES;
			while( $retries-- )
			{
				$records = json_decode( file_get_contents( $url ), TRUE );
				if( $records === FALSE )
					sleep( self::kRETRIES_INTERVAL );
				else
					break;															// =>

			} // Trying URL.

		} // Found indicators.

		//
		// Progress.
		//
		echo( '.' );

	} // InitSurveys.


	/*===================================================================================
	 *	InitData																		*
	 *==================================================================================*/

	/**
	 * <h4>Initialise surveys.</h4>
	 *
	 * This method will load the surveys.
	 */
	public function InitData()
	{
		//
		// Init local storage.
		//
		$collection = $this->mDatabase->NewDataCollection();
		$page = 1;
		$lines = 100;
		$url = self::kDHS_URL_DATA . "&page=$page&perpage=$lines";

		//
		// Read data.
		//
		$retries = self::kRETRIES;
		while( $retries-- )
		{
			$records = json_decode( file_get_contents( $url ), TRUE );
			if( $records === FALSE )
				sleep( self::kRETRIES_INTERVAL );
			else
				break;															// =>

		} // Trying URL.

		$count = $records[ 'RecordCount' ];
		$counter = $total = ceil( $count/17 );
		while( count( $records[ 'Data' ] ) )
		{
			//
			// Get data reference.
			//
			$data = & $records[ 'Data' ];

			//
			// Iterate lines.
			//
			foreach( $data as $line )
			{
				//
				// Init descriptor.
				//
				$document = new \Milko\PHPLib\Document( $collection );

				//
				// Set identifiers.
				//
				$document[ $collection->KeyOffset() ]
					= $line[ 'SurveyId' ] . ':' . $line[ 'DataId' ];

				//
				// Set other data.
				//
				$fields = [
					'DataId', 'Value', 'Precision', 'DHS_CountryCode',
					'SurveyYear', 'SurveyId', 'IndicatorId', 'IndicatorOrder',
					'CharacteristicId','CharacteristicOrder', 'CharacteristicCategory',
					'CharacteristicLabel', 'ByVariableId', 'ByVariableLabel',
					'IsTotal', 'IsPreferred', 'SDRID', 'RegionId', 'SurveyYearLabel',
					'SurveyType', 'DenominatorWeighted', 'DenominatorUnweighted',
					'CILow', 'CIHigh'
				];
				foreach( $fields as $field )
				{
					//
					// Skip empty data.
					//
					if( strlen( $value = trim( $line[ $field ] ) ) )
					{
						switch( strtolower( $field ) )
						{
							case strtolower( 'DHS_CountryCode' ):
								$value =
									self::kDHS_NAMESPACE . ':' . $field . ':' . $value;
								break;
						}

						//
						// Set value.
						//
						$document[ $this->mMatchTable[ strtolower( $field ) ] ] = $value;

					} // Has value.

				} // Iterating other fields.

				//
				// Normalise value.
				//
				$document[ $this->mMatchTable[ strtolower( "Value" ) ] ]
					= ( $document[ $this->mMatchTable[ strtolower( "Precision" ) ] ] )
					? (double)$document[ $this->mMatchTable[ strtolower( "Value" ) ] ]
					: (int)$document[ $this->mMatchTable[ strtolower( "Value" ) ] ];

				//
				// Save document.
				//
				$document->Store();

				//
				// Progress.
				//
				if( ! --$counter )
				{
					$counter = $total;
					echo( '.' );
				}

			} // Iterating lines.

			//
			// Get next.
			//
			$page++;
			$url = self::kDHS_URL_DATA . "&page=$page&perpage=$lines";
			$retries = self::kRETRIES;
			while( $retries-- )
			{
				$records = json_decode( file_get_contents( $url ), TRUE );
				if( $records === FALSE )
					sleep( self::kRETRIES_INTERVAL );
				else
					break;															// =>

			} // Trying URL.

		} // Found indicators.

		//
		// Progress.
		//
		echo( '.' );

	} // InitData.



/*=======================================================================================
 *																						*
 *					PROTECTED DATA DICTIONARY INITIALISATION INTERFACE					*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	loadSurveyData																	*
	 *==================================================================================*/

	/**
	 * <h4>Load survey data.</h4>
	 *
	 * This method will load the DHS survey data points, it will read all data points
	 * related to the provided survey ID, load them into the survey and store them into
	 * the data collection.
	 *
	 * @param string				$theSurvey			Survey ID.
	 * @return array				The list of survey data points.
	 */
	public function loadSurveyData( $theSurvey )
	{
		//
		// Init local storage.
		//
		$data_points = [];
		$collection = $this->mDatabase->NewDataCollection();
		$descriptors = $this->mDatabase->NewDescriptorsCollection();
		$page = 1;
		$lines = 100;
		$url = self::kDHS_URL_DATA . "$theSurvey?f=json&page=$page&perpage=$lines";

		//
		// Read data.
		//
		$retries = self::kRETRIES;
		while( $retries-- )
		{
			$records = json_decode( file_get_contents( $url ), TRUE );
			if( $records === FALSE )
				sleep( self::kRETRIES_INTERVAL );
			else
				break;															// =>

		} // Trying URL.

		//
		// Iterate data.
		//
		while( count( $records[ 'Data' ] ) )
		{
			//
			// Get data reference.
			//
			$data = & $records[ 'Data' ];

			//
			// Iterate lines.
			//
			foreach( $data as $line )
			{
				//
				// Init descriptor.
				//
				$document = new \Milko\PHPLib\Document( $collection );

				//
				// Set identifiers.
				//
				$document[ $collection->KeyOffset() ]
					= $line[ 'SurveyId' ] . ':' . $line[ 'DataId' ];

				//
				// Set other data.
				//
				$fields = [
					'DataId', 'Value', 'Precision', 'DHS_CountryCode',
					'SurveyYear', 'SurveyId', 'IndicatorId', 'IndicatorOrder',
					'CharacteristicId','CharacteristicOrder', 'CharacteristicCategory',
					'CharacteristicLabel', 'ByVariableId', 'ByVariableLabel',
					'IsTotal', 'IsPreferred', 'SDRID', 'RegionId', 'SurveyYearLabel',
					'SurveyType', 'DenominatorWeighted', 'DenominatorUnweighted',
					'CILow', 'CIHigh'
				];
				foreach( $fields as $field )
				{
					//
					// Skip empty data.
					//
					if( strlen( $value = trim( $line[ $field ] ) ) )
					{
						switch( strtolower( $field ) )
						{
							case strtolower( 'DHS_CountryCode' ):
								$value =
									self::kDHS_NAMESPACE . ':' . $field . ':' . $value;
								break;
						}

						//
						// Set value.
						//
						$document[ $this->mMatchTable[ strtolower( $field ) ] ] = $value;

					} // Has value.

				} // Iterating other fields.

				//
				// Normalise value.
				//
				$document[ $this->mMatchTable[ strtolower( "Value" ) ] ]
					= ( $document[ $this->mMatchTable[ strtolower( "Precision" ) ] ] )
					? (double)$document[ $this->mMatchTable[ strtolower( "Value" ) ] ]
					: (int)$document[ $this->mMatchTable[ strtolower( "Value" ) ] ];

				//
				// Save document.
				//
				$document->Store();

				//
				// Init data point structure.
				//
				$index = count( $data_points );
				$data_points[ $index ] = [];
				$point = & $data_points[ $index ];

				//
				// Add survey data.
				//
				$descr = self::kDHS_NAMESPACE . ':' . $line[ 'IndicatorId' ];
				$descriptor = \Milko\PHPLib\Descriptor::GetByGID( $descriptors, $descr );
				if( ! count( $descriptor ) )
					throw new RuntimeException(
						"Descriptor [$descr] not found." );						// !@! ==>
				$point[ $descriptor[ 0 ][ $descriptors->KeyOffset() ] ]
					= $document[ $this->mMatchTable[ strtolower( "Value" ) ] ];
				$point[ $this->mMatchTable[ strtolower( "Value" ) ] ]
					= (string)$line[ 'Value' ];
				$fields = [
					'CharacteristicId', 'CharacteristicCategory', 'IsTotal', 'IsPreferred',
					'SDRID', 'RegionId', 'DenominatorWeighted', 'DenominatorUnweighted',
					'CILow', 'CIHigh'
				];
				foreach( $fields as $field )
				{
					if( strlen( trim( $line[ $field ] ) ) )
						$point[ $this->mMatchTable[ strtolower( $field ) ] ]
							= $document[ $this->mMatchTable[ strtolower( $field ) ] ];
				}

			} // Iterating lines.

			//
			// Get next.
			//
			$page++;
			$url = self::kDHS_URL_DATA . "$theSurvey?f=json&page=$page&perpage=$lines";
			//
			// Read data.
			//
			$retries = self::kRETRIES;
			while( $retries-- )
			{
				$records = json_decode( file_get_contents( $url ), TRUE );
				if( $records === FALSE )
					sleep( self::kRETRIES_INTERVAL );
				else
					break;															// =>

			} // Trying URL.

		} // Found indicators.

		return $data_points;														// ==>

	} // loadSurveyData.



} // class DHS.


?>
