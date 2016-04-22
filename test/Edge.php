<?php

/**
 * Edge object test suite.
 *
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		30/03/2016
 */

//
// Global definitions.
//
define( 'kENGINE', "MONGO" );

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
class SRC extends \Milko\PHPLib\Document{}
class DST extends \Milko\PHPLib\Document{}

//
// Enable exception logging.
//
//triagens\ArangoDb\Exception::enableLogging();

//
// Instantiate connection.
//
echo( "Instantiate connection:\n" );
if( kENGINE == "MONGO" )
{
	echo( '$url = "mongodb://localhost:27017/test_milkolib";' . "\n" );
	$url = "mongodb://localhost:27017/test_milkolib";
	echo( '$server = new \Milko\PHPLib\MongoDB\Server( $url' . " );\n" );
	$server = new \Milko\PHPLib\MongoDB\Server( $url );
}
elseif( kENGINE == "ARANGO" )
{
	echo('$url = "tcp://localhost:8529/test_milkolib";' . "\n");
	$url = "tcp://localhost:8529/test_milkolib";
	echo( '$server = new \Milko\PHPLib\ArangoDB\Server( $url' . " );\n" );
	$server = new \Milko\PHPLib\ArangoDB\Server( $url );
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
echo( '$nodes = $database->NewCollection( "nodes" );' . "\n" );
$nodes = $database->NewCollection( "nodes" );
var_dump( get_class( $nodes ) );
echo( '$nodes->Truncate();' . "\n" );
$nodes->Truncate();

echo( "\n" );

//
// Instantiate predicates collection.
//
echo( "Instantiate predicates collection:\n" );
echo( '$predicates = $database->NewEdgesCollection( "edges" );' . "\n" );
$predicates = $database->NewEdgesCollection( "edges" );
var_dump( get_class( $predicates ) );
echo( '$predicates->Truncate();' . "\n" );
$predicates->Truncate();

echo( "\n====================================================================================\n\n" );

//
// Instantiate edge.
//
echo( "Instantiate edge:\n" );
echo( '$edge = new Milko\PHPLib\Edge( $predicates, [$predicates->KeyOffset() => "Predicate1", kTAG_NAME => ["en" => "Predicate"], "data" => "DATA"] );' . "\n" );
$edge = new Milko\PHPLib\Edge( $predicates, [$predicates->KeyOffset() => "Predicate1", kTAG_NAME => ["en" => "Predicate"], "data" => "DATA"] );
echo( "Class: " . get_class( $edge ) . "\n" );
echo( "Modified:   " . (( $edge->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $edge->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $edge->getArrayCopy() );

echo( "\n" );

//
// Instantiate SRC.
//
echo( "Instantiate SRC:\n" );
echo( '$src = new SRC( $nodes, [$nodes->KeyOffset() => "Node1", "Label" => ["en" => "Source node"]] );' . "\n" );
$src = new SRC( $nodes, [$nodes->KeyOffset() => "Node1", "Label" => ["en" => "Source node"]] );
echo( "Class: " . get_class( $src ) . "\n" );
echo( "Modified:   " . (( $src->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $src->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $src->getArrayCopy() );

echo( "\n" );

//
// Instantiate DST.
//
echo( "Instantiate DST:\n" );
echo( '$dst = new SRC( $nodes, [$nodes->KeyOffset() => "pippo", "Label" => ["en" => "Destination node"]] );' . "\n" );
$dst = new SRC( $nodes, [$nodes->KeyOffset() => "pippo", "Label" => ["en" => "Destination node"]] );
echo( "Class: " . get_class( $dst ) . "\n" );
echo( "Modified:   " . (( $dst->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $dst->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $dst->getArrayCopy() );


echo( "\n====================================================================================\n\n" );

//
// Set key.
//
echo( "Set key:\n" );
echo( '$dst[ $nodes->KeyOffset() ] = "Node2";' . "\n" );
$dst[ $nodes->KeyOffset() ] = "Node2";
echo( "Class: " . get_class( $dst ) . "\n" );
echo( "Modified:   " . (( $dst->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $dst->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $dst->getArrayCopy() );

echo( "\n" );

//
// Set class.
//
echo( "Set class:\n" );
try
{
	echo( '$dst[ $nodes->ClassOffset() ] = "A";' . "\n" );
	$dst[ $nodes->ClassOffset() ] = "A";
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
	echo( '$dst[ $nodes->RevisionOffset() ] = 33143106288;' . "\n" );
	$dst[ $nodes->RevisionOffset() ] = 33143106288;
	echo( "Class: " . get_class( $dst ) . "\n" );
	echo( "Modified:   " . (( $dst->IsModified() ) ? "Yes\n" : "No\n") );
	echo( "Persistent: " . (( $dst->IsPersistent() ) ? "Yes\n" : "No\n") );
	echo( "Data: " );
	print_r( $dst->getArrayCopy() );
}
catch( RuntimeException $error )
{
	echo( "FALIED! - Should not have raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n====================================================================================\n\n" );

//
// Insert edge.
//
echo( "Insert edge:\n" );
try
{
	echo( '$handle = $edge->Store();' . "\n" );
	$handle = $edge->Store();
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n" );

//
// Set edge source and destination.
//
echo( "Set edge source:\n" );
echo( '$result = $edge[ $predicates->VertexSource() ] = $src;' . "\n" );
$result = $edge[ $predicates->VertexSource() ] = $src;
echo( '$result = $edge[ $predicates->VertexDestination() ] = $dst;' . "\n" );
$result = $edge[ $predicates->VertexDestination() ] = $dst;

echo( "\n" );

//
// Insert edge.
//
echo( "Insert edge:\n" );
echo( '$handle = $edge->Store();' . "\n" );
$handle = $edge->Store();
var_dump( $handle );
echo( "Class: " . get_class( $edge ) . "\n" );
echo( "Modified:   " . (( $edge->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $edge->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $edge->getArrayCopy() );

echo( "\n" );

//
// Delete edge.
//
echo( "Delete edge:\n" );
echo( '$key = $edge->Delete();' . "\n" );
$key = $edge->Delete();
echo( "Class: " . get_class( $edge ) . "\n" );
echo( "Modified:   " . (( $edge->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $edge->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $edge->getArrayCopy() );

echo( "\n" );

//
// Set edge source and destination.
//
echo( "Set edge source:\n" );
echo( '$result = $edge[ $predicates->VertexSource() ] = $nodes->NewDocumentHandle( $src );' . "\n" );
$result = $edge[ $predicates->VertexSource() ] = $nodes->NewDocumentHandle( $src );
echo( '$result = $edge[ $predicates->VertexDestination() ] = $nodes->NewDocumentHandle( $dst );' . "\n" );
$result = $edge[ $predicates->VertexDestination() ] = $nodes->NewDocumentHandle( $dst );
print_r( $edge->getArrayCopy() );

echo( "\n" );

//
// Insert edge.
//
echo( "Insert edge:\n" );
echo( '$handle = $edge->Store();' . "\n" );
$handle = $edge->Store();
var_dump( $handle );
echo( "Class: " . get_class( $edge ) . "\n" );
echo( "Modified:   " . (( $edge->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $edge->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $edge->getArrayCopy() );

echo( "\n====================================================================================\n\n" );

//
// Get source vertex.
//
echo( "Get source vertex:\n" );
echo( '$result = $edge->GetSource();' . "\n" );
$result = $edge->GetSource();
echo( "Class: " . get_class( $result ) . "\n" );
echo( "Modified:   " . (( $result->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $result->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $result->getArrayCopy() );

echo( "\n" );

//
// Get destination vertex.
//
echo( "Get destination vertex:\n" );
echo( '$result = $edge->GetDestination();' . "\n" );
$result = $edge->GetDestination();
echo( "Class: " . get_class( $result ) . "\n" );
echo( "Modified:   " . (( $result->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $result->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $result->getArrayCopy() );

echo( "\n====================================================================================\n\n" );

//
// Get SRC incoming edges.
//
echo( "Get SRC incoming edges:\n" );
echo( '$result = $predicates->FindByVertex( $src, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_IN, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT] );' . "\n" );
$result = $predicates->FindByVertex( $src, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_IN, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT] );
foreach( $result as $document )
{
	echo( "Class: " . get_class( $document ) . "\n" );
	echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
	echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
	echo( "Data: " );
	print_r( $document->getArrayCopy() );
}

echo( "\n" );

//
// Get SRC outgoing edges.
//
echo( "Get SRC outgoing edges:\n" );
echo( '$result = $predicates->FindByVertex( $src, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_OUT, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT] );' . "\n" );
$result = $predicates->FindByVertex( $src, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_OUT, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT] );
foreach( $result as $document )
{
	echo( "Class: " . get_class( $document ) . "\n" );
	echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
	echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
	echo( "Data: " );
	print_r( $document->getArrayCopy() );
}

echo( "\n" );

//
// Get SRC edges.
//
echo( "Get SRC edges:\n" );
echo( '$result = $predicates->FindByVertex( $src, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_ANY, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT] );' . "\n" );
$result = $predicates->FindByVertex( $src, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_ANY, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT] );
foreach( $result as $document )
{
	echo( "Class: " . get_class( $document ) . "\n" );
	echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
	echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
	echo( "Data: " );
	print_r( $document->getArrayCopy() );
}

echo( "\n====================================================================================\n\n" );

//
// Get DST incoming edges.
//
echo( "Get DST incoming edges:\n" );
echo( '$result = $predicates->FindByVertex( $dst, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_IN, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT] );' . "\n" );
$result = $predicates->FindByVertex( $dst, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_IN, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT] );
foreach( $result as $document )
{
	echo( "Class: " . get_class( $document ) . "\n" );
	echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
	echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
	echo( "Data: " );
	print_r( $document->getArrayCopy() );
}

echo( "\n" );

//
// Get DST outgoing edges.
//
echo( "Get DST outgoing edges:\n" );
echo( '$result = $predicates->FindByVertex( $dst, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_OUT, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT] );' . "\n" );
$result = $predicates->FindByVertex( $dst, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_OUT, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT] );
foreach( $result as $document )
{
	echo( "Class: " . get_class( $document ) . "\n" );
	echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
	echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
	echo( "Data: " );
	print_r( $document->getArrayCopy() );
}

echo( "\n" );

//
// Get DST edges.
//
echo( "Get DST edges:\n" );
echo( '$result = $predicates->FindByVertex( $dst, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_ANY, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT] );' . "\n" );
$result = $predicates->FindByVertex( $dst, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_ANY, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT] );
foreach( $result as $document )
{
	echo( "Class: " . get_class( $document ) . "\n" );
	echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
	echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
	echo( "Data: " );
	print_r( $document->getArrayCopy() );
}

echo( "\n====================================================================================\n\n" );

//
// Modify source vertex.
//
echo( "Modify source vertex:\n" );
try
{
	echo( '$edge[ predicates->VertexSource() ] = $dst;' . "\n" );
	$edge[ $predicates->VertexSource() ] = $dst;
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n" );

//
// Modify destination vertex.
//
echo( "Modify destination vertex:\n" );
try
{
	echo( '$edge[ predicates->VertexDestination() ] = $src;' . "\n" );
	$edge[ $predicates->VertexDestination() ] = $src;
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( RuntimeException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n====================================================================================\n\n" );

//
// Get edge by example.
//
echo( "Get edge by example:\n" );
echo( '$result = $predicates->FindByExample( ["data" => "DATA"] );' . "\n" );
$result = $predicates->FindByExample( ["data" => "DATA"] );
foreach( $result as $document )
{
	echo( "Class: " . get_class( $document ) . "\n" );
	echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
	echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
	echo( "Data: " );
	print_r( $document->getArrayCopy() );
}


?>
