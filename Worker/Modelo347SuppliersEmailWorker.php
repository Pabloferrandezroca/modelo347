<?php
/**
 * This file is part of Modelo347 plugin for FacturaScripts
 * Copyright (C) 2026 Carlos Garcia Gomez <carlos@facturascripts.com>
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

namespace FacturaScripts\Plugins\Modelo347\Worker;

use FacturaScripts\Core\Model\WorkEvent;
use FacturaScripts\Core\Template\WorkerClass;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Validator;
use FacturaScripts\Dinamic\Lib\Email\NewMail;
use FacturaScripts\Dinamic\Lib\Email\TableBlock;
use FacturaScripts\Dinamic\Lib\Email\TextBlock;
use FacturaScripts\Dinamic\Lib\Email\TitleBlock;

/**
 * @author Esteban Sánchez Martínez <esteban@factura.city>
 */
class Modelo347SuppliersEmailWorker extends WorkerClass
{
    public function run(WorkEvent $event): bool
    {
        $codejercicio = $event->value;
        $data = $event->param('data');

        if (empty($data)) {
            return $this->done();
        }

        $sent = 0;
        $noEmail = 0;

        $i18n = Tools::lang();
        $amount = Tools::money((float)$event->param('amount'));

        foreach ($data as $row) {
            $email = $row['email'] ?? '';
            if (empty($email) || false === Validator::email($email)) {
                $noEmail++;
                Tools::log()->warning('347-no-email', [
                    '%name%' => $row['name'] ?? '',
                    '%cifnif%' => $row['cifnif'] ?? '',
                ]);
                continue;
            }

            $contactInfo = $i18n->trans('347-email-cifnif-line', ['%cifnif%' => $row['cifnif'] ?? ''])
                . "\n"
                . $i18n->trans('347-email-name-line', ['%name%' => $row['name'] ?? '']);

            $header = [$i18n->trans('concept'), $i18n->trans('amount')];
            $rows = [
                [$i18n->trans('first-trimester'), Tools::money((float)$row['t1'])],
                [$i18n->trans('second-trimester'), Tools::money((float)$row['t2'])],
                [$i18n->trans('third-trimester'), Tools::money((float)$row['t3'])],
                [$i18n->trans('fourth-trimester'), Tools::money((float)$row['t4'])],
                [$i18n->trans('total'), Tools::money((float)$row['total'])],
            ];

            $mail = NewMail::create()
                ->to($email, $row['name'] ?? '')
                ->subject($i18n->trans('347-email-subject', ['%year%' => $codejercicio]))
                ->addMainBlock(new TitleBlock($i18n->trans('347-email-title'), 'h3'))
                ->addMainBlock(new TextBlock($i18n->trans('347-email-greeting')))
                ->addMainBlock(new TextBlock($i18n->trans('347-email-intro', ['%year%' => $codejercicio, '%amount%' => $amount])))
                ->addMainBlock(new TextBlock($i18n->trans('347-email-included')))
                ->addMainBlock(new TextBlock($contactInfo))
                ->addMainBlock(new TextBlock($i18n->trans('347-email-amounts-title')))
                ->addMainBlock(new TableBlock($header, $rows))
                ->addMainBlock(new TextBlock($i18n->trans('347-email-footer')));

            if ($mail->send()) {
                $sent++;
            }
        }

        Tools::log()->info('347-emails-sent', ['%sent%' => $sent, '%noEmail%' => $noEmail]);

        return $this->done();
    }
}
