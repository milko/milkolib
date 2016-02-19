<?php

/**
 * MongoDB server object test suite.
 *
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		18/02/2016
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
use Milko\PHPLib\MongoDB\Collection;

//
// Instantiate object.
//
echo( '$url = "mongodb://localhost:27017/test_milkolib/test_collection";' . "\n" );
$url = "mongodb://localhost:27017/test_milkolib/test_collection";
echo( '$server = new \Milko\PHPLib\MongoDB\DataServer( $url' . " );\n" );
$server = new \Milko\PHPLib\MongoDB\DataServer( $url );
echo( '$result = (string)$server;' . "\n" );
echo( (string)$server . " ==> " );
echo( ( "$server" == $url ) ? "OK\n" : "FALIED\n" );

echo( "\n" );

//
// Retrieve database.
//
echo( "Retrieve database:\n" );
echo( '$db = $server->RetrieveDatabase( "test_milkolib" );' . "\n" );
$db = $server->RetrieveDatabase( "test_milkolib" );
echo( "$db ==> " );
echo( ( "$db" == "test_milkolib" ) ? "OK\n" : "FALIED\n" );

echo( "\n" );

//
// Retrieve collection.
//
echo( "Retrieve collection:\n" );
echo( '$test = $db->RetrieveCollection( "test_collection" );' . "\n" );
$test = $db->RetrieveCollection( "test_collection" );
echo( "$test ==> " );
echo( ( "$test" == "test_collection" ) ? "OK\n" : "FALIED\n" );

echo( "\n====================================================================================\n\n" );

//
// Clear collection.
//
echo( "Clear collection:\n" );
echo( '$test->Clear();' . "\n" );
$test->Clear();

echo( "\n====================================================================================\n\n" );

//
// Insert one record.
//
echo( "Insert one record:\n" );
echo( '$result = $test->InsertOne( ["data" => "Value 1", "color" => "red" ] );' . "\n" );
$result = $test->InsertOne( ["data" => "Value 1", "color" => "red" ] );
print_r( $result );

echo( "\n" );

//
// Insert many records.
//
echo( "Insert many records:\n" );
echo( '$result = $test->InsertMany( [ ["_id" => "ID1", "data" => 1, "color" => "green" ], [ "data" => "XXX", , "color" => "red" ] ] );' . "\n" );
$result = $test->InsertMany( [ ["_id" => "ID1", "data" => 1, "color" => "green" ], [ "data" => "XXX", "color" => "red" ] ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Find first record.
//
echo( "Find first record:\n" );
echo( '$result = $test->FindOne( [ "color" => "red" ] );' . "\n" );
$result = $test->FindOne( [ "color" => "red" ] );
print_r( $result );

echo( "\n" );

//
// Find all records.
//
echo( "Find all records:\n" );
echo( '$result = $test->FindMany( [ "color" => "red" ] );' . "\n" );
$result = $test->FindMany( [ "color" => "red" ] );
print_r( iterator_to_array($result) );

echo( "\n====================================================================================\n\n" );

//
// Update first record.
//
echo( "Update first record:\n" );
echo( '$result = $test->UpdateOne( [ \'$set\' => [ "color" => "blue", "status" => "changed" ] ], [ "color" => "green" ] );' . "\n" );
$result = $test->UpdateOne( [ '$set' => [ "color" => "blue", "status" => "changed" ] ], [ "color" => "green" ] );
var_dump( $result );
echo( '$result = $test->FindMany( [ "color" => "blue" ] );' . "\n" );
$result = $test->FindMany( [ "color" => "blue" ] );
print_r( iterator_to_array($result) );

echo( "\n" );

//
// Update all records.
//
echo( "Update all records:\n" );
echo( '$result = $test->UpdateMany( [ \'$set\' => [ "color" => "yellow", "status" => "was red" ] ], [ "color" => "red" ] );' . "\n" );
$result = $test->UpdateMany( [ '$set' => [ "color" => "yellow", "status" => "was red" ] ], [ "color" => "red" ] );
var_dump( $result );
echo( '$result = $test->FindMany( [ "color" => "yellow" ] );' . "\n" );
$result = $test->FindMany( [ "color" => "yellow" ] );
print_r( iterator_to_array($result) );

echo( "\n====================================================================================\n\n" );

//
// Replace a record.
//
echo( "Replace a record:\n" );
echo( '$result = $test->ReplaceOne( [ "color" => "pink", "status" => "replaced" ], [ "color" => "blue" ] );' . "\n" );
$result = $test->ReplaceOne( [ "color" => "pink", "status" => "replaced" ], [ "color" => "blue" ] );
var_dump( $result );
echo( '$result = $test->FindMany( [ "color" => "pink" ] );' . "\n" );
$result = $test->FindMany( [ "color" => "pink" ] );
print_r( iterator_to_array($result) );

echo( "\n====================================================================================\n\n" );

//
// Delete first record.
//
echo( "Delete first record:\n" );
echo( '$result = $test->DeleteOne( [ "color" => "pink" ] );' . "\n" );
$result = $test->DeleteOne( [ "color" => "pink" ] );
var_dump( $result );
echo( '$result = $test->FindMany( [ "color" => "pink" ] );' . "\n" );
$result = $test->FindMany( [ "color" => "pink" ] );
print_r( iterator_to_array($result) );

echo( "\n" );

//
// Delete all records.
//
echo( "Delete all records:\n" );
echo( '$result = $test->DeleteMany( [ "color" => "yellow" ] );' . "\n" );
$result = $test->DeleteMany( [ "color" => "yellow" ] );
var_dump( $result );
echo( '$result = $test->FindMany( [ "color" => "yellow" ] );' . "\n" );
$result = $test->FindMany( [ "color" => "yellow" ] );
print_r( iterator_to_array($result) );


?>
