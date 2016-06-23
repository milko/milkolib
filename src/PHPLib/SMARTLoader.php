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
	 * This constant holds the <em>default client connection data source name</em>.
	 *
	 * @var string
	 */
	const kNAME_DSN = 'mongodb://localhost:27017';

	/**
	 * <h4>Default database name.</h4>
	 *
	 * This constant holds the <em>default database name</em>.
	 *
	 * @var string
	 */
	const kNAME_DATABASE = 'SMART';

	/**
	 * <h4>Default survey collection name.</h4>
	 *
	 * This constant holds the <em>default survey collection name</em>.
	 *
	 * @var string
	 */
	const kNAME_SURVEY = 'survey';

	/**
	 * <h4>Default household collection name.</h4>
	 *
	 * This constant holds the <em>default household collection name</em>.
	 *
	 * @var string
	 */
	const kNAME_HOUSEHOLD = 'household';

	/**
	 * <h4>Default mother collection name.</h4>
	 *
	 * This constant holds the <em>default mother collection name</em>.
	 *
	 * @var string
	 */
	const kNAME_MOTHER = 'mother';

	/**
	 * <h4>Default child collection name.</h4>
	 *
	 * This constant holds the <em>default child collection name</em>.
	 *
	 * @var string
	 */
	const kNAME_CHILD = 'child';

	/**
	 * <h4>Default data dictionary collection name.</h4>
	 *
	 * This constant holds the <em>default data dictionary collection name</em>.
	 *
	 * @var string
	 */
	const kNAME_DDICT = 'ddict';

	/**
	 * <h4>Dataset file path.</h4>
	 *
	 * This constant holds the <em>offset</em> that <em>identifies the dataset file
	 * path</em>.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_FILE = 'DATASET_PATH';

	/**
	 * <h4>Dataset header line.</h4>
	 *
	 * This constant holds the <em>offset</em> that <em>contains the dataset header line
	 * number</em>.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_HEADER = 'DATASET_HEADER_LINE';

	/**
	 * <h4>Dataset data line.</h4>
	 *
	 * This constant holds the <em>offset</em> that <em>contains the dataset data line
	 * number</em>.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_DATA = 'DATASET_DATA_LINE';

	/**
	 * <h4>Dataset survey date variable name.</h4>
	 *
	 * This constant holds the <em>offset</em> that contains the <em>name of the survey date
	 * variable</em> in the dataset.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_DATE = 'DATASET_DATE_VARIABLE';

	/**
	 * <h4>Dataset location variable name.</h4>
	 *
	 * This constant holds the <em>offset</em> that contains the <em>name of the variable
	 * holding the location number</em> in the dataset.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_LOCATION = 'DATASET_LOCATION_VARIABLE';

	/**
	 * <h4>Dataset team variable name.</h4>
	 *
	 * This constant holds the <em>offset</em> that contains the <em>name of the variable
	 * holding the survey team number</em> in the dataset.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_TEAM = 'DATASET_TEAM_VARIABLE';

	/**
	 * <h4>Dataset cluster variable name.</h4>
	 *
	 * This constant holds the <em>offset</em> that contains the <em>name of the variable
	 * holding the cluster number</em> in the dataset.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_CLUSTER = 'DATASET_CLUSTER_VARIABLE';

	/**
	 * <h4>Dataset identifier variable name.</h4>
	 *
	 * This constant holds the <em>offset</em> that contains the <em>name of the variable
	 * holding the unit identifier number</em> in the dataset, this for children it would be
	 * the child number, for mothers the mother number and for households the household
	 * number.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_IDENTIFIER = 'DATASET_IDENTIFIER_VARIABLE';

	/**
	 * <h4>Dataset household identifier variable name.</h4>
	 *
	 * This constant holds the <em>offset</em> that contains the <em>name of the variable
	 * holding the household number</em> in the dataset, this variable corresponds to the
	 * household number in mother and child datasets.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_HOUSEHOLD = 'DATASET_HOUSEHOLD_VARIABLE';

	/**
	 * <h4>Dataset mother identifier variable name.</h4>
	 *
	 * This constant holds the <em>offset</em> that contains the <em>name of the variable
	 * holding the mother number</em> in the dataset, this variable corresponds to the
	 * mother number in child datasets.
	 *
	 * @var string
	 */
	const kDATASET_OFFSET_MOTHER = 'DATASET_MOTHER_VARIABLE';

	/**
	 * <h4>Survey date offset.</h4>
	 *
	 * This constant holds the <em>default offset</em> for the <em>survey date</em> in
	 * collections.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_DATE = '@SURVEY_DATE';

	/**
	 * <h4>Survey location offset.</h4>
	 *
	 * This constant holds the <em>default offset</em> for the <em>survey location</em> in
	 * collections.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_LOCATION = '@SURVEY_LOCATION';

	/**
	 * <h4>Team number offset.</h4>
	 *
	 * This constant holds the <em>default offset</em> for the <em>team number</em> in
	 * collections.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_TEAM = '@SURVEY_TEAM';

	/**
	 * <h4>Cluster number offset.</h4>
	 *
	 * This constant holds the <em>default offset</em> for the <em>cluster number</em> in
	 * collections.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_CLUSTER = '@SURVEY_CLUSTER';

	/**
	 * <h4>Household number offset.</h4>
	 *
	 * This constant holds the <em>default offset</em> for the <em>household number</em> in
	 * collections.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_HOUSEHOLD = '@SURVEY_HOUSEHOLD';

	/**
	 * <h4>Mother number offset.</h4>
	 *
	 * This constant holds the <em>default offset</em> for the <em>mother number</em> in
	 * collections.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_MOTHER = '@SURVEY_MOTHER';

	/**
	 * <h4>Unit identifier offset.</h4>
	 *
	 * This constant holds the <em>default offset</em> for the <em>identifier number</em> in
	 * collections, this corresponds to the child, mother and household numbers in their
	 * respective datasets.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_IDENTIFIER = '@SURVEY_UNIT';

	/**
	 * <h4>Household reference.</h4>
	 *
	 * This constant holds the <em>variable name</em> for the <em>household unique ID</em>.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_HOUSEHOLD_ID = '@SURVEY_HOUSEHOLD_ID';

	/**
	 * <h4>Mother reference.</h4>
	 *
	 * This constant holds the <em>variable name</em> for the <em>mother unique ID</em>.
	 *
	 * @var string
	 */
	const kCOLLECTION_OFFSET_MOTHER_ID = '@SURVEY_MOTHER_ID';

	/**
	 * <h4>Duplicates column name.</h4>
	 *
	 * This constant holds the <em>column offset</em> in the dataset file that contains the
	 * <em>duplicate entry flag</em>.
	 *
	 * @var string
	 */
	const kFILE_OFFSET_DUPLICATES = '@DUPLICATE@';

	/**
	 * <h4>Invalid household reference column name.</h4>
	 *
	 * This constant holds the <em>column offset</em> in the dataset file that contains the
	 * <em>invalid household reference flag</em>.
	 *
	 * @var string
	 */
	const kFILE_OFFSET_HOUSEHOLD_REF = '@INVALID_HOUSEHOLD@';

	/**
	 * <h4>Invalid mother reference column name.</h4>
	 *
	 * This constant holds the <em>column offset</em> in the dataset file that contains the
	 * <em>invalid mother reference flag</em>.
	 *
	 * @var string
	 */
	const kFILE_OFFSET_MOTHER_REF = '@INVALID_MOTHER@';

	/**
	 * <h4>Data dictionary child identifier.</h4>
	 *
	 * This constant holds the <em>unique identifier</em> of the <em>child data dictionary
	 * record</em>.
	 *
	 * @var string
	 */
	const kDDICT_CHILD_ID = 'CHILD';

	/**
	 * <h4>Data dictionary mother identifier.</h4>
	 *
	 * This constant holds the <em>unique identifier</em> of the <em>mother data dictionary
	 * record</em>.
	 *
	 * @var string
	 */
	const kDDICT_MOTHER_ID = 'MOTHER';

	/**
	 * <h4>Data dictionary household identifier.</h4>
	 *
	 * This constant holds the <em>unique identifier</em> of the <em>household data
	 * dictionary record</em>.
	 *
	 * @var string
	 */
	const kDDICT_HOUSEHOLD_ID = 'HOUSEHOLD';

	/**
	 * <h4>Data dictionary status.</h4>
	 *
	 * This constant holds the <em>dataset status</em> which indicates whether the dataset
	 * was loaded, processed or if it has errors:
	 *
	 * <ul>
	 * 	<li><tt>{@link kDDICT_STATUS_IDLE}</tt>: Idle, the dataset has not yet been
	 * 		declared.
	 * 	<li><tt>{@link kDDICT_STATUS_LOADED}</tt>: Loaded, the dataset has been loaded in
	 * 		the database.
	 * 	<li><tt>{@link kDDICT_STATUS_CHECKED_DUPS}</tt>: Checked for duplicates, the dataset
	 * 		was verified for duplicate entries.
	 * 	<li><tt>{@link kDDICT_STATUS_CHECKED_REFS}</tt>: Checked for invalid references, the
	 * 		dataset was verified for nvalid references.
	 * 	<li><tt>{@link kDDICT_STATUS_STATS}</tt>: Loaded statistics, statistic information
	 * 		was added to the dataset.
	 * 	<li><tt>{@link kDDICT_STATUS_VALID}</tt>: Validated, the dataset was validated and
	 * 		loaded into the final collection.
	 * 	<li><tt>{@link kDDICT_STATUS_COLUMNS}</tt>: Duplicate columns, the dataset has
	 * 		duplicate columns and is not valid.
	 * 	<li><tt>{@link kDDICT_STATUS_DUPLICATES}</tt>: Duplicate entries, the dataset has
	 * 		duplicate entries and is not valid.
	 * 	<li><tt>{@link kDDICT_STATUS_REFERENCES}</tt>: invalid references, the dataset has
	 * 		invalid references and is not valid.
	 * </ul>
	 *
	 * @var string
	 */
	const kDDICT_STATUS = 'status';

	/**
	 * <h4>Data dictionary dataset fields.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>list of dataset offsets and types</em>, it is an array structured as follows:
	 *
	 * <ul>
	 * 	<li><em>index</em>: The array index holds the dataset header value corresponding to
	 * 		the field, the value is an array structured as follows:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_FIELD_KIND}</tt>: The data kind inferred when the dataset
	 * 			was loaded.
	 * 		<li><tt>{@link kDDICT_FIELD_TYPE}</tt>: The data type determined by the user.
	 * 		<li><tt>{@link kDDICT_FIELD_NAME}</tt>: The standard field name used in the
	 * 			final processed dataset.
	 * 	 </ul>
	 * </ul>
	 *
	 * @var string
	 */
	const kDDICT_FIELDS = 'fields';

	/**
	 * <h4>Data dictionary field kind.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>field data kind</em>. This enumerated value indicates the general data type
	 * of the field and it is set by this class when the dataset is loaded:
	 *
	 * <ul>
	 * 	<li><tt>{@link kDDICT_TYPE_STRING</tt>: Any non numeric value will imply this kind.
	 * 	<li><tt>{@link kDDICT_TYPE_DOUBLE</tt>: Any floating point number with a decimal
	 * 		other than <tt>0</tt> will imply this type.
	 * 	<li><tt>{@link kDDICT_TYPE_INTEGER</tt>: If the set of values is all numeric and
	 * 		does not have a floating point, it implies that all values are of integer type.
	 * </ul>
	 *
	 * @var string
	 */
	const kDDICT_FIELD_KIND = 'kind';

	/**
	 * <h4>Data dictionary field type.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>field data type</em>. This enumerated value indicates the specific data type
	 * of the field and is a user determined value:
	 *
	 * <ul>
	 * 	<li><tt>{@link kDDICT_TYPE_STRING</tt>: String.
	 * 	<li><tt>{@link kDDICT_TYPE_DATE</tt>: Date in <tt>YYYY-MM-DD</tt> format.
	 * 	<li><tt>{@link kDDICT_TYPE_INTEGER</tt>: Integer.
	 * 	<li><tt>{@link kDDICT_TYPE_DOUBLE</tt>: Floating point number, double by default.
	 * </ul>
	 *
	 * @var string
	 */
	const kDDICT_FIELD_TYPE = 'type';

	/**
	 * <h4>Data dictionary field name.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>field default name</em>. This value represents the default or standard field
	 * name that will be used in the final processed datasets.
	 *
	 * @var string
	 */
	const kDDICT_FIELD_NAME = 'name';

	/**
	 * <h4>String type.</h4>
	 *
	 * This constant represents a string data type.
	 *
	 * @var string
	 */
	const kDDICT_TYPE_STRING = 'string';

	/**
	 * <h4>Integer type.</h4>
	 *
	 * This constant represents a integer data type.
	 *
	 * @var string
	 */
	const kDDICT_TYPE_INTEGER = 'int';

	/**
	 * <h4>Number kind.</h4>
	 *
	 * This constant represents a number kind, this data type is set when the dataset is
	 * loaded and represents a set of floating point values which do not have decimal
	 * numbers other than <tt>0</tt>: this means that the value may be set to an integer, if
	 * needed.
	 *
	 * @var string
	 */
	const kDDICT_TYPE_NUMBER = 'number';

	/**
	 * <h4>Double type.</h4>
	 *
	 * This constant represents a double floating point data type.
	 *
	 * @var string
	 */
	const kDDICT_TYPE_DOUBLE = 'double';

	/**
	 * <h4>Date type.</h4>
	 *
	 * This constant represents a date type, dates will be stored in the <tt>YYYY-MM-DD</tt>
	 * format.
	 *
	 * @var string
	 */
	const kDDICT_TYPE_DATE = 'date';

	/**
	 * <h4>Dataset idle status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>idle status</em>, it
	 * signifies that the dataset was not yet declared.
	 *
	 * @var int
	 */
	const kDDICT_STATUS_IDLE = 0x00000000;

	/**
	 * <h4>Dataset loaded status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>loaded status</em>, it
	 * signifies that the dataset was loaded from the file to the collection.
	 *
	 * @var int
	 */
	const kDDICT_STATUS_LOADED = 0x00000001;

	/**
	 * <h4>Dataset checked duplicates status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>duplicates checked
	 * status</em>, it signifies that the dataset has been checked for duplicate entries.
	 *
	 * @var int
	 */
	const kDDICT_STATUS_CHECKED_DUPS = 0x00000002;

	/**
	 * <h4>Dataset checked references status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>references checked
	 * status</em>, it signifies that the dataset has been checked for invalid references.
	 *
	 * @var int
	 */
	const kDDICT_STATUS_CHECKED_REFS = 0x00000004;

	/**
	 * <h4>Dataset processed stats status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>stats processed
	 * status</em>, it signifies that the dataset holds statistical information.
	 *
	 * @var int
	 */
	const kDDICT_STATUS_STATS = 0x00000008;

	/**
	 * <h4>Dataset finalised status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>finalised status</em>,
	 * it signifies that the dataset has been validated and written to the final collection.
	 *
	 * @var int
	 */
	const kDDICT_STATUS_VALID = 0x00000010;

	/**
	 * <h4>Dataset has duplicate fields status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>duplicate fields
	 * status</em>, it signifies that the dataset has duplicate columns.
	 *
	 * @var int
	 */
	const kDDICT_STATUS_COLUMNS = 0x00000020;

	/**
	 * <h4>Dataset has duplicates status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>duplicate entries
	 * status</em>, it signifies that the dataset has duplicate entries.
	 *
	 * @var int
	 */
	const kDDICT_STATUS_DUPLICATES = 0x00000040;

	/**
	 * <h4>Dataset has invalid references status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>invalid references
	 * status</em>, it signifies that the dataset has invalid references.
	 *
	 * @var int
	 */
	const kDDICT_STATUS_REFERENCES = 0x00000080;

	/**
	 * <h4>Client connection.</h4>
	 *
	 * This data member holds the <em>client connection</em>.
	 *
	 * @var MongoDB\Client
	 */
	protected $mClient = NULL;

	/**
	 * <h4>Database connection.</h4>
	 *
	 * This data member holds the <em>database connection</em>.
	 *
	 * @var MongoDB\Database
	 */
	protected $mDatabase = NULL;

	/**
	 * <h4>Survey collection connection.</h4>
	 *
	 * This data member holds the <em>survey collection connection</em>, this will be where
	 * the merged documents will reside.
	 *
	 * @var MongoDB\Collection
	 */
	protected $mSurvey = NULL;

	/**
	 * <h4>Household collection connection.</h4>
	 *
	 * This data member holds the <em>household collection connection</em>.
	 *
	 * @var MongoDB\Collection
	 */
	protected $mHousehold = NULL;

	/**
	 * <h4>Mother collection connection.</h4>
	 *
	 * This data member holds the <em>mother collection connection</em>.
	 *
	 * @var MongoDB\Collection
	 */
	protected $mMother = NULL;

	/**
	 * <h4>Child collection connection.</h4>
	 *
	 * This data member holds the <em>child collection connection</em>.
	 *
	 * @var MongoDB\Collection
	 */
	protected $mChild = NULL;

	/**
	 * <h4>Data dictionary collection connection.</h4>
	 *
	 * This data member holds the <em>data dictionary collection connection</em>.
	 *
	 * @var MongoDB\Collection
	 */
	protected $mDDICT = NULL;

	/**
	 * <h4>Data dictionary record.</h4>
	 *
	 * This data member holds the <em>data dictionary record</em>, it is an array that
	 * contains all the information related to household, mother and child datasets.
	 *
	 * The array is structured as follows:
	 *
	 * <ul>
	 * 	<li><em>Unit</em>: This element is an array containing the unit record, the array
	 * 		key identifies the unit:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_CHILD_ID</tt>: Child dataset.
	 * 		<li><tt>{@link kDDICT_MOTHER_ID</tt>: Mother dataset.
	 * 		<li><tt>{@link kDDICT_HOUSEHOLD_ID</tt>: Household dataset.
	 * 	 </ul>
	 * 		Each element is an array containing the following items:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_STATUS</tt>: Dataset <em>status</em>.
	 * 		<li><tt>{@link kDDICT_FIELDS</tt>: Dataset <em>field information</em>.
	 * 		<li><tt>{@link kDATASET_OFFSET_FILE</tt>: Dataset <em>file path</em>.
	 * 		<li><tt>{@link kDATASET_OFFSET_HEADER</tt>: Dataset <em>header row number</em>.
	 * 		<li><tt>{@link kDATASET_OFFSET_DATA</tt>: Dataset <em>data row number</em>.
	 * 		<li><tt>{@link kDATASET_OFFSET_DATE</tt>: <em>Survey date</em> header value.
	 * 		<li><tt>{@link kDATASET_OFFSET_LOCATION</tt>: <em>Survey location number</em>
	 * 			header value.
	 * 		<li><tt>{@link kDATASET_OFFSET_TEAM</tt>: <em>Survey team number</em> header
	 * 			value.
	 * 		<li><tt>{@link kDATASET_OFFSET_CLUSTER</tt>: <em>Survey cluster number</em>
	 * 			header value.
	 * 		<li><tt>{@link kDATASET_OFFSET_IDENTIFIER</tt>: <em>Unit number</em> header
	 * 			value, this corresponds to the household number in the household dataset and
	 * 			the same for mother and child datasets.
	 * 		<li><tt>{@link kDATASET_OFFSET_HOUSEHOLD_ID</tt>: <em>Household number</em>
	 * 			header value in mother and child datasets.
	 * 		<li><tt>{@link kDATASET_OFFSET_MOTHER_ID</tt>: <em>Mother number</em> header
	 * 			value in child dataset.
	 * 	 </ul>
	 * </ul>
	 *
	 * This information is stored in the {@link kNAME_DDICT} collection, it is loaded from
	 * the database when the object is instantiated and stored when the object is
	 * desctructed.
	 *
	 * @var array
	 */
	protected $mDDICTInfo = NULL;




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
	 * Once instantiated, the method will attempt to load the data dictionary from the
	 * database, if it is not found, the method will initialise it.
	 *
	 * @param string				$theDSN				Data source name.
	 * @param string				$theDatabase		Database name.
	 *
	 * @uses Client()
	 * @uses Database()
	 */
	public function __construct( $theDSN = self::kNAME_DSN,
								 $theDatabase = self::kNAME_DATABASE )
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
		$this->Dictionary( self::kNAME_DDICT );
		$this->Survey( self::kNAME_SURVEY );
		$this->Household( self::kNAME_HOUSEHOLD );
		$this->Mother( self::kNAME_MOTHER );
		$this->Child( self::kNAME_CHILD );

		//
		// Initialise data dictionary.
		//
		$this->InitDictionary();

	} // Constructor.


	/*===================================================================================
	 *	__destruct																		*
	 *==================================================================================*/

	/**
	 * <h4>Destruct class.</h4>
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
	public function __destruct()
	{
		//
		// Save data dictionary.
		//
		$this->SaveDictionary();

	} // Destructor.



/*=======================================================================================
 *																						*
 *						PUBLIC DATABASE MEMBER ACCESSOR INTERFACE						*
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
	 *	Dictionary																		*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve data dictionary collection.</h4>
	 *
	 * This method can be used to set or retrieve the data dictionary collection, if you
	 * provide a string, it will be interpreted as the collection name, if you provide
	 * <tt>NULL</tt>, the method will return the current value.
	 *
	 * @param mixed					$theValue			New value, or operation.
	 * @return Collection
	 *
	 * @uses manageCollection()
	 */
	public function Dictionary( $theValue = NULL )
	{
		return $this->manageCollection( $this->mDDICT, $theValue );					// ==>

	} // Dictionary.


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
		return $this->manageCollection( $this->mChild, $theValue );					// ==>

	} // Child.



