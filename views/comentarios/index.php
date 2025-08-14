<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-chat-dots-fill me-2"></i>
                            Sistema de Comentarios
                        </h4>
                        <div class="text-end">
                            <div class="mb-1">
                                <strong id="fecha-actual"><?= date('d/m/Y') ?></strong>
                            </div>
                            <small id="hora-actual"><?= date('H:i') ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna izquierda: Lista de aplicaciones -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-grid-3x3-gap me-2"></i>
                        Aplicaciones
                    </h5>
                </div>
                <div class="card-body">
                    <div id="lista-aplicaciones" class="row">
                        <!-- Las aplicaciones se cargarán aquí dinámicamente -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <div class="mt-2">Cargando aplicaciones...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna derecha: Comentarios -->
        <div class="col-lg-8">
            <!-- Mensaje inicial -->
            <div id="mensaje-seleccionar" class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-chat-square-dots text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-muted">Selecciona una aplicación</h5>
                    <p class="text-muted mb-0">Elige una aplicación de la lista para ver y gestionar sus comentarios</p>
                </div>
            </div>

            <!-- Panel de comentarios -->
            <div id="panel-comentarios" style="display: none;">
                <!-- Header de la aplicación seleccionada -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-chat-dots me-2"></i>
                                <span id="nombre-aplicacion-seleccionada">Aplicación</span>
                            </h5>
                            <div>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-outline-primary btn-sm" onclick="cargarComentarios()" title="Actualizar">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" onclick="verEstadisticas()" title="Estadísticas">
                                        <i class="bi bi-bar-chart"></i>
                                    </button>
                                    <button class="btn btn-outline-success btn-sm" onclick="marcarTodosComoLeidos()" title="Marcar todos como leídos">
                                        <i class="bi bi-check-all"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Búsqueda de comentarios -->
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="buscar-comentarios" 
                                   placeholder="Buscar en los comentarios..." 
                                   onkeyup="if(event.key==='Enter') buscarComentarios()">
                            <button class="btn btn-outline-primary" onclick="buscarComentarios()">
                                <i class="bi bi-search me-1"></i>Buscar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Lista de comentarios -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-chat-left-text me-2"></i>
                            Conversación
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div id="lista-comentarios" class="lista-comentarios p-3">
                            <!-- Los comentarios se cargarán aquí dinámicamente -->
                        </div>
                    </div>
                </div>

                <!-- Formulario para nuevo comentario -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-plus-circle me-2"></i>
                            Nuevo Comentario
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="form-nuevo-comentario" class="form-comentario">
                            <div class="mb-3">
                                <label for="nuevo-comentario-texto" class="form-label fw-bold">
                                    Mensaje <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="nuevo-comentario-texto" 
                                          name="com_texto" rows="4" 
                                          placeholder="Escribe tu comentario aquí... Puedes mencionar a otros usuarios con @usuario"
                                          required></textarea>
                                <div class="form-text d-flex justify-content-between">
                                    <span>Mínimo 5 caracteres. Puedes usar @menciones para notificar a usuarios.</span>
                                    <span id="contador-caracteres" class="fw-bold">0/1200</span>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary" id="btn-enviar-comentario">
                                    <i class="bi bi-send me-1"></i>
                                    Enviar Comentario
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/comentarios/index.js') ?>"></script>
<link rel="stylesheet" href="<?= asset('build/css/comentarios/style.css') ?>">