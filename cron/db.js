import mysql from 'mysql2/promise';
import dotenv from 'dotenv';

// Load environment variables
dotenv.config({
    path: '/var/www/optimusminning/project/.env'
});

class Database {
    constructor() {
        this.connection = null;
        this.init();
    }

    async init() {
        try {
            this.connection = await mysql.createConnection({
                host: process.env.DB_HOST,
                user: process.env.DB_USERNAME,
                password: process.env.DB_PASSWORD,
                database: process.env.DB_DATABASE,
                namedPlaceholders: true, // ✅ Enable named placeholders
            });

            console.log('✅ Connected to MySQL');

            // Create table if not exists
            await this.createTable();
        } catch (err) {
            console.error('❌ Database connection failed:', err);
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
            console.log("✅ Table checked/created successfully");
        } catch (err) {
            console.error("❌ Error creating table:", err);
        }
    }

    async fetchPendingWithdrawlRequests(){
        const sql = 'SELECT * FROM requests WHERE status="approved" ORDER BY id ASC';
        try {
            const [results] = await this.connection.execute(sql);
            console.log("fetched transactions", results);
            return results;
        } catch (err) {
            console.error('❌ Error fetching data:', err);
        }
    }

    async settleWithdrawlRequest(txHash, txId) {
        console.log("txHash:", txHash);
        console.log("txId:", txId);
    
        if (!txHash || !txId) {
            console.error("❌ Error: txHash or txId is undefined!");
            return;
        }
    
        // Query to fetch user details from the request
        const userSql = `SELECT DISTINCT u.id AS user_id, u.email, r.transferrable
                         FROM requests r 
                         JOIN users u ON u.walletAddress = r.account_id
                         WHERE r.id = ?`;
    
        const updateSql = "UPDATE requests SET status = ?, transaction_hash = ? WHERE id = ?";
        const insertSql = `INSERT INTO transactions (user_id, email, amount, type, profit, txnid, created_at, updated_at)
                            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())`;
        console.log("reacherd", [txId]);
        try {
            // Fetch user details
            console.log("reacherd 1");
            // this.connection.execute(userSql, [txId]);
            const [userResult] = await this.connection.execute(userSql, [txId]);
            console.log("reacherd 2");
            
            if (userResult.length === 0) {
                console.error("❌ Error: No matching user found for request ID:", txId);
                return;
            }
            // Extract user details
            console.log("userResult", userResult);
            const { user_id, email, transferrable } = userResult[0];
    
            // Update the request table
            await this.connection.execute(updateSql, ['settled', txHash, txId]);
            console.log("✅ Withdrawal request updated to 'settled' status.");
            // Insert transaction record
            const transactionType = 'invest';
            const profitType = 'plus'; // Assuming it's a withdrawal (money out)
    
            await this.connection.execute(insertSql, [user_id, email, transferrable, transactionType, profitType, txHash]);
    
            console.log("✅ Withdrawal request settled and transaction recorded.");
        } catch (err) {
            console.error('❌ Error processing transaction:', err);
        }
    }


    // Insert Data
    async insertData(data) {
        const sql = `
        INSERT IGNORE INTO usdt_transactions 
        (block_number, time_stamp, hash, nonce, block_hash, sender, contract_address, recipient, amount, token_name, token_symbol, token_decimal, transaction_index, gas, gas_price, gas_used, cumulative_gas_used, confirmations) 
        VALUES (:block_number, :time_stamp, :hash, :nonce, :block_hash, :sender, :contract_address, :recipient, :amount, :token_name, :token_symbol, :token_decimal, :transaction_index, :gas, :gas_price, :gas_used, :cumulative_gas_used, :confirmations)
    `;
        Object.keys(data).forEach(key => {
            if (data[key] === undefined) {
                data[key] = null;
            }
        });
        
        try {
            const [results] = await this.connection.execute(sql, {
                block_number: data.blockNumber,
                time_stamp: data.timeStamp,
                hash: data.hash,
                nonce: data.nonce,
                block_hash: data.blockHash,
                sender: data.from,
                contract_address: data.contractAddress,
                recipient: data.to,
                amount: data.value,
                token_name: data.tokenName,
                token_symbol: data.tokenSymbol,
                token_decimal: data.tokenDecimal,
                transaction_index: data.transactionIndex,
                gas: data.gas,
                gas_price: data.gasPrice,
                gas_used: data.gasUsed,
                cumulative_gas_used: data.cumulativeGasUsed,
                confirmations: data.confirmations
            });
            console.log('✅ Data inserted successfully with ID:', results.insertId);
            return results.insertId;
        } catch (err) {
            console.error('❌ Error inserting data:', err);
        }
    }

    // Fetch Data
    async fetchData() {
        const sql = 'SELECT * FROM usdt_transactions WHERE status="pending" ORDER BY id ASC';

        try {
            const [results] = await this.connection.execute(sql);
            return results;
        } catch (err) {
            console.error('❌ Error fetching data:', err);
        }
    }

    // Fetch Last Block Number
    async fetchLastBlock() {
        if (!this.connection) {
            console.error('❌ Database connection is not ready');
            return null;
        }
        const sql = "SELECT block_number FROM usdt_transactions ORDER BY id DESC LIMIT 1";

        try {
            const [results] = await this.connection.execute(sql);
            return results.length > 0 ? results[0].block_number : 0;
        } catch (err) {
            console.error("❌ Error fetching last block:", err);
        }
    }

    // Close Connection
    async closeConnection() {
        try {
            await this.connection.end();
            console.log('✅ Database connection closed.');
        } catch (err) {
            console.error('❌ Error closing the connection:', err);
        }
    }

    // async settleInvestments($tx){
    //     const sql = "insert into invests"
    // }
}

// Export Database Instance
const db = new Database();
export default db;
