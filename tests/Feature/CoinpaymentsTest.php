<?php  
namespace Hattori\Coinpayments\Tests\Feature;

use Hattori\Coinpayments\Facades\Coinpayments;
use Hattori\Coinpayments\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CoinpaymentsTest extends TestCase
{
	public function setUp():void
	{
		parent::setUp();
	}

	public function testBuyFromCustomer() {
		$args = [
			'currency' => 'TRX',
			'amountf' => '1',
			'allow_currencies' => 'TRX',
			'dest_tag' => '',
			'first_name' => 'mohsen',
			'last_name' => 'nurisa',
			'email' => 'mohsenn.dev@gmail.com',
			'payment_id' => 1
		];

		$response = Coinpayments::buy($args);
		$this->assertEquals('success', $response['status']);
		$this->assertArrayHasKey('data', $response);
		
	}
	
	public function testCreateWithdraw() {
		$args = [
			'amount'       => 100,
            'currency'     => 'TRX',
            'address'     => '',
            'auto_confirm' => 1,
            'dest_tag' => '',
            'ipn_url' => 'bittex.test/cp-ipn?invoice=1'
		];	

		$response = Coinpayments::createWithdraw($args);

		$this->assertEquals($response['error'], 'ok');

		$this->assertArrayHasKey('result', $response);
	}	
}
?>