<?php

/**
 * Server.php
 *
 * This file contains the definition of the {@link Server} class.
 */

namespace Milko\PHPLib;

use Milko\PHPLib\Container;
use Milko\PHPLib\Datasource;
use Milko\PHPLib\Database;

/*=======================================================================================
 *																						*
 *									Server.php											*
 *																						*
 *======================================================================================*/

/**
 * <h4>Server abstract object.</h4><p />
 *
 * This <em>abstract</em> class is the ancestor of all classes representing server
 * instances.
 *
 * The class is derived from the {@link Container} class and uses its inherited array
 * member to store the list of working databases.
 *
 * The class features two attributes:
 *
 * <ul>
 * 	<li><tt>{@link $mDatasource}</tt>: This attribute contains a {@link Datasource} instance
 * 		which stores the server's data source name.
 * 	<li><tt>{@link $mConnection}</tt>: This attribute contains the server native connection
 * 		object, this is instantiated when the server connects.
 * </ul>
 *
 * The class implements the {@link iDatasource} interface which manages the server's
 * connection parameters and a public interface that takes care of connecting,
 * disconnecting, sleeping and waking the object, the implementation of the connection
 * workflow is delegated to a protected interface which is virtual and must be implemented
 * by concrete derived classes.
 *
 * The sleep and wake workflow ensures that the connection is closed before the object
 * goes to sleep and opened when it wakes, this is to handle native connection objects that
 * cannot be serialised in the session.
 *
 * When a connection is open, none of the {@link Datasource} properties can be modified,
 * attempting to do so will trigger an exception.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		06/02/2016
 *
 *	@example	../../test/Server.php
 *	@example
 * <code>
 * $server = new Milko\PHPLib\Server( 'protocol://user:pass@host:9090' );
 * $connection = $server->Connect();
 * </code>
 */
