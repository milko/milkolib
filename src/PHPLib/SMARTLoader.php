<?php

/**
 * SMARTLoader.php
 *
 * This file contains the definition of the {@link SMARTLoader} class.
 */

/*=======================================================================================
 *																						*
 *									SMARTLoader.php										*
 *																						*
 *======================================================================================*/

use Milko\PHPLib\Container;
use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Collection;

/**
 * <h4>SMART Survey Loader.</h4>
 *
 * This class extends handles household, mother and child SMART surveys and aggregates them
 * into a single dataset.
 *
 * The class will create a <tt>household</tt>, <tt>mother</tt> and <tt>child</tt> set of
 * collections, normalise their value types, signal eventual duplicates and finally merge
 * the three datasets into a single one.
 *
 * The class is initialised by providing details on the datasets, such as the file path,
 * the labels and data rows, the administrative unit, team, cluster and unit identifier
 * columns, then operations can be performed in this order:
 *
 * <ul>
 * 	<li>Load dataset. This operation will load the data from the survey file into the
 * 		related collection, casting variables to the correct type.
 * 	<li>Check dataset. This operation will verify whether the dataset contains duplicate
 * 		entries, in which case a column will be added to the collection documents
 * 		identifying the duplicates group.
 * 	<li>Aggregate datasets. This operation will merge the three datasets into a single one
 * 		in which the child is the common denominator and the mother and household data will
 * 		be appended to each child document.
 * </ul>
 *
 * This class handles datasets in the <em>Excel</em> format and uses <em>MongoDB</em> as the
 * database engine.
 *
 *	@package	SMART
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		17/06/2016
 */
class SMARTLoader extends Container
{
	/**
	 * <h4>Default client DSN.</h4>
	 *
	 * This constant holds the <i>default client connection data source name</i>.
	 *
	 * @var string
	 */
	const kDSN = 'mongodb://localhost:27017';

	/**
	 * <h4>Default database name.</h4>
	 *
	 * This constant holds the <i>default database name</i>.
	 *
	 * @var string
	 */
	const kNAME_DATABASE = 'SMART';

	/**
	 * <h4>Default survey collection name.</h4>
	 *
	 * This constant holds the <i>default survey collection name</i>.
	 *
	 * @var string
	 */
	const kNAME_SURVEY = 'survey';

	/**
	 * <h4>Default household collection name.</h4>
	 *
	 * This constant holds the <i>default household collection name</i>.
	 *
	 * @var string
	 */
	const kNAME_HOUSEHOLD = 'household';

	/**
	 * <h4>Default mother collection name.</h4>
	 *
	 * This constant holds the <i>default mother collection name</i>.
	 *
	 * @var string
	 */
	const kNAME_MOTHER = 'mother';

	/**
	 * <h4>Default child collection name.</h4>
	 *
	 * This constant holds the <i>default child collection name</i>.
	 *
	 * @var string
	 */
	const kNAME_CHILD = 'child';

	/**
	 * <h4>Dataset record file offset.</h4>
	 *
	 * This constant holds the <i>offset</i> that <i>identifies the dataset file</i>.
	 *
	 * @var string
	 */
	const kOFFSET_FILE = 'file';

	/**
	 * <h4>Dataset object offset.</h4>
	 *
	 * This constant holds the <i>offset</i> that <i>identifies the dataset object</i>.
	 *
	 * @var string
	 */
	const kOFFSET_READER = 'read';

	/**
	 * <h4>Dataset header line offset.</h4>
	 *
	 * This constant holds the <i>offset</i> that <i>contains the dataset header line</i>.
	 *
	 * @var string
	 */
	const kOFFSET_HEADER = 'head';

	/**
	 * <h4>Dataset data line offset.</h4>
	 *
	 * This constant holds the <i>offset</i> that <i>contains the dataset data line</i>.
	 *
	 * @var string
	 */
	const kOFFSET_DATA = 'data';

	/**
	 * <h4>Dataset date variable name.</h4>
	 *
	 * This constant holds the <i>offset</i> that contains the <i>name of the date
	 * variable</i> in the dataset.
	 *
	 * @var string
	 */
	const kOFFSET_DATE = 'date';

	/**
	 * <h4>Dataset location variable name.</h4>
	 *
	 * This constant holds the <i>offset</i> that contains the <i>name of the location
	 * variable</i> in the dataset.
	 *
	 * @var string
	 */
	const kOFFSET_LOCATION = 'where';

	/**
	 * <h4>Dataset team variable name.</h4>
	 *
	 * This constant holds the <i>offset</i> that contains the <i>name of the team
	 * variable</i> in the dataset.
	 *
	 * @var string
	 */
	const kOFFSET_TEAM = 'team';

	/**
	 * <h4>Dataset cluster variable name.</h4>
	 *
	 * This constant holds the <i>offset</i> that contains the <i>name of the cluster
	 * variable</i> in the dataset.
	 *
	 * @var string
	 */
	const kOFFSET_CLUSTER = 'cluster';

	/**
	 * <h4>Dataset data dictionary.</h4>
	 *
	 * This constant holds the <i>offset</i> that contains the <i>dataset data
	 * dictionary</i>, which is an array containing the column name and the variable name.
	 *
	 * @var string
	 */
	const kOFFSET_DDICT = 'ddict';

	/**
	 * <h4>Dataset identifier variable name.</h4>
	 *
	 * This constant holds the <i>offset</i> that contains the <i>name of the identifier
	 * variable</i> in the dataset.
	 *
	 * @var string
	 */
	const kOFFSET_IDENT = 'ident';

	/**
	 * <h4>Dataset household identifier variable name.</h4>
	 *
	 * This constant holds the <i>offset</i> that contains the <i>name of the household
	 * identifier variable</i> in the dataset.
	 *
	 * @var string
	 */
	const kOFFSET_IDENT_HOUSEHOLD = 'id_hh';

	/**
	 * <h4>Dataset mother identifier variable name.</h4>
	 *
	 * This constant holds the <i>offset</i> that contains the <i>name of the mother
	 * identifier variable</i> in the dataset.
	 *
	 * @var string
	 */
	const kOFFSET_IDENT_MOTHER = 'id_mm';

	/**
	 * <h4>Dataset duplicates offset.</h4>
	 *
	 * This constant holds the <i>offset</i> that <i>contains the list of duplicate
	 * dataset records</i>.
	 *
	 * @var string
	 */
	const kOFFSET_DUPS = 'dups';

	/**
	 * <h4>Dataset related offset.</h4>
	 *
	 * This constant holds the <i>offset</i> that <i>contains the list of invalid referenced
	 * records</i>.
	 *
	 * @var string
	 */
	const kOFFSET_RELATED = 'rels';

	/**
	 * <h4>Dataset required variables offset.</h4>
	 *
	 * This constant holds the <i>offset</i> that <i>contains the list of required
	 * dataset variables</i>.
	 *
	 * @var string
	 */
	const kOFFSET_REQUIRED = 'reqs';

	/**
	 * <h4>Dataset status offset.</h4>
	 *
	 * This constant holds the <i>offset</i> that <i>contains the dataset status</i>,
	 * it is an enumeration with the following values:
	 *
	 * <ul>
	 * 	<li><tt>{@link kOFFSET_STATUS_IDLE}</tt>: Idle, the dataset has not yet been loaded
	 * 		in the database.
	 * 	<li><tt>{@link kOFFSET_STATUS_LOADED}</tt>: Loaded and valid, the dataset has been
	 * 		successfully loaded in the database.
	 * 	<li><tt>{@link kOFFSET_STATUS_ERROR}</tt>: Loaded with errors, there were errors
	 * 		while processing the dataset.
	 * </ul>
	 *
	 * @var string
	 */
	const kOFFSET_STATUS = 'stat';

	/**
	 * <h4>Dataset idle status.</h4>
	 *
	 * This constant holds the code corresponding to the idle dataset status, it signifies
	 * that the dataset was defined, but not yet processed.
	 *
	 * @var string
	 */
	const kOFFSET_STATUS_IDLE = 'idle';

	/**
	 * <h4>Dataset loaded status.</h4>
	 *
	 * This constant holds the code corresponding to the loaded dataset status, it signifies
	 * that the dataset was successfully loaded into the database.
	 *
	 * @var string
	 */
	const kOFFSET_STATUS_LOADED = 'load';

	/**
	 * <h4>Dataset duplicates status.</h4>
	 *
	 * This constant holds the code corresponding to an error dataset status implying that
	 * the dataset has duplicate entries.
	 *
	 * @var string
	 */
	const kOFFSET_STATUS_DUPLICATES = 'duplicates';

	/**
	 * <h4>Dataset related status.</h4>
	 *
	 * This constant holds the code corresponding to an error dataset status implying that
	 * the dataset has invalid household and/or mother references.
	 *
	 * @var string
	 */
	const kOFFSET_STATUS_RELATED = 'related';

	/**
	 * <h4>Default date variable.</h4>
	 *
	 * This constant holds the default name for the dataset date.
	 *
	 * @var string
	 */
	const kOFFSET_DEFAULT_DATE = '@date';

	/**
	 * <h4>Default location variable.</h4>
	 *
	 * This constant holds the default name for the dataset location.
	 *
	 * @var string
	 */
	const kOFFSET_DEFAULT_LOCATION = '@location';

	/**
	 * <h4>Default team variable.</h4>
	 *
	 * This constant holds the default name for the dataset team.
	 *
	 * @var string
	 */
	const kOFFSET_DEFAULT_TEAM = '@team';

	/**
	 * <h4>Default cluster variable.</h4>
	 *
	 * This constant holds the default name for the dataset cluster.
	 *
	 * @var string
	 */
	const kOFFSET_DEFAULT_CLUSTER = '@cluster';

	/**
	 * <h4>Default household variable.</h4>
	 *
	 * This constant holds the default name for the household identifier variable.
	 *
	 * @var string
	 */
	const kOFFSET_DEFAULT_HOUSEHOLD = '@household';

	/**
	 * <h4>Default mother variable.</h4>
	 *
	 * This constant holds the default name for the mother identifier variable.
	 *
	 * @var string
	 */
	const kOFFSET_DEFAULT_MOTHER = '@mother';

	/**
	 * <h4>Default identifier variable.</h4>
	 *
	 * This constant holds the default name for the record identifier variable.
	 *
	 * @var string
	 */
	const kOFFSET_DEFAULT_IDENT = '@identifier';

	/**
	 * <h4>Household reference.</h4>
	 *
	 * This constant holds the variable name for the household ID.
	 *
	 * @var string
	 */
	const kOFFSET_HOUSEHOLD_ID = '@id_household';

	/**
	 * <h4>Mother reference.</h4>
	 *
	 * This constant holds the variable name for the mother ID.
	 *
	 * @var string
	 */
	const kOFFSET_MOTHER_ID = '@id_mother';

	/**
	 * <h4>Dataset duplicate cluster variable name.</h4>
	 *
	 * This constant holds the <i>offset</i> that contains the <i>name of the mother
	 * identifier variable</i> in the dataset.
	 *
	 * @var string
	 */
	const kOFFSET_DUPLICATES_CLUSTER = '@duplicates@';

	/**
	 * <h4>Dataset invalid household reference variable name.</h4>
	 *
	 * This constant holds the <i>offset</i> of the column <i>signalling an invalid
	 * household reference</i>.
	 *
	 * @var string
	 */
	const kOFFSET_RELATED_HOUSEHOLD = '@related_household@';

	/**
	 * <h4>Dataset invalid mother reference variable name.</h4>
	 *
	 * This constant holds the <i>offset</i> of the column <i>signalling an invalid
	 * mother reference</i>.
	 *
	 * @var string
	 */
	const kOFFSET_RELATED_MOTHER = '@related_mother@';

	/**
	 * <h4>Client connection.</h4>
	 *
	 * This data member holds the <i>client connection</i>.
	 *
	 * @var MongoDB\Client
	 */
	protected $mClient = NULL;

	/**
	 * <h4>Database connection.</h4>
	 *
	 * This data member holds the <i>database connection</i>.
	 *
	 * @var MongoDB\Database
	 */
	protected $mDatabase = NULL;

	/**
	 * <h4>Survey collection connection.</h4>
	 *
	 * This data member holds the <i>survey collection connection</i>, this will be where
	 * the merged documents will reside.
	 *
	 * @var MongoDB\Collection
	 */
	protected $mSurvey = NULL;

