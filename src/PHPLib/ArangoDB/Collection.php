<?php

/**
 * Collection.php
 *
 * This file contains the definition of the {@link Collection} class.
 */

namespace Milko\PHPLib\ArangoDB;

use triagens\ArangoDb\Database as ArangoDatabase;
use triagens\ArangoDb\Collection as ArangoCollection;
use triagens\ArangoDb\CollectionHandler as ArangoCollectionHandler;
use triagens\ArangoDb\Endpoint as ArangoEndpoint;
use triagens\ArangoDb\Connection as ArangoConnection;
use triagens\ArangoDb\ConnectionOptions as ArangoConnectionOptions;
use triagens\ArangoDb\DocumentHandler as ArangoDocumentHandler;
use triagens\ArangoDb\Document as ArangoDocument;
use triagens\ArangoDb\Exception as ArangoException;
use triagens\ArangoDb\Export as ArangoExport;
use triagens\ArangoDb\ConnectException as ArangoConnectException;
use triagens\ArangoDb\ClientException as ArangoClientException;
use triagens\ArangoDb\ServerException as ArangoServerException;
use triagens\ArangoDb\Statement as ArangoStatement;
use triagens\ArangoDb\UpdatePolicy as ArangoUpdatePolicy;

/*=======================================================================================
 *																						*
 *									Collection.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Collection ancestor object.</h4>
 *
 * This <em>concrete</em> class is the implementation of a ArangoDB collection, it
 * implements the inherited virtual interface to provide an object that can manage ArangoDB
 * collections.
 *
 * This class stores the {@link ArangoCollection} object and instantiates at runtime the
 * {@link ArangoCollectionHandler} to perform operations.
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		21/02/2016
 *
 *	@example	../../test/ArangoCollection.php
 *	@example
 * $server = new Milko\PHPLib\DataServer( 'tcp://localhost:8529/database/collection' );<br/>
 * $database = $server->RetrieveDatabase( "database" );<br/>
 * $collection = $database->RetrieveCollection( "collection" );<br/>
 * // Work with that collection...<br/>
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
	 * @param array					$theOptions			Collection native options.
	 *
	 * @uses Database()
	 * @uses Connection()
	 * @uses ArangoCollectionHandler::truncate()
	 */
	public function Truncate( $theOptions = NULL )
	{
		//
		// Instantiate collection handler.
		//
		$collectionHandler = new ArangoCollectionHandler( $this->Database()->Connection() );

		//
		// Truncate collection.
		//
		$collectionHandler->truncate( $this->Connection() );

	} // Truncate.


	/*===================================================================================
	 *	Drop																			*
	 *==================================================================================*/

	/**
	 * <h4>Drop the current collection.</h4>
	 *
	 * We overload this method to call the native object's method.
	 *
	 * @param array					$theOptions			Collection native options.
	 *
	 * @uses Database()
	 * @uses Connection()
	 * @uses ArangoCollectionHandler::drop()
	 */
	public function Drop( $theOptions = NULL )
	{
		//
		// Instantiate collection handler.
		//
		$collectionHandler = new ArangoCollectionHandler( $this->Database()->Connection() );

		//
		// Drop collection.
		//
		$collectionHandler->drop( $this->Connection() );

	} // Drop.



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
	 * This method will return a {@link ArangoCollection} object.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Native driver options.
	 * @return mixed				Native collection object.
	 */
	protected function collectionNew( $theCollection, $theOptions = NULL )
	{
		return new ArangoCollection( $theCollection );								// ==>

	} // collectionNew.


	/*===================================================================================
	 *	collectionName																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the collection name.</h4>
	 *
	 * This method should return the current collection name.
	 *
	 * Note that this method <em>must</em> return a non empty string.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @return string				The collection name.
	 *
	 * @uses Connection()
	 * @uses ArangoCollection::getName()
	 */
	protected function collectionName( $theOptions = NULL )
	{
		return $this->Connection()->getName();										// ==>

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
	 * This method should insert the provided record or records, the method expects the
	 * following parameters:
	 *
	 * <ul>
	 *	<li><b>$theRecord</b>: The record to be inserted.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 *	<li><b>$doMany</b>: <tt>TRUE</tt> provided many records, <tt>FALSE</tt> provided
	 * 		one record.
	 * </ul>
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array|object			$theRecord			The record to be inserted.
	 * @param boolean				$doMany				Single or multiple records flag.
	 * @param array					$theOptions			Collection native options.
	 * @return mixed|array			The record's unique identifier(s).
	 *
	 * @uses Database()
	 * @uses Connection()
	 * @uses ArangoDocument::createFromArray()
	 * @uses ArangoDocumentHandlern::save()
	 *
	 * @example
	 * // Insert one record.
	 * $collection->insert( $record, FALSE );<br/>
	 * // Insert many records.
	 * $collection->insert( $records, TRUE );
	 */
	protected function doInsert( $theRecord, $doMany, $theOptions = NULL )
	{
		//
		// Create document handler.
		//
		$handler = new ArangoDocumentHandler( $this->Database()->Connection() );

		//
		// Handle many documents.
		//
		if( $doMany )
		{
			//
			// Init local storage.
			//
			$ids = [];

			//
			// Iterate documents.
			//
			foreach( $theRecord as $record )
			{
				//
				// Convert to document.
				//
				if( is_array( $record ) )
					$record = ArangoDocument::createFromArray( $record );

				//
				// Insert document.
				//
				$ids[] = $handler->save( $this->Connection(), $record );
			}

			return $ids;															// ==>

		} // Many documents.

		//
		// Convert to document.
		//
		if( is_array( $theRecord ) )
			$theRecord = ArangoDocument::createFromArray( $theRecord );

		return $documentHandler->save( $this->Connection(), $theRecord );			// ==>

	} // doInsert.


	/*===================================================================================
	 *	doUpdate																		*
	 *==================================================================================*/

	/**
	 * <h4>Update one or more records.</h4>
	 *
	 * This method should update the first or all records matching the provided search
	 * criteria, the method expects the following parameters:
	 *
	 * <ul>
	 *    <li><b>$theCriteria</b>: The modification criteria.
	 *    <li><b>$theFilter</b>: The selection criteria.
	 *    <li><b>$doMany</b>: <tt>TRUE</tt> update all records, <tt>FALSE</tt> update one
	 *        record.
	 *    <li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theCriteria		The modification criteria.
	 * @param mixed					$theFilter			The selection criteria.
	 * @param boolean				$doMany				Single or multiple records flag.
	 * @param array					$theOptions			Collection native options.
	 * @return int					The number of modified records.
	 *
	 * @uses Connection()
	 * @uses \ArangoDB\Collection::updateOne()
	 * @uses \ArangoDB\Collection::updateMany()
	 *
	 * @see kMONGO_OPTS_CL_UPDATE
	 *
	 * @example
	 * // Update one record.
	 * $collection->update( $criteria, $query, FALSE );<br/>
	 * // Insert many records.
	 * $collection->update( $criteria, $query, TRUE );
	 */
	protected function doUpdate( $theCriteria,
								 $theFilter,
								 $doMany,
								 $theOptions = NULL )
	{
		//
		// Create selection statement.
		//
		$statement = new ArangoStatement( $this->Database()->Connection(), $theFilter );

		//
		// Execute the statement.
		//
		$cursor = $statement->execute();

		//
		// Handle many documents.
		//
		if( $doMany )
		{
			//
			// Init local storage.
			//
			$ids = [];
			$count = $cursor->getCount();

			//
			// Iterate documents.
			//
			foreach( $cursor as $document )
			{
				//
				// Update document.
				//
				foreach( $theCriteria as $key => $value )
				{
					if( $value !== NULL )
						$document->set( $key, $value );
					else
						unset( $document->$key );
				}

				//
				// Update document.
				//
				$handler->update( $document );
			}

			return $count;															// ==>

		} // Many documents.

		//
		// Get first document.
		//
		$document = $cursor->current();

		//
		// Update document.
		//
		foreach( $theCriteria as $key => $value )
		{
			if( $value !== NULL )
				$document->set( $key, $value );
			else
				unset( $document->$key );
		}

		//
		// Update document.
		//
		$handler->update( $document );

		return 1;																	// ==>

	} // doUpdate.


	/*===================================================================================
	 *	doReplace																		*
	 *==================================================================================*/

	/**
	 * <h4>Replace a record.</h4>
	 *
	 * This method should replace the matching provided record, the method expects the
	 * following parameters:
	 *
	 * <ul>
	 *	<li><b>$theRecord</b>: The replacement record.
	 *	<li><b>$theFilter</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 * </ul>
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theRecord			The replacement record.
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Collection native options.
	 * @return int					The number of modified records.
	 *
	 * @uses Connection()
	 * @uses \ArangoDB\Collection::replaceOne()
	 *
	 * @see kMONGO_OPTS_CL_REPLACE
	 *
	 * @example
	 * // Update one record.
	 * $collection->replace( $record, $query, FALSE );<br/>
	 * // Insert many records.
	 * $collection->replace( $record, $query, TRUE );
	 */
	protected function doReplace( $theRecord, $theFilter, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_CL_REPLACE;

		//
		// Replace a record.
		//
		$result = $this->Connection()->replaceOne( $theFilter, $theRecord, $theOptions );

		return $result->getModifiedCount();											// ==>

	} // doReplace.


	/*===================================================================================
	 *	doDelete																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records.</h4>
	 *
	 * This method should delete the first or all records matching the provided search
	 * criteria, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFilter</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 *	<li><b>$doMany</b>: <tt>TRUE</tt> delete all records, <tt>FALSE</tt> delete first
	 * 		record.
	 * </ul>
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param boolean				$doMany				Single or multiple records flag.
	 * @param array					$theOptions			Collection native options.
	 * @return int					The number of deleted records.
	 *
	 * @uses Connection()
	 * @uses \ArangoDB\Collection::deleteOne()
	 * @uses \ArangoDB\Collection::deleteMany()
	 *
	 * @see kMONGO_OPTS_CL_DELETE
	 *
	 * @example
	 * // Delete first record.
	 * $collection->delete( $query, FALSE );<br/>
	 * // Delete all records.
	 * $collection->delete( $query, TRUE );
	 */
	protected function doDelete( $theFilter, $doMany, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		if( $theOptions === NULL )
			$theOptions = kMONGO_OPTS_CL_DELETE;

		//
		// Delete one or more records.
		//
		$result = ( $doMany )
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
	 * This method should find the first or all records matching the provided search
	 * criteria, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFilter</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options representing driver native options.
	 *	<li><b>$doMany</b>: <tt>TRUE</tt> return all records, <tt>FALSE</tt> return first
	 * 		record.
	 * </ul>
	 *
	 * This method assumes that the server is connected, it is the responsibility of the
	 * caller to ensure this.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param boolean				$doMany				Single or multiple records flag.
	 * @param array					$theOptions			Collection native options.
	 * @return Iterator				The found records.
	 *
	 * @uses Connection()
	 * @uses \ArangoDB\Collection::find()
	 * @uses \ArangoDB\Collection::findOne()
	 *
	 * @see kMONGO_OPTS_CL_FIND
	 *
	 * @example
	 * // Find first record.
	 * $collection->find( $query, FALSE );<br/>
	 * // Find all records.
	 * $collection->find( $query, TRUE );
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
		// Normalise limits.
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
		// Get collection handler.
		//
		$handler = new ArangoCollectionHandler( $this->Database()->Connection() );

		//
		// Get cursor.
		//
		return $handler->byExample(
				$this->Connection()->getId(), $theDocument, $theOptions );			// ==>

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
	 *
	 * @example
	 * // Query first record.
	 * $collection->find( [ '$gt' => [ 'age' => 20 ] ], [ '$start' => 0, '$limit' => 1 ] );<br/>
	 * // Query all records.<br/>
	 * $collection->find( [ '$gt' => [ 'age' => 20 ] ], [ '$start' => 0 ] );
	 */
	protected function doFindByQuery( $theQuery, $theOptions )
	{
		//
		// Create statement.
		//
		$statement
			= new ArangoStatement(
				$this->Database()->Connection(),
				array( 'query' => $theQuery ) );

		return $statement->execute();												// ==>

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
	 *
	 * @example
	 * // Count records.
	 * $collection->count( [ '$gt' => [ 'age' => 20 ] ] );
	 */
	protected function doCountByExample( $theDocument, $theOptions )
	{
		return $this->doFindByExample( $theDocument, $theOptions )->getCount();		// ==>

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
	 *
	 * @example
	 * // Count records.
	 * $collection->count( [ '$gt' => [ 'age' => 20 ] ] );
	 */
	protected function doCountByQuery( $theDocument, $theOptions )
	{
		return $this->doFindByQuery( $theDocument, $theOptions )->getCount();		// ==>

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
	 *
	 * @example
	 * // Find first record.
	 * $collection->find( $query, [ '$doAll' => FALSE ] );<br/>
	 * // Find all records.<br/>
	 * $collection->find( $query, [ '$doAll' => TRUE ] );
	 */
	protected function doMapReduce( $thePipeline, $theOptions )
	{
		return $this->doFindByQuery( $thePipeline, $theOptions );					// ==>

	} // doMapReduce.


} // class Collection.


?>