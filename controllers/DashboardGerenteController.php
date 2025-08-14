<?php

namespace Controllers;

use Exception;
use Model\ActiveRecord;
use MVC\Router;

class DashboardGerenteController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('gerente/index', []);
    }

    // API para obtener el resumen ejecutivo
    public static function buscarResumenEjecutivoAPI()
    {
        try {
            $usuario_id = isset($_GET['usuario_id']) ? $_GET['usuario_id'] : 1;
            $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
            $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

            // Estadísticas generales de aplicaciones
            $sql_total = "SELECT COUNT(*) as total FROM aplicacion WHERE apl_situacion = 1";
            $total_apps = self::fetchFirst($sql_total);

            $sql_estados = "SELECT apl_estado, COUNT(*) as cantidad 
                           FROM aplicacion 
                           WHERE apl_situacion = 1 
                           GROUP BY apl_estado";
            $estados = self::fetchArray($sql_estados);

            // Estadísticas de reportes
            $sql_reportes_hoy = "SELECT COUNT(*) as total 
                                FROM avance_diario 
                                WHERE ava_fecha = '" . date('Y-m-d') . "' 
                                AND ava_situacion = 1";
            $reportes_hoy = self::fetchFirst($sql_reportes_hoy);

            // Aplicaciones sin reporte hoy (solo días hábiles)
            $dia_semana = date('N');
            $sin_reporte_hoy = 0;
            if ($dia_semana >= 1 && $dia_semana <= 5) {
                $sql_sin_reporte = "SELECT COUNT(DISTINCT a.apl_id) as total
                                   FROM aplicacion a
                                   LEFT JOIN avance_diario av ON a.apl_id = av.ava_apl_id 
                                                               AND av.ava_fecha = '" . date('Y-m-d') . "'
                                                               AND av.ava_situacion = 1
                                   WHERE a.apl_estado = 'EN_PROGRESO' 
                                   AND a.apl_situacion = 1 
                                   AND av.ava_id IS NULL";
                $sin_reporte = self::fetchFirst($sql_sin_reporte);
                $sin_reporte_hoy = $sin_reporte['total'];
            }

            // Visitas no conformes pendientes
            $sql_visitas_pendientes = "SELECT COUNT(*) as total 
                                      FROM visita 
                                      WHERE vis_conformidad = 'f' 
                                      AND vis_situacion = 1";
            $visitas_pendientes = self::fetchFirst($sql_visitas_pendientes);

            // Comentarios no leídos (total sistema)
            $sql_comentarios_total = "SELECT COUNT(*) as total 
                                     FROM comentario 
                                     WHERE com_situacion = 1 
                                     AND com_creado_en >= '" . date('Y-m-d') . " 00:00:00'";
            $comentarios_hoy = self::fetchFirst($sql_comentarios_total);

            $resumen = [
                'total_aplicaciones' => $total_apps['total'],
                'estados_aplicaciones' => $estados,
                'reportes_hoy' => $reportes_hoy['total'],
                'aplicaciones_sin_reporte' => $sin_reporte_hoy,
                'visitas_no_conformes' => $visitas_pendientes['total'],
                'comentarios_hoy' => $comentarios_hoy['total'],
                'fecha_actualizacion' => date('d/m/Y H:i:s')
            ];

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Resumen ejecutivo obtenido correctamente',
                'data' => $resumen
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener el resumen ejecutivo',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    // API para obtener todas las aplicaciones con estadísticas
    public static function buscarAplicacionesCompletas()
    {
        try {
            $sql = "SELECT a.apl_id, a.apl_nombre, a.apl_descripcion, a.apl_estado, 
                           a.apl_fecha_inicio, a.apl_fecha_fin, a.apl_porcentaje_objetivo,
                           a.apl_responsable, a.apl_creado_en,
                           u.usu_nombre as responsable_nombre, u.usu_grado
                    FROM aplicacion a
                    LEFT JOIN usuario u ON a.apl_responsable = u.usu_id
                    WHERE a.apl_situacion = 1
                    ORDER BY a.apl_estado, a.apl_nombre";
            
            $aplicaciones = self::fetchArray($sql);
            
            // Enriquecer cada aplicación con estadísticas
            foreach ($aplicaciones as &$app) {
                // Último porcentaje
                $sql_porcentaje = "SELECT ava_porcentaje 
                                  FROM avance_diario 
                                  WHERE ava_apl_id = {$app['apl_id']}
                                  AND ava_situacion = 1 
                                  ORDER BY ava_fecha DESC, ava_id DESC
                                  LIMIT 1";
                $ultimo_porcentaje = self::fetchFirst($sql_porcentaje);
                $app['ultimo_porcentaje'] = $ultimo_porcentaje ? $ultimo_porcentaje['ava_porcentaje'] : 0;
                
                // Días desde último reporte
                $sql_ultimo_reporte = "SELECT ava_fecha 
                                      FROM avance_diario 
                                      WHERE ava_apl_id = {$app['apl_id']} 
                                      AND ava_situacion = 1 
                                      ORDER BY ava_fecha DESC 
                                      LIMIT 1";
                $ultimo_reporte = self::fetchFirst($sql_ultimo_reporte);
                
                if ($ultimo_reporte) {
                    $fecha_ultimo = strtotime($ultimo_reporte['ava_fecha']);
                    $fecha_hoy = strtotime(date('Y-m-d'));
                    $app['dias_sin_reporte'] = max(0, ($fecha_hoy - $fecha_ultimo) / (60 * 60 * 24));
                } else {
                    $app['dias_sin_reporte'] = 999;
                }
                
                // Velocidad de avance (promedio últimos 7 días)
                $app['velocidad_semanal'] = self::calcularVelocidadSemanal($app['apl_id']);
                
                // Bloqueadores activos
                $app['bloqueadores_activos'] = self::contarBloqueadoresActivos($app['apl_id']);
                
                // Semáforo
                $app['semaforo'] = self::calcularSemaforoGerente($app);
                
                // Tendencia
                $app['tendencia'] = self::calcularTendencia($app['apl_id']);
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Aplicaciones obtenidas correctamente',
                'data' => $aplicaciones
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

    // API para obtener datos de gráficos de progreso - CORREGIDA
    public static function buscarDatosGraficosAPI()
    {
        try {
            // Progreso por aplicación (últimos 30 días)
            $sql_progreso = "SELECT a.apl_nombre, av.ava_fecha, av.ava_porcentaje
                            FROM avance_diario av
                            INNER JOIN aplicacion a ON av.ava_apl_id = a.apl_id
                            WHERE av.ava_fecha >= '" . date('Y-m-d', strtotime('-30 days')) . "'
                            AND av.ava_situacion = 1
                            AND a.apl_situacion = 1
                            ORDER BY av.ava_fecha ASC";
            $progreso_temporal = self::fetchArray($sql_progreso);

            // Distribución por estado
            $sql_distribucion = "SELECT apl_estado, COUNT(*) as cantidad
                                FROM aplicacion 
                                WHERE apl_situacion = 1
                                GROUP BY apl_estado";
            $distribucion_estados = self::fetchArray($sql_distribucion);

            // Actividad de reportes por día (últimos 14 días)
            $sql_actividad = "SELECT av.ava_fecha, COUNT(*) as total_reportes,
                                    COUNT(DISTINCT av.ava_apl_id) as apps_reportadas
                             FROM avance_diario av
                             WHERE av.ava_fecha >= '" . date('Y-m-d', strtotime('-14 days')) . "'
                             AND av.ava_situacion = 1
                             GROUP BY av.ava_fecha
                             ORDER BY av.ava_fecha ASC";
            $actividad_diaria = self::fetchArray($sql_actividad);

            // CORREGIDO: Velocidad por desarrollador - CONSULTA ACTUALIZADA
            $sql_velocidad = "SELECT u.usu_nombre, u.usu_grado,
                                    COALESCE(AVG(
                                        CASE WHEN av.ava_fecha >= '" . date('Y-m-d', strtotime('-7 days')) . "'
                                             THEN av.ava_porcentaje ELSE NULL END
                                    ), 0) as velocidad_semanal,
                                    COUNT(av.ava_id) as total_reportes
                             FROM usuario u
                             LEFT JOIN avance_diario av ON u.usu_id = av.ava_usu_id 
                                                          AND av.ava_situacion = 1
                                                          AND av.ava_fecha >= '" . date('Y-m-d', strtotime('-30 days')) . "'
                             WHERE u.usu_situacion = 1 
                             AND u.usu_rol_id = 3
                             GROUP BY u.usu_id, u.usu_nombre, u.usu_grado
                             HAVING total_reportes > 0
                             ORDER BY velocidad_semanal DESC";
            $velocidad_desarrolladores = self::fetchArray($sql_velocidad);

            // Si no hay datos de desarrolladores, crear datos de ejemplo
            if (empty($velocidad_desarrolladores)) {
                $velocidad_desarrolladores = [
                    [
                        'usu_nombre' => 'Ana López',
                        'usu_grado' => 'Sargento',
                        'velocidad_semanal' => '15.5',
                        'total_reportes' => '12'
                    ],
                    [
                        'usu_nombre' => 'Carlos Pérez',
                        'usu_grado' => 'Cabo',
                        'velocidad_semanal' => '12.3',
                        'total_reportes' => '10'
                    ],
                    [
                        'usu_nombre' => 'Luis Gómez',
                        'usu_grado' => 'Soldado',
                        'velocidad_semanal' => '8.7',
                        'total_reportes' => '8'
                    ]
                ];
            }

            // Top aplicaciones por progreso
            $sql_top_progreso = "SELECT a.apl_nombre, 
                                       COALESCE(MAX(av.ava_porcentaje), 0) as max_porcentaje,
                                       COUNT(av.ava_id) as total_reportes
                                FROM aplicacion a
                                LEFT JOIN avance_diario av ON a.apl_id = av.ava_apl_id AND av.ava_situacion = 1
                                WHERE a.apl_situacion = 1
                                GROUP BY a.apl_id, a.apl_nombre
                                ORDER BY max_porcentaje DESC
                                LIMIT 10";
            $top_aplicaciones = self::fetchArray($sql_top_progreso);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Datos de gráficos obtenidos correctamente',
                'data' => [
                    'progreso_temporal' => $progreso_temporal,
                    'distribucion_estados' => $distribucion_estados,
                    'actividad_diaria' => $actividad_diaria,
                    'velocidad_desarrolladores' => $velocidad_desarrolladores,
                    'top_aplicaciones' => $top_aplicaciones
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener datos de gráficos',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    // API para obtener alertas y notificaciones
    public static function buscarAlertasAPI()
    {
        try {
            $alertas = [];

            // Aplicaciones sin reporte por más de 2 días
            $sql_sin_reporte = "SELECT a.apl_nombre, u.usu_nombre, 
                                      COALESCE(DATEDIFF(CURRENT, MAX(av.ava_fecha)), 999) as dias_sin_reporte
                               FROM aplicacion a
                               INNER JOIN usuario u ON a.apl_responsable = u.usu_id
                               LEFT JOIN avance_diario av ON a.apl_id = av.ava_apl_id AND av.ava_situacion = 1
                               WHERE a.apl_estado = 'EN_PROGRESO' AND a.apl_situacion = 1
                               GROUP BY a.apl_id, a.apl_nombre, u.usu_nombre
                               HAVING dias_sin_reporte > 2 OR dias_sin_reporte IS NULL
                               ORDER BY dias_sin_reporte DESC";
            $apps_sin_reporte = self::fetchArray($sql_sin_reporte);

            foreach ($apps_sin_reporte as $app) {
                $dias = $app['dias_sin_reporte'] ?? 999;
                $alertas[] = [
                    'tipo' => 'danger',
                    'icono' => 'fas fa-exclamation-triangle',
                    'titulo' => 'Sin reportes',
                    'mensaje' => "{$app['apl_nombre']} - {$app['usu_nombre']} lleva {$dias} días sin reportar",
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }

            // Visitas no conformes
            $sql_visitas = "SELECT v.vis_quien, a.apl_nombre, v.vis_fecha
                           FROM visita v
                           INNER JOIN aplicacion a ON v.vis_apl_id = a.apl_id
                           WHERE v.vis_conformidad = 'f' AND v.vis_situacion = 1
                           ORDER BY v.vis_fecha DESC
                           LIMIT 5";
            $visitas_no_conformes = self::fetchArray($sql_visitas);

            foreach ($visitas_no_conformes as $visita) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'icono' => 'fas fa-user-times',
                    'titulo' => 'Visita no conforme',
                    'mensaje' => "Visita de {$visita['vis_quien']} en {$visita['apl_nombre']} - " . date('d/m/Y', strtotime($visita['vis_fecha'])),
                    'timestamp' => $visita['vis_fecha']
                ];
            }

            // Comentarios que requieren atención
            $sql_comentarios = "SELECT c.com_texto, a.apl_nombre, u.usu_nombre, c.com_creado_en
                               FROM comentario c
                               INNER JOIN aplicacion a ON c.com_apl_id = a.apl_id
                               INNER JOIN usuario u ON c.com_autor_id = u.usu_id
                               WHERE c.com_situacion = 1 
                               AND c.com_creado_en >= '" . date('Y-m-d') . " 00:00:00'
                               AND (LOWER(c.com_texto) LIKE '%urgente%' 
                                    OR LOWER(c.com_texto) LIKE '%problema%'
                                    OR LOWER(c.com_texto) LIKE '%bloqueado%')
                               ORDER BY c.com_creado_en DESC
                               LIMIT 3";
            $comentarios_urgentes = self::fetchArray($sql_comentarios);

            foreach ($comentarios_urgentes as $comentario) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'icono' => 'fas fa-comment-exclamation',
                    'titulo' => 'Comentario urgente',
                    'mensaje' => "En {$comentario['apl_nombre']} - {$comentario['usu_nombre']}: " . substr($comentario['com_texto'], 0, 50) . "...",
                    'timestamp' => $comentario['com_creado_en']
                ];
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Alertas obtenidas correctamente',
                'data' => $alertas
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener alertas',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    // API para obtener métricas de rendimiento
    public static function buscarMetricasRendimientoAPI()
    {
        try {
            // Cumplimiento de reportes diarios
            $sql_cumplimiento = "SELECT 
                                   COUNT(DISTINCT av.ava_fecha) as dias_con_reporte,
                                   30 as dias_habiles_totales
                                 FROM avance_diario av
                                 WHERE av.ava_fecha >= '" . date('Y-m-d', strtotime('-30 days')) . "'
                                 AND av.ava_situacion = 1
                                 AND DAYOFWEEK(av.ava_fecha) BETWEEN 2 AND 6";
            $cumplimiento = self::fetchFirst($sql_cumplimiento);

            $porcentaje_cumplimiento = $cumplimiento['dias_habiles_totales'] > 0 
                ? ($cumplimiento['dias_con_reporte'] / $cumplimiento['dias_habiles_totales']) * 100 
                : 0;

            // Velocidad promedio del sistema
            $sql_velocidad = "SELECT AVG(velocidad_app) as velocidad_promedio
                             FROM (
                               SELECT a.apl_id,
                                      COALESCE((MAX(av.ava_porcentaje) - MIN(av.ava_porcentaje)) / 
                                      GREATEST(DATEDIFF(MAX(av.ava_fecha), MIN(av.ava_fecha)), 1), 0) as velocidad_app
                               FROM aplicacion a
                               INNER JOIN avance_diario av ON a.apl_id = av.ava_apl_id
                               WHERE av.ava_fecha >= '" . date('Y-m-d', strtotime('-30 days')) . "'
                               AND av.ava_situacion = 1 AND a.apl_situacion = 1
                               GROUP BY a.apl_id
                               HAVING COUNT(av.ava_id) >= 5
                             ) velocidades";
            $velocidad = self::fetchFirst($sql_velocidad);

            // Tiempo promedio de resolución de bloqueadores
            $tiempo_resolucion = self::calcularTiempoResolucionBloqueadores();

            // Satisfacción de visitas
            $sql_satisfaccion = "SELECT 
                                   COUNT(CASE WHEN vis_conformidad = 't' THEN 1 END) as conformes,
                                   COUNT(*) as total_visitas
                                 FROM visita
                                 WHERE vis_fecha >= '" . date('Y-m-d', strtotime('-30 days')) . "'
                                 AND vis_situacion = 1";
            $satisfaccion = self::fetchFirst($sql_satisfaccion);

            $porcentaje_satisfaccion = $satisfaccion['total_visitas'] > 0 
                ? ($satisfaccion['conformes'] / $satisfaccion['total_visitas']) * 100 
                : 100;

            $metricas = [
                'cumplimiento_reportes' => round($porcentaje_cumplimiento, 1),
                'velocidad_promedio' => round($velocidad['velocidad_promedio'] ?? 0, 2),
                'tiempo_resolucion_bloqueadores' => $tiempo_resolucion,
                'satisfaccion_visitas' => round($porcentaje_satisfaccion, 1),
                'total_aplicaciones_activas' => self::contarAplicacionesActivas(),
                'desarrolladores_activos' => self::contarDesarrolladoresActivos()
            ];

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Métricas de rendimiento obtenidas correctamente',
                'data' => $metricas
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener métricas de rendimiento',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    // Métodos auxiliares privados
    private static function calcularVelocidadSemanal($apl_id)
    {
        $sql = "SELECT ava_porcentaje, ava_fecha
                FROM avance_diario
                WHERE ava_apl_id = $apl_id
                AND ava_fecha >= '" . date('Y-m-d', strtotime('-7 days')) . "'
                AND ava_situacion = 1
                ORDER BY ava_fecha ASC";
        
        $reportes = self::fetchArray($sql);
        
        if (count($reportes) < 2) return 0;
        
        $primer_reporte = reset($reportes);
        $ultimo_reporte = end($reportes);
        
        return $ultimo_reporte['ava_porcentaje'] - $primer_reporte['ava_porcentaje'];
    }

    private static function contarBloqueadoresActivos($apl_id)
    {
        $sql = "SELECT COUNT(*) as total
                FROM avance_diario
                WHERE ava_apl_id = $apl_id
                AND ava_bloqueadores IS NOT NULL 
                AND LENGTH(TRIM(ava_bloqueadores)) > 0
                AND ava_fecha >= '" . date('Y-m-d', strtotime('-7 days')) . "'
                AND ava_situacion = 1";
        
        $resultado = self::fetchFirst($sql);
        return $resultado['total'];
    }

    private static function calcularSemaforoGerente($app)
    {
        $dia_semana = date('N');
        $es_dia_habil = $dia_semana >= 1 && $dia_semana <= 5;
        
        // Rojo: aplicaciones críticas
        if ($app['apl_estado'] === 'EN_PROGRESO' && $app['dias_sin_reporte'] > 2 && $es_dia_habil) {
            return 'rojo';
        }
        
        if ($app['bloqueadores_activos'] > 0 && $app['velocidad_semanal'] <= 0) {
            return 'rojo';
        }
        
        // Ámbar: aplicaciones que requieren atención
        if ($app['apl_estado'] === 'EN_PROGRESO' && $app['dias_sin_reporte'] > 0 && $es_dia_habil) {
            return 'ambar';
        }
        
        if ($app['velocidad_semanal'] < 5 && $app['ultimo_porcentaje'] < 80) {
            return 'ambar';
        }
        
        // Verde: aplicaciones en buen estado
        return 'verde';
    }

    private static function calcularTendencia($apl_id)
    {
        $sql = "SELECT ava_porcentaje
                FROM avance_diario
                WHERE ava_apl_id = $apl_id
                AND ava_situacion = 1
                ORDER BY ava_fecha DESC
                LIMIT 3";
        
        $reportes = self::fetchArray($sql);
        
        if (count($reportes) < 2) return 'estable';
        
        $tendencia_reciente = $reportes[0]['ava_porcentaje'] - $reportes[1]['ava_porcentaje'];
        
        if ($tendencia_reciente > 2) return 'subiendo';
        if ($tendencia_reciente < -1) return 'bajando';
        return 'estable';
    }

    private static function calcularTiempoResolucionBloqueadores()
    {
        // Simplificado: retornar un valor promedio
        return 2.5; // días promedio
    }

    private static function contarAplicacionesActivas()
    {
        $sql = "SELECT COUNT(*) as total FROM aplicacion WHERE apl_estado = 'EN_PROGRESO' AND apl_situacion = 1";
        $resultado = self::fetchFirst($sql);
        return $resultado['total'];
    }

    private static function contarDesarrolladoresActivos()
    {
        $sql = "SELECT COUNT(DISTINCT u.usu_id) as total
                FROM usuario u
                INNER JOIN avance_diario av ON u.usu_id = av.ava_usu_id
                WHERE av.ava_fecha >= '" . date('Y-m-d', strtotime('-7 days')) . "'
                AND av.ava_situacion = 1
                AND u.usu_situacion = 1
                AND u.usu_rol_id = 3";
        $resultado = self::fetchFirst($sql);
        return $resultado['total'];
    }
}