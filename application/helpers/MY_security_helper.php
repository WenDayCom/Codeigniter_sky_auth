<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * To transparently support this function on older versions of PHP
 * @author asphp@dsgml.com
 */

if (!function_exists('hash_equals')) {
    function hash_equals($str1, $str2)
    {
        if (strlen($str1) != strlen($str2)) {
            return false;
        } else {
            $res = $str1 ^ $str2;
            $ret = 0;
            for ($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
            return !$ret;
        }
    }
}

/**
 * A Compatibility library with PHP 5.5's simplified password hashing API.
 *
 * @author Anthony Ferrara <ircmaxell@php.net>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2012 The Authors
 */


if (!defined('PASSWORD_BCRYPT')) {
    /**
     * PHPUnit Process isolation caches constants, but not function declarations.
     * So we need to check if the constants are defined separately from
     * the functions to enable supporting process isolation in userland
     * code.
     */
    define('PASSWORD_BCRYPT', 1);
    define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);
    define('PASSWORD_BCRYPT_DEFAULT_COST', 10);
}

if (!function_exists('password_hash')) {

    /**
     * Hash the password using the specified algorithm
     *
     * @param string $password The password to hash
     * @param int $algo The algorithm to use (Defined by PASSWORD_* constants)
     * @param array $options The options for the algorithm to use
     *
     * @return string|false The hashed password, or false on error.
     */
    function password_hash($password, $algo, array $options = array())
    {
        if (!function_exists('crypt')) {
            trigger_error("Crypt must be loaded for password_hash to function", E_USER_WARNING);
            return null;
        }
        if (is_null($password) || is_int($password)) {
            $password = (string)$password;
        }
        if (!is_string($password)) {
            trigger_error("password_hash(): Password must be a string", E_USER_WARNING);
            return null;
        }
        if (!is_int($algo)) {
            trigger_error("password_hash() expects parameter 2 to be long, " . gettype($algo) . " given", E_USER_WARNING);
            return null;
        }
        $resultLength = 0;
        switch ($algo) {
            case PASSWORD_BCRYPT:
                $cost = PASSWORD_BCRYPT_DEFAULT_COST;
                if (isset($options['cost'])) {
                    $cost = (int)$options['cost'];
                    if ($cost < 4 || $cost > 31) {
                        trigger_error(sprintf("password_hash(): Invalid bcrypt cost parameter specified: %d", $cost), E_USER_WARNING);
                        return null;
                    }
                }
                // The length of salt to generate
                $raw_salt_len = 16;
                // The length required in the final serialization
                $required_salt_len = 22;
                $hash_format = sprintf("$2y$%02d$", $cost);
                // The expected length of the final crypt() output
                $resultLength = 60;
                break;
            default:
                trigger_error(sprintf("password_hash(): Unknown password hashing algorithm: %s", $algo), E_USER_WARNING);
                return null;
        }
        $salt_req_encoding = false;
        if (isset($options['salt'])) {
            switch (gettype($options['salt'])) {
                case 'NULL':
                case 'boolean':
                case 'integer':
                case 'double':
                case 'string':
                    $salt = (string)$options['salt'];
                    break;
                case 'object':
                    if (method_exists($options['salt'], '__tostring')) {
                        $salt = (string)$options['salt'];
                        break;
                    }
                case 'array':
                case 'resource':
                default:
                    trigger_error('password_hash(): Non-string salt parameter supplied', E_USER_WARNING);
                    return null;
            }
            if (_strlen($salt) < $required_salt_len) {
                trigger_error(sprintf("password_hash(): Provided salt is too short: %d expecting %d", _strlen($salt), $required_salt_len), E_USER_WARNING);
                return null;
            } elseif (0 == preg_match('#^[a-zA-Z0-9./]+$#D', $salt)) {
                $salt_req_encoding = true;
            }
        } else {
            $buffer = '';
            $buffer_valid = false;
            if (function_exists('mcrypt_create_iv') && !defined('PHALANGER')) {
                $buffer = mcrypt_create_iv($raw_salt_len, MCRYPT_DEV_URANDOM);
                if ($buffer) {
                    $buffer_valid = true;
                }
            }
            if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
                $strong = false;
                $buffer = openssl_random_pseudo_bytes($raw_salt_len, $strong);
                if ($buffer && $strong) {
                    $buffer_valid = true;
                }
            }
            if (!$buffer_valid && @is_readable('/dev/urandom')) {
                $file = fopen('/dev/urandom', 'r');
                $read = 0;
                $local_buffer = '';
                while ($read < $raw_salt_len) {
                    $local_buffer .= fread($file, $raw_salt_len - $read);
                    $read = _strlen($local_buffer);
                }
                fclose($file);
                if ($read >= $raw_salt_len) {
                    $buffer_valid = true;
                }
                $buffer = str_pad($buffer, $raw_salt_len, "\0") ^ str_pad($local_buffer, $raw_salt_len, "\0");
            }
            if (!$buffer_valid || _strlen($buffer) < $raw_salt_len) {
                $buffer_length = _strlen($buffer);
                for ($i = 0; $i < $raw_salt_len; $i++) {
                    if ($i < $buffer_length) {
                        $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
                    } else {
                        $buffer .= chr(mt_rand(0, 255));
                    }
                }
            }
            $salt = $buffer;
            $salt_req_encoding = true;
        }
        if ($salt_req_encoding) {
            // encode string with the Base64 variant used by crypt
            $base64_digits =
                'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
            $bcrypt64_digits =
                './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

            $base64_string = base64_encode($salt);
            $salt = strtr(rtrim($base64_string, '='), $base64_digits, $bcrypt64_digits);
        }
        $salt = _substr($salt, 0, $required_salt_len);

        $hash = $hash_format . $salt;

        $ret = crypt($password, $hash);

        if (!is_string($ret) || _strlen($ret) != $resultLength) {
            return false;
        }

        return $ret;
    }

    /**
     * Get information about the password hash. Returns an array of the information
     * that was used to generate the password hash.
     *
     * array(
     *    'algo' => 1,
     *    'algoName' => 'bcrypt',
     *    'options' => array(
     *        'cost' => PASSWORD_BCRYPT_DEFAULT_COST,
     *    ),
     * )
     *
     * @param string $hash The password hash to extract info from
     *
     * @return array The array of information about the hash.
     */
    function password_get_info($hash)
    {
        $return = array(
            'algo' => 0,
            'algoName' => 'unknown',
            'options' => array(),
        );
        if (_substr($hash, 0, 4) == '$2y$' && _strlen($hash) == 60) {
            $return['algo'] = PASSWORD_BCRYPT;
            $return['algoName'] = 'bcrypt';
            list($cost) = sscanf($hash, "$2y$%d$");
            $return['options']['cost'] = $cost;
        }
        return $return;
    }

    /**
     * Determine if the password hash needs to be rehashed according to the options provided
     *
     * If the answer is true, after validating the password using password_verify, rehash it.
     *
     * @param string $hash The hash to test
     * @param int $algo The algorithm used for new password hashes
     * @param array $options The options array passed to password_hash
     *
     * @return boolean True if the password needs to be rehashed.
     */
    function password_needs_rehash($hash, $algo, array $options = array())
    {
        $info = password_get_info($hash);
        if ($info['algo'] !== (int)$algo) {
            return true;
        }
        switch ($algo) {
            case PASSWORD_BCRYPT:
                $cost = isset($options['cost']) ? (int)$options['cost'] : PASSWORD_BCRYPT_DEFAULT_COST;
                if ($cost !== $info['options']['cost']) {
                    return true;
                }
                break;
        }
        return false;
    }

    /**
     * Verify a password against a hash using a timing attack resistant approach
     *
     * @param string $password The password to verify
     * @param string $hash The hash to verify against
     *
     * @return boolean If the password matches the hash
     */
    function password_verify($password, $hash)
    {
        if (!function_exists('crypt')) {
            trigger_error("Crypt must be loaded for password_verify to function", E_USER_WARNING);
            return false;
        }
        $ret = crypt($password, $hash);
        if (!is_string($ret) || _strlen($ret) != _strlen($hash) || _strlen($ret) <= 13) {
            return false;
        }

        $status = 0;
        for ($i = 0; $i < _strlen($ret); $i++) {
            $status |= (ord($ret[$i]) ^ ord($hash[$i]));
        }

        return $status === 0;
    }
}


