<?php

/**
 * Document set object test suite.
 *
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		13/03/2016
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
use Milko\PHPLib\DocumentSet;

//
// Other classes.
//
use Milko\PHPLib\MongoDB\Collection;

//
// Instantiate collection.
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
// Clear collection.
//
echo( "Clear collection:\n" );
echo( '$collection->Truncate();' . "\n" );
$collection->Truncate();

echo( "\n====================================================================================\n\n" );

//
// Instantiate document set.
//
echo( "Instantiate document set:\n" );
echo( '$test = new \Milko\PHPLib\DocumentSet( $collection, 3 );' . "\n" );
$test = new \Milko\PHPLib\DocumentSet( $collection, 3 );
echo( "Collection: " . (string) $test->Collection() . "\n" );
echo( "Buffer count: " . $test->BufferCount() . "\n" );

echo( "\n====================================================================================\n\n" );

//
// Insert many records.
//
echo( "Insert many records:\n" );
echo( 'for( $i = 1; $i <= 10; $i++ ) $test[] = [ "number" => $i ];' . "\n" );
for( $i = 1; $i <= 10; $i++ ) $test[] = [ "number" => $i ];

echo( "\n" );

//
// Get record count.
//
echo( "Get record count:\n" );
echo( '$result = $collection->RecordCount();' . "\n" );
$result = $collection->RecordCount();
var_dump( $result );

echo( "\n" );

//
// Flush buffer.
//
echo( "Flush buffer:\n" );
echo( '$test->Flush();' . "\n" );
$test->Flush();

echo( "\n" );

//
// Get record count.
//
echo( "Get record count:\n" );
echo( '$result = $collection->RecordCount();' . "\n" );
$result = $collection->RecordCount();
var_dump( $result );


?>
