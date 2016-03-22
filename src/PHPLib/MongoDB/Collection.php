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
	 * @uses Connection()
	 * @uses \MongoDB\Collection::deleteMany()
	 */
	public function Truncate()
	{
		$this->Connection()->deleteMany( [] );

	} // Truncate.


	/*===================================================================================
	 *	Drop																			*
	 *==================================================================================*/

	/**
	 * <h4>Drop the current collection.</h4>
	 *
	 * We overload this method to call the native object's method.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::drop()
	 */
	public function Drop()
	{
		$this->Connection()->drop();

	} // Drop.



/*=======================================================================================
 *																						*
 *							PUBLIC DOCUMENT INSTANTIATION INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewNativeDocument																*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database document.</h4>
	 *
	 * We overload this method to return BSONDocument.
	 *
	 * @param mixed					$theData			Document data.
	 * @return mixed				Database native object.
	 *
	 * @uses \Milko\PHPLib\Container::toArray()
	 * @see kTOKEN_OPT_FORMAT
	 * @see kTOKEN_OPT_FORMAT_NATIVE
	 */
	public function NewNativeDocument( $theData )
	{
		//
		// Handle native type.
		//
		if( $theDocument instanceof \MongoDB\Model\BSONDocument )
			return $theDocument;													// ==>

		//
		// Handle container.
		//
		if( $theDocument instanceof \Milko\PHPLib\Container )
			return new \MongoDB\Model\BSONDocument( $theDocument->toArray() );		// ==>

		return new \MongoDB\Model\BSONDocument( (array)$theDocument );				// ==>

	} // NewNativeDocument.


	/*===================================================================================
	 *	NewDocument																		*
	 *==================================================================================*/

	/**
	 * <h4>Return a {@link Document} instance.</h4>
	 *
	 * We overload this method to return a {@link \Milko\PHPLib\Document} instance of the
	 * correct class, or a {@link \Milko\PHPLib\Container} instance.
	 *
	 * @param mixed						$theData			Database native document.
	 * @param string					$theClass			Expected class name.
	 * @return \Milko\PHPLib\Container	Standard document object.
	 *
	 * @uses ClassOffset()
	 */
	public function NewDocument( $theData, $theClass = NULL )
	{
		//
		// Convert document to array.
		//
		$document = ( $theData instanceof Container )
				  ? $theData->toArray()
				  : (array)$theData;

		//
		// Use provided class name.
		//
		if( $theClass !== NULL )
		{
			$theClass = (string)$theClass;
			return new $theClass( $this, $document );								// ==>
		}

		//
		// Use class in data.
		//
		if( array_key_exists( $this->ClassOffset(), $document ) )
		{
			$class = $document[ $this->ClassOffset() ];
			return new $class( $this, $document );									// ==>
		}

		return new \Milko\PHPLib\Container( $document );							// ==>

	} // NewDocument.


	/*===================================================================================
	 *	NewDocumentHandle																*
	 *==================================================================================*/

	/**
	 * <h4>Convert a document to a document handle.</h4>
	 *
	 * We overload this method to return an array of two elements: the first represents the
	 * collection name, the second represents the document key ({@link KeyOffset()}.
	 *
	 * Note that if the provided data doesn't feature the {@link KeyOffset()} property, the
	 * method will raise an exception, since it will be impossible to resolve the document.
	 *
	 * @param mixed					$theDocument		Document to reference.
	 * @return mixed				Document handle.
	 * @throws \InvalidArgumentException
	 *
	 * @uses KeyOffset()
	 * @uses collectionName()
	 */
	public function NewDocumentHandle( $theDocument )
	{
		//
		// Init handle collection.
		//
		$handle = [ $this->collectionName() ];

		//
		// Convert to container.
		//
		$document =
			new \Milko\PHPLib\Container(
				( $theData instanceof \Milko\PHPLib\Container ) ? $theData->toArray()
					: (array)$theData );

		//
		// Check document key.
		//
		if( ($key = $document[ $this->KeyOffset() ]) === NULL )
			throw new \InvalidArgumentException (
				"Data is missing the document key." );							// !@! ==>

		//
		// Add document key.
		//
		$handle[] = $key;

		return $handle;																// ==>

	} // NewDocumentHandle.


	/*===================================================================================
	 *	NewDocumentKey																	*
	 *==================================================================================*/

	/**
	 * <h4>Return document key.</h4>
	 *
	 * We overload this method to extract the document key from the provided data.
	 *
	 * @param mixed					$theDocument		Document to reference.
	 * @return mixed				Document handle.
	 * @throws \InvalidArgumentException
	 *
	 * @uses KeyOffset()
	 */
	public function NewDocumentKey( $theDocument )
	{
		//
		// Return key.
		//
		if( array_key_exists( $this->KeyOffset(), $document = (array)$theDocument ) )
			return $document[ $this->KeyOffset() ];									// ==>

		throw new \InvalidArgumentException (
			"Data is missing the document key." );								// !@! ==>

	} // NewDocumentKey.



