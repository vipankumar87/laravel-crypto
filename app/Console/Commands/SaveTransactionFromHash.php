<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserTransaction;
use App\Models\User;

class SaveTransactionFromHash extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto:save-transaction {tx_hash} {--user_id=} {--from=} {--to=} {--amount=} {--status=transferred}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save a transaction to the database using transaction hash and details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $txHash = $this->argument('tx_hash');
        $userId = $this->option('user_id');
        $from = $this->option('from');
        $to = $this->option('to');
        $amount = $this->option('amount');
        $status = $this->option('status');

        $this->info("Processing transaction: {$txHash}");

        // Check if transaction already exists
        $existingTx = UserTransaction::where('tx_hash', $txHash)->first();
        if ($existingTx) {
            $this->warn("Transaction {$txHash} already exists in database (ID: {$existingTx->id})");
            return 0;
        }

        // If user_id not provided, try to find user by from address
        if (!$userId && $from) {
            $user = User::where('crypto_address', $from)->first();
            if ($user) {
                $userId = $user->id;
                $this->info("Found user ID {$userId} for address {$from}");
            }
        }

        // Fetch transaction details from blockchain if not all details provided
        if (!$userId || !$from || !$to || !$amount) {
            $this->info("Fetching transaction details from blockchain...");
            $txDetails = $this->fetchTransactionFromBlockchain($txHash);
            
            if (!$txDetails) {
                $this->error("Failed to fetch transaction details from blockchain");
                return 1;
            }

            // Use blockchain data if options not provided
            $from = $from ?: $txDetails['from'];
            $to = $to ?: $txDetails['to'];
            $amount = $amount ?: $txDetails['amount'];
            $blockNumber = $txDetails['blockNumber'];

            // Try to find user again with blockchain data
            if (!$userId) {
                $user = User::where('crypto_address', $from)->first();
                if ($user) {
                    $userId = $user->id;
                    $this->info("Found user ID {$userId} for address {$from}");
                } else {
                    $this->error("Could not find user with address {$from}");
                    return 1;
                }
            }
        } else {
            $blockNumber = null;
        }

        // Validate required fields
        if (!$userId || !$from || !$to || !$amount) {
            $this->error("Missing required transaction details:");
            $this->error("  user_id: " . ($userId ?: 'MISSING'));
            $this->error("  from: " . ($from ?: 'MISSING'));
            $this->error("  to: " . ($to ?: 'MISSING'));
            $this->error("  amount: " . ($amount ?: 'MISSING'));
            return 1;
        }

        // Save transaction
        try {
            $transaction = UserTransaction::create([
                'user_id' => $userId,
                'from_address' => $from,
                'to_address' => $to,
                'amount' => $amount,
                'token' => 'N/a',
                'tx_hash' => $txHash,
                'status' => $status,
                'block_number' => $blockNumber ?? null,
            ]);

            $this->info("✅ Transaction saved successfully!");
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $transaction->id],
                    ['User ID', $transaction->user_id],
                    ['From', $transaction->from_address],
                    ['To', $transaction->to_address],
                    ['Amount', $transaction->amount],
                    ['Status', $transaction->status],
                    ['TX Hash', $transaction->tx_hash],
                    ['Block', $transaction->block_number ?? 'N/A'],
                ]
            );

            // Update user pending_payment status
            User::where('id', $userId)->update(['pending_payment' => 0]);
            $this->info("Updated user pending_payment status to 0");

            // Run the auto-adjust command
            $this->info("Running auto-adjust command...");
            $this->call('app:auto-adjust-real-time-payment-to-investors');

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to save transaction: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Fetch transaction details from blockchain using BSC RPC
     *
     * @param string $txHash
     * @return array|null
     */
    private function fetchTransactionFromBlockchain(string $txHash): ?array
    {
        $bscRpc = env('BSC_RPC', 'https://bsc-dataseed.binance.org/');
        $usdtAddress = strtolower(env('USDT_ADDRESS', '0x55d398326f99059ff775485246999027b3197955'));
        
        try {
            // Fetch transaction receipt
            $receipt = $this->rpcCall($bscRpc, 'eth_getTransactionReceipt', [$txHash]);
            
            if (!$receipt) {
                $this->error("Transaction not found on blockchain");
                return null;
            }
            
            // Fetch transaction details
            $tx = $this->rpcCall($bscRpc, 'eth_getTransactionByHash', [$txHash]);
            
            if (!$tx) {
                $this->error("Transaction details not found");
                return null;
            }
            
            $from = $tx['from'];
            $to = $tx['to'];
            $amount = '0';
            
            // Parse USDT transfer from logs (ERC20/BEP20 Transfer event)
            // Transfer event signature: keccak256("Transfer(address,address,uint256)")
            $transferEventSignature = '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef';
            
            if (isset($receipt['logs']) && is_array($receipt['logs'])) {
                foreach ($receipt['logs'] as $log) {
                    // Check if this is a Transfer event from USDT contract
                    if (isset($log['topics'][0]) && 
                        $log['topics'][0] === $transferEventSignature &&
                        strtolower($log['address']) === $usdtAddress) {
                        
                        // Decode Transfer event
                        // topics[1] = from address (indexed)
                        // topics[2] = to address (indexed)
                        // data = amount (uint256)
                        
                        if (isset($log['topics'][1], $log['topics'][2], $log['data'])) {
                            $from = '0x' . substr($log['topics'][1], 26); // Remove padding
                            $to = '0x' . substr($log['topics'][2], 26);   // Remove padding
                            
                            // Convert hex amount to decimal (18 decimals for USDT)
                            $amountHex = $log['data'];
                            $amount = $this->hexToDecimal($amountHex, 18);
                        }
                        break;
                    }
                }
            }
            
            return [
                'from' => $from,
                'to' => $to,
                'amount' => $amount,
                'blockNumber' => hexdec($receipt['blockNumber']),
                'status' => $receipt['status'] === '0x1' ? 'success' : 'failed',
            ];
            
        } catch (\Exception $e) {
            $this->error("Blockchain fetch error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Make RPC call to BSC node
     *
     * @param string $rpcUrl
     * @param string $method
     * @param array $params
     * @return mixed
     */
    private function rpcCall(string $rpcUrl, string $method, array $params)
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => 1,
        ];
        
        $ch = curl_init($rpcUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$response) {
            throw new \Exception("RPC call failed: HTTP {$httpCode}");
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            throw new \Exception("RPC error: " . ($result['error']['message'] ?? 'Unknown error'));
        }
        
        return $result['result'] ?? null;
    }
    
    /**
     * Convert hex value to decimal with decimals
     *
     * @param string $hex
     * @param int $decimals
     * @return string
     */
    private function hexToDecimal(string $hex, int $decimals = 18): string
    {
        // Remove 0x prefix if present
        $hex = str_replace('0x', '', $hex);
        
        if (empty($hex)) {
            return '0';
        }
        
        // Convert hex to decimal using GMP for large numbers
        $value = gmp_init($hex, 16);
        $divisor = gmp_pow(10, $decimals);
        
        // Get integer and fractional parts
        $integerPart = gmp_div($value, $divisor);
        $remainder = gmp_mod($value, $divisor);
        
        // Format with decimals
        $fractionalPart = str_pad(gmp_strval($remainder), $decimals, '0', STR_PAD_LEFT);
        $fractionalPart = rtrim($fractionalPart, '0') ?: '0';
        
        return gmp_strval($integerPart) . '.' . $fractionalPart;
    }
}
