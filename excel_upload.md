# Excel Upload Mapping

This document describes what happens after an Excel file is uploaded in the sales-master CRM: which Excel column is read, which database table/column is written, and what transformation or condition applies.

Scope: sales-master Excel upload/preview, portal sync, default bulk update, recommended update, create-from-unmatched, ignore-unmatched, and the legacy payment import. Lead and ERP serial-number uploads are separate flows and are not part of this document.

## 1. Header normalization

All Excel headers are normalized before matching:

1. Remove UTF-8 BOM.
2. Lowercase.
3. Trim outer whitespace.
4. Replace every non-alphanumeric character with `_`.
5. Trim leading/trailing `_`.

Examples:

| Excel header | Normalized key |
|---|---|
| `Consumer Number` | `consumer_number` |
| `Mobile No.` | `mobile_no` |
| `Application Number` | `application_number` |
| `Current Status Of Application` | `current_status_of_application` |
| `Sr.No.` | `sr_no` |

Source:

- `app/Imports/SalesMasterConsumerPreview.php:199-206`
- `app/Imports/SalesMasterUpdateImport.php:219-226`

## 2. Main upload flow

Route: `POST /sales-master/consumer-preview`

Source:

- `routes/web.php:136-137`
- `app/Http/Controllers/SalesMasterController.php:1755-1950`

The upload does three things:

1. `SalesMasterConsumerPreview` parses the workbook into a session preview.
2. `PortalDataSyncService` immediately writes `portal_data`.
3. The preview page can then trigger automatic default updates to `sales_masters` and `loandatas`.

Important: the preview itself is not purely read-only because portal data is synced during upload.

### Matching rules

- A `Consumer Number` column is required. Accepted aliases:
  - `Consumer Number`, `Consumer No`, `ConsumerNumber`, `ConsumerNo`, `C No`
  - normalized: `consumer_number`, `consumer_no`, `consumernumber`, `consumerno`, `c_no`
- Straight and curly apostrophes are removed from consumer/application numbers.
- Matching uses digits only. Example: `C-12345` and `12345` both become `12345`.
- Rows where both consumer name and consumer number are blank are skipped.
- Non-ignored rows whose consumer number cannot be matched to a sales master are shown as unmatched and are not portal-synced. Ignored unmatched numbers are hidden from the preview.
- Preview row numbers start at spreadsheet row `2`.

Sources:

- `app/Imports/SalesMasterConsumerPreview.php:112-116`, `:131`, `:149-161`, `:175-178`
- `app/Http/Controllers/SalesMasterController.php:1785-1904`

## 3. Writes during upload: `portal_data`

Service: `app/Services/PortalDataSyncService.php`

Called at `app/Http/Controllers/SalesMasterController.php:1917-1918`.

### `portal_data` column mapping

All rows below write to table `portal_data`.

