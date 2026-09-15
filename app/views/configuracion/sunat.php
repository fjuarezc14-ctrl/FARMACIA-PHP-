<style>
/* ── SUNAT PREMIUM DESIGN (LIGHT THEME) ─────────────────────────── */
.sunat-hero { background: linear-gradient(135deg, #1e293b 0%, #334155 60%, #0f172a 100%); border-radius: 20px; padding: 32px 36px; margin-bottom: 28px; position: relative; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
.sunat-hero::before { content: ''; position: absolute; top: -60px; right: -60px; width: 220px; height: 220px; border-radius: 50%; background: radial-gradient(circle, rgba(99,102,241,0.2), transparent 70%); }
.sunat-hero::after { content: 'SUNAT'; position: absolute; right: 36px; bottom: -10px; font-size: 90px; font-weight: 900; color: rgba(255,255,255,0.03); letter-spacing: -4px; pointer-events: none; }
.sunat-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; border-radius: 999px; font-size: 12px; font-weight: 800; letter-spacing: 0.5px; border: 1px solid; }
.pill-off { background: #fee2e2; color: #ef4444; border-color: #fca5a5; }
.pill-beta { background: #fef3c7; color: #d97706; border-color: #fcd34d; }
.pill-prod { background: #d1fae5; color: #059669; border-color: #6ee7b7; }

.s-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 28px 32px; margin-bottom: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); transition: border-color 0.3s, box-shadow 0.3s; }
.s-card:hover { border-color: #cbd5e1; box-shadow: 0 8px 25px rgba(0,0,0,0.04); }
.s-card-header { display: flex; align-items: center; gap: 14px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; }
.s-card-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
.s-card-title { font-size: 15px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #1e293b; }
.s-card-sub { font-size: 13px; color: #64748b; margin-top: 2px; }

.s-label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #475569; margin-bottom: 8px; }
.s-label em { color: #ef4444; font-style: normal; }
.s-input { width: 100%; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; color: #1e293b; padding: 13px 18px; font-size: 14px; font-family: 'Courier New', monospace; transition: all 0.2s; font-weight: 600; }
.s-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99,102,241,0.1); background: #ffffff; }
.s-input::placeholder { color: #94a3b8; font-weight: 400; }

/* Modo toggle */
.mode-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 8px; }
.mode-card { border: 2px solid #e2e8f0; border-radius: 16px; padding: 22px; cursor: pointer; transition: all 0.25s; background: #f8fafc; text-align: left; width: 100%; }
.mode-card:hover { border-color: #cbd5e1; background: #ffffff; }
.mode-card.sel-beta { border-color: #f59e0b; background: #fffbeb; box-shadow: 0 4px 15px rgba(245,158,11,0.1); }
.mode-card.sel-prod { border-color: #10b981; background: #ecfdf5; box-shadow: 0 4px 15px rgba(16,185,129,0.1); }
.mode-card-icon { font-size: 28px; margin-bottom: 12px; }
.mode-card-title { font-size: 16px; font-weight: 800; color: #1e293b; margin-bottom: 6px; }
.mode-card-desc { font-size: 13px; color: #475569; line-height: 1.5; }

/* Toggle switch */
.s-toggle-row { display: flex; align-items: center; gap: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; margin-top: 20px; }
.s-toggle-info { flex: 1; }
.s-toggle-info strong { display: block; font-size: 14px; color: #1e293b; margin-bottom: 4px; font-weight: 700; }
.s-toggle-info small { color: #64748b; font-size: 13px; }
.form-check-input:checked { background-color: #6366f1; border-color: #6366f1; }

/* Info box */
.s-info { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 18px 22px; font-size: 13.5px; color: #1e3a8a; line-height: 1.6; }
.s-info strong { color: #1d4ed8; }
.s-info code { background: #dbeafe; padding: 3px 8px; border-radius: 6px; font-size: 12px; color: #1e40af; font-family: monospace; font-weight: 600; }
.s-warn { background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 18px 22px; font-size: 13.5px; color: #991b1b; line-height: 1.6; }
.s-warn strong { color: #b91c1c; }

/* Password wrapper */
.pwd-wrap { position: relative; }
.pwd-wrap .s-input { padding-right: 52px; }
.pwd-btn { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer; font-size: 18px; transition: color 0.2s; }
.pwd-btn:hover { color: #334155; }

/* File upload */
.s-file { display: flex; align-items: center; justify-content: center; flex-direction: column; gap: 10px; border: 2px dashed #cbd5e1; border-radius: 16px; padding: 32px; cursor: pointer; transition: all 0.25s; background: #f8fafc; }
.s-file:hover { border-color: #818cf8; background: #eef2ff; }
.s-file input { display: none; }
.s-file-icon { font-size: 36px; color: #6366f1; }
.s-file-text { font-size: 14px; color: #475569; text-align: center; }
.s-file-text strong { color: #4f46e5; font-weight: 700; display: block; margin-bottom: 6px; font-size: 15px; }

/* Status bar at top */
.status-bar { display: flex; align-items: center; gap: 10px; }
.status-dot { width: 12px; height: 12px; border-radius: 50%; animation: pulse 2s infinite; }
.status-dot.off { background: #ef4444; animation: none; }
.status-dot.beta { background: #f59e0b; }
.status-dot.prod { background: #10b981; }
@keyframes pulse { 0%, 100% { opacity: 1 } 50% { opacity: 0.4 } }

/* Action buttons */
.s-btn-test { background: #eef2ff; border: 1px solid #c7d2fe; color: #4f46e5; border-radius: 12px; padding: 14px 28px; font-weight: 700; font-size: 15px; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; }
.s-btn-test:hover { background: #e0e7ff; border-color: #818cf8; }
.s-btn-save { background: linear-gradient(135deg, #6366f1, #4f46e5); border: none; color: #fff; border-radius: 12px; padding: 14px 38px; font-weight: 800; font-size: 15px; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(99,102,241,0.3); }
.s-btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(99,102,241,0.4); }
@media(max-width:768px) { .mode-grid { grid-template-columns: 1fr; } .sunat-hero::after { display: none; } }
</style>

<?php
$habilitado = ($data['configs']['sunat_habilitado']['valor'] ?? '0') === '1';
$modo       = $data['configs']['sunat_modo']['valor'] ?? 'beta';
$solUser    = $data['configs']['sunat_sol_usuario']['valor'] ?? '';
$certPath   = $data['configs']['sunat_cert_path']['valor'] ?? '';
$urlBeta    = $data['configs']['sunat_url_beta']['valor'] ?? 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';
$urlProd    = $data['configs']['sunat_url_produccion']['valor'] ?? 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService';
?>

<div class="page-content">

<!-- ░░ HERO HEADER ░░ -->
<div class="sunat-hero">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-12 mb-2">
                <i class="bi bi-shield-lock-fill" style="font-size:28px;color:#818cf8;margin-right:10px;"></i>
                <h1 style="font-size:26px;font-weight:900;color:#f1f5f9;margin:0;letter-spacing:-0.5px;">Facturación Electrónica</h1>
            </div>
            <p style="color:#94a3b8;font-size:14px;margin:6px 0 16px 38px;">Motor de emisión de comprobantes electrónicos · Estándar SUNAT UBL 2.1</p>
            <div class="status-bar">
                <div class="status-dot <?php echo $habilitado ? ($modo==='produccion'?'prod':'beta') : 'off'; ?>"></div>
                <span style="font-size:13px;color:#cbd5e1;font-weight:600;">
                    <?php if($habilitado): ?>
                        Conectado en modo <strong style="color:<?php echo $modo==='produccion'?'#34d399':'#fbbf24';?>"><?php echo strtoupper($modo);?></strong>
                    <?php else: ?>
                        <span style="color:#f87171;">Desconectado — solo generación local de XML</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <?php if($habilitado): ?>
            <span class="sunat-pill <?php echo $modo==='produccion'?'pill-prod':'pill-beta';?>">
                <i class="bi bi-<?php echo $modo==='produccion'?'rocket-takeoff-fill':'bug-fill';?>"></i>
                <?php echo strtoupper($modo);?>
            </span>
        <?php else: ?>
            <span class="sunat-pill pill-off"><i class="bi bi-wifi-off"></i> DESCONECTADO</span>
        <?php endif; ?>
        
        <!-- Botón del Manual -->
        <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#manualSunatModal" style="border-radius: 8px; font-weight: 600; padding: 6px 14px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
            <i class="bi bi-book-half text-info"></i> Ver Manual Técnico
        </button>
    </div>
</div>

<!-- Alertas -->
<?php if(isset($_SESSION['mensaje'])): ?>
<div style="background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#34d399;border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
    <i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?>
</div>
<?php endif; ?>
<?php if(isset($_SESSION['error'])): ?>
<div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171;border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
</div>
<?php endif; ?>

<form action="<?php echo BASE_URL; ?>configuracion/saveSunat" method="POST" enctype="multipart/form-data">

<!-- ░░ BLOQUE 1: MODO ░░ -->
<div class="s-card">
    <div class="s-card-header">
        <div class="s-card-icon" style="background:rgba(245,158,11,.12);color:#f59e0b;"><i class="bi bi-toggles2"></i></div>
        <div>
            <div class="s-card-title">Modo de Operación</div>
            <div class="s-card-sub">Define si emites comprobantes de prueba o con validez fiscal real</div>
        </div>
    </div>

    <input type="hidden" name="sunat_modo" id="inputModo" value="<?php echo $modo;?>">
    <div class="mode-grid mb-4">
        <button type="button" id="btnBeta" onclick="setModo('beta')" class="mode-card <?php echo $modo==='beta'?'sel-beta':'';?>">
            <div class="mode-card-icon">🧪</div>
            <div class="mode-card-title">BETA — Pruebas</div>
            <div class="mode-card-desc">Comprobantes <strong style="color:#fbbf24;">sin validez legal</strong>. Ideal para desarrollo y validación. RUC de prueba SUNAT: <code style="background:rgba(255,255,255,.08);padding:1px 5px;border-radius:4px;font-size:11px;">20000000001</code></div>
        </button>
        <button type="button" id="btnProd" onclick="setModo('produccion')" class="mode-card <?php echo $modo==='produccion'?'sel-prod':'';?>">
            <div class="mode-card-icon">🚀</div>
            <div class="mode-card-title">PRODUCCIÓN — Real</div>
            <div class="mode-card-desc">Comprobantes con <strong style="color:#34d399;">validez fiscal real</strong>. Requiere certificado digital (.p12) y Clave SOL activa en SUNAT.</div>
        </button>
    </div>

    <div class="s-toggle-row">
        <div class="s-toggle-info">
            <strong><i class="bi bi-send-fill" style="color:#6366f1;margin-right:6px;"></i> Envío automático a SUNAT</strong>
            <small>Si está OFF, el XML se genera localmente para descarga manual. Si está ON, se enviará automáticamente al completar cada venta.</small>
        </div>
        <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" id="sunat_habilitado" name="sunat_habilitado" value="1"
                <?php echo $habilitado?'checked':''; ?> style="width:52px;height:26px;cursor:pointer;">
        </div>
    </div>
</div>

<!-- ░░ BLOQUE 2: CREDENCIALES SOL ░░ -->
<div class="s-card">
    <div class="s-card-header">
        <div class="s-card-icon" style="background:rgba(99,102,241,.12);color:#818cf8;"><i class="bi bi-key-fill"></i></div>
        <div>
            <div class="s-card-title">Credenciales SOL</div>
            <div class="s-card-sub">SUNAT Operaciones en Línea — Usuario secundario</div>
        </div>
    </div>

    <div class="s-info mb-4">
        <strong>📋 ¿Cómo obtengo el Usuario SOL?</strong><br>
        El formato es: <code>RUC + código de usuario secundario</code>.<br>
        Ejemplo: RUC <code>20123456789</code> + usuario <code>FACTURAS</code> = <code>20123456789FACTURAS</code><br>
        Créalo en: <strong>sunat.gob.pe → SOL → Usuarios Secundarios → Nuevo</strong> con perfil de Facturación Electrónica.
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <label class="s-label">Usuario SOL <em>*</em></label>
            <input type="text" name="sunat_sol_usuario" class="s-input"
                value="<?php echo htmlspecialchars($solUser);?>"
                placeholder="20123456789TUSUARIO" autocomplete="off">
        </div>
        <div class="col-md-6">
            <label class="s-label">Clave SOL <em>*</em></label>
            <div class="pwd-wrap">
                <input type="password" name="sunat_sol_clave" id="solClave" class="s-input"
                    value="<?php echo htmlspecialchars($data['configs']['sunat_sol_clave']['valor']??'');?>"
                    placeholder="••••••••••••" autocomplete="off">
                <button type="button" class="pwd-btn" onclick="togglePwd('solClave',this)">
                    <i class="bi bi-eye-slash"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ░░ BLOQUE 3: CERTIFICADO DIGITAL ░░ -->
<div class="s-card">
    <div class="s-card-header">
        <div class="s-card-icon" style="background:rgba(16,185,129,.1);color:#34d399;"><i class="bi bi-file-earmark-lock-fill"></i></div>
        <div>
            <div class="s-card-title">Certificado Digital (.p12 / .pfx)</div>
            <div class="s-card-sub">Firma digital que otorga validez legal al comprobante electrónico</div>
        </div>
    </div>

    <div class="s-warn mb-4">
        <strong>⚠ Seguridad crítica:</strong> Nunca compartas este archivo ni su contraseña. El certificado lo emiten
        <strong>DigiCert, GlobalSign, Firma Digital del Perú</strong> (costo aprox. S/ 300–500/año).
        Solo es requerido en modo <strong>PRODUCCIÓN</strong>.
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <label class="s-label">Ruta del certificado en el servidor</label>
            <input type="text" name="sunat_cert_path" class="s-input"
                value="<?php echo htmlspecialchars($certPath);?>"
                placeholder="C:/laragon/www/sistema-botica/public/sunat/certs/certificado.p12">
            <?php if(!empty($certPath)&&file_exists($certPath)): ?>
            <div style="margin-top:8px;display:flex;align-items:center;gap:6px;font-size:12px;color:#34d399;">
                <i class="bi bi-check-circle-fill"></i> Certificado encontrado en el servidor
            </div>
            <?php elseif(!empty($certPath)): ?>
            <div style="margin-top:8px;display:flex;align-items:center;gap:6px;font-size:12px;color:#f87171;">
                <i class="bi bi-exclamation-circle-fill"></i> Archivo no encontrado en esa ruta
            </div>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <label class="s-label">Contraseña del .p12</label>
            <div class="pwd-wrap">
                <input type="password" name="sunat_cert_password" id="certPwd" class="s-input"
                    value="<?php echo htmlspecialchars($data['configs']['sunat_cert_password']['valor']??'');?>"
                    placeholder="••••••••">
                <button type="button" class="pwd-btn" onclick="togglePwd('certPwd',this)">
                    <i class="bi bi-eye-slash"></i>
                </button>
            </div>
        </div>
    </div>

    <label class="s-label">Subir certificado al servidor</label>
    <label class="s-file" for="certFileInput" id="dropZone">
        <input type="file" id="certFileInput" name="cert_file" accept=".p12,.pfx" onchange="showFileName(this)">
        <i class="bi bi-cloud-arrow-up-fill s-file-icon"></i>
        <div class="s-file-text">
            <strong style="color:#a5b4fc;display:block;margin-bottom:4px;" id="fileLabel">Arrastra aquí o haz clic para seleccionar</strong>
            Formatos aceptados: <code style="color:#c4b5fd;">.p12</code> / <code style="color:#c4b5fd;">.pfx</code> — se guardará en <code style="color:#c4b5fd;">/public/sunat/certs/</code>
        </div>
    </label>
</div>

<!-- ░░ BLOQUE 4: URLS AVANZADO ░░ -->
<div class="s-card">
    <div class="s-card-header">
        <div class="s-card-icon" style="background:rgba(148,163,184,.08);color:#94a3b8;"><i class="bi bi-globe2"></i></div>
        <div>
            <div class="s-card-title">URLs del Servicio (Avanzado)</div>
            <div class="s-card-sub">Solo modifícalas si tu OSE indica un endpoint diferente al oficial SUNAT</div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-6">
            <label class="s-label">URL WebService BETA</label>
            <input type="text" name="sunat_url_beta" class="s-input" value="<?php echo htmlspecialchars($urlBeta);?>">
        </div>
        <div class="col-md-6">
            <label class="s-label">URL WebService PRODUCCIÓN</label>
            <input type="text" name="sunat_url_produccion" class="s-input" value="<?php echo htmlspecialchars($urlProd);?>">
        </div>
    </div>
</div>

<!-- ░░ ACCIONES ░░ -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-2 mb-4">
    <button type="button" class="s-btn-test" onclick="probarConexion()">
        <i class="bi bi-wifi"></i> Probar Conexión SUNAT
    </button>
    <button type="submit" class="s-btn-save">
        <i class="bi bi-floppy-fill"></i> Guardar Configuración
    </button>
</div>

</form>
</div>

<script>
function setModo(m) {
    document.getElementById('inputModo').value = m;
    document.getElementById('btnBeta').className = 'mode-card' + (m==='beta'?' sel-beta':'');
    document.getElementById('btnProd').className = 'mode-card' + (m==='produccion'?' sel-prod':'');
}
function togglePwd(id, btn) {
    const inp = document.getElementById(id);
    inp.type = inp.type==='password' ? 'text' : 'password';
    btn.querySelector('i').className = inp.type==='password' ? 'bi bi-eye-slash' : 'bi bi-eye-fill';
}
function showFileName(input) {
    const label = document.getElementById('fileLabel');
    if (input.files.length > 0) {
        label.textContent = '✅ ' + input.files[0].name;
        label.style.color = '#34d399';
        document.getElementById('dropZone').style.borderColor = '#10b981';
    }
}
function probarConexion() {
    const btn = event.currentTarget;
    btn.innerHTML = '<span style="display:inline-block;animation:spin .7s linear infinite" class="bi bi-arrow-repeat"></span> Probando...';
    btn.disabled = true;
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-wifi"></i> Probar Conexión SUNAT';
        if(typeof Swal !== 'undefined') {
            Swal.fire({icon:'info',title:'Prueba de Conexión',
                html:'Guarda primero las credenciales y genera una <strong>Boleta de prueba</strong> desde el POS. El sistema intentará enviarla al servidor BETA de SUNAT automáticamente.',
                confirmButtonText:'Entendido',background:'#1e293b',color:'#f1f5f9',confirmButtonColor:'#6366f1'});
        } else { alert('Guarda las credenciales y genera una venta para probar.'); }
    }, 1500);
}
</script>
<style>@keyframes spin{to{transform:rotate(360deg)}}</style>

<!-- Modal del Manual Técnico -->
<div class="modal fade" id="manualSunatModal" tabindex="-1" aria-labelledby="manualSunatModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content" style="background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 12px;">
      <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
        <h5 class="modal-title" id="manualSunatModalLabel" style="color: var(--text-primary); font-weight: 700;">
            <i class="bi bi-book-half text-info me-2"></i> Manual Técnico - Facturación Electrónica SUNAT
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <!-- Iframe que carga el HTML generado -->
        <iframe src="<?php echo BASE_URL; ?>manual_facturacion_sunat.html" style="width: 100%; height: 75vh; border: none; background: white;"></iframe>
      </div>
      <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
        <a href="<?php echo BASE_URL; ?>manual_facturacion_sunat.html" target="_blank" class="btn btn-outline-info">
            <i class="bi bi-box-arrow-up-right"></i> Abrir en Pestaña Nueva
        </a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
