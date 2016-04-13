<?php

/**
 * Server object test suite.
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
echo( "Class: " . get_class( $test ) . "\n" );
echo( "Protocol: " );
var_dump( $test->Protocol() );
echo( "Host: " );
var_dump( $test->Host() );
echo( "Port: " );
var_dump( $test->Port() );
echo( "Path: " );
var_dump( $test->Path() );

echo( "\n" );

//
// List server databases.
//
echo( "List server databases:\n" );
echo( '$list = $test->ListDatabases();' . "\n" );
$list = $test->ListDatabases();
print_r( $list );

echo( "\n" );

//
// List working databases.
//
echo( "List working databases:\n" );
echo( '$list = array_keys( $test->ListWorkingDatabases() );' . "\n" );
$list = array_keys( $test->ListWorkingDatabases() );
print_r( $list );

echo( "\n====================================================================================\n\n" );

//
// Instantiate object.
//
if( kENGINE == "MONGO" )
{
	echo( '$url = "mongodb://localhost:27017/test_milkolib";' . "\n" );
	$url = "mongodb://localhost:27017/test_milkolib";
	echo( '$test = new \Milko\PHPLib\MongoDB\Server( $url' . " );\n" );
	$test = new \Milko\PHPLib\MongoDB\Server( $url );
	echo( '$result = (string)$test;' . "\n" );
	echo( (string)$test . " ==> " );
	echo( ( "$test" == $url ) ? "OK\n" : "FALIED\n" );
}
elseif( kENGINE == "ARANGO" )
{
	echo( '$url = "tcp://localhost:8529/test_milkolib";' . "\n" );
	$url = "tcp://localhost:8529/test_milkolib";
	echo( '$test = new \Milko\PHPLib\ArangoDB\Server( $url' . " );\n" );
	$test = new \Milko\PHPLib\ArangoDB\Server( $url );
	echo( '$result = (string)$test;' . "\n" );
	echo( (string)$test . " ==> " );
	echo( ( "$test" == $url ) ? "OK\n" : "FALIED\n" );
}
echo( "Class: " . get_class( $test ) . "\n" );
echo( "Protocol: " );
var_dump( $test->Protocol() );
echo( "Host: " );
var_dump( $test->Host() );
echo( "Port: " );
var_dump( $test->Port() );
echo( "Path: " );
var_dump( $test->Path() );

echo( "\n" );

//
// List server databases.
//
echo( "List server databases:\n" );
echo( '$list = $test->ListDatabases();' . "\n" );
$list = $test->ListDatabases();
print_r( $list );

echo( "\n" );

//
// List working databases.
//
echo( "List working databases:\n" );
echo( '$list = array_keys( $test->ListWorkingDatabases() );' . "\n" );
$list = array_keys( $test->ListWorkingDatabases() );
print_r( $list );

echo( "\n====================================================================================\n\n" );

//
// Retrieve database.
//
echo( "Retrieve database:\n" );
echo( '$db = $test->GetDatabase( "test_milkolib" );' . "\n" );
$db = $test->GetDatabase( "test_milkolib" );
echo( get_class( $db ) . "\n" );
echo( "$db ==> " );
echo( ( "$db" == "test_milkolib" ) ? "OK\n" : "FALIED\n" );

echo( "\n" );

//
// Retrieve non existing database.
//
echo( "Retrieve non existing database:\n" );
echo( '$db = $test->GetDatabase( "UNKNOWN" );' . "\n" );
$db = $test->GetDatabase( "UNKNOWN" );
var_dump( $db );
echo( "$db ==> " );
echo( ( $db === NULL ) ? "OK\n" : "FALIED\n" );

echo( "\n====================================================================================\n\n" );

//
// Create database.
//
echo( "Create database:\n" );
echo( '$db = $test->NewDatabase( "NewDB" );' . "\n" );
$db = $test->NewDatabase( "NewDB" );
echo( '$list = $test->ListDatabases();' . "\n" );
$list = $test->ListDatabases();
print_r( $list );
echo( '$list = array_keys( $test->ListWorkingDatabases() );' . "\n" );
$list = array_keys( $test->ListWorkingDatabases() );
print_r( $list );

echo( "\n" );

//
// Forget working database.
//
echo( "Forget working database:\n" );
echo( '$test->ForgetWorkingDatabase( "NewDB" );' . "\n" );
$test->ForgetWorkingDatabase( "NewDB" );
echo( '$list = $test->ListDatabases();' . "\n" );
$list = $test->ListDatabases();
print_r( $list );
echo( '$list = array_keys( $test->ListWorkingDatabases() );' . "\n" );
$list = array_keys( $test->ListWorkingDatabases() );
print_r( $list );

echo( "\n" );

//
// Drop database.
//
echo( "Drop database:\n" );
echo( '$db = $test->DelDatabase( "test_milkolib" );' . "\n" );
$db = $test->DelDatabase( "test_milkolib" );
echo( '$list = $test->ListDatabases();' . "\n" );
$list = $test->ListDatabases();
print_r( $list );
echo( '$list = array_keys( $test->ListWorkingDatabases() );' . "\n" );
$list = array_keys( $test->ListWorkingDatabases() );
print_r( $list );


?>
