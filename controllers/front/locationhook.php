<?php

class QrsoldproductsLocationHookModuleFrontController extends ModuleFrontController
{
    public function display()
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(Tools::file_get_contents('php://input'), true);
            file_put_contents(_PS_MODULE_DIR_ . 'qrsoldproducts/debug_log.txt', "INPUT:\n" . print_r($input, true), FILE_APPEND);

            if (!$input || !isset($input['lat']) || !isset($input['lon']) || !isset($input['qr_code'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos incompletos']);
                return;
            }

            $lat = $input['lat'];
            $lon = $input['lon'];
            $qr_code = pSQL($input['qr_code']);

            // Buscar dueño del código

            $owner = Db::getInstance()->getRow("
                SELECT cc.owner_name, cc.owner_phone
                FROM "._DB_PREFIX_."qsp_qr_codes q
                JOIN "._DB_PREFIX_."qsp_customer_codes cc ON cc.id_qr_code = q.id_qr_code
                WHERE q.code = '$qr_code'
            ");

            if (!$owner || empty($owner['owner_phone'])) {
                http_response_code(404);
                echo json_encode(['error' => 'Dueño no encontrado o teléfono no disponible']);
                return;
            }

            $this->sendLocationViaApiChat($owner['owner_phone'], $lat, $lon);

            echo json_encode(['status' => 'Ubicación enviada']);
        } catch (Throwable $e) {
            file_put_contents(_PS_MODULE_DIR_ . 'qrsoldproducts/debug_log.txt', "ERROR:\n" . $e->getMessage(), FILE_APPEND);
            http_response_code(500);
            echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
        }
    }

    private function sendLocationViaApiChat($phone, $lat, $lon)
    {
        $url = "https://api.apichat.io/v1/sendLocation";

        $data = [
            "number" => "57" . preg_replace('/\D/', '', $phone),
            "chat_type" => "normal",
            "address" => "¡Hola! Escanearon el QR de tu mascota. Ubicación aproximada:",
            "latitude" => floatval($lat),
            "longitude" => floatval($lon)
        ];

        $headers = [
            "client-id: 25027",  // 🔁 reemplaza por tu client-id
            "token: smtcDBTb05Jk",          // 🔁 reemplaza por tu token
            "Content-Type: application/json"
        ];

        file_put_contents(_PS_MODULE_DIR_ . 'qrsoldproducts/debug_log.txt', "ENVIANDO:\n" . json_encode($data) . "\n", FILE_APPEND);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);

        file_put_contents(_PS_MODULE_DIR_ . 'qrsoldproducts/debug_log.txt', "RESPUESTA:\n" . $response . "\n", FILE_APPEND);

        curl_close($ch);
    }
}
