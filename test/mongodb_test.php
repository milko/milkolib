<?php

//
// MongoDB tests.
//

//
// Include local definitions.
//
require_once(dirname(__DIR__) . "/includes.local.php");

//
// Include composer autoconf.
//
require_once( dirname( __DIR__ ) . "/vendor/autoload.php" );

/*===================================================================================
 *	My tests																		*
 *==================================================================================*/

$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->selectDatabase( 'test_milkolib' );
$database->drop();
echo( "Database: " . $database->getDatabaseName() . "\n" );
$collection = $database->selectCollection( 'test_collection' );
echo( "Collection: " . $collection->getCollectionName() . "\n" );

echo( "\n*===================================================================================\n" );

$result = $collection->insertOne( [ "_id" => "ID01", "data" => "This is the first data" ] );
var_dump( $result->getInsertedId() );

$result = $collection->insertOne( [ "data" => "This is the second data" ] );
var_dump( $result->getInsertedId() );

echo( "\n*===================================================================================\n" );

$result = $collection->insertMany( [
	[ "_id" => "pippo", "data" => "Stuff" ],
	[ "property" => 25 ],
	[ "baba" => "bibi" ],
	[ "key" => "XXX", "binary" => new MongoDB\BSON\Binary( 0xFFFFAAAA, MongoDB\BSON\Binary::TYPE_GENERIC ) ],
	[ "key" => "YYY", "binary" => new MongoDB\BSON\Binary( 'pippo', MongoDB\BSON\Binary::TYPE_GENERIC ) ]] );
var_dump( $result->getInsertedIds() );

echo( "\n*===================================================================================\n" );

$result = $collection->find();
foreach( $result as $record )
{
	echo( "\n=========================================\n" );
	var_dump( $record[ '_id' ] );
	var_dump( $record );
}

echo( "\n*===================================================================================\n" );

$result = $collection->distinct( "data" );
echo( gettype( $result ) . "\n" );
foreach( $result as $record )
{
	var_dump( $record );
}
exit;

echo( "\n*===================================================================================\n" );

$result = (array) $collection->find()->toArray()[ 5 ]->bsonSerialize();
print_r( $result );
var_dump( $result[ 'binary' ] );
var_dump( (int) $result[ 'binary' ]->getData() );
var_dump( dechex( (int) $result[ 'binary' ]->getData() ) );

echo( "\n*===================================================================================\n" );

$result = (array) $collection->find()->toArray()[ 6 ]->bsonSerialize();
var_dump( $result[ 'binary' ] );
var_dump( $result[ 'binary' ]->getData() );

echo( "\n*===================================================================================\n" );

$result = (array) $collection->find( ['_id' => "UNKNOWN"] );
var_dump( $result );
$result = $collection->findOne( ['_id' => "UNKNOWN"] );
var_dump( $result );
$result = $collection->findOne( ["_id" => "pippo"] );
var_dump( $result );
var_dump( $result[ '_id' ] );
exit;
$result = $result->bsonSerialize();
var_dump( $result );
$result = (array) $result;
var_dump( $result );
exit;

/*===================================================================================
 *	Driver example																	*
 *==================================================================================*/

$manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
$collection = new MongoDB\Collection($manager, "phplib_demo", "write");

$hannes = [
	"name"    => "Hannes",
	"nick"    => "bjori",
	"citizen" => "Iceland",
];
$hayley = [
	"name"    => "Bayley",
	"nick"    => "Ninja",
	"citizen" => "USA",
];
$bobby = [
	"name" => "Robert Fischer",
	"nick" => "Bobby Fischer",
	"citizen" => "USA",
];
$kasparov = [
	"name"    => "Garry Kimovich Kasparov",
	"nick"    => "Kasparov",
	"citizen" => "Russia",
];
$spassky = [
	"name"    => "Boris Vasilievich Spassky",
	"nick"    => "Spassky",
	"citizen" => "France",
];


try {
	$result = $collection->insertOne($hannes);
	printf("Inserted _id: %s\n\n", $result->getInsertedId());

	$result = $collection->insertOne($hayley);
	printf("Inserted _id: %s\n\n", $result->getInsertedId());

	$result = $collection->insertOne($bobby);
	printf("Inserted _id: %s\n\n", $result->getInsertedId());

	$count = $collection->count(["nick" => "bjori"]);
	printf("Searching for nick => bjori, should have only one result: %d\n\n", $count);

	$result = $collection->updateOne(
		["citizen" => "USA"],
		['$set' => ["citizen" => "Iceland"]]
	);
	printf("Updated: %s (out of expected 1)\n\n", $result->getModifiedCount());

	$cursor = $collection->find(
		["citizen" => "Iceland"],
		["comment" => "Excellent query"]
	);
	echo "Searching for citizen => Iceland, verify Bayley is now Icelandic\n";
	foreach($cursor as $document) {
		var_dump($document);
	}
	echo "\n";
} catch(Exception $e) {
	printf("Caught exception '%s', on line %d\n", $e->getMessage(), __LINE__);
	exit;
}


