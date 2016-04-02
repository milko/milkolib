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
use Milko\PHPLib\Container;
use MongoDB\Model\BSONDocument;

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
	 * @uses NewDocumentArray()
	 * @uses toDocumentNative()
	 */
	public function NewNativeDocument( $theData )
	{
		//
		// Handle native type.
		//
		if( $theData instanceof \MongoDB\Model\BSONDocument )
			return $theData;														// ==>

		return $this->toDocumentNative( $this->NewDocumentArray( $theData ) );		// ==>

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
	 *	NewDocumentArray																*
	 *==================================================================================*/

	/**
	 * <h4>Return an array from a document.</h4>
	 *
	 * We overload this method to handle {@link \MongoDB\Model\BSONDocument} instances.
	 *
	 * @param mixed					$theData			Document data.
	 * @return array				Document as array.
	 */
	public function NewDocumentArray( $theData )
	{
		//
		// Handle ArangoDocument.
		//
		if( $theData instanceof BSONDocument )
			return $theData->getArrayCopy();										// ==>

		return parent::NewDocumentArray( $theData );								// ==>

	} // NewDocumentArray.


	/*===================================================================================
	 *	NewDocumentHandle																*
	 *==================================================================================*/

	/**
	 * <h4>Convert a document to a document handle.</h4>
	 *
	 * In this class a handle is an array of two elements: the first is the collection name
	 * and the second is the document key. The collection is by default the current one and
	 * if the provided document lacks its key, the method will raise an exception.
	 *
	 * @param mixed					$theData			Document to reference.
	 * @return mixed				Document handle.
	 * @throws \InvalidArgumentException
	 *
	 * @uses KeyOffset()
	 * @uses collectionName()
	 */
	public function NewDocumentHandle( $theData )
	{
		//
		// Convert to array.
		//
		$document = $this->NewDocumentArray( $theData );

		//
		// Add key.
		//
		if( array_key_exists( $this->KeyOffset(), $document ) )
			return [ $this->collectionName(), $document[ $this->KeyOffset() ];		// ==>

		throw new \InvalidArgumentException (
			"Data is missing the document key." );								// !@! ==>

	} // NewDocumentHandle.


	/*===================================================================================
	 *	NewDocumentKey																	*
	 *==================================================================================*/

	/**
	 * <h4>Return document key.</h4>
	 *
	 * We overload this method to extract the document key from the provided data.
	 *
	 * @param mixed					$theData			Document data.
	 * @return mixed				Document key.
	 * @throws \InvalidArgumentException
	 *
	 * @uses KeyOffset()
	 */
	public function NewDocumentKey( $theDocument )
	{
		//
		// Handle BSONDocument.
		//
		if( $theData instanceof BSONDocument )
		{
			//
			// Check key.
			//
			if( $theData->offsetExists( $this->KeyOffset() ) )
				return $theData[ $this->KeyOffset() ];								// ==>

			throw new \InvalidArgumentException (
				"Data is missing the document key." );							// !@! ==>

		} // BSONDocument.

		return parent::NewDocumentKey( $theData );									// ==>

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
	 *	doInsert																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert a document.</h4>
	 *
	 * We implement this method to use the {@link \MongoDB\Collection::insertOne()} method.
	 *
	 * @param mixed					$theDocument		The document to be inserted.
	 * @return mixed				The inserted document's key.
	 *
	 * @uses Connection()
	 * @uses \MongoDB\Collection::insertOne()
	 */
	protected function doInsert( $theDocument )
	{
		return
			$this->Connection()->insertOne( $theDocument )
				->getInsertedId();													// ==>

	} // doInsert.


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
	 *	doDelete																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete a document.</h4>
	 *
	 * We implement this method to use the {@link \MongoDB\Collection::deleteOne()} method.
	 *
	 * If the document lacks its key, the method will raise an exception.
	 *
	 * @param mixed					$theDocument		The document to be deleted.
	 * @return int					The number of deleted documents.
	 * @throws \InvalidArgumentException
	 *
	 * @uses KeyOffset()
	 * @uses Connection()
	 * @uses \MongoDB\Collection::deleteOne()
	 */
	protected function doDelete( $theDocument )
	{
		//
		// Check document key.
		//
		if( $theDocument->offsetExists( $this->KeyOffset() ) )
			return
				$this->Connection()->deleteOne( [
					$this->KeyOffset() => $theDocument->offsetGet( $this->KeyOffset() ) ] )
						->getDeletedCount();										// ==>

		throw new \InvalidArgumentException (
			"Document is missing its key." );									// !@! ==>

	} // doDelete.


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
	 * @return int					The number of deleted documents.
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

		return ( $theOptions[ kTOKEN_OPT_MANY ] )
			 ? $this->Connection()->deleteMany( $filter )->getDeletedCount()	// ==>
			 : $this->Connection()->deleteOne( $filter )->getDeletedCount();	// ==>

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
	 * @return int					The number of deleted documents.
	 *
	 * @uses Connection()
	 * @uses NewDocumentArray()
	 * @uses \MongoDB\Collection::deleteOne()
	 * @uses \MongoDB\Collection::deleteMany()
	 * @see kTOKEN_OPT_MANY
	 */
	protected function doDeleteByExample( $theDocument, array $theOptions )
	{
		//
		// Convert document to array.
		//
		$document = $this->NewDocumentArray( $theDocument );

		return ( $theOptions[ kTOKEN_OPT_MANY ] )
			 ? $this->Connection()->deleteMany( $document )->getDeletedCount()		// ==>
			 : $this->Connection()->deleteOne( $document )->getDeletedCount();		// ==>

	} // doDeleteByExample.


	/*===================================================================================
	 *	doDeleteByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records by query.</h4>
	 *
	 * We overload this method to call the {@link \MongoDB\Collection::deleteOne()} method
	 * to delete the first document and the {@link \MongoDB\Collection::deleteMany()} to
	 * delete all matching documents.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @param array					$theOptions			Delete options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses doDeleteByExample()
	 */
	protected function doDeleteByQuery( $theQuery, array $theOptions )
	{
		return ( $theOptions[ kTOKEN_OPT_MANY ] )
			 ? $this->Connection()->deleteMany( $theQuery )->getDeletedCount()		// ==>
			 : $this->Connection()->deleteOne( $theQuery )->getDeletedCount();		// ==>

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

		return ( $theOptions[ kTOKEN_OPT_MANY ] )
			 ? $this->Connection()
					->updateMany( $theFilter, $theCriteria )
						->getModifiedCount()										// ==>
			 : $this->Connection()
					->updateOne( $theFilter, $theCriteria )
						->getModifiedCount();										// ==>

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
	 */
	protected function doReplace( $theDocument )
	{
		//
		// Convert document.
		//
		if( $theDocument->offsetExists( $this->KeyOffset() ) )
			return
				$this->Connection()->replaceOne(
					[ $this->KeyOffset() => $theDocument->offsetGet( $this->KeyOffset() ) ],
					$theDocument )
						->getModifiedCount();										// ==>

		return 0;																	// ==>

	} // doReplace.



/*=======================================================================================
 *																						*
 *							PROTECTED SELECTION MANAGEMENT INTERFACE					*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doFindKey																		*
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
	protected function doFindKey( $theKey, array $theOptions )
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
		// Make array.
		//
		if( ! $theOptions[ kTOKEN_OPT_MANY ] )
			$result = [ $result ];

		return $result;																// ==>

	} // doFindKey.


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




/*=======================================================================================
 *																						*
 *								PROTECTED CONVERSION UTILITIES							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	toDocumentNative																*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database document.</h4>
	 *
	 * We implement this method by generating a {@link \MongoDB\Model\BSONDocument}.
	 *
	 * @param array					$theDocument		Document properties.
	 * @return mixed				Native database document object.
	 */
	protected function toDocumentNative( array $theDocument )
	{
		return new \MongoDB\Model\BSONDocument( $theDocument );						// ==>

	} // toDocumentNative.



} // class Collection.


?>
