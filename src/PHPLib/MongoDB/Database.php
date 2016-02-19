<?php

/**
 * Database.php
 *
 * This file contains the definition of the {@link Database} class.
 */

namespace Milko\PHPLib\MongoDB;

/*=======================================================================================
 *																						*
 *									Database.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>MongoDB database object.</h4>
 *
 * This <em>concrete</em> class is the implementation of a MongoDB database, it implements
 * the inherited virtual interface to provide an object that can manage MongoDB databases
 * and collections.
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		19/02/2016
 *
 *	@example	../../test/MongoDatabase.php
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
class Database extends \Milko\PHPLib\Database
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
 *							PUBLIC DATABASE MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Drop																			*
	 *==================================================================================*/

	/**
	 * <h4>Drop the current database.</h4>
	 *
	 * We overload this method to call the native object's method.
	 *
	 * @param mixed					$theOptions			Native driver options.
	 *
	 * @uses Connection()
	 */
	public function Drop( $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = [];

		//
		// Call native method.
		//
		$this->Connection()->drop( $theOptions );

	} // Drop.



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
	 * We overload this method to instantiate a native object.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param mixed					$theOptions			Native driver options.
	 * @return mixed				Native database object.
	 *
	 * @uses Server()
	 */
	protected function databaseNew( $theDatabase, $theOptions )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = [];

		return $this->Server()->Connection()->selectDatabase( $theOptions );		// ==>

	} // databaseNew.


	/*===================================================================================
	 *	databaseName																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the database name.</h4>
	 *
	 * We overload this method to use the native object.
	 *
	 * @return string				The database name.
	 *
	 * @uses Server()
	 */
	protected function databaseName()
	{
		return $this->Server()->Connection()->getDatabaseName();					// ==>

	} // databaseName.



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
	 * We overload this method to use the native driver object.
	 *
	 * @param mixed					$theOptions			Collection native options.
	 * @return array				List of database names.
	 */
	protected function collectionList( $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		$collections = [];
		if( $theOptions === NULL )
			$theOptions = [];

		//
		// Ask database for list.
		//
		$list = $this->Connection()->listCollections( $theOptions );
		foreach( $list as $element )
			$collections[] = $element[ 'name' ];

		return $collections;														// ==>
	}


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
	protected function collectionCreate( $theCollection, $theOptions )
	{
		return new Collection( $this, $theCollection, $theOptions );				// ==>
	}


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
	protected function collectionRetrieve( $theCollection, $theOptions )
	{
		//
		// Check if collection exists.
		//
		if( in_array( $theCollection, $this->collectionList() ) )
			return new Collection( $this, $theCollection, $theOptions );			// ==>

		return NULL;																// ==>
	}


} // class Database.


?>
