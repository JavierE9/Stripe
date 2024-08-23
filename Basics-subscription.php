
Introducción
Este script PHP está diseñado para manejar suscripciones y pagos utilizando la API de Stripe y enviar notificaciones por correo electrónico a través de PHPMailer. Sin embargo, es importante tener en cuenta que el script contiene ejemplos y placeholders para las claves de API de Stripe, los nombres de las tablas y las consultas SQL. Estos deben ser reemplazados y ajustados según la configuración real de su proyecto.

Puntos a tener en cuenta:
Claves de API de Stripe: Las claves de API (STRIPE_API_KEY y STRIPE_PUBLISHABLE_KEY) son ejemplos y deben ser reemplazadas con las claves reales de su cuenta de Stripe.
Consultas SQL: Las consultas SQL en el script están incompletas y utilizan nombres de tablas y campos como placeholders. Deben ser actualizadas con los nombres de las tablas y los campos reales de su base de datos.
Configuración de PHPMailer: Asegúrese de ajustar la configuración de PHPMailer (host, usuario, contraseña) a los detalles de su servidor de correo.
Validación y Seguridad: El script contiene varias validaciones y mecanismos de seguridad. Asegúrese de que estas validaciones se ajusten a sus necesidades específicas y revise el código para evitar vulnerabilidades.



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Subscription Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #f4f4f4;
        }
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }
        h1 {
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input[type="text"],
        .form-group input[type="password"],
        .form-group input[type="checkbox"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        .form-group input[type="checkbox"] {
            width: auto;
            display: inline-block;
        }
        .form-group input[type="submit"] {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        .form-group input[type="submit"]:hover {
            background-color: #218838;
        }
        #paymentResponse, #paymentResponseContra {
            color: #e23c3c;
            font-size: 14px;
            display: none;
            margin: 15px 0;
        }
        #cuadrogrande {
            transition: filter 0.3s;
        }
        #contracuadro {
            display: none;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .hiddenMsg {
            display: block !important;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container" id="cuadrogrande">
        <div id="contracuadro">
            <h2>Enter Password</h2>
            <div id="psError2" class="form-group" style="display: none;">*Incorrect password</div>
            <input type="password" id="contra" class="form-group" placeholder="Enter password" />
            <div id="paymentResponseContra" class="form-group" style="display: none;"></div>
            <input type="submit" id="boton2" value="Submit" />
            <button id="locierro">Close</button>
        </div>
        <h1>Subscribe to our Plan</h1>
        <form id="subscrFrm">
            <div class="form-group">
                <label for="subscr_plan2">Subscription Plan ID</label>
                <input type="text" id="subscr_plan2" />
            </div>
            <div class="form-group">
                <label for="tipocurrency2">Currency Type</label>
                <input type="text" id="tipocurrency2" />
            </div>
            <div class="form-group">
                <label for="discount">Discount Code</label>
                <input type="text" id="discount" />
            </div>
            <div class="form-group">
                <label for="card-element">Card Details</label>
                <div id="card-element"></div>
            </div>
            <div class="form-group">
                <input type="checkbox" id="aceptar_terminos" />
                <label for="aceptar_terminos">Accept Terms and Conditions</label>
                <div id="nocheck" style="display: none; color: red;">*You must accept the terms</div>
            </div>
            <input type="submit" value="Subscribe" />
        </form>
        <div id="paymentResponse" class="hidden"></div>
        <div id="frmProcess" style="display: none;">Processing...</div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        let STRIPE_PUBLISHABLE_KEY = 'pk_test';
        const stripe = Stripe(STRIPE_PUBLISHABLE_KEY);
        const subscrFrm = document.querySelector('#subscrFrm');

        subscrFrm.addEventListener('submit', handleSubscrSubmit);

        document.getElementById('aceptar_terminos').onclick = function() {
            document.getElementById('nocheck').style.display = 'none';
        };

        document.getElementById('contra').onclick = function() {
            document.getElementById('psError2').style.display = 'none';
        };

        document.getElementById('boton2').onclick = function() {
            var checkBox = document.getElementById('aceptar_terminos');
            if (checkBox.checked == true) {
                document.getElementById('cuadrogrande').style.filter = 'blur(10px)';
                document.getElementById('contracuadro').style.display = 'inherit';
                location.href = '#parte2';
            } else {
                document.getElementById('nocheck').style.display = 'inherit';
            }
        };

        document.getElementById('locierro').onclick = function() {
            document.getElementById('cuadrogrande').style.filter = 'inherit';
            document.getElementById('contracuadro').style.display = 'none';
        };

        let elements = stripe.elements();
        var style = {
            base: {
                lineHeight: '30px',
                fontSize: '16px',
                border: '1px solid #ced4da',
            }
        };

        let cardElement = elements.create('card', {
            style: {
                base: {
                    iconColor: '#333',
                    color: '#333',
                    fontWeight: '500',
                    fontSize: '16px',
                    ':-webkit-autofill': {
                        color: '#fce883',
                    },
                    '::placeholder': {
                        color: '#757575',
                    },
                },
                invalid: {
                    iconColor: '#e23c3c',
                    color: '#e23c3c',
                },
            },
        });
        cardElement.mount('#card-element');
        cardElement.on('change', function (event) {
            displayError(event);
        });

        function displayError(event) {
            if (event.error) {
                showmonthsage(event.error.message);
            }
        }

        async function handleSubscrSubmit(e) {
            e.preventDefault();
            document.getElementById('cuadrogrande').style.filter = 'inherit';
            document.getElementById('contracuadro').style.display = 'none';
            let contra = document.getElementById('contra').value;
            const testContra = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@%!¡¿?#\.])[A-Za-z\d@%!¡¿?#\.]{8,40}$/;
            if (testContra.test(contra) && contra != '' && contra.length <= 40) {
                document.getElementById('paymentResponseContra').style.display = 'none';
                document.getElementById('paymentResponseContra').innerHTML = '';
                let subscr_plan_id = document.getElementById('subscr_plan2').value;
                let tipocurrency = document.getElementById('tipocurrency2').value;
                let discount = document.getElementById('discount').value;
                document.getElementById('frmProcess').style.display = 'inherit';
                fetch('/6QM...', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ request_type: 'create_customer_subscription', subscr_plan_id: subscr_plan_id, tipocurrency: tipocurrency, discount: discount, contra: contra }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.subscriptionId && data.clientSecret) {
                        paymentProcess(data.subscriptionId, data.clientSecret, data.customerId);
                    } else {
                        if (data.error === 'Incorrect password') {
                            document.getElementById('psError2').style.display = 'inherit';
                            document.getElementById('cuadrogrande').style.filter = 'none';
                            document.getElementById('contracuadro').style.display = 'inherit';
                            document.getElementById('cuadrogrande').style.filter = 'blur(10px)';
                            document.getElementById('frmProcess').style.display = 'none';
                            location.href = '#parte2';
                        } else if (data.error === '') {
                            data.error = 'Check the fields on your card carefully';
                            document.getElementById('frmProcess').style.display = 'none';
                            document.getElementById('cuadrogrande').style.filter = 'inherit';
                            document.getElementById('contracuadro').style.display = 'none';
                        } else {
                            document.getElementById('frmProcess').style.display = 'none';
                            document.getElementById('cuadrogrande').style.filter = 'inherit';
                            document.getElementById('contracuadro').style.display = 'none';
                            showmonthsage(data.error);
                        }
                    }
                });
            } else {
                document.getElementById('paymentResponseContra').style.display = 'inherit';
                document.getElementById('paymentResponseContra').innerHTML = '*Contraseña incorrecta';
            }
        }

        function paymentProcess(subscriptionId, clientSecret, customerId) {
            let contra = document.getElementById('contra').value;
            const testContra = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@%!¡¿?#\.])[A-Za-z\d@%!¡¿?#\.]{8,40}$/;
            if (testContra.test(contra) && contra != '' && contra.length <= 40) {
                let subscr_plan_id = document.getElementById('subscr_plan2').value;
                let tipocurrency = document.getElementById('tipocurrency2').value;
                let discount = document.getElementById('discount').value;
                let customer_name = 'prueba';
                stripe.confirmCardPayment(clientSecret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: customer_name,
                        },
                    }
                }).then((result) => {
                    if (result.error) {
                        showmonthsage(result.error.message);
                    } else {
                        fetch('/6QMH5f311WS1Go2bT71PRnUaLz26zlL/76Rc9Cc4Slk2mLmTO7/ximpn0LuEbL9iP1JL0/EN/dashboard?p=40', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ request_type: 'payment_insert', subscription_id: subscriptionId, customer_id: customerId, subscr_plan_id: subscr_plan_id, payment_intent: result.paymentIntent, tipocurrency: tipocurrency, discount: discount, contra: contra }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.payment_id) {
                                parent.location.href = 'https://www.example.com/login?succes=1';
                            } else {
                                document.getElementById('frmProcess').style.display = 'none';
                                document.getElementById('cuadrogrande').style.filter = 'inherit';
                                document.getElementById('contracuadro').style.display = 'none';
                                showmonthsage(data.error);
                            }
                        });
                    }
                });
            }
        }

        function showmonthsage(monthsageText) {
            const monthsageContainer = document.querySelector('#paymentResponse');
            document.getElementById('frmProcess').style.display = 'none';
            monthsageContainer.classList.remove('hidden');
            monthsageContainer.classList.add('hiddenMsg');
            monthsageContainer.textContent = '*' + monthsageText;
            setTimeout(function () {
                monthsageContainer.classList.add('hidden');
                monthsageContainer.textContent = '';
            }, 6000);
        }
    </script>
