
var ApiGen = ApiGen || {};
ApiGen.elements = [["c","ArrayAccess"],["c","ArrayObject"],["c","BadFunctionCallException"],["c","BadMethodCallException"],["c","Composer\\Autoload\\ClassLoader"],["c","ComposerAutoloaderInitcea8eb84f11c06ea11353c3abbc33142"],["f","composerRequirecea8eb84f11c06ea11353c3abbc33142()"],["c","Countable"],["f","dumpCollection()"],["f","dumpWriteResults()"],["c","Exception"],["f","getConnection()"],["f","getConnectionOptions()"],["f","includeFile()"],["c","InvalidArgumentException"],["f","isCluster()"],["c","Iterator"],["c","IteratorAggregate"],["c","LogicException"],["c","Milko\\PHPLib\\ArangoDB\\Collection"],["c","Milko\\PHPLib\\ArangoDB\\Database"],["c","Milko\\PHPLib\\ArangoDB\\DataServer"],["c","Milko\\PHPLib\\Collection"],["c","Milko\\PHPLib\\Container"],["c","Milko\\PHPLib\\Database"],["c","Milko\\PHPLib\\DataServer"],["c","Milko\\PHPLib\\DataSource"],["c","Milko\\PHPLib\\MongoDB\\Collection"],["c","Milko\\PHPLib\\MongoDB\\Database"],["c","Milko\\PHPLib\\MongoDB\\DataServer"],["c","Milko\\PHPLib\\Server"],["c","MongoDB\\BSON\\Serializable"],["c","MongoDB\\BSON\\Type"],["c","MongoDB\\BSON\\Unserializable"],["c","MongoDB\\BulkWriteResult"],["c","MongoDB\\Client"],["c","MongoDB\\Collection"],["c","MongoDB\\Database"],["c","MongoDB\\DeleteResult"],["c","MongoDB\\Driver\\Cursor"],["c","MongoDB\\Driver\\Exception\\Exception"],["c","MongoDB\\Driver\\Exception\\InvalidArgumentException"],["c","MongoDB\\Driver\\Exception\\RuntimeException"],["c","MongoDB\\Driver\\Exception\\UnexpectedValueException"],["c","MongoDB\\Driver\\Manager"],["c","MongoDB\\Driver\\ReadConcern"],["c","MongoDB\\Driver\\ReadPreference"],["c","MongoDB\\Driver\\Server"],["c","MongoDB\\Driver\\WriteResult"],["c","MongoDB\\Exception\\BadMethodCallException"],["c","MongoDB\\Exception\\Exception"],["c","MongoDB\\Exception\\InvalidArgumentException"],["c","MongoDB\\Exception\\RuntimeException"],["c","MongoDB\\Exception\\UnexpectedValueException"],["c","MongoDB\\InsertManyResult"],["c","MongoDB\\InsertOneResult"],["c","MongoDB\\Model\\BSONArray"],["c","MongoDB\\Model\\BSONDocument"],["c","MongoDB\\Model\\CollectionInfo"],["c","MongoDB\\Model\\CollectionInfoIterator"],["c","MongoDB\\Model\\DatabaseInfo"],["c","MongoDB\\Model\\DatabaseInfoIterator"],["c","MongoDB\\Model\\IndexInfo"],["c","MongoDB\\Model\\IndexInfoIterator"],["c","MongoDB\\Operation\\Aggregate"],["c","MongoDB\\Operation\\BulkWrite"],["c","MongoDB\\Operation\\Count"],["c","MongoDB\\Operation\\CreateCollection"],["c","MongoDB\\Operation\\CreateIndexes"],["c","MongoDB\\Operation\\DatabaseCommand"],["c","MongoDB\\Operation\\DeleteMany"],["c","MongoDB\\Operation\\DeleteOne"],["c","MongoDB\\Operation\\Distinct"],["c","MongoDB\\Operation\\DropCollection"],["c","MongoDB\\Operation\\DropDatabase"],["c","MongoDB\\Operation\\DropIndexes"],["c","MongoDB\\Operation\\Find"],["c","MongoDB\\Operation\\FindOne"],["c","MongoDB\\Operation\\FindOneAndDelete"],["c","MongoDB\\Operation\\FindOneAndReplace"],["c","MongoDB\\Operation\\FindOneAndUpdate"],["c","MongoDB\\Operation\\InsertMany"],["c","MongoDB\\Operation\\InsertOne"],["c","MongoDB\\Operation\\ListCollections"],["c","MongoDB\\Operation\\ListDatabases"],["c","MongoDB\\Operation\\ListIndexes"],["c","MongoDB\\Operation\\ReplaceOne"],["c","MongoDB\\Operation\\UpdateMany"],["c","MongoDB\\Operation\\UpdateOne"],["c","MongoDB\\Tests\\BSONArrayTest"],["c","MongoDB\\Tests\\BSONDocumentTest"],["c","MongoDB\\Tests\\ClientFunctionalTest"],["c","MongoDB\\Tests\\ClientTest"],["c","MongoDB\\Tests\\Collection\\BulkWriteFunctionalTest"],["c","MongoDB\\Tests\\Collection\\CollectionFunctionalTest"],["c","MongoDB\\Tests\\Collection\\CrudSpec\\AggregateFunctionalTest"],["c","MongoDB\\Tests\\Collection\\CrudSpec\\CountFunctionalTest"],["c","MongoDB\\Tests\\Collection\\CrudSpec\\DeleteManyFunctionalTest"],["c","MongoDB\\Tests\\Collection\\CrudSpec\\DeleteOneFunctionalTest"],["c","MongoDB\\Tests\\Collection\\CrudSpec\\DistinctFunctionalTest"],["c","MongoDB\\Tests\\Collection\\CrudSpec\\FindFunctionalTest"],["c","MongoDB\\Tests\\Collection\\CrudSpec\\FindOneAndDeleteFunctionalTest"],["c","MongoDB\\Tests\\Collection\\CrudSpec\\FindOneAndReplaceFunctionalTest"],["c","MongoDB\\Tests\\Collection\\CrudSpec\\FindOneAndUpdateFunctionalTest"],["c","MongoDB\\Tests\\Collection\\CrudSpec\\FunctionalTestCase"],["c","MongoDB\\Tests\\Collection\\CrudSpec\\InsertManyFunctionalTest"],["c","MongoDB\\Tests\\Collection\\CrudSpec\\InsertOneFunctionalTest"],["c","MongoDB\\Tests\\Collection\\CrudSpec\\ReplaceOneFunctionalTest"],["c","MongoDB\\Tests\\Collection\\CrudSpec\\UpdateManyFunctionalTest"],["c","MongoDB\\Tests\\Collection\\CrudSpec\\UpdateOneFunctionalTest"],["c","MongoDB\\Tests\\Collection\\DeleteFunctionalTest"],["c","MongoDB\\Tests\\Collection\\FunctionalTestCase"],["c","MongoDB\\Tests\\Collection\\InsertManyFunctionalTest"],["c","MongoDB\\Tests\\Collection\\InsertOneFunctionalTest"],["c","MongoDB\\Tests\\Collection\\UpdateFunctionalTest"],["c","MongoDB\\Tests\\CollectionInfoTest"],["c","MongoDB\\Tests\\Database\\CollectionManagementFunctionalTest"],["c","MongoDB\\Tests\\Database\\DatabaseFunctionalTest"],["c","MongoDB\\Tests\\Database\\FunctionalTestCase"],["c","MongoDB\\Tests\\DatabaseInfoTest"],["c","MongoDB\\Tests\\FunctionalTestCase"],["c","MongoDB\\Tests\\FunctionsTest"],["c","MongoDB\\Tests\\IndexInfoTest"],["c","MongoDB\\Tests\\IndexInputTest"],["c","MongoDB\\Tests\\Operation\\AggregateFunctionalTest"],["c","MongoDB\\Tests\\Operation\\AggregateTest"],["c","MongoDB\\Tests\\Operation\\BulkWriteTest"],["c","MongoDB\\Tests\\Operation\\CountTest"],["c","MongoDB\\Tests\\Operation\\CreateCollectionTest"],["c","MongoDB\\Tests\\Operation\\CreateIndexesFunctionalTest"],["c","MongoDB\\Tests\\Operation\\CreateIndexesTest"],["c","MongoDB\\Tests\\Operation\\DatabaseCommandTest"],["c","MongoDB\\Tests\\Operation\\DeleteTest"],["c","MongoDB\\Tests\\Operation\\DistinctTest"],["c","MongoDB\\Tests\\Operation\\DropCollectionFunctionalTest"],["c","MongoDB\\Tests\\Operation\\DropCollectionTest"],["c","MongoDB\\Tests\\Operation\\DropDatabaseFunctionalTest"],["c","MongoDB\\Tests\\Operation\\DropDatabaseTest"],["c","MongoDB\\Tests\\Operation\\DropIndexesFunctionalTest"],["c","MongoDB\\Tests\\Operation\\DropIndexesTest"],["c","MongoDB\\Tests\\Operation\\FindAndModifyTest"],["c","MongoDB\\Tests\\Operation\\FindFunctionalTest"],["c","MongoDB\\Tests\\Operation\\FindOneAndDeleteTest"],["c","MongoDB\\Tests\\Operation\\FindOneAndReplaceTest"],["c","MongoDB\\Tests\\Operation\\FindOneAndUpdateTest"],["c","MongoDB\\Tests\\Operation\\FindOneFunctionalTest"],["c","MongoDB\\Tests\\Operation\\FindTest"],["c","MongoDB\\Tests\\Operation\\FunctionalTestCase"],["c","MongoDB\\Tests\\Operation\\InsertManyTest"],["c","MongoDB\\Tests\\Operation\\InsertOneTest"],["c","MongoDB\\Tests\\Operation\\ListCollectionsFunctionalTest"],["c","MongoDB\\Tests\\Operation\\ListDatabasesTest"],["c","MongoDB\\Tests\\Operation\\ListIndexesFunctionalTest"],["c","MongoDB\\Tests\\Operation\\ListIndexesTest"],["c","MongoDB\\Tests\\Operation\\ReplaceOneTest"],["c","MongoDB\\Tests\\Operation\\TestCase"],["c","MongoDB\\Tests\\Operation\\UpdateManyTest"],["c","MongoDB\\Tests\\Operation\\UpdateOneTest"],["c","MongoDB\\Tests\\Operation\\UpdateTest"],["c","MongoDB\\Tests\\PedantryTest"],["c","MongoDB\\Tests\\TestCase"],["c","MongoDB\\UpdateResult"],["c","RuntimeException"],["c","Serializable"],["c","stdClass"],["c","Throwable"],["c","Traversable"],["c","triagens\\ArangoDb\\AdminHandler"],["c","triagens\\ArangoDb\\AdminTest"],["c","triagens\\ArangoDb\\AqlUserFunction"],["c","triagens\\ArangoDb\\AqlUserFunctionTest"],["c","triagens\\ArangoDb\\Autoloader"],["c","triagens\\ArangoDb\\Batch"],["c","triagens\\ArangoDb\\BatchPart"],["c","triagens\\ArangoDb\\BatchTest"],["c","triagens\\ArangoDb\\BindVars"],["c","triagens\\ArangoDb\\ClientException"],["c","triagens\\ArangoDb\\Collection"],["c","triagens\\ArangoDb\\CollectionBasicTest"],["c","triagens\\ArangoDb\\CollectionExtendedTest"],["c","triagens\\ArangoDb\\CollectionHandler"],["c","triagens\\ArangoDb\\ConnectException"],["c","triagens\\ArangoDb\\Connection"],["c","triagens\\ArangoDb\\ConnectionOptions"],["c","triagens\\ArangoDb\\ConnectionTest"],["c","triagens\\ArangoDb\\Cursor"],["c","triagens\\ArangoDb\\Database"],["c","triagens\\ArangoDb\\DatabaseTest"],["c","triagens\\ArangoDb\\DefaultValues"],["c","triagens\\ArangoDb\\Document"],["c","triagens\\ArangoDb\\DocumentBasicTest"],["c","triagens\\ArangoDb\\DocumentExtendedTest"],["c","triagens\\ArangoDb\\DocumentHandler"],["c","triagens\\ArangoDb\\Edge"],["c","triagens\\ArangoDb\\EdgeBasicTest"],["c","triagens\\ArangoDb\\EdgeDefinition"],["c","triagens\\ArangoDb\\EdgeExtendedTest"],["c","triagens\\ArangoDb\\EdgeHandler"],["c","triagens\\ArangoDb\\Endpoint"],["c","triagens\\ArangoDb\\Exception"],["c","triagens\\ArangoDb\\Export"],["c","triagens\\ArangoDb\\ExportCursor"],["c","triagens\\ArangoDb\\ExportTest"],["c","triagens\\ArangoDb\\GeneralGraphExtendedTest"],["c","triagens\\ArangoDb\\Graph"],["c","triagens\\ArangoDb\\GraphBasicTest"],["c","triagens\\ArangoDb\\GraphExtendedTest"],["c","triagens\\ArangoDb\\GraphHandler"],["c","triagens\\ArangoDb\\Handler"],["c","triagens\\ArangoDb\\HttpHelper"],["c","triagens\\ArangoDb\\HttpResponse"],["c","triagens\\ArangoDb\\QueryCacheHandler"],["c","triagens\\ArangoDb\\QueryCacheTest"],["c","triagens\\ArangoDb\\QueryHandler"],["c","triagens\\ArangoDb\\QueryTest"],["c","triagens\\ArangoDb\\QueueTest"],["c","triagens\\ArangoDb\\ServerException"],["c","triagens\\ArangoDb\\Statement"],["c","triagens\\ArangoDb\\StatementTest"],["c","triagens\\ArangoDb\\TraceRequest"],["c","triagens\\ArangoDb\\TraceResponse"],["c","triagens\\ArangoDb\\Transaction"],["c","triagens\\ArangoDb\\TransactionTest"],["c","triagens\\ArangoDb\\Traversal"],["c","triagens\\ArangoDb\\TraversalTest"],["c","triagens\\ArangoDb\\UpdatePolicy"],["c","triagens\\ArangoDb\\UrlHelper"],["c","triagens\\ArangoDb\\Urls"],["c","triagens\\ArangoDb\\User"],["c","triagens\\ArangoDb\\UserBasicTest"],["c","triagens\\ArangoDb\\UserHandler"],["c","triagens\\ArangoDb\\ValueValidator"],["c","triagens\\ArangoDb\\Vertex"],["c","triagens\\ArangoDb\\VertexHandler"],["c","UnexpectedValueException"]];
