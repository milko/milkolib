<?php

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
	ArangoConnectionOptions::OPTION_DATABASE        => '_system',               // database name

	// normal unencrypted connection via TCP/IP
	ArangoConnectionOptions::OPTION_ENDPOINT        => 'tcp://localhost:8529',  // endpoint to connect to

	// // connection via SSL
	// ArangoConnectionOptions::OPTION_ENDPOINT        => 'ssl://localhost:8529',  // SSL endpoint to connect to
	// ArangoConnectionOptions::OPTION_VERIFY_CERT     => false,                   // SSL certificate validation
	// ArangoConnectionOptions::OPTION_ALLOW_SELF_SIGNED => true,                  // allow self-signed certificates
	// ArangoConnectionOptions::OPTION_CIPHERS         => 'DEFAULT',               // https://www.openssl.org/docs/manmaster/apps/ciphers.html

	// // connection via UNIX domain socket
	// ArangoConnectionOptions::OPTION_ENDPOINT        => 'unix:///tmp/arangodb.sock',  // UNIX domain socket

	ArangoConnectionOptions::OPTION_CONNECTION      => 'Keep-Alive',            // can use either 'Close' (one-time connections) or 'Keep-Alive' (re-used connections)
	ArangoConnectionOptions::OPTION_AUTH_TYPE       => 'Basic',                 // use basic authorization

	// authentication parameters (note: must also start server with option `--server.disable-authentication false`)
	// ArangoConnectionOptions::OPTION_AUTH_USER       => '',                      // user for basic authorization
	// ArangoConnectionOptions::OPTION_AUTH_PASSWD     => '',                      // password for basic authorization

	ArangoConnectionOptions::OPTION_TIMEOUT         => 30,                      // timeout in seconds
	// ArangoConnectionOptions::OPTION_TRACE           => $traceFunc,              // tracer function, can be used for debugging
	ArangoConnectionOptions::OPTION_CREATE          => false,                   // do not create unknown collections automatically
	ArangoConnectionOptions::OPTION_UPDATE_POLICY   => ArangoUpdatePolicy::LAST,	// last update wins
);

//
// My tests.
//
//$url =
//	'tcp://root:password@127.0.0.1:8529/_system?' .
//	ArangoConnectionOptions::OPTION_AUTH_TYPE . '=Basic' . '&' .
//	ArangoConnectionOptions::OPTION_CONNECTION . '=Keep-Alive' . '&' .
//	ArangoConnectionOptions::OPTION_TIMEOUT . '=3' . '&' .
//	ArangoConnectionOptions::OPTION_RECONNECT . '=true' . '&' .
//	ArangoConnectionOptions::OPTION_CREATE . '=true' . '&' .
//	ArangoConnectionOptions::OPTION_UPDATE_POLICY . '=' . ArangoUpdatePolicy::LAST;
//exit( "$url\n" );

//
// Prepare options.
//
unset( $connectionOptions[ ArangoConnectionOptions::OPTION_DATABASE ] );
unset( $connectionOptions[ ArangoConnectionOptions::OPTION_AUTH_PASSWD ] );

//
// Check endpoints.
//
echo( "Check endpoints:\n" );
$uri_tcp = 'tcp://127.0.0.1:8529';
echo( "$uri_tcp ==> " );
var_dump( ArangoEndpoint::isValid( $uri_tcp ) );
$uri_sock = 'unix:///tmp/arangodb.sock';
echo( "$uri_sock ==> " );
var_dump( ArangoEndpoint::isValid( $uri_sock ) );
echo( "\n" );

//
// Set endpoint.
//
$uri = $uri_tcp;
$connectionOptions[ ArangoConnectionOptions::OPTION_ENDPOINT ] = $uri;
//$connectionOptions[ ArangoConnectionOptions::OPTION_ENDPOINT ] = $uri_sock;

//
// Show connection options.
//
echo( "Connection options:\n" );
print_r( $connectionOptions );
echo( "\n" );

//
// Disable default database.
//
//echo( "Disabled default database:\n" );
//unset( $connectionOptions[ ArangoConnectionOptions::OPTION_DATABASE ] );
//print_r( $connectionOptions );
//echo( "\n" );

//
// Create connection.
//
echo( "Create server connection:\n" );
$connection = new ArangoConnection($connectionOptions);
echo( "\n" );

//
// Get connection information.
//
echo( "Get connection information:\n" );
$list = ArangoDatabase::getInfo( $connection );
print_r( $list );
echo( "\n" );

