<?php

/**
 * Relations.php
 *
 * This file contains the definition of the {@link Relations} class.
 */

namespace Milko\PHPLib\MongoDB;

use Milko\PHPLib\iRelations;
use Milko\PHPLib\MongoDB\Collection;

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
 *							PUBLIC DOCUMENT INSTANTIATION INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewDocument																		*
	 *==================================================================================*/

	/**
	 * <h4>Return a {@link Document} instance.</h4>
	 *
	 * We overload this method to return a {@link \Milko\PHPLib\Document} instance of the
	 * correct class, or a {@link \Milko\PHPLib\Relation} instance.
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

		return new \Milko\PHPLib\Relation( $this, $document );						// ==>

	} // NewDocument.



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
	 * @uses Database()
	 * @uses NewDocument()
	 * @uses NewDocumentKey()
	 * @uses NewDocumentHandle()
	 * @uses collectionName()
	 * @uses normaliseOptions()
	 *
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
			$theVertex = $theVertex->Handle();

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
					"Invalid conversion format code." );					// !@! ==>
		}

		//
		// Make selection.
		//
		$result = $this->Connection()->find( $query );

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

	} // FindByVertex.



} // class Relations.


?>
