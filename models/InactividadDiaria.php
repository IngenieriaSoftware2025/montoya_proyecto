<?php

namespace Model;

use Model\ActiveRecord;

class InactividadDiaria extends ActiveRecord {
    
    public static $tabla = 'inactividad_diaria';
    public static $idTabla = 'ina_id';
    public static $columnasDB = 
    [
        'ina_apl_id',
        'ina_usu_id',
        'ina_fecha',
        'ina_motivo',
        'ina_tipo',
        'ina_creado_en',
        'ina_situacion'
    ];
    
    public $ina_id;
    public $ina_apl_id;
    public $ina_usu_id;
    public $ina_fecha;
    public $ina_motivo;
    public $ina_tipo;
    public $ina_creado_en;
    public $ina_situacion;
    
    public function __construct($inactividad = [])
    {
        $this->ina_id = $inactividad['ina_id'] ?? null;
        $this->ina_apl_id = $inactividad['ina_apl_id'] ?? 0;
        $this->ina_usu_id = $inactividad['ina_usu_id'] ?? 0;
        $this->ina_fecha = $inactividad['ina_fecha'] ?? '';
        $this->ina_motivo = $inactividad['ina_motivo'] ?? '';
        $this->ina_tipo = $inactividad['ina_tipo'] ?? '';
        $this->ina_creado_en = $inactividad['ina_creado_en'] ?? '';
        $this->ina_situacion = $inactividad['ina_situacion'] ?? 1;
    }

