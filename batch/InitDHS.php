<?php

/**
 * Initialise DHS data dictionary
 *
 * This script can be used to initialise the DHS data dictionary, <em>be aware that this
 * script will erase any existing data dictionary if the {@link doINIT} definition is
 * <tt>TRUE</tt>, so use with caution.</em>
 *
 *	@package	Batch
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		13/05/2016
 */

/*
 * Initialisation definition.
 */
define( 'doINIT', TRUE );
define( 'kENGINE', 'MONGO' );

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

/*
 * Class includes.
 */
require_once( "DHS.php" );

//
// Enable exception logging.
//
//triagens\ArangoDb\Exception::enableLogging();


/*=======================================================================================
 *																						*
 *										MAIN											*
 *																						*
 *======================================================================================*/

//
// Inform.
//
echo( "\n************************************************************\n" );
echo( "* Init database:       " . (( doINIT )?"YES":"NO") . "\n" );
echo( "************************************************************\n" );
echo( "* Engine:              " . kENGINE . "\n" );
echo( "* Data source name:    " . kDSN . "\n" );
echo( "* Database name:       " . kDB . "\n" );
echo( "************************************************************\n" );
echo( "* Cache persistent ID: " . kSESSION_CACHE_ID . "\n" );
echo( "* Cache host:          " . kSESSION_CACHE_HOST . "\n" );
echo( "* Cache port:          " . kSESSION_CACHE_PORT . "\n" );
echo( "************************************************************\n" );

//
// Initialise DHS data dictionary.
//
echo( "- Initialising DHS data dictionary: .................. " );
$dhs = new DHS( doINIT );
echo( "Done.\n" );

//
// Initialise DHS namespaces.
//
echo( "- Initialising DHS namespaces: ....................... " );
$dhs->InitNamespaces();
echo( "Done.\n" );

//
// Initialise DHS descriptors.
//
echo( "- Initialising DHS descriptors: ...................... " );
$dhs->InitDescriptors();
echo( "Done.\n" );

//
// Initialise DHS countries.
//
echo( "- Initialising DHS countries: ........................ " );
$dhs->InitCountries();
echo( "Done.\n" );

//
// Initialise DHS measurement types.
//
echo( "- Initialising DHS measurement types: ................ " );
$dhs->InitMeasurementTypes();
echo( "Done.\n" );

//
// Initialise DHS indicator types.
//
echo( "- Initialising DHS indicator types: .................. " );
$dhs->InitIndicatorTypes();
echo( "Done.\n" );

//
// Initialise DHS survey characteristics.
//
echo( "- Initialising DHS survey characteristics: ........... " );
$dhs->InitSurveyCharacteristics();
echo( "Done.\n" );

//
// Initialise DHS tags.
//
echo( "- Initialising DHS tags: ............................. " );
$dhs->InitTags();
echo( "Done.\n" );

//
// Initialise DHS indicators.
//
echo( "- Initialising DHS indicators: " );
$dhs->InitIndicators();
echo( " Done.\n" );

//
// Initialise DHS surveys.
//
echo( "- Initialising DHS surveys: " );
$dhs->InitSurveys();
echo( " Done.\n" );

//
// Initialise DHS data.
//
echo( "- Initialising DHS data: " );
$retries = $dhs->InitData();
if( $retries )
	echo( " Done (retries: $retries).\n" );
else
	echo( " Done.\n" );


?>