/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET DECLARATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	KeyOffset																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the document key offset.</h4>
	 *
	 * We overload this method to use the {@link kTAG_MONGO_KEY} constant.
	 *
	 * @return string				Document key offset.
	 *
	 * @see kTAG_MONGO_KEY
	 */
	public function KeyOffset()									{	return kTAG_MONGO_KEY;	}


	/*===================================================================================
	 *	ClassOffset																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the document class offset.</h4>
	 *
	 * We overload this method to use the {@link kTAG_MONGO_CLASS} constant.
	 *
	 * @return string				Document class offset.
	 *
	 * @see kTAG_MONGO_CLASS
	 */
	public function ClassOffset()							{	return kTAG_MONGO_CLASS;	}


	/*===================================================================================
	 *	RevisionOffset																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the document revision offset.</h4>
	 *
	 * We overload this method to use the {@link kTAG_MONGO_REVISION} constant.
	 *
	 * @return string				Document revision offset.
	 *
	 * @see kTAG_MONGO_REVISION
	 */
	public function RevisionOffset()						{	return kTAG_MONGO_REVISION;	}



/*=======================================================================================
 *																						*
 *							PUBLIC RECORD MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	RecordCount																		*
	 *==================================================================================*/

	/**
	 * <h4>Count documents.</h4>
	 *
	 * We overload this method to use the collection count() method without a filter.
	 *
	 * @return int					The number of records in the collection.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::count()
	 */
	public function RecordCount()
	{
		return $this->Connection()->count();										// ==>

	} // RecordCount.


	/*===================================================================================
	 *	CountByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Count by example.</h4>
	 *
	 * In this class we overload this method to use the <tt>count()</tt> method of the
	 * Mongo Collection.
	 *
	 * @param mixed					$theDocument		The example document.
	 * @return int					The found records count.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::count()
	 */
	public function CountByExample( $theDocument = NULL )
	{
		//
		// Normalise filter.
		//
		if( $theDocument === NULL )
			$document = [];
		elseif( $theDocument instanceof \Milko\PHPLib\Container )
			$document = $theDocument->toArray();
		else
			$document = (array)$theDocument;

		return $this->Connection()->count( $document );								// ==>

	} // CountByExample.


	/*===================================================================================
	 *	CountByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Count by query.</h4>
	 *
	 * TWe overload this method by calling {@link CountByExample()}.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @return int					The found records count.
	 *
	 * @uses CountByExample()
	 */
	public function CountByQuery( $theQuery = NULL )
	{
		return $this->CountByExample( $theQuery );									// ==>

	} // CountByQuery.



