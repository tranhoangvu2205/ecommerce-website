<?php
class WC_MomoPay_Controller
{

	protected static function getAnswerObject($res)
	{
		$o = new stdClass();
		$o->status = (isset($res->status)) ? $res->status : '';
		$o->message = (isset($res->message)) ? $res->message : '';
		$o->data = (isset($res->data)) ? @$res->data : '';
		$o->code = (isset($res->code)) ? @$res->code : 200;

		return json_encode($o);
	}

	public static function processData($data)
	{
		if (!$data)
			return 'Input data error!';

		$p = new WC_MomoPay_Gateway();

		$res = '';
		switch (@$data['mode']) {
			case 'check_payment':
				$payment_id = (@$data['payment_id']) ? $data['payment_id'] : "x";
				$res = $p->getPaymentStatus($payment_id);
				if ($res->status) {
					//here we will set the order status to ->payment_complete
					//👇 ---- check this article for more on order management ----- 👇
					// https://woocommerce.com/document/woocommerce-order-status-control
					$p->updateTransaction($payment_id, $res);
				}
				break;
			default:
				break;
		}
		;

		return self::getAnswerObject($res);
	}
}
?>