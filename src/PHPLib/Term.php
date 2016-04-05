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
 * 	<li><tt>{@link kTAG_NS}</tt>: This represents the term <em>namespace</em>, it is the
 * 		document key of the term which is the current term's namespace. In this class if you
 * 		set a term object in this offset, its key will be extracted and set: the object will
 * 		not be committed when the current term is stored.
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
 * Term objects <em>should be stored in a default collection</em>, for this reason, the
 * constructor will enforce that collection in the constructor.
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
	 * <em>It is the responsibility of the caller to ensure the term is associated with the
	 * correct collection: use {@link Database::TermsCollection()} to get the right
	 * collection object</em>.
	 *
	 * @param Collection			$theCollection		Collection name.
	 * @param array					$theData			Document data.
	 *
	 * @see kTAG_NS
	 * @see kTAG_LID
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
					if( $theValue instanceof Document )
						$theValue = $this->doCreateReference( kTAG_NS, $theValue );
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
	 *
	 * @uses Collection()
	 * @uses IsPersistent()
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
				$this->Collection()->KeyOffset(),
				md5( $this->offsetGet( kTAG_GID ) ) );

	} // Validate.


	/*===================================================================================
	 *	Delete																			*
	 *==================================================================================*/

	/**
	 * <h4>Delete object.</h4>
	 *
	 * We overload this method to check whether the current term is used as the namespace of
	 * other terms, in that case we raise an exception.
	 *
	 * @return int					The number of deleted records.
	 *
	 * @uses Collection()
	 */
	public function Delete()
	{
		//
		// Get usage count.
		//
		$count =
			$this->Collection()->CountByExample(
				[ kTAG_NS => $this->offsetGet( $this->Collection()->KeyOffset() ) ] );
		if( $count )
			throw new \RuntimeException (
				"Cannot delete the term: " .
				"it is the namespace of $count other terms." );					// !@! ==>

		return parent::Delete();													// ==>

	} // Delete.



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
	 * @uses manageIndexedProperty()
	 */
	public function Name( $theLanguage = NULL, $theName = NULL )
	{
		return
			$this->manageIndexedProperty(
				kTAG_NAME, $theLanguage, $theName );								// ==>

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
	 * @uses manageIndexedProperty()
	 */
	public function Definition( $theLanguage = NULL, $theDefinition = NULL )
	{
		return
			$this->manageIndexedProperty(
				kTAG_DESCRIPTION, $theLanguage, $theDefinition );					// ==>

	} // Definition.


	/*===================================================================================
	 *	SetNamespaceByTerm																*
	 *==================================================================================*/

	/**
	 * <h4>Set namespace from term object.</h4>
	 *
	 * This method can be used to set the current namespace with the provided term object,
	 * the method expects a term object as the parameter and will return the term reference
	 * after setting it in the {@link kTAG_NS} offset.
	 *
	 * If the term cannot be found, the method will raise an exception.
	 *
	 * @param Term					$theNamespace		Namespace term object.
	 * @return mixed				Namespace reference.
	 * @throws \InvalidArgumentException
	 *
	 * @uses CreateReference()
	 * @uses manageProperty()
	 * @see kTAG_NS
	 */
	public function SetNamespaceByTerm( Term $theNamespace )
	{
		//
		// Get term reference.
		//
		$reference = $this->CreateReference( kTAG_NS, $theNamespace );
		if( $reference !== NULL )
			return
				$this->manageProperty( kTAG_NS, $reference );						// ==>

		throw new \InvalidArgumentException(
			"Missing term key." );												// !@! ==>

	} // SetNamespaceByTerm.


	/*===================================================================================
	 *	SetNamespaceByGID																*
	 *==================================================================================*/

	/**
	 * <h4>Set the namespace given a global identifier.</h4>
	 *
	 * This method can be used to set the current namespace from the provided term global
	 * identifier, the method expects the term global identifier ({@link kTAG_GID}) as the
	 * parameter and will return the term reference after setting it in the {@link kTAG_NS}
	 * offset.
	 *
	 * This method can be used to set the current term's namespace ({@link kTAG_NS}) given
	 * the namespace term's global identifier, the method will use the current term's
	 * collection to locate the right term; if the provided identifier doesn't match any
	 * terms, the method will raise an exception.
	 *
	 * @param string				$theIdentifier		Namespace global identifier.
	 * @return mixed				Namespace reference.
	 * @throws \InvalidArgumentException
	 *
	 * @uses Collection()
	 * @uses manageProperty()
	 */
	public function SetNamespaceByGID( $theIdentifier )
	{
		//
		// Select term.
		//
		$cursor =
			$this->Collection()->FindByExample(
				[ kTAG_GID => $theIdentifier ],
				[ kTOKEN_OPT_LIMIT => 1, kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_KEY ] );

		//
		// Set namespace.
		//
		foreach( $cursor as $term )
			return
				$this->manageProperty( kTAG_NS, $term );							// ==>

		throw new \InvalidArgumentException(
			"Unknown global identifier [$theIdentifier]." );					// !@! ==>

	} // SetNamespaceByGID.


	/*===================================================================================
	 *	GetNamespaceTerm																*
	 *==================================================================================*/

	/**
	 * <h4>Get namespace term object.</h4>
	 *
	 * This method can be used to retrieve the namespace term object, if the namespace is
	 * not set, the method will return <tt>NULL</tt>.
	 *
	 * @return Term					Namespace object.
	 *
	 * @uses doResolveReference()
	 * @see kTAG_NS
	 */
	public function GetNamespaceTerm()
	{
		//
		// Get namespace.
		//
		$namespace = $this->offsetGet( kTAG_NS );
		if( $namespace !== NULL )
		{
			//
			// Handle document.
			//
			if( $namespace instanceof Term )
				return $namespace;													// ==>

			return $this->doResolveReference( kTAG_NS, $namespace );				// ==>
		}

		return NULL;																// ==>

	} // GetNamespaceTerm.



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
	 *
	 * @see kTAG_NS
	 * @see kTAG_LID
	 * @see kTAG_GID
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
	 *
	 * @see kTAG_LID
	 * @see kTAG_GID
	 * @see kTAG_NAME
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
 *						PROTECTED SUBDOCUMENT PERSISTENCE INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doCreateReference																*
	 *==================================================================================*/

	/**
	 * <h4>Reference embedded documents.</h4>
	 *
	 * We overload this method to use the term key as the reference to the namespace.
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
		// Handle namespace.
		//
		if( $theOffset == kTAG_NS )
			return $theDocument[ $theDocument->Collection()->KeyOffset() ];

		return parent::doCreateReference( $theOffset, $theDocument );				// ==>

	} // doCreateReference.


	/*===================================================================================
	 *	doResolveReference																*
	 *==================================================================================*/

	/**
	 * <h4>Resolve embedded documents.</h4>
	 *
	 * The duty of this method is to resolve the provided document reference associated with
	 * the provided offset into a document object.
	 *
	 * The method will return the {@link Document} instance referenced by the provided
	 * reference, or raise an exception if the referenced document cannot be resolved.
	 *
	 * In this class we assume the provided reference is by default a handle, derived
	 * classes should overload this method to handle other reference types.
	 *
	 * @param string				$theOffset			Sub-document offset.
	 * @param mixed					$theReference		Subdocument reference.
	 * @return mixed				The document key or handle.
	 *
	 * @uses Collection()
	 * @see kTAG_NS
	 */
	protected function doResolveReference( $theOffset, $theReference )
	{
		//
		// Handle namespace.
		//
		if( $theOffset == kTAG_NS )
			return $this->Collection()->FindKey( $theReference );

		return parent::doResolveReference( $theOffset, $theReference );				// ==>

	} // doResolveReference.



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
	 * @uses Collection()
	 * @uses makeTermGID()
	 * @uses Collection::FindKey()
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
				$term = $this->Collection()->FindKey( $theNamespace );
				if( $term !== NULL )
					$this->mNamespaceGID = $term->offsetGet( kTAG_GID );
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



} // class Term.


?>
