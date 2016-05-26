db._data.aggregate(

  // Pipeline
  [
    // Stage 1
    {
      $match: {
      	"@3b": { $exists: true }
      }
    },

    // Stage 2
    {
      $project: {
      	"DHS_CountryCode": "$@10",
      	"CharacteristicLabel": "$@6"
      }
    },

    // Stage 3
    {
      $group: {
      	_id: "$DHS_CountryCode",
      	"regions": { $addToSet: "$CharacteristicLabel" }
      }
    },

    // Stage 4
    {
      $sort: {
      	"regions.region": 1
      }
    },

    // Stage 5
    {
      $out: "groups_CountryRegions"
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
