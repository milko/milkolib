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
 *							PUBLIC DOCUMENT MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewDocument																		*
	 *==================================================================================*/

	/**
	 * <h4>Convert native data to standard document.</h4>
	 *
	 * We overload this method by casting the provided data into an array, since MongoDB
	 * documents are derived from the ArrayObject class.
	 *
	 * @param mixed						$theData			Database native document.
	 * @param string					$theClass			Expected class name.
	 * @return \Milko\PHPLib\Container	Standard document object.
	 *
	 * @uses ClassOffset()
	 */
	public function NewDocument($theData, $theClass = NULL )
	{
		//
		// Convert document to array.
		//
		$document = (array)$theData;

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
	 *	NewNativeDocument																*
	 *==================================================================================*/

	/**
	 * <h4>Convert a standard document to native data.</h4>
	 *
	 * We overload this method to return BSONDocument.
	 *
	 * @param Document				$theDocument		Document to be converted.
	 * @return mixed				Database native object.
	 *
	 * @uses \Milko\PHPLib\Container::toArray()
	 */
	public function NewNativeDocument( \Milko\PHPLib\Container $theDocument )
	{
		return new \MongoDB\Model\BSONDocument( $theDocument->toArray() );			// ==>

	} // NewNativeDocument.


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
		$document = new \Milko\PHPLib\Container( (array)$theDocument );

		//
		// Check document key.
		//
		$key = $document[ $this->KeyOffset() ];
		if( $key === NULL )
			throw new \InvalidArgumentException (
				"Data is missing the document key." );							// !@! ==>

		//
		// Add document key.
		//
		$handle[] = $key;

		return $handle;																// ==>

	} // NewDocumentHandle.



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
			$theDocument = [];
		elseif( $theDocument instanceof \Milko\PHPLib\Container )
			$theDocument = $theDocument->toArray();
		else
			$theDocument = (array)$theDocument;

		return $this->Connection()->count( $theDocument );							// ==>

	} // CountByExample.


	/*===================================================================================
	 *	CountByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Count by example.</h4>
	 *
	 * TWe overload this method by calling {@link CountByExample()}.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @return int					The found records count.
	 */
	public function CountByQuery( $theQuery = NULL )
	{
		return $this->CountByExample( $theQuery );									// ==>

	} // CountByQuery.


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
	 * @uses doMapReduce()
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
	 * @param mixed					$theDocument		The document(s) to be inserted.
	 * @param array					$theOptions			Insert options.
	 * @return mixed				The document's unique identifier(s).
	 *
	 * @uses Connection()
	 * @uses NewNativeDocument()
	 * @uses \MongoDB\Collection::insertOne()
	 * @uses \MongoDB\Collection::insertMany()
	 * @see kTOKEN_OPT_MANY
	 */
	protected function doInsert( $theDocument, array $theOptions )
	{
		//
		// Convert data to be inserted.
		//
		if( $theOptions[ kTOKEN_OPT_MANY ] )
		{
			//
			// Init local storage.
			//
			$data = [];

			//
			// Iterate documents.
			//
			foreach( $theDocument as $document )
				$data[] = ( $document instanceof \Milko\PHPLib\Container )
						? $this->NewNativeDocument( $document )
						: (array)$document;

		} // Many documents.

		//
		// Handle single document.
		//
		else
			$data = ( $theDocument instanceof \Milko\PHPLib\Container )
				  ? $this->NewNativeDocument( $theDocument )
				  : (array)$theDocument;

		//
		// Insert one or more records.
		//
		$result = ( $theOptions[ kTOKEN_OPT_MANY ] )
				? $this->Connection()->insertMany( $data )
				: $this->Connection()->insertOne( $data );

		return ( $theOptions[ kTOKEN_OPT_MANY ] ) ? $result->getInsertedIds()		// ==>
												  : $result->getInsertedId();		// ==>

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
		// Insert one or more records.
		//
		$result = ( $theOptions[ kTOKEN_OPT_MANY ] )
				? $this->Connection()->updateMany( $theFilter, $theCriteria )
				: $this->Connection()->updateOne( $theFilter, $theCriteria );

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
		// Normalise container.
		//
		if( $theDocument instanceof \Milko\PHPLib\Container )
			$theDocument = $theDocument->toArray();

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
				$this->Connection()->replaceOne(
					[ $this->KeyOffset() => $record[ $this->KeyOffset() ] ], $theDocument );

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
	 * @uses cursorToArray()
	 * @uses \MongoDB\Collection::find()
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
			//
			if( ! $this->Connection()->count( $filter ) )
				return [];															// ==>

			return
				$this->formatCursor(
					$result, $theOptions[ kTOKEN_OPT_FORMAT ] );					// ==>
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
			}

			//
			// Invalid format code.
			//
			throw new \InvalidArgumentException (
				"Invalid conversion format code." );							// !@! ==>

		} // Found document.

		return $result;																// ==>

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
	 * We strip the <tt>'$start'</tt> and <tt>'$limit'</tt> parameters from the provided
	 * options and set respectively the <tt>skip</tt> and <tt>limit</tt> native options.
	 *
	 * @param mixed					$theDocument		The example document.
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found records.
	 * @throws \InvalidArgumentException
	 *
	 * @uses Connection()
	 * @uses cursorToArray()
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
		else
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
		// Format result.
		//
		switch( $theOptions[ kTOKEN_OPT_FORMAT ] )
		{
			case kTOKEN_OPT_FORMAT_NATIVE:
				return $result;														// ==>

			case kTOKEN_OPT_FORMAT_STANDARD:
			case kTOKEN_OPT_FORMAT_HANDLE:
				return $this->formatCursor(
							$result, $theOptions[ kTOKEN_OPT_FORMAT ] );			// ==>
		}

		//
		// Invalid format code.
		//
		throw new \InvalidArgumentException (
			"Invalid conversion format code." );								// !@! ==>

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
	 * @uses FindByExample()
	 */
	protected function doFindByQuery( $theQuery, array $theOptions )
	{
		return $this->FindByExample( $theQuery, $theOptions );						// ==>

	} // doFindByQuery.


	/*===================================================================================
	 *	doDeleteByKey																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records by key.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::deleteOne()} method
	 * to delete a single document and {@link \MongoDB\Collection::deleteMany()} method to
	 * delete all selected records.
	 *
	 * @param mixed					$theKey				The document key(s).
	 * @param array					$theOptions			Find options.
	 * @return int					The number of deleted records.
	 *
	 * @uses KeyOffset()
	 * @uses Connection()
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
	 * @uses KeyOffset()
	 * @uses Connection()
	 * @see kTOKEN_OPT_MANY
	 */
	protected function doDeleteByExample( $theDocument, array $theOptions )
	{
		//
		// Normalise filter.
		//
		if( $theDocument === NULL )
			$theDocument = [];
		elseif( ! is_array( $theDocument ) )
			$theDocument = (string)$theDocument;

		//
		// Delete one or more records.
		//
		$result = ( $theOptions[ kTOKEN_OPT_MANY ] )
			? $this->Connection()->deleteMany( $theDocument )
			: $this->Connection()->deleteOne( $theDocument );

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



} // class Collection.


?>
