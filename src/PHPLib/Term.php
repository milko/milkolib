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
 * Objects of this class feature the following properties:
 *
 * <ul>
 * 	<li><tt>{@link kTAG_NS}</tt>: This represents the term <em>namespace</em>, it is a
 * 		document handle ({@link Collection::NewDocumentHandle()}) that references the term
 * 		which is the current term's namespace.
 * 		<em>This property is optional</em>.
 * 	<li><tt>{@link kTAG_LID}</tt>: This represents the term <em>local identifier</em>, it is
 * 		a string code that uniquely identifies the term among all terms belonging to its
 * 		namespace.
 * 		<em>This property is required</em>.
 * 	<li><tt>{@link kTAG_GID}</tt>: This represents the term <em>global identifier</em>, it is
 * 		a string code that uniquely identifies the term among all terms in all namespaces.
 * 		This value is computed by default by concatenating the namespace's term
 * 		({@link kTAG_NS}) global identifier with the current term's local identifier
 * 		({@link kTAG_LID}), separated by the {@link kTOKEN_NAMESPACE_SEPARATOR} token.
 * 		<em>This property is required</em>.
 * 	<li><tt>{@link kTAG_NAME}</tt>: This represents the term <em>name</em> or
 * 		<em>label</em>, it is a short description or human readable identifier that can be
 * 		used to label or name the term.
 * 		<em>This property is required</em>.
 * 	<li><tt>{@link kTAG_DESCRIPTION}</tt>: This represents the term <em>description</em> or
 * 		<em>definition</em>, it is a text which describes in detail what the term represents
 * 		and, in the case of the term, may be considered its definition.
 * 		<em>This property is optional</em>.
 * </ul>
 *
 * The term's {@link Collection::OffsetKey()} property is set by extracting the <tt>MD5</tt>
 * checksum of its {@link kTAG_GID} global identifier
 *
 * The {@link Container::ClassOffset()} property is automatically set in the constructor and
 * should not be changed.
 *
 * Finally, the {@link RevisionOffset()} property is managed directly by the database
 * driver.
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
