<?php

/**
 * DatabaseServer.php
 *
 * This file contains the definition of the {@link DatabaseServer} class.
 */

namespace Milko\PHPLib;

use Milko\PHPLib\Server;
use Milko\PHPLib\Database;

/*=======================================================================================
 *																						*
 *									DatabaseServer.php									*
 *																						*
 *======================================================================================*/

/**
 * <h4>Database server ancestor object.</h4>
 *
 * This <em>abstract</em> class is the ancestor of all classes representing database
 * server instances.
 *
 * This class uses its inherited {@link Server} class to handle the server connection and
 * implements a public standard interface to handle database specific operations and
 * resources.
 *
 * All operations concerning a specific database feature a parameter that represents the
 * name of the database to handle: if a path is provided to the constructor, the first
 * element of that path will be considered the default database, in this case the database
 * name parameter may be omitted and the default database will be used in its place.
 *
 * These methods also feature a bitfield parameter that provide a workflow in the event
 * that the connection was not already open and in the case the indicated database does not
 * exist:
 * *
 * <ul>
 * 	<li><tt>{@link kFLAG_CONNECT}</tt>: If set, the server connection will be opened if not
 * 		already so.
 * 	<li><tt>{@link kFLAG_ASSERT}</tt>: If set, an exception will be raised if the connection
 * 		is not open and the {@link kFLAG_CONNECT} switch is off, and if the indicated
 * 		database cannot be found.
 * </ul>
 *
 * This class features a generalised public interface which represents the common interface:
 *
 * <ul>
 * 	<li><b>{@link ListDatabases()}</b>: Return the list of databases on the server.
 * 	<li><b>{@link GetDatabase()}</b>: Retrieve a database object.
 * 	<li><b>{@link DropDatabase()}</b>: Drop database object.
 * </ul>
 *
 * The above public methods do not implement the actual operations, this is delegated to a
 * protected virtual interface which must be implemented by derived concrete classes:
 *
 * <ul>
 * 	<li><b>{@link databaseList()}</b>: Return the list of database names.
 * 	<li><b>{@link databaseCreate()}</b>: Create and return a {@link Database} object
 * 		corresponding to the provided name.
 * 	<li><b>{@link databaseGet()}</b>: Return a {@link Database} object corresponding to the
 * 		provided name.
 * 	<li><b>{@link databaseDrop()}</b>: Drop the {@link Database} object corresponding to the
 * 		provided name.
 * </ul>
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		07/02/2016
 *
 *	@example	../../test/DatabaseServer.php
 *	@example
 * $server = new Milko\PHPLib\Server( 'protocol://user:pass@host:9090' );<br/>
 * $connection = $server->Connect();<br/>
 * $databases = $connection->DatabaseList();<br/>
 * $database = $connection->DatabaseGet( $databases[ 0 ] );<br/>
 * // Work with that database...<br/>
 * $connection->DatabaseDrop( $databases[ 0 ] );<br/>
 * // Dropped the database.
 */
abstract class DatabaseServer extends Server
{
	/**
	 * <h4>Default database object.</h4>
	 *
	 * This data member holds the <i>default database object</i>, this data member will be
	 * automatically filled from the inherited {@link Path()} information: the first element
	 * represents the database, the eventual remaining element represents the default
	 * collection.
	 *
	 * This data member will be used whenever database specific methods will be called
	 * without indicating a specific database.
	 *
	 * @var Database
	 */
	protected $mDefaultDatabase = NULL;

	/**
	 * Create resource if it doesn't exist.
	 *
	 * If this flag is set, the resource will be created if necessary.
	 *
	 * @var string
	 */
	const kFLAG_CREATE = 0x00000004;




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
	 * We overload the constructor to parse the connection string's path: if it was
	 * provided, the first element of the path will be used as the default database. By
	 * default the connection will be opened and the database created if it doesn't exist.
	 *
	 * @param string			$theConnection		Data source name.
	 *
	 * @uses Path()
	 * @uses GetDatabase()
	 *
	 * @example
	 * $dsn = new DataSource( 'driver://user:pass@host:8989/database' );<br/>
	 */
	public function __construct( $theConnection )
	{
		//
		// Call parent constructor.
		//
		parent::__construct( $theConnection );

		//
		// Handle path.
		//
		$path = $this->Path();
		if( $path !== NULL )
		{
			//
			// Parse path.
			// Note that we skip the first path element:
			// that is because the path begins with the separator.
			//
			$parts = explode( '/', $path );
			if( count( $parts ) > 1 )
				$this->mDefaultDatabase
					= $this->GetDatabase(
						$parts[ 1 ], self::kFLAG_CONNECT + self::kFLAG_CREATE );

		} // Has path.

	} // Constructor.



/*=======================================================================================
 *																						*
 *							PUBLIC CONNECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	ListDatabases																	*
	 *==================================================================================*/

	/**
	 * <h4>Return server databases list.</h4>
	 *
	 * This method can be used to retrieve the list of server database names, the method
	 * features the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFlags</b>: A bitfield providing the following options:
	 *	 <ul>
	 * 		<li><tt>{@link kFLAG_CONNECT}: If set, the server will connect if necessary, if
	 * 			not set and the server is not connected, the method will return an empty
	 * 			array.
	 * 		<li><tt>{@link kFLAG_ASSERT}: If set and the server is not connected, an
	 * 			exception will be raised.
	 * 	 </ul>
	 * </ul>
	 *
	 * The {@link kFLAG_CONNECT} flag is set by default to ensure the server is connected.
	 *
	 * @param string				$theFlags			Flags bitfield.
	 * @return mixed				List of database names.
	 *
	 * @uses isConnected()
	 * @uses databasesList()
	 */
	public function ListDatabases( $theFlags = self::kFLAG_CONNECT )
	{
		//
		// Assert connection.
		//
		if( $this->isConnected( $theFlags ) )
			return $this->databaseList();											// ==>
		
		return [];																	// ==>

	} // ListDatabases.


