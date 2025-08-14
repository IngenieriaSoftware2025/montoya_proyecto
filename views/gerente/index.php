<div class="container-fluid py-4">
    <!-- Header Ejecutivo -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="executive-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="bi bi-pie-chart-fill me-2"></i>
                                Dashboard Ejecutivo
                            </h4>
                            <small class="text-muted">Sistema de Monitoreo de Aplicaciones</small>
                        </div>
                        <div class="text-end">
                            <div class="mb-1">
                                <strong id="fecha-actual"><?= date('d/m/Y H:i') ?></strong>
                            </div>
                            <small id="ultima-actualizacion" class="text-muted">Última actualización</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas Críticas -->
    <div id="alertas-criticas" class="mb-4"></div>

    <!-- Métricas Principales -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="executive-card metric-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="metric-icon">
                                <i class="bi bi-clipboard-data"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="metric-label">Total Aplicaciones</div>
                            <div class="metric-value" id="total-aplicaciones">0</div>
                            <div class="metric-change text-success" id="aplicaciones-activas">0 activas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="executive-card metric-card success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="metric-icon success">
                                <i class="bi bi-bar-chart-fill"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="metric-label">Cumplimiento Reportes</div>
                            <div class="metric-value" id="cumplimiento-reportes">0%</div>
                            <div class="metric-change text-muted">Últimos 30 días</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="executive-card metric-card info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="metric-icon info">
                                <i class="bi bi-speedometer2"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="metric-label">Velocidad Promedio</div>
                            <div class="metric-value" id="velocidad-promedio">0</div>
                            <div class="metric-change text-muted">pts/día</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="executive-card metric-card warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="metric-icon warning">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="metric-label">Aplicaciones en Riesgo</div>
                            <div class="metric-value" id="aplicaciones-riesgo">0</div>
                            <div class="metric-change text-muted" id="texto-riesgo">Sin reportes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Principales -->
    <div class="row mb-4">
        <!-- Progreso Temporal -->
        <div class="col-lg-8 mb-3">
            <div class="executive-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up me-2 text-primary"></i>
                            Progreso de Aplicaciones
                        </h5>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary active" data-period="30">30D</button>
                            <button type="button" class="btn btn-outline-primary" data-period="7">7D</button>
                            <button type="button" class="btn btn-outline-primary" data-period="1">Hoy</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="chartProgreso"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estados y Distribución -->
        <div class="col-lg-4 mb-3">
            <div class="executive-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-pie-chart me-2 text-primary"></i>
                        Distribución por Estado
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="chartEstados"></canvas>
                    </div>
                    <div class="mt-3" id="leyenda-estados"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla y Panel Lateral -->
    <div class="row">
        <!-- Tabla de Aplicaciones -->
        <div class="col-lg-8">
            <div class="executive-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-table me-2 text-primary"></i>
                            Estado de Aplicaciones
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-executive success btn-sm" onclick="exportarReporte()">
                                <i class="bi bi-download me-1"></i>Exportar
                            </button>
                            <button class="btn btn-executive primary btn-sm" onclick="actualizarDatos()">
                                <i class="bi bi-arrow-clockwise me-1"></i>Actualizar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaAplicaciones" class="table executive-table">
                            <thead>
                                <tr>
                                    <th>Aplicación</th>
                                    <th>Responsable</th>
                                    <th>Estado</th>
                                    <th>Progreso</th>
                                    <th>Velocidad</th>
                                    <th>Último Reporte</th>
                                    <th>Semáforo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAplicacionesBody">
                                <!-- Datos dinámicos -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Actividad Reciente -->
            <div class="executive-card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2 text-primary"></i>
                        Actividad Reciente
                    </h5>
                </div>
                <div class="card-body">
                    <div id="actividad-reciente" style="max-height: 300px; overflow-y: auto;">
                        <!-- Actividad dinámica -->
                    </div>
                </div>
            </div>

            <!-- Top Desarrolladores -->
            <div class="executive-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-trophy-fill me-2 text-primary"></i>
                        Top Desarrolladores
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="chartDesarrolladores"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalles -->
<div class="modal fade" id="modalDetalleApp" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Detalles de Aplicación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="contenido-detalle-app">
                    <!-- Contenido dinámico -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-executive primary" onclick="abrirGestionApp()">
                    <i class="bi bi-gear-fill me-1"></i>Gestionar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Acciones Rápidas -->
<div class="modal fade" id="modalAccionesRapidas" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-lightning-fill me-2"></i>
                    Acciones Rápidas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-executive warning" onclick="pausarAplicacion()">
                        <i class="bi bi-pause-circle-fill me-2"></i>Pausar Aplicación
                    </button>
                    <button class="btn btn-executive info" onclick="enviarMensaje()">
                        <i class="bi bi-chat-dots-fill me-2"></i>Enviar Mensaje
                    </button>
                    <button class="btn btn-executive success" onclick="programarVisita()">
                        <i class="bi bi-calendar-plus-fill me-2"></i>Programar Visita
                    </button>
                    <button class="btn btn-executive primary" onclick="generarReporte()">
                        <i class="bi bi-file-pdf-fill me-2"></i>Generar Reporte
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/gerente/index.js') ?>"></script>
<link rel="stylesheet" href="<?= asset('build/css/gerente/style.css') ?>">