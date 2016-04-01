<?php

/**
 * Relation.php
 *
 * This file contains the definition of the {@link Relation} class.
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

use \Milko\PHPLib\Document;
use \Milko\PHPLib\ArangoDB\Relations;

/*=======================================================================================
 *																						*
 *									Relation.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Relationship ancestor object.</h4>
 *
 * This class extends {@link Document} by implementing a relationship document, it adds two
 * properties: {@link \Milko\PHPLib\Collection::RelationSourceOffset()} that references the
 * source vertex of the relationship and
 * {@link \Milko\PHPLib\CollectionRelationDestinationOffset()} that references the
 * destination vertex of the relationship, both properties are document handles; the current
 * document represents the relationship predicate.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		30/03/2016
 */
class Relation extends \Milko\PHPLib\Document
{



/*=======================================================================================
 *																						*
 *										MAGIC											*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	__construct																		*
	 *==================================================================================*/

	/**
	 * <h4>Instantiate class.</h4>
	 *
	 * We override the inherited constructor to assert that the provided collection is an
	 * edge collection (type = {@link \triagens\ArangoDb\Collection::TYPE_EDGE}).
	 *
	 * @param \Milko\PHPLib\Collection			$theCollection		Collection name.
	 * @param array					$theData			Document data.
	 * @throws \InvalidArgumentException
	 *
	 * @see \triagens\ArangoDb\Collection::TYPE_EDGE
	 */
	public function __construct( \Milko\PHPLib\Collection $theCollection, $theData = [] )
	{
		//
		// Assert collection type.
		//
//		if( ! ($theCollection instanceof \Milko\PHPLib\ArangoDB\Relations) )
//			throw new \InvalidArgumentException (
//				"Invalid collection type: "
//				."expecting an edge collection." );								// !@! ==>

		//
		// Call parent constructor.
		//
		Container::__construct( $theData );

		//
		// Set collection.
		//
		$this->mCollection = $theCollection;

		//
		// Save old class.
		//
		$class = $this->offsetGet( $this->mCollection->ClassOffset() );

		//
		// Add class.
		// Note that we overwrite the eventual existing class name
		// and we use the ancestor class method, since the class property is locked.
		//
		\ArrayObject::offsetSet(
			$this->mCollection->ClassOffset(), get_class( $this ) );

		//
		// Reset modification state.
		//
		if( $class == $this->offsetGet( $this->mCollection->ClassOffset() ) )
			$this->mStatus &= (~ self::kFLAG_DOC_MODIFIED);

	} // Constructor.



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
							[ $this->mCollection->VertexSource(),
							  $this->mCollection->VertexDestination() ] );			// ==>

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
							[ $this->mCollection->VertexSource(),
							  $this->mCollection->VertexDestination() ] );			// ==>

	} // requiredOffsets.




} // class Relation.


?>