try {
	$cursor = $collection->find();
	echo "Find all docs, should be 3, verify 1x USA citizen, 2x Icelandic\n";
	foreach($cursor as $document) {
		var_dump($document);
	}
	echo "\n";

	$result = $collection->distinct("citizen");
	echo "Distinct countries:\n";
	var_dump($result);
	echo "\n";

	echo "aggregate\n";
	$result = $collection->aggregate(
		[
			['$project' => ["name" => 1, "_id" => 0]],
		],
		[ "useCursor" => true, "batchSize" => 2 ]
	);
	printf("Should be 3 different people\n");
	foreach($result as $person) {
		var_dump($person);
	}
	echo "\n";
} catch(Exception $e) {
	printf("Caught exception '%s', on line %d\n", $e->getMessage(), __LINE__);
	exit;
}


try {
	$result = $collection->updateMany(
		["citizen" => "Iceland"],
		['$set' => ["viking" => true]]
	);
	printf("Updated: %d (out of expected 2), verify Icelandic people are vikings\n", $result->getModifiedCount());
	$result = $collection->find();
	foreach($result as $document) {
		var_dump($document);
	}
	echo "\n";
} catch(Exception $e) {
	printf("Caught exception '%s', on line %d\n", $e->getMessage(), __LINE__);
	exit;
}


try {
	$result = $collection->replaceOne(
		["nick" => "Bobby Fischer"],
		["name" => "Magnus Carlsen", "nick" => "unknown", "citizen" => "Norway"]
	);
	printf("Replaced: %d (out of expected 1), verify Bobby has been replaced with Magnus\n", $result->getModifiedCount());
	$result = $collection->find();
	foreach($result as $document) {
		var_dump($document);
	}
	echo "\n";
} catch(Exception $e) {
	printf("Caught exception '%s', on line %d\n", $e->getMessage(), __LINE__);
	exit;
}


try {
	$result = $collection->deleteOne($document);
	printf("Deleted: %d (out of expected 1)\n\n", $result->getDeletedCount());

	$result = $collection->deleteMany(["citizen" => "Iceland"]);
	printf("Deleted: %d (out of expected 2)\n\n", $result->getDeletedCount());
} catch(Exception $e) {
	printf("Caught exception '%s', on line %d\n", $e->getMessage(), __LINE__);
	exit;
}


try {
	echo "FindOneAndReplace\n";
	$result = $collection->findOneAndReplace(
		$spassky,
		$kasparov,
		["upsert" => true]
	);
	echo "Kasparov\n";
	var_dump($result);
	echo "\n";

	echo "Returning the old document where he was Russian\n";
	$result = $collection->findOneAndUpdate(
		$kasparov,
		['$set' => ["citizen" => "Croatia"]]
	);
	var_dump($result);
	echo "\n";

	echo "Deleting him, he isn't Croatian just yet\n";
	$result = $collection->findOneAndDelete(["citizen" => "Croatia"]);
	var_dump($result);
	echo "\n";

	echo "This should be empty\n";
	$result = $collection->find();
	foreach($result as $document) {
		var_dump($document);
	}
	echo "\n";
} catch(Exception $e) {
	printf("Caught exception '%s', on line %d\n", $e->getMessage(), __LINE__);
	exit;
}


try {
	$result = $collection->bulkWrite(
	// Required writes param (an array of operations)
		[
			// Operations identified by single key
			[
				'insertOne' => [
					['x' => 1]
				],
			],
			[
				'updateMany' => [
					['x' => 1],
					['$set' => ['x' => 2]],
				],
			],
			[
				'updateOne' => [
					['x' => 3],
					['$set' => ['x' => 4]],
					// Optional params are still permitted
					['upsert' => true],
				],
			],
			[
				'deleteOne' => [
					['x' => 1],
				],
			],
			[
				'deleteMany' => [
					// Required arguments must still be specified
					[],
				],
			],
		],
		// Optional named params in an associative array
		['ordered' => false]
	);
	printf("insertedCount: %d\n", $result->getInsertedCount());
	printf("matchedCount: %d\n", $result->getMatchedCount());
	printf("modifiedCount: %d\n", $result->getModifiedCount());
	printf("upsertedCount: %d\n", $result->getUpsertedCount());
	printf("deletedCount: %d\n", $result->getDeletedCount());

	foreach ($result->getUpsertedIds() as $index => $id) {
		printf("upsertedId[%d]: %s\n", $index, $id);
	}

} catch(Exception $e) {
	printf("Caught exception '%s', on line %d\n", $e->getMessage(), __LINE__);
	exit;
}

?>

