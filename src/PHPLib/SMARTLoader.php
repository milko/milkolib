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
class SMARTLoader
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
	 * <h4>Original dataset collection name prefix.</h4>
	 *
	 * This constant holds the <em>name prefix</em> for the <em>original dataset
	 * collection</em>.
	 *
	 * @var string
	 */
	const kNAME_PREFIX_ORIGINAL = 'original_';

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
	 * <h4>Data dictionary dataset columns.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>list of dataset columns</em>, it is an array in which the index is the column
	 * cell coordinate and the value is the dataset field name (corresponding header row
	 * value).
	 *
	 * @var string
	 */
	const kDDICT_COLUMNS = 'columns';

	/**
	 * <h4>Data dictionary dataset duplicate columns.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>list of duplicate dataset columns</em>, it is an array that containd the list
	 * of duplicate header row values.
	 *
	 * @var string
	 */
	const kDDICT_COLUMN_DUPS = 'column_dups';

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
	 * 	<li><tt>{@link kTYPE_STRING</tt>: Any non numeric value will imply this kind.
	 * 	<li><tt>{@link kTYPE_DOUBLE</tt>: Any floating point number with a decimal
	 * 		other than <tt>0</tt> will imply this type.
	 * 	<li><tt>{@link kTYPE_INTEGER</tt>: If the set of values is all numeric and
	 * 		does not have a floating point, it implies that all values are of integer type.
	 * </ul>
	 *
	 * @var string
	 */
	const kFIELD_KIND = 'kind';

	/**
	 * <h4>Data dictionary field type.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>field data type</em>. This enumerated value indicates the specific data type
	 * of the field and is a user determined value:
	 *
	 * <ul>
	 * 	<li><tt>{@link kTYPE_STRING</tt>: String.
	 * 	<li><tt>{@link kTYPE_DATE</tt>: Date in <tt>YYYY-MM-DD</tt> format.
	 * 	<li><tt>{@link kTYPE_INTEGER</tt>: Integer.
	 * 	<li><tt>{@link kTYPE_DOUBLE</tt>: Floating point number, double by default.
	 * </ul>
	 *
	 * @var string
	 */
	const kFIELD_TYPE = 'type';

	/**
	 * <h4>Data dictionary field name.</h4>
	 *
	 * This constant holds the <em>data dictionary offset</em> for the element that holds
	 * the <em>field default name</em>. This value represents the default or standard field
	 * name that will be used in the final processed datasets.
	 *
	 * @var string
	 */
	const kFIELD_NAME = 'name';

	/**
	 * <h4>Data dictionary distinct field values.</h4>
	 *
	 * This constant holds the <em>distinct values count</em> for the current field.
	 *
	 * @var string
	 */
	const kFIELD_DISTINCT = 'distinct';

	/**
	 * <h4>String type.</h4>
	 *
	 * This constant represents a string data type.
	 *
	 * @var string
	 */
	const kTYPE_STRING = 'string';

	/**
	 * <h4>Integer type.</h4>
	 *
	 * This constant represents a integer data type.
	 *
	 * @var string
	 */
	const kTYPE_INTEGER = 'int';

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
	const kTYPE_NUMBER = 'number';

	/**
	 * <h4>Double type.</h4>
	 *
	 * This constant represents a double floating point data type.
	 *
	 * @var string
	 */
	const kTYPE_DOUBLE = 'double';

	/**
	 * <h4>Date type.</h4>
	 *
	 * This constant represents a date type, dates will be stored in the <tt>YYYY-MM-DD</tt>
	 * format.
	 *
	 * @var string
	 */
	const kTYPE_DATE = 'date';

	/**
	 * <h4>Dataset idle status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>idle status</em>, it
	 * signifies that the dataset was not yet declared.
	 *
	 * @var int
	 */
	const kSTATUS_IDLE = 0x00000000;

	/**
	 * <h4>Dataset loaded status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>loaded status</em>, it
	 * signifies that the dataset was loaded from the file to the collection.
	 *
	 * @var int
	 */
	const kSTATUS_LOADED = 0x00000001;

	/**
	 * <h4>Dataset checked duplicates status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>duplicates checked
	 * status</em>, it signifies that the dataset has been checked for duplicate entries.
	 *
	 * @var int
	 */
	const kSTATUS_CHECKED_DUPS = 0x00000002;

	/**
	 * <h4>Dataset checked references status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>references checked
	 * status</em>, it signifies that the dataset has been checked for invalid references.
	 *
	 * @var int
	 */
	const kSTATUS_CHECKED_REFS = 0x00000004;

	/**
	 * <h4>Dataset processed stats status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>stats processed
	 * status</em>, it signifies that the dataset holds statistical information.
	 *
	 * @var int
	 */
	const kSTATUS_LOADED_STATS = 0x00000008;

	/**
	 * <h4>Dataset finalised status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>finalised status</em>,
	 * it signifies that the dataset has been validated and written to the final collection.
	 *
	 * @var int
	 */
	const kSTATUS_VALID = 0x00000010;

	/**
	 * <h4>Dataset has duplicate fields status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>duplicate fields
	 * status</em>, it signifies that the dataset has duplicate columns.
	 *
	 * @var int
	 */
	const kSTATUS_DUPLICATE_COLUMNS = 0x00000020;

	/**
	 * <h4>Dataset has duplicates status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>duplicate entries
	 * status</em>, it signifies that the dataset has duplicate entries.
	 *
	 * @var int
	 */
	const kSTATUS_DUPLICATE_ENTRIES = 0x00000040;

	/**
	 * <h4>Dataset has invalid references status.</h4>
	 *
	 * This constant holds the bitfield mask corresponding to the <em>invalid references
	 * status</em>, it signifies that the dataset has invalid references.
	 *
	 * @var int
	 */
	const kSTATUS_INVALID_REFERENCES = 0x00000080;

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
 *						PUBLIC DICTIONARY MEMBER ACCESSOR INTERFACE						*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	DataDictionary																	*
	 *==================================================================================*/

	/**
	 * <h4>Retrieve the datadictionary.</h4>
	 *
	 * This method can be used to retrieve a copy of the current data dictionary.
	 *
	 * @return array				Data dictionary.
	 */
	public function DataDictionary( $theValue = NULL )
	{
		return $this->mDDICTInfo;													// ==>

	} // DataDictionary.


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


	/*===================================================================================
	 *	ChildDatasetHeaderCoumns														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child header columns.</h4>
	 *
	 * This method can be used to manage the child data dictionary header columns, this
	 * element holds the list of header row values and their relative columns in the
	 * original dataset.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset header columns.
	 *
	 * @uses dictionaryList()
	 */
	public function ChildDatasetHeaderCoumns( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_CHILD_ID, self::kDDICT_COLUMNS, $theValue );				// ==>

	} // ChildDatasetHeaderCoumns.


	/*===================================================================================
	 *	MotherDatasetHeaderCoumns														*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother header columns.</h4>
	 *
	 * This method can be used to manage the mother data dictionary header columns, this
	 * element holds the list of header row values and their relative columns in the
	 * original dataset.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset header columns.
	 *
	 * @uses dictionaryList()
	 */
	public function MotherDatasetHeaderCoumns( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_MOTHER_ID, self::kDDICT_COLUMNS, $theValue );				// ==>

	} // MotherDatasetHeaderCoumns.


	/*===================================================================================
	 *	HouseholdDatasetHeaderCoumns													*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household header columns.</h4>
	 *
	 * This method can be used to manage the household data dictionary header columns, this
	 * element holds the list of header row values and their relative columns in the
	 * original dataset.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset header columns.
	 *
	 * @uses dictionaryList()
	 */
	public function HouseholdDatasetHeaderCoumns( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_HOUSEHOLD_ID, self::kDDICT_COLUMNS, $theValue );			// ==>

	} // HouseholdDatasetHeaderCoumns.


	/*===================================================================================
	 *	ChildDatasetDuplicateHeaderCoumns												*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child header duplicate columns.</h4>
	 *
	 * This method can be used to manage the child data dictionary header duplicate columns,
	 * this element holds the list of values that appear more than once in the header row.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset header duplicate columns.
	 *
	 * @uses dictionaryList()
	 */
	public function ChildDatasetDuplicateHeaderCoumns( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_CHILD_ID, self::kDDICT_COLUMN_DUPS, $theValue );			// ==>

	} // ChildDatasetDuplicateHeaderCoumns.


	/*===================================================================================
	 *	MotherDatasetDuplicateHeaderCoumns												*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother header duplicate columns.</h4>
	 *
	 * This method can be used to manage the mother data dictionary header duplicate columns, this
	 * element holds the list of header row values and their relative columns in the
	 * original dataset.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset header duplicate columns.
	 *
	 * @uses dictionaryList()
	 */
	public function MotherDatasetDuplicateHeaderCoumns( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_MOTHER_ID, self::kDDICT_COLUMN_DUPS, $theValue );			// ==>

	} // MotherDatasetDuplicateHeaderCoumns.


	/*===================================================================================
	 *	HouseholdDatasetDuplicateHeaderCoumns											*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household header duplicate columns.</h4>
	 *
	 * This method can be used to manage the household data dictionary header duplicate columns, this
	 * element holds the list of header row values and their relative columns in the
	 * original dataset.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset header duplicate columns.
	 *
	 * @uses dictionaryList()
	 */
	public function HouseholdDatasetDuplicateHeaderCoumns( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_HOUSEHOLD_ID, self::kDDICT_COLUMN_DUPS, $theValue );		// ==>

	} // HouseholdDatasetDuplicateHeaderCoumns.


	/*===================================================================================
	 *	ChildDatasetFields																*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve child fields.</h4>
	 *
	 * This method can be used to manage the child data dictionary fields, this
	 * element holds the list of field names and types.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset fields.
	 *
	 * @uses dictionaryList()
	 */
	public function ChildDatasetFields( $theValue = NULL )
	{
		return $this->dictionaryList(
			self::kDDICT_CHILD_ID, self::kDDICT_FIELDS, $theValue );				// ==>

	} // ChildDatasetFields.


	/*===================================================================================
	 *	MotherDatasetFields																*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve mother fields.</h4>
	 *
	 * This method can be used to manage the mother data dictionary fields, this
	 * element holds the list of field names and types.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset fields.
	 *
	 * @uses dictionaryList()
	 */
	public function MotherDatasetFields( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_MOTHER_ID, self::kDDICT_FIELDS, $theValue );				// ==>

	} // MotherDatasetFields.


	/*===================================================================================
	 *	HouseholdDatasetFields															*
	 *==================================================================================*/

	/**
	 * <h4>Set or retrieve household fields.</h4>
	 *
	 * This method can be used to manage the household data dictionary fields, this
	 * element holds the list of field names and types.
	 *
	 * The method expects a single parameter that represents the new value, or the
	 * operation:
	 *
	 * <ul>
	 * 	<li><tt>NULL</tt>: Retrieve current value.
	 * 	<li><i>array</i>: Set new value.
	 * </ul>
	 *
	 * @param array|NULL			$theValue			New value or operation.
	 * @return array				Dataset fields.
	 *
	 * @uses dictionaryList()
	 */
	public function HouseholdDatasetFields( $theValue = NULL )
	{
		return $this->datasetOffset(
			self::kDDICT_HOUSEHOLD_ID, self::kDDICT_FIELDS, $theValue );			// ==>

	} // HouseholdDatasetFields.



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
	 * @uses newDataDictionary()
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
	 *	ResetDictionary																	*
	 *==================================================================================*/

	/**
	 * <h4>Reset data dictionary.</h4>
	 *
	 * The duty of this method is to set the {@link mDDICTInfo} data member to an idle state
	 * and update the database stored copy.
	 *
	 * Use this method to reset the data dictionary.
	 *
	 * @uses newDataDictionary()
	 */
	public function ResetDictionary()
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
		// Reset in database.
		//
		$this->mDDICT->deleteMany( [] );

		//
		// Reset data dictionary.
		//
		foreach( $datasets as $dataset )
		{
			//
			// Get idle record.
			//
			$document = $this->newDataDictionary( $dataset );

			//
			// Reset data member.
			//
			$this->mDDICTInfo[ $dataset ] = $document;

			//
			// Store data member.
			//
			$this->mDDICT->insertOne( $document );
		}

	} // ResetDictionary.


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
			// Collect data.
			//
			$records = [];
			foreach( $this->mDDICTInfo as $dataset => $dictionary )
				$records[] = $dictionary;

			//
			// Clear existing data dictionary.
			// MILKO - Had to replace replaceOne() with the below,
			//		   because bulk write didn't work.
			//
			if( $this->mDDICT->count() )
				$this->mDDICT->deleteMany( [] );

			//
			// Insert dictionary.
			//
			$this->mDDICT->insertMany( $records );
		}

	} // SaveDictionary.



