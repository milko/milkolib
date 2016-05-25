<?php

/**
 * Collection.php
 *
 * This file contains the definition of the {@link Collection} class.
 */

namespace Milko\PHPLib;

/**
 * Global tag definitions.
 */
require_once('descriptors.inc.php');

/**
 * Global token definitions.
 */
require_once( 'tokens.inc.php' );

/*=======================================================================================
 *																						*
 *									Collection.php										*
 *																						*
 *======================================================================================*/

use Milko\PHPLib\Container;

/**
 * <h4>Collection ancestor object.</h4>
 *
 * This <em>abstract</em> class is the ancestor of all classes representing collection
 * instances.
 *
 * The class features two attributes:
 *
 * <ul>
 * 	<li><tt>{@link $mDatabase}</tt>: This attribute contains the {@link Database} instance
 * 		to which the collection belongs.
 * 	<li><tt>{@link $mConnection}</tt>: This attribute contains the collection native
 * 		connection object.
 * </ul>
 *
 * The class implements the following public interface:
 *
 * <ul>
 * 	<li>Connections:
 * 	 <ul>
 * 		<li><b>{@link Server()}</b>: Return collection {@link Server}.
 * 		<li><b>{@link Database()}</b>: Return collection {@link Database}.
 * 		<li><b>{@link Connection()}</b>: Return collection native connection.
 * 	 </ul>
 * 	<li>Offsets:
 * 	 <ul>
 * 		<li><b>{@link KeyOffset()}</b>: Return default document key offset.
 * 		<li><b>{@link ClassOffset()}</b>: Return default document class offset.
 * 		<li><b>{@link RevisionOffset()}</b>: Return default document revision offset.
 * 		<li><b>{@link PropertiesOffset()}</b>: Return default document properties offset.
 * 	 </ul>
 * 	<li>Collection management:
 * 	 <ul>
 * 		<li><b>{@link Drop()}</b>: Drop the collection.
 * 		<li><b>{@link Truncate()}</b>: Empty the collection.
 * 	 </ul>
 * 	<li>Document management:
 * 	 <ul>
 * 		<li><b>{@link NewDocument()}</b>: Create a {@link Document} instance.
 * 		<li><b>{@link NewDocumentArray()}</b>: Convert a document to array.
 * 		<li><b>{@link NewDocumentHandle()}</b>: Extract the handle from a document.
 * 		<li><b>{@link NewDocumentKey()}</b>: Extract the key from a document.
 * 	 </ul>
 * 	<li>Insertion:
 * 	 <ul>
 * 		<li><b>{@link Insert()}</b>: Insert a single document.
 * 		<li><b>{@link InsertMany()}</b>: Insert a set of documents.
 * 		<li><b>{@link InsertBulk()}</b>: Insert a bulk set of documents.
 * 	 </ul>
 * 	<li>Modification:
 * 	 <ul>
 * 		<li><b>{@link Replace()}</b>: Replace a document.
 * 		<li><b>{@link Update()}</b>: Update collection documents.
 * 		<li><b>{@link UpdateByExample()}</b>: Update collection documents by example.
 * 	 </ul>
 * 	<li>Selection:
 * 	 <ul>
 * 		<li><b>{@link Find()}</b>: Find by query.
 * 		<li><b>{@link FindByKey()}</b>: Find documents by key.
 * 		<li><b>{@link FindByHandle()}</b>: Find documents by handle.
 * 		<li><b>{@link FindByExample()}</b>: Find documents by example.
 * 	 </ul>
 * 	<li>Counting:
 * 	 <ul>
 * 		<li><b>{@link Count()}</b>: Return the total record count.
 * 		<li><b>{@link CountByQuery()}</b>: Count by query.
 * 		<li><b>{@link CountByExample()}</b>: Count documents by example.
 * 	 </ul>
 * 	<li>Deletion:
 * 	 <ul>
 * 		<li><b>{@link Delete()}</b>: Delete by query.
 * 		<li><b>{@link DeleteByKey()}</b>: Delete documents by key.
 * 		<li><b>{@link DeleteByHandle()}</b>: Delete documents by handle.
 * 		<li><b>{@link DeleteByExample()}</b>: Delete documents by example.
 * 	 </ul>
 * 	<li>Document sets:
 * 	 <ul>
 * 		<li><b>{@link StoreDocumentSet()}</b>: Insert or replace a set of documents.
 * 		<li><b>{@link DeleteDocumentSet()}</b>: Delete a set of documents.
 * 		<li><b>{@link ConvertDocumentSet()}</b>: Convert a document set.
 * 	 </ul>
 * </ul>
 *
 * The class declares the following protected interface which must be implemented in derived
 * concrete classes:
 *
 * <ul>
 * 	<li>Collection management:
 * 	 <ul>
 * 		<li><b>{@link collectionCreate()}</b>: Create a native collection instance.
 * 		<li><b>{@link collectionName()}</b>: Return the current collection name.
 * 	 </ul>
 * 	<li>Document management:
 * 	 <ul>
 * 		<li><b>{@link documentCreate()}</b>: Create a default {@link Document} instance.
 * 		<li><b>{@link documentNativeCreate()}</b>: Create a native document instance.
 * 		<li><b>{@link documentHandleCreate()}</b>: Create a document handle.
 * 	 </ul>
 * </ul>
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		17/02/2016
 */
abstract class Collection
{
	/**
	 * <h4>Database object.</h4>
	 *
	 * This data member holds the <i>database object</i>, it is the object that
	 * instantiated the current collection.
	 *
	 * @var Database
	 */
	protected $mDatabase = NULL;

	/**
	 * <h4>Collection native object.</h4>
	 *
	 * This data member holds the <i>collection native object</i>, it is the object provided
	 * by the database driver.
	 *
	 * @var mixed
	 */
	protected $mConnection = NULL;




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
	 * A collection is instantiated by providing the {@link Database} instance to which the
	 * collection belongs, the collection name and a set of native database driver options.
	 *
	 * @param Database				$theDatabase		Database.
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Native driver options.
	 *
	 * @uses collectionCreate()
	 *
	 * @example
	 * <code>
	 * $server = new Server( 'driver://user:pass@host:8989' );
	 * $database = new Database( $server, "database" );
	 * $collection = new Collection( $database, "collection" );
	 * </code>
	 *
	 * @example
	 * <code>
	 * // In general you will use this form:
	 * $server = new Server( 'driver://user:pass@host:8989/database/collection' );
	 * $database = $server->GetDatabase( "database" );
	 * $collection = $database->GetCollection( "collection" );
	 * </code>
	 */
	public function __construct( Database $theDatabase, $theCollection, $theOptions = NULL )
	{
		//
		// Store server instance.
		//
		$this->mDatabase = $theDatabase;

		//
		// Store the driver instance.
		//
		$this->mConnection = $this->collectionCreate( $theCollection, $theOptions );

	} // Constructor.


	/*===================================================================================
	 *	__toString																		*
	 *==================================================================================*/

	/**
	 * <h4>Return collection name</h4>
	 *
	 * Objects of this class should return the collection name when cast to string.
	 *
	 * The method will use the protected {@link collectionName()} method.
	 *
	 * @return string
	 *
	 * @uses collectionName()
	 *
	 * @example
	 * $name = (string) $collection;
	 */
	public function __toString()						{	return $this->collectionName();	}



/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Server																			*
	 *==================================================================================*/

	/**
	 * <h4>Return the database server.</h4>
	 *
	 * This method can be used to retrieve the database server object.
	 *
	 * @return Server				Collection server object.
	 */
	public function Server()						{	return $this->mDatabase->Server();	}


	/*===================================================================================
	 *	Database																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the database object.</h4>
	 *
	 * This method can be used to retrieve the database object.
	 *
	 * @return Database				Collection database object.
	 */
	public function Database()								{	return $this->mDatabase;	}


	/*===================================================================================
	 *	Connection																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the collection native driver object.</h4>
	 *
	 * This method can be used to retrieve the collection native driver object.
	 *
	 * @return mixed				Collection native driver object.
	 */
	public function Connection()							{	return $this->mConnection;	}



/*=======================================================================================
 *																						*
 *							PUBLIC OFFSET DECLARATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	KeyOffset																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the document key offset.</h4>
	 *
	 * This represents the default offset of the document key or unique identifier within
	 * its collection, this property is managed by clients.
	 *
	 * @return string				Document key offset.
	 */
	abstract public function KeyOffset();


