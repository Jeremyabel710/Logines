<?php

namespace Modules\Sale\Http\Controllers;

use App\Http\Controllers\SearchItemController;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Item;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Modules\Sale\Models\SaleOpportunityFile;
use Illuminate\Support\Facades\Storage;
use Modules\Finance\Helpers\UploadFileHelper;
use Exception;
class SaleOpportunityFileController extends Controller
{

    public function saveFiles($sale_opportunity, $files)
    {

        foreach ($files as $row) {

            $file = isset($row['response']['data']) ? $row['response']['data'] : null;

            if($file){

                $temp_path = $file['temp_path'];

                $file_name_old = $file['filename'];
                $file_name_old_array = explode('.', $file_name_old);
                $file_content = file_get_contents($temp_path);
                $extension = $file_name_old_array[1];
                $file_name = Str::slug($file_name_old_array[0]).'-'.$sale_opportunity->id.'.'.$extension;

                // validaciones archivos
                $allowed_file_types_images = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/svg'];
                $is_image = UploadFileHelper::getIsImage($temp_path, $allowed_file_types_images);

                $allowed_file_types = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/svg', 'application/pdf'];
                UploadFileHelper::checkIfValidFile($file_name, $temp_path, $is_image, 'jpg,jpeg,png,gif,svg,pdf', $allowed_file_types);
                // validaciones archivos
                
                Storage::disk('tenant')->put('sale_opportunity_files'.DIRECTORY_SEPARATOR.$file_name, $file_content);

            }else{

                $file_name = $row['filename'];

            }


            $sale_opportunity->files()->create([
                'filename' => $file_name
            ]);

        }

    }

    public function uploadFile(Request $request)
    {

        $validate_upload = UploadFileHelper::validateUploadFile($request, 'file', 'jpg,jpeg,png,gif,svg,pdf', false);

        if(!$validate_upload['success']){
            return $validate_upload;
        }

        if ($request->hasFile('file')) {
            $new_request = [
                'file' => $request->file('file'),
                'type' => $request->input('type'),
            ];

            return $this->upload_file($new_request);
        }
        return [
            'success' => false,
            'message' =>  'No es un archivo',
        ];
    }


    function upload_file($request)
    {
        $file = $request['file'];
        $type = $request['type'];

        $temp = tempnam(sys_get_temp_dir(), $type);
        file_put_contents($temp, file_get_contents($file));

        $mime = mime_content_type($temp);
        $data = file_get_contents($temp);

        return [
            'success' => true,
            'data' => [
                'filename' => $file->getClientOriginalName(),
                'temp_path' => $temp,
            ]
        ];

    }

    public function download($filename) {
try {
        $decodedFilename = urldecode($filename); // Decodificar el nombre del archivo
        $path = 'sale_opportunity_files' . DIRECTORY_SEPARATOR . $decodedFilename;

        if (!Storage::disk('tenant')->exists($path)) {
            return response()->make('
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
                            modal.style.fontFamily = "Arial, sans-serif";

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
            ', 200, ['Content-Type' => 'text/html']);
        }

        return Storage::disk('tenant')->download($path);
    } catch (Exception $e) {
        return response()->make('
            <html>
            <head>
                <script>
                    alert("Ocurrió un error al intentar descargar el archivo.");
                    window.close();
                </script>
            </head>
            <body></body>
            </html>
        ', 500, ['Content-Type' => 'text/html']);
    }
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function searchItemById($id)
    {
        $items =  SearchItemController::searchByIdToModal($id);
        return compact('items');

    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function searchItems(Request $request)
    {
        $items = SearchItemController::getNotServiceItemToModal($request);
        return compact('items');
    }
}
