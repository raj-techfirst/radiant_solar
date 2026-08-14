<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Idempotent: creates or updates every permission, and grants the
     * B2B report permissions to the Owner and Accountant roles.
     */
    public function run(): void
    {
        $permissions = [
            // Role
            ['name' => 'role-list', 'title' => 'List', 'title_tag' => 'Role', 'type' => 'CRM'],
            ['name' => 'role-create', 'title' => 'Create', 'title_tag' => 'Role', 'type' => 'CRM'],
            ['name' => 'role-edit', 'title' => 'Edit', 'title_tag' => 'Role', 'type' => 'CRM'],
            ['name' => 'role-delete', 'title' => 'Delete', 'title_tag' => 'Role', 'type' => 'CRM'],

            // Permission
            ['name' => 'permission-list', 'title' => 'List', 'title_tag' => 'Permission', 'type' => 'CRM'],
            ['name' => 'permission-create', 'title' => 'Create', 'title_tag' => 'Permission', 'type' => 'CRM'],
            ['name' => 'permission-edit', 'title' => 'Edit', 'title_tag' => 'Permission', 'type' => 'CRM'],
            ['name' => 'permission-delete', 'title' => 'Delete', 'title_tag' => 'Permission', 'type' => 'CRM'],

            // Category
            ['name' => 'category-list', 'title' => 'List', 'title_tag' => 'Category', 'type' => 'CRM'],
            ['name' => 'category-create', 'title' => 'Create', 'title_tag' => 'Category', 'type' => 'CRM'],
            ['name' => 'category-edit', 'title' => 'Edit', 'title_tag' => 'Category', 'type' => 'CRM'],
            ['name' => 'category-delete', 'title' => 'Delete', 'title_tag' => 'Category', 'type' => 'CRM'],

            // Lead
            ['name' => 'lead-list', 'title' => 'List', 'title_tag' => 'Lead', 'type' => 'CRM'],
            ['name' => 'lead-create', 'title' => 'Create', 'title_tag' => 'Lead', 'type' => 'CRM'],
            ['name' => 'lead-edit', 'title' => 'Edit', 'title_tag' => 'Lead', 'type' => 'CRM'],
            ['name' => 'lead-delete', 'title' => 'Delete', 'title_tag' => 'Lead', 'type' => 'CRM'],

            // Bill of Supply
            ['name' => 'product-list', 'title' => 'List', 'title_tag' => 'Bill of Supply', 'type' => 'ERP'],
            ['name' => 'product-create', 'title' => 'Create', 'title_tag' => 'Bill of Supply', 'type' => 'ERP'],
            ['name' => 'product-edit', 'title' => 'Edit', 'title_tag' => 'Bill of Supply', 'type' => 'ERP'],
            ['name' => 'product-delete', 'title' => 'Delete', 'title_tag' => 'Bill of Supply', 'type' => 'ERP'],

            // Panel Company
            ['name' => 'penal-company-list', 'title' => 'List', 'title_tag' => 'Panel Company', 'type' => 'CRM'],
            ['name' => 'penal-company-create', 'title' => 'Create', 'title_tag' => 'Panel Company', 'type' => 'CRM'],
            ['name' => 'penal-company-edit', 'title' => 'Edit', 'title_tag' => 'Panel Company', 'type' => 'CRM'],
            ['name' => 'penal-company-delete', 'title' => 'Delete', 'title_tag' => 'Panel Company', 'type' => 'CRM'],

            // Panel Type
            ['name' => 'penal-type-list', 'title' => 'List', 'title_tag' => 'Panel Type', 'type' => 'CRM'],
            ['name' => 'penal-type-create', 'title' => 'Create', 'title_tag' => 'Panel Type', 'type' => 'CRM'],
            ['name' => 'penal-type-edit', 'title' => 'Edit', 'title_tag' => 'Panel Type', 'type' => 'CRM'],
            ['name' => 'penal-type-delete', 'title' => 'Delete', 'title_tag' => 'Panel Type', 'type' => 'CRM'],

            // Panel Watt
            ['name' => 'penal-watt-list', 'title' => 'List', 'title_tag' => 'Panel Watt', 'type' => 'CRM'],
            ['name' => 'penal-watt-create', 'title' => 'Create', 'title_tag' => 'Panel Watt', 'type' => 'CRM'],
            ['name' => 'penal-watt-edit', 'title' => 'Edit', 'title_tag' => 'Panel Watt', 'type' => 'CRM'],
            ['name' => 'penal-watt-delete', 'title' => 'Delete', 'title_tag' => 'Panel Watt', 'type' => 'CRM'],

            // Sales Order
            ['name' => 'sales-master-list', 'title' => 'List', 'title_tag' => 'Sales Order', 'type' => 'CRM'],
            ['name' => 'sales-master-create', 'title' => 'Create', 'title_tag' => 'Sales Order', 'type' => 'CRM'],
            ['name' => 'sales-master-edit', 'title' => 'Edit', 'title_tag' => 'Sales Order', 'type' => 'CRM'],
            ['name' => 'sales-master-delete', 'title' => 'Delete', 'title_tag' => 'Sales Order', 'type' => 'CRM'],

            // District
            ['name' => 'district-list', 'title' => 'List', 'title_tag' => 'District', 'type' => 'CRM'],
            ['name' => 'district-create', 'title' => 'Create', 'title_tag' => 'District', 'type' => 'CRM'],
            ['name' => 'district-edit', 'title' => 'Edit', 'title_tag' => 'District', 'type' => 'CRM'],
            ['name' => 'district-delete', 'title' => 'Delete', 'title_tag' => 'District', 'type' => 'CRM'],

            // Taluka
            ['name' => 'taluka-list', 'title' => 'List', 'title_tag' => 'Taluka', 'type' => 'CRM'],
            ['name' => 'taluka-create', 'title' => 'Create', 'title_tag' => 'Taluka', 'type' => 'CRM'],
            ['name' => 'taluka-edit', 'title' => 'Edit', 'title_tag' => 'Taluka', 'type' => 'CRM'],
            ['name' => 'taluka-delete', 'title' => 'Delete', 'title_tag' => 'Taluka', 'type' => 'CRM'],

            // Employee
            ['name' => 'employee-list', 'title' => 'List', 'title_tag' => 'Employee', 'type' => 'CRM'],
            ['name' => 'employee-create', 'title' => 'Create', 'title_tag' => 'Employee', 'type' => 'CRM'],
            ['name' => 'employee-edit', 'title' => 'Edit', 'title_tag' => 'Employee', 'type' => 'CRM'],
            ['name' => 'employee-delete', 'title' => 'Delete', 'title_tag' => 'Employee', 'type' => 'CRM'],

            // Sales Quotation
            ['name' => 'sales-quatation-list', 'title' => 'List', 'title_tag' => 'Sales Quotation', 'type' => 'CRM'],
            ['name' => 'sales-quatation-create', 'title' => 'Create', 'title_tag' => 'Sales Quotation', 'type' => 'CRM'],
            ['name' => 'sales-quatation-edit', 'title' => 'Edit', 'title_tag' => 'Sales Quotation', 'type' => 'CRM'],
            ['name' => 'sales-quatation-delete', 'title' => 'Delete', 'title_tag' => 'Sales Quotation', 'type' => 'CRM'],

            // Sub Division
            ['name' => 'sub-division-list', 'title' => 'List', 'title_tag' => 'Sub Division', 'type' => 'CRM'],
            ['name' => 'sub-division-create', 'title' => 'Create', 'title_tag' => 'Sub Division', 'type' => 'CRM'],
            ['name' => 'sub-division-edit', 'title' => 'Edit', 'title_tag' => 'Sub Division', 'type' => 'CRM'],
            ['name' => 'sub-division-delete', 'title' => 'Delete', 'title_tag' => 'Sub Division', 'type' => 'CRM'],

            // Payment Collection
            ['name' => 'payment-collection-list', 'title' => 'List', 'title_tag' => 'Payment Collection', 'type' => 'CRM'],
            ['name' => 'payment-collection-create', 'title' => 'Create', 'title_tag' => 'Payment Collection', 'type' => 'CRM'],
            ['name' => 'payment-collection-edit', 'title' => 'Edit', 'title_tag' => 'Payment Collection', 'type' => 'CRM'],
            ['name' => 'payment-collection-delete', 'title' => 'Delete', 'title_tag' => 'Payment Collection', 'type' => 'CRM'],

            // Inverter Company
            ['name' => 'inveter-company-list', 'title' => 'List', 'title_tag' => 'Inverter Company', 'type' => 'CRM'],
            ['name' => 'inveter-company-create', 'title' => 'Create', 'title_tag' => 'Inverter Company', 'type' => 'CRM'],
            ['name' => 'inveter-company-edit', 'title' => 'Edit', 'title_tag' => 'Inverter Company', 'type' => 'CRM'],
            ['name' => 'inveter-company-delete', 'title' => 'Delete', 'title_tag' => 'Inverter Company', 'type' => 'CRM'],

            // Follow Up
            ['name' => 'follow-up-list', 'title' => 'List', 'title_tag' => 'Follow Up', 'type' => 'CRM'],
            ['name' => 'follow-up-create', 'title' => 'Create', 'title_tag' => 'Follow Up', 'type' => 'CRM'],

            // Bank
            ['name' => 'bank-list', 'title' => 'List', 'title_tag' => 'Bank', 'type' => 'CRM'],
            ['name' => 'bank-create', 'title' => 'Create', 'title_tag' => 'Bank', 'type' => 'CRM'],
            ['name' => 'bank-edit', 'title' => 'Edit', 'title_tag' => 'Bank', 'type' => 'CRM'],
            ['name' => 'bank-delete', 'title' => 'Delete', 'title_tag' => 'Bank', 'type' => 'CRM'],

            // Terms Conditions
            ['name' => 'policy-list', 'title' => 'List', 'title_tag' => 'Terms Conditions', 'type' => 'CRM'],
            ['name' => 'policy-create', 'title' => 'Create', 'title_tag' => 'Terms Conditions', 'type' => 'CRM'],
            ['name' => 'policy-edit', 'title' => 'Edit', 'title_tag' => 'Terms Conditions', 'type' => 'CRM'],

            // Employee Status
            ['name' => 'employee-status', 'title' => 'Employee Status', 'title_tag' => 'Employee Status', 'type' => 'CRM'],

            // Reports (CRM)
            ['name' => 'reports-total-collection', 'title' => 'Total Collection', 'title_tag' => 'Reports', 'type' => 'CRM'],
            ['name' => 'reports-payment-pending', 'title' => 'Payment Pending', 'title_tag' => 'Reports', 'type' => 'CRM'],
            ['name' => 'reports-meter-charges', 'title' => 'Meter Charges', 'title_tag' => 'Reports', 'type' => 'CRM'],
            ['name' => 'reports-dispach', 'title' => 'Dispach', 'title_tag' => 'Reports', 'type' => 'CRM'],
            ['name' => 'reports-installation', 'title' => 'Installation', 'title_tag' => 'Reports', 'type' => 'CRM'],
            ['name' => 'reports-meter-application', 'title' => 'Meter Application', 'title_tag' => 'Reports', 'type' => 'CRM'],
            ['name' => 'reports-final', 'title' => 'Final Report', 'title_tag' => 'Reports', 'type' => 'CRM'],
            ['name' => 'reports-invoice', 'title' => 'Invoice Report', 'title_tag' => 'Reports', 'type' => 'CRM'],
            ['name' => 'subsidy-claim-report', 'title' => 'Subsidy Claim Report', 'title_tag' => 'Reports', 'type' => 'CRM'],
            ['name' => 'b2b-accept', 'title' => 'B2B Accept', 'title_tag' => 'Reports', 'type' => 'CRM'],
            ['name' => 'b2b-dispatch', 'title' => 'B2B Dispatch', 'title_tag' => 'Reports', 'type' => 'CRM'],
            ['name' => 'b2b-rate', 'title' => 'B2B Rate', 'title_tag' => 'Reports', 'type' => 'CRM'],
            ['name' => 'sales-agent-wise-report', 'title' => 'Sales Agent Wise', 'title_tag' => 'Reports', 'type' => 'CRM'],

            // DISCOM
            ['name' => 'discom-list', 'title' => 'List', 'title_tag' => 'DISCOM', 'type' => 'CRM'],
            ['name' => 'discom-create', 'title' => 'Create', 'title_tag' => 'DISCOM', 'type' => 'CRM'],
            ['name' => 'discom-edit', 'title' => 'Edit', 'title_tag' => 'DISCOM', 'type' => 'CRM'],
            ['name' => 'discom-delete', 'title' => 'Delete', 'title_tag' => 'DISCOM', 'type' => 'CRM'],

            // Year
            ['name' => 'year-list', 'title' => 'List', 'title_tag' => 'Year', 'type' => 'ERP'],
            ['name' => 'year-create', 'title' => 'Create', 'title_tag' => 'Year', 'type' => 'ERP'],
            ['name' => 'year-edit', 'title' => 'Edit', 'title_tag' => 'Year', 'type' => 'ERP'],
            ['name' => 'year-delete', 'title' => 'Delete', 'title_tag' => 'Year', 'type' => 'ERP'],

            // Panel/Inverter
            ['name' => 'item-group-list', 'title' => 'List', 'title_tag' => 'Panel/Inverter', 'type' => 'ERP'],
            ['name' => 'item-group-create', 'title' => 'Create', 'title_tag' => 'Panel/Inverter', 'type' => 'ERP'],
            ['name' => 'item-group-edit', 'title' => 'Edit', 'title_tag' => 'Panel/Inverter', 'type' => 'ERP'],
            ['name' => 'item-group-delete', 'title' => 'Delete', 'title_tag' => 'Panel/Inverter', 'type' => 'ERP'],

            // Supplier
            ['name' => 'supplier-list', 'title' => 'List', 'title_tag' => 'Supplier', 'type' => 'ERP'],
            ['name' => 'supplier-create', 'title' => 'Create', 'title_tag' => 'Supplier', 'type' => 'ERP'],
            ['name' => 'supplier-edit', 'title' => 'Edit', 'title_tag' => 'Supplier', 'type' => 'ERP'],
            ['name' => 'supplier-delete', 'title' => 'Delete', 'title_tag' => 'Supplier', 'type' => 'ERP'],

            // Purchase Order
            ['name' => 'purchase-order-list', 'title' => 'List', 'title_tag' => 'Purchase Order', 'type' => 'ERP'],
            ['name' => 'purchase-order-create', 'title' => 'Create', 'title_tag' => 'Purchase Order', 'type' => 'ERP'],
            ['name' => 'purchase-order-edit', 'title' => 'Edit', 'title_tag' => 'Purchase Order', 'type' => 'ERP'],
            ['name' => 'purchase-order-delete', 'title' => 'Delete', 'title_tag' => 'Purchase Order', 'type' => 'ERP'],

            // Unit
            ['name' => 'unit-list', 'title' => 'List', 'title_tag' => 'Unit', 'type' => 'ERP'],
            ['name' => 'unit-create', 'title' => 'Create', 'title_tag' => 'Unit', 'type' => 'ERP'],
            ['name' => 'unit-edit', 'title' => 'Edit', 'title_tag' => 'Unit', 'type' => 'ERP'],
            ['name' => 'unit-delete', 'title' => 'Delete', 'title_tag' => 'Unit', 'type' => 'ERP'],

            // Warehouse
            ['name' => 'warehouse-list', 'title' => 'List', 'title_tag' => 'Warehouse', 'type' => 'ERP'],
            ['name' => 'warehouse-create', 'title' => 'Create', 'title_tag' => 'Warehouse', 'type' => 'ERP'],
            ['name' => 'warehouse-edit', 'title' => 'Edit', 'title_tag' => 'Warehouse', 'type' => 'ERP'],
            ['name' => 'warehouse-delete', 'title' => 'Delete', 'title_tag' => 'Warehouse', 'type' => 'ERP'],

            // Warehouse Stock
            ['name' => 'warehouse-stock-list', 'title' => 'List', 'title_tag' => 'Warehouse Stock', 'type' => 'ERP'],
            ['name' => 'warehouse-stock-create', 'title' => 'Create', 'title_tag' => 'Warehouse Stock', 'type' => 'ERP'],

            // Goods Receipt
            ['name' => 'purchase-direct-list', 'title' => 'List', 'title_tag' => 'Goods Receipt', 'type' => 'ERP'],
            ['name' => 'purchase-direct-create', 'title' => 'Create', 'title_tag' => 'Goods Receipt', 'type' => 'ERP'],
            ['name' => 'purchase-direct-edit', 'title' => 'Edit', 'title_tag' => 'Goods Receipt', 'type' => 'ERP'],
            ['name' => 'purchase-direct-delete', 'title' => 'Delete', 'title_tag' => 'Goods Receipt', 'type' => 'ERP'],

            // Warehouse Stock Adjust
            ['name' => 'warehouse-stock-adjust-list', 'title' => 'List', 'title_tag' => 'Warehouse Stock Adjust', 'type' => 'ERP'],
            ['name' => 'warehouse-stock-adjust-create', 'title' => 'Create', 'title_tag' => 'Warehouse Stock Adjust', 'type' => 'ERP'],

            // Project Wise Stock
            ['name' => 'project-wise-stock-list', 'title' => 'List', 'title_tag' => 'Project Wise Stock', 'type' => 'ERP'],
            ['name' => 'project-wise-stock-create', 'title' => 'Create', 'title_tag' => 'Project Wise Stock', 'type' => 'ERP'],

            // Project Stock Adjust
            ['name' => 'project-stock-adjust-list', 'title' => 'List', 'title_tag' => 'Project Stock Adjust', 'type' => 'ERP'],
            ['name' => 'project-stock-adjust-create', 'title' => 'Create', 'title_tag' => 'Project Stock Adjust', 'type' => 'ERP'],

            // Goods Issue (Delivery Challan)
            ['name' => 'delivery-challan-list', 'title' => 'List', 'title_tag' => 'Goods Issue', 'type' => 'ERP'],
            ['name' => 'delivery-challan-create', 'title' => 'Create', 'title_tag' => 'Goods Issue', 'type' => 'ERP'],
            ['name' => 'delivery-challan-edit', 'title' => 'Edit', 'title_tag' => 'Goods Issue', 'type' => 'ERP'],
            ['name' => 'delivery-challan-delete', 'title' => 'Delete', 'title_tag' => 'Goods Issue', 'type' => 'ERP'],

            // Complaint Management
            ['name' => 'inquiry-list', 'title' => 'List', 'title_tag' => 'Complaint Management', 'type' => 'CRM'],
            ['name' => 'inquiry-edit', 'title' => 'Edit', 'title_tag' => 'Complaint Management', 'type' => 'CRM'],
            ['name' => 'inquiry-delete', 'title' => 'Delete', 'title_tag' => 'Complaint Management', 'type' => 'CRM'],

            // Goods Return
            ['name' => 'delivery-challan-return-list', 'title' => 'List', 'title_tag' => 'Goods Return', 'type' => 'ERP'],
            ['name' => 'delivery-challan-return-create', 'title' => 'Create', 'title_tag' => 'Goods Return', 'type' => 'ERP'],
            ['name' => 'delivery-challan-return-edit', 'title' => 'Edit', 'title_tag' => 'Goods Return', 'type' => 'ERP'],
            ['name' => 'delivery-challan-return-delete', 'title' => 'Delete', 'title_tag' => 'Goods Return', 'type' => 'ERP'],

            // BOM
            ['name' => 'bom-list', 'title' => 'List', 'title_tag' => 'BOM', 'type' => 'ERP'],
            ['name' => 'bom-create', 'title' => 'Create', 'title_tag' => 'BOM', 'type' => 'ERP'],
            ['name' => 'bom-edit', 'title' => 'Edit', 'title_tag' => 'BOM', 'type' => 'ERP'],
            ['name' => 'bom-delete', 'title' => 'Delete', 'title_tag' => 'BOM', 'type' => 'ERP'],

            // Report (ERP)
            ['name' => 'get-serial-numbers', 'title' => 'Serial Number Report', 'title_tag' => 'Report', 'type' => 'ERP'],
            ['name' => 'project-wise-dispach', 'title' => 'Project Wise Dispach', 'title_tag' => 'Report', 'type' => 'ERP'],
            ['name' => 'project-wise-stock-report', 'title' => 'Project Wise Stock Report', 'title_tag' => 'Report', 'type' => 'ERP'],
            ['name' => 'required-stock-report', 'title' => 'Requisition Report', 'title_tag' => 'Report', 'type' => 'ERP'],
            ['name' => 'stock-report', 'title' => 'Stock Report', 'title_tag' => 'Report', 'type' => 'ERP'],
            ['name' => 'b2b-dispach', 'title' => 'B2B Dispach', 'title_tag' => 'Report', 'type' => 'ERP'],

            // Rate Calculator
            ['name' => 'rate-calculator-list', 'title' => 'List', 'title_tag' => 'Rate Calculator', 'type' => 'CRM'],
            ['name' => 'rate-calculator-create', 'title' => 'Create', 'title_tag' => 'Rate Calculator', 'type' => 'CRM'],
            ['name' => 'rate-calculator-edit', 'title' => 'Edit', 'title_tag' => 'Rate Calculator', 'type' => 'CRM'],
            ['name' => 'rate-calculator-delete', 'title' => 'Delete', 'title_tag' => 'Rate Calculator', 'type' => 'CRM'],

            // Commission Payment
            ['name' => 'commission-payment-list', 'title' => 'List', 'title_tag' => 'Commission Payment', 'type' => 'CRM'],
            ['name' => 'commission-payment-create', 'title' => 'Create', 'title_tag' => 'Commission Payment', 'type' => 'CRM'],
            ['name' => 'commission-payment-edit', 'title' => 'Edit', 'title_tag' => 'Commission Payment', 'type' => 'CRM'],
            ['name' => 'commission-payment-delete', 'title' => 'Delete', 'title_tag' => 'Commission Payment', 'type' => 'CRM'],

            // Commission
            ['name' => 'commission-list', 'title' => 'List', 'title_tag' => 'Commission', 'type' => 'CRM'],

            // Lead Source
            ['name' => 'lead-source-list', 'title' => 'List', 'title_tag' => 'Lead Source', 'type' => 'CRM'],
            ['name' => 'lead-source-create', 'title' => 'Create', 'title_tag' => 'Lead Source', 'type' => 'CRM'],
            ['name' => 'lead-source-edit', 'title' => 'Edit', 'title_tag' => 'Lead Source', 'type' => 'CRM'],
            ['name' => 'lead-source-delete', 'title' => 'Delete', 'title_tag' => 'Lead Source', 'type' => 'CRM'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                ['name' => $permissionData['name']],
                [
                    'title' => $permissionData['title'],
                    'title_tag' => $permissionData['title_tag'],
                    'type' => $permissionData['type'],
                ]
            );
        }

        $reportPermissions = ['b2b-accept', 'b2b-dispatch', 'b2b-rate', 'sales-agent-wise-report', 'subsidy-claim-report'];
        $roles = Role::whereIn('name', ['Owner', 'Accountant'])->get();
        foreach ($roles as $role) {
            foreach ($reportPermissions as $permissionName) {
                if (!$role->hasPermissionTo($permissionName)) {
                    $role->givePermissionTo($permissionName);
                }
            }
        }
    }
}
