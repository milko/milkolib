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
use Milko\PHPLib\Document;

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
 * 		<li><b>{@link ToDocument()}</b>: Convert native data to a standard {@link Document}.
 * 		<li><b>{@link FromDocument()}</b>: Convert a standard {@link Document} to native
 * 			data.
 * 		<li><b>{@link ToDocumentHandle()}</b>: Convert native data to a document reference.
 *   </ul>
 * 	<li><em>Default document properties:</em>
 *   <ul>
 * 		<li><b>{@link KeyOffset()}</b>: Return the document key offset.
 * 		<li><b>{@link ClassOffset()}</b>: Return the document class offset.
 * 		<li><b>{@link RevisionOffset()}</b>: Return the document revision offset.
 *   </ul>
 * 	<li><em>Record related:</em>
 *   <ul>
 * 		<li><b>{@link Insert()}</b>: Insert one or more records.
 * 		<li><b>{@link Update()}</b>: Update one or more records.
 * 		<li><b>{@link Replace()}</b>: Replace one or more records.
 * 		<li><b>{@link Delete()}</b>: Delete one or more records.
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
 * 	<li><b>{@link doDelete()}</b>: Delete one or many records.
 * 	<li><b>{@link doFindByKey()}</b>: Find one or many records by example.
 * 	<li><b>{@link doFindByExample()}</b>: Find one or many records by example.
 * 	<li><b>{@link doFindByQuery()}</b>: Perform a driver native query.
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
 *							PUBLIC DOCUMENT MANAGEMENT INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	ToDocument																		*
	 *==================================================================================*/

	/**
	 * <h4>Convert native data to standard document.</h4>
	 *
	 * This method can be used to instantiate a {@link Document} object from database native
	 * data, the method expects two parameters:
	 *
	 * <ul>
	 * 	<li><b>$theData</b>: The data expressed in the database native type.
	 * 	<li><b>$theClass</b>: The class name of the resulting {@link Document} instance.
	 * </ul>
	 *
	 * The method will first attempt to find the class in the  provided data under the
	 * {@link KeyOffset()} property, if it is not found, the method will use the provided
	 * class parameter.
	 *
	 * This method is declared virtual, to allow database native derived classes to handle
	 * their native types.
	 *
	 * Derived concrete classes must implement this method.
	 *
	 * @param mixed					$theData			Database native document.
	 * @param string				$theClass			Expected class name.
	 * @return Document				Standard document object.
	 */
	abstract public function ToDocument( $theData, string $theClass = 'Milko\PHPLib\Document' );


	/*===================================================================================
	 *	FromDocument																	*
	 *==================================================================================*/

	/**
	 * <h4>Convert a standard document to native data.</h4>
	 *
	 * This method can be used to convert a {@link Document} object into database native
	 * data.
	 *
	 * This method is declared virtual, to allow database native derived classes to handle
	 * their native types.
	 *
	 * Derived concrete classes must implement this method.
	 *
	 * @param Document				$theDocument		Document to be converted.
	 * @return mixed				Database native object.
	 */
	abstract public function FromDocument( Document $theDocument );


	/*===================================================================================
	 *	ToDocumentHandle																*
	 *==================================================================================*/

	/**
	 * <h4>Convert native data to a document handle.</h4>
	 *
	 * This method can be used to convert a document into a document handle or reference,
	 * this value can be used by {@link Document} instances to reference another document.
	 *
	 * The method expects a single parameter which can either be a native database document,
	 * or an instance of the {@link Document} class; in the latter case it is assumed that
	 * the document's collection is the current one.
	 *
	 * The resulting value is dependent on the specific database engine used.
	 *
	 * Derived concrete classes must implement this method.
	 *
	 * @param mixed					$theDocument		Document to reference.
	 * @return mixed				Document handle.
	 */
	abstract public function ToDocumentHandle( $theDocument );



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
 *							PUBLIC RECORD MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Insert																			*
	 *==================================================================================*/

	/**
	 * <h4>Insert documents.</h4>
	 *
	 * This method can be used to insert one or more documents in the collection, the method
	 * expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The document or documents to be inserted.
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
	 * The record(s) to be inserted must be either {@link Document} instances, or documents
	 * in the native database format.
	 *
	 * The method will return either an array of keys, if you provided a set of documents,
	 * or a single key, if not; the key corresponds to the object ID and is referenced by
	 * the {@link KeyOffset()} document property.
	 *
	 * By default the operation will assume we provided a single document.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theDocument		The document(s) to be inserted.
	 * @param array					$theOptions			Insert options.
	 * @return mixed				The document's unique identifier(s).
	 *
	 * @uses doInsert()
	 * @uses normaliseOptions()
	 * @see kTOKEN_OPT_MANY
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
		// Normalise options.
		//
		$this->normaliseOptions( kTOKEN_OPT_MANY, FALSE, $theOptions );

		return $this->doInsert( $theDocument, $theOptions );						// ==>

	} // Insert.

	
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
	 *	<li><b>$theCriteria</b>: The modification criteria.
	 *	<li><b>$theFilter</b>: The selection criteria.
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
	 * <h4>Replace a record.</h4>
	 *
	 * This method can be used to replace the first selected document in a collection, the
	 * method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The replacement document.
	 *	<li><b>$theFilter</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether to replace
	 * 			the first selected document, or all the selected documents:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Replace all records selected by the filter.
	 * 			<li><tt>FALSE</tt>: Replace the first record selected by the filter.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * The filter must be provided in the native database format and the document can be
	 * provided either as a {@link Document} instance, or in the native database format; if
	 * no options are provided, the operation will process all documents in the selection.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theDocument		The replacement document.
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Replace options.
	 * @return int					The number of replaced records.
	 *
	 * @uses doReplace()
	 * @uses normaliseOptions()
	 * @see kTOKEN_OPT_MANY
	 */
	public function Replace( $theDocument, $theFilter = NULL, $theOptions = NULL )
	{
		//
		// Normalise options.
		//
		$this->normaliseOptions( kTOKEN_OPT_MANY, TRUE, $theOptions );

		return $this->doReplace( $theFilter, $theDocument, $theOptions );			// ==>

	} // Replace.


	/*===================================================================================
	 *	Delete																			*
	 *==================================================================================*/

	/**
	 * <h4>Delete documents.</h4>
	 *
	 * This method can be used to delete one or more documents selected by the provided
	 * filter, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFilter</b>: The selection criteria.
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
	 * The filter must be provided in the native database format and if no options are
	 * provided, the operation will process all documents in the selection.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Delete options.
	 * @return int					The number of deleted records.
	 *
	 * @uses doDelete()
	 * @uses normaliseOptions()
	 * @see kTOKEN_OPT_MANY
	 *
	 * @example
	 * // Delete first document.<br/>
	 * $count = $collection->Delete( $filter, [ kTOKEN_OPT_MANY => FALSE ] );<br/>
	 * // Delete all selected documents.<br/>
	 * $count = $collection->Delete( $filter );<br/>
	 * // Delete all documents.<br/>
	 * $count = $collection->Delete();
	 */
	public function Delete( $theFilter = NULL, $theOptions = NULL )
	{
		//
		// Normalise options.
		//
		$this->normaliseOptions( kTOKEN_OPT_MANY, TRUE, $theOptions );

		return $this->doDelete( $theFilter, $theOptions );							// ==>

	} // Delete.


	/*===================================================================================
	 *	FindByKey																		*
	 *==================================================================================*/

	/**
	 * <h4>Find by key.</h4>
	 *
	 * This method will return the document that matches the provided key in the current
	 * collection, or <tt>NULL</tt> if the document cannot be matched.
	 *
	 * The method features two parameters:
	 *
	 * <ul>
	 *	<li><b>$theKey</b>: The document key to match.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether the first
	 * 			parameter is a set of keys or a single key:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Provided a set of keys.
	 * 			<li><tt>FALSE</tt>: Provided a single key.
	 * 		 </ul>
	 * 		<li><b>{@link kTOKEN_OPT_FORMAT}</b>: This option determines the result format:
	 * 		 <ul>
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_NATIVE}</tt>: Return the unchanged driver
	 * 				database result.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_STANDARD}</tt>: Return either a scalar or
	 * 				an array of {@link Document} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return either a scalar or
	 * 				an array of document handles (@link ToDocumentHandle()}).
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * By default the method will return {@link Document} instances and consider the first
	 * parameter as a single key.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theKey				The document identifier.
	 * @param array					$theOptions			Delete options.
	 * @return mixed				The found document(s).
	 *
	 * @uses doFindById()
	 * @uses normaliseOptions()
	 * @see kTOKEN_OPT_MANY
	 * @see kTOKEN_OPT_FORMAT
	 */
	public function FindById( $theKey, $theOptions = NULL )
	{
		//
		// Normalise options.
		//
		$this->normaliseOptions(
			kTOKEN_OPT_MANY, FALSE, $theOptions );
		$this->normaliseOptions(
			kTOKEN_OPT_FORMAT, kTOKEN_OPT_FORMAT_STANDARD, $theOptions );

		return $this->doFindById( $theKey, $theOptions );							// ==>

	} // FindById.


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
	 * 		{@link Document} instance, or a native database document.
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
	 * 				an array of {@link Document} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return either a scalar or
	 * 				an array of document handles (@link ToDocumentHandle()}).
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * By default the method will return {@link Document} instances and return the full set
	 * of results.
	 *
	 * It is the responsibility of the caller to ensure the server is connected.
	 *
	 * @param mixed					$theDocument		The example document.
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found records.
	 *
	 * @uses doFindByExample()
	 * @uses normaliseOptions()
	 * @see kTOKEN_OPT_SKIP
	 * @see kTOKEN_OPT_LIMIT
	 * @see kTOKEN_OPT_FORMAT
	 *
	 * @example
	 * // Find first five document.
	 * $iterator = $collection->FindByExample( [ 'color' => 'red', 'city' => 'Rome' ], [ kTOKEN_OPT_SKIP => 0, kTOKEN_OPT_LIMIT => 5 ] );<br/>
	 * // Find all selected documents.<br/>
	 * $iterator = $collection->FindByExample( [ 'color' => 'red', 'city' => 'Rome' ] );<br/>
	 * // Find all documents.<br/>
	 * $iterator = $collection->FindByExample();
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
	 * 				an array of {@link Document} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return either a scalar or
	 * 				an array of document handles (@link ToDocumentHandle()}).
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * By default the method will return {@link Document} instances and return the full set
	 * of results.
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
	 * <h4>Count by example.</h4>
	 *
	 * This method can be used to return the record count matching the provided query.
	 *
	 * This method must be implemented in concrete derived classes.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @return int					The found records count.
	 */
	abstract public function CountByQuery( $theQuery = NULL );


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
	abstract public function MapReduce( $thePipeline, $theOptions = [] );

	
	
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
	abstract protected function collectionNew( $theCollection, $theOptions = [] );
	
	
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
 *						PROTECTED DOCUMENT MANAGEMENT INTERFACE							*
 *																						*
 *======================================================================================*/
	
	
	
	/*===================================================================================
	 *	doInsert																		*
	 *==================================================================================*/
	
	/**
	 * <h4>Insert one or more records.</h4>
	 *
	 * This method should insert the provided record or records, the method expects the
	 * following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The document or documents to be inserted.
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
	 * The record(s) to be inserted must be either {@link Document} instances, or documents
	 * in the native database format.
	 *
	 * The method will return either an array of keys, if you provided a set of documents,
	 * or a single key, if not; the key corresponds to the object ID and is referenced by
	 * the {@link KeyOffset()} document property.
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theDocument		The document(s) to be inserted.
	 * @param array					$theOptions			Insert options.
	 * @return mixed				The document's unique identifier(s).
	 */
	abstract protected function doInsert( $theDocument, array $theOptions );


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
	 *	<li><b>$theCriteria</b>: The modification criteria.
	 *	<li><b>$theFilter</b>: The selection criteria.
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
	 * This method should replace the matching provided record, the method expects the
	 * following parameters:
	 *
	 * <ul>
	 *	<li><b>$theDocument</b>: The replacement document.
	 *	<li><b>$theFilter</b>: The selection criteria.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether to replace
	 * 			the first selected document, or all the selected documents:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Replace all records selected by the filter.
	 * 			<li><tt>FALSE</tt>: Replace the first record selected by the filter.
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param mixed					$theDocument		The replacement document.
	 * @param array					$theOptions			Replace options.
	 * @return int					The number of replaced records.
	 */
	abstract protected function doReplace( $theFilter, $theDocument, array $theOptions );


	/*===================================================================================
	 *	doDelete																		*
	 *==================================================================================*/

	/**
	 * <h4>Delete the first or all records.</h4>
	 *
	 * This method should delete the first or all records matching the provided search
	 * criteria, the method expects the following parameters:
	 *
	 * <ul>
	 *	<li><b>$theFilter</b>: The selection criteria.
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
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theFilter			The selection criteria.
	 * @param array					$theOptions			Delete options.
	 * @return int					The number of deleted records.
	 */
	abstract protected function doDelete( $theFilter, array $theOptions );


	/*===================================================================================
	 *	doFindById																		*
	 *==================================================================================*/

	/**
	 * <h4>Find by ID.</h4>
	 *
	 * This method should return the document that matches the provided key in the current
	 * collection, or <tt>NULL</tt> if the document cannot be matched.
	 *
	 * The method features two parameters:
	 *
	 * <ul>
	 *	<li><b>$theKey</b>: The document key to match.
	 *	<li><b>$theOptions</b>: An array of options:
	 * 	 <ul>
	 * 		<li><b>{@link kTOKEN_OPT_MANY}</b>: This option determines whether the first
	 * 			parameter is a set of keys or a single key:
	 * 		 <ul>
	 * 			<li><tt>TRUE</tt>: Provided an <em>array</em> of keys.
	 * 			<li><tt>FALSE</tt>: Provided a single key.
	 * 		 </ul>
	 * 		<li><b>{@link kTOKEN_OPT_FORMAT}</b>: This option determines the result format:
	 * 		 <ul>
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_NATIVE}</tt>: Return the unchanged driver
	 * 				database result.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_STANDARD}</tt>: Return either a scalar or
	 * 				an array of {@link Document} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return either a scalar or
	 * 				an array of document handles (@link ToDocumentHandle()}).
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theKey				The document identifier.
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found document(s).
	 */
	abstract protected function doFindById( $theKey, array $theOptions );


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
	 *	<li><b>$theDocument</b>: The example document; it must be either an array, a
	 * 		{@link Document} instance, or a native database document.
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
	 * 				an array of {@link Document} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return either a scalar or
	 * 				an array of document handles (@link ToDocumentHandle()}).
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theDocument		The example document.
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found records.
	 */
	abstract protected function doFindByExample( $theDocument, array $theOptions );


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
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_STANDARD}</tt>: Return either a scalar or
	 * 				an array of {@link Document} instances.
	 * 			<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Return either a scalar or
	 * 				an array of document handles (@link ToDocumentHandle()}).
	 * 		 </ul>
	 * 	 </ul>
	 * </ul>
	 *
	 * This method must be implemented by derived concrete classes.
	 *
	 * @param mixed					$theQuery			The selection criteria.
	 * @param array					$theOptions			Find options.
	 * @return mixed				The found records.
	 */
	abstract protected function doFindByQuery( $theQuery, $theOptions );



