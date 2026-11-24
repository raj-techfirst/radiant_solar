# Plan: Create Quotation / Sales Order from Unmatched Excel Preview

## Decisions

- One **Create** button per Not Matched row.
- The button opens **one combined form** with a target selector: **Quotation / Sales Order / Both**.
- Only **Resident quotation** fields are included.
- Data is saved into the existing `sales_quatations` and `sales_masters` tables.
- Add a source flag column on `sales_quatations`.
- Keep Ignore, but change it to a **per-row Ignore button** because checkboxes are being removed.
- If **Sales Order only** is selected, auto-create a hidden accepted resident quotation and link the order to it.

No files changed yet.

## 1. Not Matched preview UI

Files:

- `resources/views/admin/sale/consumer-preview.blade.php`
- `resources/views/admin/sale/consumer-preview-page.blade.php`

Changes:

- Keep matched-table checkboxes and floating **Update Record** button unchanged.
- In the Not Matched table only:
  - Remove select-all checkbox.
  - Remove per-row checkboxes.
  - Remove bulk **Ignore Selected** button.
  - Add an **Action** column.
  - Add two buttons per row:
    - `Create` opens the combined modal.
    - `Ignore` calls the existing ignore endpoint with that single consumer number.
- Extract unmatched row data into button attributes:
  - `consumer_number`
  - `consumer_name`
  - Excel row number
  - recommended preview fields: discom/circle/division/sub-division
- Refactor `updatePreviewSelectAllState()` so it only controls the matched **Update Record** button.
- Replace checkbox-based ignore JS with per-row ignore JS.
- Keep SweetAlert success/error behavior consistent with the current ignore flow.

## 2. Enrich unmatched session rows

Files:

- `app/Http/Controllers/SalesMasterController.php`
- `app/Imports/SalesMasterConsumerPreview.php`

Currently unmatched session rows only contain:

- `row`
- `consumer_name`
- `consumer_number`

Add recommended preview data to unmatched rows too:

- `discom_name`
- `circle_name`
- `division_name`
- `sub_division_name`
- resolved `sub_division_id`

This lets the combined form prefill Sales Order fields from the Excel row.

Do not change matching logic.

## 3. New source flag on quotation

New migration, for example:

- `database/migrations/2026_11_24_000002_add_created_from_unmatched_preview_to_sales_quatations_table.php`

Add one column:

- `created_from_unmatched_preview` - boolean, default `false`

Use existing `sales_quatations.reference` to store the Excel `consumer_number` for traceability, so only one new column is needed.

Update model if needed:

- `app/Models/SalesQuatation.php`

## 4. New combined modal

New partial, for example:

- `resources/views/admin/sale/unmatched-create-modal.blade.php`

Include it in:

- `resources/views/admin/sale/consumer-preview-page.blade.php`

Form structure:

- Hidden inputs:
  - `preview_row`
  - `consumer_number`
  - `consumer_name`
  - recommended Excel fields
  - `_token`
- Target selector:
  - `quotation`
  - `sales_order`
  - `both`
- Resident Quotation fields:
  - lead
  - name
  - mobile
  - address
  - reference
  - agent
  - panel company/type/watt/nos
  - inverter company/capacity/qty
  - structure/common meter
  - system cost, meter charges, registration fee, other charges
  - subsidy fields
  - bank
  - technical specification rows
- Sales Order fields:
  - consumer number/name/type
  - district, taluka, pincode
  - contact number, email, address
  - GST
  - bank name/account/IFSC
  - contracted load, register kW
  - panel/inverter/watt
  - discom/circle/division/sub-division
  - BOM/file type/amount
  - agent
  - documents if practical

Prefill rules:

- Excel `consumer_name` maps to quotation `name` and sales order `consumer_name`.
- Excel `consumer_number` maps to sales order `consumer_number` and quotation `reference`.
- Excel recommended fields map to sales order discom/circle/division/sub-division.
- Other fields remain manual or come from existing dropdown defaults.

Reuse existing dropdown option sources from:

- `resources/views/admin/sales-quatation/add_sales.blade.php`
- `resources/views/admin/sale/add.blade.php`

## 5. New backend endpoint

Route in:

- `routes/web.php`

Suggested:

