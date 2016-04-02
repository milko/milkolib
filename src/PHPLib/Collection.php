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
require_once( 'tags.inc.php' );

/**
 * Global token definitions.
 */
require_once( 'tokens.inc.php' );

use Milko\PHPLib\Container;

/*=======================================================================================
 *																						*
 *									Collection.php										*
 *																						*
 *======================================================================================*/

/**
 * <h4>Collection ancestor object.</h4>
 *
 * This <em>abstract</em> class is the ancestor of all classes representing collection
 * instances.
 *
 * This class features a data member, {@link $mDatabase}, that holds the {@link Database}
 * object that instantiated the current collection and another data member,
 * {@link $mNativeObject}, that holds the native driver's collection object.
 *
 * The class implements a public interface that implements the common interface of derived
 * concrete classes:
 *
 * <ul>
 * 	<li><em>Attribute accessors:</em>
 *   <ul>
 * 		<li><b>{@link Server()}</b>: Return the database server object.
 * 		<li><b>{@link Database()}</b>: Return the database object.
 * 		<li><b>{@link Connection()}</b>: Return the collection native driver object.
 *   </ul>
 * 	<li><em>Collection related:</em>
 *   <ul>
 * 		<li><b>{@link Truncate()}</b>: Clear collection contents; this method is virtual.
 * 		<li><b>{@link Drop()}</b>: Drop current collection; this method is virtual.
 *   </ul>
 * 	<li><em>Document related:</em>
 *   <ul>
 * 		<li><b>{@link NewDocument()}</b>: Convert native data to a standard
 * 			{@link Document}.
 * 		<li><b>{@link NewNativeDocument()}</b>: Convert a standard {@link Document} to
 * 			native data.
 * 		<li><b>{@link NewDocumentHandle()}</b>: Convert a document to a document reference.
 *   </ul>
 * 	<li><em>Default document properties:</em>
 *   <ul>
 * 		<li><b>{@link KeyOffset()}</b>: Return the document key offset.
 * 		<li><b>{@link ClassOffset()}</b>: Return the document class offset.
 * 		<li><b>{@link RevisionOffset()}</b>: Return the document revision offset.
 * 			offset.
 *   </ul>
 * 	<li><em>Record related:</em>
 *   <ul>
 * 		<li><b>{@link InsertDocument()}</b>: Insert one or more documents.
 * 		<li><b>{@link InsertBulk()}</b>: Insert a bulk of records.
 * 		<li><b>{@link DeleteDocument()}</b>: Delete one or more documents.
 * 		<li><b>{@link DeleteByKey()}</b>: Delete by key.
 * 		<li><b>{@link DeleteByExample()}</b>: Delete by example.
 * 		<li><b>{@link DeleteByQuery()}</b>: Delete by native query.
 * 		<li><b>{@link Update()}</b>: Update first or all selected documents.
 * 		<li><b>{@link Replace()}</b>: Replace first or all selected documents.
 * 		<li><b>{@link FindByKey()}</b>: Search by key.
 * 		<li><b>{@link FindByExample()}</b>: Search by example.
 * 		<li><b>{@link FindByQuery()}</b>: Perform a native query.
 * 		<li><b>{@link RecordCount()}</b>: Return collection record count.
 * 		<li><b>{@link CountByExample()}</b>: Return record count by example.
 * 		<li><b>{@link CountByQuery()}</b>: Return record count by native query.
 * 		<li><b>{@link MapReduce()}</b>: Perform a map and reduce query.
 *   </ul>
 * </ul>
 *
 * The public methods do not implement the actual operations, this is delegated to a
 * protected virtual interface which must be implemented by derived concrete classes:
 *
 * <ul>
 * 	<li><b>{@link collectionNew()}</b>: Instantiate a driver native database instance.
 * 	<li><b>{@link collectionName()}</b>: Return the collection name.
 * 	<li><b>{@link doInsert()}</b>: Insert one or more records.
 * 	<li><b>{@link doUpdate()}</b>: Update one or many records.
 * 	<li><b>{@link doReplace()}</b>: Replace one or many records.
 * 	<li><b>{@link doFindByKey()}</b>: Find one or many records by key.
 * 	<li><b>{@link doFindByExample()}</b>: Find one or many records by example.
 * 	<li><b>{@link doFindByQuery()}</b>: Perform a driver native query.
 * 	<li><b>{@link doDeleteByKey()}</b>: Delete one or many records by key.
 * 	<li><b>{@link doDeleteByExample()}</b>: Delete one or many records by example.
 * 	<li><b>{@link doDeleteByQuery()}</b>: Delete one or many records by query.
 * 	<li><b>{@link doCount()}</b>: Return record count in collection.
 * 	<li><b>{@link doCountByExample()}</b>: Return record count by example.
 * 	<li><b>{@link doCountByQuery()}</b>: Return record count by native query.
 * </ul>
 *
 * When persisting documents, this class expects all provided documents to be instances of
 * {@link Document}.
 *
 *	@package	Core
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		17/02/2016
 *
 *	@example	../../test/Collection.php
 *	@example
 * $server = new Milko\PHPLib\DataServer( 'protocol://user:pass@host:9090/database/collection' );<br/>
 * $database = $server->RetrieveDatabase( "database" );<br/>
 * $collection = $database->RetrieveCollection( "collection" );<br/>
 * // Work with that collection...<br/>
 * $collection->Drop(); // Drop collection.
 */
