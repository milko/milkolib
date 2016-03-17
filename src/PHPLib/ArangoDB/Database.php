<?php

/**
 * Database.php
 *
 * This file contains the definition of the {@link Database} class.
 */

namespace Milko\PHPLib\ArangoDB;

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

/*=======================================================================================
 *																						*
 *									Database.php										*
 *																						*
 *======================================================================================*/

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
	 * We overload this method to first check if the database exists, if that is the case,
	 * we call the native object's method to delete it.
	 *
	 * The options parameter is ignored here.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param string				$theFlags			Flags bitfield.
	 * @param array					$theOptions			Database native options.
	 * @return boolean				<tt>TRUE</tt> dropped, <tt>FALSE</tt> not found.
	 *
	 * @uses Server()
	 * @uses databaseName()
	 * @uses triagens\ArangoDb\Database::delete()
	 */
	public function Drop( $theOptions = NULL )
	{
		//
		// Check if database exists.
		//
		if( in_array( $this->databaseName(), $this->Server()->ListDatabases() ) )
			ArangoDatabase::delete( $this->Server()->Connection(), $this->databaseName() );

	} // Drop.



/*=======================================================================================
 *																						*
 *						PROTECTED DATABASE MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	databaseNew																		*
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
	 * @return ArangoConnection		Native database object.
	 *
	 * @uses Server()
	 * @uses ArangoDatabase::create()
	 *
	 * @see triagens\ArangoDb\ConnectionOptions::OPTION_DATABASE
	 */
	protected function databaseNew( $theDatabase, $theOptions = NULL )
	{
		//
		// Create connection.
		//
		$connection = new ArangoConnection( $this->Server()->GetOptions() );

		//
		// Create database.
		//
		if( ! in_array( $theDatabase, $this->Server()->ListDatabases() ) )
			ArangoDatabase::create( $connection, $theDatabase );

		//
		// Add database to connection.
		//
		$connection->setDatabase( $theDatabase );

		return $connection;															// ==>

	} // databaseNew.


	/*===================================================================================
	 *	databaseName																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the database name.</h4>
	 *
	 * We overload this method to use the native object.
	 *
	 * The options parameter is ignored here.
	 *
	 * @param array					$theOptions			Native driver options.
	 * @return string				The database name.
	 *
	 * @uses Connection()
	 * @uses triagens\ArangoDb\Database::getInfo()
	 */
	protected function databaseName( $theOptions = NULL )
	{
		return
			ArangoDatabase::getInfo( $this->Connection() )
				[ 'result' ][ 'name' ];												// ==>

	} // databaseName.



/*=======================================================================================
 *																						*
 *						PROTECTED COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	collectionList																	*
	 *==================================================================================*/

	/**
	 * <h4>List server databases.</h4>
	 *
	 * We overload this method to instantiate a collection handler from which we get the
	 * collection names.
	 *
	 * We only consider the non system collection names in the returned value.
	 *
	 * The options parameter is ignored here.
	 *
	 * @param array					$theOptions			Collection native options.
	 * @return array				List of database names.
	 *
	 * @uses Connection()
	 * @uses triagens\ArangoDb\CollectionHandler::getAllCollections()
	 */
	protected function collectionList( $theOptions = NULL )
	{
		//
		// Get collection handler.
		//
		$collectionHandler = new ArangoCollectionHandler( $this->Connection() );

		return array_keys(
			$collectionHandler->getAllCollections( ['excludeSystem' => TRUE] ) );	// ==>

	} // collectionList.


	/*===================================================================================
	 *	collectionCreate																*
	 *==================================================================================*/

	/**
	 * <h4>Create collection.</h4>
	 *
	 * We overload this method to instantiate a ArangoDB version of the {@link Collection}
	 * class.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Collection native options.
	 * @return Collection			Collection object.
	 */
	protected function collectionCreate( $theCollection, $theOptions = NULL )
	{
		return new Collection( $this, $theCollection, $theOptions );				// ==>

	} // collectionCreate.


	/*===================================================================================
	 *	collectionRetrieve																*
	 *==================================================================================*/

	/**
	 * <h4>Return a collection object.</h4>
	 *
	 * We overload this method to check whether the collection exists and to instantiate a
	 * ArangoDB version of the {@link Collection} class.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Collection native options.
	 * @return Collection			Collection object or <tt>NULL</tt> if not found.
	 *
	 * @uses collectionList()
	 */
	protected function collectionRetrieve( $theCollection, $theOptions = NULL )
	{
		//
		// Check if collection exists.
		//
		if( in_array( $theCollection, $this->collectionList() ) )
			return new Collection( $this, $theCollection, $theOptions );			// ==>

		return NULL;																// ==>

	} // collectionRetrieve.


} // class Database.


?>
