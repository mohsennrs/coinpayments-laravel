<?php  
namespace Hattori\Coinpayments;

use Hattori\Coinpayments\Exceptions\CoinpaymentsException;
use Hattori\Coinpayments\Exceptions\InvalidArgumentException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Coinpayments
{
	protected $cp_merchant_id, $cp_ipn_secret, $cp_public_key, $cp_private_key, $format, $ch;

    public function __construct()
    {
        $this->cp_merchant_id = config('coinpayments.cp_merchant');
        $this->cp_ipn_secret = config('coinpayments.cp_ipn_secret');
        $this->cp_public_key = config('coinpayments.cp_public_key');
        $this->cp_private_key = config('coinpayments.cp_private_key');
        $this->format = 'json';
        $this->ipn_url = config('coinpayments.cp_ipn');
    }

    public function createWithdraw($args) {
    	$rules = [
    		'amount'       => 'required|numeric|min:0',
            'currency'     => 'required|string',
            'address'     => 'required|string',
            'auto_confirm' => 'nullable|in:0,1',
            'dest_tag' => 'nullable|string',
            'ipn_url' => 'nullable|string'
    	];		

    	$this->checkAccountInfo();

    	$this->validate($args, $rules);

    	return $this->apiCall('create_withdrawal',$args);
    }

    public function apiCall($cmd, $req) {
    	
    	// Set the API command and required fields
    	$req['version'] = 1;
    	$req['cmd']     = $cmd;
    	$req['key']     = $this->cp_public_key;
    	$req['format']  = isset($req['format']) && !empty($req['ipn_url']) ? $req['format'] : $this->format; //supported values are json and xml
    	$req['ipn_url'] = isset($req['ipn_url']) && !empty($req['ipn_url']) ? $req['ipn_url'] : $this->ipn_url;
    	// Generate the query string
    	$postData = http_build_query($req, '', '&');

    	// Calculate the HMAC signature on the POST data
    	$hmac = hash_hmac('sha512', $postData, $this->cp_private_key);

    	// Create cURL handle and initialize (if needed)
    	if ($this->ch === null) {
    	    $this->ch = curl_init('https://www.coinpayments.net/api.php');
    	    curl_setopt($this->ch, CURLOPT_FAILONERROR, true);
    	    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    	    curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
    	}

    	curl_setopt($this->ch, CURLOPT_HTTPHEADER, ['HMAC: ' . $hmac]);
    	curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postData);

    	if (!($data = curl_exec($this->ch))) {
    	    throw new CoinpaymentsException('cURL error: ' . curl_error($this->ch));
    	}

    	$response = json_decode($data, true, 512, JSON_BIGINT_AS_STRING);

    	// If you are using PHP 5.5.0 or higher you can use json_last_error_msg() for a better error message
    	if ($response === null || !count($response)) {
    	    throw new CoinpaymentsException('Unable to parse JSON result (' . json_last_error() . ')');
    	}

    	return $response;
    }
    public function buy($args) {
    	$this->validateInitiateArgs($args);

    	$args = (object)$args;
    	
    	return ['status' => 'success', 'action' => 'submit_form', 'data' => view('coinpayments-laravel::form', ['args' => $args])->render()];

    }
    public function cpIpn(Request $request) {

    	$cp_debug_email = 'mohsenn.dev@gmail.com';    	

    	$this->validateHmack($request);


    	$status = intval($request->get('status'));

    	if ($status >= 100 || $status == 2) {
    		return ['status' => 'success', 'response' => $request->all()];
    	} else if ($status < 0) {
            //payment error, this is usually final but payments will sometimes be reopened if there was no exchange rate conversion or with seller consent
            $this->errorAndDie('Error occured, code: ' . $status . "\nMessage: " . $status_text);
        } else {
            //payment is pending, you can optionally add a note to the order page
        }


    }


    public function validateInitiateArgs($args) {
    	$rules = [
    	    'currency' => 'required',
    	    'amountf' => 'required|numeric',
    	    'allow_currencies' => 'required',
    	    'dest_tag' => 'nullable',
    	    'first_name' => 'nullable',
    	    'email' => 'required|email',
    	];

    	$this->validate($args, $rules);
    }

    public function errorAndDie($error_msg)
    {
        global $cp_debug_email;
        $report = "\n\n===========================\n\n" . 'Error: ' . $error_msg . "\n\n";
        $report .= "POST Data\n\n";
        foreach ($_POST as $k => $v) {
            $report .= "|$k| = |$v|\n";
        }
        $fp = fopen(base_path('cp.txt'), 'a');fwrite($fp, $report);fclose($fp);
        // mail($cp_debug_email, 'Coinpayments debug: ' . env('APP_URL'), $report);

        // if ($request->has('bot') && $request->get('bot') && $request->has('order_id')) {
        //     $bot=new TelegramBotController;
        //     $bot->show($request->get('order_id'),'failed');
        //     return response('success',200);
        // }
        die('IPN Error: ' . $error_msg);
    }

    public function validate($args, $rules)
    {	
    	$validator = Validator::make( $args, $rules);

    	 if ($validator->fails()) {
    	    throw new InvalidArgumentException($validator->errors()->first());
    	 }

    	 return $this;
    }

    public function validateHmack($request)
    {
    	if (!$request->has('ipn_mode') || ($request->get('ipn_mode') != 'hmac')) {
    	    $this->errorAndDie('IPN Mode is not HMAC');
    	}

    	if (!isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC'])) {
    	    $this->errorAndDie('No HMAC signature sent.');
    	}

    	$row_post = file_get_contents('php://input');
    	if ($row_post === FALSE || empty($row_post)) {
    	    $this->errorAndDie('Error reading POST data');
    	}
    	if (!$request->has('merchant') || $request->get('merchant') != trim($this->cp_merchant_id)) {
    	    $this->errorAndDie('No or incorrect Merchant ID passed');
    	}
    	$hmac = hash_hmac("sha512", $row_post ,trim($this->cp_ipn_secret));
    	if (!hash_equals($hmac, $_SERVER['HTTP_HMAC'])) {
    	    //if ($hmac != $_SERVER['HTTP_HMAC']) { <-- Use this if you are running a version of PHP below 5.6.0 without the hash_equals function
    	    $this->errorAndDie('HMAC signature does not match');
    	}
    }

    public function checkAccountInfo() {
    	if (!$this->cp_merchant_id || !$this->cp_ipn_secret || !$this->cp_public_key || !$this->cp_private_key) {
    		throw new InvalidArgumentException('Account info is not set');
    	}
    }
}
?>