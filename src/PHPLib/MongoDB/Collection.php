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

use Milko\PHPLib\Document;
use Milko\PHPLib\MongoDB\Database;

use MongoDB\Model\BSONArray;
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
 */
class Collection extends \Milko\PHPLib\Collection
{



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
 *							PUBLIC COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Drop																			*
	 *==================================================================================*/

	/**
	 * <h4>Drop the current collection.</h4>
	 *
	 * We implement this method by calling the {@link \MongoDB\Collection::drop()} method.
	 *
	 * @param array					$theOptions			Native driver options.
	 * @return mixed				<tt>TRUE</tt> dropped, <tt>NULL</tt> not found.
	 *
	 * @uses collectionName()
	 * @uses Database::ListCollections()
	 * @uses \MongoDB\Collection::drop()
	 */
	public function Drop( $theOptions = NULL )
	{
		//
		// Drop collection.
		//
		$result = $this->mConnection->drop( $theOptions );

		return ( $result->ok )
			 ? TRUE																	// ==>
			 : NULL;																// ==>

	} // Drop.


	/*===================================================================================
	 *	Truncate																		*
	 *==================================================================================*/

	/**
	 * <h4>Clear the collection contents.</h4>
	 *
	 * We overload this method to call the {@link \MongoDB\Collection::deleteMany()} method
	 * with an empty filter.
	 *
	 * @param array					$theOptions			Native driver options.
	 * @return mixed				<tt>TRUE</tt> dropped, <tt>NULL</tt> not found.
	 *
	 * @uses collectionName()
	 * @uses Database::ListCollections()
	 * @uses \MongoDB\Collection::deleteMany()
	 */
	public function Truncate( $theOptions = NULL )
	{
		//
		// Check if collection exists.
		//
		if( in_array( $this->collectionName(), $this->mDatabase->ListCollections() ) )
		{
			//
			// Init options.
			//
			if( $theOptions === NULL )
				$theOptions = [];

			//
			// Clear collection.
			//
			$this->mConnection->deleteMany( [] );

			return TRUE;															// ==>

		} // Collection exists.

		return NULL;																// ==>

	} // Truncate.



/*=======================================================================================
 *																						*
 *							PUBLIC DOCUMENT MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewDocumentNative																*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database document.</h4>
	 *
	 * We overload this method to return the eventual {@link BSONDocument} provided in the
	 * parameter.
	 *
	 * @param mixed					$theData			Document data.
	 * @return mixed				Database native object.
	 */
	public function NewDocumentNative( $theData )
	{
		//
		// Handle native document.
		//
		if( $theData instanceof BSONDocument )
			return $theData;														// ==>

		return parent::NewDocumentNative( $theData );								// ==>

	} // NewDocumentNative.


	/*===================================================================================
	 *	NewDocumentArray																*
	 *==================================================================================*/

	/**
	 * <h4>Return an array from a document.</h4>
	 *
	 * We overload this method to convert {@link BSONDocument} instances to array; we also
	 * traverse the document to convert {@link BSONArray} instances to arrays.
	 *
	 * @param mixed					$theData			Document data.
	 * @return array				Document as array.
	 *
	 * @uses serialiseNativeDocument()
	 */
	public function NewDocumentArray( $theData )
	{
		//
		// Handle BSONDocument.
		//
		if( $theData instanceof BSONDocument )
		{
			//
			// Convert to array.
			//
			$document = $theData->getArrayCopy();

			//
			// Traverse document to convert BSONArrays.
			//
			$this->serialiseNativeDocument( $document );

			return $document;														// ==>

		} // Is a BSONDocument.

		return parent::NewDocumentArray( $theData );								// ==>

	} // NewDocumentArray.


	/*===================================================================================
	 *	NewDocumentKey																	*
	 *==================================================================================*/

	/**
	 * <h4>Return document key.</h4>
	 *
	 * We overload this method to intercept {@link BSONDocument} instances and return the
	 * document key.
	 *
	 * If the provided document cannot return the key, the method will raise an exception.
	 *
	 * @param mixed					$theData			Document data.
	 * @return mixed				Document key.
	 * @throws \InvalidArgumentException
	 */
	public function NewDocumentKey( $theData )
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
				return $theData->offsetGet( $this->KeyOffset() );					// ==>

