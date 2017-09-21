<?php
/*
------------------------------------------------------------------------------
Test script for the PHP port of Bob Jenkins' ISAAC random number generator by
Ilmari Karonen.

This script is based on the built-in test code included in the ISAAC reference
implementation <http://www.burtleburtle.net/bob/c/rand.c>.  The output of this
script should match <http://www.burtleburtle.net/bob/rand/randvect.txt>.  A
copy of randvect.txt is included in this repository for convenience.

This code is released into the public domain.  Do whatever you want with it.

The latest version of this code can be found at https://github.com/vyznev/ISAAC-PHP.

HISTORY:
 2013-01-20: Initial version on Stack Overflow.
 2017-09-21: Moved to GitHub, added comments.
------------------------------------------------------------------------------
*/

require_once('isaac.php');

$seed = array_fill(0, 256, 0);
$isaac = new ISAAC ( $seed );

for ($i = 0; $i < 2; ++$i) {
    $isaac->isaac();  // XXX: the first output block is dropped!
    for ($j = 0; $j < 256; ++$j) {
        printf("%08x", $isaac->r[$j]);
        if (($j & 7) == 7) echo "\n";
    }
}
