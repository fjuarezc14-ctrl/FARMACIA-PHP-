<?php
/**
 * Script de Poblamiento de Datos para el Sistema de Botica
 * Genera 10 registros detallados en cada módulo del sistema
 * distribuidos en diferentes fechas de los últimos 10 días para alimentar el Dashboard.
 */

// Asegurar ejecución solo desde consola CLI para evitar accesos no deseados
if (php_sapi_name() !== 'cli') {
    die("Error: Este script solo puede ejecutarse desde la consola de comandos.\n");
}

echo "=========================================================\n";
echo " INICIANDO POBLAMIENTO DE DATOS - SISTEMA DE BOTICA\n";
echo "=========================================================\n";

try {
    // 1. Conexión a la base de datos
    $db = new PDO('mysql:host=localhost;dbname=botica_db;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "[CONEXIÓN] Conectado exitosamente a la base de datos 'botica_db'.\n\n";
    
    // Iniciar transacción general para asegurar consistencia
    $db->beginTransaction();
    
    // Configurar zona horaria de la base de datos
    $db->exec("SET time_zone = '-05:00'");
    
    // 2. Insertar Roles (Verificar si ya existen, si no los insertamos)
    // Los roles base son: 1-Administrador, 2-Farmacéutico, 3-Cajero, 4-Almacenero
    // Ya están definidos en botica_db.sql, los omitimos o verificamos.
    
    // 3. Insertar 10 Usuarios (Roles 2, 3, 4 y 1)
    echo "[1/15] Insertando 10 Usuarios de prueba con roles distribuidos...\n";
    $usuarios_data = [
        ['Carlos', 'Mendoza Pérez', 'cajero1', 3, 'cajero1@botica.com'],
        ['Carmen', 'Ortiz Ruiz', 'cajero2', 3, 'cajero2@botica.com'],
        ['Roberto', 'Gómez Castro', 'cajero3', 3, 'cajero3@botica.com'],
        ['Sandra', 'Solis Vargas', 'farmaceutico1', 2, 'farma1@botica.com'],
        ['Lilian', 'Ruiz Huamán', 'farmaceutico2', 2, 'farma2@botica.com'],
        ['Jorge', 'Valdivia Paz', 'farmaceutico3', 2, 'farma3@botica.com'],
        ['Mario', 'Vargas Luna', 'almacenero1', 4, 'almacen1@botica.com'],
        ['Elena', 'Vargas Solís', 'almacenero2', 4, 'almacen2@botica.com'],
        ['Ricardo', 'Arana Silva', 'almacenero3', 4, 'almacen3@botica.com'],
        ['Martha', 'Vilchez Jara', 'admin2', 1, 'admin2@botica.com']
    ];
    
    $password_hash = password_hash('password123', PASSWORD_BCRYPT);
    $inserted_usuarios = [];
    
    $stmtUser = $db->prepare("INSERT INTO `usuarios` (`nombres`, `apellidos`, `usuario`, `password`, `email`, `rol_id`, `estado`) VALUES (?, ?, ?, ?, ?, ?, 1)");
    
    foreach ($usuarios_data as $u) {
        // Verificar si ya existe para no duplicar llaves únicas
        $chk = $db->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $chk->execute([$u[2]]);
        $existing = $chk->fetchColumn();
        
        if ($existing) {
            $inserted_usuarios[] = $existing;
        } else {
            $stmtUser->execute([$u[0], $u[1], $u[2], $password_hash, $u[4], $u[3]]);
            $inserted_usuarios[] = $db->lastInsertId();
        }
    }
    echo "  -> Éxito: 10 Usuarios listos (IDs: " . implode(', ', $inserted_usuarios) . ").\n";

    // 4. Insertar 10 Categorías
    echo "[2/15] Insertando 10 Categorías farmacéuticas...\n";
    $categorias_data = [
        ['Oftálmicos', 'Medicamentos para el cuidado de los ojos y vista'],
        ['Pediatría', 'Medicamentos especializados para niños y bebés'],
        ['Dermatología', 'Productos para el cuidado de la piel y afecciones cutáneas'],
        ['Suplementos Dietéticos', 'Vitaminas, minerales y complementos nutricionales'],
        ['Antiinflamatorios', 'Tratamiento del dolor e inflamación general'],
        ['Antigripales', 'Medicamentos para el alivio de la gripe y resfrío'],
        ['Cardiovasculares', 'Control de la presión arterial y salud cardíaca'],
        ['Antidiabéticos', 'Regulación de glucosa e insulina en sangre'],
        ['Anestésicos', 'Alivio del dolor localizado y adormecimiento'],
        ['Gastrointestinales', 'Tratamiento de acidez, reflujo y dolor estomacal']
    ];
    $inserted_categorias = [];
    $stmtCat = $db->prepare("INSERT INTO `categorias` (`nombre`, `descripcion`, `estado`) VALUES (?, ?, 1)");
    foreach ($categorias_data as $c) {
        $chk = $db->prepare("SELECT id FROM categorias WHERE nombre = ?");
        $chk->execute([$c[0]]);
        $existing = $chk->fetchColumn();
        if ($existing) {
            $inserted_categorias[] = $existing;
        } else {
            $stmtCat->execute([$c[0], $c[1]]);
            $inserted_categorias[] = $db->lastInsertId();
        }
    }
    echo "  -> Éxito: 10 Categorías listas.\n";

    // 5. Insertar 10 Laboratorios/Marcas
    echo "[3/15] Insertando 10 Laboratorios/Fabricantes...\n";
    $laboratorios_data = [
        ['Bayer Farmacéutica', 'Laboratorio multinacional de origen alemán'],
        ['Pfizer S.A.', 'Líder mundial en investigación y desarrollo médico'],
        ['Roche Diagnostics', 'Innovación en tratamientos oncológicos y diagnósticos'],
        ['Sanofi Aventis', 'Especialistas en vacunas y tratamientos crónicos'],
        ['Abbott Laboratorios', 'Nutrición, diagnóstico y medicamentos genéricos de calidad'],
        ['Novartis Perú', 'Líder en tratamientos cardiovasculares y oftalmológicos'],
        ['GlaxoSmithKline (GSK)', 'Especialistas en salud respiratoria y vacunas infantiles'],
        ['Merck Sharp & Dohme', 'Medicamentos biológicos y de alta especialidad'],
        ['Laboratorios Genfar', 'Genéricos de confianza y accesibles'],
        ['Laboratorios Hersil', 'Fabricante nacional peruano de gran trayectoria']
    ];
    $inserted_laboratorios = [];
    $stmtLab = $db->prepare("INSERT INTO `laboratorios` (`nombre`, `descripcion`, `estado`) VALUES (?, ?, 1)");
    foreach ($laboratorios_data as $l) {
        $chk = $db->prepare("SELECT id FROM laboratorios WHERE nombre = ?");
        $chk->execute([$l[0]]);
        $existing = $chk->fetchColumn();
        if ($existing) {
            $inserted_laboratorios[] = $existing;
        } else {
            $stmtLab->execute([$l[0], $l[1]]);
            $inserted_laboratorios[] = $db->lastInsertId();
        }
    }
    echo "  -> Éxito: 10 Laboratorios listos.\n";

    // 6. Insertar 10 Productos
    echo "[4/15] Insertando 10 Productos con precios y especificaciones...\n";
    $productos_data = [
        ['7750000000010', 'Tobramicina 0.3%', 'Tobrex Gotas', '5ml', 'Gotas', 0.50], // Compra, Venta=1.20 * margen
        ['7750000000020', 'Paracetamol 120mg/5ml', 'Panadol Jarabe', '120ml', 'Jarabe', 3.50],
        ['7750000000030', 'Crema Regeneradora', 'Bepanthen Crema', '30g', 'Crema', 8.50],
        ['7750000000040', 'Complejo Vitamínico', 'Supradyn Activo', '30 Tabletas', 'Efervescente', 12.00],
        ['7750000000050', 'Ibuprofeno 400mg', 'Actron Rápida', '400mg', 'Cápsula', 0.30],
        ['7750000000060', 'Antigripal Completo', 'GripaCid Forte', 'Tableta', 'Tableta', 0.40],
        ['7750000000070', 'Atenolol 50mg', 'Plendil 50', '50mg', 'Tableta', 0.60],
        ['7750000000080', 'Metformina 850mg', 'Glucophage XR', '850mg', 'Tableta', 0.80],
        ['7750000000090', 'Lidocaína Jalea 2%', 'Xilocaína Gel', '20g', 'Jalea', 4.50],
        ['7750000000100', 'Omeprazol 20mg', 'Losec Gastro', '20mg', 'Cápsula', 0.20]
    ];
    
    $inserted_productos = [];
    $stmtProd = $db->prepare("INSERT INTO `productos` 
        (`codigo_barras`, `nombre_generico`, `nombre_comercial`, `concentracion`, `forma_farmaceutica`, 
         `id_laboratorio`, `id_categoria`, `precio_compra`, `precio_venta`, `margen_ganancia`, 
         `unidad_medida`, `requiere_receta`, `stock_actual`, `stock_minimo`, `estado`, `fraccionable`, 
         `unidades_por_caja`, `unidad_fraccion`, `precio_fraccion`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'UNIDAD', ?, 0, 15, 1, 0, 1, NULL, 0.00)");
    
    for ($i = 0; $i < 10; $i++) {
        $p = $productos_data[$i];
        $bar = $p[0];
        
        $chk = $db->prepare("SELECT id FROM productos WHERE codigo_barras = ?");
        $chk->execute([$bar]);
        $existing = $chk->fetchColumn();
        
        if ($existing) {
            $inserted_productos[] = $existing;
        } else {
            $p_compra = $p[5];
            $p_venta = round($p_compra * 2.2, 1); // 120% margen ganancia aproximado
            $margen = round((($p_venta - $p_compra) / $p_compra) * 100, 2);
            $req_receta = ($i == 0 || $i == 6) ? 1 : 0; // Tobramicina y Atenolol con receta
            
            $stmtProd->execute([
                $bar, 
                $p[1], 
                $p[2], 
                $p[3], 
                $p[4], 
                $inserted_laboratorios[$i], 
                $inserted_categorias[$i], 
                $p_compra, 
                $p_venta, 
                $margen, 
                $req_receta
            ]);
            $inserted_productos[] = $db->lastInsertId();
        }
    }
    echo "  -> Éxito: 10 Productos listos.\n";

    // 7. Insertar 10 Clientes
    echo "[5/15] Insertando 10 Clientes registrados...\n";
    $clientes_data = [
        ['DNI', '41234567', 'Alejandro Toledo Manrique', '944333222', 'Av. Las Palmas 550, Miraflores', 25],
        ['DNI', '42234568', 'Alan García Pérez', '955444333', 'Calle Monte Umbroso 120, Surco', 80],
        ['DNI', '43234569', 'Ollanta Humala Tasso', '966555444', 'Jr. Junín 430, San Borja', 110],
        ['DNI', '44234570', 'Keiko Fujimori Higuchi', '977666555', 'Av. El Golf 320, La Molina', 15],
        ['DNI', '45234571', 'Pedro Pablo Kuczynski', '988777666', 'Calle Choquehuanca 880, San Isidro', 230],
        ['DNI', '46234572', 'Martín Vizcarra Cornejo', '999888777', 'Av. Alameda Central 440, San Borja', 5],
        ['DNI', '47234573', 'Manuel Merino de Lama', '911222333', 'Calle Tumbes 910, Pueblo Libre', 45],
        ['DNI', '48234574', 'Francisco Sagasti Hochhausler', '922333444', 'Av. Arequipa 3210, Lince', 140],
        ['DNI', '49234575', 'Pedro Castillo Terrones', '933444555', 'Jr. Chota 1020, Lima Cercado', 0],
        ['DNI', '40234576', 'Dina Boluarte Zegarra', '944555666', 'Av. Canadá 1420, San Luis', 95]
    ];
    $inserted_clientes = [];
    $stmtCli = $db->prepare("INSERT INTO `clientes` (`tipo_documento`, `num_documento`, `nombres`, `telefono`, `direccion`, `puntos_acumulados`, `estado`) VALUES (?, ?, ?, ?, ?, ?, 1)");
    foreach ($clientes_data as $c) {
        $chk = $db->prepare("SELECT id FROM clientes WHERE num_documento = ?");
        $chk->execute([$c[1]]);
        $existing = $chk->fetchColumn();
        if ($existing) {
            $inserted_clientes[] = $existing;
        } else {
            $stmtCli->execute([$c[0], $c[1], $c[2], $c[3], $c[4], $c[5]]);
            $inserted_clientes[] = $db->lastInsertId();
        }
    }
    echo "  -> Éxito: 10 Clientes listos.\n";

    // 8. Insertar 10 Proveedores
    echo "[6/15] Insertando 10 Proveedores comerciales...\n";
    $proveedores_data = [
        ['20112233440', 'Droguería Alfa S.A.C.', 'Lic. Roberto Solís', '01-4456789', 'Av. Separadora Industrial 1240, Ate'],
        ['20223344551', 'Distribuidora FarmaRed Perú', 'Sra. Patricia Gómez', '01-5678901', 'Jr. Carabaya 550, Lima Cercado'],
        ['20334455662', 'Global Pharma Medical Solutions', 'Ing. David Chang', '01-2244668', 'Calle Las Gemas 245, Urb. El Derby, Surco'],
        ['20445566773', 'Inversiones Médicas del Sur E.I.R.L.', 'Dra. Andrea Luján', '054-203040', 'Av. Ejército 740, Arequipa'],
        ['20556677884', 'Sagitario Farma Logística', 'Sr. Hugo Martínez', '044-607080', 'Jr. Francisco Pizarro 650, Trujillo'],
        ['20667788995', 'Servicios Médicos Integrales del Perú', 'Sra. Verónica Castro', '01-3344556', 'Av. Petit Thouars 4210, Miraflores'],
        ['20778899006', 'Comercializadora Boticas Unidas', 'Sr. Luis Huamán', '01-9988776', 'Av. Nicolás Arriola 1850, La Victoria'],
        ['20889900117', 'Logística FarmaNorte S.A.', 'Ing. César Acuña', '044-242526', 'Av. Larco 1420, Trujillo'],
        ['20990011228', 'Química Continental Farmacéutica', 'Central de Ventas', '01-7112000', 'Av. Paseo de la República 5640, Miraflores'],
        ['20101122339', 'Proveedor Médico Nacional S.A.C.', 'Dra. Sandra Solís', '01-8153000', 'Av. Los Frutales 450, Ate']
    ];
    $inserted_proveedores = [];
    $stmtProv = $db->prepare("INSERT INTO `proveedores` (`ruc`, `razon_social`, `representante`, `telefono`, `direccion`, `estado`) VALUES (?, ?, ?, ?, ?, 1)");
    foreach ($proveedores_data as $p) {
        $chk = $db->prepare("SELECT id FROM proveedores WHERE ruc = ?");
        $chk->execute([$p[0]]);
        $existing = $chk->fetchColumn();
        if ($existing) {
            $inserted_proveedores[] = $existing;
        } else {
            $stmtProv->execute([$p[0], $p[1], $p[2], $p[3], $p[4]]);
            $inserted_proveedores[] = $db->lastInsertId();
        }
    }
    echo "  -> Éxito: 10 Proveedores listos.\n";

    // 9. Insertar 10 Cajas (Aperturas y Cierres en los últimos 10 días)
    echo "[7/15] Insertando 10 Cajas (Apertura y Cierre) con fechas distribuidas...\n";
    $inserted_cajas = [];
    
    // Generar cajas desde hace 9 días hasta hoy
    $stmtCaja = $db->prepare("INSERT INTO `cajas` 
        (`usuario_id`, `fecha_apertura`, `fecha_cierre`, `monto_inicial`, `ingresos_efectivo`, `ingresos_transferencia`, 
         `monto_final_esperado`, `monto_final_real`, `diferencia`, `observacion`, `estado`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
    for ($d = 9; $d >= 0; $d--) {
        $fecha_ap = date('Y-m-d 08:00:00', strtotime("-$d days"));
        $fecha_ci = date('Y-m-d 22:00:00', strtotime("-$d days"));
        
        $cajero_idx = ($d % 3); // Rotar entre cajeros
        $cajero_id = $inserted_usuarios[$cajero_idx];
        
        $m_inicial = 300.00;
        $ing_efec = round(rand(400, 1200), 2);
        $ing_trans = round(rand(200, 800), 2);
        $m_esperado = $m_inicial + $ing_efec;
        
        if ($d == 0) {
            // La caja de hoy permanece ABIERTA
            $stmtCaja->execute([$cajero_id, $fecha_ap, null, $m_inicial, 0.00, 0.00, null, null, null, 'Caja de hoy abierta en producción', 1]);
        } else {
            // Las demás cajas están CERRADAS
            $diff = (rand(0, 5) == 5) ? round((rand(1, 15) / 10), 2) : 0.00; // Ocasional descuadre de céntimos
            $m_real = $m_esperado + $diff;
            $stmtCaja->execute([$cajero_id, $fecha_ap, $fecha_ci, $m_inicial, $ing_efec, $ing_trans, $m_esperado, $m_real, $diff, 'Cierre de turno correcto sin novedades', 0]);
        }
        $inserted_cajas[] = $db->lastInsertId();
    }
    
    // Obtener caja abierta hoy para procesos posteriores
    $caja_hoy_id = end($inserted_cajas);
    echo "  -> Éxito: 10 Cajas creadas. Caja de hoy ID: $caja_hoy_id.\n";

    // 10. Insertar 10 Movimientos de Caja (Ingresos/Egresos)
    echo "[8/15] Insertando 10 Caja Movimientos (Ingresos y Egresos)...\n";
    $movimientos_data = [
        ['EGRESO', 120.00, 'Pago del servicio de energía eléctrica (Luz del Sur)'],
        ['EGRESO', 45.00, 'Pago de servicio de agua potable (Sedapal)'],
        ['INGRESO', 100.00, 'Aporte extraordinario de caja chica por cambio'],
        ['EGRESO', 35.00, 'Compra de útiles de escritorio para el POS (rollos térmicos)'],
        ['EGRESO', 15.00, 'Pago de movilidad de delivery urgente'],
        ['EGRESO', 80.00, 'Pago de servicio de internet y telefonía fija (Movistar)'],
        ['INGRESO', 150.00, 'Reposición de fondos por devolución de garantía'],
        ['EGRESO', 25.00, 'Servicios de limpieza y desinfección local'],
        ['EGRESO', 50.00, 'Compra de botiquín de primeros auxilios interno'],
        ['EGRESO', 18.00, 'Pago por copias y fotocopia de manuales de inducción']
    ];
    $stmtCajaMov = $db->prepare("INSERT INTO `caja_movimientos` (`caja_id`, `tipo`, `monto`, `motivo`, `fecha_movimiento`) VALUES (?, ?, ?, ?, ?)");
    for ($i = 0; $i < 10; $i++) {
        $caja_id = $inserted_cajas[$i]; // Repartir 1 por cada caja
        $m = $movimientos_data[$i];
        
        $fecha_mov = date('Y-m-d 12:00:00', strtotime("-" . (9 - $i) . " days"));
        $stmtCajaMov->execute([$caja_id, $m[0], $m[1], $m[2], $fecha_mov]);
    }
    echo "  -> Éxito: 10 Caja Movimientos insertados.\n";

    // 11. Insertar 10 Compras con Detalle
    echo "[9/15] Insertando 10 Compras de medicamentos con sus detalles...\n";
    $stmtCompra = $db->prepare("INSERT INTO `compras` 
        (`id_proveedor`, `id_usuario`, `tipo_comprobante`, `serie_comprobante`, `num_comprobante`, `fecha_compra`, `total`, `estado`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Completada')");
        
    $stmtDetalleCompra = $db->prepare("INSERT INTO `compra_detalles` (`id_compra`, `id_producto`, `cantidad`, `precio_unitario`, `subtotal`) VALUES (?, ?, ?, ?, ?)");
    
    $inserted_compras = [];
    $inserted_compra_detalles = [];
    
    for ($i = 0; $i < 10; $i++) {
        $prov_id = $inserted_proveedores[$i];
        $almacenero_id = $inserted_usuarios[6 + ($i % 3)]; // Almaceneros en IDs 6, 7, 8
        
        $fecha_compra = date('Y-m-d', strtotime("-" . (9 - $i) . " days"));
        $serie = 'F00' . ($i + 1);
        $numero = '3000' . ($i + 1);
        
        $producto_id = $inserted_productos[$i];
        // Obtener precio de compra del producto
        $p_compra = $productos_data[$i][5];
        $cantidad = 100; // Comprar 100 unidades
        $subtotal = $cantidad * $p_compra;
        
        $stmtCompra->execute([$prov_id, $almacenero_id, 'Factura', $serie, $numero, $fecha_compra, $subtotal]);
        $compra_id = $db->lastInsertId();
        $inserted_compras[] = $compra_id;
        
        $stmtDetalleCompra->execute([$compra_id, $producto_id, $cantidad, $p_compra, $subtotal]);
        $inserted_compra_detalles[] = $db->lastInsertId();
    }
    echo "  -> Éxito: 10 Compras y Detalles guardados.\n";

    // 12. Insertar 10 Lotes de Inventario (3 en riesgo FEFO y 7 saludables)
    echo "[10/15] Creando 10 Lotes de inventario (FEFO alert check)...\n";
    $stmtLote = $db->prepare("INSERT INTO `inventario_lotes` 
        (`id_producto`, `id_compra_detalle`, `codigo_lote`, `fecha_vencimiento`, `cantidad_inicial`, `cantidad_disponible`, `estado`) 
        VALUES (?, ?, ?, ?, ?, ?, 1)");
        
    $inserted_lotes = [];
    
    for ($i = 0; $i < 10; $i++) {
        $prod_id = $inserted_productos[$i];
        $comp_det_id = $inserted_compra_detalles[$i];
        $lote_cod = 'LT-SE-' . (1000 + $i);
        
        // Configurar fechas de vencimiento:
        // Los primeros 3 lotes vencerán en menos de 90 días (riesgo FEFO para el dashboard)
        if ($i == 0) {
            $fecha_venc = date('Y-m-d', strtotime("+15 days")); // Tobramicina vence pronto
        } elseif ($i == 1) {
            $fecha_venc = date('Y-m-d', strtotime("+45 days")); // Paracetamol vence pronto
        } elseif ($i == 2) {
            $fecha_venc = date('Y-m-d', strtotime("+75 days")); // Bepanthen vence pronto
        } else {
            // Los otros 7 lotes vencen lejos (más de 1 año)
            $fecha_venc = date('Y-m-d', strtotime("+" . (400 + ($i * 10)) . " days"));
        }
        
        $cant_ini = 100;
        $stmtLote->execute([$prod_id, $comp_det_id, $lote_cod, $fecha_venc, $cant_ini, $cant_ini]);
        $inserted_lotes[] = $db->lastInsertId();
        
        // Actualizar el stock_actual del producto correspondiente en la tabla productos
        $upd = $db->prepare("UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?");
        $upd->execute([$cant_ini, $prod_id]);
    }
    echo "  -> Éxito: 10 Lotes ingresados. 3 de ellos con riesgo FEFO (<90 días).\n";

    // 13. Insertar 10 Ventas con Detalles (Distribuidas para nutrir el gráfico semanal y medios de pago)
    echo "[11/15] Insertando 10 Ventas completadas con detalles (distribuidas en 7 días)...\n";
    $stmtVenta = $db->prepare("INSERT INTO `ventas` 
        (`id_cliente`, `caja_id`, `id_usuario`, `tipo_comprobante`, `serie_comprobante`, `num_comprobante`, `fecha_venta`, 
         `subtotal`, `descuento`, `igv`, `total`, `metodo_pago`, `pago_recibido`, `vuelto`, `puntos_ganados`, `puntos_usados`, `estado`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0.00, ?, ?, ?, ?, ?, ?, 0, 'Completada')");
        
    $stmtVentaDetalle = $db->prepare("INSERT INTO `venta_detalles` 
        (`id_venta`, `id_producto`, `cantidad`, `precio_unitario`, `subtotal`, `id_lote`, `tipo_unidad`) 
        VALUES (?, ?, ?, ?, ?, ?, 'Unidad')");
        
    $inserted_ventas = [];
    
    // Distribución en 7 días para el gráfico semanal
    // Días de desfase: 6, 5, 4, 3, 2, 1, 0, 0, 0, 0 (múltiples ventas hoy y ayer)
    $dias_desfase = [6, 5, 4, 3, 2, 1, 0, 0, 0, 0];
    $metodos_pago = ['Efectivo', 'Tarjeta', 'Yape/Plin', 'Efectivo', 'Tarjeta', 'Yape/Plin', 'Efectivo', 'Tarjeta', 'Yape/Plin', 'Efectivo'];
    
    for ($i = 0; $i < 10; $i++) {
        $cli_id = $inserted_clientes[$i];
        $cajero_id = $inserted_usuarios[$i % 3]; // Cajeros en 0, 1, 2
        
        $desfase = $dias_desfase[$i];
        // Enlazar a la caja correspondiente a ese día
        $caja_id = $inserted_cajas[9 - $desfase];
        
        $fecha_venta = date('Y-m-d H:i:s', strtotime("-$desfase days") - ($i * 1200)); // Horas ligeramente distintas
        
        $serie = 'B001';
        $numero = '4500' . ($i + 1);
        
        // Vender 5 unidades del producto correspondiente
        $prod_id = $inserted_productos[$i];
        
        // Obtener precio de venta real del producto
        $q_prod = $db->prepare("SELECT precio_venta FROM productos WHERE id = ?");
        $q_prod->execute([$prod_id]);
        $p_venta = $q_prod->fetchColumn();
        
        $cantidad = 5;
        $tot_venta = $cantidad * $p_venta;
        
        $subtotal = round($tot_venta / 1.18, 2);
        $igv = round($tot_venta - $subtotal, 2);
        
        $m_pago = $metodos_pago[$i];
        $pago_rec = ($m_pago == 'Efectivo') ? ceil($tot_venta / 10) * 10 : $tot_venta; // Vuelto simulado
        $vuelto = $pago_rec - $tot_venta;
        
        $puntos_gan = floor($tot_venta / 10); // 1 punto por cada 10 soles
        
        $stmtVenta->execute([
            $cli_id, $caja_id, $cajero_id, 'Boleta', $serie, $numero, $fecha_venta,
            $subtotal, $igv, $tot_venta, $m_pago, $pago_rec, $vuelto, $puntos_gan
        ]);
        $venta_id = $db->lastInsertId();
        $inserted_ventas[] = $venta_id;
        
        // Insertar detalle
        $lote_id = $inserted_lotes[$i];
        $stmtVentaDetalle->execute([$venta_id, $prod_id, $cantidad, $p_venta, $tot_venta, $lote_id]);
        
        // Descontar del lote
        $upd_lote = $db->prepare("UPDATE inventario_lotes SET cantidad_disponible = cantidad_disponible - ? WHERE id = ?");
        $upd_lote->execute([$cantidad, $lote_id]);
        
        // Descontar del stock del producto
        $upd_prod = $db->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?");
        $upd_prod->execute([$cantidad, $prod_id]);
        
        // Si la venta es hoy, acumular a la caja activa para que se vea reflejado el ingreso
        if ($desfase == 0 && $caja_id == $caja_hoy_id) {
            if ($m_pago == 'Efectivo') {
                $upd_caja = $db->prepare("UPDATE cajas SET ingresos_efectivo = ingresos_efectivo + ? WHERE id = ?");
                $upd_caja->execute([$tot_venta, $caja_hoy_id]);
            } else {
                $upd_caja = $db->prepare("UPDATE cajas SET ingresos_transferencia = ingresos_transferencia + ? WHERE id = ?");
                $upd_caja->execute([$tot_venta, $caja_hoy_id]);
            }
        }
    }
    echo "  -> Éxito: 10 Ventas insertadas. El gráfico de los últimos 7 días está completamente nutrido.\n";

    // 14. Insertar 10 Devoluciones a Proveedores con Detalle
    echo "[12/15] Insertando 10 Devoluciones a Proveedores...\n";
    $stmtDev = $db->prepare("INSERT INTO `compras_devoluciones` 
        (`id_compra`, `id_usuario`, `num_documento_prov`, `motivo`, `total_devuelto`, `fecha_devolucion`) 
        VALUES (?, ?, ?, ?, ?, ?)");
        
    $stmtDetalleDev = $db->prepare("INSERT INTO `compras_devolucion_detalles` 
        (`id_devolucion`, `id_producto`, `id_lote`, `cantidad`, `precio_costo`, `subtotal`) 
        VALUES (?, ?, ?, ?, ?, ?)");
        
    $motivos_dev = [
        'Presenta empaque secundario dañado por humedad',
        'Próximo a vencer en lote enviado por error',
        'Medicamento con concentración incorrecta según factura',
        'Exceso de stock enviado de forma no solicitada',
        'Fecha de vencimiento inferior a 6 meses de margen',
        'Empaque primario con falla de sellado de aluminio',
        'Error de envío: Se solicitaba blíster y llegó frasco',
        'Deterioro de etiquetas del laboratorio',
        'Falta de código de barras legible en empaque',
        'Medicamento devuelto por alerta sanitaria de lote'
    ];
    
    for ($i = 0; $i < 10; $i++) {
        $compra_id = $inserted_compras[$i];
        $almacenero_id = $inserted_usuarios[6 + ($i % 3)]; // Almacenero
        
        $fecha_dev = date('Y-m-d', strtotime("-" . (9 - $i) . " days"));
        $num_doc = 'NC-PR-' . (5000 + $i);
        
        $prod_id = $inserted_productos[$i];
        $lote_id = $inserted_lotes[$i];
        $p_compra = $productos_data[$i][5];
        $cant_dev = 2; // Devolver 2 unidades
        $subt = $cant_dev * $p_compra;
        
        $stmtDev->execute([$compra_id, $almacenero_id, $num_doc, $motivos_dev[$i], $subt, $fecha_dev]);
        $dev_id = $db->lastInsertId();
        
        $stmtDetalleDev->execute([$dev_id, $prod_id, $lote_id, $cant_dev, $p_compra, $subt]);
        
        // Descontar del lote por devolución
        $upd_l = $db->prepare("UPDATE inventario_lotes SET cantidad_disponible = cantidad_disponible - ? WHERE id = ?");
        $upd_l->execute([$cant_dev, $lote_id]);
        
        // Descontar del producto
        $upd_p = $db->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?");
        $upd_p->execute([$cant_dev, $prod_id]);
    }
    echo "  -> Éxito: 10 Devoluciones a Proveedores listas.\n";

    // 15. Insertar 10 Auditorías de Inventario con Detalles
    echo "[13/15] Insertando 10 Auditorías de Inventario Físico...\n";
    $stmtAud = $db->prepare("INSERT INTO `inventario_auditorias` (`id_usuario`, `fecha_inicio`, `fecha_fin`, `estado`, `observaciones`) VALUES (?, ?, ?, ?, ?)");
    $stmtAudDet = $db->prepare("INSERT INTO `inventario_auditoria_detalles` (`id_auditoria`, `id_lote`, `stock_sistema`, `stock_fisico`, `diferencia`) VALUES (?, ?, ?, ?, ?)");
    
    // Crear una sola auditoría general
    $almacenero_id = $inserted_usuarios[6];
    $fecha_ini = date('Y-m-d 09:00:00', strtotime("-5 days"));
    $fecha_fin = date('Y-m-d 18:00:00', strtotime("-5 days"));
    
    $stmtAud->execute([$almacenero_id, $fecha_ini, $fecha_fin, 'Finalizada', 'Auditoría física trimestral general de medicamentos nuevos']);
    $auditoria_id = $db->lastInsertId();
    
    // Insertar 10 detalles (uno por lote de producto)
    for ($i = 0; $i < 10; $i++) {
        $lote_id = $inserted_lotes[$i];
        
        // Obtener stock actual en sistema del lote
        $q_lote = $db->prepare("SELECT cantidad_disponible FROM inventario_lotes WHERE id = ?");
        $q_lote->execute([$lote_id]);
        $stock_sis = $q_lote->fetchColumn();
        
        // Ocasional diferencia física de prueba
        $diff = ($i == 4 || $i == 8) ? -1 : 0; // Faltante de 1 unidad en index 4 y 8
        $stock_fis = $stock_sis + $diff;
        
        $stmtAudDet->execute([$auditoria_id, $lote_id, $stock_sis, $stock_fis, $diff]);
        
        // Si hay diferencia, corregir el stock del lote y producto automáticamente
        if ($diff != 0) {
            $db->prepare("UPDATE inventario_lotes SET cantidad_disponible = ? WHERE id = ?")->execute([$stock_fis, $lote_id]);
            $db->prepare("UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?")->execute([$diff, $inserted_productos[$i]]);
        }
    }
    echo "  -> Éxito: 1 Auditoría General registrada con 10 Detalle de lotes de medicamentos.\n";

    // 16. Insertar 10 Movimientos de Kardex
    echo "[14/15] Insertando 10 Historiales de Kardex (Entradas, Salidas, Ajustes)...\n";
    $stmtKardex = $db->prepare("INSERT INTO `kardex` (`id_producto`, `id_usuario`, `tipo_movimiento`, `motivo`, `cantidad`, `saldo_actual`, `fecha`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    for ($i = 0; $i < 10; $i++) {
        $prod_id = $inserted_productos[$i];
        $almacenero_id = $inserted_usuarios[6 + ($i % 3)];
        
        $fecha_k = date('Y-m-d 10:00:00', strtotime("-" . (9 - $i) . " days"));
        
        // Obtener saldo real de stock del producto actual
        $q_stock = $db->prepare("SELECT stock_actual FROM productos WHERE id = ?");
        $q_stock->execute([$prod_id]);
        $saldo = $q_stock->fetchColumn();
        
        // Tipo de movimiento rotativo
        if ($i % 3 == 0) {
            $stmtKardex->execute([$prod_id, $almacenero_id, 'ENTRADA', 'Ingreso inicial por Compra Facturada', 100, $saldo, $fecha_k]);
        } elseif ($i % 3 == 1) {
            $stmtKardex->execute([$prod_id, $almacenero_id, 'SALIDA', 'Salida por venta en mostrador POS', 5, $saldo, $fecha_k]);
        } else {
            $stmtKardex->execute([$prod_id, $almacenero_id, 'AJUSTE', 'Corrección de diferencia por inventario físico', -1, $saldo, $fecha_k]);
        }
    }
    echo "  -> Éxito: 10 Registros de Kardex creados.\n";

    // 17. Insertar 10 Registros de Seguridad e Inicios de Sesión
    echo "[15/15] Creando 10 Registros en Bitácoras de Auditoría...\n";
    $stmtAcc = $db->prepare("INSERT INTO `audit_accesos` (`id_usuario`, `accion`, `ip_address`, `user_agent`, `fecha`) VALUES (?, ?, ?, ?, ?)");
    $stmtAcciones = $db->prepare("INSERT INTO `audit_acciones` (`id_usuario`, `modulo`, `accion`, `descripcion`, `monto_afectado`, `fecha`) VALUES (?, ?, ?, ?, ?, ?)");
    
    $ips = ['192.168.1.50', '192.168.1.62', '127.0.0.1', '10.0.0.8', '192.168.0.100', '172.16.0.4', '192.168.1.12', '192.168.1.9', '186.42.12.98', '190.235.14.88'];
    $browsers = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/17.2',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) Mobile/15E148',
        'Mozilla/5.0 (Linux; Android 10; K) Chrome/120.0 Mobile'
    ];
    
    $modulos = ['Seguridad', 'Productos', 'Clientes', 'Proveedores', 'Caja', 'Compras', 'Ventas', 'Inventario', 'Auditoría', 'Configuración'];
    $acciones = ['CREAR', 'EDITAR', 'CREAR', 'EDITAR', 'EGRESO', 'CONFIRMAR', 'REGISTRAR', 'AJUSTE', 'CERRAR', 'MODIFICAR'];
    $descripciones = [
        'Registro de nuevos usuarios en el sistema',
        'Modificación de precios de venta de Amoxicilina',
        'Registro del cliente VIP Alejandro Toledo',
        'Actualización de datos de Droguería Alfa RUC',
        'Egreso de caja chica para pago de energía eléctrica',
        'Confirmación y recepción de lote de Tobramicina',
        'Emisión de boleta electrónica Nro B001-45001',
        'Ajuste manual de stock por merma de jarabes rotos',
        'Cierre de auditoría trimestral de inventario físico',
        'Modificación de razón social comercial y logo de botica'
    ];
    $montos = [0.00, 0.00, 0.00, 0.00, 120.00, 50.00, 6.00, 0.00, 0.00, 0.00];
    
    for ($i = 0; $i < 10; $i++) {
        $u_id = $inserted_usuarios[$i];
        $fecha_aud = date('Y-m-d H:i:s', strtotime("-" . (9 - $i) . " days") + ($i * 600));
        
        // Inserción en accesos (LOGIN / LOGOUT)
        $acc_tipo = ($i % 2 == 0) ? 'LOGIN' : 'LOGOUT';
        $stmtAcc->execute([$u_id, $acc_tipo, $ips[$i], $browsers[$i % 5], $fecha_aud]);
        
        // Inserción en acciones críticas
        $stmtAcciones->execute([$u_id, $modulos[$i], $acciones[$i], $descripciones[$i], $montos[$i], $fecha_aud]);
    }
    echo "  -> Éxito: 10 Accesos y 10 Acciones registradas en Bitácoras.\n\n";

    // 18. Confirmar Transacción
    $db->commit();
    
    echo "=========================================================\n";
    echo " ¡POBLAMIENTO COMPLETADO EXITOSAMENTE SIN ERRORES! \n";
    echo "=========================================================\n";
    echo "Todos los módulos han sido inyectados con 10 registros de\n";
    echo "prueba premium, con fechas distribuidas y relaciones íntegras.\n";
    echo "Los paneles y gráficos del Dashboard ya reflejan la información.\n";
    echo "=========================================================\n";

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "\n!!! ERROR CRÍTICO DETECTADO EN EL POBLAMIENTO !!!\n";
    echo "Detalle del error: " . $e->getMessage() . "\n";
    echo "La transacción ha sido cancelada para proteger los datos.\n";
    exit(1);
}
