<?php

declare(strict_types=1);

namespace seregazhuk\EtherscanApi\Module\Contracts;

use seregazhuk\EtherscanApi\EtherscanClient;
use seregazhuk\EtherscanApi\Module\Contracts\Model\ContractSourceCode;

final class Contracts
{
    private const MODULE_NAME = 'contract';

    public function __construct(private readonly EtherscanClient $client) {}

    /**
     * @see https://docs.etherscan.io/api-reference/endpoint/getabi#get-contract-abi-for-verified-contract-source-codes
     */
    public function getAbi(string $address): string
    {
        $response = $this->client->sendRequest(self::MODULE_NAME, 'getabi', ['address' => $address]);
        /** @var array{result: string} $json */
        $json = json_decode($response->getBody()->getContents(), true);

        return $json['result'];
    }

    /**
     * @see https://docs.etherscan.io/api-reference/endpoint/getsourcecode
     *
     * @return ContractSourceCode[]
     */
    public function getSourceCode(string $address): array
    {
        $response = $this->client->sendRequest(self::MODULE_NAME, 'getsourcecode', ['address' => $address]);
        /** @var array{result: array<int, array{
         *     SourceCode: string,
         *     ABI: string,
         *     ContractName: string,
         *     CompilerVersion: string,
         *     OptimizationUsed: string,
         *     ConstructorArguments: string,
         *     EVMVersion: string,
         *     Proxy: string,
         *     LicenseType: string
         * }>} $json */
        $json = json_decode($response->getBody()->getContents(), true);

        return array_map(fn(array $raw): ContractSourceCode => new ContractSourceCode(
            $raw['SourceCode'],
            $raw['ABI'],
            $raw['ContractName'],
            $raw['CompilerVersion'],
            $raw['OptimizationUsed'],
            $raw['ConstructorArguments'],
            $raw['EVMVersion'],
            $raw['Proxy'],
            $raw['LicenseType'],
        ), $json['result']);
    }
}