			throw new \InvalidArgumentException (
				"Unable to retrieve key from document." );						// !@! ==>

		} // Is a BSONDocument.

		return parent::NewDocumentKey( $theData );									// ==>

	} // NewDocumentKey.



/*=======================================================================================
 *																						*
 *								PUBLIC INSERTION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Insert																			*
	 *==================================================================================*/

	/**
	 * <h4>Insert document.</h4>
	 *
	 * We implement this method by using the {@link \MongoDB\Collection::insertOne()} method
	 * to save the document.
	 *
	 * @param array					$theDocument		The document as an array.
	 * @return mixed				The document's unique identifier.
	 *
	 * @uses \MongoDB\Collection::insertOne()
	 * @uses \MongoDB\InsertOneResult::getInsertedId()
	 */
	public function Insert( array $theDocument )
	{
		return
			$this->mConnection->insertOne( $theDocument )
				->getInsertedId();													// ==>

	} // Insert.


	/*===================================================================================
	 *	InsertMany																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert a set of documents.</h4>
	 *
	 * We implement this method by using the {@link \MongoDB\Collection::insertMany()}
	 * method to save the documents set.
	 *
	 * @param array					$theDocuments		The documents set as an array.
	 * @return array				The document unique identifiers.
	 *
	 * @uses \MongoDB\Collection::insertMany()
	 * @uses \MongoDB\InsertManyResult::getInsertedIds()
	 */
	public function InsertMany( array $theDocuments )
	{
		return
			$this->mConnection->insertMany( $theDocuments )
				->getInsertedIds();													// ==>

	} // InsertMany.


	/*===================================================================================
	 *	InsertBulk																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert a set of documents.</h4>
	 *
	 * We implement this method by using the {@link InsertMany()} method.
	 *
	 * @param mixed					$theDocuments		The native documents set.
	 * @return array				The document unique identifiers.
	 *
	 * @uses InsertMany()
	 */
	public function InsertBulk( $theDocuments )
	{
		return $this->InsertMany( $theDocuments );									// ==>

	} // InsertBulk.



/*=======================================================================================
 *																						*
 *								PUBLIC UPDATE INTERFACE									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Replace																			*
	 *==================================================================================*/

	/**
	 * <h4>Replace document.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::replaceOne()} method.
	 *
	 * If the provided document doesn't have its key ({@link KeyOffset()}), the method will
	 * raise an exception.
	 *
	 * @param mixed					$theDocument		The replacement document.
	 * @return int					The number of replaced documents.
	 *
	 * @uses NewDocumentKey()
	 * @uses NewDocumentNative()
	 * @uses \MongoDB\Collection::replaceOne()
	 */
	public function Replace( $theDocument )
	{
		//
		// Get document key.
		// This will throw if key is missing.
		//
		$key = $this->NewDocumentKey( $theDocument );

		return
			$this->Connection()->replaceOne(
				[ $this->KeyOffset() => $key ],
				$this->NewDocumentNative( $theDocument ) )
					->getModifiedCount();											// ==>

	} // Replace.


	/*===================================================================================
	 *	Update																			*
	 *==================================================================================*/

	/**
	 * <h4>Update documents.</h4>
	 *
	 * We implement this method to use the {@link \MongoDB\Collection::updateOne()} method
	 * to update a single document and {@link \MongoDB\Collection::updateMany()} method to
	 * update many records.
	 *
	 * @param array					$theCriteria		The modification criteria.
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Update options.
	 * @return int					The number of modified records.
	 *
	 * @uses \MongoDB\Collection::updateOne()
	 * @uses \MongoDB\Collection::updateMany()
	 */
	public function Update( array $theCriteria,
							$theFilter = NULL,
							array $theOptions = [ kTOKEN_OPT_MANY => TRUE ] )
	{
		//
		// Normalise query.
		//
		if( $theFilter === NULL )
			$theFilter = [];

		//
		// Update all documents.
		//
		$result = ( $theOptions[ kTOKEN_OPT_MANY ] )
				? $this->mConnection->updateMany( $theFilter, $theCriteria )
				: $this->mConnection->updateOne( $theFilter, $theCriteria );

		return $result->getModifiedCount();											// ==>

	} // Update.


	/*===================================================================================
	 *	UpdateByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Update documents by example.</h4>
	 *
	 * We implement this method by using the {@link Update()} method, since the example
	 * document corresponds to a MongoDB query.
	 *
	 * @param array					$theCriteria		The modification criteria.
	 * @param array					$theDocument		The example document.
	 * @param array					$theOptions			Update options.
	 * @return int					The number of modified records.
	 *
	 * @uses Update()
	 */
	public function UpdateByExample( array $theCriteria,
									 array $theDocument,
									 array $theOptions = [ kTOKEN_OPT_MANY => TRUE ] )
	{
		return $this->Update( $theCriteria, $theDocument, $theOptions );			// ==>

	} // UpdateByExample.



