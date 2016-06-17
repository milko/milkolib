<?php

/**
 * Predicate.php
 *
 * This file contains the definition of the {@link Predicate} class.
 */

namespace Milko\PHPLib;

/**
 * Global predicate definitions.
 */
require_once('predicates.inc.php');

/*=======================================================================================
 *																						*
 *									Predicate.php										*
 *																						*
 *======================================================================================*/

use Milko\PHPLib\Edge;

/**
 * <h4>Predicate ancestor object.</h4>
 *
 * This class extends {@link Edge} by featuring a term reference holding the relationship
 * predicate term. The property is required and defines the relationship type, derived
 * classes may add further properties that implement specialised functionality or
 * definitions.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		27/04/2016
 */
class Predicate extends Edge
{



/*=======================================================================================
 *																						*
 *								PUBLIC ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	offsetSet																		*
	 *==================================================================================*/

	/**
	 * <h4>Set a value at a given offset.</h4>
	 *
	 * We overload this method to handle the predicate term reference: if provided as a
	 * {@link Document} instance we set its key, if not, we assume the value to be the term
	 * key.
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 *
	 * @uses doCreateReference()
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Skip unset.
		//
		if( $theValue !== NULL )
		{
			//
			// Intercept predicate term.
			//
			switch( $theOffset )
			{
				//
				// Handle term namespace.
				//
				case kTAG_PREDICATE_TERM:
					if( $theValue instanceof Document )
						$theValue = $this->doCreateReference(
							kTAG_PREDICATE_TERM, $theValue );
					break;

			} // Parsing predicate term.

		} // Setting, not resetting.

		//
		// Set offset.
		//
		parent::offsetSet( $theOffset, $theValue );

	} // offsetSet.


	/*===================================================================================
	 *	offsetUnset																		*
	 *==================================================================================*/

	/**
	 * <h4>Reset a value at a given offset.</h4>
	 *
	 * We overload this method to prevent resetting the predicate term reference: this
	 * property is required.
	 *
	 * @param string				$theOffset			Offset.
	 * @throws \RuntimeException
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Parse by offset.
		//
		switch( $theOffset )
		{
			//
			// Prevent deleting predicate term reference.
			//
			case kTAG_PREDICATE_TERM:
				throw new \RuntimeException(
					"The predicate term is required." );						// !@! ==>
		}

		//
		// Call parent method.
		//
		parent::offsetUnset( $theOffset );

	} // offsetUnset.



/*=======================================================================================
 *																						*
 *							PUBLIC PREDICATE MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	GetPredicate																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the predicate term object.</h4>
	 *
	 * This method can be used to retrieve the predicate term object, if the property is not
	 * set, the method will return <tt>NULL</tt>.
	 *
	 * @return Term|NULL			Source vertex or <tt>NULL</tt>.
	 *
	 * @uses ResolveReference()
	 */
	public function GetPredicate()
	{
		return $this->ResolveReference( kTAG_PREDICATE_TERM );						// ==>

	} // GetPredicate.



/*=======================================================================================
 *																						*
 *						STATIC RELATIONSHIP INSTANTIATION INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewPredicate																	*
	 *==================================================================================*/

	/**
	 * <h4>Create a new predicate.</h4>
	 *
	 * This method can be used to create a new predicate holding all required properties.
	 *
	 * @param Collection			$theCollection		Collection.
	 * @param mixed					$thePredicate		Predicate term object or key.
	 * @param mixed					$theSource			Source vertex handle or object.
	 * @param mixed					$theDestination		Destination vertex handle or object.
	 * @param array					$theData			Eventual extra predicate data.
	 * @return Predicate			Predicate object.
	 *
	 * @uses ResolveReference()
	 */
	static function NewPredicate( Collection $theCollection,
								  			 $thePredicate,
											 $theSource, $theDestination,
								  array		 $theData = [] )
	{
		//
		// Instantiate object.
		//
		$predicate = new self( $theCollection, $theData );

		//
		// Set predicate.
		//
		$predicate->offsetSet( kTAG_PREDICATE_TERM, $thePredicate );

		//
		// Set vertices.
		//
		$predicate->offsetSet(
			$predicate->mCollection->VertexSource(), $theSource );
		$predicate->offsetSet(
			$predicate->mCollection->VertexDestination(), $theDestination );

		return $predicate;															// ==>

	} // NewPredicate.



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
	 * In this class we lock the predicate term reference.
	 *
	 * @return array				List of locked offsets.
	 */
	protected function lockedOffsets()
	{
		return array_merge( parent::lockedOffsets(),
			[ kTAG_PREDICATE_TERM ] );								// ==>

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
	 * This class requires the predicate term reference.
	 *
	 * @return array				List of required offsets.
	 */
	protected function requiredOffsets()
	{
		return array_merge( parent::requiredOffsets(),
							[ kTAG_PREDICATE_TERM ] );								// ==>

	} // requiredOffsets.



/*=======================================================================================
 *																						*
 *						PROTECTED SUBDOCUMENT PERSISTENCE INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doCreateReference																*
	 *==================================================================================*/

	/**
	 * <h4>Reference embedded documents.</h4>
	 *
	 * We overload this method to use the term key as the reference to the predicate.
	 *
	 * @param string				$theOffset			Sub-document offset.
	 * @param Document				$theDocument		Subdocument.
	 * @return mixed				The document key or handle.
	 *
	 * @see kTAG_NS
	 */
	protected function doCreateReference( $theOffset, Document $theDocument )
	{
		//
		// Handle predicate term.
		//
		if( $theOffset == kTAG_PREDICATE_TERM )
			return $theDocument[ $theDocument->mCollection->KeyOffset() ];

		return parent::doCreateReference( $theOffset, $theDocument );				// ==>

	} // doCreateReference.


	/*===================================================================================
	 *	doResolveReference																*
	 *==================================================================================*/

	/**
	 * <h4>Resolve embedded documents.</h4>
	 *
	 * We overload this method by retrieving the default terms collection and matching the
	 * predicate key.
	 *
	 * @param string				$theOffset			Sub-document offset.
	 * @param mixed					$theReference		Subdocument reference.
	 * @return mixed				The document key or handle.
	 *
	 * @uses Database::NewTermsCollection()
	 * @uses Collection::FindByKey()
	 */
	protected function doResolveReference( $theOffset, $theReference )
	{
		//
		// Handle namespace.
		//
		if( $theOffset == kTAG_PREDICATE_TERM )
			return
				$this->mCollection->Database()->NewTermsCollection()
					->FindByKey( $theReference );									// ==>

		return parent::doResolveReference( $theOffset, $theReference );				// ==>

	} // doResolveReference.




} // class Predicate.


?>