	/*===================================================================================
	 *	ClassOffset																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the document class offset.</h4>
	 *
	 * This represents the default offset of the document class name, this property is
	 * managed by clients and serves the purpose on instantiating the right object when
	 * retrieving documents from the database.
	 *
	 * @return string				Document class offset.
	 */
	abstract public function ClassOffset();


	/*===================================================================================
	 *	RevisionOffset																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the document revision offset.</h4>
	 *
	 * This represents the default offset of the document revision, this property represents
	 * the database internal revision of the stored document and it is generally managed by
	 * the database.
	 *
	 * @return string				Document revision offset.
	 */
	abstract public function RevisionOffset();


	/*===================================================================================
	 *	PropertiesOffset																*
	 *==================================================================================*/

	/**
	 * <h4>Return the document properties offset.</h4>
	 *
	 * This represents the default offset of the document properties list, this property
	 * collects all leaf offsets contained in the document, it can be used to filter
	 * documents by their properties.
	 *
	 * @return string				Document properties offset.
	 */
	abstract public function PropertiesOffset();



/*=======================================================================================
 *																						*
 *							PUBLIC COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Drop																			*
	 *==================================================================================*/

	/**
	 * <h4>Drop the current collection.</h4>
	 *
	 * This method can be used to drop the current collection, it is the responsibility of
	 * the caller to ensure the server is connected.
	 *
	 * The method should return <tt>TRUE</tt> if the collection was dropped, or
	 * <tt>NULL</tt> if the collection was not found.
	 *
	 * Derived concrete classes must implement this method.
	 *
	 * @param array					$theOptions			Native driver options.
	 * @return mixed				<tt>TRUE</tt> dropped, <tt>NULL</tt> not found.
	 */
	abstract public function Drop( $theOptions = NULL );


	/*===================================================================================
	 *	Truncate																		*
	 *==================================================================================*/

	/**
	 * <h4>Clear the collection contents.</h4>
	 *
	 * This method can be used to erase the contents of the collection, it is the
	 * responsibility of the caller to ensure the server is connected.
	 *
	 * Derived concrete classes must implement this method.
	 *
	 * @param array					$theOptions			Native driver options.
	 * @return mixed				<tt>TRUE</tt> dropped, <tt>NULL</tt> not found.
	 */
	abstract public function Truncate( $theOptions = NULL );



/*=======================================================================================
 *																						*
 *							PUBLIC DOCUMENT MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewDocument																		*
	 *==================================================================================*/

	/**
	 * <h4>Return a {@link Document} instance.</h4>
	 *
	 * This method should instantiate a {@link Document} instance from the provided data
	 * that can be either a database native document, an array or an object that can be
	 * cast to an array, the resulting document's class is determined in the following
	 * order:
	 *
	 * <ul>
	 * 	<li><em>$theClass is provided</em>: We instantiate an object of the provided class
	 * 		name. Note that the provided class name will replace the current class name
	 * 		({@link ClassOffset()}) in the provided data, this means that unless you replace
	 * 		the object, these changes will not be recorded. For this reason, the class
	 * 		<em>must</em> be derived from the {@link Document} class.
	 * 	<li><em>Data has {@link ClassOffset()}</em>: We instantiate the referenced class.
	 * 	<li><em>No class specified either in parameter or data</em>: we instantiate a
	 * 		{@link Document} derived object according to the protected
	 * 		{@link documentCreate()} method.
	 * </ul>
	 *
	 * In derived concrete classes you should overload this method by intercepting native
	 * documents, extract any internal properties and pass them to the parent method.
	 *
	 * @param mixed					$theData			Document data.
	 * @param string				$theClass			Expected class name.
	 * @return Document				Standard document object.
	 *
	 * @used NewDocumentArray()
	 * @used documentCreate()
	 */
	public function NewDocument( $theData, $theClass = NULL )
	{
		//
		// Convert data to array.
		//
		$document = $this->NewDocumentArray( $theData );

		//
		// Use provided class name.
		//
		if( $theClass !== NULL )
		{
			$theClass = (string)$theClass;
			return new $theClass( $this, $document );								// ==>
		}

		//
		// Use class in data.
		//
		if( array_key_exists( $this->ClassOffset(), $document ) )
		{
			$class = $document[ $this->ClassOffset() ];
			return new $class( $this, $document );									// ==>
		}

		return $this->documentCreate( $document );									// ==>

		//
		// In derived classes:
		//
//		//
//		// Handle native document.
//		//
//		$document = <convert to array>( $theData );
//		<extract internal properties>( $document );
//
//		return parent::NewDocument( $document, $theClass );							// ==>

	} // NewDocument.


	/*===================================================================================
	 *	NewDocumentContainer															*
	 *==================================================================================*/

	/**
	 * <h4>Return a {@link Container} instance.</h4>
	 *
	 * This method should instantiate a {@link Container} instance from the provided data
	 * that can be either a database native document, an array or an object that can be
	 * cast to an array.
	 *
	 * @param mixed					$theData			Document data.
	 * @param string				$theClass			Expected class name.
	 * @return Document				Standard document object.
	 *
	 * @used NewDocumentArray()
	 * @used documentCreate()
	 */
	public function NewDocumentContainer( $theData )
	{
		return new Container( $this->NewDocumentArray( $theData ) );				// ==>

	} // NewDocumentContainer.


	/*===================================================================================
	 *	NewDocumentNative																*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database document.</h4>
	 *
	 * This method should return a native database document from the provided data which
	 * can be an array, an instance of {@link Container}, or an object that can be converted
	 * to an array.
	 *
	 * In this class we call the protected virtual {@link documentNativeCreate()} method by
	 * providing the document converted to an array: that method will take care of
	 * instantiating the correct object.
	 *
	 * In derived concrete classes you should overload this method by intercepting native
	 * documents and returning them, or call the parent method.
	 *
	 * @param mixed					$theData			Document data.
	 * @return mixed				Database native object.
	 *
	 * @uses NewDocumentArray()
	 * @uses documentNativeCreate()
	 */
	public function NewDocumentNative( $theData )
	{
		return
			$this->documentNativeCreate(
				$this->NewDocumentArray( $theData ) );								// ==>

		//
		// In derived classes:
		//
//		//
//		// Handle native document.
//		//
//		if( $theData instanceof <native document> )
//			return $theData;														// ==>
//
//		return parent::NewDocumentNative( $theData );								// ==>

	} // NewDocumentNative.


	/*===================================================================================
	 *	NewDocumentArray																*
	 *==================================================================================*/

	/**
	 * <h4>Return an array from a document.</h4>
	 *
	 * This method should convert the provided document to an array, the document should be
	 * an array, an instance of {@link Container}, or an object that can be converted to an
	 * array.
	 *
	 * In derived concrete classes you should overload this method by intercepting native
	 * documents and converting them, or call the parent method.
	 *
	 * @param mixed					$theData			Document data.
	 * @return array				Document as array.
	 */
	public function NewDocumentArray( $theData )
	{
		//
		// Handle array.
		//
		if( is_array( $theData ) )
			return $theData;														// ==>

		//
		// Handle container.
		//
		if( $theData instanceof Container )
			return $theData->toArray();												// ==>

		//
		// Handle ArrayObject.
		//
		if( $theData instanceof \ArrayObject )
			return $theData->getArrayCopy();										// ==>

		return (array)$theData;														// ==>

		//
		// In derived classes:
		//
//		//
//		// Handle native document.
//		//
//		if( $theData instanceof <native document> )
//			return <convert to array>( $theData );									// ==>
//
//		return parent::NewDocumentArray( $theData );								// ==>

	} // NewDocumentArray.


	/*===================================================================================
	 *	NewDocumentHandle																*
	 *==================================================================================*/

