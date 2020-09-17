<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function index()
	{
		$this->load->helper('url');
		$this->load->database();

		// فراخوانی کلیه سوابق پرداخت ها از دیتابیس
		$this->db->order_by('id', 'DESC');
		$db_data = $this->db->get('payments');

		$this->load->view('welcome_message',array('db_data'=>$db_data->result()));
	}

	public function payment()
	{
		$this->load->helper('url');
		$this->load->database();

		// آماده سازی متغییر ها
		$callback_url = base_url() .'welcome/callback';
		$price = intval($_POST['price']);
		$mobile = $_POST['mobile'];

		// فراخوانی کتابخانه
		$this->load->library('webpay_bahamta');
		// ایجاد درخواست
		$result = $this->webpay_bahamta->create_request($price,$callback_url,$mobile);

		// ذخیره نتایج اولیه در دیتابیس
		$data = array('reference' => $result['reference'], 'price' => $price, 'mobile' => $mobile);
		$this->db->insert('payments', $data);

		// بررسی وضعیت درخواست
		if($result['ok']) 
		{ 
			// در صورت تایید به صفحه پرداخت منتقل شود
			$this->webpay_bahamta->start_payment($result); 
		}else{
			// در صورت عدم تایید، خطا در دیتابیس ذخیره شود
			$data = array('error' => $result['error'],'trans' => $result['trans']);
			$this->db->where('reference', $result['reference']);
			$this->db->update('payments', $data);
			// بازگشت به صفحه نخست
			redirect('welcome/index');
		}
	}

	public function callback()
	{
		$this->load->helper('url');
		$this->load->database();
		$this->load->library('webpay_bahamta');

		// بررسی وضعیت بازگشت از صفحه بانک
		$callback_result = $this->webpay_bahamta->check_callback();

		// دریافت مقادیر از url بازگشتی
		$state = $callback_result['state'];
		$reference = $callback_result['reference'];

		// دریافت اطلاعات اولیه این تراکنش از دیتابیس جهت بررسی مجدد مبلغ تراکنش
		$query = $this->db->get_where('payments', array('reference' => $reference));
		$db_result = $query->result();
		if(sizeof($db_result) < 1) { redirect('welcome/index'); }
		$price = $db_result[0]->price;

		// بررسی وضعیت پرداخت
		if($callback_result['ok'] === false) 
		{ 
			// در صورت عدم تایید، خطا در دیتابیس ذخیره شود
			$data = array('state' => $state, 'error' => $callback_result['error'],'trans' => $callback_result['trans']);
			$this->db->where('reference', $reference);
			$this->db->update('payments', $data);
			// بازگشت به صفحه نخست
			redirect('welcome/index');
		}else{
			// در صورت تایید اولیه، درخواست تایید نهایی ارسال شود
			$confirm_result = $this->webpay_bahamta->confirm_payment($reference,$price);

			// بررسی وضعیت پرداخت تایید نهایی
			if($confirm_result['ok'] === false) 
			{ 
				// در صورت عدم تایید، خطا در دیتابیس ذخیره شود
				$data = array('state' => $state, 'error' => $confirm_result['error'],'trans' => $confirm_result['trans']);
				$this->db->where('reference', $reference);
				$this->db->update('payments', $data);
				// بازگشت به صفحه نخست
				redirect('welcome/index');
			}
			if($confirm_result['ok'] === true AND $confirm_result['state'] == 'paid') 
			{ 
				// پرداخت موفقیت آمیز بوده است، نتایج در دیتابیس ذخیره شود
				$data = array(
					'state' => $confirm_result['state'], 
					'total' => $confirm_result['total'], 
					'wage' => $confirm_result['wage'], 
					'gateway' => $confirm_result['gateway'], 
					'terminal' => $confirm_result['terminal'], 
					'pay_ref' => $confirm_result['pay_ref'], 
					'pay_trace' => $confirm_result['pay_trace'], 
					'pay_pan' => $confirm_result['pay_pan'], 
					'pay_cid' => $confirm_result['pay_cid'], 
					'pay_time' => $confirm_result['pay_time'], 
					'error' => '', 
					'trans' => '', 
				);
				$this->db->where('reference', $reference);
				$this->db->update('payments', $data);
				// کد مربوط به تایید پرداخت و فروش محصول/خدمات در اینجا قرار میگیرد
				// 
				// 
				// بازگشت به صفحه نخست
				redirect('welcome/index'); 
			}
		}
	}
}