abstract class Collection extends Container
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
	protected $mNativeObject = NULL;
	
	
	
	
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
	 * A database is instantiated by providing the {@link Database} instance to which the
	 * collection belongs, the collection name and a set of native database driver options.
	 *
	 * @param Database				$theDatabase		Database.
	 * @param string				$theCollection		Collection name.
	 * @param array					$theOptions			Native driver options.
	 *
	 * @uses collectionNew()
	 *
	 * @example
	 * $server = new DataServer( 'driver://user:pass@host:8989' );<br/>
	 * $database = new Database( $server, "database" );<br/>
	 * $collection = new Collection( $database, "collection" );
	 *
	 * @example
	 * // In general you will use this form:<br/>
	 * $server = new DataServer( 'driver://user:pass@host:8989/database/collection' );<br/>
	 * $database = $server->RetrieveDatabase( "database" );<br/>
	 * $collection = $database->RetrieveCollection( "collection" );
	 */
	public function __construct( Database $theDatabase, $theCollection, $theOptions = NULL )
	{
		//
		// Normalise optons.
		//
		if( $theOptions === NULL )
			$theOptions = [];

		//
		// Call parent constructor.
		//
		parent::__construct();

		//
		// Store server instance.
		//
		$this->mDatabase = $theDatabase;
		
		//
		// Store the driver instance.
		//
		$this->mNativeObject = $this->collectionNew( $theCollection, $theOptions );
		
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
	 * @return DataServer			Collection server object.
	 *
	 * @uses Database()
	 */
	public function Server()						{	return $this->Database()->Server();	}


	/*===================================================================================
	 *	Database																		*
	 *==================================================================================*/

	/**
	 * <h4>Return the database object.</h4>
	 *
	 * This method can be used to retrieve the database object.
	 *
	 * @return Database				Database object.
	 *
	 * @example
	 * $database = $this->Database();
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
	 *
	 * @example
	 * $col = $this->Connection();
	 */
	public function Connection()						{	return $this->mNativeObject;	}
	
	
	
/*=======================================================================================
 *																						*
 *							PUBLIC COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/
	


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
	 */
	abstract public function Truncate();


	/*===================================================================================
	 *	Drop																			*
	 *==================================================================================*/

	/**
	 * <h4>Drop the current collection.</h4>
	 *
	 * This method can be used to drop the current collection, it is the responsibility of
	 * the caller to ensure the server is connected.
	 *
	 * Derived concrete classes must implement this method.
	 */
	abstract public function Drop();



/*=======================================================================================
 *																						*
 *							PUBLIC DOCUMENT INSTANTIATION INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	NewNativeDocument																*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database document.</h4>
	 *
	 * This method should return a native database document from the provided data which
	 * can be an array, an instance of {@link Container}, or an object that can be converted
	 * to an array.
	 *
	 * In this class we call the protected virtual {@link toDocumentNative()} method by
	 * providing the document converted to an array: that method will take care of
	 * instantiating the correct object.
	 *
	 * Derived classes must implement the above mentioned protected method and should
	 * overload this method to intercept documents already provided in the native format.
	 *
	 * @param mixed					$theData			Document data.
	 * @return mixed				Database native object.
	 *
	 * @uses NewDocumentArray()
	 * @uses toDocumentNative()
	 */
	public function NewNativeDocument( $theData )
	{
		return $this->toDocumentNative( $this->NewDocumentArray( $theData ) );		// ==>

	} // NewNativeDocument.


	/*===================================================================================
	 *	NewDocument																		*
	 *==================================================================================*/

	/**
	 * <h4>Return a {@link Document} instance.</h4>
	 *
	 * This method should instantiate a {@link Container} instance from the provided data
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
	 * 		{@link Container} object.
	 * </ul>
	 *
	 * @param mixed					$theData			Document data.
	 * @param string				$theClass			Expected class name.
	 * @return Document				Standard document object.
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

		return $this->toDocument( $document );										// ==>

	} // NewDocument.


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
	 * Derived classes should overload this method to handle database specific documents.
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
	 * The method is virtual, since document handles depend on the specific database
	 * engine, this means that the structure and business logic must be implemented in
	 * derived classes.
	 *
	 * The method expects a native database document, an array, an instance of
	 * {@link Container}, or an object that can be converted to an array.
	 *
	 * If the provided document does not have its {@link KeyOffset()} property, this method
	 * should raise an exception.
	 *
	 * Derived concrete classes must implement this method.
	 *
	 * @param mixed					$theData			Document data.
	 * @return mixed				Document handle.
	 */
	abstract public function NewDocumentHandle( $theData );


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
	 * Derived concrete classes can implement this method by handling database native
	 * document types; in this class we handle all other document types.
	 *
	 * @param mixed					$theData			Document data.
	 * @return mixed				Document key.
	 *
	 * @uses NewDocumentArray()
	 * @uses KeyOffset()
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
 *							PUBLIC INSERT MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Insert																			*
	 *==================================================================================*/

	/**
	 * <h4>Insert document(s).</h4>
	 *
	 * This method can be used to insert a single or a set of documents into the current
	 * collection, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The document or documents to be inserted, they should
	 * 		either be in the native database format, {@link Container) instances, arrays or
	 * 		objects that can be cast to an array.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether the first
	 * 			parameter represents a single document or a set of documents:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Insert a set of documents, in this case the provided
	 * 				parameter should be an iterable object as an array or cursor.
	 * 			<li><tt>FALSE</tt>: Insert a single document, in this case the provided
	 * 				parameter will be considered the data to be inserted.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * Two protected methods, {@link doInsertOne()} and {@link doInsertMany()}, will take
	 * care of performing the actual insertions of, respectively, a single or a set of
	 * documents.
	 *
	 * When inserting {@link Document} instances this method will will update the inserted
	 * key, set the document's persistent state and reset the document's modification state.
	 *
	 * The method will return the newly inserted document key or an array of document keys
	 * if provided with a set of documents.
	 *
	 * By default the operation will assume we provided a single document.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theDocument		The document(s) to be inserted.
	 * @param array					$theOptions			Insert options.
	 * @return mixed				The document's unique identifier(s).
	 *
	 * @uses doInsertOne()
	 * @uses doInsertMany()
	 * @uses normaliseOptions()
	 *
	 * @example
	 * // Insert a single document.<br/>
	 * $id = $collection->Insert( $document );<br/>
	 * // Insert a list of documents.<br/>
	 * $ids = $collection->Insert( $list, [ kTOKEN_OPT_MANY => TRUE ] );
	 */
	public function Insert( $theDocument, $theOptions = NULL )
	{
		//
		// Assume, if nor stated, a scalar document was provided.
		//
		$this->normaliseOptions( kTOKEN_OPT_MANY, FALSE, $theOptions );
		
		//
		// Insert document set.
		//
		if( $theOptions[ kTOKEN_OPT_MANY ] )
			return $this->doInsertMany( $theDocument );								// ==>
		
		return $this->doInsertOne( $theDocument );									// ==>

	} // Insert.


	/*===================================================================================
	 *	InsertBulk																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert a set of documents.</h4>
	 *
	 * The method expects a single parameter which represents the list of documents, these
	 * should either be in the native database format, {@link Container) instances, arrays,
	 * or objects that can be cast to an array. The parameter should be an iterable object.
	 *
	 * The method will not modify the provided document set, use {@link Insert()} for that
	 * matter.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theList			The documents list.
	 * @return array				The document keys.
	 *
	 * @uses doInsertBulk()
	 */
	public function InsertBulk( $theList )
	{
		//
		// Create a list of native documents.
		//
		$list = [];
		foreach( $theList as $document )
			$list[] = $this->NewNativeDocument( $document );

		return $this->doInsertBulk( $list );										// ==>

	} // InsertBulk.



/*=======================================================================================
 *																						*
 *							PUBLIC DELETE MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Delete																			*
	 *==================================================================================*/

	/**
	 * <h4>Delete document(s).</h4>
	 *
	 * This method can be used to delete a single or a set of documents from the current
	 * collection, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The document or documents to be deleted, they should
	 * 		either be in the native database format, {@link Container) instances, arrays or
	 * 		objects that can be cast to an array.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether the first
	 * 			parameter represents a single document or a set of documents:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Delete a set of documents, in this case the provided
	 * 				parameter should be an iterable object as an array or cursor.
	 * 			<li><tt>FALSE</tt>: Delete a single document, in this case the provided
	 * 				parameter will be considered the data to be inserted.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * Two protected methods, {@link doDeleteOne()} and {@link doDeleteMany()}, will take
	 * care of performing the actual deletions of, respectively, a single or a set of
	 * documents.
	 *
	 * The method will return the number of deleted documents.
	 *
	 * By default the operation will assume we provided a single document.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theDocument		The document(s) to be deleted.
	 * @param array					$theOptions			Insert options.
	 * @return int					Number of deleted documents.
	 *
	 * @example
	 * // Delete a single document.<br/>
	 * $count = $collection->Delete( $document );<br/>
	 * // Delete a list of documents.<br/>
	 * $count = $collection->Delete( $list, [ kTOKEN_OPT_MANY => TRUE ] );
	 */
	public function Delete( $theDocument, $theOptions = NULL )
	{
		//
		// Assume, if nor stated, a scalar document was provided.
		//
		$this->normaliseOptions( kTOKEN_OPT_MANY, FALSE, $theOptions );

		//
		// Delete document set.
		//
		if( $theOptions[ kTOKEN_OPT_MANY ] )
			return $this->doDeleteMany( $theDocument );								// ==>

		return $this->doDeleteOne( $theDocument );									// ==>

	} // Delete.


	/*===================================================================================
	 *	DeleteByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete by key.</h4>
	 *
	 * This method will delete the documents that match the provided key or key(s) in the
	 * current collection, or do nothing if the document cannot be matched.
	 *
	 * The method features two parameters:
	 *
	 * <ul>
	 *	<li><b>$theKey</b>: The document key(s) to match.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether the first
	 * 			parameter is a set of keys or a single key:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Provided a set of keys; the first parameter should be
	 * 				iterable.
	 * 			<li><tt>FALSE</tt>: Provided a single key.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will use the {@link doDeleteByKey()} method to perform the deletions and
	 * will return the number of deleted documents.
	 *
	 * By default the operation will assume we provided a single key.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theKey				The document identifier.
	 * @param array					$theOptions			Delete options.
	 * @return int					The deleted records count.
	 *
	 * @uses doDeleteByKey()
	 * @uses normaliseOptions()
	 * @see kTOKEN_OPT_MANY
	 */
	public function DeleteByKey( $theKey, $theOptions = NULL )
	{
		//
		// Normalise options.
		//
		$this->normaliseOptions( kTOKEN_OPT_MANY, FALSE, $theOptions );

		return $this->doDeleteByKey( $theKey, $theOptions );						// ==>

	} // DeleteByKey.


	/*===================================================================================
	 *	DeleteByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete by example.</h4>
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
	 *	<li><b>$theDocument</b>: The example document; it must be either an array, a
	 * 		{@link Container} instance, or a native database document.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether to delete the
	 *			first or all selected documents:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Delete all selected documents; the first parameter should
	 * 				be iterable.
	 * 			<li><tt>FALSE</tt>: Delete the first document.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * By default, the method will delete all selected documents and will return the number
	 * of deleted records.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theDocument		The example document.
	 * @param array					$theOptions			Delete options.
	 * @return int					The deleted records count.
	 *
	 * @uses doDeleteByExample()
	 * @uses normaliseOptions()
	 * @see kTOKEN_OPT_MANY
	 */
	public function DeleteByExample( $theDocument = NULL, $theOptions = NULL )
	{
		//
		// Normalise options.
		//
		$this->normaliseOptions( kTOKEN_OPT_MANY, TRUE, $theOptions );

		return $this->doDeleteByExample( $theDocument, $theOptions );				// ==>

	} // DeleteByExample.


	/*===================================================================================
	 *	DeleteByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete by query.</h4>
	 *
	 * This method can be used to delete all documents matching the provided query. The
	 * method will select all documents in the collection that match the provided query and
	 * delete them.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theQuery</b>: The selection query in the native database format.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether to delete the
	 *			first or all selected documents:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Delete all selected documents; the first parameter should
	 * 				be iterable.
	 * 			<li><tt>FALSE</tt>: Delete the first document.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * By default, the method will delete all selected records and will return the number
	 * of deleted records.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theQuery			The selection filter.
	 * @param array					$theOptions			Delete options.
	 * @return int					The number of deleted documents.
	 *
	 * @uses doDeleteByQuery()
	 * @uses normaliseOptions()
	 * @see kTOKEN_OPT_MANY
	 */
	public function DeleteByQuery( $theQuery = NULL, $theOptions = NULL )
	{
		//
		// Normalise options.
		//
		$this->normaliseOptions( kTOKEN_OPT_MANY, TRUE, $theOptions );

		return $this->doDeleteByQuery( $theQuery, $theOptions );					// ==>

	} // DeleteByQuery.



/*=======================================================================================
 *																						*
 *							PUBLIC UPDATE MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Update																			*
	 *==================================================================================*/

	/**
	 * <h4>Update documents.</h4>
	 *
	 * This method can be used to update one or more documents in the collection, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCriteria</b>: The modification criteria in the native database format.
	 *	<li><b>$theFilter</b>: The selection criteria in the native database format.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether to update
	 * 			the first selected document, or all the selected documents:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Update all records selected by the filter.
	 * 			<li><tt>FALSE</tt>: Update the first record selected by the filter.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * Both the criteria and the filter must be provided in the native database format and
	 * if no options are provided, the operation will process all documents in the
	 * selection.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theCriteria		The modification criteria.
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Update options.
	 * @return int					The number of modified records.
	 *
	 * @uses doUpdate()
	 * @uses normaliseOptions()
	 * @see kTOKEN_OPT_MANY
	 *
	 * @example
	 * // Update first document.<br/>
	 * $count = $collection->Update( $criteria, $filter, [ kTOKEN_OPT_MANY => FALSE ] );<br/>
	 * // Update all documents.<br/>
	 * $count = $collection->Update( $criteria, $filter );
	 */
	public function Update( $theCriteria, $theFilter = NULL, $theOptions = NULL )
	{
		//
		// Normalise options.
		//
		$this->normaliseOptions( kTOKEN_OPT_MANY, TRUE, $theOptions );

		return $this->doUpdate( $theFilter, $theCriteria, $theOptions );			// ==>

	} // Update.


	/*===================================================================================
	 *	Replace																			*
	 *==================================================================================*/

	/**
	 * <h4>Replace a document.</h4>
	 *
	 * This method can be used to replace the provided document in a collection, the method
	 * expects the document in the native database format, as a {@link Container) instance,
	 * as an array or as an object that can be cast to an array.
	 *
	 * The provided replacement document state will not be updated in this method.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theDocument		The replacement document.
	 * @return int					The number of replaced records.
	 *
	 * @uses doReplace()
	 * @uses NewNativeDocument()
	 */
	public function Replace( $theDocument )
	{
		return $this->doReplace( $this->NewNativeDocument( $theDocument ) );		// ==>

	} // Replace.



/*=======================================================================================
 *																						*
 *							PUBLIC SELECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	FindByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Find by key.</h4>
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
	 * 			<li><tt>FALSE</tt>: Provided a single key, will return the document or
	 * 				<tt>NULL</tt>.
	 * 		 </ul>
	 * 		<li><b>{@link kTOKEN_OPT_FORMAT}</b>: This option determines the result format:
	 * 		 <ul>
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_NATIVE}</tt>: Return the unchanged driver
	 * 				database result.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_STANDARD}</tt>: Return {@link Container}
	 * 				instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return
	 * 				(@link NewDocumentHandle()}) instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return document key(s).
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * If the provided {@link kTOKEN_OPT_MANY} option is <tt>FALSE</tt>, the method will
	 * return a scalar result (except if the {@link kTOKEN_OPT_FORMAT} is
	 * {@link kTOKEN_OPT_FORMAT_NATIVE}), if not, it will return an array of results.
	 *
	 * By default the method will return documents as {@link Container} derived instances
	 * and assume the provided key is a scalar.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theKey				The document key(s).
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found document(s).
	 *
	 * @uses doFindByKey()
	 * @uses normaliseOptions()
	 * @see kTOKEN_OPT_MANY
	 * @see kTOKEN_OPT_FORMAT
	 */
	public function FindByKey( $theKey, $theOptions = NULL )
	{
		//
		// Normalise options.
		//
		$this->normaliseOptions(
			kTOKEN_OPT_MANY, FALSE, $theOptions );
		$this->normaliseOptions(
			kTOKEN_OPT_FORMAT, kTOKEN_OPT_FORMAT_STANDARD, $theOptions );

		return $this->doFindByKey( $theKey, $theOptions );							// ==>

	} // FindByKey.


	/*===================================================================================
	 *	FindByHandle																	*
	 *==================================================================================*/

	/**
	 * <h4>Find by document handle.</h4>
	 *
	 * This method will return the documents that match the provided document handle or
	 * handles in the collection specified by the handle, the method features two
	 * parameters:
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
	 * 			<li><tt>FALSE</tt>: Provided a single handle, will return the document or
	 * 				<tt>NULL</tt>.
	 * 		 </ul>
	 * 		<li><b>{@link kTOKEN_OPT_FORMAT}</b>: This option determines the result format:
	 * 		 <ul>
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_NATIVE}</tt>: Return the unchanged driver
	 * 				database result.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_STANDARD}</tt>: Return {@link Container}
	 * 				instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return
	 * 				(@link NewDocumentHandle()}) instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return document key(s).
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * If the provided {@link kTOKEN_OPT_MANY} option is <tt>FALSE</tt>, the method will
	 * return a scalar result (except if the {@link kTOKEN_OPT_FORMAT} is
	 * {@link kTOKEN_OPT_FORMAT_NATIVE}), if not, it will return an array of results.
	 *
	 * By default the method will return documents as {@link Container} derived instances
	 * and assume the provided key is a scalar.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theHandle			The document handle(s).
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found document(s).
	 *
	 * @uses doFindByHandle()
	 * @uses normaliseOptions()
	 * @see kTOKEN_OPT_MANY
	 * @see kTOKEN_OPT_FORMAT
	 */
	public function FindByHandle( $theHandle, $theOptions = NULL )
	{
		//
		// Normalise options.
		//
		$this->normaliseOptions(
			kTOKEN_OPT_MANY, FALSE, $theOptions );
		$this->normaliseOptions(
			kTOKEN_OPT_FORMAT, kTOKEN_OPT_FORMAT_STANDARD, $theOptions );

		return $this->doFindByHandle( $theHandle, $theOptions );					// ==>

	} // FindByHandle.


	/*===================================================================================
	 *	FindByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Find by example.</h4>
	 *
	 * This method can be used to select all documents matching the provided example
	 * document. The method will select all documents in the collection whose properties
	 * match all the properties of the provided example document, this means that the method
	 * will generate a query that puts in <tt>AND</tt> all the provided document offsets.
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The example document; it must be either an array, a
	 * 		{@link Container} instance, or a native database document.
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
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_STANDARD}</tt>: Return either a scalar or
	 * 				an array of {@link Container} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return either a scalar or
	 * 				an array of document handles (@link NewDocumentHandle()}).
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return document key(s).
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * The result will always be an iterable object, by default the method will use the
	 * {@link kTOKEN_OPT_FORMAT_STANDARD} option and return the full selection.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theDocument		The example document.
	 * @param array					$theOptions			Find options.
	 * @return Iterator				The found records.
	 *
	 * @uses doFindByExample()
	 * @uses normaliseOptions()
	 * @see kTOKEN_OPT_SKIP
	 * @see kTOKEN_OPT_LIMIT
	 * @see kTOKEN_OPT_FORMAT
	 */
	public function FindByExample( $theDocument = NULL, $theOptions = NULL )
	{
		//
		// Normalise options.
		//
		$this->normaliseOptions(
			kTOKEN_OPT_FORMAT, kTOKEN_OPT_FORMAT_STANDARD, $theOptions );
		if( array_key_exists( kTOKEN_OPT_LIMIT, $theOptions )
		 && (! array_key_exists( kTOKEN_OPT_SKIP, $theOptions )) )
			$theOptions[ kTOKEN_OPT_SKIP ] = 0;

		return $this->doFindByExample( $theDocument, $theOptions );					// ==>

	} // FindByExample.


	/*===================================================================================
	 *	FindByQuery																		*
	 *==================================================================================*/

	/**
	 * <h4>Query the collection.</h4>
	 *
	 * This method can be used to perform a query in the current collection, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theQuery</b>: The query in the driver native format.
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
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_STANDARD}</tt>: Return either a scalar or
	 * 				an array of {@link Container} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return either a scalar or
	 * 				an array of document handles (@link NewDocumentHandle()}).
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return document key(s).
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * The result will always be an iterable object, by default the method will use the
	 * {@link kTOKEN_OPT_FORMAT_STANDARD} option and return the full selection.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @param array					$theOptions			Query options.
	 * @return mixed				The found records.
	 *
	 * @uses doFindByQuery()
	 * @uses normaliseOptions()
	 * @see kTOKEN_OPT_SKIP
	 * @see kTOKEN_OPT_LIMIT
	 * @see kTOKEN_OPT_FORMAT
	 */
	public function FindByQuery( $theQuery = NULL, $theOptions = NULL )
	{
		//
		// Normalise options.
		//
		$this->normaliseOptions(
			kTOKEN_OPT_FORMAT, kTOKEN_OPT_FORMAT_STANDARD, $theOptions );
		if( array_key_exists( kTOKEN_OPT_LIMIT, $theOptions )
		 && (! array_key_exists( kTOKEN_OPT_SKIP, $theOptions )) )
			$theOptions[ kTOKEN_OPT_SKIP ] = 0;

		return $this->doFindByQuery( $theQuery, $theOptions );						// ==>

	} // FindByQuery.



/*=======================================================================================
 *																						*
 *							PUBLIC COUNTER MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	RecordCount																		*
	 *==================================================================================*/

	/**
	 * <h4>Count documents.</h4>
	 *
	 * This method can be used to get the total number of documents in the collection.
	 *
	 * Concrete derived classes must implement this method.
	 *
	 * @return int					The number of records in the collection.
	 */
	abstract public function RecordCount();


	/*===================================================================================
	 *	CountByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Count by example.</h4>
	 *
	 * This method can be used to return the record count matching the provided example
	 * document. The method will select all documents in the collection whose properties
	 * match all the properties of the provided example document, this means that the method
	 * will generate a query that puts in <tt>AND</tt> all the provided document offsets.
	 *
	 * The provided document must be either an array, a {@link Container} instance, or a
	 * native database document.
	 *
	 * This method must be implemented in concrete derived classes.
	 *
	 * @param mixed					$theDocument		The example document.
	 * @return int					The found records count.
	 *
	 * @example
	 * // Get record count.
	 * $count = $collection->CountByExample( [ 'color' => 'red', 'city' => 'Rome' ] );
	 */
	abstract public function CountByExample( $theDocument = NULL );


	/*===================================================================================
	 *	CountByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Count by query.</h4>
	 *
	 * This method can be used to return the record count matching the provided query, the
	 * query should be provided in the database native format.
	 *
	 * This method must be implemented in concrete derived classes.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @return int					The found records count.
	 */
	abstract public function CountByQuery( $theQuery = NULL );



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
	 * The method will return an array of documents as arrays.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$thePipeline		The aggregation pipeline.
	 * @param array					$theOptions			Query options.
	 * @return array				The result set.
	 *
	 * @uses doMapReduce()
	 */
	abstract public function MapReduce( $thePipeline, $theOptions = NULL );

	
	
/*=======================================================================================
 *																						*
 *						PROTECTED COLLECTION MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/
	
	
	
	/*===================================================================================
	 *	collectionNew																	*
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
	abstract protected function collectionNew( $theCollection, $theOptions = NULL );
	
	
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
 *							PROTECTED DOCUMENT INSERT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doInsert																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert a document.</h4>
	 *
	 * This method should insert the provided document into the current collection, the
	 * document should be provided in the database native document format.
	 *
	 * The method should return the newly inserted document key.
	 *
	 * This method must be implemented in derived classes to handle the specific database
	 * driver.
	 *
	 * @param mixed					$theDocument		The document to be inserted.
	 * @return mixed				The inserted document's key.
	 */
	abstract protected function doInsert( $theDocument );


	/*===================================================================================
	 *	doInsertOne																		*
	 *==================================================================================*/

	/**
	 * <h4>Insert a single document.</h4>
	 *
	 * This method will insert the provided document into the current collection, the
	 * document should be provided in a format compatible with the
	 * {@link NewNativeDocument()} method.
	 *
	 * The method will first follow the following workflow:
	 *
	 * <ul>
	 * 	<li>If the data is a {@link Document} instance, it will validate the object and
	 * 		store eventual embedded subdocuments.
	 * 	<li>It will convert the data to a native document.
	 * 	<li>It will insert the data.
	 * 	<li>If the data is an {@link \ArrayObject} instance, it will set the document key.
	 * 	<li>If the data is a {@link Document} instance, it will set the object's persistent
	 * 		state and reset its modification state.
	 * </ul>
	 *
	 * The method will return the newly inserted document's key ({@link KeyOffset()}).
	 *
	 * @param mixed					$theDocument		The document to be inserted.
	 * @return mixed				The inserted document's key.
	 *
	 * @uses doInsert()
	 * @uses NewNativeDocument()
	 * @uses normaliseInsertedDocument()
	 * @uses Document::Validate()
	 * @uses Document::StoreSubdocuments()
	 */
	protected function doInsertOne( $theDocument )
	{
		//
		// Prepare document.
		//
		if( $theDocument instanceof \Milko\PHPLib\Document )
		{
			//
			// Validate document.
			//
			$theDocument->Validate();

			//
			// Store sub-documents.
			//
			$theDocument->StoreSubdocuments();
		}

		//
		// Convert to native document.
		//
		$document = $this->NewNativeDocument( $theDocument );

		//
		// Insert the document.
		//
		$key = $this->doInsert( $document );

		//
		// Normalise inserted document.
		//
		$this->normaliseInsertedDocument( $theDocument, $document, $key );

		return $key;																// ==>

	} // doInsertOne.


	/*===================================================================================
	 *	doInsertMany																	*
	 *==================================================================================*/

	/**
	 * <h4>Insert a list of documents.</h4>
	 *
	 * This method is the equivalent of {@link doInsertOne()} for a set of documents, it
	 * will return an array of inserted document keys.
	 *
	 * The provided list should be an iterable object.
	 *
	 * @param mixed					$theList			An iterable list of documents.
	 * @return array				The list of inserted document keys.
	 *
	 * @uses doInsertOne()
	 */
	protected function doInsertMany( $theList )
	{
		//
		// Iterate list.
		//
		$keys = [];
		foreach( $theList as $document )
			$keys[] = $this->doInsertOne( $document );

		return $keys;																// ==>

	} // doInsertMany.


	/*===================================================================================
	 *	doInsertBulk																	*
	 *==================================================================================*/

	/**
	 * <h4>Insert a list of documents.</h4>
	 *
	 * This method should insert the provided list of documents into the current collection,
	 * the provided list of documents should be in the native database format.
	 *
	 * This method will return an array of the newly inserted document keys and will not
	 * update the provided list.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param array					$theList			The documents list.
	 * @return array				The document keys.
	 */
	abstract protected function doInsertBulk( array $theList );



/*=======================================================================================
 *																						*
 *							PROTECTED DOCUMENT DELETE INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doDelete																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete a document.</h4>
	 *
	 * This method should delete the provided document from the current collection, the
	 * document should be provided in the database native document format.
	 *
	 * The method should return <tt>1</tt> if the document was deleted, or <tt>0</tt>.
	 *
	 * This method must be implemented in derived classes to handle the specific database
	 * driver.
	 *
	 * @param mixed					$theDocument		The document to be deleted.
	 * @return int					The number of deleted documents.
	 */
	abstract protected function doDelete( $theDocument );


	/*===================================================================================
	 *	doDeleteOne																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete a document.</h4>
	 *
	 * This method should delete the provided document from the current collection, the
	 * document should be provided in a format compatible with the
	 * {@link NewNativeDocument()} method.
	 *
	 * When implementing the method you <em>must</em> follow this workflow:
	 *
	 * <ul>
	 * 	<li>Prepare the document to be deleted, generally by calling the protected method
	 * 		{@link NewNativeDocument()}, or by retrieving its key.
	 * 	<li>Check the document key ({@link KeyOffset()}): if it is missing raise an
	 * 		exception.
	 * 	<li>Delete the document.
	 * 	<li>Call the protected method {@link normalistDeletedDocument()}.
	 * 	<li>Return the number of deleted documents.
	 * </ul>
	 *
	 * The method should return the number of deleted documents, normally <tt>1</tt>.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theDocument		The document to be deleted.
	 * @return int					The number of deleted documents.
	 *
	 * @uses doDelete()
	 * @uses NewNativeDocument()
	 * @uses normaliseDeletedDocument()
	 */
	protected function doDeleteOne( $theDocument )
	{
		//
		// Convert to native format.
		//
		$document = $this->NewNativeDocument( $theDocument );

		//
		// Delete document.
		//
		$count = $this->doDelete( $document );

		//
		// Normalise deleted document.
		//
		if( $count )
			$this->normaliseDeletedDocument( $theDocument );

		return $count;																// ==>

	} // doDeleteOne.


	/*===================================================================================
	 *	doDeleteMany																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete a list of documents.</h4>
	 *
	 * This method should delete the provided list of documents from the current collection,
	 * the documents in the list should be provided in the database native format, as
	 * returned by the {@link NewNativeDocument()} method.
	 *
	 * The provided list should either be an array or an iterable object.
	 *
	 * The method should return the number of deleted documents.
	 *
	 * We implement the method in this class to iteratively call the {@link doDeleteOne}
	 * method, in derived classes you may overload this method if necessary.
	 *
	 * @param mixed					$theList			An iterable list of documents in
	 * 													native database format.
	 * @return array				Number of deleted methods.
	 */
	protected function doDeleteMany( $theList )
	{
		//
		// Iterate list.
		//
		$count = 0;
		foreach( $theList as $document )
			$count += $this->doDeleteOne( $document );

		return $count;																// ==>

	} // doDeleteMany.


	/*===================================================================================
	 *	doDeleteByKey																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records by key.</h4>
	 *
	 * This method should delete the first or all records matching the provided key(s), the
	 * method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theKey</b>: The document key or set of keys.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether to delete
	 * 			the first selected document, or all the selected documents:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Delete all records selected by the filter.
	 * 			<li><tt>FALSE</tt>: Delete the first record selected by the filter.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * The method should return the number of deleted documents.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theKey				The document key(s).
	 * @param array					$theOptions			Find options.
	 * @return int					The number of deleted documents.
	 */
	abstract protected function doDeleteByKey( $theKey, array $theOptions );


	/*===================================================================================
	 *	doDeleteByExample																*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records by example.</h4>
	 *
	 * This method should delete the first or all records matching the provided example
	 * document, the method expects the following parameters:
	 *
	 * The method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The example document; it must be either an array, a
	 * 		{@link Container} instance, or a native database document.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether to delete the
	 *			first or all selected documents:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Delete all selected documents; the first parameter should
	 * 				be iterable.
	 * 			<li><tt>FALSE</tt>: Delete the first document.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * The method should return the number of deleted documents.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theDocument		The example document.
	 * @param array					$theOptions			Delete options.
	 * @return int					The number of deleted documents.
	 */
	abstract protected function doDeleteByExample( $theDocument, array $theOptions );


	/*===================================================================================
	 *	doDeleteByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records by query.</h4>
	 *
	 * This method should delete the first or all records matching the provided query, the
	 * method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theQuery</b>: The selection query in the native database format.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether to delete the
	 *			first or all selected documents:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Delete all selected documents; the first parameter should
	 * 				be iterable.
	 * 			<li><tt>FALSE</tt>: Delete the first document.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * The method should return the number of deleted documents.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @param array					$theOptions			Delete options.
	 * @return int					The number of deleted documents.
	 */
	abstract protected function doDeleteByQuery( $theQuery, array $theOptions );



/*=======================================================================================
 *																						*
 *							PROTECTED UPDATE MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doUpdate																		*
	 *==================================================================================*/

	/**
	 * <h4>Update one or more records.</h4>
	 *
	 * This method should update the first or all records matching the provided search
	 * criteria, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theCriteria</b>: The modification criteria in the native database format.
	 *	<li><b>$theFilter</b>: The selection criteria in the native database format.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether to update
	 * 			the first selected document, or all the selected documents:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Update all records selected by the filter.
	 * 			<li><tt>FALSE</tt>: Update the first record selected by the filter.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * The method should return the number of updated documents.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param mixed					$theCriteria		The modification criteria.
	 * @param array					$theOptions			Update options.
	 * @return int					The number of modified records.
	 */
	abstract protected function doUpdate( $theFilter, $theCriteria, array $theOptions );


	/*===================================================================================
	 *	doReplace																		*
	 *==================================================================================*/

	/**
	 * <h4>Replace a record.</h4>
	 *
	 * This method should replace the provided document in the current collection, the
	 * document should be provided in the native database format.
	 *
	 * The method should return the number of replaced documents.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theDocument		The replacement document.
	 * @return int					The number of replaced records.
	 */
	abstract protected function doReplace( $theDocument );



/*=======================================================================================
 *																						*
 *							PROTECTED SELECTION MANAGEMENT INTERFACE					*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doFindByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Find by key.</h4>
	 *
	 * This method should return the document(s) that matches the provided key(s) in the
	 * current collection, or <tt>NULL</tt> if the document cannot be matched.
	 *
	 * The method features two parameters:
	 *
	 * <ul>
	 *	<li><b>$theKey</b>: The document key(s) to match.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether the first
	 * 			parameter is a set of keys or a single key:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Provided a set of keys, will return an array of results.
	 * 			<li><tt>FALSE</tt>: Provided a single key, will return the document or
	 * 				<tt>NULL</tt>.
	 * 		 </ul>
	 * 		<li><b>{@link kTOKEN_OPT_FORMAT}</b>: This option determines the result format:
	 * 		 <ul>
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_NATIVE}</tt>: Return the unchanged driver
	 * 				database result.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_STANDARD}</tt>: The resulting document(s)
	 * 				will be {@link Container} instances resulting from the
	 * 				{@link NewDocument()} method.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return either a scalar or
	 * 				an array of document handles (@link NewDocumentHandle()}).
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return document key(s).
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * If the {@link kTOKEN_OPT_FORMAT_STANDARD} {@link kTOKEN_OPT_FORMAT} option is set,
	 * the resulting documents will be processed by the {@link normaliseSelectedDocument()}
	 * method that will set the document persistent state and reset its modification state.
	 *
	 * If the provided {@link kTOKEN_OPT_MANY} option is <tt>FALSE</tt>, the method should
	 * return a scalar result, if not, it should return an array of results (except if the
	 * {@link kTOKEN_OPT_FORMAT} is {@link kTOKEN_OPT_FORMAT_NATIVE}).
	 *
	 * @param mixed					$theKey				The document identifier.
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found document(s).
	 *
	 * @uses findKey()
	 * @uses NewDocument()
	 * @uses NewDocumentKey()
	 * @uses NewDocumentHandle()
	 * @uses normaliseSelectedDocument()
	 * @see kTOKEN_OPT_MANY
	 * @see kTOKEN_OPT_FORMAT
	 */
	protected function doFindByKey( $theKey, array $theOptions )
	{
		//
		// Select by key.
		//
		$cursor = $this->doFindKey( $theKey, $theOptions );
		if( $cursor === NULL )
			return NULL;															// ==>

		return $this->normaliseCursor( $cursor, $theOptions );						// ==>

	} // doFindByKey.


	/*===================================================================================
	 *	doFindByHandle																	*
	 *==================================================================================*/

	/**
	 * <h4>Find by key.</h4>
	 *
	 * This method should return the document(s) that match the provided handle(s) in the
	 * collection specified in the handle, the method features two parameters:
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
	 * 			<li><tt>FALSE</tt>: Provided a single handle, will return the document or
	 * 				<tt>NULL</tt>.
	 * 		 </ul>
	 * 		<li><b>{@link kTOKEN_OPT_FORMAT}</b>: This option determines the result format:
	 * 		 <ul>
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_NATIVE}</tt>: Return the unchanged driver
	 * 				database result.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_STANDARD}</tt>: The resulting document(s)
	 * 				will be {@link Container} instances resulting from the
	 * 				{@link NewDocument()} method.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return either a scalar or
	 * 				an array of document handles (@link NewDocumentHandle()}).
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return document key(s).
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * If the {@link kTOKEN_OPT_FORMAT_STANDARD} {@link kTOKEN_OPT_FORMAT} option is set,
	 * the resulting documents will be processed by the {@link normaliseSelectedDocument()}
	 * method that will set the document persistent state and reset its modification state.
	 *
	 * If the provided {@link kTOKEN_OPT_MANY} option is <tt>FALSE</tt>, the method should
	 * return a scalar result, if not, it should return an array of results (except if the
	 * {@link kTOKEN_OPT_FORMAT} is {@link kTOKEN_OPT_FORMAT_NATIVE}).
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theHandle			The document handle(s).
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found document(s).
	 */
	protected function doFindByHandle( $theHandle, array $theOptions )
	{
		//
		// Select by handle.
		//
		$cursor = $this->doFindHandle( $theHandle, $theOptions );
		if( $cursor === NULL )
			return NULL;															// ==>

		return $this->normaliseCursor( $cursor, $theOptions );						// ==>

	} // doFindByKey.


	/*===================================================================================
	 *	doFindByExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Find by example the first or all records.</h4>
	 *
	 * This method should find the records matching the provided example document, the
	 * method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The example document; it must be a document in the native
	 * 		database format, a {@link Container) instance, an array or an object that can be
	 * 		cast to an array.
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
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_STANDARD}</tt>: Return an array of
	 * 				{@link Container} instances ({@link NewDocument()}).
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return an array of document
	 * 				handles (@link NewDocumentHandle()}).
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return selected document key(s)
	 * 				({@link NewDocumentKey()}).
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * If the {@link kTOKEN_OPT_FORMAT_STANDARD} {@link kTOKEN_OPT_FORMAT} option is set,
	 * the resulting documents will be processed by the {@link normaliseSelectedDocument()}
	 * method that will set the document persistent state and reset its modification state.
	 *
	 * The result should always be an iterable object.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theDocument		The example document.
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found records.
	 */
	protected function doFindByExample( $theDocument, array $theOptions )
	{
		//
		// Select by example.
		//
		$cursor = $this->doFindExample( $theDocument, $theOptions );
		if( $cursor === NULL )
			return NULL;															// ==>

		return $this->normaliseCursor( $cursor, $theOptions );						// ==>

	} // doFindByExample.


	/*===================================================================================
	 *	doFindByQuery																	*
	 *==================================================================================*/

	/**
	 * <h4>Query the collection.</h4>
	 *
	 * This method should perform the provided query expressed in the driver's native
	 * format, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theQuery</b>: The query in the driver native format.
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
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_STANDARD}</tt>: Return an array of
	 * 				{@link Container} instances ({@link NewDocument()}).
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return an array of document
	 * 				handles (@link NewDocumentHandle()}).
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_KEY}</tt>: Return selected document key(s)
	 * 				({@link NewDocumentKey()}).
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * If the {@link kTOKEN_OPT_FORMAT_STANDARD} {@link kTOKEN_OPT_FORMAT} option is set,
	 * the resulting documents will be processed by the {@link normaliseSelectedDocument()}
	 * method that will set the document persistent state and reset its modification state.
	 *
	 * The result will always be an iterable object.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found records.
	 */
	protected function doFindByQuery( $theQuery, array $theOptions )
	{
		//
		// Select by example.
		//
		$cursor = $this->doFindQuery( $theQuery, $theOptions );
		if( $cursor === NULL )
			return NULL;															// ==>

		return $this->normaliseCursor( $cursor, $theOptions );						// ==>

	} // doFindByQuery.



/*=======================================================================================
 *																						*
 *							PROTECTED QUERY MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	doFindKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Find by key.</h4>
	 *
	 * This method should search for the provided key(s) and return the resulting cursor,
	 * the method must be implemented in derived classes.
	 *
	 * @param mixed					$theKey				The document key(s).
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found document(s).
	 */
	abstract protected function doFindKey( $theKey, array $theOptions );


	/*===================================================================================
	 *	doFindHandle																	*
	 *==================================================================================*/

	/**
	 * <h4>Find by handle.</h4>
	 *
	 * This method should search for the provided handle(s) and return the resulting cursor,
	 * the method must be implemented in derived classes.
	 *
	 * @param mixed					$theHandle			The document handle(s).
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found document(s).
	 */
	abstract protected function doFindHandle( $theHandle, array $theOptions );


	/*===================================================================================
	 *	doFindExample																	*
	 *==================================================================================*/

	/**
	 * <h4>Find by example.</h4>
	 *
	 * This method should search for the provided handle(s) and return the resulting cursor,
	 * the method must be implemented in derived classes.
	 *
	 * @param mixed					$theDocument		The example document.
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found document(s).
	 */
	abstract protected function doFindExample( $theDocument, array $theOptions );


	/*===================================================================================
	 *	doFindQuery																		*
	 *==================================================================================*/

	/**
	 * <h4>Find by example.</h4>
	 *
	 * This method should search for the provided handle(s) and return the resulting cursor,
	 * the method must be implemented in derived classes.
	 *
	 * @param array					$theQuery			The selection criteria.
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found document(s).
	 */
	abstract protected function doFindQuery( $theQuery, array $theOptions );




/*=======================================================================================
 *																						*
 *								PROTECTED CONVERSION UTILITIES							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	toDocument																		*
	 *==================================================================================*/

	/**
	 * <h4>Return a standard database document.</h4>
	 *
	 * This method can be used to return a standard database document, it expects an array
	 * containing the document data.
	 *
	 * In this class we return a {@link Container} instance, in derived classes you can
	 * overload this method to return a different kind of standard document.
	 *
	 * @param array					$theData			Document as an array.
	 * @return mixed				Native database document object.
	 */
	protected function toDocument( array $theData )
	{
		return new \Milko\PHPLib\Container( $theData );								// ==>

	} // toDocument.


	/*===================================================================================
	 *	toDocumentNative																*
	 *==================================================================================*/

	/**
	 * <h4>Return a native database document.</h4>
	 *
	 * This method can be used to return a native database document, it expects an array
	 * containing all the public and internal document properties.
	 *
	 * The method is virtual and must be implemented by derived classes.
	 *
	 * @param array					$theDocument		Document properties.
	 * @return mixed				Native database document object.
	 */
	abstract protected function toDocumentNative( array $theDocument );




	/*=======================================================================================
	 *																						*
	 *								PROTECTED GENERIC UTILITIES								*
	 *																						*
	 *======================================================================================*/



	/*===================================================================================
	 *	normaliseCursor																	*
	 *==================================================================================*/

	/**
	 * <h4>Normalise cursor.</h4>
	 *
	 * This method can be used to normalise the result of a query, it expects a cursor as
	 * an iterable object and an options list in which {@link kTOKEN_OPT_MANY} indicates
	 * whether to return the first scalar result, the native result or a set of documents,
	 * in the format indicated by the {@link kTOKEN_OPT_FORMAT} option.
	 *
	 * The method will return an array if the {@link kTOKEN_OPT_MANY} option is set, or a
	 * scalar result.
	 *
	 * @param mixed					$theCursor			The iterable cursor.
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found document(s).
	 * @throws \InvalidArgumentException
	 *
	 * @uses NewDocument()
	 * @uses NewDocumentKey()
	 * @uses NewDocumentHandle()
	 * @uses normaliseSelectedDocument()
	 * @see kTOKEN_OPT_MANY
	 * @see kTOKEN_OPT_FORMAT
	 */
	protected function normaliseCursor( $theCursor, array $theOptions )
	{
		//
		// Handle native result.
		//
		if( $theOptions[ kTOKEN_OPT_FORMAT ] == kTOKEN_OPT_FORMAT_NATIVE )
			return $theCursor;														// ==>

		//
		// Iterate cursor.
		//
		$list = [];
		foreach( $theCursor as $document )
		{
			//
			// Format document.
			//
			switch( $theOptions[ kTOKEN_OPT_FORMAT ] )
			{
				case kTOKEN_OPT_FORMAT_STANDARD:
					$tmp = $this->NewDocument( $document );
					$this->normaliseSelectedDocument( $tmp, $document );
					$list[] = $tmp;
					break;

				case kTOKEN_OPT_FORMAT_HANDLE:
					$list[] = $this->NewDocumentHandle( $document );
					break;

				case kTOKEN_OPT_FORMAT_KEY:
					$list[] = $this->NewDocumentKey( $document );
					break;

				default:
					throw new \InvalidArgumentException (
						"Invalid conversion format code." );					// !@! ==>
			}
		}

		//
		// Handle scalar result.
		//
		if( array_key_exists( kTOKEN_OPT_MANY, $theOptions )
		 && ($theOptions[ kTOKEN_OPT_MANY ] === FALSE) )
			return ( count( $list ) )
				 ? $list[ 0 ]														// ==>
				 : NULL;															// ==>

		return $list;																// ==>

	} // normaliseCursor.


	/*===================================================================================
	 *	normaliseOptions																*
	 *==================================================================================*/

	/**
	 * <h4>Normalise operation options.</h4>
	 *
	 * This method can be used to normalise the options provided to methods, it expects
	 * three parameters:
	 *
	 * <ul>
	 *	<li><b>$theToken</b>: The option token.
	 *	<li><b>$theDefault</b>: The default option value.
	 *	<li><b>&$theOptions</b>: The reference to the options.
	 * </ul>
	 *
	 * @param string				$theToken			The option token.
	 * @param mixed					$theDefault			The Default choice.
	 * @param array				   &$theOptions			The options reference.
	 * @throws \InvalidArgumentException
	 */
	protected function normaliseOptions( string $theToken, $theDefault, &$theOptions )
	{
		//
		// Init options.
		//
		if( $theOptions === NULL )
			$theOptions = [ $theToken => $theDefault ];

		//
		// Validate options.
		//
		elseif( ! is_array( $theOptions ) )
			throw new \InvalidArgumentException (
				"Options must be provided as an array." );						// !@! ==>

		//
		// Set default option.
		//
		elseif( ! array_key_exists( $theToken, $theOptions ) )
			$theOptions[ $theToken ] = $theDefault;

	} // normaliseOptions.


	/*===================================================================================
	 *	normaliseInsertedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise inserted document.</h4>
	 *
	 * This method will be called when a {@link Container} instance has been inserted, its
	 * duty is to pass information back to the document, including eventual internal
	 * native database properties.
	 *
	 * The method accepts the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The {@link Container} instance provided for insertion.
	 *	<li><b>$theData</b>: The native inserted object.
	 *	<li><b>$theKey</b>: The document key ({@link KeyOffset()}).
	 * </ul>
	 *
	 * The method is implemented in this class to set global properties:
	 *
	 * <ul>
	 *	<li><tt>{@link KeyOffset()}</tt>: The newly inserted key offset will be set back
	 * 		into the document, this is necessary when the document key is automatically
	 * 		generated by the database.
	 *	<li><tt>{@link Document::IsPersistent()}</tt>: If the provided document is a
	 * 		{@link Document} instance, the document's persistent state will be set.
	 *	<li><tt>{@link Document::IsModified()}</tt>: If the provided document is a
	 * 		{@link Document} instance, the document's modification state will be reset, this
	 * 		should be the last operation performed on the object.
	 * </ul>
	 *
	 * In derived classes you should handle native document types and database specific
	 * internal properties.
	 *
	 * @param mixed					$theDocument		The inserted document.
	 * @param mixed					$theData			The native inserted document.
	 * @param mixed					$theKey				The document key.
	 *
	 * @uses Document::SetKey()
	 * @uses Document::KeyOffset()
	 * @uses Document::IsPersistent()
	 * @uses Document::IsModified()
	 */
	protected function normaliseInsertedDocument( $theDocument, $theData, $theKey )
	{
		//
		// Handle documents.
		//
		if( $theDocument instanceof Document )
		{
			//
			// Set key.
			//
			$theDocument->SetKey( $theKey, $this );

			//
			// Set persistent state.
			//
			$theDocument->IsPersistent( TRUE, $this );

			//
			// Reset modification state.
			//
			$theDocument->IsModified( FALSE, $this );

		} // Is a document.

		//
		// Handle containers.
		//
		elseif( $theDocument instanceof \ArrayObject )
			$theDocument[ $this->KeyOffset() ] = $theKey;

	} // normaliseInsertedDocument.


	/*===================================================================================
	 *	normaliseDeletedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise inserted document.</h4>
	 *
	 * This method will be called when a {@link Container} instance has been deleted, its
	 * duty is to pass information back to the document, including eventual internal
	 * native database properties.
	 *
	 * The method expects a single parameter which should be a {@link Container} instance.
	 *
	 * The method is implemented in this class to handle {@link Document} instances:
	 *
	 * <ul>
	 *	<li><tt>{@link Document::IsPersistent()}</tt>: If the provided document is a
	 * 		{@link Document} instance, the document's persistent state will be reset.
	 *	<li><tt>{@link Document::IsModified()}</tt>: If the provided document is a
	 * 		{@link Document} instance, the document's modification state will be set.
	 * </ul>
	 *
	 * In derived classes you should first manage internal database properties, if relevant,
	 * then call the current method.
	 *
	 * @param Container				$theDocument		The deleted document.
	 *
	 * @uses Document::IsPersistent()
	 * @uses Document::IsModified()
	 */
	protected function normaliseDeletedDocument( Container $theDocument )
	{
		//
		// Handle documents.
		//
		if( $theDocument instanceof Document )
		{
			//
			// Set persistent state.
			//
			$theDocument->IsPersistent( FALSE, $this );

			//
			// Reset modification state.
			//
			$theDocument->IsModified( TRUE, $this );

		} // Is a document.

	} // normaliseDeletedDocument.


	/*===================================================================================
	 *	normaliseSelectedDocument														*
	 *==================================================================================*/

	/**
	 * <h4>Normalise selected document.</h4>
	 *
	 * This method will be called when a {@link Container} instance has been selected from
	 * the current collection via a query, its duty is to pass information back to the
	 * document, including eventual internal native database properties.
	 *
	 * The method expects a parameter which should be a {@link Container} instance and the
	 * native database document returned by the selection.
	 *
	 * The method is implemented in this class to handle {@link Document} instances:
	 *
	 * <ul>
	 *	<li><tt>{@link Document::IsPersistent()}</tt>: If the provided document is a
	 * 		{@link Document} instance, the document's persistent state will be set.
	 *	<li><tt>{@link Document::IsModified()}</tt>: If the provided document is a
	 * 		{@link Document} instance, the document's modification state will be reset.
	 * </ul>
	 *
	 * In derived classes you should first manage internal database properties, if relevant,
	 * then call the current method.
	 *
	 * @param Container				$theDocument		The selected document.
	 * @param mixed					$theData			The native database document.
	 *
	 * @uses Document::IsPersistent()
	 * @uses Document::IsModified()
	 */
	protected function normaliseSelectedDocument( Container $theDocument, $theData )
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

	
	
} // class Collection.


?>