| Excel column aliases | `portal_data` column | Transformation |
|---|---|---|
| `Status`, `Current Status Of Application`, `Current Status` | `current_status_of_application` | Trimmed text. Written only when at least one row has a non-empty parsed status value. |
| `Vendor Consumer Agreement Uploaded Yes No`, `Vendor Consumer Agreement Uploaded` | `vendor_consumer_agreement_uploaded` | Trimmed text. |
| `Vendor Consumer Agreement Uploading Date` | `vendor_consumer_agreement_uploading_date` | Excel serial to `d-M-y` when numeric serial is `1..100000`; otherwise trimmed text. |
| `Solar Plant Installation Date` | `solar_plant_installation_date` | Excel serial `1..100000` to `d-M-y`; otherwise trimmed text. |
| `Loan Taken Yes No`, `Loan Taken` | `loan_taken` | Trimmed text. |
| `Loan Status Pending Sanctioned Rejected`, `Loan Status` | `loan_status` | Trimmed text. |
| `Loan Rejection Date` | `loan_rejection_date` | Excel serial `1..100000` to `d-M-y`; otherwise trimmed text. |
| `Loan Disbursed Date First Tranche` | `loan_disbursed_date_first_tranche` | Excel serial `1..100000` to `d-M-y`; otherwise trimmed text. |
| `Loan Disbursed Amount First Tranche` | `loan_disbursed_amount_first_tranche` | Trimmed text. |
| `Loan First Tranche Disbursal Utr` | `loan_first_tranche_disbursal_utr` | Trimmed text. |
| `Loan Disbursed Date Second Tranche` | `loan_disbursed_date_second_tranche` | Excel serial `1..100000` to `d-M-y`; otherwise trimmed text. |
| `Loan Disbursed Amount Second Tranche` | `loan_disbursed_amount_second_tranche` | Trimmed text. |
| `Loan Second Tranche Disbursal Utr` | `loan_second_tranche_disbursal_utr` | Trimmed text. |
| `Feasibility Applied Kw`, `Feasibility Applied` | `feasibility_applied` | Trimmed text. |
| `Installed Pv Module Capacity Kwp`, `Installed Pv Module Capacity` | `installed_pv_module_capacity` | Trimmed text. |
| `Module Capacity Wp`, `Module Capacity` | `module_capacity_wp` | Trimmed text. |
| `Module Quantity` | `module_quantity` | Trimmed text. |
| `Inspection Status Approved Return`, `Inspection Status` | `inspection_status` | Trimmed text. |
| `Inspection Return Date` | `inspection_return_date` | Excel serial `1..100000` to `d-M-y`; otherwise trimmed text. |
| `Inspection Return Comment` | `inspection_return_comment` | Trimmed text. |
| `Subsidy Amount Rs`, `Subsidy Amount` | `subsidy_amount` | Trimmed text. |
| `Subsidy Return Date` | `subsidy_return_date` | Excel serial `1..100000` to `d-M-y`; otherwise trimmed text. |
| `Subsidy Return To Installation Inspection`, `Subsidy Return To` | `subsidy_return_to` | Trimmed text. |
| `Subsidy Return Comment` | `subsidy_return_comment` | Trimmed text. |

Additional column:

| Source | `portal_data` column | Rule |
|---|---|---|
| Nonblank Excel `Consumer Number`; on insert only, else matched `sales_masters.consumer_number` | `consumer_number` | Assigned on every matched portal row when nonblank; blank Excel values do not erase existing rows. |
| Matched sales order ID | `sales_master_id` | Lookup key. |

Source: `app/Services/PortalDataSyncService.php:16-41`, `:183-210`

### Portal sync modes

| Mode | Condition | Behavior |
|---|---|---|
| Skipped | No mapped portal value anywhere in the workbook, or no sales master matches | No DB writes. |
| Status-only | The only mapped portal value present is `Status` | Loads or creates `PortalData` by `sales_master_id`; writes `current_status_of_application` only when the cell is nonblank; assigns a nonblank Excel `consumer_number`; a new row falls back to the sales-master consumer number when the Excel cell is blank; no deletes; other portal columns untouched. |
| Full sync | Any mapped portal field other than status has a value | Loads or creates each matched `PortalData` row and saves it. Only nonblank mapped Excel values are assigned, so blank cells leave existing DB values unchanged. No portal rows are deleted. |

Blank-cell rule: blank or missing Excel values are skipped for all `portal_data` fields, including `current_status_of_application` and `consumer_number`, so a blank cell never erases an existing stored value. A new portal row still receives `consumer_number` from the sales master when the Excel cell is blank.

Status detection:

- `hasStatusData()` checks whether any row has a non-empty parsed `current_status` value.
- `isStatusOnly()` returns true only when status is the only non-empty mapped portal field.
- If no row has a status value, `current_status_of_application` is omitted entirely, even if the `Status` header exists.

Source: `app/Services/PortalDataSyncService.php:53-117`, `:129-181`

## 4. Automatic default bulk update: `sales_masters` and `loandatas`

After the preview loads, the page calls `POST /sales-master/consumer-preview/apply-default`.

Sources:

- `routes/web.php:139`
- `resources/views/admin/sale/consumer-preview-page.blade.php:175-252`
- `app/Http/Controllers/SalesMasterController.php:2242-2354`
- `app/Imports/SalesMasterUpdateImport.php`

The controller first rejects a missing preview file with `Preview file not found. Please upload the Excel file again.`. It then returns an already-done response if the update has already completed, skips with `No default fields to update.` when no preview rows are selected for update, and only then requires all default headers; if any are missing, it skips with `Default columns not found in Excel. Default fields skipped.`

