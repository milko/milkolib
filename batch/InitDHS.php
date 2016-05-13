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
echo( "- Initialising data dictionary: " );
$dhs = new DHS( doINIT );
echo( ".......... " );
echo( "Done.\n" );

//
// Initialise DHS descriptors.
//
echo( "- Initialising DHS descriptors: " );
$dhs->InitBaseDescriptors();
echo( ".......... " );
echo( "Done.\n" );


?>
