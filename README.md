# ISAAC-PHP

This is a pure PHP port of the [ISAAC] cryptographic random number generator / stream cipher.

This ISAAC implementation was written by Ilmari Karonen based on the [ISAAC reference C implementation][ISAAC-Home] by Bob Jenkins, with some inspiration taken from the [Perl port by John L. Allen][ISAAC-Perl].  It was originally [posted on Stack Overflow][ISAAC-SO] in January 2013, and is now [available on GitHub][ISAAC-GitHub].

## Interface

The `isaac.php` file defines an `ISAAC` class that encapsulates the implementation.  The `ISAAC` class constructor takes an optional `$seed` parameter, which should be either a 256-element array of 32-bit integers, a string of up to 1024 bytes (which is converted to an array using `unpack()`; see the code) or `null` (the default).  If the seed is null or omitted, the shorter single-pass initialization is used, which corresponds to calling `randinit()` with `flag == FALSE` in the C reference implementation; otherwise, the two-pass initialization corresponding to `flag == TRUE`is used.

The class provides a `rand()` method that returns a single 32-bit number, just like the `rand()` macro defined in Jenkins' [`rand.h`](http://www.burtleburtle.net/bob/c/rand.h).  Alternatively, I've left the core `isaac()` method and its internal result buffer `$r` public, so that those preferring more direct access to the generator can just call `isaac()` themselves and grab the output that way.  Note that the constructor already calls `isaac()` once, just like `randinit()` in the reference implementation, so you only need to call it again after exhausting the first 256 output values.

## Basic usage

To initialize the generator, include the `isaac.php` file and create an `ISAAC` instance:

    require_once('isaac.php');

    $isaac = new ISAAC ( "my secret seed string" );

To obtain a new 32-bit pseudorandom number, call the `rand()` method on your `ISAAC` instance:

    printf("%08x", $isaac->rand());  // prints a random 8-character hex string

## Direct output buffer access

Alternatively, you can read the next 256 pseudorandom 32-bit numbers from the output buffer `$isaac->r` directly.  To refill the buffer with the next 256 outputs, call `$isaac->isaac()`.  Note that the `ISAAC` constructor automatically fills the buffer, so you don't need to call `isaac()` yourself until you've used up the first 256 outputs.  (This matches the behavior of the reference C implementation.)

For example, to obtain a byte string suitable for stream encryption, you can pass the content of the output buffer directly to `pack()`:

    function isaac_encrypt($message, $key, $salt) {
        $isaac = new ISAAC ("$key\0$salt");  // XXX: never use the same key & salt twice!
        $mask = pack("V*", ...$isaac->r);    // PHP v5.6+
        while (strlen($mask) < strlen($message)) {
            $isaac->isaac();  // refill the output buffer
            $mask .= pack("V*", ...$isaac->r);
        }
        return $message ^ substr($mask, 0, strlen($message));
    }

(See also the `isaac_stream_test.php` script in the `tests/` directory.)

If mixing calls to `rand()` with direct access to the output buffer, note that `rand()` pops off and returns the *last* value in the buffer (after refilling the buffer if it's empty).  This also matches the reference C implementation.

## Test scripts

The `tests/` directory includes two test scripts, `isaac_test.php` and `isaac_test2.php`, which are PHP ports of the test code included in the ISAAC reference C implementation.  The outputs of those two scripts should exactly match the [`randvect.txt`](http://www.burtleburtle.net/bob/rand/randvect.txt) and [`randseed.txt`](http://www.burtleburtle.net/bob/rand/randseed.txt) files from Bon Jenkins' ISAAC page.  (For convenience, copies of the reference test output files are also included in the `tests/` directory.)

## Alternative implementation

The `isaac_unroll.php` file contains an alternative version of the `ISAAC` class with an unrolled inner loop.  The unrolled code is about 10% to 20% faster than the code in `isaac.php`, but also about 20% longer.  In all other respects, the two implementations are exactly identical: they provide the same API and produce the exact same output.

## Licensing

Like the original ISAAC reference code, this PHP port is released into the public domain.  Do whatever you want with it.


  [ISAAC]: https://en.wikipedia.org/wiki/ISAAC_(cipher) "ISAAC (cipher) on Wikipedia"
  [ISAAC-Home]: http://www.burtleburtle.net/bob/rand/isaacafa.html "ISAAC: a fast cryptographic random number generator"
  [ISAAC-Perl]: http://www.burtleburtle.net/bob/rand/randperl.txt "Perl port of ISAAC by John L. Allen"
  [ISAAC-SO]: https://stackoverflow.com/questions/14420754/isaac-cipher-in-php/14428399#14428399 "Stack Overflow: ISAAC cipher in PHP"
  [ISAAC-GitHub]: https://github.com/vyznev/ISAAC-PHP "ISAAC-PHP on GitHub"