/*=======================================================================================
 *																						*
 *								PUBLIC INITIALISATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	InitDictionary																	*
	 *==================================================================================*/

	/**
	 * <h4>Initialise data dictionary.</h4>
	 *
	 * The duty of this method is to set the {@link mDDICTInfo} data member with the data
	 * dictionary, it will first attempt to load it from the database, if the data
	 * dictionary collection is empty, the method will initialise and set the dictionary.
	 *
	 * If the dictionary data member is already set, not <tt>NULL</tt>, the method will
	 * return <tt>NULL</tt>; if the dictionary was either loaded or initialised, the method
	 * will return <tt>TRUE</tt>.
	 *
	 * Any error will trigger an exception.
	 *
	 * @uses setDataset()
	 */
	public function InitDictionary()
	{
		//
		// Check data dictionary.
		//
		if( ! is_array( $this->mDDICTInfo ) )
		{
			//
			// Init local storage.
			//
			$datasets = [
				self::kDDICT_CHILD_ID,
				self::kDDICT_MOTHER_ID,
				self::kDDICT_HOUSEHOLD_ID
			];

			//
			// Initialise data dictionary.
			//
			foreach( $datasets as $dataset )
			{
				$document = $this->mDDICT->findOne( [ '_id' => $dataset ] );
				$this->mDDICTInfo[ $dataset ]
					= ( $document === NULL )
					? $this->newDataDictionary( $dataset )
					: $document->getArrayCopy();
			}

			return TRUE;															// ==>

		} // Data member not set.

		return NULL;																// ==>

	} // InitDictionary.


	/*===================================================================================
	 *	SaveDictionary																	*
	 *==================================================================================*/

	/**
	 * <h4>Save data dictionary.</h4>
	 *
	 * The duty of this method is to store the {@link mDDICTInfo} data member into the
	 * {@link kNAME_DDICT} collection.
	 */
	public function SaveDictionary()
	{
		//
		// Check data member.
		//
		if( is_array( $this->mDDICTInfo ) )
		{
			//
			// Insert dictionary.
			//
			if( ! $this->mDDICT->count() )
			{
				foreach( $this->mDDICTInfo as $dataset => $dictionary )
					$this->mDDICT->insertOne( $dictionary );
			}

			//
			// Update dictionary.
			//
			else
			{
				foreach( $this->mDDICTInfo as $dataset => $dictionary )
					$this->mDDICT->replaceOne( [ '_id' => $dataset ], $dictionary );
			}
		}

	} // SaveDictionary.



