<?php
/*
------------------------------------------------------------------------------
ISAAC random number generator by Bob Jenkins.  PHP port by Ilmari Karonen.

Based on the randport.c and readable.c reference C implementations by Bob
Jenkins, with some inspiration taken from the Perl port by John L. Allen.

This code is released into the public domain.  Do whatever you want with it.

The latest version of this code can be found at https://github.com/vyznev/ISAAC-PHP.

HISTORY:
 2013-01-20: Initial version on Stack Overflow.
 2017-09-21: Moved to GitHub, comments tweaked.
------------------------------------------------------------------------------
*/

class ISAAC {
    private $m, $a, $b, $c; // internal state
    public  $r;   // current chunk of results

    public function isaac()
    {
        $c = ++$this->c;     // c gets incremented once per 256 results
        $b = $this->b += $c; // then combined with b
        $a = $this->a;

        $m =& $this->m;
        $r = array();

        for ($i = 0; $i < 256; ++$i) {
            $x = $m[$i];
            switch ($i & 3) {
            case 0: $a ^= ($a << 13); break;
            case 1: $a ^= ($a >>  6) & 0x03ffffff; break;
            case 2: $a ^= ($a <<  2); break;
            case 3: $a ^= ($a >> 16) & 0x0000ffff; break;
            }
            $a += $m[$i ^ 128]; $a &= 0xffffffff;
            $m[$i] = $y = ($m[($x >>  2) & 255] + $a + $b) & 0xffffffff;
            $r[$i] = $b = ($m[($y >> 10) & 255] + $x) & 0xffffffff;
        }

        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
        $this->r = $r;
    }

    public function rand()
    {
        if (empty($this->r)) $this->isaac();
        return array_pop($this->r);
    }

    private static function mix( &$a, &$b, &$c, &$d, &$e, &$f, &$g, &$h )
    {
        $a ^= ($b << 11);              $d += $a; $b += $c;
        $b ^= ($c >>  2) & 0x3fffffff; $e += $b; $c += $d;
        $c ^= ($d <<  8);              $f += $c; $d += $e;
        $d ^= ($e >> 16) & 0x0000ffff; $g += $d; $e += $f;
        $e ^= ($f << 10);              $h += $e; $f += $g;
        $f ^= ($g >>  4) & 0x0fffffff; $a += $f; $g += $h;
        $g ^= ($h <<  8);              $b += $g; $h += $a;
        $h ^= ($a >>  9) & 0x007fffff; $c += $h; $a += $b;
        // 64-bit PHP does something weird on integer overflow; avoid it
        $a &= 0xffffffff; $b &= 0xffffffff; $c &= 0xffffffff; $d &= 0xffffffff;
        $e &= 0xffffffff; $f &= 0xffffffff; $g &= 0xffffffff; $h &= 0xffffffff;
    }

    public function __construct ( $seed = null )
    {
        $this->a = $this->b = $this->c = 0;
        $this->m = array_fill(0, 256, 0);
        $m =& $this->m;

        $a = $b = $c = $d = $e = $f = $g = $h = 0x9e3779b9;  // golden ratio

        for ($i = 0; $i < 4; ++$i) {
            ISAAC::mix($a, $b, $c, $d, $e, $f, $g, $h);      // scramble it
        }

        if ( isset($seed) ) {
            if ( is_string($seed) ) {
                // emulate casting char* to int* on a little-endian CPU
                $seed = array_values(unpack("V256", pack("a1024", $seed)));
            }

            // initialize using the contents of $seed as the seed
            for ($i = 0; $i < 256; $i += 8) {
                $a += $seed[$i  ]; $b += $seed[$i+1];
                $c += $seed[$i+2]; $d += $seed[$i+3];
                $e += $seed[$i+4]; $f += $seed[$i+5];
                $g += $seed[$i+6]; $h += $seed[$i+7];
                ISAAC::mix($a, $b, $c, $d, $e, $f, $g, $h);
                $m[$i  ] = $a; $m[$i+1] = $b; $m[$i+2] = $c; $m[$i+3] = $d;
                $m[$i+4] = $e; $m[$i+5] = $f; $m[$i+6] = $g; $m[$i+7] = $h;
            }

            // do a second pass to make all of the seed affect all of $m
            for ($i = 0; $i < 256; $i += 8) {
                $a += $m[$i  ]; $b += $m[$i+1]; $c += $m[$i+2]; $d += $m[$i+3];
                $e += $m[$i+4]; $f += $m[$i+5]; $g += $m[$i+6]; $h += $m[$i+7];
                ISAAC::mix($a, $b, $c, $d, $e, $f, $g, $h);
                $m[$i  ] = $a; $m[$i+1] = $b; $m[$i+2] = $c; $m[$i+3] = $d;
                $m[$i+4] = $e; $m[$i+5] = $f; $m[$i+6] = $g; $m[$i+7] = $h;
            }
        }
        else {
            // fill in $m with messy stuff (does anyone really use this?)
            for ($i = 0; $i < 256; $i += 8) {
                ISAAC::mix($a, $b, $c, $d, $e, $f, $g, $h);
                $m[$i  ] = $a; $m[$i+1] = $b; $m[$i+2] = $c; $m[$i+3] = $d;
                $m[$i+4] = $e; $m[$i+5] = $f; $m[$i+6] = $g; $m[$i+7] = $h;
            }
        }

        // fill in the first set of results
        $this->isaac();
    }
}