	/**
	 * <h4>Household collection connection.</h4>
	 *
	 * This data member holds the <i>household collection connection</i>.
	 *
	 * @var MongoDB\Collection
	 */
	protected $mHousehold = NULL;

	/**
	 * <h4>Mother collection connection.</h4>
	 *
	 * This data member holds the <i>mother collection connection</i>.
	 *
	 * @var MongoDB\Collection
	 */
	protected $mMother = NULL;

	/**
	 * <h4>Child collection connection.</h4>
	 *
	 * This data member holds the <i>child collection connection</i>.
	 *
	 * @var MongoDB\Collection
	 */
	protected $mChild = NULL;

	/**
	 * <h4>Household dataset record.</h4>
	 *
	 * This data member holds the <i>household dataset record</i>, it is an array that
	 * contains all the information related to the household dataset:
	 *
	 * <ul>
	 * 	<li><tt>{@link kOFFSET_FILE}</tt>: The file reference (SplFileObject).
	 * 	<li><tt>{@link kOFFSET_READER}</tt>: The file PHPExcel reader (PHPExcel_Reader).
	 * 	<li><tt>{@link kOFFSET_HEADER}</tt>: The variables header line (int).
	 * 	<li><tt>{@link kOFFSET_DATA}</tt>: The variables data line (int).
	 * 	<li><tt>{@link kOFFSET_DATE}</tt>: The date variable name (string).
	 * 	<li><tt>{@link kOFFSET_LOCATION}</tt>: The location variable name (string).
	 * 	<li><tt>{@link kOFFSET_TEAM}</tt>: The team variable name (string).
	 * 	<li><tt>{@link kOFFSET_CLUSTER}</tt>: The cluster variable name (string).
	 * 	<li><tt>{@link kOFFSET_IDENT}</tt>: The identifier variable name (string).
	 * 	<li><tt>{@link kOFFSET_STATUS}</tt>: The processing status (string).
	 * 	<li><tt>{@link kOFFSET_REQUIRED}</tt>: List of required variables (array).
	 * 	<li><tt>{@link kOFFSET_DDICT}</tt>: The data dictionary (array).
	 * 	<li><tt>{@link kOFFSET_DUPS}</tt>: The eventual duplicate records (array).
	 * 	<li><tt>{@link kOFFSET_RELATED}</tt>: The eventual invalid related records (array).
	 * </ul>
	 *
	 * @var array
	 */
	protected $mHouseholdInfo = NULL;

	/**
	 * <h4>Mother dataset record.</h4>
	 *
	 * This data member holds the <i>mother dataset record</i>, it is an array that
	 * contains all the information related to the mother dataset:
	 *
	 * <ul>
	 * 	<li><tt>{@link kOFFSET_FILE}</tt>: The file reference (SplFileObject).
	 * 	<li><tt>{@link kOFFSET_READER}</tt>: The file PHPExcel reader (PHPExcel_Reader).
	 * 	<li><tt>{@link kOFFSET_HEADER}</tt>: The variables header line (int).
	 * 	<li><tt>{@link kOFFSET_DATA}</tt>: The variables data line (int).
	 * 	<li><tt>{@link kOFFSET_DATE}</tt>: The date variable name (string).
	 * 	<li><tt>{@link kOFFSET_LOCATION}</tt>: The location variable name (string).
	 * 	<li><tt>{@link kOFFSET_TEAM}</tt>: The team variable name (string).
	 * 	<li><tt>{@link kOFFSET_CLUSTER}</tt>: The cluster variable name (string).
	 * 	<li><tt>{@link kOFFSET_IDENT_HOUSEHOLD}</tt>: The household identifier variable name
	 * 		(string).
	 * 	<li><tt>{@link kOFFSET_IDENT}</tt>: The identifier variable name (string).
	 * 	<li><tt>{@link kOFFSET_STATUS}</tt>: The processing status (string).
	 * 	<li><tt>{@link kOFFSET_REQUIRED}</tt>: List of required variables (array).
	 * 	<li><tt>{@link kOFFSET_DDICT}</tt>: The data dictionary (array).
	 * 	<li><tt>{@link kOFFSET_DUPS}</tt>: The eventual duplicate records (array).
	 * 	<li><tt>{@link kOFFSET_RELATED}</tt>: The eventual invalid related records (array).
	 * </ul>
	 *
	 * @var array
	 */
	protected $mMotherInfo = NULL;

