<?php

/**
 * DataServer.php
 *
 * This file contains the definition of the {@link DataServer} class.
 */

namespace Milko\PHPLib;

use Milko\PHPLib\Server;
use Milko\PHPLib\Database;

/*=======================================================================================
 *																						*
 *									DataServer.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Data server ancestor object.</h4><p />
 *
 * This <em>abstract</em> class is the ancestor of all classes representing data server
 * instances.
 *
 * A data server is essentially a database server, it is divided into <em>databases</em>,
 * which in turn are divided into <em>collections</em> that contain data <em>records</em>.
 *
 * This class uses its inherited {@link Server} class to handle the server connection and
 * implements a public standard interface to handle database operations and resources.
 *
 * The class implements a common public interface that manages {@link Database} objects:
 *
 * <ul>
 * 	<li><b>{@link ListDatabases()}</b>: Return the list of database names on the server.
 * 	<li><b>{@link WorkingDatabases()}</b>: Return the list of working database names.
 * 	<li><b>{@link RetrieveDatabase()}</b>: Create or retrieve a database object.
 * 	<li><b>{@link ForgetDatabase()}</b>: Clear or dispose of a database object.
 * 	<li><b>{@link DropDatabase()}</b>: Drop a database.
 * </ul>
 *
 * Most of the above methods feature a bitfield parameter that determines the actions to
 * take in the event a database is not found or the connection to the server is not open:
 *
 * <ul>
 * 	<li><tt>{@link kFLAG_CONNECT}</tt>: If set, the server connection will be automatically
 * 		opened if necessary.
 * 	<li><tt>{@link kFLAG_CREATE}</tt>: If set and the indicated database was not found, the
 * 		database will be created in the process.
 * 	<li><tt>{@link kFLAG_ASSERT}</tt>: If set, an exception will be raised if the connection
 * 		is not open and the {@link kFLAG_CONNECT} switch is off and if the indicated
 * 		database cannot be found and the {@link kFLAG_CREATE} flag is not set.
 * </ul>
 *
 * The public methods do not implement the actual operations, this is delegated to a
 * protected virtual interface which must be implemented by derived concrete classes:
 *
 * <ul>
 * 	<li><b>{@link databaseList()}</b>: Return the list of server database names.
 * 	<li><b>{@link databaseCreate()}</b>: Create and return a {@link Database} object.
 * 	<li><b>{@link databaseRetrieve()}</b>: Return a {@link Database} object.
 * </ul>
 *
 * Each time a database object is retrieved, the object will store it in the
 * {@link mDatabases} data member for fast subsequent retrievals.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		07/02/2016
 *
 *	@example	../../test/DataServer.php
 *	@example
 * $server = new Milko\PHPLib\DataServer( 'protocol://user:pass@host:9090' );<br/>
 * $server->Connect();<br/>
 * $databases = $server->ListDatabases();<br/>
 * $database = $server->RetrieveDatabase( $databases[ 0 ], self::kFLAG_CREATE );<br/>
 * // Work with that database...<br/>
 * $server->DatabaseDrop( $databases[ 0 ] );<br/>
 * // Dropped the database.
 */
abstract class DataServer extends Server
{
	/**
	 * <h4>Working databases.</h4><p />
	 *
	 * This data member holds the <i>working list of database objects</i>, it is an array
	 * indexed by the database name with the relative {@link Database} objects as value.
	 *
	 * Each time a database is requested this list will be searched first by default.
	 *
	 * @var array
	 */
	protected $mDatabases = [];

	
	
	
/*=======================================================================================
 *																						*
 *										MAGIC											*
 *																						*
 *======================================================================================*/
	
	
	
	/*===================================================================================
	 *	__construct																		*
	 *==================================================================================*/
	
