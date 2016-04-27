<?php

/**
 * Edge.php
 *
 * This file contains the definition of the {@link Edge} class.
 */

namespace Milko\PHPLib;

/**
 * Global predicate definitions.
 */
require_once('predicates.inc.php');

/*=======================================================================================
 *																						*
 *										Edge.php										*
 *																						*
 *======================================================================================*/

use Milko\PHPLib\Document;

/**
 * <h4>Relationship ancestor object.</h4>
 *
 * This class extends {@link Document} by implementing a relationship, it adds two
 * properties: {@link iEdges::VertexSource()} that references the source vertex of the
 * relationship and {@link iEdges::VertexDestination()} that references the destination
 * vertex of the relationship, both properties are document handles; the current document
 * represents the relationship predicate.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		30/03/2016
 */
class Edge extends Document
{



/*=======================================================================================
 *																						*
 *							PUBLIC VERTEX MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	GetSource																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the source vertex object.</h4>
	 *
	 * This method can be used to retrieve the source vertex object, if the property is not
	 * set, the method will return <tt>NULL</tt>.
	 *
	 * @return Document|NULL		Source vertex or <tt>NULL</tt>.
	 *
	 * @uses ResolveReference()
	 */
	public function GetSource()
	{
		return $this->ResolveReference( $this->mCollection->VertexSource() );		// ==>

	} // GetSource.


	/*===================================================================================
	 *	GetDestination																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the destination vertex object.</h4>
	 *
	 * This method can be used to retrieve the destination vertex object, if the property is
	 * not set, the method will return <tt>NULL</tt>.
	 *
	 * @return Document|NULL		Destination vertex or <tt>NULL</tt>.
	 *
	 * @uses ResolveReference()
	 */
	public function GetDestination()
	{
		return $this->ResolveReference( $this->mCollection->VertexDestination() );	// ==>

	} // GetDestination.



/*=======================================================================================
 *																						*
 *							PROTECTED VALIDATION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	lockedOffsets																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the list of locked offsets.</h4>
	 *
	 * This method should return the list of offsets which cannot be modified once the
	 * object has been committed to its {@link Container}.
	 *
	 * By default the key, revision and class should be locked.
	 *
	 * @return array				List of locked offsets.
	 *
	 * @uses Edge::VertexSource()
	 * @uses Edge::VertexDestination()
	 */
	protected function lockedOffsets()
	{
		return array_merge( parent::lockedOffsets(),
			[ $this->mCollection->VertexSource(),
			  $this->mCollection->VertexDestination() ] );							// ==>

	} // lockedOffsets.


	/*===================================================================================
	 *	requiredOffsets																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the list of required offsets.</h4>
	 *
	 * This method should return the list of offsets which are required prior to saving the
	 * document in its collection.
	 *
	 * This class requires source an destination vertices.
	 *
	 * @return array				List of required offsets.
	 *
	 * @uses Edge::VertexSource()
	 * @uses Edge::VertexDestination()
	 */
	protected function requiredOffsets()
	{
		return array_merge( parent::requiredOffsets(),
			[ $this->mCollection->VertexSource(),
			  $this->mCollection->VertexDestination() ] );							// ==>

	} // requiredOffsets.




} // class Edge.


?>
