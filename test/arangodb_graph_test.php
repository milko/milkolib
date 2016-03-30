<?php

/**
 * ArangoDB graphs test suite.
 *
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		28/03/2016
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

// set up some aliases for less typing later
use triagens\ArangoDb\Database as ArangoDatabase;
use triagens\ArangoDb\Collection as ArangoCollection;
use triagens\ArangoDb\CollectionHandler as ArangoCollectionHandler;
use triagens\ArangoDb\Endpoint as ArangoEndpoint;
use triagens\ArangoDb\Connection as ArangoConnection;
use triagens\ArangoDb\ConnectionOptions as ArangoConnectionOptions;
use triagens\ArangoDb\DocumentHandler as ArangoDocumentHandler;
use triagens\ArangoDb\Document as ArangoDocument;
use triagens\ArangoDb\EdgeDefinition as ArangoEdgeDefinition;
use triagens\ArangoDb\EdgeHandler as ArangoEdgeHandler;
use triagens\ArangoDb\Edge as ArangoEdge;
use triagens\ArangoDb\GraphHandler as ArangoGraphHandler;
use triagens\ArangoDb\Graph as ArangoGraph;
use triagens\ArangoDb\Exception as ArangoException;
use triagens\ArangoDb\Export as ArangoExport;
use triagens\ArangoDb\ConnectException as ArangoConnectException;
use triagens\ArangoDb\ClientException as ArangoClientException;
use triagens\ArangoDb\ServerException as ArangoServerException;
use triagens\ArangoDb\Statement as ArangoStatement;
use triagens\ArangoDb\UpdatePolicy as ArangoUpdatePolicy;

/* set up a trace function that will be called for each communication with the server */
$traceFunc = function($type, $data) {
	print "TRACE FOR ". $type . PHP_EOL;
	var_dump($data);
};

/* set up connection options */
$connectionOptions = array(
	ArangoConnectionOptions::OPTION_ENDPOINT      => 'tcp://localhost:8529',   // endpoint to connect to
	ArangoConnectionOptions::OPTION_DATABASE      => 'test_graphs',            // database name
	ArangoConnectionOptions::OPTION_AUTH_TYPE     => 'Basic',                  // use basic authorization
	ArangoConnectionOptions::OPTION_CONNECTION    => 'Keep-Alive',             // can use either 'Close' (one-time connections) or 'Keep-Alive' (re-used connections)
	ArangoConnectionOptions::OPTION_TIMEOUT       => 30,                       // timeout in seconds
	ArangoConnectionOptions::OPTION_RECONNECT     => false,                    // reconnect
	ArangoConnectionOptions::OPTION_CREATE        => true,                     // do not create unknown collections automatically
	ArangoConnectionOptions::OPTION_UPDATE_POLICY => ArangoUpdatePolicy::LAST, // last update wins
//	ArangoConnectionOptions::OPTION_TRACE         => $traceFunc,               // tracer function, can be used for debugging
);

//
// Enable exception logging.
//
//ArangoException::enableLogging();

//
// Prepare options.
//
unset( $connectionOptions[ ArangoConnectionOptions::OPTION_DATABASE ] );
//unset( $connectionOptions[ ArangoConnectionOptions::OPTION_AUTH_PASSWD ] );

//
// Create connection.
//
echo( "Create server connection:\n" );
echo( '$connection = new ArangoConnection($connectionOptions);' . "\n" );
$connection = new ArangoConnection($connectionOptions);

echo( "\n" );

//
// Create database.
//
echo( "Create database:\n" );

if( ! in_array( 'test_graphs', ArangoDatabase::listDatabases( $connection )[ 'result' ] ) ) {
	$result = ArangoDatabase::create( $connection, 'test_graphs' );
	print_r( $result );
}
$connection->setDatabase( 'test_graphs' );
$list = ArangoDatabase::getInfo( $connection );
print_r( $list );

echo( "\n" );

echo( "Create collection handler:\n" );
echo( '$collectionHandler = new ArangoCollectionHandler($connection);' . "\n" );
$collectionHandler = new ArangoCollectionHandler($connection);
echo( "Create document handler:\n" );
echo( '$documentHandler = new ArangoDocumentHandler($connection);' . "\n" );
$documentHandler = new ArangoDocumentHandler($connection);
echo( "Create edge handler:\n" );
echo( '$edgeHandler = new ArangoEdgeHandler($connection);' . "\n" );
$edgeHandler = new ArangoEdgeHandler($connection);

