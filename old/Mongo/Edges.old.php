<?php

/**
 * Edges.php
 *
 * This file contains the definition of the {@link Relations} class.
 */

namespace Milko\PHPLib\MongoDB;

use Milko\PHPLib\Container;
use Milko\PHPLib\iEdges;
use Milko\PHPLib\MongoDB\Collection;
use Milko\PHPLib\Edge;

/*=======================================================================================
 *																						*
 *										Edges.php										*
 *																						*
 *======================================================================================*/

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
 *							PROTECTED SELECTION MANAGEMENT INTERFACE					*
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
	public function FindByVertex( $theVertex, $theOptions = NULL )
	{
		//
		// Normalise options.
		//
		$this->normaliseOptions(
			kTOKEN_OPT_FORMAT, kTOKEN_OPT_FORMAT_STANDARD, $theOptions );
		$this->normaliseOptions(
			kTOKEN_OPT_DIRECTION, kTOKEN_OPT_DIRECTION_ANY, $theOptions );

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
			$this->normaliseCursor(
				$this->Connection()->find( $query ), $theOptions );					// ==>

	} // FindByVertex.



/*=======================================================================================
 *																						*
 *						PROTECTED RECORD MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doInsert																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert a document.</h4>
	 *
	 * We overload this method to assert that both source and destination vertices are
	 * present.
	 *
	 * @param mixed					$theDocument		Database native format document.
	 * @return mixed				The inserted document's key.
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\DocumentHandler::saveEdge()
	 */
	protected function doInsert( $theDocument )
	{
		//
		// Check vertices.
		//
		if( ! $theDocument->offsetExists( $this->VertexSource() ) )
			throw new \InvalidArgumentException (
				"Missing source vertex." );										// !@! ==>
		if( ! $theDocument->offsetExists( $this->VertexDestination() ) )
			throw new \InvalidArgumentException (
				"Missing destination vertex." );								// !@! ==>

		return parent::doInsert( $theDocument );

	} // doInsert.


	/*===================================================================================
	 *	doInsertBulk																	*
	 *==================================================================================*/

	/**
	 * <h4>Insert a list of documents.</h4>
	 *
	 * We overload this method to iterate the provided list and call the
	 * {@link doInsertOne()} method on each document.
	 *
	 * @param array					$theList			Native format documents list.
	 * @return array				The document keys.
	 *
	 * @uses Database()
	 * @uses collectionName()
	 * @uses triagens\ArangoDb\DocumentHandler::saveEdge()
	 */
	protected function doInsertBulk( array $theList )
	{
		//
		// Iterate documents.
		//
		foreach( $theList as $document )
		{
			//
			// Check vertices.
			//
			if( ! $document->offsetExists( $this->VertexSource() ) )
				throw new \InvalidArgumentException (
					"Missing source vertex." );									// !@! ==>
			if( ! $document->offsetExists( $this->VertexDestination() ) )
				throw new \InvalidArgumentException (
					"Missing destination vertex." );							// !@! ==>
		}

		return parent::doInsertBulk( $theList );									// ==>

	} // doInsertBulk.




/*=======================================================================================
 *																						*
 *								PROTECTED CONVERSION UTILITIES							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	toDocument																		*
	 *==================================================================================*/

	/**
	 * <h4>Return a standard database document.</h4>
	 *
	 * We overload this method to return {@link \Milko\PHPLib\Relations} instances by
	 * default.
	 *
	 * @param array					$theData			Document as an array.
	 * @return mixed				Native database document object.
	 */
	protected function documentCreate( array $theData )
	{
		return new Edge( $this, $theData );										// ==>

	} // toDocument.



} // class Edges.


?>
