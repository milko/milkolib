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
	 * We overload this method to call the native object's method.
	 *
	 * @param array					$theOptions			Native driver options.
	 *
	 * @uses Server()
	 * @uses databaseName()
	 * @uses ArangoDatabase::delete()
	 */
	public function Drop( $theOptions = NULL )
	{
		ArangoDatabase::delete( $this->Server(), $this->databaseName() );

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
	 * We overload this method to instantiate a native object.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param array					$theOptions			Native driver options.
	 * @return ArangoConnection		Native database object.
	 *
	 * @uses Server()
	 * @uses ArangoDatabase::create()
	 *
	 * @see ArangoConnectionOptions::OPTION_DATABASE
	 */
	protected function databaseNew( $theDatabase, $theOptions = NULL )
	{
		//
		// Create database.
		//
		ArangoDatabase::create( $this->Server()->Connection(), $theDatabase );

		//
		// Add database to connection.
		//
		$options = $this->Server()->getConnectionOptions();
		$options[ ArangoConnectionOptions::OPTION_DATABASE ] = $theDatabase;

		return new ArangoConnection( $options );									// ==>

	} // databaseNew.


	/*===================================================================================
	 *	databaseName																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the database name.</h4>
	 *
	 * We overload this method to use the native object.
	 *
	 * @param array					$theOptions			Native driver options.
	 * @return string				The database name.
	 *
	 * @uses Connection()
	 * @uses ArangoDatabase::getInfo()
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
	 * We overload this method to use the native driver object, we only consider the non
	 * system collection names in the returned value.
	 *
	 * @param array					$theOptions			Collection native options.
	 * @return array				List of database names.
	 *
	 * @uses Connection()
	 */
	protected function collectionList( $theOptions = NULL )
	{
		//
		// Get collection handler.
		//
		$collectionHandler = new ArangoCollectionHandler( $this->Connection() );

		return array_keys(
			$collectionHandler->getAllCollections( ['excludeSystem' => TRUE] ) );	// ==>
	}


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
	}


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
	}


} // class Database.


?>
