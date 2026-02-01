#!/usr/bin/env node
// import 'dotenv/config'; // Load environment variables
import Web3 from 'web3';
import fetch from 'node-fetch';
import db from './db.js'; // Ensure the file extension is included
import dotenv from 'dotenv';
import CryptoJS from 'crypto-js';

console.log(process.env.PRIVATE_KEY, process.env.SECRET);
const PRIVATE_KEY = CryptoJS.AES.decrypt(process.env.PRIVATE_KEY, process.env.SECRET).toString(CryptoJS.enc.Utf8);

const FROM_ADDRESS=process.env.FROM_ADDRESS;
const USDT_CONTRACT=process.env.USDT_CONTRACT;
const RPC_URL="https://bsc-mainnet.public.blastapi.io"
const BSC_SCAN_API_KEY="RCFWXA64ZZDFH8JT3CQN585V16RS9F6RKE"

const web3 = new Web3(RPC_URL);
const usdtContractAddress = USDT_CONTRACT;

// USDT BEP-20 ABI (Minimal)
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

// Sender & Receiver Details
const privateKey = PRIVATE_KEY;
const fromAddress = FROM_ADDRESS;
//const toAddress = "0x32F2560dBBE6DdBCA246D0E38C8F11a06fDBdA9f"; // Replace with recipient wallet address

async function sendUSDT(toAddress, amount_transfer, txId) {
    try {
        // Initialize Contract
        const usdtContract = new web3.eth.Contract(usdtAbi, usdtContractAddress);
        const BN = web3.utils.BN;
        const amount = web3.utils.toBigInt(amount_transfer*(10**18));// / web3.utils.toBigInt(10 ** 12);
        const nonce = await web3.eth.getTransactionCount(fromAddress, "latest");
        if (nonce === null) throw new Error("Failed to get nonce!");

        const gasPrice = await web3.eth.getGasPrice();
        const gasLimit = 100000; // Adjust if needed
        // 🔹 Encode Transaction Data
        const data = usdtContract.methods.transfer(toAddress, amount).encodeABI();

        // 🔹 Prepare Transaction
        const tx = {
            from: fromAddress,
            to: usdtContractAddress,
            gas: gasLimit,
            gasPrice,
            nonce,
            data,
            value: "0x0", // Must be 0 for token transfers
            chainId: 56 // Binance Smart Chain ID
        };

        // 🔹 Sign Transaction
        const signedTx = await web3.eth.accounts.signTransaction(tx, privateKey);
	
	    console.log("Starting transfer");
        let receipt = await web3.eth.sendSignedTransaction(signedTx.rawTransaction);
        // Simulate transaction
	    console.log("send complete", receipt.transactionHash);
            console.log("✅ Transaction sent with hash:", receipt.transactionHash);
            await db.settleWithdrawlRequest(receipt.transactionHash, txId);
    } catch (error) {
        console.error("❌ Error sending USDT:", error);
    }
}

