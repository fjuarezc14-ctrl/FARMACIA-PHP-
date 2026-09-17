<?php
require_once __DIR__ . '/SimpleXlsxReader.php';

/**
 * ExcelProductImporter - Servicio de importación masiva de catálogo desde archivos .xlsx
 */
class ExcelProductImporter {
    private $conn;
    private $idUsuario;

    public function __construct($pdoConnection, $idUsuario = 1) {
        $this->conn = $pdoConnection;
        $this->idUsuario = (int)$idUsuario;
    }

    /**
     * Limpia y formatea números decimales (soporta formatos con coma o punto)
     */
    public static function limpiarDecimal($valor, $default = 0.00) {
        if ($valor === null || $valor === '') return $default;
        $v = trim((string)$valor);
        // Quitar espacios y símbolos de moneda
        $v = preg_replace('/[^\d.,\-]/', '', $v);
        // Reemplazar coma por punto si viene como decimal
        $v = str_replace(',', '.', $v);
        return is_numeric($v) ? (float)$v : $default;
    }

    /**
     * Limpia y formatea números enteros
     */
    public static function limpiarEntero($valor, $default = 0) {
        if ($valor === null || $valor === '') return $default;
        $v = trim((string)$valor);
        $v = preg_replace('/[^\d\-]/', '', $v);
        return is_numeric($v) ? (int)$v : $default;
    }

    /**
     * Normaliza fechas de vencimiento desde texto o números seriales de Excel
     */
    public static function normalizarFecha($valor) {
        $v = trim((string)$valor);
        if (empty($v) || $v === '0000-00-00' || $v === '0' || $v === '00/00/0000') {
            return '2099-12-31';
        }

        // Si es número serial de Excel (ej. 45678)
        if (is_numeric($v) && (int)$v > 30000 && (int)$v < 70000) {
            $timestamp = ((int)$v - 25569) * 86400;
            return gmdate('Y-m-d', $timestamp);
        }

        // Formato DD/MM/YYYY o DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $v, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        // Formato YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
            return $v;
        }

