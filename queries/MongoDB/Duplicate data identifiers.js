db._data.aggregate(

  // Pipeline
  [
    // Stage 1
    {
      $group: {
      	_id: "$@b",
      	"count": { $sum: 1 }
      }
    },

    // Stage 2
    {
      $match: {
      	"count": { $gt: 1 }
      }
    },

    // Stage 3
    {
      $out: "checks_DuplicateDataIdentifiers"
    }
  ],

  // Options
  {
    cursor: {
      batchSize: 50
    },

    allowDiskUse: true
  }

  // Created with 3T MongoChef, the GUI for MongoDB - http://3t.io/mongochef

);
