<?php

declare(strict_types=1);

namespace seregazhuk\EtherscanApi\Module\Proxy;

use phpseclib3\Math\BigInteger;
use seregazhuk\EtherscanApi\EtherscanClient;
use seregazhuk\EtherscanApi\Module\Proxy\Model\BlockInfo;
use seregazhuk\EtherscanApi\Module\Proxy\Model\TransactionByHashInfo;
use seregazhuk\EtherscanApi\Module\Proxy\Model\TransactionReceipt;
use seregazhuk\EtherscanApi\Module\Proxy\Model\TransactionReceiptLog;

final class Proxy
{
    private const MODULE_NAME = 'proxy';

    public function __construct(private readonly EtherscanClient $client) {}

    /**
     * @see https://docs.etherscan.io/api-reference/endpoint/ethgettransactionbyhash
     */
    public function getTransactionByHash(string $hash): TransactionByHashInfo
    {
        $params = [
            'action' => 'eth_getTransactionByHash',
            'txhash' => $hash,
        ];
        $response = $this->client->sendRequest(self::MODULE_NAME, 'eth_getTransactionByHash', $params);
        /** @var array{result: array{
         *     blockHash: string,
         *     blockNumber: string,
         *     from: string,
         *     gas: string,
         *     gasPrice: string,
         *     maxFeePerGas?: string,
         *     maxPriorityFeePerGas?: string,
         *     hash: string,
         *     input: string,
         *     nonce: string,
         *     to: string,
         *     transactionIndex: string,
         *     value: string,
         *     type: string
         * }} $json */
        $json = json_decode($response->getBody()->getContents(), true);

        return new TransactionByHashInfo(
            $json['result']['blockHash'],
            new BigInteger($json['result']['blockNumber'], 16),
            $json['result']['from'],
            $json['result']['gas'],
            $json['result']['gasPrice'],
            $json['result']['maxFeePerGas'] ?? null,
            $json['result']['maxPriorityFeePerGas'] ?? null,
            $json['result']['hash'],
            $json['result']['input'],
            $json['result']['nonce'],
            $json['result']['to'],
            $json['result']['transactionIndex'],
            $json['result']['value'],
            $json['result']['type'],
        );
    }

    /**
     * @see https://docs.etherscan.io/api-reference/endpoint/ethblocknumber
     */
    public function getBlockNumber(): BigInteger
    {
        $response = $this->client->sendRequest(self::MODULE_NAME, 'eth_blockNumber');
        /** @var array{result: string} $json */
        $json = json_decode($response->getBody()->getContents(), true);

        return new BigInteger($json['result'], 16);
    }

    /**
     * @see https://docs.etherscan.io/api-reference/endpoint/ethgetblockbynumber
     */
    public function getBlockByNumber(string $hexBlockNumber): BlockInfo
    {
        $params = [
            'tag' => $hexBlockNumber,
            'boolean' => false,
        ];
        $response = $this->client->sendRequest(self::MODULE_NAME, 'eth_getBlockByNumber', $params);
        /** @var array{result: array{
         *     baseFeePerGas: string,
         *     difficulty: string,
         *     extraData: string,
         *     gasLimit: string,
         *     gasUsed: string,
         *     hash: string,
         *     logsBloom: string,
         *     miner: string,
         *     mixHash: string,
         *     nonce: string,
         *     number: string,
         *     parentHash: string,
         *     receiptsRoot: string,
         *     sha3Uncles: string,
         *     size: string,
         *     stateRoot: string,
         *     timestamp: string,
         *     totalDifficulty: string,
         *     transactions: array<string>,
         *     transactionsRoot: string,
         *     uncles: array<string>
         * }} $json */
        $json = json_decode($response->getBody()->getContents(), true);

        return new BlockInfo(
            baseFeePerGas: $json['result']['baseFeePerGas'],
            difficulty: $json['result']['difficulty'],
            extraData: $json['result']['extraData'],
            gasLimit: $json['result']['gasLimit'],
            gasUsed: $json['result']['gasUsed'],
            hash: $json['result']['hash'],
            logsBloom: $json['result']['logsBloom'],
            miner: $json['result']['miner'],
            mixHash: $json['result']['mixHash'],
            nonce: $json['result']['nonce'],
            number: $json['result']['number'],
            parentHash: $json['result']['parentHash'],
            receiptsRoot: $json['result']['receiptsRoot'],
            sha3Uncles: $json['result']['sha3Uncles'],
            size: $json['result']['size'],
            stateRoot: $json['result']['stateRoot'],
            timestamp: $json['result']['timestamp'],
            totalDifficulty: $json['result']['totalDifficulty'],
            transactions: $json['result']['transactions'],
            transactionsRoot: $json['result']['transactionsRoot'],
            uncles: $json['result']['uncles'],
        );
    }

