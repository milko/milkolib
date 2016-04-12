<?php

/**
 * Data server object test suite.
 *
 * This test suite will use a ficticious test class, to perform more in depth test use the
 * concrete classes derived from DataServer.
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
	echo( '$test = new \Milko\PHPLib\MongoDB\Server();' . "\n" );
	$test = new \Milko\PHPLib\MongoDB\Server();
	echo( '$result = (string)$test;' . "\n" );
	echo( (string)$test . ' ==> ' );
	echo( ( "$test" == kMONGO_OPTS_CLIENT_DEFAULT ) ? "OK\n" : "FALIED\n" );
}
elseif( kENGINE == "ARANGO" )
{
	echo( '$test = new \Milko\PHPLib\ArangoDB\Server();' . "\n" );
	$test = new \Milko\PHPLib\ArangoDB\Server();
	echo( '$result = (string)$test;' . "\n" );
	echo( (string)$test . ' ==> ' );
	echo( ( "$test" == kARANGO_OPTS_CLIENT_DEFAULT ) ? "OK\n" : "FALIED\n" );
}
exit;

echo( "\n" );

//
// Instantiate object.
//
if( kENGINE == "MONGO" )
{
	echo( '$url = "mongodb://localhost:27017/test_milkolib/test_collection";' . "\n" );
	$url = "mongodb://localhost:27017/test_milkolib/test_collection";
	echo( '$test = new \Milko\PHPLib\MongoDB\DataServer( $url' . " );\n" );
	$test = new \Milko\PHPLib\MongoDB\DataServer( $url );
	echo( '$result = (string)$test;' . "\n" );
	echo( (string)$test . " ==> " );
	echo( ( "$test" == $url ) ? "OK\n" : "FALIED\n" );
}
elseif( kENGINE == "ARANGO" )
{
	echo( '$url = "tcp://localhost:8529/test_milkolib/test_collection";' . "\n" );
	$url = "tcp://localhost:8529/test_milkolib/test_collection";
	echo( '$test = new \Milko\PHPLib\ArangoDB\DataServer( $url' . " );\n" );
	$test = new \Milko\PHPLib\ArangoDB\DataServer( $url );
	echo( '$result = (string)$test;' . "\n" );
	echo( (string)$test . " ==> " );
	echo( ( "$test" == $url ) ? "OK\n" : "FALIED\n" );
}

echo( "\n====================================================================================\n\n" );

//
// List server databases.
//
echo( "List databases:\n" );
echo( '$list = $test->ListDatabases();' . "\n" );
$list = $test->ListDatabases();
print_r( $list );

echo( "\n" );

//
// List working databases.
//
echo( "List databases:\n" );
echo( '$list = $test->WorkingDatabases();' . "\n" );
$list = $test->WorkingDatabases();
print_r( $list );

echo( "\n====================================================================================\n\n" );

//
// Retrieve database.
//
echo( "Retrieve database:\n" );
echo( '$db = $test->RetrieveDatabase( "test_milkolib", \Milko\PHPLib\Server::kFLAG_DEFAULT );' . "\n" );
$db = $test->RetrieveDatabase( "test_milkolib", \Milko\PHPLib\Server::kFLAG_DEFAULT );
echo( get_class( $db ) . "\n" );
echo( "$db ==> " );
echo( ( "$db" == "test_milkolib" ) ? "OK\n" : "FALIED\n" );

echo( "\n" );

//
// Retrieve non existing database.
//
echo( "Retrieve non existing database:\n" );
echo( '$db = $test->RetrieveDatabase( "UNKNOWN", \Milko\PHPLib\Server::kFLAG_DEFAULT );' . "\n" );
$db = $test->RetrieveDatabase( "UNKNOWN", \Milko\PHPLib\Server::kFLAG_DEFAULT );
var_dump( $db );
echo( "$db ==> " );
echo( ( $db === NULL ) ? "OK\n" : "FALIED\n" );

echo( "\n====================================================================================\n\n" );

//
// Create database.
//
echo( "Create database:\n" );
echo( '$db = $test->RetrieveDatabase( "NewDB" );' . "\n" );
$db = $test->RetrieveDatabase( "NewDB" );
echo( '$list = $test->ListDatabases();' . "\n" );
$list = $test->ListDatabases();
print_r( $list );
echo( '$list = $test->WorkingDatabases();' . "\n" );
$list = $test->WorkingDatabases();
print_r( $list );

echo( "\n" );

//
// Forget database.
//
echo( "Forget database:\n" );
echo( '$db = $test->ForgetDatabase( "NewDB" );' . "\n" );
$db = $test->ForgetDatabase( "NewDB" );
echo( '$list = $test->ListDatabases();' . "\n" );
$list = $test->ListDatabases();
print_r( $list );
echo( '$list = $test->WorkingDatabases();' . "\n" );
$list = $test->WorkingDatabases();
print_r( $list );

echo( "\n" );

//
// Drop database.
//
echo( "Drop database:\n" );
echo( '$db = $test->DropDatabase( "test_milkolib" );' . "\n" );
$db = $test->DropDatabase( "test_milkolib" );
echo( '$list = $test->ListDatabases();' . "\n" );
$list = $test->ListDatabases();
print_r( $list );
echo( '$list = $test->WorkingDatabases();' . "\n" );
$list = $test->WorkingDatabases();
print_r( $list );


?>
