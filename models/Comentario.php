<?php

namespace Model;

use Model\ActiveRecord;

class Comentario extends ActiveRecord {
    
    public static $tabla = 'comentario';
    public static $idTabla = 'com_id';
    public static $columnasDB = 
    [
        'com_apl_id',
        'com_autor_id',
        'com_texto',
        'com_creado_en',
        'com_situacion'
    ];
    
    public $com_id;
    public $com_apl_id;
    public $com_autor_id;
    public $com_texto;
    public $com_creado_en;
    public $com_situacion;
    
    public function __construct($comentario = [])
    {
        $this->com_id = $comentario['com_id'] ?? null;
        $this->com_apl_id = $comentario['com_apl_id'] ?? 0;
        $this->com_autor_id = $comentario['com_autor_id'] ?? 0;
        $this->com_texto = $comentario['com_texto'] ?? '';
        $this->com_creado_en = $comentario['com_creado_en'] ?? '';
        $this->com_situacion = $comentario['com_situacion'] ?? 1;
    }

    // Override del método crear para manejar fechas de Informix correctamente
    public function crear() {
        try {
            // Construir la consulta manualmente para Informix
            $sql = "INSERT INTO " . static::$tabla . " (
                        com_apl_id, com_autor_id, com_texto, 
                        com_creado_en, com_situacion
                    ) VALUES (
                        " . (int)$this->com_apl_id . ",
                        " . (int)$this->com_autor_id . ",
                        " . self::$db->quote($this->com_texto) . ",
                        CURRENT,
                        " . (int)$this->com_situacion . "
                    )";
            
            $resultado = self::$db->exec($sql);
            
            return [
                'resultado' => $resultado,
                'id' => self::$db->lastInsertId()
            ];
            
        } catch (\Exception $e) {
            error_log("Error al crear comentario: " . $e->getMessage());
            return [
                'resultado' => 0,
                'id' => null
            ];
        }
    }

    public static function EliminarComentario($id){
        $sql = "UPDATE comentario SET com_situacion = 0 WHERE com_id = $id";
        return self::SQL($sql);
    }

    // Obtener comentarios de una aplicación con información del autor
    public static function obtenerPorAplicacion($apl_id, $usuario_id = null, $limite = 50) {
        $join_leidos = "";
        $select_leido = "";
        
        if ($usuario_id) {
            $join_leidos = "LEFT JOIN comentario_leido cl ON c.com_id = cl.col_com_id AND cl.col_usu_id = $usuario_id AND cl.col_situacion = 1";
            $select_leido = ", CASE WHEN cl.col_usu_id IS NOT NULL THEN 't' ELSE 'f' END as leido";
        }
        
        $sql = "SELECT c.com_id, c.com_apl_id, c.com_autor_id, c.com_texto, c.com_creado_en,
                       u.usu_nombre, u.usu_grado, u.usu_email,
                       a.apl_nombre$select_leido
                FROM comentario c
                INNER JOIN usuario u ON c.com_autor_id = u.usu_id
                INNER JOIN aplicacion a ON c.com_apl_id = a.apl_id
                $join_leidos
                WHERE c.com_apl_id = $apl_id 
                AND c.com_situacion = 1 
                ORDER BY c.com_creado_en DESC
                FIRST $limite";
        
        return self::fetchArray($sql);
    }

    // Obtener comentarios no leídos por un usuario específico
    public static function obtenerNoLeidosPorUsuario($usuario_id, $apl_id = null) {
        $where_app = $apl_id ? "AND c.com_apl_id = $apl_id" : "";
        
        $sql = "SELECT c.com_id, c.com_apl_id, c.com_autor_id, c.com_texto, c.com_creado_en,
                       u.usu_nombre, u.usu_grado, a.apl_nombre
                FROM comentario c
                INNER JOIN usuario u ON c.com_autor_id = u.usu_id
                INNER JOIN aplicacion a ON c.com_apl_id = a.apl_id
                LEFT JOIN comentario_leido cl ON c.com_id = cl.col_com_id AND cl.col_usu_id = $usuario_id AND cl.col_situacion = 1
                WHERE c.com_situacion = 1 
                AND cl.col_usu_id IS NULL
                $where_app
                ORDER BY c.com_creado_en DESC";
        
        return self::fetchArray($sql);
    }

    // Contar comentarios no leídos por usuario y aplicación
    public static function contarNoLeidos($usuario_id, $apl_id = null) {
        $where_app = $apl_id ? "AND c.com_apl_id = $apl_id" : "";
        
        $sql = "SELECT COUNT(*) as total
                FROM comentario c
                LEFT JOIN comentario_leido cl ON c.com_id = cl.col_com_id AND cl.col_usu_id = $usuario_id AND cl.col_situacion = 1
                WHERE c.com_situacion = 1 
                AND cl.col_usu_id IS NULL
                $where_app";
        
        $resultado = self::fetchFirst($sql);
        return $resultado ? $resultado['total'] : 0;
    }

    // Marcar comentario como leído
    public static function marcarComoLeido($comentario_id, $usuario_id) {
        try {
            // Verificar si ya está marcado como leído
            $sql_existe = "SELECT col_id FROM comentario_leido 
                          WHERE col_com_id = $comentario_id 
                          AND col_usu_id = $usuario_id 
                          AND col_situacion = 1";
            
            $ya_leido = self::fetchFirst($sql_existe);
            
            if ($ya_leido) {
                return true; // Ya estaba marcado como leído
            }
            
            // Insertar nuevo registro de lectura
            $sql = "INSERT INTO comentario_leido (
                        col_com_id, col_usu_id, col_leido_en, col_situacion
                    ) VALUES (
                        $comentario_id, $usuario_id, CURRENT, 1
                    )";
            
            return self::$db->exec($sql);
            
        } catch (\Exception $e) {
            error_log("Error al marcar como leído: " . $e->getMessage());
            return false;
        }
    }

    // Obtener estadísticas de comentarios
    public static function obtenerEstadisticas($apl_id = null, $fecha_inicio = null, $fecha_fin = null) {
        $where_conditions = ["com_situacion = 1"];
        
        if ($apl_id) {
            $where_conditions[] = "com_apl_id = $apl_id";
        }
        
        if ($fecha_inicio && $fecha_fin) {
            $where_conditions[] = "DATE(com_creado_en) >= '$fecha_inicio'";
            $where_conditions[] = "DATE(com_creado_en) <= '$fecha_fin'";
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        
        $sql = "SELECT 
                    COUNT(*) as total_comentarios,
                    COUNT(DISTINCT com_autor_id) as usuarios_participantes,
                    COUNT(DISTINCT com_apl_id) as aplicaciones_con_comentarios,
                    MIN(com_creado_en) as primer_comentario,
                    MAX(com_creado_en) as ultimo_comentario,
                    AVG(LENGTH(com_texto)) as longitud_promedio
                FROM comentario 
                $where_clause";
        
        return self::fetchFirst($sql);
    }

    // Obtener comentarios por usuario
    public static function obtenerPorUsuario($usuario_id, $limite = 50) {
        $sql = "SELECT c.com_id, c.com_apl_id, c.com_texto, c.com_creado_en,
                       a.apl_nombre, a.apl_estado
                FROM comentario c
                INNER JOIN aplicacion a ON c.com_apl_id = a.apl_id
                WHERE c.com_autor_id = $usuario_id 
                AND c.com_situacion = 1 
                ORDER BY c.com_creado_en DESC
                FIRST $limite";
        
        return self::fetchArray($sql);
    }

    // Obtener actividad de comentarios por día
    public static function obtenerActividadDiaria($apl_id = null, $dias = 30) {
        $where_app = $apl_id ? "AND com_apl_id = $apl_id" : "";
        
        $sql = "SELECT 
                    DATE(com_creado_en) as fecha,
                    COUNT(*) as total_comentarios,
                    COUNT(DISTINCT com_autor_id) as usuarios_activos
                FROM comentario 
                WHERE com_situacion = 1 
                AND com_creado_en >= CURRENT - $dias UNITS DAY
                $where_app
                GROUP BY DATE(com_creado_en)
                ORDER BY fecha DESC";
        
        return self::fetchArray($sql);
    }

    // Obtener comentarios más recientes del sistema
    public static function obtenerRecientes($limite = 10, $usuario_id = null) {
        $where_usuario = $usuario_id ? "AND c.com_autor_id = $usuario_id" : "";
        
        $sql = "SELECT c.com_id, c.com_apl_id, c.com_texto, c.com_creado_en,
                       u.usu_nombre, u.usu_grado,
                       a.apl_nombre
                FROM comentario c
                INNER JOIN usuario u ON c.com_autor_id = u.usu_id
                INNER JOIN aplicacion a ON c.com_apl_id = a.apl_id
                WHERE c.com_situacion = 1 
                $where_usuario
                ORDER BY c.com_creado_en DESC
                FIRST $limite";
        
        return self::fetchArray($sql);
    }

    // Buscar comentarios por texto
    public static function buscarPorTexto($texto, $apl_id = null, $limite = 50) {
        $where_app = $apl_id ? "AND c.com_apl_id = $apl_id" : "";
        $texto_busqueda = strtolower($texto);
        
        $sql = "SELECT c.com_id, c.com_apl_id, c.com_texto, c.com_creado_en,
                       u.usu_nombre, u.usu_grado,
                       a.apl_nombre
                FROM comentario c
                INNER JOIN usuario u ON c.com_autor_id = u.usu_id
                INNER JOIN aplicacion a ON c.com_apl_id = a.apl_id
                WHERE c.com_situacion = 1 
                AND LOWER(c.com_texto) LIKE '%$texto_busqueda%'
                $where_app
                ORDER BY c.com_creado_en DESC
                FIRST $limite";
        
        return self::fetchArray($sql);
    }

    // Obtener usuarios más activos en comentarios
    public static function obtenerUsuariosMasActivos($apl_id = null, $limite = 10) {
        $where_app = $apl_id ? "AND c.com_apl_id = $apl_id" : "";
        
        $sql = "SELECT u.usu_id, u.usu_nombre, u.usu_grado,
                       COUNT(*) as total_comentarios,
                       MAX(c.com_creado_en) as ultimo_comentario
                FROM comentario c
                INNER JOIN usuario u ON c.com_autor_id = u.usu_id
                WHERE c.com_situacion = 1 
                $where_app
                GROUP BY u.usu_id, u.usu_nombre, u.usu_grado
                ORDER BY total_comentarios DESC, ultimo_comentario DESC
                FIRST $limite";
        
        return self::fetchArray($sql);
    }

    // Verificar si un usuario puede eliminar un comentario
    public static function puedeEliminar($comentario_id, $usuario_id) {
        $sql = "SELECT com_autor_id, com_creado_en 
                FROM comentario 
                WHERE com_id = $comentario_id AND com_situacion = 1";
        
        $comentario = self::fetchFirst($sql);
        
        if (!$comentario) {
            return false;
        }
        
        // Solo el autor puede eliminar
        if ($comentario['com_autor_id'] != $usuario_id) {
            return false;
        }
        
        // Solo se puede eliminar dentro de las primeras 24 horas
        $ahora = time();
        $fecha_creacion = strtotime($comentario['com_creado_en']);
        $horas_transcurridas = ($ahora - $fecha_creacion) / 3600;
        
        return $horas_transcurridas <= 24;
    }

    // Obtener hilo de conversación (comentarios relacionados)
    public static function obtenerHilo($apl_id, $limite = 100) {
        return self::obtenerPorAplicacion($apl_id, null, $limite);
    }

    // Validar texto del comentario
    public static function validarTexto($texto) {
        $texto = trim($texto);
        
        if (empty($texto)) {
            return ['valido' => false, 'mensaje' => 'El comentario no puede estar vacío'];
        }
        
        if (strlen($texto) < 5) {
            return ['valido' => false, 'mensaje' => 'El comentario debe tener al menos 5 caracteres'];
        }
        
        if (strlen($texto) > 1200) {
            return ['valido' => false, 'mensaje' => 'El comentario no puede exceder 1200 caracteres'];
        }
        
        return ['valido' => true, 'mensaje' => 'Comentario válido'];
    }

    // Detectar menciones (@usuario) en el texto
    public static function detectarMenciones($texto) {
        $menciones = [];
        if (preg_match_all('/@([a-zA-Z0-9_\.]+)/', $texto, $matches)) {
            $menciones = array_unique($matches[1]);
        }
        return $menciones;
    }

    // Obtener resumen de actividad para un período
    public static function obtenerResumenActividad($apl_id, $fecha_inicio, $fecha_fin) {
        $sql = "SELECT 
                    COUNT(*) as total_comentarios,
                    COUNT(DISTINCT com_autor_id) as usuarios_participantes,
                    DATE(MIN(com_creado_en)) as primer_comentario,
                    DATE(MAX(com_creado_en)) as ultimo_comentario,
                    AVG(LENGTH(com_texto)) as longitud_promedio_texto
                FROM comentario 
                WHERE com_apl_id = $apl_id 
                AND com_situacion = 1
                AND DATE(com_creado_en) >= '$fecha_inicio'
                AND DATE(com_creado_en) <= '$fecha_fin'";
        
        return self::fetchFirst($sql);
    }
}