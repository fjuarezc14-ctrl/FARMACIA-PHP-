<?php
/**
 * SimpleXlsxReader - Lector nativo y de alto rendimiento para archivos Excel (.xlsx)
 * No requiere PhpSpreadsheet ni librerías externas.
 * Utiliza extensiones nativas de PHP: ZipArchive y SimpleXML/XMLReader.
 */
class SimpleXlsxReader {
    private $filePath;
    private $sharedStrings = [];
    private $zip = null;

    public function __construct($filePath) {
        $this->filePath = $filePath;
    }

    /**
     * Parsea el archivo y devuelve un array de filas (cada fila es un array numérico de celdas)
     * @return array
     * @throws Exception
     */
    public function readRows() {
        if (!file_exists($this->filePath)) {
            throw new Exception("El archivo no existe: " . $this->filePath);
        }

        $zip = new ZipArchive();
        if ($zip->open($this->filePath) !== true) {
            throw new Exception("No se pudo abrir el archivo .xlsx. Asegúrese de que es un archivo Excel válido.");
        }

        $this->zip = $zip;

        // 1. Cargar Shared Strings (si existen)
        $this->loadSharedStrings();

        // 2. Buscar y cargar la primera hoja de cálculo
        $sheetPath = $this->findFirstSheetPath();
        $sheetContent = $zip->getFromName($sheetPath);
        if ($sheetContent === false) {
            $zip->close();
            throw new Exception("No se encontró la hoja de cálculo en el archivo .xlsx");
        }

        $rows = $this->parseSheet($sheetContent);
        $zip->close();

        return $rows;
    }

    /**
     * Carga el diccionario de cadenas compartidas (xl/sharedStrings.xml)
     */
    private function loadSharedStrings() {
        $sharedStringsXml = $this->zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml === false) {
            $this->sharedStrings = [];
            return;
        }

        $xml = simplexml_load_string($sharedStringsXml);
        if (!$xml) return;

        foreach ($xml->si as $si) {
            if (isset($si->t)) {
                $this->sharedStrings[] = (string)$si->t;
            } elseif (isset($si->r)) {
                $text = '';
                foreach ($si->r as $r) {
                    $text .= (string)$r->t;
                }
                $this->sharedStrings[] = $text;
            } else {
                $this->sharedStrings[] = '';
            }
        }
    }

    /**
     * Encuentra la ruta interna de la primera hoja (sheet1.xml)
     */
    private function findFirstSheetPath() {
        // Rutas estándar en archivos Excel generados por Microsoft Excel, LibreOffice o sistemas web
        $candidates = [
            'xl/worksheets/sheet1.xml',
            'xl/worksheets/Sheet1.xml',
            'xl/worksheets/hoja1.xml'
        ];

        foreach ($candidates as $cand) {
            if ($this->zip->locateName($cand) !== false) {
                return $cand;
            }
        }

        // Si no coincide por nombre directo, buscar cualquier archivo en xl/worksheets/
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $name = $this->zip->getNameIndex($i);
            if (strpos($name, 'xl/worksheets/') === 0 && substr($name, -4) === '.xml') {
                return $name;
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    /**
     * Parsea el XML de la hoja y extrae las filas preservando las columnas
     */
    private function parseSheet($xmlContent) {
        $xml = simplexml_load_string($xmlContent);
        if (!$xml || !isset($xml->sheetData)) {
            return [];
        }

        $rows = [];

        foreach ($xml->sheetData->row as $row) {
            $rowData = [];
            $maxCol = -1;

            foreach ($row->c as $c) {
                $cellRef = (string)$c['r'];
                $colLetters = preg_replace('/[0-9]/', '', $cellRef);
                $colIndex = $this->columnLetterToIndex($colLetters);
                if ($colIndex > $maxCol) $maxCol = $colIndex;

                $type = (string)$c['t'];
                $val = '';

                if ($type === 's') {
                    // Cadena en diccionario
                    $sIndex = (int)$c->v;
                    $val = $this->sharedStrings[$sIndex] ?? '';
                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                    // Cadena en línea
                    $val = (string)$c->is->t;
                } elseif ($type === 'b') {
                    // Booleano
                    $val = ((string)$c->v === '1') ? '1' : '0';
                } elseif (isset($c->v)) {
                    // Numérico o valor directo
                    $val = (string)$c->v;
                }

                $rowData[$colIndex] = trim($val);
            }

            // Rellenar columnas intermedias vacías
            if ($maxCol >= 0) {
                $completeRow = [];
                for ($i = 0; $i <= $maxCol; $i++) {
                    $completeRow[$i] = $rowData[$i] ?? '';
                }
                $rows[] = $completeRow;
            }
        }

        return $rows;
    }

    /**
     * Convierte letras de columna de Excel (A, B, ..., Z, AA, AB) a índice 0-based
     */
    private function columnLetterToIndex($column) {
        $column = strtoupper($column);
        $length = strlen($column);
        $index = 0;

        for ($i = 0; $i < $length; $i++) {
            $index *= 26;
            $index += (ord($column[$i]) - 64);
        }

        return $index - 1;
    }
}
