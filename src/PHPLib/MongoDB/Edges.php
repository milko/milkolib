<?php

/**
 * Edges.php
 *
 * This file contains the definition of the {@link Relations} class.
 */

namespace Milko\PHPLib\MongoDB;

/*=======================================================================================
 *																						*
 *										Edges.php										*
 *																						*
 *======================================================================================*/

use Milko\PHPLib\Container;
use Milko\PHPLib\Document;
use Milko\PHPLib\MongoDB\Collection;
use Milko\PHPLib\iEdges;
use Milko\PHPLib\Edge;

/**
 * <h4>Relationships collection object.</h4>
 *
 * This <em>concrete</em> class is the implementation of an edge collection, it overloads
 * the inherited {@link Collection} interface and implements the
 * {@link iEdges} interface.
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		01/04/2016
 */
class Edges extends Collection
			implements iEdges
{



/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET DECLARATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	VertexSource																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the relationship source offset.</h4>
	 *
	 * We overload this method to use the {@link kTAG_MONGO_REL_FROM} constant.
	 *
	 * @return mixed				Source vertex document handle.
	 *
	 * @see kTAG_MONGO_REL_FROM
	 */
	public function VertexSource()							{	return kTAG_MONGO_REL_FROM;	}


	/*===================================================================================
	 *	VertexDestination																*
	 *==================================================================================*/

	/**
	 * <h4>Return the relationship destination offset.</h4>
	 *
	 * We overload this method to use the {@link kTAG_MONGO_REL_TO} constant.
	 *
	 * @return mixed				Destination vertex document handle.
	 *
	 * @see kTAG_MONGO_REL_TO
	 */
	public function VertexDestination()						{	return kTAG_MONGO_REL_TO;	}



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
	 * We override this method to check whether the edge has its vertices, if that is not
	 * the case, we raise an exception.
	 *
	 * @param array					$theDocument		The document data.
	 * @return mixed				The document's key.
	 * @throws \RuntimeException
	 *
	 * @uses NewDocumentNative()
	 * @uses normaliseInsertedDocument()
	 * @uses Document::Validate()
	 * @uses Document::TraverseDocument()
	 * @uses Document::SetPropertiesList()
	 * @uses Document::PrepareInsert()
	 * @uses \MongoDB\Collection::insertOne()
	 * @uses \MongoDB\InsertOneResult::getInsertedId()
	 */
	public function Insert( $theDocument )
	{
		//
		// Validate and prepare document.
		//
		if( $theDocument instanceof Document )
		{
			//
			// Validate document.
			//
			$theDocument->Validate();

			//
			// Store sub-documents and collect offsets.
			//
			$theDocument->SetPropertiesList(
				$theDocument->Traverse(), $this );

			//
			// Prepare document.
			//
			$theDocument->PrepareInsert();

		} // Document instance.

		//
		// Convert document.
		//
		$document = $this->NewDocumentNative( $theDocument );

		//
		// Check vertices.
		//
		if( ! ($theDocument instanceof Document) )
		{
			if( ! $document->offsetExists( $this->VertexSource() ) )
				throw new \RuntimeException (
					"Missing source vertex." );									// !@! ==>
			if( ! $document->offsetExists( $this->VertexDestination() ) )
				throw new \RuntimeException (
					"Missing destination vertex." );							// !@! ==>

		} // Not already checked.

		//
		// Insert document.
		//
		$key =
			$this->mConnection->insertOne( $document )
				->getInsertedId();

		//
		// Normalise document.
		//
		$this->normaliseInsertedDocument( $theDocument, $document, $key );

		return $key;																// ==>

	} // Insert.



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
	 * We override this method to check whether the edge has its vertices, if that is not
	 * the case, we raise an exception.
	 *
	 * @param mixed					$theDocument		The replacement document.
	 * @return int					The number of replaced documents.
	 * @throws \RuntimeException
	 *
	 * @uses NewDocumentKey()
	 * @uses NewDocumentNative()
	 * @uses normaliseInsertedDocument()
	 * @uses Document::Validate()
	 * @uses Document::TraverseDocument()
	 * @uses Document::SetPropertiesList()
	 * @uses Document::PrepareReplace()
	 * @uses \MongoDB\Collection::replaceOne()
	 */
	public function Replace( $theDocument )
	{
		//
		// Validate and prepare document.
		//
		if( $theDocument instanceof Document )
		{
			//
			// Validate document.
			//
			$theDocument->Validate();

			//
			// Store sub-documents.
			//
			$theDocument->SetPropertiesList(
				$theDocument->Traverse(), $this );

			//
			// Prepare document.
			//
			$theDocument->PrepareReplace();

		} // Document instance.

		//
		// Get document key.
		// This will throw if key is missing.
		//
		$key = $this->NewDocumentKey( $theDocument );

		//
		// Convert document.
		//
		$document = $this->NewDocumentNative( $theDocument );

		//
		// Check vertices.
		//
		if( ! ($theDocument instanceof Document) )
		{
			if( ! $document->offsetExists( $this->VertexSource() ) )
				throw new \RuntimeException (
					"Missing source vertex." );									// !@! ==>
			if( ! $document->offsetExists( $this->VertexDestination() ) )
				throw new \RuntimeException (
					"Missing destination vertex." );							// !@! ==>

		} // Not already checked.

		//
		// Replace document.
		//
		$count =
			$this->mConnection->replaceOne( [ $this->KeyOffset() => $key ], $document )
				->getModifiedCount();

		//
		// Normalise document.
		//
		if( $count )
			$this->normaliseInsertedDocument( $theDocument, $document, $key );

		return $count;																// ==>

	} // Replace.



/*=======================================================================================
 *																						*
 *							PUBLIC GRAPH MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	FindByVertex																	*
	 *==================================================================================*/

	/**
	 * <h4>Find by vertex.</h4>
	 *
	 * We overload this method by applying the appropriate query to the database.
	 *
	 * @param mixed					$theVertex			The vertex document or handle.
	 * @param array					$theOptions			Find options.
	 * @return array				The found documents.
	 * @throws \InvalidArgumentException
	 *
	 * @uses Connection()
	 * @uses collectionName()
	 * @uses VertexSource()
	 * @uses VertexDestination()
	 * @uses normaliseOptions()
	 * @uses NewDocumentHandle()
	 * @uses \MongoDB\Collection::find()
	 * @see kTOKEN_OPT_FORMAT
	 * @see kTOKEN_OPT_DIRECTION
	 */
	public function FindByVertex(
		$theVertex,
		array $theOptions = [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT,
							  kTOKEN_OPT_DIRECTION => kTOKEN_OPT_DIRECTION_ANY ] )
	{
		//
		// Get vertex handle.
		//
		if( $theVertex instanceof Container )
			$theVertex = $this->NewDocumentHandle( $theVertex );

		//
		// Build query.
		//
		switch( $theOptions[ kTOKEN_OPT_DIRECTION ] )
		{
			case kTOKEN_OPT_DIRECTION_IN:
				$query = [ $this->VertexDestination() => $theVertex ];
				break;

			case kTOKEN_OPT_DIRECTION_OUT:
				$query = [ $this->VertexSource() => $theVertex ];
				break;

			case kTOKEN_OPT_DIRECTION_ANY:
				$query = [ '$or' => [ [ $this->VertexSource() => $theVertex ],
									  [ $this->VertexDestination() => $theVertex ] ] ];
				break;

			default:
				throw new \InvalidArgumentException (
					"Invalid conversion format code." );						// !@! ==>
		}

		return
			$this->ConvertDocumentSet(
				$this->mConnection->find( $query ),
				$theOptions[ kTOKEN_OPT_FORMAT ],
				TRUE );																// ==>

	} // FindByVertex.



/*=======================================================================================
 *																						*
 *								PROTECTED CONVERSION UTILITIES							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	documentCreate																	*
	 *==================================================================================*/

	/**
	 * <h4>Return a standard database document.</h4>
	 *
	 * In this class we return an {@link Edge} instance, in derived classes you can
	 * overload this method to return a different kind of standard document.
	 *
	 * @param array					$theData			Document as an array.
	 * @return mixed				Standard document object.
	 */
	protected function documentCreate( array $theData )
	{
		return new Edge( $this, $theData );											// ==>

	} // documentCreate.



} // class Edges.


?>
