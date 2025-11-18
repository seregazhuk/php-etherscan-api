# PHP wrapper for the Etherscan API (supports v2).

PHP client for [Etherscan API](https://docs.etherscan.io) (and its families like BscScan), with nearly full API bindings 
(accounts, transactions, tokens, contracts, blocks, stats) and full [chains](https://docs.etherscan.io/supported-chains) support.


**Table of Contents**
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Available bindings](#available-bindings)
    - [Accounts](#accounts)
    - [Contracts](#contracts)

## Installation

```bash
composer req seregazhuk/etherscan-api:dev-main 
```

## Quick Start

Register Etherscan account and create [free API key](https://etherscan.io/myapikey).

```php
$etherscan = new seregazhuk\EtherscanApi\EtherscanApi('your-api-key');
$currentBlock = $etherscan->proxy->getBlockNumber();
$transactionInfo = $etherscan->proxy->getTransactionByHash('0x136f818dfe87b367eee9890c162ef343dbd65e409aef102219a6091ba7e696d7');
$isConfirmed = $currentBlock
        ->subtract($transactionInfo->blockNumber)
        ->compare(new BigInteger('12')) >= 0;

echo $isConfirmed ? 'Confirmed' : 'Not confirmed';
```

## Available bindings

### Accounts

[Get Ether balance for a single address](https://docs.etherscan.io/api-endpoints/accounts#get-ether-balance-for-a-single-address): 

```php
$balance = $etherscan->accounts->getBalance('0xde0b295669a9fd93d5f28d9ec85e40f4cb697bae');
```

Get Ether balance for multiple addresses in a single call:

```php
$balances = $etherscan->accounts->getBalances(['0xddbd2b932c763ba5b1b7ae3b362eac3e8d40121a', '0x63a9975ba31b0b9626b34300f7f627147df1f526']);
```

[Get a list of 'Normal' transactions by address](https://docs.etherscan.io/api-reference/endpoint/txlist):
```php
$transactions = $etherscan->accounts->getTransactions('0xc5102fE9359FD9a28f877a67E36B0F050d81a3CC');
```

[Get a list of internal transactions by hash](https://docs.etherscan.io/api-reference/endpoint/txlistinternal-txhash):
```php
$transactions = $etherscan->accounts->getInternalTransactionsByHash('0x40eb908387324f2b575b4879cd9d7188f69c8fc9d87c901b9e2daaea4b442170');
```

[Get internal transactions by block range](https://docs.etherscan.io/api-reference/endpoint/txlistinternal-blockrange):
```php
$transactions = $this->accounts->getInternalTransactionsByBlockRange(13481773, 13491773);
```

[Get ERC20 token transfers by address](https://docs.etherscan.io/api-reference/endpoint/tokentx):
```php
$erc20Events = $this->accounts->getErc20TokenTransferEvents('0x4e83362442b8d1bec281594cea3050c8eb01311c', '0x9f8f72aa9304c8b593d555f12ef6589cc3a579a2');
```

[Get ERC721 token transfers by address](https://docs.etherscan.io/api-reference/endpoint/tokennfttx):
```php
$erc721Events = $this->accounts->getErc721TokenTransferEvents('0x6975be450864c02b4613023c2152ee0743572325', '0x06012c8cf97bead5deae237070f9587f8e7a266d');
```

[Get ERC1155 token transfers by address](https://docs.etherscan.io/api-reference/endpoint/token1155tx):
```php
$erc1155Events = $this->accounts->getErc1155TokenTransferEvents('0x83f564d180b58ad9a02a449105568189ee7de8cb', '0x76be3b62873462d2142405439777e971754e8e77');
```

### Contracts

[Get Contract ABI](https://docs.etherscan.io/api-reference/endpoint/getabi#get-contract-abi-for-verified-contract-source-codes):

```php
$abi = $this->contracts->getAbi('0xBB9bc244D798123fDe783fCc1C72d3Bb8C189413');
```

[Get Contract Source Code](https://docs.etherscan.io/api-reference/endpoint/getsourcecode):

```php
$sourceCode = $this->contracts->getSourceCode('0xBB9bc244D798123fDe783fCc1C72d3Bb8C189413');
```