    /**
     * @see https://docs.etherscan.io/api-endpoints/geth-parity-proxy#eth_sendrawtransaction
     */
    public function sendRawTransaction(string $hex): string
    {
        $response = $this->client->sendRequest(self::MODULE_NAME, 'eth_sendRawTransaction', ['hex' => $hex]);
        /** @var array{result: string} $json */
        $json = json_decode($response->getBody()->getContents(), true);

        return $json['result'];
    }

    /**
     * @see https://docs.etherscan.io/api-endpoints/geth-parity-proxy#eth_getblocktransactioncountbynumber
     */
    public function getTransactionCountByNumber(string $hexBlockNumber): BigInteger
    {
        $response = $this->client->sendRequest(self::MODULE_NAME, 'eth_getBlockTransactionCountByNumber', ['tag' => $hexBlockNumber]);
        /** @var array{result: string} $json */
        $json = json_decode($response->getBody()->getContents(), true);

        return new BigInteger($json['result'], 16);
    }

    /**
     * @see https://docs.etherscan.io/api-endpoints/geth-parity-proxy#eth_gettransactioncount
     */
    public function getTransactionCount(string $address): BigInteger
    {
        $response = $this->client->sendRequest(self::MODULE_NAME, 'eth_getTransactionCount', ['address' => $address]);
        /** @var array{result: string} $json */
        $json = json_decode($response->getBody()->getContents(), true);

        return new BigInteger($json['result'], 16);
    }

    /**
     * @see https://docs.etherscan.io/api-endpoints/geth-parity-proxy#eth_gettransactionreceipt
     */
    public function getTransactionReceipt(string $hash): TransactionReceipt
    {
        $response = $this->client->sendRequest(self::MODULE_NAME, 'eth_getTransactionReceipt', ['txhash' => $hash]);
        /** @var array{result: array{
         *     blockHash: string,
         *     blockNumber: string,
         *     contractAddress: string,
         *     cumulativeGasUsed: string,
         *     from: string,
         *     gasUsed: string,
         *     logs: array<array{
         *         address: string,
         *         topics: array<string>,
         *         data: string,
         *         blockNumber: string,
         *         transactionHash: string,
         *         transactionIndex: string,
         *         logIndex: string,
         *         blockHash: string,
         *         removed: bool
         *     }>,
         *     logsBloom: string,
         *     status: string,
         *     to: string,
         *     transactionHash: string,
         *     transactionIndex: string,
         *     type: string
         * }} $json */
        $json = json_decode($response->getBody()->getContents(), true);

        return new TransactionReceipt(
            $json['result']['blockHash'],
            new BigInteger($json['result']['blockNumber'], 16),
            $json['result']['contractAddress'],
            $json['result']['cumulativeGasUsed'],
            $json['result']['from'],
            $json['result']['gasUsed'],
            array_map(fn(array $log): TransactionReceiptLog => new TransactionReceiptLog(
                $log['address'],
                $log['topics'],
                $log['data'],
                new BigInteger($log['blockNumber'], 16),
                $log['transactionHash'],
                $log['transactionIndex'],
                $log['logIndex'],
                $log['blockHash'],
                $log['removed'],
            ), $json['result']['logs']),
            $json['result']['logsBloom'],
            $json['result']['status'],
            $json['result']['to'],
            $json['result']['transactionHash'],
            $json['result']['transactionIndex'],
            $json['result']['type'],
        );
    }

    /**
     * @see https://docs.etherscan.io/api-endpoints/geth-parity-proxy#eth_gasprice
     */
    public function getGasPrice(): BigInteger
    {
        $response = $this->client->sendRequest(self::MODULE_NAME, 'eth_gasPrice');
        /** @var array{result: string} $json */
        $json = json_decode($response->getBody()->getContents(), true);

        return new BigInteger($json['result'], 16);
    }
}