    // OVERRIDE del método crear para manejar fechas de Informix correctamente
    public function crear() {
        try {
            // Construir la consulta manualmente para Informix
            $sql = "INSERT INTO " . static::$tabla . " (
                        ina_apl_id, ina_usu_id, ina_fecha, ina_motivo, 
                        ina_tipo, ina_creado_en, ina_situacion
                    ) VALUES (
                        " . (int)$this->ina_apl_id . ",
                        " . (int)$this->ina_usu_id . ",
                        '" . $this->ina_fecha . "',
                        " . self::$db->quote($this->ina_motivo) . ",
                        " . self::$db->quote($this->ina_tipo) . ",
                        CURRENT,
                        " . (int)$this->ina_situacion . "
                    )";
            
            $resultado = self::$db->exec($sql);
            
            return [
                'resultado' => $resultado,
                'id' => self::$db->lastInsertId()
            ];
            
        } catch (\Exception $e) {
            error_log("Error al crear inactividad: " . $e->getMessage());
            return [
                'resultado' => 0,
                'id' => null
            ];
        }
    }

    public static function EliminarInactividad($id){
        $sql = "UPDATE inactividad_diaria SET ina_situacion = 0 WHERE ina_id = $id";
        return self::SQL($sql);
    }

    // Verificar si ya existe justificación para hoy
    public static function existeJustificacionHoy($apl_id, $usuario_id, $fecha = null) {
        $fecha = $fecha ?: date('Y-m-d');
        
        $sql = "SELECT COUNT(*) as total 
                FROM inactividad_diaria 
                WHERE ina_apl_id = $apl_id 
                AND ina_usu_id = $usuario_id 
                AND ina_fecha = '$fecha' 
                AND ina_situacion = 1";
        
        $resultado = self::fetchFirst($sql);
        return $resultado['total'] > 0;
    }

    // Obtener justificación de hoy si existe
    public static function obtenerJustificacionHoy($apl_id, $usuario_id, $fecha = null) {
        $fecha = $fecha ?: date('Y-m-d');
        
        $sql = "SELECT * 
                FROM inactividad_diaria 
                WHERE ina_apl_id = $apl_id 
                AND ina_usu_id = $usuario_id 
                AND ina_fecha = '$fecha' 
                AND ina_situacion = 1";
        
        return self::fetchFirst($sql);
    }

    // Obtener tipos de inactividad disponibles
    public static function obtenerTiposInactividad() {
        return [
            'LICENCIA' => 'Licencia médica o personal',
            'FALLA_TECNICA' => 'Falla técnica o de infraestructura',
            'BLOQUEADOR_EXTERNO' => 'Bloqueador externo al desarrollador',
            'VISITA' => 'Visita o inspección',
            'ESPERA_APROBACION' => 'Esperando aprobación o revisión',
            'CAPACITACION' => 'Capacitación o entrenamiento',
            'REUNION' => 'Reuniones de trabajo',
            'MANTENIMIENTO' => 'Mantenimiento de sistemas',
            'OTROS' => 'Otros motivos justificados'
        ];
    }

    // Obtener historial de inactividades de una aplicación
    public static function obtenerHistorial($apl_id, $usuario_id = null, $limite = 30) {
        $where_usuario = $usuario_id ? "AND i.ina_usu_id = $usuario_id" : "";
        
        $sql = "SELECT i.*, u.usu_nombre, u.usu_grado, ap.apl_nombre
                FROM inactividad_diaria i
                INNER JOIN usuario u ON i.ina_usu_id = u.usu_id
                INNER JOIN aplicacion ap ON i.ina_apl_id = ap.apl_id
                WHERE i.ina_apl_id = $apl_id 
                $where_usuario
                AND i.ina_situacion = 1 
                ORDER BY i.ina_fecha DESC, i.ina_id DESC
                LIMIT $limite";
        
        return self::fetchArray($sql);
    }

    // Obtener inactividades por rango de fechas
    public static function obtenerPorRango($apl_id, $fecha_inicio, $fecha_fin, $usuario_id = null) {
        $where_usuario = $usuario_id ? "AND i.ina_usu_id = $usuario_id" : "";
        
        $sql = "SELECT i.*, u.usu_nombre, u.usu_grado
                FROM inactividad_diaria i
                INNER JOIN usuario u ON i.ina_usu_id = u.usu_id
                WHERE i.ina_apl_id = $apl_id 
                AND i.ina_fecha >= '$fecha_inicio' 
                AND i.ina_fecha <= '$fecha_fin' 
                $where_usuario
                AND i.ina_situacion = 1 
                ORDER BY i.ina_fecha ASC, i.ina_id ASC";
        
        return self::fetchArray($sql);
    }

    // Obtener estadísticas de inactividad
    public static function obtenerEstadisticas($apl_id, $usuario_id = null) {
        $where_usuario = $usuario_id ? "AND ina_usu_id = $usuario_id" : "";
        
        $sql = "SELECT 
                    COUNT(*) as total_inactividades,
                    COUNT(CASE WHEN ina_tipo = 'LICENCIA' THEN 1 END) as licencias,
                    COUNT(CASE WHEN ina_tipo = 'FALLA_TECNICA' THEN 1 END) as fallas_tecnicas,
                    COUNT(CASE WHEN ina_tipo = 'BLOQUEADOR_EXTERNO' THEN 1 END) as bloqueadores_externos,
                    COUNT(CASE WHEN ina_tipo = 'ESPERA_APROBACION' THEN 1 END) as esperas_aprobacion,
                    COUNT(CASE WHEN ina_tipo = 'VISITA' THEN 1 END) as visitas,
                    COUNT(CASE WHEN ina_tipo = 'CAPACITACION' THEN 1 END) as capacitaciones,
                    COUNT(CASE WHEN ina_tipo = 'REUNION' THEN 1 END) as reuniones,
                    COUNT(CASE WHEN ina_tipo = 'MANTENIMIENTO' THEN 1 END) as mantenimientos,
                    COUNT(CASE WHEN ina_tipo = 'OTROS' THEN 1 END) as otros,
                    MIN(ina_fecha) as primera_fecha,
                    MAX(ina_fecha) as ultima_fecha
                FROM inactividad_diaria 
                WHERE ina_apl_id = $apl_id 
                $where_usuario
                AND ina_situacion = 1";
        
        return self::fetchFirst($sql);
    }

    // Obtener resumen por tipo de inactividad
    public static function obtenerResumenPorTipo($apl_id, $usuario_id = null, $fecha_inicio = null, $fecha_fin = null) {
        $where_usuario = $usuario_id ? "AND ina_usu_id = $usuario_id" : "";
        $where_fechas = "";
        
        if ($fecha_inicio && $fecha_fin) {
            $where_fechas = "AND ina_fecha >= '$fecha_inicio' AND ina_fecha <= '$fecha_fin'";
        }
        
        $sql = "SELECT 
                    ina_tipo as tipo,
                    COUNT(*) as cantidad
                FROM inactividad_diaria 
                WHERE ina_apl_id = $apl_id 
                $where_usuario
                $where_fechas
                AND ina_situacion = 1
                GROUP BY ina_tipo
                ORDER BY cantidad DESC";
        
        return self::fetchArray($sql);
    }

    // Verificar si es día hábil
    public static function esDiaHabil($fecha = null) {
        $fecha = $fecha ?: date('Y-m-d');
        $timestamp = strtotime($fecha);
        $dia_semana = date('N', $timestamp); // 1=Lunes, 7=Domingo
        
        return ($dia_semana >= 1 && $dia_semana <= 5); // Lunes a Viernes
    }

    // Verificar si es hoy
    public static function esHoy($fecha) {
        return date('Y-m-d') === $fecha;
    }

    // Obtener inactividades del mes actual - SIMPLIFICADO PARA INFORMIX
    public static function obtenerDelMes($usuario_id, $mes = null) {
        if (!$mes) {
            $mes = date('Y-m');
        }
        
        // Crear fechas para el rango del mes
        $anio = (int)substr($mes, 0, 4);
        $mes_num = (int)substr($mes, 5, 2);
        $fecha_inicio = sprintf('%04d-%02d-01', $anio, $mes_num);
        $ultimo_dia = date('t', strtotime($fecha_inicio));
        $fecha_fin = sprintf('%04d-%02d-%02d', $anio, $mes_num, $ultimo_dia);
        
        $sql = "SELECT i.*, ap.apl_nombre
                FROM inactividad_diaria i
                INNER JOIN aplicacion ap ON i.ina_apl_id = ap.apl_id
                WHERE i.ina_usu_id = $usuario_id
                AND i.ina_situacion = 1
                AND i.ina_fecha >= '$fecha_inicio'
                AND i.ina_fecha <= '$fecha_fin'
                ORDER BY i.ina_fecha DESC";
        
        return self::fetchArray($sql);
    }

    // Validar que no exista reporte de avance el mismo día
    public static function validarNoConflictoConAvance($apl_id, $usuario_id, $fecha) {
        $sql = "SELECT COUNT(*) as total 
                FROM avance_diario 
                WHERE ava_apl_id = $apl_id 
                AND ava_usu_id = $usuario_id 
                AND ava_fecha = '$fecha' 
                AND ava_situacion = 1";
        
        $resultado = self::fetchFirst($sql);
        return $resultado['total'] == 0; // Retorna true si NO hay conflicto
    }

    // Obtener días consecutivos de inactividad
    public static function obtenerDiasConsecutivos($apl_id, $usuario_id, $fecha_limite = null) {
        $fecha_limite = $fecha_limite ?: date('Y-m-d');
        
        $sql = "SELECT ina_fecha, ina_tipo, ina_motivo
                FROM inactividad_diaria
                WHERE ina_apl_id = $apl_id 
                AND ina_usu_id = $usuario_id 
                AND ina_fecha <= '$fecha_limite'
                AND ina_situacion = 1
                ORDER BY ina_fecha DESC";
        
        $inactividades = self::fetchArray($sql);
        
        $dias_consecutivos = 0;
        $fecha_actual = strtotime($fecha_limite);
        
        foreach ($inactividades as $inactividad) {
            // Verificar si es el día esperado (considerando solo días hábiles)
            if (date('Y-m-d', $fecha_actual) === $inactividad['ina_fecha']) {
                $dias_consecutivos++;
                
                // Ir al día hábil anterior
                do {
                    $fecha_actual = strtotime('-1 day', $fecha_actual);
                } while (!self::esDiaHabil(date('Y-m-d', $fecha_actual)) && $fecha_actual > strtotime('-30 days'));
            } else {
                break;
            }
        }
        
        return $dias_consecutivos;
    }

}