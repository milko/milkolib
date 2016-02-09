<?php

/**
 * DatabaseServer.php
 *
 * This file contains the definition of the {@link DatabaseServer} class.
 */

namespace Milko\PHPLib;

use Milko\PHPLib\Server;

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
 * As with its ancestor, the actual implementation of the database operations is delegated
 * to a protected interface that must be implemented by derived concrete classes, this is
 * why this class is declared abstract.
 *
 * When instantiating this class a default database and collection can be provided in the
 * {@link DataSource::Path()} part of the connection string, these will be added to the
 * server and all operations will use these unless explicitly indicated.
 *
 * This class features a generalised public interface for communicating with the server:
 *
 * <ul>
 * 	<li><b>{@link Databases()}</b>: Return the list of databases on server.
 * 	<li><b>{@link Database()}</b>: Set, retrieve or reset database object.
 * 	<li><b>{@link DropDatabase()}</b>: Drop a database.
 * </ul>
 *
 * The above public methods do not implement the actual operations, this is delegated to a
 * protected virtual interface which must be implemented by derived concrete classes.
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
 * $connection = $server->Connect();
 * $database = $this->Database( "test" );
 * // Work with that database...
 *	@example
 * $connection = $server->Connect();
 * $database = $this->Database();
 * // Work with the database...
 */
abstract class DatabaseServer extends Server
{


	/*=======================================================================================
	 *																						*
	 *							PUBLIC CONNECTION MANAGEMENT INTERFACE						*
	 *																						*
	 *======================================================================================*/



	/*===================================================================================
	 *	Databases																		*
	 *==================================================================================*/

	/**
	 * <h4>Return server databases list.</h4>
	 *
	 * This method can be used to retrieve the list of server databases, the method features
	 * the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFlags</b>: A bitfield providing the following options:
	 *	 <ul>
	 * 		<li><tt>{@link kFLAG_CONNECT}: If set, the server will connect if necessary, if
	 * 			not set and the server is not connected, the method will raise an exception.
	 * 		<li><tt>{@link kFLAG_NATIVE}: If not set, the method will return an array of
	 * 			database names, if set, the method will return the result from the native
	 * 			driver.
	 * 	 </ul>
	 *	<li><b>$theOptions</b>: An optional list of options to be provided to the native
	 * 		driver.
	 * </ul>
	 *
	 * @param string				$theFlags			Flags bitfield.
	 * @param array					$theOptions			Options for the driver.
	 * @return mixed				List of database names or native result.
	 *
	 * @uses isConnected()
	 * @uses databasesList()
	 */
	public function Databases( $theFlags = self::kFLAG_DEFAULT, $theOptions = NULL )
	{
		//
		// Assert connection.
		//
		if( $this->isConnected( $doConnect, TRUE ) )
			return $this->databasesList( $doNative );								// ==>
		
		return [];																	// ==>

	} // ListDatabases.


	/*===================================================================================
	 *	ListCollections																	*
	 *==================================================================================*/

	/**
	 * <h4>List database collections.</h4>
	 *
	 * This method can be used to retrieve the list of database collections, the method
	 * features the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDatabase</b>: The database name or <tt>NULL</tt> for the default database
	 *		collections list.
	 *	<li><b>$doConnect</b>: If <tt>TRUE</tt> the method will attempt to connect to the
	 *		server if not already connected.
	 *	<li><b>$doNative</b>: If <tt>TRUE</tt> the method will return the native result
	 *		returned by the server, if not, the method will return a list of names as an
	 *		array. If set to false and the server is not connected, the method will raise
	 *		an exception.
	 * </ul>
	 *
	 * @param string				$theDatabase		Collections database name or NULL.
	 * @param boolean				$doConnect			<tt>TRUE</tt> connect if necessary.
	 * @param boolean				$doNative			<tt>TRUE</tt> return native result.
	 * @return mixed				List of collection names or native result.
	 *
	 * @uses GetDatabase()
	 * @uses collectionsList()
	 */
	public function ListCollections( $theDatabase = NULL,
									 $doConnect = FALSE,
									 $doNative = FALSE )
	{
		//
		// Get database.
		//
		$database = $this->GetDatabase( $theDatabase, $doConnect );
		if( $database !== NULL )
			return $this->collectionsList( $database, $doNative );					// ==>
		
		return [];																	// ==>

	} // ListCollections.