/*=======================================================================================
 *																						*
 *						PUBLIC DICTIONARY MEMBER ACCESSOR INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	ChildDataset																	*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child dataset.</h4>
	 *
	 * This method can be used to manage the child dataset file, it expects a single
	 * parameter that represents the new dataset file path, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current dataset path.
	 * 	<li><i>string</i>: The file path to set a new file.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return string				Dataset file path.
	 *
	 * @uses datasetPath()
	 */
	public function ChildDataset( $theValue = NULL )
	{
		return $this->datasetPath( self::kDDICT_CHILD_ID, $theValue );				// ==>

	} // ChildDataset.


	/*===================================================================================
	 *	MotherDataset																	*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother dataset.</h4>
	 *
	 * This method can be used to manage the mother dataset file, it expects a single
	 * parameter that represents the new dataset file path, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current dataset path.
	 * 	<li><i>string</i>: The file path to set a new file.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return string				Dataset file path.
	 *
	 * @uses datasetPath()
	 */
	public function MotherDataset( $theValue = NULL )
	{
		return $this->datasetPath( self::kDDICT_MOTHER_ID, $theValue );				// ==>

	} // MotherDataset.


	/*===================================================================================
	 *	HouseholdDataset																*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household dataset.</h4>
	 *
	 * This method can be used to manage the household dataset file, it expects a single
	 * parameter that represents the new dataset file path, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current dataset path.
	 * 	<li><i>string</i>: The file path to set a new file.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return string				Dataset file path.
	 *
	 * @uses datasetPath()
	 */
	public function HouseholdDataset( $theValue = NULL )
	{
		return $this->datasetPath( self::kDDICT_HOUSEHOLD_ID, $theValue );			// ==>

	} // HouseholdDataset.


	/*===================================================================================
	 *	ChildDatasetReader																*
	 *==================================================================================*/

	/**
	 * <h4>Get child file reader.</h4>
	 *
	 * This method can be used to retrieve the child dataset Excel reader.
	 *
	 * @return PHPExcel_Reader_Abstract
	 *
	 * @uses getDatasetReader()
	 */
	public function ChildDatasetReader()
	{
		return $this->getDatasetReader( self::kDDICT_CHILD_ID );					// ==>

	} // ChildDatasetReader.


	/*===================================================================================
	 *	MotherDatasetReader																*
	 *==================================================================================*/

	/**
	 * <h4>Get mother file reader.</h4>
	 *
	 * This method can be used to retrieve the mother dataset Excel reader.
	 *
	 * @return PHPExcel_Reader_Abstract
	 *
	 * @uses getDatasetReader()
	 */
	public function MotherDatasetReader()
	{
		return $this->getDatasetReader( self::kDDICT_MOTHER_ID );					// ==>

	} // MotherDatasetReader.


	/*===================================================================================
	 *	HouseholdDatasetReader															*
	 *==================================================================================*/

	/**
	 * <h4>Get household file reader.</h4>
	 *
	 * This method can be used to retrieve the household dataset Excel reader.
	 *
	 * @return PHPExcel_Reader_Abstract
	 *
	 * @uses getDatasetReader()
	 */
	public function HouseholdDatasetReader()
	{
		return $this->getDatasetReader( self::kDDICT_HOUSEHOLD_ID );					// ==>

	} // HouseholdDatasetReader.


	/*===================================================================================
	 *	ChildDatasetHeaderRow																*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child dataset header row.</h4>
	 *
	 * This method can be used to manage the child dataset header row number, it expects a
	 * single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current header row number.
	 * 	<li><i>integer</i>: The new header row number.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return int					Dataset header row number.
	 *
	 * @uses datasetHeaderRow()
	 */
	public function ChildDatasetHeaderRow( $theValue = NULL )
	{
		return $this->datasetHeaderRow( self::kDDICT_CHILD_ID, $theValue );			// ==>

	} // ChildDatasetHeaderRow.


	/*===================================================================================
	 *	MotherDatasetHeaderRow															*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother dataset header row.</h4>
	 *
	 * This method can be used to manage the mother dataset header row number, it expects a
	 * single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current header row number.
	 * 	<li><i>integer</i>: The new header row number.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return int					Dataset header row number.
	 *
	 * @uses datasetHeaderRow()
	 */
	public function MotherDatasetHeaderRow( $theValue = NULL )
	{
		return $this->datasetHeaderRow( self::kDDICT_MOTHER_ID, $theValue );		// ==>

	} // MotherDatasetHeaderRow.


	/*===================================================================================
	 *	HouseholdDatasetHeaderRow														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household dataset header row.</h4>
	 *
	 * This method can be used to manage the household dataset header row number, it expects
	 * a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current header row number.
	 * 	<li><i>integer</i>: The new header row number.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return int					Dataset header row number.
	 *
	 * @uses datasetHeaderRow()
	 */
	public function HouseholdDatasetHeaderRow( $theValue = NULL )
	{
		return $this->datasetHeaderRow( self::kDDICT_HOUSEHOLD_ID, $theValue );		// ==>

	} // HouseholdDatasetHeaderRow.


	/*===================================================================================
	 *	ChildDatasetDataRow																*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child dataset data row.</h4>
	 *
	 * This method can be used to manage the child dataset data row number, it expects a
	 * single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current data row number.
	 * 	<li><i>integer</i>: The new data row number.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return int					Dataset data row number.
	 *
	 * @uses datasetDataRow()
	 */
	public function ChildDatasetDataRow( $theValue = NULL )
	{
		return $this->datasetDataRow( self::kDDICT_CHILD_ID, $theValue );			// ==>

	} // ChildDatasetDataRow.


	/*===================================================================================
	 *	MotherDatasetDataRow															*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother dataset data row.</h4>
	 *
	 * This method can be used to manage the mother dataset data row number, it expects a
	 * single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current data row number.
	 * 	<li><i>integer</i>: The new data row number.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return int					Dataset data row number.
	 *
	 * @uses datasetDataRow()
	 */
	public function MotherDatasetDataRow( $theValue = NULL )
	{
		return $this->datasetDataRow( self::kDDICT_MOTHER_ID, $theValue );			// ==>

	} // MotherDatasetDataRow.


	/*===================================================================================
	 *	HouseholdDatasetDataRow															*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household dataset data row.</h4>
	 *
	 * This method can be used to manage the household dataset data row number, it expects
	 * a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current data row number.
	 * 	<li><i>integer</i>: The new data row number.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			Dataset file path.
	 * @return int					Dataset data row number.
	 *
	 * @uses datasetDataRow()
	 */
	public function HouseholdDatasetDataRow( $theValue = NULL )
	{
		return $this->datasetDataRow( self::kDDICT_HOUSEHOLD_ID, $theValue );		// ==>

	} // HouseholdDatasetDataRow.


	/*===================================================================================
	 *	ChildDatasetDateOffset															*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child survey date offset.</h4>
	 *
	 * This method can be used to manage the child survey date offset, it expects a
	 * single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey date offset.
	 *
	 * @uses datasetOffset()
	 */
	public function ChildDatasetDateOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_CHILD_ID, self::kDATASET_OFFSET_DATE, $theValue );			// ==>

	} // ChildDatasetDateOffset.


	/*===================================================================================
	 *	MotherDatasetDateOffset															*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother survey date offset.</h4>
	 *
	 * This method can be used to manage the mother survey date offset, it expects a
	 * single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey date offset.
	 *
	 * @uses datasetOffset()
	 */
	public function MotherDatasetDateOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_MOTHER_ID, self::kDATASET_OFFSET_DATE, $theValue );		// ==>

	} // MotherDatasetDateOffset.


	/*===================================================================================
	 *	HouseholdDatasetDateOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household survey date offset.</h4>
	 *
	 * This method can be used to manage the household survey date offset, it expects a
	 * single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey date offset.
	 *
	 * @uses datasetOffset()
	 */
	public function HouseholdDatasetDateOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_HOUSEHOLD_ID, self::kDATASET_OFFSET_DATE, $theValue );		// ==>

	} // HouseholdDatasetDateOffset.


	/*===================================================================================
	 *	ChildDatasetLocationOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child survey location offset.</h4>
	 *
	 * This method can be used to manage the child survey location number offset, it expects
	 * a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey location number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function ChildDatasetLocationOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_CHILD_ID, self::kDATASET_OFFSET_LOCATION, $theValue );		// ==>

	} // ChildDatasetLocationOffset.


	/*===================================================================================
	 *	MotherDatasetLocationOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother survey location offset.</h4>
	 *
	 * This method can be used to manage the mother survey location number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey location number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function MotherDatasetLocationOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_MOTHER_ID, self::kDATASET_OFFSET_LOCATION, $theValue );	// ==>

	} // MotherDatasetLocationOffset.


	/*===================================================================================
	 *	HouseholdDatasetLocationOffset													*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household survey location offset.</h4>
	 *
	 * This method can be used to manage the household survey location number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey location number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function HouseholdDatasetLocationOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_HOUSEHOLD_ID, self::kDATASET_OFFSET_LOCATION, $theValue );	// ==>

	} // HouseholdDatasetLocationOffset.


	/*===================================================================================
	 *	ChildDatasetTeamOffset															*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child survey team offset.</h4>
	 *
	 * This method can be used to manage the child survey team number offset, it expects
	 * a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey team number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function ChildDatasetTeamOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_CHILD_ID, self::kDATASET_OFFSET_TEAM, $theValue );			// ==>

	} // ChildDatasetTeamOffset.


	/*===================================================================================
	 *	MotherDatasetTeamOffset															*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother survey team offset.</h4>
	 *
	 * This method can be used to manage the mother survey team number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey team number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function MotherDatasetTeamOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_MOTHER_ID, self::kDATASET_OFFSET_TEAM, $theValue );		// ==>

	} // MotherDatasetTeamOffset.


	/*===================================================================================
	 *	HouseholdDatasetTeamOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household survey team offset.</h4>
	 *
	 * This method can be used to manage the household survey team number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey team number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function HouseholdDatasetTeamOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_HOUSEHOLD_ID, self::kDATASET_OFFSET_TEAM, $theValue );		// ==>

	} // HouseholdDatasetTeamOffset.


	/*===================================================================================
	 *	ChildDatasetClusterOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child survey cluster offset.</h4>
	 *
	 * This method can be used to manage the child survey cluster number offset, it expects
	 * a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey cluster number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function ChildDatasetClusterOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_CHILD_ID, self::kDATASET_OFFSET_CLUSTER, $theValue );		// ==>

	} // ChildDatasetClusterOffset.


	/*===================================================================================
	 *	MotherDatasetClusterOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother survey cluster offset.</h4>
	 *
	 * This method can be used to manage the mother survey cluster number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey cluster number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function MotherDatasetClusterOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_MOTHER_ID, self::kDATASET_OFFSET_CLUSTER, $theValue );		// ==>

	} // MotherDatasetClusterOffset.


	/*===================================================================================
	 *	HouseholdDatasetClusterOffset													*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household survey cluster offset.</h4>
	 *
	 * This method can be used to manage the household survey cluster number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey cluster number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function HouseholdDatasetClusterOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_HOUSEHOLD_ID, self::kDATASET_OFFSET_CLUSTER, $theValue );	// ==>

	} // HouseholdDatasetClusterOffset.


	/*===================================================================================
	 *	ChildDatasetIdentifierOffset													*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child survey identifier offset.</h4>
	 *
	 * This method can be used to manage the child survey identifier number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey identifier number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function ChildDatasetIdentifierOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_CHILD_ID, self::kDATASET_OFFSET_IDENTIFIER, $theValue );	// ==>

	} // ChildDatasetIdentifierOffset.


	/*===================================================================================
	 *	MotherDatasetIdentifierOffset													*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother survey identifier offset.</h4>
	 *
	 * This method can be used to manage the mother survey identifier number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey identifier number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function MotherDatasetIdentifierOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_MOTHER_ID, self::kDATASET_OFFSET_IDENTIFIER, $theValue );	// ==>

	} // MotherDatasetIdentifierOffset.


	/*===================================================================================
	 *	HouseholdDatasetIdentifierOffset												*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household survey identifier offset.</h4>
	 *
	 * This method can be used to manage the household survey identifier number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey identifier number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function HouseholdDatasetIdentifierOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_HOUSEHOLD_ID,
			self::kDATASET_OFFSET_IDENTIFIER,
			$theValue );															// ==>

	} // HouseholdDatasetIdentifierOffset.


	/*===================================================================================
	 *	ChildDatasetHouseholdOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child survey household offset.</h4>
	 *
	 * This method can be used to manage the child survey household number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey household number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function ChildDatasetHouseholdOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_CHILD_ID, self::kDATASET_OFFSET_HOUSEHOLD, $theValue );	// ==>

	} // ChildDatasetHouseholdOffset.


	/*===================================================================================
	 *	MotherDatasetHouseholdOffset													*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother survey household offset.</h4>
	 *
	 * This method can be used to manage the mother survey household number offset, it
	 * expects a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey household number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function MotherDatasetHouseholdOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_MOTHER_ID, self::kDATASET_OFFSET_HOUSEHOLD, $theValue );	// ==>

	} // MotherDatasetHouseholdOffset.


	/*===================================================================================
	 *	ChildDatasetMotherOffset														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child survey mother offset.</h4>
	 *
	 * This method can be used to manage the child survey mother number offset, it expects
	 * a single parameter that represents the new value, or the operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>string</i>: Set new value.
	 * </ul>
	 *
	 * @param string|NULL			$theValue			New value or operation.
	 * @return int					Dataset survey mother number offset.
	 *
	 * @uses datasetOffset()
	 */
	public function ChildDatasetMotherOffset( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_CHILD_ID, self::kDATASET_OFFSET_MOTHER, $theValue );		// ==>

	} // ChildDatasetMotherOffset.



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
				if( $status == self::kDDICT_STATUS_DUPLICATES )
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

				//
				// Write to final collection.
				//
				if( $this->mHouseholdInfo[ self::kDDICT_STATUS ] == self::kDDICT_STATUS_LOADED )
					$this->loadFinalHouseholdCollection();

				return $this->mHouseholdInfo[ self::kDDICT_STATUS ];				// ==>

			} // Has data.

			return self::kDDICT_STATUS_IDLE;										// ==>

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
				if( $status == self::kDDICT_STATUS_DUPLICATES )
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
				if( $status == self::kDDICT_STATUS_REFERENCES )
					$this->signalFileRelatedHouseholds(
						$this->mMotherInfo,			// Dataset info record.
						self::kNAME_MOTHER			// Dataset default name,
					);

				//
				// Write to final collection.
				//
				if( $this->mMotherInfo[ self::kDDICT_STATUS ] == self::kDDICT_STATUS_LOADED )
					$this->loadFinalMotherCollection();

				return $this->mMotherInfo[ self::kDDICT_STATUS ];					// ==>

			} // Has data.

			return self::kDDICT_STATUS_IDLE;										// ==>

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
				if( $status == self::kDDICT_STATUS_DUPLICATES )
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
				if( $status == self::kDDICT_STATUS_REFERENCES )
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
				if( $status == self::kDDICT_STATUS_REFERENCES )
					$this->signalFileRelatedMothers(
						$this->mChildInfo,			// Dataset info record.
						self::kNAME_CHILD			// Dataset default name,
					);

				//
				// Write to final collection.
				//
				if( $this->mMotherInfo[ self::kDDICT_STATUS ] == self::kDDICT_STATUS_LOADED )
					$this->loadFinalChildCollection();

				return $this->mChildInfo[ self::kDDICT_STATUS ];					// ==>

			} // Has data.

			return self::kDDICT_STATUS_IDLE;										// ==>

		} // Defined child dataset.

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // LoadChildDataset.


	/*===================================================================================
	 *	CreateSurveyCollection															*
	 *==================================================================================*/

	/**
	 * <h4>Merge survey collection.</h4>
	 *
	 * This method can be used to create the merged survey collection.
	 *
	 * The method expects all final collections to be present and not empty, if any error
	 * occurs, the method will raise an exception.
	 *
	 * @throws RuntimeException
	 */
	public function CreateSurveyCollection()
	{
		//
		// Check status.
		//
		if( ($this->mChildInfo[ self::kDDICT_STATUS ] == self::kDDICT_STATUS_LOADED)
		 && ($this->mMotherInfo[ self::kDDICT_STATUS ] == self::kDDICT_STATUS_LOADED)
		 && ($this->mHouseholdInfo[ self::kDDICT_STATUS ] == self::kDDICT_STATUS_LOADED) )
		{
			//
			// Check child collections.
			//
			if( $this->mChild->count() )
			{
				//
				// Check mother collections.
				//
				if( $this->mMother->count() )
				{
					//
					// Check household collections.
					//
					if( $this->mHousehold->count() )
					{
						//
						// Clear temporary collections.
						//
						$this->Database()
							->selectCollection( "temp_" . self::kNAME_CHILD )->drop();
						$this->Database()
							->selectCollection( "temp_" . self::kNAME_MOTHER )->drop();
						$this->Database()
							->selectCollection( "temp_" . self::kNAME_HOUSEHOLD )->drop();

						//
						// Merge datasets.
						//
						$this->loadFinalSurveyCollection();

				} // Household collection not empty.

				else
					throw new RuntimeException(
						"Household dataset is empty." );						// !@! ==>

				} // Mother collection not empty.

				else
					throw new RuntimeException(
						"Mother dataset is empty." );							// !@! ==>

			} // Child collection not empty.

			else
				throw new RuntimeException(
					"Child dataset is empty." );								// !@! ==>

		} // All checks cleared.

		else
			throw new RuntimeException(
				"Datasets need cleaning." );									// !@! ==>

	} // CreateSurveyCollection.



