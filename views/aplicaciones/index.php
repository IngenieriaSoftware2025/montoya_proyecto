<style>
    /* Variables para tema urbano cemento */
    :root {
        --bg-primary: #f8fafc;     /* Gris muy claro - base */
        --bg-secondary: #f1f5f9;   /* Gris claro - cards */
        --bg-tertiary: #e2e8f0;    /* Gris medio - inputs */
        --bg-surface: #cbd5e1;     /* Gris superficie */
        
        --text-primary: #1e293b;   /* Texto principal oscuro */
        --text-secondary: #475569; /* Texto secundario */
        --text-muted: #64748b;     /* Texto atenuado */
        
        --accent-primary: #3b82f6; /* Azul para acciones principales */
        --accent-success: #059669; /* Verde esmeralda */
        --accent-warning: #d97706; /* Naranja tierra */
        --accent-danger: #dc2626;  /* Rojo ladrillo */
        
        --border-color: #d1d5db;   /* Bordes suaves */
        --shadow-color: rgba(0, 0, 0, 0.08); /* Sombras suaves */
        
        /* Colores industriales específicos */
        --concrete-light: #f7f8fc;
        --concrete-medium: #e5e7eb;
        --concrete-dark: #9ca3af;
        --steel-blue: #64748b;
        --industrial-orange: #ea580c;
        --urban-green: #16a34a;
    }

    body {
        background: linear-gradient(135deg, var(--bg-primary) 0%, var(--concrete-light) 100%) !important;
        color: var(--text-primary) !important;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        min-height: 100vh;
    }

    .card {
        background: linear-gradient(145deg, var(--bg-secondary) 0%, white 100%) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 16px !important;
        box-shadow: 
            0 4px 6px var(--shadow-color),
            0 1px 3px rgba(0, 0, 0, 0.05),
            inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .card:hover {
        transform: translateY(-4px);
        box-shadow: 
            0 10px 25px var(--shadow-color),
            0 4px 10px rgba(0, 0, 0, 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
    }

    .card-header {
        background: linear-gradient(135deg, var(--concrete-medium) 0%, var(--bg-tertiary) 100%) !important;
        border-bottom: 1px solid var(--border-color) !important;
        border-radius: 16px 16px 0 0 !important;
        color: var(--text-primary) !important;
        padding: 1.5rem;
        position: relative;
    }

    .card-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--accent-primary), var(--urban-green), var(--industrial-orange));
        border-radius: 16px 16px 0 0;
    }

    .card-body {
        background-color: var(--bg-secondary) !important;
        color: var(--text-primary) !important;
        padding: 1.5rem;
    }

    .bg-primary {
        background: linear-gradient(135deg, var(--accent-primary) 0%, #2563eb 100%) !important;
        color: white !important;
    }

    .bg-light {
        background-color: var(--bg-tertiary) !important;
        color: var(--text-primary) !important;
    }

    .btn {
        border-radius: 8px !important;
        font-weight: 500;
        border: none !important;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--accent-primary) 0%, #2563eb 100%) !important;
        color: white !important;
        box-shadow: 
            0 4px 12px rgba(59, 130, 246, 0.3),
            inset 0 1px 0 rgba(255, 255, 255, 0.2);
        border: none !important;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
        transform: translateY(-2px);
        box-shadow: 
            0 6px 16px rgba(59, 130, 246, 0.4),
            inset 0 1px 0 rgba(255, 255, 255, 0.3);
    }

    .btn-outline-primary {
        background: rgba(255, 255, 255, 0.8) !important;
        border: 2px solid var(--accent-primary) !important;
        color: var(--accent-primary) !important;
        backdrop-filter: blur(10px);
    }

    .btn-outline-primary:hover {
        background: var(--accent-primary) !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        transform: translateY(-1px);
    }

    .btn-outline-secondary {
        background: rgba(255, 255, 255, 0.8) !important;
        border: 2px solid var(--concrete-dark) !important;
        color: var(--steel-blue) !important;
        backdrop-filter: blur(10px);
    }

    .btn-outline-secondary:hover {
        background: var(--steel-blue) !important;
        color: white !important;
        transform: translateY(-1px);
    }

    .btn-outline-info {
        background: rgba(255, 255, 255, 0.8) !important;
        border: 2px solid #06b6d4 !important;
        color: #06b6d4 !important;
        backdrop-filter: blur(10px);
    }

    .btn-outline-info:hover {
        background: #06b6d4 !important;
        color: white !important;
        transform: translateY(-1px);
    }

    .btn-outline-warning {
        background: rgba(255, 255, 255, 0.8) !important;
        border: 2px solid var(--industrial-orange) !important;
        color: var(--industrial-orange) !important;
        backdrop-filter: blur(10px);
    }

    .btn-outline-warning:hover {
        background: var(--industrial-orange) !important;
        color: white !important;
        transform: translateY(-1px);
    }

    .btn-outline-danger {
        background: rgba(255, 255, 255, 0.8) !important;
        border: 2px solid var(--accent-danger) !important;
        color: var(--accent-danger) !important;
        backdrop-filter: blur(10px);
    }

    .btn-outline-danger:hover {
        background: var(--accent-danger) !important;
        color: white !important;
        transform: translateY(-1px);
    }

    .text-primary { color: var(--accent-primary) !important; }
    .text-success { color: var(--urban-green) !important; }
    .text-warning { color: var(--industrial-orange) !important; }
    .text-info { color: #0891b2 !important; }
    .text-muted { color: var(--text-muted) !important; }

    .badge {
        border-radius: 8px !important;
        font-size: 0.75rem !important;
        padding: 0.4rem 0.8rem !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
    }

    .badge.bg-success {
        background: linear-gradient(135deg, var(--urban-green) 0%, #16a34a 100%) !important;
        color: white !important;
        box-shadow: 0 2px 8px rgba(22, 163, 74, 0.3);
    }

    .badge.bg-warning {
        background: linear-gradient(135deg, var(--industrial-orange) 0%, #ea580c 100%) !important;
        color: white !important;
        box-shadow: 0 2px 8px rgba(234, 88, 12, 0.3);
    }

    .badge.bg-danger {
        background: linear-gradient(135deg, var(--accent-danger) 0%, #dc2626 100%) !important;
        color: white !important;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
    }

    .badge.bg-info {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%) !important;
        color: white !important;
        box-shadow: 0 2px 8px rgba(8, 145, 178, 0.3);
    }

    .badge.bg-secondary {
        background: linear-gradient(135deg, var(--concrete-dark) 0%, var(--steel-blue) 100%) !important;
        color: white !important;
        box-shadow: 0 2px 8px rgba(100, 116, 139, 0.3);
    }

    .progress {
        background: linear-gradient(90deg, var(--concrete-medium) 0%, var(--bg-tertiary) 100%) !important;
        border-radius: 10px !important;
        height: 20px !important;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .progress-bar {
        background: linear-gradient(90deg, var(--urban-green) 0%, #059669 50%, var(--accent-primary) 100%) !important;
        transition: width 0.6s ease;
        border-radius: 8px;
        position: relative;
        overflow: hidden;
    }

    .progress-bar::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        animation: shine 2s infinite;
    }

    @keyframes shine {
        0% { left: -100%; }
        100% { left: 100%; }
    }

    .progress-bar.bg-success {
        background: linear-gradient(90deg, var(--urban-green) 0%, #16a34a 100%) !important;
    }

    .progress-bar.bg-warning {
        background: linear-gradient(90deg, var(--industrial-orange) 0%, #ea580c 100%) !important;
    }

    .progress-bar.bg-danger {
        background: linear-gradient(90deg, var(--accent-danger) 0%, #dc2626 100%) !important;
    }

    .table {
        color: var(--text-primary) !important;
        background: rgba(255, 255, 255, 0.8) !important;
        backdrop-filter: blur(10px);
    }

    .table th {
        background: linear-gradient(135deg, var(--concrete-medium) 0%, var(--bg-tertiary) 100%) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        font-size: 0.85rem !important;
    }

    .table td {
        border-color: var(--border-color) !important;
        background: rgba(255, 255, 255, 0.9) !important;
        vertical-align: middle !important;
    }

    .table-bordered {
        border: 2px solid var(--border-color) !important;
        border-radius: 12px !important;
        overflow: hidden;
    }

    .spinner-border {
        color: var(--accent-primary) !important;
    }

    .modal-content {
        background-color: var(--bg-secondary) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 12px !important;
    }

    .modal-header {
        background-color: var(--bg-tertiary) !important;
        border-bottom: 1px solid var(--border-color) !important;
        color: var(--text-primary) !important;
    }

    .modal-body {
        background-color: var(--bg-secondary) !important;
        color: var(--text-primary) !important;
    }

    .modal-footer {
        background-color: var(--bg-secondary) !important;
        border-top: 1px solid var(--border-color) !important;
    }

    .form-control, .form-select {
        background: rgba(255, 255, 255, 0.9) !important;
        border: 2px solid var(--border-color) !important;
        color: var(--text-primary) !important;
        border-radius: 10px !important;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        background: white !important;
        border-color: var(--accent-primary) !important;
        box-shadow: 
            0 0 0 4px rgba(59, 130, 246, 0.1),
            0 4px 12px rgba(59, 130, 246, 0.15) !important;
        color: var(--text-primary) !important;
        transform: translateY(-1px);
    }

    .form-control::placeholder {
        color: var(--text-muted) !important;
        opacity: 0.7;
    }

    .form-label {
        color: var(--text-secondary) !important;
        font-weight: 500;
    }

    .border-left-primary { 
        border-left: 6px solid var(--accent-primary) !important; 
        position: relative;
    }
    .border-left-primary::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        bottom: 0;
        width: 6px;
        background: linear-gradient(180deg, var(--accent-primary), #2563eb);
    }
    
    .border-left-success { 
        border-left: 6px solid var(--urban-green) !important; 
        position: relative;
    }
    .border-left-success::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        bottom: 0;
        width: 6px;
        background: linear-gradient(180deg, var(--urban-green), #16a34a);
    }
    
    .border-left-warning { 
        border-left: 6px solid var(--industrial-orange) !important; 
        position: relative;
    }
    .border-left-warning::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        bottom: 0;
        width: 6px;
        background: linear-gradient(180deg, var(--industrial-orange), #ea580c);
    }
    
    .border-left-info { 
        border-left: 6px solid #0891b2 !important; 
        position: relative;
    }
    .border-left-info::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        bottom: 0;
        width: 6px;
        background: linear-gradient(180deg, #0891b2, #0e7490);
    }

    .h5 {
        color: var(--text-primary) !important;
    }

    .text-gray-800 {
        color: var(--text-primary) !important;
    }

    .text-xs {
        font-size: 0.75rem !important;
    }

    .font-weight-bold {
        font-weight: 600 !important;
    }

    .no-gutters {
        margin-right: 0;
        margin-left: 0;
    }

    .no-gutters > .col {
        padding-right: 0;
        padding-left: 0;
    }

    .visually-hidden {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }
</style>

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

<script src="<?= asset('build/js/aplicaciones/index.js') ?>"></script>