```php
POST sales-master/consumer-preview/create-from-unmatched
name: sales-master-consumer-preview-create-from-unmatched
```

Use permission:

- `sales-master-create`
- plus existing preview access `sales-master-list`

Controller method in:

- `app/Http/Controllers/SalesMasterController.php`

Suggested method:

```php
createFromUnmatched(Request $request)
```

Recommended support service:

- `app/Services/UnmatchedRecordCreatorService.php`

The service keeps the controller small and centralizes the two inserts.

## 6. Save behavior

Inside one `DB::transaction()`:

### Target = `quotation`

- Create resident quotation.
- Set:
  - `form_type = resident`
  - `current_status = accepted`
  - `created_from_unmatched_preview = true`
  - `reference = Excel consumer_number`
- Do not create sales order.

### Target = `both`

- Create resident quotation with the same settings.
- Create sales order linked by new `sales_quatation_id`.

### Target = `sales_order`

- Auto-create hidden resident quotation:
  - `current_status = accepted`
  - `created_from_unmatched_preview = true`
  - minimum required fields derived from form/shared inputs
- Create sales order linked to that quotation.
- Return only sales order as the created record, but keep hidden quotation id in response for audit.

### Sales order creation

Mirror existing `SalesMasterController::store()` behavior:

- `user_id = Auth::id()`
- `master_create_date = today`
- `village_id = 0`
- `file_type = C` default
- `pending_amonut = total_amount`
- commission calculation from `UserCommission`
- normalized consumer-number duplicate check
- reject if normalized number already exists in non-deleted `sales_masters`
- create linked documents if provided

### Quotation creation

Mirror existing resident branch of:

- `SalesQuatationController::store()` lines `383-442`

Important:

- Set `current_status = accepted`, not `active`, so it is valid for sales order creation.
- Add technical specification rows if provided.
- Set the new source flag.

## 7. Validation

Add proper server-side validation; the current quotation store has none.

Required for all targets:

- `target` in `quotation,sales_order,both`
- `consumer_number`
- `consumer_name`
- `agent_sales_person_id`
- `address`

Required when quotation is created:

- resident lead
- mobile
- panel company/type/watt
- inverter company
- capacity
- structure
- amounts/charges

Required when sales order is created:

- consumer number
- consumer name
- consumer type
- district
- taluka
- pincode
- contact number
- panel company
- inverter company
- panel watt
- agent

Return validation errors as JSON for inline display in modal plus SweetAlert error.

## 8. After success

AJAX response:

```json
{
  "success": true,
  "message": "...",
  "quotation_id": 123,
  "sales_master_id": 456,
  "created": ["quotation", "sales_order"]
}
```

Frontend behavior:

- Show SweetAlert success.
- Close and reset modal.
- Remove that Excel row from the Not Matched table.
- Remove it from session `unmatched` using normalized consumer number.
- Update visible counts/tabs without full page reload.

If sales order creation fails:

- Roll back everything, including hidden quotation.
- Keep the unmatched row.
- Show the exact error.

## 9. Verification

Read-only/test plan after implementation:

1. `php -l` on changed PHP files.
2. `php artisan route:list` to confirm the new route.
3. Blade render check for:
   - no unmatched checkbox
   - per-row Create and Ignore buttons
   - modal target selector
   - prefilled hidden values
4. `node --check` on extracted inline JS.
5. Run targeted migration only; do not run full `migrate` because of the unrelated `installation_penals` failure.
6. Transactional PHP test:
   - target `quotation`
   - target `both`
   - target `sales_order`
   - duplicate consumer number rejection
   - rollback on failure
   - source flag set correctly
   - unmatched session row removed after success
   - ignore still works per row

## Main files to change

- `database/migrations/2026_11_24_000002_add_created_from_unmatched_preview_to_sales_quatations_table.php`
- `app/Models/SalesQuatation.php`
- `app/Services/UnmatchedRecordCreatorService.php`
- `app/Http/Controllers/SalesMasterController.php`
- `routes/web.php`
- `resources/views/admin/sale/consumer-preview.blade.php`
- `resources/views/admin/sale/consumer-preview-page.blade.php`
- `resources/views/admin/sale/unmatched-create-modal.blade.php`
