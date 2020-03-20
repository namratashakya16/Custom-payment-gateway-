<?php
/*
 * Plugin Name: WooCommerce Namrata Payment Gateway
 * Plugin URI: http://namrata.codingkloud.com/wp-admin/plugins.php
 * Description: Custom WooCommerce Payment Gateway.
 * Author: Namrata Shakya
 * Author URI: http://namrata.codingkloud.com
 * Version: 1.0.1
 */
 
add_filter( 'woocommerce_payment_gateways', 'add_gateway_class' );
 function add_gateway_class( $gateways ) {
	 $gateways[] = 'DemoPaymentGateway'; // your class name is here
	 return $gateways;
}

add_action( 'plugins_loaded', 'init_gateway_class' );

function init_gateway_class() {
 
  class DemoPaymentGateway extends WC_Payment_Gateway { 
 		
    public function __construct() {
 
	      $this->id = 'custom_stripe_gateway'; 
	      $this->icon = ''; 
	      $this->has_fields = true; 
	      $this->method_title = __('New Gateway','custom_stripe_gateway' );
	      $this->method_description = 'Description of Namrata payment gateway'; 
 
	        // gateways can support subscriptions, refunds, saved payment methods,
	        // but in this tutorial we begin with simple payments
	      $this->supports = array( 'subscriptions', 'products' );


		  // support default form with credit card

		  $this->supports = array(
			  'products',
			  'refunds',
			  'subscriptions',
			  'subscription_cancellation',
			  'subscription_suspension',
			  'subscription_reactivation',
			  'subscription_amount_changes',
			  'subscription_date_changes',
			  'subscription_payment_method_change',
			  'subscription_payment_method_change_customer',
			  'subscription_payment_method_change_admin',
			  'multiple_subscriptions',
			  'pre-orders',
			  'default_credit_card_form',
		      /*'tokenization',*/
			  'add_payment_method',
		  );
 	   
	           $this->init_form_fields();
 
	           $this->init_settings();
	           $this->title = $this->get_option( 'title' );
	           $this->description = $this->get_option( 'description' );
	           $this->enabled = $this->get_option( 'enabled' );
	           $this->testmode = 'yes' === $this->get_option( 'testmode' );
	           $this->private_key = $this->testmode ? $this->get_option( 'test_private_key' ) : $this->get_option( 'private_key' );
	           $this->publishable_key = $this->testmode ? $this->get_option( 'test_publishable_key' ) : $this->get_option( 'publishable_key' );
 
	           add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) ); 
	          
	           add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
	          
	           foreach ( $this->settings as $setting_key => $value ) {
			         $this->$setting_key = $value;
		         }
 
	           // You can also register a webhook here
	           // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );
    }
 
    public function init_form_fields(){
 
		        $this->form_fields = array(
		                   'enabled' => array(
			                   'title'       => __( 'Enable / Disable', 'custom_stripe_gateway' ),
			                   'label'       => 'Enable Namrata Gateway',
			                   'type'        => 'checkbox',
			                   'description' => '',
			                   'default'     => 'no'
		                   ),
		                   'title' => array(
			                   'title'       =>  __('Title', 'custom_stripe_gateway' ),
			                   'type'        => 'text',
			                   'description' => 'This controls the title which the user sees during checkout.',
			                   'default'     => 'Credit Card',
			                   'desc_tip'    => true,
		                   ),
		                   'description' => array(
			                   'title'       =>  __('Description', 'custom_stripe_gateway' ),
			                   'type'        => 'textarea',
			                   'description' => 'This controls the description which the user sees during checkout.',
			                   'default'     => 'Pay with your credit card via our super-cool payment gateway.',
		                   ),
		                   'testmode' => array(
			                   'title'       =>  __('Test mode', 'custom_stripe_gateway' ),
			                   'label'       => 'Enable Test Mode',
			                   'type'        => 'checkbox',
			                   'description' => 'Place the payment gateway in test mode using test API keys.',
			                   'default'     => 'yes',
			                   'desc_tip'    => true,
		                   ),
		                   'test_publishable_key' => array(
			                   'title'       =>  __('Test Publishable Key', 'custom_stripe_gateway' ),
			                   'type'        => 'text'
		                   ),
		                   'test_private_key' => array(
			                   'title'       =>  __('Test Private Key', 'custom_stripe_gateway' ),
			                   'type'        => 'password',
		                   ),
		                   'publishable_key' => array(
		                   	'title'       =>  __('Live Publishable Key', 'custom_stripe_gateway' ),
		                   	'type'        => 'text'
		                   ),
		                   'private_key' => array(
			                   'title'       =>  __('Live Private Key', 'custom_stripe_gateway' ),
			                   'type'        => 'password'
		                   ),
		                   'saved_cards' => array(
			                   'title'       => __( 'Saved Cards', 'custom_stripe_gateway' ),
			                   'label'       => __( 'Enable Payment via Saved Cards', 'custom_stripe_gateway' ),
			                   'type'        => 'checkbox',
			                   'description' => __( 'If enabled, users will be able to pay with a saved card during checkout. Card details are saved on Stripe servers, not on your store.', 'custom_stripe_gateway' ),
			                   'default'     => 'no',
			                   'desc_tip'    => true,
		                       ),
		                  'stripe_cardtypes' => array(
						      'title'    => __( 'Accepted Cards', 'custom_stripe_gateway' ),
						      'type'     => 'multiselect',
						      'class'    => 'chosen_select',
						      'css'      => 'width: 350px;',
						      'desc_tip' => __( 'Select the card types to accept.', 'woocommerce' ),
						      'options'  => array(
							      'mastercard'       => 'MasterCard',
							      'visa'             => 'Visa',
							      'discover'         => 'Discover',
							      'amex' 		       => 'American Express',
							      'jcb'		       => 'JCB',
							      'dinersclub'       => 'Dinners Club',
							      ),
						      'default' => array( 'mastercard', 'visa', 'discover', 'amex' )
					           ),
	                   
	                         );

 
	}
 
		
    public function payment_fields() {    

	            if ( $this->description ) {
		           
		            if ( $this->testmode ) {
			            $this->description .= ' TEST MODE ENABLED. In test mode, you can use the card numbers listed in <a href="#" target="_blank" rel="noopener noreferrer">documentation</a>.';
			            $this->description  = trim( $this->description );
		            }
		            
		            echo wpautop( wp_kses_post( $this->description ) );
	            }
                if($this->saved_cards=='yes')
	            {	            	
	              $this->tokens = WC_Payment_Tokens::get_customer_tokens( get_current_user_id(), $this->id );
	              $this->saved_payment_methods();
	              $this->credit_card_form();
	              $this->save_payment_method_checkbox();
	            }else
	            {
	              $this->credit_card_form(); 
	            }

    }  
 
    public  function payment_scripts() {       
    
                wp_enqueue_script( 'stripeApi_script', 'https://js.stripe.com/v2/', array( 'jquery' ));
                wp_enqueue_script( 'api_script', 'https://js.stripe.com/v3/', array( 'jquery' ));	          
 
    }
    
    public function process_payment( $order_id ){

           global $current_user; 
		   global $woocommerce; 
		   $user_id=$current_user->ID;
	       $customer_order = new WC_Order($order_id);

	       $billing_first_name= $customer_order->billing_first_name;
           $billing_last_name=  $customer_order->billing_last_name; 
           $billing_company=  $customer_order->billing_company;
           $billing_country=  $customer_order->billing_country; 
           $billing_address_1=  $customer_order->billing_address_1;
           $billing_address_2=  $customer_order->billing_address_2; 
           $billing_city=  $customer_order->billing_city; 
           $billing_state= $customer_order->billing_state ;
           $billing_postcode=  $customer_order->billing_postcode;
           $billing_phone=  $customer_order->billing_phone;
           $billing_email=  $customer_order->billing_email; 
           $order_comments=  $customer_order->order_comments;
           $payment_method=  $customer_order->payment_method;

           $shipping_first_name=$customer_order->shipping_first_name;
           $shipping_last_name=$customer_order->shipping_last_name;
           $shipping_address_1=$customer_order->shipping_address_1;
		   $shipping_address_2	= $customer_order->shipping_address_2;
		   $shipping_city = $customer_order->shipping_city;
		   $shipping_state	= $customer_order->shipping_state;
		   $shipping_country =$customer_order->shipping_country;
		   $shipping_postcode= $customer_order->shipping_postcode;

		   $amount = absint( wc_format_decimal( ( (float) $customer_order->order_total * 100 ), wc_get_price_decimals() ) );
           $currency = get_woocommerce_currency();

           $card_number=  sanitize_text_field($_POST['custom_stripe_gateway-card-number']);           
           $cvc=  sanitize_text_field($_POST['custom_stripe_gateway-card-cvc']);
           $card_no = str_replace(' ', '', $card_number);
           $str=$_POST['custom_stripe_gateway-card-expiry'];
           list($part1, $part2) = explode('/', $str);
           $month = (int)$part1;
           $year = (int)$part2;

	       $test_key= 'test key';
	       $secret_key='secret key';

	       require("stripe-php/init.php");    
	         
	       \Stripe\Stripe::setApiKey($secret_key);
	       \Stripe\Stripe::setMaxNetworkRetries(2);

            if( isset( $_POST['wc-custom_stripe_gateway-payment-token'] ) &&  'new' !== $_POST['wc-custom_stripe_gateway-payment-token'])
            {            	
	         $token_id = wc_clean( $_POST['wc-custom_stripe_gateway-payment-token'] );	        
	         $card = WC_Payment_Tokens::get( $token_id );	          
	         $users_id = $card->get_user_id();	        
	         $customer_id=get_user_meta($users_id,'_custom_stripe_gateway_customer_id');
	         $customer_id=array_shift($customer_id);
	         $source_id= $card->get_token();
	         // print_r($card);
	         // die();	        
            }

            $saved_cards = ( $this->saved_cards == "yes" ) ? 'TRUE' : 'FALSE'; 
      
          	//when save card option is off in admin panel then 
            if($saved_cards=='TRUE')
              {
              	 if($customer_id)   //if there is customer id
              	  { 
		          	             	
		          	 $charge =create_charge($amount,$currency,$billing_first_name,$billing_last_name,$order_id,$billing_email,$customer_id,$source_id,$shipping_address_1,$shipping_address_2,$shipping_city,$shipping_state,$shipping_country,$shipping_postcode,$shipping_first_name,$shipping_last_name,$billing_phone); 
		          }

		          // when customer click on new payment method  then create token
		         elseif('true'==$_POST['wc-custom_stripe_gateway-new-payment-method'])
		         { 
		         	$token= new_token_create($billing_first_name,$billing_last_name,$card_no,$month,$year,$cvc,$billing_city,$billing_country,$billing_address_1,$billing_address_2,$billing_state,$billing_email);

		         	if($token){

		              $token_ID=$token['id'];		           
		           
		              $customer_id=get_user_meta($user_id,'_custom_stripe_gateway_customer_id');
		              $customer_id=array_shift($customer_id);

		              if($customer_id)
			          {
			         	    $customer = \Stripe\Customer::retrieve($customer_id);			        			     
               
			                 $ab= $customer->sources->create(array("source" => $token['id']));
			                      			                       
			                 $source_id= $ab['id'];	                       
			                      		         	       
			          	     $charge =create_charge($amount,$currency,$billing_first_name,$billing_last_name,$order_id,$billing_email,$customer_id,$source_id,$shipping_address_1,$shipping_address_2,$shipping_city,$shipping_state,$shipping_country,$shipping_postcode,$shipping_first_name,$shipping_last_name,$billing_phone);	           
		                  
			           }
			       else{
     				         $customer =create_customer($billing_first_name,$billing_last_name,$billing_phone,$token_ID,$billing_email,$billing_city,$billing_country,$billing_address_1,$billing_address_2,$billing_postcode,$billing_state,$order_id);             		   
			                  
			                  if ($customer) {
			                   		
			                   	$customer_id =$customer->id;			          	       
			          	        $charge =create_charge($amount,$currency,$billing_first_name,$billing_last_name,$order_id,$billing_email,$customer_id,$source_id,$shipping_address_1,$shipping_address_2,$shipping_city,$shipping_state,$shipping_country,$shipping_postcode,$shipping_first_name,$shipping_last_name,$billing_phone);
			                   		}	                   			          	                	       
			           } 
		                  $this->save_card($charge);
			              update_user_meta($user_id,'_custom_stripe_gateway_customer_id',$charge['customer']);
			              update_post_meta($order_id,'_custom_stripe_gateway_customer_id',$charge['customer']);
			              update_post_meta($order_id,'_custom_stripe_gateway_source_id',$charge['source']['id']);
 		               }

		         }

			    // when the new customer dont click new payment method
	             else
	                {
		             $token = new_token_create($billing_first_name,$billing_last_name,$card_no,$month,$year,$cvc,$billing_city,$billing_country,$billing_address_1,$billing_address_2,$billing_state,$billing_email);		             

                     $charge =create_charge($amount,$currency,$billing_first_name,$billing_last_name,$order_id,$billing_email,$customer_id,$source_id,$shipping_address_1,$shipping_address_2,$shipping_city,$shipping_state,$shipping_country,$shipping_postcode,$shipping_first_name,$shipping_last_name,$billing_phone);
	               }

	              

		      }

		   //when save card option is off in admin panel then 
		    else
             {               
           	   $token = new_token_create($billing_first_name,$billing_last_name,$card_no,$month,$year,$cvc,$billing_city,$billing_country,$billing_address_1,$billing_address_2,$billing_state,$billing_email);

             
               $charge =create_charge($amount,$currency,$billing_first_name,$billing_last_name,$order_id,$billing_email,$customer_id,$source_id,$shipping_address_1,$shipping_address_2,$shipping_city,$shipping_state,$shipping_country,$shipping_postcode,$shipping_first_name,$shipping_last_name,$billing_phone);  			
		     }

		  if('true'==$this->process_response($charge,$order_id))
          {
    	     $card_key = $charge['customer'];
             // $this->save_source_to_order( $order_id, $card_key );
             update_post_meta($order_id,'_transaction_id',$charge['id']);

	         $customer_order->payment_complete();
 
	         $woocommerce->cart->empty_cart();
 
	         return array(
				   'result'   => 'success',
				   'redirect' => $this->get_return_url( $customer_order ),
			       );
          }
    else  {
    	     wc_add_notice( 'Payment processing failed. Please retry.', 'error' );
          }      
                 
                
         
	 	}


	public function process_response($charge,$order_id)
	  {
		$order = wc_get_order( $order_id );
		$status = $charge['status'];		
		if($status=='pending')
		{
			$order->update_status( 'on-hold', sprintf( __( 'Stripe charge awaiting payment ('.$charge['id'].')', 'custom_stripe_gateway' ), $charge->id ) );
			return 'true';
		}
		if($status=='succeeded')
		{
			$order->add_order_note( __( 'Stripe charge complete ('.$charge['id'].')', 'custom_stripe_gateway' ) );
			return 'true';
		}
		if($status=='failed')
		{
			$order->add_order_note( __( 'Payment processing failed. Please retry.', 'custom_stripe_gateway' ) );
			return 'false';
		}

	}

    public function save_card($charge) {
	    $card_number = str_replace( ' ', '', $_POST['custom_stripe_gateway-card-number'] );
		$exp_date_array = explode( "/", $_POST['custom_stripe_gateway-card-expiry'] );
		$exp_month = trim( $exp_date_array[0] );
		$exp_year = trim( $exp_date_array[1] );
		$exp_date = $exp_month . substr( $exp_year, -2 );
		$token = new WC_Payment_Token_CC();
		$token->set_token($charge['source']['id']);
		$token->set_gateway_id( 'custom_stripe_gateway' );
		$token->set_card_type( strtolower( $this->get_card_type( $card_number ) ) );
		$token->set_last4( substr( $card_number, -4 ) );
		$token->set_expiry_month( substr( $exp_date, 0, 2 ) );
		$token->set_expiry_year( '20' . substr( $exp_date, -2 ) );
		$token->set_user_id( get_current_user_id() );
		$token->save();
	}

	public function get_card_type( $number ) {
		if ( preg_match( '/^4\d{12}(\d{3})?(\d{3})?$/', $number ) ) {
			return 'Visa';
		} elseif ( preg_match( '/^3[47]\d{13}$/', $number ) ) {
			return 'American Express';
		} elseif ( preg_match( '/^(5[1-5]\d{4}|677189|222[1-9]\d{2}|22[3-9]\d{3}|2[3-6]\d{4}|27[01]\d{3}|2720\d{2})\d{10}$/', $number ) ) {
			return 'MasterCard';
		} elseif ( preg_match( '/^(6011|65\d{2}|64[4-9]\d)\d{12}|(62\d{14})$/', $number ) ) {
			return 'Discover';
		} elseif  (preg_match( '/^35(28|29|[3-8]\d)\d{12}$/', $number ) ) {
			return 'JCB';
		} elseif ( preg_match( '/^3(0[0-5]|[68]\d)\d{11}$/', $number ) ) {
			return 'Diners Club';
		}
	}

    public function process_refund( $order_id, $amount = null, $reason = '') {
          global $woocommerce; 
          require("stripe-php/init.php");
		  $order = wc_get_order( $order_id );	         

         $amount=number_format($amount)*100; 
         $secret_key='sk_test_OkVL5DK1aV6eE3iPazVcgDVJ00zQas5GTT';
		 \Stripe\Stripe::setApiKey($secret_key);

		 $charge = get_post_meta($order_id,'_transaction_id',true);
         $refund = \Stripe\Refund::create([
                  'charge' => $charge,
                  'amount' =>$amount
                 ]);
        return true;
    }

