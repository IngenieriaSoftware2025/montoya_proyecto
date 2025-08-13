<style>
    /* Variables para tema urbano cemento - EXACTO AL EJEMPLO ✅ */
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

    .btn-success {
        background: linear-gradient(135deg, var(--accent-success) 0%, var(--urban-green) 100%) !important;
        color: white !important;
        box-shadow: 0 2px 8px rgba(5, 150, 105, 0.3);
    }

    .btn-warning {
        background: linear-gradient(135deg, var(--accent-warning) 0%, var(--industrial-orange) 100%) !important;
        color: white !important;
        box-shadow: 0 2px 8px rgba(217, 119, 6, 0.3);
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--accent-danger) 0%, #b91c1c 100%) !important;
        color: white !important;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
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
    .text-success { color: var(--accent-success) !important; }
    .text-warning { color: var(--accent-warning) !important; }
    .text-danger { color: var(--accent-danger) !important; }
    .text-info { color: #0891b2 !important; }
    .text-muted { color: var(--text-muted) !important; }

    .bg-success { background-color: var(--accent-success) !important; }
    .bg-warning { background-color: var(--accent-warning) !important; }
    .bg-danger { background-color: var(--accent-danger) !important; }
    .bg-info { background-color: #0891b2 !important; }

    .badge {
        border-radius: 8px !important;
        font-size: 0.75rem !important;
        padding: 0.4rem 0.8rem !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
    }

    .badge.bg-success {
        background: linear-gradient(135deg, var(--accent-success) 0%, var(--urban-green) 100%) !important;
        color: white !important;
        box-shadow: 0 2px 8px rgba(5, 150, 105, 0.3);
    }

    .badge.bg-warning {
        background: linear-gradient(135deg, var(--accent-warning) 0%, var(--industrial-orange) 100%) !important;
        color: white !important;
        box-shadow: 0 2px 8px rgba(217, 119, 6, 0.3);
    }

    .badge.bg-danger {
        background: linear-gradient(135deg, var(--accent-danger) 0%, #b91c1c 100%) !important;
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

    .alert-success {
        background-color: rgba(5, 150, 105, 0.1) !important;
        border-color: var(--accent-success) !important;
        color: var(--accent-success) !important;
    }

    .alert-warning {
        background-color: rgba(217, 119, 6, 0.1) !important;
        border-color: var(--accent-warning) !important;
        color: var(--accent-warning) !important;
    }

    .alert-danger {
        background-color: rgba(220, 38, 38, 0.1) !important;
        border-color: var(--accent-danger) !important;
        color: var(--accent-danger) !important;
    }

    .alert-info {
        background-color: rgba(8, 145, 178, 0.1) !important;
        border-color: #0891b2 !important;
        color: #0891b2 !important;
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
        border-left: 6px solid var(--accent-success) !important; 
        position: relative;
    }
    .border-left-success::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        bottom: 0;
        width: 6px;
        background: linear-gradient(180deg, var(--accent-success), var(--urban-green));
    }
    
    .border-left-warning { 
        border-left: 6px solid var(--accent-warning) !important; 
        position: relative;
    }
    .border-left-warning::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        bottom: 0;
        width: 6px;
        background: linear-gradient(180deg, var(--accent-warning), var(--industrial-orange));
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

    /* Específico para tarjetas de visitas */
    .card-visita {
        transition: transform 0.2s;
    }
    
    .card-visita:hover {
        transform: translateY(-2px);
    }
    
    .conformidad-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
    }
    
    .indicator-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-people-fill me-2"></i>
                            Gestión de Visitas
                        </h4>
                        <div class="text-end">
                            <div class="mb-1">
                                <strong id="fecha-actual"><?= date('d/m/Y') ?></strong>
                            </div>
                            <small id="dia-semana"><?= ucfirst(strftime('%A', time())) ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen rápido -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card h-100 text-center border-left-primary">
                <div class="card-body">
                    <div class="h2 text-primary mb-1" id="total-visitas">0</div>
                    <div class="text-muted">Total Visitas</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 text-center border-left-success">
                <div class="card-body">
                    <div class="h2 text-success mb-1" id="visitas-conformes">0</div>
                    <div class="text-muted">Conformes</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 text-center border-left-warning">
                <div class="card-body">
                    <div class="h2 text-warning mb-1" id="visitas-no-conformes">0</div>
                    <div class="text-muted">No Conformes</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 text-center border-left-info">
                <div class="card-body">
                    <div class="h2 text-info mb-1" id="visitas-mes">0</div>
                    <div class="text-muted">Este Mes</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna principal: Lista de visitas -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul me-2"></i>
                            Registro de Visitas
                        </h5>
                        <div>
                            <button class="btn btn-success btn-sm me-2" onclick="abrirModalNuevaVisita()">
                                <i class="bi bi-plus-lg me-1"></i>
                                Nueva Visita
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="cargarVisitas()">
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Actualizar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="filtro-aplicacion" class="form-label">Aplicación</label>
                            <select class="form-select" id="filtro-aplicacion" onchange="aplicarFiltros()">
                                <option value="">Todas las aplicaciones</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtro-conformidad" class="form-label">Conformidad</label>
                            <select class="form-select" id="filtro-conformidad" onchange="aplicarFiltros()">
                                <option value="">Todas</option>
                                <option value="true">Conformes</option>
                                <option value="false">No Conformes</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filtro-fecha-inicio" class="form-label">Desde</label>
                            <input type="date" class="form-control" id="filtro-fecha-inicio" onchange="aplicarFiltros()">
                        </div>
                        <div class="col-md-2">
                            <label for="filtro-fecha-fin" class="form-label">Hasta</label>
                            <input type="date" class="form-control" id="filtro-fecha-fin" onchange="aplicarFiltros()">
                        </div>
                    </div>

                    <div id="contenedor-visitas">
                        <!-- Las visitas se cargarán aquí dinámicamente -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <div class="mt-2">Cargando visitas...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna lateral: Estadísticas y acciones rápidas -->
        <div class="col-lg-4">
            <!-- Estadísticas -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-pie-chart me-2"></i>
                        Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Tasa de Conformidad</small>
                            <small class="fw-bold" id="tasa-conformidad">0%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" id="barra-conformidad" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="row text-center small">
                        <div class="col-6">
                            <div class="text-success fw-bold" id="stat-conformes">0</div>
                            <div class="text-muted">Conformes</div>
                        </div>
                        <div class="col-6">
                            <div class="text-warning fw-bold" id="stat-no-conformes">0</div>
                            <div class="text-muted">No Conformes</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-lightning-charge me-2"></i>
                        Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="abrirModalNuevaVisita()">
                            <i class="bi bi-plus-lg me-2"></i>
                            Registrar Visita
                        </button>
                        <button class="btn btn-outline-info" onclick="verEstadisticasDetalladas()">
                            <i class="bi bi-bar-chart me-2"></i>
                            Ver Estadísticas
                        </button>
                        <button class="btn btn-outline-warning" onclick="verVisitasNoConformes()">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Visitas No Conformes
                        </button>
                    </div>

                    <hr>

                    <div class="mt-3">
                        <h6 class="text-muted mb-2">Leyenda</h6>
                        <div class="small">
                            <div class="mb-1">
                                <span class="indicator-dot bg-success"></span>
                                Conforme
                            </div>
                            <div class="mb-1">
                                <span class="indicator-dot bg-warning"></span>
                                No Conforme
                            </div>
                            <div class="mb-1">
                                <span class="indicator-dot bg-info"></span>
                                Con Solución
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Nueva/Editar Visita -->
<div class="modal fade" id="modalVisita" tabindex="-1" aria-labelledby="modalVisitaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalVisitaLabel">
                    <i class="bi bi-people-fill me-2"></i>
                    Registrar Visita
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formVisita">
                <div class="modal-body">
                    <!-- CAMPOS OCULTOS -->
                    <input type="hidden" id="visita_id" name="vis_id">
                    <input type="hidden" id="visita_creado_por" name="vis_creado_por" value="1">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="visita_aplicacion" class="form-label fw-bold">
                                Aplicación <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="visita_aplicacion" name="vis_apl_id" required>
                                <option value="">Seleccione una aplicación...</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="visita_fecha" class="form-label fw-bold">Fecha</label>
                            <input type="date" class="form-control" id="visita_fecha" name="vis_fecha" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="visita_hora" class="form-label fw-bold">Hora</label>
                            <input type="time" class="form-control" id="visita_hora" name="vis_hora" value="<?= date('H:i') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="visita_quien" class="form-label fw-bold">
                            ¿Quién realizó la visita? <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="visita_quien" name="vis_quien" 
                               placeholder="Ej: Coronel Juan Pérez" required>
                        <div class="form-text">Nombre y grado de la persona que visitó</div>
                    </div>

                    <div class="mb-3">
                        <label for="visita_motivo" class="form-label fw-bold">
                            Motivo de la Visita <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="visita_motivo" name="vis_motivo" rows="3" 
                                  placeholder="Describa el motivo o propósito de la visita..." required></textarea>
                        <div class="form-text">Mínimo 10 caracteres</div>
                    </div>

                    <div class="mb-3">
                        <label for="visita_procedimiento" class="form-label fw-bold">
                            Procedimiento Realizado <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="visita_procedimiento" name="vis_procedimiento" rows="3" 
                                  placeholder="Describa qué se hizo durante la visita..." required></textarea>
                        <div class="form-text">Mínimo 10 caracteres</div>
                    </div>

                    <div class="mb-3">
                        <label for="visita_solucion" class="form-label fw-bold">
                            Solución Propuesta/Realizada
                        </label>
                        <textarea class="form-control" id="visita_solucion" name="vis_solucion" rows="2" 
                                  placeholder="Describa qué soluciones se propusieron o implementaron (opcional)"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="visita_observacion" class="form-label fw-bold">
                            Observaciones Adicionales
                        </label>
                        <textarea class="form-control" id="visita_observacion" name="vis_observacion" rows="2" 
                                  placeholder="Cualquier observación adicional relevante (opcional)"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            ¿La visita fue conforme? <span class="text-danger">*</span>
                        </label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="vis_conformidad" id="conformidad_si" value="t" checked>
                            <label class="form-check-label text-success" for="conformidad_si">
                                <i class="bi bi-check-circle me-1"></i>
                                Sí, conforme
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="vis_conformidad" id="conformidad_no" value="f">
                            <label class="form-check-label text-warning" for="conformidad_no">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                No, no conforme
                            </label>
                        </div>
                        <div class="form-text">Indique si la visita fue satisfactoria y cumplió con las expectativas</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarVisita">
                        <i class="bi bi-floppy me-1"></i>
                        Guardar Visita
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/visitas/index.js') ?>"></script>