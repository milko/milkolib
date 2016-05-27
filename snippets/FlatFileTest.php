<?php

/**
 * FlatFileTest.php
 *
 * This file contains a test script for parsing flat file datasets.
 *
 * The script expects a single argument corresponding to the directory path where the data
 * files are stored. The directory should only contain files related to a single dataset,
 * since the script will differentiate files by their extension.
 */

/*=======================================================================================
 *																						*
 *										MAIN											*
 *																						*
 *======================================================================================*/

//
// Init globals.
//
$file_DCF = $file_DCT = $file_DAT = NULL;

//
// Parse arguments.
//
if( $argc < 2 )
	exit( "USAGE: php -f FlatFileTest.php <dataset files directory>\n" );			// ==>

//
// Get data files.
//
$directory = new DirectoryIterator( $argv[ 1 ] );
foreach( $directory as $file )
{
	if( ! $file->isDot() )
	{
		switch( strtolower( $file->getExtension() ) )
		{
			case 'dcf':
				$file_DCF = new SplFileObject( $file->getRealPath() );
				break;

			case 'dct':
				$file_DCT = new SplFileObject( $file->getRealPath() );
				break;

			case 'dat':
				$file_DAT = new SplFileObject( $file->getRealPath() );
				break;
		}
	}
}

//
// Check data files.
//
if( $file_DCF === NULL )
	throw new RuntimeException( "Missing DCF file." );							// !@! ==>
if( $file_DCT === NULL )
	throw new RuntimeException( "Missing DCT file." );							// !@! ==>
if( $file_DAT === NULL )
	throw new RuntimeException( "Missing DAT file." );							// !@! ==>

//
// Init data dictionary.
//
$ddict = [];
$ddict[ 'country' ] = substr( $file_DCF->getBasename( '.DCF' ), 0, 2 );
$ddict[ 'type' ] = substr( $file_DCF->getBasename( '.DCF' ), 2, 2 );
$ddict[ 'phase' ] = substr( $file_DCF->getBasename( '.DCF' ), 4, 1 );
$ddict[ 'release' ] = substr( $file_DCF->getBasename( '.DCF' ), 5, 1 );

//
// Parse dictionary files.
//
LoadDCT( $file_DCT, $ddict );
LoadDCF( $file_DCF, $ddict );

//
// Get data types.
//
$types = [];
foreach( $ddict[ 'dict' ] as $var )
{
	if( ! in_array( $var[ 'type' ], $types ) )
		$types[] = $var[ 'type' ];
}

print_r( $types );


/*=======================================================================================
 *																						*
 *										FUNCTIONS										*
 *																						*
 *======================================================================================*/



/*===================================================================================
 *	LoadDCT																			*
 *==================================================================================*/

/**
 * <h4>Load DCT file.</h4>
 *
 * This function will load the DCT file information, it expects the following parameters:
 *
 * <ul>
 * 	<li><b>$theFile</b>: The DCT file object.
 * 	<li><b>&$theDictionary</b>: A reference to the array that will receive the data
 * 		dictionary.
 * </ul>
 *
 * @param SplFileObject			$theFile			DCT file.
 * @param array				   &$theDictionary		Receives data dictionary.
 * @throws RuntimeException
 */
function LoadDCT( SplFileObject $theFile, array &$theDictionary )
{
	//
	// Get lines count.
	//
	while( ! $theFile->eof() )
	{
		//
		// Read line.
		//
		$line = $theFile->fgets();

		//
		// Match lines line.
		//
		if( preg_match( '/[0-9] lines/', $line ) )
		{
			$theDictionary[ 'lines' ] = (int)explode( ' ', $line )[ 0 ];
			break;
		}
	}

	//
	// Collect data dictionary.
	//
	$theDictionary[ 'dict' ] = [];
	$dict = & $theDictionary[ 'dict' ];
	while( ! $theFile->eof() )
	{
		//
		// Read line.
		//
		$line = $theFile->fgets();

		//
		// Exit on end.
		//
		if( preg_match( '/\}/', $line ) )
			break;

		//
		// Get data type, name, line and position.
		//
		if( ! preg_match_all( '/\w+/', $line, $matches ) )
			throw new RuntimeException( "Invalid line format: [$line]" );			// !@! ==>
		if( count( $matches[ 0 ] ) != 5 )
			throw new RuntimeException( "Invalid line format: [$line]" );			// !@! ==>

		//
		// Set data dictionary entry.
		//
		$dict[ $matches[ 0 ][ 1 ] ] = [
			'type' => $matches[ 0 ][ 0 ],
			'line' => $matches[ 0 ][ 2 ],
			'start' => $matches[ 0 ][ 3 ],
			'end' => $matches[ 0 ][ 4 ],
			'size' => ($matches[ 0 ][ 4 ] - $matches[ 0 ][ 3 ]) + 1
		];
	}

} // LoadDCT.


/*===================================================================================
 *	LoadDCF																			*
 *==================================================================================*/

/**
 * <h4>Load DCF file.</h4>
 *
 * This function will load the DCF file information, it expects the following parameters:
 *
 * <ul>
 * 	<li><b>$theFile</b>: The DCF file object.
 * 	<li><b>&$theDictionary</b>: A reference to the array that will receive the data
 * 		dictionary.
 * </ul>
 *
 * @param SplFileObject			$theFile			DCF file.
 * @param array				   &$theDictionary		Receives data dictionary.
 * @throws RuntimeException
 */
