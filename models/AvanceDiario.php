<?php

namespace Model;

use Model\ActiveRecord;

class AvanceDiario extends ActiveRecord {
    
    public static $tabla = 'avance_diario';
    public static $idTabla = 'ava_id';
    public static $columnasDB = 
    [
        'ava_apl_id',
        'ava_usu_id',
        'ava_fecha',
        'ava_porcentaje',
        'ava_resumen',
        'ava_bloqueadores',
        'ava_justificacion',
        'ava_creado_en',
        'ava_situacion'
    ];
    
    public $ava_id;
    public $ava_apl_id;
    public $ava_usu_id;
    public $ava_fecha;
    public $ava_porcentaje;
    public $ava_resumen;
    public $ava_bloqueadores;
    public $ava_justificacion;
    public $ava_creado_en;
    public $ava_situacion;
    
    public function __construct($avance = [])
    {
        $this->ava_id = $avance['ava_id'] ?? null;
        $this->ava_apl_id = $avance['ava_apl_id'] ?? 0;
        $this->ava_usu_id = $avance['ava_usu_id'] ?? 0;
        $this->ava_fecha = $avance['ava_fecha'] ?? '';
        $this->ava_porcentaje = $avance['ava_porcentaje'] ?? 0;
        $this->ava_resumen = $avance['ava_resumen'] ?? '';
        $this->ava_bloqueadores = $avance['ava_bloqueadores'] ?? '';
        $this->ava_justificacion = $avance['ava_justificacion'] ?? '';
        $this->ava_creado_en = $avance['ava_creado_en'] ?? '';
        $this->ava_situacion = $avance['ava_situacion'] ?? 1;
    }

    public static function EliminarAvance($id){
        $sql = "UPDATE avance_diario SET ava_situacion = 0 WHERE ava_id = $id";
        return self::SQL($sql);
    }

    // Obtener último porcentaje de una aplicación
    public static function obtenerUltimoPorcentaje($apl_id, $usuario_id = null) {
        $where_usuario = $usuario_id ? "AND ava_usu_id = $usuario_id" : "";
        
        $sql = "SELECT ava_porcentaje 
                FROM avance_diario 
                WHERE ava_apl_id = $apl_id 
                $where_usuario
                AND ava_situacion = 1 
                ORDER BY ava_fecha DESC, ava_id DESC
                LIMIT 1";
        
        $resultado = self::fetchFirst($sql);
        return $resultado ? $resultado['ava_porcentaje'] : 0;
    }

    // Verificar si ya existe reporte hoy - CORREGIDO PARA INFORMIX
    public static function existeReporteHoy($apl_id, $usuario_id, $fecha = null) {
        if (!$fecha) {
            $fecha = date('Y-m-d');
        }
        
        $sql = "SELECT COUNT(*) as total 
                FROM avance_diario 
                WHERE ava_apl_id = $apl_id 
                AND ava_usu_id = $usuario_id 
                AND ava_fecha = '$fecha'
                AND ava_situacion = 1";
        
        $resultado = self::fetchFirst($sql);
        return $resultado['total'] > 0;
    }

    // Obtener reporte de hoy si existe
    public static function obtenerReporteHoy($apl_id, $usuario_id, $fecha = null) {
        if (!$fecha) {
            $fecha = date('Y-m-d');
        }
        
        $sql = "SELECT * 
                FROM avance_diario 
                WHERE ava_apl_id = $apl_id 
                AND ava_usu_id = $usuario_id 
                AND ava_fecha = '$fecha'
                AND ava_situacion = 1";
        
        return self::fetchFirst($sql);
    }

    // Validar que el porcentaje no baje (regla de monotonía)
    public static function validarMonotonia($apl_id, $usuario_id, $nuevo_porcentaje, $fecha = null) {
        if (!$fecha) {
            $fecha = date('Y-m-d');
        }
        
        // Obtener el último porcentaje anterior a la fecha actual
        $sql = "SELECT ava_porcentaje 
                FROM avance_diario 
                WHERE ava_apl_id = $apl_id 
                AND ava_usu_id = $usuario_id 
                AND ava_fecha < '$fecha'
                AND ava_situacion = 1 
                ORDER BY ava_fecha DESC, ava_id DESC
                LIMIT 1";
        
        $ultimo = self::fetchFirst($sql);
        $porcentaje_anterior = $ultimo ? $ultimo['ava_porcentaje'] : 0;
        
        return [
            'puede_bajar' => $nuevo_porcentaje < $porcentaje_anterior,
            'porcentaje_anterior' => $porcentaje_anterior,
            'diferencia' => $nuevo_porcentaje - $porcentaje_anterior
        ];
    }

