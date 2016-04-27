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

use Milko\PHPLib\Database;
use Milko\PHPLib\MongoDB\Server;

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
		$this->mConnection->drop( $theOptions );

	} // Drop.



	/*=======================================================================================
	 *																						*
	 *							PUBLIC COLLECTION MANAGEMENT INTERFACE						*
	 *																						*
	 *======================================================================================*/



	/*===================================================================================
	 *	NewTermsCollection																*
	 *==================================================================================*/

	/**
	 * <h4>Create a terms collection object.</h4>
	 *
	 * We overload this method to use the {@link kTAG_MONGO_TERMS} collection name.
	 *
	 * @return Collection			Collection object.
	 */
	public function NewTermsCollection()
	{
		return $this->NewCollection( kTAG_MONGO_TERMS );							// ==>

	} // NewTermsCollection.


	/*===================================================================================
	 *	NewTypesCollection																*
	 *==================================================================================*/

	/**
	 * <h4>Create a types collection object.</h4>
	 *
	 * We overload this method to use the {@link kTAG_MONGO_TYPES} collection name.
	 *
	 * @return Collection			Collection object.
	 */
	public function NewTypesCollection()
	{
		return $this->NewEdgesCollection( kTAG_MONGO_TYPES );						// ==>

	} // NewTypesCollection.


	/*===================================================================================
	 *	NewDescriptorsCollection														*
	 *==================================================================================*/

	/**
	 * <h4>Create a descriptors collection object.</h4>
	 *
	 * We overload this method to use the {@link kTAG_MONGO_DESCRIPTORS} collection name.
	 *
	 * @return Collection			Collection object.
	 */
	public function NewDescriptorsCollection()
	{
		return $this->NewCollection( kTAG_MONGO_DESCRIPTORS );						// ==>

	} // NewDescriptorsCollection.


	/*===================================================================================
	 *	NewResourcesCollection															*
	 *==================================================================================*/

	/**
	 * <h4>Create a resources collection object.</h4>
	 *
	 * We overload this method to use the {@link kTAG_MONGO_RESOURCES} collection name.
	 *
	 * @return Collection			Collection object.
	 */
	public function NewResourcesCollection()
	{
		return $this->NewCollection( kTAG_MONGO_RESOURCES );						// ==>

	} // NewResourcesCollection.



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
	 * We overload this method to instantiate a native object.
	 *
	 * @param string				$theDatabase		Database name.
	 * @param array					$theOptions			Native driver options.
	 * @return mixed				Native database object.
	 *
	 * @uses \MongoDB\Client::selectDatabase()
	 */
	protected function databaseCreate( $theDatabase, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = [];

		return
			$this->mServer
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
	 * @uses \MongoDB\Database::getDatabaseName()
	 */
	protected function databaseName( $theOptions = NULL )
	{
		return $this->mConnection->getDatabaseName();								// ==>

	} // databaseName.



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
	 * We overload this method to instantiate a MongoDB version of the {@link Collection}
	 * class.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Collection native options.
	 * @return Collection			Collection object.
	 */
	protected function collectionCreate( $theCollection, array $theOptions )
	{
		//
		// Parse collection type.
		//
		switch( $tmp = $theOptions[ kTOKEN_OPT_COLLECTION_TYPE ] )
		{
			//
			// Documents collection.
			//
			case kTOKEN_OPT_COLLECTION_TYPE_DOC:
				unset( $theOptions[ kTOKEN_OPT_COLLECTION_TYPE ] );
				return new Collection( $this, $theCollection, $theOptions );		// ==>

			//
			// Edges collection.
			//
			case kTOKEN_OPT_COLLECTION_TYPE_EDGE:
				unset( $theOptions[ kTOKEN_OPT_COLLECTION_TYPE ] );
				return new Edges( $this, $theCollection, $theOptions );				// ==>
		}

		throw new \InvalidArgumentException (
			"Invalid collection type [$tmp]." );								// !@! ==>
	}


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
	 * @uses \MongoDB\Database::listCollections()
	 */
	protected function collectionList( array $theOptions )
	{
		//
		// Ask database for list.
		//
		$collections = [];
		$list = $this->mConnection->listCollections( $theOptions );
		foreach( $list as $element )
			$collections[] = $element->getName();

		return $collections;														// ==>
	}


} // class Database.


?>
