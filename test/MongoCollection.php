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
require_once(dirname(__DIR__) . "/includes.local.php");
require_once(dirname(__DIR__) . "/mongo.local.php");

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
echo( '$test->Truncate();' . "\n" );
$test->Truncate();

echo( "\n====================================================================================\n\n" );

//
// Insert one record.
//
echo( "Insert one record:\n" );
echo( '$result = $test->Insert( ["data" => "Value 1", "color" => "red" ] );' . "\n" );
$result = $test->Insert( ["data" => "Value 1", "color" => "red" ] );
print_r( $result );

echo( "\n" );

//
// Insert many records.
//
echo( "Insert many records:\n" );
echo( '$result = $test->Insert( [ ["_id" => "ID1", "data" => 1, "color" => "green" ], [ "data" => "XXX", , "color" => "red" ], [ "_id" => "ID2", "data" => "XXX", "color" => "yellow" ] ], [ \'$doAll\' => TRUE ] );' . "\n" );
$result = $test->Insert( [ ["_id" => "ID1", "data" => 1, "color" => "green" ], [ "data" => "XXX", "color" => "red" ], [ "_id" => "ID2", "data" => "XXX", "color" => "yellow" ] ], [ '$doAll' => TRUE ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Count by example.
//
echo( "Count by example:\n" );
echo( '$result = $test->CountByExample( [ "color" => "red" ] );' . "\n" );
$result = $test->CountByExample( [ "color" => "red" ] );
var_dump( $result );

echo( "\n" );

//
// Find first record.
//
echo( "Find first record:\n" );
echo( '$result = $test->FindByExample( [ "color" => "red" ], [ \'$limit\' => 1 ] );' . "\n" );
$result = $test->FindByExample( [ "color" => "red" ], [ '$limit' => 1 ] );
print_r( $result );

echo( "\n" );

//
// Find all records.
//
echo( "Find all records:\n" );
echo( '$result = $test->FindByExample( [ "color" => "red" ] );' . "\n" );
$result = $test->FindByExample( [ "color" => "red" ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Update first record.
//
echo( "Update first record:\n" );
echo( '$result = $test->Update( [ \'$set\' => [ "color" => "blue", "status" => "changed" ] ], [ "color" => "green" ], [ \'$doAll\' => FALSE ] );' . "\n" );
$result = $test->Update( [ '$set' => [ "color" => "blue", "status" => "changed" ] ], [ "color" => "green" ], [ '$doAll' => FALSE ] );
var_dump( $result );
echo( '$result = $test->FindByExample( [ "status" => "changed" ] );' . "\n" );
$result = $test->FindByExample( [ "status" => "changed" ] );
print_r( $result );

echo( "\n" );

//
// Update all records.
//
echo( "Update all records:\n" );
echo( '$result = $test->Update( [ \'$set\' => [ "color" => "yellow", "status" => "was red" ] ], [ "color" => "red" ] );' . "\n" );
$result = $test->Update( [ '$set' => [ "color" => "yellow", "status" => "was red" ] ], [ "color" => "red" ] );
var_dump( $result );
echo( '$result = $test->FindByExample( [ "status" => "was red" ] );' . "\n" );
$result = $test->FindByExample( [ "status" => "was red" ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Replace a record.
//
echo( "Replace a record:\n" );
echo( '$result = $test->Replace( [ "color" => "pink", "status" => "replaced" ], [ "color" => "blue" ] );' . "\n" );
$result = $test->Replace( [ "color" => "pink", "status" => "replaced" ], [ "color" => "blue" ] );
var_dump( $result );
echo( '$result = $test->FindByExample( [ "status" => "replaced" ] );' . "\n" );
$result = $test->FindByExample( [ "status" => "replaced" ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Count by query.
//
echo( "Count by query:\n" );
echo( '$result = $test->CountByQuery( [ \'$or\' => [ [ \'data\' => \'XXX\' ], [ \'status\' => \'replaced\' ] ] ] );' . "\n" );
$result = $test->CountByQuery( [ '$or' => [ [ 'data' => 'XXX' ], [ 'status' => 'replaced' ] ] ] );
var_dump( $result );

echo( "\n" );

//
// Query records.
//
echo( "Query records:\n" );
echo( '$result = $test->FindByQuery( [ \'$or\' => [ [ \'data\' => \'XXX\' ], [ \'status\' => \'replaced\' ] ] ] );' . "\n" );
$result = $test->FindByQuery( [ '$or' => [ [ 'data' => 'XXX' ], [ 'status' => 'replaced' ] ] ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Aggregate records.
//
echo( "Aggregate records:\n" );
$project = [ "colour" => '$color' ];
$group = [ "_id" => '$colour', "count" => [ '$sum' => 1 ] ];
$sort = [ "count" => 1 ];
$pipeline = [ [ '$project' => $project ], [ '$group' => $group ], [ '$sort' => $sort ] ];
echo( '$pipeline = ' );
print_r( $pipeline );
$result = $test->MapReduce( $pipeline );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Delete first record.
//
echo( "Delete first record:\n" );
echo( '$result = $test->Delete( [ "color" => "pink" ], [ \'$doAll\' => FALSE ] );' . "\n" );
$result = $test->Delete( [ "color" => "pink" ], [ '$doAll' => FALSE ] );
var_dump( $result );
echo( '$result = $test->FindByExample( [ "color" => "pink" ] );' . "\n" );
$result = $test->FindByExample();
print_r( $result );

echo( "\n" );

//
// Delete all selected records.
//
echo( "Delete all selected records:\n" );
echo( '$result = $test->Delete( [ "data" => "XXX" ] );' . "\n" );
$result = $test->Delete( [ "data" => "XXX" ] );
var_dump( $result );
echo( '$result = $test->FindByExample();' . "\n" );
$result = $test->FindByExample();
print_r( $result );

echo( "\n" );

//
// Delete all records.
//
echo( "Delete all records:\n" );
echo( '$result = $test->Delete();' . "\n" );
$result = $test->Delete();
var_dump( $result );
echo( '$result = $test->FindByExample();' . "\n" );
$result = $test->FindByExample();
print_r( $result );


?>