    // Obtener historial de avances de una aplicación
    public static function obtenerHistorial($apl_id, $usuario_id = null, $limite = 30) {
        $where_usuario = $usuario_id ? "AND av.ava_usu_id = $usuario_id" : "";
        
        $sql = "SELECT av.*, u.usu_nombre, u.usu_grado, ap.apl_nombre
                FROM avance_diario av
                INNER JOIN usuario u ON av.ava_usu_id = u.usu_id
                INNER JOIN aplicacion ap ON av.ava_apl_id = ap.apl_id
                WHERE av.ava_apl_id = $apl_id 
                $where_usuario
                AND av.ava_situacion = 1 
                ORDER BY av.ava_fecha DESC, av.ava_id DESC
                LIMIT $limite";
        
        return self::fetchArray($sql);
    }

    // Obtener estadísticas de avance
    public static function obtenerEstadisticas($apl_id, $usuario_id = null) {
        $where_usuario = $usuario_id ? "AND ava_usu_id = $usuario_id" : "";
        
        $sql = "SELECT 
                    COUNT(*) as total_reportes,
                    AVG(ava_porcentaje) as porcentaje_promedio,
                    MAX(ava_porcentaje) as porcentaje_maximo,
                    MIN(ava_fecha) as primera_fecha,
                    MAX(ava_fecha) as ultima_fecha,
                    SUM(CASE WHEN ava_bloqueadores IS NOT NULL AND LENGTH(TRIM(ava_bloqueadores)) > 0 THEN 1 ELSE 0 END) as reportes_con_bloqueadores,
                    SUM(CASE WHEN ava_justificacion IS NOT NULL AND LENGTH(TRIM(ava_justificacion)) > 0 THEN 1 ELSE 0 END) as reportes_con_justificacion
                FROM avance_diario 
                WHERE ava_apl_id = $apl_id 
                $where_usuario
                AND ava_situacion = 1";
        
        return self::fetchFirst($sql);
    }

    // Obtener avances por rango de fechas
    public static function obtenerPorRango($apl_id, $fecha_inicio, $fecha_fin, $usuario_id = null) {
        $where_usuario = $usuario_id ? "AND av.ava_usu_id = $usuario_id" : "";
        
        $sql = "SELECT av.*, u.usu_nombre, u.usu_grado
                FROM avance_diario av
                INNER JOIN usuario u ON av.ava_usu_id = u.usu_id
                WHERE av.ava_apl_id = $apl_id 
                AND av.ava_fecha >= '$fecha_inicio'
                AND av.ava_fecha <= '$fecha_fin'
                $where_usuario
                AND av.ava_situacion = 1 
                ORDER BY av.ava_fecha ASC, av.ava_id ASC";
        
        return self::fetchArray($sql);
    }

    // Verificar si es día hábil
    public static function esDiaHabil($fecha = null) {
        if (!$fecha) {
            $fecha = date('Y-m-d');
        }
        $timestamp = strtotime($fecha);
        $dia_semana = date('N', $timestamp); // 1=Lunes, 7=Domingo
        
        return ($dia_semana >= 1 && $dia_semana <= 5); // Lunes a Viernes
    }

    // Verificar si es hoy
    public static function esHoy($fecha) {
        return date('Y-m-d') === $fecha;
    }

    // Obtener días sin reporte en rango
    public static function obtenerDiasSinReporte($apl_id, $usuario_id, $fecha_inicio, $fecha_fin) {
        $sql = "SELECT ava_fecha 
                FROM avance_diario av
                WHERE av.ava_apl_id = $apl_id 
                AND av.ava_usu_id = $usuario_id 
                AND av.ava_fecha >= '$fecha_inicio'
                AND av.ava_fecha <= '$fecha_fin'
                AND av.ava_situacion = 1";
        
        $reportes_existentes = self::fetchArray($sql);
        $fechas_con_reporte = array_column($reportes_existentes, 'ava_fecha');
        
        $dias_sin_reporte = [];
        $fecha_actual = strtotime($fecha_inicio);
        $fecha_limite = strtotime($fecha_fin);
        
        while ($fecha_actual <= $fecha_limite) {
            $fecha_str = date('Y-m-d', $fecha_actual);
            
            // Solo considerar días hábiles
            if (self::esDiaHabil($fecha_str) && !in_array($fecha_str, $fechas_con_reporte)) {
                $dias_sin_reporte[] = $fecha_str;
            }
            
            $fecha_actual = strtotime('+1 day', $fecha_actual);
        }
        
        return $dias_sin_reporte;
    }

}