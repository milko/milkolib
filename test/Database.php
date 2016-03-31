<?php

/**
 * Database object test suite.
 *
 * This test suite will use a ficticious test class, to perform more in depth test use the
 * concrete classes derived from Database.
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		18/02/2016
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
//ArangoException::enableLogging();

//
// Instantiate object.
//
if( kENGINE == "MONGO" )
{
	echo( '$url = "mongodb://localhost:27017/test_milkolib/test_collection";' . "\n" );
	$url = "mongodb://localhost:27017/test_milkolib/test_collection";
	echo( '$server = new \Milko\PHPLib\MongoDB\DataServer( $url' . " );\n" );
	$server = new \Milko\PHPLib\MongoDB\DataServer( $url );
}
elseif( kENGINE == "ARANGO" )
{
	echo( '$url = "tcp://localhost:8529/test_milkolib/test_collection";' . "\n" );
	$url = "tcp://localhost:8529/test_milkolib/test_collection";
	echo( '$server = new \Milko\PHPLib\ArangoDB\DataServer( $url' . " );\n" );
	$server = new \Milko\PHPLib\ArangoDB\DataServer( $url );
}
echo( '$result = (string)$server;' . "\n" );
echo( (string)$server . " ==> " );
echo( ( "$server" == $url ) ? "OK\n" : "FALIED\n" );

echo( "\n" );

//
// Retrieve database.
//
echo( "Retrieve database:\n" );
echo( '$test = $server->RetrieveDatabase( "test_milkolib" );' . "\n" );
$test = $server->RetrieveDatabase( "test_milkolib" );
echo( "$test ==> " );
echo( ( "$test" == "test_milkolib" ) ? "OK\n" : "FALIED\n" );

echo( "\n====================================================================================\n\n" );

//
// List database collections.
//
echo( "List database collections:\n" );
echo( '$list = $test->ListCollections();' . "\n" );
$list = $test->ListCollections();
print_r( $list );

echo( "\n" );

//
// List working collections.
//
echo( "List working collections:\n" );
echo( '$list = $test->WorkingCollections();' . "\n" );
$list = $test->WorkingCollections();
print_r( $list );

echo( "\n====================================================================================\n\n" );

//
// Retrieve collection.
//
echo( "Retrieve collection:\n" );
echo( '$result = $test->RetrieveCollection( "test_collection" );' . "\n" );
$result = $test->RetrieveCollection( "test_collection" );
echo( "$result ==> " );
echo( ( "$result" == "test_collection" ) ? "OK\n" : "FALIED\n" );

echo( "\n" );

//
// Retrieve non existing collection.
//
echo( "Retrieve non existing collection:\n" );
echo( '$result = $test->RetrieveCollection( "UNKNOWN" );' . "\n" );
$result = $test->RetrieveCollection( "UNKNOWN" );
var_dump( $result );
echo( "$result ==> " );
echo( ( $result === NULL ) ? "OK\n" : "FALIED\n" );

echo( "\n====================================================================================\n\n" );

//
// Create collection.
//
echo( "Create collection:\n" );
echo( '$result = $test->RetrieveCollection( "NewCollection", \Milko\PHPLib\Server::kFLAG_CREATE );' . "\n" );
$result = $test->RetrieveCollection( "NewCollection", \Milko\PHPLib\Server::kFLAG_CREATE );
echo( '$list = $test->ListCollections();' . "\n" );
$list = $test->ListCollections();
print_r( $list );
echo( '$list = $test->WorkingCollections();' . "\n" );
$list = $test->WorkingCollections();
print_r( $list );

echo( "\n" );

//
// Forget collection.
//
echo( "Forget collection:\n" );
echo( '$result = $test->ForgetCollection( "NewCollection" );' . "\n" );
$result = $test->ForgetCollection( "NewCollection" );
echo( '$list = $test->ListCollections();' . "\n" );
$list = $test->ListCollections();
print_r( $list );
echo( '$list = $test->WorkingCollections();' . "\n" );
$list = $test->WorkingCollections();
print_r( $list );

echo( "\n" );

//
// Drop collection.
//
echo( "Drop collection:\n" );
echo( '$result = $test->DropCollection( "test_collection" );' . "\n" );
$result = $test->DropCollection( "test_collection" );
echo( '$list = $test->ListCollections();' . "\n" );
$list = $test->ListCollections();
print_r( $list );
echo( '$list = $test->WorkingCollections();' . "\n" );
$list = $test->WorkingCollections();
print_r( $list );


?>

