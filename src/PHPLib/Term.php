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
	/**
	 * Namespace global identifier.
	 *
	 * This attribute stores the current term namespace global identifier.
	 *
	 * The attribute is used to cache the namespace.
	 *
	 * @var string
	 */
	protected $mNamespaceGID = '';



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
	 * We overload the parent constructor to pass the provided object properties through
	 * the class methods, this is to ensure that the global identifier is set if missing.
	 *
	 * @param Collection			$theCollection		Collection name.
	 * @param array					$theData			Document data.
	 *
	 * @uses setTermNamespace()
	 * @uses setTermIdentifier()
	 * @see kTAG_NS
	 * @see kTAG_GID
	 */
	public function __construct( Collection $theCollection, $theData = [] )
	{
		//
		// Convert data to array.
		//
		$theData = (array)$theData;

		//
		// Strip identifiers from data.
		//
		$ns = NULL;
		if( array_key_exists( kTAG_NS, $theData ) )
		{
			$ns = $theData[ kTAG_NS ];
			unset( $theData[ kTAG_NS ] );
		}
		$lid = NULL;
		if( array_key_exists( kTAG_LID, $theData ) )
		{
			$lid = $theData[ kTAG_LID ];
			unset( $theData[ kTAG_LID ] );
		}
		$gid = NULL;
		if( array_key_exists( kTAG_GID, $theData ) )
		{
			$gid = $theData[ kTAG_GID ];
			unset( $theData[ kTAG_GID ] );
		}

		//
		// Call parent constructor.
		//
		parent::__construct( $theCollection, $theData );

		//
		// Set namespace.
		//
		if( $ns !== NULL )
			$this->offsetSet( kTAG_NS, $ns );

		//
		// Set local identifier.
		//
		if( $lid !== NULL )
			$this->offsetSet( kTAG_LID, $lid );

		//
		// Set global identifier.
		//
		if( $gid !== NULL )
			$this->offsetSet( kTAG_GID, $gid );

	} // Constructor.



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
	 * We overload this method to update the term's global identifier ({@link kTAG_GID})
	 * when setting the namespace ({@link kTAG_NS}) or local identifier ({@link kTAG_LID}).
	 *
	 * @param string				$theOffset			Offset.
	 * @param mixed					$theValue			Value to set at offset.
	 *
	 * @uses setTermNamespace()
	 * @uses setTermIdentifier()
	 * @see kTAG_NS
	 * @see kTAG_LID
	 */
	public function offsetSet( $theOffset, $theValue )
	{
		//
		// Skip unset.
		//
		if( $theValue !== NULL )
		{
			//
			// Intercept identifiers.
			//
			switch( $theOffset )
			{
				//
				// Handle term namespace.
				//
				case kTAG_NS:
					$this->setTermNamespace( $theValue );
					break;

				//
				// Handle term identifier.
				//
				case kTAG_LID:
					$this->setTermIdentifier( $theValue );
					break;

			} // Parsing identifiers.

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
	 * We overload this method to prevent resetting the local identifier, global identifier
	 * and name: these properties are required.
	 *
	 * We also update the term global identifier ({@link kTAG_GID}) when resetting the
	 * namespace.
	 *
	 * @param string				$theOffset			Offset.
	 * @throws \RuntimeException
	 *
	 * @uses setTermNamespace()
	 * @see kTAG_NS
	 * @see kTAG_LID
	 * @see kTAG_GID
	 * @see kTAG_NAME
	 */
	public function offsetUnset( $theOffset )
	{
		//
		// Parse by offset.
		//
		switch( $theOffset )
		{
			//
			// Handle term namespace.
			//
			case kTAG_NS:
				$this->setTermNamespace( NULL );
				break;

			//
			// Prevent deleting local identifier, global identifier and name.
			//
			case kTAG_LID:
			case kTAG_GID:
			case kTAG_NAME:
				throw new \RuntimeException(
					"The name, local and global identifiers required." );		// !@! ==>
		}

		//
		// Call parent method.
		//
		parent::offsetUnset( $theOffset );

	} // offsetUnset.



/*=======================================================================================
 *																						*
 *								PUBLIC VALIDATION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Validate																		*
	 *==================================================================================*/

	/**
	 * <h4>Validate object.</h4>
	 *
	 * We overload this method to set the object key ({@link Collection::KeyOffset()}) by
	 * generating the MD5 hash of the current term's global identifier ({@link kTAG_GID}).
	 *
	 * This means that when you insert a term, you <em>must</em> call this method first;
	 * nothe that this is done automatically by the collection.
	 *
	 * The method will set the key only if the current term is not persistent
	 * ({@link IsPersistent()}).
	 */
	public function Validate()
	{
		//
		// Call parent method.
		// In this class we only check for required fields,
		// which is taken care of by the parent.
		//
		parent::Validate();

		//
		// Set key.
		//
		if( ! $this->IsPersistent() )
			$this->offsetSet(
				$this->mCollection->KeyOffset(),
				md5( $this->offsetGet( kTAG_GID ) ) );

	} // Validate.



/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Name																			*
	 *==================================================================================*/

	/**
	 * <h4>Manage term name.</h4>
	 *
	 * This method can be used to set, retrieve and delete individual term names expressed
	 * in different languages, the method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theLanguage</tt>: The language code or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the full set of names, the next parameter is ignored.
	 *		<li><tt>FALSE</tt>: Delete the full set of names and return the old value, the
	 * 			next parameter is ignored.
	 *		<li><em>other</em>: Use the value as the language code, the next parameter will
	 * 			be considered the name in tha provided language.
	 *	 </ul>
	 *	<li><tt>$theValue</tt>: The name or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the name in the provided language.
	 *		<li><tt>FALSE</tt>: Delete the the name in the provided language and return the
	 * 			old name.
	 *		<li><em>other</em>: Set the the name in the provided language to the provided
	 * 			value and return it.
	 *	 </ul>
	 * </ul>
	 *
	 * @param string				$theLanguage		Language code.
	 * @param string				$theName			Term name in the provided language.
	 * @return mixed				Old or current name.
	 *
	 * @uses getTermKey()
	 * @uses setTermNamespace()
	 */
	public function Name( $theLanguage = NULL, $theName = NULL )
	{
		return $this->manageIndexedProperty( kTAG_NAME, $theLanguage, $theName );	// ==>

	} // Name.


	/*===================================================================================
	 *	Definition																		*
	 *==================================================================================*/

	/**
	 * <h4>Manage term definition.</h4>
	 *
	 * This method can be used to set, retrieve and delete individual term definitions
	 * expressed in different languages, the method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><tt>$theLanguage</tt>: The language code or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the full set of definitions, the next parameter is
	 * 			ignored.
	 *		<li><tt>FALSE</tt>: Delete the full set of definitions and return the old value,
	 * 			the next parameter is ignored.
	 *		<li><em>other</em>: Use the value as the language code, the next parameter will
	 * 			be considered the definition in tha provided language.
	 *	 </ul>
	 *	<li><tt>$theValue</tt>: The definition or operation:
	 *	 <ul>
	 *		<li><tt>NULL</tt>: Return the definition in the provided language.
	 *		<li><tt>FALSE</tt>: Delete the the definition in the provided language and
	 * 			return the old definition.
	 *		<li><em>other</em>: Set the the definition in the provided language to the
	 * 			provided value and return it.
	 *	 </ul>
	 * </ul>
	 *
	 * @param string				$theLanguage		Language code.
	 * @param string				$theDefinition		Term definition in the provided
	 * 													language.
	 * @return mixed				Old or current definition.
	 *
	 * @uses getTermKey()
	 * @uses setTermNamespace()
	 */
	public function Definition( $theLanguage = NULL, $theName = NULL )
	{
		return
			$this->manageIndexedProperty(
				kTAG_DESCRIPTION, $theLanguage, $theDefinition );					// ==>

	} // Definition.


	/*===================================================================================
	 *	NamespaceTerm																	*
	 *==================================================================================*/

	/**
	 * <h4>Return namespace term.</h4>
	 *
	 * This method can be used to retrieve the namespace term, it will return the term
	 * document if the namespace is set, or <tt>NULL</tt>.
	 *
	 * @return Term					Namespace term or <tt>NULL</tt>.
	 *
	 * @uses getTermKey()
	 * @uses setTermNamespace()
	 */
	public function NamespaceTerm( $theLanguage = NULL, $theName = NULL )
	{
		//
		// Get namespace.
		//
		if( ($namespace = $this->offsetGet( kTAG_NS )) !== NULL )
		{
			//
			// Handle namespace object.
			//
			if( $namespace instanceof Document )
				return $namespace;													// ==>

			return
				$this->mCollection->FindByKey( $namespace );						// ==>

		} // Has namespace.

		return NULL;																// ==>

	} // NamespaceTerm.



/*=======================================================================================
 *																						*
 *								PUBLIC NAMESPACE INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	SetNamespaceByGID																*
	 *==================================================================================*/

	/**
	 * <h4>Set the namespace given a global identifier.</h4>
	 *
	 * This method can be used to set the current term's namespace ({@link kTAG_NS}) given
	 * the namespace term's global identifier, the method will use the current term's
	 * collection to locate the right term; if the provided identifier doesn't match any
	 * terms, the method will raise an exception.
	 *
	 * If you provide <tt>NULL</tt>, or an empty string, the method will reset the
	 * namespace.
	 *
	 * @param string				$theIdentifier		Namespace global identifier.
	 * @throws \InvalidArgumentException
	 *
	 * @uses getTermKey()
	 * @uses setTermNamespace()
	 */
	public function SetNamespaceByGID( $theIdentifier )
	{
		//
		// Reset namespace.
		//
		if( ! strlen( $theIdentifier ) )
			$this->setTermNamespace( $theIdentifier );

		//
		// Reference namespace.
		//
		else
		{
			//
			// Locate term.
			//
			$key = $this->getTermKey( (string)$theIdentifier );
			if( $key !== NULL )
				$this->offsetSet( kTAG_NS, $key );
			else
				throw new \InvalidArgumentException(
					"Unknown global identifier [$theIdentifier]." );			// !@! ==>

		} // Provided identifier.

	} // SetNamespaceByGID.



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
	 * We overload this method to add the following offsets:
	 *
	 * <ul>
	 * 	<li><tt>kTAG_NS</tt>: Term namespace.
	 * 	<li><tt>kTAG_LID</tt>: Local identifier.
	 * 	<li><tt>kTAG_GID</tt>: Global identifier.
	 * </ul>
	 *
	 * @return array				List of locked offsets.
	 */
	protected function lockedOffsets()
	{
		return
			array_merge(
				parent::lockedOffsets(),
				[ kTAG_NS, kTAG_LID, kTAG_GID ] );									// ==>

	} // lockedOffsets.


	/*===================================================================================
	 *	requiredOffsets																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the list of required offsets.</h4>
	 *
	 * We overload this method to add the following offsets:
	 *
	 * <ul>
	 * 	<li><tt>kTAG_LID</tt>: Local identifier.
	 * 	<li><tt>kTAG_GID</tt>: Global identifier.
	 * 	<li><tt>kTAG_NAME</tt>: Term name or label.
	 * </ul>
	 *
	 * @return array				List of required offsets.
	 */
	protected function requiredOffsets()
	{
		return
			array_merge(
				parent::requiredOffsets(),
				[ kTAG_LID, kTAG_GID, kTAG_NAME ] );								// ==>

	} // requiredOffsets.



/*=======================================================================================
 *																						*
 *								PROTECTED PERSISTENCE INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doDelete																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete object.</h4>
	 *
	 * We overload this method to ensure the current term is not used as a namespace
	 * elsewhere.
	 *
	 * @return int					The number of deleted records.
	 * @throws \RuntimeException
	 *
	 * @uses Collection::CountByExample()
	 */
	protected function doDelete()
	{
		//
		// Get usage count.
		//
		$count =
			$this->mCollection->CountByExample(
				[ kTAG_NS => $this->offsetGet( $this->mCollection->KeyOffset() ) ] );
		if( $count )
			throw new \RuntimeException (
				"Cannot delete the term: " .
				"it is the namespace of $count other terms." );					// !@! ==>

		return parent::doDelete();													// ==>

	} // doDelete.



/*=======================================================================================
 *																						*
 *							PROTECTED IDENTIFIER INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	setTermNamespace																*
	 *==================================================================================*/

	/**
	 * <h4>Set term namespace.</h4>
	 *
	 * This method can be used to cache the term namespace ({@link kTAG_NS}) and update the
	 * term global identifier ({@link kTAG_GID}), the method expects a single parameter
	 * which represents the namespace term object key ({@link Collection::KeyOffset()}).
	 *
	 * If you provide <tt>NULL</tt>, the namespace will be reset.
	 *
	 * This method makes use of an attribute, {@link $mNamespaceGID}, which caches the
	 * current term's namespace term global identifier.
	 *
	 * The method will <em>not set the namespace property</em>, this should be done by the
	 * caller: the only property changed by this method is the current term's global
	 * identifier ({@link kTAG_GID}).
	 *
	 * If the term cannot be found, the method will raise an exception.
	 *
	 * By default we use the current term's collection for probing the namespace.
	 *
	 * @param mixed					$theNamespace		Term namespace key.
	 * @throws \RuntimeException
	 *
	 * @uses getTermGID()
	 * @uses makeTermGID()
	 * @see kTAG_NS
	 * @see kTAG_LID
	 * @see kTAG_GID
	 */
	protected function setTermNamespace( $theNamespace )
	{
		//
		// Check if namespace changed.
		//
		if( $this->offsetGet( kTAG_NS ) != $theNamespace )
		{
			//
			// Reset namespace.
			//
			if( $theNamespace === NULL )
				$this->mNamespaceGID = '';

			//
			// Cache namespace global identifier.
			//
			else
			{
				//
				// Get namespace global identifier.
				//
				$gid = $this->getTermGID( $theNamespace );
				if( $gid !== NULL )
					$this->mNamespaceGID = $gid;
				else
					throw new \RuntimeException(
						"Unknown term [$theNamespace]." );						// !@! ==>

			} // Provided new namespace.

			//
			// Set current term global identifier.
			//
			$this->offsetSet(
				kTAG_GID,
				$this->makeTermGID(
					$this->mNamespaceGID,
					$this->offsetGet( kTAG_LID ) ) );

		} // Namespace changed.

	} // setTermNamespace.


	/*===================================================================================
	 *	setTermIdentifier																*
	 *==================================================================================*/

	/**
	 * <h4>Set term identifier.</h4>
	 *
	 * This method can be used to set the term local identifier ({@link kTAG_LID}) and
	 * update the term global identifier({@link kTAG_GID}), the method expects a single
	 * parameter which represents the current term's local identifier.
	 *
	 * If you provide <tt>NULL</tt>, or an empty string, the method will raise an exception,
	 * since the local identifier is required.
	 *
	 * The method will <em>not set the identifier property</em>, this should be done by the
	 * caller: the only property changed by this method is the current term's global
	 * identifier ({@link kTAG_GID}).
	 *
	 * @param string				$theIdentifier		Term local identifier.
	 * @throws \InvalidArgumentException
	 *
	 * @uses makeTermGID()
	 * @see kTAG_LID
	 * @see kTAG_GID
	 */
	protected function setTermIdentifier( $theIdentifier )
	{
		//
		// Check if namespace changed.
		//
		if( $this->offsetGet( kTAG_LID ) != (string)$theIdentifier )
		{
			//
			// Check identifier.
			//
			if( ! strlen( $theIdentifier ) )
				throw new \InvalidArgumentException(
					"Cannot reset term local identifier." );					// !@! ==>

			//
			// Set current term global identifier.
			//
			$this->offsetSet(
				kTAG_GID,
				$this->makeTermGID(
					$this->mNamespaceGID,
					(string)$theIdentifier ) );

		} // Identifier changed.

	} // setTermIdentifier.


	/*===================================================================================
	 *	makeTermGID																		*
	 *==================================================================================*/

	/**
	 * <h4>Compute terrm global identifier.</h4>
	 *
	 * This method will compute the current term's global identifier ({@link kTAG_GID})
	 * using the cached namespace global identifier ({@link mNamespaceGID}) and the current
	 * term's local identifier ({@link kTAG_LID}), and return the value.
	 *
	 * @param string				$theNamespace		Term namespace global identifier.
	 * @param string				$theIdentifier		Term local identifier.
	 * @return string				Term global identifier.
	 *
	 * @see kTOKEN_NAMESPACE_SEPARATOR
	 */
	protected function makeTermGID( $theNamespace, $theIdentifier )
	{
		//
		// Handle namespace.
		//
		if( strlen( $theNamespace ) )
			return $theNamespace . kTOKEN_NAMESPACE_SEPARATOR . $theIdentifier;		// ==>

		return $theIdentifier;														// ==>

	} // makeTermGID.



/*=======================================================================================
 *																						*
 *							PROTECTED REFERENCE INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	getTermGID																		*
	 *==================================================================================*/

	/**
	 * <h4>Return terrm global identifier.</h4>
	 *
	 * Given a term key ({@link Collection::KeyOffset()}) this method will return the
	 * corresponding term's global identifier ({@link kTAG_GID}), or <tt>NULL</tt> if the
	 * key is not found.
	 *
	 * By default we use the current term's collection.
	 *
	 * @param mixed					$theKey				Term key.
	 * @return string				Term global identifier or <tt>NULL</tt>.
	 *
	 * @uses Collection::FindByKey()
	 * @see kTAG_GID
	 */
	protected function getTermGID( $theKey )
	{
		//
		// Select term.
		//
		$term = $this->mCollection->FindByKey( $theKey );
		if( $term !== NULL )
			return $term[ kTAG_GID ];												// ==>

		return NULL;																// ==>

	} // getTermGID.


	/*===================================================================================
	 *	getTermKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Return terrm key.</h4>
	 *
	 * Given a term global identifier ({@link kTAG_GID}) this method will return the
	 * corresponding term's key ({@link  ({@link Collection::KeyOffset()}), or <tt>NULL</tt>
	 * if the global identifier matches no term.
	 *
	 * By default we use the current term's collection.
	 *
	 * @param mixed					$theGID				Term global identifier.
	 * @return string				Term key or <tt>NULL</tt>.
	 *
	 * @uses Collection::FindByExample()
	 * @see kTAG_GID
	 * @see kTOKEN_OPT_LIMIT
	 */
	protected function getTermKey( $theGID )
	{
		//
		// Select term.
		//
		$term =
			$this->mCollection->FindByExample(
				[ kTAG_GID => $theGID ],
				[ kTOKEN_OPT_LIMIT => 1 ] );
		if( count( $term ) )
			return $term[ 0 ][ $this->mCollection->KeyOffset() ];					// ==>

		return NULL;																// ==>

	} // getTermKey.




} // class Term.


?>
