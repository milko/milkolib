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
 * $server = new Milko\PHPLib\DataServer( 'mongodb://localhost:27017/database/collection' );<br/>
 * $database = $server->RetrieveDatabase( "database" );<br/>
 * // Work with that database...<br/>
 */
class Database extends \Milko\PHPLib\Database
{



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
	 * @param array					$theOptions			Native driver options.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Database::drop()
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
	 * @param array					$theOptions			Native driver options.
	 * @return \MongoDB\Database	Native database object.
	 *
	 * @uses Server()
	 * @uses \MongoDB\Client::selectDatabase()
	 */
	protected function databaseNew( $theDatabase, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = [];

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
	 * @param array					$theOptions			Native driver options.
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
	 * <h4>List database collections.</h4>
	 *
	 * We overload this method to use the native driver object, we only consider the
	 * collection names in the returned value.
	 *
	 * @param array					$theOptions			Collection native options.
	 * @return array				List of database collection names.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Database::listCollections()
	 */
	protected function collectionList( $theOptions )
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
	 * @param array					$theOptions			Collection native options.
	 * @return Collection			Collection object.
	 */
	protected function collectionCreate( $theCollection, $theOptions )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = [];

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
	 * @param array					$theOptions			Collection native options.
	 * @return Collection			Collection object or <tt>NULL</tt> if not found.
	 *
	 * @uses collectionList()
	 */
	protected function collectionRetrieve( $theCollection, $theOptions )
	{
		//
		// Check if collection exists.
		//
		if( in_array( $theCollection, $this->collectionList( $theOptions ) ) )
		{
			//
			// Init local storage.
			//
			if( $theOptions === NULL )
				$theOptions = [];
			
			return new Collection( $this, $theCollection, $theOptions );			// ==>
		
		} // Collection exists.

		return NULL;																// ==>
	}


} // class Database.


?>
