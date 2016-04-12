<?php

/**
 * Database.php
 *
 * This file contains the definition of the {@link Database} class.
 */

namespace Milko\PHPLib\ArangoDB;

/*=======================================================================================
 *																						*
 *									Database.php										*
 *																						*
 *======================================================================================*/

use Milko\PHPLib\Database;
use Milko\PHPLib\ArangoDB\Server;

use triagens\ArangoDb\Database as ArangoDatabase;
use triagens\ArangoDb\Collection as ArangoCollection;
use triagens\ArangoDb\CollectionHandler as ArangoCollectionHandler;
use triagens\ArangoDb\Connection as ArangoConnection;

/**
 * <h4>ArangoDB database object.</h4>
 *
 * This <em>concrete</em> class is the implementation of a ArangoDB database, it implements
 * the inherited virtual interface to provide an object that can manage ArangoDB databases
 * and collections.
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		21/02/2016
 *
 *	@example	../../test/ArangoDatabase.php
 *	@example
 * $server = new Milko\PHPLib\DataServer( 'tcp://localhost:8529/database/collection' );<br/>
 * $database = $server->RetrieveDatabase( "database" );<br/>
 * // Work with that database...<br/>
 */
class Database extends \Milko\PHPLib\Database
{



/*=======================================================================================
 *																						*
 *							PUBLIC DATABASE MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Drop																			*
	 *==================================================================================*/

	/**
	 * <h4>Drop the current database.</h4>
	 *
	 * We overload this method to use the {@link triagens\ArangoDb\Database::delete()}
	 * method.
	 *
	 * The options parameter is ignored here.
	 *
	 * @param mixed					$theOptions			Database native options.
	 *
	 * @uses databaseName()
	 * @uses Server::isConnected()
	 * @uses triagens\ArangoDb\Database::delete()
	 */
	public function Drop( $theOptions = NULL )
	{
		//
		// Assert connection.
		//
		$this->mServer->isConnected( Server::kFLAG_CONNECT );

		ArangoDatabase::delete( $this->mServer->Connection(), $this->databaseName() );

	} // Drop.



/*=======================================================================================
 *																						*
 *						PROTECTED DATABASE MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	databaseCreate																	*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database object.</h4>
	 *
	 * We overload this method to instantiate a native object, we first create a server
	 * connection using the parent data source options, then, if the database does not
	 * exist we create it, we then add the database to the newly created server connection
	 * and return it.
	 *
	 * The options parameter is ignored here.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param array					$theOptions			Native driver options.
	 * @return mixed				Native database object.
	 *
	 * @uses Server::ListDatabases()
	 * @uses triagens\ArangoDb\Database::create()
	 * @uses triagens\ArangoDb\Connection::setDatabase()
	 */
	protected function databaseCreate( $theDatabase, $theOptions = NULL )
	{
		//
		// Get server connection options.
		//
		$connection = new ArangoConnection( $this->mServer->GetOptions() );

		//
		// Create database.
		//
		if( ! in_array( $theDatabase, $this->mServer->ListDatabases() ) )
			ArangoDatabase::create( $connection, $theDatabase );

		//
		// Add database to connection.
		//
		$connection->setDatabase( $theDatabase );

		return $connection;															// ==>

	} // databaseCreate.


	/*===================================================================================
	 *	databaseName																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the database name.</h4>
	 *
	 * We overload this method to use the native database method.
	 *
	 * The options parameter is ignored here.
	 *
	 * @param array					$theOptions			Native driver options.
	 * @return string				The database name.
	 *
	 * @uses triagens\ArangoDb\Database::getInfo()
	 */
	protected function databaseName( $theOptions = NULL )
	{
		return
			ArangoDatabase::getInfo( $this->mConnection )
				[ 'result' ][ 'name' ];												// ==>

	} // databaseName.



/*=======================================================================================
 *																						*
 *						PROTECTED COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	collectionCreate																*
	 *==================================================================================*/

	/**
	 * <h4>Create collection.</h4>
	 *
	 * We overload this method to instantiate the correct version of the {@link Collection}
	 * class according to the {@link kTOKEN_OPT_COLLECTION_TYPE} options parameter.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Native driver options.
	 * @return Collection			Collection object.
	 * @throws \InvalidArgumentException
	 */
	protected function collectionCreate( $theCollection, array $theOptions )
	{
		//
		// Parse collection type.
		//
		switch( $tmp = $theOptions[ kTOKEN_OPT_COLLECTION_TYPE ] )
		{
			//
			// Documents collection.
			//
			case kTOKEN_OPT_COLLECTION_TYPE_DOC:
				unset( $theOptions[ kTOKEN_OPT_COLLECTION_TYPE ] );
				$theOptions[ "type" ] = ArangoCollection::TYPE_DOCUMENT;
				return new Collection( $this, $theCollection, $theOptions );		// ==>

			//
			// Edges collection.
			//
			case kTOKEN_OPT_COLLECTION_TYPE_EDGE:
				unset( $theOptions[ kTOKEN_OPT_COLLECTION_TYPE ] );
				$theOptions[ "type" ] = ArangoCollection::TYPE_EDGE;
				return new Edges( $this, $theCollection, $theOptions );				// ==>
		}

		throw new \InvalidArgumentException (
			"Invalid collection type [$tmp]." );								// !@! ==>

	} // collectionCreate.


	/*===================================================================================
	 *	collectionList																	*
	 *==================================================================================*/

	/**
	 * <h4>List database collections.</h4>
	 *
	 * This method should return the list of database collection names.
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * The provided parameter represents a set of native options provided to the driver for
	 * performing the operation.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theOptions			Native driver options.
	 * @return array				List of database collection names.
	 *
	 * @uses triagens\ArangoDb\CollectionHandler::getAllCollections()
	 */
	protected function collectionList( array $theOptions )
	{
		//
		// Instantiate collection handler.
		//
		$handler = new ArangoCollectionHandler( $this->mConnection );

		return
			array_keys(
				$handler->getAllCollections(
					[ 'excludeSystem' => TRUE, 'keys' => 'names' ] ) );				// ==>

	} // collectionList.


} // class Database.


?>