abstract class Server extends Container
					  implements iDatasource
{
	/**
	 * <h4>Data source object.</h4><p />
	 *
	 * This data member holds the <i>data source object</i> which stores the server
	 * connection attributes.
	 *
	 * @var Datasource
	 */
	protected $mDatasource = NULL;

	/**
	 * <h4>Server connection object.</h4><p />
	 *
	 * This data member holds the <i>server connection object</i>, it is the native object
	 * representing the server connection.
	 *
	 * Before the object goes to sleep ({@link __sleep()}), this attribute will be set to
	 * <tt>TRUE</tt> if a connection was open and to <tt>NULL</tt> if not: this determines
	 * whether a connection should be restored when the object is waken (@link __wakeup()}).
	 *
	 * @var mixed
	 */
	protected $mConnection = NULL;

	/**
	 * Default flags set.
	 *
	 * This represents the default set of flags.
	 *
	 * @var string
	 */
	const kFLAG_DEFAULT = 0x00000000;

	/**
	 * Assert.
	 *
	 * If this flag is set, a missing connection will trigger an exception.
	 *
	 * @var string
	 */
	const kFLAG_ASSERT = 0x00000001;

	/**
	 * Connect if necessary.
	 *
	 * If this flag is set, the server connection will be opened if necessary.
	 *
	 * @var string
	 */
	const kFLAG_CONNECT = 0x00000002;

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
	 * <h4>Instantiate class.</h4><p />
	 *
	 * The object must be instantiated from a data source name which will be passed to the
	 * {@link Datasource} constructor. If a path ({@link Path()}) is provided, we consider
	 * the first element to be a {@link Database} name and the second a {@link Collection}
	 * name, these will also be instantiated and/or created if provided.
	 *
	 * The constructor accepts a second parameter that may contain server native connection
	 * options.
	 *
	 * @param string			$theConnection		Data source name.
	 * @param mixed				$theOptions			Server connection options.
	 *
	 * @uses Path()
	 * @uses Connect()
	 * @uses NewDatabase()
	 *
	 * @example
	 * <code>
	 * $dsn = new Server( 'html://user:pass@host:8080/dir/file?arg=val#frag' );
	 * $dsn = new Server( 'protocol://user:password@host1:9090,host2,host3:9191/dir/file?arg=val#frag' );
	 * </code>
	 */
	public function __construct( $theConnection, $theOptions = NULL )
	{
		//
		// Instantiate data source attribute.
		//
		$this->mDatasource = new Datasource( $theConnection );

		//
		// Init parent.
		//
		parent::__construct();

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
				// Connect.
				//
				$this->Connect( $theOptions );

				//
				// Instantiate and register database.
				//
				$this->NewDatabase( $parts[ 1 ] );

			} // Has at least directory.

		} // Has path.

	} // Constructor.


	/*===================================================================================
	 *	__destruct																		*
	 *==================================================================================*/

	/**
	 * <h4>Destruct instance.</h4><p />
	 *
	 * In this class we close any open connection before disposing of the object.
	 *
	 * @uses Disconnect()
	 */
	public function __destruct()
	{
		//
		// Disconnect.
		//
		$this->Disconnect();

	} // __destruct.


	/*===================================================================================
	 *	__sleep																			*
	 *==================================================================================*/

	/**
	 * <h4>Put the object to sleep.</h4><p />
	 *
	 * This method will close the connection and replace the connection resource with
	 * <tt>TRUE</tt> if the connection was open, this will be used by the {@link __wakeup()}
	 * method to re-open the connection.
	 *
	 * @uses Disconnect()
	 */
	public function __sleep()
	{
		//
		// Signal there was a connection.
		//
		$this->mConnection = ( $this->Disconnect() ) ? TRUE : NULL;

	} // __sleep.


	/*===================================================================================
	 *	__wakeup																		*
	 *==================================================================================*/

	/**
	 * <h4>Wake the object from sleep.</h4><p />
	 *
	 * This method will re-open the connection if it was closed by the {@link __sleep()}
	 * method.
	 *
	 * @uses Connect()
	 */
	public function __wakeup()
	{
		//
		// Open closed connection.
		//
		if( $this->mConnection === TRUE )
			$this->Connect();

	} // __wakeup.


	/*===================================================================================
	 *	__toString																		*
	 *==================================================================================*/

	/**
	 * <h4>Return data source name</h4><p />
	 *
	 * In this class we consider the data source name as the server's name, when cast to a
	 * string the data source URL will be returned. In derived concrete classes you should
	 * be careful to shadow sensitive data such as user names and passwords.
	 *
	 * Note that this method cannot return the <tt>NULL</tt> value, which means that it
	 * cannot be used until there is a data source name for the object.
	 *
	 * @return string
	 */
	public function __toString()					{	return (string)$this->mDatasource;	}



