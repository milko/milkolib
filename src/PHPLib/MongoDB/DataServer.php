<?php

/**
 * DataServer.php
 *
 * This file contains the definition of the {@link DataServer} class.
 */

namespace Milko\PHPLib\MongoDB;

use \MongoDB\Client;
use \MongoDB\Driver\ReadConcern;
use \MongoDB\Driver\ReadPreference;
use \MongoDB\Driver\WriteConcern;

use \Milko\PHPLib\Server;
use \Milko\PHPLib\Database;

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
 *								PROTECTED CONNECTION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	connectionCreate																*
	 *==================================================================================*/

	/**
	 * Open connection.
	 *
	 * This method should create the actual connection and return the native connection
	 * object; in this class the method is virtual, it is the responsibility of concrete
	 * classes to implement this method.
	 *
	 * This method assumes the caller has checked whether the connection was already open,
	 * it should not take care of closing previously opened connections.
	 *
	 * All the options required for the connection should have been provided via the data
	 * source connection query parameters.
	 *
	 * If the operation fails, the method should raise an exception.
	 *
	 * @return mixed				The native connection.
	 */
	protected function connectionCreate()
	{

	} // connectionCreate.


	/*===================================================================================
	 *	connectionDestruct																*
	 *==================================================================================*/

	/**
	 * Close connection.
	 *
	 * This method should close the open connection, in this class the method is virtual, it
	 * is the responsibility of concrete classes to implement this method.
	 *
	 * This method assumes the caller has checked whether a connection is open, it should
	 * assume the {@link $mConnection} attribute holds a valid native connection object.
	 *
	 * All the options required for the connection should have been provided via the data
	 * source connection query parameters.
	 *
	 * If the operation fails, the method should raise an exception.
	 */
	protected function connectionDestruct()
	{

	} // connectionDestruct.



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
	 * This method should return the list of server database names.
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @return array				List of database names.
	 */
	protected function databaseList()
	{

	} // databaseList.


	/*===================================================================================
	 *	databaseCreate																	*
	 *==================================================================================*/

	/**
	 * <h4>Create database.</h4>
	 *
	 * This method should create and return a {@link Database} object corresponding to the
	 * provided name, if the operation fails, the method should raise an exception.
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * The method should not be concerned if the database already exists, it is the
	 * responsibility of the caller to check it.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param mixed					$theOptions			Database native options.
	 * @return Database				Database object.
	 */
	protected function databaseCreate( $theDatabase, $theOptions )
	{

	} // databaseCreate.


	/*===================================================================================
	 *	databaseRetrieve																*
	 *==================================================================================*/

	/**
	 * <h4>Return a database object.</h4>
	 *
	 * This method should return a {@link Database} object corresponding to the provided
	 * name, or <tt>NULL</tt> if the provided name does not correspond to any database in
	 * the server.
	 *
	 * The method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param mixed					$theOptions			Database native options.
	 * @return Database				Database object or <tt>NULL</tt> if not found.
	 */
	protected function databaseRetrieve( $theDatabase, $theOptions )
	{

	} // databaseRetrieve.


	/*===================================================================================
	 *	databaseDrop																	*
	 *==================================================================================*/

	/**
	 * <h4>Drop a database.</h4>
	 *
	 * This method should drop the provided database.
	 *
	 * The method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param Database				$theDatabase		Database object.
	 * @param mixed					$theOptions			Database native options.
	 */
	protected function databaseDrop( Database $theDatabase, $theOptions )
	{

	} // databaseDrop.



} // class DataServer.


?>
