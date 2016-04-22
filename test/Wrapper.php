<?php

/**
 * Wrapper object test suite.
 *
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		21/04/2016
 */

//
// Global definitions.
//
define( 'kENGINE', "ARANGO" );

//
// Include global definitions.
//
require_once(dirname(__DIR__) . "/includes.local.php");
if( kENGINE == "MONGO" )
	require_once(dirname(__DIR__) . "/mongo.local.php");
elseif( kENGINE == "ARANGO" )
	require_once(dirname(__DIR__) . "/arango.local.php");

//
// Include local definitions.
//
require_once(dirname(__DIR__) . "/defines.inc.php");

//
// Include utility functions.
//
require_once( "functions.php" );

//
// Enable exception logging.
//
//triagens\ArangoDb\Exception::enableLogging();

//
// Instantiate server.
//
echo( "Instantiate server:\n" );
if( kENGINE == "MONGO" )
{
	echo( '$url = "mongodb://localhost:2701";' . "\n" );
	$url = "mongodb://localhost:27017";
	echo( '$server = new \Milko\PHPLib\MongoDB\Server( $url' . " );\n" );
	$server = new \Milko\PHPLib\MongoDB\Server( $url );
}
elseif( kENGINE == "ARANGO" )
{
	echo('$url = "tcp://localhost:8529";' . "\n");
	$url = "tcp://localhost:8529";
	echo( '$server = new \Milko\PHPLib\ArangoDB\Server( $url' . " );\n" );
	$server = new \Milko\PHPLib\ArangoDB\Server( $url );
}

echo( "\n" );

//
// Drop database.
//
echo( "Drop database:\n" );
echo( '$tmp = $server->NewDatabase( "test_milkolib" );' . "\n" );
$tmp = $server->NewDatabase( "test_milkolib" );
echo( '$tmp->Drop();' . "\n" );
$tmp->Drop();

echo( "\n" );

//
// Instantiate wrapper.
//
echo( "Instantiate wrapper:\n" );
echo( '$wrapper = $server->NewWrapper( "test_milkolib" );' . "\n" );
$wrapper = $server->NewWrapper( "test_milkolib" );
echo( "Class: " . get_class( $wrapper ) . "\n" );
exit;

echo( "\n====================================================================================\n\n" );

//
// Get descriptors serial.
//
echo( "Get descriptors serial:\n" );
echo( '$serial = $wrapper->NewDescriptorKey();' . "\n" );
$serial = $wrapper->NewDescriptorKey();
var_dump( $serial );

echo( "\n" );

//
// Get descriptors serial.
//
echo( "Get descriptors serial:\n" );
echo( '$serial = $wrapper->NewDescriptorKey();' . "\n" );
$serial = $wrapper->NewDescriptorKey();
var_dump( $serial );


?>
