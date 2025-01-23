<?php
namespace FacturaScripts\Plugins\facturaDjmarian\Lib\PlantillasPDF;

use FacturaScripts\Core\Model\Base\BusinessDocument;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\Contacto;
use FacturaScripts\Dinamic\Model\Proveedor;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\DataSrc\Impuestos;

/**
 * Description of TemplateDjmarian
 *
 * @author Ferran Llop Alonso <ferran@misterbit.es>
 */

class TemplateDjmarian extends \FacturaScripts\Plugins\PlantillasPDF\Lib\PlantillasPDF\Template4
{

    /**
     * 
     * @param BusinessDocument $model
     * @param array $lines
     * @param string           $class
     *
     * @return string
     */
    protected function getInvoiceTaxes($model, $lines, $class = 'table-big'): string
    {
        return $this->fixInvoiceTaxesColumns($model, $lines, $class);
    }

    /*
     * Remove 'percentage' column and move 'tax' column 
     * to come after 'taxbase' column 
     */
    private function fixInvoiceTaxesColumns($model, $lines, $class): string
    {
        if ($this->format->hide_vat_breakdown) {
            return '';
        }

        $taxes = $this->getTaxesRows($model, $lines);
        if (empty($model->totaliva)) {
            return '';
        }

        $i18n = Tools::lang();

        $trs = '';
        foreach ($taxes['iva'] as $row) {
            $trs .= '<tr>'
                . '<td class="nowrap" align="center">' . Tools::money($row['neto'], $model->coddivisa) . '</td>'
                . '<td class="nowrap" align="center">' . Impuestos::get($row['codimpuesto'])->descripcion . '</td>'
                . '<td class="nowrap" align="center">' . Tools::money($row['totaliva'], $model->coddivisa) . '</td>';

            if (empty($model->totalrecargo)) {
                $trs .= '</tr>';
                continue;
            }

            $trs .= '<td class="nowrap" align="center">' . (empty($row['recargo']) ? '-' : Tools::number($row['recargo']) . '%') . '</td>'
                . '<td class="nowrap" align="right">' . (empty($row['totalrecargo']) ? '-' : Tools::money($row['totalrecargo'])) . '</td>'
                . '</tr>';
        }

        if (empty($model->totalrecargo)) {
            return '<table class="' . $class . '">'
                . '<thead>'
                . '<tr>'
                . '<th align="center">' . $i18n->trans('tax-base') . '</th>'
                . '<th align="center">' . $i18n->trans('percentage') . '</th>'
                . '<th align="center">' . $i18n->trans('tax') . '</th>'
                . '</tr>'
                . '</thead>'
                . $trs
                . '</table>';
        }

        return '<table class="' . $class . '">'
            . '<tr>'
            . '<th align="center">' . $i18n->trans('tax-base') . '</th>'
            . '<th align="center">' . $i18n->trans('percentage') . '</th>'
            . '<th align="center">' . $i18n->trans('tax') . '</th>'
            . '<th align="center">' . $i18n->trans('re') . '</th>'
            . '<th align="right">' . $i18n->trans('amount') . '</th>'
            . '</tr>'
            . $trs
            . '</table>';
    }
}
