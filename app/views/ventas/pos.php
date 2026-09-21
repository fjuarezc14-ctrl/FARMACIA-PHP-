<?php
// Cliente por defecto: Público General (id 1) si existe, si no el primero de la lista
$clientesPos = array_map(function($c) {
    return [
        'id'      => (int)$c['id'],
        'tipo'    => $c['tipo_documento'] ?? 'DOC',
        'doc'     => $c['num_documento'] ?? '',
        'nombres' => $c['nombres'] ?? '',
        'puntos'  => (int)($c['puntos_acumulados'] ?? 0),
    ];
}, $data['clientes']);
$clienteDefault = null;
foreach ($clientesPos as $c) { if ($c['id'] === 1) { $clienteDefault = $c; break; } }
if (!$clienteDefault && !empty($clientesPos)) $clienteDefault = $clientesPos[0];
?>
<style>
/* ============ POS CENGFARMA ============ */
.pos-layout { display: flex; gap: 18px; height: calc(100vh - 120px); min-height: 460px; }
.pos-catalog-col { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; }
.pos-ticket-col { flex: 0 0 430px; display: flex; flex-direction: column; min-width: 0; }
@media (max-width: 1200px) { .pos-layout { height: calc(100vh - 105px); } .pos-ticket-col { flex-basis: 390px; } }
@media (max-height: 768px) {
    .pos-layout { height: calc(100vh - 95px); min-height: 440px; }
    .pos-search input { height: 42px; font-size: 14px; }
    .item-card { min-height: 92px; padding: 10px; }
    .ticket-head { padding: 10px 12px 8px; }
    .ticket-foot { padding: 10px 12px 12px; }
}

.pos-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 14px; box-shadow: 0 2px 10px rgba(26,34,56,0.04); }
.pos-section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: var(--text-secondary); margin-bottom: 6px; }