/*=======================================================================================
 *																						*
 *							PUBLIC FILE IMPORT INTERFACE								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	LoadChildDataset																*
	 *==================================================================================*/

	/**
	 * <h4>Load child dataset.</h4>
	 *
	 * This method can be used to load the child dataset into the database, the original
	 * file will be stored in a collection named with {@link kNAME_PREFIX_ORIGINAL} as
	 * prefix and {@link kNAME_CHILD} as suffix.
	 *
	 * The collection will feature the row number as the ID and the column as the field
	 * name, the method will return the status code {@link kSTATUS_LOADED} or raise an
	 * exception if the file was not declared.
	 *
	 * @return string				Status code.
	 */
	public function LoadChildDataset()
	{
		return $this->loadDataset( self::kDDICT_CHILD_ID );							// ==>

	} // LoadChildDataset.


	/*===================================================================================
	 *	LoadMotherDataset																*
	 *==================================================================================*/

	/**
	 * <h4>Load mother dataset.</h4>
	 *
	 * This method can be used to load the mother dataset into the database, the original
	 * file will be stored in a collection named with {@link kNAME_PREFIX_ORIGINAL} as
	 * prefix and {@link kNAME_MOTHER} as suffix.
	 *
	 * The collection will feature the row number as the ID and the column as the field
	 * name, the method will return the status code {@link kSTATUS_LOADED} or raise an
	 * exception if the file was not declared.
	 *
	 * @return string				Status code.
	 */
	public function LoadMotherDataset()
	{
		return $this->loadDataset( self::kDDICT_MOTHER_ID );						// ==>

	} // LoadMotherDataset.


	/*===================================================================================
	 *	LoadHouseholdDataset															*
	 *==================================================================================*/

	/**
	 * <h4>Load household dataset.</h4>
	 *
	 * This method can be used to load the household dataset into the database, the original
	 * file will be stored in a collection named with {@link kNAME_PREFIX_ORIGINAL} as
	 * prefix and {@link kNAME_HOUSEHOLD} as suffix.
	 *
	 * The collection will feature the row number as the ID and the column as the field
	 * name, the method will return the status code {@link kSTATUS_LOADED} or raise an
	 * exception if the file was not declared.
	 *
	 * @return string				Status code.
	 */
	public function LoadHouseholdDataset()
	{
		return $this->loadDataset( self::kDDICT_HOUSEHOLD_ID );						// ==>

	} // LoadHouseholdDataset.



