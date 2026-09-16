<div class="page-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Base de Datos y Sistema</h1>
            <div class="page-subtitle">Gestiona copias de seguridad, restaura información o formatea el sistema.</div>
        </div>
    </div>

    <!-- Alertas -->
    <?php if (isset($data['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($data['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($data['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($data['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        
        <!-- CARD 1: BACKUP -->
        <div class="col-md-4">
            <div class="card-metric h-100 d-flex flex-column text-center" style="border-top: 4px solid #3498DB;">
                <div class="mb-3">
                    <div class="metric-icon mx-auto" style="background-color: rgba(52, 152, 219, 0.1); color: #3498DB; width: 64px; height: 64px; font-size: 32px;">
                        <i class="bi bi-cloud-arrow-down-fill"></i>
                    </div>
                </div>
                <h4 style="font-weight: 700; color: var(--text-primary);">Copia de Seguridad</h4>
                <p style="color: var(--text-secondary); font-size: 14px; flex-grow: 1;">
                    Genera y descarga un archivo <code>.sql</code> con toda la información, ventas y configuración actual de la base de datos.
                </p>
                <div class="mt-4">
                    <a href="<?php echo BASE_URL; ?>sistema/backup" class="btn-primary-custom text-decoration-none d-inline-block" style="background: linear-gradient(135deg, #3498DB, #2980B9); width: 100%;">
                        <i class="bi bi-download me-2"></i> Generar Backup
                    </a>
                </div>
            </div>
        </div>

        <!-- CARD 2: RESTORE -->
        <div class="col-md-4">
            <div class="card-metric h-100 d-flex flex-column text-center" style="border-top: 4px solid #F39C12;">
                <div class="mb-3">
                    <div class="metric-icon mx-auto" style="background-color: rgba(243, 156, 18, 0.1); color: #F39C12; width: 64px; height: 64px; font-size: 32px;">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                    </div>
                </div>
                <h4 style="font-weight: 700; color: var(--text-primary);">Restaurar Sistema</h4>
                <p style="color: var(--text-secondary); font-size: 14px; flex-grow: 1;">
                    Sube un archivo <code>.sql</code> previamente descargado para sobreescribir la base de datos actual.
                </p>
                
                <form action="<?php echo BASE_URL; ?>sistema/restaurar" method="POST" enctype="multipart/form-data" id="form-restore" class="mt-3">
                    <?php echo Controller::csrfField(); ?>
                    <input type="file" name="backup_file" id="backup_file" accept=".sql" class="d-none" required onchange="updateFileName()">
                    
                    <button type="button" class="btn btn-outline-secondary mb-3 w-100" onclick="document.getElementById('backup_file').click();" style="border-radius: 12px; border-style: dashed; padding: 12px;">
                        <i class="bi bi-file-earmark-arrow-up"></i> <span id="file-name-label">Seleccionar Archivo .sql</span>
                    </button>

                    <button type="button" class="btn-primary-custom w-100" style="background: linear-gradient(135deg, #F39C12, #D68910);" onclick="confirmRestore()">
                        <i class="bi bi-arrow-repeat me-2"></i> Subir y Restaurar
                    </button>
                </form>
            </div>
        </div>

        <!-- CARD 3: RESET -->
        <div class="col-md-4">
            <div class="card-metric h-100 d-flex flex-column text-center" style="border-top: 4px solid #E74C3C; background-color: #FFF9F9;">
                <div class="mb-3">
                    <div class="metric-icon mx-auto" style="background-color: rgba(231, 76, 60, 0.1); color: #E74C3C; width: 64px; height: 64px; font-size: 32px;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
                <h4 style="font-weight: 700; color: var(--danger);">Reset de Fábrica</h4>
                <p style="color: var(--text-secondary); font-size: 14px; flex-grow: 1;">
                    Vacía las tablas transaccionales de la botica para empezar desde cero, manteniendo la cuenta actual de Administrador.
                </p>
                
                <form action="<?php echo BASE_URL; ?>sistema/reset" method="POST" id="form-reset" class="mt-3 text-start">
                    <?php echo Controller::csrfField(); ?>
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 13px; font-weight: 600;">Alcance del Purgado:</label>
                        <select name="tipo_reset" class="form-control-custom" style="padding: 10px 15px; font-size: 13px;">
                            <option value="transacciones">Solo Transacciones (Conserva Productos/Clientes)</option>
                            <option value="todo">Limpieza Total (Nueva Empresa)</option>
                        </select>
                    </div>

                    <button type="button" class="btn-primary-custom w-100 mt-2" style="background: linear-gradient(135deg, #E74C3C, #C0392B); box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);" onclick="confirmReset()">
                        <i class="bi bi-trash3-fill me-2"></i> Ejecutar Reseteo
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function updateFileName() {
        const fileInput = document.getElementById('backup_file');
        const label = document.getElementById('file-name-label');
        if (fileInput.files.length > 0) {
            label.textContent = fileInput.files[0].name;
            label.style.fontWeight = 'bold';
            label.style.color = 'var(--text-primary)';
        }
    }

    function confirmRestore() {
        const fileInput = document.getElementById('backup_file');
        if (fileInput.files.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Falta Archivo',
                text: 'Por favor, selecciona un archivo .sql antes de restaurar.',
                confirmButtonColor: '#3498DB'
            });
            return;
        }

        Swal.fire({
            title: '¿Restaurar Sistema?',
            text: "Estás a punto de sobreescribir la base de datos actual. Todos los datos no respaldados se perderán.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F39C12',
            cancelButtonColor: '#7F8C8D',
            confirmButtonText: 'Sí, restaurar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-restore').submit();
            }
        });
    }

    function confirmReset() {
        Swal.fire({
            title: '¡PELIGRO: Reseteo de Sistema!',
            html: `
                <p style="color: #E74C3C; font-weight: bold;">Esta acción es IRREVERSIBLE y destructiva.</p>
                <p>Escribe la palabra <strong>CONFIRMAR</strong> en mayúsculas para vaciar la base de datos.</p>
            `,
            icon: 'error',
            input: 'text',
            inputAttributes: {
                autocapitalize: 'off',
                autocomplete: 'off'
            },
            showCancelButton: true,
            confirmButtonColor: '#E74C3C',
            cancelButtonColor: '#7F8C8D',
            confirmButtonText: 'Purgar Sistema',
            cancelButtonText: 'Cancelar',
            preConfirm: (inputValue) => {
                if (inputValue !== 'CONFIRMAR') {
                    Swal.showValidationMessage('Debes escribir CONFIRMAR exactamente.');
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-reset').submit();
            }
        });
    }
</script>