/*=======================================================================================
 *																						*
 *							PUBLIC AGGREGATION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	MapReduce																		*
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
	 * @param array					$theOptions			Query options.
	 * @return array				The result set.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::aggregate()
	 */
	public function MapReduce( $thePipeline, $theOptions = [] )
	{
		//
		// Init local storage.
		//
		$options = [];

		//
		// Handle options.
		//
		if( count( $theOptions ) )
		{
			//
			// Force skip if limit is there.
			//
			if( array_key_exists( kTOKEN_OPT_LIMIT, $theOptions )
			 && (! array_key_exists( kTOKEN_OPT_SKIP, $theOptions )) )
				$theOptions[ kTOKEN_OPT_SKIP ] = 0;

			//
			// Convert to native options.
			//
			if( array_key_exists( kTOKEN_OPT_SKIP, $theOptions ) )
				$options[ 'skip' ] = $theOptions[ kTOKEN_OPT_SKIP ];
			if( array_key_exists( kTOKEN_OPT_LIMIT, $theOptions ) )
				$options[ 'limit' ] = $theOptions[ kTOKEN_OPT_LIMIT ];
		}

		//
		// Serialise result.
		//
		$result = [];
		foreach( $this->Connection()->aggregate( $thePipeline, $options ) as $record )
			$result[] = (array) $record;

		return $result;																// ==>

	} // MapReduce.



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
	 * We overload this method to have the database connection generate a collection.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Driver native options.
	 * @return mixed				Native collection object.
	 *
	 * @uses Database()
	 * @uses \MongoDB\Database::selectCollection()
	 */
	protected function collectionNew( $theCollection, $theOptions = [] )
	{
		return
			$this->
				Database()->
					Connection()->
						selectCollection( (string)$theCollection, $theOptions );	// ==>

	} // collectionNew.


	/*===================================================================================
	 *	collectionName																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the collection name.</h4>
	 *
	 * We overload this method to probe the native collection object.
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
 *							PROTECTED DOCUMENT INSERT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doInsertContainer																*
	 *==================================================================================*/

	/**
	 * <h4>Insert a container.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::insertOne()} method.
	 *
	 * @param \Milko\PHPLib\Container	$theDocument		The document to insert.
	 * @return mixed					The document's key.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::insertOne()
	 * @uses \MongoDB\Cursor::getInsertedId()
	 */
	protected function doInsertContainer( \Milko\PHPLib\Container $theDocument )
	{
		return
			$this->Connection()->insertOne( $theDocument->toArray() )
				->getInsertedId();													// ==>

	} // doInsertContainer.


	/*===================================================================================
	 *	doInsertArray																	*
	 *==================================================================================*/

	/**
	 * <h4>Insert an array.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::insertOne()} method.
	 *
	 * @param array					$theDocument		The document to insert.
	 * @return mixed				The document's key.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::insertOne()
	 * @uses \MongoDB\Cursor::getInsertedId()
	 */
	protected function doInsertArray( array $theDocument )
	{
		return
			$this->Connection()->insertOne( $theDocument )
				->getInsertedId();													// ==>

	} // doInsertArray.


	/*===================================================================================
	 *	doInsertBulk																	*
	 *==================================================================================*/

	/**
	 * <h4>Insert a list of documents.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::insertMany()} method.
	 *
	 * @param array					$theList			The documents list.
	 * @return array				The document keys.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::insertMany()
	 * @uses \MongoDB\Cursor::getInsertedIds()
	 */
	protected function doInsertBulk( array $theList )
	{
		return
			$this->Connection()->insertMany( $theList )
				->getInsertedIds();													// ==>

	} // doInsertBulk.



