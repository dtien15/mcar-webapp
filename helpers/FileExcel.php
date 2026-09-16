<?php
// =====================================================================
// FileExcel - Ghi file .xlsx THAT (khong phai CSV doi duoi ten).
//
// Vi sao khong dung CSV nua: CSV mo bang Excel thi moi cot deu hep bang
// nhau, khong co vien, khong co dong tieu de co dinh, so tien khong co dau
// phan cach - nhin rat kho doc (nhat la file co may chuc cot). File .xlsx
// that thi giu duoc het: do rong tung cot, vien, mau tieu de, dong tieu de
// dong bang khi cuon, nut loc san tren tung cot, dinh dang so tien.
//
// Khong dung thu vien ngoai (hosting khong co composer): .xlsx thuc chat
// la mot file ZIP chua vai file XML - ZipArchive cua PHP tu lam duoc.
// =====================================================================

class FileExcel
{
    /** Kieu o - dung lam tham so $kieu khi khai bao cot */
    const CHU   = 'chu';    // chu thuong
    const DAI   = 'dai';    // doan chu dai -> tu xuong dong trong o
    const TIEN  = 'tien';   // so tien -> 1.234.567
    const SO    = 'so';     // so nguyen thuong
    const GIUA  = 'giua';   // canh giua (ngay, trang thai...)

    /** May tinh co ghi duoc .xlsx khong (can ext-zip) */
    public static function ghiDuoc()
    {
        return class_exists('ZipArchive');
    }

