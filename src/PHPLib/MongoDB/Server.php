<?php

/**
 * Server.php
 *
 * This file contains the definition of the MongoDB {@link DataServer} class.
 */

namespace Milko\PHPLib\MongoDB;

use \MongoDB\Client;

/*=======================================================================================
 *																						*
 *										Server.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>MongoDB data server object.</h4>
 *
 * This <em>concrete</em> class is the implementation of a MongoDB data server, it
 * implements the inherited virtual interface to provide an object that can manage MongoDB
 * databases, collections and documents.
 *
 * This class makes use of the {@link https://github.com/mongodb/mongo-php-library.git} PHP
 * library to communicate with the server.
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		18/02/2016
 */
class Server extends \Milko\PHPLib\Server
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
	 * @param string			$theConnection		Data source name.
	 * @param mixed				$theOptions			Server connection options.
	 *
	 * @example
	 * $dsn = new DataSource( 'mongodb://user:pass@host:27017/database/collection' );
	 */
	public function __construct( $theConnection = NULL, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theConnection === NULL )
			$theConnection = kMONGO_OPTS_CLIENT_DEFAULT;
		
		//
		// Call parent constructor.
		//
		parent::__construct( $theConnection, $theOptions );

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
	 * We overload this method to return a MongoDB client object; we also remove the path
	 * from the data source URL.
	 *
	 * @param array					$theOptions			Connection native options.
	 * @return Client				The native connection.
	 *
	 * @uses toURL()
	 */
	protected function connectionCreate( $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		$uri_opts = [];
		if( $theOptions === NULL )
			$theOptions = [];
		
		return new Client(
			$this->URL( [ \Milko\PHPLib\Datasource::PATH ] ),
			$uri_opts,
			$theOptions );															// ==>

	} // connectionCreate.


	/*===================================================================================
	 *	connectionDestruct																*
	 *==================================================================================*/

	/**
	 * Close connection.
	 *
	 * The MongoDB client does not have a destructor, this method does nothing.
	 */
	protected function connectionDestruct( $theOptions = NULL ) {}



/*=======================================================================================
 *																						*
 *						PROTECTED DATABASE MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	databaseCreate																	*
	 *==================================================================================*/

	/**
	 * <h4>Create database.</h4>
	 *
	 * In this class we instantiate a {@link Database} object.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param array					$theOptions			Database native options.
	 * @return Database				Database object.
	 */
	protected function databaseCreate( $theDatabase, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = [];

		return
			new \Milko\PHPLib\MongoDB\Database(
				$this, $theDatabase, $theOptions );									// ==>

	} // databaseCreate.


	/*===================================================================================
	 *	databaseList																	*
	 *==================================================================================*/

	/**
	 * <h4>List server databases.</h4>
	 *
	 * In this class we ask the Mongo client for the list of databases and extract their
	 * names.
	 *
	 * @param array					$theOptions			Database native options.
	 * @return array				List of database names.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Client::listDatabases()
	 */
	protected function databaseList( $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		$databases = [];
		if( $theOptions === NULL )
			$theOptions = [];

		//
		// Ask client for list.
		//
		$list = $this->mConnection->listDatabases( $theOptions );
		foreach( $list as $element )
			$databases[] = $element->getName();

		return $databases;															// ==>

	} // databaseList.



} // class DataServer.


?>
