<?php
namespace FacturaScripts\Plugins\facturaDjmarian\Lib\PlantillasPDF;

use FacturaScripts\Core\Model\Base\BusinessDocument;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\Contacto;
use FacturaScripts\Dinamic\Model\Proveedor;

/**
 * Description of TemplateDjmarian
 *
 * @author Ferran Llop Alonso <ferranllop@gmail.com>
 */

class TemplateDjmarian extends \FacturaScripts\Plugins\PlantillasPDF\Lib\PlantillasPDF\Template4
{

    /**
     * 
     * @param string $txt
     *
     * @return string
     */
    protected function getInvoiceLineFieldTitle(string $txt): string
    {
        return parent::getInvoiceLineFieldTitle($this->fixTotalLinesHeading($txt));
    }

    /**
     * Replace the heading from pvptotal that prints 'Net' heading,
     * which is incorrect, to amount that prints 'Amount'
     */
    private function fixTotalLinesHeading(string $txt): string
    {
        return $txt == 'pvptotal' ? 'amount' : $txt;
    }

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
        $rows = $this->getTaxesRows($model, $lines);
        if (empty($model->totaliva)) {
            return '';
        }

        $coins = $this->toolBox()->coins();
        $i18n = $this->toolBox()->i18n();
        $numbers = $this->toolBox()->numbers();

        $trs = '';
        foreach ($rows as $row) {
            $trs .= '<tr>'
                . '<td align="center">' . $coins->format($row['taxbase']) . '</td>'
                . '<td align="center">' . $row['tax'] . '</td>'
                . '<td align="center">' . $coins->format($row['taxamount']) . '</td>';

            if (empty($model->totalrecargo)) {
                $trs .= '</tr>';
                continue;
            }

            $trs .= '<td align="center">' . (empty($row['taxsurchargep']) ? '-' : $numbers->format($row['taxsurchargep']) . '%') . '</td>'
                . '<td align="right">' . (empty($row['taxsurcharge']) ? '-' : $coins->format($row['taxsurcharge'])) . '</td>'
                . '</tr>';
        }

        if (empty($model->totalrecargo)) {
            return '<table class="' . $class . '">'
                . '<thead>'
                . '<tr>'
                . '<th align="center">' . $i18n->trans('tax-base') . '</th>'
                . '<th align="center">' . $i18n->trans('tax') . '</th>'
                . '<th align="center">' . $i18n->trans('amount') . '</th>'
                . '</tr>'
                . '</thead>'
                . $trs
                . '</table>';
        }

        return '<table class="' . $class . '">'
            . '<tr>'
            . '<th align="center">' . $i18n->trans('tax-base') . '</th>'
            . '<th align="center">' . $i18n->trans('tax') . '</th>'
            . '<th align="center">' . $i18n->trans('amount') . '</th>'
            . '<th align="center">' . $i18n->trans('re') . '</th>'
            . '<th align="right">' . $i18n->trans('amount') . '</th>'
            . '</tr>'
            . $trs
            . '</table>';
    }

}
