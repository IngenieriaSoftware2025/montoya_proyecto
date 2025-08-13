<?php

namespace Model;

use Model\ActiveRecord;

class Visita extends ActiveRecord {
    
    public static $tabla = 'visita';
    public static $idTabla = 'vis_id';
    public static $columnasDB = 
    [
        'vis_apl_id',
        'vis_fecha',
        'vis_quien',
        'vis_motivo',
        'vis_procedimiento',
        'vis_solucion',
        'vis_observacion',
        'vis_conformidad',
        'vis_creado_por',
        'vis_creado_en',
        'vis_situacion'
    ];
    
    public $vis_id;
    public $vis_apl_id;
    public $vis_fecha;
    public $vis_quien;
    public $vis_motivo;
    public $vis_procedimiento;
    public $vis_solucion;
    public $vis_observacion;
    public $vis_conformidad;
    public $vis_creado_por;
    public $vis_creado_en;
    public $vis_situacion;
    
    public function __construct($visita = [])
    {
        $this->vis_id = $visita['vis_id'] ?? null;
        $this->vis_apl_id = $visita['vis_apl_id'] ?? 0;
        $this->vis_fecha = $visita['vis_fecha'] ?? '';
        $this->vis_quien = $visita['vis_quien'] ?? '';
        $this->vis_motivo = $visita['vis_motivo'] ?? '';
        $this->vis_procedimiento = $visita['vis_procedimiento'] ?? '';
        $this->vis_solucion = $visita['vis_solucion'] ?? '';
        $this->vis_observacion = $visita['vis_observacion'] ?? '';
        $this->vis_conformidad = $visita['vis_conformidad'] ?? 'f';
        $this->vis_creado_por = $visita['vis_creado_por'] ?? 0;
        $this->vis_creado_en = $visita['vis_creado_en'] ?? '';
        $this->vis_situacion = $visita['vis_situacion'] ?? 1;
    }

