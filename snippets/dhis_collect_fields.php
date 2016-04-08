<?php

//
// URL.
//
$url = "http://api.dhsprogram.com/rest/dhs/indicators?perpage=100&page=@@@";

//
// Init local storage.
//
$page = 1;
$hierarchy = [];
$occurrence = [];
$denominators = [];
$categories = [ "Level 1" => [], "Level 2" => [], "Level 3" => [] ];

//
// Iterate indicators.
//
$request = str_replace( '@@@', $page++, $url );
$packet = json_decode( file_get_contents( $request ), TRUE );
while( count( $packet[ 'Data' ] ) )
{
	//
	// Collect fields.
	//
	$data = & $packet[ 'Data' ];
	foreach( $data as $row )
	{
		//
		// Iterate fields.
		//
		foreach( $row as $field => $value )
		{
			//
			// Handle existing fields.
			//
			if( strlen( $value ) )
			{
				//
				// Collect field occurrance.
				//
				if( ! array_key_exists( $field, $occurrence ) )
					$occurrence[ $field ] = 0;
				$occurrence[ $field ]++;

				//
				// Load categories.
				//
				for( $i = 1; $i <= 3; $i++ )
				{
					if( $field == "Level$i" )
					{
						if( ! in_array( $value, $categories[ "Level $i" ] ) )
							$categories[ "Level $i" ][] = $value;
					}
				}

				//
				// Collect denominator.
				//
				if( $field == 'Denominator' )
				{
					if( ! in_array( $value, $denominators ) )
						$denominators[] = $value;
				}
			}

			//
			// Load hierarchy.
			//
//			if( ! array_key_exists( $row[ "Level3" ], $hierarchy ) )
//				$hierarchy[ $row[ "Level3" ] ] = [];
//			$level1 = & $hierarchy[ $row[ "Level3" ] ];
//			if( ! array_key_exists( $row[ "Level1" ], $level1 ) )
//				$level1[ $row[ "Level1" ] ] = [];
//			$level2 = & $level1[ $row[ "Level1" ] ];
//			if( ! in_array( $row[ "Level2" ], $level2 ) )
//				$level2[] = $row[ "Level2" ];

			if( ! array_key_exists( $row[ "Level3" ], $hierarchy ) )
				$hierarchy[ $row[ "Level3" ] ] = [];
			$level1 = & $hierarchy[ $row[ "Level3" ] ];
			if( ! array_key_exists( $row[ "Level1" ], $level1 ) )
				$level1[ $row[ "Level1" ] ] = [];
			$level2 = & $level1[ $row[ "Level1" ] ];
			if( strlen( $row[ "Denominator" ] ) )
			{
				if( ! array_key_exists( $row[ "Level2" ], $level2 ) )
					$level2[ $row[ "Level2" ] ] = [];
				$level3 = & $level2[ $row[ "Level2" ] ];
				if( ! in_array( $row[ "Denominator" ], $level3 ) )
					$level3[] = $row[ "Denominator" ];
			}
			else
			{
				if( ! in_array( $row[ "Level2" ], $level2 ) )
					$level2[] = $row[ "Level2" ];
			}
		}
	}

	//
	// Get next.
	//
	$request = str_replace( '@@@', $page++, $url );
	$packet = json_decode( file_get_contents( $request ), TRUE );
	echo( '.' );

} // Iterating indicators.

echo( "\n\n" );

echo( "Field occurrance: " );
print_r( $occurrence );

echo( "\n\n" );

echo( "Denominators: " );
print_r( $denominators );

echo( "\n\n" );

echo( "Categories: " );
print_r( $categories );

echo( "\n\n" );

echo( "Hierarchy: " );
print_r( $hierarchy );

?>