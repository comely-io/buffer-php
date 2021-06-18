# Buffer PHP

Buffer for PHP strings (ByteArray)

## Prerequisites

* PHP 8.0+
* GMP extension enabled

## Installation

`composer require comely-io/buffer-php`

## Buffer

### Constructors

Method | Description
--- | ---
fromBase16 | Initialize buffer using Base16/Hexadecimal encoded string
fromBase64 | Initialize buffer from Base64 encoded string
fromByteArray | Initialize buffer from byte array
fromBinary | Initialize buffer from array of binary encoded bytes
__construct | Creates a new buffer instance from given argument

## Fixed-length Buffers

### Methods

Method | Description
--- | ---
len | Get length/size of buffer in bytes
raw | Get existing bytes in buffer as string
byteArray | Returns an indexed Array comprised of every byte's position on ASCII table
copy | Creates a copy of buffer from given start and/or up to length
equals | Compares buffer bytes with another buffer or string
read | Creates a [ByteReader](#bytereader) instance
hash | Creates a [ByteDigest](#bytedigest) instance
switchEndianess | Converts endianess of entire buffer between big and little endians
dump | Buffer information as an Array
applyFn | Applies a function/callback to existing buffer creating a new buffer instance as result
toBase16 | Encodes the buffer as Base16/Hexadecimal
toBase64 | Encodes the buffer in Base64
toBinary | Encodes the buffer into an indexed Array where each index is binary representation of byte (1s and 0s)

## Writeable Buffers

All the methods from fixed-length buffers above, and also:

### Methods

Method | Description
--- | ---
clean | Flush any existing data in buffer
readOnly | Puts buffer in Read-only state; Nothing further can be appended to it
writable | Puts buffer in writable state
isWritable | Return boolean
append | Append bytes to end of buffer
prepend | Prepend bytes to start of buffer
appendUInt8 | Appends single byte integer
appendUInt16LE | Appends 2 byte integer in little endian byte order
appendUInt16BE | Appends 2 byte integer in big endian byte order
appendUInt32LE | Appends 4 byte integer in little endian byte order
appendUInt32BE | Appends 4 byte integer in big endian byte order
appendUInt64LE | Appends 8 byte integer in little endian byte order
appendUInt64BE | Appends 8 byte integer in big endian byte order

## ByteReader

For systematically reading bytes from buffer in order. Useful for serialization and un-serialization of data (i.e. Bitcoin blocks and transactions) 

### Methods

Method | Description
--- | ---
ignoreUnderflow | If invoked, `ByteReaderUnderflowException` will not be thrown if required number of bytes are not present
isEnd | return boolean if end of buffer has been reached
len | Length of buffer in bytes
pos | Current position/index at byte in buffer
reset | Resets position index to 0
first | Resets position index to 0, then reads N number of bytes from start of buffer
lookBehind | Reads last N bytes previously read (does NOT update internal pointer)
lookAhead | Reads next N bytes but does NOT updates internal pointer
next | Reads next N bytes while updating the pointer
readUInt8 | Reads and converts next 1 byte as UInt8
readUInt16LE | Reads and converts next 2 bytes as UInt16 from little endian byte order
readUInt16BE | Reads and converts next 2 bytes as UInt16 from big endian byte order
readUInt32LE | Reads and converts next 4 bytes as UInt32 from little endian byte order
readUInt32BE | Reads and converts next 4 bytes as UInt32 from big endian byte order
readUInt64LE | Reads and converts next 8 bytes as UInt64 from little endian byte order
readUInt64BE | Reads and converts next 8 bytes as UInt64 from big endian byte order
setPointer | Sets internal reading pointer to specified byte
remaining | Retrieved all remaining bytes in buffer (does NOT update internal pointer)

## ByteDigest

Applies hash function to bytes in buffer and returns digest bytes.

Check PHP.net [hash_algos()](https://www.php.net/hash_algos) and [hash_hmac_algos()](https://www.php.net/hash_hmac_algos) for list of available/supported algorithms.

### Methods

Method | Description
--- | ---
toString | If invoked before any other method, all hash functions will return `string` instead of new buffer instance
hash | Applies specified hash algorithm times N iterations and returns X numbers of bytes from digest
hmac | Applies HMAC hash function
pbkdf2 | Applies PBKDF2 hash function
md5 | Applies `md5` hash function
sha1 | Applies `SHA1` hash function
sha256 | Applies `SHA256` hash function
sha512 | Applies `SHA512` hash function
ripeMd160 | Applies `ripeMd160` hash function


