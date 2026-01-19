#!/usr/bin/env node

/**
 * Node.js Example: Decrypt Private Keys
 *
 * This script shows how to decrypt private keys stored by PHP
 * using the same WALLET_ENCRYPTION_KEY from .env
 */

const crypto = require('crypto');

// Encryption key from .env (same key used in PHP)
const WALLET_ENCRYPTION_KEY = process.env.WALLET_ENCRYPTION_KEY || '2002c7b8a4ea2b4e3a239b1edd9ee21f05ec3a30c409fc14dedbd9e5d651af7d';

/**
 * Decrypt a private key that was encrypted by PHP
 * @param {string} encryptedPrivateKey - Base64 encoded encrypted private key
 * @returns {string} - Decrypted private key
 */
function decryptPrivateKey(encryptedPrivateKey) {
    try {
        // Decode the base64 string
        const data = Buffer.from(encryptedPrivateKey, 'base64');

        // Extract IV (first 16 bytes) and encrypted data
        const iv = data.slice(0, 16);
        const encryptedData = data.slice(16);

        // Create decipher
        const decipher = crypto.createDecipheriv(
            'aes-256-cbc',
            Buffer.from(WALLET_ENCRYPTION_KEY, 'hex'),
            iv
        );

        // Decrypt
        let decrypted = decipher.update(encryptedData);
        decrypted = Buffer.concat([decrypted, decipher.final()]);

        return decrypted.toString('utf8');
    } catch (error) {
        throw new Error('Failed to decrypt private key: ' + error.message);
    }
}

/**
 * Encrypt a private key (optional - for testing)
 * @param {string} privateKey - Plain text private key
 * @returns {string} - Base64 encoded encrypted private key
 */
function encryptPrivateKey(privateKey) {
    // Generate random IV (16 bytes)
    const iv = crypto.randomBytes(16);

    // Create cipher
    const cipher = crypto.createCipheriv(
        'aes-256-cbc',
        Buffer.from(WALLET_ENCRYPTION_KEY, 'hex'),
        iv
    );

    // Encrypt
    let encrypted = cipher.update(privateKey, 'utf8');
    encrypted = Buffer.concat([encrypted, cipher.final()]);

    // Combine IV and encrypted data, then base64 encode
    const combined = Buffer.concat([iv, encrypted]);
    return combined.toString('base64');
}

// Example usage with MySQL
async function getPrivateKeyFromDatabase(userId) {
    const mysql = require('mysql2/promise');
    require('dotenv').config({ path: '/var/www/.env' });

    const db = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME
    });

    try {
        const [rows] = await db.query(
            'SELECT private_key, crypto_address FROM users WHERE id = ?',
            [userId]
        );

        if (rows.length === 0) {
            throw new Error(`User ${userId} not found`);
        }

        const encryptedPrivateKey = rows[0].private_key;
        const cryptoAddress = rows[0].crypto_address;

        // Decrypt the private key
        const privateKey = decryptPrivateKey(encryptedPrivateKey);

        console.log(`✅ User ${userId}:`);
        console.log(`   Address: ${cryptoAddress}`);
        console.log(`   Private Key: ${privateKey}`);

        return {
            address: cryptoAddress,
            privateKey: privateKey
        };

    } finally {
        await db.end();
    }
}

// Test the decryption
if (require.main === module) {
    // Example: Test encryption and decryption
    const testPrivateKey = '0x1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef';

    console.log('🔐 Testing Encryption/Decryption:');
    console.log('Original:', testPrivateKey);

    const encrypted = encryptPrivateKey(testPrivateKey);
    console.log('Encrypted:', encrypted);

    const decrypted = decryptPrivateKey(encrypted);
    console.log('Decrypted:', decrypted);

    console.log('\n✅ Match:', testPrivateKey === decrypted);

    // Uncomment to test with real database
    // getPrivateKeyFromDatabase(3).catch(console.error);
}

// Export functions for use in other modules
module.exports = {
    decryptPrivateKey,
    encryptPrivateKey,
    getPrivateKeyFromDatabase
};
