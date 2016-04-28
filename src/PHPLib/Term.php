<?php

/**
 * Term.php
 *
 * This file contains the definition of the {@link Term} class.
 */

namespace Milko\PHPLib;

/*=======================================================================================
 *																						*
 *										Term.php										*
 *																						*
 *======================================================================================*/

use Milko\PHPLib\Document;

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
 * Descriptor objects <em>should be stored in a default collection</em>.
 *
 *	@package	Code
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
	protected $mNamespaceGID = NULL;



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
	 */
	public function __construct( Collection $theCollection, array $theData = [] )
	{
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
	 * @uses doCreateReference()
	 * @uses setTermNamespace()
	 * @uses setTermIdentifier()
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
 *							PUBLIC DOCUMENT PERSISTENCE INTERFACE						*
 *																						*
 *======================================================================================*/



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
	 */
	public function Delete()
	{
		//
		// Get affected collections.
		//
		$terms = $this->mCollection;
		$descriptors = $this->mCollection->Database()->NewDescriptorsCollection();

		//
		// Get terms usage count.
		//
		$count = $terms->CountByExample(
				[ kTAG_NS => $this->offsetGet( $this->mCollection->KeyOffset() ) ] );
		if( $count )
			throw new \RuntimeException (
				"Cannot delete the term: " .
				"it is the namespace of $count terms." );						// !@! ==>

		//
		// Get descriptors usage count.
		//
		$count = $descriptors->CountByExample(
			[ kTAG_NS => $this->offsetGet( $this->mCollection->KeyOffset() ) ] );
		if( $count )
			throw new \RuntimeException (
				"Cannot delete the term: " .
				"it is the namespace of $count descriptors." );					// !@! ==>

		return parent::Delete();													// ==>

	} // Delete.



/*=======================================================================================
 *																						*
 *								PUBLIC TRAVERSAL INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	PrepareInsert																	*
	 *==================================================================================*/

	/**
	 * <h4>Prepare document to be inserted.</h4>
	 *
	 * We overload this method to ensure the key doesn't contain invalid characters: by
	 * default we set the document key to the global identifier value, if that value
	 * contains invalid characters, we convert it to an <tt>MD5</tt> hash.
	 */
	public function PrepareInsert()
	{
		//
		// Try global identifier.
		//
		$gid = $this->offsetGet( kTAG_GID );
		if( preg_match("/^[0-9a-zA-Z_\-:\.@\(\)+,=;$!*'%]+$/", $gid ) )
			$this->offsetSet( $this->mCollection->KeyOffset(), $gid );

		//
		// Hash global identifier.
		//
		else
			$this->offsetSet( $this->mCollection->KeyOffset(), md5( $gid ) );

	} // PrepareInsert.



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



/*=======================================================================================
 *																						*
 *							PUBLIC REFERENCE MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



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
	 * This method will use the current term's collection to locate the right term; if the
	 * provided identifier doesn't match any terms, the method will raise an exception.
	 *
	 * @param string				$theIdentifier		Namespace global identifier.
	 * @return mixed				Namespace reference.
	 * @throws \InvalidArgumentException
	 *
	 * @uses GetByGID()
	 * @uses manageProperty()
	 * @uses getNamespaceCollection()
	 */
	public function SetNamespaceByGID( $theIdentifier )
	{
		//
		// Get namespace term.
		//
		$term = self::GetByGID( $this->getNamespaceCollection(), $theIdentifier );
		if( $term !== NULL )
			return
				$this->manageProperty(
					kTAG_NS,
					$term->offsetGet( $this->mCollection->KeyOffset() ) );			// ==>

		throw new \InvalidArgumentException(
			"Unknown namespace [$theIdentifier]." );							// !@! ==>

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
 *							STATIC IDENTIFICATION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	MakeGID																			*
	 *==================================================================================*/

	/**
	 * <h4>Return an object global identifier.</h4>
	 *
	 * This method will return a global identifier given a term namespace and local
	 * identifier, the method expects the following parameters:
	 *
	 * <ul>
	 * 	<li><b>$theIdentifier</b>: The object local identifier.
	 * 	<li><b>$theNamespace</b>: The object namespace: if the next parameter is provided,
	 * 		it means that the namespace is expressed as the namespace term key; if the next
	 * 		parameter is omitted, it means that the namespace was provided as the namespace
	 * 		term global identifier. The namespace is optional, so if missing the local
	 * 		identifier will be returned.
	 * 	<li><b>$theCollection</b>: The terms collection, used only when providing the
	 * 		namespace as a term object key, if the key is not found, the method will raise
	 * 		an exception.
	 * </ul>
	 *
	 * @param string				$theIdentifier		Local identifier.
	 * @param string				$theNamespace		Namespace term key.
	 * @param Collection			$theCollection		Terms collection.
	 * @return string				Global identifier.
	 * @throws \RuntimeException
	 *
	 * @uses Collection::FindByKey()
	 */
	static function MakeGID( $theIdentifier, $theNamespace = NULL, $theCollection = NULL )
	{
		//
		// Handle namespace.
		//
		if( $theNamespace !== NULL )
		{
			//
			// Handle namespace key.
			//
			if( $theCollection !== NULL )
			{
				//
				// Get namespace term.
				//
				$term = $theCollection->FindByKey( $theNamespace );
				if( $term === NULL )
					throw new \RuntimeException(
						"Unknown term [$theNamespace]." );						// !@! ==>

				//
				// Set namespace global identifier.
				//
				$theNamespace = $term->offsetGet( kTAG_GID );

			} // Provided namespace key.

			return $theNamespace . kTOKEN_NAMESPACE_SEPARATOR . $theIdentifier;		// ==>

		} // Provided namespace.

		return $theIdentifier;														// ==>

	} // MakeGID.


	/*===================================================================================
	 *	GetByGID																		*
	 *==================================================================================*/

	/**
	 * <h4>Return a term by global identifier.</h4>
	 *
	 * This method will return a term object given a global identifier, or <tt>NULL</tt> if
	 * not found.
	 *
	 * The method expects as first parameter the terms collection, and as second parameter
	 * the global identifier of the term.
	 *
	 * We first match the key with the global identifier, if that doesn't work we try with
	 * its <tt>md5</tt> hash.
	 *
	 * @param Collection			$theCollection		Collection.
	 * @param string				$theIdentifier		Global identifier.
	 * @return Term					Term object or <tt>NULL</tt>.
	 * @throws \RuntimeException
	 *
	 * @uses Collection::FindByKey()
	 */
	static function GetByGID( Collection $theCollection, $theIdentifier )
	{
		//
		// Handle default namespace.
		//
		if( ! strlen( $theIdentifier ) )
			$theIdentifier = md5( (string)$theIdentifier );

		//
		// Try with GID.
		//
		$object = $theCollection->FindByKey( (string)$theIdentifier );
		if( $object === NULL )
			return $theCollection->FindByKey( md5( (string)$theIdentifier ) );		// ==>

		return $object;																// ==>

	} // GetByGID.



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
	 */
	protected function doCreateReference( $theOffset, Document $theDocument )
	{
		//
		// Handle namespace.
		//
		if( $theOffset == kTAG_NS )
			return $theDocument[ $theDocument->mCollection->KeyOffset() ];

		return parent::doCreateReference( $theOffset, $theDocument );				// ==>

	} // doCreateReference.


	/*===================================================================================
	 *	doResolveReference																*
	 *==================================================================================*/

	/**
	 * <h4>Resolve embedded documents.</h4>
	 *
	 * We overload this method by finding the namespaces collection and retrieving the
	 * term by key.
	 *
	 * @param string				$theOffset			Sub-document offset.
	 * @param mixed					$theReference		Subdocument reference.
	 * @return mixed				The document key or handle.
	 *
	 * @uses getNamespaceCollection()
	 */
	protected function doResolveReference( $theOffset, $theReference )
	{
		//
		// Handle namespace.
		//
		if( $theOffset == kTAG_NS )
			return
				$this->getNamespaceCollection()
					->FindByKey( $theReference );									// ==>

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
	 * @uses MakeGID()
	 * @uses Collection::FindByKey()
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
				$this->mNamespaceGID = NULL;

			//
			// Cache namespace global identifier.
			//
			else
			{
				//
				// Get namespace global identifier.
				//
				$term =
					$this->getNamespaceCollection()
						->FindByKey( $theNamespace );
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
				self::MakeGID(
					$this->offsetGet( kTAG_LID ),
					$this->mNamespaceGID ) );

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
	 * @uses MakeGID()
	 */
	protected function setTermIdentifier( $theIdentifier )
	{
		//
		// Check if namespace changed.
		//
		if( $this->offsetGet( kTAG_LID ) !== (string)$theIdentifier )
		{
			//
			// Check identifier.
			//
			if( $theIdentifier === NULL )
				throw new \InvalidArgumentException(
					"Cannot reset term local identifier." );					// !@! ==>

			//
			// Set current term global identifier.
			//
			$this->offsetSet(
				kTAG_GID,
				self::MakeGID(
					$theIdentifier,
					$this->mNamespaceGID ) );

		} // Identifier changed.

	} // setTermIdentifier.



/*=======================================================================================
 *																						*
 *						PROTECTED NAMESPACE COLLECTION INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	getNamespaceCollection															*
	 *==================================================================================*/

	/**
	 * <h4>Get namespace collection.</h4>
	 *
	 * This method can be used to retrieve the namespace collection as opposed to the object
	 * collection, in this class both the object and the namespace collections are the
	 * same, but this class is used as an ancestor to other classes which reside in
	 * different collections, so this method should be overloaded in those classes.
	 *
	 * @return Collection					Namespace collection.
	 */
	protected function getNamespaceCollection()
	{
		return $this->mCollection;													// ==>

	} // getNamespaceCollection.



} // class Term.


?>
