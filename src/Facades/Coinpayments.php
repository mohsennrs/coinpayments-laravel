<?php  
namespace Hattori\Coinpayments\Facades;

use Illuminate\Support\Facades\Facade;


class Coinpayments extends Facade
{

	protected static function getFacadeAccessor()
    {
        return 'coinpayments';
    }
}
?>