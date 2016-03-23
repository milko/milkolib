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

echo( "\n" );

//
// Clear collection.
//
echo( "Clear collection:\n" );
echo( '$test->Truncate();' . "\n" );
$test->Truncate();

echo( "\n====================================================================================\n\n" );

//
// Insert native document.
//
echo( "Insert native document:\n" );
echo( '$document = new MongoDB\Model\BSONDocument( ["data" => "Value 1", "color" => "red" ] );' . "\n" );
$document = new MongoDB\Model\BSONDocument( ["data" => "Value 1", "color" => "red" ] );
echo( '$result = $test->Insert( $document );' . "\n" );
$result = $test->Insert( $document );
var_dump( $result );
print_r( $document );

echo( "\n" );

//
// Insert container.
//
echo( "Insert container:\n" );
echo( '$document = new Milko\PHPLib\Container( [kTAG_MONGO_KEY => "ID1", "data" => 1, "color" => "green" ] );' . "\n" );
$document = new Milko\PHPLib\Container( [kTAG_MONGO_KEY => "ID1", "data" => 1, "color" => "green" ] );
echo( '$result = $test->Insert( $document );' . "\n" );
$result = $test->Insert( $document );
var_dump( $result );
print_r( $document );

echo( "\n" );

//
// Insert document.
//
echo( "Insert document:\n" );
echo( '$document = new Milko\PHPLib\Document( $test, [ "data" => "XXX", "color" => "red" ] );' . "\n" );
$document = new Milko\PHPLib\Document( $test, [ "data" => "XXX", "color" => "red" ] );
echo( '$result = $test->Insert( $document );' . "\n" );
$result = $test->Insert( $document );
var_dump( $result );
print_r( $document );
exit;

echo( "\n====================================================================================\n\n" );

//
// Insert many array documents.
//
echo( "Insert many array documents:\n" );
echo( '$document = [ [ kTAG_MONGO_KEY => "ID2", "data" => "XXX", "color" => "yellow" ], [ "name" => "Nati" ] ];' . "\n" );
$document = [ [ kTAG_MONGO_KEY => "ID2", "data" => "XXX", "color" => "yellow" ], [ "name" => "Nati" ] ];
echo( '$result = $test->Insert( $document, [ kTOKEN_OPT_MANY => TRUE ] );' . "\n" );
$result = $test->Insert( $document, [ kTOKEN_OPT_MANY => TRUE ] );
var_dump( $result );
print_r( $document );

echo( "\n" );

//
// Insert many documents.
//
echo( "Insert many documents:\n" );
echo( '$documents = [ new Milko\PHPLib\Document( $test, [ kTAG_MONGO_KEY => 7, "name" => "Cangalovic" ] ), new Milko\PHPLib\Document( $test, [ "name" => "no" ] ), new Milko\PHPLib\Document( $test, [ "name" => "yes" ] ) ];' . "\n" );
$documents = [ new Milko\PHPLib\Document( $test, [ kTAG_MONGO_KEY => 7, "name" => "Cangalovic" ] ), new Milko\PHPLib\Document( $test, [ "name" => "no" ] ), new Milko\PHPLib\Document( $test, [ "name" => "yes" ] ) ];
echo( '$result = $test->Insert( $documents, [ kTOKEN_OPT_MANY => TRUE ] );' . "\n" );
$result = $test->Insert( $documents, [ kTOKEN_OPT_MANY => TRUE ] );
print_r( $result );
print_r( $documents );

echo( "\n====================================================================================\n\n" );

//
// Delete one document.
//
echo( "Delete one document:\n" );
echo( '$result = $test->Delete( $documents[ 0 ] );' . "\n" );
$result = $test->Delete( $documents[ 0 ] );
var_dump( $result );
print_r( $documents[ 0 ] );

echo( "\n" );

//
// Delete many documents.
//
echo( "Delete many documents:\n" );
echo( '$result = $test->Delete( $documents, [ kTOKEN_OPT_MANY => TRUE ] );' . "\n" );
$result = $test->Delete( $documents, [ kTOKEN_OPT_MANY => TRUE ] );
var_dump( $result );
print_r( $documents );

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
echo( '$result = $test->CountByQuery( [ "color" => "red" ] );' . "\n" );
$result = $test->CountByQuery( [ "color" => "red" ] );
var_dump( $result );

echo( "\n====================================================================================\n\n" );