//
// Get connection endpoints.
//
echo( "Get connection endpoints:\n" );
$list = ArangoEndpoint::listEndpoints( $connection );
print_r( $list );
echo( "\n" );

//
// List databases.
//
echo( "List databases:\n" );
$list = ArangoDatabase::listDatabases( $connection );
print_r( $list );
echo( "\n" );

//
// List user databases.
//
echo( "User List databases:\n" );
$list = ArangoDatabase::listUserDatabases( $connection );
print_r( $list );
echo( "\n" );

echo( "\n====================================================================================\n\n" );

//
// Get collection handler.
//
echo( "Get collection handler\n" );
$collectionHandler = new ArangoCollectionHandler( $connection );
echo( "\n" );

//
// Get collections list.
//
echo( "Get collections list:\n" );
$list = $collectionHandler->getAllCollections();
print_r( $list );
echo( "\n" );

//
// Get non system collections list.
//
echo( "Get non system collections list:\n" );
$list = $collectionHandler->getAllCollections( ['excludeSystem' => TRUE] );
print_r( $list );
echo( "\n" );

echo( "\n====================================================================================\n\n" );

//
// Set test database.
//
echo( "Set test database:\n" );
if( ! in_array( 'test_database', ArangoDatabase::listDatabases( $connection )[ 'result' ] ) ) {
	$result = ArangoDatabase::create( $connection, 'test_database' );
	print_r( $result );
}
$connection->setDatabase( 'test_database' );
$list = ArangoDatabase::getInfo( $connection );
print_r( $list );
echo( "\n" );

echo( "\n====================================================================================\n\n" );

//
// Check for collection.
//
echo( "Check for collection:\n" );
echo( 'test_collection ==> ' );
$found = $collectionHandler->has( 'test_collection' );
var_dump( $found );
if( $found )
{
	echo( "Found:" );
	$collection = $collectionHandler->get( 'test_collection' );
	print_r( $collection );
}
echo( "\n" );

//
// Drop collection.
//
if( ! $found )
{
	echo( "Create collection:\n" );
	$collection = $collectionHandler->create( 'test_collection' );
	print_r( $collection );
}
echo( "\n" );

//
// Get collection info.
//
echo( "Get collection info:\n" );
$collection = $collectionHandler->get( 'test_collection' );
print_r( $collection );
echo( "\n" );

//
// Get collections list.
//
echo( "Get collections list:\n" );
$list = $collectionHandler->getAllCollections();
print_r( $list );
echo( "\n" );

//
// Get non system collections list.
//
echo( "Get non system collections list:\n" );
$list = $collectionHandler->getAllCollections( ['excludeSystem' => TRUE] );
print_r( $list );
echo( "\n" );

//
// Get collection ID.
//
echo( "Get collection ID:\n" );
$result = $collection->getId();
var_dump( $result );
echo( "\n" );

//
// Get collection name.
//
echo( "Get collection name:\n" );
$result = $collection->getName();
var_dump( $result );
echo( "\n" );

//
// Get collection type.
//
echo( "Get collection type:\n" );
$result = $collection->getType();
var_dump( $result );
echo( "\n" );

//
// Truncate collection.
//
echo( "Truncate collection:\n" );
$collectionHandler = new ArangoCollectionHandler( $connection );
$collectionHandler->truncate( $collection );
echo( "\n" );

echo( "\n====================================================================================\n\n" );

//
// Create a document handler.
//
echo( "Create a document handler:\n" );
$documentHandler = new ArangoDocumentHandler( $connection );
echo( "\n" );

//
// Create a document.
//
echo( "Create a document:\n" );
$data = [ "date" => 19570728, "name" => "Milko", "surname" => "Škofič" ];
print_r( $data );
$document = ArangoDocument::createFromArray( $data );
print_r( $document );
echo( "\n" );

//
// Add a document without key.
//
echo( "Add a document without key:\n" );
$id = $documentHandler->save( $collection, $document );
echo( "Document: " );
print_r( $document );
echo( "ID: " );
var_dump( $id );
echo( "getId: " );
var_dump( $document->getId() );
echo( "getInternalId: " );
var_dump( $x = $document->getInternalId() );
echo( "getKey: " );
var_dump( $document->getKey() );
echo( "getInternalKey: " );
var_dump( $document->getInternalKey() );
echo( "getRevision: " );
var_dump( $revision = $document->getRevision() );
echo( "getAll: " );
print_r( $document->getAll() );
echo( "\n" );

