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
	 * @uses \MongoDB\Database::drop()
	 *
	 * @see kMONGO_OPTS_DB_DROP
	 */
	public function Drop( $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_DB_DROP;

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
	 * @uses \MongoDB\Client::selectDatabase()
	 *
	 * @see kMONGO_OPTS_DB_CREATE
	 */
	protected function databaseNew( $theDatabase, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_DB_CREATE;

		return
			$this->Server()
				->Connection()
					->selectDatabase( $theDatabase, $theOptions );					// ==>

	} // databaseNew.


	/*===================================================================================
	 *	databaseName																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the database name.</h4>
	 *
	 * We overload this method to use the native object.
	 *
	 * @param mixed					$theOptions			Native driver options.
	 * @return string				The database name.
	 *
	 * @uses Server()
	 * @uses \MongoDB\Database::getDatabaseName()
	 */
	protected function databaseName( $theOptions = NULL )
	{
		return $this->Connection()->getDatabaseName();								// ==>

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
	 * We overload this method to use the native driver object, we only consider the
	 * collection names in the returned value.
	 *
	 * @param mixed					$theOptions			Collection native options.
	 * @return array				List of database names.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Database::listCollections()
	 *
	 * @see kMONGO_OPTS_DB_CLLIST
	 */
	protected function collectionList( $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		$collections = [];
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_DB_CLLIST;

		//
		// Ask database for list.
		//
		$list = $this->Connection()->listCollections( $theOptions );
		foreach( $list as $element )
			$collections[] = $element->getName();

		return $collections;														// ==>
	}


	/*===================================================================================
	 *	collectionCreate																*
	 *==================================================================================*/

	/**
	 * <h4>Create collection.</h4>
	 *
	 * We overload this method to instantiate a MongoDB version of the {@link Collection}
	 * class.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param mixed					$theOptions			Collection native options.
	 * @return Collection			Collection object.
	 *
	 * @see kMONGO_OPTS_DB_CLCREATE
	 */
	protected function collectionCreate( $theCollection, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_DB_CLCREATE;

		return new Collection( $this, $theCollection, $theOptions );				// ==>
	}


	/*===================================================================================
	 *	collectionRetrieve																*
	 *==================================================================================*/

	/**
	 * <h4>Return a collection object.</h4>
	 *
	 * We overload this method to check whether the collection exists and to instantiate a
	 * MongoDB version of the {@link Collection} class.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param mixed					$theOptions			Collection native options.
	 * @return Collection			Collection object or <tt>NULL</tt> if not found.
	 *
	 * @uses collectionList()
	 *
	 * @see kMONGO_OPTS_DB_CLRETRIEVE
	 */
	protected function collectionRetrieve( $theCollection, $theOptions = NULL )
	{
		//
		// Check if collection exists.
		//
		if( in_array( $theCollection, $this->collectionList() ) )
		{
			//
			// Init local storage.
			//
			if( $theOptions === NULL )
				$theOptions = kMONGO_OPTS_DB_CLRETRIEVE;
			
			return new Collection( $this, $theCollection, $theOptions );			// ==>
		
		} // Collection exists.

		return NULL;																// ==>
	}


} // class Database.


?>