	/*===================================================================================
	 *	DefaultDatabase																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage default database.</h4>
	 *
	 * This method can be used to set, retrieve or reset the default server database, the
	 * method features the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theValue</b>: The database name or the operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Retrieve the current default server database.
	 *		<li><tt>FALSE</tt>: Reset the current default server database, will reset also
	 *			the default collection, if set.
	 *		<li><tt>string</tt>: Set the default database by name.
	 *	 </ul>
	 *	<li><b>$doConnect</b>: Assert server connection (only relevant if setting or
	 *		resetting):
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Connect if necessary.
	 *		<li><tt>FALSE</tt>: Raise exception if not connected.
	 *	 </ul>
	 * </ul>
	 *
	 * @param mixed					$theValue			Database or operation.
	 * @param boolean				$doConnect			Assert connection.
	 * @return mixed				Default database.
	 *
	 * @uses collectionClose()
	 * @uses databaseClose()
	 * @uses isConnected()
	 * @uses databaseGet()
	 */
	public function DefaultDatabase( $theValue = NULL, $doConnect = FALSE )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mDefaultDatabase;											// ==>
		
		//
		// Close default database.
		//
		if( $this->mDefaultDatabase !== NULL )
		{
			//
			// Close and reset default collection.
			//
			if( $this->mDefaultCollection !== NULL )
			{
				$this->collectionClose( $this->mDefaultCollection );
				$this->mDefaultCollection = NULL;
			}
	
			//
			// Close and reset default database.
			//
			$this->databaseClose( $this->mDefaultDatabase );
			$this->mDefaultDatabase = NULL;
		
		} // Has default database.
	
		//
		// Set new default database.
		// Will raise an exception if not connected.
		//
		if( ($theValue !== FALSE)
		 && $this->isConnected( $doConnect, TRUE ) )
			$this->mDefaultDatabase = $this->databaseGet( (string) $theValue );
			
