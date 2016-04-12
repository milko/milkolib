<?php

/**
 * Relations object test suite.
 *
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		31/03/2016
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
// Document test classes.
//
class SRC extends Milko\PHPLib\Document{}
class DST extends Milko\PHPLib\Document{}
class DerivedFromDocument extends \Milko\PHPLib\Document {}

//
// Instantiate server.
//
echo( "Instantiate connection:\n" );
if( kENGINE == "MONGO" )
{
	echo( '$url = "mongodb://localhost:27017/test_milkolib";' . "\n" );
	$url = "mongodb://localhost:27017/test_milkolib";
	echo( '$server = new \Milko\PHPLib\MongoDB\DataServer( $url' . " );\n" );
	$server = new \Milko\PHPLib\MongoDB\DataServer( $url );
}
elseif( kENGINE == "ARANGO" )
{
	echo('$url = "tcp://localhost:8529/test_milkolib";' . "\n");
	$url = "tcp://localhost:8529/test_milkolib";
	echo( '$server = new \Milko\PHPLib\ArangoDB\DataServer( $url' . " );\n" );
	$server = new \Milko\PHPLib\ArangoDB\DataServer( $url );
}

echo( "\n" );

//
// Instantiate database.
//
echo( "Instantiate database:\n" );
echo( '$database = $server->RetrieveDatabase( "test_milkolib" );' . "\n" );
$database = $server->GetDatabase( "test_milkolib" );

echo( "\n" );

//
// Instantiate nodes collection.
//
echo( "Instantiate nodes collection:\n" );
echo( '$nodes = $database->RetrieveCollection( "nodes", Milko\PHPLib\Server::kFLAG_CREATE );' . "\n" );
$nodes = $database->GetCollection( "nodes", Milko\PHPLib\Server::kFLAG_CREATE );
var_dump( get_class( $nodes ) );
echo( '$nodes->Truncate();' . "\n" );
$nodes->Truncate();

echo( "\n" );

//
// Instantiate predicates collection.
//
echo( "Instantiate predicates collection:\n" );
echo( '$test = $database->RetrieveRelations( "edges", Milko\PHPLib\Server::kFLAG_CREATE );' . "\n" );
$test = $database->RetrieveRelations( "edges", Milko\PHPLib\Server::kFLAG_CREATE );
var_dump( get_class( $test ) );
echo( '$test->Truncate();' . "\n" );
$test->Truncate();

echo( "\n====================================================================================\n\n" );

//
// Test document conversion.
//
echo( "Test document conversion:\n" );
echo( '$data = [$test->KeyOffset() => "KEY", $test->RevisionOffset() => "REVISION", "data" => "some data"];' . "\n" );
$data = [$test->KeyOffset() => "KEY", $test->RevisionOffset() => "REVISION", "data" => "some data"];
print_r( $data );

echo( "\n" );

//
// Convert to native document from array.
//
echo( "Convert to native document from array:\n" );
echo( '$document = $test->NewNativeDocument( $data );' . "\n" );
$document = $test->NewNativeDocument( $data );
print_r( $document );

echo( "\n" );

//
// Convert to native document from array object.
//
echo( "Convert to native document from array object:\n" );
echo( '$document = $test->NewNativeDocument( new ArrayObject( $data ) );' . "\n" );
$document = $test->NewNativeDocument( new ArrayObject( $data ) );
print_r( $document );

echo( "\n" );

//
// Convert to native document from Container.
//
echo( "Convert to native document from Container:\n" );
echo( '$document = $test->NewNativeDocument( new Milko\PHPLib\Container( $data ) );' . "\n" );
$document = $test->NewNativeDocument( new Milko\PHPLib\Container( $data ) );
print_r( $document );

echo( "\n" );

//
// Convert to native document from Document.
//
echo( "Convert to native document from Document:\n" );
echo( '$document = $test->NewNativeDocument( new Milko\PHPLib\Document( $test, $data ) );' . "\n" );
$document = $test->NewNativeDocument( new Milko\PHPLib\Document( $test, $data ) );
print_r( $document );

echo( "\n====================================================================================\n\n" );

//
// Convert to document from native data.
//
echo( "Convert to document from native data:\n" );
echo( '$document = $test->NewDocument( $test->NewNativeDocument( $data ) );' . "\n" );
$document = $test->NewDocument( $test->NewNativeDocument( $data ) );
echo( "Class: " . get_class( $document ) . "\n" );
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Convert to document from array.
//
echo( "Convert to document from array:\n" );
echo( '$document = $test->NewDocument( $data );' . "\n" );
$document = $test->NewDocument( $data );
echo( "Class: " . get_class( $document ) . "\n" );
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Convert to document from array object.
//
echo( "Convert to document from array object:\n" );
echo( '$document = $test->NewDocument( new ArrayObject( $data ) );' . "\n" );
$document = $test->NewDocument( new ArrayObject( $data ) );
echo( "Class: " . get_class( $document ) . "\n" );
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Convert to document from Container.
//
echo( "Convert to document from Container:\n" );
echo( '$document = $test->NewDocument( new Milko\PHPLib\Container( $data ) );' . "\n" );
$document = $test->NewDocument( new Milko\PHPLib\Container( $data ) );
echo( "Class: " . get_class( $document ) . "\n" );
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Convert to document from Document.
//
echo( "Convert to document from Document:\n" );
echo( '$document = $test->NewDocument( new Milko\PHPLib\Document( $test, $data ) );' . "\n" );
$document = $test->NewDocument( new Milko\PHPLib\Document( $test, $data ) );
echo( "Class: " . get_class( $document ) . "\n" );
$tmp = $document[ $test->CLassOffset() ];
echo( "Document class: [$tmp]\n" );
$tmp = $document[ $test->KeyOffset() ];
echo( "Document key: [$tmp]\n" );
$tmp = $document[ $test->RevisionOffset() ];
echo( "Document revision: [$tmp]\n" );
echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Convert to document with class.
//
echo( "Convert to document with class:\n" );
echo( '$document = $test->NewDocument( array_merge( $data, [$test->ClassOffset() => "DerivedFromDocument"] ) );' . "\n" );
$document = $test->NewDocument( array_merge( $data, [$test->ClassOffset() => "DerivedFromDocument"] ) );
echo( "Class: " . get_class( $document ) . "\n" );
$tmp = $document[ $test->CLassOffset() ];
echo( "Document class: [$tmp]\n" );
$tmp = $document[ $test->KeyOffset() ];
echo( "Document key: [$tmp]\n" );
$tmp = $document[ $test->RevisionOffset() ];
echo( "Document revision: [$tmp]\n" );
echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Convert to document from derived class.
//
echo( "Convert to document from derived class:\n" );
echo( '$document = $test->NewDocument( new DerivedFromDocument( $test, $data ) );' . "\n" );
$document = $test->NewDocument( new DerivedFromDocument( $test, $data ) );
echo( "Class: " . get_class( $document ) . "\n" );
$tmp = $document[ $test->CLassOffset() ];
echo( "Document class: [$tmp]\n" );
$tmp = $document[ $test->KeyOffset() ];
echo( "Document key: [$tmp]\n" );
$tmp = $document[ $test->RevisionOffset() ];
echo( "Document revision: [$tmp]\n" );
echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $document->getArrayCopy() );

echo( "\n====================================================================================\n\n" );

//
// Convert to document handle from array.
//
echo( "Convert to document handle from array:\n" );
try
{
	echo( '$document = $test->NewDocumentHandle( ["data" => "some data"] );' . "\n" );
	$document = $test->NewDocumentHandle( ["data" => "some data"] );
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( InvalidArgumentException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}
echo( '$document = $test->NewDocumentHandle( ["data" => "some data", $test->KeyOffset() => "KEY"] );' . "\n" );
$document = $test->NewDocumentHandle( ["data" => "some data", $test->KeyOffset() => "KEY"] );
var_dump( $document );

echo( "\n" );

//
// Convert to document handle from array object.
//
echo( "Convert to document handle from array object:\n" );
echo( '$document = $test->NewDocumentHandle( new ArrayObject( ["data" => "some data", $test->KeyOffset() => "KEY"] ) );' . "\n" );
$document = $test->NewDocumentHandle( new ArrayObject( ["data" => "some data", $test->KeyOffset() => "KEY"] ) );
var_dump( $document );

echo( "\n" );

//
// Convert to document handle from Container.
//
echo( "Convert to document handle from Container:\n" );
echo( '$document = $test->NewDocumentHandle( new Milko\PHPLib\Container( ["data" => "some data", $test->KeyOffset() => "KEY"] ) );' . "\n" );
$document = $test->NewDocumentHandle( new Milko\PHPLib\Container( ["data" => "some data", $test->KeyOffset() => "KEY"] ) );
var_dump( $document );

echo( "\n" );

//
// Convert to document handle from Document.
//
echo( "Convert to document handle from Document:\n" );
echo( '$document = $test->NewDocumentHandle( new Milko\PHPLib\Document( $test, ["data" => "some data", $test->KeyOffset() => "KEY"] ) );' . "\n" );
$document = $test->NewDocumentHandle( new Milko\PHPLib\Document( $test, ["data" => "some data", $test->KeyOffset() => "KEY"] ) );
var_dump( $document );

echo( "\n====================================================================================\n\n" );

//
// Convert to document key from array.
//
echo( "Convert to document key from array:\n" );
try
{
	echo( '$document = $test->NewDocumentKey( ["data" => "some data"] );' . "\n" );
	$document = $test->NewDocumentKey( ["data" => "some data"] );
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( InvalidArgumentException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}
echo( '$document = $test->NewDocumentKey( ["data" => "some data", $test->KeyOffset() => "KEY"] );' . "\n" );
$document = $test->NewDocumentKey( ["data" => "some data", $test->KeyOffset() => "KEY"] );
var_dump( $document );

echo( "\n" );

//
// Convert to document key from array object.
//
echo( "Convert to document key from array object:\n" );
echo( '$document = $test->NewDocumentKey( new ArrayObject( ["data" => "some data", $test->KeyOffset() => "KEY"] ) );' . "\n" );
$document = $test->NewDocumentKey( new ArrayObject( ["data" => "some data", $test->KeyOffset() => "KEY"] ) );
var_dump( $document );

echo( "\n" );

//
// Convert to document key from Container.
//
echo( "Convert to document key from Container:\n" );
echo( '$document = $test->NewDocumentKey( new Milko\PHPLib\Container( ["data" => "some data", $test->KeyOffset() => "KEY"] ) );' . "\n" );
$document = $test->NewDocumentKey( new Milko\PHPLib\Container( ["data" => "some data", $test->KeyOffset() => "KEY"] ) );
var_dump( $document );

echo( "\n" );

//
// Convert to document key from Document.
//
echo( "Convert to document key from Document:\n" );
echo( '$document = $test->NewDocumentKey( new Milko\PHPLib\Document( $test, ["data" => "some data", $test->KeyOffset() => "KEY"] ) );' . "\n" );
$document = $test->NewDocumentKey( new Milko\PHPLib\Document( $test, ["data" => "some data", $test->KeyOffset() => "KEY"] ) );
var_dump( $document );

echo( "\n====================================================================================\n\n" );

//
// Insert nodes.
//
echo( "Insert nodes:\n" );
for( $i = 1; $i < 10; $i++ )
	$nodes->Insert( [$nodes->KeyOffset() => "Node$i" ] );

echo( "\n====================================================================================\n\n" );

//
// Insert native document.
//
echo( "Insert native document:\n" );
echo( '$document = $test->NewNativeDocument( [$test->VertexSource() => $nodes->NewHandle( "Node1" ), $test->VertexDestination() => $nodes->NewHandle( "Node2" ), "data" => "Value 1", "color" => "red", $test->ClassOffset() => "\DerivedFromDocument" ] );' . "\n" );
$document = $test->NewNativeDocument( [$test->VertexSource() => $nodes->NewHandle( "Node1" ), $test->VertexDestination() => $nodes->NewHandle( "Node2" ), "data" => "Value 1", "color" => "red", $test->ClassOffset() => "\DerivedFromDocument" ] );
echo( '$result = $test->Insert( $document );' . "\n" );
$result = $test->Insert( $document );
var_dump( $result );
print_r( $document );

echo( "\n" );

//
// Insert container.
//
echo( "Insert container:\n" );
echo( '$document = new Milko\PHPLib\Container( [$test->VertexSource() => $nodes->NewHandle( "Node3" ), $test->VertexDestination() => $nodes->NewHandle( "Node4" ), $test->KeyOffset() => "ID1", "data" => 1, "color" => "green" ] );' . "\n" );
$document = new Milko\PHPLib\Container( [$test->VertexSource() => $nodes->NewHandle( "Node3" ), $test->VertexDestination() => $nodes->NewHandle( "Node4" ), $test->KeyOffset() => "ID1", "data" => 1, "color" => "green" ] );
echo( '$result = $test->Insert( $document );' . "\n" );
$result = $test->Insert( $document );
var_dump( $result );
print_r( $document );
echo( "\n" );

//
// Insert document.
//
echo( "Insert document:\n" );
echo( '$document = new Milko\PHPLib\Document( $test, [$test->VertexSource() => $nodes->NewHandle( "Node5" ), $test->VertexDestination() => $nodes->NewHandle( "Node6" ), "data" => "XXX", "color" => "red"] );' . "\n" );
$document = new Milko\PHPLib\Document( $test, [$test->VertexSource() => $nodes->NewHandle( "Node5" ), $test->VertexDestination() => $nodes->NewHandle( "Node6" ), "data" => "XXX", "color" => "red"] );
echo( '$result = $test->Insert( $document );' . "\n" );
$result = $test->Insert( $document );
var_dump( $result );
echo( "Class: " . get_class( $document ) . "\n" );
$tmp = $document[ $test->CLassOffset() ];
echo( "Document class: [$tmp]\n" );
$tmp = $document[ $test->KeyOffset() ];
echo( "Document key: [$tmp]\n" );
$tmp = $document[ $test->RevisionOffset() ];
echo( "Document revision: [$tmp]\n" );
echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $document->getArrayCopy() );

echo( "\n" );

//
// Insert relation.
//
echo( "Insert relation:\n" );
echo( '$document = new Milko\PHPLib\Relation( $test, [$test->VertexSource() => $nodes->NewHandle( "Node7" ), $test->VertexDestination() => $nodes->NewHandle( "Node8" ), "data" => "XXX", "color" => "red"] );' . "\n" );
$document = new Milko\PHPLib\Edge( $test, [$test->VertexSource() => $nodes->NewHandle( "Node7" ), $test->VertexDestination() => $nodes->NewHandle( "Node8" ), "data" => "XXX", "color" => "red"] );
echo( '$result = $test->Insert( $document );' . "\n" );
$result = $test->Insert( $document );
var_dump( $result );
echo( "Class: " . get_class( $document ) . "\n" );
$tmp = $document[ $test->CLassOffset() ];
echo( "Document class: [$tmp]\n" );
$tmp = $document[ $test->KeyOffset() ];
echo( "Document key: [$tmp]\n" );
$tmp = $document[ $test->RevisionOffset() ];
echo( "Document revision: [$tmp]\n" );
echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $document->getArrayCopy() );

echo( "\n====================================================================================\n\n" );

//
// Insert many documents.
//
echo( "Insert many documents:\n" );
$documents = [];
$documents[0] = [ $test->VertexSource() => $nodes->NewHandle( "Node9" ), $test->VertexDestination() => $nodes->NewHandle( "Node1" ), $test->KeyOffset() => "ID2", "data" => "XXX", "color" => "yellow" ];
$documents[1] = $test->NewNativeDocument( [ $test->VertexSource() => $nodes->NewHandle( "Node2" ), $test->VertexDestination() => $nodes->NewHandle( "Node3" ), "name" => "Nati" ] );
$documents[2] = new Milko\PHPLib\Edge( $test, [ $test->VertexSource() => $nodes->NewHandle( "Node4" ), $test->VertexDestination() => $nodes->NewHandle( "Node5" ), $test->KeyOffset() => 7, "name" => "Cangalovic" ] );
$documents[3] = new \DerivedFromDocument( $test, [ $test->VertexSource() => $nodes->NewHandle( "Node6" ), $test->VertexDestination() => $nodes->NewHandle( "Node7" ), "name" => "no" ] );
$documents[4] = new Milko\PHPLib\Container( [ $test->VertexSource() => $nodes->NewHandle( "Node8" ), $test->VertexDestination() => $nodes->NewHandle( "Node9" ), "name" => "yes" ] );
echo( "»»»[0] " ); print_r( $documents[0] );
echo( "»»»[1] " ); print_r( $documents[1] );
echo( "»»»[2] Class: " . get_class( $documents[2] ) . "\n" );
$tmp = $documents[2][ $test->CLassOffset() ];
echo( "Document class: [$tmp]\n" );
$tmp = $documents[2][ $test->KeyOffset() ];
echo( "Document key: [$tmp]\n" );
$tmp = $documents[2][ $test->RevisionOffset() ];
echo( "Document revision: [$tmp]\n" );
echo( "Modified:   " . (( $documents[2]->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $documents[2]->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $documents[2]->getArrayCopy() );
echo( "»»»[3] Class: " . get_class( $documents[3] ) . "\n" );
$tmp = $documents[3][ $test->CLassOffset() ];
echo( "Document class: [$tmp]\n" );
$tmp = $documents[3][ $test->KeyOffset() ];
echo( "Document key: [$tmp]\n" );
$tmp = $documents[3][ $test->RevisionOffset() ];
echo( "Document revision: [$tmp]\n" );
echo( "Modified:   " . (( $documents[3]->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $documents[3]->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $documents[3]->getArrayCopy() );
echo( "»»»[4] " ); print_r( $documents[4] );
echo( "»»»\n" );
echo( '$result = $test->Insert( $documents, [ kTOKEN_OPT_MANY => TRUE ] );' . "\n" );
$result = $test->Insert( $documents, [ kTOKEN_OPT_MANY => TRUE ] );
print_r( $result );
echo( "Document keys:\n" );
foreach( $documents as $key => $document )
{
	if( $document instanceof Milko\PHPLib\Container )
		echo( "[$key] => " . get_class( $document ) . ' [' . $document[ $test->KeyOffset() ] . "]\n" );
	elseif( is_object( $document ) )
		echo( "[$key] => " . get_class( $document ) . "\n" );
	else
		echo( "[$key] => " . gettype( $document ) . "\n" );
}
echo( "Documents:\n" );
foreach( $documents as $key => $document )
{
	echo( "»»»[$key] " );
	if( $document instanceof Milko\PHPLib\Document )
	{
		echo( "Class: " . get_class( $document ) . "\n" );
		$tmp = $document[ $test->CLassOffset() ];
		echo( "Document class: [$tmp]\n" );
		$tmp = $document[ $test->KeyOffset() ];
		echo( "Document key: [$tmp]\n" );
		$tmp = $document[ $test->RevisionOffset() ];
		echo( "Document revision: [$tmp]\n" );
		echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
		echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
		echo( "Data: " );
		print_r( $document->getArrayCopy() );
	}
	else
		print_r( $document );
}

echo( "\n====================================================================================\n\n" );

//
// Get record count.
//
echo( "Get record count:\n" );
echo( '$result = $test->RecordCount();' . "\n" );
$result = $test->RecordCount();
var_dump( $result );
if( $result == 9 )
	echo( "SUCCEEDED!\n" );
else
	echo( "FAILED!!!\n" );

echo( "\n" );

//
// Count by example.
//
echo( "Count by example:\n" );
echo( '$result = $test->CountByExample( [ "color" => "red" ] );' . "\n" );
$result = $test->CountByExample( [ "color" => "red" ] );
var_dump( $result );
if( $result == 3 )
	echo( "SUCCEEDED!\n" );
else
	echo( "FAILED!!!\n" );

echo( "\n" );

//
// Count by query.
//
echo( "Count by query:\n" );
if( kENGINE == "MONGO" )
{
	echo( '$result = $test->CountByQuery( [ "color" => "red" ] );' . "\n" );
	$result = $test->CountByQuery( [ "color" => "red" ] );
}
elseif( kENGINE == "ARANGO" )
{
	echo( '$result = $test->CountByQuery( ["query" => "FOR r IN edges FILTER r.color == \'red\' RETURN r"] );' . "\n" );
	$result = $test->CountByQuery( ["query" => "FOR r IN edges FILTER r.color == 'red' RETURN r"] );
}
var_dump( $result );
if( $result == 3 )
	echo( "SUCCEEDED!\n" );
else
	echo( "FAILED!!!\n" );

echo( "\n====================================================================================\n\n" );

//
// Update first record.
//
echo( "Update first record:\n" );
if( kENGINE == "ARANGO" )
{
	echo( '$result = $test->Update( [ "color" => "blue", "status" => "changed" ], ["query" => "FOR r IN edges FILTER r.color == \'green\' RETURN r"], [ kTOKEN_OPT_MANY => FALSE ] );' . "\n" );
	$result = $test->Update( [ "color" => "blue", "status" => "changed" ], ["query" => "FOR r IN edges FILTER r.color == 'green' RETURN r"], [ kTOKEN_OPT_MANY => FALSE ] );
}
elseif( kENGINE == "MONGO" )
{
	echo( '$result = $test->Update( [ \'$set\' => [ "color" => "blue", "status" => "changed" ] ], [ "color" => "green" ], [ kTOKEN_OPT_MANY => FALSE ] );' . "\n" );
	$result = $test->Update( [ '$set' => [ "color" => "blue", "status" => "changed" ] ], [ "color" => "green" ], [ kTOKEN_OPT_MANY => FALSE ] );
}
var_dump( $result );
echo( '$result = $test->FindByExample( [ "status" => "changed" ] );' . "\n" );
$result = $test->FindByExample( [ "status" => "changed" ] );
foreach( $result as $key => $document )
{
	echo( "»»»[$key] " );
	if( $document instanceof Milko\PHPLib\Document )
	{
		echo( "Class: " . get_class( $document ) . "\n" );
		$tmp = $document[ $test->CLassOffset() ];
		echo( "Document class: [$tmp]\n" );
		$tmp = $document[ $test->KeyOffset() ];
		echo( "Document key: [$tmp]\n" );
		$tmp = $document[ $test->RevisionOffset() ];
		echo( "Document revision: [$tmp]\n" );
		echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
		echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
		echo( "Data: " );
		print_r( $document->getArrayCopy() );
	}
	else
		print_r( $document );
}

echo( "\n" );

//
// Update all records.
//
echo( "Update all records:\n" );
if( kENGINE == "ARANGO" )
{
	echo( '$result = $test->Update( [ "color" => "yellow", "status" => "was red" ], ["query" => "FOR r IN edges FILTER r.color == \'red\' RETURN r"] );' . "\n" );
	$result = $test->Update( [ "color" => "yellow", "status" => "was red" ], ["query" => "FOR r IN edges FILTER r.color == 'red' RETURN r"] );
}
elseif( kENGINE == "MONGO" )
{
	echo( '$result = $test->Update( [ \'$set\' => [ "color" => "yellow", "status" => "was red" ] ], [ "color" => "red" ] );' . "\n" );
	$result = $test->Update( [ '$set' => [ "color" => "yellow", "status" => "was red" ] ], [ "color" => "red" ] );
}
var_dump( $result );
echo( '$result = $test->FindByExample( [ "status" => "was red" ] );' . "\n" );
$result = $test->FindByExample( [ "status" => "was red" ] );
foreach( $result as $key => $document )
{
	echo( "»»»[$key] " );
	if( $document instanceof Milko\PHPLib\Document )
	{
		echo( "Class: " . get_class( $document ) . "\n" );
		$tmp = $document[ $test->CLassOffset() ];
		echo( "Document class: [$tmp]\n" );
		$tmp = $document[ $test->KeyOffset() ];
		echo( "Document key: [$tmp]\n" );
		$tmp = $document[ $test->RevisionOffset() ];
		echo( "Document revision: [$tmp]\n" );
		echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
		echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
		echo( "Data: " );
		print_r( $document->getArrayCopy() );
	}
	else
		print_r( $document );
}

echo( "\n====================================================================================\n\n" );

//
// Replace a record.
//
echo( "Replace a record:\n" );
if( kENGINE == "ARANGO" )
{
	echo( '$result = $test->Replace( [ $test->KeyOffset() => "ID1", "color" => "pink", "status" => "replaced" ] );' . "\n" );
	$result = $test->Replace( [ $test->KeyOffset() => "ID1", $test->VertexSource() => $nodes->NewHandle( "Node3" ), $test->VertexDestination () => $nodes->NewHandle( "Node4" ), "color" => "pink", "status" => "replaced" ] );
}
elseif( kENGINE == "MONGO" )
{
	echo( '$result = $test->Replace( [ $test->KeyOffset() => "ID1", $test->VertexSource() => $nodes->NewHandle( "Node3" ), $test->VertexDestination () => $nodes->NewHandle( "Node4" ), "color" => "pink", "status" => "replaced" ] );' . "\n" );
	$result = $test->Replace( [ $test->KeyOffset() => "ID1", $test->VertexSource() => $nodes->NewHandle( "Node3" ), $test->VertexDestination () => $nodes->NewHandle( "Node4" ), "color" => "pink", "status" => "replaced" ] );
}
var_dump( $result );
echo( '$result = $test->FindByExample( [ "status" => "replaced" ] );' . "\n" );
$result = $test->FindByExample( [ "status" => "replaced" ] );
foreach( $result as $key => $document )
{
	echo( "»»»[$key] " );
	if( $document instanceof Milko\PHPLib\Document )
	{
		echo( "Class: " . get_class( $document ) . "\n" );
		$tmp = $document[ $test->CLassOffset() ];
		echo( "Document class: [$tmp]\n" );
		$tmp = $document[ $test->KeyOffset() ];
		echo( "Document key: [$tmp]\n" );
		$tmp = $document[ $test->RevisionOffset() ];
		echo( "Document revision: [$tmp]\n" );
		echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
		echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
		echo( "Data: " );
		print_r( $document->getArrayCopy() );
	}
	else
		print_r( $document );
}

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
echo( "Class: " . get_class( $document ) . "\n" );
$tmp = $document[ $test->CLassOffset() ];
echo( "Document class: [$tmp]\n" );
$tmp = $document[ $test->KeyOffset() ];
echo( "Document key: [$tmp]\n" );
$tmp = $document[ $test->RevisionOffset() ];
echo( "Document revision: [$tmp]\n" );
echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $document->getArrayCopy() );
echo( "\n" );

echo( "\n" );

//
// Find by ID handle.
//
echo( "Find by ID handle:\n" );
echo( '$handle = $test->FindByKey( "ID1", [kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE] );' . "\n" );
$handle = $test->FindByKey( "ID1", [kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE] );
print_r( $handle );
echo( "\n" );

echo( "\n" );

//
// Find by handle.
//
echo( "Find by handle:\n" );
echo( '$result = $test->FindByHandle( $handle );' . "\n" );
$result = $test->FindByHandle( $handle );
echo( "Class: " . get_class( $document ) . "\n" );
$tmp = $document[ $test->CLassOffset() ];
echo( "Document class: [$tmp]\n" );
$tmp = $document[ $test->KeyOffset() ];
echo( "Document key: [$tmp]\n" );
$tmp = $document[ $test->RevisionOffset() ];
echo( "Document revision: [$tmp]\n" );
echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $document->getArrayCopy() );

echo( "\n====================================================================================\n\n" );

//
// Find many by ID native.
//
echo( "Find many by ID native:\n" );
echo( '$result = $test->FindByKey( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE] );' . "\n" );
$result = $test->FindByKey( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE] );
foreach( $result as $key => $document )
{
	echo( "»»»[$key] " );
	if( $document instanceof Milko\PHPLib\Document )
	{
		echo( "Class: " . get_class( $document ) . "\n" );
		$tmp = $document[ $test->CLassOffset() ];
		echo( "Document class: [$tmp]\n" );
		$tmp = $document[ $test->KeyOffset() ];
		echo( "Document key: [$tmp]\n" );
		$tmp = $document[ $test->RevisionOffset() ];
		echo( "Document revision: [$tmp]\n" );
		echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
		echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
		echo( "Data: " );
		print_r( $document->getArrayCopy() );
	}
	else
		print_r( $document );
}

echo( "\n" );

//
// Find many by ID standard.
//
echo( "Find many by ID standard:\n" );
echo( '$result = $test->FindByKey( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD] );' . "\n" );
$result = $test->FindByKey( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD] );
foreach( $result as $key => $document )
{
	echo( "»»»[$key] " );
	if( $document instanceof Milko\PHPLib\Document )
	{
		echo( "Class: " . get_class( $document ) . "\n" );
		$tmp = $document[ $test->CLassOffset() ];
		echo( "Document class: [$tmp]\n" );
		$tmp = $document[ $test->KeyOffset() ];
		echo( "Document key: [$tmp]\n" );
		$tmp = $document[ $test->RevisionOffset() ];
		echo( "Document revision: [$tmp]\n" );
		echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
		echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
		echo( "Data: " );
		print_r( $document->getArrayCopy() );
	}
	else
		print_r( $document );
}

echo( "\n" );

//
// Find many by ID key.
//
echo( "Find many by ID key:\n" );
echo( '$handle = $test->FindByKey( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_KEY] );' . "\n" );
$handle = $test->FindByKey( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_KEY] );
print_r( $handle );
echo( "\n" );

echo( "\n" );

//
// Find many by ID handle.
//
echo( "Find many by ID handle:\n" );
echo( '$handle = $test->FindByKey( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE] );' . "\n" );
$handle = $test->FindByKey( ["ID1", "ID2"], [kTOKEN_OPT_MANY => TRUE, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE] );
print_r( $handle );
echo( "\n" );

echo( "\n" );

//
// Find many by handle.
//
echo( "Find by many by handle:\n" );
echo( '$result = $test->FindByHandle( $handle, [kTOKEN_OPT_MANY => TRUE] );' . "\n" );
$result = $test->FindByHandle( $handle, [kTOKEN_OPT_MANY => TRUE] );
foreach( $result as $key => $document )
{
	echo( "»»»[$key] " );
	if( $document instanceof Milko\PHPLib\Document )
	{
		echo( "Class: " . get_class( $document ) . "\n" );
		$tmp = $document[ $test->CLassOffset() ];
		echo( "Document class: [$tmp]\n" );
		$tmp = $document[ $test->KeyOffset() ];
		echo( "Document key: [$tmp]\n" );
		$tmp = $document[ $test->RevisionOffset() ];
		echo( "Document revision: [$tmp]\n" );
		echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
		echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
		echo( "Data: " );
		print_r( $document->getArrayCopy() );
	}
	else
		print_r( $document );
}

echo( "\n====================================================================================\n\n" );

//
// Find first record native by example.
//
echo( "Find first record native by example:\n" );
echo( '$result = $test->FindByExample( [ "color" => "yellow" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );' . "\n" );
$result = $test->FindByExample( [ "color" => "yellow" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );
print_r( $result );

echo( "\n" );

//
// Find first record standard by example.
//
echo( "Find first record standard by example:\n" );
echo( '$result = $test->FindByExample( [ "color" => "yellow" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );' . "\n" );
$result = $test->FindByExample( [ "color" => "yellow" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );
foreach( $result as $key => $document )
{
	echo( "»»»[$key] " );
	if( $document instanceof Milko\PHPLib\Document )
	{
		echo( "Class: " . get_class( $document ) . "\n" );
		$tmp = $document[ $test->CLassOffset() ];
		echo( "Document class: [$tmp]\n" );
		$tmp = $document[ $test->KeyOffset() ];
		echo( "Document key: [$tmp]\n" );
		$tmp = $document[ $test->RevisionOffset() ];
		echo( "Document revision: [$tmp]\n" );
		echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
		echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
		echo( "Data: " );
		print_r( $document->getArrayCopy() );
	}
	else
		print_r( $document );
}

echo( "\n" );

//
// Find first record key by example.
//
echo( "Find first record key by example:\n" );
echo( '$result = $test->FindByExample( [ "color" => "yellow" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_KEY ] );' . "\n" );
$result = $test->FindByExample( [ "color" => "yellow" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_KEY ] );
print_r( $result );

echo( "\n" );

//
// Find first record handle by example.
//
echo( "Find first record handle by example:\n" );
echo( '$result = $test->FindByExample( [ "color" => "yellow" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );' . "\n" );
$result = $test->FindByExample( [ "color" => "yellow" ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Find all records native by example.
//
echo( "Find all records native by example:\n" );
echo( '$result = $test->FindByExample( [ "color" => "yellow" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );' . "\n" );
$result = $test->FindByExample( [ "color" => "yellow" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );
print_r( $result );

echo( "\n" );

//
// Find all records standard by example.
//
echo( "Find all records standard by example:\n" );
echo( '$result = $test->FindByExample( [ "color" => "yellow" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );' . "\n" );
$result = $test->FindByExample( [ "color" => "yellow" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );
foreach( $result as $key => $document )
{
	echo( "»»»[$key] " );
	if( $document instanceof Milko\PHPLib\Document )
	{
		echo( "Class: " . get_class( $document ) . "\n" );
		$tmp = $document[ $test->CLassOffset() ];
		echo( "Document class: [$tmp]\n" );
		$tmp = $document[ $test->KeyOffset() ];
		echo( "Document key: [$tmp]\n" );
		$tmp = $document[ $test->RevisionOffset() ];
		echo( "Document revision: [$tmp]\n" );
		echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
		echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
		echo( "Data: " );
		print_r( $document->getArrayCopy() );
	}
	else
		print_r( $document );
}

echo( "\n" );

//
// Find all records key by example.
//
echo( "Find all records key by example:\n" );
echo( '$result = $test->FindByExample( [ "color" => "yellow" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_KEY ] );' . "\n" );
$result = $test->FindByExample( [ "color" => "yellow" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_KEY ] );
print_r( $result );

echo( "\n" );

//
// Find all records handle by example.
//
echo( "Find all records handle by example:\n" );
echo( '$result = $test->FindByExample( [ "color" => "yellow" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );' . "\n" );
$result = $test->FindByExample( [ "color" => "yellow" ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Find first record native by query.
//
echo( "Find first record native by query:\n" );
if( kENGINE == "ARANGO" )
{
	echo( '$result = $test->FindByQuery( ["query" => "FOR r IN edges FILTER r.color == \'yellow\' OR r.color == \'pink\' LIMIT 1 RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );' . "\n" );
	$result = $test->FindByQuery( ["query" => "FOR r IN edges FILTER r.color == 'yellow' OR r.color == 'pink' LIMIT 1 RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );
}
elseif( kENGINE == "MONGO" )
{
	echo( '$result = $test->FindByQuery( [ \'$or\' => [ ["color" => "yellow"], ["color" => "pink"] ] ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );' . "\n" );
	$result = $test->FindByQuery( [ '$or' => [ ["color" => "yellow"], ["color" => "pink"] ] ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );
}
print_r( $result );

echo( "\n" );

//
// Find first record standard by query.
//
echo( "Find first record standard by query:\n" );
if( kENGINE == "ARANGO" )
{
	echo( '$result = $test->FindByQuery( ["query" => "FOR r IN edges FILTER r.color == \'yellow\' OR r.color == \'pink\' LIMIT 1 RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );' . "\n" );
	$result = $test->FindByQuery( ["query" => "FOR r IN edges FILTER r.color == 'yellow' OR r.color == 'pink' LIMIT 1 RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );
}
elseif( kENGINE == "MONGO" )
{
	echo( '$result = $test->FindByQuery( [ \'$or\' => [ ["color" => "yellow"], ["color" => "pink"] ] ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );' . "\n" );
	$result = $test->FindByQuery( [ '$or' => [ ["color" => "yellow"], ["color" => "pink"] ] ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );
}
foreach( $result as $key => $document )
{
	echo( "»»»[$key] " );
	if( $document instanceof Milko\PHPLib\Document )
	{
		echo( "Class: " . get_class( $document ) . "\n" );
		$tmp = $document[ $test->CLassOffset() ];
		echo( "Document class: [$tmp]\n" );
		$tmp = $document[ $test->KeyOffset() ];
		echo( "Document key: [$tmp]\n" );
		$tmp = $document[ $test->RevisionOffset() ];
		echo( "Document revision: [$tmp]\n" );
		echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
		echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
		echo( "Data: " );
		print_r( $document->getArrayCopy() );
	}
	else
		print_r( $document );
}

echo( "\n" );

//
// Find first record key by query.
//
echo( "Find first record key by query:\n" );
if( kENGINE == "ARANGO" )
{
	echo( '$result = $test->FindByQuery( ["query" => "FOR r IN edges FILTER r.color == \'yellow\' OR r.color == \'pink\' LIMIT 1 RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_KEY ] );' . "\n" );
	$result = $test->FindByQuery( ["query" => "FOR r IN edges FILTER r.color == 'yellow' OR r.color == 'pink' LIMIT 1 RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_KEY ] );
}
elseif( kENGINE == "MONGO" )
{
	echo( '$result = $test->FindByQuery( [ \'$or\' => [ ["color" => "yellow"], ["color" => "pink"] ] ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );' . "\n" );
	$result = $test->FindByQuery( [ '$or' => [ ["color" => "yellow"], ["color" => "pink"] ] ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );
}
print_r( $result );

echo( "\n" );

//
// Find first record handle by query.
//
echo( "Find first record handle by query:\n" );
if( kENGINE == "ARANGO" )
{
	echo( '$result = $test->FindByQuery( ["query" => "FOR r IN edges FILTER r.color == \'yellow\' OR r.color == \'pink\' LIMIT 1 RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );' . "\n" );
	$result = $test->FindByQuery( ["query" => "FOR r IN edges FILTER r.color == 'yellow' OR r.color == 'pink' LIMIT 1 RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );
}
elseif( kENGINE == "MONGO" )
{
	echo( '$result = $test->FindByQuery( [ \'$or\' => [ ["color" => "yellow"], ["color" => "pink"] ] ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );' . "\n" );
	$result = $test->FindByQuery( [ '$or' => [ ["color" => "yellow"], ["color" => "pink"] ] ], [ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );
}
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Find all records native by query.
//
echo( "Find all records native by query:\n" );
if( kENGINE == "ARANGO" )
{
	echo( '$result = $test->FindByQuery( ["query" => "FOR r IN edges FILTER r.color == \'yellow\' OR r.color == \'pink\' RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );' . "\n" );
	$result = $test->FindByQuery( ["query" => "FOR r IN edges FILTER r.color == 'yellow' OR r.color == 'pink' RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );
}
elseif( kENGINE == "MONGO" )
{
	echo( '$result = $test->FindByQuery( [ \'$or\' => [ ["color" => "yellow"], ["color" => "pink"] ] ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );' . "\n" );
	$result = $test->FindByQuery( [ '$or' => [ ["color" => "yellow"], ["color" => "pink"] ] ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_NATIVE ] );
}
print_r( $result );

echo( "\n" );

//
// Find all records standard by query.
//
echo( "Find all records standard by query:\n" );
if( kENGINE == "ARANGO" )
{
	echo( '$result = $test->FindByQuery( ["query" => "FOR r IN edges FILTER r.color == \'yellow\' OR r.color == \'pink\' RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );' . "\n" );
	$result = $test->FindByQuery( ["query" => "FOR r IN edges FILTER r.color == 'yellow' OR r.color == 'pink' RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );
}
elseif( kENGINE == "MONGO" )
{
	echo( '$result = $test->FindByQuery( [ \'$or\' => [ ["color" => "yellow"], ["color" => "pink"] ] ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );' . "\n" );
	$result = $test->FindByQuery( [ '$or' => [ ["color" => "yellow"], ["color" => "pink"] ] ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_STANDARD ] );
}
foreach( $result as $key => $document )
{
	echo( "»»»[$key] " );
	if( $document instanceof Milko\PHPLib\Document )
	{
		echo( "Class: " . get_class( $document ) . "\n" );
		$tmp = $document[ $test->CLassOffset() ];
		echo( "Document class: [$tmp]\n" );
		$tmp = $document[ $test->KeyOffset() ];
		echo( "Document key: [$tmp]\n" );
		$tmp = $document[ $test->RevisionOffset() ];
		echo( "Document revision: [$tmp]\n" );
		echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
		echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
		echo( "Data: " );
		print_r( $document->getArrayCopy() );
	}
	else
		print_r( $document );
}

echo( "\n" );

//
// Find all records key by query.
//
echo( "Find all records key by query:\n" );
if( kENGINE == "ARANGO" )
{
	echo( '$result = $test->FindByQuery( ["query" => "FOR r IN edges FILTER r.color == \'yellow\' OR r.color == \'pink\' RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_KEY ] );' . "\n" );
	$result = $test->FindByQuery( ["query" => "FOR r IN edges FILTER r.color == 'yellow' OR r.color == 'pink' RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_KEY ] );
}
elseif( kENGINE == "MONGO" )
{
	echo( '$result = $test->FindByQuery( [ \'$or\' => [ ["color" => "yellow"], ["color" => "pink"] ] ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );' . "\n" );
	$result = $test->FindByQuery( [ '$or' => [ ["color" => "yellow"], ["color" => "pink"] ] ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );
}
print_r( $result );

echo( "\n" );

//
// Find all records handle by query.
//
echo( "Find all records handle by query:\n" );
if( kENGINE == "ARANGO" )
{
	echo( '$result = $test->FindByQuery( ["query" => "FOR r IN edges FILTER r.color == \'yellow\' OR r.color == \'pink\' RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );' . "\n" );
	$result = $test->FindByQuery( ["query" => "FOR r IN edges FILTER r.color == 'yellow' OR r.color == 'pink' RETURN r"], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );
}
elseif( kENGINE == "MONGO" )
{
	echo( '$result = $test->FindByQuery( [ \'$or\' => [ ["color" => "yellow"], ["color" => "pink"] ] ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );' . "\n" );
	$result = $test->FindByQuery( [ '$or' => [ ["color" => "yellow"], ["color" => "pink"] ] ], [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_HANDLE ] );
}
print_r( $result );

echo( "\n====================================================================================\n\n" );

//
// Record count.
//
echo( "Record count:\n" );
echo( '$result = $test->RecordCount();' . "\n" );
$result = $test->RecordCount();
var_dump( $result );
if( $result == 9 )
	echo( "SUCCEEDED!\n" );
else
	echo( "FAILED!!!\n" );

echo( "\n" );

//
// Count by example.
//
echo( "Count by example:\n" );
echo( '$result = $test->CountByExample( [ "status" => "replaced" ] );' . "\n" );
$result = $test->CountByExample( [ "status" => "replaced" ] );
var_dump( $result );
if( $result == 1 )
	echo( "SUCCEEDED!\n" );
else
	echo( "FAILED!!!\n" );

echo( "\n" );

//
// Count by query.
//
echo( "Count by query:\n" );
if( kENGINE == "ARANGO" )
{
	echo( '$result = $test->CountByQuery( ["query" => "FOR r IN edges FILTER r.data == \'XXX\' OR r.status == \'replaced\' RETURN r"] );' . "\n" );
	$result = $test->CountByQuery( ["query" => "FOR r IN edges FILTER r.data == 'XXX' OR r.status == 'replaced' RETURN r"] );
}
elseif( kENGINE == "MONGO" )
{
	echo( '$result = $test->CountByQuery( [ \'$or\' => [ [ \'data\' => \'XXX\' ], [ \'status\' => \'replaced\' ] ] ] );' . "\n" );
	$result = $test->CountByQuery( [ '$or' => [ [ 'data' => 'XXX' ], [ 'status' => 'replaced' ] ] ] );
}
var_dump( $result );
if( $result == 4 )
	echo( "SUCCEEDED!\n" );
else
	echo( "FAILED!!!\n" );

echo( "\n====================================================================================\n\n" );

//
// Aggregate records.
//
echo( "Aggregate records:\n" );
if( kENGINE == "ARANGO" )
{
	$pipeline = ["query" => "FOR r IN edges COLLECT theColour = r.color WITH COUNT INTO theCount RETURN{ theColour, theCount }"];
	echo( '$pipeline = ' );
	print_r( $pipeline );
	echo( '$result = $test->MapReduce( $pipeline );' . "\n" );
	$result = $test->MapReduce( $pipeline );
}
elseif( kENGINE == "MONGO" )
{
	$project = [ "colour" => '$color' ];
	$group = [ kTAG_MONGO_KEY => '$colour', "count" => [ '$sum' => 1 ] ];
	$sort = [ "count" => 1 ];
	$pipeline = [ [ '$project' => $project ], [ '$group' => $group ], [ '$sort' => $sort ] ];
	echo( '$pipeline = ' );
	print_r( $pipeline );
	$result = $test->MapReduce( $pipeline );
}
foreach( $result as $key => $document )
{
	echo( "»»»[$key] " );
	if( $document instanceof Milko\PHPLib\Document )
	{
		echo( "Class: " . get_class( $document ) . "\n" );
		$tmp = $document[ $test->CLassOffset() ];
		echo( "Document class: [$tmp]\n" );
		$tmp = $document[ $test->KeyOffset() ];
		echo( "Document key: [$tmp]\n" );
		$tmp = $document[ $test->RevisionOffset() ];
		echo( "Document revision: [$tmp]\n" );
		echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
		echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
		echo( "Data: " );
		print_r( $document->getArrayCopy() );
	}
	else
		print_r( $document );
}
echo( "Should be: 4 without color; 1 pink and 4 yellow.\n" );

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
var_dump( count( $result ) );
foreach( $result as $key => $document )
{
	echo( "»»»[$key] " );
	if( $document instanceof Milko\PHPLib\Document )
	{
		echo( "Class: " . get_class( $document ) . "\n" );
		$tmp = $document[ $test->CLassOffset() ];
		echo( "Document class: [$tmp]\n" );
		$tmp = $document[ $test->KeyOffset() ];
		echo( "Document key: [$tmp]\n" );
		$tmp = $document[ $test->RevisionOffset() ];
		echo( "Document revision: [$tmp]\n" );
		echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
		echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
		echo( "Data: " );
		print_r( $document->getArrayCopy() );
	}
	else
		print_r( $document );
}

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
var_dump( count( $result ) );
foreach( $result as $key => $document )
{
	echo( "»»»[$key] " );
	if( $document instanceof Milko\PHPLib\Document )
	{
		echo( "Class: " . get_class( $document ) . "\n" );
		$tmp = $document[ $test->CLassOffset() ];
		echo( "Document class: [$tmp]\n" );
		$tmp = $document[ $test->KeyOffset() ];
		echo( "Document key: [$tmp]\n" );
		$tmp = $document[ $test->RevisionOffset() ];
		echo( "Document revision: [$tmp]\n" );
		echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
		echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
		echo( "Data: " );
		print_r( $document->getArrayCopy() );
	}
	else
		print_r( $document );
}

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
var_dump( count( $result ) );
foreach( $result as $key => $document )
{
	echo( "»»»[$key] " );
	if( $document instanceof Milko\PHPLib\Document )
	{
		echo( "Class: " . get_class( $document ) . "\n" );
		$tmp = $document[ $test->CLassOffset() ];
		echo( "Document class: [$tmp]\n" );
		$tmp = $document[ $test->KeyOffset() ];
		echo( "Document key: [$tmp]\n" );
		$tmp = $document[ $test->RevisionOffset() ];
		echo( "Document revision: [$tmp]\n" );
		echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
		echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
		echo( "Data: " );
		print_r( $document->getArrayCopy() );
	}
	else
		print_r( $document );
}


?>