	/**
	 * <h4>Child dataset record.</h4>
	 *
	 * This data member holds the <i>child dataset record</i>, it is an array that
	 * contains all the information related to the child dataset:
	 *
	 * <ul>
	 * 	<li><tt>{@link kOFFSET_FILE}</tt>: The file reference (SplFileObject).
	 * 	<li><tt>{@link kOFFSET_READER}</tt>: The file PHPExcel reader (PHPExcel_Reader).
	 * 	<li><tt>{@link kOFFSET_HEADER}</tt>: The variables header line (int).
	 * 	<li><tt>{@link kOFFSET_DATA}</tt>: The variables data line (int).
	 * 	<li><tt>{@link kOFFSET_DATE}</tt>: The date variable name (string).
	 * 	<li><tt>{@link kOFFSET_LOCATION}</tt>: The location variable name (string).
	 * 	<li><tt>{@link kOFFSET_TEAM}</tt>: The team variable name (string).
	 * 	<li><tt>{@link kOFFSET_CLUSTER}</tt>: The cluster variable name (string).
	 * 	<li><tt>{@link kOFFSET_IDENT_HOUSEHOLD}</tt>: The household identifier variable name
	 * 		(string).
	 * 	<li><tt>{@link kOFFSET_IDENT_MOTHER}</tt>: The mother identifier variable name
	 * 		(string).
	 * 	<li><tt>{@link kOFFSET_IDENT}</tt>: The identifier variable name (string).
	 * 	<li><tt>{@link kOFFSET_STATUS}</tt>: The processing status (string).
	 * 	<li><tt>{@link kOFFSET_REQUIRED}</tt>: List of required variables (array).
	 * 	<li><tt>{@link kOFFSET_DDICT}</tt>: The data dictionary (array).
	 * 	<li><tt>{@link kOFFSET_DUPS}</tt>: The eventual duplicate records (array).
	 * 	<li><tt>{@link kOFFSET_RELATED}</tt>: The eventual invalid related records (array).
	 * </ul>
	 *
	 * @var array
	 */
	protected $mChildInfo = NULL;




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
	 * The object can be instantiated by providing the database connection information:
	 *
	 * <ul>
	 * 	<li><b>$theDSN</b>: The client data source name (defaults to {@link kDSN}.
	 * 	<li><b>$theDatabase</b>: The database name (defaults to {@link kNAME_DATABASE}.
	 * </ul>
	 *
	 * Once instantiated, all other elements can be set via accessor methods.
	 *
	 * @param string				$theDSN				Data source name.
	 * @param string				$theDatabase		Database name.
	 *
	 * @uses Client()
	 * @uses Database()
	 */
	public function __construct( $theDSN = self::kDSN, $theDatabase = self::kNAME_DATABASE )
	{
		//
		// Create client.
		//
		$this->Client( $theDSN );

		//
		// Create database.
		//
		$this->Database( $theDatabase );

		//
		// Set collections.
		//
		$this->Survey( self::kNAME_SURVEY );
		$this->Household( self::kNAME_HOUSEHOLD );
		$this->Mother( self::kNAME_MOTHER );
		$this->Child( self::kNAME_CHILD );

	} // Constructor.



/*=======================================================================================
 *																						*
 *							PUBLIC MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	Client																			*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve client.</h4>
	 *
	 * This method can be used to set or retrieve the database client, if you provide a
	 * string, it will be interpreted as the client data source name, if you provide
	 * <tt>NULL</tt>, the method will return the current value.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return Client
	 * @throws InvalidArgumentException
	 */
	public function Client( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mClient;													// ==>

		//
		// Check parameter.
		//
		if( $theValue === FALSE )
			throw new InvalidArgumentException(
				"Client cannot be deleted." );									// !@! ==>

		return $this->mClient = new Client( (string)$theValue );					// ==>

	} // Client.


	/*===================================================================================
	 *	Database																		*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve database.</h4>
	 *
	 * This method can be used to set or retrieve the database connection, if you provide a
	 * string, it will be interpreted as the database name, if you provide <tt>NULL</tt>,
	 * the method will return the current value.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return Database
	 * @throws InvalidArgumentException
	 */
	public function Database( $theValue = NULL )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $this->mDatabase;												// ==>

		//
		// Check parameter.
		//
		if( $theValue === FALSE )
			throw new InvalidArgumentException(
				"Database cannot be deleted." );								// !@! ==>

		return
			$this->mDatabase
				= $this->Client()->selectDatabase( (string)$theValue );				// ==>

	} // Database.


	/*===================================================================================
	 *	Survey																			*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve survey collection.</h4>
	 *
	 * This method can be used to set or retrieve the survey collection, if you provide a
	 * string, it will be interpreted as the collection name, if you provide <tt>NULL</tt>,
	 * the method will return the current value.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return Collection
	 *
	 * @uses manageCollection()
	 */
	public function Survey( $theValue = NULL )
	{
		return $this->manageCollection( $this->mSurvey, $theValue );				// ==>

	} // Survey.


	/*===================================================================================
	 *	Household																		*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household collection.</h4>
	 *
	 * This method can be used to set or retrieve the household collection, if you provide a
	 * string, it will be interpreted as the collection name, if you provide <tt>NULL</tt>,
	 * the method will return the current value.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return Collection
	 *
	 * @uses manageCollection()
	 */
	public function Household( $theValue = NULL )
	{
		return $this->manageCollection( $this->mHousehold, $theValue );				// ==>

	} // Household.


	/*===================================================================================
	 *	Mother																			*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother collection.</h4>
	 *
	 * This method can be used to set or retrieve the mother collection, if you provide a
	 * string, it will be interpreted as the collection name, if you provide <tt>NULL</tt>,
	 * the method will return the current value.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return Collection
	 *
	 * @uses manageCollection()
	 */
	public function Mother( $theValue = NULL )
	{
		return $this->manageCollection( $this->mMother, $theValue );				// ==>

	} // Mother.


	/*===================================================================================
	 *	Child																			*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child collection.</h4>
	 *
	 * This method can be used to set or retrieve the child collection, if you provide a
	 * string, it will be interpreted as the collection name, if you provide <tt>NULL</tt>,
	 * the method will return the current value.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return Collection
	 *
	 * @uses manageCollection()
	 */
	public function Child( $theValue = NULL )
	{
		return $this->manageCollection( $this->mMother, $theValue );				// ==>

	} // Child.


	/*===================================================================================
	 *	SetHouseholdDataset																*
	 *==================================================================================*/

	/**
	 * <h4>Set household dataset.</h4>
	 *
	 * This method can be used to set the household dataset information, it expects the
	 * following parameters:
	 *
	 * <ul>
	 * 	<li><b>$thePath</b>: The Excel dataset file path.
	 * 	<li><b>$theHeader</b>: The Excel dataset header line number.
	 * 	<li><b>$theData</b>: The Excel dataset first data line number.
	 * </ul>
	 *
	 * The method will return an SplFileObject of the provided dataset.
	 *
	 * @param string				$thePath			Dataset file path.
	 * @param int					$theHeader			Header line number.
	 * @param int					$theData			Data line number.
	 * @param string				$theDate			Date variable name.
	 * @param string				$theLocation		Location variable name.
	 * @param string				$theTeam			Team variable name.
	 * @param string				$theCluster			Cluster variable name.
	 * @param string				$theIdentifier		Unit identifier variable name.
	 * @return array				Dataset record.
	 *
	 * @uses setDataset()
	 */
	public function SetHouseholdDataset( string $thePath,
										 int	$theHeader,
										 int	$theData,
										 string $theDate,
										 string $theLocation,
										 string $theTeam,
										 string $theCluster,
										 string $theIdentifier )
	{
		//
		// Fill record.
		//
		$this->mHouseholdInfo =
			$this->setDataset(
				$thePath, $theHeader, $theData, $theDate, $theLocation,
				$theTeam, $theCluster, $theIdentifier );

		//
		// Set required fields.
		//
		$this->mHouseholdInfo[ self::kOFFSET_REQUIRED ] = [
			$this->mHouseholdInfo[ self::kOFFSET_DATE ],
			$this->mHouseholdInfo[ self::kOFFSET_LOCATION ],
			$this->mHouseholdInfo[ self::kOFFSET_TEAM ],
			$this->mHouseholdInfo[ self::kOFFSET_CLUSTER ],
			$this->mHouseholdInfo[ self::kOFFSET_IDENT ]
		];

		return $this->mHouseholdInfo;												// ==>

	} // SetHouseholdDataset.


	/*===================================================================================
	 *	SetMotherDataset																*
	 *==================================================================================*/

	/**
	 * <h4>Set mother dataset.</h4>
	 *
	 * This method can be used to set the mother dataset information, it expects the
	 * following parameters:
	 *
	 * <ul>
	 * 	<li><b>$thePath</b>: The Excel dataset file path.
	 * 	<li><b>$theHeader</b>: The Excel dataset header line number.
	 * 	<li><b>$theData</b>: The Excel dataset first data line number.
	 * </ul>
	 *
	 * The method will return an SplFileObject of the provided dataset.
	 *
	 * @param string				$thePath			Dataset file path.
	 * @param int					$theHeader			Header line number.
	 * @param int					$theData			Data line number.
	 * @param string				$theDate			Date variable name.
	 * @param string				$theLocation		Location variable name.
	 * @param string				$theTeam			Team variable name.
	 * @param string				$theCluster			Cluster variable name.
	 * @param string				$theIdentifier		Unit identifier variable name.
	 * @param string				$theHousehold		Household identifier variable name.
	 * @return array				Dataset record.
	 *
	 * @uses setDataset()
	 */
	public function SetMotherDataset( string $thePath,
									  int	 $theHeader,
									  int	 $theData,
									  string $theDate,
									  string $theLocation,
									  string $theTeam,
									  string $theCluster,
									  string $theIdentifier,
									  string $theHousehold )
	{
		//
		// Fill record.
		//
		$this->mMotherInfo =
			$this->setDataset(
				$thePath, $theHeader, $theData, $theDate, $theLocation,
				$theTeam, $theCluster, $theIdentifier, $theHousehold );

		//
		// Set required fields.
		//
		$this->mMotherInfo[ self::kOFFSET_REQUIRED ] = [
			$this->mMotherInfo[ self::kOFFSET_DATE ],
			$this->mMotherInfo[ self::kOFFSET_LOCATION ],
			$this->mMotherInfo[ self::kOFFSET_TEAM ],
			$this->mMotherInfo[ self::kOFFSET_CLUSTER ],
			$this->mMotherInfo[ self::kOFFSET_IDENT ],
			$this->mMotherInfo[ self::kOFFSET_IDENT_HOUSEHOLD ]
		];

		return $this->mMotherInfo;													// ==>

	} // SetMotherDataset.


	/*===================================================================================
	 *	SetChildDataset																	*
	 *==================================================================================*/

	/**
	 * <h4>Set child dataset.</h4>
	 *
	 * This method can be used to set the child dataset information, it expects the
	 * following parameters:
	 *
	 * <ul>
	 * 	<li><b>$thePath</b>: The Excel dataset file path.
	 * 	<li><b>$theHeader</b>: The Excel dataset header line number.
	 * 	<li><b>$theData</b>: The Excel dataset first data line number.
	 * </ul>
	 *
	 * The method will return an SplFileObject of the provided dataset.
	 *
	 * @param string				$thePath			Dataset file path.
	 * @param int					$theHeader			Header line number.
	 * @param int					$theData			Data line number.
	 * @param string				$theDate			Date variable name.
	 * @param string				$theLocation		Location variable name.
	 * @param string				$theTeam			Team variable name.
	 * @param string				$theCluster			Cluster variable name.
	 * @param string				$theIdentifier		Unit identifier variable name.
	 * @param string				$theHousehold		Household identifier variable name.
	 * @param string				$theMother			Mother identifier variable name.
	 * @return array				Dataset record.
	 *
	 * @uses setDataset()
	 */
	public function SetChildDataset( string $thePath,
									 int	$theHeader,
									 int	$theData,
									 string $theDate,
									 string $theLocation,
									 string $theTeam,
									 string $theCluster,
									 string $theIdentifier,
									 string $theHousehold,
									 string $theMother )
	{
		//
		// Fill record.
		//
		$this->mChildInfo =
			$this->setDataset(
				$thePath, $theHeader, $theData, $theDate,
				$theLocation, $theTeam, $theCluster,
				$theIdentifier, $theHousehold, $theMother );

		//
		// Set required fields.
		//
		$this->mChildInfo[ self::kOFFSET_REQUIRED ] = [
			$this->mChildInfo[ self::kOFFSET_DATE ],
			$this->mChildInfo[ self::kOFFSET_LOCATION ],
			$this->mChildInfo[ self::kOFFSET_TEAM ],
			$this->mChildInfo[ self::kOFFSET_CLUSTER ],
			$this->mChildInfo[ self::kOFFSET_IDENT ],
			$this->mChildInfo[ self::kOFFSET_IDENT_HOUSEHOLD ],
			$this->mChildInfo[ self::kOFFSET_IDENT_MOTHER ]
		];

		return $this->mChildInfo;													// ==>

	} // SetChildDataset.


	/*===================================================================================
	 *	HouseholdFile																	*
	 *==================================================================================*/

	/**
	 * <h4>Get household file.</h4>
	 *
	 * This method can be used to retrieve the household file path, if the dataset was not
	 * yet defined, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetPath()
	 */
	public function HouseholdFile()
	{
		return $this->getDatasetPath( $this->mHouseholdInfo );						// ==>

	} // HouseholdFile.


	/*===================================================================================
	 *	MotherFile																		*
	 *==================================================================================*/

	/**
	 * <h4>Get mother file.</h4>
	 *
	 * This method can be used to retrieve the mother file path, if the dataset was not
	 * yet defined, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetPath()
	 */
	public function MotherFile()
	{
		return $this->getDatasetPath( $this->mMotherInfo );							// ==>

	} // MotherFile.


	/*===================================================================================
	 *	ChildFile																		*
	 *==================================================================================*/

	/**
	 * <h4>Get child file.</h4>
	 *
	 * This method can be used to retrieve the child file path, if the dataset was not
	 * yet defined, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetPath()
	 */
	public function ChildFile()
	{
		return $this->getDatasetPath( $this->mChildInfo );							// ==>

	} // ChildFile.


	/*===================================================================================
	 *	HouseholdReader																	*
	 *==================================================================================*/

	/**
	 * <h4>Get household reader.</h4>
	 *
	 * This method can be used to retrieve the household reader, if the reader was not yet
	 * set, the method will raise an exception.
	 *
	 * @return PHPExcel_Reader_Abstract
	 *
	 * @uses getDatasetReader()
	 */
	public function HouseholdReader()
	{
		return $this->getDatasetReader( $this->mHouseholdInfo );					// ==>

	} // HouseholdReader.


	/*===================================================================================
	 *	MotherReader																	*
	 *==================================================================================*/

	/**
	 * <h4>Get mother reader.</h4>
	 *
	 * This method can be used to retrieve the mother reader, if the reader was not yet
	 * set, the method will raise an exception.
	 *
	 * @return PHPExcel_Reader_Abstract
	 *
	 * @uses getDatasetReader()
	 */
	public function MotherReader()
	{
		return $this->getDatasetReader( $this->mMotherInfo );						// ==>

	} // MotherReader.


	/*===================================================================================
	 *	ChildReader																		*
	 *==================================================================================*/

	/**
	 * <h4>Get child reader.</h4>
	 *
	 * This method can be used to retrieve the child reader, if the reader was not yet
	 * set, the method will raise an exception.
	 *
	 * @return PHPExcel_Reader_Abstract
	 *
	 * @uses getDatasetReader()
	 */
	public function ChildReader()
	{
		return $this->getDatasetReader( $this->mChildInfo );						// ==>

	} // ChildReader.


	/*===================================================================================
	 *	HouseholdHeaderLine																*
	 *==================================================================================*/

	/**
	 * <h4>Get household header.</h4>
	 *
	 * This method can be used to retrieve the household header line, if the header was not
	 * yet set, the method will raise an exception.
	 *
	 * @return int
	 *
	 * @uses getDatasetHeader()
	 */
	public function HouseholdHeaderLine()
	{
		return $this->getDatasetHeader( $this->mHouseholdInfo );					// ==>

	} // HouseholdHeaderLine.


	/*===================================================================================
	 *	MotherHeaderLine																*
	 *==================================================================================*/

	/**
	 * <h4>Get mother header.</h4>
	 *
	 * This method can be used to retrieve the mother header line, if the header was not yet
	 * set, the method will raise an exception.
	 *
	 * @return int
	 *
	 * @uses getDatasetHeader()
	 */
	public function MotherHeaderLine()
	{
		return $this->getDatasetHeader( $this->mMotherInfo );						// ==>

	} // MotherHeaderLine.


	/*===================================================================================
	 *	ChildHeaderLine																	*
	 *==================================================================================*/

	/**
	 * <h4>Get child header.</h4>
	 *
	 * This method can be used to retrieve the child header line, if the header was not yet
	 * set, the method will raise an exception.
	 *
	 * @return int
	 *
	 * @uses getDatasetHeader()
	 */
	public function ChildHeaderLine()
	{
		return $this->getDatasetHeader( $this->mChildInfo );						// ==>

	} // ChildHeaderLine.


	/*===================================================================================
	 *	HouseholdDataLine																*
	 *==================================================================================*/

	/**
	 * <h4>Get household data.</h4>
	 *
	 * This method can be used to retrieve the household data line, if the data was not
	 * yet set, the method will raise an exception.
	 *
	 * @return int
	 *
	 * @uses getDatasetData()
	 */
	public function HouseholdDataLine()
	{
		return $this->getDatasetData( $this->mHouseholdInfo );						// ==>

	} // HouseholdDataLine.


	/*===================================================================================
	 *	MotherDataLine																	*
	 *==================================================================================*/

	/**
	 * <h4>Get mother data.</h4>
	 *
	 * This method can be used to retrieve the mother data line, if the data was not yet
	 * set, the method will raise an exception.
	 *
	 * @return int
	 *
	 * @uses getDatasetData()
	 */
	public function MotherDataLine()
	{
		return $this->getDatasetData( $this->mMotherInfo );							// ==>

	} // MotherDataLine.


	/*===================================================================================
	 *	ChildDataLine																	*
	 *==================================================================================*/

	/**
	 * <h4>Get child data.</h4>
	 *
	 * This method can be used to retrieve the child data line, if the data was not yet
	 * set, the method will raise an exception.
	 *
	 * @return int
	 *
	 * @uses getDatasetData()
	 */
	public function ChildDataLine()
	{
		return $this->getDatasetData( $this->mChildInfo );							// ==>

	} // ChildDataLine.


	/*===================================================================================
	 *	HouseholdDate																	*
	 *==================================================================================*/

	/**
	 * <h4>Get household date.</h4>
	 *
	 * This method can be used to retrieve the household date, if the dataset was not
	 * yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetDate()
	 */
	public function HouseholdDate()
	{
		return $this->getDatasetDate( $this->mHouseholdInfo );						// ==>

	} // HouseholdDate.


	/*===================================================================================
	 *	MotherDate																		*
	 *==================================================================================*/

	/**
	 * <h4>Get mother date.</h4>
	 *
	 * This method can be used to retrieve the mother date, if the dataset was not yet
	 * set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetDate()
	 */
	public function MotherDate()
	{
		return $this->getDatasetDate( $this->mMotherInfo );							// ==>

	} // MotherDate.


	/*===================================================================================
	 *	ChildDate																		*
	 *==================================================================================*/

	/**
	 * <h4>Get child date.</h4>
	 *
	 * This method can be used to retrieve the child date, if the dataset was not yet
	 * set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetDate()
	 */
	public function ChildDate()
	{
		return $this->getDatasetDate( $this->mChildInfo );							// ==>

	} // ChildDate.


	/*===================================================================================
	 *	HouseholdLocation																*
	 *==================================================================================*/

	/**
	 * <h4>Get household location.</h4>
	 *
	 * This method can be used to retrieve the household location, if the dataset was not
	 * yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetLocation()
	 */
	public function HouseholdLocation()
	{
		return $this->getDatasetLocation( $this->mHouseholdInfo );					// ==>

	} // HouseholdLocation.


	/*===================================================================================
	 *	MotherLocation																	*
	 *==================================================================================*/

	/**
	 * <h4>Get mother location.</h4>
	 *
	 * This method can be used to retrieve the mother location, if the dataset was not yet
	 * set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetLocation()
	 */
	public function MotherLocation()
	{
		return $this->getDatasetLocation( $this->mMotherInfo );						// ==>

	} // MotherLocation.


	/*===================================================================================
	 *	ChildLocation																	*
	 *==================================================================================*/

	/**
	 * <h4>Get child location.</h4>
	 *
	 * This method can be used to retrieve the child location, if the dataset was not yet
	 * set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetLocation()
	 */
	public function ChildLocation()
	{
		return $this->getDatasetLocation( $this->mChildInfo );						// ==>

	} // ChildLocation.


	/*===================================================================================
	 *	HouseholdTeam																	*
	 *==================================================================================*/

	/**
	 * <h4>Get household team.</h4>
	 *
	 * This method can be used to retrieve the household team, if the dataset was not
	 * yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetTeam()
	 */
	public function HouseholdTeam()
	{
		return $this->getDatasetTeam( $this->mHouseholdInfo );						// ==>

	} // HouseholdTeam.


	/*===================================================================================
	 *	MotherTeam																		*
	 *==================================================================================*/

	/**
	 * <h4>Get mother team.</h4>
	 *
	 * This method can be used to retrieve the mother team, if the dataset was not yet
	 * set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetTeam()
	 */
	public function MotherTeam()
	{
		return $this->getDatasetTeam( $this->mMotherInfo );							// ==>

	} // MotherTeam.


	/*===================================================================================
	 *	ChildTeam																		*
	 *==================================================================================*/

	/**
	 * <h4>Get child team.</h4>
	 *
	 * This method can be used to retrieve the child team, if the dataset was not yet
	 * set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetTeam()
	 */
	public function ChildTeam()
	{
		return $this->getDatasetTeam( $this->mChildInfo );							// ==>

	} // ChildTeam.


	/*===================================================================================
	 *	HouseholdCluster																*
	 *==================================================================================*/

	/**
	 * <h4>Get household cluster.</h4>
	 *
	 * This method can be used to retrieve the household cluster, if the dataset was not
	 * yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetCluster()
	 */
	public function HouseholdCluster()
	{
		return $this->getDatasetCluster( $this->mHouseholdInfo );					// ==>

	} // HouseholdCluster.


	/*===================================================================================
	 *	MotherCluster																	*
	 *==================================================================================*/

	/**
	 * <h4>Get mother cluster.</h4>
	 *
	 * This method can be used to retrieve the mother cluster, if the dataset was not yet
	 * set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetCluster()
	 */
	public function MotherCluster()
	{
		return $this->getDatasetCluster( $this->mMotherInfo );						// ==>

	} // MotherCluster.


	/*===================================================================================
	 *	ChildCluster																	*
	 *==================================================================================*/

	/**
	 * <h4>Get child cluster.</h4>
	 *
	 * This method can be used to retrieve the child cluster, if the dataset was not yet
	 * set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetCluster()
	 */
	public function ChildCluster()
	{
		return $this->getDatasetCluster( $this->mChildInfo );						// ==>

	} // ChildCluster.


	/*===================================================================================
	 *	HouseholdIdentifier																*
	 *==================================================================================*/

	/**
	 * <h4>Get household unit identifier.</h4>
	 *
	 * This method can be used to retrieve the household unit identifier, if the dataset was
	 * not yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetIdentifier()
	 */
	public function HouseholdIdentifier()
	{
		return $this->getDatasetIdentifier( $this->mHouseholdInfo );				// ==>

	} // HouseholdIdentifier.


	/*===================================================================================
	 *	MotherIdentifier																*
	 *==================================================================================*/

	/**
	 * <h4>Get mother unit identifier.</h4>
	 *
	 * This method can be used to retrieve the mother unit identifier, if the dataset was
	 * not yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetIdentifier()
	 */
	public function MotherIdentifier()
	{
		return $this->getDatasetIdentifier( $this->mMotherInfo );					// ==>

	} // MotherIdentifier.


	/*===================================================================================
	 *	ChildIdentifier																	*
	 *==================================================================================*/

	/**
	 * <h4>Get child unit identifier.</h4>
	 *
	 * This method can be used to retrieve the child unit identifier, if the dataset was not
	 * yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetIdentifier()
	 */
	public function ChildIdentifier()
	{
		return $this->getDatasetIdentifier( $this->mChildInfo );					// ==>

	} // ChildIdentifier.


	/*===================================================================================
	 *	MotherHouseholdIdentifier														*
	 *==================================================================================*/

	/**
	 * <h4>Get mother household identifier.</h4>
	 *
	 * This method can be used to retrieve the mother household identifier, if the dataset was
	 * not yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetHouseholdIdentifier()
	 */
	public function MotherHouseholdIdentifier()
	{
		return $this->getDatasetHouseholdIdentifier( $this->mMotherInfo );			// ==>

	} // MotherHouseholdIdentifier.


	/*===================================================================================
	 *	ChildHouseholdIdentifier														*
	 *==================================================================================*/

	/**
	 * <h4>Get child household identifier.</h4>
	 *
	 * This method can be used to retrieve the child household identifier, if the dataset was
	 * not yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetHouseholdIdentifier()
	 */
	public function ChildHouseholdIdentifier()
	{
		return $this->getDatasetHouseholdIdentifier( $this->mChildInfo );			// ==>

	} // ChildHouseholdIdentifier.


	/*===================================================================================
	 *	ChildMotherIdentifier															*
	 *==================================================================================*/

	/**
	 * <h4>Get child mother identifier.</h4>
	 *
	 * This method can be used to retrieve the child mother identifier, if the dataset was
	 * not yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetMotherIdentifier()
	 */
	public function ChildMotherIdentifier()
	{
		return $this->getDatasetMotherIdentifier( $this->mChildInfo );				// ==>

	} // ChildMotherIdentifier.


	/*===================================================================================
	 *	HouseholdStatus																	*
	 *==================================================================================*/

	/**
	 * <h4>Get household status.</h4>
	 *
	 * This method can be used to retrieve the household status, if the dataset was not
	 * yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetStatus()
	 */
	public function HouseholdStatus()
	{
		return $this->getDatasetStatus( $this->mHouseholdInfo );					// ==>

	} // HouseholdStatus.


	/*===================================================================================
	 *	MotherStatus																	*
	 *==================================================================================*/

	/**
	 * <h4>Get mother status.</h4>
	 *
	 * This method can be used to retrieve the mother status, if the dataset was not yet
	 * set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetStatus()
	 */
	public function MotherStatus()
	{
		return $this->getDatasetStatus( $this->mMotherInfo );						// ==>

	} // MotherStatus.


	/*===================================================================================
	 *	ChildStatus																		*
	 *==================================================================================*/

	/**
	 * <h4>Get child status.</h4>
	 *
	 * This method can be used to retrieve the child status, if the dataset was not yet
	 * set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetStatus()
	 */
	public function ChildStatus()
	{
		return $this->getDatasetStatus( $this->mChildInfo );						// ==>

	} // ChildStatus.


	/*===================================================================================
	 *	HouseholdRequired																*
	 *==================================================================================*/

	/**
	 * <h4>Get household required fields.</h4>
	 *
	 * This method can be used to retrieve the household required fields, if the dataset was
	 * not yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetRequired()
	 */
	public function HouseholdRequired()
	{
		return $this->getDatasetRequired( $this->mHouseholdInfo );					// ==>

	} // HouseholdRequired.


	/*===================================================================================
	 *	MotherRequired																	*
	 *==================================================================================*/

	/**
	 * <h4>Get mother required fields.</h4>
	 *
	 * This method can be used to retrieve the mother required fields, if the dataset was
	 * not yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetRequired()
	 */
	public function MotherRequired()
	{
		return $this->getDatasetRequired( $this->mMotherInfo );						// ==>

	} // MotherRequired.


	/*===================================================================================
	 *	ChildRequired																	*
	 *==================================================================================*/

	/**
	 * <h4>Get child required fields.</h4>
	 *
	 * This method can be used to retrieve the child required fields, if the dataset was not
	 * yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetRequired()
	 */
	public function ChildRequired()
	{
		return $this->getDatasetRequired( $this->mChildInfo );						// ==>

	} // ChildRequired.


	/*===================================================================================
	 *	HouseholdDictionary																*
	 *==================================================================================*/

	/**
	 * <h4>Get household data dictionary.</h4>
	 *
	 * This method can be used to retrieve the household data dictionary, if the dataset was not
	 * yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetDictionary()
	 */
	public function HouseholdDictionary()
	{
		return $this->getDatasetDictionary( $this->mHouseholdInfo );				// ==>

	} // HouseholdDictionary.


	/*===================================================================================
	 *	MotherDictionary																*
	 *==================================================================================*/

	/**
	 * <h4>Get mother data dictionary.</h4>
	 *
	 * This method can be used to retrieve the mother data dictionary, if the dataset was not yet
	 * set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetDictionary()
	 */
	public function MotherDictionary()
	{
		return $this->getDatasetDictionary( $this->mMotherInfo );					// ==>

	} // MotherDictionary.


	/*===================================================================================
	 *	ChildDictionary																	*
	 *==================================================================================*/

	/**
	 * <h4>Get child data dictionary.</h4>
	 *
	 * This method can be used to retrieve the child data dictionary, if the dataset was not yet
	 * set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetDictionary()
	 */
	public function ChildDictionary()
	{
		return $this->getDatasetDictionary( $this->mChildInfo );					// ==>

	} // ChildDictionary.


	/*===================================================================================
	 *	HouseholdDuplicates																*
	 *==================================================================================*/

	/**
	 * <h4>Get household unit duplicates.</h4>
	 *
	 * This method can be used to retrieve the household unit duplicates, if the dataset was not
	 * yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetDuplicates()
	 */
	public function HouseholdDuplicates()
	{
		return $this->getDatasetDuplicates( $this->mHouseholdInfo );					// ==>

	} // HouseholdDuplicates.


	/*===================================================================================
	 *	MotherDuplicates																*
	 *==================================================================================*/

	/**
	 * <h4>Get mother unit duplicates.</h4>
	 *
	 * This method can be used to retrieve the mother unit duplicates, if the dataset was not yet
	 * set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetDuplicates()
	 */
	public function MotherDuplicates()
	{
		return $this->getDatasetDuplicates( $this->mMotherInfo );						// ==>

	} // MotherDuplicates.


	/*===================================================================================
	 *	ChildDuplicates																	*
	 *==================================================================================*/

	/**
	 * <h4>Get child unit duplicates.</h4>
	 *
	 * This method can be used to retrieve the child unit duplicates, if the dataset was not yet
	 * set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetDuplicates()
	 */
	public function ChildDuplicates()
	{
		return $this->getDatasetDuplicates( $this->mChildInfo );						// ==>

	} // ChildDuplicates.


	/*===================================================================================
	 *	MotherRelated																	*
	 *==================================================================================*/

	/**
	 * <h4>Get mother unit related.</h4>
	 *
	 * This method can be used to retrieve the mother unit related, if the dataset was
	 * not yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetRelated()
	 */
	public function MotherRelated()
	{
		return $this->getDatasetRelated( $this->mMotherInfo );						// ==>

	} // MotherRelated.


	/*===================================================================================
	 *	ChildRelated																	*
	 *==================================================================================*/

	/**
	 * <h4>Get child unit related.</h4>
	 *
	 * This method can be used to retrieve the child unit related, if the dataset was not
	 * yet set, the method will raise an exception.
	 *
	 * @return string
	 *
	 * @uses getDatasetRelated()
	 */
	public function ChildRelated()
	{
		return $this->getDatasetRelated( $this->mChildInfo );						// ==>

	} // ChildRelated.



/*=======================================================================================
 *																						*
 *								PUBLIC OPERATIONS INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	LoadHouseholdDataset															*
	 *==================================================================================*/

	/**
	 * <h4>Load household dataset.</h4>
	 *
	 * This method can be used to load the household dataset into the database, the method
	 * will return the status code:
	 *
	 * <ul>
	 * 	<li><tt>{@link kOFFSET_STATUS_IDLE}</tt>: The dataset is empty.
	 * 	<li><tt>{@link kOFFSET_STATUS_LOADED}</tt>: The dataset was loaded with success.
	 * 	<li><tt>{@link kOFFSET_STATUS_DUPLICATES}</tt>: Found duplicate entries.
	 * </ul>.
	 *
	 * @return string				Status code.
	 * @throws RuntimeException
	 */
	public function LoadHouseholdDataset()
	{
		//
		// Check dataset.
		//
		if( is_array( $this->mHouseholdInfo ) )
		{
			//
			// Load dataset sheet.
			// We assume there is a single worksheet
			//
			$data = $this->HouseholdReader()
				->getActiveSheet()
				->toArray( NULL, TRUE, TRUE, TRUE );

			//
			// Load data in temp collection.
			//
			$count =
				$this->loadDatasetTempCollection(
					$this->mHouseholdInfo,			// Dataset info record.
					self::kNAME_HOUSEHOLD,			// Dataset default name,
					$data							// Dataset array.
				);

			//
			// Skip empty dataset.
			//
			if( $count )
			{
				//
				// Collect data types.
				//
				$this->collectTempCollectionDataTypes(
					$this->mHouseholdInfo,			// Dataset info record.
					self::kNAME_HOUSEHOLD			// Dataset default name.
				);

				//
				// Normalise data types in temp collection.
				//
				$this->normaliseTempCollectionDataTypes(
					$this->mHouseholdInfo,			// Dataset info record.
					self::kNAME_HOUSEHOLD			// Dataset default name.
				);

				//
				// Identify duplicates.
				//
				$status = $this->identifyTempCollectionDuplicates(
					$this->mHouseholdInfo,			// Dataset info record.
					self::kNAME_HOUSEHOLD			// Dataset default name.
				);

				//
				// Handle duplicates.
				//
				if( $status == self::kOFFSET_STATUS_DUPLICATES )
				{
					//
					// Write to collection.
					//
					$this->signalTempCollectionDuplicates(
						$this->mHouseholdInfo,		// Dataset info record.
						self::kNAME_HOUSEHOLD		// Dataset default name.
					);

					//
					// Write to file.
					//
					$this->signalFileDuplicates(
						$this->mHouseholdInfo,		// Dataset info record.
						self::kNAME_HOUSEHOLD		// Dataset default name.
					);

				} // Has duplicates.

				return $this->mHouseholdInfo[ self::kOFFSET_STATUS ];				// ==>

			} // Has data.

			return self::kOFFSET_STATUS_IDLE;										// ==>

		} // Defined household dataset.

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // LoadHouseholdDataset.


	/*===================================================================================
	 *	LoadMotherDataset																*
	 *==================================================================================*/

	/**
	 * <h4>Load mother dataset.</h4>
	 *
	 * This method can be used to load the mother dataset into the database, the method
	 * will return the status code:
	 *
	 * <ul>
	 * 	<li><tt>{@link kOFFSET_STATUS_IDLE}</tt>: The dataset is empty.
	 * 	<li><tt>{@link kOFFSET_STATUS_LOADED}</tt>: The dataset was loaded with success.
	 * 	<li><tt>{@link kOFFSET_STATUS_DUPLICATES}</tt>: Found duplicate entries.
	 * </ul>.
	 *
	 * @return string				Status code.
	 * @throws RuntimeException
	 */
	public function LoadMotherDataset()
	{
		//
		// Check dataset.
		//
		if( is_array( $this->mMotherInfo ) )
		{
			//
			// Load dataset sheet.
			// We assume there is a single worksheet
			//
			$data = $this->MotherReader()
				->getActiveSheet()
				->toArray( NULL, TRUE, TRUE, TRUE );

			//
			// Load data in temp collection.
			//
			$count =
				$this->loadDatasetTempCollection(
					$this->mMotherInfo,				// Dataset info record.
					self::kNAME_MOTHER,				// Dataset default name,
					$data							// Dataset array.
				);

			//
			// Skip empty dataset.
			//
			if( $count )
			{
				//
				// Collect data types.
				//
				$this->collectTempCollectionDataTypes(
					$this->mMotherInfo,				// Dataset info record.
					self::kNAME_MOTHER				// Dataset default name,
				);

				//
				// Normalise data types in temp collection.
				//
				$this->normaliseTempCollectionDataTypes(
					$this->mMotherInfo,				// Dataset info record.
					self::kNAME_MOTHER				// Dataset default name,
				);

				//
				// Identify duplicates.
				//
				$status = $this->identifyTempCollectionDuplicates(
					$this->mMotherInfo,				// Dataset info record.
					self::kNAME_MOTHER				// Dataset default name,
				);

				//
				// Handle duplicates.
				//
				if( $status == self::kOFFSET_STATUS_DUPLICATES )
				{
					//
					// Write to collection.
					//
					$this->signalTempCollectionDuplicates(
						$this->mMotherInfo,			// Dataset info record.
						self::kNAME_MOTHER			// Dataset default name,
					);

					//
					// Write to file.
					//
					$this->signalFileDuplicates(
						$this->mMotherInfo,			// Dataset info record.
						self::kNAME_MOTHER			// Dataset default name,
					);

				} // Has duplicates.

				//
				// Identify related households.
				//
				$status = $this->identifyRelatedHouseholds(
					$this->mMotherInfo,				// Dataset info record.
					self::kNAME_MOTHER				// Dataset default name,
				);

				//
				// Handle missing related households.
				//
				if( $status == self::kOFFSET_STATUS_RELATED )
					$this->signalFileRelatedHouseholds(
						$this->mMotherInfo,			// Dataset info record.
						self::kNAME_MOTHER			// Dataset default name,
					);

				return $this->mMotherInfo[ self::kOFFSET_STATUS ];					// ==>

			} // Has data.

			return self::kOFFSET_STATUS_IDLE;										// ==>

		} // Defined mother dataset.

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // LoadMotherDataset.


	/*===================================================================================
	 *	LoadChildDataset																*
	 *==================================================================================*/

	/**
	 * <h4>Load child dataset.</h4>
	 *
	 * This method can be used to load the child dataset into the database, the method
	 * will return the status code:
	 *
	 * <ul>
	 * 	<li><tt>{@link kOFFSET_STATUS_IDLE}</tt>: The dataset is empty.
	 * 	<li><tt>{@link kOFFSET_STATUS_LOADED}</tt>: The dataset was loaded with success.
	 * 	<li><tt>{@link kOFFSET_STATUS_DUPLICATES}</tt>: Found duplicate entries.
	 * </ul>.
	 *
	 * @return string				Status code.
	 * @throws RuntimeException
	 */
	public function LoadChildDataset()
	{
		//
		// Check dataset.
		//
		if( is_array( $this->mChildInfo ) )
		{
			//
			// Load dataset sheet.
			// We assume there is a single worksheet
			//
			$data = $this->ChildReader()
				->getActiveSheet()
				->toArray( NULL, TRUE, TRUE, TRUE );

			//
			// Load data in temp collection.
			//
			$count =
				$this->loadDatasetTempCollection(
					$this->mChildInfo,				// Dataset info record.
					self::kNAME_CHILD,				// Dataset default name,
					$data							// Dataset array.
				);

			//
			// Skip empty dataset.
			//
			if( $count )
			{
				//
				// Collect data types.
				//
				$this->collectTempCollectionDataTypes(
					$this->mChildInfo,				// Dataset info record.
					self::kNAME_CHILD				// Dataset default name,
				);

				//
				// Normalise data types in temp collection.
				//
				$this->normaliseTempCollectionDataTypes(
					$this->mChildInfo,				// Dataset info record.
					self::kNAME_CHILD				// Dataset default name,
				);

				//
				// Identify duplicates.
				//
				$status = $this->identifyTempCollectionDuplicates(
					$this->mChildInfo,				// Dataset info record.
					self::kNAME_CHILD				// Dataset default name,
				);

				//
				// Handle duplicates.
				//
				if( $status == self::kOFFSET_STATUS_DUPLICATES )
				{
					//
					// Write to collection.
					//
					$this->signalTempCollectionDuplicates(
						$this->mChildInfo,			// Dataset info record.
						self::kNAME_CHILD			// Dataset default name,
					);

					//
					// Write to file.
					//
					$this->signalFileDuplicates(
						$this->mChildInfo,			// Dataset info record.
						self::kNAME_CHILD			// Dataset default name,
					);

				} // Has duplicates.

				//
				// Identify related households.
				//
				$status = $this->identifyRelatedHouseholds(
					$this->mChildInfo,				// Dataset info record.
					self::kNAME_CHILD				// Dataset default name,
				);

				//
				// Handle missing related households.
				//
				if( $status == self::kOFFSET_STATUS_RELATED )
					$this->signalFileRelatedHouseholds(
						$this->mChildInfo,			// Dataset info record.
						self::kNAME_CHILD			// Dataset default name,
					);

				//
				// Identify related mothers.
				//
				$status = $this->identifyRelatedMothers(
					$this->mChildInfo,				// Dataset info record.
					self::kNAME_CHILD				// Dataset default name,
				);

				//
				// Handle missing related mothers.
				//
				if( $status == self::kOFFSET_STATUS_RELATED )
					$this->signalFileRelatedMothers(
						$this->mChildInfo,			// Dataset info record.
						self::kNAME_CHILD			// Dataset default name,
					);

				return $this->mChildInfo[ self::kOFFSET_STATUS ];					// ==>

			} // Has data.

			return self::kOFFSET_STATUS_IDLE;										// ==>

		} // Defined child dataset.

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // LoadChildDataset.



/*=======================================================================================
 *																						*
 *							PROTECTED MEMBER ACCESSOR INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	manageCollection																*
	 *==================================================================================*/

	/**
	 * <h4>Manage a collection.</h4>
	 *
	 * This method can be used by public accessor methods to set or retrieve collections,
	 * including the necessary checks.
	 *
	 * @param Collection		   &$theMember			Reference to collection data member.
	 * @param mixed					$theValue			New value, or operation.
	 * @return Collection
	 * @throws InvalidArgumentException
	 */
	protected function manageCollection( &$theMember, $theValue )
	{
		//
		// Return current value.
		//
		if( $theValue === NULL )
			return $theMember;														// ==>

		//
		// Check parameter.
		//
		if( $theValue === FALSE )
			throw new InvalidArgumentException(
				"Collection cannot be deleted." );								// !@! ==>

		//
		// Check database.
		//
		$database = $this->Database();
		if( ! ($database instanceof Database) )
			throw new InvalidArgumentException(
				"Cannot create collection: " .
				"database not defined." );										// !@! ==>

		return
			$theMember
				= $this->Database()->selectCollection( (string)$theValue );			// ==>

	} // manageCollection.


	/*===================================================================================
	 *	setDataset																		*
	 *==================================================================================*/

	/**
	 * <h4>Set dataset information.</h4>
	 *
	 * This method can be used by public accessor methods to set a dataset information
	 * record, it expects the dataset file path, the header line number and the first data
	 * line number. This information will be compiled and returned into an array structured
	 * as follows:
	 *
	 * <ul>
	 * 	<li><tt>{@link kOFFSET_FILE}</tt>: The file reference (SplFileObject).
	 * 	<li><tt>{@link kOFFSET_READER}</tt>: The file PHPExcel reader (PHPExcel_Reader).
	 * 	<li><tt>{@link kOFFSET_HEADER}</tt>: The variables header line (int).
	 * 	<li><tt>{@link kOFFSET_DATA}</tt>: The variables data line (int).
	 * 	<li><tt>{@link kOFFSET_DATE}</tt>: The date variable name (string).
	 * 	<li><tt>{@link kOFFSET_LOCATION}</tt>: The location variable name (string).
	 * 	<li><tt>{@link kOFFSET_TEAM}</tt>: The team variable name (string).
	 * 	<li><tt>{@link kOFFSET_CLUSTER}</tt>: The cluster variable name (string).
	 * 	<li><tt>{@link kOFFSET_IDENT_HOUSEHOLD}</tt>: The household identifier variable name
	 * 		(string), valid for mother and child datasets.
	 * 	<li><tt>{@link kOFFSET_IDENT_MOTHER}</tt>: The mother identifier variable name
	 * 		(string), valid for child dataset.
	 * 	<li><tt>{@link kOFFSET_IDENT}</tt>: The identifier variable name (string).
	 * 	<li><tt>{@link kOFFSET_STATUS}</tt>: The processing status (string).
	 * 	<li><tt>{@link kOFFSET_DUPS}</tt>: The eventual duplicate records (array).
	 * 	<li><tt>{@link kOFFSET_RELATED}</tt>: The eventual invalid related records (array).
	 * </ul>
	 *
	 * @param string				$thePath			Dataset file path.
	 * @param int					$theHeader			Header line number.
	 * @param int					$theData			Data line number.
	 * @param string				$theDate			Date variable name.
	 * @param string				$theLocation		Location variable name.
	 * @param string				$theTeam			Team variable name.
	 * @param string				$theCluster			Cluster variable name.
	 * @param string				$theIdentifier		Unit identifier variable name.
	 * @param string				$theHousehold		Household identifier variable name.
	 * @param string				$theMother			Mother identifier variable name.
	 * @return array
	 * @throws InvalidArgumentException
	 */
	protected function setDataset( string $thePath,
								   int	  $theHeader,
								   int	  $theData,
								   string $theDate,
								   string $theLocation,
								   string $theTeam,
								   string $theCluster,
								   string $theIdentifier,
								   string $theHousehold = NULL,
								   string $theMother = NULL )
	{
		//
		// Open file in read.
		//
		$file = new SplFileObject( $thePath, "r" );

		//
		// Check file.
		//
		if( (! $file->isFile())
		 || (! $file->isWritable()) )
			throw new InvalidArgumentException(
				"Invalid file reference [$thePath]." );							// !@! ==>

		//
		// Create reader.
		//
		$type = PHPExcel_IOFactory::identify( $thePath );
		$reader = PHPExcel_IOFactory::createReader( $type );
		$reader = $reader->load( $thePath );

		//
		// Init record.
		//
		$record = [
			self::kOFFSET_FILE		=> $file,
			self::kOFFSET_READER	=> $reader,
			self::kOFFSET_HEADER	=> $theHeader,
			self::kOFFSET_DATA		=> $theData,
			self::kOFFSET_DATE		=> $theDate,
			self::kOFFSET_LOCATION	=> $theLocation,
			self::kOFFSET_TEAM		=> $theTeam,
			self::kOFFSET_CLUSTER	=> $theCluster,
			self::kOFFSET_IDENT		=> $theIdentifier,
			self::kOFFSET_STATUS	=> self::kOFFSET_STATUS_IDLE,
			self::kOFFSET_DDICT		=> [],
			self::kOFFSET_DUPS		=> [],
			self::kOFFSET_RELATED	=> []
		];

		//
		// Add other elements.
		//
		if( $theHousehold !== NULL )
			$record[ self::kOFFSET_IDENT_HOUSEHOLD ] = $theHousehold;
		if( $theMother !== NULL )
			$record[ self::kOFFSET_IDENT_MOTHER ] = $theMother;

		return $record;																// ==>

	} // setDataset.


	/*===================================================================================
	 *	getDatasetPath																	*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset path.</h4>
	 *
	 * This method can be used by public accessor methods to retrieve the dataset file
	 * path, if the dataset record was not yet set, the method will raise an exception.
	 *
	 * The method expects a reference to the data member holding the dataset record.
	 *
	 * @param array				   &$theMember			Reference to dataset record.
	 * @return PHPExcel_Reader_Abstract
	 * @throws RuntimeException
	 */
	protected function getDatasetPath( &$theMember )
	{
		//
		// Check dataset record member.
		//
		if( is_array( $theMember ) )
			return $theMember[ self::kOFFSET_FILE ]->getRealPath();					// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetPath.


	/*===================================================================================
	 *	getDatasetReader																*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset reader.</h4>
	 *
	 * This method can be used by public accessor methods to retrieve the dataset reader
	 * object, if the dataset record was not yet set, the method will raise an exception.
	 *
	 * The method expects a reference to the data member holding the dataset record.
	 *
	 * @param array				   &$theMember			Reference to dataset record.
	 * @return PHPExcel_Reader_Abstract
	 * @throws RuntimeException
	 */
	protected function getDatasetReader( &$theMember )
	{
		//
		// Check dataset record member.
		//
		if( is_array( $theMember ) )
			return $theMember[ self::kOFFSET_READER ];								// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetReader.


	/*===================================================================================
	 *	getDatasetHeader																*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset header.</h4>
	 *
	 * This method can be used by public accessor methods to retrieve the dataset header
	 * line number, if the dataset record was not yet set, the method will raise an
	 * exception.
	 *
	 * The method expects a reference to the data member holding the dataset record.
	 *
	 * @param array				   &$theMember			Reference to dataset record.
	 * @return int
	 * @throws RuntimeException
	 */
	protected function getDatasetHeader( &$theMember )
	{
		//
		// Check dataset record member.
		//
		if( is_array( $theMember ) )
			return $theMember[ self::kOFFSET_HEADER ];								// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetHeader.


	/*===================================================================================
	 *	getDatasetData																	*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset data.</h4>
	 *
	 * This method can be used by public accessor methods to retrieve the first dataset data
	 * line number, if the dataset record was not yet set, the method will raise an
	 * exception.
	 *
	 * The method expects a reference to the data member holding the dataset record.
	 *
	 * @param array				   &$theMember			Reference to dataset record.
	 * @return int
	 * @throws RuntimeException
	 */
	protected function getDatasetData( &$theMember )
	{
		//
		// Check dataset record member.
		//
		if( is_array( $theMember ) )
			return $theMember[ self::kOFFSET_DATA ];								// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetData.


	/*===================================================================================
	 *	getDatasetDate																	*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset date.</h4>
	 *
	 * This method can be used by public accessor methods to retrieve the dataset date
	 * variable name, if the dataset record was not yet set, the method will raise an
	 * exception.
	 *
	 * The method expects a reference to the data member holding the dataset record.
	 *
	 * @param array				   &$theMember			Reference to dataset record.
	 * @return string
	 * @throws RuntimeException
	 */
	protected function getDatasetDate( &$theMember )
	{
		//
		// Check dataset record member.
		//
		if( is_array( $theMember ) )
			return $theMember[ self::kOFFSET_DATE ];								// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetDate.


	/*===================================================================================
	 *	getDatasetLocation																*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset location.</h4>
	 *
	 * This method can be used by public accessor methods to retrieve the dataset location
	 * variable name, if the dataset record was not yet set, the method will raise an
	 * exception.
	 *
	 * The method expects a reference to the data member holding the dataset record.
	 *
	 * @param array				   &$theMember			Reference to dataset record.
	 * @return string
	 * @throws RuntimeException
	 */
	protected function getDatasetLocation( &$theMember )
	{
		//
		// Check dataset record member.
		//
		if( is_array( $theMember ) )
			return $theMember[ self::kOFFSET_LOCATION ];							// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetLocation.


	/*===================================================================================
	 *	getDatasetTeam																	*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset team.</h4>
	 *
	 * This method can be used by public accessor methods to retrieve the dataset team
	 * variable name, if the dataset record was not yet set, the method will raise an
	 * exception.
	 *
	 * The method expects a reference to the data member holding the dataset record.
	 *
	 * @param array				   &$theMember			Reference to dataset record.
	 * @return string
	 * @throws RuntimeException
	 */
	protected function getDatasetTeam( &$theMember )
	{
		//
		// Check dataset record member.
		//
		if( is_array( $theMember ) )
			return $theMember[ self::kOFFSET_TEAM ];							// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetTeam.


	/*===================================================================================
	 *	getDatasetCluster																*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset cluster.</h4>
	 *
	 * This method can be used by public accessor methods to retrieve the dataset cluster
	 * variable name, if the dataset record was not yet set, the method will raise an
	 * exception.
	 *
	 * The method expects a reference to the data member holding the dataset record.
	 *
	 * @param array				   &$theMember			Reference to dataset record.
	 * @return string
	 * @throws RuntimeException
	 */
	protected function getDatasetCluster( &$theMember )
	{
		//
		// Check dataset record member.
		//
		if( is_array( $theMember ) )
			return $theMember[ self::kOFFSET_CLUSTER ];								// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetCluster.


	/*===================================================================================
	 *	getDatasetIdentifier															*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset unit identifier.</h4>
	 *
	 * This method can be used by public accessor methods to retrieve the dataset unit
	 * identifier variable name, if the dataset record was not yet set, the method will
	 * raise an exception.
	 *
	 * The method expects a reference to the data member holding the dataset record.
	 *
	 * @param array				   &$theMember			Reference to dataset record.
	 * @return string
	 * @throws RuntimeException
	 */
	protected function getDatasetIdentifier( &$theMember )
	{
		//
		// Check dataset record member.
		//
		if( is_array( $theMember ) )
			return $theMember[ self::kOFFSET_IDENT ];								// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetIdentifier.


	/*===================================================================================
	 *	getDatasetHouseholdIdentifier													*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset household identifier.</h4>
	 *
	 * This method can be used by public accessor methods to retrieve the dataset household
	 * identifier variable name, if the dataset record was not yet set, the method will
	 * raise an exception.
	 *
	 * The method expects a reference to the data member holding the dataset record.
	 *
	 * @param array				   &$theMember			Reference to dataset record.
	 * @return string
	 * @throws RuntimeException
	 */
	protected function getDatasetHouseholdIdentifier( &$theMember )
	{
		//
		// Check dataset record member.
		//
		if( is_array( $theMember ) )
			return $theMember[ self::kOFFSET_IDENT_HOUSEHOLD ];						// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetHouseholdIdentifier.


	/*===================================================================================
	 *	getDatasetMotherIdentifier														*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset mother identifier.</h4>
	 *
	 * This method can be used by public accessor methods to retrieve the dataset mother
	 * identifier variable name, if the dataset record was not yet set, the method will
	 * raise an exception.
	 *
	 * The method expects a reference to the data member holding the dataset record.
	 *
	 * @param array				   &$theMember			Reference to dataset record.
	 * @return string
	 * @throws RuntimeException
	 */
	protected function getDatasetMotherIdentifier( &$theMember )
	{
		//
		// Check dataset record member.
		//
		if( is_array( $theMember ) )
			return $theMember[ self::kOFFSET_IDENT_MOTHER ];						// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetMotherIdentifier.


	/*===================================================================================
	 *	getDatasetStatus																*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset status.</h4>
	 *
	 * This method can be used by public accessor methods to retrieve the dataset status,
	 * if the dataset record was not yet set, the method will raise an exception.
	 *
	 * The method expects a reference to the data member holding the dataset record.
	 *
	 * @param array				   &$theMember			Reference to dataset record.
	 * @return string
	 * @throws RuntimeException
	 */
	protected function getDatasetStatus( &$theMember )
	{
		//
		// Check dataset record member.
		//
		if( is_array( $theMember ) )
			return $theMember[ self::kOFFSET_STATUS ];								// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetStatus.


	/*===================================================================================
	 *	getDatasetRequired																*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset required variables.</h4>
	 *
	 * This method can be used by public accessor methods to retrieve the dataset required
	 * variables list, if the dataset record was not yet set, the method will raise an
	 * exception.
	 *
	 * The method expects a reference to the data member holding the dataset record.
	 *
	 * @param array				   &$theMember			Reference to dataset record.
	 * @return string
	 * @throws RuntimeException
	 */
	protected function getDatasetRequired( &$theMember )
	{
		//
		// Check dataset record member.
		//
		if( is_array( $theMember ) )
			return $theMember[ self::kOFFSET_REQUIRED ];							// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetRequired.


	/*===================================================================================
	 *	getDatasetDictionary															*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset data dictionary.</h4>
	 *
	 * This method can be used by public accessor methods to retrieve the dataset data
	 * dictionary, if the dataset record was not yet set, the method will raise an
	 * exception.
	 *
	 * The method expects a reference to the data member holding the dataset record.
	 *
	 * @param array				   &$theMember			Reference to dataset record.
	 * @return string
	 * @throws RuntimeException
	 */
	protected function getDatasetDictionary( &$theMember )
	{
		//
		// Check dataset record member.
		//
		if( is_array( $theMember ) )
			return $theMember[ self::kOFFSET_DDICT ];								// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetDictionary.


	/*===================================================================================
	 *	getDatasetDuplicates															*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset duplicates.</h4>
	 *
	 * This method can be used by public accessor methods to retrieve the dataset duplicate
	 * entries, if the dataset record was not yet set, the method will raise an exception.
	 *
	 * The method expects a reference to the data member holding the dataset record.
	 *
	 * @param array				   &$theMember			Reference to dataset record.
	 * @return string
	 * @throws RuntimeException
	 */
	protected function getDatasetDuplicates( &$theMember )
	{
		//
		// Check dataset record member.
		//
		if( is_array( $theMember ) )
			return $theMember[ self::kOFFSET_DUPS ];								// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetDuplicates.


	/*===================================================================================
	 *	getDatasetRelated																*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset related.</h4>
	 *
	 * This method can be used by public accessor methods to retrieve the dataset invalid
	 * referenced entries, if the dataset record was not yet set, the method will raise an
	 * exception.
	 *
	 * The method expects a reference to the data member holding the dataset record.
	 *
	 * @param array				   &$theMember			Reference to dataset record.
	 * @return string
	 * @throws RuntimeException
	 */
	protected function getDatasetRelated( &$theMember )
	{
		//
		// Check dataset record member.
		//
		if( is_array( $theMember ) )
			return $theMember[ self::kOFFSET_RELATED ];								// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetRelated.



/*=======================================================================================
 *																						*
 *								PROTECTED OPERATIONS INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	loadDatasetTempCollection														*
	 *==================================================================================*/

	/**
	 * <h4>Load dataset temporary collection.</h4>
	 *
	 * This method can be used to load a temporary collection with data.
	 *
	 * @param array				   &$theInfo			Dataset info record.
	 * @param string				$theName			Collection base name.
	 * @param array					$theData			Dataset data as an array.
	 * @return int					Number of written records.
	 * @throws RuntimeException
	 */
	protected function loadDatasetTempCollection( array &$theInfo,
												  string $theName,
												  array  $theData )
	{
		//
		// Init collection.
		//
		$collection = $this->Database()->selectCollection( "temp_$theName" );
		$collection->drop();

		//
		// Load header row.
		//
		foreach( $theData[ $theInfo[ self::kOFFSET_HEADER ] ] as $key => $value )
			$theInfo[ self::kOFFSET_DDICT ][ (string)$key ] = (string)$value;

		//
		// Iterate rows.
		//
		for(
			$row = $this->HouseholdDataLine();
			$row < (count( $theData ) - ($this->HouseholdDataLine() - 1));
			$row++ )
		{
			//
			// Init local storage.
			//
			$record = [];
			$data = $theData[ $row ];

			//
			// Load data.
			//
			foreach( $theInfo[ self::kOFFSET_DDICT ] as $column => $variable )
			{
				//
				// Trim value.
				//
				$value = trim( $data[ $column ] );

				//
				// Set value.
				//
				if( strlen( $value ) )
					$record[ $variable ] = $value;

			} // Iterating variable names.

			//
			// Save record.
			//
			if( count( $record ) )
			{
				//
				// Check for missing required variables.
				//
				$missing =
					array_diff(
						$theInfo[ self::kOFFSET_REQUIRED ],
						array_intersect(
							$theInfo[ self::kOFFSET_REQUIRED ],
							array_keys( $record )
						)
					);
				if( count( $missing ) )
					throw new RuntimeException(
						"Missing required variable(s) [" .
						implode( ', ', $missing ) .
						"] at line $row." );									// !@! ==>

				//
				// Write row.
				//
				$record[ '_id' ] = $row;
				$collection->insertOne( $record );

			} // Record not empty.

		} // Iterating rows.

		return $collection->count();												// ==>

	} // loadDatasetTempCollection.


	/*===================================================================================
	 *	collectTempCollectionDataTypes													*
	 *==================================================================================*/

	/**
	 * <h4>Collect data types.</h4>
	 *
	 * This method can be used to collect the data types of a temporary collection and
	 * update the data dictionary in the dataset record.
	 *
	 * @param array				   &$theInfo			Dataset info record.
	 * @param string				$theName			Collection base name.
	 * @return array				Data dictionary with types.
	 * @throws RuntimeException
	 */
	protected function collectTempCollectionDataTypes( array &$theInfo,
													   string $theName )
	{
		//
		// Init local storage.
		//
		$types = [];
		$collection = $this->Database()->selectCollection( "temp_$theName" );

		//
		// Load data types.
		//
		foreach( $theInfo[ self::kOFFSET_DDICT ] as $variable )
		{
			//
			// Init default data type.
			//
			$types[ $variable ] = 'int';

			//
			// Handle distinct values.
			//
			$values = $collection->distinct( $variable );
			foreach( $values as $value )
			{
				//
				// Handle number.
				//
				if( is_numeric( $value ) )
				{
					//
					// Check decimal.
					//
					$tmp = explode( '.', (string)$value );
					if( (count( $tmp ) > 1)
					 && ($tmp[ 1 ] != '0') )
						$types[ $variable ] = 'double';

				} // Is numeric.

				//
				// Handle string.
				//
				else
				{
					$types[ $variable ] = 'string';
					break;														// =>

				} // Value is string.

			} // Iterating variable distinct values.

		} // Iterating variable names.

		//
		// Update data dictionary.
		//
		$theInfo[ self::kOFFSET_DDICT ] = $types;

		return $types;																// ==>

	} // collectTempCollectionDataTypes.


	/*===================================================================================
	 *	normaliseTempCollectionDataTypes												*
	 *==================================================================================*/

	/**
	 * <h4>Normalise data types.</h4>
	 *
	 * This method can be used to normalise the data types of a temporary collection.
	 *
	 * @param array				   &$theInfo			Dataset info record.
	 * @param string				$theName			Collection base name.
	 * @throws RuntimeException
	 */
	protected function normaliseTempCollectionDataTypes( array &$theInfo,
														 string $theName )
	{
		//
		// Init local storage.
		//
		$records = [];
		$collection = $this->Database()->selectCollection( "temp_$theName" );

		//
		// Iterate all temp collection documents.
		//
		$cursor = $collection->find();
		foreach( $cursor as $record )
		{
			//
			// Convert to array.
			//
			$record = $record->getArrayCopy();

			//
			// Convert data.
			//
			foreach( $theInfo[ self::kOFFSET_DDICT ] as $variable => $type )
			{
				//
				// Skip missing variables.
				//
				if( array_key_exists( $variable, $record ) )
				{
					//
					// Parse by type.
					//
					switch( $type )
					{
						case 'int':
							$record[ $variable ] = (int)$record[ $variable ];
							break;

						case 'double':
							$record[ $variable ] = (double)$record[ $variable ];
							break;

						case 'string':
							$record[ $variable ] = (string)$record[ $variable ];
							break;
					}
					 
				} // Has variable.
				
			} // Iterating data dictionary.

			//
			// Save record.
			//
			$records[] = $record;
			
		} // Iterating cursor.

		//
		// Clear collection.
		//
		$collection->drop();

		//
		// Write data.
		//
		$collection->insertMany( $records );

	} // normaliseTempCollectionDataTypes.


	/*===================================================================================
	 *	identifyTempCollectionDuplicates												*
	 *==================================================================================*/

	/**
	 * <h4>Identify temp collection duplicates.</h4>
	 *
	 * This method can be used to identify duplicate records, it will return the status
	 * code: {@link kOFFSET_STATUS_LOADED} id there are no duplicates, or
	 * {@link kOFFSET_STATUS_DUPLICATES}.
	 *
	 * @param array				   &$theInfo			Dataset info record.
	 * @param string				$theName			Collection base name.
	 * @return string				Status code.
	 * @throws RuntimeException
	 */
	protected function identifyTempCollectionDuplicates( array &$theInfo,
														 string $theName )
	{
		//
		// Init local storage.
		//
		$pipeline = [];
		$collection = $this->Database()->selectCollection( "temp_$theName" );

		//
		// Init selection group.
		//
		$temp = [
			$theInfo[ self::kOFFSET_LOCATION ] => '$' . $theInfo[ self::kOFFSET_LOCATION ],
			$theInfo[ self::kOFFSET_TEAM ] => '$' . $theInfo[ self::kOFFSET_TEAM ],
			$theInfo[ self::kOFFSET_CLUSTER ] => '$' . $theInfo[ self::kOFFSET_CLUSTER ]
		];

		//
		// Add identifiers to selection group.
		//
		for( $i = 4; $i < count( $theInfo[ self::kOFFSET_REQUIRED ] ); $i++ )
			$temp[ $theInfo[ self::kOFFSET_REQUIRED ][ $i ] ]
				= '$' . $theInfo[ self::kOFFSET_REQUIRED ][ $i ];

		//
		// Add group.
		//
		$pipeline[] = [
			'$group' => [ '_id' => $temp,
				'count' => [ '$sum' => 1 ] ]
		];

		//
		// Add duplicates match.
		//
		$pipeline[] = [
			'$match' => [ 'count' => [ '$gt' => 1 ] ]
		];

		//
		// Aggregate.
		//
		$duplicates =
			iterator_to_array(
				$collection->aggregate( $pipeline, [ 'allowDiskUse' => TRUE ] )
			);

		//
		// Handle duplicates.
		//
		if( count( $duplicates ) )
		{
			//
			// Set status.
			//
			$theInfo[ self::kOFFSET_STATUS ] = self::kOFFSET_STATUS_DUPLICATES;

			//
			// Iterate duplicate groups.
			//
			$duplicate_id = 1;
			foreach( $duplicates as $duplicate )
			{
				//
				// Init duplicates entry.
				//
				$theInfo[ self::kOFFSET_DUPS ][ $duplicate_id ] = [
					'Record identifiers' => $duplicate[ '_id' ]->getArrayCopy(),
					'Duplicate rows' => []
				];

				//
				// Get duplicate group.
				//
				$duplicate = $duplicate->getArrayCopy();
				$duplicate = $duplicate[ '_id' ]->getArrayCopy();

				//
				// Locate duplicates.
				//
				$cursor =
					iterator_to_array(
						$collection->find( $duplicate )
					);
				foreach( $cursor as $document )
					$theInfo[ self::kOFFSET_DUPS ]
					[ $duplicate_id ]
					[ 'Duplicate rows' ]
					[]
						= $document[ '_id' ];

				//
				// Ingrement group identifier.
				//
				$duplicate_id++;

			} // Iterating duplicates.

			return self::kOFFSET_STATUS_DUPLICATES;									// ==>

		} // Has duplicates.

		return
			$theInfo[ self::kOFFSET_STATUS ] = self::kOFFSET_STATUS_LOADED;			// ==>

	} // identifyTempCollectionDuplicates.


	/*===================================================================================
	 *	signalTempCollectionDuplicates													*
	 *==================================================================================*/

	/**
	 * <h4>Signal temp collection duplicates.</h4>
	 *
	 * This method can be used to load duplicate information onto the temporary collection.
	 *
	 * @param array				   &$theInfo			Dataset info record.
	 * @param string				$theName			Collection base name.
	 */
	protected function signalTempCollectionDuplicates( array &$theInfo,
													   string $theName )
	{
		//
		// Check if there are duplicates.
		//
		if( count( $theInfo[ self::kOFFSET_DUPS ] ) )
		{
			//
			// Init local storage.
			//
			$collection = $this->Database()->selectCollection( "temp_$theName" );

			//
			// Iterate duplicates.
			//
			foreach( $theInfo[ self::kOFFSET_DUPS ] as $id => $data )
			{
				//
				// Set update commands.
				//
				$query = [ '_id' => [ '$in' => $data[ 'Duplicate rows' ] ] ];
				$criteria = [ '$set' => [ self::kOFFSET_DUPLICATES_CLUSTER => $id ] ];

				//
				// Update documents.
				//
				$collection->updateMany( $query, $criteria );

			} // Iterating duplicates.

		} // Has duplicates.

	} // signalTempCollectionDuplicates.


	/*===================================================================================
	 *	signalFileDuplicates															*
	 *==================================================================================*/

	/**
	 * <h4>Signal file duplicates.</h4>
	 *
	 * This method can be used to load duplicate information onto the original file.
	 *
	 * @param array				   &$theInfo			Dataset info record.
	 * @param string				$theName			Collection base name.
	 */
	protected function signalFileDuplicates( array &$theInfo,
											 string $theName )
	{
		//
		// Check if there are invalid references.
		//
		if( count( $theInfo[ self::kOFFSET_DUPS ] ) )
		{
			//
			// Get worksheet.
			//
			$worksheet =
				$theInfo[ self::kOFFSET_READER ]
					->getActiveSheet();

			//
			// Get highest row and column.
			//
			$end = $worksheet->getHighestRow();
			$column = PHPExcel_Cell::columnIndexFromString(
				$worksheet->getHighestColumn()
			);

			//
			// Set header.
			//
			$worksheet->setCellValueByColumnAndRow(
				$column,
				$theInfo[ self::kOFFSET_HEADER ],
				self::kOFFSET_DUPLICATES_CLUSTER );

			//
			// Iterate duplicate groups.
			//
			foreach( $theInfo[ self::kOFFSET_DUPS ] as $id => $data )
			{
				//
				// Iterate duplicate rows.
				//
				foreach( $data[ 'Duplicate rows' ] as $row )
					$worksheet->setCellValueByColumnAndRow(
						$column,
						$row,
						$id
					);

			} // Iterating duplicate groups.

			//
			// Write file.
			//
			$writer = PHPExcel_IOFactory::createWriter(
					$theInfo[ self::kOFFSET_READER ], 'Excel2007'
			)->save( $this->getDatasetPath( $theInfo ) );

		} // Has duplicates.

	} // signalFileDuplicates.


	/*===================================================================================
	 *	identifyRelatedHouseholds														*
	 *==================================================================================*/

	/**
	 * <h4>Identify related households.</h4>
	 *
	 * This method can be used to identify related households, it will return the status
	 * code: {@link kOFFSET_STATUS_LOADED} id there are no errors, or
	 * {@link kOFFSET_STATUS_RELATED} if there are.
	 *
	 * @param array				   &$theInfo			Dataset info record.
	 * @param string				$theName			Collection base name.
	 * @return string				Status code.
	 * @throws RuntimeException
	 */
	protected function identifyRelatedHouseholds( array &$theInfo,
												  string $theName )
	{
		//
		// Init local storage.
		//
		$collection = $this->Database()->selectCollection( "temp_$theName" );
		$households = $this->Database()->selectCollection( "temp_".self::kNAME_HOUSEHOLD );
		$default = [ self::kOFFSET_LOCATION, self::kOFFSET_TEAM, self::kOFFSET_CLUSTER ];

		//
		// Init selection group.
		//
		$temp = [];
		foreach( $default as $item )
			$temp[ $theInfo[ $item ] ] = '$' . $theInfo[ $item ];
		$temp[ $theInfo[ self::kOFFSET_IDENT_HOUSEHOLD ] ]
			= '$' . $theInfo[ self::kOFFSET_IDENT_HOUSEHOLD ];

		//
		// Add group.
		//
		$pipeline[] = [
			'$group' => [ '_id' => $temp ]
		];

		//
		// Aggregate.
		//
		$related =
			iterator_to_array(
				$collection->aggregate( $pipeline, [ 'allowDiskUse' => TRUE ] )
			);

		//
		// Handle related households.
		//
		if( count( $related ) )
		{
			//
			// Iterate duplicate groups.
			//
			foreach( $related as $relation )
			{
				//
				// Normalise relation.
				//
				$relation = $relation[ '_id' ]->getArrayCopy();

				//
				// Init query.
				//
				$query = [];
				foreach( $default as $item )
					$query[ $this->mHouseholdInfo[ $item ] ]
						= $relation[ $theInfo[ $item ] ];
				$query[ $this->mHouseholdInfo[ self::kOFFSET_IDENT ] ]
					= $relation[ $theInfo[ self::kOFFSET_IDENT_HOUSEHOLD ] ];

				//
				// Check household.
				//
				$household = $households->findOne( $query );
				if( $household !== NULL )
				{
					//
					// Set update commands.
					//
					$criteria = [
						'$set' => [ self::kOFFSET_HOUSEHOLD_ID => $household[ '_id' ] ]
					];

					//
					// Update documents.
					//
					$collection->updateMany( $relation, $criteria );

				} // Found household.

				//
				// Handle missing household.
				//
				else
				{
					//
					// Set status.
					//
					$theInfo[ self::kOFFSET_STATUS ] = self::kOFFSET_STATUS_RELATED;

					//
					// Add missing info.
					//
					$index = count( $theInfo[ self::kOFFSET_RELATED ] );
					$theInfo[ self::kOFFSET_RELATED ][ $index ]
						= [ 'Household reference' => $relation,
							'Rows' => [] ];

					//
					// Select offending rows.
					//
					$documents = $collection->find( $relation );
					foreach( $documents as $document )
						$theInfo[ self::kOFFSET_RELATED ][ $index ][ 'Rows' ][]
							= $document[ '_id' ];

				} // Missing household.

			} // Iterating related households.

		} // Has related households.

		return $theInfo[ self::kOFFSET_STATUS ];									// ==>

	} // identifyRelatedHouseholds.


	/*===================================================================================
	 *	signalFileRelatedHouseholds														*
	 *==================================================================================*/

	/**
	 * <h4>Signal invalid household references.</h4>
	 *
	 * This method can be used to signal invalid household references onto the original
	 * file.
	 *
	 * @param array				   &$theInfo			Dataset info record.
	 * @param string				$theName			Collection base name.
	 */
	protected function signalFileRelatedHouseholds( array &$theInfo,
													string $theName )
	{
		//
		// Check if there are duplicates.
		//
		if( count( $theInfo[ self::kOFFSET_RELATED ] ) )
		{
			//
			// Get worksheet.
			//
			$worksheet =
				$theInfo[ self::kOFFSET_READER ]
					->getActiveSheet();

			//
			// Get highest row and column.
			//
			$end = $worksheet->getHighestRow();
			$column = PHPExcel_Cell::columnIndexFromString(
				$worksheet->getHighestColumn()
			);

			//
			// Set header.
			//
			$worksheet->setCellValueByColumnAndRow(
				$column,
				$theInfo[ self::kOFFSET_HEADER ],
				self::kOFFSET_RELATED_HOUSEHOLD );

			//
			// Iterate missing related households.
			//
			foreach( $theInfo[ self::kOFFSET_RELATED ] as $data )
			{
				//
				// Iterate offending rows.
				//
				foreach( $data[ 'Rows' ] as $row )
					$worksheet->setCellValueByColumnAndRow(
						$column,
						$row,
						'X'
					);

			} // Iterating duplicate groups.

			//
			// Write file.
			//
			$writer = PHPExcel_IOFactory::createWriter(
				$theInfo[ self::kOFFSET_READER ], 'Excel2007'
			)->save( $this->getDatasetPath( $theInfo ) );

		} // Has duplicates.

	} // signalFileRelatedHouseholds.


	/*===================================================================================
	 *	identifyRelatedMothers															*
	 *==================================================================================*/

	/**
	 * <h4>Identify related mothers.</h4>
	 *
	 * This method can be used to identify related mothers, it will return the status
	 * code: {@link kOFFSET_STATUS_LOADED} id there are no errors, or
	 * {@link kOFFSET_STATUS_RELATED} if there are.
	 *
	 * @param array				   &$theInfo			Dataset info record.
	 * @param string				$theName			Collection base name.
	 * @return string				Status code.
	 * @throws RuntimeException
	 */
	protected function identifyRelatedMothers( array &$theInfo,
											   string $theName )
	{
		//
		// Init local storage.
		//
		$collection = $this->Database()->selectCollection( "temp_$theName" );
		$mothers = $this->Database()->selectCollection( "temp_".self::kNAME_MOTHER );
		$default = [
			self::kOFFSET_LOCATION,
			self::kOFFSET_TEAM,
			self::kOFFSET_CLUSTER,
			self::kOFFSET_IDENT_HOUSEHOLD
		];

		//
		// Init selection group.
		//
		$temp = [];
		foreach( $default as $item )
			$temp[ $theInfo[ $item ] ] = '$' . $theInfo[ $item ];
		$temp[ $theInfo[ self::kOFFSET_IDENT_MOTHER ] ]
			= '$' . $theInfo[ self::kOFFSET_IDENT_MOTHER ];

		//
		// Add group.
		//
		$pipeline[] = [
			'$group' => [ '_id' => $temp ]
		];

		//
		// Aggregate.
		//
		$related =
			iterator_to_array(
				$collection->aggregate( $pipeline, [ 'allowDiskUse' => TRUE ] )
			);

		//
		// Handle related mothers.
		//
		if( count( $related ) )
		{
			//
			// Iterate duplicate groups.
			//
			foreach( $related as $relation )
			{
				//
				// Normalise relation.
				//
				$relation = $relation[ '_id' ]->getArrayCopy();

				//
				// Init query.
				//
				$query = [];
				foreach( $default as $item )
					$query[ $this->mMotherInfo[ $item ] ]
						= $relation[ $theInfo[ $item ] ];
				$query[ $this->mMotherInfo[ self::kOFFSET_IDENT ] ]
					= $relation[ $theInfo[ self::kOFFSET_IDENT_MOTHER ] ];

				//
				// Check household.
				//
				$mother = $mothers->findOne( $query );
				if( $mother !== NULL )
				{
					//
					// Set update commands.
					//
					$criteria = [
						'$set' => [ self::kOFFSET_MOTHER_ID => $mother[ '_id' ] ]
					];

					//
					// Update documents.
					//
					$collection->updateMany( $relation, $criteria );

				} // Found household.

				//
				// Handle missing mother.
				//
				else
				{
					//
					// Set status.
					//
					$theInfo[ self::kOFFSET_STATUS ] = self::kOFFSET_STATUS_RELATED;

					//
					// Add missing info.
					//
					$index = count( $theInfo[ self::kOFFSET_RELATED ] );
					$theInfo[ self::kOFFSET_RELATED ][ $index ]
						= [ 'Mother reference' => $relation,
							'Rows' => [] ];

					//
					// Select offending rows.
					//
					$documents = $collection->find( $relation );
					foreach( $documents as $document )
						$theInfo[ self::kOFFSET_RELATED ][ $index ][ 'Rows' ][]
							= $document[ '_id' ];

				} // Missing mother.

			} // Iterating related mothers.

		} // Has related mother.

		return $theInfo[ self::kOFFSET_STATUS ];									// ==>

	} // identifyRelatedMothers.


	/*===================================================================================
	 *	signalFileRelatedMothers														*
	 *==================================================================================*/

	/**
	 * <h4>Signal invalid mother references.</h4>
	 *
	 * This method can be used to signal invalid mother references onto the original
	 * file.
	 *
	 * @param array				   &$theInfo			Dataset info record.
	 * @param string				$theName			Collection base name.
	 */
	protected function signalFileRelatedMothers( array &$theInfo,
												 string $theName )
	{
		//
		// Check if there are invalid references.
		//
		if( count( $theInfo[ self::kOFFSET_RELATED ] ) )
		{
			//
			// Get worksheet.
			//
			$worksheet =
				$theInfo[ self::kOFFSET_READER ]
					->getActiveSheet();

			//
			// Get highest row and column.
			//
			$end = $worksheet->getHighestRow();
			$column = PHPExcel_Cell::columnIndexFromString(
				$worksheet->getHighestColumn()
			);

			//
			// Set header.
			//
			$worksheet->setCellValueByColumnAndRow(
				$column,
				$theInfo[ self::kOFFSET_HEADER ],
				self::kOFFSET_RELATED_MOTHER );

			//
			// Iterate missing related households.
			//
			foreach( $theInfo[ self::kOFFSET_RELATED ] as $data )
			{
				//
				// Iterate offending rows.
				//
				foreach( $data[ 'Rows' ] as $row )
					$worksheet->setCellValueByColumnAndRow(
						$column,
						$row,
						'X'
					);

			} // Iterating duplicate groups.

			//
			// Write file.
			//
			$writer = PHPExcel_IOFactory::createWriter(
				$theInfo[ self::kOFFSET_READER ], 'Excel2007'
			)->save( $this->getDatasetPath( $theInfo ) );

		} // Has duplicates.

	} // signalFileRelatedMothers.




} // class SMARTLoader.


?>