/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Protocol																		*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source protocol.</h4><p />
	 *
	 * We use the {@link Datasource::Protocol()} method here, if setting or resetting the
	 * value and the server is connected, the method will raise an exception.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string
	 * @throws \RuntimeException
	 *
	 * @uses isConnected()
	 */
	public function Protocol( $theValue = NULL )
	{
		//
		// Assert value change and connection.
		//
		if( $this->isConnected()
		 && ($theValue !== NULL) )
			throw new \RuntimeException(
				"Cannot modify protocol while server is connected." );			// !@! ==>

		return $this->mDatasource->Protocol( $theValue );							// ==>

	} // Protocol.


	/*===================================================================================
	 *	Host																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source host.</h4><p />
	 *
	 * We use the {@link Datasource::Host()} method here, if setting or resetting the value
	 * and the server is connected, the method will raise an exception.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string|array
	 * @throws \RuntimeException
	 *
	 * @uses isConnected()
	 */
	public function Host( $theValue = NULL )
	{
		//
		// Assert value change and connection.
		//
		if( $this->isConnected()
		 && ($theValue !== NULL) )
			throw new \RuntimeException(
				"Cannot modify host while server is connected." );				// !@! ==>

		return $this->mDatasource->Host( $theValue );								// ==>

	} // Host.


	/*===================================================================================
	 *	Port																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source port.</h4><p />
	 *
	 * We use the {@link Datasource::Port()} method here, if setting or resetting the value
	 * and the server is connected, the method will raise an exception.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return int
	 * @throws \RuntimeException
	 *
	 * @uses isConnected()
	 */
	public function Port( $theValue = NULL )
	{
		//
		// Assert value change and connection.
		//
		if( $this->isConnected()
		 && ($theValue !== NULL) )
			throw new \RuntimeException(
				"Cannot modify port while server is connected." );				// !@! ==>

		return $this->mDatasource->Port( $theValue );								// ==>

	} // Port.


	/*===================================================================================
	 *	User																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source user name.</h4><p />
	 *
	 * We use the {@link Datasource::User()} method here, if setting or resetting the value
	 * and the server is connected, the method will raise an exception.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string
	 * @throws \RuntimeException
	 *
	 * @uses isConnected()
	 */
	public function User( $theValue = NULL )
	{
		//
		// Assert value change and connection.
		//
		if( $this->isConnected()
		 && ($theValue !== NULL) )
			throw new \RuntimeException(
				"Cannot modify user while server is connected." );				// !@! ==>

		return $this->mDatasource->User( $theValue );								// ==>

	} // User.


	/*===================================================================================
	 *	Password																		*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source user password.</h4><p />
	 *
	 * We use the {@link Datasource::Password()} method here, if setting or resetting the
	 * value and the server is connected, the method will raise an exception.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string
	 * @throws \RuntimeException
	 *
	 * @uses isConnected()
	 */
	public function Password( $theValue = NULL )
	{
		//
		// Assert value change and connection.
		//
		if( $this->isConnected()
		 && ($theValue !== NULL) )
			throw new \RuntimeException(
				"Cannot modify password while server is connected." );			// !@! ==>

		return $this->mDatasource->Password( $theValue );							// ==>

	} // Password.


	/*===================================================================================
	 *	Path																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source path.</h4><p />
	 *
	 * We use the {@link Datasource::Path()} method here, if setting or resetting the value
	 * and the server is connected, the method will raise an exception.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string
	 * @throws \RuntimeException
	 *
	 * @uses isConnected()
	 */
	public function Path( $theValue = NULL )
	{
		//
		// Assert value change and connection.
		//
		if( $this->isConnected()
		 && ($theValue !== NULL) )
			throw new \RuntimeException(
				"Cannot modify path while server is connected." );				// !@! ==>

		return $this->mDatasource->Path( $theValue );								// ==>

	} // Path.


	/*===================================================================================
	 *	Query																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source query.</h4><p />
	 *
	 * We use the {@link Datasource::Query()} method here, if setting or resetting the value
	 * and the server is connected, the method will raise an exception.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return array
	 * @throws \RuntimeException
	 *
	 * @uses isConnected()
	 */
	public function Query( $theValue = NULL )
	{
		//
		// Assert value change and connection.
		//
		if( $this->isConnected()
		 && ($theValue !== NULL) )
			throw new \RuntimeException(
				"Cannot modify query while server is connected." );				// !@! ==>

		return $this->mDatasource->Query( $theValue );								// ==>

	} // Query.


	/*===================================================================================
	 *	Fragment																		*
	 *==================================================================================*/

	/**
	 * <h4>Manage data source fragment.</h4><p />
	 *
	 * We use the {@link Datasource::Fragment()} method here, if setting or resetting the
	 * value and the server is connected, the method will raise an exception.
	 *
	 * @param mixed				$theValue			Value or operation.
	 * @return string
	 * @throws \RuntimeException
	 *
	 * @uses isConnected()
	 */
	public function Fragment( $theValue = NULL )
	{
		//
		// Assert value change and connection.
		//
		if( $this->isConnected()
		 && ($theValue !== NULL) )
			throw new \RuntimeException(
				"Cannot modify fragment while server is connected." );			// !@! ==>

		return $this->mDatasource->Fragment( $theValue );							// ==>

	} // Fragment.



