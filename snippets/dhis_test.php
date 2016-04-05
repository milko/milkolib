<?php

//
// URL.
//
$url = "http://api.dhsprogram.com/rest/dhs/indicators?perpage=10&page=1";

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