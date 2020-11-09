<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Webpay_bahamta {

		// مقدار API KEY مربوط به فروشگاه خود را در اینجا وارد کنید
		var $webpay_api_key = 'webpay:xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx:zzzzzzzz-zzzz-zzzz-zzzz-zzzzzzzzzzzz';

		var $create_request_url = 'https://webpay.bahamta.com/api/create_request';
		var $confirm_payment_url = 'https://webpay.bahamta.com/api/confirm_payment';

		/**
		 * ایجاد درخواست
		 * @param  $amount_irr   	مبلغ مورد نظر به ریال
		 * @param  $callback_url 	آدرس اینترنتی صفحه بازگشت به فروشگاه
		 * @param  $payer_mobile 	شماره موبایل پرداخت کننده
		 * @param  $trusted_pan  	شماره کارت پرداخت کننده
		 * @return array			در صورت موفقیت آرایه شامل کد شناسه و آدرس ارجاع به درگاه 
		 *                    		در صورت عدم موفقیت آرایه شامل کد شناسه و اطلاعات خطا
		 */
        public function create_request($amount_irr = '10000', $callback_url = '', $payer_mobile = '', $trusted_pan = '')
        {
        	$dataArray = array(
        		'api_key' => $this->webpay_api_key,
        		'reference' => $this->generate_unique_reference(),
        		'amount_irr' => $amount_irr,
        		'payer_mobile' => $payer_mobile,
        		'callback_url' => $callback_url,
        		'trusted_pan' => $trusted_pan,
        	);

        	try {
	        	$ch = curl_init();
	        	$data = http_build_query($dataArray);
	        	$url = $this->create_request_url."?".$data;
	        	curl_setopt($ch, CURLOPT_URL, $url);
	        	curl_setopt($ch, CURLOPT_USERAGENT, 'Bahamta Webpay Codeigniter 3 Library 1.0.0');
	        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	        	$data = curl_exec($ch);
	        	curl_close($ch);
	        	if(!$data) { throw new Exception(); }
        	}
        	catch(exception $e)
        	{
        		return 
        			[
	        			'ok' => false, // not successful
	        			'reference' => $dataArray['reference'], // reference id
	        			'error' => 'CURL Error', // error name
	        			'trans' => $this->translate_error('CURL') // error translate
        			];
        	}
        	
        	// convert to json
        	$result = json_decode($data);

        	if($result->ok === true)
        	{
        		return 
        			[
        				'ok' => true, // successful
        				'reference' => $dataArray['reference'] , // reference id
        				'payment_url' => $result->result->payment_url, // payment url
        			];
        	}else{
        		return 
        			[
        				'ok' => false, // not successful
        				'reference' => $dataArray['reference'] , // reference id
        				'error' => $result->error, // error name
        				'trans' => $this->translate_error($result->error) // error translate
        			];
        	}
        }

        /**
         * شروع پرداخت
         * @param  $result 	مقدار خروجی تابع create_request
         * @return 			در صورت موفقیت ارجاع به صفحه پرداخت
         *                  در صورت عدم مموفقیت مقدار false 
         */
        public function start_payment($result)
        {
        	if($result['ok'] === true)
        	{
        		header("Location: " . $result['payment_url'] );
        	}else{
        		return false;
        	}
        }

        /**
         * بررسی وضعیت اطلاعات بازگشتی
         * @return 		آرایه شامل کد وضعیت و شناسه یکتا و در صورت عدم موفقیت، مقادیر خطا
         */
        public function check_callback()
        {
        	$state = $_REQUEST['state'];
        	$reference = $_REQUEST['reference'];

        	if($state <> 'error')
        	{
        		return 
        			[
        				'ok' => true, // successful
        				'state' => $state, // state
        				'reference' => $reference // reference id
        			];
        	}else{
        		return 
        			[
        				'ok' => false, // not successful
        				'state' => $state, // state
        				'reference' => $reference, // reference id
        				'error' => $_REQUEST['error_key'], // error name
        				'trans' => $this->translate_error($_REQUEST['error_key']) // error translate
        			];
        	}
        }

        /**
         * تایید پرداخت
         * @param  $reference  شناسه یکتا
         * @param  $amount_irr مبلغ پرداخت
         * @return             در صورت موفقیت آرایه نتایج
         *                     در صورت عدم موفقیت آرایه شامل کد و مقدار خطا
         */
        public function confirm_payment($reference , $amount_irr)
        {
        	$dataArray = array(
        		'api_key' => $this->webpay_api_key,
        		'reference' => $reference,
        		'amount_irr' => $amount_irr,
        	);

        	try {
	        	$ch = curl_init();
	        	$data = http_build_query($dataArray);
	        	$url = $this->confirm_payment_url."?".$data;
	        	curl_setopt($ch, CURLOPT_URL, $url);
	        	curl_setopt($ch, CURLOPT_USERAGENT, 'Bahamta Webpay Codeigniter 3 Library 1.0.0');
	        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	        	$data = curl_exec($ch);
	        	curl_close($ch);
        		if(!$data) { throw new Exception(); }
        	}
        	catch(exception $e)
        	{
        		return 
        			[
	        			'ok' => false, // not successful
	        			'reference' => $dataArray['reference'] , // reference id
	        			'error' => 'CURL Error', // error name
	        			'trans' => $this->translate_error('CURL') // error translate
        			];
        	}
        	
        	$result = json_decode($data);

        	if($result->ok === true)
        	{
			$payment_result = $result->result;
        		return 
        			[
        				'ok' => true, // successful
        				'state' => $payment_result->state,
        				'total' => $payment_result->total,
        				'wage' => $payment_result->wage,
        				'gateway' => $payment_result->gateway,
        				'terminal' => $payment_result->terminal,
        				'pay_ref' => $payment_result->pay_ref,
        				'pay_trace' => $payment_result->pay_trace,
        				'pay_pan' => $payment_result->pay_pan,
        				'pay_cid' => $payment_result->pay_cid,
        				'pay_time' => $payment_result->pay_time,
        				'amount_irr' => $amount_irr,
        				'reference' => $reference,
        				'error' => '',
        				'trans' => 'پرداخت موفق'
        			];
        	}else{
        		return 
        			[
        				'ok' => false, // not successful
        				'error' => $result->error, // error name
        				'trans' => $this->translate_error($result->error) // error translate
        			];
        	}
        }

        /**
         * ترجمه مقدار خطا
         * @param  $err  		کد خطا
         * @return 			    مقدار ترجمه شده خطا
         */
        private function translate_error($err)
        {
        	switch ($err) {
			  case 'INVALID_API_CALL':
			    return 'قالب فراخوانی سرویس رعایت نشده است.';
			    break;
			  case 'INVALID_API_KEY':
			    return 'کلید الکترونیکی API اشتباه است';
			    break;
			  case 'NOT_AUTHORIZED':
			    return 'فروشنده‌ای با کلید ثبت شده در وب‌پی یافت نشد.';
			    break;
			  case 'INVALID_AMOUNT':
			    return 'مبلغ به صورت صحیح فرستاده نشده است.';
			    break;
			  case 'LESS_THAN_WAGE_AMOUNT':
			    return 'مبلغ کمتر از کارمزد پرداختی است';
			    break;
			  case 'TOO_LESS_AMOUNT':
			    return 'مبلغ کمتر از حد مجاز است';
			    break;
			  case 'TOO_MUCH_AMOUNT':
			    return 'مبلغ بیشتر از حد مجاز است';
			    break;
			  case 'INVALID_REFERENCE':
			    return 'شماره شناسه پرداخت ناردست است';
			    break;
			  case 'INVALID_TRUSTED_PAN':
			    return 'لیست شماره کارتها نادرست است';
			    break;
			  case 'INVALID_CALLBACK':
			    return 'آدرس فراخوانی نادرست است';
			    break;
			  case 'INVALID_PARAM':
			    return 'خطایی در مقادیر فرستاده شده وجود دارد';
			    break;
			  case 'ALREADY_PAID':
			    return 'درخواست پرداختی با شناسه داده شده قبلاً ثبت و پرداخت شده است';
			    break;
			  case 'MISMATCHED_DATA':
			    return 'درخواست پرداختی با شناسه داده شده (با مقادیر متفاوت!) قبلاً ثبت و منتظر پرداخت است';
			    break;
			  case 'NO_REG_TERMINAL':
			    return 'ترمینالی برای این فروشنده ثبت نشده است';
			    break;
			  case 'NO_AVAILABLE_GATEWAY':
			    return 'درگاههای پرداختی قادر به ارائه خدمات نیستند';
			    break;
			  case 'SERVICE_ERROR':
			    return 'خطای داخلی سرویس رخ داده است';
			    break;
			  case 'UNKNOWN_BILL':
			    return 'پرداختی با شماره شناسه فرستاده شده ثبت نشده است';
			    break;
			  case 'MISMATCHED_DATA':
			    return 'مبلغ اعلام شده با آنچه در webpay ثبت شده است مطابقت ندارد';
			    break;
			  case 'NOT_CONFIRMED':
			    return 'این پرداخت تأیید نشد';
			    break;
			  case 'FAILED_PAYMENT':
			    return 'پرداخت ناموفق';
			    break;
			  case 'CURL':
			    return 'خطا در ارسال درخواست به سرویس باهمتا';
			    break;
			  default:
			    return 'خطای نامشخص :‌ ' . $err;
			}
        }

		/**
		 * تولید شناسه یکتا
		 * @return 			شناسه یکتا تولید شده بر اساس زمان // مناسب برای فروشگاه‌های کوچک
		 */
        private function generate_unique_reference()
        {
        	return strtr(base64_encode(time()), '+/=', '._-');
        }
}
