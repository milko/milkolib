<?php

/**
 * Collection.php
 *
 * This file contains the definition of the {@link Collection} class.
 */

namespace Milko\PHPLib\MongoDB;

/*=======================================================================================
 *																						*
 *									Collection.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Collection ancestor object.</h4>
 *
 * This <em>concrete</em> class is the implementation of a MongoDB collection, it implements
 * the inherited virtual interface to provide an object that can manage MongoDB collections.
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		19/02/2016
 *
 *	@example	../../test/MongoCollection.php
 *	@example
 * $server = new Milko\PHPLib\DataServer( 'protocol://user:pass@host:9090/database/collection' );<br/>
 * $server->Connect();<br/>
 * $database = $server->RetrieveCollection( "database" );<br/>
 * $collection = $database->RetrieveCollection( "collection" );<br/>
 * // Work with that collection...
 */
class Collection extends \Milko\PHPLib\Collection
{



/*=======================================================================================
 *																						*
 *							PUBLIC COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Truncate																		*
	 *==================================================================================*/

	/**
	 * <h4>Clear the collection contents.</h4>
	 *
	 * We overload this method to call the native object's method.
	 *
	 * @param array					$theOptions			Driver native options.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::deleteMany()
	 */
	public function Truncate( $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = [];

		//
		// Empty collection.
		//
		$this->Connection()->deleteMany( [], $theOptions );

	} // Truncate.


	/*===================================================================================
	 *	Drop																			*
	 *==================================================================================*/

	/**
	 * <h4>Drop the current collection.</h4>
	 *
	 * We overload this method to call the native object's method.
	 *
	 * @param array					$theOptions			Driver native options.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::drop()
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
 *							PUBLIC DOCUMENT MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	ToDocument																		*
	 *==================================================================================*/

	/**
	 * <h4>Convert native data to standard document.</h4>
	 *
	 * We overload this method by casting the provided data into an array and instantiating
	 * the expected document.
	 *
	 * @param mixed					$theData			Database native document.
	 * @param string				$theClass			Expected class name.
	 * @return Document				Standard document object.
	 */
	public function ToDocument( $theData, $theClass = 'Milko\PHPLib\Document' )
	{
		return new $theClass( $this, (array) $theData );							// ==>

	} // ToDocument.


	/*===================================================================================
	 *	FromDocument																	*
	 *==================================================================================*/

	/**
	 * <h4>Convert a standard document to native data.</h4>
	 *
	 * We overload this method to return an array representation of the document.
	 *
	 * @param Document				$theDocument		Document to be converted.
	 * @return mixed				Database native object.
	 */
	public function FromDocument( \Milko\PHPLib\Document $theDocument )
	{
		return $theDocument->toArray();												// ==>

	} // FromDocument.



/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET DECLARATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	IdOffset																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the document identifier offset.</h4>
	 *
	 * In this class we return the default <tt>_id</tt> offset.
	 *
	 * @return string				Document identifier offset.
	 */
	public function IdOffset()											{	return '_id';	}


	/*===================================================================================
	 *	KeyOffset																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the document key offset.</h4>
	 *
	 * In this class we return the identifier <tt>_id</tt> offset.
	 *
	 * @return string				Document key offset.
	 */
	public function KeyOffset()											{	return '_id';	}


	/*===================================================================================
	 *	ClassOffset																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the document class offset.</h4>
	 *
	 * In this class we return the <tt>_class</tt> offset.
	 *
	 * @return string				Document class offset.
	 */
	public function ClassOffset()									{	return '_class';	}


	/*===================================================================================
	 *	RevisionOffset																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the document revision offset.</h4>
	 *
	 * In this class we return the <tt>_rev</tt> offset.
	 *
	 * @return string				Document revision offset.
	 */
	public function RevisionOffset()									{	return '_rev';	}



/*=======================================================================================
 *																						*
 *						PROTECTED COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	collectionNew																	*
	 *==================================================================================*/

	/**
	 * <h4>Return a native collection object.</h4>
	 *
	 * This method should instantiate and return a native driver collection object.
	 *
	 * This method assumes that the server is connected and that the {@link Server()} was
	 * set.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Driver native options.
	 * @return mixed				Native collection object.
	 *
	 * @uses Database()
	 * @uses \MongoDB\Database::selectCollection()
	 */
	protected function collectionNew( $theCollection, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = [];

		return $this->Database()->Connection()->selectCollection(
				(string)$theCollection, $theOptions );								// ==>

	} // collectionNew.


