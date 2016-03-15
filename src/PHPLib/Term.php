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




} // class Term.


?>
