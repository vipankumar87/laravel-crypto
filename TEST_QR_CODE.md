# QR Code Payment Testing Guide

## What's Happening

The crypto payment page now displays a **QR code containing the user's public crypto address**.

## Current Setup

### User #3 Details:
- **Username**: `user`
- **Crypto Address**: `0x02eDc139D426D356d62B22A0C7613DBf30209882`
- **Network**: BNB Smart Chain (BEP20)

## How to Test

### 1. Access the Payment Page
Visit: `http://dogdge.loc/investments/crypto-payment/2`

### 2. What You'll See
- **Loading Animation**: Shows for 2 seconds while "generating" the QR code
- **QR Code**: Displays the user's public crypto address (`crypto_address` field)
- **Wallet Address**: Shows the full address with a "Copy" button
- **Network Type**: Shows "BNB (BEP20)"
- **Amount**: Shows the investment amount in USDT

### 3. Scan the QR Code
You can scan the QR code with:
- ✅ Trust Wallet
- ✅ MetaMask Mobile
- ✅ Binance Wallet
- ✅ Any BEP20-compatible wallet app

### 4. What the QR Code Contains
The QR code contains the **public crypto address** only:
```
0x02eDc139D426D356d62B22A0C7613DBf30209882
```

**Note**: The QR code does NOT include the amount. Users must manually enter the amount shown on the page.

## Optional: Include Amount in QR Code

If you want the QR code to include the payment amount (supported by some wallets), uncomment line 298 in the view:

```javascript
// Change from:
const qrData = walletAddress;

// To:
const qrData = `ethereum:${walletAddress}@56?value=${amount}`;
```

This creates an EIP-681 payment URI format:
- `ethereum:` - Protocol
- `address` - Recipient address
- `@56` - Chain ID (56 = BNB Smart Chain)
- `?value=amount` - Payment amount

## Security Notes

### What's Safe to Display
✅ **Public Address** (`crypto_address`) - Safe to display publicly
✅ **Public Key** (`public_key`) - Safe to display
✅ **QR Code of Address** - Safe to share

### What Must Stay Secret
❌ **Private Key** (`private_key`) - NEVER display or share
❌ **Decrypted Private Key** - Only use server-side in Node.js scripts
❌ **Encryption Key** - Keep in `.env` files only

## How It Works

1. **User visits payment page**
2. **Controller checks** if crypto wallet exists:
   - If missing → auto-generates wallet
   - If exists → uses existing wallet
3. **Page loads** with user's public crypto address
4. **QR code generated** containing the address
5. **User scans** QR code with their wallet app
6. **User sends payment** to the displayed address
7. **Your Node.js script** monitors the blockchain for incoming transactions
8. **Transaction detected** → Update investment status to "active"

## Testing Checklist

- [ ] Page loads without errors
- [ ] Loading animation shows for 2 seconds
- [ ] QR code appears after loading
- [ ] Wallet address is displayed correctly
- [ ] "Copy" button works
- [ ] Network type shows "BNB (BEP20)"
- [ ] Amount shows correct investment amount
- [ ] QR code scans successfully with wallet app
- [ ] Console logs show address and amount

## Console Output

Check browser console (F12) for:
```
✅ QR Code generated for address: 0x02eDc139D426D356d62B22A0C7613DBf30209882
📱 Amount to pay: 50 USDT
```

## Troubleshooting

### QR Code Shows "Address not generated"
**Problem**: User doesn't have a crypto address
**Solution**: The middleware should auto-generate it. Check:
1. Is `MASTER_MNEMONIC` set in `.env`?
2. Check Laravel logs for wallet generation errors
3. Manually generate: `php artisan crypto:generate-wallets`

### QR Code Won't Scan
**Problem**: QR code format not recognized
**Solution**:
1. Try different wallet apps
2. Ensure address format is correct (starts with `0x`)
3. Check if wallet supports BEP20/BSC network

### Wrong Address Displayed
**Problem**: Cached user data
**Solution**: The page now uses `Auth::user()->crypto_address` which is refreshed by the controller

## Next Steps

After QR code is working:
1. ✅ User scans and sends payment
2. ⏳ Implement blockchain monitoring (Node.js script)
3. ⏳ Detect incoming transaction
4. ⏳ Verify transaction amount matches investment
5. ⏳ Update investment status from "pending" to "active"
6. ⏳ Send confirmation notification to user
