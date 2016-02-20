<?php

/**
 * DataServer.php
 *
 * This file contains the definition of the ArangoDB {@link DataServer} class.
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
 *									DataServer.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>ArangoDB data server object.</h4>
 *
 * This <em>concrete</em> class is the implementation of a ArangoDB data server, it
 * implements the inherited virtual interface to provide an object that can manage ArangoDB
 * databases, collections and documents.
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		20/02/2016
 *
 *	@example	../../test/ArangoDataServer.php
 *	@example
 * $server = new Milko\PHPLib\DataServer();<br/>
 * $databases = $server->ListDatabases( kFLAG_CONNECT );<br/>
 * $database = $server->RetrieveDatabase( $databases[ 0 ] );<br/>
 * // Work with that database...<br/>
 * $server->DatabaseDrop( $databases[ 0 ] );<br/>
 * // Dropped the database.
 */
class DataServer extends \Milko\PHPLib\DataServer
{



	/*=======================================================================================
	 *																						*
	 *										MAGIC											*
	 *																						*
	 *======================================================================================*/



	/*===================================================================================
	 *	__construct																		*
	 *==================================================================================*/

	/**
	 * <h4>Instantiate class.</h4>
	 *
	 * We override the constructor to provide a default connection URL.
	 *
	 * @param string				$theConnection		Data source name.
	 *
	 * @see kMONGO_OPTS_CLIENT_DEFAULT
	 *
	 * @example
	 * $dsn = new DataSource( 'mongodb://user:pass@host:27017/database/collection' );
	 */
	public function __construct( $theConnection = NULL )
	{
		//
		// Init local storage.
		//
		if( $theConnection === NULL )
			$theConnection = kMONGO_OPTS_CLIENT_DEFAULT;

		//
		// Call parent constructor.
		//
		parent::__construct( $theConnection );

	} // Constructor.



	/*=======================================================================================
	 *																						*
	 *								PROTECTED CONNECTION INTERFACE							*
	 *																						*
	 *======================================================================================*/



	/*===================================================================================
	 *	connectionCreate																*
	 *==================================================================================*/

	/**
	 * Open connection.
	 *
	 * We overload this method to return a ArangoDB client object; we also remove the path
	 * from the data source URL.
	 *
	 * @param mixed					$theOptions			Connection native options.
	 * @return Client				The native connection.
	 *
	 * @uses toURL()
	 *
	@see kMONGO_OPTS_CLIENT_CREATE
	 */
	protected function connectionCreate( $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		$uri_opts = [];
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_CLIENT_CREATE;

		return new Client(
			$this->toURL( [ \Milko\PHPLib\DataSource::PATH ] ),
			$uri_opts,
			$theOptions );															// ==>

	} // connectionCreate.


	/*===================================================================================
	 *	connectionDestruct																*
	 *==================================================================================*/

	/**
	 * Close connection.
	 *
	 * The ArangoDB client does not have a destructor, this method does nothing.
	 */
	protected function connectionDestruct( $theOptions = NULL ) {}



	/*=======================================================================================
	 *																						*
	 *						PROTECTED DATABASE MANAGEMENT INTERFACE							*
	 *																						*
	 *======================================================================================*/



	/*===================================================================================
	 *	databaseList																	*
	 *==================================================================================*/

	/**
	 * <h4>List server databases.</h4>
	 *
	 * In this class we ask the Mongo client for the list of databases and extract their
	 * names.
	 *
	 * @param mixed					$theOptions			Database native options.
	 * @return array				List of database names.
	 *
	 * @uses Connection()
	 * @uses \ArangoDB\Client::listDatabases()
	 *
	 * @see kMONGO_OPTS_CLIENT_DBLIST
	 */
	protected function databaseList( $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		$databases = [];
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_CLIENT_DBLIST;

		//
		// Ask client for list.
		//
		$list = $this->Connection()->listDatabases( $theOptions );
		foreach( $list as $element )
			$databases[] = $element->getName();

		return $databases;															// ==>

	} // databaseList.


	/*===================================================================================
	 *	databaseCreate																	*
	 *==================================================================================*/

	/**
	 * <h4>Create database.</h4>
	 *
	 * In this class we instantiate a {@link Database} object.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param mixed					$theOptions			Database native options.
	 * @return Database				Database object.
	 *
	 * @see kMONGO_OPTS_CLIENT_DBCREATE
	 */
	protected function databaseCreate( $theDatabase, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_CLIENT_DBCREATE;

		return new Database( $this, $theDatabase, $theOptions );					// ==>

	} // databaseCreate.


	/*===================================================================================
	 *	databaseRetrieve																*
	 *==================================================================================*/

	/**
	 * <h4>Return a database object.</h4>
	 *
	 * In this class we first check whether the database exists in the server, if that is
	 * not the case, we return <tt>NULL</tt>.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param mixed					$theOptions			Database native options.
	 * @return Database				Database object or <tt>NULL</tt> if not found.
	 *
	 * @uses databaseList()
	 *
	 * @see kMONGO_OPTS_CLIENT_DBRETRIEVE
	 */
	protected function databaseRetrieve( $theDatabase, $theOptions = NULL )
	{
		//
		// Check if database exists.
		//
		if( in_array( $theDatabase, $this->databaseList() ) )
		{
			//
			// Init local storage.
			//
			if( $theOptions === NULL )
				$theOptions = kMONGO_OPTS_CLIENT_DBRETRIEVE;

			return new Database( $this, $theDatabase, $theOptions );				// ==>

		} // Among server databases.

		return NULL;																// ==>

	} // databaseRetrieve.



} // class DataServer.


?>