/*=======================================================================================
 *																						*
 *							PROTECTED INITIALISATION INTERFACE							*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	newDataDictionary																*
	 *==================================================================================*/

	/**
	 * <h4>Initialise household data dictionary.</h4>
	 *
	 * This method can be used to retrieve a new data dictionary, the method will return the
	 * <em>idle</em> version of the data dictionary record.
	 *
	 * The method expects the value of the record ID (<tt>_id</tt>).
	 *
	 * @param string				$theIdentifier		Record ID.
	 * @return array				New data dictionary record.
	 */
	protected function newDataDictionary( string $theIdentifier )
	{
		return [
			'_id'				=> $theIdentifier,
			self::kDDICT_STATUS	=> self::kDDICT_STATUS_IDLE,
			self::kDDICT_FIELDS	=> []
		];																			// ==>

	} // newDataDictionary.



/*=======================================================================================
 *																						*
 *						PROTECTED DICTIONARY MEMBER ACCESSOR INTERFACE					*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	datasetPath																		*
	 *==================================================================================*/

	/**
	 * <h4>Manage dataset file path.</h4>
	 *
	 * This method is used by the public interface to set or retrieve the dataset file path
	 * from the data dictionary, the method expects two parameters:
	 *
	 * <ul>
	 * 	<li><b>$theDataset</b>: Dataset identifier:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 		<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 		<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * 	 </ul>
	 * 	<li><b>$theValue</b>: Dataset file path or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL<tt>: Return current value.
	 * 		<li><i>string<i>: New dataset path.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will raise an exception if the the selector is not correct, the file is
	 * not readable or a directory; when retrieving the current value and the dataset was
	 * not yet declared, the method will return <tt>NULL</tt>.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @param string				$theValue			Dataset file path or operation.
	 * @return string				Dataset file path.
	 *
	 * @throws InvalidArgumentException
	 */
	protected function datasetPath( string $theDataset, $theValue = NULL )
	{
		//
		// Check dataset selector.
		//
		switch( $theDataset )
		{
			case self::kDDICT_CHILD_ID:
			case self::kDDICT_MOTHER_ID:
			case self::kDDICT_HOUSEHOLD_ID:
				break;

			default:
				throw new InvalidArgumentException(
					"Invalid dataset selector [$theDataset]." );				// !@! ==>
		}

		//
		// Retrieve current value.
		//
		if( $theValue === NULL )
		{
			//
			// Check dictionary.
			//
			if( ! array_key_exists(
				self::kDATASET_OFFSET_FILE, $this->mDDICTInfo[ $theDataset ] ) )
				return NULL;														// ==>

		} // Retrieve current value.

		//
		// Set new file.
		//
		else
		{
			//
			// Open file in read.
			//
			$file = new SplFileObject( (string)$theValue, "r" );

			//
			// Check file.
			//
			if( (! $file->isFile())
			 || (! $file->isWritable()) )
				throw new InvalidArgumentException(
					"Invalid file reference [$theValue]." );					// !@! ==>

			//
			// Set dictionary entry.
			//
			$this->mDDICTInfo
				[ $theDataset ]
				[ self::kDATASET_OFFSET_FILE ] = $file->getRealPath();

		} // Set new value.

		return
			$this->mDDICTInfo
				[ $theDataset ]
				[ self::kDATASET_OFFSET_FILE ];										// ==>

	} // datasetPath.


	/*===================================================================================
	 *	datasetHeaderRow																*
	 *==================================================================================*/

	/**
	 * <h4>Manage dataset header row.</h4>
	 *
	 * This method is used by the public interface to set or retrieve the dataset header
	 * row number from the data dictionary, the method expects two parameters:
	 *
	 * <ul>
	 * 	<li><b>$theDataset</b>: Dataset identifier:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 		<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 		<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * 	 </ul>
	 * 	<li><b>$theValue</b>: Dataset header row or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL<tt>: Return current value.
	 * 		<li><i>int<i>: New dataset header row.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will raise an exception if the the selector is not correct and the value
	 * is not an integer; when retrieving the current value and the dataset was not yet
	 * declared, the method will return <tt>NULL</tt>.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @param int					$theValue			Dataset header line or operation.
	 * @return int					Dataset header line number.
	 *
	 * @throws InvalidArgumentException
	 */
	protected function datasetHeaderRow( string $theDataset, $theValue = NULL )
	{
		//
		// Check dataset selector.
		//
		switch( $theDataset )
		{
			case self::kDDICT_CHILD_ID:
			case self::kDDICT_MOTHER_ID:
			case self::kDDICT_HOUSEHOLD_ID:
				break;

			default:
				throw new InvalidArgumentException(
					"Invalid dataset selector [$theDataset]." );				// !@! ==>
		}

		//
		// Retrieve current value.
		//
		if( $theValue === NULL )
		{
			//
			// Check dictionary.
			//
			if( ! array_key_exists(
				self::kDATASET_OFFSET_HEADER, $this->mDDICTInfo[ $theDataset ] ) )
				return NULL;														// ==>

		} // Retrieve current value.

		//
		// Set new header line.
		//
		else
		{
			//
			// Check value.
			//
			if( ! is_int( $theValue ) )
				throw new InvalidArgumentException(
					"Invalid header row number [$theValue]." );					// !@! ==>

			//
			// Set dictionary entry.
			//
			$this->mDDICTInfo
			[ $theDataset ]
			[ self::kDATASET_OFFSET_HEADER ] = (int)$theValue;

		} // Set new value.

		return
			$this->mDDICTInfo
			[ $theDataset ]
			[ self::kDATASET_OFFSET_HEADER ];									// ==>

	} // datasetHeaderRow.


	/*===================================================================================
	 *	datasetDataRow																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage dataset data row.</h4>
	 *
	 * This method is used by the public interface to set or retrieve the dataset data
	 * row number from the data dictionary, the method expects two parameters:
	 *
	 * <ul>
	 * 	<li><b>$theDataset</b>: Dataset identifier:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 		<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 		<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * 	 </ul>
	 * 	<li><b>$theValue</b>: Dataset data row or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL<tt>: Return current value.
	 * 		<li><i>int<i>: New dataset data row.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will raise an exception if the the selector is not correct and the value
	 * is not an integer; when retrieving the current value and the dataset was not yet
	 * declared, the method will return <tt>NULL</tt>.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @param int					$theValue			Dataset data line or operation.
	 * @return int					Dataset header line number.
	 *
	 * @throws InvalidArgumentException
	 */
	protected function datasetDataRow( string $theDataset, $theValue = NULL )
	{
		//
		// Check dataset selector.
		//
		switch( $theDataset )
		{
			case self::kDDICT_CHILD_ID:
			case self::kDDICT_MOTHER_ID:
			case self::kDDICT_HOUSEHOLD_ID:
				break;

			default:
				throw new InvalidArgumentException(
					"Invalid dataset selector [$theDataset]." );				// !@! ==>
		}

		//
		// Retrieve current value.
		//
		if( $theValue === NULL )
		{
			//
			// Check dictionary.
			//
			if( ! array_key_exists(
				self::kDATASET_OFFSET_DATA, $this->mDDICTInfo[ $theDataset ] ) )
				return NULL;														// ==>

		} // Retrieve current value.

		//
		// Set new data line.
		//
		else
		{
			//
			// Check value.
			//
			if( ! is_int( $theValue ) )
				throw new InvalidArgumentException(
					"Invalid first data row number [$theValue]." );				// !@! ==>

			//
			// Set dictionary entry.
			//
			$this->mDDICTInfo
			[ $theDataset ]
			[ self::kDATASET_OFFSET_DATA ] = (int)$theValue;

		} // Set new value.

		return
			$this->mDDICTInfo
			[ $theDataset ]
			[ self::kDATASET_OFFSET_DATA ];									// ==>

	} // datasetDataRow.


	/*===================================================================================
	 *	datasetOffset																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage dataset offset.</h4>
	 *
	 * This method is used by the public interface to set or retrieve the offset names of
	 * specific dataset columns, the method expects three parameters:
	 *
	 * <ul>
	 * 	<li><b>$theDataset</b>: Dataset identifier:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 		<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 		<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * 	 </ul>
	 * 	<li><b>$theOffset</b>: Dictionary offset, this value corresponds to the data
	 * 		dictionary record offset to be managed:
	 * 	 <ul>
	 * 		<li><tt>{@link kDATASET_DATE}<tt>: Survey date.
	 * 		<li><tt>{@link kDATASET_LOCATION}<tt>: Survey location number.
	 * 		<li><tt>{@link kDATASET_TEAM}<tt>: Survey team number.
	 * 		<li><tt>{@link kDATASET_CLUSTER}<tt>: Survey cluster number.
	 * 		<li><tt>{@link kDATASET_HOUSEHOLD}<tt>: Survey household number.
	 * 		<li><tt>{@link kDATASET_MOTHER}<tt>: Survey mother number.
	 * 		<li><tt>{@link kDATASET_IDENTIFIER}<tt>: Survey unit number.
	 * 	 </ul>
	 * 	<li><b>$theValue</b>: Offset or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL<tt>: Return current value.
	 * 		<li><i>int<i>: New offset.
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will raise an exception if the the selector is not correct; when
	 * retrieving the current value and the dataset was not yet declared, the method will
	 * return <tt>NULL</tt>.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @param string				$theOffset			Dictionary offset.
	 * @param string				$theValue			Dataset variable name.
	 * @return string				Current value.
	 *
	 * @throws InvalidArgumentException
	 */
	protected function datasetOffset( string $theDataset,
									  string $theOffset,
									  		 $theValue = NULL )
	{
		//
		// Check dataset selector.
		//
		switch( $theDataset )
		{
			case self::kDDICT_CHILD_ID:
			case self::kDDICT_MOTHER_ID:
			case self::kDDICT_HOUSEHOLD_ID:
				break;

			default:
				throw new InvalidArgumentException(
					"Invalid dataset selector [$theDataset]." );				// !@! ==>
		}

		//
		// Retrieve current value.
		//
		if( $theValue === NULL )
		{
			//
			// Check dictionary.
			//
			if( ! array_key_exists( $theOffset, $this->mDDICTInfo[ $theDataset ] ) )
				return NULL;														// ==>

		} // Retrieve current value.

		//
		// Set new offset.
		//
		else
			$this->mDDICTInfo[ $theDataset ][ $theOffset ] = (string)$theValue;

		return $this->mDDICTInfo[ $theDataset ][ $theOffset ];						// ==>

	} // datasetOffset.


	/*===================================================================================
	 *	getDatasetReader																*
	 *==================================================================================*/

	/**
	 * <h4>Get dataset reader.</h4>
	 *
	 * This method can be used to get a dataset reader, it expects a single parameter
	 * representing the dataset selector:
	 *
	 * <ul>
	 * 	<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 	<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 	<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * </ul>
	 *
	 * The method will raise an exception if the the selector is not correct and if the
	 * dataset path was not yet set.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @return PHPExcel_Reader_Abstract
	 *
	 * @throws RuntimeException
	 */
	protected function getDatasetReader( string $theDataset )
	{
		//
		// Get dataset path.
		//
		$path = $this->datasetPath( $theDataset );
		if( $path !== NULL )
		{
			//
			// Create reader.
			//
			$type = PHPExcel_IOFactory::identify( $path );
			$reader = PHPExcel_IOFactory::createReader( $type );
			$reader = $reader->load( $path );

			return $reader;														// ==>

		} // Dataset was declared.

		throw new RuntimeException(
			"Dataset [$theDataset] was not declared." );						// !@! ==>

	} // getDatasetReader.



