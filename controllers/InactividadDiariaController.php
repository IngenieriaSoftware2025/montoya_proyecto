<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\InactividadDiaria;
use Model\AvanceDiario;

class InactividadDiariaController extends ActiveRecord
{

    public static function renderizarPagina(Router $router)
    {
        $router->render('inactividad/index', []);
    }

    // Convertir fecha ISO a formato BD
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
            $_POST['ina_apl_id'] = filter_var($_POST['ina_apl_id'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['ina_apl_id'] <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe seleccionar una aplicación válida'
                ]);
                exit;
            }

            $_POST['ina_usu_id'] = filter_var($_POST['ina_usu_id'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['ina_usu_id'] <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe especificar un usuario válido'
                ]);
                exit;
            }

            // Manejo de fecha
            $_POST['ina_fecha'] = trim(htmlspecialchars($_POST['ina_fecha']));
            
            if (empty($_POST['ina_fecha'])) {
                $_POST['ina_fecha'] = date('Y-m-d');
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['ina_fecha'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Formato de fecha inválido. Use YYYY-MM-DD'
                ]);
                exit;
            }

            // Convertir fecha ISO a formato BD
            $fecha_para_bd = self::convertirFechaParaBD($_POST['ina_fecha']);

            // Verificar si ya existe un registro
            $sql_verificar = "SELECT COUNT(*) as total 
                             FROM inactividad_diaria 
                             WHERE ina_apl_id = {$_POST['ina_apl_id']} 
                             AND ina_usu_id = {$_POST['ina_usu_id']} 
                             AND ina_fecha = '$fecha_para_bd'
                             AND ina_situacion = 1";
            
            $existe = self::fetchFirst($sql_verificar);
            
            if ($existe && $existe['total'] > 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Ya existe una justificación de inactividad para esta aplicación, usuario y fecha. Solo se permite una justificación por día.'
                ]);
                exit;
            }

            // Verificar si ya hay un reporte de avance para esta fecha
            $sql_verificar_avance = "SELECT COUNT(*) as total 
                                    FROM avance_diario 
                                    WHERE ava_apl_id = {$_POST['ina_apl_id']} 
                                    AND ava_usu_id = {$_POST['ina_usu_id']} 
                                    AND ava_fecha = '$fecha_para_bd'
                                    AND ava_situacion = 1";
            
            $existe_avance = self::fetchFirst($sql_verificar_avance);
            
            if ($existe_avance && $existe_avance['total'] > 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se puede justificar inactividad porque ya existe un reporte de avance para esta fecha'
                ]);
                exit;
            }

            // Validar que solo se puede justificar HOY
            if ($_POST['ina_fecha'] !== date('Y-m-d')) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Solo se puede justificar inactividad para el día de hoy'
                ]);
                exit;
            }

            // Validar que sea día hábil
            $dia_semana = date('N', strtotime($_POST['ina_fecha']));
            if ($dia_semana > 5) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se requiere justificación en fines de semana'
                ]);
                exit;
            }

            $_POST['ina_motivo'] = trim(htmlspecialchars($_POST['ina_motivo']));
            
            if (empty($_POST['ina_motivo'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe ingresar el motivo de la inactividad'
                ]);
                exit;
            }
            
            if (strlen($_POST['ina_motivo']) < 10) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El motivo debe tener al menos 10 caracteres'
                ]);
                exit;
            }

            $_POST['ina_tipo'] = trim(htmlspecialchars($_POST['ina_tipo']));
            
            if (empty($_POST['ina_tipo'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe seleccionar un tipo de inactividad'
                ]);
                exit;
            }

            // Validar que el tipo sea válido
            $tipos_validos = array_keys(InactividadDiaria::obtenerTiposInactividad());
            if (!in_array($_POST['ina_tipo'], $tipos_validos)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Tipo de inactividad no válido'
                ]);
                exit;
            }

            // Inserción con fecha en formato BD
            $sql = "INSERT INTO inactividad_diaria (
                        ina_apl_id, ina_usu_id, ina_fecha, ina_motivo, 
                        ina_tipo, ina_creado_en, ina_situacion
                    ) VALUES (
                        " . (int)$_POST['ina_apl_id'] . ",
                        " . (int)$_POST['ina_usu_id'] . ",
                        '$fecha_para_bd',
                        " . self::$db->quote($_POST['ina_motivo']) . ",
                        " . self::$db->quote($_POST['ina_tipo']) . ",
                        CURRENT,
                        1
                    )";
            
            $resultado = self::$db->exec($sql);
            
            if ($resultado) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Justificación de inactividad registrada correctamente',
                    'data' => [
                        'id' => self::$db->lastInsertId()
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al registrar la justificación'
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno del servidor',
                'detalle' => $e->getMessage(),
            ]);
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
                $data = InactividadDiaria::obtenerPorRango($apl_id, $fecha_inicio, $fecha_fin, $usuario_id);
            } elseif ($apl_id) {
                $data = InactividadDiaria::obtenerHistorial($apl_id, $usuario_id, $limite);
            } elseif ($usuario_id) {
                $mes = isset($_GET['mes']) ? $_GET['mes'] : date('Y-m');
                $data = InactividadDiaria::obtenerDelMes($usuario_id, $mes);
            } else {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe especificar al menos una aplicación o usuario'
                ]);
                return;
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Justificaciones obtenidas correctamente',
                'data' => $data
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener las justificaciones',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function modificarAPI()
    {
        getHeadersApi();

        try {
            $id = $_POST['ina_id'];
            
            $justificacion_actual = InactividadDiaria::find($id);
            
            if (!$justificacion_actual) {
                http_response_code(404);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Justificación no encontrada'
                ]);
                return;
            }

            if (!InactividadDiaria::esHoy($justificacion_actual->ina_fecha)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Solo se puede modificar la justificación del día actual'
                ]);
                return;
            }

            $hora_actual = date('H:i');
            if ($hora_actual > '18:00') {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'No se puede modificar la justificación después de las 18:00'
                ]);
                return;
            }

            $_POST['ina_motivo'] = trim(htmlspecialchars($_POST['ina_motivo']));
            
            if (empty($_POST['ina_motivo']) || strlen($_POST['ina_motivo']) < 10) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El motivo debe tener al menos 10 caracteres'
                ]);
                return;
            }

            $_POST['ina_tipo'] = trim(htmlspecialchars($_POST['ina_tipo']));
            
            $tipos_validos = array_keys(InactividadDiaria::obtenerTiposInactividad());
            if (!in_array($_POST['ina_tipo'], $tipos_validos)) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Tipo de inactividad no válido'
                ]);
                return;
            }

            $justificacion_actual->sincronizar([
                'ina_motivo' => $_POST['ina_motivo'],
                'ina_tipo' => $_POST['ina_tipo'],
                'ina_situacion' => 1
            ]);
            
            $justificacion_actual->actualizar();

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Justificación modificada correctamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al modificar la justificación',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function buscarTiposAPI()
    {
        try {
            $tipos = InactividadDiaria::obtenerTiposInactividad();
            
            $tipos_array = [];
            foreach ($tipos as $clave => $descripcion) {
                $tipos_array[] = [
                    'clave' => $clave,
                    'descripcion' => $descripcion
                ];
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Tipos de inactividad obtenidos correctamente',
                'data' => $tipos_array
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los tipos de inactividad',
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

            $estadisticas = InactividadDiaria::obtenerEstadisticas($apl_id, $usuario_id);
            $resumen_tipos = InactividadDiaria::obtenerResumenPorTipo($apl_id, $usuario_id);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Estadísticas obtenidas correctamente',
                'data' => [
                    'estadisticas' => $estadisticas,
                    'resumen_por_tipos' => $resumen_tipos
                ]
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

    public static function verificarEstadoDiaAPI()
    {
        try {
            $apl_id = isset($_GET['apl_id']) ? $_GET['apl_id'] : null;
            $usuario_id = isset($_GET['usuario_id']) ? $_GET['usuario_id'] : null;
            $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

            if (!$apl_id || !$usuario_id) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe especificar aplicación y usuario'
                ]);
                return;
            }

            $tiene_reporte = AvanceDiario::existeReporteHoy($apl_id, $usuario_id, $fecha);
            $tiene_justificacion = InactividadDiaria::existeJustificacionHoy($apl_id, $usuario_id, $fecha);
            $es_dia_habil = InactividadDiaria::esDiaHabil($fecha);
            $es_hoy = InactividadDiaria::esHoy($fecha);

            $puede_crear_reporte = $es_hoy && $es_dia_habil && !$tiene_reporte && !$tiene_justificacion;
            $puede_crear_justificacion = $es_hoy && $es_dia_habil && !$tiene_reporte && !$tiene_justificacion;

            $estado = 'completo';
            if ($es_dia_habil && !$tiene_reporte && !$tiene_justificacion) {
                $estado = 'pendiente';
            } elseif ($tiene_reporte) {
                $estado = 'con_reporte';
            } elseif ($tiene_justificacion) {
                $estado = 'justificado';
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Estado verificado correctamente',
                'data' => [
                    'fecha' => $fecha,
                    'es_dia_habil' => $es_dia_habil,
                    'es_hoy' => $es_hoy,
                    'tiene_reporte' => $tiene_reporte,
                    'tiene_justificacion' => $tiene_justificacion,
                    'puede_crear_reporte' => $puede_crear_reporte,
                    'puede_crear_justificacion' => $puede_crear_justificacion,
                    'estado' => $estado
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al verificar el estado del día',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

}