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

use MongoDB\BSON\UTCDatetime;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;
use MongoDB\Operation\ReplaceOne;

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


	/*===================================================================================
	 *	PropertiesOffset																*
	 *==================================================================================*/

	/**
	 * <h4>Return the document properties offset.</h4>
	 *
	 * We overload this method to use the {@link kTAG_MONGO_OFFSETS} constant.
	 *
	 * @return string				Document properties offset.
	 */
	public function PropertiesOffset()						{	return kTAG_MONGO_OFFSETS;	}



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


	/*===================================================================================
	 *	BuildDocumentHandle																*
	 *==================================================================================*/

	/**
	 * <h4>Build a document handle.</h4>
	 *
	 * We implement this method to return an array of two elements: the first is the
	 * collection name, the second is the document key.
	 *
	 * @param mixed					$theKey				Document key.
	 * @param mixed					$theCollection		Collection instance or name.
	 * @return mixed				Document handle.
	 */
	public function BuildDocumentHandle( $theKey, $theCollection = NULL )
	{
		//
		// Set current collection.
		//
		if( $theCollection === NULL )
			$theCollection = $this;

		//
		// Handle collection instance.
		//
		if( $theCollection instanceof \Milko\PHPLib\Collection )
			return $theCollection->documentHandleCreate( $theKey );					// ==>

		return [ (string)$theCollection, $theKey ];									// ==>

	} // BuildDocumentHandle.



/*=======================================================================================
 *																						*
 *						PUBLIC TIMESTAMP MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewTimestamp																	*
	 *==================================================================================*/

	/**
	 * <h4>Return a native time stamp.</h4>
	 *
	 * We implement this method by instantiating a {@link UTCDatetime} object with the
	 * current <em>milliseconds</em>.
	 *
	 * @param int					$theTimeStamp		Milliseconds.
	 * @return mixed				Time stamp in native format.
	 */
	public function NewTimestamp( $theTimeStamp = NULL )
	{
		return ( $theTimeStamp === NULL )
			 ? UTCDatetime( (int)(microtime( TRUE ) * 1000) )						// ==>
			 : UTCDatetime( (int)$theTimeStamp );									// ==>

	} // NewTimestamp.


	/*===================================================================================
	 *	GetTimestamp																	*
	 *==================================================================================*/

	/**
	 * <h4>Return an ISO date from a timestamp.</h4>
	 *
	 * We implement this method by converting the time stamp into a {@link \DateTime}
	 * object and returning the following format: <tt>"Y-m-d\TH:i:s.u\\Z"</tt>.
	 *
	 * Note that we round the fractional seconds value to the nearest millisecond.
	 *
	 * @param mixed					$theTimeStamp		Native time stamp.
	 * @return string				ISO 8601 date.
	 */
	public function GetTimestamp( $theTimeStamp )
	{
		//
		// Convert to date time.
		//
		$date = $theTimeStamp->toDateTime();

		//
		// Round to milliseconds.
		//
		$milli = (int)(((double)$date->format( "u" )) / 1000000);

		//
		// Build date with seconds.
		//
		$string = $date->format( "Y-m-d\TH:i:s" );

		return $string . '.' . $milli . "Z";										// ==>

	} // GetTimestamp.



/*=======================================================================================
 *																						*
 *								PUBLIC INSERTION INTERFACE								*
 *																						*
 *======================================================================================*/



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
		return
			$this->mConnection->insertMany( $theDocuments )
				->getInsertedIds();													// ==>

	} // InsertBulk.



/*=======================================================================================
 *																						*
 *								PUBLIC UPDATE INTERFACE									*
 *																						*
 *======================================================================================*/



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
	 * @uses \MongoDB\Cursor::getModifiedCount()
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
		// Normalise criteria.
		//
		$theCriteria[ '$set' ][ kTAG_MODIFICATION ] = microtime( TRUE );

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
	 * @uses ConvertDocumentSet()
	 * @uses \MongoDB\Collection::find()
	 */
	public function Find(
		$theFilter = NULL,
		array $theOptions = [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT ] )
	{
		//
		// Init query.
		//
		if( $theFilter === NULL )
			$theFilter = [];

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
			$this->ConvertDocumentSet(
				$result, $theOptions[ kTOKEN_OPT_FORMAT ], TRUE );					// ==>

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
	 * @uses ConvertDocumentSet()
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
					$result, $theOptions[ kTOKEN_OPT_FORMAT ], TRUE );				// ==>

		return
			$this->ConvertDocumentSet(
				$result, $theOptions[ kTOKEN_OPT_FORMAT ], TRUE );					// ==>

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
							$collection->formatDocument(
								$found, $theOptions[ kTOKEN_OPT_FORMAT ], TRUE );

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
					$collection->formatDocument(
						$found, $theOptions[ kTOKEN_OPT_FORMAT ], TRUE );			// ==>

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
	 * @uses ConvertDocumentSet()
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
			$this->ConvertDocumentSet(
				$result, $theOptions[ kTOKEN_OPT_FORMAT ], TRUE );					// ==>

	} // FindByExample.



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
	 * @param mixed					$thePipeline		The aggregation pipeline.
	 * @param array					$theOptions			Query options.
	 * @return array				The result set.
	 *
	 * @uses NewDocumentArray()
	 * @uses \MongoDB\Collection::aggregate()
	 */
	public function MapReduce( $thePipeline, array $theOptions = [] )
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
		foreach( $this->mConnection->aggregate( $thePipeline, $options ) as $record )
			$result[] = $this->NewDocumentArray( $record );

		return $result;																// ==>

	} // MapReduce.