/*=======================================================================================
 *																						*
 *							PUBLIC VALIDATION INTERFACE									*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	LoadChildDatasetHeader															*
	 *==================================================================================*/

	/**
	 * <h4>Load child dataset header.</h4>
	 *
	 * This method can be used to load the child dataset header into the data dictionary,
	 * the method will process the header row and check whether it contains duplicate
	 * values, in which case it will return the {@link kSTATUS_DUPLICATE_COLUMNS} status
	 * code; if there are no errors, the method will return the current status code.
	 *
	 * @return string				Status code.
	 */
	public function LoadChildDatasetHeader()
	{
		return $this->loadDatasetHeader( self::kDDICT_CHILD_ID );					// ==>

	} // LoadChildDatasetHeader.


	/*===================================================================================
	 *	LoadMotherDatasetHeader															*
	 *==================================================================================*/

	/**
	 * <h4>Load mother dataset header.</h4>
	 *
	 * This method can be used to load the mother dataset header into the data dictionary,
	 * the method will process the header row and check whether it contains duplicate
	 * values, in which case it will return the {@link kSTATUS_DUPLICATE_COLUMNS} status
	 * code; if there are no errors, the method will return the current status code.
	 *
	 * @return string				Status code.
	 */
	public function LoadMotherDatasetHeader()
	{
		return $this->loadDatasetHeader( self::kDDICT_MOTHER_ID );					// ==>

	} // LoadMotherDatasetHeader.


	/*===================================================================================
	 *	LoadHouseholdDatasetHeader														*
	 *==================================================================================*/

	/**
	 * <h4>Load household dataset header.</h4>
	 *
	 * This method can be used to load the household dataset header into the data
	 * dictionary, the method will process the header row and check whether it contains
	 * duplicate values, in which case it will return the {@link kSTATUS_DUPLICATE_COLUMNS}
	 * status code; if there are no errors, the method will return the current status code.
	 *
	 * @return string				Status code.
	 */
	public function LoadHouseholdDatasetHeader()
	{
		return $this->loadDatasetHeader( self::kDDICT_HOUSEHOLD_ID );				// ==>

	} // LoadHouseholdDatasetHeader.


	/*===================================================================================
	 *	LoadChildDatasetFields															*
	 *==================================================================================*/

	/**
	 * <h4>Load child dataset fields.</h4>
	 *
	 * This method can be used to load the child dataset fields into the data dictionary,
	 * the method will process the dataset columns and determine the data type of each
	 * column; if the columns have not yet been loaded, the method will raise an exception.
	 */
	public function LoadChildDatasetFields()
	{
		return $this->loadDatasetFields( self::kDDICT_CHILD_ID );					// ==>

	} // LoadChildDatasetFields.


	/*===================================================================================
	 *	LoadMotherDatasetFields															*
	 *==================================================================================*/

	/**
	 * <h4>Load mother dataset fields.</h4>
	 *
	 * This method can be used to load the mother dataset fields into the data dictionary,
	 * the method will process the dataset columns and determine the data type of each
	 * column; if the columns have not yet been loaded, the method will raise an exception.
	 */
	public function LoadMotherDatasetFields()
	{
		return $this->loadDatasetFields( self::kDDICT_MOTHER_ID );					// ==>

	} // LoadMotherDatasetFields.


	/*===================================================================================
	 *	LoadHouseholdDatasetFields														*
	 *==================================================================================*/

	/**
	 * <h4>Load household dataset fields.</h4>
	 *
	 * This method can be used to load the household dataset fields into the data
	 * the method will process the dataset columns and determine the data type of each
	 * column; if the columns have not yet been loaded, the method will raise an exception.
	 */
	public function LoadHouseholdDatasetFields()
	{
		return $this->loadDatasetFields( self::kDDICT_HOUSEHOLD_ID );				// ==>

	} // LoadHouseholdDatasetFields.



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
			'_id'						=> $theIdentifier,
			self::kDDICT_STATUS			=> self::kSTATUS_IDLE,
			self::kDDICT_COLUMNS		=> [],
			self::kDDICT_COLUMN_DUPS	=> [],
			self::kDDICT_FIELDS			=> []
		];																			// ==>

	} // newDataDictionary.



