<?php

/**
 * ArangoDB server object test suite.
 *
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		08/03/2016
 */

//
// Include local definitions.
//
require_once(dirname(__DIR__) . "/includes.local.php");
require_once(dirname(__DIR__) . "/arango.local.php");

//
// Include utility functions.
//
require_once( "functions.php" );

//
// Reference class.
//
use Milko\PHPLib\MongoDB\Collection;
use triagens\ArangoDb\Exception as ArangoException;

//
// Enable exception logging.
//
ArangoException::enableLogging();

//
// Instantiate object.
//
echo( '$url = "tcp://localhost:8529/test_milkolib/test_collection";' . "\n" );
$url = "tcp://localhost:8529/test_milkolib/test_collection";
echo( '$server = new \Milko\PHPLib\ArangoDB\DataServer( $url' . " );\n" );
$server = new \Milko\PHPLib\ArangoDB\DataServer( $url );
echo( '$result = (string)$test;' . "\n" );
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

echo( "\n" );

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
$result = $id = $test->Insert( ["data" => "Value 1", "color" => "red" ] );
var_dump( $result );

echo( "\n" );

//
// Insert many records.
//
echo( "Insert many records:\n" );
echo( '$result = $test->Insert( [ ["_key" => "ID1", "data" => 1, "color" => "green" ], [ "data" => "XXX", , "color" => "red" ], [ "_key" => "ID2", "data" => "XXX", "color" => "yellow" ] ], [ \'$doAll\' => TRUE ] );' . "\n" );
$result = $test->Insert( [ ["_key" => "ID1", "data" => 1, "color" => "green" ], [ "data" => "XXX", "color" => "red" ], [ "_key" => "ID2", "data" => "XXX", "color" => "yellow" ] ], [ '$doAll' => TRUE ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Get record count.
//
echo( "Get record count:\n" );
echo( '$result = $test->RecordCount();' . "\n" );
$result = $test->RecordCount();
var_dump( $result );

echo( "\n" );

//
// Count by example.
//
echo( "Count by example:\n" );
echo( '$result = $test->CountByExample( [ "color" => "red" ] );' . "\n" );
$result = $test->CountByExample( [ "color" => "red" ] );
var_dump( $result );

echo( "\n" );

//
// Count by query.
//
echo( "Count by query:\n" );
echo( '$result = $test->CountByQuery( ["query" => "FOR r IN test_collection FILTER r.color == \'red\' RETURN r"] );' . "\n" );
$result = $test->CountByQuery( ["query" => "FOR r IN test_collection FILTER r.color == 'red' RETURN r"] );
var_dump( $result );

echo( "\n====================================================================================\n\n" );

//
// Find by ID native.
//
echo( "Find by ID native:\n" );
echo( '$result = $test->FindByKey( "ID1", [kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE] );' . "\n" );
$result = $test->FindByKey( "ID1", [kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE] );
print_r( $result );
echo( "\n" );

echo( "\n" );

//
// Find by ID standard.
//
echo( "Find by ID standard:\n" );
echo( '$result = $test->FindByKey( "ID1", [kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD] );' . "\n" );
$result = $test->FindByKey( "ID1", [kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD] );
print_r( $result );
echo( "\n" );

echo( "\n" );

//
// Find by ID handle.
//
echo( "Find by ID handle:\n" );
echo( '$result = $test->FindByKey( "ID1", [kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE] );' . "\n" );
$result = $test->FindByKey( "ID1", [kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE] );
print_r( $result );
echo( "\n" );

echo( "\n====================================================================================\n\n" );

//
// Find many by ID native.
//
echo( "Find many by ID native:\n" );
echo( '$result = $test->FindByKey( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE] );' . "\n" );
$result = $test->FindByKey( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE] );
print_r( $result );
echo( "\n" );

echo( "\n" );

//
// Find many by ID standard.
//
echo( "Find many by ID standard:\n" );
echo( '$result = $test->FindByKey( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD] );' . "\n" );
$result = $test->FindByKey( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD] );
print_r( $result );
echo( "\n" );

echo( "\n" );

//
// Find many by ID handle.
//
echo( "Find by ID handle:\n" );
echo( '$result = $test->FindByKey( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE] );' . "\n" );
$result = $test->FindByKey( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE] );
print_r( $result );
echo( "\n" );

