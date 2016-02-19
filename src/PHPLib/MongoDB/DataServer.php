<?php

/**
 * DataServer.php
 *
 * This file contains the definition of the {@link DataServer} class.
 */

namespace Milko\PHPLib\MongoDB;

use \MongoDB\Client;

/*=======================================================================================
 *																						*
 *									DataServer.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>MongoDB data server object.</h4>
 *
 * This <em>concrete</em> class is the implementation of a MongoDB data server, it
 * implements the inherited virtual interface to provide an object that can manage MongoDB
 * databases, collections and documents.
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		18/02/2016
 *
 *	@example	../../test/MongoDataServer.php
 *	@example
 * $server = new Milko\PHPLib\DataServer( 'protocol://user:pass@host:9090' );<br/>
 * $server->Connect();<br/>
 * $databases = $server->ListDatabases();<br/>
 * $database = $server->RetrieveDatabase( $databases[ 0 ], self::kFLAG_CREATE );<br/>
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
	 * @param string			$theConnection		Data source name.
	 *
	 * @example
	 * $dsn = new DataSource( 'html://user:pass@host:8080/dir/file?arg=val#frag' );
	 */
	public function __construct( $theConnection = 'mongodb://localhost:27017' )
	{
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
	 * We overload this method to return a MongoDB client object; we also remove the path
	 * from the data source URL.
	 *
	 * @return Client				The native connection.
	 *
	 * @uses toURL()
	 */
	protected function connectionCreate()
	{
		return new Client( $this->toURL( [ \Milko\PHPLib\DataSource::PATH ] ) );	// ==>

	} // connectionCreate.


	/*===================================================================================
	 *	connectionDestruct																*
	 *==================================================================================*/

	/**
	 * Close connection.
	 *
	 * In this class there is no need to close the connection.
	 */
	protected function connectionDestruct()												   {}



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
		$list = $this->Connection()->listDatabases( $theOptions );
		foreach( $list as $element )
			$databases[] = $element[ 'name' ];

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
	 */
	protected function databaseCreate( $theDatabase, $theOptions )
	{
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
	 */
	protected function databaseRetrieve( $theDatabase, $theOptions )
	{
		//
		// Check if database exists.
		//
		if( in_array( $theDatabase, $this->databaseList() ) )
			return new Database( $this, $theDatabase, $theOptions );				// ==>

		return NULL;																// ==>

	} // databaseRetrieve.



} // class DataServer.


?>
