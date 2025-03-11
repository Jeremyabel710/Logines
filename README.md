# Documentación de Cambios en Funciones

## 1. `download($filename)`

### Antes:
```php
public function download($filename) {
    return Storage::disk('tenant')->download('sale_opportunity_files'.DIRECTORY_SEPARATOR.$filename);
}
```

### Después:
```php
public function download($filename) {
    try {
        $decodedFilename = urldecode($filename);
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
                            modal.style.background = "#ffff";
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
```

### Cambios Realizados:
- Se validó si el archivo existe antes de intentar descargarlo.
- Se agregó una ventana modal de error si el archivo no existe.
- Se capturaron excepciones para evitar fallos inesperados.

---

## 2. `getFile($filename)`

### Antes:
```php
public function getFile($filename) {
    $file = Storage::disk('tenant')->get('sale_opportunity_files'.DIRECTORY_SEPARATOR.$filename);
    $temp = tempnam(sys_get_temp_dir(), 'tmp_sale_opportunity_files');
    file_put_contents($temp, $file);
    $mime = mime_content_type($temp);
    $data = file_get_contents($temp);

    $image = 'data:' . $mime . ';base64,' . base64_encode($data);
    return $image;
}
```

### Después:
```php
public function getFile($filename) {
    $path = 'sale_opportunity_files' . DIRECTORY_SEPARATOR . $filename;

    if (!Storage::disk('tenant')->exists($path)) {
        header("Content-Type: text/html");
        die('
            <html>
            <head>
                <script>
                    window.onload = function() {
                        document.body.style.background = "rgba(0,0,0,0.5)";

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
                        modal.style.width = "300px";

                        let message = document.createElement("p");
                        message.textContent = "❌ No se ha encontrado uno de los archivos. Por favor vuelva a subirlos";
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
            <body style="margin: 0; padding: 0; background: rgba(0,0,0,0.5);"></body>
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
```

### Cambios Realizados:
- Se agregó validación para verificar si el archivo existe.
- Se agregó una ventana modal de error si el archivo no se encuentra.
- Se forzó el `Content-Type: text/html` para evitar interpretaciones incorrectas.
- Se mejoró la experiencia de usuario al mostrar un mensaje claro de error.

---

## Conclusión
Estos cambios mejoran la estabilidad y experiencia de usuario en la descarga y obtención de archivos, evitando errores inesperados y proporcionando mensajes claros en caso de fallos.
