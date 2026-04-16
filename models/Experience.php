<?php
class Experience {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener una sección por tipo (transporte, tours)
     */
    public function getSection($seccion) {
        $stmt = $this->conn->prepare("SELECT * FROM experiencias WHERE seccion = ?");
        $stmt->execute([$seccion]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todas las secciones ordenadas
     */
    public function getAllSections() {
        $stmt = $this->conn->query("SELECT * FROM experiencias ORDER BY orden ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar una sección de experiencia
     */
    public function updateSection($seccion, $data) {
        $stmt = $this->conn->prepare(
            "UPDATE experiencias SET subtitulo = ?, titulo = ?, descripcion = ?, tags = ?, lista = ?, imagen = ? WHERE seccion = ?"
        );
        return $stmt->execute([
            $data['subtitulo'],
            $data['titulo'],
            $data['descripcion'],
            $data['tags'],
            $data['lista'],
            $data['imagen'],
            $seccion
        ]);
    }

    /**
     * Obtener todas las fotos de la galería ordenadas por posición
     */
    public function getGalleryPhotos() {
        $stmt = $this->conn->query("SELECT * FROM experiencias_galeria ORDER BY posicion ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar una foto de la galería
     */
    public function updateGalleryPhoto($posicion, $imagen, $alt_text = 'Experiencia') {
        // Primero obtener la imagen anterior
        $stmt = $this->conn->prepare("SELECT imagen FROM experiencias_galeria WHERE posicion = ?");
        $stmt->execute([$posicion]);
        $old = $stmt->fetch(PDO::FETCH_ASSOC);
        $old_image = $old ? $old['imagen'] : null;

        // Actualizar o insertar
        $stmt = $this->conn->prepare(
            "INSERT INTO experiencias_galeria (posicion, imagen, alt_text) VALUES (?, ?, ?) 
             ON DUPLICATE KEY UPDATE imagen = VALUES(imagen), alt_text = VALUES(alt_text)"
        );
        $result = $stmt->execute([$posicion, $imagen, $alt_text]);

        return ['success' => $result, 'old_image' => $old_image];
    }

    /**
     * Obtener la imagen actual de una sección
     */
    public function getSectionImage($seccion) {
        $stmt = $this->conn->prepare("SELECT imagen FROM experiencias WHERE seccion = ?");
        $stmt->execute([$seccion]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['imagen'] : null;
    }
}
?>
