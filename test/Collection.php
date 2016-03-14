<?php

/**
 * Collection object test suite.
 *
 * This test suite will use a ficticious test class, to perform more in depth test use the
 * concrete classes derived from Database.
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		18/02/2016
 */

//
// Include local definitions.
//
require_once(dirname(__DIR__) . "/includes.local.php");

//
// Include utility functions.
//
require_once( "functions.php" );

//
// Reference class.
//
use Milko\PHPLib\Collection;

//
// DataServer test class.
//
class test_DataServer extends Milko\PHPLib\DataServer
{
	//
	// Implement Server virtual interface.
	//
	protected function connectionCreate()	{	return "I am open!";	}
	protected function connectionDestruct()	{	$this->mConnection = "I am closed.";	}

	//
	// Implement DataServer virtual interface.
	//
	protected function databaseList() {
		return array_merge( ["db1", "db2", "db3" ], $this->WorkingDatabases() );	}
	protected function databaseCreate( $theDatabase, $theOptions ) {
		return new test_Database( $this, (string)$theDatabase, $theOptions );	}
	protected function databaseRetrieve( $theDatabase, $theOptions ) {
		return (string) $theDatabase;	}
	protected function databaseDrop( Milko\PHPLib\Database $theDatabase, $theOptions ) {}
}

//
// Database test class.
//
class test_Database extends Milko\PHPLib\Database
{
	//
	// Implement Database virtual interface.
	//
	protected function newDatabase( $theDatabase, $theOptions )	{
		return (string)$theDatabase;	}
	protected function databaseName() {
		return $this->mNativeObject;	}
	protected function collectionList() {
		return array_merge( ["cl1", "cl2", "cl3" ], $this->WorkingCollections() );	}
	protected function collectionCreate( $theCollection, $theOptions ) {
		return new test_Collection( $this, $theCollection, $theOptions );	}
	protected function collectionRetrieve( $theCollection, $theOptions ) {
		return new test_Collection( $this, $theCollection, $theOptions );	}
	protected function collectionEmpty( Milko\PHPLib\Collection $theCollection, $theOptions ) {}
	protected function collectionDrop( Milko\PHPLib\Collection $theCollection, $theOptions ) {}
}

//
// Collection test class.
//
class test_Collection extends Milko\PHPLib\Collection
{
	//
	// Implement Collection virtual interface.
	//
	protected function newCollection( $theCollection, $theOptions ) {
		return (string)$theCollection;	}
	protected function collectionName() {
		return $this->mNativeObject;	}

	//
	// Declare record management interface,
	// will not be tested here.
	//
	protected function insert( $theRecord, $theOptions, $doMany ) {}
	protected function update( $theCriteria, $theFilter, $theOptions, $doMany ) {}
	protected function replace( $theRecord, $theFilter, $theOptions ) {}
	protected function find( $theFilter, $theOptions, $doMany ) {}
	protected function query( $theQuery, $theOptions ) {}
	protected function delete( $theFilter, $theOptions, $doMany ) {}
}

//
// Instantiate data server, database and collection.
//
echo( "Instantiate data server, database and collection:\n" );
echo( '$url = "protocol://user:pass@host:9090/db0/col0";' . "\n" );
$url = "protocol://user:pass@host:9090/db0/col0";
echo( '$server = new test_DataServer( $url' . " );\n" );
$server = new test_DataServer( $url );
echo( "$server\n" );
echo( '$db = $server->RetrieveDatabase( "db0" );' . "\n" );
$db = $server->RetrieveDatabase( "db0" );
echo( '$name = (string)$db;' . "\n" );

echo( "\n" );

//
// Instantiate collection.
//
echo( "Instantiate collection:\n" );
echo( '$test = new test_Collection( $db, "pippo" );' . "\n" );
$test = new test_Collection( $db, "pippo" );
echo( '$name = (string)$test;' . "\n" );
$name = (string)$test;
echo( "$name\n" );
echo( '$list = $db->WorkingCollections();' . "\n" );
$list = $db->WorkingCollections();
print_r( $list );

echo( "\n" );

//
// Instantiate collection from database.
//
echo( "Instantiate collection from database:\n" );
echo( '$test = $db->RetrieveCollection( "pippo" );' . "\n" );
$test = $db->RetrieveCollection( "pippo" );
echo( '$name = (string)$test;' . "\n" );
$name = (string)$test;
echo( "$name\n" );
echo( '$list = $db->WorkingCollections();' . "\n" );
$list = $db->WorkingCollections();
print_r( $list );

echo( "\n====================================================================================\n\n" );

//
// Get server.
//
echo( "Get server:\n" );
echo( '$x = $test->Server();' . "\n" );
$x = $test->Server();
echo( "$x\n" );

echo( "\n" );

//
// Get database.
//
echo( "Get database:\n" );
echo( '$x = $test->Database();' . "\n" );
$x = $test->Database();
echo( "$x\n" );

echo( "\n" );

//
// Get connection.
//
echo( "Get connection:\n" );
echo( '$x = $test->Connection();' . "\n" );
$x = $test->Connection();
echo( "$x\n" );

echo( "\n" );

echo( "For testing data management try concrete collection classes.\n\n" );


?>
