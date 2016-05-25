db._data.aggregate(

  // Pipeline
  [
    // Stage 1
    {
      $group: {
      	"_id" : "$@4",
      	"count" : { $sum : 1 }
      }
    },

    // Stage 2
    {
      $sort: {
      	"_id" : 1
      }
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
