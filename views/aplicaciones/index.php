<div class="container-fluid py-4">
    <!-- Header y botones principales -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-grid-3x3-gap text-primary"></i>
                Gestión de Aplicaciones
            </h1>
            <p class="mb-0 text-muted">Administra las aplicaciones del sistema</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" onclick="mostrarFormularioNuevaAplicacion()">
                <i class="bi bi-plus-lg"></i> Nueva Aplicación
            </button>
            <button class="btn btn-outline-secondary" onclick="cargarEstadisticas()">
                <i class="bi bi-bar-chart"></i> Estadísticas
            </button>
        </div>
    </div>

    <!-- Cards de estadísticas -->
    <div class="row mb-4" id="cardsEstadisticas">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Aplicaciones
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalAplicaciones">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-grid-3x3-gap fa-2x text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                En Progreso
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="enProgreso">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-play fa-2x text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Pausadas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="pausadas">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-pause fa-2x text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Planificación
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="enPlanificacion">0</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clipboard-check fa-2x text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-funnel"></i> Filtros
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label for="filtroEstado" class="form-label">Estado</label>
                    <select class="form-select" id="filtroEstado" onchange="aplicarFiltros()">
                        <option value="">Todos los estados</option>
                        <option value="EN_PLANIFICACION">En Planificación</option>
                        <option value="EN_PROGRESO">En Progreso</option>
                        <option value="PAUSADO">Pausado</option>
                        <option value="CERRADO">Cerrado</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filtroResponsable" class="form-label">Responsable</label>
                    <select class="form-select" id="filtroResponsable" onchange="aplicarFiltros()">
                        <option value="">Todos los responsables</option>
                        <!-- Se llenará dinámicamente -->
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="buscarTexto" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="buscarTexto" placeholder="Nombre de aplicación..." onkeyup="aplicarFiltros()">
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de aplicaciones -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-table"></i> Lista de Aplicaciones
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="tablaAplicaciones" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Responsable</th>
                            <th>Estado</th>
                            <th>Progreso</th>
                            <th>Fecha Inicio</th>
                            <th>Días sin Reporte</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaAplicacionesBody">
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para nueva/editar aplicación -->
<div class="modal fade" id="modalAplicacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAplicacionTitulo">
                    <i class="bi bi-grid-3x3-gap"></i> Nueva Aplicación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAplicacion">
                <div class="modal-body">
                    <input type="hidden" id="aplicacionId" name="apl_id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="aplicacionNombre" class="form-label">
                                    Nombre de la Aplicación <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="aplicacionNombre" name="apl_nombre" 
                                       placeholder="Ej: Sistema de Inventario" maxlength="120" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="aplicacionEstado" class="form-label">Estado</label>
                                <select class="form-select" id="aplicacionEstado" name="apl_estado">
                                    <option value="EN_PLANIFICACION">En Planificación</option>
                                    <option value="EN_PROGRESO">En Progreso</option>
                                    <option value="PAUSADO">Pausado</option>
                                    <option value="CERRADO">Cerrado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="aplicacionDescripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="aplicacionDescripcion" name="apl_descripcion" 
                                  rows="3" placeholder="Descripción breve de la aplicación..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="aplicacionFechaInicio" class="form-label">
                                    Fecha de Inicio <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="aplicacionFechaInicio" 
                                       name="apl_fecha_inicio" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="aplicacionFechaFin" class="form-label">Fecha de Fin</label>
                                <input type="date" class="form-control" id="aplicacionFechaFin" 
                                       name="apl_fecha_fin">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="aplicacionPorcentajeObjetivo" class="form-label">
                                    % Objetivo <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="aplicacionPorcentajeObjetivo" 
                                       name="apl_porcentaje_objetivo" min="0" max="100" value="100" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="aplicacionResponsable" class="form-label">
                            Responsable <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="aplicacionResponsable" name="apl_responsable" required>
                            <option value="">Seleccione un responsable...</option>
                            <!-- Se llenará dinámicamente -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarAplicacion">
                        <i class="bi bi-floppy"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?= asset('build/css/aplicaciones/style.css') ?>">
<script src="<?= asset('build/js/aplicaciones/index.js') ?>"></script>