	/**
	 * <h4>Return document handle.</h4>
	 *
	 * This method should return a document handle derived from the provided data.
	 *
	 * A document handle is a value that contains a reference to the collection and key of
	 * a specific document, and that can be used to point to a specific document instance.
	 *
	 * The method expects a native database document, an array, an instance of
	 * {@link Container}, or an object that can be converted to an array.
	 *
	 * If the provided document does not have its {@link KeyOffset()} property, this method
	 * should raise an exception.
	 *
	 * Derived classes should implement this method to handle database native document
	 * types or call the parent method for other types.
	 *
	 * @param mixed					$theData			Document data.
	 * @return mixed				Document handle.
	 *
	 * @uses KeyOffset()
	 * @uses NewDocumentArray()
	 * @uses documentHandleCreate()
	 */
	public function NewDocumentHandle( $theData )
	{
		//
		// Handle documents.
		//
		if( $theData instanceof Document )
			return
				$theData->Collection()->NewDocumentHandle(
					$theData->Collection()->NewDocumentArray(
						$theData ) );												// ==>

		//
		// Convert to array.
		//
		$document = $this->NewDocumentArray( $theData );

		//
		// Compute handle.
		//
		if( array_key_exists( $this->KeyOffset(), $document ) )
			return $this->documentHandleCreate( $document[ $this->KeyOffset() ] );	// ==>

		throw new \InvalidArgumentException (
			"Data is missing the document key." );								// !@! ==>

		//
		// In derived classes:
		//
//		//
//		// Handle native document.
//		//
//		if( $theData instanceof <native document> )
//			return $theData-><get handle>();										// ==>
//
//		return parent::NewDocumentHandle( $theData );								// ==>

	} // NewDocumentHandle.


	/*===================================================================================
	 *	NewDocumentKey																	*
	 *==================================================================================*/

	/**
	 * <h4>Return document key.</h4>
	 *
	 * This method should return a document key from from the provided data.
	 *
	 * The method expects a native database document, an array, an instance of
	 * {@link Container}, or an object that can be converted to an array.
	 *
	 * If the provided data does not have its {@link KeyOffset()} property, this method
	 * should raise an exception.
	 *
	 * Derived classes should implement this method to handle database native document
	 * types or call the parent method for other types.
	 *
	 * @param mixed					$theData			Document data.
	 * @return mixed				Document key.
	 *
	 * @uses KeyOffset()
	 * @uses NewDocumentArray()
	 */
	public function NewDocumentKey( $theData )
	{
		//
		// Convert to array.
		//
		$document = $this->NewDocumentArray( $theData );

		//
		// Check key.
		//
		if( array_key_exists( $this->KeyOffset(), $document ) )
			return $theData[ $this->KeyOffset() ];									// ==>

		throw new \InvalidArgumentException (
			"Data is missing the document key." );								// !@! ==>

	} // NewDocumentKey.


	/*===================================================================================
	 *	BuildDocumentHandle																*
	 *==================================================================================*/

	/**
	 * <h4>Build a document handle.</h4>
	 *
	 * This method should return a document handle from the provided collection and document
	 * key, the method expects two arguments: the document key and a {@link Collection}
	 * instance or a string containing the collection name, the latter may be omitted if the
	 * collection corresponds to the current collection.
	 *
	 * Derived classes must implement this method.
	 *
	 * @param mixed					$theKey				Document key.
	 * @param mixed					$theCollection		Collection instance or name.
	 * @return mixed				Document handle.
	 */
	abstract public function BuildDocumentHandle( $theKey, $theCollection = NULL );



/*=======================================================================================
 *																						*
 *						PUBLIC TIMESTAMP MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewTimestamp																	*
	 *==================================================================================*/

	/**
	 * <h4>Return a native time stamp.</h4>
	 *
	 * This method can be used to return a time stamp in the native database format, it
	 * expects a single optional parameter that represents the timestamp expressed in
	 * milliseconds as an integer.
	 * 
	 * The method must be implemented by concrete derived classes.
	 *
	 * @param int					$theTimeStamp		Milliseconds.
	 * @return mixed				Time stamp in native format.
	 */
	abstract public function NewTimestamp( $theTimeStamp = NULL );


	/*===================================================================================
	 *	GetTimestamp																	*
	 *==================================================================================*/

	/**
	 * <h4>Return an ISO date from a timestamp.</h4>
	 *
	 * This method can be used to return an ISO 8601 date from a time stamp in the native
	 * database format, the method must be implemented by concrete derived classes.
	 *
	 * @param mixed					$theTimeStamp		Native time stamp.
	 * @return string				ISO 8601 date.
	 */
	abstract public function GetTimestamp( $theTimeStamp );



/*=======================================================================================
 *																						*
 *								PUBLIC INSERTION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Insert																			*
	 *==================================================================================*/

	/**
	 * <h4>Insert document.</h4>
	 *
	 * This method can be used to insert a single document into the current collection, the
	 * method expects a single parameter that represents the document expressed as a value
	 * compatible with the {@link NewDocumentNative()} method.
	 *
	 * When providing {@link Document} derived instances, the method will perform the
	 * following steps prior to inserting the data:
	 *
	 * <ul>
	 * 	<li><tt>{@link Document::Validate()}</tt>: The method will validate the document.
	 * 	<li><tt>{@link Document::TraverseDocument()}</tt>: The method will traverse the
	 * 		document structure validating and collecting its properties with
	 * 		<tt>{@link Document::SetPropertiesList()}</tt>.
	 * 	<li><tt>{@link Document::PrepareInsert()}</tt>: The method will prepare the document
	 * 		for insertion.
	 * </ul>
	 *
	 * Once these steps have bben performed the method will:
	 *
	 * <ul>
	 * 	<li><tt>{@link NewDocumentNative()}</tt>: Convert data into a native document.
	 * 	<li><tt>{@link documentInsert()}</tt>: Insert the document into the collection.
	 * 	<li><tt>{@link normaliseInsertedDocument()}</tt>: Normalise the inserted document:
	 * 	 <ul>
	 * 		<li><tt>{@link \ArrayObject}</tt>: The method will set the newly inserted key.
	 * 		<li><tt>{@link Document}</tt>: The method will set the document's
	 * 			{@link Document::IsPersistent()} state and reset the
	 * 			{@link Document::IsModified()} state.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will return the newly inserted document key.
	 *
	 * @param mixed					$theDocument		The document data.
	 * @return mixed				The document's key.
	 *
	 * @uses NewDocumentNative()
	 * @uses documentInsert()
	 * @uses normaliseInsertedDocument()
	 * @uses Document::Validate()
	 * @uses Document::TraverseDocument()
	 * @uses Document::SetPropertiesList()
	 * @uses Document::PrepareInsert()
	 */
	public function Insert( $theDocument )
	{
		//
		// Validate and prepare document.
		//
		if( $theDocument instanceof Document )
		{
			//
			// Validate document.
			//
			$theDocument->Validate();

			//
			// Store sub-documents and collect offsets.
			//
			$theDocument->SetPropertiesList(
				$theDocument->Traverse(), $this );

			//
			// Prepare document.
			//
			$theDocument->PrepareInsert();

		} // Document instance.

		//
		// Convert document.
		//
		$document = $this->NewDocumentNative( $theDocument );

		//
		// Insert document.
		//
		$key = $this->documentInsert( $document );

		//
		// Normalise document.
		//
		$this->normaliseInsertedDocument( $theDocument, $document, $key );

		return $key;																// ==>

	} // Insert.


	/*===================================================================================
	 *	InsertMany																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert a set of documents.</h4>
	 *
	 * This method can be used to insert a set of documents into the current collection, the
	 * method expects a single parameter that represents the set of documents as an array,
	 * each array element must be a document compatible with the {@link NewDocumentNative()}
	 * method.
	 *
	 * The method will return the list of newly inserted document keys.
	 *
	 * @param array					$theDocuments		The documents set as an array.
	 * @return array				The document unique identifiers.
	 *
	 * @uses Insert()
	 */
	public function InsertMany( array $theDocuments )
	{
		//
		// Init local storage.
		//
		$ids = [];

		//
		// Iterate set.
		//
		foreach( $theDocuments as $document )
			$ids[] = $this->Insert( $document );

		return $ids;																// ==>

	} // InsertMany.


	/*===================================================================================
	 *	InsertBulk																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert a bulk set of documents.</h4>
	 *
	 * This method can be used to insert a set of documents into the current collection, the
	 * method expects a single parameter that represents the set of documents expressed in
	 * the database native format.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theDocuments		The native documents set.
	 * @return array				The document keys.
	 */
	abstract public function InsertBulk( $theDocuments );



/*=======================================================================================
 *																						*
 *								PUBLIC UPDATE INTERFACE									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Replace																			*
	 *==================================================================================*/