Required default headers are the consumer-number column plus all default fields listed below.

### Default mode column mapping

| Excel column aliases | Target table.column | Transformation / condition |
|---|---|---|
| `Consumer Number`, `Consumer No`, `ConsumerNumber`, `ConsumerNo`, `C No` | lookup `sales_masters.consumer_number` | Required. Apostrophes stripped. Bulk update uses row→`sales_master_id` binding when available; otherwise exact `TRIM(consumer_number)` match. No direct write. |
| `Application Number` | `sales_masters.ragistration_number`, `sales_masters.pending_approvel` | Trimmed text. If nonblank, `pending_approvel = '1'`. |
| `Consumer Address`, `Address` | `sales_masters.address` | Nonblank trimmed text. |
| `District Name` | `sales_masters.district_id` | Name lookup in `districts`; case/trim-insensitive; `devbhoomi dwarka` maps to `Devbhumi Dwarka`; not-found leaves unchanged. |
| `Sanction Load Kwp`, `Sanction Load Kw`, `Sanction Load` | `sales_masters.contracted_load` | Digits, `.`, and `-` kept; formatted to 2 decimals. |
| `Proposed Pv Capacity Kwp`, `Proposed Pv Capacity Kw`, `Proposed Pv Capacity`, `Registation Kw` | `sales_masters.register_kw` | Digits, `.`, and `-` kept; formatted to 2 decimals. |
| `Application Submitted Date`, `Application Submitted At` | `sales_masters.ragistration_date` | Parsed to `Y-m-d`; blank, invalid, or time-only values leave the existing value unchanged. |
| `Feasibility Approved Date`, `Feasibility Date` | `sales_masters.feasibility_date`, `sales_masters.feasibility_approved` | Non-null parsed date sets `feasibility_approved = '1'`. |
| `Inspection Date`, `Meter Application Date` | `sales_masters.meter_application_date`, `sales_masters.meter_application_done` | Non-null parsed date sets `meter_application_done = '1'`. |
| `Subsidy Redeem Date`, `Subsidy Request Date` | `sales_masters.subsidy_request_date`, `sales_masters.subsidy_claimed` | Non-null parsed date sets `subsidy_claimed = '1'`. |
| `Subsidy Verified Date`, `Subsidy Disbursement Verify Date` | `sales_masters.subsidy_disbursement_verify_date` | Parsed to `Y-m-d`. |
| `Subsidy Disbursed Date`, `Subsidy Disbursement Date` | `sales_masters.subsidy_disbursement_date`, `sales_masters.subsidy_receveid`, `sales_masters.project_completion_date`, `sales_masters.project_completion` | Non-null parsed date also copies to `project_completion_date`, sets `subsidy_receveid = '1'`, and `project_completion = '1'`. |
| `Loan Application Date` | `loandatas.submitted_at` | Parsed to `Y-m-d`; loan row created if missing. |
| `Loan Sanctioned Date`, `Loan Sanction Date` | `loandatas.loan_sanction_date` | Parsed to `Y-m-d`; loan row created if missing. |
| `Loan Applied At Bank`, `Loan Applied Bank`, `Loan Portal` | `loandatas.loan_portal` | Nonblank trimmed text; loan row created if missing. |

Sources:

- Aliases: `app/Imports/SalesMasterUpdateImport.php:36-117`
- Mapping: `app/Imports/SalesMasterUpdateImport.php:285-378`
- Loan insert/update: `app/Imports/SalesMasterUpdateImport.php:514-613`

### Date parsing rules

`SalesMasterUpdateImport::parseDate()`:

| Excel value | Result |
|---|---|
| Blank / `null` | No field update. |
| `DateTimeInterface` | `Y-m-d`. |
| Numeric Excel serial | `Carbon::createFromTimestamp(round((value - 25569) * 86400))->format('Y-m-d')`. |
| Time-only like `12:30` | Ignored; existing value left unchanged and reported in the import error list. |
| `d-m-Y`, `d/m/Y`, `Y-m-d`, `d.m.Y` | Parsed with that format. |
| Other text | `Carbon::parse()`. |
| Invalid date | Ignored; existing value left unchanged, reported in the import error list, and warning logged. |