/*=======================================================================================
 *																						*
 *							PROTECTED DOCUMENT DELETE INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doDeleteContainer																*
	 *==================================================================================*/

	/**
	 * <h4>Delete a container.</h4>
	 *
	 * We overload this method to call the {@link \MongoDB\Collection::deleteOne()} method;
	 * we also check if the provided document has its key, if that is not the case, we
	 * raise an exception.
	 *
	 * @param \Milko\PHPLib\Container	$theDocument		The document to delete.
	 * @return int						The number of deleted documents.
	 * @throws \InvalidArgumentException
	 *
	 * @uses Connection()
	 * @uses KeyOffset()
	 * @uses \MongoDB\Collection::deleteOne()
	 * @uses \MongoDB\Cursor::getDeletedCount()
	 */
	protected function doDeleteContainer( \Milko\PHPLib\Container $theDocument )
	{
		//
		// Check document key.
		//
		if( ($key = $theDocument[ $this->KeyOffset() ]) !== NULL )
		{
			//
			// Delete.
			//
			$count =
				$this->Connection()->deleteOne( [ $this->KeyOffset() => $key ] )
					->getDeletedCount();

			//
			// Set document state.
			//
			if( $count )
			{
				$theDocument->IsPersistent( FALSE, $this );
				$theDocument->IsModified( TRUE, $this );
			}

			return $count;															// ==>
		}

		throw new \InvalidArgumentException (
			"Document is missing its key." );									// !@! ==>

	} // doDeleteContainer.


	/*===================================================================================
	 *	doDeleteArray																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete an array.</h4>
	 *
	 * We overload this method to call the {@link \MongoDB\Collection::deleteOne()} method;
	 * we also check if the provided document has its key, if that is not the case, we
	 * raise an exception.
	 *
	 * @param array					$theDocument		The document to delete.
	 * @return int					The number of deleted documents.
	 */
	protected function doDeleteArray( array $theDocument )
	{
		//
		// Check document key.
		//
		if( ($key = $theDocument[ $this->KeyOffset() ]) !== NULL )
			return
				$this->Connection()->deleteOne( [ $this->KeyOffset() => $key ] )
					->getDeletedCount();											// ==>

		throw new \InvalidArgumentException (
			"Document is missing its key." );									// !@! ==>

	} // doDeleteArray.


	/*===================================================================================
	 *	doDeleteByKey																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records by key.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::deleteOne()} method
	 * to delete a single document and {@link \MongoDB\Collection::deleteMany()} method to
	 * delete a list of documents.
	 *
	 * @param mixed					$theKey				The document key(s).
	 * @param array					$theOptions			Find options.
	 * @return int					The number of deleted records.
	 *
	 * @uses KeyOffset()
	 * @uses Connection()
	 * @uses \MongoDB\Collection::deleteOne()
	 * @uses \MongoDB\Collection::deleteMany()
	 * @see kTOKEN_OPT_MANY
	 */
	protected function doDeleteByKey( $theKey, array $theOptions )
	{
		//
		// Set selection filter.
		//
		$filter = ( $theOptions[ kTOKEN_OPT_MANY ] )
				? [ $this->KeyOffset() => [ '$in' => (array)$theKey ] ]
				: [ $this->KeyOffset() => $theKey ];

		//
		// Delete one or more records.
		//
		$result = ( $theOptions[ kTOKEN_OPT_MANY ] )
				? $this->Connection()->deleteMany( $filter )
				: $this->Connection()->deleteOne( $filter );

		return $result->getDeletedCount();											// ==>

	} // doDeleteByKey.


	/*===================================================================================
	 *	doDeleteByExample																*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records by example.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::deleteOne()} method
	 * to delete a single document and {@link \MongoDB\Collection::deleteMany()} method to
	 * delete all selected records.
	 *
	 * @param mixed					$theDocument		The example document.
	 * @param array					$theOptions			Delete options.
	 * @return int					The number of deleted records.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::deleteOne()
	 * @uses \MongoDB\Collection::deleteMany()
	 * @see kTOKEN_OPT_MANY
	 */
	protected function doDeleteByExample( $theDocument, array $theOptions )
	{
		//
		// Normalise filter.
		//
		if( $theDocument === NULL )
			$document = [];
		elseif( $theDocument instanceof \Milko\PHPLib\Container )
			$document = $theDocument->toArray();
		else
			$document = (array)$theDocument;

		//
		// Delete one or more records.
		//
		$result = ( $theOptions[ kTOKEN_OPT_MANY ] )
				? $this->Connection()->deleteMany( $document )
				: $this->Connection()->deleteOne( $document );

		return $result->getDeletedCount();											// ==>

	} // doDeleteByExample.


	/*===================================================================================
	 *	doDeleteByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records by query.</h4>
	 *
	 * We overload this method to call the {@link doDeleteByExample()} method, since it
	 * treats the example document as a filter.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @param array					$theOptions			Delete options.
	 * @return int					The number of deleted records.
	 *
	 * @uses doDeleteByExample()
	 */
	protected function doDeleteByQuery( $theQuery, array $theOptions )
	{
		return $this->doDeleteByExample( $theQuery, $theOptions );					// ==>

	} // doDeleteByQuery.