/*=======================================================================================
 *																						*
 *								PUBLIC SELECTION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Find																			*
	 *==================================================================================*/

	/**
	 * <h4>Find documents by query.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::find()} method, we
	 * convert the {@link kTOKEN_OPT_SKIP} and {@link kTOKEN_OPT_LIMIT} parameters into
	 * respectively the <tt>skip</tt> and <tt>limit</tt> native options.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Query options.
	 * @return mixed				The found records.
	 *
	 * @uses formatCursor()
	 * @uses \MongoDB\Collection::find()
	 */
	public function Find(
		$theFilter,
		array $theOptions = [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT ] )
	{
		//
		// Convert to native options.
		//
		$options = [];
		if( array_key_exists( kTOKEN_OPT_SKIP, $theOptions ) )
			$options[ 'skip' ] = $theOptions[ kTOKEN_OPT_SKIP ];
		if( array_key_exists( kTOKEN_OPT_LIMIT, $theOptions ) )
		{
			$options[ 'limit' ] = $theOptions[ kTOKEN_OPT_LIMIT ];
			if( ! array_key_exists( kTOKEN_OPT_SKIP, $theOptions ) )
				$options[ 'skip' ] = 0;
		}

		//
		// Find documents.
		//
		$result = $this->mConnection->find( $theFilter, $options );

		//
		// Handle native result.
		//
		if( $theOptions[ kTOKEN_OPT_FORMAT ] == kTOKEN_OPT_FORMAT_NATIVE )
			return $result;															// ==>

		return
			$this->formatCursor(
				$result, $theOptions[ kTOKEN_OPT_FORMAT ] );						// ==>

	} // Find.


	/*===================================================================================
	 *	FindByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Find documents by key.</h4>
	 *
	 * We implement this method to use the {@link \MongoDB\Collection::findOne()} method for
	 * a single key and {@link \MongoDB\Collection::find()} for a set of keys.
	 *
	 * @param mixed					$theKey				Single or set of document keys.
	 * @param array					$theOptions			Query options.
	 * @return mixed				The found records.
	 *
	 * @uses KeyOffset()
	 * @uses formatCursor()
	 * @uses formatDocument()
	 * @uses \MongoDB\Collection::find()
	 * @uses \MongoDB\Collection::findOne()
	 */
	public function FindByKey(
		$theKey,
		array $theOptions = [ kTOKEN_OPT_MANY => FALSE,
							  kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT ] )
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
				? $this->mConnection->find( $filter )
				: $this->mConnection->findOne( $filter );

		//
		// Handle native result.
		//
		if( $theOptions[ kTOKEN_OPT_FORMAT ] == kTOKEN_OPT_FORMAT_NATIVE )
			return $result;															// ==>

		//
		// Handle single key.
		//
		if( ! $theOptions[ kTOKEN_OPT_MANY ] )
			return
				$this->formatDocument(
					$result, $theOptions[ kTOKEN_OPT_FORMAT ] );					// ==>

		return $this->formatCursor( $result, $theOptions[ kTOKEN_OPT_FORMAT ] );	// ==>

	} // FindByKey.


	/*===================================================================================
	 *	FindByHandle																	*
	 *==================================================================================*/

	/**
	 * <h4>Find documents by handle.</h4>
	 *
	 * We implement this method to use the {@link \MongoDB\Collection::findOne()} method for
	 * both a single key and a set of keys.
	 *
	 * @param mixed					$theHandle			Single or set of document handles.
	 * @param array					$theOptions			Query options.
	 * @return mixed				The found records.
	 *
	 * @uses KeyOffset()
	 * @uses formatDocument()
	 * @uses \MongoDB\Collection::findOne()
	 */
	public function FindByHandle(
		$theHandle,
		array $theOptions = [ kTOKEN_OPT_MANY => FALSE,
							  kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT ] )
	{
		//
		// Handle list.
		//
		if( $theOptions[ kTOKEN_OPT_MANY ] )
		{
			//
			// Iterate handles.
			//
			$result = [];
			foreach( $theHandle as $handle )
			{
				//
				// Get collection.
				//
				$collection = $this->mDatabase->GetCollection( $handle[ 0 ] );
				if( $collection instanceof Collection )
				{
					//
					// Get by key.
					//
					$found =
						$collection->Connection()->findOne(
							[ $collection->KeyOffset() => $handle[ 1 ] ] );

					//
					// Add if found.
					//
					if( $found !== NULL )
						$result[] =
							$this->formatDocument(
								$found, $theOptions[ kTOKEN_OPT_FORMAT ] );

				} // Collection exists.

			} // Iterating handles.

			return $result;															// ==>

		} // List of handles.

		//
		// Get collection.
		//
		$collection = $this->mDatabase->GetCollection( $theHandle[ 0 ] );
		if( $collection instanceof Collection )
		{
			//
			// Get by key.
			//
			$found =
				$collection->Connection()->findOne(
					[ $collection->KeyOffset() => $theHandle[ 1 ] ] );

			//
			// Add if found.
			//
			if( $found !== NULL )
				return
					$this->formatDocument(
						$found, $theOptions[ kTOKEN_OPT_FORMAT ] );					// ==>

		} // Collection exists.

		return NULL;																// ==>

	} // FindByHandle.


	/*===================================================================================
	 *	FindByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Find documents by example.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::find()} method, the
	 * provided example document will be used as the actual selection criteria.
	 *
	 * We convert the {@link kTOKEN_OPT_SKIP} and {@link kTOKEN_OPT_LIMIT} parameters into
	 * respectively the <tt>skip</tt> and <tt>limit</tt> native options.
	 *
	 * @param array					$theDocument		Example document as an array.
	 * @param array					$theOptions			Query options.
	 * @return mixed				The found records.
	 *
	 * @uses formatCursor()
	 * @uses \MongoDB\Collection::find()
	 */
	public function FindByExample(
		array $theDocument,
		array $theOptions = [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT ] )
	{
		//
		// Convert to native options.
		//
		$options = [];
		if( array_key_exists( kTOKEN_OPT_SKIP, $theOptions ) )
			$options[ 'skip' ] = $theOptions[ kTOKEN_OPT_SKIP ];
		if( array_key_exists( kTOKEN_OPT_LIMIT, $theOptions ) )
		{
			$options[ 'limit' ] = $theOptions[ kTOKEN_OPT_LIMIT ];
			if( ! array_key_exists( kTOKEN_OPT_SKIP, $theOptions ) )
				$options[ 'skip' ] = 0;
		}

		//
		// Make selection.
		//
		$result = $this->mConnection->find( $theDocument, $options );

		//
		// Handle native result.
		//
		if( $theOptions[ kTOKEN_OPT_FORMAT ] == kTOKEN_OPT_FORMAT_NATIVE )
			return $result;															// ==>

		return
			$this->formatCursor(
				$result, $theOptions[ kTOKEN_OPT_FORMAT ] );						// ==>

	} // FindByExample.



	/*=======================================================================================
	 *																						*
	 *								PUBLIC COUNTING INTERFACE								*
	 *																						*
	 *======================================================================================*/



	/*===================================================================================
	 *	Count																			*
	 *==================================================================================*/

	/**
	 * <h4>Return the documents count.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::count()} method.
	 *
	 * @return int					The total number of documents in the collection.
	 *
	 * @uses \MongoDB\Collection::count()
	 */
	public function Count()
	{
		return $this->mConnection->count();											// ==>

	} // Count.


	/*===================================================================================
	 *	CountByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the number of documents by query.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::count()} method.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @return int					The number of selected documents.
	 *
	 * @uses \MongoDB\Collection::count()
	 */
	public function CountByQuery( $theFilter )
	{
		return $this->mConnection->count( $theFilter );								// ==>

	} // CountByQuery.


	/*===================================================================================
	 *	CountByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Find documents by example.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::count()} method.
	 *
	 * @param array					$theDocument		Example document as an array.
	 * @return int					The number of selected documents.
	 *
	 * @uses \MongoDB\Collection::count()
	 */
	public function CountByExample( array $theDocument )
	{
		return $this->mConnection->count( $theDocument );							// ==>

	} // CountByExample.



