<?php

/**
 * Relations.php
 *
 * This file contains the definition of the {@link Relations} class.
 */

namespace Milko\PHPLib\MongoDB;

use Milko\PHPLib\iRelations;
use Milko\PHPLib\MongoDB\Collection;
use Milko\PHPLib\Relation;

/*=======================================================================================
 *																						*
 *									Relations.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Relationships collection object.</h4>
 *
 * This <em>concrete</em> class is the implementation of an edge collection, it overloads
 * the inherited {@link Collection} interface and implements the
 * {@link iRelations} interface.
 *
 *	@package	Data
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		01/04/2016
 */
class Relations extends Collection
				implements iRelations
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
		if( $theVertex instanceof \Milko\PHPLib\Container )
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
	protected function toDocument( array $theData )
	{
		return new Relation( $this, $theData );										// ==>

	} // toDocument.



} // class Relations.


?>
