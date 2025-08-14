<?php

namespace Controllers;

use Exception;
use MVC\Router;
use Model\ActiveRecord;
use Model\Comentario;

class ComentarioController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('comentarios/index', []);
    }

    public static function guardarAPI()
    {
        getHeadersApi();

        try {
            // Validar campos obligatorios
            $_POST['com_apl_id'] = filter_var($_POST['com_apl_id'], FILTER_SANITIZE_NUMBER_INT);
            $_POST['com_autor_id'] = filter_var($_POST['com_autor_id'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['com_apl_id'] <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe seleccionar una aplicación válida'
                ]);
                exit;
            }

            if ($_POST['com_autor_id'] <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe especificar un autor válido'
                ]);
                exit;
            }

            $_POST['com_texto'] = trim(htmlspecialchars($_POST['com_texto']));
            
            if (empty($_POST['com_texto'])) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El comentario no puede estar vacío'
                ]);
                exit;
            }
            
            if (strlen($_POST['com_texto']) < 5) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El comentario debe tener al menos 5 caracteres'
                ]);
                exit;
            }

            if (strlen($_POST['com_texto']) > 1200) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El comentario no puede exceder 1200 caracteres'
                ]);
                exit;
            }

            // Verificar que la aplicación existe
            $sql_app = "SELECT apl_id FROM aplicacion WHERE apl_id = {$_POST['com_apl_id']} AND apl_situacion = 1";
            $app_existe = self::fetchFirst($sql_app);
            
            if (!$app_existe) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'La aplicación especificada no existe'
                ]);
                exit;
            }

            // Verificar que el usuario existe
            $sql_user = "SELECT usu_id FROM usuario WHERE usu_id = {$_POST['com_autor_id']} AND usu_situacion = 1";
            $user_existe = self::fetchFirst($sql_user);
            
            if (!$user_existe) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El usuario especificado no existe'
                ]);
                exit;
            }

            // Inserción
            $sql = "INSERT INTO comentario (
                        com_apl_id, com_autor_id, com_texto, 
                        com_creado_en, com_situacion
                    ) VALUES (
                        " . (int)$_POST['com_apl_id'] . ",
                        " . (int)$_POST['com_autor_id'] . ",
                        " . self::$db->quote($_POST['com_texto']) . ",
                        CURRENT,
                        1
                    )";
            
            $resultado = self::$db->exec($sql);

            if ($resultado) {
                $comentario_id = self::$db->lastInsertId();
                
                // Marcar automáticamente como leído por el autor
                self::marcarComoLeido($comentario_id, $_POST['com_autor_id']);
                
                // Obtener el comentario recién creado con información del autor
                $comentario_completo = self::obtenerComentarioCompleto($comentario_id);
                
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Comentario registrado correctamente',
                    'data' => [
                        'id' => $comentario_id,
                        'comentario' => $comentario_completo
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al registrar el comentario'
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
            $limite = isset($_GET['limite']) ? $_GET['limite'] : 50;
            $solo_no_leidos = isset($_GET['solo_no_leidos']) ? $_GET['solo_no_leidos'] === 'true' : false;

            if (!$apl_id) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Debe especificar una aplicación'
                ]);
                return;
            }

            $where_clauses = ["c.com_apl_id = $apl_id", "c.com_situacion = 1"];
            
            // Si se especifica usuario y solo_no_leidos, filtrar comentarios no leídos por ese usuario
            if ($usuario_id && $solo_no_leidos) {
                $where_clauses[] = "cl.col_usu_id IS NULL";
            }
            
            $where_clause = implode(' AND ', $where_clauses);
            
            $join_leidos = "";
            if ($usuario_id && $solo_no_leidos) {
                $join_leidos = "LEFT JOIN comentario_leido cl ON c.com_id = cl.col_com_id AND cl.col_usu_id = $usuario_id AND cl.col_situacion = 1";
            }
            
            $sql = "SELECT c.com_id, c.com_apl_id, c.com_autor_id, c.com_texto, c.com_creado_en,
                           u.usu_nombre, u.usu_grado, u.usu_email,
                           a.apl_nombre" .
                           ($usuario_id && !$solo_no_leidos ? ", CASE WHEN cl.col_usu_id IS NOT NULL THEN 't' ELSE 'f' END as leido" : "") . "
                    FROM comentario c
                    INNER JOIN usuario u ON c.com_autor_id = u.usu_id
                    INNER JOIN aplicacion a ON c.com_apl_id = a.apl_id" .
                    ($usuario_id && !$solo_no_leidos ? " LEFT JOIN comentario_leido cl ON c.com_id = cl.col_com_id AND cl.col_usu_id = $usuario_id AND cl.col_situacion = 1" : "") .
                    " $join_leidos
                    WHERE $where_clause
                    ORDER BY c.com_creado_en DESC
                    FIRST $limite";
            
            $data = self::fetchArray($sql);

            // Obtener conteo de comentarios no leídos para este usuario y aplicación
            $no_leidos = 0;
            if ($usuario_id) {
                $sql_no_leidos = "SELECT COUNT(*) as total
                                 FROM comentario c
                                 LEFT JOIN comentario_leido cl ON c.com_id = cl.col_com_id AND cl.col_usu_id = $usuario_id AND cl.col_situacion = 1
                                 WHERE c.com_apl_id = $apl_id 
                                 AND c.com_situacion = 1 
                                 AND cl.col_usu_id IS NULL";
                $resultado_no_leidos = self::fetchFirst($sql_no_leidos);
                $no_leidos = $resultado_no_leidos ? $resultado_no_leidos['total'] : 0;
            }

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Comentarios obtenidos correctamente',
                'data' => [
                    'comentarios' => $data,
                    'no_leidos' => $no_leidos,
                    'total' => count($data)
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al obtener los comentarios',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function marcarLeidoAPI()
    {
        getHeadersApi();

        try {
            $_POST['com_id'] = filter_var($_POST['com_id'], FILTER_SANITIZE_NUMBER_INT);
            $_POST['usu_id'] = filter_var($_POST['usu_id'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['com_id'] <= 0 || $_POST['usu_id'] <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Parámetros inválidos'
                ]);
                exit;
            }

            // Verificar que el comentario existe
            $sql_comentario = "SELECT com_id FROM comentario WHERE com_id = {$_POST['com_id']} AND com_situacion = 1";
            $comentario_existe = self::fetchFirst($sql_comentario);
            
            if (!$comentario_existe) {
                http_response_code(404);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Comentario no encontrado'
                ]);
                exit;
            }

            // Verificar si ya está marcado como leído
            $sql_existe = "SELECT col_id FROM comentario_leido 
                          WHERE col_com_id = {$_POST['com_id']} 
                          AND col_usu_id = {$_POST['usu_id']} 
                          AND col_situacion = 1";
            $ya_leido = self::fetchFirst($sql_existe);
            
            if ($ya_leido) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Comentario ya estaba marcado como leído'
                ]);
                exit;
            }

            $resultado = self::marcarComoLeido($_POST['com_id'], $_POST['usu_id']);
            
            if ($resultado) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Comentario marcado como leído'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al marcar como leído'
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

    public static function marcarTodosLeidosAPI()
    {
        getHeadersApi();

        try {
            $_POST['apl_id'] = filter_var($_POST['apl_id'], FILTER_SANITIZE_NUMBER_INT);
            $_POST['usu_id'] = filter_var($_POST['usu_id'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['apl_id'] <= 0 || $_POST['usu_id'] <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Parámetros inválidos'
                ]);
                exit;
            }

            // Obtener todos los comentarios no leídos de la aplicación para este usuario
            $sql_no_leidos = "SELECT c.com_id
                             FROM comentario c
                             LEFT JOIN comentario_leido cl ON c.com_id = cl.col_com_id AND cl.col_usu_id = {$_POST['usu_id']} AND cl.col_situacion = 1
                             WHERE c.com_apl_id = {$_POST['apl_id']} 
                             AND c.com_situacion = 1 
                             AND cl.col_usu_id IS NULL";
            
            $comentarios_no_leidos = self::fetchArray($sql_no_leidos);
            
            $marcados = 0;
            foreach ($comentarios_no_leidos as $comentario) {
                if (self::marcarComoLeido($comentario['com_id'], $_POST['usu_id'])) {
                    $marcados++;
                }
            }
            
            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => "Se marcaron $marcados comentarios como leídos",
                'data' => ['marcados' => $marcados]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error interno del servidor',
                'detalle' => $e->getMessage(),
            ]);
        }
    }

    public static function eliminarAPI()
    {
        getHeadersApi();

        try {
            $_POST['com_id'] = filter_var($_POST['com_id'], FILTER_SANITIZE_NUMBER_INT);
            $_POST['usu_id'] = filter_var($_POST['usu_id'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($_POST['com_id'] <= 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'ID de comentario inválido'
                ]);
                exit;
            }

            // Verificar que el comentario existe y pertenece al usuario (solo el autor puede eliminar)
            $sql_comentario = "SELECT com_id, com_autor_id FROM comentario 
                              WHERE com_id = {$_POST['com_id']} AND com_situacion = 1";
            $comentario = self::fetchFirst($sql_comentario);
            
            if (!$comentario) {
                http_response_code(404);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Comentario no encontrado'
                ]);
                exit;
            }

            // Verificar que el usuario es el autor del comentario
            if ($comentario['com_autor_id'] != $_POST['usu_id']) {
                http_response_code(403);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Solo el autor puede eliminar el comentario'
                ]);
                exit;
            }

            // Verificar que el comentario no tenga más de 24 horas
            $sql_fecha = "SELECT com_creado_en FROM comentario WHERE com_id = {$_POST['com_id']}";
            $fecha_comentario = self::fetchFirst($sql_fecha);
            
            if ($fecha_comentario) {
                $ahora = time();
                $fecha_creacion = strtotime($fecha_comentario['com_creado_en']);
                $horas_transcurridas = ($ahora - $fecha_creacion) / 3600;
                
                if ($horas_transcurridas > 24) {
                    http_response_code(400);
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'No se puede eliminar un comentario después de 24 horas'
                    ]);
                    exit;
                }
            }

            // Eliminación lógica del comentario
            $sql_eliminar = "UPDATE comentario SET com_situacion = 0 WHERE com_id = {$_POST['com_id']}";
            $resultado = self::$db->exec($sql_eliminar);
            
            if ($resultado) {
                // También eliminar las marcas de leído asociadas
                $sql_eliminar_leidos = "UPDATE comentario_leido SET col_situacion = 0 WHERE col_com_id = {$_POST['com_id']}";
                self::$db->exec($sql_eliminar_leidos);
                
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Comentario eliminado correctamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al eliminar el comentario'
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

    public static function obtenerEstadisticasAPI()
    {
        try {
            $apl_id = isset($_GET['apl_id']) ? $_GET['apl_id'] : null;
            $usuario_id = isset($_GET['usuario_id']) ? $_GET['usuario_id'] : null;
            
            $where_conditions = ["c.com_situacion = 1"];
            
            if ($apl_id) {
                $where_conditions[] = "c.com_apl_id = $apl_id";
            }
            
            if ($usuario_id) {
                $where_conditions[] = "c.com_autor_id = $usuario_id";
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            
            // Estadísticas básicas
            $sql_stats = "SELECT 
                            COUNT(*) as total_comentarios,
                            COUNT(DISTINCT c.com_autor_id) as usuarios_activos,
                            COUNT(DISTINCT c.com_apl_id) as aplicaciones_con_comentarios,
                            MIN(c.com_creado_en) as primer_comentario,
                            MAX(c.com_creado_en) as ultimo_comentario
                         FROM comentario c
                         WHERE $where_clause";
            
            $estadisticas = self::fetchFirst($sql_stats);
            
            // Comentarios por usuario
            $sql_por_usuario = "SELECT u.usu_nombre, u.usu_grado, COUNT(*) as total_comentarios
                               FROM comentario c
                               INNER JOIN usuario u ON c.com_autor_id = u.usu_id
                               WHERE $where_clause
                               GROUP BY u.usu_id, u.usu_nombre, u.usu_grado
                               ORDER BY total_comentarios DESC";
            
            $por_usuario = self::fetchArray($sql_por_usuario);
            
            // Actividad por día (últimos 7 días)
            $sql_actividad = "SELECT 
                                DATE(c.com_creado_en) as fecha,
                                COUNT(*) as comentarios
                             FROM comentario c
                             WHERE $where_clause
                             AND c.com_creado_en >= CURRENT - 7 UNITS DAY
                             GROUP BY DATE(c.com_creado_en)
                             ORDER BY fecha DESC";
            
            $actividad_diaria = self::fetchArray($sql_actividad);

            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Estadísticas obtenidas correctamente',
                'data' => [
                    'estadisticas' => $estadisticas,
                    'por_usuario' => $por_usuario,
                    'actividad_diaria' => $actividad_diaria
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

    // Método auxiliar para marcar un comentario como leído
    private static function marcarComoLeido($comentario_id, $usuario_id)
    {
        try {
            $sql = "INSERT INTO comentario_leido (
                        col_com_id, col_usu_id, col_leido_en, col_situacion
                    ) VALUES (
                        $comentario_id, $usuario_id, CURRENT, 1
                    )";
            
            return self::$db->exec($sql);
        } catch (Exception $e) {
            error_log("Error al marcar como leído: " . $e->getMessage());
            return false;
        }
    }

    // Método auxiliar para obtener un comentario completo
    private static function obtenerComentarioCompleto($comentario_id)
    {
        $sql = "SELECT c.com_id, c.com_apl_id, c.com_autor_id, c.com_texto, c.com_creado_en,
                       u.usu_nombre, u.usu_grado, u.usu_email,
                       a.apl_nombre
                FROM comentario c
                INNER JOIN usuario u ON c.com_autor_id = u.usu_id
                INNER JOIN aplicacion a ON c.com_apl_id = a.apl_id
                WHERE c.com_id = $comentario_id AND c.com_situacion = 1";
        
        return self::fetchFirst($sql);
    }
}