		return $this->mDefaultDatabase;												// ==>

	} // DefaultDatabase.


	/*===================================================================================
	 *	DefaultCollection																*
	 *==================================================================================*/

	/**
	 * <h4>Manage default database.</h4>
	 *
	 * This method can be used to set, retrieve or reset the default server collection, the
	 * method features the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDatabase</b>: The database name or <tt>NULL</tt> to use the default
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Use the default database.
	 *		<li><tt>string</tt>: Use the database of the provided name; this database will
	 *			bevome the default database.
	 *	 </ul>
	 *	<li><b>$theValue</b>: The collection name or the operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Retrieve the current default server collection.
	 *		<li><tt>FALSE</tt>: Reset the current default server collection.
	 *		<li><tt>string</tt>: Set the default collection by name.
	 *	 </ul>
	 *	<li><b>$doConnect</b>: Assert server connection (only relevant if setting or
	 *		resetting):
	 *	 <ul>
	 *		<li><tt>TRUE</tt>: Connect if necessary.
	 *		<li><tt>FALSE</tt>: Raise exception if not connected.
	 *	 </ul>
	 * </ul>
	 *
	 * @param mixed					$theDatabase		Database name or <tt>NULL</tt>.
	 * @param mixed					$theValue			Collection name or operation.
	 * @param boolean				$doConnect			Assert connection.
	 * @return mixed				Default collection.
	 * @throws \RuntimeException
	 *
	 * @uses collectionClose()
	 * @uses GetCollection()
	 * @uses DefaultDatabase()
	 */
	public function DefaultCollection( $theDatabase = NULL,
									   $theValue = NULL,
									   $doConnect = FALSE )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mDefaultCollection;										// ==>
		
		//
		// Close and reset default collection.
		//
		if( $this->mDefaultCollection !== NULL )
		{
			$this->collectionClose( $this->mDefaultCollection );
			$this->mDefaultCollection = NULL;
		}
		
		//
		// Handle new value.
		//
		if( $theValue !== FALSE )
		{
			//
			// Set new collection.
			//
			$this->mDefaultCollection
				= $this->GetCollection(
					$theDatabase, $theValue, $doConnect );
			
			//
			// Set new default database.
			//
			if( $theDatabase !== NULL )
				$this->DefaultDatabase( $theDatabase, $doConnect );
		
		} // Not deleting.
			
		return $this->mDefaultCollection;											// ==>

	} // DefaultCollection.


	/*===================================================================================
	 *	GetDatabase																		*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database object.</h4>
	 *
	 * This method can be used to retrieve a database native object, the method features
	 * the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDatabase</b>: The database name or <tt>NULL</tt> for the default
	 *		database.
	 *	<li><b>$doConnect</b>: If <tt>TRUE</tt> the method will attempt to connect to the
	 *		server if not already connected.
	 * </ul>
	 *
	 * @param string				$theValue			Database name or <tt>NULL</tt>.
	 * @param boolean				$doConnect			<tt>TRUE</tt> connect if necessary.
	 * @return mixed				Database native object.
	 *
	 * @uses isConnected()
	 * @uses databaseGet()
	 */
	public function GetDatabase( $theValue = NULL, $doConnect = FALSE )
	{
		//
		// Handle non default database.
		// If not connected and value is not NULL raise exception.
		//
		if( ($theValue !== NULL)
		 && $this->isConnected( $doConnect, TRUE ) )
			return $this->databaseGet( (string) $theValue );						// ==>
		
		return $this->mDefaultDatabase;												// ==>

	} // GetDatabase.


	/*===================================================================================
	 *	DropDatabase																	*
	 *==================================================================================*/

	/**
	 * <h4>Drop a database.</h4>
	 *
	 * This method can be used to drop a database, the method features the following
	 * parameters:
	 *
	 * <ul>
	 *	<li><b>$theDatabase</b>: The database name or <tt>NULL</tt> for the default
	 *		database; in the latter case the default database will be reset.
	 *	<li><b>$doConnect</b>: If <tt>TRUE</tt> the method will attempt to connect to the
	 *		server if not already connected.
	 * </ul>
	 *
	 * @param string				$theValue			Database name or <tt>NULL</tt>.
	 * @param boolean				$doConnect			<tt>TRUE</tt> connect if necessary.
	 *
	 * @uses databaseDrop()
	 * @uses DefaultDatabase()
	 * @uses GetDatabase()
	 */
	public function DropDatabase( $theValue = NULL, $doConnect = FALSE )
	{
		// Handle existing default database.
		//
		if( ($theValue === NULL)
		 && ($this->mDefaultDatabase !== NULL) )
		{
			//
			// Drop database.
			//
			$this->databaseDrop( $this->mDefaultDatabase );
			
			//
			// Reset default database.
			//
			$this->DefaultDatabase( FALSE, $doConnect );
		
		} // Existing default database.
		
		//
		// Handle other database.
		//
		else
			$this->databaseDrop( $this->GetDatabase( $theValue, $doConnect ) );

	} // DropDatabase.


	/*===================================================================================
	 *	GetCollection																	*
	 *==================================================================================*/

	/**
	 * <h4>Return a native collection object.</h4>
	 *
	 * This method can be used to retrieve a colection native object, the method features
	 * the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDatabase</b>: The database name or <tt>NULL</tt> for the default
	 *		database.
	 *	<li><b>$theValue</b>: The collection name or <tt>NULL</tt> for the default
	 *		collection; note that in this case we ignore the database.
	 *	<li><b>$doConnect</b>: If <tt>TRUE</tt> the method will attempt to connect to the
	 *		server if not already connected.
	 * </ul>
	 *
	 * @param string				$theDatabase		Database name or <tt>NULL</tt>.
	 * @param string				$theValue			Collection name or <tt>NULL</tt>.
	 * @param boolean				$doConnect			<tt>TRUE</tt> connect if necessary.
	 * @return mixed				Database native object.
	 * @throws \RuntimeException
	 *
	 * @uses GetDatabase()
	 * @uses collectionGet()
	 */
	public function GetCollection( $theDatabase = NULL,
								   $theValue = NULL,
								   $doConnect = FALSE )
	{
		//
		// Return  default collection.
		//
		if( $theValue === NULL )
			return $this->mDefaultCollection;										// ==>
		
		//
		// Resolve database.
		//
		$database = $this->GetDatabase( $theDatabase, $doConnect );
		if( $database !== NULL )
			return $this->collectionGet( $database, (string) $theValue );			// ==>
		
		throw new \RuntimeException (
			"Unable to get collection: missing database." );					// !@! ==>

	} // GetCollection.


	/*===================================================================================
	 *	DropCollection																	*
	 *==================================================================================*/

	/**
	 * <h4>Drop a database.</h4>
	 *
	 * This method can be used to drop a database, the method features the following
	 * parameters:
	 *
	 * <ul>
	 *	<li><b>$theDatabase</b>: The database name or <tt>NULL</tt> for the default
	 *		database.
	 *	<li><b>$theCollection</b>: The collection name or <tt>NULL</tt> for the default
	 *		collection; in the latter case the default database will be reset.
	 *	<li><b>$doConnect</b>: If <tt>TRUE</tt> the method will attempt to connect to the
	 *		server if not already connected.
	 * </ul>
	 *
	 * @param string				$theDatabase		Database name or <tt>NULL</tt>.
	 * @param string				$theCollection		Collection name or <tt>NULL</tt>.
	 * @param boolean				$doConnect			<tt>TRUE</tt> connect if necessary.
	 *
	 * @uses GetCollection()
	 * @uses collectionDrop()
	 * @uses DefaultCollection()
	 */
	public function DropCollection( $theDatabase = NULL,
									$theCollection = NULL,
									$doConnect = FALSE )
	{
		//
		// Get collection.
		//
		$collection = $this->GetCollection( $theDatabase, $theCollection, $doConnect );
		if( $collection !== NULL )
		{
			//
			// Drop collection.
			//
			$this->collectionDrop( $collection );
			
			//
			// Reset default collection.
			//
			if( $theCollection === NULL )
				$this->DefaultCollection( $theDatabase, FALSE, $doConnect );
		
		} // Collection exists.

	} // DropCollection.



