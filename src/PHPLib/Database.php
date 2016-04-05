<?php

/**
 * Database.php
 *
 * This file contains the definition of the {@link Database} class.
 */

namespace Milko\PHPLib;

use Milko\PHPLib\Container;
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
 * This class features a data member that holds the {@link DataServer} object that
 * instantiated the current database and another data member that holds the native driver's
 * database object.
 *
 * The class implements a public interface that deploys the common interface of derived
 * concrete classes:
 *
 * <ul>
 * 	<li><b>{@link Server()}</b>: Return the database server object.
 * 	<li><b>{@link Connection()}</b>: Return the database native driver object.
 * 	<li><b>{@link Drop()}</b>: Drop the database; this method is virtual.
 * 	<li><b>{@link ListCollections()}</b>: Return the list of collection names on the
 * 		database.
 * 	<li><b>{@link WorkingCollections()}</b>: Return the list of working collection names.
 * 	<li><b>{@link RetrieveCollection()}</b>: Create or retrieve a collection object.
 * 	<li><b>{@link ForgetCollection()}</b>: Clear or dispose of a collection object.
 * 	<li><b>{@link EmptyCollection()}</b>: Empty a collection.
 * 	<li><b>{@link DropCollection()}</b>: Drop a collection.
 * </ul>
 *
 * The object's inherited array will be populated with collections as they are used, the
 * offsets represent the collection name and the values will be {@link Collection} objects.
 *
 * Most of the above methods feature a bitfield parameter that determines the actions to
 * take in the event a collection is not found:
 *
 * <ul>
 * 	<li><tt>{@link kFLAG_CREATE}</tt>: If set and the indicated collection was not found,
 * 		it will be created in the process.
 * 	<li><tt>{@link kFLAG_ASSERT}</tt>: If set, an exception will be raised if the indicated
 * 		collection cannot be found and the {@link kFLAG_CREATE} flag is not set.
 * </ul>
 *
 * The public methods do not implement the actual operations, this is delegated to a
 * protected virtual interface which must be implemented by derived concrete classes:
 *
 * <ul>
 * 	<li><b>{@link databaseNew()}</b>: Instantiate a driver native database instance.
 * 	<li><b>{@link databaseName()}</b>: Return the database name.
 * 	<li><b>{@link collectionList()}</b>: Return the list of database collection names.
 * 	<li><b>{@link collectionCreate()}</b>: Create and return a {@link Collection} object
 * 		corresponding to the provided name.
 * 	<li><b>{@link collectionRetrieve()}</b>: Create and/or return a {@link Collection}
 * 		object corresponding to the provided name.
 * </ul>
 *
 * Each time a collection object is retrieved, the object will store it in the
 * {@link mCollections} data member for fast subsequent retrievals.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		16/02/2016
 *
 *	@example	../../test/Database.php
 *	@example
 * $server = new Milko\PHPLib\DataServer( 'protocol://user:pass@host:9090' );<br/>
 * $server->Connect();<br/>
 * $databases = $server->ListCollections();<br/>
 * $database = $server->RetrieveCollection( $databases[ 0 ], self::kFLAG_CREATE );<br/>
 * $collections = $database->ListCollections();<br/>
 * $collection = $database->RetrieveCollection( $collections[ 0 ], self::kFLAG_CREATE );<br/>
 * // Work with that collection...<br/>
 * $database->CollectionDrop( $collections[ 0 ] );<br/>
 * // Dropped the collection.
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
	protected $mNativeObject = NULL;




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
	 * A database is instantiated by providing the {@link DataServer} instance to which the
	 * database belongs, the database name and a set of native database driver options.
	 *
	 * @param DataServer			$theServer			Data server.
	 * @param string				$theDatabase		Database name.
	 * @param array					$theOptions			Native driver options.
	 *
	 * @uses Server()
	 * @uses RetrieveCollection()
	 * @uses databaseNew()
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989' );<br/>
	 * $server->Connect();<br/>
	 * $database = new Database( $server, "database" );
	 *
	 * @example
	 * // In general you will use this form:<br/>
	 * $server = new DataServer( 'driver://user:pass@host:8989/database' );<br/>
	 * $database = $server->RetrieveDatabase( "database" );
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
		$this->mNativeObject = $this->databaseNew( $theDatabase, $theOptions );

		//
		// Handle connection path.
		//
		$path = $this->Server()->Path();
		if( $path !== NULL )
		{
			//
			// Parse path.
			// We skip the first path element:
			// that is because the path begins with the separator.
			//
			$parts = explode( '/', $path );
			if( count( $parts ) > 2 )
				$this->RetrieveCollection( $parts[ 2 ], Server::kFLAG_CREATE );

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
	 * The method will use the protected {@link databaseName()} method.
	 *
	 * @return string
	 *
	 * @uses databaseName()
	 *
	 * @example
	 * $name = (string) $database;
	 */
	public function __toString()
	{
		//
		// Try to get name.
		//
		try{ return $this->databaseName(); }										// ==>
		catch( Exception $error ){ return ""; }										// ==>

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
	 *
	 * @example
	 * $server = $this->Server();
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
	 *
	 * @example
	 * $db = $this->Connection();
	 */
	public function Connection()						{	return $this->mNativeObject;	}



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
	 *	ListCollections																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the database list of collections.</h4>
	 *
	 * This method can be used to retrieve the list of collection names present on the
	 * database, the method accepts a single parameter that represents a driver dependent
	 * set of options.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param array					$theOptions			Collection native options.
	 * @return array				List of collection names.
	 *
	 * @uses collectionList()
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989/database' );<br/>
	 * $database = $server->RetrieveDatabase( "database" );<br/>
	 * $list = $database->ListCollections();
	 */
	public function ListCollections( $theOptions = NULL )
	{
		return $this->collectionList( $theOptions );								// ==>

	} // ListCollections.


	/*===================================================================================
	 *	WorkingCollections																*
	 *==================================================================================*/

	/**
	 * <h4>Return the working list of collections.</h4>
	 *
	 * This method can be used to retrieve the list of working collection names.
	 *
	 * @return array				List of working collection names.
	 *
	 * @uses arrayKeys()
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989/database' );<br/>
	 * $database = $server->RetrieveDatabase( "database" );<br/>
	 * $list = $database->WorkingCollections();
	 */
	public function WorkingCollections()					{	return $this->arrayKeys();	}


	/*===================================================================================
	 *	RetrieveCollection																*
	 *==================================================================================*/

	/**
	 * <h4>Return a collection object.</h4>
	 *
	 * This method can be used to create or retrieve a collection object, it features the
	 * following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCollection</b>: The collection name.
	 *	<li><b>$theFlags</b>: A bitfield providing the following options:
	 *	 <ul>
	 * 		<li><tt>{@link kFLAG_CREATE}</tt>: If set, the method will create the collection
	 * 			if it doesn't already exist.
	 * 		<li><tt>{@link kFLAG_ASSERT}</tt>: If set, the method will raise an exception if
	 * 			the collection cannot be found.
	 * 	 </ul>
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * The method will either return a {@link Collection} object, or <tt>NULL</tt> if the
	 * collection was not found.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param string				$theFlags			Flags bitfield.
	 * @param array					$theOptions			Collection native options.
	 * @return Collection			Collection object or <tt>NULL</tt>.
	 * @throws \RuntimeException
	 *
	 * @uses collectionCreate()
	 * @uses collectionRetrieve()
	 *
	 * @example
	 * // Retrieve an existing collection.<br/>
	 * $server = new DataServer( 'driver://user:pass@host:8989/database/collection' );<br/>
	 * $database = $server->RetrieveDatabase( "database" );<br/>
	 * $collection = $database->RetrieveCollection( "collection" );
	 * @example
	 * // Create a new collection.<br/>
	 * $server = new DataServer( 'driver://user:pass@host:8989' );<br/>
	 * $database = $server->RetrieveDatabase( "database", kFLAG_CREATE );<br/>
	 * $collection = $database->RetrieveCollection( "collection", kFLAG_CREATE );
	 */
	public function RetrieveCollection( $theCollection,
										$theFlags = Server::kFLAG_DEFAULT,
										$theOptions = NULL )
	{
		//
		// Init local storage.
		//
		$theCollection = (string)$theCollection;

		//
		// Match working collections.
		//
		if( $this->offsetExists( $theCollection ) )
			return $this->offsetGet( $theCollection );								// ==>

		//
		// Create or retrieve collection.
		//
		$collection = $this->collectionRetrieve( $theCollection, $theOptions );
		if( ($collection === NULL)
			&& ($theFlags & Server::kFLAG_CREATE) )
			$collection = $this->collectionCreate( $theCollection, $theOptions );

		//
		// Return collection.
		//
		if( $collection instanceof Collection )
		{
			//
			// Add collection.
			//
			$this->offsetSet( $theCollection, $collection );

			return $collection;														// ==>

		} // Created or retrieved.

		//
		// Assert collection.
		//
		if( $theFlags & Server::kFLAG_ASSERT )
			throw new \RuntimeException (
				"Unknown collection [$theCollection]." );						// !@! ==>

		return $collection;															// ==>

	} // RetrieveCollection.


	/*===================================================================================
	 *	RetrieveTerms																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the terms collection object.</h4>
	 *
	 * This method can be used to retrieve the default terms collection, it has the same
	 * parameters as the {@link RetrieveCollection()} method, except that the collection
	 * name is enforced and thus omitted from the parameters list.
	 *
	 * Since the collection name is driver dependent, the method must be implemented in
	 * driver specific derived classes.
	 *
	 * @param string				$theFlags			Flags bitfield.
	 * @param array					$theOptions			Collection native options.
	 * @return Collection			Collection object or <tt>NULL</tt>.
	 */
	abstract public function RetrieveTerms( $theFlags = Server::kFLAG_DEFAULT,
											$theOptions = NULL );


	/*===================================================================================
	 *	RetrieveRelations																*
	 *==================================================================================*/

	/**
	 * <h4>Return a relations object.</h4>
	 *
	 * This method can be used to create or retrieve a relationships collection object, it
	 * will return a collection that can hold edges.
	 *
	 * The method makes use of the {@link RetrieveCollection()} method by seting the
	 * requested collection type.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param string				$theFlags			Flags bitfield.
	 * @param array					$theOptions			Collection native options.
	 * @return Collection			Collection object or <tt>NULL</tt>.
	 * @throws \RuntimeException
	 *
	 * @uses collectionCreate()
	 * @uses collectionRetrieve()
	 *
	 * @example
	 * // Retrieve an existing collection.<br/>
	 * $server = new DataServer( 'driver://user:pass@host:8989/database/collection' );<br/>
	 * $database = $server->RetrieveDatabase( "database" );<br/>
	 * $collection = $database->RetrieveRelations( "edges" );
	 * @example
	 * // Create a new collection.<br/>
	 * $server = new DataServer( 'driver://user:pass@host:8989' );<br/>
	 * $database = $server->RetrieveDatabase( "database", kFLAG_CREATE );<br/>
	 * $collection = $database->RetrieveRelations( "edges", kFLAG_CREATE );
	 */
	public function RetrieveRelations( $theCollection,
									   $theFlags = Server::kFLAG_DEFAULT,
									   $theOptions = NULL )
	{
		//
		// Init requested collection type.
		//
		if( $theOptions === NULL )
			$theOptions = [];

		//
		// Set requested collection type.
		//
		$theOptions[ kTOKEN_OPT_COLLECTION_TYPE ] = kTOKEN_OPT_COLLECTION_TYPE_EDGE;

		return $this->RetrieveCollection( $theCollection, $theFlags, $theOptions );	// ==>

	} // RetrieveRelations.


	/*===================================================================================
	 *	ForgetCollection																*
	 *==================================================================================*/

	/**
	 * <h4>Dispose of a collection object.</h4>
	 *
	 * This method can be used to dispose of a collection object contained in the working
	 * collections collection.
	 *
	 * If the provided collection name doesn't match any entries among the working list,
	 * the method will return <tt>NULL</tt>, if it does, the method will return the related
	 * {@link Collection} object.
	 *
	 * The method features a single parameter representing the collection name.
	 *
	 * @param string				$theCollection		Collection name.
	 * @return Collection			Collection object or <tt>NULL</tt>.
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989/database/collection' );<br/>
	 * $database = $server->RetrieveDatabase( "database" );<br/>
	 * $collection = $database->ForgetCollection( "collection" );
	 */
	public function ForgetCollection( $theCollection )
	{
		//
		// Init local storage.
		//
		$theCollection = (string)$theCollection;
		$collection = $this->offsetGet( $theCollection );

		//
		// Match working collections.
		//
		if( $collection instanceof Collection )
			$this->offsetUnset( $theCollection );

		return $collection;															// ==>

	} // ForgetCollection.


	/*===================================================================================
	 *	EmptyCollection																	*
	 *==================================================================================*/

	/**
	 * <h4>Clear a collection.</h4>
	 *
	 * This method can be used to clear the contents of a collection, it features the
	 * following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCollection</b>: The collection name.
	 *	<li><b>$theFlags</b>: A bitfield providing the following options:
	 *	 <ul>
	 * 		<li><tt>{@link kFLAG_ASSERT}</tt>: If set, the method will raise an exception if
	 * 			the collection cannot be found.
	 * 	 </ul>
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * The method will return <tt>TRUE</tt> if the collection was found or <tt>NULL</tt> if
	 * not.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param string				$theFlags			Flags bitfield.
	 * @param array					$theOptions			Database native options.
	 * @return boolean				<tt>TRUE</tt> dropped, <tt>FALSE</tt> not found.
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989/database/collection' );<br/>
	 * $database = $server->RetrieveDatabase( "database" );<br/>
	 * $done = $database->EmptyCollection( "collection" );
	 *
	 * @uses RetrieveCollection()
	 */
	public function EmptyCollection( $theCollection,
									 $theFlags = Server::kFLAG_DEFAULT,
									 $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		$theDatabase = (string)$theCollection;

		//
		// Drop collection.
		//
		$collection = $this->RetrieveCollection( $theCollection, $theFlags );
		if( $collection instanceof Collection )
		{
			//
			// Clear collection.
			//
			$collection->Truncate( $theOptions );

			return TRUE;															// ==>
		}

		return FALSE;																// ==>

	} // EmptyCollection.


	/*===================================================================================
	 *	DropCollection																	*
	 *==================================================================================*/

	/**
	 * <h4>Drop a collection.</h4>
	 *
	 * This method can be used to drop a collection, it features the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCollection</b>: The collection name.
	 *	<li><b>$theFlags</b>: A bitfield providing the following options:
	 *	 <ul>
	 * 		<li><tt>{@link kFLAG_ASSERT}</tt>: If set, the method will raise an exception if
	 * 			the collection cannot be found.
	 * 	 </ul>
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * The method will return <tt>TRUE</tt> if the collection was found or <tt>NULL</tt> if
	 * not.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param string				$theFlags			Flags bitfield.
	 * @param array					$theOptions			Database native options.
	 * @return boolean				<tt>TRUE</tt> dropped, <tt>FALSE</tt> not found.
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989/database/collection' );<br/>
	 * $database = $server->RetrieveDatabase( "database" );<br/>
	 * $done = $database->DropCollection( "collection" );
	 *
	 * @uses RetrieveCollection()
	 */
	public function DropCollection( $theCollection,
									$theFlags = Server::kFLAG_DEFAULT,
									$theOptions = NULL )
	{
		//
		// Init local storage.
		//
		$theDatabase = (string)$theCollection;

		//
		// Drop collection.
		//
		$collection = $this->RetrieveCollection( $theCollection, $theFlags );
		if( $collection instanceof Collection )
		{
			//
			// Drop and forget the collection.
			//
			$collection->Drop( $theOptions );
			$this->offsetUnset( $theCollection );

			return TRUE;															// ==>
		}

		return FALSE;																// ==>

	} // DropCollection.



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
	 * This method should instantiate and return a native driver database object.
	 *
	 * This method assumes that the server is connected and that the {@link Server()} was
	 * set.
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
	 * @param array					$theOptions			Native driver options.
	 * @return mixed				Native database object.
	 */
	abstract protected function databaseNew( $theDatabase, $theOptions = NULL );


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
	 * performing the operation: if needed, in derived concrete classes you should define
	 * globally a set of options and subtitute a <tt>NULL</tt> value with them in this
	 * method, this will guarantee that the options will always be used when performing this
	 * operation.
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
	 * performing the operation: if needed, in derived concrete classes you should define
	 * globally a set of options and subtitute a <tt>NULL</tt> value with them in this
	 * method, this will guarantee that the options will always be used when performing this
	 * operation.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theOptions			Native driver options.
	 * @return array				List of database collection names.
	 */
	abstract protected function collectionList( $theOptions );


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
	abstract protected function collectionCreate( $theCollection, $theOptions = NULL );


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
	abstract protected function collectionRetrieve( $theCollection, $theOptions = NULL );



} // class Database.


?>
