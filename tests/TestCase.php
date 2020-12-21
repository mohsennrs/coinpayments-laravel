<?php  
namespace Hattori\Coinpayments\Tests;

use Illuminate\Support\Facades\View;
use Orchestra\Testbench\TestCase as TestBench;

abstract class TestCase extends TestBench
{
	protected function setUp(): void
	{
	    parent::setUp();
		// $this->loadMigrationsFrom(realpath(__DIR__.'/../Database/migrations')); 
		// $this->withFactories(__DIR__.'/../Database/factory');	 
		$this->withoutExceptionHandling(); 

		// $this->loadLaravelMigrations(['--database' => 'testbench']);
	    // and other test setup steps you need to perform
	}
	protected function getPackageProviders($app)
	{
	    return ['Hattori\Coinpayments\CoinpaymentsServiceProvider'];
	}

	protected function getEnvironmentSetUp($app)
	{
	    // Setup default database to use sqlite :memory:
		View::addLocation(__DIR__.'/../src/Views');

	    $app['config']->set('database.default', 'testbench');

	    $app['config']->set('database.connections.testbench', [
	        'driver'   => 'mysql',
	        'database' => 'testbench',
	        'host' => '127.0.0.1',
	        'port' => '3306',
	        'username' => 'root',
	        'password' => 'secret'
	    ]);

	    $app['config']->set('coinpayments', [
	        'cp_merchant' => env('CP_API_KEY', ''),
	        'cp_ipn_secret' => env('CP_IPN_SECRET', ''),
	        'cp_public_key' => '',
	        'cp_private_key' => '',
	    ]);

	    $app['config']->set('mail', [
	        'driver'   => 'smtp',
	        'host' => 'smtp.mailtrap.io',
	        'port' => '2525',
	        'username' => 'fdd7c89ea0492a',
	        'password' => '920293f91c82cf'
	    ]);

	    $app['config']->set('mail.from', [
	    	'address' => 'info@mohsen-nurisa.ir',
	    	'name' => 'mohsen nurisa'
	    ]);


	}



}
?>