/*=======================================================================================
 *																						*
 *						PROTECTED DICTIONARY MEMBER ACCESSOR INTERFACE					*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	datasetStatus																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage dataset status.</h4>
	 *
	 * This method is used by the public interface to set or retrieve the dataset status
	 * code, the method expects three parameters:
	 *
	 * <ul>
	 * 	<li><b>$theDataset</b>: Dataset identifier:
	 * 	 <ul>
	 * 		<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 		<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 		<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * 	 </ul>
	 * 	<li><b>$theOperation</b>: Status operation. Since the status is a bitfield, the
	 * 		provided value may:
	 * 	 <ul>
	 * 		<li><tt>NULL<tt>: Set the status with the provided value (<tt>=</tt>).
	 * 		<li><tt>TRUE<tt>: Add the value to the existing status (<tt>|=</tt>).
	 * 		<li><tt>FALSE<tt>: Remove the value from the existing status (<tt>\&= ~</tt>).
	 * 	 </ul>
	 * 		By default the value is <tt>NULL</tt>, so that retrieving the status needs only
	 * 		the dataset selector.
	 * 	<li><b>$theValue</b>: Status or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL<tt>: Return current status; <em>in this case the previous parameter
	 * 			is ignored</em>.
	 * 		<li><i>int<i>: Set, add or reset status (depending on the previous parameter).
	 * 	 </ul>
	 * </ul>
	 *
	 * The method will return the current status, or raise an exception if the the selector
	 * is not correct.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @param mixed					$theOperation		Bitfield operation.
	 * @param string				$theValue			Dataset variable name.
	 * @return int					Current status.
	 *
	 * @throws InvalidArgumentException
	 */
	protected function datasetStatus( string $theDataset,
										  	 $theOperation = NULL,
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
			return
				$this->mDDICTInfo[ $theDataset ][ self::kDDICT_STATUS ];			// ==>

		//
		// Set new status.
		//
		if( $theOperation === NULL )
			$this->mDDICTInfo[ $theDataset ][ self::kDDICT_STATUS ]
				= $theValue;

		//
		// Add status.
		//
		elseif( $theOperation === TRUE )
			$this->mDDICTInfo[ $theDataset ][ self::kDDICT_STATUS ]
				|= $theValue;

		//
		// Reset status.
		//
		elseif( $theOperation === FALSE )
			$this->mDDICTInfo[ $theDataset ][ self::kDDICT_STATUS ]
				&= (~ $theValue);

		//
		// Invalid operation.
		//
		else
			throw new InvalidArgumentException(
				"Invalid bitfield operation." );								// !@! ==>

		return $this->mDDICTInfo[ $theDataset ][ self::kDDICT_STATUS ];				// ==>

	} // datasetStatus.


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
	 *	dictionaryList																	*
	 *==================================================================================*/

	/**
	 * <h4>Manage dictionary list.</h4>
	 *
	 * This method is used by the public interface to set or retrieve data dictionary list
	 * elements, the method expects three parameters:
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
	 * 		<li><tt>{@link kDDICT_COLUMNS}<tt>: Dataset header columns list.
	 * 		<li><tt>{@link kDDICT_FIELDS}<tt>: Data dictionary fields list.
	 * 	 </ul>
	 * 	<li><b>$theValue</b>: Offset or operation:
	 * 	 <ul>
	 * 		<li><tt>NULL<tt>: Return current value.
	 * 		<li><i>array<i>: New value.
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
	 * @return array				Current value.
	 *
	 * @throws InvalidArgumentException
	 */
	protected function dictionaryList( string $theDataset,
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
		{
			//
			// Assert data type.
			//
			if( ! is_array( $theValue ) )
				throw new InvalidArgumentException(
					"Invalid columns list data type." );						// !@! ==>

			//
			// Set value in data dictionary.
			//
			$this->mDDICTInfo[ $theDataset ][ $theOffset ] = $theValue;

		} // New value.

		return $this->mDDICTInfo[ $theDataset ][ $theOffset ];						// ==>

	} // dictionaryList.