Source: `app/Imports/SalesMasterUpdateImport.php:659-705`

### Number parsing rules

- Keeps only digits, `.`, and `-`.
- Values like `-`, `.`, or empty become `null`.
- Otherwise formatted with exactly 2 decimals.

Source: `app/Imports/SalesMasterUpdateImport.php:644-657`

## 5. Manual recommended-field update

Route: `POST /sales-master/update-records`

Sources:

- `routes/web.php:142`
- `app/Http/Controllers/SalesMasterController.php:2382-2520`
- `resources/views/admin/sale/update-record-modal.blade.php:8-39`

Within this manual call, only fields selected in the modal are written, and the default bulk-update block is skipped; no dates, flags, or loan fields from section 4 are processed by this call. The preview page separately triggers the automatic default update from section 4 on load, so that update may already have run before the manual recommended update.

| Selected field | Accepted Excel aliases | Target table.column | Transformation |
|---|---|---|---|
| Consumer Name | `Consumer Name`, `CustomerName`, `Name` | `sales_masters.consumer_name` | Nonblank trimmed text. |
| DISCOM | `Discom Name`, `DiscomName`, `Discom` | `sales_masters.discom` | Nonblank trimmed text. |
| Circle | `Circle Name`, `CircleName`, `Circle` | `sales_masters.circle` | Nonblank trimmed text. |
| Division | `Division Name`, `DivisionName`, `Division` | `sales_masters.division` | Nonblank trimmed text. |
| Sub Division | `Sub Division Name`, `Subdivision Name`, `Sub Division`, `Subdivision` | `sales_masters.sub_division_id` | Case/trim-insensitive lookup in `sub_divisions.name`; not-found leaves unchanged. |

Source: `app/Imports/SalesMasterUpdateImport.php:24-30`, `:412-450`

## 6. Create from unmatched

Routes:

- `GET /sales-master/consumer-preview/create-from-unmatched`
- `POST /sales-master/consumer-preview/create-from-unmatched`

Sources:

- `routes/web.php:140-141`
- `app/Http/Controllers/SalesMasterController.php:1471-1528`, `:2070-2240`
- `app/Services/UnmatchedRecordCreatorService.php`

Excel values prefill the form. No DB write happens at upload time for this flow; rows are inserted when the user submits the create form. The controller forces `target = both`, so both a quotation and a sales order are created.

### Hidden/prefilled Excel fields