	/**
	 * <h4>Replace document.</h4>
	 *
	 * This method can be used to replace the provided document in the collection, the
	 * method expects a single parameter that represents the document expressed as a value
	 * compatible with the {@link NewDocumentNative()} method.
	 *
	 * The method expects the replacement document to have its key, if that is missing, the
	 * method should raise an exception.
	 *
	 * When providing {@link Document} derived instances, the method will perform the
	 * following steps prior to replacing:
	 *
	 * <ul>
	 * 	<li><tt>{@link Document::Validate()}</tt>: The method will validate the document.
	 * 	<li><tt>{@link Document::TraverseDocument()}</tt>: The method will traverse the
	 * 		document structure validating and collecting its properties with
	 * 		<tt>{@link Document::SetPropertiesList()}</tt>.
	 * 	<li><tt>{@link Document::PrepareInsert()}</tt>: The method will prepare the document
	 * 		for insertion.
	 * </ul>
	 *
	 * Once these steps have bben performed the method will:
	 *
	 * <ul>
	 * 	<li><tt>{@link NewDocumentNative()}</tt>: Convert data into a native document.
	 * 	<li><tt>{@link documentReplace()}</tt>: Replace the document in the collection.
	 * 	<li><tt>{@link normaliseReplacedDocument()}</tt>: Normalise the replaced document:
	 * 	 <ul>
	 * 		<li><tt>{@link Document}</tt>: The method will reset the document's
	 * 			{@link Document::IsPersistent()} state and set the
	 * 			{@link Document::IsModified()} state.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will return the number of replaced documents.
	 *
	 * @param mixed					$theDocument		The replacement document.
	 * @return int					The number of replaced documents.
	 *
	 * @uses NewDocumentKey()
	 * @uses NewDocumentNative()
	 * @uses documentReplace()
	 * @uses normaliseReplacedDocument()
	 * @uses Document::Validate()
	 * @uses Document::TraverseDocument()
	 * @uses Document::SetPropertiesList()
	 * @uses Document::PrepareReplace()
	 */
	public function Replace( $theDocument )
	{
		//
		// Validate and prepare document.
		//
		if( $theDocument instanceof Document )
		{
			//
			// Validate document.
			//
			$theDocument->Validate();

			//
			// Store sub-documents.
			//
			$theDocument->SetPropertiesList(
				$theDocument->Traverse(), $this );

			//
			// Prepare document.
			//
			$theDocument->PrepareReplace();

		} // Document instance.

		//
		// Get document key.
		// This will throw if key is missing.
		//
		$key = $this->NewDocumentKey( $theDocument );

		//
		// Convert document.
		//
		$document = $this->NewDocumentNative( $theDocument );

		//
		// Replace document.
		//
		$count = $this->documentReplace( $key, $document );

		//
		// Normalise document.
		//
		if( $count )
			$this->normaliseReplacedDocument( $theDocument, $document );

		return $count;																// ==>

	} // Replace.


	/*===================================================================================
	 *	Update																			*
	 *==================================================================================*/

	/**
	 * <h4>Update documents.</h4>
	 *
	 * This method should update the documents selected by the provided filter using the
	 * provided modification criteria. The options parameter may contain the following
	 * values:
	 *
	 * <ul>
	 * 	<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether to update
	 * 		the first selected document, or all the selected documents:
	 * 	 <ul>
	 * 		<li><tt>TRUE</tt>: Update all records selected by the filter.
	 * 		<li><tt>FALSE</tt>: Update the first record selected by the filter.
	 * 	 </ul>
	 * </ul>
	 *
	 * The filter parameter may be <tt>NULL</tt>, in which case it should select all
	 * documents, if not, it should be provided as a database native query.
	 *
	 * The criteria parameter must be provided as an array in which properties with a
	 * <tt>NULL</tt> values are expected to be deleted.
	 *
	 * The method should return the number of modified documents.
	 *
	 * Concrete derived classes must implement this method.
	 *
	 * @param array					$theCriteria		The modification criteria.
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Update options.
	 * @return int					The number of modified records.
	 */
	abstract public function Update(
		array $theCriteria,
		$theFilter = NULL,
		array $theOptions = [ kTOKEN_OPT_MANY => TRUE ]
	);


	/*===================================================================================
	 *	UpdateByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Update documents by example.</h4>
	 *
	 * This method should update the documents matching the provided example document using
	 * the provided modification criteria. The options parameter may contain the following
	 * values:
	 *
	 * <ul>
	 * 	<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether to update
	 * 		the first selected document, or all the selected documents:
	 * 	 <ul>
	 * 		<li><tt>TRUE</tt>: Update all records selected by the filter.
	 * 		<li><tt>FALSE</tt>: Update the first record selected by the filter.
	 * 	 </ul>
	 * </ul>
	 *
	 * The example document parameter must be provided as an array, the method should select
	 * all documents in the collection whose properties match all the properties of the
	 * provided example document, this means that the method will generate a query that puts
	 * in <tt>AND</tt> all the provided document offsets.
	 *
	 * The criteria parameter must be provided as an array in which properties with a
	 * <tt>NULL</tt> values are expected to be deleted.
	 *
	 * The method should return the number of modified documents.
	 *
	 * Concrete derived classes must implement this method.
	 *
	 * @param array					$theCriteria		The modification criteria.
	 * @param array					$theDocument		The example document.
	 * @param array					$theOptions			Update options.
	 * @return int					The number of modified records.
	 */
	abstract public function UpdateByExample(
		array $theCriteria,
		array $theDocument,
		array $theOptions = [ kTOKEN_OPT_MANY => TRUE ]
	);



/*=======================================================================================
 *																						*
 *								PUBLIC SELECTION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Find																			*
	 *==================================================================================*/

	/**
	 * <h4>Find documents by query.</h4>
	 *
	 * This method can be used to select documents based on the provided query and return
	 * them in the requested format. The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFilter</b>: The query in the driver native format.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_SKIP}</b>: This option determines how many records to
	 * 			skip in the results selection, it is equivalent to the SQL <tt>START</tt>
	 * 			directive, it is zero based and expressed as an integer.
	 * 		<li><b>{@link kTOKEN_OPT_LIMIT}</b>: This option determines how many records to
	 * 			return, it is equivalent to the SQL <tt>LIMIT</tt> directive and expressed
	 * 			as an integer.
	 * 		<li><b>{@link kTOKEN_OPT_FORMAT}</b>: This option determines the result format:
	 * 		 <ul>
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_NATIVE}</tt>: Return the unchanged driver
	 * 				database result.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_ARRAY}</tt>: Return an iterable set of
	 * 				arrays.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_CONTAINER}</tt>: Return an iterable set of
	 * 				{@link Container} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_DOCUMENT}</tt>: Return an iterable set of
	 * 				{@link Document} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return an array of document
	 * 				handles.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return an array of document
	 * 				keys.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * The result will always be an iterable object, by default the method will use the
	 * {@link kTOKEN_OPT_FORMAT_DOCUMENT} option and return the full selection.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Query options.
	 * @return mixed				The found records.
	 */
	abstract public function Find(
		$theFilter = NULL,
		array $theOptions = [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT ]
	);