if (!function_exists('_strlen')) {

    /**
     * Count the number of bytes in a string
     *
     * We cannot simply use strlen() for this, because it might be overwritten by the mbstring extension.
     * In this case, strlen() will count the number of *characters* based on the internal encoding. A
     * sequence of bytes might be regarded as a single multibyte character.
     *
     * @param string $binary_string The input string
     *
     * @internal
     * @return int The number of bytes
     */
    function _strlen($binary_string)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($binary_string, '8bit');
        }
        return strlen($binary_string);
    }

    /**
     * Get a substring based on byte limits
     *
     * @see _strlen()
     *
     * @param string $binary_string The input string
     * @param int $start
     * @param int $length
     *
     * @internal
     * @return string The substring
     */
    function _substr($binary_string, $start, $length)
    {
        if (function_exists('mb_substr')) {
            return mb_substr($binary_string, $start, $length, '8bit');
        }
        return substr($binary_string, $start, $length);
    }

    /**
     * Check if current PHP version is compatible with the library
     *
     * @return boolean the check result
     */
    function check()
    {
        static $pass = NULL;

        if (is_null($pass)) {
            if (function_exists('crypt')) {
                $hash = '$2y$04$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
                $test = crypt("password", $hash);
                $pass = $test == $hash;
            } else {
                $pass = false;
            }
        }
        return $pass;
    }

}