	/**
	 * <h4>Instantiate class.</h4><p />
	 *
	 * We overload the constructor to parse the connection string's path: if it was
	 * provided, the first element of the path will be considered as a database name and
	 * added or created to the working databases list.
	 *
	 * @param string				$theConnection		Data source name.
	 *
	 * @uses Path()
	 * @uses Connect()
	 * @uses databaseCreate()
	 * @uses databaseRetrieve()
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989/database' );
	 */
	public function __construct( $theConnection )
	{
		//
		// Call parent constructor.
		//
		parent::__construct( $theConnection );

		//
		// Handle connection path.
		//
		$path = $this->Path();
		if( $path !== NULL )
		{
			//
			// Parse path.
			// We skip the first path element:
			// that is because the path begins with the separator.
			// Also note that it is the responsibility of the database
			// to instantiate the eventual default collection.
			//
			$parts = explode( '/', $path );
			if( count( $parts ) > 1 )
			{
				//
				// Retrieve or create database.
				//
				$this->Connect();
				$database = $this->databaseRetrieve( $parts[ 1 ] );
				if( $database === NULL )
					$database = $this->databaseCreate( $parts[ 1 ] );

				//
				// Store in working databases list.
				//
				$this->mDatabases[ $parts[ 1 ] ] = $database;

			} // Has at least directory.

		} // Has path.

	} // Constructor.
	
	
	
/*=======================================================================================
 *																						*
 *							PUBLIC DATABASE MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/
	
	
	
	/*===================================================================================
	 *	ListDatabases																	*
	 *==================================================================================*/
	
	/**
	 * <h4>Return the server list of databases.</h4><p />
	 *
	 * This method can be used to retrieve the list of database names present on the server,
	 * the method features the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFlags</b>: A bitfield providing the following options:
	 *	 <ul>
	 * 		<li><tt>{@link kFLAG_CONNECT}</tt>: If set, the server will connect if
	 * 			necessary, if not set and the server is not connected, the method will
	 * 			return an emptyarray.
	 * 		<li><tt>{@link kFLAG_ASSERT}</tt>: If set and the server is not connected, an
	 * 			exception will be raised.
	 * 	 </ul>
	 * </ul>
	 *
	 * @param string				$theFlags			Flags bitfield.
	 * @param array					$theOptions			Database native options.
	 * @return array				List of database names.
	 *
	 * @uses isConnected()
	 * @uses databaseList()
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989' );<br/>
	 * $server->Connect();<br/>
	 * $list = $server->ListDatabases();
	 */
	public function ListDatabases( $theFlags = self::kFLAG_DEFAULT, $theOptions = NULL )
	{
		//
		// Assert connection.
		//
		if( $this->isConnected( $theFlags ) )
			return $this->databaseList( $theOptions );								// ==>
		
		return [];																	// ==>
		
	} // ListDatabases.


	/*===================================================================================
	 *	WorkingDatabases																*
	 *==================================================================================*/

	/**
	 * <h4>Return the working list of databases.</h4><p />
	 *
	 * This method can be used to retrieve the list of working database names.
	 *
	 * @return array				List of working database names.
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989/database' );<br/>
	 * $list = $server->WorkingDatabases();
	 */
	public function WorkingDatabases()			{	return array_keys( $this->mDatabases );	}

	
	/*===================================================================================
	 *	RetrieveDatabase																*
	 *==================================================================================*/
	
