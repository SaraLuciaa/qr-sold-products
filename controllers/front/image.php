<?php

class QrsoldproductsImageModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $filename = Tools::getValue('file');
        
        if (!$filename || !preg_match('/^profile_\d+_\d+\.(jpg|jpeg|png|gif)$/', $filename)) {
            http_response_code(404);
            exit;
        }
        
        $filepath = _PS_MODULE_DIR_ . 'qrsoldproducts/uploads/' . $filename;
        
        if (!file_exists($filepath)) {
            http_response_code(404);
            exit;
        }
        
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        if (!isset($mimeTypes[$extension])) {
            http_response_code(404);
            exit;
        }
        
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: ' . $mimeTypes[$extension]);
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: public, max-age=31536000');
        header('Access-Control-Allow-Origin: *');
        
        readfile($filepath);
        exit;
    }
} 