/*=======================================================================================
 *																						*
 *								PUBLIC DELETION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Delete																			*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents by query.</h4>
	 *
	 * We overload this method to call the {@link \MongoDB\Collection::deleteOne()} method
	 * to delete the first document and the {@link \MongoDB\Collection::deleteMany()} to
	 * delete all matching documents.
	 *
	 * @param mixed					$theFilter			The deletion criteria.
	 * @param array					$theOptions			Query options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses \MongoDB\Collection::deleteOne()
	 * @uses \MongoDB\Collection::deleteMany()
	 * @uses \MongoDB\Cursor::getDeletedCount()
	 */
	public function Delete(
		$theFilter,
		array $theOptions = [ kTOKEN_OPT_MANY => TRUE ] )
	{
		//
		// Delete documents.
		//
		$result = ( $theOptions[ kTOKEN_OPT_MANY ] )
				? $this->Connection()->deleteMany( $theFilter )
				: $this->Connection()->deleteOne( $theFilter );

		return $result->getDeletedCount();											// ==>

	} // Delete.


	/*===================================================================================
	 *	DeleteByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents by key.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::deleteOne()} method
	 * to delete a single document and {@link \MongoDB\Collection::deleteMany()} method to
	 * delete a list of documents.
	 *
	 * @param mixed					$theKey				Single or set of document keys.
	 * @param array					$theOptions			Query options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses KeyOffset()
	 * @uses \MongoDB\Collection::deleteOne()
	 * @uses \MongoDB\Collection::deleteMany()
	 * @uses \MongoDB\Cursor::getDeletedCount()
	 */
	public function DeleteByKey(
		$theKey,
		array $theOptions = [ kTOKEN_OPT_MANY => FALSE ] )
	{
		//
		// Set selection filter.
		//
		$filter = ( $theOptions[ kTOKEN_OPT_MANY ] )
				? [ $this->KeyOffset() => [ '$in' => (array)$theKey ] ]
				: [ $this->KeyOffset() => $theKey ];

		//
		// Delete documents.
		//
		$result = ( $theOptions[ kTOKEN_OPT_MANY ] )
				? $this->Connection()->deleteMany( $filter )
				: $this->Connection()->deleteOne( $filter );

		return $result->getDeletedCount();											// ==>

	} // DeleteByKey.


	/*===================================================================================
	 *	DeleteByHandle																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents by handle.</h4>
	 *
	 * We implement the method by aggregating the handles and calling the
	 * {@link \MongoDB\Collection::deleteMany()} method if there is more than one key per
	 * collection or {@link \MongoDB\Collection::deleteOne()} method if there is a single
	 * key.
	 *
	 * @param mixed					$theHandle			Single or set of document handles.
	 * @param array					$theOptions			Query options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses \MongoDB\Collection::deleteOne()
	 * @uses \MongoDB\Collection::deleteMany()
	 * @uses \MongoDB\Cursor::getDeletedCount()
	 */
	public function DeleteByHandle(
		$theHandle,
		array $theOptions = [ kTOKEN_OPT_MANY => FALSE ] )
	{
		//
		// Normalise handles.
		//
		if( ! $theOptions[ kTOKEN_OPT_MANY ] )
			$theHandle = [ $theHandle ];
		else
			$theHandle = (array)$theHandle;

		//
		// Aggregate handles.
		//
		$handles = [];
		foreach( $theHandle as $handle )
		{
			//
			// Aggregate.
			//
			if( array_key_exists( $handle[ 0 ], $handles ) )
				$handles[ $handle[ 0 ] ] = $handle[ 1 ];
			else
				$handles[ $handle[ 0 ] ] = [ $handle[ 1 ] ];
		}

		//
		// Iterate handles.
		//
		$count = 0;
		foreach( $handles as $collection => $keys )
		{
			//
			// Delete documents.
			//
			$result = ( count( $keys ) > 1 )
					? $this->Connection()->deleteMany(
						[ $this->KeyOffset() => [ '$in' => $keys ] ] )
					: $this->Connection()->deleteOne(
						[ $this->KeyOffset() => $keys[ 0 ] ] );

			//
			// Increment.
			//
			$count += $result->getDeletedCount();
		}

		return $count;																// ==>

	} // DeleteByHandle.


	/*===================================================================================
	 *	DeleteByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents by example.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::deleteOne()} method
	 * to delete a single document and {@link \MongoDB\Collection::deleteMany()} method to
	 * delete all selected records.
	 *
	 * @param array					$theDocument		Example document as an array.
	 * @param array					$theOptions			Query options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses \MongoDB\Collection::deleteOne()
	 * @uses \MongoDB\Collection::deleteMany()
	 * @uses \MongoDB\Cursor::getDeletedCount()
	 */
	public function DeleteByExample(
		array $theDocument,
		array $theOptions = [ kTOKEN_OPT_MANY => TRUE ] )
	{
		//
		// Delete documents.
		//
		$result = ( $theOptions[ kTOKEN_OPT_MANY ] )
				? $this->Connection()->deleteMany( $theDocument )
				: $this->Connection()->deleteOne( $theDocument );

		return $result->getDeletedCount();											// ==>

	} // DeleteByExample.



