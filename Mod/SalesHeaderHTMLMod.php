<?php
/**
 * This file is part of Modelo347 plugin for FacturaScripts
 * Copyright (C) 2020-2023 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Plugins\Modelo347\Mod;

use FacturaScripts\Core\Contract\SalesModInterface;
use FacturaScripts\Core\Translator;
use FacturaScripts\Core\Model\Base\SalesDocument;
use FacturaScripts\Core\Model\User;
use FacturaScripts\Core\Tools;


/**
 * Add new fields in the modal window of the document header
 *   - excluir347: Allows the user to mark the invoice as excluded from the 347 calculation.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 * @author Carlos Garcia Gomez           <carlos@facturascripts.com>
 */
class SalesHeaderHTMLMod implements SalesModInterface
{
    public function apply(SalesDocument &$model, array $formData): void
    {
        if (property_exists($model, 'excluir347')) {
            $model->excluir347 = ($formData['excluir347'] ?? '') === 'true';
        }
    }

    public function applyBefore(SalesDocument &$model, array $formData): void
    {
    }

    public function assets(): void
    {
    }

    public function newBtnFields(): array
    {
        return [];
    }

    public function newFields(): array
    {
        return [];
    }

    public function newModalFields(): array
    {
        return ['excluir347'];
    }

    public function renderField(SalesDocument $model, string $field): ?string
    {
        if ($field == 'excluir347') {
            $i18n = new Translator();
            return $this->excluir347($i18n, $model);
        }

        return null;
    }

    private static function excluir347(Translator $i18n, SalesDocument $model): string
    {
        if (false === property_exists($model, 'excluir347')) {
            return '';
        }

        $options = [];
        foreach (['false', 'true'] as $row) {
            $txt = ($row === 'true') ? 'yes' : 'no';
            $options[] = ($row == $model->excluir347) ?
                '<option value="' . $row . '" selected>' . $i18n->trans($txt) . '</option>' :
                '<option value="' . $row . '">' . $i18n->trans($txt) . '</option>';
        }

        $attributes = $model->editable ? 'name="excluir347" required=""' : 'disabled=""';
        return '<div class="col-sm-6">'
            . '<div class="mb-3">' . $i18n->trans('exclude-347')
            . '<select ' . $attributes . ' class="form-select"/>' . implode('', $options) . '</select>'
            . '</div>'
            . '</div>';
    }
}
