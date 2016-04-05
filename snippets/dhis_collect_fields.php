<?php

//
// URL.
//
$url = "http://api.dhsprogram.com/rest/dhs/indicators?perpage=100&page=@@@";

//
// Init local storage.
//
$page = 1;
$fields = [];
$hierarchy = [];

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
				if( ! array_key_exists( $field, $fields ) )
					$fields[ $field ] = 0;
				$fields[ $field ]++;
			}
		}

		//
		// Load hierarchy.
		//
		if( ! array_key_exists( $row[ 'Level3' ], $hierarchy ) )
			$hierarchy[ $row[ 'Level3' ] ] = [];
		$level1 = & $hierarchy[ $row[ 'Level3' ] ];

		if( ! array_key_exists( $row[ 'Level2' ], $level1 ) )
			$level1[ $row[ 'Level2' ] ] = [];
		$level2 = & $level1[ $row[ 'Level2' ] ];

		if( ! in_array( $row[ 'Level1' ], $level2 ) )
			$level2[] = $row[ 'Level1' ];
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
print_r( $fields );

echo( "\n\n" );

echo( "Level 1: " );
print_r( $level1 );

echo( "\n\n" );

echo( "Level 2: " );
print_r( $level2 );

echo( "\n\n" );

echo( "Level 3: " );
print_r( $level3 );

exit;


//
// Load data.
//
$packet = json_decode( file_get_contents( $url ), TRUE );
print_r( $packet );
if( array_key_exists( 'Data', $packet ) )
{
	//
	// Reduce data.
	//
	$data = & $packet[ 'Data' ];

	//
	// Iterate records.
	//
	$rows = array_keys( $data );
	foreach( $rows as $row )
	{
		//
		// Init local storage.
		//
		$record = & $data[ $row ];

		//
		// Iterate record properties.
		//
		$keys = array_keys( $record );
		foreach( $keys as $key )
		{
			//
			// Ignore old identifier.
			//
			if( ! strlen( $record[ $key ] ) )
			{
				unset( $record[ $key ] );

				continue;												// =>

			} // Remove empty properties.

			//
			// Normalise variables.
			//
			switch( $key )
			{
				case 'ByLabels':
				case 'IndicatorOldId':
					unset( $record[ $key ] );
					break;

			} // Parsing variable.

			//
			// Convert tags to aray.
			//
			if( $key == 'TagIds' )
			{
				$list = [];
				$tags = explode( ',', $record[ $key ] );
				foreach( $tags as $tag )
				{
					$tag = trim( $tag );
					if( strlen( $tag ) )
						$list[] = $tag;

				} // Iterating tags.

				if( count( $list ) )
					$record[ $key ] = $list;

			} // Converted tags to array.

		} // Traversing record.

		//
		// Set unique identifier.
		//
		$record[ '_id' ] = $record[ 'IndicatorId' ];

	} // Iterating records.

} // Has data.

print_r( $packet );
//	var_dump( $data );

?>