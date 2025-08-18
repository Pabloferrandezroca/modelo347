<?php
/**
 * This file is part of Modelo347 plugin for FacturaScripts
 * Copyright (C) 2024 Carlos Garcia Gomez <carlos@facturascripts.com>
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

namespace FacturaScripts\Test\Plugins\Extension\Table;

use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\FacturaCliente;
use FacturaScripts\Dinamic\Model\FacturaProveedor;
use FacturaScripts\Dinamic\Model\Proveedor;
use FacturaScripts\Test\Traits\DefaultSettingsTrait;
use FacturaScripts\Test\Traits\LogErrorsTrait;
use PHPUnit\Framework\TestCase;

/**
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
final class FacturasTest extends TestCase
{
    use LogErrorsTrait;
    use DefaultSettingsTrait;

    public static function setUpBeforeClass(): void
    {
        // configuración por defecto para el almacén por defecto
        self::setDefaultSettings();
        self::installAccountingPlan();
        self::removeTaxRegularization();
    }

    public function testExistsPropertyFacturaCliente()
    {
        // crear el cliente
        $customer = new Cliente();
        $customer->cifnif = 'B' . mt_rand(1, 999999);
        $customer->nombre = 'Customer Rand ' . mt_rand(1, 99999);
        $customer->observaciones = 'Test';
        $customer->razonsocial = 'Empresa ' . mt_rand(1, 99999);
        $this->assertTrue($customer->save(), 'cant-create-customer');

        // crear la factura
        $invoice = new FacturaCliente();
        $invoice->setSubject($customer);
        $invoice->numero2 = 'INV-' . mt_rand(1, 99999) . '-' . mt_rand(1, 99999);
        $invoice->observaciones = 'Test';
        $invoice->excluir347 = true;
        $this->assertTrue($invoice->save(), 'cant-create-invoice');
        $this->assertTrue($invoice->exists(), 'invoice-not-exists');

        // comprobar el campo
        $this->assertTrue($invoice->load($invoice->idfactura), 'cant-create-load');
        $this->assertTrue($invoice->excluir347, 'value-not-saved');
        $invoice->excluir347 = false;
        $this->assertTrue($invoice->save(), 'cant-create-invoice');

        $this->assertTrue($invoice->load($invoice->idfactura), 'cant-create-load');
        $this->assertFalse($invoice->excluir347, 'value-not-saved');

        // eliminar la factura
        $this->assertTrue($invoice->delete(), 'cant-delete-invoice');
        $this->assertTrue($customer->getDefaultAddress()->delete());
        $this->assertTrue($customer->delete(), 'cant-delete-customer');
    }

    public function testExistsPropertyFacturaProveedor()
    {
        // crear proveedor
        $suplier = new Proveedor();
        $suplier->cifnif = 'B' . mt_rand(1, 999999);
        $suplier->nombre = 'suplier Rand ' . mt_rand(1, 99999);
        $suplier->observaciones = 'Test';
        $suplier->razonsocial = 'Empresa ' . mt_rand(1, 99999);
        $this->assertTrue($suplier->save(), 'cant-create-suplier');

        // crear la factura de proveedor
        $invoice = new FacturaProveedor();
        $invoice->setSubject($suplier);
        $invoice->numero2 = 'INV-' . mt_rand(1, 99999) . '-' . mt_rand(1, 99999);
        $invoice->observaciones = 'Test';
        $invoice->excluir347 = true;
        $this->assertTrue($invoice->save(), 'cant-create-invoice');
        $this->assertTrue($invoice->exists(), 'invoice-not-exists');

        // comprobar el campo
        $this->assertTrue($invoice->load($invoice->idfactura), 'cant-create-load');
        $this->assertTrue($invoice->excluir347, 'value-not-saved');
        $invoice->excluir347 = false;
        $this->assertTrue($invoice->save(), 'cant-create-invoice');

        $this->assertTrue($invoice->load($invoice->idfactura), 'cant-create-load');
        $this->assertFalse($invoice->excluir347, 'value-not-saved');

        // eliminar la factura
        $this->assertTrue($invoice->delete(), 'cant-delete-invoice');
        $this->assertTrue($suplier->getDefaultAddress()->delete());
        $this->assertTrue($suplier->delete(), 'cant-delete-suplier');
    }

    protected function tearDown(): void
    {
        $this->logErrors();
    }
}
