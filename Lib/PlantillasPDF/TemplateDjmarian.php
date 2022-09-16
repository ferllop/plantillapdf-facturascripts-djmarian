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
     * @return string
     */
    protected function headerLeft(): string
    {
        $contactData = [];
        foreach (['telefono1', 'telefono2', 'email', 'web'] as $field) {
            if ($this->empresa->{$field}) {
                $contactData[] = $this->empresa->{$field};
            }
        }

        $title = $this->showHeaderTitle ? '<h1 class="title">' . $this->get('headertitle') . '</h1>' . $this->spacer() : '';
        return '<table class="table-big">'
            . '<tr>'
            . '<td valign="top"><img src="' . $this->logoPath . '" height="' . $this->get('logosize') . '"/></td>'
            . '<td align="right" valign="top">'
            . $title
            . '<p><b>' . $this->empresa->nombre . '</b>'
            . '<br/>' . $this->empresa->tipoidfiscal . ': ' . $this->empresa->cifnif
            . '<br/>' . $this->combineAddress($this->empresa) . '</p>'
            . $this->spacer()
            . '<p>' . \implode(' · ', $contactData) . '</p>'
            . '</td>'
            . '</tr>'
            . '</table>';
    }
    /**
     * 
     * @param string $txt
     *
     * @return string
     */
    protected function getInvoiceLineFieldTitle(string $txt): string
    {
        $codes = [
            'cantidad' => 'quantity-abb',
            'descripcion' => 'description',
            'dtopor' => 'dto',
            'dtopor2' => 'dto-2',
            'iva' => 'tax-abb',
            'pvpunitario' => 'price',
            'pvptotal' => 'amount',
            'recargo' => 're',
            'referencia' => 'reference'
        ];

        return isset($codes[$txt]) ? $this->toolBox()->i18n()->trans($codes[$txt]) : $this->toolBox()->i18n()->trans($txt);
    }

    /**
     * 
     * @param BusinessDocument $model
     *
     * @return string
     */
    protected function getInvoiceHeaderResume($model): string
    {
        $i18n = $this->toolBox()->i18n();

        $extra1 = '';
        if ($this->get('logoalign') === 'full-size') {
            $title = empty($this->format->titulo) ? $i18n->trans($model->modelClassName() . '-min') : $this->format->titulo;
            $extra1 .= '<tr>'
                . '<td><b>' . $title . '</b>:</td>'
                . '<td>' . $model->codigo . '</td>'
                . '</tr>';
        }

        /// rectified invoice?
        if (isset($model->codigorect) && !empty($model->codigorect)) {
            $extra1 .= '<tr>'
                . '<td><b>' . $i18n->trans('original') . '</b>:</td>'
                . '<td>' . $model->codigorect . '</td>'
                . '</tr>';
        }

        /// number2?
        $extra2 = '';
        if (isset($model->numero2) && !empty($model->numero2) && (bool) $this->get('shownumero2')) {
            $extra2 .= '<tr>'
                . '<td><b>' . $i18n->trans('number2') . '</b>:</td>'
                . '<td>' . $model->numero2 . '</td>'
                . '</tr>';
        }

        /// cif/nif?
        $extra3 = empty($model->cifnif) ? '' : '<tr>'
            . '<td><b>' . $model->getSubject()->tipoidfiscal . '</b>:</td>'
            . '<td>' . $model->cifnif . '</td>'
            . '</tr>';

        $size = empty($extra2) ? 170 : 200;
        return '<td valign="top" width="' . $size . '">'
            . '<table class="table-big">'
            . $extra1
            . '<tr>'
            . '<td><b>' . $i18n->trans('date') . '</b>:</td>'
            . '<td>' . $model->fecha . '</td>'
            . '</tr>'
            . $extra2
            . $extra3
            . $this->getInvoiceHeaderResumePhones($model->getSubject())
            . '</table>'
            . '</td>';
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
                . '<th align="center">' . $row['tax'] . '</th>'
                . '</tr>'
                . '</thead>'
                . $trs
                . '</table>';
        }

        return '<table class="' . $class . '">'
            . '<tr>'
            . '<th align="center">' . $i18n->trans('tax') . '</th>'
            . '<th align="center">' . $i18n->trans('tax-base') . '</th>'
            . '<th align="center">' . $row['tax'] . '</th>'
            . '<th align="center">' . $i18n->trans('re') . '</th>'
            . '<th align="right">' . $i18n->trans('amount') . '</th>'
            . '</tr>'
            . $trs
            . '</table>';
    }

}
