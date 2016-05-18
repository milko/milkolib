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
define( 'kENGINE', 'ARANGO' );

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
// Initialise DHS object.
//
echo( "- Initialising data dictionary: ......... " );
$dhs = new DHS( doINIT );
echo( "Done.\n" );

//
// Initialise DHS namespaces.
//
echo( "- Initialising DHS namespaces: .......... " );
$dhs->InitTypes();
echo( "Done.\n" );

//
// Initialise DHS indicator descriptors.
//
echo( "- Initialising DHS indicator descriptors: " );
$dhs->InitIndicators();
echo( "Done.\n" );

//
// Initialise DHS data descriptors.
//
echo( "- Initialising DHS data descriptors: .... " );
$dhs->InitDataIndicators();
echo( "Done.\n" );


?>
