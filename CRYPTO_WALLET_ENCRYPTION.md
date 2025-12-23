# Crypto Wallet Encryption - PHP & Node.js Compatible

## Overview
Private keys are encrypted using **AES-256-CBC** encryption, which is compatible between PHP and Node.js.

## Encryption Method

### Algorithm
- **Cipher**: AES-256-CBC
- **Key Size**: 256 bits (32 bytes / 64 hex characters)
- **IV Size**: 128 bits (16 bytes)
- **Encoding**: Base64

### Storage Format
```
Base64( IV (16 bytes) + Encrypted Data )
```

## Configuration

### .env File
```bash
WALLET_ENCRYPTION_KEY="2002c7b8a4ea2b4e3a239b1edd9ee21f05ec3a30c409fc14dedbd9e5d651af7d"
```

**Important:** This key must be the same in both PHP and Node.js environments!

## PHP Usage

### Encrypt (Already implemented in WalletService)
```php
use App\Services\WalletService;

$walletService = new WalletService();
$encrypted = $walletService->encryptPrivateKey($privateKey);
```

### Decrypt
```php
use App\Services\WalletService;

$walletService = new WalletService();
$decrypted = $walletService->decryptPrivateKey($encryptedPrivateKey);
```

## Node.js Usage

### Setup
```javascript
const crypto = require('crypto');

const WALLET_ENCRYPTION_KEY = process.env.WALLET_ENCRYPTION_KEY;
```

### Decrypt Function
```javascript
function decryptPrivateKey(encryptedPrivateKey) {
    // Decode base64
    const data = Buffer.from(encryptedPrivateKey, 'base64');

    // Extract IV and encrypted data
    const iv = data.slice(0, 16);
    const encryptedData = data.slice(16);

    // Decrypt
    const decipher = crypto.createDecipheriv(
        'aes-256-cbc',
        Buffer.from(WALLET_ENCRYPTION_KEY, 'hex'),
        iv
    );

    let decrypted = decipher.update(encryptedData);
    decrypted = Buffer.concat([decrypted, decipher.final()]);

    return decrypted.toString('utf8');
}
```

### Example: Get Private Key from Database
```javascript
const mysql = require('mysql2/promise');

async function getUserPrivateKey(userId) {
    const db = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME
    });

    const [rows] = await db.query(
        'SELECT private_key, crypto_address FROM users WHERE id = ?',
        [userId]
    );

    if (rows.length === 0) {
        throw new Error('User not found');
    }

    // Decrypt the private key
    const privateKey = decryptPrivateKey(rows[0].private_key);

    await db.end();

    return {
        address: rows[0].crypto_address,
        privateKey: privateKey
    };
}
```

## Testing

### Test File
See `decrypt-private-key.example.js` for a complete working example.

### Run Test
```bash
node decrypt-private-key.example.js
```

### Test with Real Database
```javascript
const { getPrivateKeyFromDatabase } = require('./decrypt-private-key.example.js');

getPrivateKeyFromDatabase(3)
    .then(wallet => {
        console.log('Address:', wallet.address);
        console.log('Private Key:', wallet.privateKey);
    })
    .catch(console.error);
```

## Security Best Practices

1. **Never commit `WALLET_ENCRYPTION_KEY` to git**
   - Add `.env` to `.gitignore`
   - Use environment variables in production

2. **Use the same key across environments**
   - Copy the key to your Node.js `.env` file
   - Ensure both PHP and Node.js use the exact same key

3. **Keep encryption key secure**
   - Store in secure environment variables
   - Rotate keys periodically (requires re-encrypting all private keys)

4. **Never log decrypted private keys**
   - Only decrypt when needed
   - Clear from memory after use

## Migration from Laravel's encrypt()

If you have existing users with private keys encrypted using Laravel's `encrypt()` function, you need to:

1. Decrypt using Laravel's `decrypt()`
2. Re-encrypt using the new `WalletService::encryptPrivateKey()`
3. Update database

Example migration script:
```php
use App\Models\User;
use App\Services\WalletService;

$walletService = new WalletService();
$users = User::whereNotNull('private_key')->get();

foreach ($users as $user) {
    try {
        // Decrypt using Laravel's method
        $decrypted = decrypt($user->private_key);

        // Re-encrypt using compatible method
        $reencrypted = $walletService->encryptPrivateKey($decrypted);

        // Update
        $user->update(['private_key' => $reencrypted]);
    } catch (\Exception $e) {
        // Handle error
    }
}
```

## Compatibility

✅ **Compatible:**
- PHP 7.4+
- Node.js 10+
- Any language with AES-256-CBC support

✅ **Tested:**
- PHP 8.2
- Node.js 18+

## Support

For issues or questions, check:
- PHP `openssl` extension is enabled
- Node.js `crypto` module is available
- Encryption key is exactly 64 hex characters (32 bytes)
