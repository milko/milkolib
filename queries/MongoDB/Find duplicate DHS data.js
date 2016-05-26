db._data.aggregate(

  // Pipeline
  [
    // Stage 1
    {
      $group: {
      	_id: {
      	  "SurveyId": "$@47",
      	  "SurveyYear": "$@4b",
      	  "DHS_CountryCode": "$@10",
      	  "IndicatorId": "$@18",
      	  "CharacteristicCategory": "$@4",
      	  "CharacteristicLabel": "$@6",
      	  "RegionId": "$@3b",
      	  "Value": "$:value",
      	  "SDRID": "$@41",
      	  "DenominatorUnweighted": "$@e",
      	  "DenominatorWeighted": "$@f",
      	  "CIHigh": "$@8",
      	  "CILow": "$@9" },
      	"DataIds": { $addToSet: "$@b" },
      	"IDs": { $addToSet: "$_id" },
      	count: { $sum: 1 }
      }
    },

    // Stage 2
    {
      $match: {
      	count: { $gt: 1 }
      }
    },

    // Stage 3
    {
      $project: {
      	"count": "$count",
      	"SurveyId": "$_id.SurveyId",
      	"SurveyYear": "$_id.SurveyYear",
      	"DHS_CountryCode": "$_id.DHS_CountryCode",
      	"IndicatorId": "$_id.IndicatorId",
      	"CharacteristicCategory": "$_id.CharacteristicCategory",
      	"CharacteristicLabel": "$_id.CharacteristicLabel",
      	"RegionId": "$_id.RegionId",
      	"Value": "$_id.Value",
      	"SDRID": "$_id.SDRID",
      	"DenominatorUnweighted": "$_id.DenominatorUnweighted",
      	"DenominatorWeighted": "$_id.DenominatorWeighted",
      	"CIHigh": "$_id.CIHigh",
      	"CILow": "$_id.CILow",
      	"DataIds": true,
      	"IDs": true
      }
    },

    // Stage 4
    {
      $sort: {
      	"count": -1
      }
    },

    // Stage 5
    {
      $out: "checks_PossibleDuplicates"
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