	/*===================================================================================
	 *	collectionName																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the collection name.</h4>
	 *
	 * We overload this method to use the native object.
	 *
	 * The options parameter is ignored here.
	 *
	 * @return string				The collection name.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::getCollectionName()
	 */
	protected function collectionName()
	{
		return $this->Connection()->getCollectionName();							// ==>
	
	} // collectionName.



/*=======================================================================================
 *																						*
 *						PROTECTED RECORD MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doInsert																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert one or more records.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::insertOne()} method
	 * to insert a single document and {@link \MongoDB\Collection::insertMany()} method to
	 * insert many records.
	 *
	 * We strip the <tt>'$doAll'</tt> parameter from the options and keep the other options
	 * as driver native parameters.
	 *
	 * @param mixed					$theDocument		The document(s) to be inserted.
	 * @param array					$theOptions			Driver native options.
	 * @return mixed				The document's unique identifier(s).
	 *
	 * @uses Connection()
	 * @uses FromDocument()
	 * @uses \MongoDB\Collection::insertOne()
	 * @uses \MongoDB\Collection::insertMany()
	 */
	protected function doInsert( $theDocument, $theOptions )
	{
		//
		// Init local storage.
		//
		$do_all = $theOptions[ '$doAll' ];

		//
		// Normalise container.
		//
		if( $do_all )
		{
			//
			// Init local storage.
			//
			$data = [];

			//
			// Iterate documents.
			//
			foreach( $theDocument as $document )
				$data[] = ( $document instanceof \Milko\PHPLib\Document )
						? $this->FromDocument( $document )
						: (array) $document;
		}
		else
			$data = ( $theDocument instanceof \Milko\PHPLib\Document )
				? $this->FromDocument( $theDocument )
				: (array) $theDocument;

		//
		// Insert one or more records.
		//
		$result = ( $do_all ) ? $this->Connection()->insertMany( $data, $theOptions )
							  : $this->Connection()->insertOne( $data, $theOptions );

		return ( $do_all ) ? $result->getInsertedIds()								// ==>
						   : $result->getInsertedId();								// ==>

	} // doInsert.


	/*===================================================================================
	 *	doUpdate																		*
	 *==================================================================================*/

	/**
	 * <h4>Update one or more records.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::updateOne()} method
	 * to update a single document and {@link \MongoDB\Collection::updateMany()} method to
	 * update many records.
	 *
	 * We strip the <tt>'$doAll'</tt> parameter from the options and keep the other options
	 * as driver native parameters.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theCriteria		The modification criteria.
	 * @param array					$theOptions			Driver native options.
	 * @return int					The number of modified records.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::updateOne()
	 * @uses \MongoDB\Collection::updateMany()
	 */
	protected function doUpdate( $theFilter, $theCriteria, $theOptions )
	{
		//
		// Init local storage.
		//
		$do_all = $theOptions[ '$doAll' ];

		//
		// Insert one or more records.
		//
		$result = ( $do_all )
				? $this->Connection()->updateMany( $theFilter, $theCriteria, $theOptions )
				: $this->Connection()->updateOne( $theFilter, $theCriteria, $theOptions );

		return $result->getModifiedCount();											// ==>
	
	} // doUpdate.


	/*===================================================================================
	 *	doReplace																		*
	 *==================================================================================*/

	/**
	 * <h4>Replace a record.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::replaceOne()} method.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theDocument		The replacement document.
	 * @param array					$theOptions			Driver native options.
	 * @return int					The number of replaced records.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::replaceOne()
	 */
	protected function doReplace( $theFilter, $theDocument, $theOptions )
	{
		//
		// Normalise container.
		//
		if( $theDocument instanceof \Milko\PHPLib\Container )
			$theDocument = $theDocument->toArray();

		//
		// Replace a record.
		//
		$result = $this->Connection()->replaceOne( $theFilter, $theDocument, $theOptions );

		return $result->getModifiedCount();											// ==>
	
	} // doReplace.


	/*===================================================================================
	 *	doDelete																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::deleteOne()} method
	 * to delete a single document and {@link \MongoDB\Collection::deleteMany()} method to
	 * delete all selected records.
	 *
	 * We strip the <tt>'$doAll'</tt> parameter from the options and keep the other options
	 * as driver native parameters.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Driver native options.
	 * @return int					The number of deleted records.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::deleteOne()
	 * @uses \MongoDB\Collection::deleteMany()
	 *
	 * @see kMONGO_OPTS_CL_DELETE
	 */
	protected function doDelete( $theFilter, $theOptions )
	{
		//
		// Init local storage.
		//
		$do_all = $theOptions[ '$doAll' ];

		//
		// Delete one or more records.
		//
		$result = ( $do_all )
			? $this->Connection()->deleteMany( $theFilter, $theOptions )
			: $this->Connection()->deleteOne( $theFilter, $theOptions );

		return $result->getDeletedCount();											// ==>

	} // doDelete.


