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
	 * <h4>DHS namespace key.</h4>
	 *
	 * This constant holds the <i>DHS namespace key</i>.
	 *
	 * @var string
	 */
	const kDHS_NAMESPACE = 'DHS';

	/**
	 * <h4>DHS descriptors URL.</h4>
	 *
	 * This constant holds the <i>DHS descriptors URL</i>.
	 *
	 * @var string
	 */
	const kDHS_URL_INDICATORS =
		'http://api.dhsprogram.com/rest/dhs/indicators/fields?f=json';

	/**
	 * <h4>DHS data descriptors URL.</h4>
	 *
	 * This constant holds the <i>DHS data descriptors URL</i>.
	 *
	 * @var string
	 */
	const kDHS_URL_DATA_INDICATORS =
		'http://api.dhsprogram.com/rest/dhs/data/fields?f=json';

	/**
	 * <h4>DHS country descriptors URL.</h4>
	 *
	 * This constant holds the <i>DHS country descriptors URL</i>.
	 *
	 * @var string
	 */
	const kDHS_URL_COUNTRY_INDICATORS =
		'http://api.dhsprogram.com/rest/dhs/countries/fields?f=json';

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
	 *	InitTypes																		*
	 *==================================================================================*/

	/**
	 * <h4>Initialise namespaces and types.</h4>
	 *
	 * This method will load the base namespaces and enumerated types.
	 */
	public function InitTypes()
	{
		//
		// Init local storage.
		//
		$types = $this->mDatabase->NewTypesCollection();
		$terms = $this->mDatabase->NewTermsCollection();
		$descriptors = $this->mDatabase->NewDescriptorsCollection();

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

		//
		// Instantiate DHS measurement type namespace.
		//
		$enum = new \Milko\PHPLib\Term( $terms );
		$enum[ kTAG_NS ] = $ns;
		$enum[ kTAG_LID ] = 'MeasurementType';
		$enum[ kTAG_NODE_KIND ] = [ kKIND_TYPE ];
		$enum[ kTAG_NAME ] = [ 'en' => "Measurement type" ];
		$dst = $enum->Store();

		//
		// Set measurement type enumerations.
		//
		$enums = [
			'Mean' => 'Mean',
			'Median' => 'Median',
			'Number' => 'Number',
			'Percent' => 'Percent',
			'Rate' => 'Rate'
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
		// Instantiate DHS indicator type namespace.
		//
		$enum = new \Milko\PHPLib\Term( $terms );
		$enum[ kTAG_NS ] = self::kDHS_NAMESPACE;
		$enum[ kTAG_LID ] = 'IndicatorType';
		$enum[ kTAG_NODE_KIND ] = [ kKIND_TYPE ];
		$enum[ kTAG_NAME ] = [ 'en' => "Indicator types" ];
		$dst = $enum->Store();

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
			'C' => 'Confidence interval'
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

	} // InitTypes.


	/*===================================================================================
	 *	InitIndicators																	*
	 *==================================================================================*/

	/**
	 * <h4>Initialise base descriptors.</h4>
	 *
	 * This method will load the base descriptors and load the descriptor match table.
	 */
	public function InitIndicators()
	{
		//
		// Init local storage.
		//
		$types = $this->mDatabase->NewTypesCollection();
		$terms = $this->mDatabase->NewTermsCollection();
		$descriptors = $this->mDatabase->NewDescriptorsCollection();

		//
		// Load base descriptors.
		//
		$indicators =
			json_decode( file_get_contents( self::kDHS_URL_INDICATORS ), TRUE )
			[ 'Data' ];

		//
		// Initialise match table.
		//
		$this->mMatchTable = [ 'Label' => kTAG_NAME, 'Definition' => kTAG_DESCRIPTION ];

		//
		// Load match table.
		//
		foreach( $indicators as $indicator )
		{
			//
			// Skip default or unused descriptors.
			//
			if( in_array( $indicator[ 'fieldname' ], ['Label', 'Definition'] ) )
				continue;														// =>

			//
			// Init descriptor.
			//
			$descriptor = new \Milko\PHPLib\Descriptor( $descriptors );

			//
			// Set identifiers.
			//
			$descriptor[ kTAG_NS ] = self::kDHS_NAMESPACE;
			$descriptor[ kTAG_LID ] = $indicator[ 'fieldname' ];
			$descriptor[ kTAG_SYMBOL ] = $indicator[ 'fieldname' ];

			//
			// Set names.
			//
			$descriptor[ kTAG_NAME ] = [ 'en' => $indicator[ 'fieldname' ] ];
			$descriptor[ kTAG_DESCRIPTION ] =
				[ 'en' => str_replace( "\t", '', $indicator[ 'fieldDescription' ] ) ];

			//
			// Set data types.
			//
			switch( $indicator[ 'fieldname' ] )
			{
				case 'IndicatorId':
				case 'IndicatorOldId':
				case 'Level1':
				case 'Level2':
				case 'Level3':
				case 'Denominator':
				case 'ShortName':
				case 'ByLabels':
					$descriptor[ kTAG_DATA_TYPE ] = kTYPE_STRING;
					$descriptor[ kTAG_DATA_KIND ] = [ kKIND_DISCRETE ];
					break;

				case 'IndicatorOrder':
				case 'NumberScale':
				case 'QuickStatOrder':
				case 'SDRID':
					$descriptor[ kTAG_DATA_TYPE ] = kTYPE_INT;
					$descriptor[ kTAG_DATA_KIND ] = [ kKIND_DISCRETE ];
					break;

				case 'MeasurementType':
				case 'IndicatorType':
					$descriptor[ kTAG_DATA_TYPE ] = kTYPE_ENUM;
					$descriptor[ kTAG_DATA_KIND ] = [ kKIND_CATEGORICAL ];
					break;

				case 'TagIds':
					$descriptor[ kTAG_DATA_TYPE ] = kTYPE_ENUM_SET;
					$descriptor[ kTAG_DATA_KIND ] = [ kKIND_CATEGORICAL ];
					break;

				case 'IsQuickStat':
					$descriptor[ kTAG_DATA_TYPE ] = kTYPE_BOOLEAN;
					$descriptor[ kTAG_DATA_KIND ] = [ kKIND_DISCRETE ];
					break;

			} // Setting data types.

			//
			// Store descriptor.
			//
			$handle = $descriptor->Store();

			//
			// Set match table entry.
			//
			if( ! array_key_exists( $indicator[ 'fieldname' ], $this->mMatchTable ) )
				$this->mMatchTable[ $indicator[ 'fieldname' ] ]
					= $descriptor[ $descriptors->KeyOffset() ];

			//
			// Connect enumeration types.
			//
			switch( $indicator[ 'fieldname' ] )
			{
				case 'MeasurementType':
					$pred =
						\Milko\PHPLib\Predicate::NewPredicate(
							$types,
							kPREDICATE_TYPE_OF,
							$terms->FindByKey(
								$descriptor[ kTAG_GID ],
								[ kTOKEN_OPT_MANY => FALSE,
									kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] ),
							$handle );
					$pred->Store();
					$this->mDatabase->SetDescriptor( $descriptor );
					break;

				case 'IndicatorType':
					$pred =
						\Milko\PHPLib\Predicate::NewPredicate(
							$types,
							kPREDICATE_TYPE_OF,
							$terms->FindByKey(
								$descriptor[ kTAG_GID ],
								[ kTOKEN_OPT_MANY => FALSE,
									kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] ),
							$handle );
					$pred->Store();
					$this->mDatabase->SetDescriptor( $descriptor );
					break;

			} // Setting enumerated types.

		} // Loading descriptors.

	} // InitIndicators.


	/*===================================================================================
	 *	InitDataIndicators																*
	 *==================================================================================*/

	/**
	 * <h4>Initialise data descriptors.</h4>
	 *
	 * This method will load the data descriptors and load the descriptor match table.
	 */
	public function InitDataIndicators()
	{
		//
		// Init local storage.
		//
		$types = $this->mDatabase->NewTypesCollection();
		$terms = $this->mDatabase->NewTermsCollection();
		$descriptors = $this->mDatabase->NewDescriptorsCollection();

		//
		// Load base descriptors.
		//
		$indicators =
			json_decode( file_get_contents( self::kDHS_URL_DATA_INDICATORS ), TRUE )
			[ 'Data' ];

		//
		// Load match table.
		//
		foreach( $indicators as $indicator )
		{
			//
			// Init descriptor.
			//
			$descriptor = new \Milko\PHPLib\Descriptor( $descriptors );

			//
			// Set identifiers.
			//
			$descriptor[ kTAG_NS ] = self::kDHS_NAMESPACE;
			$descriptor[ kTAG_LID ] = $indicator[ 'fieldname' ];
			$descriptor[ kTAG_SYMBOL ] = $indicator[ 'fieldname' ];

			//
			// Set names.
			//
			$descriptor[ kTAG_NAME ] = [ 'en' => $indicator[ 'fieldname' ] ];
			$descriptor[ kTAG_DESCRIPTION ] =
				[ 'en' => str_replace( "\t", '', $indicator[ 'fieldDescription' ] ) ];

			//
			// Set data types.
			//
			switch( $indicator[ 'fieldname' ] )
			{
				case 'Indicator':
				case 'CountryName':
				case 'SurveyId':
				case 'IndicatorId':
				case 'CharacteristicLabel':
				case 'ByVariableLabel':
				case 'SurveyYearLabel':
					$descriptor[ kTAG_DATA_TYPE ] = kTYPE_STRING;
					$descriptor[ kTAG_DATA_KIND ] = [ kKIND_DISCRETE ];
					break;

				case 'CharacteristicCategory':
				case 'CharacteristicId':
				case 'RegionId':
				case 'SurveyType':
					$descriptor[ kTAG_DATA_TYPE ] = kTYPE_STRING;
					$descriptor[ kTAG_DATA_KIND ] = [ kKIND_CATEGORICAL, kKIND_SUMMARY ];
					break;

				case 'DataId':
				case 'Precision':
				case 'SurveyYear':
				case 'CharacteristicOrder':
				case 'ByVariableId':
					$descriptor[ kTAG_DATA_TYPE ] = kTYPE_INT;
					$descriptor[ kTAG_DATA_KIND ] = [ kKIND_DISCRETE ];
					break;

				case 'DenominatorWeighted':
				case 'DenominatorUnweighted':
					$descriptor[ kTAG_DATA_TYPE ] = kTYPE_INT;
					$descriptor[ kTAG_DATA_KIND ] = [ kKIND_QUANTITATIVE ];
					break;

				case 'Value':
				case 'CILow':
				case 'CIHigh':
					$descriptor[ kTAG_DATA_TYPE ] = kTYPE_FLOAT;
					$descriptor[ kTAG_DATA_KIND ] = [ kKIND_QUANTITATIVE ];
					break;

				case 'DHS_CountryCode':
					$descriptor[ kTAG_DATA_TYPE ] = kTYPE_ENUM;
					$descriptor[ kTAG_DATA_KIND ] = [ kKIND_CATEGORICAL ];
					break;

				case 'IsTotal':
				case 'IsPreferred':
					$descriptor[ kTAG_DATA_TYPE ] = kTYPE_BOOLEAN;
					$descriptor[ kTAG_DATA_KIND ] = [ kKIND_DISCRETE ];
					break;

			} // Setting data types.

			//
			// Store descriptor.
			//
			$handle = $descriptor->Store();

			//
			// Set match table entry.
			//
			if( ! array_key_exists( $indicator[ 'fieldname' ], $this->mMatchTable ) )
				$this->mMatchTable[ $indicator[ 'fieldname' ] ]
					= $descriptor[ $descriptors->KeyOffset() ];

		} // Loading descriptors.

	} // InitDataIndicators.



} // class DHS.


?>
