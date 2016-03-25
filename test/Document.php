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
echo( '$collection->Truncate();' . "\n" );
$collection->Truncate();

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
echo( "Class: " . get_class( $document ) . "\n" );
echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Instantiate A.
//
echo( "Instantiate A:\n" );
echo( '$A = new A( $collection, $container );' . "\n" );
$A = new A( $collection, $container );
echo( "Class: " . get_class( $A ) . "\n" );
echo( "Modified:   " . (( $A->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $A->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $A->getArrayCopy() );

echo( "\n" );

//
// Instantiate B.
//
echo( "Instantiate B:\n" );
echo( '$B = new B( $collection, $A );' . "\n" );
$B = new B( $collection, $A );
echo( "Class: " . get_class( $B ) . "\n" );
echo( "Modified:   " . (( $B->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $B->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $B->getArrayCopy() );

echo( "\n====================================================================================\n\n" );

//
// Set key.
//
echo( "Set key:\n" );
echo( '$B[ $collection->KeyOffset() ] = "pippo";' . "\n" );
$B[ $collection->KeyOffset() ] = "pippo";
echo( "Class: " . get_class( $B ) . "\n" );
echo( "Modified:   " . (( $B->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $B->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $B->getArrayCopy() );

echo( "\n" );

//
// Set class.
//
echo( "Set class:\n" );
try
{
	echo( '$B[ $collection->ClassOffset() ] = "A";' . "\n" );
	$B[ $collection->ClassOffset() ] = "A";
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n" );

//
// Set revision.
//
echo( "Set revision:\n" );
try
{
	echo( '$B[ $collection->RevisionOffset() ] = 33143106288;' . "\n" );
	$B[ $collection->RevisionOffset() ] = 33143106288;
	echo( "Class: " . get_class( $B ) . "\n" );
	echo( "Modified:   " . (( $B->IsModified() ) ? "Yes\n" : "No\n") );
	echo( "Persistent: " . (( $B->IsPersistent() ) ? "Yes\n" : "No\n") );
	echo( "Data: " );
	print_r( $B->getArrayCopy() );
}
catch( RuntimeException $error )
{
	echo( "FALIED! - Should not have raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n====================================================================================\n\n" );

//
// Insert B.
//
echo( "Insert B:\n" );
echo( '$key = $collection->Insert( $B );' . "\n" );
$key = $collection->Insert( $B );
echo( "Class: " . get_class( $B ) . "\n" );
echo( "Modified:   " . (( $B->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $B->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $B->getArrayCopy() );

echo( "\n====================================================================================\n\n" );

//
// Set key.
//
echo( "Set key:\n" );
try
{
	echo( '$B[ $collection->KeyOffset() ] = "pippo";' . "\n" );
	$B[ $collection->KeyOffset() ] = "pippo";
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n" );

//
// Set class.
//
echo( "Set class:\n" );
try
{
	echo( '$B[ $collection->ClassOffset() ] = "A";' . "\n" );
	$B[ $collection->ClassOffset() ] = "A";
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n" );

//
// Set revision.
//
echo( "Set revision:\n" );
try
{
	echo( '$B[ $collection->RevisionOffset() ] = 33143106288;' . "\n" );
	$B[ $collection->RevisionOffset() ] = 33143106288;
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}


?>
