<?php
/*
------------------------------------------------------------------------------
Test script for the PHP port of Bob Jenkins' ISAAC random number generator by
Ilmari Karonen.

This script is based on <http://www.burtleburtle.net/bob/c/randtest.c> from
the ISAAC challenge.  The output of this script should match the content of
<http://www.burtleburtle.net/bob/rand/randseed.txt>.  (A copy of randseed.txt
is included in this repository for convenience.)

This code is released into the public domain.  Do whatever you want with it.

The latest version of this code can be found at https://github.com/vyznev/ISAAC-PHP.

HISTORY:
 2013-01-20: Initial version on Stack Overflow.
 2017-09-21: Moved to GitHub, added comments.
------------------------------------------------------------------------------
*/

require_once('isaac.php');

$seed = "This is <i>not</i> the right mytext.";
$isaac = new ISAAC ( $seed );

for ($j = 0; $j < 10 * 256; ++$j) {
    printf("%08x ", $isaac->rand());
    if (($j & 7) == 7) echo "\n";
}