    /**
     * Xuat thang ra trinh duyet.
     * $cot: mang ['tieu_de' => ..., 'kieu' => self::..., 'rong' => 18]
     * $dong: mang cac mang gia tri, cung thu tu voi $cot
     */
    public static function xuat($tenFile, $tenSheet, array $cot, array $dong)
    {
        $noiDung = self::taoNoiDung($tenSheet, $cot, $dong);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $tenFile . '.xlsx"');
        header('Content-Length: ' . strlen($noiDung));
        header('Cache-Control: max-age=0');
        echo $noiDung;
    }

    /** Dung noi dung file .xlsx, tra ve chuoi nhi phan */
    public static function taoNoiDung($tenSheet, array $cot, array $dong)
    {
        $tapTin = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        $zip->open($tapTin, ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', self::xmlContentTypes());
        $zip->addFromString('_rels/.rels', self::xmlRels());
        $zip->addFromString('xl/workbook.xml', self::xmlWorkbook($tenSheet));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::xmlWorkbookRels());
        $zip->addFromString('xl/styles.xml', self::xmlStyles());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::xmlSheet($cot, $dong));

        $zip->close();

        $noiDung = file_get_contents($tapTin);
        @unlink($tapTin);
        return $noiDung;
    }

    // -----------------------------------------------------------------
    // Cac phan XML cua file xlsx
    // -----------------------------------------------------------------

    private static function xmlContentTypes()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private static function xmlRels()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function xmlWorkbook($tenSheet)
    {
        // Ten sheet khong duoc chua : \ / ? * [ ] va toi da 31 ky tu
        $ten = mb_substr(str_replace([':', '\\', '/', '?', '*', '[', ']'], '', $tenSheet), 0, 31, 'UTF-8');
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . self::thoat($ten) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private static function xmlWorkbookRels()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    /**
     * Bang dinh dang. Thu tu cellXfs chinh la ma kieu (s="...") dung o duoi:
     *   0 = mac dinh
     *   1 = tieu de (dam, chu trang, nen xanh, canh giua, xuong dong)
     *   2 = chu thuong
     *   3 = chu dai (tu xuong dong)
     *   4 = so tien (1.234.567, canh phai)
     *   5 = so nguyen (canh phai)
     *   6 = canh giua
     */
    private static function xmlStyles()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            // Dau phan cach hang nghin cho tien; o trong thi de trong han
            . '<numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0;[Red]-#,##0;&quot;&quot;"/></numFmts>'
            . '<fonts count="2">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF2563EB"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border>'
            . '<left style="thin"><color rgb="FFCBD5E1"/></left>'
            . '<right style="thin"><color rgb="FFCBD5E1"/></right>'
            . '<top style="thin"><color rgb="FFCBD5E1"/></top>'
            . '<bottom style="thin"><color rgb="FFCBD5E1"/></bottom>'
            . '<diagonal/></border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="7">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">'
            . '<alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">'
            . '<alignment vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">'
            . '<alignment vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1">'
            . '<alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">'
            . '<alignment horizontal="right" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">'
            . '<alignment horizontal="center" vertical="center"/></xf>'
            . '</cellXfs>'
            . '</styleSheet>';
    }

    /** Ma kieu (s=) tuong ung voi kieu cot khai bao */
    private static function maKieu($kieu)
    {
        switch ($kieu) {
            case self::DAI:  return 3;
            case self::TIEN: return 4;
            case self::SO:   return 5;
            case self::GIUA: return 6;
            default:         return 2;
        }
    }

    private static function xmlSheet(array $cot, array $dong)
    {
        $soCot = count($cot);
        $cotCuoi = self::tenCot($soCot);
        $soDong  = count($dong) + 1;

        // Do rong tung cot
        $xmlCols = '<cols>';
        foreach ($cot as $i => $c) {
            $xmlCols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="'
                      . (isset($c['rong']) ? (float)$c['rong'] : 16) . '" customWidth="1"/>';
        }
        $xmlCols .= '</cols>';

        // Dong tieu de
        $xml = '<row r="1" ht="30" customHeight="1">';
        foreach ($cot as $i => $c) {
            $xml .= '<c r="' . self::tenCot($i + 1) . '1" s="1" t="inlineStr"><is><t>'
                  . self::thoat($c['tieu_de']) . '</t></is></c>';
        }
        $xml .= '</row>';

        // Cac dong du lieu
        foreach ($dong as $viTri => $hang) {
            $soHang = $viTri + 2;
            $xml .= '<row r="' . $soHang . '">';
            foreach ($cot as $i => $c) {
                $o     = self::tenCot($i + 1) . $soHang;
                $kieu  = isset($c['kieu']) ? $c['kieu'] : self::CHU;
                $s     = self::maKieu($kieu);
                $giaTri = isset($hang[$i]) ? $hang[$i] : '';

                if ($kieu === self::TIEN || $kieu === self::SO) {
                    // O trong van phai co (giu du bo cot), chi la khong co gia tri
                    if ($giaTri === '' || $giaTri === null) {
                        $xml .= '<c r="' . $o . '" s="' . $s . '"/>';
                    } else {
                        $xml .= '<c r="' . $o . '" s="' . $s . '"><v>' . (0 + $giaTri) . '</v></c>';
                    }
                } else {
                    if ($giaTri === '' || $giaTri === null) {
                        $xml .= '<c r="' . $o . '" s="' . $s . '"/>';
                    } else {
                        $xml .= '<c r="' . $o . '" s="' . $s . '" t="inlineStr"><is><t xml:space="preserve">'
                              . self::thoat($giaTri) . '</t></is></c>';
                    }
                }
            }
            $xml .= '</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>'
            . '<dimension ref="A1:' . $cotCuoi . $soDong . '"/>'
            // Dong tieu de dong bang lai khi cuon xuong - file nhieu cot/nhieu dong
            // ma khong co cai nay thi cuon mot ti la khong biet dang xem cot gi
            . '<sheetViews><sheetView workbookViewId="0" tabSelected="1">'
            . '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>'
            . '</sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>'
            . $xmlCols
            . '<sheetData>' . $xml . '</sheetData>'
            // Nut loc san tren tung cot tieu de
            . '<autoFilter ref="A1:' . $cotCuoi . $soDong . '"/>'
            . '<pageMargins left="0.3" right="0.3" top="0.5" bottom="0.5" header="0.3" footer="0.3"/>'
            . '</worksheet>';
    }

    /** 1 -> A, 27 -> AA ... */
    private static function tenCot($so)
    {
        $ten = '';
        while ($so > 0) {
            $du  = ($so - 1) % 26;
            $ten = chr(65 + $du) . $ten;
            $so  = (int)(($so - $du) / 26);
        }
        return $ten;
    }

    private static function thoat($chuoi)
    {
        return htmlspecialchars((string)$chuoi, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