function LoadDCF( SplFileObject $theFile, array &$theDictionary )
{
	//
	// Get level.
	//
	while( ! $theFile->eof() )
	{
		//
		// Read line.
		//
		$line = $theFile->fgets();

		//
		// Match level line.
		//
		if( preg_match( '/\[Level\]/', $line ) )
		{
			//
			// Get level.
			//
			while( ! $theFile->eof() )
			{
				//
				// Read line.
				//
				$line = $theFile->fgets();

				//
				// Match level line.
				//
				if( preg_match( '/Name=/', $line ) )
				{
					$theDictionary[ 'level' ] = explode( '=', $line )[ 1 ];
					break;
				}
			}
			break;
		}
	}

	//
	// Load information blocks.
	//
	$block = FindDCFBlock( $theFile );
	while( $block !== NULL )
	{
		//
		// Parse item.
		//
		if( $block == 'Item' )
		{
			$data = ParseItemBlock( $theFile );
			$name = $data[ 'Name' ];
			$dict = & $theDictionary[ 'dict' ];
			$descr = strtolower( $name );
			if( array_key_exists( $descr, $dict ) )
			{
				$dict[ $descr ][ 'name' ] = $name;
				if( array_key_exists( 'Label', $data ) )
					$dict[ $descr ][ 'label' ] = $data[ 'Label' ];
				else
					$dict[ $descr ][ 'label' ] = $name;
				$dict[ $descr ][ 'size' ] = $data[ 'Len' ];
			}
			else
				var_dump( $descr );
		}

		//
		// Next.
		//
		$block = FindDCFBlock( $theFile );

		//
		// Parse ValueSet.
		//
		if( $block == 'ValueSet' )
		{
			//
			// Parse value set.
			//
			$data = ParseValueSetBlock( $theFile );
			if( ($data !== NULL)
			 && count( $data ) )
				$dict[ $descr ][ 'enum' ] = $data;

			//
			// Next.
			//
			$block = FindDCFBlock( $theFile );
		}
	}

} // LoadDCF.


/*===================================================================================
 *	FindDCFBlock																	*
 *==================================================================================*/

/**
 * <h4>Find DCF block.</h4>
 *
 * This function will iterate the provided file until it finds a <tt>[Item]</tt> or
 * <tt>[ValueSet]</tt> block and will return the block name; it it reaches the end of file,
 * it will return <tt>NULL</tt>.
 *
 * @param SplFileObject			$theFile			DCF file.
 * @return string				The block name.
 * @throws RuntimeException
 */
function FindDCFBlock( SplFileObject $theFile )
{
	//
	// Iterate file.
	//
	while( ! $theFile->eof() )
	{
		//
		// Read line.
		//
		$line = $theFile->fgets();

		//
		// Match level line.
		//
		if( preg_match( '/\[(.+)\]/', $line, $matches ) )
		{
			switch( $matches[ 1 ] )
			{
				case 'Item':
				case 'ValueSet':
					return $matches[ 1 ];											// ==>
			}
		}
	}

	return NULL;																	// ==>

} // FindDCFBlock.


/*===================================================================================
 *	ParseItemBlock																	*
 *==================================================================================*/

/**
 * <h4>Parse item block.</h4>
 *
 * This function expects the file cursor to be after an <tt>[Item]</tt> line and will parse
 * and return the block data formatted as an array; if reached the end of file, the function
 * will return <tt>NULL</tt>.
 *
 * @param SplFileObject			$theFile			DCF file.
 * @return array				The block data.
 * @throws RuntimeException
 */
function ParseItemBlock( SplFileObject $theFile )
{
	//
	// Iterate file.
	//
	$data = [];
	while( ! $theFile->eof() )
	{
		//
		// Read line.
		//
		$line = $theFile->fgets();

		//
		// Match level line.
		//
		if( preg_match( '/(.+)=(.+)/', $line, $matches ) )
			$data[ $matches[ 1 ] ] = substr( $matches[ 2 ], 0, strlen( $matches[ 2 ] ) - 1 );
		else
			return $data;															// ==>
	}

	return NULL;																	// ==>

} // ParseItemBlock.


/*===================================================================================
 *	ParseValueSetBlock																*
 *==================================================================================*/

/**
 * <h4>Parse item block.</h4>
 *
 * This function expects the file cursor to be after an <tt>[ValueSet]</tt> line and will
 * parse and return the block data formatted as an array; if reached the end of file, the
 * function will return <tt>NULL</tt>.
 *
 * @param SplFileObject			$theFile			DCF file.
 * @return array				The block data.
 * @throws RuntimeException
 */
function ParseValueSetBlock( SplFileObject $theFile )
{
	//
	// Iterate file.
	//
	$name = NULL;
	$data = $values = [];
	while( ! $theFile->eof() )
	{
		//
		// Read line.
		//
		$line = $theFile->fgets();

		//
		// Match level line.
		//
		if( preg_match( '/(.+)=(.+)/', $line, $matches ) )
		{
			//
			// Get key and value.
			//
			$key = $matches[ 1 ];
			$value = substr( $matches[ 2 ], 0, strlen( $matches[ 2 ] ) - 1 );

			//
			// Parse by key.
			//
			switch( $key )
			{
				case 'Name':
					if( $name === NULL )
					{
						$name = $value;
						$data[ 'name' ] = $name;
					}
				break;

				case 'Value':
					if( strpos( $value, ';' ) !== FALSE )
					{
						$tmp = explode( ';', $value );
						$tmp[ 0 ] = str_replace( "'", '', $tmp[ 0 ] );
						$tmp[ 0 ] = trim( $tmp[ 0 ] );
						if( strlen( $tmp[ 0 ] ) )
							$values[ $tmp[ 0 ] ] = $tmp[ 1 ];
					}
					break;
			}
		}
		else
		{
			if( count( $values ) )
			{
				$data[ 'enums' ] = $values;
				return $data;														// ==>
			}

			return [];																// ==>
		}
	}

	return NULL;																	// ==>

} // ParseValueSetBlock.

?>

