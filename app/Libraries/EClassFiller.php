<?php
namespace App\Libraries;

use DOMDocument;
use DOMXPath;
use ZipArchive;

class EClassFiller
{
    private const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    public function fill(string $template, string $output, string $sheetPath, array $cells): void
    {
        copy($template, $output);

        $zip = new ZipArchive();
        if ($zip->open($output) !== true) {
            throw new \RuntimeException('Cannot open template');
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->loadXML($zip->getFromName($sheetPath));
        $xp = new DOMXPath($dom);
        $xp->registerNamespace('m', self::NS);

        foreach ($cells as $ref => $value) {
            $this->setCell($dom, $xp, $ref, $value);
        }
        $zip->addFromString($sheetPath, $dom->saveXML());

        // make Excel recalculate the Term/Summary sheets on open
        $wb = $zip->getFromName('xl/workbook.xml');
        if (preg_match('/<calcPr[^>]*>/', $wb)) {
            $wb = preg_replace('/<calcPr(?![^>]*fullCalcOnLoad)/', '<calcPr fullCalcOnLoad="1"', $wb, 1);
        } else {
            $wb = str_replace('</definedNames>', '</definedNames><calcPr fullCalcOnLoad="1"/>', $wb);
        }
        $zip->addFromString('xl/workbook.xml', $wb);
        $zip->close();
    }

    private function setCell(DOMDocument $dom, DOMXPath $xp, string $ref, $value): void
    {
        preg_match('/^([A-Z]+)(\d+)$/', $ref, $m);
        [, $col, $rowNum] = $m;

        $row = $xp->query("//m:sheetData/m:row[@r='$rowNum']")->item(0);
        if (!$row instanceof \DOMElement) {
            throw new \RuntimeException("Row $rowNum not found in template");
        }

        $cell = $xp->query("m:c[@r='$ref']", $row)->item(0);
        if (!$cell instanceof \DOMElement) { // create it in column order
            $cell = $dom->createElementNS(self::NS, 'c');
            $cell->setAttribute('r', $ref);
            $before = null;
            foreach ($xp->query('m:c', $row) as $c) {
                if (!$c instanceof \DOMElement) {
                    continue;
                }
                preg_match('/^[A-Z]+/', $c->getAttribute('r'), $cc);
                if ($this->colIndex($cc[0]) > $this->colIndex($col)) {
                    $before = $c;
                    break;
                }
            }
            $row->insertBefore($cell, $before);
        }

        while ($cell->firstChild) {
            $cell->removeChild($cell->firstChild);
        } // keep style (s="..")
        $cell->removeAttribute('t');

        if ($value === null || $value === '') {
            return;
        }
        if (is_int($value) || is_float($value)) {
            $cell->appendChild($dom->createElementNS(self::NS, 'v', (string) $value));
        } else {
            $cell->setAttribute('t', 'inlineStr');
            $is = $dom->createElementNS(self::NS, 'is');
            $t = $dom->createElementNS(self::NS, 't');
            $t->setAttribute('xml:space', 'preserve');
            $t->appendChild($dom->createTextNode((string) $value));
            $is->appendChild($t);
            $cell->appendChild($is);
        }
    }
    private function colIndex(string $col): int
    {
        $n = 0;
        foreach (str_split($col) as $ch) {
            $n = $n * 26 + (ord($ch) - 64);
        }
        return $n;
    }
}