	/*===================================================================================
	 *	FindByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Find documents by key.</h4>
	 *
	 * This method will return the documents that match the provided key or keys in the
	 * current collection, the method features two parameters:
	 *
	 * <ul>
	 *	<li><b>$theKey</b>: The document key(s) to match.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether the first
	 * 			parameter is a set of keys or a single key:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Provided a set of keys, will return an array of results.
	 * 			<li><tt>FALSE</tt>: Provided a single key, will return the selected document
	 * 				or <tt>NULL</tt>.
	 * 		 </ul>
	 * 		<li><b>{@link kTOKEN_OPT_FORMAT}</b>: This option determines the result format:
	 * 		 <ul>
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_NATIVE}</tt>: Return the unchanged driver
	 * 				database result.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_ARRAY}</tt>: Return an array or a set of
	 * 				arrays.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_CONTAINER}</tt>: Return a single or set of
	 * 				{@link Container} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_DOCUMENT}</tt>: Return a single or set of
	 * 				{@link Document} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return a single or set of
	 * 				document handles.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return a single or set of
	 * 				document handles.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * If the provided {@link kTOKEN_OPT_MANY} option is <tt>FALSE</tt>, the method will
	 * return a scalar result, if not, it will return an array of results; if the
	 * {@link kTOKEN_OPT_FORMAT} is set to {@link kTOKEN_OPT_FORMAT_NATIVE}, the result will
	 * depend on the specific native driver.
	 *
	 * By default the method assumes you provided a single key and the requested format is
	 * {@link kTOKEN_OPT_FORMAT_DOCUMENT}.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theKey				Single or set of document keys.
	 * @param array					$theOptions			Query options.
	 * @return mixed				The found records.
	 */
	abstract public function FindByKey(
		$theKey,
		array $theOptions = [ kTOKEN_OPT_MANY => FALSE,
							  kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT ]
	);


	/*===================================================================================
	 *	FindByHandle																	*
	 *==================================================================================*/

	/**
	 * <h4>Find documents by handle.</h4>
	 *
	 * This method will return the documents that match the provided handle or handles in
	 * the collections indicated in the handles, the method features two parameters:
	 *
	 * <ul>
	 *	<li><b>$theHandle</b>: The document handle(s) to match.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether the first
	 * 			parameter is a set of handles or a single handle:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Provided a set of handles, will return an array of
	 * 				results.
	 * 			<li><tt>FALSE</tt>: Provided a single handle, will return the selected
	 * 				document or <tt>NULL</tt>.
	 * 		 </ul>
	 * 		<li><b>{@link kTOKEN_OPT_FORMAT}</b>: This option determines the result format:
	 * 		 <ul>
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_NATIVE}</tt>: Return the unchanged driver
	 * 				database result.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_ARRAY}</tt>: Return an array or a set of
	 * 				arrays.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_CONTAINER}</tt>: Return a single or set of
	 * 				{@link Container} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_DOCUMENT}</tt>: Return a single or set of
	 * 				{@link Document} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return a single or set of
	 * 				document handles.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return a single or set of
	 * 				document handles.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * If the provided {@link kTOKEN_OPT_MANY} option is <tt>FALSE</tt>, the method will
	 * return a scalar result, if not, it will return an array of results; if the
	 * {@link kTOKEN_OPT_FORMAT} is set to {@link kTOKEN_OPT_FORMAT_NATIVE}, the result will
	 * depend on the specific native driver.
	 *
	 * By default the method assumes you provided a single handle and the requested format
	 * is {@link kTOKEN_OPT_FORMAT_DOCUMENT}.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theHandle			Single or set of document handles.
	 * @param array					$theOptions			Query options.
	 * @return mixed				The found records.
	 */
	abstract public function FindByHandle(
		$theHandle,
		array $theOptions = [ kTOKEN_OPT_MANY => FALSE,
							  kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT ]
	);


	/*===================================================================================
	 *	FindByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Find documents by example.</h4>
	 *
	 * This method can be used to select all documents matching the provided example
	 * document. The method will select all documents in the collection whose properties
	 * match all the properties of the provided example document, this means that the method
	 * will generate a query that puts in <tt>AND</tt> all the provided document offsets.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The example document as an array.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_SKIP}</b>: This option determines how many records to
	 * 			skip in the results selection, it is equivalent to the SQL <tt>START</tt>
	 * 			directive, it is zero based and expressed as an integer.
	 * 		<li><b>{@link kTOKEN_OPT_LIMIT}</b>: This option determines how many records to
	 * 			return, it is equivalent to the SQL <tt>LIMIT</tt> directive and expressed
	 * 			as an integer.
	 * 		<li><b>{@link kTOKEN_OPT_FORMAT}</b>: This option determines the result format:
	 * 		 <ul>
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_NATIVE}</tt>: Return the unchanged driver
	 * 				database result.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_ARRAY}</tt>: Return an iterable set of
	 * 				arrays.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_CONTAINER}</tt>: Return an iterable set of
	 * 				{@link Container} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_DOCUMENT}</tt>: Return an iterable set of
	 * 				{@link Document} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return an array of document
	 * 				handles.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return an array of document
	 * 				keys.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * The result will always be an iterable object, by default the method will use the
	 * {@link kTOKEN_OPT_FORMAT_DOCUMENT} option and return the full selection.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theDocument		Example document as an array.
	 * @param array					$theOptions			Query options.
	 * @return mixed				The found records.
	 */
	abstract public function FindByExample(
		array $theDocument,
		array $theOptions = [ kTOKEN_OPT_FORMAT => kTOKEN_OPT_FORMAT_DOCUMENT ]
	);



/*=======================================================================================
 *																						*
 *							PUBLIC AGGREGATION FRAMEWORK INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	MapReduce																		*
	 *==================================================================================*/

	/**
	 * <h4>Execute an aggregation query.</h4>
	 *
	 * This method can be used to perform a map and reduce query, the method expects the
	 * following parameters:
	 *
	 * <ul>
	 *	<li><b>$thePipeline</b>: The aggregation pipeline in native format.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_SKIP}</b>: This option determines how many records to
	 * 			skip in the results selection, it is equivalent to the SQL <tt>START</tt>
	 * 			directive, it is zero based and expressed as an integer. The corresponding
	 *			value will be set in the native <tt>skip</tt> option.
	 * 		<li><b>{@link kTOKEN_OPT_LIMIT}</b>: This option determines how many records to
	 * 			return, it is equivalent to the SQL <tt>LIMIT</tt> directive and expressed
	 * 			as an integer. The corresponding value will be set in the native
	 *			<tt>limit</tt> option.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will return an array of arrays.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$thePipeline		The aggregation pipeline.
	 * @param array					$theOptions			Query options.
	 * @return array				The result set.
	 */
	abstract public function MapReduce( $thePipeline, array $theOptions = [] );



/*=======================================================================================
 *																						*
 *								PUBLIC DISTINCT INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Distinct																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the distinct values of a property.</h4>
	 *
	 * This method can be used to return all the distinct values of a property contained in
	 * the collection, the method expects parameter that represents the property offset and
	 * a boolean flag that determines whether to return the element count.
	 *
	 * If the element count flag is <tt>TRUE</tt>, the method will return an array indexed
	 * by the distinct property values and with the count as value, if <tt>FALSE</tt>, the
	 * method will return an array with the distint values.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theOffset			The property offset.
	 * @param boolean				$doCount			Return element counts.
	 * @return array				The result set.
	 */
	abstract public function Distinct( $theOffset, $doCount = FALSE );


	/*===================================================================================
	 *	DistinctByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the distinct values of a property by query.</h4>
	 *
	 * This method can be used to return the distinct values of a property contained in the
	 * collection matching the query provided in the database native format. The method
	 * expects the property offset, the query filter and a boolean flag that determines
	 * whether to return the element count.
	 *
	 * If the element count flag is <tt>TRUE</tt>, the method will return an array indexed
	 * by the distinct property values and with the count as value, if <tt>FALSE</tt>, the
	 * method will return an array with the distint values.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theOffset			The property offset.
	 * @param mixed					$theFilter			The selection criteria.
	 * @param boolean				$doCount			Return element counts.
	 * @return array				The result set.
	 */
	abstract public function DistinctByQuery( $theOffset, $theFilter, $doCount = FALSE );


	/*===================================================================================
	 *	DistinctByExample																*
	 *==================================================================================*/

	/**
	 * <h4>Return the distinct values of a property by example.</h4>
	 *
	 * This method can be used to return the distinct values of a property contained in the
	 * collection matching the provided example document. The method expects the property
	 * offset, the example document provided as an array and a boolean flag that determines
	 * whether to return the element count.
	 *
	 * If the element count flag is <tt>TRUE</tt>, the method will return an array indexed
	 * by the distinct property values and with the count as value, if <tt>FALSE</tt>, the
	 * method will return an array with the distint values.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theOffset			The property offset.
	 * @param array					$theDocument		Example document as an array.
	 * @param boolean				$doCount			Return element counts.
	 * @return array				The result set.
	 */
	abstract public function DistinctByExample( 	  $theOffset,
												array $theDocument,
													  $doCount = FALSE );



/*=======================================================================================
 *																						*
 *								PUBLIC COUNTING INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Count																			*
	 *==================================================================================*/

