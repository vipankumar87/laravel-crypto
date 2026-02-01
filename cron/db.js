import mysql from 'mysql2/promise';
import dotenv from 'dotenv';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

// Load environment variables from Laravel .env
dotenv.config({
    path: path.resolve(__dirname, '..', '.env')
});

class Database {
    constructor() {
        this.connection = null;
    }

    async init() {
        try {
            this.connection = await mysql.createConnection({
                host: process.env.DB_HOST,
                user: process.env.DB_USERNAME,
                password: process.env.DB_PASSWORD,
                database: process.env.DB_DATABASE,
            });

            console.log('Connected to MySQL');

            // Create usdt_transactions table if not exists (for blockchain sync tracking)
            await this.createTable();
        } catch (err) {
            console.error('Database connection failed:', err.message);
            process.exit(1);
        }
    }

    async createTable() {
        const createTableQuery = `
            CREATE TABLE IF NOT EXISTS usdt_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                block_number BIGINT,
                time_stamp BIGINT,
                hash VARCHAR(255) UNIQUE,
                nonce INT,
                block_hash VARCHAR(255),
                sender VARCHAR(42),
                contract_address VARCHAR(42),
                recipient VARCHAR(42),
                amount VARCHAR(200),
                token_name VARCHAR(50),
                token_symbol VARCHAR(50),
                token_decimal INT,
                transaction_index INT,
                gas BIGINT,
                gas_price BIGINT,
                gas_used BIGINT,
                cumulative_gas_used BIGINT,
                confirmations INT,
                status ENUM('pending', 'synced') DEFAULT 'pending'
            ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        `;

        try {
            await this.connection.execute(createTableQuery);
            console.log("Table checked/created successfully");
        } catch (err) {
            console.error("Error creating table:", err.message);
        }
    }

    /**
     * Fetch completed withdrawal transactions that haven't been sent on-chain yet.
     * Joins with users table to get the wallet address for sending funds.
     */
    async fetchPendingWithdrawlRequests() {
        const sql = `
            SELECT
                t.id,
                t.user_id,
                t.transaction_id,
                t.currency,
                t.amount,
                t.fee,
                t.net_amount,
                u.bep_wallet_address AS wallet_address,
                u.name AS user_name,
                u.email
            FROM transactions t
            JOIN users u ON u.id = t.user_id
            WHERE t.type = 'withdrawal'
              AND t.status = 'completed'
              AND t.tx_hash IS NULL
              AND u.bep_wallet_address IS NOT NULL
              AND u.bep_wallet_address != ''
            ORDER BY t.id ASC
        `;

        try {
            const [results] = await this.connection.execute(sql);
            console.log(`Fetched ${results.length} unsettled withdrawal(s)`);
            return results;
        } catch (err) {
            console.error('Error fetching pending withdrawals:', err.message);
            return [];
        }
    }

    /**
     * Update the transaction with the on-chain tx hash after successful transfer.
     */
    async settleWithdrawlRequest(txHash, txId) {
        if (!txHash || !txId) {
            console.error("Error: txHash or txId is undefined!");
            return false;
        }

        const updateSql = "UPDATE transactions SET tx_hash = ?, updated_at = NOW() WHERE id = ?";

        try {
            await this.connection.execute(updateSql, [txHash, txId]);
            console.log(`Transaction #${txId} settled with hash: ${txHash}`);
            return true;
        } catch (err) {
            console.error(`Error settling transaction #${txId}:`, err.message);
            return false;
        }
    }

    // Insert blockchain transaction data for sync tracking
    async insertData(data) {
        const sql = `
            INSERT IGNORE INTO usdt_transactions
            (block_number, time_stamp, hash, nonce, block_hash, sender, contract_address, recipient, amount, token_name, token_symbol, token_decimal, transaction_index, gas, gas_price, gas_used, cumulative_gas_used, confirmations)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        `;

        const values = [
            data.blockNumber ?? null,
            data.timeStamp ?? null,
            data.hash ?? null,
            data.nonce ?? null,
            data.blockHash ?? null,
            data.from ?? null,
            data.contractAddress ?? null,
            data.to ?? null,
            data.value ?? null,
            data.tokenName ?? null,
            data.tokenSymbol ?? null,
            data.tokenDecimal ?? null,
            data.transactionIndex ?? null,
            data.gas ?? null,
            data.gasPrice ?? null,
            data.gasUsed ?? null,
            data.cumulativeGasUsed ?? null,
            data.confirmations ?? null,
        ];

        try {
            const [results] = await this.connection.execute(sql, values);
            console.log('Data inserted with ID:', results.insertId);
            return results.insertId;
        } catch (err) {
            console.error('Error inserting data:', err.message);
        }
    }

    async fetchData() {
        const sql = 'SELECT * FROM usdt_transactions WHERE status="pending" ORDER BY id ASC';
        try {
            const [results] = await this.connection.execute(sql);
            return results;
        } catch (err) {
            console.error('Error fetching data:', err.message);
            return [];
        }
    }

    async fetchLastBlock() {
        if (!this.connection) {
            console.error('Database connection is not ready');
            return null;
        }
        const sql = "SELECT block_number FROM usdt_transactions ORDER BY id DESC LIMIT 1";
        try {
            const [results] = await this.connection.execute(sql);
            return results.length > 0 ? results[0].block_number : 0;
        } catch (err) {
            console.error("Error fetching last block:", err.message);
            return 0;
        }
    }

    async closeConnection() {
        try {
            if (this.connection) {
                await this.connection.end();
                console.log('Database connection closed.');
            }
        } catch (err) {
            console.error('Error closing connection:', err.message);
        }
    }
}

const db = new Database();
export default db;