/*=======================================================================================
 *																						*
 *							PROTECTED PROCESSING UTILITIES								*
 *																						*
 *======================================================================================*/



	/*===================================================================================
	 *	loadDataset																		*
	 *==================================================================================*/

	/**
	 * <h4>Load a dataset.</h4>
	 *
	 * This method is used by the public interface to load a dataset file, the method
	 * expects a single parameter that represents the dataset identifier:
	 *
	 * <ul>
	 * 	<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 	<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 	<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * </ul>
	 *
	 * The method will raise an exception if the the dataset file was not yet declared and
	 * will set and return the {@link kSTATUS_LOADED} status code.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @return int					Status code ({@link kSTATUS_LOADED}).
	 *
	 * @throws RuntimeException
	 */
	protected function loadDataset( string $theDataset )
	{
		//
		// Check dataset.
		//
		$path = $this->datasetPath( $theDataset );
		if( $path !== NULL )
		{
			//
			// Get original collection.
			//
			$collection = $this->originalCollection( $theDataset );
			$collection->drop();

			//
			// Load current worksheet.
			//
			$worksheet =
				PHPExcel_IOFactory::createReader(
					PHPExcel_IOFactory::identify( $path ) )
					->setReadDataOnly( TRUE )
					->load( $path )
					->getActiveSheet();

			//
			// Reset data dictionary.
			//
			$this->datasetStatus( $theDataset, NULL, self::kSTATUS_IDLE );
			$this->dictionaryList( $theDataset, self::kDDICT_FIELDS, [] );
			$this->dictionaryList( $theDataset, self::kDDICT_COLUMNS, [] );
			$this->dictionaryList( $theDataset, self::kDDICT_COLUMN_DUPS, [] );

			//
			// Iterate rows.
			//
			foreach( $worksheet->getRowIterator() as $row )
			{
				//
				// Init local storage.
				//
				$document = [ '_id' => $row->getRowIndex() ];

				//
				// Iterate columns.
				//
				foreach( $row->getCellIterator() as $cell )
					$document[ $cell->getColumn() ]
						= $cell->getValue();

				//
				// Save document.
				//
				$collection->insertOne( $document );

			} // Iterating rows.

			return
				$this->datasetStatus(
					$theDataset,
					NULL,
					self::kSTATUS_LOADED
				);																	// ==>

		} // Has dataset path.

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset file not yet defined." );									// !@! ==>

	} // loadDataset.


	/*===================================================================================
	 *	loadDatasetHeader																*
	 *==================================================================================*/

	/**
	 * <h4>Load dataset header.</h4>
	 *
	 * This method is used by the public interface to load the dataset header, the method
	 * expects a single parameter that represents the dataset identifier:
	 *
	 * <ul>
	 * 	<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 	<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 	<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * </ul>
	 *
	 * The method will raise an exception if the the dataset header row number was not yet
	 * declared and if the declared row cannot be found in the original collection.
	 *
	 * If the method encounters duplicate header values, the method will fill the header row
	 * in the data dictionary, but return the {@link kSTATUS_DUPLICATE_COLUMNS} status code.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @return int					Status code ({@link kSTATUS_LOADED}).
	 *
	 * @throws RuntimeException
	 */
	protected function loadDatasetHeader( string $theDataset )
	{
		//
		// Check header row.
		//
		$row = $this->datasetHeaderRow( $theDataset );
		if( $row !== NULL )
		{
			//
			// Load header row.
			//
			$document =
				$this->originalCollection( $theDataset )
					->findOne( [ '_id' => $row ] );
			if( $document !== NULL )
			{
				//
				// Init local storage.
				//
				$header = $errors = [];

				//
				// Iterate row.
				//
				foreach( $document as $column => $value )
				{
					//
					// Skip row number.
					//
					if( $column == '_id' )
						continue;												// =>

					//
					// Skip empty values.
					//
					$value = trim( $value );
					if( strlen( $value ) )
					{
						//
						// Check header.
						//
						$index = array_search( $value, $header );
						if( $index !== FALSE )
						{
							//
							// Set value.
							//
							if( ! in_array( $value, $errors ) )
								$errors[] = $value;

						} // Found duplicate.

						//
						// Set header.
						//
						$header[ $column ] = $value;

					} // Not empty.

				} // Iterating header row.

				//
				// Load columns and errors.
				//
				$this->dictionaryList( $theDataset, self::kDDICT_COLUMNS, $header );
				$this->dictionaryList( $theDataset, self::kDDICT_COLUMN_DUPS, $errors );

				//
				// Handle duplicates.
				//
				if( count( $errors ) )
					$this->datasetStatus(
						$theDataset,
						TRUE,
						self::kSTATUS_DUPLICATE_COLUMNS
					);

				return $this->datasetStatus( $theDataset );							// ==>

			} // Found row.

			//
			// Missing header row.
			//
			throw new RuntimeException(
				"Missing dataset header row [$row]." );							// !@! ==>

		} // Has header row.

		//
		// Check parameter.
		//
		throw new RuntimeException(
			"Dataset header row not yet defined." );							// !@! ==>

	} // loadDatasetHeader.


	/*===================================================================================
	 *	loadDatasetFields																*
	 *==================================================================================*/

	/**
	 * <h4>Load dataset fields.</h4>
	 *
	 * This method is used by the public interface to load the dataset fields, the method
	 * expects a single parameter that represents the dataset identifier:
	 *
	 * <ul>
	 * 	<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 	<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 	<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * </ul>
	 *
	 * The method will raise an exception if the the dataset columns were not yet loaded.
	 *
	 * The method will determine the data type of all columns and load the information in
	 * the {@link kDDICT_FIELDS} element of the data dictionary.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 *
	 * @throws RuntimeException
	 */
	protected function loadDatasetFields( string $theDataset )
	{
		//
		// Check data row.
		//
		$row = $this->datasetDataRow( $theDataset );
		if( $row !== NULL )
		{
			//
			// Check dataset columns.
			//
			$columns = $this->dictionaryList( $theDataset, self::kDDICT_COLUMNS );
			if( count( $columns  ) )
			{
				//
				// Init local storage.
				//
				$fields = [];

				//
				// Iterate columns.
				//
				foreach( $columns as $column => $field )
				{
					//
					// Handle distinct values.
					//
					$values =
						$this->originalCollection( $theDataset )
							->distinct(
								$column,
								[ '_id' => [ '$gt' => $row ],
								  $column => [ '$ne' => NULL ] ]
							);
					if( count( $values ) )
					{
						//
						// Init local storage.
						//
						$count = 0;
						$fields[ $field ] = [];
						$kind = $type = self::kTYPE_INTEGER;

						//
						// Iterate distinct values.
						//
						foreach( $values as $value )
						{
							//
							// Skip empty values.
							//
							$value = trim( $value );
							if( strlen( $value ) )
							{
								//
								// Handle number.
								//
								if( is_numeric( $value ) )
								{
									//
									// Check decimal.
									//
									$tmp = explode( '.', $value );
									if( count( $tmp ) > 1 )
									{
										//
										// Set kind.
										//
										$kind = self::kTYPE_NUMBER;

										//
										// Check decimal.
										//
										if( $tmp[ 1 ] != '0' )
											$kind = $type = self::kTYPE_DOUBLE;

									} // Has decimals.

								} // Is numeric.

								//
								// Handle string.
								//
								else
								{
									//
									// Must be string.
									//
									$kind = $type = self::kTYPE_STRING;

									break;										// =>

								} // Value is string.

								//
								// Increment distinct values count.
								//
								$count++;

							} // Not empty.

						} // Iterating distinct values.

						//
						// Set kind, type and distinct count.
						//
						$fields[ $field ][ self::kFIELD_KIND ] = $kind;
						$fields[ $field ][ self::kFIELD_TYPE ] = $type;
						$fields[ $field ][ self::kFIELD_DISTINCT ] = $count;

					} // Column has values.

				} // Iterating columns.

				//
				// Update data dictionary.
				//
				$this->dictionaryList( $theDataset, self::kDDICT_FIELDS, $fields );

			} // Has columns.

			//
			// Missing columns.
			//
			else
				throw new RuntimeException(
					"Dataset header columns not yet loaded." );					// !@! ==>

		} // Declared data row.

		//
		// Missing data row.
		//
		else
			throw new RuntimeException(
				"Dataset data row not yet declared." );							// !@! ==>

	} // loadDatasetFields.



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


	/*===================================================================================
	 *	originalCollection																*
	 *==================================================================================*/

	/**
	 * <h4>Get the original collection.</h4>
	 *
	 * This method will return the original dataset collection connection, the method
	 * expects a single parameter that represents the dataset identifier:
	 *
	 * <ul>
	 * 	<li><tt>{@link kDDICT_CHILD_ID}<tt>: Child dataset.
	 * 	<li><tt>{@link kDDICT_MOTHER_ID}<tt>: Mother dataset.
	 * 	<li><tt>{@link kDDICT_HOUSEHOLD_ID}<tt>: Household dataset.
	 * </ul>
	 *
	 * The method will raise an exception if the the dataset selector is invalid.
	 *
	 * @param string				$theDataset			Dataset identifier.
	 * @return Collection			Original collection.
	 *
	 * @throws InvalidArgumentException
	 */
	protected function originalCollection( string $theDataset )
	{
		//
		// Check dataset selector and set collection.
		//
		switch( $theDataset )
		{
			case self::kDDICT_CHILD_ID:
				return
					$this->Database()
						->selectCollection(
							self::kNAME_PREFIX_ORIGINAL . self::kNAME_CHILD
						);															// ==>

			case self::kDDICT_MOTHER_ID:
				return
					$this->Database()
						->selectCollection(
							self::kNAME_PREFIX_ORIGINAL . self::kNAME_MOTHER
						);															// ==>

			case self::kDDICT_HOUSEHOLD_ID:
				return
					$this->Database()
						->selectCollection(
							self::kNAME_PREFIX_ORIGINAL . self::kNAME_HOUSEHOLD
						);															// ==>
		}

		throw new InvalidArgumentException(
			"Invalid dataset selector [$theDataset]." );						// !@! ==>

	} // originalCollection.




} // class SMARTLoader.


?>
