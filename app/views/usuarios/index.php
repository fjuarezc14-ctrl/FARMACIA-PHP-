<div class="page-content">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger mb-4"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success mb-4"><i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Gestión de Personal</h1>
            <div class="page-subtitle">Administra los usuarios, credenciales y niveles de acceso al sistema.</div>
        </div>
        <button class="btn-primary-custom" style="width: auto; padding: 10px 20px;" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetForm()">
            <i class="bi bi-person-plus"></i> Nuevo Usuario
        </button>
    </div>

    <div class="card-metric">
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="width: 100%; font-size: 14px;">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Nombres y Apellidos</th>
                        <th>Usuario</th>
                        <th>Rol Asignado</th>
                        <th>Último Acceso</th>
                        <th>Estado</th>
                        <th width="160" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['usuarios'])): ?>
                    <tr><td colspan="7" class="text-center text-muted">No hay usuarios registrados</td></tr>
                    <?php else: ?>
                    <?php foreach ($data['usuarios'] as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td>
                            <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($u['nombres'] . ' ' . $u['apellidos']); ?></strong><br>
                            <small style="color: var(--text-secondary);"><?php echo htmlspecialchars($u['email'] ?? '-'); ?></small>
                        </td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($u['usuario']); ?></span></td>
                        <td>
                            <?php 
                            $badgeClass = 'bg-info';
                            if($u['rol_id'] == 1) $badgeClass = 'bg-danger';
                            if($u['rol_id'] == 2) $badgeClass = 'bg-success';
                            if($u['rol_id'] == 3) $badgeClass = 'bg-primary';
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($u['rol_nombre']); ?></span>
                        </td>
                        <td style="color: var(--text-secondary); font-size: 12px;"><?php echo $u['ultimo_login'] ? date('d/m/Y H:i', strtotime($u['ultimo_login'])) : 'Nunca'; ?></td>
                        <td>
                            <?php if($u['estado'] == 1): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm" style="color: #00CFE8;" onclick='editUser(<?php echo json_encode($u); ?>)' title="Editar Usuario">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <?php if($u['id'] != 1): ?>
                                <form action="<?php echo BASE_URL; ?>usuario/toggle" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas <?php echo $u['estado'] ? 'desactivar' : 'activar'; ?> este usuario?');">
                                    <?php echo Controller::csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                    <button type="submit" class="btn btn-sm" style="color: <?php echo $u['estado'] ? 'var(--warning)' : 'var(--success)'; ?>;" title="<?php echo $u['estado'] ? 'Desactivar acceso' : 'Activar acceso'; ?>">
                                        <i class="bi bi-power"></i>
                                    </button>
                                </form>
                                <?php if($u['id'] != ($_SESSION['user_id'] ?? 0)): ?>
                                <form action="<?php echo BASE_URL; ?>usuario/delete" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar definitivamente a este usuario?\n\nAdvertencia: Esta acción es irreversible.');">
                                    <?php echo Controller::csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                    <button type="submit" class="btn btn-sm" style="color: var(--danger);" title="Eliminar Usuario">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <form action="<?php echo BASE_URL; ?>usuario/save" method="POST" class="modal-content" style="background-color: var(--bg-card); border: 1px solid var(--border-color);">
            <?php echo Controller::csrfField(); ?>
            <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
                <h5 class="modal-title" id="modalTitle" style="color: var(--text-primary); font-size: 16px; font-weight:700;">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="userId">
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6 form-group">
                        <label class="form-label">Nombres <span class="text-danger">*</span></label>
                        <input type="text" name="nombres" id="nombres" class="form-control-custom" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="form-label">Apellidos <span class="text-danger">*</span></label>
                        <input type="text" name="apellidos" id="apellidos" class="form-control-custom" required>
                    </div>
                </div>
                
                <div class="form-group mb-3">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="email" id="email" class="form-control-custom">
                </div>
                
                <div class="row g-3 mb-3">
                    <div class="col-md-6 form-group">
                        <label class="form-label">Nombre de Usuario <span class="text-danger">*</span></label>
                        <input type="text" name="usuario" id="usuario" class="form-control-custom" required>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="form-label">Rol <span class="text-danger">*</span></label>
                        <select name="rol_id" id="rol_id" class="form-control-custom" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['roles'] as $r): ?>
                                <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group mb-2">
                    <label class="form-label">Contraseña <span id="passReq" class="text-danger">*</span></label>
                    <input type="password" name="password" id="password" class="form-control-custom" placeholder="••••••••">
                    <small style="color: var(--text-secondary); font-size: 11px; display:none;" id="passHelp">Deje en blanco para mantener la contraseña actual.</small>
                </div>
                
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-primary-custom" style="width: auto; padding: 8px 20px;">Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('modalTitle').innerText = 'Nuevo Usuario';
    document.getElementById('userId').value = '';
    document.getElementById('nombres').value = '';
    document.getElementById('apellidos').value = '';
    document.getElementById('email').value = '';
    document.getElementById('usuario').value = '';
    document.getElementById('rol_id').value = '';
    document.getElementById('password').value = '';
    document.getElementById('password').required = true;
    document.getElementById('passReq').style.display = 'inline';
    document.getElementById('passHelp').style.display = 'none';
}

function editUser(user) {
    document.getElementById('modalTitle').innerText = 'Editar Usuario';
    document.getElementById('userId').value = user.id;
    document.getElementById('nombres').value = user.nombres;
    document.getElementById('apellidos').value = user.apellidos;
    document.getElementById('email').value = user.email;
    document.getElementById('usuario').value = user.usuario;
    document.getElementById('rol_id').value = user.rol_id;
    document.getElementById('password').value = '';
    
    // Al editar, la contraseña no es obligatoria
    document.getElementById('password').required = false;
    document.getElementById('passReq').style.display = 'none';
    document.getElementById('passHelp').style.display = 'block';
    
    new bootstrap.Modal(document.getElementById('userModal')).show();
}
</script>
