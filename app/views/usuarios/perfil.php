<div class="container-fluid px-4 pt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 text-gray-800 mb-0">Mi Perfil</h2>
    </div>

    <?php if (isset($_SESSION['error']) || isset($_SESSION['error_perfil'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php 
            $err = $_SESSION['error'] ?? $_SESSION['error_perfil'];
            unset($_SESSION['error'], $_SESSION['error_perfil']);
            echo $err; 
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje_perfil'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['mensaje_perfil']; unset($_SESSION['mensaje_perfil']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Información Personal -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Información Personal</h6>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>perfil/update" method="POST">
                        <?php echo Controller::csrfField(); ?>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombres <span class="text-danger">*</span></label>
                                <input type="text" name="nombres" class="form-control" value="<?php echo htmlspecialchars($data['usuario']['nombres']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" name="apellidos" class="form-control" value="<?php echo htmlspecialchars($data['usuario']['apellidos']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($data['usuario']['email'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre de Usuario <small class="text-muted">(No se puede cambiar)</small></label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['usuario']['usuario']); ?>" disabled>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Actualizar Datos</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cambio de Contraseña -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Cambiar Contraseña</h6>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>perfil/updatePassword" method="POST">
                        <?php echo Controller::csrfField(); ?>
                        <div class="mb-3">
                            <label class="form-label">Contraseña Actual <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirmar Nueva Contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                        </div>
                        
                        <button type="submit" class="btn btn-warning">Actualizar Contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