/*=======================================================================================
 *																						*
 *								PUBLIC DISTINCT INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Distinct																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the distinct values of a property.</h4>
	 *
	 * We implement this method by using the {@link \MongoDB\Collection::distinct()} method,
	 * if only distinct values are required, or call the {@link MapReduce()} method if
	 * counts are requested.
	 *
	 * @param string				$theOffset			The property offset.
	 * @param boolean				$doCount			Return element counts.
	 * @return array				The result set.
	 *
	 * @uses MapReduce()
	 * @uses \MongoDB\Collection::distinct()
	 */
	public function Distinct( $theOffset, $doCount = FALSE )
	{
		//
		// Handle only distinct.
		//
		if( ! $doCount )
			return $this->mConnection->distinct( $theOffset );						// ==>

		//
		// Aggregate.
		//
		$result =
			$this->MapReduce( [
				[ '$group' => [ '_id' => '$' . $theOffset,
								'count' => [ '$sum' => 1 ] ] ] ] );

		//
		// Format result.
		//
		$list = [];
		foreach( $result as $item )
			$list[ $item[ '_id' ] ] = $item[ 'count' ];

		return $list;																// ==>

	} // Distinct.


	/*===================================================================================
	 *	DistinctByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the distinct values of a property by query.</h4>
	 *
	 * We implement this method by using the {@link \MongoDB\Collection::distinct()} method,
	 * if only distinct values are required, or call the {@link MapReduce()} method if
	 * counts are requested.
	 *
	 * @param string				$theOffset			The property offset.
	 * @param mixed					$theFilter			The selection criteria.
	 * @param boolean				$doCount			Return element counts.
	 * @return array				The result set.
	 *
	 * @uses MapReduce()
	 * @uses \MongoDB\Collection::distinct()
	 */
	public function DistinctByQuery( $theOffset, $theFilter, $doCount = FALSE )
	{
		//
		// Handle only distinct.
		//
		if( ! $doCount )
			return $this->mConnection->distinct( $theOffset, $theFilter );			// ==>

		//
		// Aggregate.
		//
		$result =
			$this->MapReduce( [
				[ '$match' => $theFilter ],
				[ '$group' => [ '_id' => '$' . $theOffset,
								'count' => [ '$sum' => 1 ] ] ] ] );

		//
		// Format result.
		//
		$list = [];
		foreach( $result as $item )
			$list[ $item[ '_id' ] ] = $item[ 'count' ];

		return $list;																// ==>

	} // DistinctByQuery.


	/*===================================================================================
	 *	DistinctByExample																*
	 *==================================================================================*/

	/**
	 * <h4>Return the distinct values of a property by example.</h4>
	 *
	 * We implement this method by calling {@link DistinctByQuery()}, since the example
	 * document represents a query.
	 *
	 * @param string				$theOffset			The property offset.
	 * @param array					$theDocument		Example document as an array.
	 * @param boolean				$doCount			Return element counts.
	 * @return array				The result set.
	 *
	 * @uses DistinctByQuery()
	 */
	public function DistinctByExample( $theOffset, array $theDocument, $doCount = FALSE )
	{
		return $this->DistinctByQuery( $theOffset, $theDocument, $doCount );		// ==>

	} // DistinctByExample.



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
	 *	CountByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Count documents by key.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::count()} method.
	 *
	 * @param mixed					$theKey				Document key.
	 * @return int					The number of selected documents.
	 */
	public function CountByKey( $theKey )
	{
		return $this->mConnection->count( [ $this->KeyOffset() => $theKey ] );		// ==>
		
	} // CountByKey.


	/*===================================================================================
	 *	CountByHandle																	*
	 *==================================================================================*/

	/**
	 * <h4>Count documents by handle.</h4>
	 *
	 * We implement this method to use the {@link Collection::CountByKey()} method.
	 *
	 * @param mixed					$theHandle			Document handle.
	 * @return int					The number of selected documents.
	 *
	 * @uses Database()
	 */
	public function CountByHandle( $theHandle )
	{
		return
			$this->Database()->NewCollection( $theHandle[ 0 ] )
				->CountByKey( $theHandle[ 1 ] );									// ==>

	} // CountByHandle.


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
				? $this->mConnection->deleteMany( $theFilter )
				: $this->mConnection->deleteOne( $theFilter );

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
				? $this->mConnection->deleteMany( $filter )
				: $this->mConnection->deleteOne( $filter );

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
					? $this->mConnection->deleteMany(
						[ $this->KeyOffset() => [ '$in' => $keys ] ] )
					: $this->mConnection->deleteOne(
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
				? $this->mConnection->deleteMany( $theDocument )
				: $this->mConnection->deleteOne( $theDocument );

		return $result->getDeletedCount();											// ==>

	} // DeleteByExample.



