<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [

            ['title_tag' => 'Role', 'title' => 'List', 'name' => 'role-list', 'type' => 'CRM'],
            ['title_tag' => 'Role', 'title' => 'Create', 'name' => 'role-create', 'type' => 'CRM'],
            ['title_tag' => 'Role', 'title' => 'Edit', 'name' => 'role-edit', 'type' => 'CRM'],
            ['title_tag' => 'Role', 'title' => 'Delete', 'name' => 'role-delete', 'type' => 'CRM'],

            ['title_tag' => 'Permission', 'title' => 'List', 'name' => 'permission-list', 'type' => 'CRM'],
            ['title_tag' => 'Permission', 'title' => 'Create', 'name' => 'permission-create', 'type' => 'CRM'],
            ['title_tag' => 'Permission', 'title' => 'Edit', 'name' => 'permission-edit', 'type' => 'CRM'],
            ['title_tag' => 'Permission', 'title' => 'Delete', 'name' => 'permission-delete', 'type' => 'CRM'],

            ['title_tag' => 'Category', 'title' => 'List', 'name' => 'category-list', 'type' => 'CRM'],
            ['title_tag' => 'Category', 'title' => 'Create', 'name' => 'category-create', 'type' => 'CRM'],
            ['title_tag' => 'Category', 'title' => 'Edit', 'name' => 'category-edit', 'type' => 'CRM'],
            ['title_tag' => 'Category', 'title' => 'Delete', 'name' => 'category-delete', 'type' => 'CRM'],

            ['title_tag' => 'Lead', 'title' => 'List', 'name' => 'lead-list', 'type' => 'CRM'],
            ['title_tag' => 'Lead', 'title' => 'Create', 'name' => 'lead-create', 'type' => 'CRM'],
            ['title_tag' => 'Lead', 'title' => 'Edit', 'name' => 'lead-edit', 'type' => 'CRM'],
            ['title_tag' => 'Lead', 'title' => 'Delete', 'name' => 'lead-delete', 'type' => 'CRM'],

            ['title_tag' => 'Bill of Supply', 'title' => 'List', 'name' => 'product-list', 'type' => 'ERP'],
            ['title_tag' => 'Bill of Supply', 'title' => 'Create', 'name' => 'product-create', 'type' => 'ERP'],
            ['title_tag' => 'Bill of Supply', 'title' => 'Edit', 'name' => 'product-edit', 'type' => 'ERP'],
            ['title_tag' => 'Bill of Supply', 'title' => 'Delete', 'name' => 'product-delete', 'type' => 'ERP'],

            ['title_tag' => 'Panel Company', 'title' => 'List', 'name' => 'penal-company-list', 'type' => 'CRM'],
            ['title_tag' => 'Panel Company', 'title' => 'Create', 'name' => 'penal-company-create', 'type' => 'CRM'],
            ['title_tag' => 'Panel Company', 'title' => 'Edit', 'name' => 'penal-company-edit', 'type' => 'CRM'],
            ['title_tag' => 'Panel Company', 'title' => 'Delete', 'name' => 'penal-company-delete', 'type' => 'CRM'],

            ['title_tag' => 'Panel Type', 'title' => 'List', 'name' => 'penal-type-list', 'type' => 'CRM'],
            ['title_tag' => 'Panel Type', 'title' => 'Create', 'name' => 'penal-type-create', 'type' => 'CRM'],
            ['title_tag' => 'Panel Type', 'title' => 'Edit', 'name' => 'penal-type-edit', 'type' => 'CRM'],
            ['title_tag' => 'Panel Type', 'title' => 'Delete', 'name' => 'penal-type-delete', 'type' => 'CRM'],

            ['title_tag' => 'Panel Watt', 'title' => 'List', 'name' => 'penal-watt-list', 'type' => 'CRM'],
            ['title_tag' => 'Panel Watt', 'title' => 'Create', 'name' => 'penal-watt-create', 'type' => 'CRM'],
            ['title_tag' => 'Panel Watt', 'title' => 'Edit', 'name' => 'penal-watt-edit', 'type' => 'CRM'],
            ['title_tag' => 'Panel Watt', 'title' => 'Delete', 'name' => 'penal-watt-delete', 'type' => 'CRM'],

            ['title_tag' => 'Sales Order', 'title' => 'List', 'name' => 'sales-master-list', 'type' => 'CRM'],
            ['title_tag' => 'Sales Order', 'title' => 'Create', 'name' => 'sales-master-create', 'type' => 'CRM'],
            ['title_tag' => 'Sales Order', 'title' => 'Edit', 'name' => 'sales-master-edit', 'type' => 'CRM'],
            ['title_tag' => 'Sales Order', 'title' => 'Delete', 'name' => 'sales-master-delete', 'type' => 'CRM'],

            ['title_tag' => 'District', 'title' => 'List', 'name' => 'district-list', 'type' => 'CRM'],
            ['title_tag' => 'District', 'title' => 'Create', 'name' => 'district-create', 'type' => 'CRM'],
            ['title_tag' => 'District', 'title' => 'Edit', 'name' => 'district-edit', 'type' => 'CRM'],
            ['title_tag' => 'District', 'title' => 'Delete', 'name' => 'district-delete', 'type' => 'CRM'],

            ['title_tag' => 'Taluka', 'title' => 'List', 'name' => 'taluka-list', 'type' => 'CRM'],
            ['title_tag' => 'Taluka', 'title' => 'Create', 'name' => 'taluka-create', 'type' => 'CRM'],
            ['title_tag' => 'Taluka', 'title' => 'Edit', 'name' => 'taluka-edit', 'type' => 'CRM'],
            ['title_tag' => 'Taluka', 'title' => 'Delete', 'name' => 'taluka-delete', 'type' => 'CRM'],

            ['title_tag' => 'Employee', 'title' => 'List', 'name' => 'employee-list', 'type' => 'CRM'],
            ['title_tag' => 'Employee', 'title' => 'Create', 'name' => 'employee-create', 'type' => 'CRM'],
            ['title_tag' => 'Employee', 'title' => 'Edit', 'name' => 'employee-edit', 'type' => 'CRM'],
            ['title_tag' => 'Employee', 'title' => 'Delete', 'name' => 'employee-delete', 'type' => 'CRM'],

            ['title_tag' => 'Sales Quotation', 'title' => 'List', 'name' => 'sales-quatation-list', 'type' => 'CRM'],
            ['title_tag' => 'Sales Quotation', 'title' => 'Create', 'name' => 'sales-quatation-create', 'type' => 'CRM'],
            ['title_tag' => 'Sales Quotation', 'title' => 'Edit', 'name' => 'sales-quatation-edit', 'type' => 'CRM'],
            ['title_tag' => 'Sales Quotation', 'title' => 'Delete', 'name' => 'sales-quatation-delete', 'type' => 'CRM'],

            ['title_tag' => 'Sub Division', 'title' => 'List', 'name' => 'sub-division-list', 'type' => 'CRM'],
            ['title_tag' => 'Sub Division', 'title' => 'Create', 'name' => 'sub-division-create', 'type' => 'CRM'],
            ['title_tag' => 'Sub Division', 'title' => 'Edit', 'name' => 'sub-division-edit', 'type' => 'CRM'],
            ['title_tag' => 'Sub Division', 'title' => 'Delete', 'name' => 'sub-division-delete', 'type' => 'CRM'],

            ['title_tag' => 'Payment Collection', 'title' => 'List', 'name' => 'payment-collection-list', 'type' => 'CRM'],
            ['title_tag' => 'Payment Collection', 'title' => 'Create', 'name' => 'payment-collection-create', 'type' => 'CRM'],
            ['title_tag' => 'Payment Collection', 'title' => 'Edit', 'name' => 'payment-collection-edit', 'type' => 'CRM'],
            ['title_tag' => 'Payment Collection', 'title' => 'Delete', 'name' => 'payment-collection-delete', 'type' => 'CRM'],

            ['title_tag' => 'Inverter Company', 'title' => 'List', 'name' => 'inveter-company-list', 'type' => 'CRM'],
            ['title_tag' => 'Inverter Company', 'title' => 'Create', 'name' => 'inveter-company-create', 'type' => 'CRM'],
            ['title_tag' => 'Inverter Company', 'title' => 'Edit', 'name' => 'inveter-company-edit', 'type' => 'CRM'],
            ['title_tag' => 'Inverter Company', 'title' => 'Delete', 'name' => 'inveter-company-delete', 'type' => 'CRM'],

            ['title_tag' => 'Follow Up', 'title' => 'List', 'name' => 'follow-up-list', 'type' => 'CRM'],
            ['title_tag' => 'Follow Up', 'title' => 'Create', 'name' => 'follow-up-create', 'type' => 'CRM'],

            ['title_tag' => 'Bank', 'title' => 'List', 'name' => 'bank-list', 'type' => 'CRM'],
            ['title_tag' => 'Bank', 'title' => 'Create', 'name' => 'bank-create', 'type' => 'CRM'],
            ['title_tag' => 'Bank', 'title' => 'Edit', 'name' => 'bank-edit', 'type' => 'CRM'],
            ['title_tag' => 'Bank', 'title' => 'Delete', 'name' => 'bank-delete', 'type' => 'CRM'],

            ['title_tag' => 'Terms Conditions', 'title' => 'List', 'name' => 'policy-list', 'type' => 'CRM'],
            ['title_tag' => 'Terms Conditions', 'title' => 'Create', 'name' => 'policy-create', 'type' => 'CRM'],
            ['title_tag' => 'Terms Conditions', 'title' => 'Edit', 'name' => 'policy-edit', 'type' => 'CRM'],

            ['title_tag' => 'Employee Status', 'title' => 'Employee Status', 'name' => 'employee-status', 'type' => 'CRM'],

            ['title_tag' => 'Reports', 'title' => 'Total Collection', 'name' => 'reports-total-collection', 'type' => 'CRM'],
            ['title_tag' => 'Reports', 'title' => 'Payment Pending', 'name' => 'reports-payment-pending', 'type' => 'CRM'],
            ['title_tag' => 'Reports', 'title' => 'Meter Charges', 'name' => 'reports-meter-charges', 'type' => 'CRM'],
            ['title_tag' => 'Reports', 'title' => 'Dispach', 'name' => 'reports-dispach', 'type' => 'CRM'],
            ['title_tag' => 'Reports', 'title' => 'Installation', 'name' => 'reports-installation', 'type' => 'CRM'],
            ['title_tag' => 'Reports', 'title' => 'Meter Application', 'name' => 'reports-meter-application', 'type' => 'CRM'],
            ['title_tag' => 'Reports', 'title' => 'Final Report', 'name' => 'reports-final', 'type' => 'CRM'],
            ['title_tag' => 'Reports', 'title' => 'Invoice Report', 'name' => 'reports-invoice', 'type' => 'CRM'],
            ['title_tag' => 'Reports', 'title' => 'Panel Required', 'name' => 'panels-required-reports', 'type' => 'CRM'],
            ['title_tag' => 'Reports', 'title' => 'Inverters Required', 'name' => 'inverters-required-reports', 'type' => 'CRM'],

            ['title_tag' => 'DISCOM', 'title' => 'List', 'name' => 'discom-list', 'type' => 'CRM'],
            ['title_tag' => 'DISCOM', 'title' => 'Create', 'name' => 'discom-create', 'type' => 'CRM'],
            ['title_tag' => 'DISCOM', 'title' => 'Edit', 'name' => 'discom-edit', 'type' => 'CRM'],
            ['title_tag' => 'DISCOM', 'title' => 'Delete', 'name' => 'discom-delete', 'type' => 'CRM'],

            ['title_tag' => 'Year', 'title' => 'List', 'name' => 'year-list', 'type' => 'ERP'],
            ['title_tag' => 'Year', 'title' => 'Create', 'name' => 'year-create', 'type' => 'ERP'],
            ['title_tag' => 'Year', 'title' => 'Edit', 'name' => 'year-edit', 'type' => 'ERP'],
            ['title_tag' => 'Year', 'title' => 'Delete', 'name' => 'year-delete', 'type' => 'ERP'],

            ['title_tag' => 'Panel/Inverter', 'title' => 'List', 'name' => 'item-group-list', 'type' => 'ERP'],
            ['title_tag' => 'Panel/Inverter', 'title' => 'Create', 'name' => 'item-group-create', 'type' => 'ERP'],
            ['title_tag' => 'Panel/Inverter', 'title' => 'Edit', 'name' => 'item-group-edit', 'type' => 'ERP'],
            ['title_tag' => 'Panel/Inverter', 'title' => 'Delete', 'name' => 'item-group-delete', 'type' => 'ERP'],

            ['title_tag' => 'Supplier', 'title' => 'List', 'name' => 'supplier-list', 'type' => 'ERP'],
            ['title_tag' => 'Supplier', 'title' => 'Create', 'name' => 'supplier-create', 'type' => 'ERP'],
            ['title_tag' => 'Supplier', 'title' => 'Edit', 'name' => 'supplier-edit', 'type' => 'ERP'],
            ['title_tag' => 'Supplier', 'title' => 'Delete', 'name' => 'supplier-delete', 'type' => 'ERP'],

            ['title_tag' => 'Purchase Order', 'title' => 'List', 'name' => 'purchase-order-list', 'type' => 'ERP'],
            ['title_tag' => 'Purchase Order', 'title' => 'Create', 'name' => 'purchase-order-create', 'type' => 'ERP'],
            ['title_tag' => 'Purchase Order', 'title' => 'Edit', 'name' => 'purchase-order-edit', 'type' => 'ERP'],
            ['title_tag' => 'Purchase Order', 'title' => 'Delete', 'name' => 'purchase-order-delete', 'type' => 'ERP'],

            ['title_tag' => 'Unit', 'title' => 'List', 'name' => 'unit-list', 'type' => 'ERP'],
            ['title_tag' => 'Unit', 'title' => 'Create', 'name' => 'unit-create', 'type' => 'ERP'],
            ['title_tag' => 'Unit', 'title' => 'Edit', 'name' => 'unit-edit', 'type' => 'ERP'],
            ['title_tag' => 'Unit', 'title' => 'Delete', 'name' => 'unit-delete', 'type' => 'ERP'],

            ['title_tag' => 'Warehouse', 'title' => 'List', 'name' => 'warehouse-list', 'type' => 'ERP'],
            ['title_tag' => 'Warehouse', 'title' => 'Create', 'name' => 'warehouse-create', 'type' => 'ERP'],
            ['title_tag' => 'Warehouse', 'title' => 'Edit', 'name' => 'warehouse-edit', 'type' => 'ERP'],
            ['title_tag' => 'Warehouse', 'title' => 'Delete', 'name' => 'warehouse-delete', 'type' => 'ERP'],

            ['title_tag' => 'Warehouse Stock', 'title' => 'List', 'name' => 'warehouse-stock-list', 'type' => 'ERP'],
            ['title_tag' => 'Warehouse Stock', 'title' => 'Create', 'name' => 'warehouse-stock-create', 'type' => 'ERP'],

            ['title_tag' => 'Goods Receipt', 'title' => 'List', 'name' => 'purchase-direct-list', 'type' => 'ERP'],
            ['title_tag' => 'Goods Receipt', 'title' => 'Create', 'name' => 'purchase-direct-create', 'type' => 'ERP'],
            ['title_tag' => 'Goods Receipt', 'title' => 'Edit', 'name' => 'purchase-direct-edit', 'type' => 'ERP'],
            ['title_tag' => 'Goods Receipt', 'title' => 'Delete', 'name' => 'purchase-direct-delete', 'type' => 'ERP'],

            ['title_tag' => 'Warehouse Stock Adjust', 'title' => 'List', 'name' => 'warehouse-stock-adjust-list', 'type' => 'ERP'],
            ['title_tag' => 'Warehouse Stock Adjust', 'title' => 'Create', 'name' => 'warehouse-stock-adjust-create', 'type' => 'ERP'],

            ['title_tag' => 'Project Wise Stock', 'title' => 'List', 'name' => 'project-wise-stock-list', 'type' => 'ERP'],
            ['title_tag' => 'Project Wise Stock', 'title' => 'Create', 'name' => 'project-wise-stock-create', 'type' => 'ERP'],

            ['title_tag' => 'Project Stock Adjust', 'title' => 'List', 'name' => 'project-stock-adjust-list', 'type' => 'ERP'],
            ['title_tag' => 'Project Stock Adjust', 'title' => 'Create', 'name' => 'project-stock-adjust-create', 'type' => 'ERP'],

            ['title_tag' => 'Goods Issue', 'title' => 'List', 'name' => 'delivery-challan-list', 'type' => 'ERP'],
            ['title_tag' => 'Goods Issue', 'title' => 'Create', 'name' => 'delivery-challan-create', 'type' => 'ERP'],
            ['title_tag' => 'Goods Issue', 'title' => 'Edit', 'name' => 'delivery-challan-edit', 'type' => 'ERP'],
            ['title_tag' => 'Goods Issue', 'title' => 'Delete', 'name' => 'delivery-challan-delete', 'type' => 'ERP'],

            ['title_tag' => 'Inquiry', 'title' => 'List', 'name' => 'inquiry-list', 'type' => 'ERP'],

            ['title_tag' => 'Goods Return', 'title' => 'List', 'name' => 'delivery-challan-return-list', 'type' => 'ERP'],
            ['title_tag' => 'Goods Return', 'title' => 'Create', 'name' => 'delivery-challan-return-create', 'type' => 'ERP'],
            ['title_tag' => 'Goods Return', 'title' => 'Edit', 'name' => 'delivery-challan-return-edit', 'type' => 'ERP'],
            ['title_tag' => 'Goods Return', 'title' => 'Delete', 'name' => 'delivery-challan-return-delete', 'type' => 'ERP'],

            ['title_tag' => 'BOM', 'title' => 'List', 'name' => 'bom-list', 'type' => 'ERP'],
            ['title_tag' => 'BOM', 'title' => 'Create', 'name' => 'bom-create', 'type' => 'ERP'],
            ['title_tag' => 'BOM', 'title' => 'Edit', 'name' => 'bom-edit', 'type' => 'ERP'],
            ['title_tag' => 'BOM', 'title' => 'Delete', 'name' => 'bom-delete', 'type' => 'ERP'],

            ['title_tag' => 'Report', 'title' => 'Serial Number Report', 'name' => 'get-serial-numbers', 'type' => 'ERP'],
            ['title_tag' => 'Report', 'title' => 'Project Wise Dispach', 'name' => 'project-wise-dispach', 'type' => 'ERP'],
            ['title_tag' => 'Report', 'title' => 'Project Wise Stock Report', 'name' => 'project-wise-stock-report', 'type' => 'ERP'],
            ['title_tag' => 'Report', 'title' => 'Requisition Report', 'name' => 'required-stock-report', 'type' => 'ERP'],
            ['title_tag' => 'Report', 'title' => 'Stock Report', 'name' => 'stock-report', 'type' => 'ERP'],
            ['title_tag' => 'Report', 'title' => 'B2B Dispach', 'name' => 'b2b-dispach', 'type' => 'ERP'],

            ['title_tag' => 'Rate Calculator', 'title' => 'List', 'name' => 'rate-calculator-list', 'type' => 'CRM'],
            ['title_tag' => 'Rate Calculator', 'title' => 'Create', 'name' => 'rate-calculator-create', 'type' => 'CRM'],
            ['title_tag' => 'Rate Calculator', 'title' => 'Edit', 'name' => 'rate-calculator-edit', 'type' => 'CRM'],
            ['title_tag' => 'Rate Calculator', 'title' => 'Delete', 'name' => 'rate-calculator-delete', 'type' => 'CRM'],

            ['title_tag' => 'Commission Payment', 'title' => 'List', 'name' => 'commission-payment-list', 'type' => 'CRM'],
            ['title_tag' => 'Commission Payment', 'title' => 'Create', 'name' => 'commission-payment-create', 'type' => 'CRM'],
            ['title_tag' => 'Commission Payment', 'title' => 'Edit', 'name' => 'commission-payment-edit', 'type' => 'CRM'],
            ['title_tag' => 'Commission Payment', 'title' => 'Delete', 'name' => 'commission-payment-delete', 'type' => 'CRM'],

            ['title_tag' => 'Commission', 'title' => 'List', 'name' => 'commission-list', 'type' => 'CRM']
        ];

        foreach ($permissions as $permissionData) {
            $existingPermission = Permission::where('name', $permissionData['name'])->first();
            if (!$existingPermission) {
                Permission::create([
                    'name' => $permissionData['name'],
                    'title' => $permissionData['title'],
                    'title_tag' => $permissionData['title_tag'],
                    'type' => $permissionData['type']
                ]);
            }
        }
    }
}
