<?php

/**
 * Edge.php
 *
 * This file contains the definition of the {@link Edge} class.
 */

namespace Milko\PHPLib;

/**
 * Global tag definitions.
 */
require_once( 'tags.inc.php' );

/**
 * Global token definitions.
 */
require_once( 'tokens.inc.php' );

use Milko\PHPLib\Document;
use Milko\PHPLib\Collection;

/*=======================================================================================
 *																						*
 *										Edge.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Relationship ancestor object.</h4>
 *
 * This class extends {@link Document} by implementing a relationship document, it adds two
 * properties: {@link Collection::RelationSourceOffset()} that references the source vertex
 * of the relationship and {@link RelationDestinationOffset()} that references the
 * destination vertex of the relationship, both properties are document handles; the current
 * document represents the relationship predicate.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		29/03/2016
 */
class Edge extends Document
{



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
	 * @uses Collection::RelationSourceOffset()
	 * @uses Collection::RelationDestinationOffset()
	 */
	protected function lockedOffsets()
	{
		return array_merge( parent::lockedOffsets(),
							$this->mCollection->RelationSourceOffset(),
							$this->mCollection->RelationDestinationOffset() );		// ==>

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
	 * This class doesn't feature any required offsets.
	 *
	 * @return array				List of required offsets.
	 *
	 * @uses Collection::RelationSourceOffset()
	 * @uses Collection::RelationDestinationOffset()
	 */
	protected function requiredOffsets()
	{
		return array_merge( parent::requiredOffsets(),
							$this->mCollection->RelationSourceOffset(),
							$this->mCollection->RelationDestinationOffset() );		// ==>

	} // requiredOffsets.




} // class Relationship.


?>
