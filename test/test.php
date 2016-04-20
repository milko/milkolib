<?php

/**
 * Document object test suite.
 *
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		17/03/2016
 */

//
// Global definitions.
//
define( 'kENGINE', "ARANGO" );

//
// Include local definitions.
//
require_once(dirname(__DIR__) . "/includes.local.php");
if( kENGINE == "MONGO" )
	require_once(dirname(__DIR__) . "/mongo.local.php");
elseif( kENGINE == "ARANGO" )
	require_once(dirname(__DIR__) . "/arango.local.php");

//
// Include utility functions.
//
require_once( "functions.php" );

//
// Reference class.
//
use Milko\PHPLib\MongoDB\Document;
use Milko\PHPLib\MongoDB\Collection;

//
// Instantiate object.
//
if( kENGINE == "MONGO" )
{
	echo( '$url = "mongodb://localhost:27017/test_milkolib/test_collection";' . "\n" );
	$url = "mongodb://localhost:27017/test_milkolib/test_collection";
	echo( '$server = new \Milko\PHPLib\MongoDB\Server( $url' . " );\n" );
	$server = new \Milko\PHPLib\MongoDB\Server( $url );
}
elseif( kENGINE == "ARANGO" )
{
	echo('$url = "tcp://localhost:8529/test_milkolib/test_collection";' . "\n");
	$url = "tcp://localhost:8529/test_milkolib/test_collection";
	echo( '$server = new \Milko\PHPLib\ArangoDB\Server( $url' . " );\n" );
	$server = new \Milko\PHPLib\ArangoDB\Server( $url );
}
echo( '$database = $server->RetrieveDatabase( "test_milkolib" );' . "\n" );
$database = $server->GetDatabase( "test_milkolib" );
echo( '$collection = $database->RetrieveCollection( "test_collection" );' . "\n" );
$collection = $database->GetCollection( "test_collection" );
echo( '$collection->Truncate();' . "\n" );
$collection->Truncate();

echo( "\n====================================================================================\n\n" );

//
// Set timestamp.
//
echo( "Set timestamp:\n" );
echo( '$result = $collection->NewTimestamp();' . "\n" );
$result = $collection->NewTimestamp();
var_dump( $result );

echo( "\n" );

//
// Get timestamp.
//
echo( "Get timestamp:\n" );
echo( '$result = $collection->GetTimestamp( $result );' . "\n" );
$result = $collection->GetTimestamp( $result );
var_dump( $result );

echo( "\n====================================================================================\n\n" );

//
// Init wrapper.
//
echo( "Init wrapper:\n" );
echo( '$database->InitWrapper( TRUE );' . "\n" );
$database->InitWrapper( TRUE );


?>
