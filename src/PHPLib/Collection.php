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
	 * documents, converting them to an array and passing them to the parent method.
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
	 * method expects a single parameter that represents the document as an array.
	 *
	 * The method will return the newly inserted document key.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theDocument		The document as an array.
	 * @return mixed				The document's unique identifier.
	 */
	abstract public function Insert( array $theDocument );


	/*===================================================================================
	 *	InsertMany																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert a set of documents.</h4>
	 *
	 * This method can be used to insert a set of documents into the current collection, the
	 * method expects a single parameter that represents the set of documents as an array,
	 * each array element must be a document represented as an array.
	 *
	 * The method will return the list of newly inserted document keys.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theDocuments		The documents set as an array.
	 * @return array				The document unique identifiers.
	 */
	abstract public function InsertMany( array $theDocuments );


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
	 * @return array				The document unique identifiers.
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
	 * method expects the document to be provided as a native database document, an array or
	 * an object that can be cast to array, the method will return the number of replaced
	 * documents.
	 *
	 * The method expects the replacement document to have its key, if that is missing, the
	 * method should raise an exception.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theDocument		The replacement document.
	 * @return int					The number of replaced documents.
	 */
	abstract public function Replace( $theDocument );


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
		$theFilter,
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
	 *	CountByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Find documents by example.</h4>
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
	 * If the provided format is not supported, the method will raise an exception.
	 *
	 * @param mixed					$theData			Document data.
	 * @param string				$theFormat			Format type.
	 * @return mixed				Formatted document object.
	 * @throws \InvalidArgumentException
	 *
	 * @uses NewDocument()
	 * @uses NewDocumentKey()
	 * @uses NewDocumentHandle()
	 * @uses NewDocumentContainer()
	 */
	protected function formatDocument( $theData, $theFormat )
	{
		//
		// Parse format.
		//
		switch( $theFormat )
		{
			case kTOKEN_OPT_FORMAT_DOCUMENT:
				return $this->NewDocument( $theData );								// ==>

			case kTOKEN_OPT_FORMAT_KEY:
				return $this->NewDocumentKey( $theData );							// ==>

			case kTOKEN_OPT_FORMAT_HANDLE:
				return $this->NewDocumentHandle( $theData );						// ==>

			case kTOKEN_OPT_FORMAT_NATIVE:
				return $this->NewDocumentNative( $theData );						// ==>

			case kTOKEN_OPT_FORMAT_CONTAINER:
				return $this->NewDocumentContainer( $theData );						// ==>
		}

		throw new \InvalidArgumentException(
			"Unsupported format type [$theFormat]." );							// !@! ==>

	} // formatDocument.


	/*===================================================================================
	 *	formatCursor																	*
	 *==================================================================================*/

	/**
	 * <h4>Convert a set of documents according to provided format.</h4>
	 *
	 * This method will return an array of documents in the provided format:
	 *
	 * <ul>
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_NATIVE}</tt>: Return a native driver document.
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_CONTAINER}</tt>: Return a {@link Container}
	 * 		instance.
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_DOCUMENT}</tt>: Return a {@link Document}
	 * 		instance.
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return a document handle.
	 * 	<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return a document key.
	 * </ul>
	 *
	 * The method expects an iterable set of documents and will return an array of elements
	 * corresponding to the provided format.
	 *
	 * @param mixed					$theCursor			Iterable set of documents.
	 * @param string				$theFormat			Format type.
	 * @return mixed				Array of formatted documents.
	 *
	 * @uses formatDocument()
	 */
	protected function formatCursor( $theCursor, $theFormat )
	{
		//
		// Init local storage.
		//
		$list = [];

		//
		// Iterate documents set.
		//
		foreach( $theCursor as $document )
			$list[] = $this->formatDocument( $document, $theFormat );

		return $list;																// ==>

	} // formatCursor.



} // class Collection.


?>