async function getUSDTTransactions() {
    const url = `https://api.bscscan.com/api?module=account&action=tokentx&contractaddress=${usdtContractAddress}&address=${fromAddress}&startblock=0&endblock=99999999&sort=desc&apikey=${BSC_SCAN_API_KEY}`;

    const response = await fetch(url);
    const data = await response.json();

    if (data.status === "1") {
        data.result = data.result.filter((result) => {
            let minAmount = 1 * 10**18;
            if(result.value >= minAmount){
                result.value = result.value / 10**18;
                return result;
            }
        });

        data.result.map((result)=>{
            result.minAmount = 1 * 10**18;
            console.log(result);
        })
        // console.log(data.result);
    } else {
        console.error("Error:", data.message);
    }
}
async function getLast10MinutesTransactions() {
    try {
        // Get the latest block number
        const currentTimestamp = Math.floor(Date.now() / 1000); // Convert to seconds

        // Calculate timestamp 10 minutes ago
        const pastTimestamp = currentTimestamp - (60*60*10); // 600 seconds = 10 minutes
        let startblock = 0;
        let endblock = 99999999;

        let fetchBlocks = await db.fetchLastBlock();
        if(fetchBlocks){
            //startblock = fetchBlocks;
        }
        //0x32F2560dBBE6DdBCA246D0E38C8F11a06fDBdA9f
        // Fetch transactions from BscScan API
        const receiver = '0x32F2560dBBE6DdBCA246D0E38C8F11a06fDBdA9f';
        // const url = `https://api.bscscan.com/api?module=account&action=tokentx&contractaddress=${usdtContractAddress}&address=${fromAddress}&startblock=0&endblock=99999999&sort=desc&apikey=${BSC_SCAN_API_KEY}`;
        // const url = `https://api.bscscan.com/api?module=account&action=tokentx&contractaddress=${usdtContractAddress}&address=${fromAddress}&startblock=${startblock}&endblock=${endblock}&sort=desc&apikey=${BSC_SCAN_API_KEY}`;
        const url = `https://api.bscscan.com/api?module=account&action=tokentx&contractaddress=${usdtContractAddress}&address=${receiver}&startblock=${startblock}&endblock=${endblock}&sort=desc&apikey=${BSC_SCAN_API_KEY}`;
        const api = await fetch(url);
        const response = await api.json();
            
        if (response.status === "1") {
            
            return response.result.filter(tx => {
                let minAmount = 1 * 10**18;
                if(tx.value >= minAmount){
                    tx.value = tx.value / 10**18;
                } else {
                    return false;
                }
                return Number(tx.timeStamp) >= pastTimestamp
            });
        } else {
            console.log(response);
            console.log("❌ Error:", response.message);
        }
    } catch (error) {
        console.error("❌ Error fetching transactions:", error);
    }
}

async function syncInvestments() {
    try {
        const transactions = await db.fetchData();
        console.log("📊 Total transactions:", transactions.length);
        console.log("📈 Total investments:", transactions.length);

        for(const tx of transactions){
            await db.settleInvestments(tx);
        }
        const totalInvestments = transactions.reduce((total, tx) => {
            return total + parseFloat(tx.amount);
        }, 0);

        console.log("💰 Total USDT investments:", totalInvestments);
    } catch (error) {
        console.error("❌ Error syncing investments:", error);
    }
}
function simulateSendTransaction(signedTx, callback, txId) {
    return new Promise((resolve, reject) => {
        console.log("⏳ Simulating transaction...");

        setTimeout(async () => {
            const fakeReceipt = {
                transactionHash: "0x" + Math.random().toString(16).substr(2, 64), // Generate a random TX hash
                status: true, // Simulate a successful transaction
                gasUsed: 21000,
                blockNumber: Math.floor(Math.random() * 1000000),
            };

            console.log("✅ Simulated transaction hash:", fakeReceipt.transactionHash);

            if (callback) {
                await callback(fakeReceipt.transactionHash, txId);
            }

            resolve(fakeReceipt);
        }, 2000); // Simulate network delay of 2 seconds
    }).catch(console.error);
}

// Execute
// sendUSDT();
await db.init();

const pendingWithdrawlRequests = await db.fetchPendingWithdrawlRequests();
for(const tx of pendingWithdrawlRequests){
    // await db.settleWithdrawlRequest(Math.random().toString(36).substring(7), tx.id);
    // await simulateSendTransaction(Math.random().toString(36), db.settleWithdrawlRequest.bind(db), tx.id);
console.log([tx.account_id, tx.transferrable, tx.id]);
    await sendUSDT(tx.account_id, tx.transferrable, tx.id);
}

console.log("settlement complete");

//console.log(pendingWithdrawlRequests);
// await getUSDTTransactions();
//const transactions = await getLast10MinutesTransactions();
// await Promise.all(transactions.map(tx => db.insertData(tx)));
//let i = 1;
//console.log("creating investments");
//console.log("total transactions fetched", transactions.length, FROM_ADDRESS);
/*console.log(transactions)
for (const tx of transactions) {
    try {
        await db.insertData(tx);
        i++;
    } catch (error) {
        console.error("❌ Error inserting:", error);
    }
}
// await syncInvestments();
*/	
console.log(new Date());
console.log("✅ All transactions processed sequentially");
process.exit(0);