	/*===================================================================================
	 *	GetDatabase																		*
	 *==================================================================================*/

	/**
	 * <h4>Return a database object.</h4>
	 *
	 * This method can be used to retrieve a database object, it features the following
	 * parameters:
	 *
	 * <ul>
	 *	<li><b>$theDatabase</b>: The database name; if omitted or <tt>NULL</tt>, the
	 * 		the default database will be used if it was provided in the constructor.
	 *	<li><b>$theFlags</b>: A bitfield providing the following options:
	 *	 <ul>
	 * 		<li><tt>{@link kFLAG_CONNECT}: If set, the server will connect if necessary, if
	 * 			not set and the server is not connected, the method will return
	 * 			<tt>NULL</tt>.
	 * 		<li><tt>{@link kFLAG_ASSERT}: If set, the method will raise an exception if the
	 * 			server is not connected or if the database cannot be found.
	 * 		<li><tt>{@link kFLAG_CREATE}: If set, the method will create the database if it
	 * 			doesn't already exist.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will either return a database object, or <tt>NULL</tt> if the server is
	 * not connected or if the database was not found.
	 *
	 * The {@link kFLAG_CONNECT} and {@link kFLAG_CREATE} flags are set by default to ensure
	 * the connection is open and that the indicated database is created if necessary.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param string				$theFlags			Flags bitfield.
	 * @return Database				Database object or <tt>NULL</tt>.
	 * @throws \RuntimeException
	 *
	 * @uses isConnected()
	 * @uses databaseGet()
	 */
	public function GetDatabase( $theDatabase = NULL,
								 $theFlags = self::kFLAG_CONNECT + self::kFLAG_CREATE )
	{
		//
		// Handle default database.
		//
		if( $theDatabase === NULL )
		{
			//
			// Return database.
			//
			if( $this->mDefaultDatabase !== NULL )
				return $this->mDefaultDatabase;										// ==>

			//
			// Assert database.
			//
			if( $theFlags & self::kFLAG_ASSERT )
				throw new \RuntimeException (
					"There is no default database." );							// !@! ==>

			return NULL;															// ==>

		} // Use default database.

		//
		// Check if connected.
		//
		if( $this->isConnected( $theFlags ) )
		{
			//
			// Get database.
			//
			$database = ( $theFlags & self::kFLAG_CREATE )
					  ? $this->databaseCreate( (string) $theDatabase )
					  : $this->databaseGet( (string) $theDatabase );

			//
			// Assert.
			//
			if( ($theFlags & self::kFLAG_ASSERT)
			 && (! ($database instanceof Database)) )
				throw new \RuntimeException (
					"Unknown database ($theDatabase)." );						// !@! ==>

			return $database;														// ==>

		} // Connected.

		return NULL;																// ==>

	} // GetDatabase.


	/*===================================================================================
	 *	DropDatabase																	*
	 *==================================================================================*/

	/**
	 * <h4>Drop a database.</h4>
	 *
	 * This method can be used to drop a database, it features the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDatabase</b>: The database name, if omitted or <tt>NULL</tt>, the
	 * 		the default database will be used if it was provided in the constructor.
	 *	<li><b>$theFlags</b>: A bitfield providing the following options:
	 *	 <ul>
	 * 		<li><tt>{@link kFLAG_CONNECT}: If set, the server will connect if necessary, if
	 * 			not set and the server is not connected, the method will return
	 * 			<tt>NULL</tt>.
	 * 		<li><tt>{@link kFLAG_ASSERT}: If set, the method will raise an exception if the
	 * 			server is not connected or if the database cannot be found.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will attempt to drop the database if this was found; if the database is
	 * unknown, the method will do nothing, except if the {@link kFLAG_ASSERT} was set.
	 *
	 * The {@link kFLAG_CONNECT} flag is set by default to ensure the connection is open;
	 * if the database doesn't exists (or was already dropped), the method will do nothing.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param string				$theFlags			Flags bitfield.
	 *
	 * @uses GetDatabase()
	 */
	public function DropDatabase( $theDatabase = NULL,
								  $theFlags = self::kFLAG_CONNECT )
	{
		//
		// Handle database.
		//
		$database = $this->GetDatabase( $theDatabase, $theFlags );
		if( $database instanceof Database )
			$this->databaseDrop( $database );

		//
		// Reset default database.
		//
		if( $theDatabase === NULL )
			$this->mDefaultDatabase = NULL;

	} // DropDatabase.



/*=======================================================================================
 *																						*
 *						PROTECTED CONNECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	databaseList																	*
	 *==================================================================================*/

	/**
	 * <h4>List server databases.</h4>
	 *
	 * This method should return the list of server databases, if the provided parameter is
	 * <tt>TRUE</tt>, the method will return the result in the server's native format; if
	 * not, it will return an array of database names.
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @return array				List of database names.
	 */
	abstract protected function databaseList();


	/*===================================================================================
	 *	databaseCreate																	*
	 *==================================================================================*/

	/**
	 * <h4>Create server databases.</h4>
	 *
	 * This method should return a {@link Database} object corresponding to the provided
	 * name, if the database doesn't already exist, the method should create it.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theDatabase		Database name.
	 * @return Database				Database object.
	 */
	abstract protected function databaseCreate( $theDatabase );


	/*===================================================================================
	 *	databaseGet																		*
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
	 * @return Database				Database object or <tt>NULL</tt>.
	 */
	abstract protected function databaseGet( $theDatabase );


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
	 */
	abstract protected function databaseDrop( Database $theDatabase );



} // class DatabaseServer.


?>