    // Override del método crear para manejar fechas de Informix correctamente
    public function crear() {
        try {
            // Normalizar conformidad
            $conformidad = in_array($this->vis_conformidad, ['t', '1', 'true', 1]) ? 't' : 'f';
            
            // Construir la consulta manualmente para Informix
            $sql = "INSERT INTO " . static::$tabla . " (
                        vis_apl_id, vis_fecha, vis_quien, vis_motivo, 
                        vis_procedimiento, vis_solucion, vis_observacion, 
                        vis_conformidad, vis_creado_por, vis_creado_en, vis_situacion
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT, ?)";
            
            $stmt = self::$db->prepare($sql);
            $resultado = $stmt->execute([
                (int)$this->vis_apl_id,
                $this->vis_fecha,
                $this->vis_quien,
                $this->vis_motivo,
                $this->vis_procedimiento,
                $this->vis_solucion,
                $this->vis_observacion,
                $conformidad,
                (int)$this->vis_creado_por,
                (int)$this->vis_situacion
            ]);
            
            return [
                'resultado' => $resultado,
                'id' => self::$db->lastInsertId()
            ];
            
        } catch (\Exception $e) {
            error_log("Error al crear visita: " . $e->getMessage());
            return [
                'resultado' => 0,
                'id' => null
            ];
        }
    }

    public static function EliminarVisita($id){
        $sql = "UPDATE visita SET vis_situacion = 0 WHERE vis_id = ?";
        $stmt = self::$db->prepare($sql);
        return $stmt->execute([$id]);
    }

    // Obtener todas las visitas con filtros opcionales
    public static function obtenerTodas($limite = 50, $conformidad = null) {
        $where_conformidad = "";
        $params = [];
        
        if ($conformidad !== null) {
            $conformidad_valor = ($conformidad == 'true' || $conformidad == '1') ? 't' : 'f';
            $where_conformidad = "AND v.vis_conformidad = ?";
            $params[] = $conformidad_valor;
        }
        
        $sql = "SELECT v.*, a.apl_nombre, u.usu_nombre as creado_por_nombre, u.usu_grado
                FROM visita v
                INNER JOIN aplicacion a ON v.vis_apl_id = a.apl_id
                LEFT JOIN usuario u ON v.vis_creado_por = u.usu_id
                WHERE v.vis_situacion = 1 
                $where_conformidad
                ORDER BY v.vis_fecha DESC, v.vis_id DESC
                FIRST ?";
        
        $params[] = $limite;
        
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Obtener historial de visitas de una aplicación
    public static function obtenerHistorial($apl_id, $limite = 30, $conformidad = null) {
        $where_conformidad = "";
        $params = [$apl_id];
        
        if ($conformidad !== null) {
            $conformidad_valor = ($conformidad == 'true' || $conformidad == '1') ? 't' : 'f';
            $where_conformidad = "AND v.vis_conformidad = ?";
            $params[] = $conformidad_valor;
        }
        
        $sql = "SELECT v.*, a.apl_nombre, u.usu_nombre as creado_por_nombre, u.usu_grado
                FROM visita v
                INNER JOIN aplicacion a ON v.vis_apl_id = a.apl_id
                LEFT JOIN usuario u ON v.vis_creado_por = u.usu_id
                WHERE v.vis_apl_id = ? 
                AND v.vis_situacion = 1 
                $where_conformidad
                ORDER BY v.vis_fecha DESC, v.vis_id DESC
                FIRST ?";
        
        $params[] = $limite;
        
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Obtener visitas por rango de fechas
    public static function obtenerPorRango($apl_id, $fecha_inicio, $fecha_fin, $conformidad = null) {
        $where_conformidad = "";
        $params = [$apl_id, $fecha_inicio, $fecha_fin];
        
        if ($conformidad !== null) {
            $conformidad_valor = ($conformidad == 'true' || $conformidad == '1') ? 't' : 'f';
            $where_conformidad = "AND v.vis_conformidad = ?";
            $params[] = $conformidad_valor;
        }
        
        $sql = "SELECT v.*, a.apl_nombre, u.usu_nombre as creado_por_nombre, u.usu_grado
                FROM visita v
                INNER JOIN aplicacion a ON v.vis_apl_id = a.apl_id
                LEFT JOIN usuario u ON v.vis_creado_por = u.usu_id
                WHERE v.vis_apl_id = ? 
                AND DATE(v.vis_fecha) >= ?
                AND DATE(v.vis_fecha) <= ?
                AND v.vis_situacion = 1 
                $where_conformidad
                ORDER BY v.vis_fecha ASC, v.vis_id ASC";
        
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Obtener estadísticas de visitas
    public static function obtenerEstadisticas($apl_id = null, $fecha_inicio = null, $fecha_fin = null) {
        $where_conditions = ["vis_situacion = 1"];
        $params = [];
        
        if ($apl_id) {
            $where_conditions[] = "vis_apl_id = ?";
            $params[] = $apl_id;
        }
        
        if ($fecha_inicio && $fecha_fin) {
            $where_conditions[] = "DATE(vis_fecha) >= ?";
            $where_conditions[] = "DATE(vis_fecha) <= ?";
            $params[] = $fecha_inicio;
            $params[] = $fecha_fin;
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        
        $sql = "SELECT 
                    COUNT(*) as total_visitas,
                    COUNT(CASE WHEN vis_conformidad = 't' THEN 1 END) as visitas_conformes,
                    COUNT(CASE WHEN vis_conformidad = 'f' THEN 1 END) as visitas_no_conformes,
                    COUNT(CASE WHEN vis_solucion IS NOT NULL AND LENGTH(TRIM(vis_solucion)) > 0 THEN 1 END) as visitas_con_solucion,
                    MIN(DATE(vis_fecha)) as primera_fecha,
                    MAX(DATE(vis_fecha)) as ultima_fecha
                FROM visita 
                $where_clause";
        
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Obtener resumen de conformidad
    public static function obtenerResumenConformidad($apl_id = null, $fecha_inicio = null, $fecha_fin = null) {
        $where_conditions = ["vis_situacion = 1"];
        $params = [];
        
        if ($apl_id) {
            $where_conditions[] = "vis_apl_id = ?";
            $params[] = $apl_id;
        }
        
        if ($fecha_inicio && $fecha_fin) {
            $where_conditions[] = "DATE(vis_fecha) >= ?";
            $where_conditions[] = "DATE(vis_fecha) <= ?";
            $params[] = $fecha_inicio;
            $params[] = $fecha_fin;
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        
        $sql = "SELECT 
                    vis_conformidad as conformidad,
                    COUNT(*) as cantidad
                FROM visita 
                $where_clause
                GROUP BY vis_conformidad
                ORDER BY conformidad DESC";
        
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Obtener tendencia mensual de visitas
    public static function obtenerTendenciaMensual($apl_id = null) {
        $where_clause = "WHERE vis_situacion = 1 AND vis_fecha >= CURRENT - INTERVAL(6) MONTH TO MONTH";
        $params = [];
        
        if ($apl_id) {
            $where_clause .= " AND vis_apl_id = ?";
            $params[] = $apl_id;
        }
        
        $sql = "SELECT 
                    YEAR(vis_fecha) as anio,
                    MONTH(vis_fecha) as mes,
                    COUNT(*) as total_visitas,
                    COUNT(CASE WHEN vis_conformidad = 't' THEN 1 END) as conformes,
                    COUNT(CASE WHEN vis_conformidad = 'f' THEN 1 END) as no_conformes
                FROM visita 
                $where_clause
                GROUP BY YEAR(vis_fecha), MONTH(vis_fecha)
                ORDER BY anio DESC, mes DESC";
        
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Verificar si es hoy
    public static function esHoy($fecha) {
        $fecha_visita = date('Y-m-d', strtotime($fecha));
        return date('Y-m-d') === $fecha_visita;
    }

    // Obtener última visita de una aplicación
    public static function obtenerUltimaVisita($apl_id) {
        $sql = "SELECT * 
                FROM visita 
                WHERE vis_apl_id = ? 
                AND vis_situacion = 1 
                ORDER BY vis_fecha DESC, vis_id DESC 
                FIRST 1";
        
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$apl_id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // Verificar si hay visitas no conformes pendientes
    public static function obtenerVisitasNoConformesPendientes($apl_id = null) {
        $where_clause = "WHERE v.vis_conformidad = 'f' AND v.vis_situacion = 1";
        $params = [];
        
        if ($apl_id) {
            $where_clause .= " AND v.vis_apl_id = ?";
            $params[] = $apl_id;
        }
        
        $sql = "SELECT v.*, a.apl_nombre
                FROM visita v
                INNER JOIN aplicacion a ON v.vis_apl_id = a.apl_id
                $where_clause
                ORDER BY v.vis_fecha DESC";
        
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Obtener visitantes frecuentes
    public static function obtenerVisitantesFrecuentes($apl_id = null, $limite = 10) {
        $where_clause = "WHERE vis_situacion = 1";
        $params = [];
        
        if ($apl_id) {
            $where_clause .= " AND vis_apl_id = ?";
            $params[] = $apl_id;
        }
        
        $sql = "SELECT 
                    vis_quien as visitante,
                    COUNT(*) as total_visitas,
                    COUNT(CASE WHEN vis_conformidad = 't' THEN 1 END) as visitas_conformes,
                    MAX(vis_fecha) as ultima_visita
                FROM visita 
                $where_clause
                GROUP BY vis_quien
                HAVING COUNT(*) > 1
                ORDER BY total_visitas DESC, ultima_visita DESC
                FIRST ?";
        
        $params[] = $limite;
        
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Validar conformidad basada en observaciones
    public static function analizarConformidad($observacion) {
        if (empty($observacion)) return 't';
        
        $palabras_negativas = [
            'problema', 'error', 'falla', 'defecto', 'malo', 'incorrecto', 
            'inconforme', 'rechazar', 'no funciona', 'buggy', 'lento'
        ];
        
        $observacion_lower = strtolower($observacion);
        
        foreach ($palabras_negativas as $palabra) {
            if (strpos($observacion_lower, $palabra) !== false) {
                return 'f';
            }
        }
        
        return 't';
    }

    // Obtener tipos de motivos más comunes
    public static function obtenerMotivosFrecuentes($apl_id = null, $limite = 10) {
        $where_clause = "WHERE vis_situacion = 1 AND LENGTH(TRIM(vis_motivo)) > 0";
        $params = [];
        
        if ($apl_id) {
            $where_clause .= " AND vis_apl_id = ?";
            $params[] = $apl_id;
        }
        
        $sql = "SELECT 
                    vis_motivo as motivo,
                    COUNT(*) as frecuencia,
                    AVG(CASE WHEN vis_conformidad = 't' THEN 1.0 ELSE 0.0 END) as tasa_conformidad
                FROM visita 
                $where_clause
                GROUP BY vis_motivo
                ORDER BY frecuencia DESC
                FIRST ?";
        
        $params[] = $limite;
        
        $stmt = self::$db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}