	/**
	 * <h4>Return the documents count.</h4>
	 *
	 * This method can be used to return the current collection's documents count, the
	 * method will return an integer.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @return int					The total number of documents in the collection.
	 */
	abstract public function Count();


	/*===================================================================================
	 *	CountByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the number of documents by query.</h4>
	 *
	 * This method can be used to return the number of documents selected by the provided
	 * query, the method expects the provided query to be in the driver native format and
	 * will return an integer.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @return int					The number of selected documents.
	 */
	abstract public function CountByQuery( $theFilter );


	/*===================================================================================
	 *	CountByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Count documents by key.</h4>
	 *
	 * This method can be used to return the number of documents matching the provided key,
	 * the returned value will be either <tt>1</tt> or <tt>0</tt>.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theKey				Document key.
	 * @return int					The number of selected documents.
	 */
	abstract public function CountByKey( $theKey );


	/*===================================================================================
	 *	CountByHandle																	*
	 *==================================================================================*/

	/**
	 * <h4>Count documents by handle.</h4>
	 *
	 * This method can be used to return the number of documents matching the provided
	 * document handle, the returned value will be either <tt>1</tt> or <tt>0</tt>.
	 *
	 * Derived concrete classes must implement this method.
	 *
	 * @param mixed					$theHandle			Document handle.
	 * @return int					The number of selected documents.
	 *
	 * @uses Database()
	 */
	abstract public function CountByHandle( $theHandle );


	/*===================================================================================
	 *	CountByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Count documents by example.</h4>
	 *
	 * This method can be used to return the number of documents matching the provided
	 * example document. The method will select all documents in the collection whose
	 * properties match all the properties of the provided example document, this means that
	 * the method will generate a query that puts in <tt>AND</tt> all the provided document
	 * offsets.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theDocument		Example document as an array.
	 * @return int					The number of selected documents.
	 */
	abstract public function CountByExample( array $theDocument );



/*=======================================================================================
 *																						*
 *								PUBLIC DELETION INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Delete																			*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents by query.</h4>
	 *
	 * This method can be used to delete documents based on the provided query, the method
	 * expects the the query in the driver native format and will return the number of
	 * deleted documents.
	 *
	 * The options parameter can be used to determine whether to delete the first selected
	 * document, or all documents; by default, the method will delete all documents:
	 *
	 * <ul>
	 * 	<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether to delete the
	 * 		first document or all documents:
	 * 	 <ul>
	 * 		<li><tt>TRUE</tt>: Delete all selected documents.
	 * 		<li><tt>FALSE</tt>: Delete the first selected document.
	 * 	 </ul>
	 * </ul>
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theFilter			The deletion criteria.
	 * @param array					$theOptions			Query options.
	 * @return int					The number of deleted documents.
	 */
	abstract public function Delete(
		$theFilter,
		array $theOptions = [ kTOKEN_OPT_MANY => TRUE ]
	);


	/*===================================================================================
	 *	DeleteByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents by key.</h4>
	 *
	 * This method will delete the documents that match the provided key or keys and return
	 * the number of deleted documents. The method features two parameters:
	 *
	 * <ul>
	 *	<li><b>$theKey</b>: The document key(s) to match.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether the first
	 * 			parameter is a set of keys or a single key:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Provided a set of keys.
	 * 			<li><tt>FALSE</tt>: Provided a single ke.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * By default the method assumes you provided a single key.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theKey				Single or set of document keys.
	 * @param array					$theOptions			Query options.
	 * @return int					The number of deleted documents.
	 */
	abstract public function DeleteByKey(
		$theKey,
		array $theOptions = [ kTOKEN_OPT_MANY => FALSE ]
	);


	/*===================================================================================
	 *	DeleteByHandle																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents by handle.</h4>
	 *
	 * This method will delete the documents that match the provided handle or handles from
	 * the collections indicated in the handles and return the number of deleted documents.
	 * The method features two parameters:
	 *
	 * <ul>
	 *	<li><b>$theHandle</b>: The document handle(s) to match.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether the first
	 * 			parameter is a set of handles or a single handle:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Provided a set of handles.
	 * 			<li><tt>FALSE</tt>: Provided a single handle.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * By default the method assumes you provided a single handle.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theHandle			Single or set of document handles.
	 * @param array					$theOptions			Query options.
	 * @return int					The number of deleted documents.
	 */
	abstract public function DeleteByHandle(
		$theHandle,
		array $theOptions = [ kTOKEN_OPT_MANY => FALSE ]
	);


	/*===================================================================================
	 *	DeleteByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents by example.</h4>
	 *
	 * This method can be used to delete the first or all documents matching the provided
	 * example document. The method will select all documents in the collection whose
	 * properties match all the properties of the provided example document, this means that
	 * the method will generate a query that puts in <tt>AND</tt> all the provided document
	 * offsets.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The example document as an array.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether to delete
	 * 			the first or all documents:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Delete all documents.
	 * 			<li><tt>FALSE</tt>: Delete the first document.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * By default the method will delete all the selected documents.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theDocument		Example document as an array.
	 * @param array					$theOptions			Query options.
	 * @return int					The number of deleted documents.
	 */
	abstract public function DeleteByExample(
		array $theDocument,
		array $theOptions = [ kTOKEN_OPT_MANY => TRUE ]
	);


	/*===================================================================================
	 *	DeleteDocument																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete a document.</h4>
	 *
	 * This method can be used to delete the provided {@link Document} instance, the method
	 * will reset the document's persistent state and set the document's modification state.
	 *
	 * If the provided document does not have a key, the method will return <tt>0</tt>.
	 *
	 * @param Document				$theDocument		Document to delete.
	 * @return int					The number of deleted documents.
	 *
	 * @uses KeyOffset()
	 * @uses DeleteByKey()
	 * @uses normaliseDeletedDocument()
	 */
	public function DeleteDocument( Document $theDocument )
	{
		//
		// Get document key.
		//
		$key = $theDocument[ $this->KeyOffset() ];
		if( $key !== NULL )
		{
			//
			// Delete document.
			//
			$count = $this->DeleteByKey( $key );

			//
			// Normalise deleted document.
			//
			$this->normaliseDeletedDocument( $theDocument );

			return $count;															// ==>

		} // Has key.

		return 0;																	// ==>

	} // DeleteDocument.



/*=======================================================================================
 *																						*
 *								PUBLIC DOCUMENT SET INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	StoreDocumentSet																*
	 *==================================================================================*/

	/**
	 * <h4>Store a set of documents.</h4>
	 *
	 * This method can be used to store a set of documents, these documents may be of the
	 * following types:
	 *
	 * <ul>
	 * 	<li><tt>array</tt>: The method will assume that the document is to be inserted and
	 * 		the array will be left unchanged.
	 * 	<li><tt>{@link Container}</tt>: The method will assume that the document is to be
	 * 		inserted and the object will receive the new document key.
	 * 	<li><tt>{@link Document}</tt>: The method will call the document's
	 * 		{@link Document::Store()} method that will either insert or replace the
	 * 		document and update its state.
	 * </ul>
	 *
	 * The method will return an array of document handles.
	 *
	 * @param mixed					$theDocuments		The documents set.
	 * @return array				The document keys.
	 */
	public function StoreDocumentSet( $theDocuments )
	{
		//
		// Init local storage.
		//
		$handles = [];

		//
		// Iterate set.
		//
		foreach( $theDocuments as $document )
		{
			//
			// Handle array.
			//
			if( is_array( $document ) )
				$handles[] =
					$this->documentHandleCreate(
						$this->Insert( $document ) );

			//
			// Handle document.
			//
			elseif( $document instanceof Document )
				$handles[] = $document->Store();

			//
			// Handle containers.
			//
			elseif( $document instanceof Container )
			{
				//
				// Insert document.
				//
				$handles[] =
					$this->documentHandleCreate(
						$this->Insert( $document ) );

				//
				// Normalise socument.
				//
				$this->normaliseInsertedDucument( $document );
			}

			//
			// Handle other types.
			//
			else
				$handles[] =
					$this->documentHandleCreate(
						$this->Insert(
							$this->NewDocumentArray( $document ) ) );

		} // Iterating document set.

		return $handles;															// ==>

	} // StoreDocumentSet.