if(! function_exists('randomBytes')){
    /**
     * @author Tom Worster <fsb@thefsb.org>
     * @copyright Copyright (c) 2008 Yii Software LLC
     * @license 3-Clause BSD. See XTRAS.md in this Gist.
     */
    /**
     * Generates specified number of random bytes. Output is binary string, not ASCII.
     *
     * @param integer $length the number of bytes to generate
     *
     * @return string the generated random bytes
     * @throws \Exception
     */
    function randomBytes($length)
    {
        if (function_exists('random_bytes')) {
            return random_bytes($length);
        }
        if (!is_int($length) || $length < 1) {
            throw new \Exception('Invalid first parameter ($length)');
        }
        // The recent LibreSSL RNGs are faster and better than /dev/urandom.
        // Parse OPENSSL_VERSION_TEXT because OPENSSL_VERSION_NUMBER is no use for LibreSSL.
        // https://bugs.php.net/bug.php?id=71143
        if (defined('OPENSSL_VERSION_TEXT')
            && preg_match('{^LibreSSL (\d\d?)\.(\d\d?)\.(\d\d?)$}', OPENSSL_VERSION_TEXT, $matches)
            && (10000 * $matches[1]) + (100 * $matches[2]) + $matches[3] >= 20105
        ) {
            $key = openssl_random_pseudo_bytes($length, $cryptoStrong);
            if ($cryptoStrong === false) {
                throw new \Exception('openssl_random_pseudo_bytes() set $crypto_strong false. Your PHP setup is insecure.');
            }
            if ($key !== false && mb_strlen($key, '8bit') === $length) {
                return $key;
            }
        }
        // mcrypt_create_iv() does not use libmcrypt. Since PHP 5.3.7 it directly reads
        // CrypGenRandom on Windows. Elsewhere it directly reads /dev/urandom.
        if (PHP_VERSION_ID >= 50307 && function_exists('mcrypt_create_iv')) {
            $key = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            if (mb_strlen($key, '8bit') === $length) {
                return $key;
            }
        }
        // If not on Windows, test for a /dev/urandom device.
        if (DIRECTORY_SEPARATOR === '/') {
            // Check it for speacial character device protection mode. Do not follow
            // symbolic link at '/dev/urandom', as such would be suspicious. With lstat()
            // (as opposed to stat()) the test fails if it is.
            $lstat = @lstat('/dev/urandom');
            if ($lstat !== false && ($lstat['mode'] & 0170000) === 020000) {
                $key = @file_get_contents('/dev/urandom', false, null, 0, $length);
                if ($key !== false && mb_strlen($key, '8bit') === $length) {
                    return $key;
                }
            }
        }
        // Since 5.4.0, openssl_random_pseudo_bytes() reads from CryptGenRandom on Windows instead
        // of using OpenSSL library. Don't use OpenSSL on other platforms.
        if (DIRECTORY_SEPARATOR !== '/' && PHP_VERSION_ID >= 50400 && defined('OPENSSL_VERSION_TEXT')) {
            $key = openssl_random_pseudo_bytes($length, $cryptoStrong);
            if ($cryptoStrong === false) {
                throw new \Exception('openssl_random_pseudo_bytes() set $crypto_strong false. Your PHP setup is insecure.');
            }
            if ($key !== false && mb_strlen($key, '8bit') === $length) {
                return $key;
            }
        }
        throw new \Exception('Unable to generate a random key');
    }
}