//
// Set key and revision.
//
echo( "Set key and revision:\n" );
echo( '$document->setInternalKey( $id );' . "\n" );
$document->setInternalKey( $id );
echo( "getKey: " );
var_dump( $document->getKey() );
echo( '$document->setRevision( $revision );' . "\n" );
$document->setRevision( $revision );
echo( "getRevision: " );
var_dump( $document->getRevision() );
echo( "\n" );
exit;

//
// Add a document with key.
//
echo( "Add a document with key:\n" );
echo( '$document = ArangoDocument::createFromArray( [ \"_key\" => \"pippo\", \"data\" => \"The data\" ] );' . "\n" );
$document = ArangoDocument::createFromArray( [ "_key" => "pippo", "data" => "The data" ] );
$id = $documentHandler->save( $collection, $document );
echo( "Document: " );
print_r( $document );
echo( "ID: " );
var_dump( $id );
echo( "getId: " );
var_dump( $document->getId() );
echo( "getInternalId: " );
var_dump( $x = $document->getInternalId() );
echo( "getKey: " );
var_dump( $document->getKey() );
echo( "getInternalKey: " );
var_dump( $document->getInternalKey() );
echo( "getRevision: " );
var_dump( $document->getRevision() );
echo( "\n" );
exit;

//
// Create a document with key and revision.
//
echo( "Create a document with key and revision:\n" );
echo( '$document = ArangoDocument::createFromArray( [ \"_key\" => \"pippo\", \"_rev\" => $document->getRevision(), \"data\" => \"The data\" ] );' . "\n" );
$document = ArangoDocument::createFromArray( [ "_key" => "pippo", "_rev" => $document->getRevision(), "data" => "The data" ] );
echo( "Document: " );
print_r( $document );
echo( "ID: " );
var_dump( $id );
echo( "getId: " );
var_dump( $document->getId() );
echo( "getInternalId: " );
var_dump( $x = $document->getInternalId() );
echo( "getKey: " );
var_dump( $document->getKey() );
echo( "getInternalKey: " );
var_dump( $document->getInternalKey() );
echo( "getRevision: " );
var_dump( $document->getRevision() );
echo( "getAll: " );
print_r( $document->getAll() );
echo( "\n" );
//
// Get the document.
//
echo( "Get the document by ID:\n" );
$document = $documentHandler->getById($collection->getId(), $id );
echo( "Document: " );
print_r( $document );
echo( "ID: " );
var_dump( $id );
echo( "getId: " );
var_dump( $document->getId() );
echo( "getInternalId: " );
var_dump( $document->getInternalId() );
echo( "getKey: " );
var_dump( $document->getKey() );
echo( "getInternalKey: " );
var_dump( $document->getInternalKey() );
echo( "\n" );

//
// List all documents.
//
echo( "List all documents:\n" );
$result = $collectionHandler->all( $collection->getId() );
print_r( $result->getAll() );
echo( "\n" );

//
// Get by ID.
//
echo( "Get by ID:\n" );
$result = $documentHandler->getById( $collection->getId(), $id );
print_r( $result );
echo( "\n" );

//
// Get by example.
//
echo( "Get by example (surname=:Škofič)\n" );
$result = $collectionHandler->byExample( $collection->getId(), ["surname" => "Škofič"] );
$full_count = $result->getCount();
$result = $collectionHandler->byExample( $collection->getId(), ["surname" => "Škofič"], ["skip" => 0, "limit" => 10] );
echo( "Count: " . $result->getCount() . "\n" );
echo( "Full count: " . $full_count/*$result->getFullCount()*/ . "\n" );
echo( "Result:" );
print_r( $result->getAll() );
echo( "\n" );

exit;																				// ==>


// turn on exception logging (logs to whatever PHP is configured)
//ArangoException::enableLogging();