	/**
	 * <h4>Return a database object.</h4><p />
	 *
	 * This method can be used to create or retrieve a database object, it features the
	 * following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDatabase</b>: The database name.
	 *	<li><b>$theFlags</b>: A bitfield providing the following options:
	 *	 <ul>
	 * 		<li><tt>{@link kFLAG_CONNECT}</tt>: If set, the server will connect if
	 * 			necessary, if not set and the server is not connected, the method will
	 * 			return <tt>NULL</tt>.
	 * 		<li><tt>{@link kFLAG_CREATE}</tt>: If set, the method will create the database
	 * 			if it doesn't already exist.
	 * 		<li><tt>{@link kFLAG_ASSERT}</tt>: If set, the method will raise an exception if
	 * 			the server is not connected or if the database cannot be found.
	 * 	 </ul>
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * The method will either return a {@link Database} object, or <tt>NULL</tt> if the
	 * server is not connected or if the database was not found.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param string				$theFlags			Flags bitfield.
	 * @param array					$theOptions			Database native options.
	 * @return Database				Database object or <tt>NULL</tt>.
	 * @throws \RuntimeException
	 *
	 * @uses isConnected()
	 * @uses databaseCreate()
	 * @uses databaseRetrieve()
	 *
	 * @example
	 * // Retrieve an existing database.
	 * $server = new DataServer( 'driver://user:pass@host:8989/database' );<br/>
	 * $db = $server->RetrieveDatabase( "database" );
	 * @example
	 * // Create a new database.
	 * $server = new DataServer( 'driver://user:pass@host:8989' );<br/>
	 * $db = $server->RetrieveDatabase( "database", kFLAG_CONNECT | kFLAG_CREATE );
	 */
	public function GetDatabase( $theDatabase,
								 $theFlags = self::kFLAG_DEFAULT,
								 $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		$theDatabase = (string)$theDatabase;

		//
		// Match working databases.
		//
		if( array_key_exists( $theDatabase, $this->mDatabases ) )
			return $this->mDatabases[ $theDatabase ];								// ==>

		//
		// Check server connection.
		//
		if( $this->isConnected( $theFlags ) )
		{
			//
			// Retrieve existing database.
			//
			$database = $this->databaseRetrieve( $theDatabase, $theOptions );
			if( $database instanceof Database )
				return $this->mDatabases[ $theDatabase ]
					= $database;													// ==>

			//
			// Create database.
			//
			if( $theFlags & self::kFLAG_CREATE )
				return $this->mDatabases[ $theDatabase ]
					= $this->databaseCreate( $theDatabase, $theOptions );			// ==>

			//
			// Assert database.
			//
			if( $theFlags & self::kFLAG_ASSERT )
				throw new \RuntimeException (
					"Unknown database [$theDatabase]." );						// !@! ==>

		} // Server is connected.

		return NULL;																// ==>
		
	} // RetrieveDatabase.


	/*===================================================================================
	 *	ForgetDatabase																	*
	 *==================================================================================*/

	/**
	 * <h4>Dispose of a database object.</h4><p />
	 *
	 * This method can be used to dispose of a database object contained in the working
	 * databases collection.
	 *
	 * If the provided database name doesn't match any entries among the working databases,
	 * the method will return <tt>NULL</tt>, if it does, the method will return the related
	 * {@link Database} object.
	 *
	 * The method features a single parameter representing the database name.
	 *
	 * @param string				$theDatabase		Database name.
	 * @return Database				Database object or <tt>NULL</tt>.
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989/database' );<br/>
	 * $db = $server->ForgetDatabase( "database" );
	 */
	public function ForgetDatabase( $theDatabase )
	{
		//
		// Init local storage.
		//
		$theDatabase = (string)$theDatabase;

		//
		// Match working databases.
		//
		if( array_key_exists( $theDatabase, $this->mDatabases ) )
		{
			//
			// Save database object.
			//
			$database = $this->mDatabases[ $theDatabase ];

			//
			// Clear working database entry.
			//
			unset( $this->mDatabases[ $theDatabase ] );

			return $database;														// ==>
		}

		return NULL;																// ==>

	} // ForgetDatabase.

	
	/*===================================================================================
	 *	DropDatabase																	*
	 *==================================================================================*/
	
