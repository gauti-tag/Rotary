<?php

if (isset($_POST) && $_SERVER["REQUEST_METHOD"] == "POST") {

    $transaction_id = $_POST['cpm_trans_id'];
    $site_id = $_POST['cpm_site_id'];
    $api_key = "33090642464ee1c640d6e35.22816872";
    
    $data = array(
        "apikey" => $api_key,
        "site_id" => $site_id,
        "transaction_id" => $transaction_id
    );

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api-checkout.cinetpay.com/v2/payment/check',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>json_encode($data, JSON_PRETTY_PRINT),
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    
    $object = json_decode($response);

    if ($object->message == "SUCCES")
    {

        $update_data = array(
            "records" => array(
                array(
                    "id" => $transaction_id,
                    "fields" => array(
                        "Moyen De Paiement" => $object->data->payment_method,
                        "Référence De Paiement" => $object->data->operator_id,
                        "statut" => $object->data->status,
                        "Date De Paiement" => $object->data->payment_date
                    )
                )
            )
        );

    }else{
        
        $update_data = array(
            "records" => array(
                array(
                    "id" => $transaction_id,
                    "fields" => array(
                        "Moyen De Paiement" => $object->data->payment_method,
                        "statut" => $object->data->status,
                    )
                )
            )
        );

    }


    $curl = curl_init();
    curl_setopt_array($curl, array(

        CURLOPT_URL => "https://api.airtable.com/v0/appHQucHvSDAOanyW/Don-RCB",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PATCH',
        CURLOPT_POSTFIELDS => json_encode($update_data, JSON_PRETTY_PRINT),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer keySC50HbEpJT9R3U'
          ),
    ));
    
    $update_record_response = curl_exec($curl);
    curl_close($curl);

    $message = $object->data->status;
    
    echo json_encode(array("message"=> $message, "status" => $object->message));
}

?>