| Excel column aliases | Form field | Target on submit |
|---|---|---|
| `Consumer Number`, `Consumer No`, `ConsumerNumber`, `ConsumerNo`, `C No` | `consumer_number` | `sales_masters.consumer_number` |
| `Consumer Name`, `CustomerName`, `Name` | `consumer_name` | `sales_quatations.name`, `sales_masters.consumer_name` (uppercased) |
| `Consumer Registration Number`, `Application Number`, `Registration Number` | `ragistration_number` | `sales_masters.ragistration_number` |
| `Application Submitted Date`, `Application Submitted At`, `Submitted Date` | `ragistration_date` | `sales_masters.ragistration_date` |
| `Consumer Address`, `Address` | `address` | `sales_quatations.address`, `sales_masters.address` |
| `Mobile No Of Consumer`, `Mobile Number Of Consumer`, `Consumer Mobile`, `Mobile No`, `Mobile` | `contact_number` | `sales_quatations.mobile`, `sales_masters.contact_number` |
| `Email Of Consumer`, `Consumer Email`, `Email` | `email` | `sales_quatations.email`, `sales_masters.email` |
| `District Name` | `district_id` | `sales_masters.district_id` |
| `Sub Division Name`, `Subdivision Name`, `Sub Division`, `Subdivision` | `sub_division_id` | `sales_masters.sub_division_id` |
| `Division Name`, `DivisionName`, `Division` | `division` | `sales_masters.division` |
| `Circle Name`, `CircleName`, `Circle` | `circle` | `sales_masters.circle` |
| `Discom Name`, `DiscomName`, `Discom` | `discom` | `sales_masters.discom` |
| `Connection Category Name Rwa Domestic`, `Connection Category Name`, `Connection Category` | `consumer_type` | `sales_masters.consumer_type` (quotation `form_type` is hardcoded to `resident`) |
| `Sanction Load Kwp`, `Sanction Load Kw`, `Sanction Load` | `contracted_load` | `sales_masters.contracted_load` |
| `Proposed Pv Capacity Kwp`, `Proposed Pv Capacity Kw`, `Proposed Pv Capacity` | `pv_capacity_kw` | `sales_quatations.pv_capacity_kw`, `sales_masters.register_kw` |
| `Pv Module Make`, `Module Make` | `penal_company_id` | `sales_quatations.penal_company_id`, `sales_masters.penal_company_id` |
| `Module Capacity Wp`, `Module Capacity` | `penal_watt_id` | `sales_quatations.penal_watt_id`, `sales_masters.penal_watt_id` |
| `Module Quantity` | `penal_nos` | `sales_quatations.penal_nos` |
| `Inverter Make` | `inveter_company_id` | `sales_quatations.inveter_company_id`, `sales_masters.inveter_company_id` |
| `Inverter Capacity Kw`, `Inverter Capacity` | `inveter_capacity` | `sales_quatations.inveter_capacity` |
| `Inverter Quantity` | `no_of_inveter` | `sales_quatations.no_of_inveter` |
| `Subsidy Amount Rs`, `Subsidy Amount` | `subsidy` | `sales_quatations.subsidy` |
| `Loan Applied At Bank`, `Loan Applied Bank`, `Bank Name` | `bank_name`, `bank_id` | `sales_masters.bank_name`, `sales_quatations.bank_id` |
| `Last Comment` | `remark` | `sales_masters.remark` |
| `Loan Taken Yes No`, `Loan Taken` | `file_type` | `sales_masters.file_type`: `Y...` → `L`, `N...` → `C` |

Prefill source: `app/Http/Controllers/SalesMasterController.php:1573-1640`

### Values not from Excel but written on create

| Value | Target |
|---|---|
| `National` | `sales_masters.ragistration_portal` |
| `Resident` default when no consumer type is provided | `sales_masters.consumer_type` |
| `null` | `sales_quatations.lead_master_id`, `sales_masters.lead_master_id` |
| `No` | `sales_quatations.common_meter` |
| Uppercased `GST Number` input | `sales_masters.gst_number` |
| `0` | `sales_masters.village_id` |
| `0` when blank | `sales_masters.sub_division_id`, `sales_masters.bom_id` |
| `C` when blank | `sales_masters.file_type` |
| `Single phase` when blank | `sales_masters.phase` |
| Form `total_amount` | `sales_masters.total_amount`, `sales_masters.pending_amonut` |
| Manual `Reference` input; blank becomes `null`, otherwise uppercase | `sales_quatations.reference`, `sales_masters.reference` |
| Current user | `sales_quatations.user_id`, `sales_masters.user_id` |
| Current date | `sales_masters.master_create_date` |
| `accepted` | `sales_quatations.current_status` |
| `resident` | `sales_quatations.form_type` |
| `true` | `sales_quatations.created_from_unmatched_preview` |
| Conditional commission | `sales_masters.commission_amount`, `sales_masters.sub_commission_amount` |
| Form totals/charges | `sales_quatations.total_system_cost`, `meter_charges_extra`, `meter_charges`, `registration_fee`, `total_amount`, `other_charge_name`, `other_charge_amount`, `state_subsidy_cast`, `state_subsidy` |
| Uploaded documents | `documents.sales_master_id`, `documents.name`, `documents.image` |
| Technical specification rows | `sales_quatation_technical_specifications` |

Insert source: `app/Services/UnmatchedRecordCreatorService.php:18-146`, `:148-270`

## 7. Ignore unmatched rows

Route: `POST /sales-master/consumer-preview/ignore`

Source: `app/Http/Controllers/SalesMasterController.php:1962-2068`

Writes table `consumer_ignore`:

| Source | Target column |
|---|---|
| Digits-only normalized Excel/preview consumer number | `consumer_ignore.consumer_number` |
| Submitted name → preview name → existing stored ignore name | `consumer_ignore.consumer_name` |
| Current time/date | `consumer_ignore.ignore_date`, `consumer_ignore.timestep`, `updated_at` |
| Insert only | `consumer_ignore.created_at` |