/* ---- Catálogo ---- */
.pos-search { position: relative; }
.pos-search > i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 18px; color: var(--accent-primary); }
.pos-search input { width: 100%; height: 50px; padding: 0 110px 0 48px; border: 2px solid var(--border-color); border-radius: 12px; font-size: 15px; font-weight: 500; background: var(--bg-card); color: var(--text-primary); outline: none; transition: border-color .15s, box-shadow .15s; }
.pos-search input:focus { border-color: var(--accent-primary); box-shadow: 0 0 0 4px var(--accent-light); }
.pos-search .kbd-hint { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); font-size: 11px; color: var(--text-secondary); }
kbd.pos-kbd { background: #f1f3f5; color: var(--text-secondary); border: 1px solid #dee2e6; border-bottom-width: 2px; border-radius: 4px; padding: 1px 5px; font-size: 10px; font-weight: 700; font-family: inherit; }

.pos-catalog { flex: 1 1 auto; overflow-y: auto; padding: 14px; margin-top: 12px; }
.pos-catalog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 10px; }
.item-card { position: relative; display: flex; flex-direction: column; justify-content: space-between; gap: 8px; min-height: 108px; padding: 12px; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-card); cursor: pointer; transition: border-color .15s, box-shadow .15s, transform .15s; text-align: left; }
.item-card:hover { border-color: var(--accent-primary); box-shadow: 0 4px 14px rgba(4,123,7,.12); transform: translateY(-1px); }
.item-card:active { transform: scale(.98); }
.item-card.disabled { opacity: .5; pointer-events: none; background: #fafafa; }
.item-card .ic-name { font-size: 14px; font-weight: 700; color: var(--text-primary); line-height: 1.25; }
.item-card .ic-meta { font-size: 11px; color: var(--text-secondary); }
.item-card .ic-foot { display: flex; justify-content: space-between; align-items: flex-end; gap: 6px; }
.item-card .ic-price { font-size: 17px; font-weight: 800; color: var(--accent-primary); white-space: nowrap; }
.stock-pill { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 20px; background: var(--success-bg); color: var(--accent-primary); white-space: nowrap; }
.stock-pill.low { background: var(--warning-bg); color: #b45f06; }
.stock-pill.out { background: var(--danger-bg); color: var(--danger); }
.rx-badge { display: inline-block; font-size: 9px; font-weight: 800; padding: 2px 5px; border-radius: 4px; vertical-align: middle; margin-left: 3px; }
.rx-badge.ret { background: var(--danger); color: #fff; }
.rx-badge.sim { background: #ffc107; color: #222; }
.catalog-empty { display: none; text-align: center; padding: 40px 10px; color: var(--text-secondary); }

/* ---- Ticket ---- */
.pos-ticket { display: flex; flex-direction: column; height: 100%; overflow: hidden; }
.ticket-head { padding: 14px 14px 10px; border-bottom: 1px solid var(--border-color); }

.cli-picker { position: relative; }
.cli-box { display: flex; align-items: center; gap: 8px; }
.cli-input-wrap { position: relative; flex: 1; min-width: 0; }
.cli-input-wrap > i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-size: 16px; pointer-events: none; }
.cli-input-wrap input { width: 100%; height: 42px; padding: 0 34px 0 38px; border: 1px solid var(--border-color); border-radius: 10px; font-size: 14px; font-weight: 600; color: var(--text-primary); background: #fff; outline: none; text-overflow: ellipsis; }
.cli-input-wrap input:focus { border-color: var(--accent-primary); box-shadow: 0 0 0 3px var(--accent-light); font-weight: 500; }
.cli-input-wrap .cli-caret { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); pointer-events: none; }
.btn-cli-new { height: 42px; width: 42px; flex: 0 0 42px; border-radius: 10px; border: 1px solid var(--accent-primary); background: var(--accent-light); color: var(--accent-primary); font-size: 18px; display: inline-flex; align-items: center; justify-content: center; }
.btn-cli-new:hover { background: var(--accent-primary); color: #fff; }
.cli-results { position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 1060; background: #fff; border: 1px solid var(--border-color); border-radius: 10px; box-shadow: 0 10px 30px rgba(26,34,56,.15); max-height: 280px; overflow-y: auto; display: none; }
.cli-opt { display: flex; justify-content: space-between; align-items: center; gap: 8px; padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #f3f3f3; }
.cli-opt:last-child { border-bottom: none; }
.cli-opt:hover, .cli-opt.active { background: var(--accent-light); }
.cli-opt .co-name { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.cli-opt .co-doc { font-size: 11px; color: var(--text-secondary); }
.cli-opt .co-pts { font-size: 10px; font-weight: 700; color: #b45f06; white-space: nowrap; }
.cli-meta { display: flex; justify-content: space-between; align-items: center; margin-top: 6px; min-height: 18px; font-size: 12px; }
.cli-meta .pts { font-weight: 600; color: #b45f06; }

.seg { display: flex; background: #f3f4f6; border-radius: 10px; padding: 3px; gap: 3px; margin-top: 8px; }
.seg input { display: none; }
.seg label { flex: 1; text-align: center; padding: 6px 4px; border-radius: 8px; font-size: 12px; font-weight: 600; color: var(--text-secondary); cursor: pointer; transition: background .15s; margin: 0; }
.seg input:checked + label { background: #fff; color: var(--accent-primary); box-shadow: 0 1px 4px rgba(0,0,0,.1); }

.ticket-items { flex: 1 1 auto; overflow-y: auto; padding: 4px 14px; }
.cart-row { display: grid; grid-template-columns: 1fr auto; gap: 4px 10px; padding: 10px 0; border-bottom: 1px dashed var(--border-color); }
.cart-row:last-child { border-bottom: none; }
.cart-row .cr-name { font-size: 13px; font-weight: 700; color: var(--text-primary); line-height: 1.25; }
.cart-row .cr-sub { font-size: 15px; font-weight: 800; color: var(--text-primary); text-align: right; white-space: nowrap; }
.cart-row .cr-controls { grid-column: 1 / -1; display: flex; align-items: center; gap: 8px; }
.cart-row .cr-unit { font-size: 11px; color: var(--text-secondary); }
.cart-row select.cr-unit { border: 1px solid var(--border-color); border-radius: 6px; padding: 2px 4px; background: #fff; color: var(--text-primary); }
.cart-row .cr-price { font-size: 11px; color: var(--text-secondary); margin-left: auto; white-space: nowrap; }
.qty { display: inline-flex; align-items: center; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; }
.qty button { width: 28px; height: 28px; border: none; background: #f6f7f8; color: var(--text-primary); font-weight: 700; font-size: 16px; line-height: 1; }
.qty button:hover { background: var(--accent-primary); color: #fff; }
.qty input { width: 46px; height: 28px; border: none; border-left: 1px solid var(--border-color); border-right: 1px solid var(--border-color); text-align: center; font-weight: 700; font-size: 13px; color: var(--text-primary); outline: none; padding: 0; -moz-appearance: textfield; }
.qty input::-webkit-outer-spin-button, .qty input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.qty input:focus { background: var(--accent-light); }
.btn-rm { border: none; background: transparent; color: #adb5bd; font-size: 16px; padding: 0 2px; }
.btn-rm:hover { color: var(--danger); }
.cart-empty { text-align: center; color: var(--text-secondary); padding: 40px 10px; }
.cart-empty i { font-size: 44px; opacity: .25; }

.cmp-block { margin: 0 14px 10px; padding: 10px; border-radius: 10px; background: var(--danger-bg); border: 1px solid rgba(230,57,70,.4); display: none; }
.cmp-block label { font-size: 12px; font-weight: 700; color: var(--danger); margin-bottom: 4px; }

.ticket-foot { border-top: 1px solid var(--border-color); padding: 12px 14px 14px; background: #fcfcfc; }
.sum-line { display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: var(--text-secondary); margin-bottom: 4px; }
.sum-line input.desc { width: 80px; text-align: right; border: 1px solid var(--border-color); border-radius: 6px; padding: 2px 6px; font-weight: 700; color: var(--danger); background: #fff; outline: none; }
.sum-line input.desc:focus { border-color: var(--accent-primary); }
.desc-reason { display: none; margin: 2px 0 6px; }
.desc-reason input { width: 100%; height: 30px; border: 1px solid var(--border-color); border-radius: 6px; padding: 0 8px; font-size: 12px; outline: none; background: #fff; }
.desc-reason input:focus { border-color: var(--accent-primary); }
.desc-reason input.invalid { border-color: var(--danger); box-shadow: 0 0 0 3px var(--danger-bg); }
.desc-pts-chip { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #b45f06; background: #fff8ec; border: 1px solid #f5d6a8; border-radius: 20px; padding: 2px 10px; }
.desc-pts-chip button { border: none; background: none; padding: 0; color: #b45f06; font-size: 14px; line-height: 1; }
.btn-pts { font-size: 10px; font-weight: 700; padding: 1px 7px; border-radius: 20px; border: 1px solid #f0ad4e; background: #fff8ec; color: #b45f06; margin-left: 6px; display: none; }
.btn-pay { display: flex; justify-content: space-between; align-items: center; width: 100%; margin-top: 10px; padding: 14px 18px; border: none; border-radius: 12px; color: #fff; font-weight: 800; background: linear-gradient(135deg, var(--accent-primary) 0%, #1FA95B 100%); box-shadow: 0 6px 16px rgba(4,123,7,.25); transition: transform .15s, filter .15s; }
.btn-pay:hover { filter: brightness(1.05); transform: translateY(-1px); }
.btn-pay .lbl { font-size: 17px; letter-spacing: .5px; }
.btn-pay .amt { font-size: 24px; }
.btn-pay:disabled { background: #c9cfd6; box-shadow: none; transform: none; cursor: not-allowed; }

/* ---- Modal cobro (minimal) ---- */
#modalCobro .modal-dialog { max-width: 440px; }
#modalCobro .modal-content { border-radius: 18px; }
#modalCobro .modal-body { padding: 22px 24px 8px; }
.pay-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; }
.pay-top .t-lbl { font-size: 12px; color: var(--text-secondary); }
.pay-top .t-amt { font-size: 32px; font-weight: 800; color: var(--text-primary); line-height: 1.1; letter-spacing: -.5px; }
.pay-methods { display: flex; background: #f3f4f6; border-radius: 10px; padding: 3px; gap: 2px; margin-bottom: 20px; }
.pay-methods input { display: none; }
.pay-methods label { flex: 1; text-align: center; padding: 7px 2px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin: 0; transition: background .15s, color .15s; }
.pay-methods input:checked + label { background: #fff; color: var(--text-primary); box-shadow: 0 1px 3px rgba(0,0,0,.08); }

.pay-label { display: block; font-size: 12px; color: var(--text-secondary); margin-bottom: 4px; }
.pay-input { display: flex; align-items: baseline; gap: 6px; border-bottom: 2px solid var(--border-color); padding: 2px 0 4px; transition: border-color .15s; }
.pay-input:focus-within { border-color: var(--accent-primary); }
.pay-input span { font-size: 18px; font-weight: 600; color: var(--text-secondary); }
.pay-input { min-width: 0; }
.pay-input input { flex: 1 1 0; width: 100%; min-width: 0; text-overflow: ellipsis; border: none; outline: none; background: transparent; font-size: 26px; font-weight: 700; color: var(--text-primary); padding: 0; }
.pay-input.sm input { font-size: 16px; font-weight: 600; }
.pay-input.sm span { font-size: 14px; }
.pay-input input::-webkit-outer-spin-button, .pay-input input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.pay-input input[type=number] { -moz-appearance: textfield; }

.bills { display: flex; gap: 6px; margin-top: 12px; }
.bill { position: relative; flex: 1; height: 38px; border: 1px solid var(--border-color); border-left: 4px solid var(--bill-c); border-radius: 8px; background: #fff; color: var(--text-primary); font-weight: 700; font-size: 13px; transition: background .1s; }
.bill:hover { background: #f8f9fa; }
.bill:active { transform: scale(.96); }
.bill.b10 { --bill-c: #3f9b4f; } .bill.b20 { --bill-c: #e8943a; } .bill.b50 { --bill-c: #d9587a; } .bill.b100 { --bill-c: #3f7fc4; } .bill.b200 { --bill-c: #9a6cc4; }
.bill .bill-count { position: absolute; top: -7px; right: -5px; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 9px; background: var(--text-primary); color: #fff; font-size: 10px; line-height: 18px; display: none; }
.bill .bill-count.show { display: inline-block; }
.bills-actions { display: flex; align-items: center; gap: 14px; margin-top: 8px; font-size: 12px; }
.bills-actions button { border: none; background: none; padding: 0; color: var(--text-secondary); font-weight: 600; }
.bills-actions button:hover { color: var(--accent-primary); }
.bills-detail { margin-left: auto; color: var(--text-secondary); }

.change-box { display: flex; justify-content: space-between; align-items: baseline; margin-top: 16px; padding-top: 12px; border-top: 1px solid #f0f0f0; }
.change-box .c-lbl { font-size: 13px; color: var(--text-secondary); }
.change-box .c-amt { font-size: 20px; font-weight: 700; color: var(--text-secondary); }
.change-box.ok .c-amt { color: var(--accent-primary); }
.change-box.bad .c-lbl, .change-box.bad .c-amt { color: var(--danger); }

.mix-row { display: flex; align-items: center; gap: 12px; padding: 8px 0; }
.mix-row .mix-title { flex: 0 0 72px; font-size: 13px; font-weight: 600; color: var(--text-primary); }
.mix-row .pay-input { flex: 0 1 120px; }
.mix-row .mix-extra { flex: 1 1 0; display: none; }
.mix-row.filled .mix-extra { display: flex; }
.mix-cash { margin-top: 10px; padding-top: 12px; border-top: 1px solid #f0f0f0; }
.mix-cash-due { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px; }
.mix-cash-due .d-lbl { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.mix-cash-due .d-amt { font-size: 20px; font-weight: 700; color: var(--text-primary); }
.mix-cash.zero .d-amt { color: var(--text-secondary); }
.mix-cash.error .d-lbl, .mix-cash.error .d-amt { color: var(--danger); }
.mix-cash-msg { font-size: 12px; color: var(--text-secondary); }
.mix-cash.error .mix-cash-msg { color: var(--danger); }
.mix-cash-msg:empty { display: none; }

#modalCobro .modal-footer { border: 0; padding: 12px 24px 22px; position: sticky; bottom: 0; z-index: 1055; background: #fff; box-shadow: 0 -4px 12px rgba(0,0,0,0.05); }
.btn-confirm { width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 13px 18px; border: none; border-radius: 12px; background: var(--accent-primary); color: #fff; font-weight: 700; font-size: 15px; }
.btn-confirm:hover { background: var(--accent-hover); }
.btn-confirm:disabled { opacity: .7; }

/* ---- Notificación de venta ---- */
.pos-flash { position: relative; overflow: hidden; }
.pos-flash-bar { position: absolute; left: 0; bottom: 0; height: 3px; width: 100%; background: var(--accent-primary); opacity: .5; transform-origin: left; }
.pos-flash.paused .pos-flash-bar { animation-play-state: paused !important; }
@keyframes posFlashBar { from { transform: scaleX(1); } to { transform: scaleX(0); } }

/* ---- Comprobante en modal ---- */
.pay-section-lbl { display: block; font-size: 12px; color: var(--text-secondary); margin-bottom: 6px; }
#modalCobro .seg { margin-top: 0; margin-bottom: 6px; }
#modalCobro .seg label { font-size: 13px; padding: 7px 4px; }
.comp-warn { display: none; align-items: center; justify-content: space-between; gap: 8px; font-size: 12px; font-weight: 600; color: var(--danger); background: var(--danger-bg); border-radius: 8px; padding: 6px 10px; margin-bottom: 8px; }
.comp-warn.show { display: flex; }
.comp-warn button { border: none; background: var(--danger); color: #fff; border-radius: 6px; padding: 3px 10px; font-size: 12px; font-weight: 700; white-space: nowrap; }
.comp-cli { font-size: 12px; color: var(--text-secondary); margin-bottom: 16px; }

/* ---- Responsive ---- */
.pos-mobile-tabs { display: none; gap: 8px; margin-bottom: 10px; }
.pos-tab-btn { flex: 1; padding: 10px; font-weight: 700; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-secondary); border-radius: 10px; }
.pos-tab-btn.active { background: var(--accent-primary); color: #fff; border-color: var(--accent-primary); }
.pos-tab-btn .badge { background: #fff; color: var(--accent-primary); }
@media (max-width: 991px) {
    .pos-layout { flex-direction: column; height: auto; min-height: 0; }
    .pos-mobile-tabs { display: flex; }
    .pos-panel { display: none !important; }
    .pos-panel.active { display: flex !important; }
    .pos-ticket-col { flex-basis: auto; }
    .pos-ticket { height: calc(100vh - 170px); min-height: 520px; }
    .pos-catalog { max-height: calc(100vh - 240px); }
}
@media (max-width: 576px) {
    .pay-methods label { font-size: 12px; }
    .mix-row { flex-wrap: wrap; }
    .mix-row.filled .mix-extra { flex-basis: 100%; }
}
</style>

<!-- Pestañas para vista Móvil / Tablet -->
<div class="pos-mobile-tabs">
    <button type="button" class="pos-tab-btn" id="btnTabCatalog" onclick="switchPosTab('catalog')"><i class="bi bi-grid-3x3-gap"></i> Productos</button>
    <button type="button" class="pos-tab-btn active" id="btnTabCart" onclick="switchPosTab('cart')"><i class="bi bi-receipt"></i> Venta <span class="badge rounded-pill ms-1" id="tabCartCount">0</span></button>
</div>

<!-- Notificaciones PHP (se cierran solas o con la X) -->
<?php if(isset($_SESSION['mensaje_pos'])): ?>
    <div class="alert alert-success alert-dismissible fade show pos-flash py-2 px-3 mb-3" role="alert" data-autoclose="8000" style="background-color: var(--success-bg); color: var(--accent-primary); border: 1px solid var(--accent-primary); font-weight:600; display:flex; align-items:center; flex-wrap:wrap; gap:8px; padding-right: 3rem !important;">
        <span class="me-auto"><i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['mensaje_pos']; unset($_SESSION['mensaje_pos']); ?></span>
        <?php if(isset($_SESSION['last_ticket'])): ?>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-success" onclick="window.open('<?php echo BASE_URL; ?>venta/ticket/<?php echo $_SESSION['last_ticket']; ?>', 'Ticket', 'width=400,height=600')"><i class="bi bi-printer"></i> Tiquetera</button>
                <button class="btn btn-sm btn-success" onclick="window.open('<?php echo BASE_URL; ?>venta/pdf/<?php echo $_SESSION['last_ticket']; unset($_SESSION['last_ticket']); ?>', 'PDF', 'width=900,height=700')"><i class="bi bi-file-earmark-pdf-fill"></i> PDF A4</button>
            </div>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar" style="padding: 0.9rem 1rem;"></button>
        <div class="pos-flash-bar"></div>
    </div>
<?php endif; ?>
<?php if(isset($_SESSION['error_pos']) || isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show pos-flash py-2 px-3 mb-3" role="alert" style="background-color: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger); padding-right: 3rem !important;">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <?php
        $err = $_SESSION['error_pos'] ?? $_SESSION['error'];
        unset($_SESSION['error_pos'], $_SESSION['error']);
        echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8');
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar" style="padding: 0.9rem 1rem;"></button>
    </div>
<?php endif; ?>

<div class="pos-layout">
    <!-- ============ IZQUIERDA: CATÁLOGO ============ -->
    <div class="pos-catalog-col pos-panel">
        <div class="pos-search">
            <i class="bi bi-upc-scan"></i>
            <input type="text" id="buscadorPOS" placeholder="Escanea el código de barras o busca por nombre..." oninput="filtrarCatalogo()" autocomplete="off" autofocus>
            <span class="kbd-hint d-none d-md-inline"><kbd class="pos-kbd">F2</kbd> buscar</span>
        </div>

        <div class="pos-card pos-catalog" id="catList">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="pos-section-title mb-0">Catálogo</span>
                <span class="pos-section-title mb-0" id="catCount"><?php echo count($data['productos']); ?> productos</span>
            </div>
            <div class="pos-catalog-grid">
            <?php foreach($data['productos'] as $prod):
                $stock = (int)$prod['stock_actual'];
                $disabledClass = $stock <= 0 ? 'disabled' : '';
                $cond = $prod['condicion_venta'] ?? 'Venta Libre';
                $jsonP = json_encode([
                    'id' => $prod['id'],
                    'codigo_barras' => $prod['codigo_barras'],
                    'nombre' => $prod['nombre_comercial'],
                    'precio' => $prod['precio_venta'],
                    'precio_fraccion' => isset($prod['precio_fraccion']) ? $prod['precio_fraccion'] : 0,
                    'fraccionable' => isset($prod['fraccionable']) ? $prod['fraccionable'] : 0,
                    'unidad_fraccion' => isset($prod['unidad_fraccion']) ? $prod['unidad_fraccion'] : 'Fracción',
                    'unidad_medida' => $prod['unidad_medida'] ? $prod['unidad_medida'] : 'Caja',
                    'unidades_por_caja' => isset($prod['unidades_por_caja']) ? $prod['unidades_por_caja'] : 1,
                    'stock' => $stock,
                    'requiere_receta' => $prod['requiere_receta'],
                    'condicion_venta' => $cond,
                    'registro_sanitario' => $prod['registro_sanitario'] ?? ''
                ]);
                $stockClass = $stock <= 0 ? 'out' : ($stock <= 10 ? 'low' : '');
            ?>
                <div class="item-card <?php echo $disabledClass; ?>" data-busqueda="<?php echo htmlspecialchars(mb_strtolower($prod['codigo_barras'] . ' ' . $prod['nombre_comercial'] . ' ' . $prod['nombre_generico']), ENT_QUOTES); ?>" onclick='agregarAlCarrito(<?php echo htmlspecialchars($jsonP, ENT_QUOTES); ?>)'>
                    <div>
                        <div class="ic-name">
                            <?php echo htmlspecialchars($prod['nombre_comercial']); ?>
                            <?php if($cond === 'Receta Médica Retenida'): ?><span class="rx-badge ret">R. RETENIDA</span>
                            <?php elseif($cond === 'Receta Médica Simple'): ?><span class="rx-badge sim">R. SIMPLE</span><?php endif; ?>
                        </div>
                        <div class="ic-meta"><?php echo htmlspecialchars(trim($prod['unidad_medida'] . ' ' . $prod['concentracion'])); ?></div>
                    </div>
                    <div class="ic-foot">
                        <span class="stock-pill <?php echo $stockClass; ?>"><?php echo $stock > 0 ? "Stock: $stock" : 'Agotado'; ?></span>
                        <span class="ic-price">S/ <?php echo number_format($prod['precio_venta'], 2); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <div class="catalog-empty" id="catEmpty">
                <i class="bi bi-search" style="font-size: 36px; opacity: .3;"></i>
                <p class="mt-2 mb-0">No hay productos que coincidan con la búsqueda.</p>
            </div>
        </div>
    </div>

    <!-- ============ DERECHA: TICKET DE VENTA ============ -->
    <div class="pos-ticket-col pos-panel active">
        <form action="<?php echo BASE_URL; ?>venta/save" method="POST" id="formVenta" class="pos-card pos-ticket">
            <?php echo Controller::csrfField(); ?>

            <div class="ticket-head">
                <div class="pos-section-title">Cliente</div>
                <div class="cli-picker">
                    <div class="cli-box">
                        <div class="cli-input-wrap">
                            <i class="bi bi-person-circle"></i>
                            <input type="text" id="filtroClientePos" placeholder="Buscar por DNI/RUC o nombre..." autocomplete="off"
                                   value="<?php echo $clienteDefault ? htmlspecialchars($clienteDefault['doc'] . ' - ' . $clienteDefault['nombres']) : ''; ?>">
                            <i class="bi bi-chevron-down cli-caret"></i>
                        </div>
                        <button type="button" class="btn-cli-new" data-bs-toggle="modal" data-bs-target="#modalNuevoClientePos" title="Registrar nuevo cliente">
                            <i class="bi bi-person-plus-fill"></i>
                        </button>
                    </div>
                    <div id="resultadosClientePos" class="cli-results"></div>
                </div>
                <input type="hidden" name="id_cliente" id="selectCliente" value="<?php echo $clienteDefault ? $clienteDefault['id'] : ''; ?>">
                <div class="cli-meta">
                    <span id="cliDocInfo" class="text-muted"></span>
                    <span id="puntosBlock" class="pts" style="display:none;"><i class="bi bi-star-fill text-warning"></i> <span id="lblPuntos">0</span> pts</span>
                </div>

            </div>

            <div class="ticket-items" id="cartContainer">
                <div id="cartItems"></div>
                <div id="cartEmpty" class="cart-empty">
                    <i class="bi bi-cart3"></i>
                    <p class="mt-2 mb-0">El carrito está vacío.<br><small>Escanea o selecciona productos del catálogo.</small></p>
                </div>
                <div id="cartHiddenInputs"></div>
            </div>

            <!-- CMP para productos con receta -->
            <div id="cmpBlock" class="cmp-block">
                <label class="d-block"><i class="bi bi-file-medical"></i> CMP del médico (requerido por receta)</label>
                <input type="text" class="form-control form-control-sm" name="medico_cmp" id="inCmp" placeholder="Ej. 12345">
                <div id="advRetenidos"></div>
            </div>

            <div class="ticket-foot">
                <div class="sum-line">
                    <span>Op. gravada</span>
                    <span id="txtSub">S/ 0.00</span>
                </div>
                <div class="sum-line">
                    <span>IGV (<?php echo htmlspecialchars($data['igv']); ?>%)</span>
                    <span id="txtIgv">S/ 0.00</span>
                </div>
                <div class="sum-line">
                    <span>Descuento <button type="button" id="btnCanjear" class="btn-pts" onclick="canjearPuntos()"><i class="bi bi-star-fill"></i> Usar puntos</button></span>
                    <span>- S/ <input type="number" step="0.01" min="0" id="inDesc" class="desc" value="0.00" oninput="renderCarrito()"></span>
                </div>
                <div class="desc-reason" id="descMotivoRow">
                    <span class="desc-pts-chip" id="descPtsChip" style="display:none;">
                        <i class="bi bi-star-fill"></i> <span id="descPtsTxt">Descuento por puntos</span>
                        <button type="button" onclick="quitarDescuento()" title="Quitar descuento" aria-label="Quitar descuento">&times;</button>
                    </span>
                    <input type="text" id="inMotivoDesc" name="motivo_descuento" maxlength="255" list="motivosDescuento"
                           placeholder="Motivo del descuento (obligatorio)" autocomplete="off" oninput="this.classList.remove('invalid')">
                    <datalist id="motivosDescuento">
                        <option value="Cliente frecuente">
                        <option value="Promoción vigente">
                        <option value="Redondeo de precio">
                        <option value="Producto próximo a vencer">
                        <option value="Autorizado por administrador">
                    </datalist>
                </div>
                <input type="hidden" id="fiSub" name="subtotal_venta" value="0">
                <input type="hidden" id="fiIgv" name="igv_venta" value="0">
                <input type="hidden" id="fiTot" name="total_venta" value="0">
                <input type="hidden" id="fiDesc" name="descuento_venta" value="0">
                <input type="hidden" id="fiPuso" name="puntos_usados" value="0">

                <button type="button" class="btn-pay" id="btnAbrirCobro" onclick="abrirCobro()" disabled>
                    <span class="lbl"><i class="bi bi-wallet2"></i> COBRAR <kbd class="pos-kbd ms-1 d-none d-md-inline">F10</kbd></span>
                    <span class="amt" id="txtTot">S/ 0.00</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Botonera de billetes peruanos acumulables. $ctx: 'efe' (pago en efectivo) | 'mix' (parte en efectivo del pago mixto)
function posBilletes($ctx) { ?>
    <div class="bills">
        <?php foreach ([10, 20, 50, 100, 200] as $b): ?>
            <button type="button" class="bill b<?php echo $b; ?>" onclick="sumarBillete('<?php echo $ctx; ?>', <?php echo $b; ?>)" title="Billete de S/ <?php echo $b; ?>">
                <?php echo $b; ?>
                <span class="bill-count" id="billCount_<?php echo $ctx . '_' . $b; ?>" onclick="event.stopPropagation(); quitarBillete('<?php echo $ctx; ?>', <?php echo $b; ?>)" title="Quitar uno"></span>
            </button>
        <?php endforeach; ?>
    </div>
    <div class="bills-actions">
        <button type="button" onclick="pagoExacto('<?php echo $ctx; ?>')">Exacto</button>
        <button type="button" onclick="deshacerBillete('<?php echo $ctx; ?>')">Deshacer</button>
        <button type="button" onclick="limpiarBilletes('<?php echo $ctx; ?>')">Limpiar</button>
        <span class="bills-detail" id="billDetalle_<?php echo $ctx; ?>"></span>
    </div>
<?php } ?>

<!-- ============ MODAL DE COBRO ============ -->
<div class="modal fade" id="modalCobro" tabindex="-1" aria-labelledby="modalCobroLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body">
                <div class="pay-top">
                    <div>
                        <div class="t-lbl" id="modalCobroLabel">Total a cobrar</div>
                        <div class="t-amt" id="cobroTotal">S/ 0.00</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <span class="pay-section-lbl">Comprobante</span>
                <div class="seg" role="radiogroup" aria-label="Tipo de comprobante">
                    <input type="radio" form="formVenta" name="tipo_comprobante" id="tcTicket" value="Ticket" checked onchange="validarComprobante()"><label for="tcTicket">Ticket</label>
                    <input type="radio" form="formVenta" name="tipo_comprobante" id="tcBoleta" value="Boleta" onchange="validarComprobante()"><label for="tcBoleta">Boleta</label>
                    <input type="radio" form="formVenta" name="tipo_comprobante" id="tcFactura" value="Factura" onchange="validarComprobante()"><label for="tcFactura">Factura</label>
                </div>
                <div class="comp-warn" id="compWarn">
                    <span id="compWarnTxt"></span>
                    <button type="button" onclick="elegirClienteDesdeCobro()">Elegir cliente</button>
                </div>
                <div class="comp-cli" id="compCli"></div>

                <span class="pay-section-lbl">Método de pago</span>
                <div class="pay-methods" role="radiogroup" aria-label="Método de pago">
                    <input type="radio" form="formVenta" name="metodo_pago" id="btnEfecti" value="Efectivo" checked onchange="cambiarMetodoPago('Efectivo')">
                    <label for="btnEfecti">Efectivo</label>
                    <input type="radio" form="formVenta" name="metodo_pago" id="btnYape" value="Yape/Plin" onchange="cambiarMetodoPago('Yape/Plin')">
                    <label for="btnYape">Yape/Plin</label>
                    <input type="radio" form="formVenta" name="metodo_pago" id="btnTarj" value="Tarjeta" onchange="cambiarMetodoPago('Tarjeta')">
                    <label for="btnTarj">Tarjeta</label>
                    <input type="radio" form="formVenta" name="metodo_pago" id="btnMixto" value="Mixto" onchange="cambiarMetodoPago('Mixto')">
                    <label for="btnMixto">Mixto</label>
                </div>

                <!-- Efectivo -->
                <div id="panelEfectivo" class="payment-panel">
                    <label class="pay-label" for="inPago">Recibido</label>
                    <div class="pay-input">
                        <span>S/</span>
                        <input type="number" step="0.01" min="0" form="formVenta" name="pago_recibido" id="inPago" placeholder="0.00" oninput="onPagoManual('efe')">
                    </div>
                    <?php posBilletes('efe'); ?>
                    <div class="change-box" id="boxVuelto">
                        <span class="c-lbl">Vuelto</span>
                        <span class="c-amt">S/ <span id="lblVuelto">0.00</span></span>
                    </div>
                    <input type="hidden" form="formVenta" name="vuelto_venta" id="inVuelto" value="0.00">
                </div>

                <!-- Yape / Plin -->
                <div id="panelYape" class="payment-panel" style="display:none;">
                    <label class="pay-label" for="inOpTrans">N° de operación</label>
                    <div class="pay-input sm">
                        <input type="text" form="formVenta" name="num_operacion_trans" id="inOpTrans" placeholder="Ej. 084920">
                    </div>
                </div>

                <!-- Tarjeta -->
                <div id="panelTarjeta" class="payment-panel" style="display:none;">
                    <label class="pay-label" for="inOpTarj">N° de voucher</label>
                    <div class="pay-input sm">
                        <input type="text" form="formVenta" name="num_operacion_tarj" id="inOpTarj" placeholder="Ej. 102948">
                    </div>
                </div>

                <!-- Mixto: primero lo digital, el resto es efectivo -->
                <div id="panelMixto" class="payment-panel" style="display:none;">
                    <div class="mix-row" id="mixRowTrans">
                        <span class="mix-title">Yape/Plin</span>
                        <div class="pay-input sm">
                            <span>S/</span>
                            <input type="number" step="0.01" min="0" form="formVenta" name="monto_transferencia" id="inMontoTransMixto" placeholder="0.00" oninput="calcularMixto()">
                        </div>
                        <div class="pay-input sm mix-extra">
                            <input type="text" id="inOpTransMixto" placeholder="N° operación">
                        </div>
                    </div>

                    <div class="mix-row" id="mixRowTarj">
                        <span class="mix-title">Tarjeta</span>
                        <div class="pay-input sm">
                            <span>S/</span>
                            <input type="number" step="0.01" min="0" form="formVenta" name="monto_tarjeta" id="inMontoTarjMixto" placeholder="0.00" oninput="calcularMixto()">
                        </div>
                        <div class="pay-input sm mix-extra">
                            <input type="text" id="inOpTarjMixto" placeholder="N° voucher">
                        </div>
                    </div>

                    <div class="mix-cash" id="mixCash">
                        <div class="mix-cash-due">
                            <span class="d-lbl">Efectivo <small class="text-muted fw-normal">(resto)</small></span>
                            <span class="d-amt">S/ <span id="lblEfeMixto">0.00</span></span>
                        </div>
                        <div class="mix-cash-msg" id="mixCashMsg"></div>
                        <input type="hidden" form="formVenta" name="monto_efectivo" id="inMontoEfeMixto" value="0">

                        <div id="mixCashPay">
                            <label class="pay-label" for="inPagoEfeMixto">Recibido</label>
                            <div class="pay-input">
                                <span>S/</span>
                                <input type="number" step="0.01" min="0" id="inPagoEfeMixto" name="pago_recibido_mixto" class="inPagoEfe inPagoEfeMixto" placeholder="0.00" oninput="onPagoManual('mix')">
                            </div>
                            <?php posBilletes('mix'); ?>
                            <div class="change-box" id="boxVueltoMixto">
                                <span class="c-lbl">Vuelto</span>
                                <span class="c-amt">S/ <span id="lblVueltoMixto">0.00</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-confirm" id="btnConfirmarVenta" onclick="confirmarVenta()">
                    <span>Confirmar venta</span>
                    <kbd class="pos-kbd d-none d-md-inline" style="background: rgba(255,255,255,.2); color:#fff; border-color: rgba(255,255,255,.3);">Enter</kbd>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL REGISTRO RÁPIDO DE CLIENTE DESDE POS -->
<div class="modal fade" id="modalNuevoClientePos" tabindex="-1" aria-labelledby="modalNuevoClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title fw-bold" id="modalNuevoClienteLabel"><i class="bi bi-person-plus-fill me-2"></i> Nuevo Cliente Rápido</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevoClientePos" onsubmit="guardarClientePos(event)">
                <div class="modal-body p-3">
                    <div id="alertaErrorClientePos" class="alert alert-danger py-2 d-none" style="font-size: 12px;"></div>

                    <div class="row g-2 mb-2">
                        <div class="col-5">
                            <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: #444;">Tipo Doc *</label>
                            <select class="form-select form-select-sm" name="tipo_documento" id="nuevoCliTipoDoc" onchange="ajustarTipoDocPos()">
                                <option value="DNI" selected>DNI</option>
                                <option value="RUC">RUC</option>
                                <option value="CE">Carnet Ext.</option>
                                <option value="Pasaporte">Pasaporte</option>
                            </select>
                        </div>
                        <div class="col-7">
                            <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: #444;">N° Documento * <span id="posDocHelp" style="font-size:10px; color:#666;">(8 díg.)</span></label>
                            <input type="text" class="form-control form-control-sm" name="num_documento" id="nuevoCliNumDoc" maxlength="8" required placeholder="Ej. 70854120" oninput="limpiarNumeroDocPos(this)">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: #444;">Nombres y Apellidos / Razón Social *</label>
                        <input type="text" class="form-control form-control-sm" name="nombres" id="nuevoCliNombres" required placeholder="Ej. Juan Pérez García">
                    </div>

                    <div class="row g-2 mb-1">
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: #444;">Teléfono / Celular</label>
                            <input type="text" class="form-control form-control-sm" name="telefono" id="nuevoCliTelefono" placeholder="Ej. 987654321">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: #444;">Dirección</label>
                            <input type="text" class="form-control form-control-sm" name="direccion" id="nuevoCliDireccion" placeholder="Ej. Av. Principal 123">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold" id="btnGuardarClientePos"><i class="bi bi-check-circle"></i> Guardar y Asignar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Canje de Puntos de Fidelización en POS -->
<div class="modal fade" id="modalCanjePuntos" tabindex="-1" aria-labelledby="modalCanjeLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="background: var(--bg-card); color: var(--text-primary); border-radius: 14px;">
            <div class="modal-header border-bottom py-2 px-3">
                <h6 class="modal-title fw-bold" id="modalCanjeLabel">
                    <i class="bi bi-star-fill text-warning me-1"></i> Canjear Puntos
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="p-2 mb-3 rounded" style="background: var(--bg-dark); font-size: 12px;">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Puntos Disponibles:</span>
                        <strong id="modalCanjeDisponibles" class="text-warning">0 pts</strong>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="text-muted">Equivalente Total:</span>
                        <strong id="modalCanjeEquivMax" class="text-success">S/ 0.00</strong>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label mb-1" style="font-size: 12px; font-weight: 600;">Puntos a canjear en esta venta:</label>
                    <input type="number" min="1" step="1" id="inModalPuntosCanjear" class="form-control form-control-sm bg-dark text-white border-secondary fw-bold text-center" style="font-size: 16px;" oninput="actualizarPreviewCanje()">
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 11px;">
                    <span class="text-muted">Descuento que genera:</span>
                    <strong id="modalCanjeDescuentoPreview" class="text-danger fw-bold fs-6">S/ 0.00</strong>
                </div>
                <button type="button" class="btn btn-outline-warning btn-sm w-100" onclick="usarMaximosPuntosCanje()">
                    <i class="bi bi-lightning-fill"></i> Usar Máximo Posible
                </button>
            </div>
            <div class="modal-footer border-top py-2 px-3">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning btn-sm px-3 fw-bold" onclick="aplicarCanjePuntos()">
                    <i class="bi bi-check-lg"></i> Aplicar Canje
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let carrito = {};

// Catálogo Global en JS
const catalogoGlobal = [
<?php foreach($data['productos'] as $p): ?>
    <?php echo json_encode([
        'id' => $p['id'],
        'codigo_barras' => $p['codigo_barras'],
        'nombre' => $p['nombre_comercial'],
        'precio' => $p['precio_venta'],
        'precio_fraccion' => isset($p['precio_fraccion']) ? $p['precio_fraccion'] : 0,
        'fraccionable' => isset($p['fraccionable']) ? $p['fraccionable'] : 0,
        'unidad_fraccion' => isset($p['unidad_fraccion']) ? $p['unidad_fraccion'] : 'Fracción',
        'unidad_medida' => $p['unidad_medida'] ? $p['unidad_medida'] : 'Caja',
        'unidades_por_caja' => isset($p['unidades_por_caja']) ? $p['unidades_por_caja'] : 1,
        'stock' => $p['stock_actual'],
        'requiere_receta' => $p['requiere_receta'],
        'condicion_venta' => $p['condicion_venta'] ?? 'Venta Libre',
        'registro_sanitario' => $p['registro_sanitario'] ?? ''
    ]); ?>,
<?php endforeach; ?>
];

// Clientes activos (búsqueda local instantánea)
const clientesPos = <?php echo json_encode($clientesPos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
let clienteActual = <?php echo json_encode($clienteDefault, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

const IGV_PCT = <?php echo floatval($data['igv']); ?>;

// Reglas Dinámicas de Fidelidad y Puntos
let puntosValorCanje = <?php echo floatval($data['configPuntos']['valor_canje'] ?? 0.10); ?>;
let puntosConsumoBase = <?php echo floatval($data['configPuntos']['consumo_base'] ?? 10.00); ?>;
let puntosHabilitado = <?php echo intval($data['configPuntos']['habilitado'] ?? 1); ?>;
let ratioCanje = puntosValorCanje > 0 ? (1 / puntosValorCanje) : 10;

function escHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
function money(n) { return (Math.round((parseFloat(n) || 0) * 100) / 100).toFixed(2); }
function getTotal() { return parseFloat(document.getElementById('fiTot').value) || 0; }

// -----------------------------------------
// CLIENTE: buscador + selector unificado
// -----------------------------------------
const inputFiltroCli = document.getElementById('filtroClientePos');
const resDivCli = document.getElementById('resultadosClientePos');
const selCli = document.getElementById('selectCliente');
let cliActiveIdx = -1;

function etiquetaCliente(c) { return c ? (c.doc + ' - ' + c.nombres) : ''; }

function buscarClientes(q) {
    q = q.trim().toLowerCase();
    let lista = clientesPos;
    if (q !== '') {
        lista = clientesPos.filter(c => c.doc.toLowerCase().includes(q) || c.nombres.toLowerCase().includes(q));
    } else {
        lista = [...clientesPos].sort((a, b) => (b.id === 1) - (a.id === 1));
    }
    return lista.slice(0, 30);
}

function renderResultadosCliente(q) {
    const lista = buscarClientes(q);
    cliActiveIdx = lista.length ? 0 : -1;
    if (!lista.length) {
        resDivCli.innerHTML = `<div class="p-3 text-center text-muted" style="font-size:13px;">
            No se encontró el cliente.<br>
            <button type="button" class="btn btn-sm btn-success mt-2" onmousedown="event.preventDefault()" onclick="abrirNuevoCliente()"><i class="bi bi-person-plus-fill"></i> Registrar nuevo</button>
        </div>`;
    } else {
        resDivCli.innerHTML = lista.map((c, i) => `
            <div class="cli-opt ${i === 0 ? 'active' : ''}" data-id="${c.id}" onmousedown="event.preventDefault(); seleccionarCliente(${c.id})">
                <div>
                    <div class="co-name">${escHtml(c.nombres)}</div>
                    <div class="co-doc">${escHtml(c.tipo)}: ${escHtml(c.doc)}</div>
                </div>
                ${c.id !== 1 ? `<span class="co-pts"><i class="bi bi-star-fill"></i> ${c.puntos}</span>` : ''}
            </div>`).join('');
    }
    resDivCli.style.display = 'block';
}

function cerrarResultadosCliente() {
    resDivCli.style.display = 'none';
    inputFiltroCli.value = etiquetaCliente(clienteActual);
}

function seleccionarCliente(id) {
    const c = clientesPos.find(x => x.id == id);
    if (!c) return;
    clienteActual = c;
    selCli.value = c.id;
    resDivCli.style.display = 'none';
    inputFiltroCli.value = etiquetaCliente(c);
    inputFiltroCli.blur();
    evaluarClientePuntos();
    // Si se vino desde el cobro (Boleta/Factura sin cliente), volver al modal de cobro
    if (reabrirCobroTrasCliente) {
        reabrirCobroTrasCliente = false;
        setTimeout(() => abrirCobro(), 400);
    }
}

// Compatibilidad con la firma anterior
function seleccionarClientePredictivo(id, numDoc, nombres, puntos) {
    if (!clientesPos.find(x => x.id == id)) {
        clientesPos.push({ id: parseInt(id), tipo: 'DOC', doc: numDoc, nombres: nombres, puntos: parseInt(puntos) || 0 });
    }
    seleccionarCliente(id);
}

function abrirNuevoCliente() {
    const q = inputFiltroCli.value.trim();
    cerrarResultadosCliente();
    if (/^\d{8}$/.test(q)) { document.getElementById('nuevoCliTipoDoc').value = 'DNI'; document.getElementById('nuevoCliNumDoc').value = q; }
    else if (/^\d{11}$/.test(q)) { document.getElementById('nuevoCliTipoDoc').value = 'RUC'; document.getElementById('nuevoCliNumDoc').value = q; }
    else if (q && !/\d/.test(q)) { document.getElementById('nuevoCliNombres').value = q; }
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNuevoClientePos')).show();
}

inputFiltroCli.addEventListener('focus', function() { this.select(); renderResultadosCliente(''); });
inputFiltroCli.addEventListener('input', function() { renderResultadosCliente(this.value); });
inputFiltroCli.addEventListener('blur', function() { setTimeout(cerrarResultadosCliente, 120); });
inputFiltroCli.addEventListener('keydown', function(e) {
    const opts = resDivCli.querySelectorAll('.cli-opt');
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        e.preventDefault();
        if (!opts.length) return;
        cliActiveIdx = (cliActiveIdx + (e.key === 'ArrowDown' ? 1 : -1) + opts.length) % opts.length;
        opts.forEach((o, i) => o.classList.toggle('active', i === cliActiveIdx));
        opts[cliActiveIdx].scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (opts[cliActiveIdx]) seleccionarCliente(opts[cliActiveIdx].dataset.id);
    } else if (e.key === 'Escape') {
        reabrirCobroTrasCliente = false;
        cerrarResultadosCliente();
        this.blur();
    }
});

function evaluarClientePuntos() {
    const c = clienteActual;
    const pts = c ? (parseInt(c.puntos) || 0) : 0;
    const docInfo = document.getElementById('cliDocInfo');
    if (docInfo) {
        docInfo.textContent = c ? (c.id === 1 ? 'Venta sin datos de cliente' : c.tipo + ': ' + c.doc) : '';
    }

    const pBlock = document.getElementById('puntosBlock');
    const bCanjear = document.getElementById('btnCanjear');
    const fPuso = document.getElementById('fiPuso');

    if (!c || c.id == 1 || !puntosHabilitado) { // Público General o puntos deshabilitados
        if (pBlock) pBlock.style.display = 'none';
        if (bCanjear) bCanjear.style.display = 'none';
        if (fPuso) fPuso.value = 0;
    } else {
        if (pBlock) {
            pBlock.style.display = 'inline';
            let valorSoles = (pts * puntosValorCanje).toFixed(2);
            let lbl = document.getElementById('lblPuntos');
            if (lbl) lbl.innerHTML = `${pts} <span class="text-muted" style="font-size:11px;">(equiv. S/ ${valorSoles})</span>`;
        }
        if (bCanjear) {
            bCanjear.style.display = (pts > 0 && ratioCanje > 0) ? 'inline-block' : 'none';
        }
    }
    renderCarrito();
}

// Descuento: por puntos (motivo automático) o manual (motivo obligatorio)
function actualizarMotivoDescuento(descuento) {
    const row = document.getElementById('descMotivoRow');
    const inMotivo = document.getElementById('inMotivoDesc');
    const puntos = parseInt(document.getElementById('fiPuso').value) || 0;
    if (descuento <= 0) {
        row.style.display = 'none';
        inMotivo.value = '';
        inMotivo.classList.remove('invalid');
        return;
    }
    row.style.display = 'block';
    const porPuntos = puntos > 0;
    document.getElementById('descPtsChip').style.display = porPuntos ? 'inline-flex' : 'none';
    inMotivo.style.display = porPuntos ? 'none' : 'block';
    if (porPuntos) {
        document.getElementById('descPtsTxt').textContent = 'Canje de ' + puntos + ' puntos (-S/ ' + money(descuento) + ')';
        inMotivo.value = 'Canje de ' + puntos + ' puntos';
    }
}

function validarMotivoDescuento() {
    const descuento = parseFloat(document.getElementById('fiDesc').value) || 0;
    const puntos = parseInt(document.getElementById('fiPuso').value) || 0;
    const inMotivo = document.getElementById('inMotivoDesc');
    if (descuento > 0 && puntos === 0 && inMotivo.value.trim() === '') {
        alert("Indique el motivo del descuento manual.");
        if (window.matchMedia('(max-width: 991px)').matches) switchPosTab('cart');
        inMotivo.classList.add('invalid');
        inMotivo.focus();
        return false;
    }
    return true;
}

function quitarDescuento() {
    document.getElementById('inDesc').value = '0.00';
    document.getElementById('fiPuso').value = 0;
    renderCarrito();
}

function canjearPuntos() {
    let pts = clienteActual ? (parseInt(clienteActual.puntos) || 0) : 0;
    let maxSoles = pts * puntosValorCanje;

    let sum = 0;
    Object.values(carrito).forEach(i => { sum += (i.tipo_unidad == 'CAJA' ? i.precio_caja : i.precio_fraccion) * i.cantidad; });

    if(sum <= 0) { alert("Primero agrega productos al carrito."); return; }
    if(pts <= 0 || maxSoles <= 0) { alert("El cliente no tiene puntos acumulados suficientes."); return; }

    let dsctoSoles = Math.min(maxSoles, sum);
    let puntosAUsar = Math.min(pts, Math.ceil(dsctoSoles / (puntosValorCanje > 0 ? puntosValorCanje : 0.1)));
    dsctoSoles = Math.min(sum, +(puntosAUsar * puntosValorCanje).toFixed(2));

    document.getElementById('inDesc').value = dsctoSoles.toFixed(2);
    document.getElementById('fiPuso').value = puntosAUsar;
    renderCarrito();
}

// -----------------------------------------
// CATÁLOGO
// -----------------------------------------
document.getElementById("buscadorPOS").addEventListener('keydown', function(e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    let codigo = this.value.trim();
    if (codigo === '') return;

    const agregarYLimpiar = (prod) => {
        if (prod.stock <= 0) { alert("Producto sin stock o agotado: " + prod.nombre); }
        else { agregarAlCarrito(prod); }
        this.value = '';
        filtrarCatalogo();
    };

    let porCodigo = catalogoGlobal.find(p => p.codigo_barras && p.codigo_barras.trim().toLowerCase() === codigo.toLowerCase());
    if (porCodigo) return agregarYLimpiar(porCodigo);

    let porNombre = catalogoGlobal.filter(p => p.nombre && p.nombre.toLowerCase() === codigo.toLowerCase());
    if (porNombre.length === 1) return agregarYLimpiar(porNombre[0]);

    let visibleCards = Array.from(document.querySelectorAll('#catList .item-card')).filter(el => el.style.display !== 'none');
    if (visibleCards.length === 1 && !visibleCards[0].classList.contains('disabled')) {
        visibleCards[0].click();
        this.value = '';
        filtrarCatalogo();
        return;
    }

    alert('No se encontró ningún producto con el código de barras o término: "' + codigo + '"');
    this.select();
});

function filtrarCatalogo() {
    let input = document.getElementById("buscadorPOS").value.toLowerCase().trim();
    let items = document.querySelectorAll("#catList .item-card");
    let visibles = 0;
    items.forEach(it => {
        const busq = (it.getAttribute("data-busqueda") || "").toLowerCase();
        const ok = busq.indexOf(input) > -1;
        it.style.display = ok ? "flex" : "none";
        if (ok) visibles++;
    });
    document.getElementById('catCount').textContent = visibles + ' productos';
    document.getElementById('catEmpty').style.display = visibles === 0 ? 'block' : 'none';
}

// -----------------------------------------
// CARRITO
// -----------------------------------------
function agregarAlCarrito(producto) {
    if(!producto) return;
    let stockTotal = parseInt(producto.stock) || 0;
    if (stockTotal <= 0) {
        alert('❌ ¡PRODUCTO AGOTADO!\nNo hay unidades disponibles de "' + (producto.nombre || 'este producto') + '" en inventario.');
        return;
    }

    let id = producto.id;
    if(carrito[id]) {
        let stockRequerido = carrito[id].cantidad;
        if (carrito[id].tipo_unidad == 'CAJA' && carrito[id].fraccionable == 1) {
            stockRequerido = carrito[id].cantidad * carrito[id].unidades_por_caja;
        }
        let stockFuturo = stockRequerido + (carrito[id].tipo_unidad == 'CAJA' && carrito[id].fraccionable == 1 ? carrito[id].unidades_por_caja : 1);

        if(stockFuturo <= stockTotal) {
            carrito[id].cantidad++;
        } else {
            alert('¡Límite de stock alcanzado para este producto (Stock en unidades: '+stockTotal+')!');
        }
    } else {
        carrito[id] = {
            id: producto.id,
            nombre: producto.nombre,
            precio_caja: parseFloat(producto.precio),
            precio_fraccion: parseFloat(producto.precio_fraccion),
            fraccionable: producto.fraccionable,
            unidad_medida: producto.unidad_medida,
            unidad_fraccion: producto.unidad_fraccion,
            unidades_por_caja: parseInt(producto.unidades_por_caja) || 1,
            tipo_unidad: 'CAJA',
            cantidad: 1,
            stock: stockTotal,
            requiere_receta: producto.requiere_receta,
            condicion_venta: producto.condicion_venta || 'Venta Libre',
            registro_sanitario: producto.registro_sanitario || ''
        };
    }
    renderCarrito();
    const buscador = document.getElementById("buscadorPOS");
    buscador.value = '';
    if (window.matchMedia('(min-width: 992px)').matches) buscador.focus();
}

function cambiarUnidad(id, tipo) {
    if(carrito[id]) {
        carrito[id].tipo_unidad = tipo;
        let stockRequerido = carrito[id].cantidad;
        if (tipo == 'CAJA' && carrito[id].fraccionable == 1) {
            stockRequerido = carrito[id].cantidad * carrito[id].unidades_por_caja;
        }
        if (stockRequerido > carrito[id].stock) {
            alert("No hay suficiente stock para vender en este formato (Stock disponible: " + carrito[id].stock + " unidades mínimas).");
            carrito[id].tipo_unidad = 'FRACCION';
        }
    }
    renderCarrito();
}

function modQty(id, delta) {
    if(carrito[id]) {
        let nueva = carrito[id].cantidad + delta;
        if(nueva <= 0) {
            delete carrito[id];
        } else {
            let stock_req = nueva;
            if (carrito[id].tipo_unidad == 'CAJA' && carrito[id].fraccionable == 1) {
                stock_req = nueva * carrito[id].unidades_por_caja;
            }
            if (stock_req > carrito[id].stock) {
                alert('Stock insuficiente (Disponibles: ' + carrito[id].stock + ' unidades mínimas).');
            } else {
                carrito[id].cantidad = nueva;
            }
        }
    }
    renderCarrito();
}

// Cantidad escrita directamente (valida contra el stock disponible)
function setQty(id, valor) {
    const item = carrito[id];
    if (!item) return;
    let nueva = parseInt(valor, 10);
    if (isNaN(nueva) || nueva < 1) {
        renderCarrito();
        return;
    }
    const factor = (item.tipo_unidad == 'CAJA' && item.fraccionable == 1) ? item.unidades_por_caja : 1;
    const maximo = Math.floor(item.stock / factor);
    if (nueva > maximo) {
        alert('Stock insuficiente. Máximo disponible: ' + maximo + ' (' + item.stock + ' unidades mínimas).');
        nueva = Math.max(1, maximo);
    }
    item.cantidad = nueva;
    renderCarrito();
}

function removeRow(id) {
    delete carrito[id];
    renderCarrito();
}

function renderCarrito() {
    let cont = document.getElementById('cartItems');
    let emptyMsg = document.getElementById('cartEmpty');

    let sum = 0;
    let rowsHtml = '';
    let formsHtml = '';
    let arrCart = Object.values(carrito);
    let requiereCmp = false;
    let medicamentosRetenidos = [];
    let unidades = 0;

    emptyMsg.style.display = arrCart.length === 0 ? 'block' : 'none';

    arrCart.forEach(item => {
        if(item.requiere_receta == 1) requiereCmp = true;
        if(item.condicion_venta === 'Receta Médica Retenida') medicamentosRetenidos.push(item.nombre);

        let precioUnit = item.tipo_unidad == 'CAJA' ? item.precio_caja : item.precio_fraccion;
        let subtotal = precioUnit * item.cantidad;
        sum += subtotal;
        unidades += item.cantidad;

        let comboUnidad = item.fraccionable == 1
            ? `<select class="cr-unit" onchange="cambiarUnidad(${item.id}, this.value)">
                <option value="CAJA" ${item.tipo_unidad == 'CAJA' ? 'selected' : ''}>${escHtml(item.unidad_medida)}</option>
                <option value="FRACCION" ${item.tipo_unidad == 'FRACCION' ? 'selected' : ''}>${escHtml(item.unidad_fraccion)}</option>
               </select>`
            : `<span class="cr-unit">${escHtml(item.unidad_medida)}</span>`;

        rowsHtml += `
        <div class="cart-row">
            <div class="cr-name">${escHtml(item.nombre)}</div>
            <div class="cr-sub">S/ ${subtotal.toFixed(2)}</div>
            <div class="cr-controls">
                <div class="qty">
                    <button type="button" onclick="modQty(${item.id}, -1)" aria-label="Quitar uno">−</button>
                    <input type="number" min="1" step="1" inputmode="numeric" value="${item.cantidad}" aria-label="Cantidad"
                        onfocus="this.select()" onchange="setQty(${item.id}, this.value)"
                        onkeydown="if(event.key==='Enter'){event.preventDefault(); this.blur();} else if(event.key==='Escape'){this.value=${item.cantidad}; this.blur();}">
                    <button type="button" onclick="modQty(${item.id}, 1)" aria-label="Agregar uno">+</button>
                </div>
                ${comboUnidad}
                <span class="cr-price">× S/ ${precioUnit.toFixed(2)}</span>
                <button type="button" class="btn-rm" onclick="removeRow(${item.id})" title="Quitar producto"><i class="bi bi-trash3"></i></button>
            </div>
        </div>`;

        formsHtml += `
            <input type="hidden" name="producto_id[]" value="${item.id}">
            <input type="hidden" name="cantidad[]" value="${item.cantidad}">
            <input type="hidden" name="precio_d[]" value="${precioUnit}">
            <input type="hidden" name="subtotal_d[]" value="${subtotal}">
            <input type="hidden" name="tipo_unidad[]" value="${item.tipo_unidad}">
        `;
    });

    cont.innerHTML = rowsHtml;
    document.getElementById('cartHiddenInputs').innerHTML = formsHtml;
    document.getElementById('tabCartCount').textContent = unidades;

    // CMP para productos con receta
    const cmpBlock = document.getElementById('cmpBlock');
    const inCmp = document.getElementById('inCmp');
    const adv = document.getElementById('advRetenidos');
    if(requiereCmp) {
        cmpBlock.style.display = 'block';
        inCmp.setAttribute('required', 'required');
        adv.innerHTML = medicamentosRetenidos.length > 0
            ? `<div class="mt-2" style="font-size: 11px; color: var(--danger); font-weight:700;">
                   <i class="bi bi-exclamation-triangle-fill"></i> RETENER RECETA FÍSICA para: ${escHtml(medicamentosRetenidos.join(', '))}. Archivar en el Libro de Control.
               </div>`
            : '';
    } else {
        cmpBlock.style.display = 'none';
        inCmp.removeAttribute('required');
        inCmp.value = '';
        adv.innerHTML = '';
    }

    // Validar descuento
    let inDescObj = document.getElementById('inDesc');
    let descuento = parseFloat(inDescObj.value) || 0;
    if(descuento < 0) { descuento = 0; inDescObj.value = '0.00'; }
    if(descuento > sum) { descuento = sum; inDescObj.value = descuento.toFixed(2); }

    // Si el descuento manual difiere del canje por puntos, se anula el canje
    if(clienteActual && clienteActual.id != 1) {
        let ptsObj = document.getElementById('fiPuso');
        let dEsperado = (parseFloat(ptsObj.value) || 0) / ratioCanje;
        if(Math.abs(dEsperado - descuento) > 0.01) ptsObj.value = 0;
    } else {
        document.getElementById('fiPuso').value = 0;
    }

    actualizarMotivoDescuento(descuento);

    let totalCobrar = sum - descuento;
    let factorIgv = (IGV_PCT / 100) + 1;
    let mIgv = totalCobrar - (totalCobrar / factorIgv);
    let mSubSec = totalCobrar - mIgv;

    document.getElementById('txtSub').innerText = 'S/ ' + mSubSec.toFixed(2);
    document.getElementById('txtIgv').innerText = 'S/ ' + mIgv.toFixed(2);
    document.getElementById('txtTot').innerText = 'S/ ' + totalCobrar.toFixed(2);
    document.getElementById('cobroTotal').innerText = 'S/ ' + totalCobrar.toFixed(2);

    document.getElementById('fiSub').value = mSubSec.toFixed(2);
    document.getElementById('fiIgv').value = mIgv.toFixed(2);
    document.getElementById('fiTot').value = totalCobrar.toFixed(2);
    document.getElementById('fiDesc').value = descuento.toFixed(2);

    document.getElementById('btnAbrirCobro').disabled = arrCart.length === 0;

    calcularVuelto();
    if(document.getElementById('btnMixto').checked) calcularMixto();
}

// -----------------------------------------
// PESTAÑAS RESPONSIVE (MÓVIL / TABLET)
// -----------------------------------------
function switchPosTab(tab) {
    const cat = document.querySelector('.pos-catalog-col');
    const tic = document.querySelector('.pos-ticket-col');
    const esCart = tab === 'cart';
    tic.classList.toggle('active', esCart);
    cat.classList.toggle('active', !esCart);
    document.getElementById('btnTabCart').classList.toggle('active', esCart);
    document.getElementById('btnTabCatalog').classList.toggle('active', !esCart);
    if (!esCart) setTimeout(() => document.getElementById('buscadorPOS').focus(), 150);
}

// -----------------------------------------
// REGISTRO RÁPIDO DE CLIENTE
// -----------------------------------------
function ajustarTipoDocPos() {
    const tipo = document.getElementById('nuevoCliTipoDoc').value;
    const numInput = document.getElementById('nuevoCliNumDoc');
    const help = document.getElementById('posDocHelp');
    if (tipo === 'DNI') {
        numInput.maxLength = 8;
        numInput.placeholder = 'Ej: 70854120';
        if (help) help.textContent = '(8 díg.)';
    } else if (tipo === 'RUC') {
        numInput.maxLength = 11;
        numInput.placeholder = 'Ej: 20601234567';
        if (help) help.textContent = '(11 díg.)';
    } else {
        numInput.maxLength = 15;
        numInput.placeholder = 'Ej: P12345678';
        if (help) help.textContent = '(4-15 car.)';
    }
}

function limpiarNumeroDocPos(input) {
    const tipo = document.getElementById('nuevoCliTipoDoc').value;
    if (tipo === 'DNI' || tipo === 'RUC') {
        input.value = input.value.replace(/\D/g, '');
    }
}

function guardarClientePos(e) {
    e.preventDefault();
    const btn = document.getElementById('btnGuardarClientePos');
    const alertBox = document.getElementById('alertaErrorClientePos');
    alertBox.classList.add('d-none');

    const tipo = document.getElementById('nuevoCliTipoDoc').value;
    const num = document.getElementById('nuevoCliNumDoc').value.trim();

    if (tipo === 'DNI' && !/^\d{8}$/.test(num)) {
        alertBox.textContent = '❌ El DNI debe contener exactamente 8 dígitos numéricos.';
        alertBox.classList.remove('d-none');
        document.getElementById('nuevoCliNumDoc').focus();
        return;
    }
    if (tipo === 'RUC' && !/^\d{11}$/.test(num)) {
        alertBox.textContent = '❌ El RUC debe contener exactamente 11 dígitos numéricos.';
        alertBox.classList.remove('d-none');
        document.getElementById('nuevoCliNumDoc').focus();
        return;
    }
    if (tipo !== 'DNI' && tipo !== 'RUC' && num.length < 4) {
        alertBox.textContent = '❌ El número de documento debe tener al menos 4 caracteres.';
        alertBox.classList.remove('d-none');
        document.getElementById('nuevoCliNumDoc').focus();
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    const form = document.getElementById('formNuevoClientePos');
    const formData = new FormData(form);
    const csrfEl = document.querySelector('input[name="csrf_token"]');
    if (csrfEl) formData.append('csrf_token', csrfEl.value);

    fetch('<?php echo BASE_URL; ?>cliente/saveAjax', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Guardar y Asignar';
        if (data.success && data.cliente) {
            const c = data.cliente;
            if (!clientesPos.find(x => x.id == c.id)) {
                clientesPos.push({ id: parseInt(c.id), tipo: c.tipo_documento || formData.get('tipo_documento') || 'DOC', doc: c.num_documento, nombres: c.nombres, puntos: 0 });
            }
            seleccionarCliente(c.id);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNuevoClientePos')).hide();
            form.reset();
            ajustarTipoDocPos();
        } else {
            alertBox.textContent = data.error || 'Error al registrar cliente.';
            alertBox.classList.remove('d-none');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Guardar y Asignar';
        alertBox.textContent = 'Error de comunicación con el servidor.';
        alertBox.classList.remove('d-none');
    });
}

// -----------------------------------------
// COBRO: MÉTODOS DE PAGO Y VUELTO
// -----------------------------------------
const modalCobroEl = document.getElementById('modalCobro');

// Boleta y Factura exigen cliente identificado; Factura además exige RUC
function esClienteRuc(c) {
    return !!c && (String(c.tipo).toUpperCase() === 'RUC' || /^(10|15|17|20)\d{9}$/.test(String(c.doc)));
}

function problemaComprobante() {
    const tipo = document.querySelector('input[name="tipo_comprobante"]:checked').value;
    const c = clienteActual;
    const sinCliente = !c || c.id == 1;
    if (tipo === 'Boleta' && sinCliente) return 'La boleta requiere un cliente identificado (DNI/RUC).';
    if (tipo === 'Factura' && sinCliente) return 'La factura requiere un cliente con RUC.';
    if (tipo === 'Factura' && !esClienteRuc(c)) return 'La factura requiere un cliente con RUC (el actual tiene ' + (c.tipo || 'DOC') + ').';
    return '';
}

function validarComprobante() {
    const msg = problemaComprobante();
    const warn = document.getElementById('compWarn');
    document.getElementById('compWarnTxt').textContent = msg;
    warn.classList.toggle('show', msg !== '');
    const c = clienteActual;
    document.getElementById('compCli').innerHTML = msg === '' && c
        ? '<i class="bi bi-person"></i> ' + escHtml(c.id == 1 ? 'Público General' : (c.tipo + ' ' + c.doc + ' · ' + c.nombres))
        : '';
    return msg === '';
}

let reabrirCobroTrasCliente = false;
function elegirClienteDesdeCobro() {
    reabrirCobroTrasCliente = true;
    bootstrap.Modal.getOrCreateInstance(modalCobroEl).hide();
    if (window.matchMedia('(max-width: 991px)').matches) switchPosTab('cart');
    setTimeout(() => { inputFiltroCli.focus(); }, 350);
}

function abrirCobro(metodo) {
    if (Object.keys(carrito).length === 0) { alert("El carrito está vacío. Agregue productos antes de cobrar."); return; }
    if (!validarMotivoDescuento()) return;
    let cmp = document.getElementById('inCmp');
    if (cmp.hasAttribute('required') && cmp.value.trim() === '') {
        alert("Atención: Ha incluido productos con receta. Debe ingresar el CMP del médico.");
        if (window.matchMedia('(max-width: 991px)').matches) switchPosTab('cart');
        cmp.focus();
        return;
    }
    if (metodo) {
        const map = { 'Efectivo': 'btnEfecti', 'Yape/Plin': 'btnYape', 'Tarjeta': 'btnTarj', 'Mixto': 'btnMixto' };
        document.getElementById(map[metodo]).checked = true;
    }
    renderQuickCash();
    validarComprobante();
    bootstrap.Modal.getOrCreateInstance(modalCobroEl).show();
}

modalCobroEl.addEventListener('shown.bs.modal', function() {
    cambiarMetodoPago(document.querySelector('input[name="metodo_pago"]:checked').value);
});
modalCobroEl.addEventListener('hidden.bs.modal', function() {
    if (reabrirCobroTrasCliente) return;
    if (window.matchMedia('(min-width: 992px)').matches) document.getElementById('buscadorPOS').focus();
});

// Billetes peruanos acumulables (ej. 2 × S/ 200, o 2 × S/ 10 + 1 × S/ 50)
const BILLETES = [10, 20, 50, 100, 200];
const billetesUsados = { efe: [], mix: [] }; // historial en orden, para "Deshacer"
const BILL_INPUT = { efe: 'inPago', mix: 'inPagoEfeMixto' };

function recalcularCtx(ctx) { if (ctx === 'efe') calcularVuelto(); else calcularMixto(); }

function renderBilletes(ctx) {
    const usados = billetesUsados[ctx];
    const conteo = {};
    usados.forEach(b => conteo[b] = (conteo[b] || 0) + 1);
    BILLETES.forEach(b => {
        const el = document.getElementById('billCount_' + ctx + '_' + b);
        el.textContent = conteo[b] ? '×' + conteo[b] : '';
        el.classList.toggle('show', !!conteo[b]);
    });
    const partes = BILLETES.filter(b => conteo[b]).reverse().map(b => `${conteo[b]} × S/ ${b}`);
    document.getElementById('billDetalle_' + ctx).textContent = partes.join(' + ');
}

function aplicarBilletes(ctx) {
    const suma = billetesUsados[ctx].reduce((a, b) => a + b, 0);
    document.getElementById(BILL_INPUT[ctx]).value = suma > 0 ? suma.toFixed(2) : '';
    renderBilletes(ctx);
    recalcularCtx(ctx);
}

function sumarBillete(ctx, b) { billetesUsados[ctx].push(b); aplicarBilletes(ctx); }

function quitarBillete(ctx, b) {
    const i = billetesUsados[ctx].lastIndexOf(b);
    if (i > -1) billetesUsados[ctx].splice(i, 1);
    aplicarBilletes(ctx);
}

function deshacerBillete(ctx) { billetesUsados[ctx].pop(); aplicarBilletes(ctx); }

function limpiarBilletes(ctx) { billetesUsados[ctx] = []; aplicarBilletes(ctx); document.getElementById(BILL_INPUT[ctx]).focus(); }

function pagoExacto(ctx) {
    const monto = ctx === 'efe' ? getTotal() : (parseFloat(document.getElementById('inMontoEfeMixto').value) || 0);
    billetesUsados[ctx] = [];
    renderBilletes(ctx);
    const inp = document.getElementById(BILL_INPUT[ctx]);
    inp.value = money(monto);
    recalcularCtx(ctx);
    inp.focus();
}

// Si el cajero escribe el monto a mano, se descarta el conteo de billetes
function onPagoManual(ctx) {
    if (billetesUsados[ctx].length) { billetesUsados[ctx] = []; renderBilletes(ctx); }
    recalcularCtx(ctx);
}

function renderQuickCash() { renderBilletes('efe'); renderBilletes('mix'); }

function cambiarMetodoPago(metodo) {
    document.getElementById('panelEfectivo').style.display = (metodo === 'Efectivo') ? 'block' : 'none';
    document.getElementById('panelYape').style.display = (metodo === 'Yape/Plin') ? 'block' : 'none';
    document.getElementById('panelTarjeta').style.display = (metodo === 'Tarjeta') ? 'block' : 'none';
    document.getElementById('panelMixto').style.display = (metodo === 'Mixto') ? 'block' : 'none';

    const total = getTotal();
    if (metodo === 'Efectivo') {
        document.getElementById('inPago').focus();
        calcularVuelto();
    } else if (metodo === 'Yape/Plin') {
        const inOpTrans = document.getElementById('inOpTrans');
        if (!inOpTrans.value.trim()) inOpTrans.value = 'YAPE-' + Math.floor(100000 + Math.random() * 900000);
        document.getElementById('inPago').value = total.toFixed(2);
        document.getElementById('inVuelto').value = '0.00';
        inOpTrans.focus();
    } else if (metodo === 'Tarjeta') {
        const inOpTarj = document.getElementById('inOpTarj');
        if (!inOpTarj.value.trim()) inOpTarj.value = 'TARJ-' + Math.floor(100000 + Math.random() * 900000);
        document.getElementById('inPago').value = total.toFixed(2);
        document.getElementById('inVuelto').value = '0.00';
        inOpTarj.focus();
    } else if (metodo === 'Mixto') {
        const inMontoTrans = document.getElementById('inMontoTransMixto');
        const inMontoTarj = document.getElementById('inMontoTarjMixto');
        if (!inMontoTrans.value && !inMontoTarj.value && total > 0) {
            const mitad = Math.round((total / 2) * 100) / 100;
            inMontoTarj.value = mitad.toFixed(2);
        }
        calcularMixto();
        inMontoTarj.focus();
    }
}

function calcularVuelto() {
    let total = getTotal();
    let raw = document.getElementById('inPago').value;
    let pago = parseFloat(raw) || 0;
    let vuelto = pago >= total && pago > 0 ? pago - total : 0;
    document.getElementById('inVuelto').value = vuelto.toFixed(2);
    pintarCambio('boxVuelto', 'lblVuelto', raw, pago, total);
}

function pintarCambio(boxId, lblId, raw, pago, debe) {
    const box = document.getElementById(boxId);
    const lbl = document.getElementById(lblId);
    const titulo = box.querySelector('.c-lbl');
    box.classList.remove('ok', 'bad');
    if (raw === '') { titulo.textContent = 'Vuelto'; lbl.textContent = '0.00'; }
    else if (pago < debe) { box.classList.add('bad'); titulo.textContent = 'Falta'; lbl.textContent = (debe - pago).toFixed(2); }
    else { box.classList.add('ok'); titulo.textContent = 'Vuelto'; lbl.textContent = (pago - debe).toFixed(2); }
}

function calcularMixto() {
    const total = getTotal();
    const tra = parseFloat(document.getElementById('inMontoTransMixto').value) || 0;
    const tar = parseFloat(document.getElementById('inMontoTarjMixto').value) || 0;
    const efe = Math.round((total - tra - tar) * 100) / 100;

    document.getElementById('mixRowTrans').classList.toggle('filled', tra > 0);
    document.getElementById('mixRowTarj').classList.toggle('filled', tar > 0);

    const box = document.getElementById('mixCash');
    const msg = document.getElementById('mixCashMsg');
    const payBlock = document.getElementById('mixCashPay');
    box.classList.remove('zero', 'error');

    document.getElementById('inMontoEfeMixto').value = Math.max(0, efe).toFixed(2);
    document.getElementById('lblEfeMixto').textContent = Math.max(0, efe).toFixed(2);

    if (efe < 0) {
        box.classList.add('error');
        msg.textContent = 'Excede el total por S/ ' + Math.abs(efe).toFixed(2);
        payBlock.style.display = 'none';
    } else if (efe === 0) {
        box.classList.add('zero');
        msg.textContent = '';
        payBlock.style.display = 'none';
    } else {
        msg.textContent = '';
        payBlock.style.display = 'block';
        const inPagoMix = document.getElementById('inPagoEfeMixto');
        if (!inPagoMix.value || parseFloat(inPagoMix.value) <= 0) {
            inPagoMix.value = efe.toFixed(2);
        }
        const raw = inPagoMix.value;
        pintarCambio('boxVueltoMixto', 'lblVueltoMixto', raw, parseFloat(raw) || 0, efe);
    }

}

function confirmarVenta() {
    let total = getTotal();
    if(total <= 0 && Object.keys(carrito).length === 0) {
        alert("El carrito está vacío. Agregue productos antes de cobrar.");
        return;
    }

    if (!validarMotivoDescuento()) { bootstrap.Modal.getOrCreateInstance(modalCobroEl).hide(); return; }

    if (!validarComprobante()) {
        alert(problemaComprobante());
        return;
    }

    let metodo = document.querySelector('input[name="metodo_pago"]:checked').value;
    const inPago = document.getElementById('inPago');
    const inOpTrans = document.getElementById('inOpTrans');
    const inOpTarj = document.getElementById('inOpTarj');

    if(metodo === 'Efectivo') {
        let pago = parseFloat(inPago.value) || 0;
        if (pago <= 0) {
            alert("Ingrese el efectivo recibido (o pulse \"Exacto\").");
            inPago.focus();
            return;
        }
        if (pago < total) {
            alert("El efectivo recibido (S/ " + pago.toFixed(2) + ") es menor al total de la venta (S/ " + total.toFixed(2) + ").");
            inPago.focus();
            return;
        }
        inOpTrans.value = ''; inOpTarj.value = '';
    }
    else if (metodo === 'Yape/Plin') {
        if (inOpTrans.value.trim() === '') {
            inOpTrans.value = 'YAPE-' + Math.floor(100000 + Math.random() * 900000);
        }
        inPago.value = total.toFixed(2);
        document.getElementById('inVuelto').value = '0.00';
        inOpTarj.value = '';
    }
    else if (metodo === 'Tarjeta') {
        if (inOpTarj.value.trim() === '') {
            inOpTarj.value = 'TARJ-' + Math.floor(100000 + Math.random() * 900000);
        }
        inPago.value = total.toFixed(2);
        document.getElementById('inVuelto').value = '0.00';
        inOpTrans.value = '';
    }
    else if (metodo === 'Mixto') {
        calcularMixto();
        let tra = parseFloat(document.getElementById('inMontoTransMixto').value) || 0;
        let tar = parseFloat(document.getElementById('inMontoTarjMixto').value) || 0;
        let efe = Math.round((total - tra - tar) * 100) / 100;

        if (tra + tar <= 0) {
            tar = Math.round((total / 2) * 100) / 100;
            document.getElementById('inMontoTarjMixto').value = tar.toFixed(2);
            efe = Math.round((total - tar) * 100) / 100;
            document.getElementById('inMontoEfeMixto').value = efe.toFixed(2);
        }
        if (efe < 0) {
            alert("Yape/Plin + tarjeta (S/ " + (tra + tar).toFixed(2) + ") superan el total a pagar (S/ " + total.toFixed(2) + ").");
            return;
        }

        let opTra = document.getElementById('inOpTransMixto').value.trim();
        if (tra > 0 && opTra === '') {
            opTra = 'YAPE-' + Math.floor(100000 + Math.random() * 900000);
            document.getElementById('inOpTransMixto').value = opTra;
        }
        let opTar = document.getElementById('inOpTarjMixto').value.trim();
        if (tar > 0 && opTar === '') {
            opTar = 'TARJ-' + Math.floor(100000 + Math.random() * 900000);
            document.getElementById('inOpTarjMixto').value = opTar;
        }

        if (efe > 0) {
            const inPagoMix = document.getElementById('inPagoEfeMixto');
            let pagoEfe = parseFloat(inPagoMix.value) || 0;
            if (pagoEfe <= 0) {
                pagoEfe = efe;
                inPagoMix.value = efe.toFixed(2);
            }
            if (pagoEfe < efe) {
                alert("El efectivo entregado (S/ " + pagoEfe.toFixed(2) + ") no cubre los S/ " + efe.toFixed(2) + " a cobrar en efectivo.");
                inPagoMix.focus();
                return;
            }
            inPago.value = pagoEfe;
        } else {
            inPago.value = 0;
        }

        document.getElementById('inMontoEfeMixto').value = Math.max(0, efe).toFixed(2);
        inOpTrans.value = tra > 0 ? opTra : '';
        inOpTarj.value = tar > 0 ? opTar : '';
    }

    let cmp = document.getElementById('inCmp');
    if(cmp.hasAttribute('required') && cmp.value.trim() === '') {
        bootstrap.Modal.getOrCreateInstance(modalCobroEl).hide();
        alert("Atención: Ha incluido productos con receta. Debe ingresar el CMP del médico.");
        cmp.focus();
        return;
    }

    const btn = document.getElementById('btnConfirmarVenta');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Procesando...';
    }
    document.getElementById('formVenta').submit();
}

// -----------------------------------------
// ATAJOS DE TECLADO Y LECTOR DE CÓDIGOS
// -----------------------------------------
document.addEventListener('keydown', function(e) {
    const cobroAbierto = modalCobroEl.classList.contains('show');
    const algunModal = document.querySelector('.modal.show');

    if (e.key === 'F2') {
        e.preventDefault();
        if (algunModal) return;
        if (window.matchMedia('(max-width: 991px)').matches) switchPosTab('catalog');
        document.getElementById('buscadorPOS').focus();
    }
    if (e.key === 'F4') {
        e.preventDefault();
        if (!cobroAbierto) abrirCobro('Efectivo');
        else { document.getElementById('btnEfecti').checked = true; cambiarMetodoPago('Efectivo'); }
    }
    if (e.key === 'F10') {
        e.preventDefault();
        if (cobroAbierto) confirmarVenta(); else if (!algunModal) abrirCobro();
    }
    // Enter dentro del modal de cobro confirma la venta
    if (e.key === 'Enter' && cobroAbierto && e.target.tagName !== 'BUTTON') {
        e.preventDefault();
        confirmarVenta();
    }

    // Lector de código de barras: si no hay foco en un campo, redirigir al buscador
    if (!algunModal && !['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
        if (e.key.length === 1 && /[a-zA-Z0-9\-]/.test(e.key)) {
            document.getElementById('buscadorPOS').focus();
        }
    }
});

// Evitar que Enter en un campo del ticket (motivo, CMP) envíe la venta sin validar
document.getElementById('formVenta').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.target.tagName === 'INPUT') e.preventDefault();
});

// Notificaciones: cierre automático (se pausa con el mouse encima) y manual con la X
document.querySelectorAll('.pos-flash[data-autoclose]').forEach(el => {
    const ms = parseInt(el.dataset.autoclose) || 8000;
    const bar = el.querySelector('.pos-flash-bar');
    let restante = ms, inicio = Date.now(), timer = null;
    const cerrar = () => bootstrap.Alert.getOrCreateInstance(el).close();
    const iniciar = () => { inicio = Date.now(); timer = setTimeout(cerrar, restante); };
    if (bar) bar.style.animation = `posFlashBar ${ms}ms linear forwards`;
    el.addEventListener('mouseenter', () => { clearTimeout(timer); restante -= Date.now() - inicio; el.classList.add('paused'); });
    el.addEventListener('mouseleave', () => { el.classList.remove('paused'); iniciar(); });
    iniciar();
});

// Inicialización
evaluarClientePuntos();
</script>