//
// Find by ID native.
//
echo( "Find by ID native:\n" );
echo( '$result = $test->FindById( "ID1", [kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE] );' . "\n" );
$result = $test->FindByKey( "ID1", [kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE] );
print_r( $result );
echo( "\n" );

echo( "\n" );

//
// Find by ID standard.
//
echo( "Find by ID standard:\n" );
echo( '$result = $test->FindById( "ID1", [kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD] );' . "\n" );
$result = $test->FindByKey( "ID1", [kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD] );
print_r( $result );
echo( "\n" );

echo( "\n" );

//
// Find by ID handle.
//
echo( "Find by ID handle:\n" );
echo( '$result = $test->FindById( "ID1", [kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE] );' . "\n" );
$result = $test->FindByKey( "ID1", [kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE] );
print_r( $result );
echo( "\n" );

echo( "\n====================================================================================\n\n" );

//
// Find many by ID native.
//
echo( "Find many by ID native:\n" );
echo( '$result = $test->FindById( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE] );' . "\n" );
$result = $test->FindByKey( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE] );
print_r( $result );
echo( "\n" );

echo( "\n" );

//
// Find many by ID standard.
//
echo( "Find many by ID standard:\n" );
echo( '$result = $test->FindById( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD] );' . "\n" );
$result = $test->FindByKey( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD] );
print_r( $result );
echo( "\n" );

echo( "\n" );

//
// Find many by ID handle.
//
echo( "Find by ID handle:\n" );
echo( '$result = $test->FindById( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE] );' . "\n" );
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
echo( '$result = $test->FindByQuery( [ "color" => "red" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );' . "\n" );
$result = $test->FindByQuery( [ "color" => "red" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );
print_r( $result );

echo( "\n" );

//
// Find first record standard by query.
//
echo( "Find first record standard by query:\n" );
echo( '$result = $test->FindByQuery( [ "color" => "red" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );' . "\n" );
$result = $test->FindByQuery( [ "color" => "red" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );
print_r( $result );

echo( "\n" );

//
// Find first record handle by query.
//
echo( "Find first record handle by query:\n" );
echo( '$result = $test->FindByQuery( [ "color" => "red" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );' . "\n" );
$result = $test->FindByQuery( [ "color" => "red" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Find all records native by query.
//
echo( "Find all records native by query:\n" );
echo( '$result = $test->FindByQuery( [ "color" => "red" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );' . "\n" );
$result = $test->FindByQuery( [ "color" => "red" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );
print_r( $result );

echo( "\n" );

//
// Find all records standard by query.
//
echo( "Find all records standard by query:\n" );
echo( '$result = $test->FindByQuery( [ "color" => "red" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );' . "\n" );
$result = $test->FindByQuery( [ "color" => "red" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );
print_r( $result );

echo( "\n" );

//
// Find all records handle by query.
//
echo( "Find all records handle by query:\n" );
echo( '$result = $test->FindByQuery( [ "color" => "red" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );' . "\n" );
$result = $test->FindByQuery( [ "color" => "red" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Update first record.
//
echo( "Update first record:\n" );
echo( '$result = $test->Update( [ \'$set\' => [ "color" => "blue", "status" => "changed" ] ], [ "color" => "green" ], [ kTOKEN_OPT_MANY => FALSE ] );' . "\n" );
$result = $test->Update( [ '$set' => [ "color" => "blue", "status" => "changed" ] ], [ "color" => "green" ], [ kTOKEN_OPT_MANY => FALSE ] );
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
echo( '$result = $test->CountByQuery( [ \'$or\' => [ [ \'data\' => \'XXX\' ], [ \'status\' => \'replaced\' ] ] ] );' . "\n" );
$result = $test->CountByQuery( [ '$or' => [ [ 'data' => 'XXX' ], [ 'status' => 'replaced' ] ] ] );
var_dump( $result );

echo( "\n====================================================================================\n\n" );

//
// Aggregate records.
//
echo( "Aggregate records:\n" );
$project = [ "colour" => '$color' ];
$group = [ kTAG_MONGO_KEY => '$colour', "count" => [ '$sum' => 1 ] ];
$sort = [ "count" => 1 ];
$pipeline = [ [ '$project' => $project ], [ '$group' => $group ], [ '$sort' => $sort ] ];
echo( '$pipeline = ' );
print_r( $pipeline );
$result = $test->MapReduce( $pipeline );
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
