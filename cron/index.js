#!/usr/bin/env node
import Web3 from 'web3';
import fetch from 'node-fetch';
import db from './db.js';
import dotenv from 'dotenv';
import CryptoJS from 'crypto-js';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

dotenv.config({
    path: path.resolve(__dirname, '../','../web3-wallet-creation/' , '.env')
});

// const PRIVATE_KEY = CryptoJS.AES.decrypt(process.env.PRIVATE_KEY, process.env.SECRET).toString(CryptoJS.enc.Utf8);
const PRIVATE_KEY = process.env.MAIN_WALLET_PRIVATE_KEY;
const FROM_ADDRESS = process.env.MAIN_WALLET_ADDRESS;
const USDT_CONTRACT = '0x55d398326f99059ff775485246999027b3197955';

console.log(`Using FROM_ADDRESS: ${FROM_ADDRESS}`);
console.log(`Using USDT_CONTRACT: ${USDT_CONTRACT}`);

const RPC_URL = "https://bsc-mainnet.public.blastapi.io";
const BSC_SCAN_API_KEY = process.env.BSC_SCAN_API_KEY || "RCFWXA64ZZDFH8JT3CQN585V16RS9F6RKE";

const web3 = new Web3(RPC_URL);

// USDT BEP-20 ABI (Minimal transfer)
const usdtAbi = [
    {
        "constant": false,
        "inputs": [
            { "name": "_to", "type": "address" },
            { "name": "_value", "type": "uint256" }
        ],
        "name": "transfer",
        "outputs": [{ "name": "", "type": "bool" }],
        "type": "function"
    }
];

async function sendUSDT(toAddress, amount_transfer, txId) {
    try {
        const usdtContract = new web3.eth.Contract(usdtAbi, USDT_CONTRACT);
        const amount = web3.utils.toBigInt(Math.round(amount_transfer * (10 ** 18)));
        const nonce = await web3.eth.getTransactionCount(FROM_ADDRESS, "latest");
        if (nonce === null) throw new Error("Failed to get nonce!");

        const gasPrice = await web3.eth.getGasPrice();
        const gasLimit = 100000;

        const data = usdtContract.methods.transfer(toAddress, amount).encodeABI();

        const tx = {
            from: FROM_ADDRESS,
            to: USDT_CONTRACT,
            gas: gasLimit,
            gasPrice,
            nonce,
            data,
            value: "0x0",
            chainId: 56
        };

        const signedTx = await web3.eth.accounts.signTransaction(tx, PRIVATE_KEY);

        console.log(`Sending ${amount_transfer} USDT to ${toAddress} (txn #${txId})...`);
        let receipt = await web3.eth.sendSignedTransaction(signedTx.rawTransaction);
        console.log(`Transaction sent: ${receipt.transactionHash}`);

        await db.settleWithdrawlRequest(receipt.transactionHash, txId);
        return true;
    } catch (error) {
        console.error(`Error sending USDT for txn #${txId}:`, error.message);
        return false;
    }
}

// Main execution
await db.init();

const pendingWithdrawals = await db.fetchPendingWithdrawlRequests();

if (pendingWithdrawals.length === 0) {
    console.log("No unsettled withdrawals to process.");
} else {
    let settled = 0;
    let failed = 0;

    for (const tx of pendingWithdrawals) {
        console.log(`Processing: #${tx.id} | ${tx.user_name} | ${tx.currency} ${tx.net_amount} -> ${tx.wallet_address}`);

        if (tx.currency === 'USDT') {
            const success = await sendUSDT(tx.wallet_address, parseFloat(tx.net_amount), tx.id);
            success ? settled++ : failed++;
        } else {
            // DOGE withdrawals - skip on-chain for now (no BSC DOGE contract configured)
            console.log(`Skipping DOGE withdrawal #${tx.id} - DOGE on-chain transfer not configured`);
            failed++;
        }
    }

    console.log(`Settlement complete: ${settled} settled, ${failed} failed/skipped`);
}

await db.closeConnection();
console.log(new Date());
process.exit(0);