/*=======================================================================================
 *																						*
 *							PROTECTED DATABASE UTILITIES								*
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



/*=======================================================================================
 *																						*
 *									PROTECTED UTILITIES									*
 *																						*
 *======================================================================================*/



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
		$file = new SplFileObject( (string)$theValue, "r" );

		//
		// Check file.
		//
		if( (! $file->isFile())
			|| (! $file->isWritable()) )
			throw new InvalidArgumentException(
				"Invalid file reference [$theValue]." );						// !@! ==>

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
			self::kDATASET_OFFSET_FILE		=> $file,
			self::kDATASET_OFFSET_READER	=> $reader,
			self::kDATASET_OFFSET_HEADER	=> $theHeader,
			self::kDATASET_OFFSET_DATA		=> $theData,
			self::kDATASET_OFFSET_DATE		=> $theDate,
			self::kDATASET_OFFSET_LOCATION	=> $theLocation,
			self::kDATASET_OFFSET_TEAM		=> $theTeam,
			self::kDATASET_OFFSET_CLUSTER	=> $theCluster,
			self::kDATASET_OFFSET_IDENTIFIER		=> $theIdentifier,
			self::kDDICT_STATUS	=> self::kDDICT_STATUS_IDLE,
			self::kOFFSET_DDICT		=> [],
			self::kOFFSET_DUPS		=> [],
			self::kOFFSET_RELATED	=> []
		];

		//
		// Add other elements.
		//
		if( $theHousehold !== NULL )
			$record[ self::kDATASET_OFFSET_HOUSEHOLD ] = $theHousehold;
		if( $theMother !== NULL )
			$record[ self::kDATASET_OFFSET_MOTHER ] = $theMother;

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
			return $theMember[ self::kDATASET_OFFSET_FILE ]->getRealPath();					// ==>

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset not yet defined." );										// !@! ==>

	} // getDatasetPath.


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
			return $theMember[ self::kDATASET_OFFSET_HEADER ];								// ==>

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
			return $theMember[ self::kDATASET_OFFSET_DATA ];								// ==>

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
			return $theMember[ self::kDATASET_OFFSET_DATE ];								// ==>

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
			return $theMember[ self::kDATASET_OFFSET_LOCATION ];							// ==>

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
			return $theMember[ self::kDATASET_OFFSET_TEAM ];							// ==>

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
			return $theMember[ self::kDATASET_OFFSET_CLUSTER ];								// ==>

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
			return $theMember[ self::kDATASET_OFFSET_IDENTIFIER ];								// ==>

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
			return $theMember[ self::kDATASET_OFFSET_HOUSEHOLD ];						// ==>

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
			return $theMember[ self::kDATASET_OFFSET_MOTHER ];						// ==>

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
			return $theMember[ self::kDDICT_STATUS ];								// ==>

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
		foreach( $theData[ $theInfo[ self::kDATASET_OFFSET_HEADER ] ] as $key => $value )
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
			$theInfo[ self::kDATASET_OFFSET_LOCATION ] => '$' . $theInfo[ self::kDATASET_OFFSET_LOCATION ],
			$theInfo[ self::kDATASET_OFFSET_TEAM ] => '$' . $theInfo[ self::kDATASET_OFFSET_TEAM ],
			$theInfo[ self::kDATASET_OFFSET_CLUSTER ] => '$' . $theInfo[ self::kDATASET_OFFSET_CLUSTER ]
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
			$theInfo[ self::kDDICT_STATUS ] = self::kDDICT_STATUS_DUPLICATES;

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

			return self::kDDICT_STATUS_DUPLICATES;									// ==>

		} // Has duplicates.

		return
			$theInfo[ self::kDDICT_STATUS ] = self::kDDICT_STATUS_LOADED;			// ==>

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
				$criteria = [ '$set' => [ self::kFILE_OFFSET_DUPLICATES => $id ] ];

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
			// Init local storage.
			//
			$style = [
				'fill' => [
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => [ 'argb' => 'FFFF0000' ]
				],
				'font' => [
					'bold' => TRUE
				],
				'alignment' => [
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
				]
			];

			//
			// Get worksheet.
			//
			$worksheet =
				$theInfo[ self::kDATASET_OFFSET_READER ]
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
				$theInfo[ self::kDATASET_OFFSET_HEADER ],
				self::kFILE_OFFSET_DUPLICATES );

			//
			// Iterate duplicate groups.
			//
			foreach( $theInfo[ self::kOFFSET_DUPS ] as $id => $data )
			{

				//
				// Iterate duplicate rows.
				//
				foreach( $data[ 'Duplicate rows' ] as $row )
				{
					//
					// Get cell.
					//
					$cell = $worksheet->getCellByColumnAndRow( $column, $row );

					//
					// Set cell style.
					//
					$cell->getStyle()->applyFromArray( $style );

					//
					// Set cell value.
					//
					$cell->setValue( "$id - DUPLICATE" );
				}

			} // Iterating duplicate groups.

			//
			// Write file.
			//
			$writer = PHPExcel_IOFactory::createWriter(
					$theInfo[ self::kDATASET_OFFSET_READER ], 'Excel2007'
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
		$default = [ self::kDATASET_OFFSET_LOCATION, self::kDATASET_OFFSET_TEAM, self::kDATASET_OFFSET_CLUSTER ];

		//
		// Init selection group.
		//
		$temp = [];
		foreach( $default as $item )
			$temp[ $theInfo[ $item ] ] = '$' . $theInfo[ $item ];
		$temp[ $theInfo[ self::kDATASET_OFFSET_HOUSEHOLD ] ]
			= '$' . $theInfo[ self::kDATASET_OFFSET_HOUSEHOLD ];

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
				$query[ $this->mHouseholdInfo[ self::kDATASET_OFFSET_IDENTIFIER ] ]
					= $relation[ $theInfo[ self::kDATASET_OFFSET_HOUSEHOLD ] ];

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
						'$set' => [ self::kCOLLECTION_OFFSET_HOUSEHOLD_ID => $household[ '_id' ] ]
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
					$theInfo[ self::kDDICT_STATUS ] = self::kDDICT_STATUS_REFERENCES;

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

		return $theInfo[ self::kDDICT_STATUS ];									// ==>

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
			// Init local storage.
			//
			$style = [
				'fill' => [
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => [ 'argb' => 'FFFF0000' ]
				],
				'font' => [
					'bold' => TRUE
				],
				'alignment' => [
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
				]
			];

			//
			// Get worksheet.
			//
			$worksheet =
				$theInfo[ self::kDATASET_OFFSET_READER ]
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
				$theInfo[ self::kDATASET_OFFSET_HEADER ],
				self::kFILE_OFFSET_HOUSEHOLD_REF );

			//
			// Iterate missing related households.
			//
			foreach( $theInfo[ self::kOFFSET_RELATED ] as $data )
			{
				//
				// Iterate offending rows.
				//
				foreach( $data[ 'Rows' ] as $row )
				{
					//
					// Get cell.
					//
					$cell = $worksheet->getCellByColumnAndRow( $column, $row );

					//
					// Set cell style.
					//
					$cell->getStyle()->applyFromArray( $style );

					//
					// Set cell value.
					//
					$cell->setValue( "MISSING HOUSEHOLD" );

				} // Iterating offending rows.

			} // Iterating duplicate groups.

			//
			// Write file.
			//
			$writer = PHPExcel_IOFactory::createWriter(
				$theInfo[ self::kDATASET_OFFSET_READER ], 'Excel2007'
			)->save( $this->getDatasetPath( $theInfo ) );

		} // Has missing households.

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
			self::kDATASET_OFFSET_LOCATION,
			self::kDATASET_OFFSET_TEAM,
			self::kDATASET_OFFSET_CLUSTER,
			self::kDATASET_OFFSET_HOUSEHOLD
		];

		//
		// Init selection group.
		//
		$temp = [];
		foreach( $default as $item )
			$temp[ $theInfo[ $item ] ] = '$' . $theInfo[ $item ];
		$temp[ $theInfo[ self::kDATASET_OFFSET_MOTHER ] ]
			= '$' . $theInfo[ self::kDATASET_OFFSET_MOTHER ];

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
				$query[ $this->mMotherInfo[ self::kDATASET_OFFSET_IDENTIFIER ] ]
					= $relation[ $theInfo[ self::kDATASET_OFFSET_MOTHER ] ];

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
						'$set' => [ self::kCOLLECTION_OFFSET_MOTHER_ID => $mother[ '_id' ] ]
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
					$theInfo[ self::kDDICT_STATUS ] = self::kDDICT_STATUS_REFERENCES;

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

		return $theInfo[ self::kDDICT_STATUS ];									// ==>

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
			// Init local storage.
			//
			$style = [
				'fill' => [
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => [ 'argb' => 'FFFF0000' ]
				],
				'font' => [
					'bold' => TRUE
				],
				'alignment' => [
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
				]
			];

			//
			// Get worksheet.
			//
			$worksheet =
				$theInfo[ self::kDATASET_OFFSET_READER ]
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
				$theInfo[ self::kDATASET_OFFSET_HEADER ],
				self::kFILE_OFFSET_MOTHER_REF );

			//
			// Iterate missing related households.
			//
			foreach( $theInfo[ self::kOFFSET_RELATED ] as $data )
			{
				//
				// Iterate offending rows.
				//
				foreach( $data[ 'Rows' ] as $row )
				{
					//
					// Get cell.
					//
					$cell = $worksheet->getCellByColumnAndRow( $column, $row );

					//
					// Set cell style.
					//
					$cell->getStyle()->applyFromArray( $style );

					//
					// Set cell value.
					//
					$cell->setValue( "MISSING MOTHER" );

				} // Iterating offending rows.

			} // Iterating duplicate groups.

			//
			// Write file.
			//
			$writer = PHPExcel_IOFactory::createWriter(
				$theInfo[ self::kDATASET_OFFSET_READER ], 'Excel2007'
			)->save( $this->getDatasetPath( $theInfo ) );

		} // Has missing mothers.

	} // signalFileRelatedMothers.


	/*===================================================================================
	 *	loadFinalHouseholdCollection													*
	 *==================================================================================*/

	/**
	 * <h4>Load final household collection.</h4>
	 *
	 * This method can be used to load the final household dataset.
	 */
	protected function loadFinalHouseholdCollection()
	{
		//
		// Init collections.
		//
		$this->mHousehold->drop();
		$collection =
			$this->Database()->selectCollection( "temp_" . self::kNAME_HOUSEHOLD );

		//
		// Init default fields.
		//
		$defaults = [
			$this->mHouseholdInfo[ self::kDATASET_OFFSET_DATE ]
				=> self::kCOLLECTION_OFFSET_DATE,
			$this->mHouseholdInfo[ self::kDATASET_OFFSET_LOCATION ]
				=> self::kCOLLECTION_OFFSET_LOCATION,
			$this->mHouseholdInfo[ self::kDATASET_OFFSET_TEAM ]
				=> self::kCOLLECTION_OFFSET_TEAM,
			$this->mHouseholdInfo[ self::kDATASET_OFFSET_CLUSTER ]
				=> self::kCOLLECTION_OFFSET_CLUSTER,
			$this->mHouseholdInfo[ self::kDATASET_OFFSET_IDENTIFIER ]
				=> self::kCOLLECTION_OFFSET_IDENTIFIER
		];

		//
		// Init other fields.
		//
		$other =
			array_diff(
				array_keys( $this->mHouseholdInfo[ self::kOFFSET_DDICT ] ),
				array_keys( $defaults )
			);

		//
		// Iterate temporary collection.
		//
		$cursor = $collection->find();
		foreach( $cursor as $record )
		{
			//
			// Init document.
			//
			$document = [ '_id' => $record[ '_id' ] ];

			//
			// Load default fields.
			//
			foreach( $defaults as $name => $default )
				$document[ $default ] = $record[ $name ];

			//
			// Load other fields.
			//
			foreach( $other as $name )
			{
				if( array_key_exists( $name, $record ) )
					$document[ $name ] = $record[ $name ];
			}

			//
			// Save record.
			//
			$this->mHousehold->insertOne( $document );

		} // Iterating temporary collection.

	} // loadFinalHouseholdCollection.


	/*===================================================================================
	 *	loadFinalMotherCollection														*
	 *==================================================================================*/

	/**
	 * <h4>Load final mother collection.</h4>
	 *
	 * This method can be used to load the final mother dataset.
	 */
	protected function loadFinalMotherCollection()
	{
		//
		// Init collections.
		//
		$this->mMother->drop();
		$collection =
			$this->Database()->selectCollection( "temp_" . self::kNAME_MOTHER );

		//
		// Init default fields.
		//
		$defaults = [
			$this->mMotherInfo[ self::kDATASET_OFFSET_DATE ] => self::kCOLLECTION_OFFSET_DATE,
			$this->mMotherInfo[ self::kDATASET_OFFSET_LOCATION ] => self::kCOLLECTION_OFFSET_LOCATION,
			$this->mMotherInfo[ self::kDATASET_OFFSET_TEAM ] => self::kCOLLECTION_OFFSET_TEAM,
			$this->mMotherInfo[ self::kDATASET_OFFSET_CLUSTER ] => self::kCOLLECTION_OFFSET_CLUSTER,
			$this->mMotherInfo[ self::kDATASET_OFFSET_HOUSEHOLD ] => self::kCOLLECTION_OFFSET_HOUSEHOLD,
			$this->mMotherInfo[ self::kDATASET_OFFSET_IDENTIFIER ] => self::kCOLLECTION_OFFSET_IDENTIFIER,
			self::kCOLLECTION_OFFSET_HOUSEHOLD_ID => self::kCOLLECTION_OFFSET_HOUSEHOLD_ID
		];

		//
		// Init other fields.
		//
		$other =
			array_diff(
				array_keys( $this->mMotherInfo[ self::kOFFSET_DDICT ] ),
				array_keys( $defaults )
			);

		//
		// Iterate temporary collection.
		//
		$cursor = $collection->find();
		foreach( $cursor as $record )
		{
			//
			// Init document.
			//
			$document = [ '_id' => $record[ '_id' ] ];

			//
			// Load default fields.
			//
			foreach( $defaults as $name => $default )
				$document[ $default ] = $record[ $name ];

			//
			// Load other fields.
			//
			foreach( $other as $name )
			{
				if( array_key_exists( $name, $record ) )
					$document[ $name ] = $record[ $name ];
			}

			//
			// Save record.
			//
			$this->mMother->insertOne( $document );

		} // Iterating temporary collection.

	} // loadFinalMotherCollection.


	/*===================================================================================
	 *	loadFinalChildCollection														*
	 *==================================================================================*/

	/**
	 * <h4>Load final child collection.</h4>
	 *
	 * This method can be used to load the final child dataset.
	 */
	protected function loadFinalChildCollection()
	{
		//
		// Init collections.
		//
		$this->mChild->drop();
		$collection =
			$this->Database()->selectCollection( "temp_" . self::kNAME_CHILD );

		//
		// Init default fields.
		//
		$defaults = [
			$this->mChildInfo[ self::kDATASET_OFFSET_DATE ] => self::kCOLLECTION_OFFSET_DATE,
			$this->mChildInfo[ self::kDATASET_OFFSET_LOCATION ] => self::kCOLLECTION_OFFSET_LOCATION,
			$this->mChildInfo[ self::kDATASET_OFFSET_TEAM ] => self::kCOLLECTION_OFFSET_TEAM,
			$this->mChildInfo[ self::kDATASET_OFFSET_CLUSTER ] => self::kCOLLECTION_OFFSET_CLUSTER,
			$this->mChildInfo[ self::kDATASET_OFFSET_HOUSEHOLD ] => self::kCOLLECTION_OFFSET_HOUSEHOLD,
			$this->mChildInfo[ self::kDATASET_OFFSET_MOTHER ] => self::kCOLLECTION_OFFSET_MOTHER,
			$this->mChildInfo[ self::kDATASET_OFFSET_IDENTIFIER ] => self::kCOLLECTION_OFFSET_IDENTIFIER,
			self::kCOLLECTION_OFFSET_HOUSEHOLD_ID => self::kCOLLECTION_OFFSET_HOUSEHOLD_ID,
			self::kCOLLECTION_OFFSET_MOTHER_ID => self::kCOLLECTION_OFFSET_MOTHER_ID
		];

		//
		// Init other fields.
		//
		$other =
			array_diff(
				array_keys( $this->mChildInfo[ self::kOFFSET_DDICT ] ),
				array_keys( $defaults )
			);

		//
		// Iterate temporary collection.
		//
		$cursor = $collection->find();
		foreach( $cursor as $record )
		{
			//
			// Init document.
			//
			$document = [ '_id' => $record[ '_id' ] ];

			//
			// Load default fields.
			//
			foreach( $defaults as $name => $default )
				$document[ $default ] = $record[ $name ];

			//
			// Load other fields.
			//
			foreach( $other as $name )
			{
				if( array_key_exists( $name, $record ) )
					$document[ $name ] = $record[ $name ];
			}

			//
			// Save record.
			//
			$this->mChild->insertOne( $document );

		} // Iterating temporary collection.

	} // loadFinalChildCollection.


	/*===================================================================================
	 *	loadFinalSurveyCollection														*
	 *==================================================================================*/

	/**
	 * <h4>Load final survey collection.</h4>
	 *
	 * This method can be used to merge the child, mother and household surveys into the
	 * final survey collection.
	 */
	protected function loadFinalSurveyCollection()
	{
		//
		// Init collections.
		//
		$this->mSurvey->drop();

		//
		// Init default fields.
		//
		$defaults = [
			self::kCOLLECTION_OFFSET_DATE,
			self::kCOLLECTION_OFFSET_LOCATION,
			self::kCOLLECTION_OFFSET_TEAM,
			self::kCOLLECTION_OFFSET_CLUSTER,
			self::kCOLLECTION_OFFSET_HOUSEHOLD,
			self::kCOLLECTION_OFFSET_MOTHER,
			self::kCOLLECTION_OFFSET_IDENTIFIER
		];

		//
		// Init other fields.
		//
		$child_fields = $this->getChildFields();
		$mother_fields = $this->getMotherFields();
		$household_fields = $this->getHouseholdFields();

		//
		// Iterate children.
		//
		$child_cursor = $this->mChild->find();
		foreach( $child_cursor as $child )
		{
			//
			// Init document.
			//
			$document = [ '_id' => $child[ '_id' ] ];

			//
			// Load default fields.
			//
			foreach( $defaults as $name => $default )
				$document[ $default ] = $child[ $default ];

			//
			// Load child fields.
			//
			foreach( $child_fields as $name )
			{
				if( array_key_exists( $name, $child ) )
					$document[ $name ] = $child[ $name ];
			}

			//
			// Get mother.
			//
			$mother =
				$this->mMother->findOne(
					[ '_id' => $child[ self::kCOLLECTION_OFFSET_MOTHER_ID ] ] );
			if( $mother !== NULL )
			{
				//
				// Set mother ID.
				//
				$document[ self::kCOLLECTION_OFFSET_MOTHER_ID ] = $child[ self::kCOLLECTION_OFFSET_MOTHER_ID ];

				//
				// Load mother fields.
				//
				foreach( $mother_fields as $name )
				{
					if( array_key_exists( $name, $mother ) )
						$document[ $name ] = $mother[ $name ];
				}

				//
				// Get household.
				//
				$household =
					$this->mHousehold->findOne(
						[ '_id' => $child[ self::kCOLLECTION_OFFSET_HOUSEHOLD_ID ] ] );
				if( $household !== NULL )
				{
					//
					// Set household ID.
					//
					$document[ self::kCOLLECTION_OFFSET_HOUSEHOLD_ID ]
						= $child[ self::kCOLLECTION_OFFSET_HOUSEHOLD_ID ];

					//
					// Load household fields.
					//
					foreach( $household_fields as $name )
					{
						if( array_key_exists( $name, $household ) )
							$document[ $name ] = $household[ $name ];
					}

				} // Found household.

				else
					throw new InvalidArgumentException(
						"Missing household with ID [" .
						$child[ self::kCOLLECTION_OFFSET_HOUSEHOLD_ID ] .
						"]." );													// !@! ==>

			} // Found mother.

			else
				throw new InvalidArgumentException(
					"Missing mother with ID [" .
					$child[ self::kCOLLECTION_OFFSET_MOTHER_ID ] .
					"]." );														// !@! ==>

			//
			// Save record.
			//
			$this->mSurvey->insertOne( $document );

		} // Iterating children.

	} // loadFinalSurveyCollection.