</body>
</html>












<?php


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

define('STRIPE_API_KEY', 'sk_test_51');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_51');
require_once($_SERVER['DOCUMENT_ROOT'].'/connect.php');
$db = $conexion;
function test_input($data) {
$data = trim($data);
$data = stripslashes($data);
$data = htmlspecialchars($data);
return $data;
}

require_once($_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php');

\Stripe\Stripe::setApiKey(STRIPE_API_KEY);
$jsonStr = file_get_contents('php://input');
$jsonObj = json_decode($jsonStr);
if($jsonObj->request_type == 'create_customer_subscription'){
$subscr_plan_id = !empty($jsonObj->subscr_plan_id)?$jsonObj->subscr_plan_id:'';
$tipocurrency = !empty($jsonObj->tipocurrency)?$jsonObj->tipocurrency:'';
$discount = !empty($jsonObj->discount)?$jsonObj->discount:'';
$contra = !empty($jsonObj->contra)?$jsonObj->contra:'';
$fase1 = false;
if(isset($_COOKIE["qe84655412tow45444hw12"])) {
$variablecookie2 = test_input($_COOKIE["qe84655412tow45444hw12"]);
$variablecookie = preg_split("/\./",$variablecookie2);
$user = $variablecookie[0];
$decodeR = base64_decode(base64_decode(base64_decode(base64_decode($user))));
$total = strlen($decodeR) / 6;
$Er = 0;
$arr1 = str_split($decodeR, 6);
$userA="";
while($total > $Er){
$arr2 = str_split($arr1[$Er], 5);
$userA .= $arr2[1];
$Er++;}
$cokieprovisional = $variablecookie[1];
$errors = array();
$errmsg="";
$bien = array();
$bnmsg="";
$javascript="";
$javascript2="";
$patter = '/^[a-zA-Z0-9_]{0,75}$/';
$patterCod = '/^[a-zA-Z0-9]{3,20}$/';
$patterCod2 = "/^[a-zA-Z0-9.=]*$/";
$patterContra = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@%!¡¿?#\.])[A-Za-z\d@%!¡¿?#\.]{8,40}$/";
$encontrado = false;
$buscarWord = array("schema", "union", "all", "distinc", "-","--");
$regex_pattern = '/(?:' . implode('|', $buscarWord) . ')/i';
if(preg_match_all($regex_pattern, $userA, $matches)){$encontrado = true;}
if(preg_match_all($regex_pattern, $variablecookie2, $matches)){$encontrado = true;}
if(preg_match($patter, $subscr_plan_id) && preg_match($patter, $tipocurrency) && preg_match($patter, $discount) && preg_match($patterContra, $contra) && $encontrado === false && preg_match($patterCod, $userA) && !empty($userA) && strlen($userA) <= 20 && preg_match($patterCod2, $variablecookie2) && !empty($variablecookie2) && strlen($variablecookie2) <= 500){
$ASASAS = mysqli_query($conexion, "SELECT ...  FROM  ... WHERE user='$userA' ") or die("Ups a habido un error 3499953212" );
$ASASAS3434  = mysqli_fetch_array($ASASAS);
$proR = ;
$PIN = ;
$hash = hash_hmac('sha256', $PIN, $proR, false);
if($hash === $cokieprovisional){
if($tipocurrency === "1" OR $tipocurrency === "2" OR $tipocurrency === "3" OR $tipocurrency === "4" OR $tipocurrency === "5" && $subscr_plan_id === "1" OR $subscr_plan_id === "2" OR $subscr_plan_id === "3" ){
$fase1 = true;
if($tipocurrency === "1" ){
$moneda = 'USD';
if( $subscr_plan_id === "1" ){$pre = "price_1OQ"; }
if( $subscr_plan_id === "2" ){ $pre = "price_1Gq";}
if( $subscr_plan_id === "3" ){$pre = "price_1OqQGq";}
}
if($tipocurrency === "2" ){$moneda = 'EUR';
if( $subscr_plan_id === "1" ){$pre = "price_1OQGq"; }
if( $subscr_plan_id === "2" ){$pre = "price_1OQqQGq"; }
if( $subscr_plan_id === "3" ){ $pre = "price_1OQCGGq"; }
}
if($tipocurrency === "3" ){$moneda = 'GBP';
if( $subscr_plan_id === "1" ){$pre = "price"; }
if( $subscr_plan_id === "2" ){$pre = "price_1"; }
if( $subscr_plan_id === "3" ){ $pre = "price_1MHF9"; }
}
if($tipocurrency === "4" ){$moneda = 'CHF';
if( $subscr_plan_id === "1" ){ $pre = "price_1MHX1jXTX5mQ"; }
if( $subscr_plan_id === "2" ){ $pre = "price_1MHXZbFX2ddxx3QoQEX1aSuy"; }
if( $subscr_plan_id === "3" ){ $pre = "price_1MHXacFX2ddxx3Qo1pIRuit6";}
}
if($tipocurrency === "5" ){$moneda = 'MXN';
if( $subscr_plan_id === "1" ){ $pre = "price_1N"; }
if( $subscr_plan_id === "2" ){ $pre = "price_1N"; }
if( $subscr_plan_id === "3" ){ $pre = "price_1N";}
}
$name = $ASASAS3434['nombre'];
$email = $ASASAS3434['email'];
if($general2Vaa[9] === "1"){
if($general2Vaa[14] === "1" && $subscr_plan_id === "1"){$fase2 = false; $mensajeError = 'Error in the transaction at this time you have this plan!';   }
if($general2Vaa[14] === "2" && $subscr_plan_id === "2"){$fase2 = false;  $mensajeError = 'Error in the transaction at this time you have this plan!';}
if($general2Vaa[14] === "3" && $subscr_plan_id === "3"){$fase2 = false;  $mensajeError = 'Error in the transaction at this time you have this plan!';}
}
}
}
if( $discount !== "" ){
if($discount !== " "  ){
$fase1 = false;
$mensajeError = 'Wrong discount code!';
}else{
$cupon = $discount;
}
}else{
$cupon = "";
}
if(password_verify($contra, $userContra)){
}else{
$fase1 = false;
$mensajeError = 'Incorrect password';
}
}
}
if($fase1 === false ){ echo json_encode(['error' => $mensajeError]);  }
if($fase1 === true ){
try {
$TRYCUSTOM2 = mysqli_query($conexion, "SELECT  user, USD, EUR, GBP, CHF, MXN FROM ...  WHERE user='$userA' ") or die("Ups a habido un error 3499953212" );
if($TRYCUSTOM  = mysqli_fetch_array($TRYCUSTOM2)){
if($TRYCUSTOM['user'] === $userA ){
if($tipocurrency === "1" && $TRYCUSTOM['USD'] !== ""){
$IDEN = $TRYCUSTOM['USD'];
if($cupon !== "" ){
$update = \Stripe\Customer::update(
$IDEN,
["coupon" => $cupon]
);
$customer = \Stripe\Customer::retrieve(
$IDEN,
[]
);
}else{
$customer = \Stripe\Customer::retrieve(
$IDEN,
[]
);
}
}elseif($tipocurrency === "2" && $TRYCUSTOM['EUR'] !== ""){
$IDEN = $TRYCUSTOM['EUR'];
if($cupon !== "" ){
$update = \Stripe\Customer::update(
$IDEN,
["coupon" => $cupon]
);
$customer = \Stripe\Customer::retrieve(
$IDEN,
[]
);
}else{
$customer = \Stripe\Customer::retrieve(
$IDEN,
[]
);
$crearnuevo = false;
}
}elseif($tipocurrency === "3" && $TRYCUSTOM['GBP'] !== ""){
$IDEN = $TRYCUSTOM['GBP'];
if($cupon !== "" ){
$update = \Stripe\Customer::update(
$IDEN,
["coupon" => $cupon]
);
$customer = \Stripe\Customer::retrieve(
$IDEN,
[]
);
}else{
$customer = \Stripe\Customer::retrieve(
$IDEN,
[]
);
$crearnuevo = false;
}
}elseif($tipocurrency === "4" && $TRYCUSTOM['CHF'] !== ""){
$IDEN = $TRYCUSTOM['CHF'];
if($cupon !== "" ){
$update = \Stripe\Customer::update(
$IDEN,
["coupon" => $cupon]
);
$customer = \Stripe\Customer::retrieve(
$IDEN,
[]
);
}else{
$customer = \Stripe\Customer::retrieve(
$IDEN,
[]
);
$crearnuevo = false;
}
}elseif($tipocurrency === "5" && $TRYCUSTOM['MXN'] !== ""){
$IDEN = $TRYCUSTOM['MXN'];
if($cupon !== "" ){
$update = \Stripe\Customer::update(
$IDEN,
["coupon" => $cupon]
);
$customer = \Stripe\Customer::retrieve(
$IDEN,
[]
);
}else{
$customer = \Stripe\Customer::retrieve(
$IDEN,
[]
);
$crearnuevo = false;
}
}else{
if($cupon !== "" ){
$customer = \Stripe\Customer::create([
'name' => $name,
'email' => $email,
"coupon" => $cupon
]);
}else{
$customer = \Stripe\Customer::create([
'name' => $name,
'email' => $email
]);
}
}
}
}
}catch(Exception $e) {
 echo json_encode(['error' => 'Error in the transaction ']);
}
if(empty($api_error) && $customer){
if(empty($api_error) ){
try {
$subscription = \Stripe\Subscription::create([
'customer' => $customer->id,
'items' => [[
'price' => $pre,
'quantity' => 1,
]],
'payment_behavior' => 'default_incomplete',
'expand' => ['latest_invoice.payment_intent'],
]);
}catch(Exception $e) {
 echo json_encode(['error' => 'Error in the transaction ']);
}
if(empty($api_error) && $subscription){
$output = [
'subscriptionId' => $subscription->id,
'clientSecret' => $subscription->latest_invoice->payment_intent->client_secret,
'customerId' => $customer->id
];
echo json_encode($output);
}else{
echo json_encode(['error' => $api_error]);
}
}else{
echo json_encode(['error' => $api_error]);
}
}else{
echo json_encode(['error' => $api_error]);
}
}
}elseif($jsonObj->request_type == 'payment_insert'){
$fase2 = false;
if(isset($_COOKIE["qe84655412tow45444hw12"])) {
$variablecookie2 = test_input($_COOKIE["qe84655412tow45444hw12"]);
$variablecookie = preg_split("/\./",$variablecookie2);
$user = $variablecookie[0];
$decodeR = base64_decode(base64_decode(base64_decode(base64_decode($user))));
$total = strlen($decodeR) / 6;
$Er = 0;
$arr1 = str_split($decodeR, 6);
$userA="";
while($total > $Er){
$arr2 = str_split($arr1[$Er], 5);
$userA .= $arr2[1];
$Er++;}
$cokieprovisional = $variablecookie[1];
$errors = array();
$errmsg="";
$bien = array();
$bnmsg="";
$javascript="";
$javascript2="";
$patterCod = '/^[a-zA-Z0-9]{3,20}$/';
$patterCod2 = "/^[a-zA-Z0-9.=]*$/";
$encontrado = false;
$buscarWord = array("schema", "union", "all", "distinc", "-","--");
$regex_pattern = '/(?:' . implode('|', $buscarWord) . ')/i';
if(preg_match_all($regex_pattern, $userA, $matches)){$encontrado = true;}
if(preg_match_all($regex_pattern, $variablecookie2, $matches)){$encontrado = true;}
if($encontrado === false && preg_match($patterCod, $userA) && !empty($userA) && strlen($userA) <= 20 && preg_match($patterCod2, $variablecookie2) && !empty($variablecookie2) && strlen($variablecookie2) <= 500){
$ASASAS = mysqli_query($conexion, "SELECT  email, nombre, general, rand1 FROM ... WHERE user='$userA' ") or die("Ups a habido un error 3499953212" );
$ASASAS3434  = mysqli_fetch_array($ASASAS);

$hash = hash_hmac('sha256', $provarSNUMBER, $provarPIN, false);
if($hash === $cokieprovisional){
$subscr_plan_id = !empty($jsonObj->subscr_plan_id)?$jsonObj->subscr_plan_id:'';
$tipocurrency = !empty($jsonObj->tipocurrency)?$jsonObj->tipocurrency:'';
$discount = !empty($jsonObj->discount)?$jsonObj->discount:'';
if($tipocurrency === "1" OR $tipocurrency === "2" OR $tipocurrency === "3" OR $tipocurrency === "4" OR $tipocurrency === "5" && $subscr_plan_id === "1" OR $subscr_plan_id === "2" OR $subscr_plan_id === "3" ){
$fase2 = true;
if($tipocurrency === "1" ){
$moneda = 'USD';
if( $subscr_plan_id === "1" ){$planPrice = 3.95;$planName = ""; $planInterval = '3 months';
$planIntervalNum = "3";
$planIntervalMes =  "months";}

 // invent your array with intervals .....
}
}
$name = $ASASAS3434['nombre'];
$email = $ASASAS3434['email'];
if( $discount !== "" ){
if($discount !== ""  ){
$fase2 = false;
}else{
$cupon = $discount;
}
}else{
$cupon = "NONE";
}
}
}
}
}
if($fase2 === false ){ echo json_encode(['error' => 'Error in the transaction ']);   }
if( $fase2 === true){
$payment_intent = !empty($jsonObj->payment_intent)?$jsonObj->payment_intent:'';
$subscription_id = !empty($jsonObj->subscription_id)?$jsonObj->subscription_id:'';
$customer_id = !empty($jsonObj->customer_id)?$jsonObj->customer_id:'';
$subscr_plan_id = !empty($jsonObj->subscr_plan_id)?$jsonObj->subscr_plan_id:'';
try {
$customer = \Stripe\Customer::retrieve($customer_id);
}catch(Exception $e) {
echo json_encode(['error' => 'Error in the transaction ']);
}
if(!empty($payment_intent) && $payment_intent->status == 'succeeded'){
try {
$subscriptionData = \Stripe\Subscription::retrieve($subscription_id);
}catch(Exception $e) {
echo json_encode(['error' => 'Error in the transaction ']);
}

$payment_intent_id = $payment_intent->id;
$paidAmount = $payment_intent->amount;
$paidAmount = ($paidAmount/100);
$paidCurrency = $payment_intent->currency;
$payment_status = $payment_intent->status;

$created = date("Y-m-d H:i:s", $payment_intent->created);
$current_period_start = $current_period_end = '';
if(!empty($subscriptionData)){
$created = date("Y-m-d H:i:s", $subscriptionData->created);
$current_period_start = date("Y-m-d H:i:s", $subscriptionData->current_period_start);
$current_period_end = date("Y-m-d H:i:s", $subscriptionData->current_period_end);
}
$payment_id = 0;
try{
if( $general2Vaa[9] === "1"){
$plansB = mysqli_query($conexion, "SELECT ... FROM ...  WHERE email='$email'  ") or die("Ups a habido un error 224277" );
if($plansB = mysqli_fetch_array($plansB)){
if($email === $plansB['email'] ){
$stripe = new \Stripe\StripeClient(
'sk_test_5'
);
$df = $plansB['stripe_subscription_id'];
$stripe->subscriptions->cancel(
$df,
[]
);
mysqli_query($conexion, "delete from where ") or die("Problemas en el select error 966666554646B:" );
$newcreated = $plansB['created'];
}else{$created = $newcreated;  }
}else{
$newcreated = $created;
$tz = 'Africa/Abidjan';
$timestamp = time();
$dt = new DateTime("now", new DateTimeZone($tz));
$created = $dt->format('Y-m-d H:i:s');
}
}
$paymentIntentTxn = \Stripe\PaymentIntent::retrieve([
'id' => $payment_intent_id,
'expand' => ['latest_charge.balance_transaction'],
]);
$NET = $paymentIntentTxn -> latest_charge -> balance_transaction -> net;
$NET = $NET / 100;
$consulta2 = "INSERT INTO ...(user, email, stripe_subscription_id, stripe_customer_id, stripe_payment_intent_id, paid_amount, paid_amount_currency, plan_interval, created, plan_period_start, plan_period_end, promotion, Neto) VALUES ('$userA','$email','$subscription_id','$customer_id','$payment_intent_id','$paidAmount','$paidCurrency','$planInterval','$created','$current_period_start','$current_period_end','$cupon', '$NET')";
$resultado2 = mysqli_query($conexion,$consulta2);

} catch (Exception $efail2) {

}
$pagado = $paidAmount;
$moneda = $paidCurrency;
$Startime = $current_period_start;
$Endtime = $current_period_end;
$franjaPais2 = preg_split("/\,/",$franjaHoraria);
$franjaPais = $franjaPais2[1];
if($franjaPais > 0){
$d2 = DateTime::createFromFormat( 'Y-m-d H:i:s', $created );
$d2->modify( '+'.$franjaPais.' hours');
$created = $d2->format( 'Y-m-d H:i:s' );
}
if($franjaPais < 0) {
$d3 = DateTime::createFromFormat( 'Y-m-d H:i:s', $created );
$d3->modify( '-'.$franjaPais.' hours');
$created = $d3->format( 'Y-m-d H:i:s' );
}
if($franjaPais === 0){
$d4 = DateTime::createFromFormat( 'Y-m-d H:i:s', $created );
$created = $d4->format( 'Y-m-d H:i:s' );
}
if($cupon === 'NONE'){
$mailInside = "
<html>
<body>
</html> ..............    ";

require $_SERVER['DOCUMENT_ROOT'].'/mailchipblabla...';
$mail = new PHPMailer(true);
try {
$mail->SMTPDebug = 0;
$mail->isSMTP();
$mail->Host       = 'mail.example.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'accounts@example.com';
$mail->Password   = 'xdoTC!pn$';
$mail->SMTPSecure = 'ssl';
$mail->Port       = 465;
$mail->setFrom('accounts@example.com', 'example');
$mail->addAddress($email, $userA);
$mail->isHTML(true);
$mail->Subject = 'YOU ARE PREMIUM | example';
$mail->Body    = $mailInside;
$mail->send();
} catch (Exception $efail) {
$tz = 'Africa/Abidjan';
$timestamp = time();
$dt = new DateTime("now", new DateTimeZone($tz));
$actual = $dt->format('Y-m-d H:i:s');
$errorfatal = "error se ha pagado pero no enviado mail";
$consulta23 = "INSERT INTO (user, error, fecha) VALUES ('$userA','$errorfatal','$actual')";
$resultado23 = mysqli_query($conexion,$consulta23);
}
$output = [
'payment_id' => base64_encode($payment_id)
];
echo json_encode($output);
}else{
echo json_encode(['error' => 'Transaction has been failed!']);
}
}
}
?>
