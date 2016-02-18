<?php

/**
 * Database object test suite.
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
require_once( dirname( __DIR__ ) . "/includes.local.php" );

//
// Include utility functions.
//
require_once( "functions.php" );

//
// Reference class.
//
use Milko\PHPLib\Database;

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
	protected function databaseName() {
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
// Instantiate data server.
//
echo( "Instantiate data server:\n" );
echo( '$url = "protocol://user:pass@host:9090/db0/col0";' . "\n" );
$url = "protocol://user:pass@host:9090/db0/col0";
echo( '$server = new test_DataServer( $url' . " );\n" );
$server = new test_DataServer( $url );

echo( "\n" );

//
// Instantiate database.
//
echo( "Instantiate database:\n" );
echo( '$db = new test_Database( $server, "db" );' . "\n" );
$db = new test_Database( $server, "db" );
echo( '$name = (string)$db;' . "\n" );
$name = (string)$db;
echo( "$name\n" );
echo( '$list = $server->WorkingDatabases();' . "\n" );
$list = $server->WorkingDatabases();
print_r( $list );

echo( "\n" );

//
// Instantiate database from server.
//
echo( "Instantiate database from server:\n" );
echo( '$db = $server->RetrieveDatabase( "db0" );' . "\n" );
$db = $server->RetrieveDatabase( "db0" );
echo( '$name = (string)$db;' . "\n" );
$name = (string)$db;
echo( "$name\n" );
echo( '$list = $server->WorkingDatabases();' . "\n" );
$list = $server->WorkingDatabases();
print_r( $list );

echo( "\n====================================================================================\n\n" );

//
// Get server.
//
echo( "Get server:\n" );
echo( '$x = $db->Server();' . "\n" );
$x = $db->Server();
print_r( $x );

echo( "\n" );

//
// Get connection.
//
echo( "Get connection:\n" );
echo( '$x = $db->Connection();' . "\n" );
$x = $db->Connection();
var_dump( $x );

echo( "\n" );

//
// List database collections.
//
echo( "List database collections:\n" );
echo( '$list = $db->ListCollections();' . "\n" );
$list = $db->ListCollections();
print_r( $list );

echo( "\n" );

//
// List working collections.
//
echo( "List working collections:\n" );
echo( '$list = $db->WorkingCollections();' . "\n" );
$list = $db->WorkingCollections();
print_r( $list );

echo( "\n" );

//
// Retrieve collection.
//
echo( "Retrieve collection:\n" );
echo( '$col = $db->RetrieveCollection( "col0" );' . "\n" );
$col = $db->RetrieveCollection( "col0" );
print_r( $col );

echo( "\n" );

//
// Create collection.
//
echo( "Create collection:\n" );
echo( '$col = $db->RetrieveCollection( "NewCol" );' . "\n" );
$col = $db->RetrieveCollection( "NewCol" );
echo( '$list = $db->WorkingCollections();' . "\n" );
$list = $db->WorkingCollections();
print_r( $list );

echo( "\n" );

//
// Forget collection.
//
echo( "Forget collection:\n" );
echo( '$col = $db->ForgetCollection( "NewCol" );' . "\n" );
$col = $db->ForgetCollection( "NewCol" );
echo( '$list = $db->WorkingCollections();' . "\n" );
$list = $db->WorkingCollections();
print_r( $list );

echo( "\n" );

//
// Drop collection.
//
echo( "Drop collection:\n" );
echo( '$col = $db->DropCollection( "col0" );' . "\n" );
$col = $db->DropCollection( "col0" );
echo( '$list = $db->WorkingCollections();' . "\n" );
$list = $db->WorkingCollections();
print_r( $list );


?>
