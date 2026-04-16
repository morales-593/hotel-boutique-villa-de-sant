<?php
require_once "../config/config.php";
require_once "../config/database.php";
require_once "../models/Experience.php";
require_once "../config/session.php";
checkLogin();

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$experience = new Experience($db);

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'update_section':
        $seccion = $_POST['seccion'] ?? '';
        if (!in_array($seccion, ['transporte', 'tours'])) {
            echo json_encode(['success' => false, 'message' => 'Sección inválida']);
            exit;
        }

        // Obtener imagen actual
        $imagen_actual = $experience->getSectionImage($seccion);
        $nueva_imagen = $imagen_actual;

        // Manejar upload de imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['imagen']['tmp_name'];
            $file_name = $_FILES['imagen']['name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed)) {
                $upload_dir = '../assets/img/experiencia/uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                $final_name = 'exp_' . $seccion . '_' . time() . '.' . $ext;
                $destination = $upload_dir . $final_name;

                if (move_uploaded_file($file_tmp, $destination)) {
                    // Borrar imagen anterior si existe y es un upload (no original)
                    if ($imagen_actual && strpos($imagen_actual, 'uploads/') !== false && file_exists('../' . $imagen_actual)) {
                        unlink('../' . $imagen_actual);
                    }
                    $nueva_imagen = 'assets/img/experiencia/uploads/' . $final_name;
                }
            }
        }

        // Construir tags JSON
        $tags_raw = $_POST['tags'] ?? '';
        $tags_array = array_filter(array_map('trim', explode(',', $tags_raw)));
        $tags_json = json_encode(array_values($tags_array));

        // Construir lista JSON
        $lista_iconos = $_POST['lista_iconos'] ?? [];
        $lista_textos = $_POST['lista_textos'] ?? [];
        $lista = [];
        for ($i = 0; $i < count($lista_textos); $i++) {
            if (!empty(trim($lista_textos[$i]))) {
                $lista[] = [
                    'icono' => $lista_iconos[$i] ?? 'fa-solid fa-check',
                    'texto' => trim($lista_textos[$i])
                ];
            }
        }
        $lista_json = json_encode($lista);

        $data = [
            'subtitulo' => $_POST['subtitulo'] ?? '',
            'titulo' => $_POST['titulo'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'tags' => $tags_json,
            'lista' => $lista_json,
            'imagen' => $nueva_imagen
        ];

        if ($experience->updateSection($seccion, $data)) {
            echo json_encode(['success' => true, 'message' => 'Sección actualizada correctamente', 'imagen' => $nueva_imagen]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la sección']);
        }
        break;

    case 'update_gallery':
        $posicion = intval($_POST['posicion'] ?? 0);
        if ($posicion < 1 || $posicion > 12) {
            echo json_encode(['success' => false, 'message' => 'Posición inválida']);
            exit;
        }

        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No se recibió imagen']);
            exit;
        }

        $file_tmp = $_FILES['imagen']['tmp_name'];
        $file_name = $_FILES['imagen']['name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Formato de imagen no permitido']);
            exit;
        }

        $upload_dir = '../assets/img/experiencia/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $final_name = 'gallery_' . $posicion . '_' . time() . '.' . $ext;
        $destination = $upload_dir . $final_name;

        if (move_uploaded_file($file_tmp, $destination)) {
            $nueva_ruta = 'assets/img/experiencia/uploads/' . $final_name;
            $alt = $_POST['alt_text'] ?? 'Experiencia ' . $posicion;
            
            $result = $experience->updateGalleryPhoto($posicion, $nueva_ruta, $alt);
            
            if ($result['success']) {
                // Borrar imagen anterior si era un upload
                if ($result['old_image'] && strpos($result['old_image'], 'uploads/') !== false && file_exists('../' . $result['old_image'])) {
                    unlink('../' . $result['old_image']);
                }
                echo json_encode(['success' => true, 'message' => 'Foto actualizada', 'imagen' => $nueva_ruta]);
            } else {
                // Si falla la DB, borrar el archivo recién subido
                if (file_exists($destination)) unlink($destination);
                echo json_encode(['success' => false, 'message' => 'Error al actualizar en base de datos']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al subir la imagen']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
}
?>