echo( "\n====================================================================================\n\n" );

//
// Find first record native by example.
//
echo( "Find first record native by example:\n" );
echo( '$result = $test->FindByExample( [ "color" => "red" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );' . "\n" );
$result = $test->FindByExample( [ "color" => "red" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );
print_r( $result );

echo( "\n" );

//
// Find first record standard by example.
//
echo( "Find first record standard by example:\n" );
echo( '$result = $test->FindByExample( [ "color" => "red" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );' . "\n" );
$result = $test->FindByExample( [ "color" => "red" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );
print_r( $result );

echo( "\n" );

//
// Find first record handle by example.
//
echo( "Find first record handle by example:\n" );
echo( '$result = $test->FindByExample( [ "color" => "red" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );' . "\n" );
$result = $test->FindByExample( [ "color" => "red" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Find all records native by example.
//
echo( "Find all records native by example:\n" );
echo( '$result = $test->FindByExample( [ "color" => "red" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );' . "\n" );
$result = $test->FindByExample( [ "color" => "red" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );
print_r( $result );

echo( "\n" );

//
// Find all records standard by example.
//
echo( "Find all records standard by example:\n" );
echo( '$result = $test->FindByExample( [ "color" => "red" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );' . "\n" );
$result = $test->FindByExample( [ "color" => "red" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );
print_r( $result );

echo( "\n" );

//
// Find all records handle by example.
//
echo( "Find all records handle by example:\n" );
echo( '$result = $test->FindByExample( [ "color" => "red" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );' . "\n" );
$result = $test->FindByExample( [ "color" => "red" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Find first record native by query.
//
echo( "Find first record native by query:\n" );
echo( '$result = $test->FindByQuery( ["query" => "FOR r IN test_collection FILTER r.color == \'red\' LIMIT 1 RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );' . "\n" );
$result = $test->FindByQuery( ["query" => "FOR r IN test_collection FILTER r.color == 'red' LIMIT 1 RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );
print_r( $result );

echo( "\n" );

//
// Find first record standard by query.
//
echo( "Find first record standard by query:\n" );
echo( '$result = $test->FindByQuery( ["query" => "FOR r IN test_collection FILTER r.color == \'red\' LIMIT 1 RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );' . "\n" );
$result = $test->FindByQuery( ["query" => "FOR r IN test_collection FILTER r.color == 'red' LIMIT 1 RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );
print_r( $result );

echo( "\n" );

//
// Find first record handle by query.
//
echo( "Find first record handle by query:\n" );
echo( '$result = $test->FindByQuery( ["query" => "FOR r IN test_collection FILTER r.color == \'red\' LIMIT 1 RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );' . "\n" );
$result = $test->FindByQuery( ["query" => "FOR r IN test_collection FILTER r.color == 'red' LIMIT 1 RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Find all records native by query.
//
echo( "Find all records native by query:\n" );
echo( '$result = $test->FindByQuery( ["query" => "FOR r IN test_collection FILTER r.color == \'red\' RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );' . "\n" );
$result = $test->FindByQuery( ["query" => "FOR r IN test_collection FILTER r.color == 'red' RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );
print_r( $result );

echo( "\n" );

//
// Find all records standard by query.
//
echo( "Find all records standard by query:\n" );
echo( '$result = $test->FindByQuery( ["query" => "FOR r IN test_collection FILTER r.color == \'red\' RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );' . "\n" );
$result = $test->FindByQuery( ["query" => "FOR r IN test_collection FILTER r.color == 'red' RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );
print_r( $result );

echo( "\n" );

//
// Find all records handle by query.
//
echo( "Find all records handle by query:\n" );
echo( '$result = $test->FindByQuery( ["query" => "FOR r IN test_collection FILTER r.color == \'red\' RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );' . "\n" );
$result = $test->FindByQuery( ["query" => "FOR r IN test_collection FILTER r.color == 'red' RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Update first record.
//
echo( "Update first record:\n" );
echo( '$result = $test->Update( [ "color" => "blue", "status" => "changed" ], ["query" => "FOR r IN test_collection FILTER r.color == \'green\' RETURN r"], [ kTOKEN_OPT_MANY => FALSE ] );' . "\n" );
$result = $test->Update( [ "color" => "blue", "status" => "changed" ], ["query" => "FOR r IN test_collection FILTER r.color == 'green' RETURN r"], [ kTOKEN_OPT_MANY => FALSE ] );
var_dump( $result );
echo( '$result = $test->FindByExample( [ "status" => "changed" ] );' . "\n" );
$result = $test->FindByExample( [ "status" => "changed" ] );
print_r( $result );

echo( "\n" );

//
// Update all records.
//
echo( "Update all records:\n" );
echo( '$result = $test->Update( [ "color" => "yellow", "status" => "was red" ], ["query" => "FOR r IN test_collection FILTER r.color == \'red\' RETURN r"] );' . "\n" );
$result = $test->Update( [ "color" => "yellow", "status" => "was red" ], ["query" => "FOR r IN test_collection FILTER r.color == 'red' RETURN r"] );
var_dump( $result );
echo( '$result = $test->FindByExample( [ "status" => "was red" ] );' . "\n" );
$result = $test->FindByExample( [ "status" => "was red" ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Replace a record.
//
echo( "Replace a record:\n" );
echo( '$result = $test->Replace( [ "color" => "pink", "status" => "replaced" ], ["query" => "FOR r IN test_collection FILTER r.color == \'blue\' RETURN r"] );' . "\n" );
$result = $test->Replace( [ "color" => "pink", "status" => "replaced" ], ["query" => "FOR r IN test_collection FILTER r.color == 'blue' RETURN r"] );
var_dump( $result );
echo( '$result = $test->FindByExample( [ "status" => "replaced" ] );' . "\n" );
$result = $test->FindByExample( [ "status" => "replaced" ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Record count.
//
echo( "Record count:\n" );
echo( '$result = $test->RecordCount();' . "\n" );
$result = $test->RecordCount();
var_dump( $result );

echo( "\n" );

//
// Count by example.
//
echo( "Count by example:\n" );
echo( '$result = $test->CountByExample( [ "status" => "replaced" ] );' . "\n" );
$result = $test->CountByExample( [ "status" => "replaced" ] );
var_dump( $result );

echo( "\n" );

//
// Count by query.
//
echo( "Count by query:\n" );
echo( '$result = $test->CountByQuery( ["query" => "FOR r IN test_collection FILTER r.data == \'XXX\' OR r.status == \'replaced\' RETURN r"] );' . "\n" );
$result = $test->CountByQuery( ["query" => "FOR r IN test_collection FILTER r.data == 'XXX' OR r.status == 'replaced' RETURN r"] );
var_dump( $result );

echo( "\n====================================================================================\n\n" );

//
// Aggregate records.
//
echo( "Aggregate records:\n" );
echo( '$result = $test->MapReduce( ["query" => "FOR r IN test_collection COLLECT theColour = r.color WITH COUNT INTO theCount RETURN{ theColour, theCount }"] );' . "\n" );
$result = $test->MapReduce( ["query" => "FOR r IN test_collection COLLECT theColour = r.color WITH COUNT INTO theCount RETURN{ theColour, theCount }"] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Delete by ID.
//
echo( "Delete by ID:\n" );
echo( '$result = $test->DeleteByKey( "ID1" );' . "\n" );
$result = $test->DeleteByKey( "ID1" );
var_dump( $result );
echo( '$result = $test->FindByExample();' . "\n" );
$result = $test->FindByExample();
print_r( $result );

echo( "\n" );

//
// Delete by example.
//
echo( "Delete by example:\n" );
echo( '$result = $test->DeleteByExample( ["data" => "Value 1"] );' . "\n" );
$result = $test->DeleteByExample( ["data" => "Value 1"] );
var_dump( $result );
echo( '$result = $test->FindByExample();' . "\n" );
$result = $test->FindByExample();
print_r( $result );

echo( "\n" );

//
// Delete by query.
//
echo( "Delete by query:\n" );
echo( '$result = $test->DeleteByQuery();' . "\n" );
$result = $test->DeleteByExample();
var_dump( $result );
echo( '$result = $test->FindByExample();' . "\n" );
$result = $test->FindByExample();
print_r( $result );


?>