	/*===================================================================================
	 *	DeleteDocumentSet																*
	 *==================================================================================*/

	/**
	 * <h4>Delete a set of documents.</h4>
	 *
	 * This method can be used to delete a set of {@link Document} derived instances, the
	 * method expects a single parameter which must be an iterable set of {@link Document}
	 * instances, the method will return the number of deleted documents.
	 *
	 * @param mixed					$theDocuments		The documents set.
	 * @return array				The document keys.
	 */
	public function DeleteDocumentSet( $theDocuments )
	{
		//
		// Iterate document set.
		//
		$count = 0;
		foreach( $theDocuments as $document )
			$count += $document->Delete();

		return $count;																// ==>

	} // DeleteDocumentSet.


	/*===================================================================================
	 *	ConvertDocumentSet																*
	 *==================================================================================*/

	/**
	 * <h4>Convert a document set.</h4>
	 *
	 * This method will convert the elements of the provided document set into the format
	 * indicated by the provided code:
	 *
	 * <ul>
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_NATIVE}</tt>: Return a native driver document.
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_ARRAY}</tt>: Return an array.
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_CONTAINER}</tt>: Return a {@link Container}
	 * 		instance.
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_DOCUMENT}</tt>: Return a {@link Document}
	 * 		instance.
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return a document handle.
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return a document key.
	 * </ul>
	 *
	 * The method will return an array of converted elements, if the provided format is not
	 * supported, the method will raise an exception.
	 *
	 * If the third parameter is <tt>TRUE</tt>, it means that the document set comes from a
	 * query: in that case the documents will be normalised by the
	 * {@link normaliseSelectedDocument()} method.
	 *
	 * By default the method will return a set of {@link Document} instances.
	 *
	 * @param mixed					$theSet				Iterable set of documents.
	 * @param string				$theFormat			Expected document format.
	 * @param bool					$isPersistent		Persistent flag.
	 * @return array				Converted set.
	 *
	 * @uses KeyOffset()
	 * @uses NewDocumentArray()
	 */
	public function ConvertDocumentSet( $theSet,
										$theFormat = kTOKEN_OPT_FORMAT_DOCUMENT,
										$isPersistent = FALSE )
	{
		//
		// Init local storage.
		//
		$set = [];

		//
		// Iterate documents set.
		//
		foreach( $theSet as $document )
			$set[] = $this->formatDocument( $document, $theFormat, $isPersistent );

		return $set;																// ==>

	} // ConvertDocumentSet.



/*=======================================================================================
 *																						*
 *							PUBLIC HANDLE PARSING INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	ParseDocumentHandle																*
	 *==================================================================================*/

	/**
	 * <h4>Return handle components.</h4>
	 *
	 * This method can be used to parse and retrieve the provided handle components, it will
	 * return the collection name and object key in the provided reference parameters.
	 *
	 * The method must be implemented in derived concrete classes to handle database
	 * specific handles.
	 *
	 * @param mixed					$theHandle			The object handle.
	 * @param string			   &$theCollection		Receives collection name.
	 * @param mixed				   &$theIdentifier		Receives object key.
	 */
	abstract public function ParseDocumentHandle( $theHandle,
												  &$theCollection,
												  &$theIdentifier );



/*=======================================================================================
 *																						*
 *							PUBLIC INTERNAL OFFSETS INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	GetInternalOffsets																*
	 *==================================================================================*/

	/**
	 * <h4>Return internal offsets.</h4>
	 *
	 * This method can be used to retrieve the list of internal offsets, these are not
	 * registered among descriptors and are collection dependant.
	 *
	 * @return array				List of internal offsets.
	 */
	public function GetInternalOffsets()
	{
		return [
			$this->KeyOffset(), $this->ClassOffset(),
			$this->RevisionOffset(), $this->PropertiesOffset()
		];																			// ==>

	} // GetInternalOffsets.



/*=======================================================================================
 *																						*
 *						PROTECTED COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	collectionCreate																*
	 *==================================================================================*/

	/**
	 * <h4>Return a native collection object.</h4>
	 *
	 * This method should instantiate and return a native driver collection object.
	 *
	 * This method assumes that the server is connected and that the {@link Server()} was
	 * set.
	 *
	 * The options parameter represents a set of native options provided to the driver for
	 * performing the operation: if needed, in derived concrete classes you should define
	 * globally a set of options and subtitute a <tt>NULL</tt> value with them in this
	 * method, this will guarantee that the options will always be used when performing this
	 * operation.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Driver native options.
	 * @return mixed				Native collection object.
	 */
	abstract protected function collectionCreate( $theCollection, $theOptions = NULL );


	/*===================================================================================
	 *	collectionName																	*
	 *==================================================================================*/

	/**
	 * <h4>Return the collection name.</h4>
	 *
	 * This method should return the current collection name.
	 *
	 * Note that this method <em>must</em> return a non empty string.
	 *
	 * The provided parameter represents a set of native options provided to the driver for
	 * performing the operation: if needed, in derived concrete classes you should define
	 * globally a set of options and subtitute a <tt>NULL</tt> value with them in this
	 * method, this will guarantee that the options will always be used when performing this
	 * operation.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @return string				The collection name.
	 */
	abstract protected function collectionName();



/*=======================================================================================
 *																						*
 *								PROTECTED CONVERSION UTILITIES							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	documentCreate																	*
	 *==================================================================================*/

	/**
	 * <h4>Return a standard database document.</h4>
	 *
	 * This method can be used to return a standard database document, it expects an array
	 * containing the document data.
	 *
	 * In this class we return a {@link Document} instance, in derived classes you can
	 * overload this method to return a different kind of standard document.
	 *
	 * @param array					$theData			Document as an array.
	 * @return mixed				Standard document object.
	 */
	protected function documentCreate( array $theData )
	{
		return new \Milko\PHPLib\Document( $this, $theData );						// ==>

	} // documentCreate.


	/*===================================================================================
	 *	documentNativeCreate															*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database document.</h4>
	 *
	 * This method can be used to return a native database document, it expects an array
	 * containing all the public and internal document properties.
	 *
	 * The method is virtual and must be implemented by derived classes.
	 *
	 * @param array					$theData			Document as an array.
	 * @return mixed				Native database document object.
	 */
	abstract protected function documentNativeCreate( array $theData );


	/*===================================================================================
	 *	documentHandleCreate															*
	 *==================================================================================*/

	/**
	 * <h4>Return a document handle.</h4>
	 *
	 * This method should return a document handle from the provided key.
	 *
	 * The method is virtual and must be implemented by derived classes.
	 *
	 * @param mixed					$theKey				Document key.
	 * @return mixed				Document handle.
	 */
	abstract protected function documentHandleCreate( $theKey );



/*=======================================================================================
 *																						*
 *							PROTECTED PERSISTENCE INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	documentInsert																	*
	 *==================================================================================*/

	/**
	 * <h4>Insert a document.</h4>
	 *
	 * This method should insert the provided document into the current collection and
	 * return the newly created record key.
	 *
	 * The method expects a single parameter representing the document in the database
	 * native format.
	 *
	 * The method must be implemented in concrete derived classes.
	 *
	 * @param mixed					$theDocument		The native document to insert.
	 * @return mixed				The inserted document key.
	 */
	abstract protected function documentInsert( $theDocument );


	/*===================================================================================
	 *	documentReplace																	*
	 *==================================================================================*/

