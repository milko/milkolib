<?php

/**
 * Database.php
 *
 * This file contains the definition of the {@link Database} class.
 */

namespace Milko\PHPLib;

use Milko\PHPLib\Server;
use Milko\PHPLib\Collection;

/*=======================================================================================
 *																						*
 *									Database.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Database ancestor object.</h4>
 *
 * This <em>abstract</em> class is the ancestor of all classes representing database
 * instances.
 *
 * The class is derived from the {@link Container} class and uses its inherited array
 * member to store the list of working collections.
 *
 * The class features two attributes:
 *
 * <ul>
 * 	<li><tt>{@link $mServer}</tt>: This attribute contains a {@link Server} instance to
 * 		which the database belongs.
 * 	<li><tt>{@link $mConnection}</tt>: This attribute contains the database native
 * 		connection object.
 * </ul>
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		16/02/2016
 */
abstract class Database extends Container
{
	/**
	 * <h4>Database server object.</h4>
	 *
	 * This data member holds the <i>database server object</i>, it is the object that
	 * instantiated the current database.
	 *
	 * @var DataServer
	 */
	protected $mServer = NULL;

	/**
	 * <h4>Database native object.</h4>
	 *
	 * This data member holds the <i>database native object</i>, it is the object provided
	 * by the database driver.
	 *
	 * @var mixed
	 */
	protected $mConnection = NULL;




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
	 * A database is instantiated by providing the {@link Server} instance to which the
	 * database belongs, the database name and a set of native database driver options.
	 *
	 * @param Server				$theServer			Server.
	 * @param string				$theDatabase		Database name.
	 * @param array					$theOptions			Native driver options.
	 *
	 * @uses databaseCreate()
	 * @uses NewCollection()
	 * @uses Server::Path()
	 *
	 * @example
	 * <code>
	 * $server = new Server( 'driver://user:pass@host:8989' );
	 * $server->Connect();
	 * $database = new Database( $server, "database" );
	 * </code>
	 *
	 * @example
	 * <code>
	 * // In general you will use this form:
	 * $server = new Server( 'driver://user:pass@host:8989/database' );
	 * $database = $server->GetDatabase( "database" );
	 * </code>
	 */
	public function __construct( DataServer $theServer, $theDatabase, $theOptions = NULL )
	{
		//
		// Call parent constructor.
		//
		parent::__construct();

		//
		// Store server instance.
		//
		$this->mServer = $theServer;

		//
		// Store the driver instance.
		//
		$this->mConnection = $this->databaseCreate( (string)$theDatabase, $theOptions );

		//
		// Handle connection path.
		//
		$path = $this->mServer->Path();
		if( $path !== NULL )
		{
			//
			// Parse path.
			// We skip the first path element:
			// that is because the path begins with the separator.
			//
			$parts = explode( '/', $path );
			if( count( $parts ) > 2 )
				$this->NewCollection( $parts[ 2 ] );

		} // Has path.

	} // Constructor.


	/*===================================================================================
	 *	__toString																		*
	 *==================================================================================*/

	/**
	 * <h4>Return database name</h4>
	 *
	 * Objects of this class should return the database name when cast to string.
	 *
	 * If the server is not yet connected, its connection will be opened.
	 *
	 * The method will use the protected {@link databaseName()} method.
	 *
	 * @return string
	 *
	 * @uses databaseName()
	 */
	public function __toString()
	{
		//
		// Assert connection.
		//
		$this->mServer->isConnected( Server::kFLAG_CONNECT );

		//
		// Try to get name.
		//
		try{ return $this->databaseName(); }										// ==>
		catch( \Exception $error ){ return ""; }									// ==>

	} // __toString.



/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Server																			*
	 *==================================================================================*/

	/**
	 * <h4>Return the database server.</h4>
	 *
	 * This method can be used to retrieve the database server object.
	 *
	 * @return DataServer			Database server object.
	 */
	public function Server()									{	return $this->mServer;	}


	/*===================================================================================
	 *	Connection																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the database native driver object.</h4>
	 *
	 * This method can be used to retrieve the database native driver object.
	 *
	 * @return mixed				Database native driver object.
	 */
	public function Connection()							{	return $this->mConnection;	}



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
	 * This method can be used to drop the current database, the provided parameter
	 * represents driver native options.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * The method must be implemented by derived concrete classes.
	 *
	 * @param array					$theOptions			Native driver options.
	 */
	abstract public function Drop( $theOptions = NULL );



/*=======================================================================================
 *																						*
 *							PUBLIC COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewCollection																	*
	 *==================================================================================*/

