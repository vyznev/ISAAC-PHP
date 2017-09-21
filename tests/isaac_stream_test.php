<?php
/*
------------------------------------------------------------------------------
Test script for the PHP port of Bob Jenkins' ISAAC random number generator by
Ilmari Karonen.

This script demonstrates basic usage of ISAAC as a synchronous stream cipher.
The isaac_encrypt() function takes a message string, a secret key and a unique
salt.  The key and the salt are concatenated (with a null byte in between) to
produce the ISAAC seed.  The ISAAC output is then converted into a stream of
bytes that are XORed with the message.  The resulting ciphertext can thus be
decrypted by re-encrypting it with the same key and salt.

Note that the same key and salt should *never* be used to encrypt more than
one distinct message, as doing so would compromise the security of the cipher!
Also note that such simple stream encryption offers no ciphertext integrity
protection, and may thus be vulnerable to active tampering attacks unless
additionally protected by a message authentication code.

This code is released into the public domain.  Do whatever you want with it.

The latest version of this code can be found at https://github.com/vyznev/ISAAC-PHP.

HISTORY:
 2017-09-21: Initial version.
------------------------------------------------------------------------------
*/

require_once('isaac.php');

function isaac_encrypt($message, $key, $salt) {
    $isaac = new ISAAC ("$key\0$salt");  // XXX: never use the same key & salt twice!
    $mask = pack("V*", ...$isaac->r);    // PHP v5.6+
    while (strlen($mask) < strlen($message)) {
        $isaac->isaac();  // refill the output buffer
        $mask .= pack("V*", ...$isaac->r);
    }
    return $message ^ substr($mask, 0, strlen($message));
}

$key = "a secret key";  // this should be kept secret
$salt = time();         // this can be public, but MUST be unique for each message!

echo "Key = \"$key\", salt = $salt\n";

$plaintext = "A quick brown fox jumps over the lazy dog.";
echo "Plaintext: $plaintext\n";

$encrypted = isaac_encrypt($plaintext, $key, $salt);
echo "Encrypted (hex): ", unpack("H*", $encrypted)[1], "\n";

// decrypt by re-encrypting with the original key and salt
$decrypted = isaac_encrypt($encrypted, $key, $salt);

if ($plaintext === $decrypted) {
    echo "Decryption test successful.\n";
} else {
    echo "Decryption failed, got hex ", unpack("H*", $decrypted)[1], "\n";
}
