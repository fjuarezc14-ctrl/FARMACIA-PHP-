<div class="container-fluid px-4 pt-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h2 class="h3 text-gray-800 mb-0"><i class="bi bi-box-arrow-in-right text-success"></i> Apertura de Caja</h2>
            <p class="text-muted mt-2">Ingrese el monto base de sencillo con el que iniciará su turno.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger mb-4"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['ultimo_arqueo_cerrado'])): 
        $arqueoId = $_SESSION['ultimo_arqueo_cerrado']; 
        unset($_SESSION['ultimo_arqueo_cerrado']);
    ?>
        <div class="alert alert-success border-success shadow-sm mb-4 p-3" style="border-radius: 12px; background: #ecfdf5; border-color: #6ee7b7;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 45px; height: 45px; border-radius: 50%; background: #10b981; color: white; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        <i class="bi bi-check2"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading fw-bold mb-0 text-success">¡Turno Cerrado Correctamente!</h5>
                        <div class="text-muted" style="font-size: 13px;">El arqueo de caja <strong>#CAJ-<?php echo str_pad($arqueoId, 6, '0', STR_PAD_LEFT); ?></strong> ha sido guardado exitosamente en el sistema.</div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary fw-bold" onclick="window.open('<?php echo BASE_URL; ?>caja/ticket_arqueo/<?php echo $arqueoId; ?>', 'TicketArqueo', 'width=420,height=650,scrollbars=yes')">
                        <i class="bi bi-printer me-1"></i> Imprimir Arqueo
                    </button>
                    <a href="<?php echo BASE_URL; ?>dashboard/index" class="btn btn-outline-secondary">
                        <i class="bi bi-house me-1"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
        <script>
            // Abrir automáticamente el ticket en una ventana emergente sin sacar al usuario de la aplicación
            window.open('<?php echo BASE_URL; ?>caja/ticket_arqueo/<?php echo $arqueoId; ?>', 'TicketArqueo', 'width=420,height=650,scrollbars=yes');
        </script>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-success">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-cash-stack text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 font-weight-bold text-success">Registrar Saldo Inicial</h5>
                    <form action="<?php echo BASE_URL; ?>caja/apertura" method="POST" class="mt-4">
                        <?php echo Controller::csrfField(); ?>
                        <div class="mb-3">
                            <label class="form-label text-start d-block font-weight-bold">Monto Base (S/)</label>
                            <input type="number" step="0.01" min="0" max="10000" name="monto_inicial" class="form-control form-control-lg text-center" style="font-size: 1.5rem; font-weight: bold;" placeholder="0.00" required autofocus>
                            <small class="text-muted text-start d-block mt-2">Monto físico (monedas/billetes) disponible en gaveta al iniciar.</small>
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100 font-weight-bold">
                            <i class="bi bi-check-circle"></i> ABRIR CAJA
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
