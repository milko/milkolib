<?php

/**
 * Test suite functions.
 *
 *	@author		Milko A. Škofič <skofic@gmail.com>
 *	@version	1.00
 *	@since		06/02/2016
 */

//
// Display value and type.
//
function dumpValue( $theValue )
{
	//
	// Handle classes.
	//
	if( is_object( $theValue ) )
		return get_class( $theValue );

	//
	// Handle NULL.
	//
	if( $theValue === NULL )
		return "NULL";

	//
	// Handle FALSE.
	//
	if( $theValue === FALSE )
		return "FALSE";

	//
	// Handle TRUE.
	//
	if( $theValue === TRUE )
		return "TRUE";

	//
	// Handle other types.
	//
	return '(' . gettype( $theValue ) . ') [' . $theValue . ']';
}

?>