/*=======================================================================================
 *																						*
 *						PROTECTED CONNECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	databaseClose																	*
	 *==================================================================================*/

	/**
	 * <h4>Cleanup database before disposing of it.</h4>
	 *
	 * This method should dispose of resources before a database is closed, in this class
	 * we do nothing.
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method should be implemented by derived concrete classes if there is the need.
	 *
	 * @param mixed					$theDatabase		Database native object.
	 */
	protected function databaseClose( $theDatabase )									   {}


	/*===================================================================================
	 *	collectionClose																	*
	 *==================================================================================*/

	/**
	 * <h4>Cleanup collection before disposing of it.</h4>
	 *
	 * This method should dispose of resources before a collection is closed, in this class
	 * we do nothing.
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method should be implemented by derived concrete classes if there is the need.
	 *
	 * @param mixed					$theCollection		Collection native object.
	 */
	protected function collectionClose( $theCollection )								   {}


	/*===================================================================================
	 *	databasesList																	*
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
	 * @param boolean				$doNative			<tt>TRUE</tt> return native result.
	 * @return mixed				Array or native server's result.
	 */
	abstract protected function databasesList( $doNative );


	/*===================================================================================
	 *	collectionsList																	*
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
	 * @param mixed					$theDatabase		Collections database native object.
	 * @param boolean				$doNative			<tt>TRUE</tt> return native result.
	 * @return mixed				Array or native server's result.
	 */
	abstract protected function collectionsList( $theDatabase, $doNative );


	/*===================================================================================
	 *	databaseGet																		*
	 *==================================================================================*/

	/**
	 * <h4>Return a database native object.</h4>
	 *
	 * This method should return a server native database object corresponding to the
	 * provided name.
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theValue			Database name.
	 * @return mixed				Database native object.
	 */
	abstract protected function databaseGet( $theValue );


	/*===================================================================================
	 *	collectionGet																	*
	 *==================================================================================*/

	/**
	 * <h4>Return a collection native object.</h4>
	 *
	 * This method should return a server native collection object corresponding to the
	 * provided database native object and collection name.
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theDatabase		Collections database name or object.
	 * @param string				$theValue			Collection name.
	 * @return mixed				Collection native object.
	 */
	abstract protected function collectionGet( $theDatabase, $theValue );


	/*===================================================================================
	 *	databaseDrop																	*
	 *==================================================================================*/

	/**
	 * <h4>Drop database.</h4>
	 *
	 * This method should drop the database corresponding to the provided database native
	 * object.
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theDatabase		Database native object.
	 */
	abstract protected function databaseDrop( $theDatabase );


	/*===================================================================================
	 *	collectionDrop																	*
	 *==================================================================================*/

	/**
	 * <h4>Drop collection.</h4>
	 *
	 * This method should drop the collection corresponding to the provided database native
	 * object.
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theCollection		Collection native object.
	 */
	abstract protected function collectionDrop( $theCollection );



} // class Server.


?>