        return '2099-12-31';
    }

    /**
     * Procesa e importa el archivo Excel completo
     * @param string $filePath Ruta absoluta al archivo .xlsx
     * @param array $options Opciones de importación
     * @return array Estadísticas y resultado del proceso
     */
    public function procesarArchivo($filePath, $options = []) {
        $crearCategorias = $options['crear_categorias'] ?? true;
        $crearLaboratorios = $options['crear_laboratorios'] ?? true;
        $actualizarExistentes = $options['actualizar_existentes'] ?? true;

        $reader = new SimpleXlsxReader($filePath);
        $rows = $reader->readRows();

        if (empty($rows)) {
            throw new Exception("El archivo Excel no contiene filas o está vacío.");
        }

        // 1. Detectar fila de encabezados
        $headerRowIndex = -1;
        $colMap = [];

        foreach ($rows as $idx => $row) {
            $normalizedRow = array_map(function($c) {
                return strtolower(trim((string)$c));
            }, $row);

            $foundCodigo = false;
            $foundNombre = false;

            foreach ($normalizedRow as $colIdx => $headerText) {
                if (strpos($headerText, 'codigo') !== false || strpos($headerText, 'código') !== false) {
                    $foundCodigo = true;
                }
                if (strpos($headerText, 'nombre') !== false || strpos($headerText, 'descripcion') !== false || strpos($headerText, 'descripción') !== false) {
                    $foundNombre = true;
                }
            }

            if ($foundCodigo && $foundNombre) {
                $headerRowIndex = $idx;
                // Mapear columnas encontradas
                foreach ($normalizedRow as $cIdx => $val) {
                    if (strpos($val, 'codigo') !== false || strpos($val, 'código') !== false) {
                        $colMap['codigo'] = $cIdx;
                    } elseif (strpos($val, 'categoria') !== false || strpos($val, 'categoría') !== false) {
                        $colMap['categoria'] = $cIdx;
                    } elseif (strpos($val, 'laboratorio') !== false) {
                        $colMap['laboratorio'] = $cIdx;
                    } elseif (strpos($val, 'nombre') !== false || strpos($val, 'descripcion') !== false) {
                        $colMap['nombre'] = $cIdx;
                    } elseif (strpos($val, 'precio caja') !== false) {
                        $colMap['precio_caja'] = $cIdx;
                    } elseif (strpos($val, 'pvp1') !== false || $val === 'precio venta' || $val === 'pvp') {
                        $colMap['pvp1'] = $cIdx;
                    } elseif (strpos($val, 'pvp2') !== false || strpos($val, 'mayor') !== false) {
                        $colMap['pvp2'] = $cIdx;
                    } elseif (strpos($val, 'costo') !== false || strpos($val, 'compra') !== false) {
                        $colMap['precio_compra'] = $cIdx;
                    } elseif (strpos($val, 'blister') !== false || strpos($val, 'fraccion') !== false) {
                        $colMap['precio_fraccion'] = $cIdx;
                    } elseif ($val === 'stock' || strpos($val, 'cant') !== false) {
                        if (!isset($colMap['stock'])) $colMap['stock'] = $cIdx;
                    } elseif (strpos($val, 'stock min') !== false || strpos($val, 'minimo') !== false) {
                        $colMap['stock_min'] = $cIdx;
                    } elseif (strpos($val, 'lote') !== false) {
                        $colMap['lote'] = $cIdx;
                    } elseif (strpos($val, 'vencimiento') !== false || strpos($val, 'f. venc') !== false) {
                        $colMap['vencimiento'] = $cIdx;
                    } elseif (strpos($val, 'prin a') !== false || strpos($val, 'principio') !== false) {
                        $colMap['prin_a'] = $cIdx;
                    }
                }
                break;
            }
        }

        if ($headerRowIndex === -1 || !isset($colMap['nombre'])) {
            throw new Exception("No se detectaron los encabezados requeridos (debe contener al menos 'Codigo' y 'Nombre').");
        }

        // Cache de categorías y laboratorios para máxima velocidad
        $catCache = [];
        $labCache = [];

        $stmtCat = $this->conn->query("SELECT id, LOWER(TRIM(nombre)) as nom FROM categorias");
        while ($r = $stmtCat->fetch(PDO::FETCH_ASSOC)) {
            $catCache[$r['nom']] = (int)$r['id'];
        }

        $stmtLab = $this->conn->query("SELECT id, LOWER(TRIM(nombre)) as nom FROM laboratorios");
        while ($r = $stmtLab->fetch(PDO::FETCH_ASSOC)) {
            $labCache[$r['nom']] = (int)$r['id'];
        }

        $stats = [
            'total_filas' => 0,
            'creados' => 0,
            'actualizados' => 0,
            'omitidos' => 0,
            'lotes_creados' => 0,
            'categorias_creadas' => 0,
            'laboratorios_creados' => 0,
            'errores' => []
        ];

        // Preparar sentencias SQL para optimizar rendimiento
        $stmtInsertProd = $this->conn->prepare("
            INSERT INTO productos (
                codigo_barras, nombre_generico, codigo_prin_activo, nombre_comercial,
                concentracion, forma_farmaceutica, registro_sanitario, condicion_venta,
                id_laboratorio, id_categoria, precio_compra, precio_venta, precio_mayor,
                margen_ganancia, unidad_medida, requiere_receta, stock_actual, stock_minimo,
                estado, fraccionable, unidades_por_caja, unidad_fraccion, precio_fraccion
            ) VALUES (
                :cb, :ng, :cpa, :nc,
                :conc, :ff, :rs, 'Venta Libre',
                :idl, :idc, :pc, :pv, :pmay,
                :mg, :um, 0, :stk, :sm,
                1, :frac, :upc, :ufrac, :pfrac
            )
        ");

        $stmtUpdateProd = $this->conn->prepare("
            UPDATE productos SET 
                codigo_barras = COALESCE(:cb, codigo_barras),
                codigo_prin_activo = :cpa,
                id_laboratorio = COALESCE(:idl, id_laboratorio),
                id_categoria = COALESCE(:idc, id_categoria),
                precio_compra = :pc,
                precio_venta = :pv,
                precio_mayor = :pmay,
                margen_ganancia = :mg,
                stock_minimo = :sm,
                precio_fraccion = :pfrac,
                stock_actual = stock_actual + :stk_add
            WHERE id = :id
        ");

        $stmtInsertLote = $this->conn->prepare("
            INSERT INTO inventario_lotes (id_producto, codigo_lote, fecha_vencimiento, cantidad_inicial, cantidad_disponible, estado)
            VALUES (:prod, :lote, :venc, :cant, :cant, 1)
        ");

        $stmtInsertKardex = $this->conn->prepare("
            INSERT INTO kardex (id_producto, id_usuario, tipo_movimiento, motivo, cantidad, saldo_actual)
            VALUES (:prod, :usr, 'ENTRADA', :motivo, :cant, :saldo)
        ");

        $stmtFindCB = $this->conn->prepare("SELECT id, stock_actual FROM productos WHERE codigo_barras = :cb LIMIT 1");
        $stmtFindNom = $this->conn->prepare("SELECT id, stock_actual FROM productos WHERE LOWER(TRIM(nombre_comercial)) = :nom LIMIT 1");

        // Iniciar Transacción
        $this->conn->beginTransaction();

        try {
            $totalFilas = count($rows);

            for ($i = $headerRowIndex + 1; $i < $totalFilas; $i++) {
                $r = $rows[$i];
                $stats['total_filas']++;

                $nombre = isset($colMap['nombre']) ? trim((string)($r[$colMap['nombre']] ?? '')) : '';
                if (empty($nombre)) {
                    $stats['omitidos']++;
                    continue;
                }

                // Código de barras (limpiar comillas simples iniciales como '00000007101)
                $codigo = isset($colMap['codigo']) ? trim((string)($r[$colMap['codigo']] ?? '')) : '';
                $codigo = ltrim($codigo, "'");
                $codigo = empty($codigo) ? null : $codigo;

                // Categoría
                $catId = null;
                if (isset($colMap['categoria'])) {
                    $catNombre = trim((string)($r[$colMap['categoria']] ?? ''));
                    if (!empty($catNombre)) {
                        $key = strtolower($catNombre);
                        if (isset($catCache[$key])) {
                            $catId = $catCache[$key];
                        } elseif ($crearCategorias) {
                            $insC = $this->conn->prepare("INSERT INTO categorias (nombre, descripcion, estado) VALUES (:n, 'Importado de catálogo Excel', 1)");
                            $insC->execute([':n' => $catNombre]);
                            $catId = (int)$this->conn->lastInsertId();
                            $catCache[$key] = $catId;
                            $stats['categorias_creadas']++;
                        }
                    }
                }

                // Laboratorio
                $labId = null;
                if (isset($colMap['laboratorio'])) {
                    $labNombre = trim((string)($r[$colMap['laboratorio']] ?? ''));
                    if (!empty($labNombre)) {
                        $key = strtolower($labNombre);
                        if (isset($labCache[$key])) {
                            $labId = $labCache[$key];
                        } elseif ($crearLaboratorios) {
                            $insL = $this->conn->prepare("INSERT INTO laboratorios (nombre, descripcion, estado) VALUES (:n, 'Importado de catálogo Excel', 1)");
                            $insL->execute([':n' => $labNombre]);
                            $labId = (int)$this->conn->lastInsertId();
                            $labCache[$key] = $labId;
                            $stats['laboratorios_creados']++;
                        }
                    }
                }

                // Precios y Márgenes
                $precioVenta = isset($colMap['pvp1']) ? self::limpiarDecimal($r[$colMap['pvp1']], 0.00) : 0.00;
                $precioMayor = isset($colMap['pvp2']) ? self::limpiarDecimal($r[$colMap['pvp2']], null) : null;
                if ($precioMayor !== null && $precioMayor <= 0) $precioMayor = null;

                $precioCompra = isset($colMap['precio_compra']) ? self::limpiarDecimal($r[$colMap['precio_compra']], 0.00) : 0.00;
                $precioFraccion = isset($colMap['precio_fraccion']) ? self::limpiarDecimal($r[$colMap['precio_fraccion']], 0.00) : 0.00;

                $margen = 0.00;
                if ($precioCompra > 0 && $precioVenta > $precioCompra) {
                    $margen = round((($precioVenta - $precioCompra) / $precioCompra) * 100, 2);
                    $margen = min(999999.99, max(0.00, $margen));
                }

                // Principio Activo
                $codPrinActivo = null;
                if (isset($colMap['prin_a'])) {
                    $cpaVal = self::limpiarEntero($r[$colMap['prin_a']], 0);
                    if ($cpaVal > 0) $codPrinActivo = $cpaVal;
                }

                // Stock y Lotes
                $stock = isset($colMap['stock']) ? max(0, self::limpiarEntero($r[$colMap['stock']], 0)) : 0;
                $stockMin = isset($colMap['stock_min']) ? max(0, self::limpiarEntero($r[$colMap['stock_min']], 2)) : 2;

                $loteCodigo = isset($colMap['lote']) ? trim((string)($r[$colMap['lote']] ?? '')) : '';
                if (empty($loteCodigo) || $loteCodigo === '0') $loteCodigo = 'P. SIN LOTE';

                $fechaVenc = isset($colMap['vencimiento']) ? self::normalizarFecha($r[$colMap['vencimiento']] ?? '') : '2099-12-31';

                // Fraccionamiento por defecto
                $fraccionable = ($precioFraccion > 0) ? 1 : 0;
                $unidadesPorCaja = 1;
                $unidadFraccion = ($fraccionable == 1) ? 'Blíster' : null;

                // Buscar producto existente
                $existingProduct = null;
                if ($codigo) {
                    $stmtFindCB->execute([':cb' => $codigo]);
                    $existingProduct = $stmtFindCB->fetch(PDO::FETCH_ASSOC);
                }
                if (!$existingProduct) {
                    $stmtFindNom->execute([':nom' => strtolower($nombre)]);
                    $existingProduct = $stmtFindNom->fetch(PDO::FETCH_ASSOC);
                }

                if ($existingProduct && $actualizarExistentes) {
                    $prodId = (int)$existingProduct['id'];
                    $stockAnterior = (int)$existingProduct['stock_actual'];
                    $nuevoSaldo = $stockAnterior + $stock;

                    $stmtUpdateProd->execute([
                        ':cb' => $codigo,
                        ':cpa' => $codPrinActivo,
                        ':idl' => $labId,
                        ':idc' => $catId,
                        ':pc' => $precioCompra,
                        ':pv' => $precioVenta,
                        ':pmay' => $precioMayor,
                        ':mg' => $margen,
                        ':sm' => $stockMin,
                        ':pfrac' => $precioFraccion,
                        ':stk_add' => $stock,
                        ':id' => $prodId
                    ]);

                    if ($stock > 0) {
                        $stmtInsertLote->execute([
                            ':prod' => $prodId,
                            ':lote' => $loteCodigo,
                            ':venc' => $fechaVenc,
                            ':cant' => $stock
                        ]);
                        $stmtInsertKardex->execute([
                            ':prod' => $prodId,
                            ':usr' => $this->idUsuario,
                            ':motivo' => 'Carga Adicional Excel (Lote ' . $loteCodigo . ')',
                            ':cant' => $stock,
                            ':saldo' => $nuevoSaldo
                        ]);
                        $stats['lotes_creados']++;
                    }

                    $stats['actualizados']++;

                } elseif (!$existingProduct) {
                    // Inserción de nuevo producto
                    $stmtInsertProd->execute([
                        ':cb' => $codigo,
                        ':ng' => $nombre, // Nombre comercial como fallback limpio
                        ':cpa' => $codPrinActivo,
                        ':nc' => $nombre,
                        ':conc' => null,
                        ':ff' => 'Unidad',
                        ':rs' => null,
                        ':idl' => $labId,
                        ':idc' => $catId,
                        ':pc' => $precioCompra,
                        ':pv' => $precioVenta,
                        ':pmay' => $precioMayor,
                        ':mg' => $margen,
                        ':um' => 'Unidad',
                        ':stk' => $stock,
                        ':sm' => $stockMin,
                        ':frac' => $fraccionable,
                        ':upc' => $unidadesPorCaja,
                        ':ufrac' => $unidadFraccion,
                        ':pfrac' => $precioFraccion
                    ]);

                    $newProdId = (int)$this->conn->lastInsertId();

                    if ($stock > 0) {
                        $stmtInsertLote->execute([
                            ':prod' => $newProdId,
                            ':lote' => $loteCodigo,
                            ':venc' => $fechaVenc,
                            ':cant' => $stock
                        ]);
                        $stmtInsertKardex->execute([
                            ':prod' => $newProdId,
                            ':usr' => $this->idUsuario,
                            ':motivo' => 'Carga Inicial Catálogo Excel (Lote ' . $loteCodigo . ')',
                            ':cant' => $stock,
                            ':saldo' => $stock
                        ]);
                        $stats['lotes_creados']++;
                    }

                    $stats['creados']++;
                } else {
                    $stats['omitidos']++;
                }
            }

            $this->conn->commit();
            return $stats;

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
