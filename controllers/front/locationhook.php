<?php

class QrsoldproductsLocationhookModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(Tools::file_get_contents('php://input'), true);

            if (!$input || !isset($input['lat']) || !isset($input['lon']) || !isset($input['qr_code'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos incompletos']);
                exit;
            }

            $lat = (float) $input['lat'];
            $lon = (float) $input['lon'];
            $qr_code = pSQL($input['qr_code']);
        
            // Obtener el user_name usando la consulta correcta que une las tablas
            $user_name = strtoupper(Db::getInstance()->getValue("
                SELECT cc.user_name 
                FROM " . _DB_PREFIX_ . "qsp_qr_codes q
                INNER JOIN " . _DB_PREFIX_ . "qsp_customer_codes cc ON cc.id_qr_code = q.id_qr_code
                WHERE q.code = '$qr_code' AND q.status = 'ACTIVO'
            "));

            // Si no se encuentra el user_name, usar un valor por defecto
            if (!$user_name) {
                $user_name = "USUARIO";
            }

            // Buscar los contactos de emergencia asociados al código QR con información de país
            $contacts = Db::getInstance()->executeS("
                SELECT c.contact_name, c.contact_phone_number, co.call_prefix, cc.user_name
                FROM " . _DB_PREFIX_ . "qsp_qr_codes q
                INNER JOIN " . _DB_PREFIX_ . "qsp_customer_codes cc ON cc.id_qr_code = q.id_qr_code
                INNER JOIN " . _DB_PREFIX_ . "qsp_customer_contacts c ON c.id_customer_code = cc.id_customer_code
                LEFT JOIN " . _DB_PREFIX_ . "country co ON c.contact_country_id = co.id_country
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
            $exitosos = 0;
            foreach ($contacts as $contact) {
                $phone = $contact['contact_phone_number'];
                $country_prefix = $contact['call_prefix'];
                
                if ($phone) {
                    // Construir el número completo con código de país
                    $full_phone = $country_prefix ? $country_prefix . $phone : $phone;
                    $contact_name = $contact['contact_name'];
                    
                    if ($this->sendMessageViaApiChat($user_name, $full_phone, $lat, $lon)) {
                        $exitosos++;
                    }
                    $enviados++;
                }
            }

            if ($enviados > 0) {
                $message = 'Ubicación enviada a ' . $enviados . ' contacto(s) de emergencia';
                echo json_encode(['status' => $message]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'No se pudo enviar ubicación a ningún contacto de emergencia']);
            }
            exit;

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
            exit;
        }
    }

    private function sendMessageViaApiChat($user_name, $phone, $lat, $lon)
    {
        $url = "https://api.apichat.io/v1/sendText";
        
        // Configuración de la API (deberías mover esto a configuración del módulo)
        $client_id = "25027";
        $token = "smtcDBTb05Jk";
        
        // Crear el enlace de Google Maps
        $maps_link = "https://www.google.com/maps?q={$lat},{$lon}";
        
        // Mensaje de emergencia
        $message_text = "Hola, te escribimos de EMERGENCIA ID para informarte que el código QR de Emergencia de {$user_name} fue escaneado desde la ubicación de: {$maps_link} Nota: Si no puedes visualizar la ubicación agrega nuestro número a tus contactos.";
        
        // Datos para la API (simplificado como en tu ejemplo de Python)
        $data = [
            "number" => $phone,
            "text" => $message_text
        ];
        
        // Headers como en tu ejemplo de Python
        $headers = [
            "client-id: " . $client_id,
            "token: " . $token,
            "Content-Type: application/json"
        ];
        
        // Realizar la petición POST
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Verificar si el envío fue exitoso
        $response_data = json_decode($response, true);
        if ($http_code === 200 && isset($response_data['success']) && $response_data['success']) {
            return true;
        } else {
            return false;
        }
    }
}