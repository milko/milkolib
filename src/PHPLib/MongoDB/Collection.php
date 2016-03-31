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
		if( $theData instanceof \MongoDB\Model\BSONDocument )
			return $theData;														// ==>

		//
		// Handle container.
		//
		if( $theData instanceof \Milko\PHPLib\Container )
			return new \MongoDB\Model\BSONDocument( $theData->toArray() );			// ==>

		return new \MongoDB\Model\BSONDocument( (array)$theData );					// ==>

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
		$document = ( $theData instanceof \Milko\PHPLib\Container )
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
				( $theDocument instanceof \Milko\PHPLib\Container )
					? $theDocument->toArray()
					: (array)$theDocument );

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
		// Handle container.
		//
		if( $theDocument instanceof Container )
		{
			if( $theDocument->offsetExists( $this->KeyOffset() ) )
				return $theDocument[ $this->KeyOffset() ];							// ==>
		}

		//
		// Handle object or array.
		//
		else
		{
			$document = (array)$theDocument;
			if( array_key_exists( $this->KeyOffset(), $document ) )
				return $document[ $this->KeyOffset() ];								// ==>
		}

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


	/*===================================================================================
	 *	RelationSourceOffset															*
	 *==================================================================================*/

	/**
	 * <h4>Return the relationship source offset.</h4>
	 *
	 * We overload this method to use the {@link kTAG_MONGO_REL_FROM} constant.
	 *
	 * @return string				Relationship source offset.
	 */
	public function RelationSourceOffset()					{	return kTAG_MONGO_REL_FROM;	}


	/*===================================================================================
	 *	RelationDestinationOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Return the relationship destination offset.</h4>
	 *
	 * We overload this method to use the {@link kTAG_MONGO_REL_TO} constant.
	 *
	 * @return string				Relationship source offset.
	 */
	public function RelationDestinationOffset()				{	return kTAG_MONGO_REL_TO;	}



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
	 * @uses Connection()
	 * @uses \MongoDB\Collection::count()
	 */
	public function CountByQuery( $theQuery = NULL )
	{
		return $this->Connection()->count( $theQuery );								// ==>

	} // CountByQuery.



/*=======================================================================================
 *																						*
 *							PUBLIC AGGREGATION FRAMEWORK INTERFACE						*
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
	public function MapReduce( $thePipeline, $theOptions = NULL )
	{
		//
		// Init local storage.
		//
		$options = [];
		if( $theOptions === NULL )
			$theOptions = [];

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
	protected function collectionNew( $theCollection, $theOptions )
	{
		//
		// Init options.
		//
		if( $theOptions === NULL )
			$theOptions = [];

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
	 *	doInsertOne																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert a document.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::insertOne()} method.
	 *
	 * @param mixed					$theDocument		The document to be inserted.
	 * @return mixed				The inserted document's key.
	 *
	 * @uses Connection()
	 * @uses NewNativeDocument()
	 * @uses normalistInsertedDocument()
	 * @uses \Milko\PHPLib\Document::Validate()
	 * @uses \Milko\PHPLib\Document::StoreSubdocuments()
	 * @uses \MongoDB\Collection::insertOne()
	 * @uses \MongoDB\Cursor::getInsertedId()
	 */
	protected function doInsertOne( $theDocument )
	{
		//
		// Prepare document.
		//
		if( $theDocument instanceof \Milko\PHPLib\Document )
		{
			//
			// Validate document.
			//
			$theDocument->Validate();

			//
			// Store sub-documents.
			//
			$theDocument->StoreSubdocuments();
		}
		
		//
		// Convert to native document.
		//
		$document = $this->NewNativeDocument( $theDocument );

		//
		// Insert document.
		//
		$key = $this->Connection()->insertOne( $document )->getInsertedId();

		//
		// Normalise inserted document.
		//
		if( $theDocument instanceof \Milko\PHPLib\Container )
			$this->normaliseInsertedDocument( $theDocument, $document, $key );

		return $key;																// ==>

	} // doInsertOne.


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
	 *	doDeleteOne																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete a document.</h4>
	 *
	 * We overload this method to call the {@link \MongoDB\Collection::deleteOne()} method;
	 * we also check if the provided document has its key, if that is not the case, we
	 * raise an exception.
	 *
	 * @param mixed					$theDocument		The document to be deleted.
	 * @return mixed				The number of deleted documents.
	 */
	protected function doDeleteOne( $theDocument )
	{
		//
		// Convert document.
		//
		$document = $this->NewNativeDocument( $theDocument );

		//
		// Check document key.
		//
		if( $document->offsetExists( $this->KeyOffset() ) )
		{
			//
			// Get key.
			//
			$key = $document->offsetGet( $this->KeyOffset() );

			//
			// Delete document.
			//
			$count =
				$this->Connection()->deleteOne( [ $this->KeyOffset() => $key ] )
					->getDeletedCount();

			//
			// Normalise deleted document.
			//
			if( $theDocument instanceof \Milko\PHPLib\Container )
				$this->normaliseDeletedDocument( $theDocument );

			return $count;															// ==>

		} // Has key.

		throw new \InvalidArgumentException (
			"Document is missing its key." );									// !@! ==>

	} // doDeleteOne.


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
		// Delete.
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
		//
		// Delete.
		//
		$result = ( $theOptions[ kTOKEN_OPT_MANY ] )
				? $this->Connection()->deleteMany( $theQuery )
				: $this->Connection()->deleteOne( $theQuery );

		return $result->getDeletedCount();											// ==>

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
	 * If the provided document doesn't have its key ({@link KeyOffset()}), the method will
	 * not perform the replacement and return <tt>0</tt>.
	 *
	 * @param mixed					$theDocument		The replacement document.
	 * @return int					The number of replaced records.
	 *
	 * @uses Connection()
	 * @uses KeyOffset()
	 * @uses \MongoDB\Collection::replaceOne()
	 * @see kTOKEN_OPT_MANY
	 */
	protected function doReplace( $theDocument )
	{
		//
		// Convert document.
		//
		$document = $this->NewNativeDocument( $theDocument );
		if( $document->offsetExists( $this->KeyOffset() ) )
			return
				$this->Connection()->replaceOne(
					[ $this->KeyOffset() => $document->offsetGet( $this->KeyOffset() ) ],
					$document )
						->getModifiedCount();										// ==>

		return 0;																	// ==>

	} // doReplace.