/*=======================================================================================
 *																						*
 *									PROTECTED UTILITIES									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	getChildFields																	*
	 *==================================================================================*/

	/**
	 * <h4>Get significant child fields.</h4>
	 *
	 * This method can be used to retrieve the list of child collection fields excluding
	 * default fields.
	 *
	 * The method assumes all required information is loaded into the object.
	 *
	 * @return array
	 */
	protected function getChildFields()
	{
		//
		// Init default fields.
		//
		$defaults = [
			self::kCOLLECTION_OFFSET_DATE,
			self::kCOLLECTION_OFFSET_LOCATION,
			self::kCOLLECTION_OFFSET_TEAM,
			self::kCOLLECTION_OFFSET_CLUSTER,
			self::kCOLLECTION_OFFSET_HOUSEHOLD,
			self::kCOLLECTION_OFFSET_MOTHER,
			self::kCOLLECTION_OFFSET_IDENTIFIER
		];

		return
			array_diff(
				array_keys( $this->mChildInfo[ self::kOFFSET_DDICT ] ),
				$defaults,
				[	$this->mChildInfo[ self::kDATASET_OFFSET_DATE ],
					$this->mChildInfo[ self::kDATASET_OFFSET_LOCATION ],
					$this->mChildInfo[ self::kDATASET_OFFSET_TEAM ],
					$this->mChildInfo[ self::kDATASET_OFFSET_CLUSTER ],
					$this->mChildInfo[ self::kDATASET_OFFSET_HOUSEHOLD ],
					$this->mChildInfo[ self::kDATASET_OFFSET_MOTHER ],
					$this->mChildInfo[ self::kDATASET_OFFSET_IDENTIFIER ] ],
				[ self::kCOLLECTION_OFFSET_HOUSEHOLD_ID, self::kCOLLECTION_OFFSET_MOTHER_ID ]
			);																		// ==>

	} // getChildFields.


	/*===================================================================================
	 *	getMotherFields																	*
	 *==================================================================================*/

	/**
	 * <h4>Get significant mother fields.</h4>
	 *
	 * This method can be used to retrieve the list of mother collection fields excluding
	 * default fields.
	 *
	 * The method assumes all required information is loaded into the object.
	 *
	 * @return array
	 */
	protected function getMotherFields()
	{
		//
		// Init default fields.
		//
		$defaults = [
			self::kCOLLECTION_OFFSET_DATE,
			self::kCOLLECTION_OFFSET_LOCATION,
			self::kCOLLECTION_OFFSET_TEAM,
			self::kCOLLECTION_OFFSET_CLUSTER,
			self::kCOLLECTION_OFFSET_HOUSEHOLD,
			self::kCOLLECTION_OFFSET_IDENTIFIER
		];

		return
			array_diff(
				array_keys( $this->mMotherInfo[ self::kOFFSET_DDICT ] ),
				$defaults,
				[	$this->mMotherInfo[ self::kDATASET_OFFSET_DATE ],
					$this->mMotherInfo[ self::kDATASET_OFFSET_LOCATION ],
					$this->mMotherInfo[ self::kDATASET_OFFSET_TEAM ],
					$this->mMotherInfo[ self::kDATASET_OFFSET_CLUSTER ],
					$this->mMotherInfo[ self::kDATASET_OFFSET_HOUSEHOLD ],
					$this->mMotherInfo[ self::kDATASET_OFFSET_IDENTIFIER ] ],
				[ self::kCOLLECTION_OFFSET_HOUSEHOLD_ID ]
			);																		// ==>

	} // getMotherFields.


	/*===================================================================================
	 *	getHouseholdFields																*
	 *==================================================================================*/

	/**
	 * <h4>Get significant mother fields.</h4>
	 *
	 * This method can be used to retrieve the list of mother collection fields excluding
	 * default fields.
	 *
	 * The method assumes all required information is loaded into the object.
	 *
	 * @return array
	 */
	protected function getHouseholdFields()
	{
		//
		// Init default fields.
		//
		$defaults = [
			self::kCOLLECTION_OFFSET_DATE,
			self::kCOLLECTION_OFFSET_LOCATION,
			self::kCOLLECTION_OFFSET_TEAM,
			self::kCOLLECTION_OFFSET_CLUSTER,
			self::kCOLLECTION_OFFSET_IDENTIFIER
		];

		return
			array_diff(
				array_keys( $this->mHouseholdInfo[ self::kOFFSET_DDICT ] ),
				$defaults,
				[	$this->mHouseholdInfo[ self::kDATASET_OFFSET_DATE ],
					$this->mHouseholdInfo[ self::kDATASET_OFFSET_LOCATION ],
					$this->mHouseholdInfo[ self::kDATASET_OFFSET_TEAM ],
					$this->mHouseholdInfo[ self::kDATASET_OFFSET_CLUSTER ],
					$this->mHouseholdInfo[ self::kDATASET_OFFSET_IDENTIFIER ] ]
			);																		// ==>

	} // getHouseholdFields.




} // class SMARTLoader.


?>
