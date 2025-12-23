<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SweepCryptoBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto:sweep';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sweep USDT balances from user wallets to main wallet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting USDT sweep process...');
        
        $sweepScript = base_path('../web3-wallet-creation/sweepCommand');
        $envPath = base_path('../web3-wallet-creation/.env');
        
        // Check if script exists
        if (!file_exists($sweepScript)) {
            $this->error("Sweep script not found at: {$sweepScript}");
            return 1;
        }
        
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
        
        // Execute the sweep script
        $command = $envString . 'node ' . escapeshellarg($sweepScript) . ' 2>&1';
        
        $this->line('Executing sweep script...');
        $this->newLine();
        
        // Execute and stream output in real-time
        $descriptorspec = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w']   // stderr
        ];
        
        $process = proc_open($command, $descriptorspec, $pipes);
        
        if (is_resource($process)) {
            fclose($pipes[0]);
            
            // Read stdout
            while ($line = fgets($pipes[1])) {
                $this->line(trim($line));
            }
            fclose($pipes[1]);
            
            // Read stderr
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            
            if (!empty($errors)) {
                $this->error('Errors:');
                $this->error($errors);
            }
            
            $returnCode = proc_close($process);
            
            $this->newLine();
            if ($returnCode === 0) {
                $this->info('✅ Sweep process completed successfully!');
                return 0;
            } else {
                $this->error("❌ Sweep process failed with exit code: {$returnCode}");
                return 1;
            }
        } else {
            $this->error('Failed to execute sweep script');
            return 1;
        }
    }
}