// class end
		
 	}
}

// payment process function token,customer,charge

function new_token_create($billing_first_name,$billing_last_name,$card_no,$month,$year,$cvc,$billing_city,$billing_country,$billing_address_1,$billing_address_2,$billing_state,$billing_email)
     {
    	
    	try {
    	 $token= \Stripe\Token::create(array(
 		                     "card" => array(
	                         "name"   => $billing_first_name. " " .$billing_last_name,
   		                     "number" => $card_no,
   		                     "exp_month" => $month,
   		                     "exp_year" => $year,
    	                     "cvc" => $cvc,
    	                     "address_city"=> $billing_city,
                             "address_country"=> $billing_country,
                             "address_line1"=> $billing_address_1,
                             "address_line2"=> $billing_address_2,
                             "address_state"=> $billing_state,
  		                     "metadata" => array("customer_email" => $billing_email),
 		                           )
		                        ));

		} catch(\Stripe\Exception\CardException $e) {
 
        echo 'Status is:' . $e->getHttpStatus() . '\n';
        echo 'Type is:' . $e->getError()->type . '\n';
        echo 'Code is:' . $e->getError()->code . '\n';
  
        echo 'Param is:' . $e->getError()->param . '\n';
        echo 'Message is:' . $e->getError()->message . '\n';
      } catch (\Stripe\Exception\RateLimitException $e) {
  
      } catch (\Stripe\Exception\InvalidRequestException $e) {
  
      } catch (\Stripe\Exception\AuthenticationException $e) {
 
      } catch (\Stripe\Exception\ApiConnectionException $e) {
 
      } catch (\Stripe\Exception\ApiErrorException $e) {

      } catch (Exception $e) {
      	echo $e;
  
      }  
      return $token;   
	}

