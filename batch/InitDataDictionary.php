<?php

/**
 * Initialise data dictionary
 *
 * This script can be used to initialise the data dictionary, it will:
 *
 * <em>Be aware that this script will erase any existing data dictionary, so use with
 * caution.</em>
 *
 *	@package	Batch
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		07/04/2016
 */

/**
 * Local environment definitions.
 */
require_once ("defines.inc.php" );

//
// Include global definitions.
//
require_once( dirname(__DIR__) . "/includes.local.php" );

//
// Include local definitions.
//
if( kENGINE == "MONGO" )
	require_once( dirname(__DIR__) . "/mongo.local.php" );
elseif( kENGINE == "ARANGO" )
	require_once( dirname(__DIR__) . "/arango.local.php" );

//
// Include definitions.
//
require_once( kPATH_LIBRARY_ROOT . "/src/PHPLib/tokens.inc.php" );
require_once( kPATH_LIBRARY_ROOT . "/src/PHPLib/types.inc.php" );
require_once( kPATH_LIBRARY_ROOT . "/src/PHPLib/kinds.inc.php" );
require_once( kPATH_LIBRARY_ROOT . "/src/PHPLib/predicates.inc.php" );
require_once( kPATH_LIBRARY_ROOT . "/src/PHPLib/descriptors.inc.php" );

//
// Include functions.
//
require_once ("loadTerms.php" );


/*=======================================================================================
 *																						*
 *										MAIN											*
 *																						*
 *======================================================================================*/

//
// Enable exception logging.
//
//triagens\ArangoDb\Exception::enableLogging();

//
// Set environment dependent variables.
//
switch( kENGINE )
{
	case "MONGO":
		$dsn = kDSN_MONGO;
		$terms_name = kTAG_MONGO_TERMS;
		break;

	case "ARANGO":
		$dsn = kDSN_ARANGO;
		$terms_name = kTAG_ARANGO_TERMS;
		break;

	default:
		exit( "The database engine can be \"ARANGO\" or \"MONGO\"\n" );				// ==>
}

//
// Inform.
//
echo( "********************************************************************************\n" );
echo( "* Database engine:    " . kENGINE . "\n" );
echo( "* Data source:        $dsn\n" );
echo( "* Database name:      " . kDB . "\n" );
echo( "********************************************************************************\n" );

//
// Instantiate data source.
//
switch( kENGINE )
{
	case "MONGO":
		$server = new \Milko\PHPLib\MongoDB\DataServer( $dsn );
		break;

	case "ARANGO":
		$server = new \Milko\PHPLib\ArangoDB\DataServer( $dsn );
		break;
}

//
// Instantiate database.
//
$database =
	$server->RetrieveDatabase(
		kDB,
		\Milko\PHPLib\Server::kFLAG_CREATE | \Milko\PHPLib\Server::kFLAG_CONNECT );

//
// Instantiate terms collection.
//
$collection = $database->RetrieveCollection( $terms_name, \Milko\PHPLib\Server::kFLAG_CREATE );
echo( "* Terms collection:   $collection\n" );
echo( "********************************************************************************\n" );

//
// Truncate terms collection.
//
$collection->Truncate();

//
// Load terms.
//
loadTerms( $collection );


?>