	/*===================================================================================
	 *	doFindByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Find by example the first or all records.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::find()} method, the
	 * provided example document will be used as the actual selection criteria.
	 *
	 * We strip the <tt>'$start'</tt> and <tt>'$limit'</tt> parameters from the provided
	 * options and set respectively the <tt>skip</tt> and <tt>limit</tt> native options.
	 *
	 * @param array					$theDocument		The example document.
	 * @param array					$theOptions			Driver native options.
	 * @return Iterator				The found records.
	 *
	 * @uses Connection()
	 * @uses cursorToArray()
	 * @uses \MongoDB\Collection::find()
	 */
	protected function doFindByExample( $theDocument, $theOptions )
	{
		//
		// Normalise document.
		//
		if( $theDocument === NULL )
			$theDocument = [];
		elseif( $theDocument instanceof \Milko\PHPLib\Container )
			$theDocument = $theDocument->toArray();

		//
		// Init local storage.
		//
		if( array_key_exists( '$start', $theOptions ) )
		{
			$theOptions[ 'skip' ] = $theOptions[ '$start' ];
			unset( $theOptions[ '$start' ] );
		}
		if( array_key_exists( '$limit', $theOptions ) )
		{
			$theOptions[ 'limit' ] = $theOptions[ '$limit' ];
			unset( $theOptions[ '$limit' ] );
		}

		return $this->cursorToArray(
				$this->Connection()->find( $theDocument, $theOptions ) );			// ==>

	} // doFindByExample.


	/*===================================================================================
	 *	doFindByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Query the collection.</h4>
	 *
	 * We overload this method to use the {@link doFind()} method, since the latter method
	 * uses the example document as a query.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @param array					$theOptions			Collection native options.
	 * @return Iterator				The found records.
	 *
	 * @uses FindByExample()
	 */
	protected function doFindByQuery( $theQuery, $theOptions )
	{
		return $this->FindByExample( $theQuery, $theOptions );						// ==>

	} // doFindByQuery.


	/*===================================================================================
	 *	doCountByExample																*
	 *==================================================================================*/

	/**
	 * <h4>Return a find by example record count.</h4>
	 *
	 * We overload this method to use the {@link count()} method, since the latter method
	 * uses the example document as a query.
	 *
	 * @param array					$theDocument		The example document.
	 * @param array					$theOptions			Driver native options.
	 * @return int					The records count.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::count()
	 */
	protected function doCountByExample( $theDocument, $theOptions )
	{
		//
		// Normalise document.
		//
		if( $theDocument === NULL )
			$theDocument = [];
		elseif( $theDocument instanceof \Milko\PHPLib\Container )
			$theDocument = $theDocument->toArray();

		return $this->Connection()->count( $theDocument, $theOptions );				// ==>

	} // doCountByExample.


	/*===================================================================================
	 *	doCountByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Return a find by query record count.</h4>
	 *
	 * We overload this method to use the {@link doCountByExample()} method, since the
	 * latter method uses the example document as a query.
	 *
	 * @param array					$theDocument		The example document.
	 * @param array					$theOptions			Driver native options.
	 * @return int					The records count.
	 *
	 * @uses doCountByExample()
	 */
	protected function doCountByQuery( $theDocument, $theOptions )
	{
		return $this->doCountByExample( $theDocument, $theOptions );				// ==>

	} // doCountByQuery.


	/*===================================================================================
	 *	doMapReduce																		*
	 *==================================================================================*/

	/**
	 * <h4>Execute an aggregation query.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::aggregate()} method.
	 *
	 * We strip the <tt>'$start'</tt> and <tt>'$limit'</tt> parameters from the provided
	 * options and set respectively the <tt>skip</tt> and <tt>limit</tt> native options.
	 *
	 * @param mixed					$thePipeline		The aggregation pipeline.
	 * @param array					$theOptions			Driver native options.
	 * @return Iterator				The found records.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::aggregate()
	 */
	protected function doMapReduce( $thePipeline, $theOptions )
	{
		//
		// Init local storage.
		//
		if( array_key_exists( '$start', $theOptions ) )
		{
			$theOptions[ 'skip' ] = $theOptions[ '$start' ];
			unset( $theOptions[ '$start' ] );
		}
		if( array_key_exists( '$limit', $theOptions ) )
		{
			$theOptions[ 'limit' ] = $theOptions[ '$limit' ];
			unset( $theOptions[ '$limit' ] );
		}

		//
		// Serialise result.
		//
		$result = [];
		foreach( $this->Connection()->aggregate( $thePipeline, $theOptions ) as $record )
			$result[] = (array) $record;

		return $result;																// ==>

	} // doMapReduce.



} // class Collection.


?>
