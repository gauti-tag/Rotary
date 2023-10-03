<?php

$name = isset($_POST['name']) ? $_POST['name'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
$montant = isset($_POST['montant']) ? $_POST['montant'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $montant = $_POST["montant"];

    // Validation
    if (empty($name) || empty($email) || empty($phone) || empty($montant)) {
        echo "Tous les champs sont requis.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Adresse email invalide.";
        exit;
    }

    if (!preg_match("/^(\+225|00225)?[ -]?(\d{2}[ -]?){4}\d{2}$/", $phone)) {
        echo "Numéro de téléphone invalide.";
        exit;
    }

    if (!is_numeric($montant) || $montant <= 0) {
        echo "Montant invalide.";
        exit;
    }

    // Envoi des données à AIRTABLE
    $api_url = "https://api.airtable.com/v0/appHQucHvSDAOanyW/Don-RCB";
    $api_key = "keySC50HbEpJT9R3U";
    //$current_date = new DateTime("now");
    //$transaction_id = (string) $date->getTimestamp();
    $headers = array(
        "Authorization: Bearer " . $api_key,
        "Content-Type: application/json"
    );

    $data = array(
        "fields" => array(
            "Nom & Prénoms" => $name,
            "Adresse email" => $email,
            "Téléphone" => $phone,
            "Montant Don" => $montant,
            //"TransactionID" => $transaction_id,
            "statut" => "En attente"
        )
    );

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $err = curl_error($ch);

    curl_close($ch);

    if ($err) {

        $message = "Erreur lors de l'envoi des données à AIRTABLE: $err";
   
    } else {
        
        // Envoi des données à CINETPAY
        $cinetpay_payment_url = "https://api-checkout.cinetpay.com/v2/payment";
        $transaction_id = json_decode($response)->id;  
        $payment_data = array(
                "apikey" => "33090642464ee1c640d6e35.22816872",
                "site_id" => "922853",
                "transaction_id" => $transaction_id,
                "amount" => $montant,
                "currency" => "XOF",
                "description" => "Rotary Donation",
                "notify_url" => "https://topdigitalevel.site/rotary/server/notification.php"
        );

        $request = curl_init($cinetpay_payment_url);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($request, CURLOPT_POSTFIELDS, json_encode($payment_data));
        curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);

        $payment_response = curl_exec($request);
        $payment_err = curl_error($request);

        curl_close($request);

        if ($payment_err){
            $message = "Erreur lors du paiement: $payment_err";
        }

            $message = "Lien de paiement genere avec succes.";

            $object = json_decode($payment_response)->data;
            $url_generated = $object->payment_url;

            $current_date = date('d-m-Y h:i:s');

            $update_data = array(
                "records" => array(
                    array(
                        "id" => $transaction_id,
                        "fields" => array(
                            "Lien De Paiement" => $url_generated,
                            "TransactionID" => $transaction_id,
                            "statut" => "En attente de confirmation",
                            "Date Ordre De Paiement" => $current_date
                        )
                    )
                )
            );

            $curl = curl_init();
            curl_setopt_array($curl, array(

                CURLOPT_URL => $api_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PATCH',
                CURLOPT_POSTFIELDS => json_encode($update_data, JSON_PRETTY_PRINT),
                CURLOPT_HTTPHEADER => $headers,
            ));
            
            $update_record_response = curl_exec($curl);
            curl_close($curl);        
    }

    echo json_encode(array("message" => $message, "url" => trim($url_generated))); 
}
?>