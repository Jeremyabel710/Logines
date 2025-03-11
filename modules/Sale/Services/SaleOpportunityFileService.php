<?php

namespace Modules\Sale\Services;

use Illuminate\Support\Facades\Storage;

class SaleOpportunityFileService
{

    public function getFile($filename)
    {
        $path = 'sale_opportunity_files' . DIRECTORY_SEPARATOR . $filename;

        if (!Storage::disk('tenant')->exists($path)) {
            die('
                <html>
                <head>
                    <script>
                        window.onload = function() {
                            let modal = document.createElement("div");
                            modal.style.position = "fixed";
                            modal.style.top = "50%";
                            modal.style.left = "50%";
                            modal.style.transform = "translate(-50%, -50%)";
                            modal.style.background = "#fff";
                            modal.style.padding = "20px";
                            modal.style.boxShadow = "0px 4px 6px rgba(0, 0, 0, 0.1)";
                            modal.style.borderRadius = "8px";
                            modal.style.textAlign = "center";
                            modal.style.zIndex = "1000";
        
                            let message = document.createElement("p");
                            message.textContent = "❌ El archivo no existe.";
                            message.style.color = "#d9534f";
                            message.style.fontSize = "18px";
                            message.style.marginBottom = "15px";
        
                            let button = document.createElement("button");
                            button.textContent = "Cerrar";
                            button.style.background = "#d9534f";
                            button.style.color = "#fff";
                            button.style.border = "none";
                            button.style.padding = "10px 20px";
                            button.style.cursor = "pointer";
                            button.style.borderRadius = "5px";
        
                            button.onclick = function() {
                                window.close();
                            };
        
                            modal.appendChild(message);
                            modal.appendChild(button);
                            document.body.appendChild(modal);
                        };
                    </script>
                </head>
                <body style="background: rgba(0,0,0,0.5);"></body>
                </html>
            ');
            exit;
        }                     
    
        $file = Storage::disk('tenant')->get($path);
        $temp = tempnam(sys_get_temp_dir(), 'tmp_sale_opportunity_files');
        file_put_contents($temp, $file);
        $mime = mime_content_type($temp);
        $data = file_get_contents($temp);

        $image = 'data:' . $mime . ';base64,' . base64_encode($data);
        
        return $image;
    }
    

    public function isImage($filename)
    {

        $image_types = [
            'jpeg',
            'jpg',
            'png',
            'svg',
            'bmp',
            'tiff',
        ];

        $array_filename = explode('.', $filename);
         
        return (in_array($array_filename[1], $image_types)) ?? false;
    }

}