/*=======================================================================================
 *																						*
 *								PROTECTED CURSOR UTILITIES								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	formatCursor																	*
	 *==================================================================================*/

	/**
	 * <h4>Format cursor.</h4>
	 *
	 * This method can be used to convert a cursor resulting from a query operation in a
	 * format determined by the second parameter of this method:
	 *
	 * <ul>
	 *	<li><tt>{@link kTOKEN_OPT_FORMAT_STANDARD}</tt>: Convert into an array of
	 * 		{@link Document} instances.
	 *	<li><tt>{@link kTOKEN_OPT_FORMAT_HANDLE}</tt>: Convert into an array of document
	 * 		handles (@link ToDocumentHandle()}).
	 * </ul>
	 *
	 * @param mixed			$theCursor	The result cursor.
	 * @param int			$theFormat	The cursor format.
	 * @return array					An array of different format documents.
	 * @throws InvalidArgumentException
	 *
	 * @uses ToDocument()
	 * @uses ToDocumentHandle()
	 * @uses KeyOffset()
	 */
	protected function formatCursor($theCursor, int $theFormat )
	{
		//
		// Init local storage.
		//
		$array = [];

		//
		// Iterate cursor.
		//
		foreach( $theCursor as $document )
		{
			//
			// Convert document.
			//
			switch( $theFormat )
			{
				case kTOKEN_OPT_FORMAT_STANDARD:
					$document = $this->ToDocument( $document );
					$array[ (string) $document[ $this->KeyOffset() ] ] = $document;
					break;

				case kTOKEN_OPT_FORMAT_HANDLE:
					$document = $this->ToDocumentHandle( $document );
					$array[] = $document;
					break;

				default:
					throw new \InvalidArgumentException (
						"Invalid conversion format." );							// !@! ==>
			}
		}

		return $array;																// ==>

	} // cursorToArray.



/*=======================================================================================
 *																						*
 *								PROTECTED GENERIC UTILITIES								*
 *																						*
 *======================================================================================*/



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
	 * @throws InvalidArgumentException
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

	
	
} // class Collection.


?>