/*=======================================================================================
 *																						*
 *							PUBLIC HANDLE PARSING INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	ParseDocumentHandle																*
	 *==================================================================================*/

	/**
	 * <h4>Return handle components.</h4>
	 *
	 * We implement this method by assuming the handle is an array of two elements: the
	 * first element contains the collection name, the second element contains the document
	 * key.
	 *
	 * @param mixed					$theHandle			The object handle.
	 * @param string			   &$theCollection		Receives collection name.
	 * @param mixed				   &$theIdentifier		Receives object key.
	 */
	public function ParseDocumentHandle( $theHandle, &$theCollection, &$theIdentifier )
	{
		//
		// Extract components.
		//
		$theCollection = $theHandle[ 0 ];
		$theIdentifier = $theHandle[ 1 ];

	} // ParseDocumentHandle.



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
 *							PROTECTED PERSISTENCE INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	documentInsert																	*
	 *==================================================================================*/

	/**
	 * <h4>Insert a document.</h4>
	 *
	 * We implement this method by using the {@link \MongoDB\Collection::insertOne()} method
	 * to save the document.
	 *
	 * @param mixed					$theDocument		The native document to insert.
	 * @return mixed				The inserted document key.
	 *
	 * @uses \MongoDB\Collection::insertOne()
	 * @uses \MongoDB\InsertOneResult::getInsertedId()
	 */
	protected function documentInsert( $theDocument )
	{
		return
			$this->mConnection->insertOne( $theDocument )
				->getInsertedId();													// ==>

	} // documentInsert.


	/*===================================================================================
	 *	documentReplace																	*
	 *==================================================================================*/

	/**
	 * <h4>Replace a document.</h4>
	 *
	 * We overload this method to use the {@link \MongoDB\Collection::replaceOne()} method.
	 *
	 * @param mixed					$theKey				The document key.
	 * @param mixed					$theDocument		The replacement native document.
	 * @return mixed				The number of replaced documents.
	 *
	 * @uses KeyOffset()
	 * @uses \MongoDB\Collection::replaceOne()
	 * @uses \MongoDB\Operation\ReplaceOne::getModifiedCount()
	 */
	protected function documentReplace( $theKey, $theDocument )
	{
		return
			$this->mConnection->replaceOne(
				[ $this->KeyOffset() => $theKey ], $theDocument )
					->getModifiedCount();											// ==>

	} // documentReplace.



/*=======================================================================================
 *																						*
 *								PROTECTED PERSISTENCE UTILITIES							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	normaliseInsertedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise inserted document.</h4>
	 *
	 * We overload this method to skip native documents.
	 *
	 * @param mixed					$theDocument		The inserted document.
	 * @param mixed					$theData			The native inserted document.
	 * @param mixed					$theKey				The document key.
	 */
	protected function normaliseInsertedDocument( $theDocument, $theData, $theKey )
	{
		//
		// Skip native documents.
		//
		if( ! ($theDocument instanceof BSONDocument) )
			parent::normaliseInsertedDocument( $theDocument, $theData, $theKey );

	} // normaliseInsertedDocument.


	/*===================================================================================
	 *	normaliseReplacedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise replaced document.</h4>
	 *
	 * We overload this method to skip native documents.
	 *
	 * @param mixed					$theDocument		The replaced document.
	 * @param mixed						$theData		The native database document.
	 *
	 * @uses Document::IsModified()
	 */
	protected function normaliseReplacedDocument( $theDocument, $theData )
	{
		//
		// Skip native documents.
		//
		if( ! ($theDocument instanceof BSONDocument) )
			parent::normaliseReplacedDocument( $theDocument, $theData );

	} // normaliseReplacedDocument.


	/*===================================================================================
	 *	normaliseSelectedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise selected document.</h4>
	 *
	 * We overload this method to skip native documents.
	 *
	 * @param mixed					$theDocument		The selected document.
	 * @param mixed					$theData			The native database document.
	 *
	 * @uses Document::IsPersistent()
	 * @uses Document::IsModified()
	 */
	protected function normaliseSelectedDocument( $theDocument, $theData )
	{
		//
		// Skip native documents.
		//
		if( ! ($theDocument instanceof BSONDocument) )
			parent::normaliseSelectedDocument( $theDocument, $theData );

	} // normaliseSelectedDocument.


	/*===================================================================================
	 *	normaliseDeletedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise deleted document.</h4>
	 *
	 * We overload this method to skip native documents.
	 *
	 * @param mixed					$theDocument		The deleted document.
	 *
	 * @uses RevisionOffset()
	 */
	protected function normaliseDeletedDocument( $theDocument )
	{
		//
		// Skip native documents.
		//
		if( ! ($theDocument instanceof BSONDocument) )
			parent::normaliseDeletedDocument( $theDocument );

	} // normaliseDeletedDocument.



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