function create_customer($billing_first_name,$billing_last_name,$billing_phone,$token_ID,$billing_email,$billing_city,$billing_country,$billing_address_1,$billing_address_2,$billing_postcode,$billing_state,$order_id)
     {
    	
    	try {
    	 $customer = \Stripe\Customer::create([
   			         	      'name'   => $billing_first_name. " " .$billing_last_name,
   			         	      'phone' => $billing_phone,
   			                  'source' => $token_ID,
    		                  'email' => $billing_email,
    		                  "address" => array("city" => $billing_city,
                                                 "country" => $billing_country,
                                                 "line1" => $billing_address_1, 
                                                 "line2" => $billing_address_2, 
                                                 "postal_code" => $billing_postcode, 
                                                 "state" => $billing_state),
                             'description' => $billing_first_name. ' - Order No '. $order_id,
                           ]);

		} catch(\Stripe\Exception\CardException $e) {
 
        echo 'Status is:' . $e->getHttpStatus() . '\n';
        echo 'Type is:' . $e->getError()->type . '\n';
        echo 'Code is:' . $e->getError()->code . '\n';
  
        echo 'Param is:' . $e->getError()->param . '\n';
        echo 'Message is:' . $e->getError()->message . '\n';
      } catch (\Stripe\Exception\RateLimitException $e) {
  
      } catch (\Stripe\Exception\InvalidRequestException $e) {
  
      } catch (\Stripe\Exception\AuthenticationException $e) {
 
      } catch (\Stripe\Exception\ApiConnectionException $e) {
 
      } catch (\Stripe\Exception\ApiErrorException $e) {

      } catch (Exception $e) {
      	echo $e;
  
      }  
      return $customer;   
	}


	function create_charge($amount,$currency,$billing_first_name,$billing_last_name,$order_id,$billing_email,$customer_id,$source_id,$shipping_address_1,$shipping_address_2,$shipping_city,$shipping_state,$shipping_country,$shipping_postcode,$shipping_first_name,$shipping_last_name,$billing_phone)
     {
    	
    	try {
    		$randkey = base64_encode(openssl_random_pseudo_bytes(32));
    			$charge = \Stripe\Charge::create(array(
  		            "amount" => $amount,
  		            "currency" => $currency,
  		            "description" => $billing_first_name. ' ' .$billing_last_name. ' - Charged for Order No '. $order_id,
  		            "metadata" => array("customer_email" => $billing_email,
  		       	                        "customer_name" =>$billing_first_name,
  		       	                        "order_id" =>$order_id),
  		             "customer" => $customer_id,
 		             "source" => $source_id,

 		             "receipt_email" => $billing_email,
 		             "shipping" => array(
				             "address" => array(
					               "line1"			=> $shipping_address_1,
					               "line2"			=> $shipping_address_2,
					               "city"			=> $shipping_city,
					               "state"			=> $shipping_state,
					               "country"		=> $shipping_country,
					               "postal_code"	=> $shipping_postcode
				                ),
				             "name" => $shipping_first_name . ' ' . $shipping_last_name,
				             "phone"=> $billing_phone
			             ) 		        
		           ),
    			[ "idempotency_key" => $randkey]);	
    		
    	

		} catch(\Stripe\Exception\CardException $e) {
 
        echo 'Status is:' . $e->getHttpStatus() . '\n';
        echo 'Type is:' . $e->getError()->type . '\n';
        echo 'Code is:' . $e->getError()->code . '\n';
  
        echo 'Param is:' . $e->getError()->param . '\n';
        echo 'Message is:' . $e->getError()->message . '\n';
      } catch (\Stripe\Exception\RateLimitException $e) {
  
      } catch (\Stripe\Exception\InvalidRequestException $e) {
  
      } catch (\Stripe\Exception\AuthenticationException $e) {
 
      } catch (\Stripe\Exception\ApiConnectionException $e) {
 
      } catch (\Stripe\Exception\ApiErrorException $e) {

      } catch (Exception $e) {
      	echo $e;
  
      }  
      return $charge;   
	}