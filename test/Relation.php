<?php

/**
 * Relation object test suite.
 *
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		30/03/2016
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
// Reference class.
//
use Milko\PHPLib\MongoDB\Relation;
use Milko\PHPLib\MongoDB\Collection;

//
// Document test classes.
//
class SRC extends Milko\PHPLib\Document{}
class DST extends Milko\PHPLib\Document{}

//
// Instantiate object.
//
if( kENGINE == "MONGO" )
{
	echo( '$url = "mongodb://localhost:27017/test_milkolib/test_collection";' . "\n" );
	$url = "mongodb://localhost:27017/test_milkolib/test_collection";
	echo( '$server = new \Milko\PHPLib\MongoDB\DataServer( $url' . " );\n" );
	$server = new \Milko\PHPLib\MongoDB\DataServer( $url );
}
elseif( kENGINE == "ARANGO" )
{
	echo('$url = "tcp://localhost:8529/test_milkolib/test_collection";' . "\n");
	$url = "tcp://localhost:8529/test_milkolib/test_collection";
	echo( '$server = new \Milko\PHPLib\ArangoDB\DataServer( $url' . " );\n" );
	$server = new \Milko\PHPLib\ArangoDB\DataServer( $url );
}
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
// Instantiate edge.
//
echo( "Instantiate edge:\n" );
echo( '$document = new Milko\PHPLib\Relation( $collection, $container );' . "\n" );
$document = new Milko\PHPLib\Relation( $collection, $container );
echo( "Class: " . get_class( $document ) . "\n" );
echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Instantiate SRC.
//
echo( "Instantiate SRC:\n" );
echo( '$A = new SRC( $collection, $container );' . "\n" );
$A = new SRC( $collection, $container );
echo( "Class: " . get_class( $A ) . "\n" );
echo( "Modified:   " . (( $A->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $A->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $A->getArrayCopy() );

echo( "\n" );

//
// Instantiate DST.
//
echo( "Instantiate DST:\n" );
echo( '$B = new DST( $collection, $A );' . "\n" );
$B = new DST( $collection, $A );
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
echo( '$key = $B->Store();' . "\n" );
$key = $B->Store();
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

echo( "\n====================================================================================\n\n" );

//
// Create embedded document 1.
//
echo( "Create embedded document 1:\n" );
echo( '$sub1 = new A( $collection, [$collection->KeyOffset() => "sub1", "name" => "Object 1"] );' . "\n" );
$sub1 = new SRC( $collection, [$collection->KeyOffset() => "sub1", "name" => "Object 1"] );
echo( "Class: " . get_class( $sub1 ) . "\n" );
echo( "Modified:   " . (( $sub1->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $sub1->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $sub1->getArrayCopy() );

echo( "\n" );

//
// Create embedded document 2.
//
echo( "Create embedded document 2:\n" );
echo( '$sub2 = new B( $collection, [$collection->KeyOffset() => "sub2", "name" => "Object 2"] );' . "\n" );
$sub2 = new DST( $collection, [$collection->KeyOffset() => "sub2", "name" => "Object 2"] );
echo( "Class: " . get_class( $sub2 ) . "\n" );
echo( "Modified:   " . (( $sub2->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $sub2->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $sub2->getArrayCopy() );

echo( "\n" );

//
// Create container document.
//
echo( "Create container document:\n" );
echo( '$document = new Milko\PHPLib\Relation( $collection, ["name" => "container", "sub1" => $sub1, "sub2" => $sub2] );' . "\n" );
$document = new Milko\PHPLib\Relation( $collection, ["name" => "container", "sub1" => $sub1, "sub2" => $sub2] );
echo( "Class: " . get_class( $document ) . "\n" );
echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Store document.
//
echo( "Store document:\n" );
echo( '$key = $document->Store();' . "\n" );
$key = $document->Store();
var_dump( $key );
echo( "Class: " . get_class( $document ) . "\n" );
echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $document->getArrayCopy() );

echo( "\n====================================================================================\n\n" );

//
// Retrieve embedded document 1.
//
echo( "Retrieve embedded document 1:\n" );
echo( '$sub1 = $collection->FindByHandle( $document["sub1"] );' . "\n" );
$sub1 = $collection->FindByHandle( $document["sub1"] );
echo( "Class: " . get_class( $sub1 ) . "\n" );
echo( "Modified:   " . (( $sub1->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $sub1->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $sub1->getArrayCopy() );

echo( "\n" );

//
// Retrieve embedded document 2.
//
echo( "Retrieve embedded document 2:\n" );
echo( '$sub2 = $collection->FindByHandle( $document["sub2"] );' . "\n" );
$sub2 = $collection->FindByHandle( $document["sub2"] );
echo( "Class: " . get_class( $sub2 ) . "\n" );
echo( "Modified:   " . (( $sub2->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $sub2->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $sub2->getArrayCopy() );


?>
