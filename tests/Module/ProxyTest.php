<?php

declare(strict_types=1);

namespace tests\Module;

use GuzzleHttp\Psr7\Response;
use Http\Discovery\Psr17FactoryDiscovery;
use phpseclib3\Math\BigInteger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use seregazhuk\EtherscanApi\ChainId;
use seregazhuk\EtherscanApi\EtherscanClient;
use seregazhuk\EtherscanApi\Module\Proxy\Proxy;

final class ProxyTest extends TestCase
{
    private ClientInterface&MockObject $httpClientMock;

    private Proxy $proxy;

    protected function setUp(): void
    {
        $this->httpClientMock = self::createMock(ClientInterface::class);
        $client = new EtherscanClient(
            $this->httpClientMock,
            'apiKey',
            Psr17FactoryDiscovery::findRequestFactory(),
            ChainId::ETHEREUM_MAINNET,
        );
        $this->proxy = new Proxy($client);
        parent::setUp();
    }

    #[Test]
    public function it_retrieves_transaction_by_hash(): void
    {
        $json = <<<'JSON'
            {
               "jsonrpc":"2.0",
               "id":1,
               "result":{
                  "blockHash":"0xf850331061196b8f2b67e1f43aaa9e69504c059d3d3fb9547b04f9ed4d141ab7",
                  "blockNumber":"0xcf2420",
                  "from":"0x00192fb10df37c9fb26829eb2cc623cd1bf599e8",
                  "gas":"0x5208",
                  "gasPrice":"0x19f017ef49",
                  "maxFeePerGas":"0x1f6ea08600",
                  "maxPriorityFeePerGas":"0x3b9aca00",
                  "hash":"0x136f818dfe87b367eee9890c162ef343dbd65e409aef102219a6091ba7e696d7",
                  "input":"0x",
                  "nonce":"0x33b79d",
                  "to":"0xc67f4e626ee4d3f272c2fb31bad60761ab55ed9f",
                  "transactionIndex":"0x5b",
                  "value":"0x19755d4ce12c00",
                  "type":"0x2",
                  "accessList":[
                     
                  ],
                  "chainId":"0x1",
                  "v":"0x0",
                  "r":"0xa681faea68ff81d191169010888bbbe90ec3eb903e31b0572cd34f13dae281b9",
                  "s":"0x3f59b0fa5ce6cf38aff2cfeb68e7a503ceda2a72b4442c7e2844d63544383e3"
               }
            }
        JSON;

        $this->httpClientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->callback(function (RequestInterface $request): bool {
                    $this->assertSame(
                        'chainid=1&module=proxy&action=eth_getTransactionByHash&apikey=apiKey&txhash=0x136f818dfe87b367eee9890c162ef343dbd65e409aef102219a6091ba7e696d7',
                        $request->getUri()->getQuery(),
                    );

                    return true;
                }),
            )
            ->willReturn(new Response(200, [], $json));
        $transaction = $this->proxy->getTransactionByHash(
            '0x136f818dfe87b367eee9890c162ef343dbd65e409aef102219a6091ba7e696d7',
        );
        $this->assertSame(
            '0xf850331061196b8f2b67e1f43aaa9e69504c059d3d3fb9547b04f9ed4d141ab7',
            $transaction->blockHash,
        );
        $this->assertTrue((new BigInteger('0xcf2420', 16))->equals($transaction->blockNumber));
        $this->assertSame('0x00192fb10df37c9fb26829eb2cc623cd1bf599e8', $transaction->from);
        $this->assertSame('0x5208', $transaction->gas);
        $this->assertSame('0x19f017ef49', $transaction->gasPrice);
        $this->assertSame('0x1f6ea08600', $transaction->maxFeePerGas);
        $this->assertSame('0x3b9aca00', $transaction->maxPriorityFeePerGas);
        $this->assertSame('0x', $transaction->input);
        $this->assertSame('0x5b', $transaction->transactionIndex);
        $this->assertSame('0x136f818dfe87b367eee9890c162ef343dbd65e409aef102219a6091ba7e696d7', $transaction->hash);
        $this->assertSame('0xc67f4e626ee4d3f272c2fb31bad60761ab55ed9f', $transaction->to);
        $this->assertSame('0x19755d4ce12c00', $transaction->value);
        $this->assertSame('0x2', $transaction->type);
        $this->assertSame('0x33b79d', $transaction->nonce);
    }

    #[Test]
    public function it_retrieves_transaction_by_hash_with_nullable_fields(): void
    {
        $json = <<<'JSON'
            {
               "jsonrpc":"2.0",
               "id":1,
               "result":{
                  "blockHash":"0xf850331061196b8f2b67e1f43aaa9e69504c059d3d3fb9547b04f9ed4d141ab7",
                  "blockNumber":"0xcf2420",
                  "from":"0x00192fb10df37c9fb26829eb2cc623cd1bf599e8",
                  "gas":"0x5208",
                  "gasPrice":"0x19f017ef49",
                  "hash":"0x136f818dfe87b367eee9890c162ef343dbd65e409aef102219a6091ba7e696d7",
                  "input":"0x",
                  "nonce":"0x33b79d",
                  "to":"0xc67f4e626ee4d3f272c2fb31bad60761ab55ed9f",
                  "transactionIndex":"0x5b",
                  "value":"0x19755d4ce12c00",
                  "type":"0x2",
                  "accessList":[
                     
                  ],
                  "chainId":"0x1",
                  "v":"0x0",
                  "r":"0xa681faea68ff81d191169010888bbbe90ec3eb903e31b0572cd34f13dae281b9",
                  "s":"0x3f59b0fa5ce6cf38aff2cfeb68e7a503ceda2a72b4442c7e2844d63544383e3"
               }
            }
        JSON;

        $this->httpClientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->callback(function (RequestInterface $request): bool {
                    $this->assertSame(
                        'chainid=1&module=proxy&action=eth_getTransactionByHash&apikey=apiKey&txhash=0x136f818dfe87b367eee9890c162ef343dbd65e409aef102219a6091ba7e696d7',
                        $request->getUri()->getQuery(),
                    );

                    return true;
                }),
            )
            ->willReturn(new Response(200, [], $json));
        $transaction = $this->proxy->getTransactionByHash(
            '0x136f818dfe87b367eee9890c162ef343dbd65e409aef102219a6091ba7e696d7',
        );
        $this->assertSame(
            '0xf850331061196b8f2b67e1f43aaa9e69504c059d3d3fb9547b04f9ed4d141ab7',
            $transaction->blockHash,
        );
        $this->assertTrue((new BigInteger('0xcf2420', 16))->equals($transaction->blockNumber));
        $this->assertSame('0x00192fb10df37c9fb26829eb2cc623cd1bf599e8', $transaction->from);
        $this->assertSame('0x5208', $transaction->gas);
        $this->assertSame('0x19f017ef49', $transaction->gasPrice);
        $this->assertNull($transaction->maxFeePerGas);
        $this->assertNull($transaction->maxPriorityFeePerGas);
        $this->assertSame('0x', $transaction->input);
        $this->assertSame('0x5b', $transaction->transactionIndex);
        $this->assertSame('0x136f818dfe87b367eee9890c162ef343dbd65e409aef102219a6091ba7e696d7', $transaction->hash);
        $this->assertSame('0xc67f4e626ee4d3f272c2fb31bad60761ab55ed9f', $transaction->to);
        $this->assertSame('0x19755d4ce12c00', $transaction->value);
        $this->assertSame('0x2', $transaction->type);
        $this->assertSame('0x33b79d', $transaction->nonce);
    }

    #[Test]
    public function it_retrieves_block_number(): void
    {
        $json = <<<'JSON'
            {
               "jsonrpc":"2.0",
               "id":83,
               "result":"0xc36b29"
            }
        JSON;

        $this->httpClientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->callback(function (RequestInterface $request): bool {
                    $this->assertSame(
                        'chainid=1&module=proxy&action=eth_blockNumber&apikey=apiKey',
                        $request->getUri()->getQuery(),
                    );

                    return true;
                }),
            )
            ->willReturn(new Response(200, [], $json));
        $result = $this->proxy->getBlockNumber();
        $this->assertTrue((new BigInteger('0xc36b29', 16))->equals($result));
    }

    #[Test]
    public function it_submits_pre_signed_transaction_for_broadcast(): void
    {
        $json = <<<'JSON'
            {             
                "id":1,
                "jsonrpc": "2.0",
                "result": "0xe670ec64341771606e55d6b4ca35a1a6b75ee3d5145a99d05921026d1527331"
            }
        JSON;

        $this->httpClientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->callback(function (RequestInterface $request): bool {
                    $this->assertSame(
                        'chainid=1&module=proxy&action=eth_sendRawTransaction&apikey=apiKey&hex=0xf904808000831cfde080',
                        $request->getUri()->getQuery(),
                    );

                    return true;
                }),
            )
            ->willReturn(new Response(200, [], $json));
        $result = $this->proxy->sendRawTransaction('0xf904808000831cfde080');
        $this->assertSame('0xe670ec64341771606e55d6b4ca35a1a6b75ee3d5145a99d05921026d1527331', $result);
    }

    #[Test]
    public function it_retrieves_transaction_block_by_number(): void
    {
        $json = <<<'JSON'
            {
               "jsonrpc":"2.0",
               "id":1,
               "result":{
                  "baseFeePerGas":"0x5cfe76044",
                  "difficulty":"0x1b4ac252b8a531",
                  "extraData":"0xd883010a06846765746888676f312e31362e36856c696e7578",
                  "gasLimit":"0x1caa87b",
                  "gasUsed":"0x5f036a",
                  "hash":"0x396288e0ad6690159d56b5502a172d54baea649698b4d7af2393cf5d98bf1bb3",
                  "logsBloom":"0x5020418e211832c600000411c00098852850124700800500580d406984009104010420410c00420080414b044000012202448082084560844400d00002202b1209122000812091288804302910a246e25380282000e00002c00050009038cc205a018180028225218760100040820ac12302840050180448420420b000080000410448288400e0a2c2402050004024a240200415016c105844214060005009820302001420402003200452808508401014690208808409000033264a1b0d200c1200020280000cc0220090a8000801c00b0100a1040a8110420111870000250a22dc210a1a2002409c54140800c9804304b408053112804062088bd700900120",
                  "miner":"0x5a0b54d5dc17e0aadc383d2db43b0a0d3e029c4c",
                  "mixHash":"0xc547c797fb85c788ecfd4f5d24651bddf15805acbaad2c74b96b0b2a2317e66c",
                  "nonce":"0x04a99df972bd8412",
                  "number":"0xc63251",
                  "parentHash":"0xbb2d43395f93dab5c424421be22d874f8c677e3f466dc993c218fa2cd90ef120",
                  "receiptsRoot":"0x3de3b59d208e0fd441b6a2b3b1c814a2929f5a2d3016716465d320b4d48cc1e5",
                  "sha3Uncles":"0xee2e81479a983dd3d583ab89ec7098f809f74485e3849afb58c2ea8e64dd0930",
                  "size":"0x6cb6",
                  "stateRoot":"0x60fdb78b92f0e621049e0aed52957971e226a11337f633856d8b953a56399510",
                  "timestamp":"0x6110bab2",
                  "totalDifficulty":"0x612789b0aba90e580f8",
                  "transactions":[
                     "0x40330c87750aa1ba1908a787b9a42d0828e53d73100ef61ae8a4d925329587b5",
                     "0x6fa2208790f1154b81fc805dd7565679d8a8cc26112812ba1767e1af44c35dd4"
                  ],
                  "transactionsRoot":"0xaceb14fcf363e67d6cdcec0d7808091b764b4428f5fd7e25fb18d222898ef779",
                  "uncles":[
                     "0x9e8622c7bf742bdeaf96c700c07151c1203edaf17a38ea8315b658c2e6d873cd"
                  ]
               }
            }
        JSON;

        $this->httpClientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->callback(function (RequestInterface $request): bool {
                    $this->assertSame(
                        'chainid=1&module=proxy&action=eth_getBlockByNumber&apikey=apiKey&tag=0x10d4f&boolean=0',
                        $request->getUri()->getQuery(),
                    );

                    return true;
                }),
            )
            ->willReturn(new Response(200, [], $json));

        $result = $this->proxy->getBlockByNumber('0x10d4f');
        $this->assertSame('0x5cfe76044', $result->baseFeePerGas);
        $this->assertSame('0x1b4ac252b8a531', $result->difficulty);
        $this->assertSame('0xd883010a06846765746888676f312e31362e36856c696e7578', $result->extraData);
        $this->assertSame('0x1caa87b', $result->gasLimit);
        $this->assertSame('0x5f036a', $result->gasUsed);
        $this->assertSame('0x396288e0ad6690159d56b5502a172d54baea649698b4d7af2393cf5d98bf1bb3', $result->hash);
        $this->assertSame(
            '0x5020418e211832c600000411c00098852850124700800500580d406984009104010420410c00420080414b044000012202448082084560844400d00002202b1209122000812091288804302910a246e25380282000e00002c00050009038cc205a018180028225218760100040820ac12302840050180448420420b000080000410448288400e0a2c2402050004024a240200415016c105844214060005009820302001420402003200452808508401014690208808409000033264a1b0d200c1200020280000cc0220090a8000801c00b0100a1040a8110420111870000250a22dc210a1a2002409c54140800c9804304b408053112804062088bd700900120',
            $result->logsBloom,
        );
        $this->assertSame('0x5a0b54d5dc17e0aadc383d2db43b0a0d3e029c4c', $result->miner);
        $this->assertSame('0xc547c797fb85c788ecfd4f5d24651bddf15805acbaad2c74b96b0b2a2317e66c', $result->mixHash);
        $this->assertSame('0x04a99df972bd8412', $result->nonce);
        $this->assertSame('0xc63251', $result->number);
        $this->assertSame('0xbb2d43395f93dab5c424421be22d874f8c677e3f466dc993c218fa2cd90ef120', $result->parentHash);
        $this->assertSame('0x3de3b59d208e0fd441b6a2b3b1c814a2929f5a2d3016716465d320b4d48cc1e5', $result->receiptsRoot);
        $this->assertSame('0xee2e81479a983dd3d583ab89ec7098f809f74485e3849afb58c2ea8e64dd0930', $result->sha3Uncles);
        $this->assertSame('0x6cb6', $result->size);
        $this->assertSame('0x60fdb78b92f0e621049e0aed52957971e226a11337f633856d8b953a56399510', $result->stateRoot);
        $this->assertSame('0x6110bab2', $result->timestamp);
        $this->assertSame('0x612789b0aba90e580f8', $result->totalDifficulty);
        $this->assertSame(
            '0x40330c87750aa1ba1908a787b9a42d0828e53d73100ef61ae8a4d925329587b5',
            $result->transactions[0],
        );
        $this->assertSame(
            '0x6fa2208790f1154b81fc805dd7565679d8a8cc26112812ba1767e1af44c35dd4',
            $result->transactions[1],
        );
        ;
        $this->assertSame('0x9e8622c7bf742bdeaf96c700c07151c1203edaf17a38ea8315b658c2e6d873cd', $result->uncles[0]);
        $this->assertSame(
            '0xaceb14fcf363e67d6cdcec0d7808091b764b4428f5fd7e25fb18d222898ef779',
            $result->transactionsRoot,
        );
    }

    #[Test]
    public function it_retrieves_transaction_count_for_block(): void
    {
        $json = <<<'JSON'
            {             
                "id":1,
                "jsonrpc": "2.0",
                "result": "0x3"
            }
        JSON;

        $this->httpClientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->callback(function (RequestInterface $request): bool {
                    $this->assertSame(
                        'chainid=1&module=proxy&action=eth_getBlockTransactionCountByNumber&apikey=apiKey&tag=0x10FB78',
                        $request->getUri()->getQuery(),
                    );

                    return true;
                }),
            )
            ->willReturn(new Response(200, [], $json));
        $result = $this->proxy->getBlockTransactionCountByNumber('0x10FB78');
        $this->assertTrue((new BigInteger('0x3', 16))->equals($result));
    }

    #[Test]
    public function it_retrieves_transaction_count_for_address(): void
    {
        $json = <<<'JSON'
            {             
                "id":1,
                "jsonrpc": "2.0",
                "result": "0x44"
            }
        JSON;

        $this->httpClientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->callback(function (RequestInterface $request): bool {
                    $this->assertSame(
                        'chainid=1&module=proxy&action=eth_getTransactionCount&apikey=apiKey&address=0x4bd5900Cb274ef15b153066D736bf3e83A9ba44e',
                        $request->getUri()->getQuery(),
                    );

                    return true;
                }),
            )
            ->willReturn(new Response(200, [], $json));
        $result = $this->proxy->getTransactionCount('0x4bd5900Cb274ef15b153066D736bf3e83A9ba44e');
        $this->assertTrue((new BigInteger('0x44', 16))->equals($result));
    }

    #[Test]
    public function it_retrieves_transaction_receipt(): void
    {
        $json = <<<'JSON'
                {
                   "jsonrpc":"2.0",
                   "id":1,
                   "result":{
                      "blockHash":"0x07c17710dbb7514e92341c9f83b4aab700c5dba7c4fb98caadd7926a32e47799",
                      "blockNumber":"0xcf2427",
                      "contractAddress":null,
                      "cumulativeGasUsed":"0xeb67d5",
                      "effectiveGasPrice":"0x1a96b24c26",
                      "from":"0x292f04a44506c2fd49bac032e1ca148c35a478c8",
                      "gasUsed":"0xb41d",
                      "logs":[
                         {
                            "address":"0xdac17f958d2ee523a2206206994597c13d831ec7",
                            "topics":[
                               "0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef",
                               "0x000000000000000000000000292f04a44506c2fd49bac032e1ca148c35a478c8",
                               "0x000000000000000000000000ab6960a6511ff18ed8b8c012cb91c7f637947fc0"
                            ],
                            "data":"0x00000000000000000000000000000000000000000000000000000000013f81a6",
                            "blockNumber":"0xcf2427",
                            "transactionHash":"0xadb8aec59e80db99811ac4a0235efa3e45da32928bcff557998552250fa672eb",
                            "transactionIndex":"0x122",
                            "blockHash":"0x07c17710dbb7514e92341c9f83b4aab700c5dba7c4fb98caadd7926a32e47799",
                            "logIndex":"0xdb",
                            "removed":false
                         }
                      ],
                      "logsBloom":"0x00000000000000000000000000000000000000000000000000000000000004000000000004000000000000000000010000000000000000000000000000000000000000000000000000000008000000000000000000000000800000000000000000000000000000000000000000000000000000000000000000000010000000001100000000000000000000000000000000000000000000000000000200100000000000000000000000000080000000000000000000000000000000000000000000000002000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000",
                      "status":"0x1",
                      "to":"0xdac17f958d2ee523a2206206994597c13d831ec7",
                      "transactionHash":"0xadb8aec59e80db99811ac4a0235efa3e45da32928bcff557998552250fa672eb",
                      "transactionIndex":"0x122",
                      "type":"0x2"
                   }
                }
        JSON;

        $this->httpClientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->callback(function (RequestInterface $request): bool {
                    $this->assertSame(
                        'chainid=1&module=proxy&action=eth_getTransactionReceipt&apikey=apiKey&txhash=0xf75e354c5edc8efed9b59ee9f67a80845ade7d0c',
                        $request->getUri()->getQuery(),
                    );

                    return true;
                }),
            )
            ->willReturn(new Response(200, [], $json));

        $result = $this->proxy->getTransactionReceipt('0xf75e354c5edc8efed9b59ee9f67a80845ade7d0c');
        $this->assertSame('0x07c17710dbb7514e92341c9f83b4aab700c5dba7c4fb98caadd7926a32e47799', $result->blockHash);
        $this->assertTrue((new BigInteger('0xcf2427', 16))->equals($result->blockNumber));
        $this->assertSame('0x292f04a44506c2fd49bac032e1ca148c35a478c8', $result->from);
        $this->assertSame('0xb41d', $result->gasUsed);
        $this->assertSame('0xdac17f958d2ee523a2206206994597c13d831ec7', $result->logs[0]->address);
        $this->assertSame(
            '0x00000000000000000000000000000000000000000000000000000000000004000000000004000000000000000000010000000000000000000000000000000000000000000000000000000008000000000000000000000000800000000000000000000000000000000000000000000000000000000000000000000010000000001100000000000000000000000000000000000000000000000000000200100000000000000000000000000080000000000000000000000000000000000000000000000002000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000',
            $result->logsBloom,
        );
        $this->assertSame('0x1', $result->status);
        $this->assertSame('0xdac17f958d2ee523a2206206994597c13d831ec7', $result->to);
        $this->assertSame(
            '0xadb8aec59e80db99811ac4a0235efa3e45da32928bcff557998552250fa672eb',
            $result->transactionHash,
        );
        $this->assertSame('0x122', $result->transactionIndex);
        $this->assertSame('0x2', $result->type);

        // Logs
        $this->assertSame('0x00000000000000000000000000000000000000000000000000000000013f81a6', $result->logs[0]->data);
        $this->assertTrue((new BigInteger('0xcf2427', 16))->equals($result->logs[0]->blockNumber));
        $this->assertSame(
            '0xadb8aec59e80db99811ac4a0235efa3e45da32928bcff557998552250fa672eb',
            $result->logs[0]->transactionHash,
        );
        $this->assertSame('0x122', $result->logs[0]->transactionIndex);
        $this->assertSame(
            '0x07c17710dbb7514e92341c9f83b4aab700c5dba7c4fb98caadd7926a32e47799',
            $result->logs[0]->blockHash,
        );
        $this->assertSame('0xdb', $result->logs[0]->logIndex);
        $this->assertFalse($result->logs[0]->removed);
        $this->assertSame(
            '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef',
            $result->logs[0]->topics[0],
        );
        $this->assertSame(
            '0x000000000000000000000000292f04a44506c2fd49bac032e1ca148c35a478c8',
            $result->logs[0]->topics[1],
        );
        $this->assertSame(
            '0x000000000000000000000000ab6960a6511ff18ed8b8c012cb91c7f637947fc0',
            $result->logs[0]->topics[2],
        );
    }

    #[Test]
    public function it_retrieves_gas_price(): void
    {
        $json = <<<'JSON'
            {             
                "id":1,
                "jsonrpc": "2.0",
                "result": "0x430e23400"
            }
        JSON;

        $this->httpClientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->callback(function (RequestInterface $request): bool {
                    $this->assertSame(
                        'chainid=1&module=proxy&action=eth_gasPrice&apikey=apiKey',
                        $request->getUri()->getQuery(),
                    );

                    return true;
                }),
            )
            ->willReturn(new Response(200, [], $json));
        $result = $this->proxy->getGasPrice();
        $this->assertTrue((new BigInteger('0x430e23400', 16))->equals($result));
    }
}