	/**
	 * <h4>Create a collection object.</h4>
	 *
	 * This method can be used to create a collection object, it features a parameter
	 * that contains the requested collection name and a parameter containing driver native
	 * options that can be used when creating the database; besides the driver native
	 * options, the second parameter contains the following global options:
	 *
	 * <ul>
	 * 	<li><tt>{@link kTOKEN_OPT_COLLECTION_TYPE}</tt>: Collection type:
	 * 	 <ul>
	 * 		<li><tt>{@link kTOKEN_OPT_COLLECTION_TYPE_DOC}</tt>: Default document type.
	 * 		<li><tt>{@link kTOKEN_OPT_COLLECTION_TYPE_EDGE}</tt>: Edge document type.
	 * 	 </ul>
	 * </ul>
	 *
	 * The {@link kTOKEN_OPT_COLLECTION_TYPE_DOC} type is enforced by default, in derived
	 * classes you could enforce another type.
	 *
	 * If the collection already exists, it will be returned, if not, it will be created and
	 * added to the working collections of the database which are stored in the object's
	 * inherited array object.
	 *
	 * If the server is not connected, the connection will be opened automatically.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Collection native options.
	 * @return Collection			Collection object.
	 *
	 * @uses GetCollection()
	 * @uses collectionCreate()
	 */
	public function NewCollection( $theCollection, $theOptions = NULL )
	{
		//
		// Normalise options.
		//
		if( $theOptions === NULL )
			$theOptions = [ kTOKEN_OPT_COLLECTION_TYPE => kTOKEN_OPT_COLLECTION_TYPE_DOC ];

		//
		// Check existing collection.
		//
		$collection = $this->GetCollection( $theCollection, $theOptions );
		if( $collection instanceof Collection )
			return $collection;														// ==>

		//
		// Create and register database.
		//
		$collection = $this->collectionCreate( (string)$theCollection, $theOptions );
		$this->offsetSet( $theCollection, $collection );

		return $collection;															// ==>

	} // NewCollection.


	/*===================================================================================
	 *	GetCollection																	*
	 *==================================================================================*/

	/**
	 * <h4>Return a collection object.</h4><p />
	 *
	 * This method can be used to retrieve a collection object, it features a parameter
	 * that contains the requested collection name and a parameter containing options that
	 * can be used to filter the current set of collections; by default the
	 * {@link kTOKEN_OPT_COLLECTION_TYPE} option will be set to
	 * {@link kTOKEN_OPT_COLLECTION_TYPE_DOC}.
	 *
	 * If the collection exists, a {@link Collection} object will be returned and added to
	 * the working collections of the database which are stored in the object's inherited
	 * array object.
	 *
	 * If the collection doesn't exist, the method will return <tt>NULL</tt>.
	 *
	 * If the server is not connected, the connection will be opened automatically.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param mixed					$theOptions			Collection selection options.
	 * @return Database				Database object or <tt>NULL</tt>.
	 *
	 * @uses collectionRetrieve()
	 * @uses Server::isConnected()
	 */
	public function GetCollection( $theCollection, $theOptions = NULL )
	{
		//
		// Assert connection.
		//
		$this->mServer->isConnected( Server::kFLAG_CONNECT );

		//
		// Normalise options.
		//
		if( $theOptions === NULL )
			$theOptions = [ kTOKEN_OPT_COLLECTION_TYPE => kTOKEN_OPT_COLLECTION_TYPE_DOC ];

		//
		// Match working collections.
		//
		if( $this->offsetExists( $theCollection ) )
			return $this->offsetGet( $theCollection );								// ==>

		//
		// Check if collection exists.
		//
		$collection = $this->collectionRetrieve( (string)$theCollection, $theOptions );
		if( $collection instanceof Collection )
		{
			//
			// Save collection in working set.
			//
			$this->offsetSet( $theCollection, $collection );

			return $collection;														// ==>

		} // Collection exists.

		return NULL;																// ==>

	} // GetCollection.


	/*===================================================================================
	 *	DelCollection																	*
	 *==================================================================================*/