A blank submitted name falls back to the preview name; an existing stored ignore name is preserved only when both are blank.

Ignored unmatched consumer numbers are hidden from the unmatched preview; ignored numbers that match a sales master are still shown in the matched preview. Ignore records are deleted when a matching unmatched record is successfully created.

## 8. Legacy payment Excel import

Route: `POST /sales-order-import`

Sources:

- `routes/web.php:266`
- `app/Http/Controllers/SalesMasterController.php:2522-2536`
- `app/Imports/Salesorder.php`

This flow does not create sales orders. It imports payments.

| Excel column | Target table.column | Transformation / condition |
|---|---|---|
| `c_no` | lookup `sales_masters.consumer_number` | Exact match; blank or no match skips row. |
| `type` | `paymet_collections.payment_type` | Raw value. |
| `amount` | `paymet_collections.amount` | Raw value. |
| `date` | `paymet_collections.payment_date` | Excel serial converted to `Y-m-d`; blank becomes empty string. |
| `nos` | `paymet_collections.cheque_number` when `type = Cheque`; `utr_number` when `type = NEFT`; `upi_id` for `UPI` and all other types | Raw value. |
| `bank_name` | `paymet_collections.bank_name` | Raw value. |
| `branch_name` | `paymet_collections.branch_name` | Raw value. |
| none | `paymet_collections.remark` | Hardcoded empty string. |
| `status` | `paymet_collections.status` | Only `Approved` sets `1`. |

When `status = Approved`, these `sales_masters` columns are also updated:

| Target column | Rule |
|---|---|
| `pending_amonut` | `pending_amonut - amount` |
| `payment_receveid` | `1` when remaining pending amount is `0` |
| `dispach_pending_list` | `1` when `feasibility_approved = 1` and payment is fully received |
| `installation_pending` | `1` when an installation person is assigned and dispatch is pending |

Source: `app/Imports/Salesorder.php:24-74`

## 9. Parsed but not written at upload

These values are parsed/displayed but are not written by the upload-time default update or portal sync:

| Excel column | Where it is used |
|---|---|
| `State Name` | Malformed CSV row repair only. |
| `Mobile No Of Consumer`, `Mobile Number Of Consumer`, `Consumer Mobile`, `Mobile No`, `Mobile` | Create-from-unmatched prefill only. |
| `Email Of Consumer`, `Consumer Email`, `Email` | Create-from-unmatched prefill only. |
| `Connection Category Name Rwa Domestic`, `Connection Category Name`, `Connection Category` | Create-from-unmatched prefill only. |
| `Pv Module Make`, `Module Make` | Create-from-unmatched company lookup only. |
| `Module Capacity Wp`, `Module Capacity` | Create-from-unmatched watt lookup only. |
| `Inverter Capacity Kw`, `Inverter Capacity` | Create-from-unmatched quotation capacity only. |
| `Inverter Make` | Create-from-unmatched inverter-company lookup only. |
| `Inverter Quantity` | Create-from-unmatched quotation quantity only. |
| `Last Comment` | Create-from-unmatched remark only. |

The preview column `Sales Order Status` is derived from existing DB flags via `toGetSalesMasterLastStatus()`; it is not read from the Excel `Status` column. Excel `Status` is written only to `portal_data.current_status_of_application`.

## 10. Derived dashboard statuses after upload

These are not direct writes. They are calculated from imported values.

### Portal Status Wise

Statuses are exclusive and first-match-wins: a sales order is counted in the earliest matching row below and skipped for later rows.

| Status | Source data |
|---|---|
| Vendar Selection (Pending) | `sales_masters.consumer_type` is `Resident` and no `portal_data` row or blank portal status |
| Upload Agreement (Pending) | `portal_data.vendor_consumer_agreement_uploaded` blank or `no` |
| Installation (Pending) | Agreement `yes` and `solar_plant_installation_date` empty |
| Inspection (Pending) | Installation date exists and `inspection_status` blank |
| Inspection (Return) | `inspection_status` contains `return` |
| Subsidy Request (Not Redeemed) | Inspection `approved` and no valid `subsidy_request_date` |
| Subsidy Request (Redeemed) | Valid `subsidy_request_date` and no verified date |
| Subsidy Request (Return) | `portal_data.subsidy_return_date` exists and no verified date |
| Subsidy Disbursal (Pending) | Verified date exists and no disbursement date |
| Subsidy Disbursal (Disbursed) | Valid `subsidy_disbursement_date` |
| Subsidy Request (Give Up) | `sales_masters.subsidy_giveup = 1` |

