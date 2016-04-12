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
// Include database definitions.
//
require_once( kPATH_LIBRARY_ROOT . "/mongo.local.php" );
require_once( kPATH_LIBRARY_ROOT . "/arango.local.php" );

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
// Set default arguments.
//
if( $argc == 1 )
{
	$engine = kENGINE;
	switch( kENGINE )
	{
		case "MONGO":
			$datasource_name = kDSN_MONGO;
			break;

		case "ARANGO":
			$datasource_name = kDSN_ARANGO;
			break;

		default:
			exit( "The database engine can be \"ARANGO\" or \"MONGO\"\n" );			// ==>
	}
	$database_name = kDB;
}

//
// Load default engine arguments.
//
elseif( $argc == 2 )
{
	$engine = $argv[ 1 ];
	switch( $engine )
	{
		case "MONGO":
			$datasource_name = kDSN_MONGO;
			break;

		case "ARANGO":
			$datasource_name = kDSN_ARANGO;
			break;

		default:
			exit( "The database engine can be \"ARANGO\" or \"MONGO\"\n" );			// ==>
	}
	$database_name = kDB;
}

//
// Load default engine and database arguments.
//
elseif( $argc == 3 )
{
	$engine = $argv[ 1 ];
	$database_name = $argv[ 2 ];
	switch( $engine )
	{
		case "MONGO":
			$datasource_name = kDSN_MONGO;
			break;

		case "ARANGO":
			$datasource_name = kDSN_ARANGO;
			break;

		default:
			exit( "The database engine can be \"ARANGO\" or \"MONGO\"\n" );			// ==>
	}
}

//
// Get script arguments.
//
elseif( $argc == 4 )
{
	$engine = $argv[ 1 ];
	switch( kENGINE )
	{
		case "MONGO":
		case "ARANGO":
			break;

		default:
			exit( "The database engine can be \"ARANGO\" or \"MONGO\"\n" );			// ==>
	}
	$database_name = $argv[ 2 ];
	$datasource_name = $argv[ 3 ];
}

//
// Check script arguments.
//
else
	exit( "Usage: php -f InitDataDictionary.php "
		 ."<engine> <database_name> <data_source_name>\n" );						// ==>

//
// Inform.
//
echo( "********************************************************************************\n" );
echo( "* Database engine:    $engine\n" );
echo( "* Data source:        $datasource_name\n" );
echo( "* Database name:      $database_name\n" );
echo( "********************************************************************************\n" );

//
// Instantiate data source.
//
switch( $engine )
{
	case "MONGO":
		$server = new \Milko\PHPLib\MongoDB\DataServer( $datasource_name );
		break;

	case "ARANGO":
		$server = new \Milko\PHPLib\ArangoDB\DataServer( $datasource_name );
		break;
}

//
// Instantiate database.
//
$database =
	$server->GetDatabase(
		$database_name,
		\Milko\PHPLib\Server::kFLAG_CREATE | \Milko\PHPLib\Server::kFLAG_CONNECT );

//
// Instantiate terms collection.
//
$collection = $database->RetrieveTerms( \Milko\PHPLib\Server::kFLAG_CREATE );
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
