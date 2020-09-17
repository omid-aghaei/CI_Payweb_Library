<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="fa">
<head>
	<meta charset="utf-8">
	<title>Bahamta Webpay</title>

	<style type="text/css">

	body {
		direction: rtl;
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	hr {
		margin: 15px 0;
	}

	#body {
		margin: 0 15px 15px 15px;
	}

	#container {
		margin: 10px;
		border: 1px solid #D0D0D0;
		box-shadow: 0 0 8px #D0D0D0;
	}
	</style>
</head>
<body>

<div id="container">
	<h1>نمونه کد درگاه وب‌پی باهمتا !</h1>

	<div id="body">
		<p>جهت شروع، مبلغ و شماره موبایل خود را وارد کنید : </p>
		<form action="<?php echo base_url(); ?>welcome/payment" method="post" accept-charset="utf-8">
			<label for="price">
				مبلغ : <input type="number" name="price" value="20000">
			</label>
			<label for="mobile">
				موبایل : <input type="text" name="mobile">
			</label>
			<input type="submit" value="پرداخت">
		</form>

		<hr>

		<table border="1" width="100%">
			<tr>
				<td>id</td>
				<td>reference</td>
				<td>price</td>
				<td>mobile</td>
				<td>total</td>
				<td>wage</td>
				<td>gateway</td>
				<td>terminal</td>
				<td>pay_ref</td>
				<td>pay_trace</td>
				<td>pay_pan</td>
				<td>pay_cid</td>
				<td>pay_time</td>
				<td>error</td>
				<td>trans</td>
			</tr>
			<?php foreach ($db_data as $key => $value) {?>
			<tr>
				<td><?php echo $value->id; ?></td>
				<td><?php echo $value->reference; ?></td>
				<td><?php echo $value->price; ?></td>
				<td><?php echo $value->mobile; ?></td>
				<td><?php echo $value->total; ?></td>
				<td><?php echo $value->wage; ?></td>
				<td><?php echo $value->gateway; ?></td>
				<td><?php echo $value->terminal; ?></td>
				<td><?php echo $value->pay_ref; ?></td>
				<td><?php echo $value->pay_trace; ?></td>
				<td><?php echo $value->pay_pan; ?></td>
				<td><?php echo $value->pay_cid; ?></td>
				<td><?php echo $value->pay_time; ?></td>
				<td><?php echo $value->error; ?></td>
				<td><?php echo $value->trans; ?></td>
			</tr>
			<?php } ?>
		</table>
	</div>
</div>

</body>
</html>