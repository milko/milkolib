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
// Include local definitions.
//
require_once(dirname(__DIR__) . "/includes.local.php");
require_once(dirname(__DIR__) . "/mongo.local.php");

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
// Document test classes.
//
class A extends Milko\PHPLib\Document{}
class B extends Milko\PHPLib\Document{}

//
// Instantiate object.
//
echo( '$url = "mongodb://localhost:27017/test_milkolib/test_collection";' . "\n" );
$url = "mongodb://localhost:27017/test_milkolib/test_collection";
echo( '$server = new \Milko\PHPLib\MongoDB\DataServer( $url' . " );\n" );
$server = new \Milko\PHPLib\MongoDB\DataServer( $url );
echo( '$database = $server->RetrieveDatabase( "test_milkolib" );' . "\n" );
$database = $server->RetrieveDatabase( "test_milkolib" );
echo( '$collection = $database->RetrieveCollection( "test_collection" );' . "\n" );
$collection = $database->RetrieveCollection( "test_collection" );

echo( "\n====================================================================================\n\n" );

//
// Instantiate container.
//
echo( "Instantiate container:\n" );
echo( '$container = new Milko\PHPLib\Container( ["name" => "Jim", "age" => 21] );' . "\n" );
$container = new Milko\PHPLib\Container( ["name" => "Jim", "age" => 21] );
print_r( $container );

echo( "\n" );

//
// Instantiate document.
//
echo( "Instantiate document:\n" );
echo( '$document = new Milko\PHPLib\Document( $collection, $container );' . "\n" );
$document = new Milko\PHPLib\Document( $collection, $container );
print_r( $document );

echo( "\n" );

//
// Instantiate A.
//
echo( "Instantiate A:\n" );
echo( '$A = new A( $collection, $container );' . "\n" );
$A = new A( $collection, $container );
print_r( $A );

echo( "\n" );

//
// Instantiate B.
//
echo( "Instantiate B:\n" );
echo( '$B = new B( $collection, $A );' . "\n" );
$B = new B( $collection, $A );
print_r( $B );


?>