echo( "\n" );

//
// Create nodes collection.
//
echo( "Create nodes collection:\n" );
if( $collectionHandler->has( "test_nodes" ) )
	$collectionHandler->truncate( "test_nodes" );
else
	$collectionHandler->create( "test_nodes" );
echo( '$nodes_collection = $collectionHandler->get( "test_nodes" );' . "\n" );
$nodes_collection = $collectionHandler->get( "test_nodes" );
echo( '$result = $nodes_collection->getType();' . "\n" );
$result = $nodes_collection->getType();
var_dump( $result );

echo( "\n" );

//
// Create predicates collection.
//
echo( "Create predicates collection:\n" );
if( $collectionHandler->has( "test_predicates" ) )
	$collectionHandler->truncate( "test_predicates" );
else
	$collectionHandler->create( "test_predicates", [ "type" => 3 ] );
echo( '$predicates_collection = $collectionHandler->get( "test_predicates" );' . "\n" );
$predicates_collection = $collectionHandler->get( "test_predicates" );
echo( '$result = $predicates_collection->getType();' . "\n" );
$result = $predicates_collection->getType();
var_dump( $result );

echo( "\n====================================================================================\n\n" );

//
// Create source node.
//
echo( "Create source node:\n" );
echo( '$node_source = ArangoDocument::createFromArray( [ "_key" => "node1", "Name" => "Source node" ] );' . "\n" );
$node_source = ArangoDocument::createFromArray( [ "_key" => "node1", "Name" => "Source node" ] );
echo( '$result = $documentHandler->save( $nodes_collection->getId(), $node_source );' . "\n" );
$result = $documentHandler->save( $nodes_collection->getId(), $node_source );
var_dump( $result );

echo( "\n" );

//
// Create destination node.
//
echo( "Create destination node:\n" );
echo( '$node_dest = ArangoDocument::createFromArray( [ "_key" => "node2", "Name" => "Destination node" ] );' . "\n" );
$node_dest = ArangoDocument::createFromArray( [ "_key" => "node2", "Name" => "Destination node" ] );
echo( '$result = $documentHandler->save( $nodes_collection->getId(), $node_dest );' . "\n" );
$result = $documentHandler->save( $nodes_collection->getId(), $node_dest );
var_dump( $result );

echo( "\n" );

//
// Add vertices via data.
//
echo( "Add vertices via data:\n" );
echo( '$predicate = ArangoEdge::createFromArray( [ "_key" => "pred1", "Name" => "Predicate", kTAG_ARANGO_REL_FROM => $node_source->getHandle(), kTAG_ARANGO_REL_TO => $node_dest->getHandle() ] );' . "\n" );
$predicate = ArangoEdge::createFromArray( [ "_key" => "pred1", "Name" => "Predicate", kTAG_ARANGO_REL_FROM => $node_source->getHandle(), kTAG_ARANGO_REL_TO => $node_dest->getHandle() ] );
print_r( $predicate );
echo( '$predicate->getAll();' . "\n" );
print_r( $predicate->getAll() );

echo( "\n" );

//
// Save predicate.
//
echo( "Save predicate:\n" );
echo( '$edge_id = $edgeHandler->saveEdge( $predicates_collection->getName(), $node_source->getHandle(), $node_dest->getHandle(), $predicate );' . "\n" );
$edge_id = $edgeHandler->saveEdge( $predicates_collection->getName(), $node_source->getHandle(), $node_dest->getHandle(), $predicate );
var_dump( $edge_id );
print_r( $predicate );

echo( "\n" );

//
// Get predicate.
//
echo( "Get predicate:\n" );
echo( '$result = $edgeHandler->getById( $predicates_collection->getName(), $edge_id );' . "\n" );
$result = $edgeHandler->getById( $predicates_collection->getName(), $edge_id );
print_r( $result );
print_r( $result->getAll() );

echo( "\n====================================================================================\n\n" );

//
// Drop predicate and nodes.
//
echo( "Drop predicate and nodes:\n" );
echo( '$edgeHandler->removeById( $predicates_collection->getName(), $edge_id );' . "\n" );
$edgeHandler->removeById( $predicates_collection->getName(), $edge_id );
echo( '$documentHandler->remove( $node_source );' . "\n" );
$documentHandler->remove( $node_source );
echo( '$documentHandler->remove( $node_dest );' . "\n" );
$documentHandler->remove( $node_dest );

echo( "\n====================================================================================\n\n" );

