<?php

/**
 * Document object test suite.
 *
 * This test suite will use a ficticious test class, to perform more in depth test use the
 * concrete classes derived from MongoDB Document.
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		10/03/2016
 */

//
// Include local definitions.
//
require_once( dirname( __DIR__ ) . "/includes.local.php" );

//
// Include utility functions.
//
require_once( "functions.php" );

//
// Reference class.
//
use Milko\PHPLib\MongoDB\Document;

//
// Instantiate empty document.
//
echo( "Instantiate empty document:\n" );
echo( '$test = new Milko\PHPLib\MongoDB\Document(' . ");\n" );
$test = new Document();
var_dump( $test );

echo( "\n" );

//
// Add identifier.
//
echo( "Add identifier:\n" );
echo( '$result = $test->ID( "ID" );' . "\n" );
$result = $test->ID( "ID" );
var_dump( $result );
print_r( $test );

echo( "\n" );

//
// Add key.
//
echo( "Add key:\n" );
echo( '$result = $test->Key( "KEY" );' . "\n" );
$result = $test->Key( "KEY" );
var_dump( $result );
print_r( $test );

echo( "\n" );

//
// Add value.
//
echo( "Add value:\n" );
echo( '$test[ "value" ] = "some value";' . "\n" );
$test[ "value" ] = "some value";
print_r( $test );

echo( "\n====================================================================================\n\n" );

//
// Get ID.
//
echo( "Get ID:\n" );
echo( '$result = $test->ID();' . "\n" );
$result = $test->ID();
var_dump( $result );

echo( "\n" );

//
// Get key.
//
echo( "Get key:\n" );
echo( '$result = $test->Key();' . "\n" );
$result = $test->Key();
var_dump( $result );

echo( "\n" );

//
// Serialise object.
//
echo( "Serialise object:\n" );
echo( '$result = $test->Record();' . "\n" );
$result = $test->Record();
var_dump( $result );

echo( "\n====================================================================================\n\n" );

//
// Add subdocument.
//
echo( "Add subdocument:\n" );
echo( '$test[ "subdocument" ] = new Milko\PHPLib\MongoDB\Document( ["field1" => "value 1", "field2" => 47] );' . "\n" );
$test[ "subdocument" ] = new Milko\PHPLib\MongoDB\Document( ["field1" => "value 1", "field2" => 47] );
var_dump( $test );

echo( "\n" );

//
// Serialise object.
//
echo( "Serialise object:\n" );
echo( '$result = $test->Record();' . "\n" );
$result = $test->Record();
var_dump( $result );

echo( "\n====================================================================================\n\n" );

//
// Connect.
//
echo( "Connect:\n" );
echo( '$url = "mongodb://localhost:27017/test_milkolib/test_collection";' . "\n" );
$url = "mongodb://localhost:27017/test_milkolib/test_collection";
echo( '$server = new \Milko\PHPLib\MongoDB\DataServer( $url' . " );\n" );
$server = new \Milko\PHPLib\MongoDB\DataServer( $url );
echo( '$db = $server->RetrieveDatabase( "test_milkolib" );' . "\n" );
$db = $server->RetrieveDatabase( "test_milkolib" );
echo( '$collection = $db->RetrieveCollection( "test_collection" );' . "\n" );
$collection = $db->RetrieveCollection( "test_collection" );
echo( '$collection->Truncate();' . "\n" );
$collection->Truncate();

echo( "\n" );

//
// Insert the record.
//
echo( "Insert the record:\n" );
echo( '$result = $collection->Insert( $test->Record() );' . "\n" );
$result = $collection->Insert( $test->Record() );
var_dump( $result );
echo( '$result = $collection->FindByExample( [ "_id" => $result ] );' . "\n" );
$result = $collection->FindByExample( [ "_id" => $result ] );
print_r( $result );



?>
