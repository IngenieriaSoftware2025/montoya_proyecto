<?php

namespace Model;

use Model\ActiveRecord;

class Aplicacion extends ActiveRecord {
    
    public static $tabla = 'aplicacion';
    public static $idTabla = 'apl_id';
    public static $columnasDB = 
    [
        'apl_nombre',
        'apl_descripcion',
        'apl_fecha_inicio',
        'apl_fecha_fin',
        'apl_porcentaje_objetivo',
        'apl_estado',
        'apl_responsable',
        'apl_creado_en',
        'apl_situacion'
    ];
    
    public $apl_id;
    public $apl_nombre;
    public $apl_descripcion;
    public $apl_fecha_inicio;
    public $apl_fecha_fin;
    public $apl_porcentaje_objetivo;
    public $apl_estado;
    public $apl_responsable;
    public $apl_creado_en;
    public $apl_situacion;
    
    public function __construct($aplicacion = [])
    {
        $this->apl_id = $aplicacion['apl_id'] ?? null;
        $this->apl_nombre = $aplicacion['apl_nombre'] ?? '';
        $this->apl_descripcion = $aplicacion['apl_descripcion'] ?? '';
        $this->apl_fecha_inicio = $aplicacion['apl_fecha_inicio'] ?? '';
        $this->apl_fecha_fin = $aplicacion['apl_fecha_fin'] ?? null;
        $this->apl_porcentaje_objetivo = $aplicacion['apl_porcentaje_objetivo'] ?? 100;
        $this->apl_estado = $aplicacion['apl_estado'] ?? 'EN_PLANIFICACION';
        $this->apl_responsable = $aplicacion['apl_responsable'] ?? 0;
        $this->apl_creado_en = $aplicacion['apl_creado_en'] ?? '';
        $this->apl_situacion = $aplicacion['apl_situacion'] ?? 1;
    }

    public static function EliminarAplicacion($id){
        $sql = "UPDATE aplicacion SET apl_situacion = 0 WHERE apl_id = $id";
        return self::SQL($sql);
    }

    // Método para obtener aplicaciones por responsable
    public static function obtenerPorResponsable($usuario_id) {
        $sql = "SELECT a.*, u.usu_nombre as responsable_nombre, u.usu_grado
                FROM aplicacion a
                LEFT JOIN usuario u ON a.apl_responsable = u.usu_id
                WHERE a.apl_responsable = $usuario_id 
                AND a.apl_situacion = 1
                ORDER BY a.apl_estado, a.apl_fecha_inicio DESC";
        return self::fetchArray($sql);
    }

    // Método para obtener el último porcentaje de una aplicación
    public static function obtenerUltimoPorcentaje($apl_id) {
        $sql = "SELECT ava_porcentaje 
                FROM avance_diario 
                WHERE ava_apl_id = $apl_id 
                AND ava_situacion = 1 
                ORDER BY ava_fecha DESC, ava_id DESC
                LIMIT 1";
        $resultado = self::fetchFirst($sql);
        return $resultado ? $resultado['ava_porcentaje'] : 0;
    }

    // Método para verificar si hay reporte hoy - CORREGIDO PARA INFORMIX
    public static function tieneReporteHoy($apl_id, $usuario_id) {
        $hoy = date('Y-m-d');
        $sql = "SELECT COUNT(*) as total 
                FROM avance_diario 
                WHERE ava_apl_id = $apl_id 
                AND ava_usu_id = $usuario_id 
                AND ava_fecha = '$hoy'
                AND ava_situacion = 1";
        $resultado = self::fetchFirst($sql);
        return $resultado['total'] > 0;
    }

    // Método para verificar si hay inactividad hoy - CORREGIDO PARA INFORMIX
    public static function tieneInactividadHoy($apl_id, $usuario_id) {
        $hoy = date('Y-m-d');
        $sql = "SELECT COUNT(*) as total 
                FROM inactividad_diaria 
                WHERE ina_apl_id = $apl_id 
                AND ina_usu_id = $usuario_id 
                AND ina_fecha = '$hoy'
                AND ina_situacion = 1";
        $resultado = self::fetchFirst($sql);
        return $resultado['total'] > 0;
    }

}