	/**
	 * <h4>Replace a document.</h4>
	 *
	 * This method should replace the provided document in the current collection and
	 * return <tt>1</tt> if the document was repaled, or <tt>0</tt>.
	 *
	 * The method expects the document key and the replacement document in the database
	 * native format.
	 *
	 * The method must be implemented in concrete derived classes.
	 *
	 * @param mixed					$theKey				The document key.
	 * @param mixed					$theDocument		The replacement native document.
	 * @return mixed				The number of replaced documents.
	 */
	abstract protected function documentReplace( $theKey, $theDocument );



/*=======================================================================================
 *																						*
 *								PROTECTED PERSISTENCE UTILITIES							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	normaliseInsertedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise inserted document.</h4>
	 *
	 * This method will be called when a document has been inserted, its duty is to pass
	 * information back to the document, including eventual internal native database
	 * properties.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The document instance provided for insertion.
	 *	<li><b>$theData</b>: The native inserted object.
	 *	<li><b>$theKey</b>: The document key ({@link KeyOffset()}).
	 * </ul>
	 *
	 * The method will operate exclusively on {@link \ArrayObject} derived instances, in
	 * this class it will set the document key ({@link KeyOffset()}); if the provided
	 * document is an instance of {@link Document}, the method will also set the
	 * {@link Document::IsPersistent()} state and reset its {@link Document::IsModified()}
	 * state.
	 *
	 * In derived classes you should first handle native document types and database
	 * specific internal properties, then call the parent method; you should also skip
	 * native documents.
	 *
	 * @param mixed					$theDocument		The inserted document.
	 * @param mixed					$theData			The native inserted document.
	 * @param mixed					$theKey				The document key.
	 *
	 * @uses KeyOffset()
	 * @uses Document::IsPersistent()
	 * @uses Document::IsModified()
	 */
	protected function normaliseInsertedDocument( $theDocument, $theData, $theKey )
	{
		//
		// Set document key.
		//
		if( $theDocument instanceof \ArrayObject )
		{
			//
			// Set key.
			//
			$theDocument[ $this->KeyOffset() ] = $theKey;

			//
			// Handle documents.
			//
			if( $theDocument instanceof Document )
			{
				//
				// Set persistent state.
				//
				$theDocument->IsPersistent( TRUE, $this );

				//
				// Reset modification state.
				//
				$theDocument->IsModified( FALSE, $this );

			} // Document instance.

		} // ArrayObject instance.

	} // normaliseInsertedDocument.


	/*===================================================================================
	 *	normaliseReplacedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise replaced document.</h4>
	 *
	 * This method will be called when a document has been replaced in the current
	 * collection, its duty is to pass information back to the document, including eventual
	 * internal native database properties.
	 *
	 * The method expects the replaced document, that is, the original document provided to
	 * the {@link Replace()} method and the native document returned by the operation.
	 *
	 * In this class we handle {@link Document} instances by resetting their
	 * {@link Document::IsModified()} state.
	 *
	 * In derived classes you should first handle native document types and database
	 * specific internal properties, then call the parent method; you should also skip
	 * native documents.
	 *
	 * @param mixed					$theDocument		The replaced document.
	 * @param mixed						$theData		The native database document.
	 *
	 * @uses Document::IsPersistent()
	 * @uses Document::IsModified()
	 */
	protected function normaliseReplacedDocument( $theDocument, $theData )
	{
		//
		// Reset modification state.
		//
		if( $theDocument instanceof Document )
		{
			//
			// Set persistent state.
			//
			$theDocument->IsPersistent( TRUE, $this );

			//
			// Reset modification state.
			//
			$theDocument->IsModified( FALSE, $this );

		} // Document instance.

	} // normaliseReplacedDocument.


	/*===================================================================================
	 *	normaliseSelectedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise selected document.</h4>
	 *
	 * This method will be called when a document has been selected from the current
	 * collection via a query, its duty is to pass information back to the document,
	 * including eventual internal native database properties.
	 *
	 * The method expects the selected document, that is, the document in the desired format
	 * and the native document returned by the query.
	 *
	 * In this class we handle {@link Document} instances by setting their
	 * {@link Document::IsPersistent()} state and reset their {@link Document::IsModified()}
	 * state.
	 *
	 * In derived classes you should first handle native document types and database
	 * specific internal properties, then call the parent method; you should also skip
	 * native documents.
	 *
	 * @param mixed					$theDocument		The selected document.
	 * @param mixed					$theData			The native database document.
	 *
	 * @uses Document::IsPersistent()
	 * @uses Document::IsModified()
	 */
	protected function normaliseSelectedDocument( $theDocument, $theData )
	{
		//
		// Handle documents.
		//
		if( $theDocument instanceof Document )
		{
			//
			// Set persistent state.
			//
			$theDocument->IsPersistent( TRUE, $this );

			//
			// Reset modification state.
			//
			$theDocument->IsModified( FALSE, $this );

		} // Is a document.

	} // normaliseSelectedDocument.


	/*===================================================================================
	 *	normaliseDeletedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise deleted document.</h4>
	 *
	 * This method will be called when a document has been deleted, its duty is to update
	 * the document state after the deletion.
	 *
	 * The method accepts the a single parameter that represents the original document
	 * provided to the {@link Delete()} method.
	 *
	 * In this class wel reset the {@link Document} {@link Document::IsPersistent()} state
	 * and set its {@link Document::IsModified()} state.
	 *
	 * In derived classes you should first handle native document types and database
	 * specific internal properties, then call the parent method; you should also skip
	 * native documents.
	 *
	 * @param mixed					$theDocument		The deleted document.
	 *
	 * @uses RevisionOffset()
	 */
	protected function normaliseDeletedDocument( $theDocument )
	{
		//
		// Handle documents.
		//
		if( $theDocument instanceof Document )
		{
			//
			// Reset persistent state.
			//
			$theDocument->IsPersistent( FALSE, $this );

			//
			// Set modification state.
			//
			$theDocument->IsModified( TRUE, $this );

			//
			// Remove creation time stamp.
			//
			$theDocument->offsetUnset( kTAG_CREATION );

			//
			// Remove modification time stamp.
			//
			$theDocument->offsetUnset( kTAG_MODIFICATION );

		} // Is a document.

	} // normaliseDeletedDocument.



/*=======================================================================================
 *																						*
 *								PROTECTED FORMATTING UTILITIES							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	formatDocument																	*
	 *==================================================================================*/

	/**
	 * <h4>Convert document according to provided format.</h4>
	 *
	 * This method will return the provided document in the provided format:
	 *
	 * <ul>
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_NATIVE}</tt>: Return a native driver document.
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_ARRAY}</tt>: Return an array.
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_CONTAINER}</tt>: Return a {@link Container}
	 * 		instance.
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_DOCUMENT}</tt>: Return a {@link Document}
	 * 		instance.
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return a document handle.
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return a document key.
	 * </ul>
	 *
	 * The method expects a document expressed as a database native document, an array or an
	 * object that can be cast to an array.
	 *
	 * If the third parameter is <tt>TRUE</tt>, it means that the document set comes from a
	 * query: in that case the documents will be normalised by the
	 * {@link normaliseSelectedDocument()} method.
	 *
	 * If the provided format is not supported, the method will raise an exception.
	 *
	 * @param mixed					$theData			Document data.
	 * @param string				$theFormat			Format type.
	 * @param bool					$isPersistent		Persistent flag.
	 * @return mixed				Formatted document object.
	 * @throws \InvalidArgumentException
	 *
	 * @uses NewDocument()
	 * @uses NewDocumentKey()
	 * @uses NewDocumentHandle()
	 * @uses NewDocumentContainer()
	 * @uses normaliseSelectedDocument()
	 */
	protected function formatDocument( $theData, $theFormat, bool $isPersistent )
	{
		//
		// Parse format.
		//
		switch( $theFormat )
		{
			case kTOKEN_OPT_FORMAT_DOCUMENT:
				$document = $this->NewDocument( $theData );
				break;

			case kTOKEN_OPT_FORMAT_KEY:
				return $this->NewDocumentKey( $theData );							// ==>

			case kTOKEN_OPT_FORMAT_ARRAY:
				return $this->NewDocumentArray( $theData );							// ==>

			case kTOKEN_OPT_FORMAT_HANDLE:
				return $this->NewDocumentHandle( $theData );						// ==>

			case kTOKEN_OPT_FORMAT_NATIVE:
				return $this->NewDocumentNative( $theData );						// ==>

			case kTOKEN_OPT_FORMAT_CONTAINER:
				$document = $this->NewDocumentContainer( $theData );
				break;

			default:
				throw new \InvalidArgumentException(
					"Unsupported format type [$theFormat]." );					// !@! ==>
		}

		//
		// Normalise document.
		//
		if( $isPersistent )
			$this->normaliseSelectedDocument( $document, $theData );

		return $document;															// ==>

	} // formatDocument.



} // class Collection.


?>
