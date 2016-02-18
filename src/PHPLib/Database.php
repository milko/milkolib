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
 * 	<li><b>{@link newDatabase()}</b>: Instantiate a driver native database instance.
 * 	<li><b>{@link databaseName()}</b>: Return the database name.
 * 	<li><b>{@link collectionList()}</b>: Return the list of database collection names.
 * 	<li><b>{@link collectionCreate()}</b>: Create and return a {@link Collection} object
 * 		corresponding to the provided name.
 * 	<li><b>{@link collectionRetrieve()}</b>: Return a {@link Collection} object
 * 		corresponding to the provided name.
 * 	<li><b>{@link collectionEmpty()}</b>: Clear the contents of the provided
 * 		{@link Collection} object.
 * 	<li><b>{@link collectionDrop()}</b>: Drop the provided {@link Collection} object.
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
	 * @param mixed					$theOptions			Native driver options.
	 *
	 * @uses newDatabase()
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989' );<br/>
	 * $database = new Database( $server, "database" );
	 *
	 * @example
	 * // In general you will use this form:
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
		$this->mNativeObject = $this->newDatabase( $theDatabase, $theOptions );

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
	public function __toString()						{	return $this->databaseName();	}



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
	 * database, the method accepts a single parameter that represents a filter to select
	 * specific collections, the format of the filter is driver dependent.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theFilter			Collections selection filter.
	 * @return array				List of collection names.
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989/database' );<br/>
	 * $database = $server->RetrieveDatabase( "database" );<br/>
	 * $list = $database->ListCollections();
	 *
	 * @uses collectionList()
	 */
	public function ListCollections( $theFilter = NULL )
	{
		return $this->collectionList( $theFilter );									// ==>

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
	 * The {@link kFLAG_CREATE} flag is set by default to ensure the indicated collection is
	 * created if necessary.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param string				$theFlags			Flags bitfield.
	 * @param mixed					$theOptions			Collection native options.
	 * @return Collection			Collection object or <tt>NULL</tt>.
	 * @throws \RuntimeException
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989/database/collection' );<br/>
	 * $database = $server->RetrieveDatabase( "database" );<br/>
	 * $collection = $database->RetrieveCollection( "collection" );
	 *
	 * @uses collectionRetrieve()
	 * @uses collectionCreate()
	 */
	public function RetrieveCollection( $theCollection,
										$theFlags = DataServer::kFLAG_CREATE,
										$theOptions = NULL )
	{
		//
		// Init local storage.
		//
		$theCollection = (string)$theCollection;

		//
		// Match working collections.
		//
		$collection = $this->offsetGet( $theCollection );
		if( $collection instanceof Collection )
			return $collection;														// ==>

		//
		// Retrieve existing collection.
		//
		$collection = $this->collectionRetrieve( $theCollection, $theOptions );
		if( $collection instanceof Collection )
			return $collection;														// ==>

		//
		// Create collection.
		//
		if( $theFlags & DataServer::kFLAG_CREATE )
		{
			$collection = $this->collectionCreate( $theCollection, $theOptions );
			$this->offsetSet( $theCollection, $collection );

			return $collection;														// ==>
		}

		//
		// Assert collection.
		//
		if( $theFlags & DataServer::kFLAG_ASSERT )
			throw new \RuntimeException (
				"Unknown collection [$theCollection]." );						// !@! ==>

		return NULL;																// ==>

	} // RetrieveCollection.


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
	 * @param mixed					$theOptions			Database native options.
	 * @return boolean				<tt>TRUE</tt> dropped, <tt>FALSE</tt> not found.
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989/database/collection' );<br/>
	 * $database = $server->RetrieveDatabase( "database" );<br/>
	 * $done = $database->DropCollection( "collection" );
	 *
	 * @uses RetrieveCollection()
	 * @uses collectionEmpty()
	 */
	public function EmptyCollection( $theCollection,
									 $theFlags = DataServer::kFLAG_DEFAULT,
									 $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		$theDatabase = (string)$theCollection;
		$collection = $this->RetrieveCollection( $theCollection, $theFlags );

		//
		// Drop collection.
		//
		if( $collection instanceof Collection )
		{
			//
			// Clear collection.
			//
			$this->collectionEmpty( $collection, $theOptions );

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
	 * @param mixed					$theOptions			Database native options.
	 * @return boolean				<tt>TRUE</tt> dropped, <tt>FALSE</tt> not found.
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989/database/collection' );<br/>
	 * $database = $server->RetrieveDatabase( "database" );<br/>
	 * $done = $database->DropCollection( "collection" );
	 *
	 * @uses RetrieveCollection()
	 * @uses collectionDrop()
	 */
	public function DropCollection( $theCollection,
									$theFlags = DataServer::kFLAG_DEFAULT,
									$theOptions = NULL )
	{
		//
		// Init local storage.
		//
		$theDatabase = (string)$theCollection;
		$collection = $this->RetrieveCollection( $theCollection, $theFlags );

		//
		// Drop collection.
		//
		if( $collection instanceof Collection )
		{
			//
			// Drop collection.
			//
			$this->collectionDrop( $collection, $theOptions );

			//
			// Clear working collections entry.
			// Note that unsetting a non existing offset
			// will do nothing in the ancestor class.
			//
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
	 *	newDatabase																		*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database object.</h4>
	 *
	 * This method should instantiate and return a native driver database object.
	 *
	 * This method assumes that the server is connected and that the {@link Server()} was
	 * set.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param mixed					$theOptions			Native driver options.
	 * @return mixed				Native database object.
	 */
	abstract protected function newDatabase( $theDatabase, $theOptions );


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
	 * This method must be implemented by derived concrete classes.
	 *
	 * @return string				The database name.
	 */
	abstract protected function databaseName();



/*=======================================================================================
 *																						*
 *						PROTECTED COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	collectionList																	*
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
	abstract protected function collectionList();


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
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param mixed					$theOptions			Collection native options.
	 * @return Collection			Collection object.
	 */
	abstract protected function collectionCreate( $theCollection, $theOptions );


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
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param mixed					$theOptions			Collection native options.
	 * @return Collection			Collection object or <tt>NULL</tt> if not found.
	 */
	abstract protected function collectionRetrieve( $theCollection, $theOptions );


	/*===================================================================================
	 *	collectionEmpty																	*
	 *==================================================================================*/

	/**
	 * <h4>Clear a collection.</h4>
	 *
	 * This method should clear the contents of the provided collection.
	 *
	 * The method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param Collection			$theCollection		Collection object.
	 * @param mixed					$theOptions			Collection native options.
	 */
	abstract protected function collectionEmpty( Collection $theCollection, $theOptions );


	/*===================================================================================
	 *	collectionDrop																	*
	 *==================================================================================*/

	/**
	 * <h4>Drop a collection.</h4>
	 *
	 * This method should drop the provided collection.
	 *
	 * The method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param Collection			$theCollection		Collection object.
	 * @param mixed					$theOptions			Collection native options.
	 */
	abstract protected function collectionDrop( Collection $theCollection, $theOptions );



} // class Database.


?>