/*=======================================================================================
 *																						*
 *							PROTECTED SELECTION MANAGEMENT INTERFACE					*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doFindByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Find by key.</h4>
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
	 * @uses NewDocument()
	 * @uses NewDocumentKey()
	 * @uses NewDocumentHandle()
	 * @uses normaliseSelectedDocument()
	 * @uses \MongoDB\Collection::find()
	 * @uses \MongoDB\Collection::count()
	 * @uses \MongoDB\Collection::findOne()
	 * @see kTOKEN_OPT_MANY
	 * @see kTOKEN_OPT_FORMAT
	 */
	protected function doFindByKey( $theKey, array $theOptions )
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
		// Convert to array.
		//
		if( ! $theOptions[ kTOKEN_OPT_MANY ] )
			$result = [ $result ];

		//
		// Iterate cursor.
		//
		$list = [];
		foreach( $result as $document )
		{
			//
			// Format document.
			//
			switch( $theOptions[ kTOKEN_OPT_FORMAT ] )
			{
				case kTOKEN_OPT_FORMAT_STANDARD:
					$tmp = $this->NewDocument( $document );
					$this->normaliseSelectedDocument( $tmp, $document );
					$list[] = $tmp;
					break;

				case kTOKEN_OPT_FORMAT_HANDLE:
					$list[] = $this->NewDocumentHandle( $document );
					break;

				case kTOKEN_OPT_FORMAT_KEY:
					$list[] = $this->NewDocumentKey( $document );
					break;

				default:
					throw new \InvalidArgumentException (
						"Invalid conversion format code." );					// !@! ==>
			}
		}

		if( $theOptions[ kTOKEN_OPT_MANY ] )
			return $list;															// ==>
		if( count( $list ) )
			return $list[ 0 ];														// ==>
		return NULL;																// ==>

	} // doFindByKey.


	/*===================================================================================
	 *	doFindByHandle																	*
	 *==================================================================================*/

	/**
	 * <h4>Find by handle.</h4>
	 *
	 * We implement this method to use the <tt>findOne</tt> method.
	 *
	 * @param mixed					$theHandle			The document handle(s).
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found document(s).
	 * @throws \InvalidArgumentException
	 *
	 * @uses collectionName()
	 * @uses collectionNew()
	 * @uses KeyOffset()
	 * @uses NewDocument()
	 * @uses NewDocumentKey()
	 * @uses NewDocumentHandle()
	 * @uses normaliseSelectedDocument()
	 * @uses \MongoDB\Collection::findOne()
	 * @see kTOKEN_OPT_MANY
	 * @see kTOKEN_OPT_FORMAT
	 */
	protected function doFindByHandle( $theHandle, array $theOptions )
	{
		//
		// Init local storage.
		//
		$list = [];

		//
		// Convert scalar to array.
		//
		if( ! $theOptions[ kTOKEN_OPT_MANY ] )
			$theHandle = [ $theHandle ];

		//
		// Iterate handles.
		//
		foreach( $theHandle as $handle )
		{
			//
			// Get collection.
			//
			if( $handle[ 0 ] == $this->collectionName() )
				$collection = $this;
			else
				$collection = $this->Database()->collectionRetrieve( $theHandle[ 0 ] );

			//
			// Get by key.
			//
			$found =
				$collection->Connection()->findOne(
					[ $collection->KeyOffset() => $handle[ 1 ] ] );
			if( $found !== NULL )
			{
				//
				// Format document.
				//
				switch( $theOptions[ kTOKEN_OPT_FORMAT ] )
				{
					case kTOKEN_OPT_FORMAT_STANDARD:
						$document = $this->NewDocument( $found );
						$this->normaliseSelectedDocument( $document, $found );
						$list[] = $document;
						break;

					case kTOKEN_OPT_FORMAT_NATIVE:
						$list[] = $found;
						break;

					case kTOKEN_OPT_FORMAT_HANDLE:
						$list[] = $this->NewDocumentHandle( $found );
						break;

					case kTOKEN_OPT_FORMAT_KEY:
						$list[] = $this->NewDocumentKey( $found );
						break;

					default:
						throw new \InvalidArgumentException (
							"Invalid conversion format code." );				// !@! ==>

				} // Formatted document.

			} // Found.

		} // Iterating handles.

		if( $theOptions[ kTOKEN_OPT_MANY ] )
			return $list;															// ==>
		if( count( $list ) )
			return $list[ 0 ];														// ==>
		return NULL;																// ==>

	} // doFindByHandle.


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
	 * @uses NewDocument()
	 * @uses NewDocumentKey()
	 * @uses NewDocumentHandle()
	 * @uses normaliseSelectedDocument()
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
			$filter = [];
		elseif( $theDocument instanceof \Milko\PHPLib\Container )
			$filter = $theDocument->toArray();
		else
			$filter = (array)$theDocument;

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
		$result = $this->Connection()->find( $filter, $options );

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
			//
			// Format document.
			//
			switch( $theOptions[ kTOKEN_OPT_FORMAT ] )
			{
				case kTOKEN_OPT_FORMAT_STANDARD:
					$tmp = $this->NewDocument( $document );
					$this->normaliseSelectedDocument( $tmp, $document );
					$list[] = $tmp;
					break;

				case kTOKEN_OPT_FORMAT_HANDLE:
					$list[] = $this->NewDocumentHandle( $document );
					break;

				case kTOKEN_OPT_FORMAT_KEY:
					$list[] = $this->NewDocumentKey( $document );
					break;

				default:
					throw new \InvalidArgumentException (
						"Invalid conversion format code." );					// !@! ==>
			}
		}

		return $list;																// ==>

	} // doFindByExample.


	/*===================================================================================
	 *	doFindByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Query the collection.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::find()} method, the
	 * provided example document will be used as the actual selection criteria.
	 *
	 * We convert the {@link kTOKEN_OPT_SKIP} and {@link kTOKEN_OPT_LIMIT} parameters into
	 * respectively the <tt>skip</tt> and <tt>limit</tt> native options.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found records.
	 *
	 * @uses Connection()
	 * @uses NewDocument()
	 * @uses NewDocumentKey()
	 * @uses NewDocumentHandle()
	 * @uses normaliseSelectedDocument()
	 * @uses \MongoDB\Collection::find()
	 * @see kTOKEN_OPT_SKIP
	 * @see kTOKEN_OPT_LIMIT
	 * @see kTOKEN_OPT_FORMAT
	 */
	protected function doFindByQuery( $theQuery, array $theOptions )
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
		// Make selection.
		//
		$result = $this->Connection()->find( $theQuery, $options );

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
			//
			// Format document.
			//
			switch( $theOptions[ kTOKEN_OPT_FORMAT ] )
			{
				case kTOKEN_OPT_FORMAT_STANDARD:
					$tmp = $this->NewDocument( $document );
					$this->normaliseSelectedDocument( $tmp, $document );
					$list[] = $tmp;
					break;

				case kTOKEN_OPT_FORMAT_HANDLE:
					$list[] = $this->NewDocumentHandle( $document );
					break;

				case kTOKEN_OPT_FORMAT_KEY:
					$list[] = $this->NewDocumentKey( $document );
					break;

				default:
					throw new \InvalidArgumentException (
						"Invalid conversion format code." );					// !@! ==>
			}
		}

		return $list;																// ==>

	} // doFindByQuery.



} // class Collection.


?>
