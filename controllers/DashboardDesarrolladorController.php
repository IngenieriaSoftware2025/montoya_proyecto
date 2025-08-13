<?php

namespace Controllers;

use Exception;
use Model\ActiveRecord;
use Model\Aplicacion;
use MVC\Router;

class DashboardDesarrolladorController extends ActiveRecord
{

    public static function renderizarPagina(Router $router)
    {
        $router->render('desarrollador/index', []);
    }

    // API para obtener aplicaciones del desarrollador
    public static function buscarAplicacionesAPI()
    {
        try {
            $usuario_id = isset($_GET['usuario_id']) ? $_GET['usuario_id'] : 3;
            
            $sql = "SELECT a.*, u.usu_nombre as responsable_nombre, u.usu_grado
                    FROM aplicacion a
                    LEFT JOIN usuario u ON a.apl_responsable = u.usu_id
                    WHERE a.apl_responsable = $usuario_id 
                    AND a.apl_situacion = 1
                    ORDER BY a.apl_estado, a.apl_id DESC";
            
            $data = self::fetchArray($sql);

            foreach ($data as &$app) {
                // Obtener último porcentaje
                $sql_porcentaje = "SELECT ava_porcentaje 
                                  FROM avance_diario 
                                  WHERE ava_apl_id = {$app['apl_id']}
                                  AND ava_situacion = 1 
                                  ORDER BY ava_fecha DESC, ava_id DESC
                                  LIMIT 1";
                $ultimo_porcentaje = self::fetchFirst($sql_porcentaje);
                $app['ultimo_porcentaje'] = $ultimo_porcentaje ? $ultimo_porcentaje['ava_porcentaje'] : 0;
                
                // Buscar todos los reportes de esta app y usuario
                $sql_todos_reportes = "SELECT ava_fecha 
                                      FROM avance_diario 
                                      WHERE ava_apl_id = {$app['apl_id']}
                                      AND ava_usu_id = $usuario_id 
                                      AND ava_situacion = 1";
                $todos_reportes = self::fetchArray($sql_todos_reportes);
                
                // Verificar manualmente si alguna fecha coincide con hoy
                $hoy = date('Y-m-d');
                $tiene_reporte_hoy = false;
                foreach ($todos_reportes as $reporte) {
                    $fecha_normalizada = self::normalizarFecha($reporte['ava_fecha']);
                    if ($fecha_normalizada === $hoy) {
                        $tiene_reporte_hoy = true;
                        break;
                    }
                }
                
                $app['tiene_reporte_hoy'] = $tiene_reporte_hoy;
                $app['tiene_inactividad_hoy'] = false; // Simplificado
                
                $app['semaforo'] = self::calcularSemaforo($app);
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Aplicaciones obtenidas correctamente',
                'data' => $data
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener las aplicaciones',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    // API para obtener el calendario de reportes
    public static function buscarCalendarioAPI()
    {
        try {
            $usuario_id = isset($_GET['usuario_id']) ? $_GET['usuario_id'] : 3;
            $mes = isset($_GET['mes']) ? $_GET['mes'] : date('Y-m');
            
            // Obtener todos los reportes del usuario
            $sql_todos = "SELECT ava_fecha, ava_apl_id, ava_porcentaje, apl_nombre
                         FROM avance_diario av
                         INNER JOIN aplicacion ap ON av.ava_apl_id = ap.apl_id
                         WHERE av.ava_usu_id = $usuario_id
                         AND av.ava_situacion = 1
                         ORDER BY av.ava_fecha DESC";
            
            $todos_reportes = self::fetchArray($sql_todos);
            
            // Procesar cada reporte y normalizarlo
            $reportes_procesados = [];
            foreach ($todos_reportes as $reporte) {
                $fecha_original = $reporte['ava_fecha'];
                $fecha_normalizada = self::normalizarFecha($fecha_original);
                
                $reporte['ava_fecha'] = $fecha_normalizada;
                $reportes_procesados[] = $reporte;
            }
            
            // Filtrar por mes después de normalizar
            $anio = (int)substr($mes, 0, 4);
            $mes_num = (int)substr($mes, 5, 2);
            $mes_patron = sprintf('%04d-%02d', $anio, $mes_num);
            
            $reportes_del_mes = array_filter($reportes_procesados, function($reporte) use ($mes_patron) {
                return strpos($reporte['ava_fecha'], $mes_patron) === 0;
            });
            
            // Inactividades vacío para simplificar
            $inactividades = [];

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Calendario obtenido correctamente',
                'data' => [
                    'reportes' => array_values($reportes_del_mes),
                    'inactividades' => $inactividades
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener el calendario',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    // Normalizar fecha a formato ISO
    private static function normalizarFecha($fecha)
    {
        // Si ya está en formato ISO (YYYY-MM-DD), devolverla tal como está
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $fecha;
        }
        
        // Si está en formato M/D/YYYY, convertir a YYYY-MM-DD
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $fecha, $matches)) {
            $mes = sprintf('%02d', $matches[1]);
            $dia = sprintf('%02d', $matches[2]);
            $anio = $matches[3];
            return "$anio-$mes-$dia";
        }
        
        // Si está en formato D/M/YYYY, convertir a YYYY-MM-DD  
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $fecha, $matches)) {
            if ($matches[1] > 12) {
                $dia = sprintf('%02d', $matches[1]);
                $mes = sprintf('%02d', $matches[2]);
                $anio = $matches[3];
                return "$anio-$mes-$dia";
            }
        }
        
        // Si está en otro formato, intentar con strtotime
        $timestamp = strtotime($fecha);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        return $fecha;
    }

    // API para obtener resumen del usuario
    public static function buscarResumenAPI()
    {
        try {
            $usuario_id = isset($_GET['usuario_id']) ? $_GET['usuario_id'] : 3;

            $sql = "SELECT COUNT(*) as total_apps
                    FROM aplicacion 
                    WHERE apl_responsable = $usuario_id 
                    AND apl_situacion = 1";
            $total_result = self::fetchFirst($sql);
            
            $sql = "SELECT COUNT(*) as en_progreso
                    FROM aplicacion 
                    WHERE apl_responsable = $usuario_id 
                    AND apl_estado = 'EN_PROGRESO'
                    AND apl_situacion = 1";
            $progreso_result = self::fetchFirst($sql);
            
            $sql = "SELECT COUNT(*) as pausadas
                    FROM aplicacion 
                    WHERE apl_responsable = $usuario_id 
                    AND apl_estado = 'PAUSADO'
                    AND apl_situacion = 1";
            $pausadas_result = self::fetchFirst($sql);

            $sql = "SELECT COUNT(*) as reportes_semana
                    FROM avance_diario 
                    WHERE ava_usu_id = $usuario_id 
                    AND ava_situacion = 1";
            
            $reportes_semana = self::fetchFirst($sql);

            $resumen = [
                'total_apps' => $total_result ? $total_result['total_apps'] : 0,
                'en_progreso' => $progreso_result ? $progreso_result['en_progreso'] : 0,
                'pausadas' => $pausadas_result ? $pausadas_result['pausadas'] : 0,
                'cerradas' => 0,
                'reportes_semana' => $reportes_semana ? $reportes_semana['reportes_semana'] : 0
            ];

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Resumen obtenido correctamente',
                'data' => $resumen
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener el resumen',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    // API para verificar si es día hábil
    public static function verificarDiaHabilAPI()
    {
        try {
            $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
            
            $timestamp = strtotime($fecha);
            $dia_semana = date('N', $timestamp);
            
            $es_habil = ($dia_semana >= 1 && $dia_semana <= 5);
            $es_hoy = (date('Y-m-d') === $fecha);
            
            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Verificación completada',
                'data' => [
                    'es_habil' => $es_habil,
                    'es_hoy' => $es_hoy,
                    'dia_semana' => $dia_semana,
                    'fecha' => $fecha
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al verificar el día',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    // Método privado para calcular semáforo
    private static function calcularSemaforo($app)
    {
        if ($app['tiene_reporte_hoy'] || $app['tiene_inactividad_hoy']) {
            return 'verde';
        }

        $dia_semana = date('N');
        if ($dia_semana > 5) {
            return 'verde';
        }

        if (in_array($app['apl_estado'], ['PAUSADO', 'CERRADO'])) {
            return 'verde';
        }

        if ($app['apl_estado'] === 'EN_PROGRESO') {
            return 'ambar';
        }

        return 'verde';
    }

}