//
// Create graph connections.
//
echo( "Create graph connections:\n" );
echo( '$graphHandler = new ArangoGraphHandler($connection);' . "\n" );
$graphHandler = new ArangoGraphHandler($connection);
echo( '$graph = new ArangoGraph( "Graph1" );' . "\n" );
$graph = new ArangoGraph( "Graph1" );
echo( '$graph->addEdgeDefinition( ArangoEdgeDefinition::createUndirectedRelation( "test_predicates", [ "test_nodes" ] ) );' . "\n" );
$graph->addEdgeDefinition( ArangoEdgeDefinition::createUndirectedRelation( "test_predicates", [ "test_nodes" ] ) );

echo( "\n" );

//
// Create graph.
//
echo( "Create graph:\n" );
echo( 'try{ $graphHandler->dropGraph($graph); }' . "\n" );
try{ $graphHandler->dropGraph($graph); }
catch( \Exception $error ){}
echo( '$graphHandler->createGraph($graph);' . "\n" );
$graphHandler->createGraph($graph);

echo( "\n" );

//
// Create predicate and nodes.
//
echo( "Create predicate and nodes:\n" );
echo( '$node_source = ArangoDocument::createFromArray( $node_source->getAll() );' . "\n" );
$node_source = ArangoDocument::createFromArray( $node_source->getAll() );
echo( '$node_dest = ArangoDocument::createFromArray( $node_dest->getAll() );' . "\n" );
$node_dest = ArangoDocument::createFromArray( $node_dest->getAll() );
echo( '$predicate = ArangoEdge::createFromArray( $predicate->getAll() );' . "\n" );
$predicate = ArangoEdge::createFromArray( $predicate->getAll() );

echo( "\n" );

//
// Save vertices.
//
//echo( '$handle_in = $graphHandler->saveVertex( $graph, $node_source );' . "\n" );
//$handle_in = $graphHandler->saveVertex( $graph, $node_source );
//var_dump( $handle_in );
//echo( '$handle_out = $graphHandler->saveVertex( $graph, $node_dest );' . "\n" );
//$handle_out = $graphHandler->saveVertex( $graph, $node_dest );
//var_dump( $handle_out );

//
// Save vertices.
//
echo( "Save vertices:\n" );
echo( '$result = $documentHandler->save( $nodes_collection->getName(), $node_source );' . "\n" );
$result = $documentHandler->save( $nodes_collection->getName(), $node_source );
echo( '$handle_in = $node_source->getHandle();' . "\n" );
$handle_in = $node_source->getHandle();
var_dump( $handle_in );
echo( '$result = $documentHandler->save( $nodes_collection->getName(), $node_dest );' . "\n" );
$result = $documentHandler->save( $nodes_collection->getName(), $node_dest );
echo( '$handle_out = $node_dest->getHandle();' . "\n" );
$handle_out = $node_dest->getHandle();
var_dump( $handle_out );

echo( "\n" );

//
// Add predicate.
//
echo( "Add predicate:\n" );
echo( '$edge_id = $graphHandler->saveEdge( $graph, $handle_in, $handle_out, "a label", $predicate );' . "\n" );
$edge_id = $graphHandler->saveEdge( $graph, $handle_in, $handle_out, "a label", $predicate );
var_dump( $edge_id );
print_r( $predicate );

echo( "\n====================================================================================\n\n" );

//
// Create graph connections.
//
echo( "Create graph connections:\n" );
echo( '$graphHandler = new ArangoGraphHandler($connection);' . "\n" );
$graphHandler = new ArangoGraphHandler($connection);
echo( '$graph = new ArangoGraph( "Graph2" );' . "\n" );
$graph = new ArangoGraph( "Graph2" );
echo( '$graph->addEdgeDefinition( ArangoEdgeDefinition::createUndirectedRelation( "test_predicates", [ "test_nodes" ] ) );' . "\n" );
$graph->addEdgeDefinition( ArangoEdgeDefinition::createUndirectedRelation( "test_predicates", [ "test_nodes" ] ) );

echo( "\n" );

//
// Create graph.
//
echo( "Create graph:\n" );
echo( 'try{ $graphHandler->dropGraph($graph); }' . "\n" );
try{ $graphHandler->dropGraph($graph); }
catch( \Exception $error ){}
echo( '$graphHandler->createGraph($graph);' . "\n" );
$graphHandler->createGraph($graph);

echo( "\n" );