/*=======================================================================================
 *																						*
 *							PROTECTED UPDATE MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



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
	 * @param mixed					$theCriteria		The modification criteria.
	 * @param array					$theOptions			Update options.
	 * @return int					The number of modified records.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::updateOne()
	 * @uses \MongoDB\Collection::updateMany()
	 * @see kTOKEN_OPT_MANY
	 */
	protected function doUpdate( $theFilter, $theCriteria, array $theOptions )
	{
		//
		// Normalise filter.
		//
		if( $theFilter === NULL )
			$theFilter = [];

		//
		// Insert one or more records.
		//
		$result = ( ! $theOptions[ kTOKEN_OPT_MANY ] )
				? $this->Connection()->updateOne( $theFilter, $theCriteria )
				: $this->Connection()->updateMany( $theFilter, $theCriteria );

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
	 * @param mixed					$theDocument		The replacement document.
	 * @param array					$theOptions			Replace options.
	 * @return int					The number of replaced records.
	 *
	 * @uses Connection()
	 * @uses KeyOffset()
	 * @uses \MongoDB\Collection::replaceOne()
	 * @see kTOKEN_OPT_MANY
	 */
	protected function doReplace( $theFilter, $theDocument, array $theOptions )
	{
		//
		// Normalise filter.
		//
		if( $theFilter === NULL )
			$theFilter = [];

		//
		// Normalise container.
		//
		if( $theDocument instanceof \Milko\PHPLib\Container )
			$document = $theDocument->toArray();
		else
			$document = (array)$theDocument;

		//
		// Replace many records.
		//
		if( $theOptions[ kTOKEN_OPT_MANY ] )
		{
			//
			// Replace selection.
			//
			$count = 0;
			$cursor = $this->Connection()->find( $theFilter );
			foreach( $cursor as $record )
			{
				//
				// Convert to container.
				//
				$record = new \Milko\PHPLib\Container( (array)$record );

				//
				// Replace document.
				//
				$result =
					$this->Connection()->replaceOne(
						[ $this->KeyOffset() => $record[ $this->KeyOffset() ] ],
						$theDocument );

				//
				// Increment replaced count.
				//
				$count++;
			}

			return $count;															// ==>

		} // Many documents.

		//
		// Replace a single record.
		//
		$result = $this->Connection()->replaceOne( $theFilter, $theDocument );

		return $result->getModifiedCount();											// ==>
	
	} // doReplace.


	/*===================================================================================
	 *	doFindByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Find by ID.</h4>
	 *
	 * We implement this method to use the <tt>findOne</tt> method.
	 *
	 * @param mixed					$theKey				The document identifier.
	 * @param array					$theOptions			Delete options.
	 * @return mixed				The found document(s).
	 * @throws \InvalidArgumentException
	 *
	 * @uses KeyOffset()
	 * @uses Connection()
	 * @uses formatCursor()
	 * @uses NewDocument()
	 * @uses NewDocumentHandle()
	 * @uses \MongoDB\Collection::find()
	 * @uses \MongoDB\Collection::count()
	 * @uses \MongoDB\Collection::findOne()
	 * @see kTOKEN_OPT_MANY
	 * @see kTOKEN_OPT_FORMAT
	 */
	protected function doFindByKey($theKey, array $theOptions )
	{
		//
		// Set selection filter.
		//
		$filter = ( $theOptions[ kTOKEN_OPT_MANY ] )
				? [ $this->KeyOffset() => [ '$in' => (array)$theKey ] ]
				: [ $this->KeyOffset() => $theKey ];

		//
		// Make selection.
		//
		$result = ( $theOptions[ kTOKEN_OPT_MANY ] )
			? $this->Connection()->find( $filter )
			: $this->Connection()->findOne( $filter );

		//
		// Handle native result.
		//
		if( $theOptions[ kTOKEN_OPT_FORMAT ] == kTOKEN_OPT_FORMAT_NATIVE )
			return $result;															// ==>

		//
		// Handle multiple results.
		//
		if( $theOptions[ kTOKEN_OPT_MANY ] )
		{
			//
			// Handle no results.
			// For some reason the cursor doesn't seem to have the count() method.
			//
			if( ! $this->Connection()->count( $filter ) )
				return [];															// ==>

			//
			// Iterate cursor.
			//
			$list = [];
			foreach( $result as $document )
			{
				switch( $theOptions[ kTOKEN_OPT_FORMAT ] )
				{
					case kTOKEN_OPT_FORMAT_STANDARD:
						$list[] = $this->NewDocument( $document );
						break;

					case kTOKEN_OPT_FORMAT_HANDLE:
						$list[] = $this->NewDocumentHandle( $document );
						break;

					case kTOKEN_OPT_FORMAT_KEY:
						$list[] = $this->NewDocumentKey( $document );
						break;

					default:
						throw new \InvalidArgumentException (
							"Invalid conversion format code." );				// !@! ==>
				}
			}

			return $list;															// ==>
		}

		//
		// Handle found document.
		//
		if( $result !== NULL )
		{
			//
			// Format result.
			//
			switch( $theOptions[ kTOKEN_OPT_FORMAT ] )
			{
				case kTOKEN_OPT_FORMAT_STANDARD:
					return $this->NewDocument( $result );							// ==>

				case kTOKEN_OPT_FORMAT_HANDLE:
					return $this->NewDocumentHandle( $result );						// ==>

				case kTOKEN_OPT_FORMAT_KEY:
					return $this->NewDocumentKey( $result );						// ==>
			}

			//
			// Invalid format code.
			//
			throw new \InvalidArgumentException (
				"Invalid conversion format code." );							// !@! ==>

		} // Found document.

		return NULL;																// ==>

	} // doFindByKey.


	/*===================================================================================
	 *	doFindByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Find by example the first or all records.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::find()} method, the
	 * provided example document will be used as the actual selection criteria.
	 *
	 * We convert the {@link kTOKEN_OPT_SKIP} and {@link kTOKEN_OPT_LIMIT} parameters into
	 * respectively the <tt>skip</tt> and <tt>limit</tt> native options.
	 *
	 * @param mixed					$theDocument		The example document.
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found records.
	 * @throws \InvalidArgumentException
	 *
	 * @uses Connection()
	 * @uses formatCursor()
	 * @uses \MongoDB\Collection::find()
	 * @see kTOKEN_OPT_SKIP
	 * @see kTOKEN_OPT_LIMIT
	 * @see kTOKEN_OPT_FORMAT
	 */
	protected function doFindByExample( $theDocument, array $theOptions )
	{
		//
		// Init local storage.
		//
		$options = [];

		//
		// Normalise filter.
		//
		if( $theDocument === NULL )
			$theDocument = [];
		elseif( $theDocument instanceof \Milko\PHPLib\Container )
			$theDocument = $theDocument->toArray();
		elseif( ! is_array( $theDocument ) )
			$theDocument = (array)$theDocument;

		//
		// Handle options.
		//
		if( count( $theOptions ) )
		{
			//
			// Force skip if limit is there.
			//
			if( array_key_exists( kTOKEN_OPT_LIMIT, $theOptions )
			 && (! array_key_exists( kTOKEN_OPT_SKIP, $theOptions )) )
				$theOptions[ kTOKEN_OPT_SKIP ] = 0;

			//
			// Convert to native options.
			//
			if( array_key_exists( kTOKEN_OPT_SKIP, $theOptions ) )
				$options[ 'skip' ] = $theOptions[ kTOKEN_OPT_SKIP ];
			if( array_key_exists( kTOKEN_OPT_LIMIT, $theOptions ) )
				$options[ 'limit' ] = $theOptions[ kTOKEN_OPT_LIMIT ];
		}

		//
		// Make selection.
		//
		$result = $this->Connection()->find( $theDocument, $options );

		//
		// Handle native result.
		//
		if( $theOptions[ kTOKEN_OPT_FORMAT ] == kTOKEN_OPT_FORMAT_NATIVE )
			return $result;															// ==>

		//
		// Iterate cursor.
		//
		$list = [];
		foreach( $result as $document )
		{
			switch( $theOptions[ kTOKEN_OPT_FORMAT ] )
			{
				case kTOKEN_OPT_FORMAT_STANDARD:
					$list[] = $this->NewDocument( $document );
					break;

				case kTOKEN_OPT_FORMAT_HANDLE:
					$list[] = $this->NewDocumentHandle( $document );
					break;

				case kTOKEN_OPT_FORMAT_KEY:
					$list[] = $this->NewDocumentKey( $document );
					break;

				default:
					throw new \InvalidArgumentException (
						"Invalid conversion format code." );				// !@! ==>
			}
		}

		return $list;															// ==>

	} // doFindByExample.


	/*===================================================================================
	 *	doFindByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Query the collection.</h4>
	 *
	 * We overload this method to use the {@link doFind()} method, since the latter method
	 * treats the example document as a query.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found records.
	 *
	 * @uses doFindByExample()
	 */
	protected function doFindByQuery( $theQuery, array $theOptions )
	{
		return $this->doFindByExample( $theQuery, $theOptions );					// ==>

	} // doFindByQuery.



} // class Collection.


?>
