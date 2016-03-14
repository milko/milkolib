<?php

/**
 * Term.php
 *
 * This file contains the definition of the {@link Term} class.
 */

namespace Milko\PHPLib;

use Milko\PHPLib\Document;

/*=======================================================================================
 *																						*
 *										Term.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Term object.</h4>
 *
 * This class implements an object that represents a concept or term, it is the ancestor of
 * all objects involved in the data dictionary and ontology.
 *
 * The object implements the following global properties:
 *
 * <ul>
 * 	<li><tt>{@link TermNamespace()</tt>: Manage the term namespace reference
 * 		(<tt>{@link kTAG_NS}</tt>).
 * 	<li><tt>{@link LocalIdentifier()</tt>: Manage the term local identifier
 * 		(<tt>{@link kTAG_LID}</tt>).
 * 	<li><tt>{@link GlobalIdentifier()</tt>: Manage the term global identifier
 * 		(<tt>{@link kTAG_GID}</tt>).
 * 	<li><tt>{@link Name()</tt>: Manage the term name (<tt>{@link kTAG_NAME}</tt>).
 * 	<li><tt>{@link Description()</tt>: Manage the term description
 * 		(<tt>{@link kTAG_DESCRIPTION}</tt>).
 * </ul>
 *
 * As with {@link Container} objects these properties can be accessed also via offsets.
 *
 * The class implements also a set of procedures which handle the consistency of the object,
 * part of these are applied as object data is modified and the {@link Validate()} method
 * can be called before the object will be stored.
 *
 * There is a public method that can be used to compile the global identifier,
 * {@link MakeGID()}, which expects the terms collection and the reference to the term's
 * namespace object.
 *
 *	@package	Terms
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		14/03/2016
 */
class Term extends Document
{



/*=======================================================================================
 *																						*
 *								PUBLIC ARRAY ACCESS INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	offsetUnset																		*
	 *==================================================================================*/

	/**
	 * <h4>Reset a value at a given offset.</h4>
	 *
	 * We overload this method to prevent resetting the local identifier, global identifier
	 * and name: these properties are required.
	 *
	 * @param string				$theOffset			Offset.
	 * @throws \BadMethodCallException
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Prevent deleting local identifier, global identifier and name.
		//
		switch( $theOffset )
		{
			case kTAG_LID:
			case kTAG_GID:
			case kTAG_NAME:
				throw new \BadMethodCallException(
					"The name, local and global identifiersare required." );	// !@! ==>
		}

		//
		// Call parent method.
		//
		parent::offsetUnset( $theOffset );

	} // offsetUnset.



/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	TermNamespace																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage the term's namespace.</h4>
	 *
	 * This method can be used to set or retrieve the term's namespace, it accepts the
	 * following parameters:
	 *
	 * <ul>
	 * 	<li><b>$theValue</b>: Either the value or the operation:
	 * 	 <ul>
	 * 		<li><tt>NULL</tt>: Return the current value; the next parameter will be ignored.
	 * 		<li><tt>FALSE</tt>: Reset the current value; the next parameter will be ignored.
	 * 		<li><em>other</em>: Any other value represents the new namespace; depending if
	 * 			the next parameter was provided:
	 * 		 <ul>
	 * 			<li><em>Collection provided</em>: If the collection was provided, it is
	 * 				assumed that the value represents the namespace term reference, in that
	 * 				case, the method will retrieve the namespace term's global identifier
	 * 				and compile the current's global identifier, if the local identifier is
	 * 				already set.
	 * 			<li><em>Collection not provided</em>: If the collection was not provided, it
	 * 				is assumed that the value is the namespace term global identifier, in
	 * 				that case, the method will compile the current's global identifier, if
	 * 				the local identifier is already set.
	 * 		 </ul>
	 * 	 </ul>
	 * 	<li><b>$theCollection</b>: The collection object of the term, or <tt>NULL</tt>; if
	 * 		you provide a value other than {@link Collection} or <tt>NULL</tt>, the method
	 * 		will raise an exception.
	 * </ul>
	 *
	 * The method will return the current namespace property value.
	 *
	 * @param mixed					$theValue			Namespace or <tt>NULL</tt>.
	 * @param mixed					$theCollection		Term collection or <tt>NULL</tt>.
	 * @return mixed				Term namespace reference.
	 * @throws BadMethodCallException
	 *
	 * @see kTAG_NS
	 */
	public function TermNamespace( $theValue, $theCollection = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->offsetGet( kTAG_NS );										// ==>

		//
		// Reset namespace.
		//
		if( $theValue === FALSE )
		{
			//
			// Reset namespace.
			// We don't need to check if it is required.
			//
			parent::offsetUnset( kTAG_NS );

			return NULL;															// ==>

		} // Reset namespace.

		//
		// Handle collection.
		//
		if( ! ($theCollection instanceof Collection) )
			throw new \BadMethodCallException(
				"Expecting a Collection object." );								// !@! ==>

		//
		// Retrieve collection.

	} // TermNamespace.




} // class Term.


?>