/**
 * Generates a random UUID using the secure RNG.
 *
 * Returns Version 4 UUID format: xxxxxxxx-xxxx-4xxx-Yxxx-xxxxxxxxxxxx where x is
 * any random hex digit and Y is a random choice from 8, 9, a, or b.
 *
 * @return string the UUID
 */

if(! function_exists('randomUuid')) {

    function randomUuid()
    {
        $bytes = randomBytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $id = str_split(bin2hex($bytes), 4);
        return "{$id[0]}{$id[1]}-{$id[2]}-{$id[3]}-{$id[4]}-{$id[5]}{$id[6]}{$id[7]}";
    }
}

/**
 * Returns a random integer in the range $min through $max inclusive.
 *
 * @param int $min Minimum value of the returned integer.
 * @param int $max Maximum value of the returned integer.
 *
 * @return int The generated random integer.
 * @throws \Exception
 */
if(! function_exists('randomInt')){
    function randomInt($min, $max)
    {
        if (function_exists('random_int')) {
            return random_int($min, $max);
        }
        if (!is_int($min)) {
            throw new \Exception('First parameter ($min) must be an integer');
        }
        if (!is_int($max)) {
            throw new \Exception('Second parameter ($max) must be an integer');
        }
        if ($min > $max) {
            throw new \Exception('First parameter ($min) must be no greater than second parameter ($max)');
        }
        if ($min === $max) {
            return $min;
        }
        // $range is a PHP float if the expression exceeds PHP_INT_MAX.
        $range = $max - $min + 1;
        if (is_float($range)) {
            $mask = null;
        } else {
            // Make a bit mask of (the next highest power of 2 >= $range) minus one.
            $mask = 1;
            $shift = $range;
            while ($shift > 1) {
                $shift >>= 1;
                $mask = ($mask << 1) | 1;
            }
        }
        $tries = 0;
        do {
            $bytes = randomBytes(PHP_INT_SIZE);
            // Convert byte string to a signed int by shifting each byte in.
            $value = 0;
            for ($pos = 0; $pos < PHP_INT_SIZE; $pos += 1) {
                $value = ($value << 8) | ord($bytes[$pos]);
            }
            if ($mask === null) {
                // Use all bits in $bytes and check $value against $min and $max instead of $range.
                if ($value >= $min && $value <= $max) {
                    return $value;
                }
            } else {
                // Use only enough bits from $bytes to cover the $range.
                $value &= $mask;
                if ($value < $range) {
                    return $value + $min;
                }
            }
            $tries += 1;
        } while ($tries < 123);
        // Worst case: this is as likely as 123 heads in as many coin tosses.
        throw new \Exception('Unable to generate random int after 123 tries');
    }
}