	/**
	 * <h4>Drop a collection.</h4><p />
	 *
	 * This method can be used to drop a collection, it expects the collection name and
	 * driver native options used to drop the collection.
	 *
	 * The method will return <tt>TRUE</tt> if the collection was dropped or <tt>NULL</tt>
	 * if not.
	 *
	 * If the server is not connected, the connection will be opened automatically.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Database native options.
	 * @return boolean				<tt>TRUE</tt> dropped, <tt>NULL</tt> not found.
	 *
	 * @uses GetCollection()
	 * @uses Collection::Drop()
	 */
	public function DelCollection( $theCollection, $theOptions = NULL )
	{
		//
		// Retrieve collection.
		//
		$collection = $this->GetCollection( $theCollection );
		if( $collection instanceof Collection )
		{
			//
			// Normalise options.
			//
			if( $theOptions === NULL )
				$theOptions = [];

			//
			// Drop and unregister database.
			//
			$collection->Drop( $theOptions );
			$this->offsetUnset( $theCollection );

			return TRUE;															// ==>

		} // Found collection.

		return NULL;																// ==>

	} // DelCollection.


	/*===================================================================================
	 *	ListCollections																	*
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
	 * @uses collectionList()
	 * @uses Server::isConnected()
	 */
	public function ListCollections( $theOptions = NULL )
	{
		//
		// Assert connection.
		//
		$this->mServer->isConnected( self::kFLAG_CONNECT );

		//
		// Normalise options.
		//
		if( $theOptions === NULL )
			$theOptions = [];

		return $this->collectionList( $theOptions );								// ==>

	} // ListCollections.



/*=======================================================================================
 *																						*
 *						PROTECTED DATABASE MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	databaseCreate																	*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database object.</h4>
	 *
	 * This method should instantiate and return a native driver database object.
	 *
	 * This method assumes that the server was set and connected.
	 *
	 * The provided parameter represents a set of native options provided to the driver for
	 * performing the operation.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param array					$theOptions			Native driver options.
	 * @return mixed				Native database object.
	 */
	abstract protected function databaseCreate( $theDatabase, $theOptions = NULL );


	/*===================================================================================
	 *	databaseName																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the database name.</h4>
	 *
	 * This method should return the current database name.
	 *
	 * Note that this method <em>must</em> return a non empty string.
	 *
	 * The provided parameter represents a set of native options provided to the driver for
	 * performing the operation.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theOptions			Native driver options.
	 * @return string				The database name.
	 */
	abstract protected function databaseName( $theOptions = NULL );



/*=======================================================================================
 *																						*
 *						PROTECTED COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	collectionCreate																*
	 *==================================================================================*/

	/**
	 * <h4>Create collection.</h4>
	 *
	 * This method should create and return a {@link Collection} object corresponding to the
	 * provided name, if the operation fails, the method should raise an exception.
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * The method should not be concerned if the collection already exists, it is the
	 * responsibility of the caller to check it.
	 *
	 * The provided parameter represents a set of native options provided to the driver for
	 * performing the operation: if needed, in derived concrete classes you should define
	 * globally a set of options and subtitute a <tt>NULL</tt> value with them in this
	 * method, this will guarantee that the options will always be used when performing this
	 * operation.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Native driver options.
	 * @return Collection			Collection object.
	 */
	abstract protected function collectionCreate( $theCollection, array $theOptions );


	/*===================================================================================
	 *	collectionRetrieve																*
	 *==================================================================================*/

	/**
	 * <h4>Return a collection object.</h4>
	 *
	 * This method should return a {@link Collection} object corresponding to the provided
	 * name, or <tt>NULL</tt> if the provided name does not correspond to any collection in
	 * the database.
	 *
	 * The method assumes that the server is connected, it is the responsibility of the
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
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Native driver options.
	 * @return Collection			Collection object or <tt>NULL</tt> if not found.
	 */
	abstract protected function collectionRetrieve( $theCollection, array $theOptions );


	/*===================================================================================
	 *	collectionList																	*
	 *==================================================================================*/

	/**
	 * <h4>List database collections.</h4>
	 *
	 * This method should return the list of database collection names.
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * The provided parameter represents a set of native options provided to the driver for
	 * performing the operation.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theOptions			Native driver options.
	 * @return array				List of database collection names.
	 */
	abstract protected function collectionList( array $theOptions );



} // class Database.


?>
