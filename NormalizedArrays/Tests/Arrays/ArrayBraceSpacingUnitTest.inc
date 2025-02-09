<?php

// Not (short) array.
$array['access'] = $foo;
[$a, $b] = $array;

/*
 * Keyword spacing
 */
$foo = array();
$foo = array (); // Error.
$foo = array /*comment*/ (); // Error (non-fixable).
$foo = array
	   (); // Error.

// Test incorrect setting of property.
// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing keywordSpacing -10
$foo = array    (); // Error for no spaces.

// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing keywordSpacing 1
$foo = array ();
$foo = array(); // Error.
$foo = array
	   (); // Error.
$foo = array    (); // Error.

// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing keywordSpacing false
$foo = array ();
$foo = array();
$foo = array
	   ();
$foo = array    ();

// Reset to default.
// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing keywordSpacing 0

/*
 * Empty arrays.
 */
$foo = array();
$foo = [];

$foo = array(  ); // Error.
$foo = [

]; // Error.

// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing spacesWhenEmpty 1
$foo = array( );
$foo = [ ];

$foo = array(); // Error.
$foo = []; // Error.
$foo = array(

); // Error.
$foo = [      ]; // Error.

// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing spacesWhenEmpty newline
$foo = array(
);
$foo = [
];

$foo = array(); // Error.
$foo = []; // Error.
$foo = array(     ); // Error.
$foo = [ ]; // Error.
$foo = array(


); // Error.

// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing spacesWhenEmpty false
$foo = array();
$foo = [];
$foo = array(     );
$foo = [
];

// Reset to default.
// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing spacesWhenEmpty 0

/*
 * Non-empty single-line arrays.
 */

$foo = array($a, $b);
$foo = [$a, $b];
$foo = [/*comment*/];

$foo = array( $a, $b ); // Error x 2.
$foo = [    $a, $b      ]; // Error x 2.
$foo = array(/* comment */ /* comment */   ); // Error x 1.

// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing spacesSingleLine 1
$foo = array( $a, $b );
$foo = [ $a, $b ];
$foo = [ /* comment */ ];

$foo = array($a, $b); // Error x 2.
$foo = [$a, $b]; // Error x 2.
$foo = array(   $a, $b      ); // Error x 2.
$foo = [  $a, $b]; // Error x 2.

// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing spacesSingleLine false
$foo = array($a, $b);
$foo = [$a, $b];
$foo = array(  $a, $b   );
$foo = [ $a, $b ];

// Reset to default.
// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing spacesSingleLine 0

/*
 * Non-empty multi-line arrays.
 */

$array = array(
    $a,
    $b
);

$array = [
    $a,
    $b
];

$array = array($a,
    $b); // Error x 2.

$array = [



    $a,
    $b]; // Error x 1.

$array = array($a,
    $b
); // Error x 1.

$array = array(
    // Comment at the first line is always fine.
    $a,
    $b
);

$array = array( // Allow trailing comment after opener.
    $a,
    $b
);

$array = array( /* Allow trailing comment after opener. */
    $a,
    $b
);

$array = array( // phpcs:ignore Stdn.Cat.Sniff -- Allow trailing annotation after opener.
    $a,
    $b
);

$array = array( /* Allow trailing comment after opener. */ /* But don't allow a second one */
    $a,
    $b
); // Error x 1.

$array = array( /* This is still an error */ $a,
    $b
); // Error x 1.

$array = [ // Allow trailing comment after opener.



    $a,
    $b
];

$array = [ /* Allow trailing comment after opener */



    $a,
    $b
];

// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing spacesMultiLine 0

$array = [$a,
    $b];
$array = array($a,
    $b);

$array = [
    $a,
    $b
]; // Error x 2.

$array = [



    $a,
    $b]; // Error x 1.

$array = array($a,
    $b
); // Error x 1.

$array = array( // Comment. Error x 1.
    $a,
    $b);

$array = array( /* Comment. Error x 1. */
    $a,
    $b);

$array = array( // phpcs:ignore Stdn.Cat.Sniff -- Error x 1.
    $a,
    $b);

$array = array(// Comment. No space, this is fine.
    $a,
    $b);

$array = array(/* Comment. No space, this is fine. */
    $a,
    $b);

$array = array(// phpcs:ignore Stdn.Cat.Sniff -- No space, this is fine.
    $a,
    $b);

// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing spacesMultiLine 1

$array = [ $a,
    $b ];
$array = array( $a,
    $b );

$array = [
    $a,
    $b
]; // Error x 2.

$array = [



    $a,
    $b]; // Error x 2.

$array = array($a,
    $b
); // Error x 2.

$array = array( // Comment. One space, this is fine.
    $a,
    $b );

$array = array( /* Comment. One space, this is fine. */
    $a,
    $b );

$array = array( // phpcs:ignore Stdn.Cat.Sniff -- One space, this is fine.
    $a,
    $b );

$array = array(// Comment. No space, error x 1.
    $a,
    $b );

$array = array(/* Comment. No space, error x 1. */
    $a,
    $b );

$array = array(// phpcs:ignore Stdn.Cat.Sniff -- No space, error x 1.
    $a,
    $b );

// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing spacesMultiLine false

$array = array(
    $a,
    $b
);

$array = array($a,
    $b);

$array = [



    $a,
    $b];

$array = array($a,
    $b
);

// Test behaviour with all checks turned off.
// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing keywordSpacing false
// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing spacesWhenEmpty false
// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing spacesSingleLine false
// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing spacesMultiLine false
$foo = array (   );
$foo = array (  $a   );
$array = array($a,
    $b);

// Reset to default.
// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing keywordSpacing 0
// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing spacesWhenEmpty 0
// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing spacesSingleLine 0
// phpcs:set NormalizedArrays.Arrays.ArrayBraceSpacing spacesMultiLine newline
