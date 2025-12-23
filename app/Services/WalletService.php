<?php

namespace App\Services;

use Exception;

class WalletService
{
    /**
     * Master mnemonic seed phrase from environment
     * This should be stored securely in .env
     */
    private string $masterSeed;

    public function __construct()
    {
        $this->masterSeed = env('MASTER_MNEMONIC', '');

        if (empty($this->masterSeed)) {
            throw new Exception('MASTER_MNEMONIC is not set in .env file');
        }
    }

    /**
     * Generate a deterministic wallet address for a user
     * Uses HD wallet derivation path: m/44'/60'/0'/0/{userId}
     * This matches the logic in wallet-generator.js
     *
     * @param int $userId
     * @return array
     */
    public function generateUserWallet(int $userId): array
    {
        // Use Node.js script to generate wallet with proper HD derivation
        // This ensures consistency with wallet-generator.js logic
        $nodeScript = base_path('../web3-wallet-creation/generate-single-wallet.js');
        $envPath = base_path('../web3-wallet-creation/.env');
        
        // Read and parse the .env file
        $envVars = [];
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            $lines = explode("\n", $envContent);
            
            foreach ($lines as $line) {
                $line = trim($line);
                // Skip empty lines and comments
                if (empty($line) || strpos($line, '#') === 0) {
                    continue;
                }
                
                // Parse KEY=VALUE format
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    // Remove quotes if present
                    $value = trim($value, '"\'');
                    $envVars[$key] = escapeshellarg($value);
                }
            }
        }
        
        // Build environment string for command
        $envString = '';
        foreach ($envVars as $key => $value) {
            $envString .= "{$key}={$value} ";
        }
        
        // Execute Node.js script to generate wallet
        $command = $envString . 'node ' . escapeshellarg($nodeScript) . ' ' . escapeshellarg((string)$userId) . ' 2>&1';
        $output = shell_exec($command);
        
        // Log the command and output for debugging
        \Log::info("Wallet generation command: {$command}");
        \Log::info("Wallet generation output: {$output}");
        
        if (empty($output)) {
            throw new Exception('Failed to generate wallet: No output from Node.js script');
        }
        
        $wallet = json_decode($output, true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !isset($wallet['address'], $wallet['privateKey'])) {
            throw new Exception('Failed to generate wallet: Invalid response from Node.js script');
        }
        
        return [
            'address' => $wallet['address'],
            'private_key' => $wallet['privateKey'],
            'public_key' => $wallet['publicKey'] ?? '',
        ];
    }

    /**
     * Generate public key from private key
     * NOTE: This is a simplified version. For production, use proper secp256k1 library
     *
     * @param string $privateKey
     * @return string
     */
    private function generatePublicKey(string $privateKey): string
    {
        // This is a simplified implementation
        // In production, you should use proper elliptic curve cryptography
        return hash('sha256', 'public:' . $privateKey);
    }

    /**
     * Generate Ethereum-compatible address from public key
     *
     * @param string $publicKey
     * @return string
     */
    private function generateAddress(string $publicKey): string
    {
        // Keccak-256 hash of the public key
        $hash = $this->keccak256($publicKey);

        // Take last 20 bytes (40 characters) and prepend 0x
        $address = '0x' . substr($hash, -40);

        // Apply checksum (EIP-55)
        return $this->toChecksumAddress($address);
    }

    /**
     * Simplified Keccak-256 implementation
     * NOTE: For production, use kornrunner/keccak library or similar
     *
     * @param string $data
     * @return string
     */
    private function keccak256(string $data): string
    {
        // This is a placeholder - use proper Keccak-256 library in production
        // For now, using SHA3-256 as approximation
        return hash('sha3-256', $data);
    }

    /**
     * Convert address to EIP-55 checksum format
     *
     * @param string $address
     * @return string
     */
    private function toChecksumAddress(string $address): string
    {
        $address = strtolower(str_replace('0x', '', $address));
        $hash = $this->keccak256($address);
        $checksum = '0x';

        for ($i = 0; $i < 40; $i++) {
            if (intval($hash[$i], 16) >= 8) {
                $checksum .= strtoupper($address[$i]);
            } else {
                $checksum .= $address[$i];
            }
        }

        return $checksum;
    }

    /**
     * Encrypt private key before storing in database
     * Uses AES-256-CBC encryption compatible with Node.js
     *
     * @param string $privateKey
     * @return string
     */
    public function encryptPrivateKey(string $privateKey): string
    {
        $encryptionKey = env('WALLET_ENCRYPTION_KEY');

        if (empty($encryptionKey)) {
            throw new \Exception('WALLET_ENCRYPTION_KEY is not set in .env file');
        }

        // Generate a random IV (16 bytes for AES-256-CBC)
        $iv = random_bytes(16);

        // Encrypt using AES-256-CBC
        $encrypted = openssl_encrypt(
            $privateKey,
            'AES-256-CBC',
            $encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        // Combine IV and encrypted data, then base64 encode
        // Format: base64(iv + encrypted_data)
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt private key from database
     * Compatible with Node.js crypto module
     *
     * @param string $encryptedPrivateKey
     * @return string
     */
    public function decryptPrivateKey(string $encryptedPrivateKey): string
    {
        $encryptionKey = env('WALLET_ENCRYPTION_KEY');

        if (empty($encryptionKey)) {
            throw new \Exception('WALLET_ENCRYPTION_KEY is not set in .env file');
        }

        // Decode the base64 string
        $data = base64_decode($encryptedPrivateKey);

        // Extract IV (first 16 bytes) and encrypted data
        $iv = substr($data, 0, 16);
        $encryptedData = substr($data, 16);

        // Decrypt using AES-256-CBC
        $decrypted = openssl_decrypt(
            $encryptedData,
            'AES-256-CBC',
            $encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            throw new \Exception('Failed to decrypt private key');
        }

        return $decrypted;
    }

    /**
     * Generate wallets for all users who don't have one
     *
     * @return int Number of wallets generated
     */
    public function generateWalletsForAllUsers(): int
    {
        $users = \App\Models\User::whereNull('crypto_address')->get();
        $count = 0;

        foreach ($users as $user) {
            $wallet = $this->generateUserWallet($user->id);

            $user->update([
                'crypto_address' => $wallet['address'],
                'private_key' => $this->encryptPrivateKey($wallet['private_key']),
                'public_key' => $wallet['public_key'],
            ]);

            $count++;
        }

        return $count;
    }
}
