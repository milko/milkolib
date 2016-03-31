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
// Document test classes.
//
class SRC extends Milko\PHPLib\Document{}
class DST extends Milko\PHPLib\Document{}

//
// Instantiate connection.
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
	echo('$url = "tcp://localhost:8529/test_milkolib/test_collection";' . "\n");
	$url = "tcp://localhost:8529/test_milkolib/test_collection";
	echo( '$server = new \Milko\PHPLib\ArangoDB\DataServer( $url' . " );\n" );
	$server = new \Milko\PHPLib\ArangoDB\DataServer( $url );
}

echo( "\n" );

//
// Instantiate database.
//
echo( "Instantiate database:\n" );
echo( '$database = $server->RetrieveDatabase( "test_milkolib" );' . "\n" );
$database = $server->RetrieveDatabase( "test_milkolib" );

echo( "\n" );

//
// Instantiate nodes collection.
//
echo( "Instantiate nodes collection:\n" );
echo( '$nodes = $database->RetrieveCollection( "nodes", Milko\PHPLib\Server::kFLAG_CREATE );' . "\n" );
$nodes = $database->RetrieveCollection( "nodes", Milko\PHPLib\Server::kFLAG_CREATE );
var_dump( get_class( $nodes ) );
echo( '$nodes->Truncate();' . "\n" );
$nodes->Truncate();

echo( "\n" );

//
// Instantiate predicates collection.
//
echo( "Instantiate predicates collection:\n" );
echo( '$predicates = $database->RetrieveCollection( "edges", Milko\PHPLib\Server::kFLAG_CREATE, ["type" => \triagens\ArangoDb\Collection::TYPE_EDGE] );' . "\n" );
$predicates = $database->RetrieveCollection( "edges", Milko\PHPLib\Server::kFLAG_CREATE, ["type" => \triagens\ArangoDb\Collection::TYPE_EDGE] );
var_dump( get_class( $predicates ) );
echo( '$predicates->Truncate();' . "\n" );
$predicates->Truncate();

echo( "\n====================================================================================\n\n" );

//
// Instantiate edge.
//
echo( "Instantiate edge:\n" );
try
{
	echo( '$edge = new Milko\PHPLib\Relation( $nodes, [$predicates->KeyOffset() => "Predicate1", "Label" => "Predicate"] );' . "\n" );
	$edge = new Milko\PHPLib\Relation( $nodes, [$predicates->KeyOffset() => "Predicate1", "Label" => "Predicate"] );
	echo( "FALIED! - Should have raised an exception.\n" );
}
catch( InvalidArgumentException $error )
{
	echo( "SUCCEEDED! - Has raised an exception.\n" );
	echo( $error->getMessage() . "\n" );
}

echo( "\n" );

//
// Instantiate edge.
//
echo( "Instantiate edge:\n" );
echo( '$edge = new Milko\PHPLib\Relation( $predicates, [$predicates->KeyOffset() => "Predicate1", "Label" => "Predicate"] );' . "\n" );
$edge = new Milko\PHPLib\Relation( $predicates, [$predicates->KeyOffset() => "Predicate1", "Label" => "Predicate"] );
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
	echo( '$key = $edge->Store();' . "\n" );
	$key = $edge->Store();
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
print_r( $edge->getArrayCopy() );

echo( "\n" );

//
// Insert edge.
//
echo( "Insert edge:\n" );
echo( '$key = $edge->Store();' . "\n" );
$key = $edge->Store();
var_dump( $key );
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
echo( '$key = $edge->Store();' . "\n" );
$key = $edge->Store();
var_dump( $key );
echo( "Class: " . get_class( $edge ) . "\n" );
echo( "Modified:   " . (( $edge->IsModified() ) ? "Yes\n" : "No\n") );
echo( "Persistent: " . (( $edge->IsPersistent() ) ? "Yes\n" : "No\n") );
echo( "Data: " );
print_r( $edge->getArrayCopy() );

echo( "\n====================================================================================\n\n" );

//
// Get SRC edges.
//
echo( "Get SRC edges:\n" );
echo( '$result = $predicates->FindByVertex( $src, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_ANY] );' . "\n" );
$result = $predicates->FindByVertex( $src, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_ANY] );
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
// Get SRC incoming edges.
//
echo( "Get SRC incoming edges:\n" );
echo( '$result = $predicates->FindByVertex( $src, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_IN] );' . "\n" );
$result = $predicates->FindByVertex( $src, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_IN] );
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
echo( '$result = $predicates->FindByVertex( $src, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_OUT] );' . "\n" );
$result = $predicates->FindByVertex( $src, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_OUT] );
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
// Get DST edges.
//
echo( "Get DST edges:\n" );
echo( '$result = $predicates->FindByVertex( $dst, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_ANY] );' . "\n" );
$result = $predicates->FindByVertex( $dst, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_ANY] );
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
// Get DST incoming edges.
//
echo( "Get DST incoming edges:\n" );
echo( '$result = $predicates->FindByVertex( $dst, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_IN] );' . "\n" );
$result = $predicates->FindByVertex( $dst, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_IN] );
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
echo( '$result = $predicates->FindByVertex( $dst, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_OUT] );' . "\n" );
$result = $predicates->FindByVertex( $dst, [kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_OUT] );
foreach( $result as $document )
{
	echo( "Class: " . get_class( $document ) . "\n" );
	echo( "Modified:   " . (( $document->IsModified() ) ? "Yes\n" : "No\n") );
	echo( "Persistent: " . (( $document->IsPersistent() ) ? "Yes\n" : "No\n") );
	echo( "Data: " );
	print_r( $document->getArrayCopy() );
}


?>