/*=======================================================================================
 *																						*
 *							PUBLIC CONNECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Connect																			*
	 *==================================================================================*/

	/**
	 * <h4>Open server connection.</h4><p />
	 *
	 * This method can be used to create and open the server connection, if the connection
	 * is already open, the method will do nothing.
	 *
	 * The method will return the native connection object, or raise an exception if unable
	 * to open the connection.
	 *
	 * @param array					$theOptions			Connection native options.
	 * @return mixed				Native connection object.
	 *
	 * @uses isConnected( )
	 * @uses connectionCreate()
	 */
	public function Connect( $theOptions = NULL )
	{
		//
		// Create connection if not conected.
		//
		if( ! $this->isConnected() )
			$this->mConnection =
				$this->connectionCreate( $theOptions );

		return $this->mConnection;													// ==>

	} // Connect.


	/*===================================================================================
	 *	Disconnect																		*
	 *==================================================================================*/

	/**
	 * <h4>Close server connection.</h4><p />
	 *
	 * This method can be used to close and destruct the server connection, if no connection
	 * was open, the method will do nothing.
	 *
	 * The method will return <tt>TRUE</tt> if it closed a connection
	 *
	 * @param array					$theOptions			Connection native options.
	 * @return boolean				<tt>TRUE</tt> was connected, <tt>FALSE</tt> wasn't.
	 *
	 * @uses isConnected()
	 * @uses connectionDestruct()
	 */
	public function Disconnect( $theOptions = NULL )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected() )
		{
			//
			// Destruct connection.
			//
			$this->connectionDestruct( $theOptions );

			//
			// Reset native connection attribute.
			//
			$this->mConnection = NULL;

			return TRUE;															// ==>
		}

		return FALSE;																// ==>

	} // Disconnect.


	/*===================================================================================
	 *	Connection																		*
	 *==================================================================================*/

	/**
	 * <h4>Return native connection object.</h4><p />
	 *
	 * This method will return the native connection object, if a connection is open, or
	 * <tt>NULL</tt> if not.
	 *
	 * The provided bitfield parameter provides the following options:
	 *
	 * <ul~
	 * 	<li><tt>{@link kFLAG_CONNECT}</tt>: If set, the server will be connected if that is
	 * 		not yet the case.
	 * 	<li><tt>{@link kFLAG_ASSERT}</tt>: If the <tt>kFLAG_CONNECT</tt> flag is not set and
	 * 		the server is not connected, the method will raise a {@link \RuntimeException}.
	 * </ul>
	 *
	 * The second parameter represents eventual native driver options to be used when
	 * opening the connection.
	 *
	 * @param string				$theFlags			Flags bitfield.
	 * @param array					$theOptions			Connection native options.
	 * @return mixed				Native connection object or <tt>NULL</tt>.
	 *
	 * @uses isConnected()
	 *
	 * @example
	 * <code>
	 * // Will return connection or NULL if not connected.
	 * $connection = $server->Connection();
	 * // Will raise an exception if not connected.
	 * $connection = $server->Connection( Milko\PHPLib\Server::kFLAG_ASSERT );
	 * // Will connect if not connected.
	 * $connection = $server->Connection( Milko\PHPLib\Server::kFLAG_CONNECT );
	 * </code>
	 */
	public function Connection( $theFlags = self::kFLAG_DEFAULT, $theOptions = NULL )
	{
		//
		// Check if connected.
		//
		if( $this->isConnected( $theFlags, $theOptions ) )
			return $this->mConnection;												// ==>

		return NULL;																// ==>

	} // Connection.


	/*===================================================================================
	 *	isConnected																		*
	 *==================================================================================*/

	/**
	 * <h4>Check if connection is open.</h4><p />
	 *
	 * This method returns a boolean flag indicating whether the connection is open or not.
	 *
	 * The provided bitfield parameter provides the following options:
	 *
	 * <ul~
	 * 	<li><tt>{@link kFLAG_CONNECT}</tt>: If set, the server will be connected if that is
	 * 		not yet the case.
	 * 	<li><tt>{@link kFLAG_ASSERT}</tt>: If the <tt>kFLAG_CONNECT</tt> flag is not set and
	 * 		the server is not connected, the method will raise a {@link \RuntimeException}.
	 * </ul>
	 *
	 * The second parameter represents a set of options to be provided to the native driver
	 * if the connection should be opened.
	 *
	 * This method will be used by derived classes to ensure a connection is open before
	 * performing certain operations, the reason for providing the flags parameter is to
	 * allow automatic connection, doing so in this class makes it easier.
	 *
	 * @param string				$theFlags			Flags bitfield.
	 * @param array					$theOptions			Connection native options.
	 * @return boolean				<tt>TRUE</tt> is connected.
	 * @throws \RuntimeException
	 *
	 * @uses Connect()
	 *
	 * @example
	 * <code>
	 * // Will return TRUE if connected or FALSE.
	 * $connection = $server->isConnected();
	 * // Will return TRUE or raise an exception.
	 * $connection = $server->isConnected (Milko\PHPLib\Server::kFLAG_ASSERT );
	 * // Will return TRUE and connect if not connected.
	 * $connection = $server->isConnected( Milko\PHPLib\Server::kFLAG_CONNECT );
	 * </code>
	 */
	public function isConnected( $theFlags = self::kFLAG_DEFAULT, $theOptions = NULL )
	{
		//
		// Check if connected.
		//
		if( ($this->mConnection !== NULL)
		 && ($this->mConnection !== TRUE) )
			return TRUE;															// ==>

		//
		// Connect.
		//
		if( $theFlags & self::kFLAG_CONNECT )
		{
			$this->Connect( $theOptions );

			return TRUE;															// ==>
		}

		//
		// Assert.
		//
		if( $theFlags & self::kFLAG_ASSERT )
			throw new \RuntimeException (
				"Server connection was not opened." );							// !@! ==>

		return FALSE;																// ==>

	} // isConnected.