	/**
	 * <h4>Drop a database.</h4><p />
	 *
	 * This method can be used to drop a database, it features the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDatabase</b>: The database name.
	 *	<li><b>$theFlags</b>: A bitfield providing the following options:
	 *	 <ul>
	 * 		<li><tt>{@link kFLAG_CONNECT}</tt>: If set, the server will connect if
	 * 			necessary, if not set and the server is not connected, the method will
	 * 			return <tt>NULL</tt>.
	 * 		<li><tt>{@link kFLAG_ASSERT}</tt>: If set, the method will raise an exception if
	 * 			the server is not connected or if the database cannot be found.
	 * 	 </ul>
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * The method will return <tt>TRUE</tt> if the database was found or <tt>NULL</tt> if
	 * not.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param string				$theFlags			Flags bitfield.
	 * @param array					$theOptions			Database native options.
	 * @return boolean				<tt>TRUE</tt> dropped, <tt>FALSE</tt> not found.
	 * @throws \RuntimeException
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989/database' );<br/>
	 * $done = $server->DropDatabase( "database" );
	 *
	 * @uses RetrieveDatabase()
	 */
	public function DelDatabase( $theDatabase,
								 $theFlags = self::kFLAG_CONNECT,
								 $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		$theDatabase = (string)$theDatabase;

		//
		// Retrieve database.
		//
		$database = $this->GetDatabase( $theDatabase, $theFlags, $theOptions );
		if( $database instanceof Database )
		{
			//
			// Drop and forget database.
			//
			$database->Drop( $theOptions );
			unset( $this->mDatabases[ $theDatabase ] );

			return TRUE;															// ==>

		} // Found database.

		//
		// Assert database.
		//
		if( $theFlags & self::kFLAG_ASSERT )
			throw new \RuntimeException (
				"Unknown database [$theDatabase]." );							// !@! ==>

		return FALSE;																// ==>

	} // DropDatabase.
	
	
	
/*=======================================================================================
 *																						*
 *						PROTECTED DATABASE MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/
	
	
	
	/*===================================================================================
	 *	databaseList																	*
	 *==================================================================================*/
	
	/**
	 * <h4>List server databases.</h4><p />
	 *
	 * This method should return the list of server database names.
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * The provided parameter represents a set of native options provided to the driver for
	 * performing the operation: if needed, in derived concrete classes you should define
	 * globally a set of options and subtitute a <tt>NULL</tt> value with them in this
	 * method, this will guarantee that the options will always be used when performing this
	 * operation.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theOptions			Database native options.
	 * @return array				List of database names.
	 */
	abstract protected function databaseList( $theOptions = NULL );
	
	
	/*===================================================================================
	 *	databaseCreate																	*
	 *==================================================================================*/
	
	/**
	 * <h4>Create database.</h4><p />
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
	 * This method exists to allow instantiating the relevant derived concrete class.
	 *
	 * The provided parameter represents a set of native options provided to the driver for
	 * performing the operation: if needed, in derived concrete classes you should define
	 * globally a set of options and subtitute a <tt>NULL</tt> value with them in this
	 * method, this will guarantee that the options will always be used when performing this
	 * operation.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param array					$theOptions			Database native options.
	 * @return Database				Database object.
	 */
	abstract protected function databaseCreate( $theDatabase, $theOptions = NULL );
	
	
	/*===================================================================================
	 *	databaseRetrieve																*
	 *==================================================================================*/
	
	/**
	 * <h4>Return a database object.</h4><p />
	 *
	 * This method should return a {@link Database} object corresponding to the provided
	 * name, or <tt>NULL</tt> if the provided name does not correspond to any database in
	 * the server.
	 *
	 * The method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method exists to allow instantiating the relevant derived concrete class.
	 *
	 * The provided parameter represents a set of native options provided to the driver for
	 * performing the operation: if needed, in derived concrete classes you should define
	 * globally a set of options and subtitute a <tt>NULL</tt> value with them in this
	 * method, this will guarantee that the options will always be used when performing this
	 * operation.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param array					$theOptions			Database native options.
	 * @return Database				Database object or <tt>NULL</tt> if not found.
	 */
	abstract protected function databaseRetrieve( $theDatabase, $theOptions = NULL );

	
	
} // class DataServer.


?>