try {
	$connection = new ArangoConnection($connectionOptions);

	$collectionHandler = new ArangoCollectionHandler($connection);

	// clean up first
	echo( ($collectionHandler->has('users')) ? "Dropped users\n" : "Users doesn't exist\n" );
	if ($collectionHandler->has('users')) {
		$collectionHandler->drop('users');
	}
	echo( ($collectionHandler->has('example')) ? "Dropped example\n" : "Examples doesn't exist\n" );
	if ($collectionHandler->has('example')) {
		$collectionHandler->drop('example');
	}

	// create a new collection
	$userCollection = new ArangoCollection();
	$userCollection->setName('users');
	$id = $collectionHandler->add($userCollection);

	// print the collection id created by the server
	echo( "Users collection ID: " );
	var_dump($id);

	// check if the collection exists
	$result = $collectionHandler->has('users');
	echo( "Has users: " );
	var_dump($result);

	$handler = new ArangoDocumentHandler($connection);

	// create a new document
	$user = new ArangoDocument();

	// use set method to set document properties
	$user->set("name", "John");
	$user->set("age", 25);

	// use magic methods to set document properties
	$user->likes = array('fishing', 'hiking', 'swimming');

	// send the document to the server
	$id = $handler->add('users', $user);

	// check if a document exists
	$result = $handler->has("users", $id);
	var_dump($result);

	// print the document id created by the server
	var_dump($id);
	var_dump($user->getId());


	// get the document back from the server
	$userFromServer = $handler->get('users', $id);
	var_dump($userFromServer);

	// get a document list back from the server, using a document example
	$cursor = $handler->getByExample('users', array('name' => 'John'));
	var_dump($cursor->getAll());


	// update a document
	$userFromServer->likes = array('fishing', 'swimming');
	$userFromServer->state = 'CA';
	unset($userFromServer->age);

	$result = $handler->update($userFromServer);
	var_dump($result);

	// get the document back from the server
	$userFromServer = $handler->get('users', $id);
	var_dump($userFromServer);


	// remove a document on the server
	$result = $handler->remove($userFromServer);
	var_dump($result);


	// create a statement to insert 1000 test users
	$statement = new ArangoStatement($connection, array(
		'query' => 'FOR i IN 1..1000 INSERT { _key: CONCAT("test", i) } IN users'
	));

	// execute the statement
	$cursor = $statement->execute();


	// now run another query on the data, using bind parameters
	$statement = new ArangoStatement($connection, array(
		'query' => 'FOR u IN @@collection FILTER u.name == @name RETURN u',
		'bindVars' => array(
			'@collection' => 'users',
			'name' => 'John'
		)
	));

	// executing the statement returns a cursor
	$cursor = $statement->execute();

	// easiest way to get all results returned by the cursor
	var_dump($cursor->getAll());

	// to get statistics for the query, use Cursor::getExtra();
	var_dump($cursor->getExtra());


	// creates an export object for collection users
	$export = new ArangoExport($connection, 'users', array());

	// execute the export. this will return a special, forward-only cursor
	$cursor = $export->execute();

	// now we can fetch the documents from the collection in blocks
	while ($docs = $cursor->getNextBatch()) {
		// do something with $docs
		var_dump($docs);
	}

	// the export can also be restricted to just a few attributes per document:
	$export = new ArangoExport($connection, 'users', array(
		'_flat' => true,
		'restrict' => array(
			'type' => "include",
			'fields' => array("_key", "likes")
		)
	));

	// now fetch just the configured attributes for each document
	while ($docs = $cursor->getNextBatch()) {
		// do something with $docs
		var_dump($docs);
	}


	$exampleCollection = new ArangoCollection();
	$exampleCollection->setName('example');
	$id = $collectionHandler->add($exampleCollection);

	// create a statement to insert 100 example documents
	$statement = new ArangoStatement($connection, array(
		'query' => 'FOR i IN 1..100 INSERT { _key: CONCAT("example", i), value: i } IN example'
	));
	$statement->execute();

	// later on, we can assemble a list of document keys
	$keys = array();
	for ($i = 1; $i <= 100; ++$i) {
		$keys[] = 'example' . $i;
	}
	// and fetch all the documents at once
	$documents = $collectionHandler->lookupByKeys('example', $keys);
	var_dump($documents);

	// we can also bulk-remove them:
	$result = $collectionHandler->removeByKeys('example', $keys);

	var_dump($result);


	// drop a collection on the server, using its name,
	$result = $collectionHandler->drop('users');
	var_dump($result);

	// drop the other one we created, too
	$collectionHandler->drop('example');
}
catch (ArangoConnectException $e) {
	print 'Connection error: ' . $e->getMessage() . PHP_EOL;
}
catch (ArangoClientException $e) {
	print 'Client error: ' . $e->getMessage() . PHP_EOL;
}
catch (ArangoServerException $e) {
	print 'Server error: ' . $e->getServerCode() . ': ' . $e->getServerMessage() . ' - ' . $e->getMessage() . PHP_EOL;
}