Source: `app/Services/PortalStatusService.php`

### Loan Status

| Imported/derived value | Stage |
|---|---|
| Blank or `initiated` | Apply |
| `sanctioned` | Sanctioned |
| `reject`, `expired` | Reject |
| `partial disburse` | Partial Disburse |
| `disbursed`, `completed` | Disbursed |
| Other Finance with no valid `loandatas.submitted_at` | Apply Pending |

For Other Finance with a valid `loandatas.submitted_at`, `portal_data.loan_status` is overridden by loan amounts/dates: approved amount with disbursements gives `Disbursed` or `Partial Disburse`; otherwise a valid `loandatas.loan_sanction_date` gives `Sanctioned`.

Lender classification:

| Source data | Lender |
|---|---|
| `portal_data.loan_taken = yes` | Jansamarth |
| `portal_data.loan_taken = no` and `sales_masters.file_type = L` | Other Finance |

Source: `app/Services/LoanStatusService.php`

## 11. Safeguards and failure behavior

| Safeguard | Behavior |
|---|---|
| File validation | Main upload accepts `xlsx`, `xls`, `csv`; maximum 10 MB. |
| Required Consumer Number | Upload fails if no consumer-number alias exists. |
| Wrong Excel / no matches | Portal sync is skipped when no workbook row matches a sales master. |
| No upload DB deletes | Upload sync never deletes DB rows; `portal_data` rows absent from the Excel file are left unchanged. |
| Blank Excel cells | Blank/missing cells are skipped; existing DB values are not erased. Default and recommended updates already skip blank values, and portal sync now skips blank portal fields. |
| Status-only workbook | Existing portal rows get a nonblank `current_status_of_application` and nonblank Excel `consumer_number`; newly inserted rows also get a sales-master `consumer_number` fallback when Excel is blank; never deletes other portal rows. |
| Default header validation | Automatic default update is skipped unless all default headers are present. |
| Recommended header validation | Only consumer number plus selected recommended fields are required. |
| Apostrophe cleanup | Preview strips straight/curly apostrophes from consumer and application numbers. Default bulk update strips apostrophes only from the consumer number. Duplicate check strips spaces and straight apostrophes from DB consumer numbers. |
| Consumer matching | Preview matching uses digits only. Default bulk update uses row→`sales_master_id` binding when available, otherwise exact `TRIM(consumer_number)` match. |
| Row+ID binding | Bulk update binds spreadsheet row to selected `sales_master_id`, preventing wrong-row updates. |
| Empty row skip | Rows with blank name and blank number are skipped. |
| Malformed CSV repair | Address fragments and shifted consumer/date columns can be repaired when required headers exist. |
| Invalid date | Ignored; existing value left unchanged. Invalid dates are logged, time-only values are reported in the import error list. |
| Ignore blank name | An existing `consumer_ignore.consumer_name` is kept only when both the submitted and preview names are blank. |
| Duplicate unmatched consumer | The submitted consumer number is digit-normalized; the DB comparison strips only spaces and straight apostrophes from stored consumer numbers. |

## 12. Quick table index

| Table | Written by | Excel-driven columns |
|---|---|---|
| `portal_data` | Upload-time portal sync | All columns in section 3 |
| `sales_masters` | Default bulk update, recommended update, create-from-unmatched, payment import | Sections 4-8 |
| `loandatas` | Default bulk update | `submitted_at`, `loan_sanction_date`, `loan_portal` |
| `sales_quatations` | Create-from-unmatched | Section 6 |
| `sales_quatation_technical_specifications` | Create-from-unmatched form | Section 6 |
| `documents` | Create-from-unmatched form uploads | Section 6 |
| `consumer_ignore` | Ignore unmatched action | Section 7 |
| `paymet_collections` | Legacy payment import | Section 8 |
