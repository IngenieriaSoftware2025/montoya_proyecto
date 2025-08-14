<?php var_dump($_SESSION) ?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            Dashboard del Desarrollador
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
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="h2 text-primary mb-1" id="total-apps">0</div>
                    <div class="text-muted">Total Aplicaciones</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="h2 text-success mb-1" id="apps-progreso">0</div>
                    <div class="text-muted">En Progreso</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="h2 text-warning mb-1" id="apps-pausadas">0</div>
                    <div class="text-muted">Pausadas</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 text-center">
                <div class="card-body">
                    <div class="h2 text-info mb-1" id="reportes-semana">0</div>
                    <div class="text-muted">Reportes esta semana</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna izquierda: Aplicaciones -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-tasks me-2"></i>
                            Mis Aplicaciones
                        </h5>
                        <button class="btn btn-outline-primary btn-sm" onclick="cargarAplicaciones()">
                            <i class="fas fa-sync-alt me-1"></i>
                            Actualizar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="contenedor-aplicaciones">
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

        <!-- Columna derecha: Calendario y formularios -->
        <div class="col-lg-4">
            <!-- Calendario -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Calendario
                        </h6>
                        <div>
                            <button class="btn btn-outline-secondary btn-sm" onclick="cambiarMes(-1)">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span id="mes-actual" class="mx-2 fw-bold"></span>
                            <button class="btn btn-outline-secondary btn-sm" onclick="cambiarMes(1)">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-2">
                    <div id="calendario">
                        <!-- El calendario se generará aquí -->
                    </div>
                    <div class="mt-3">
                        <div class="row text-center small">
                            <div class="col">
                                <span class="indicator-dot" style="background-color: var(--accent-success);"></span>
                                Con reporte
                            </div>
                            <div class="col">
                                <span class="indicator-dot" style="background-color: var(--accent-warning);"></span>
                                Justificado
                            </div>
                            <div class="col">
                                <span class="indicator-dot" style="background-color: var(--accent-danger);"></span>
                                Sin reporte
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de acción rápida -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Acción Rápida - Hoy
                    </h6>
                </div>
                <div class="card-body">
                    <div id="panel-accion-rapida">
                        <div class="text-center py-3">
                            <div class="text-muted">Selecciona una aplicación para continuar</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Reporte de Avance -->
<div class="modal fade" id="modalReporte" tabindex="-1" aria-labelledby="modalReporteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalReporteLabel">
                    <i class="fas fa-chart-bar me-2"></i>
                    Reporte Diario de Avance
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formReporte">
                <div class="modal-body">
                    <!-- CAMPOS OCULTOS -->
                    <input type="hidden" id="reporte_apl_id" name="ava_apl_id">
                    <input type="hidden" id="reporte_usu_id" name="ava_usu_id" value="4">
                    <input type="hidden" id="reporte_fecha" name="ava_fecha">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Aplicación:</label>
                        <div id="reporte_app_nombre" class="form-control-plaintext border rounded p-2 bg-light"></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="reporte_porcentaje" class="form-label fw-bold">
                                Porcentaje de Avance <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="reporte_porcentaje" name="ava_porcentaje" 
                                       min="0" max="100" required>
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-text">
                                <span id="porcentaje_anterior_info"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha:</label>
                            <div class="form-control-plaintext border rounded p-2 bg-light">
                                <?= date('d/m/Y') ?> (Hoy)
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reporte_resumen" class="form-label fw-bold">
                            Resumen del Trabajo Realizado <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="reporte_resumen" name="ava_resumen" rows="4" 
                                  placeholder="Describe brevemente las actividades realizadas hoy..." required></textarea>
                        <div class="form-text">Mínimo 10 caracteres</div>
                    </div>

                    <div class="mb-3">
                        <label for="reporte_bloqueadores" class="form-label fw-bold">
                            Bloqueadores o Impedimentos
                        </label>
                        <textarea class="form-control" id="reporte_bloqueadores" name="ava_bloqueadores" rows="2" 
                                  placeholder="Describe cualquier bloqueador que esté afectando el avance (opcional)"></textarea>
                    </div>

                    <div class="mb-3" id="contenedor_justificacion" style="display: none;">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Justificación requerida:</strong> El porcentaje actual es menor al anterior.
                        </div>
                        <label for="reporte_justificacion" class="form-label fw-bold">
                            Justificación <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="reporte_justificacion" name="ava_justificacion" rows="3" 
                                  placeholder="Explica por qué el porcentaje de avance disminuyó..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarReporte">
                        <i class="fas fa-save me-1"></i>
                        Guardar Reporte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Justificación de Inactividad -->
<div class="modal fade" id="modalInactividad" tabindex="-1" aria-labelledby="modalInactividadLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalInactividadLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Justificar Inactividad
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formInactividad">
                <div class="modal-body">
                    <!-- CAMPOS OCULTOS -->
                    <input type="hidden" id="inactividad_apl_id" name="ina_apl_id">
                    <input type="hidden" id="inactividad_usu_id" name="ina_usu_id" value="3">
                    <input type="hidden" id="inactividad_fecha" name="ina_fecha">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Aplicación:</label>
                        <div id="inactividad_app_nombre" class="form-control-plaintext border rounded p-2 bg-light"></div>
                    </div>

                    <div class="mb-3">
                        <label for="inactividad_tipo" class="form-label fw-bold">
                            Tipo de Inactividad <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="inactividad_tipo" name="ina_tipo" required>
                            <option value="">Seleccione el tipo...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="inactividad_motivo" class="form-label fw-bold">
                            Motivo Detallado <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="inactividad_motivo" name="ina_motivo" rows="4" 
                                  placeholder="Explica detalladamente el motivo de la inactividad..." required></textarea>
                        <div class="form-text">Mínimo 10 caracteres</div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Importante:</strong> Esta justificación indica que no hubo avance hoy debido a circunstancias específicas.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning" id="btnGuardarInactividad">
                        <i class="fas fa-save me-1"></i>
                        Guardar Justificación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/desarrollador/index.js') ?>"></script>
<link rel="stylesheet" href="<?= asset('build/css/desarrollador/style.css') ?>">