<?php

/**
 * Miscellanea
 *
 * This script should be used after DHS data has been loaded.
 *
 *	@package	Batch
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		24/05/2016
 */

/*
 * Initialisation definition.
 */
define( 'doINIT', FALSE );
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
echo( "- Initialising DHS data dictionary: ...... " );
$dhs = new DHS( doINIT );
echo( "Done.\n" );

//
// Load distinct.
//
echo( "- Load distinct values: " );
$distinct = $dhs->GetDistinct();
echo( " Done.\n" );
print_r( $distinct );


?>
