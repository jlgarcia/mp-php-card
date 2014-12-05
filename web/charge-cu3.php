<?php
  session_start();
  require_once('./apihelper.php');

  if ($_POST) {
    $error = NULL;
    $card_token = $_POST['card_token'];
    $amount = $_SESSION['amount']; 
    $payer_email = $_SESSION['payer_email'];



    $body = array();
    $body['transaction_amount'] = $amount;
    $body['installments'] = $installments;
    $body['card'] = $card_token;

    //Deberiamos hacer esto opcional
    $body['description'] = 'PHP reason';
    $body['installments'] = 1;
    $body['payment_method_id'] = 'visa';
    //$body['payment_method_id'] = get_payment_method_id($card_token);


    if (isset($_SESSION['customer_id'])){
      $body['payer'] = array("type" => "customer",
                          "id" => $_SESSION['customer_id']);
      //$body['customer'] = $_SESSION['customer_id'];
    }
    else{
      $body['payer_email'] =$payer_email;
    }


    try {

      $payment = create_payment_mp($body);

      if (isset($_SESSION['customer_id']) && !isset($_POST['cardId'])){
          //Agregar tarjeta al usuario ya creado
          customer_add_card($_SESSION['customer_id'], $card_token);
          $customer_id = $_SESSION['customer_id'];
      }
      else{
          //Crear el customer en MP. 
          $customer = create_mp_customer($payer_email, $card_token);
          $customer_id = $customer->id;
      }

    }
    catch (Exception $e) {
      $error = $e->getMessage();
    }

    if ($error == NULL) {
      echo "<h3>Payment id: $payment->payment_id </h3>";
      echo "<h3>Payment status: $payment->status </h3>";
      echo '<pre>';
      print_r($payment);
      echo '</pre>';
    }
    else {
      echo "<div class=\"error\">".$error."</div>";
      require_once('./login.html');
    }
  }
  session_destroy();
?>