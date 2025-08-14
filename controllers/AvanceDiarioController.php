<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\AvanceDiario;

class AvanceDiarioController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('avance/index', []);
    }

    // Convertir fecha ISO a formato BD
    //este med dio mucho error, es facrtible utilzarlo 
    private static function convertirFechaParaBD($fecha_iso)
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $fecha_iso, $matches)) {
            $anio = $matches[1];
            $mes = (int)$matches[2];
            $dia = (int)$matches[3];
            return "$mes/$dia/$anio";
        }
        
        return $fecha_iso;
    }

    public static function guardarAPI()
    {
        getHeadersApi();

        try {
            // Validar campos obligatorios
            $_POST['ava_apl_id'] = filter_var($_POST['ava_apl_id'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['ava_apl_id'] <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe seleccionar una aplicación válida'
                ]);
                exit;
            }

            $_POST['ava_usu_id'] = filter_var($_POST['ava_usu_id'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['ava_usu_id'] <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe especificar un usuario válido'
                ]);
                exit;
            }

            // Manejo de fecha
            $_POST['ava_fecha'] = trim(htmlspecialchars($_POST['ava_fecha']));
            
            if (empty($_POST['ava_fecha'])) {
                $_POST['ava_fecha'] = date('Y-m-d');
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['ava_fecha'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Formato de fecha inválido. Use YYYY-MM-DD'
                ]);
                exit;
            }

            // Convertir fecha ISO a formato BD
            $fecha_para_bd = self::convertirFechaParaBD($_POST['ava_fecha']);

            // Verificar si ya existe un registro
            $sql_verificar = "SELECT COUNT(*) as total 
                             FROM avance_diario 
                             WHERE ava_apl_id = {$_POST['ava_apl_id']} 
                             AND ava_usu_id = {$_POST['ava_usu_id']} 
                             AND ava_fecha = '$fecha_para_bd'
                             AND ava_situacion = 1";
            
            $existe = self::fetchFirst($sql_verificar);
            
            if ($existe && $existe['total'] > 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Ya existe un reporte para esta aplicación, usuario y fecha. Solo se permite un reporte por día.'
                ]);
                exit;
            }

            // Validar que solo se puede reportar HOY
            if ($_POST['ava_fecha'] !== date('Y-m-d')) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Solo se puede crear reportes para el día de hoy'
                ]);
                exit;
            }

            // Validar que sea día hábil
            $dia_semana = date('N', strtotime($_POST['ava_fecha']));
            if ($dia_semana > 5) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se pueden crear reportes en fines de semana'
                ]);
                exit;
            }

            $_POST['ava_porcentaje'] = filter_var($_POST['ava_porcentaje'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['ava_porcentaje'] < 0 || $_POST['ava_porcentaje'] > 100) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El porcentaje debe estar entre 0 y 100'
                ]);
                exit;
            }

            // Validar regla de monotonía
            $validacion = AvanceDiario::validarMonotonia($_POST['ava_apl_id'], $_POST['ava_usu_id'], $_POST['ava_porcentaje'], $_POST['ava_fecha']);
            
            if ($validacion['puede_bajar']) {
                $_POST['ava_justificacion'] = trim(htmlspecialchars($_POST['ava_justificacion']));
                
                if (empty($_POST['ava_justificacion'])) {
                    http_response_code(400);
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'Debe justificar por qué el porcentaje bajó de ' . $validacion['porcentaje_anterior'] . '% a ' . $_POST['ava_porcentaje'] . '%'
                    ]);
                    exit;
                }
                
                if (strlen($_POST['ava_justificacion']) < 10) {
                    http_response_code(400);
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'La justificación debe tener al menos 10 caracteres'
                    ]);
                    exit;
                }
            }

            $_POST['ava_resumen'] = trim(htmlspecialchars($_POST['ava_resumen']));
            
            if (empty($_POST['ava_resumen'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe ingresar un resumen del trabajo realizado'
                ]);
                exit;
            }
            
            if (strlen($_POST['ava_resumen']) < 10) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El resumen debe tener al menos 10 caracteres'
                ]);
                exit;
            }

            $_POST['ava_bloqueadores'] = trim(htmlspecialchars($_POST['ava_bloqueadores']));
            $_POST['ava_justificacion'] = trim(htmlspecialchars($_POST['ava_justificacion'] ?? ''));
            
            // Inserción con fecha en formato BD
            $sql = "INSERT INTO avance_diario (
                        ava_apl_id, ava_usu_id, ava_fecha, ava_porcentaje, 
                        ava_resumen, ava_bloqueadores, ava_justificacion, 
                        ava_creado_en, ava_situacion
                    ) VALUES (
                        " . (int)$_POST['ava_apl_id'] . ",
                        " . (int)$_POST['ava_usu_id'] . ",
                        '$fecha_para_bd',
                        " . (int)$_POST['ava_porcentaje'] . ",
                        " . self::$db->quote($_POST['ava_resumen']) . ",
                        " . self::$db->quote($_POST['ava_bloqueadores']) . ",
                        " . self::$db->quote($_POST['ava_justificacion']) . ",
                        CURRENT,
                        1
                    )";
            
            $resultado = self::$db->exec($sql);

            if($resultado){
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Reporte diario registrado correctamente',
                    'data' => [
                        'id' => self::$db->lastInsertId(),
                        'porcentaje_anterior' => $validacion['porcentaje_anterior'],
                        'diferencia' => $validacion['diferencia']
                    ]
                ]);
                exit;
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al registrar el reporte'
                ]);
                exit;
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno del servidor',
                'detalle' => $e->getMessage(),
            ]);
            exit;
        }
    }

    public static function buscarAPI()
    {
        try {
            $apl_id = isset($_GET['apl_id']) ? $_GET['apl_id'] : null;
            $usuario_id = isset($_GET['usuario_id']) ? $_GET['usuario_id'] : null;
            $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
            $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
            $limite = isset($_GET['limite']) ? $_GET['limite'] : 30;

            if ($apl_id && $fecha_inicio && $fecha_fin) {
                $data = AvanceDiario::obtenerPorRango($apl_id, $fecha_inicio, $fecha_fin, $usuario_id);
            } elseif ($apl_id) {
                $data = AvanceDiario::obtenerHistorial($apl_id, $usuario_id, $limite);
            } else {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe especificar al menos una aplicación'
                ]);
                return;
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Reportes obtenidos correctamente',
                'data' => $data
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los reportes',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function modificarAPI()
    {
        getHeadersApi();

        try {
            $id = $_POST['ava_id'];
            
            $reporte_actual = AvanceDiario::find($id);
            
            if (!$reporte_actual) {
                http_response_code(404);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Reporte no encontrado'
                ]);
                return;
            }

            if (!AvanceDiario::esHoy($reporte_actual->ava_fecha)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Solo se puede modificar el reporte del día actual'
                ]);
                return;
            }

            $hora_actual = date('H:i');
            if ($hora_actual > '18:00') {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se puede modificar el reporte después de las 18:00'
                ]);
                return;
            }

            $_POST['ava_porcentaje'] = filter_var($_POST['ava_porcentaje'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['ava_porcentaje'] < 0 || $_POST['ava_porcentaje'] > 100) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El porcentaje debe estar entre 0 y 100'
                ]);
                return;
            }

            $validacion = AvanceDiario::validarMonotonia($reporte_actual->ava_apl_id, $reporte_actual->ava_usu_id, $_POST['ava_porcentaje'], $reporte_actual->ava_fecha);
            
            if ($validacion['puede_bajar']) {
                $_POST['ava_justificacion'] = trim(htmlspecialchars($_POST['ava_justificacion']));
                
                if (empty($_POST['ava_justificacion'])) {
                    http_response_code(400);
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'Debe justificar por qué el porcentaje bajó'
                    ]);
                    return;
                }
            }

            $_POST['ava_resumen'] = trim(htmlspecialchars($_POST['ava_resumen']));
            
            if (empty($_POST['ava_resumen']) || strlen($_POST['ava_resumen']) < 10) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El resumen debe tener al menos 10 caracteres'
                ]);
                return;
            }

            $_POST['ava_bloqueadores'] = trim(htmlspecialchars($_POST['ava_bloqueadores']));

            $reporte_actual->sincronizar([
                'ava_porcentaje' => $_POST['ava_porcentaje'],
                'ava_resumen' => $_POST['ava_resumen'],
                'ava_bloqueadores' => $_POST['ava_bloqueadores'],
                'ava_justificacion' => $_POST['ava_justificacion'] ?? '',
                'ava_situacion' => 1
            ]);
            
            $reporte_actual->actualizar();

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Reporte modificado correctamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al modificar el reporte',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function buscarEstadisticasAPI()
    {
        try {
            $apl_id = isset($_GET['apl_id']) ? $_GET['apl_id'] : null;
            $usuario_id = isset($_GET['usuario_id']) ? $_GET['usuario_id'] : null;

            if (!$apl_id) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe especificar una aplicación'
                ]);
                return;
            }

            $estadisticas = AvanceDiario::obtenerEstadisticas($apl_id, $usuario_id);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Estadísticas obtenidas correctamente',
                'data' => $estadisticas
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener las estadísticas',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function buscarDiasSinReporteAPI()
    {
        try {
            $apl_id = isset($_GET['apl_id']) ? $_GET['apl_id'] : null;
            $usuario_id = isset($_GET['usuario_id']) ? $_GET['usuario_id'] : null;
            $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
            $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

            if (!$apl_id || !$usuario_id) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe especificar aplicación y usuario'
                ]);
                return;
            }

            $dias_sin_reporte = AvanceDiario::obtenerDiasSinReporte($apl_id, $usuario_id, $fecha_inicio, $fecha_fin);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Días sin reporte obtenidos correctamente',
                'data' => $dias_sin_reporte
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los días sin reporte',
                'detalle' => $e->getMessage(),
            ]);
        }
    }
}