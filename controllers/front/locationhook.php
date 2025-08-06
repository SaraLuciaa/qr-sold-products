<?php

class QrsoldproductsLocationhookModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(Tools::file_get_contents('php://input'), true);
            file_put_contents(_PS_MODULE_DIR_ . 'qrsoldproducts/debug_log.txt', "INPUT:\n" . print_r($input, true), FILE_APPEND);

            if (!$input || !isset($input['lat']) || !isset($input['lon']) || !isset($input['qr_code'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos incompletos']);
                exit;
            }

            $lat = (float) $input['lat'];
            $lon = (float) $input['lon'];
            $qr_code = pSQL($input['qr_code']);
        
            $user_name = strtoupper(Db::getInstance()->getValue("SELECT user_name FROM " . _DB_PREFIX_ . "qsp_customer_codes WHERE id_qr_code = '$qr_code'"));

            // Buscar los contactos de emergencia asociados al código QR
            $contacts = Db::getInstance()->executeS("
                SELECT c.contact_name, c.contact_phone, cc.user_name
                FROM " . _DB_PREFIX_ . "qsp_qr_codes q
                INNER JOIN " . _DB_PREFIX_ . "qsp_customer_codes cc ON cc.id_qr_code = q.id_qr_code
                INNER JOIN " . _DB_PREFIX_ . "qsp_customer_contacts c ON c.id_customer_code = cc.id_customer_code
                WHERE q.code = '$qr_code' AND q.status = 'ACTIVO'
                ORDER BY c.contact_index ASC
                LIMIT 2
            ");

            if (!$contacts || count($contacts) == 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Contactos de emergencia no encontrados']);
                exit;
            }

            $enviados = 0;
            foreach ($contacts as $contact) {
                $phone = $contact['contact_phone'];
                if ($phone) {
                    $this->sendLocationViaApiChat($user_name, $phone, $lat, $lon);
                    $enviados++;
                }
            }

            if ($enviados > 0) {
                echo json_encode(['status' => 'Ubicación enviada a ' . $enviados . ' contacto(s) de emergencia']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'No se pudo enviar ubicación a ningún contacto de emergencia']);
            }
            exit;

        } catch (Throwable $e) {
            file_put_contents(_PS_MODULE_DIR_ . 'qrsoldproducts/debug_log.txt', "ERROR:\n" . $e->getMessage(), FILE_APPEND);
            http_response_code(500);
            echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
            exit;
        }
    }

    private function sendLocationViaApiChat($user_name, $phone, $lat, $lon)
    {
        $url = "https://api.apichat.io/v1/sendLocation";

        $data = [
            "number" => "57" . preg_replace('/\D/', '', $phone),
            "chat_type" => "normal",
            "address" => "Hola, te escribimos de EMERGENCIA ID para informarte que el código QR de Emergencia de " . $user_name . " ha sido escaneado.",
            "latitude" => $lat,
            "longitude" => $lon
        ];

        $headers = [
            "client-id: 25027", // <-- cambia por tu client-id real
            "token: smtcDBTb05Jk", // <-- cambia por tu token real
            "Content-Type: application/json"
        ];

        file_put_contents(_PS_MODULE_DIR_ . 'qrsoldproducts/debug_log.txt', "ENVIANDO:\n" . json_encode($data) . "\n", FILE_APPEND);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        $error = curl_error($ch);

        file_put_contents(_PS_MODULE_DIR_ . 'qrsoldproducts/debug_log.txt', "RESPUESTA:\n" . $response . "\n", FILE_APPEND);
        if ($error) {
            file_put_contents(_PS_MODULE_DIR_ . 'qrsoldproducts/debug_log.txt', "CURL ERROR:\n" . $error . "\n", FILE_APPEND);
        }

        curl_close($ch);
    }
}