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
define( 'kENGINE', "MONGO" );

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
// Enable exception logging.
//
triagens\ArangoDb\Exception::enableLogging();

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
// Add records.
//
echo( "Add records:\n" );
$records = [
	[ "color" => "red", "number" => 7 ],
	[ "color" => "green", "number" => 6 ],
	[ "color" => "yellow", "number" => 3 ],
	[ "color" => "yellow", "number" => 5 ],
	[ "color" => "blue", "number" => 8 ],
	[ "color" => "black", "number" => 10 ] ];
$result = $collection->InsertMany( $records );
print_r( $records );

echo( "\n====================================================================================\n\n" );

//
// Get distinct.
//
echo( "Get distinct:\n" );
echo( '$result = $collection->Distinct( "color", FALSE );' . "\n" );
$result = $collection->Distinct( "color", FALSE );
print_r( $result );

echo( "\n" );

//
// Get distinct with count.
//
echo( "Get distinct with count:\n" );
echo( '$result = $collection->Distinct( "color", TRUE );' . "\n" );
$result = $collection->Distinct( "color", TRUE );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Get distinct by query.
//
echo( "Get distinct by query:\n" );
echo( '$result = $collection->DistinctByQuery( "color", [ "color" => "yellow" ], FALSE );' . "\n" );
$result = $collection->DistinctByQuery( "color", [ "color" => "yellow" ], FALSE );
print_r( $result );

echo( "\n" );

//
// Get distinct by query with count.
//
echo( "Get distinct by query with count:\n" );
echo( '$result = $collection->DistinctByQuery( "color", [ "color" => "yellow" ], TRUE );' . "\n" );
$result = $collection->DistinctByQuery( "color", [ "color" => "yellow" ], TRUE );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Get distinct by example.
//
echo( "Get distinct by example:\n" );
echo( '$result = $collection->DistinctByExample( "color", [ "color" => "yellow" ], FALSE );' . "\n" );
$result = $collection->DistinctByExample( "color", [ "color" => "yellow" ], FALSE );
print_r( $result );

echo( "\n" );

//
// Get distinct by example with count.
//
echo( "Get distinct by example with count:\n" );
echo( '$result = $collection->DistinctByExample( "color", [ "color" => "yellow" ], TRUE );' . "\n" );
$result = $collection->DistinctByExample( "color", [ "color" => "yellow" ], TRUE );
print_r( $result );


?>