/*=======================================================================================
 *																						*
 *							PUBLIC DATABASE MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewDatabase																		*
	 *==================================================================================*/

	/**
	 * <h4>Create a database object.</h4><p />
	 *
	 * This method can be used to create a database object, it features a parameter
	 * that contains the requested database name and a parameter containing driver native
	 * options that can be used when creating the database.
	 *
	 * If the database already exists, it will be returned, if not, it will be created and
	 * added to the working databases of the server which are stored in the object's
	 * inherited array object.
	 *
	 * If the server is not connected, the connection will be opened automatically.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param mixed					$theOptions			Database creation native options.
	 * @return Database				Database object.
	 *
	 * @uses GetDatabase()
	 * @uses databaseCreate()
	 */
	public function NewDatabase( $theDatabase, $theOptions = NULL )
	{
		//
		// Check existing database.
		//
		$database = $this->GetDatabase( $theDatabase );
		if( $database instanceof Database )
			return $database;														// ==>

		//
		// Normalise database name.
		//
		$theDatabase = (string)$theDatabase;

		//
		// Create and register database.
		//
		$database = $this->databaseCreate( $theDatabase, $theOptions );
		$this->offsetSet( $theDatabase, $database );

		return $database;															// ==>

	} // NewDatabase.


	/*===================================================================================
	 *	GetDatabase																		*
	 *==================================================================================*/

	/**
	 * <h4>Return a database object.</h4><p />
	 *
	 * This method can be used to retrieve a database object, it features a parameter
	 * that contains the requested database name and a parameter containing driver native
	 * options that can be used to filter the current set of databases; by default only
	 * user databases should be selected.
	 *
	 * If the database exists, a {@link Database} object will be returned and added to the
	 * working databases of the server which are stored in the object's inherited array
	 * object.
	 *
	 * If the database doesn't exist, the method will return <tt>NULL</tt>.
	 *
	 * If the server is not connected, the connection will be opened automatically.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param mixed					$theOptions			Database selection native options.
	 * @return Database				Database object or <tt>NULL</tt>.
	 *
	 * @uses isConnected()
	 * @uses databaseRetrieve()
	 */
	public function GetDatabase( $theDatabase, $theOptions = NULL )
	{
		//
		// Assert connection.
		//
		$this->isConnected( self::kFLAG_CONNECT );

		//
		// Normalise database name.
		//
		$theDatabase = (string)$theDatabase;

		//
		// Match working databases.
		//
		if( $this->offsetExists( $theDatabase ) )
			return $this->offsetGet( $theDatabase );								// ==>

		//
		// Check if database exists.
		//
		$database = $this->databaseRetrieve( $theDatabase );
		if( $database instanceof Database )
		{
			//
			// Save database in working set.
			//
			$this->offsetSet( $theDatabase, $database );

			return $database;														// ==>

		} // Database exists.

		return NULL;																// ==>

	} // GetDatabase.


	/*===================================================================================
	 *	DelDatabase																		*
	 *==================================================================================*/

	/**
	 * <h4>Drop a database.</h4><p />
	 *
	 * This method can be used to drop a database, it expects the database name and driver
	 * native options used to drop the database.
	 *
	 * The method will return <tt>TRUE</tt> if the database was dropped or <tt>NULL</tt> if
	 * not.
	 *
	 * If the server is not connected, the connection will be opened automatically.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param array					$theOptions			Database native options.
	 * @return boolean				<tt>TRUE</tt> dropped, <tt>NULL</tt> not found.
	 *
	 * @uses GetDatabase()
	 * @uses Database::Drop()
	 */
	public function DelDatabase( $theDatabase, $theOptions = NULL )
	{
		//
		// Retrieve database.
		//
		$database = $this->GetDatabase( $theDatabase );
		if( $database instanceof Database )
		{
			//
			// Drop and unregister database.
			//
			$database->Drop( $theOptions );
			$this->offsetUnset( $theDatabase );

			return TRUE;															// ==>

		} // Found database.

		return NULL;																// ==>

	} // DelDatabase.


	/*===================================================================================
	 *	ListDatabases																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the server list of databases.</h4><p />
	 *
	 * This method can be used to retrieve the list of database names present on the server,
	 * the method features a parameter that represents driver native options: this parameter
	 * should be used to retrieve by default only the user databases.
	 *
	 * If the server is not connected, the connection will be opened automatically.
	 *
	 * @param mixed					$theOptions			Database selection native options.
	 * @return array				List of database names.
	 *
	 * @uses isConnected()
	 * @uses databaseList()
	 */
	public function ListDatabases( $theOptions = NULL )
	{
		//
		// Assert connection.
		//
		if( $this->isConnected( self::kFLAG_CONNECT ) )
			return $this->databaseList( $theOptions );								// ==>

		return [];																	// ==>

	} // ListDatabases.



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
	 * derived classes to implement this method.
	 *
	 * This method assumes the caller has checked whether the connection was already open
	 * and if the previously opened connection was closed.
	 *
	 * All the required connection properties must have been provided via the data source
	 * connection query parameters ({@link Query()}).
	 *
	 * The provided parameter represents the default or additional set of options provided
	 * to the driver.
	 *
	 * If the operation fails, the method should raise an exception.
	 *
	 * @param array					$theOptions			Connection native options.
	 * @return mixed				The native connection.
	 */
	abstract protected function connectionCreate( $theOptions = NULL );
	
	
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
	 * The provided parameter represents the default or additional set of options provided
	 * to the driver when closing the connection.
	 *
	 * If the operation fails, the method should raise an exception.
	 *
	 * @param array					$theOptions			Connection native options.
	 */
	abstract protected function connectionDestruct( $theOptions = NULL );



/*=======================================================================================
 *																						*
 *						PROTECTED DATABASE MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



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
	 * The first parameter represents the database name, the second parameter represents a
	 * set of native options provided to the driver when creating the database.
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



} // class Server.


?>