//
// Create predicate and nodes.
//
echo( "Create predicate and nodes:\n" );
echo( '$node_source = ArangoDocument::createFromArray( [ "_key" => "node3", "Name" => "Source node" ] );' . "\n" );
$node_source = ArangoDocument::createFromArray( [ "_key" => "node3", "Name" => "Source node" ] );
echo( '$node_dest = ArangoDocument::createFromArray( [ "_key" => "node4", "Name" => "Destination node" ] );' . "\n" );
$node_dest = ArangoDocument::createFromArray( [ "_key" => "node4", "Name" => "Destination node" ] );
echo( '$predicate = ArangoEdge::createFromArray( [ "_key" => "pred2", "Name" => "Predicate" ] );' . "\n" );
$predicate = ArangoEdge::createFromArray( [ "_key" => "pred2", "Name" => "Predicate" ] );

echo( "\n" );

//
// Save vertices.
//
//echo( '$handle_in = $graphHandler->saveVertex( $graph, $node_source );' . "\n" );
//$handle_in = $graphHandler->saveVertex( $graph, $node_source );
//var_dump( $handle_in );
//echo( '$handle_out = $graphHandler->saveVertex( $graph, $node_dest );' . "\n" );
//$handle_out = $graphHandler->saveVertex( $graph, $node_dest );
//var_dump( $handle_out );

//
// Save vertices.
//
echo( "Save vertices:\n" );
echo( '$result = $documentHandler->save( $nodes_collection->getName(), $node_source );' . "\n" );
$result = $documentHandler->save( $nodes_collection->getName(), $node_source );
echo( '$handle_in = $node_source->getHandle();' . "\n" );
$handle_in = $node_source->getHandle();
var_dump( $handle_in );
echo( '$result = $documentHandler->save( $nodes_collection->getName(), $node_dest );' . "\n" );
$result = $documentHandler->save( $nodes_collection->getName(), $node_dest );
echo( '$handle_out = $node_dest->getHandle();' . "\n" );
$handle_out = $node_dest->getHandle();
var_dump( $handle_out );

echo( "\n" );

//
// Add predicate.
//
echo( "Add predicate:\n" );
echo( '$edge_id = $graphHandler->saveEdge( $graph, $handle_in, $handle_out, "another label", $predicate );' . "\n" );
$edge_id = $graphHandler->saveEdge( $graph, $handle_in, $handle_out, "another label", $predicate );
var_dump( $edge_id );

echo( "\n" );

//
// Drop graph.
//
echo( "Drop graph:\n" );
echo( '$graphHandler->dropGraph( $graph, FALSE );' . "\n" );
$graphHandler->dropGraph( $graph, FALSE );

echo( "\n====================================================================================\n\n" );

//
// Create graph connections.
//
echo( "Create graph connections:\n" );
echo( '$graphHandler = new ArangoGraphHandler($connection);' . "\n" );
$graphHandler = new ArangoGraphHandler($connection);
echo( '$graph = new ArangoGraph( "Graph2" );' . "\n" );
$graph = new ArangoGraph( "Graph2" );
echo( '$graph->addEdgeDefinition( ArangoEdgeDefinition::createUndirectedRelation( "test_predicates", [ "test_nodes" ] ) );' . "\n" );
$graph->addEdgeDefinition( ArangoEdgeDefinition::createUndirectedRelation( "test_predicates", [ "test_nodes" ] ) );

echo( "\n" );

//
// Create graph.
//
echo( "Create graph:\n" );
echo( 'try{ $graphHandler->dropGraph($graph); }' . "\n" );
try{ $graphHandler->dropGraph($graph); }
catch( \Exception $error ){}
echo( '$graphHandler->createGraph($graph);' . "\n" );
$graphHandler->createGraph($graph);

echo( "\n====================================================================================\n\n" );

//
// Drop predicate and vertices.
//
echo( "Drop predicate and vertices:\n" );
echo( '$graphHandler->removeEdge( $graph, $edge_id );' . "\n" );
$graphHandler->removeEdge( $graph, $edge_id );
echo( '$graphHandler->removeVertex( $graph, $handle_in );' . "\n" );
$graphHandler->removeVertex( $graph, $handle_in );
echo( '$graphHandler->removeVertex( $graph, $handle_out );' . "\n" );
$graphHandler->removeVertex( $graph, $handle_out );

echo( "\n" );

//
// Drop graph.
//
echo( "Drop graph:\n" );
echo( '$graphHandler->dropGraph( $graph, TRUE );' . "\n" );
$graphHandler->dropGraph( $graph, TRUE );


?>