/*=======================================================================================
 *																						*
 *						PROTECTED COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	collectionCreate																*
	 *==================================================================================*/

	/**
	 * <h4>Return a native collection object.</h4>
	 *
	 * We implement this method by using the database
	 * {@link \MongoDB\Database::selectCollection()} method.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Driver native options.
	 * @return mixed				Native collection object.
	 *
	 * @uses \MongoDB\Database::selectCollection()
	 */
	protected function collectionCreate( $theCollection, $theOptions = NULL )
	{
		//
		// Init options.
		//
		if( $theOptions === NULL )
			$theOptions = [];

		return
			$this->mDatabase->Connection()->selectCollection(
				(string)$theCollection, $theOptions );								// ==>

	} // collectionCreate.


	/*===================================================================================
	 *	collectionName																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the collection name.</h4>
	 *
	 * We implement this method by using the
	 * {@link \MongoDB\Collection::getCollectionName()} method.
	 *
	 * @return string				The collection name.
	 *
	 * @uses \MongoDB\Collection::getCollectionName()
	 */
	protected function collectionName()
	{
		return $this->mConnection->getCollectionName();								// ==>

	} // collectionName.



/*=======================================================================================
 *																						*
 *								PROTECTED CONVERSION UTILITIES							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	documentNativeCreate															*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database document.</h4>
	 *
	 * We implement this method to create a {@link BSONDocument} instance.
	 *
	 * @param array					$theData			Document as an array.
	 * @return mixed				Native database document object.
	 */
	protected function documentNativeCreate( array $theData )
	{
		return new BSONDocument( $theData );										// ==>

	} // documentNativeCreate.


	/*===================================================================================
	 *	documentHandleCreate															*
	 *==================================================================================*/

	/**
	 * <h4>Return a document handle.</h4>
	 *
	 * We implement this method to return an array of two elements: the first is the
	 * collection name, the second is the document key.
	 *
	 * @param mixed					$theKey				Document key.
	 * @return mixed				Document handle.
	 *
	 * @uses collectionName()
	 */
	protected function documentHandleCreate( $theKey )
	{
		return [ $this->collectionName(), (string)$theKey ];						// ==>

	} // documentHandleCreate.



/*=======================================================================================
 *																						*
 *							PROTECTED SERIALISATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	serialiseNativeDocument															*
	 *==================================================================================*/

	/**
	 * <h4>Convert native document to array.</h4>
	 *
	 * This method can be used to convert a native document to an array, it will traverse
	 * the object and convert all BSONArrays to arrays.
	 *
	 * The method expects an array at entry.
	 *
	 * @param array				   &$theDocument		Source and destination document.
	 */
	protected function serialiseNativeDocument( array &$theDocument )
	{
		//
		// Traverse source.
		//
		$keys = array_keys( $theDocument );
		foreach( $keys as $key )
		{
			//
			// Convert BSONArray instances.
			//
			if( $theDocument[ $key ] instanceof BSONArray )
				$theDocument[ $key ] = $theDocument[ $key ]->getArrayCopy();

			//
			// Handle collections.
			//
			if( is_array( $theDocument[ $key ] ) )
				$this->serialiseNativeDocument( $theDocument[ $key ] );

		} // Traversing source.

	} // serialiseNativeDocument.



} // class Collection.


?>
