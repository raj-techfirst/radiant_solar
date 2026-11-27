<?php

namespace App\Services;

use App\Imports\SalesMasterConsumerPreview;
use App\Models\AgentSalesPerson;
use App\Models\CompanyProfile;
use App\Models\Document;
use App\Models\SalesMaster;
use App\Models\SalesQuatation;
use App\Models\SalesQuatationTechnicalSpecification;
use App\Models\UserCommission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnmatchedRecordCreatorService
{
    public function create(array $data, string $target): array
    {
        $reference = trim((string) ($data['reference'] ?? ''));
        $data['reference'] = $reference === '' ? null : strtoupper($reference);

        return DB::transaction(function () use ($data, $target) {
            $quotation = $this->createQuotation($data);
            $salesMaster = null;
            $created = ['quotation'];

            if (in_array($target, ['sales_order', 'both'], true)) {
                $salesMaster = $this->createSalesOrder($data, $quotation->id);
                $created[] = 'sales_order';
            }

            return [
                'quotation_id' => $quotation->id,
                'sales_master_id' => $salesMaster ? $salesMaster->id : null,
                'created' => $created,
            ];
        });
    }

    public function consumerNumberExists(string $consumerNumber): bool
    {
        $normalized = SalesMasterConsumerPreview::normalizeNumber($consumerNumber);

        if ($normalized === '') {
            return false;
        }

        return DB::table('sales_masters')
            ->whereNull('deleted_at')
            ->whereRaw("REPLACE(REPLACE(consumer_number, ' ', ''), CHAR(39), '') = ?", [$normalized])
            ->exists();
    }

    private function createQuotation(array $data): SalesQuatation
    {
        $quotation = new SalesQuatation();
        $quotation->user_id = Auth::id();
        $quotation->form_type = 'resident';
        $quotation->current_status = 'accepted';
        $quotation->created_from_unmatched_preview = true;
        $quotation->lead_master_id = $data['lead_master_id'] ?? null;
        $quotation->name = strtoupper((string) ($data['consumer_name'] ?? ''));
        $quotation->mobile = $data['contact_number'] ?? ($data['mobile'] ?? null);
        $quotation->address = $data['address'] ?? null;
        $quotation->penal_company_id = $this->multiValue($data['penal_company_id'] ?? null);
        $quotation->penal_type_id = $data['penal_type_id'] ?? null;
        $quotation->penal_watt_id = $data['penal_watt_id'] ?? null;
        $quotation->penal_nos = $data['penal_nos'] ?? null;
        $quotation->pv_capacity_kw = $data['pv_capacity_kw'] ?? null;
        $quotation->inveter_company_id = $this->multiValue($data['inveter_company_id'] ?? null);
        $quotation->inveter_capacity = $data['inveter_capacity'] ?? null;
        $quotation->no_of_inveter = $data['no_of_inveter'] ?? null;
        $quotation->structure = $data['structure'] ?? null;
        $quotation->common_meter = $data['common_meter'] ?? 'No';
        $quotation->total_system_cost = $this->numberOrZero($data['total_system_cost'] ?? 0);
        $quotation->meter_charges_extra = $data['meter_charges_extra'] ?? 'No';
        $quotation->meter_charges = $this->numberOrZero($data['meter_charges'] ?? 0);
        $quotation->registration_fee = $this->numberOrZero($data['registration_fee'] ?? 0);
        $quotation->total_amount = $this->numberOrZero($data['total_amount'] ?? 0);
        $quotation->reference = $data['reference'] ?? null;
        $quotation->agent_sales_person_id = $data['agent_sales_person_id'] ?? null;
        $quotation->other_charge_name = $data['other_charge_name'] ?? null;
        $quotation->other_charge_amount = $this->numberOrZero($data['other_charge_amount'] ?? 0);
        $quotation->subsidy = $this->numberOrZero($data['subsidy'] ?? 0);
        $quotation->state_subsidy_cast = $data['state_subsidy_select'] ?? null;
        $quotation->state_subsidy = $this->numberOrZero($data['state_subsidy'] ?? 0);
        $quotation->bank_id = $data['bank_id'] ?? null;
        $quotation->save();

        $this->saveTechnicalSpecifications($quotation, $data);

        return $quotation;
    }

    private function createSalesOrder(array $data, int $quotationId): SalesMaster
    {
        $salesMaster = new SalesMaster();
        $salesMaster->master_create_date = date('Y-m-d');
        $salesMaster->user_id = Auth::id();
        $salesMaster->penal_company_id = $data['penal_company_id'] ?? null;
        $salesMaster->inveter_company_id = $data['inveter_company_id'] ?? null;
        $salesMaster->penal_watt_id = $data['penal_watt_id'] ?? null;
        $salesMaster->lead_master_id = $data['lead_master_id'] ?? null;
        $salesMaster->sales_quatation_id = $quotationId;
        $salesMaster->consumer_number = $data['consumer_number'];
        $salesMaster->consumer_type = ! empty($data['consumer_type']) ? $data['consumer_type'] : 'Resident';
        $salesMaster->consumer_name = strtoupper((string) ($data['consumer_name'] ?? ''));
        $salesMaster->ragistration_portal = $data['ragistration_portal'] ?? 'National';
        $salesMaster->ragistration_number = $data['ragistration_number'] ?? null;
        $registrationDate = trim((string) ($data['ragistration_date'] ?? ''));
        $registrationTimestamp = $registrationDate === '' ? false : strtotime($registrationDate);
        $salesMaster->ragistration_date = $registrationTimestamp === false ? null : date('Y-m-d', $registrationTimestamp);
        $salesMaster->gst_number = isset($data['gst_number']) ? strtoupper((string) $data['gst_number']) : null;
        $salesMaster->district_id = $data['district_id'] ?? null;
        $salesMaster->taluka_id = $data['taluka_id'] ?? null;
        $salesMaster->village_id = 0;
        $salesMaster->pin_code = $data['pin_code'] ?? null;
        $salesMaster->contact_number = $data['contact_number'] ?? null;
        $salesMaster->register_kw = $data['pv_capacity_kw'] ?? null;
        $salesMaster->email = $data['email'] ?? null;
        $salesMaster->address = $data['address'] ?? null;
        $salesMaster->aadhaar_number = $data['aadhaar_number'] ?? null;
        $salesMaster->bank_name = $data['bank_name'] ?? null;
        $salesMaster->bank_account = $data['bank_account'] ?? null;
        $salesMaster->ifsc_code = $data['ifsc_code'] ?? null;
        $salesMaster->contracted_load = $data['contracted_load'] ?? null;
        $salesMaster->phase = $data['phase'] ?? 'Single phase';
        $salesMaster->division = $data['division'] ?? null;
        $salesMaster->sub_division_id = ! empty($data['sub_division_id']) ? $data['sub_division_id'] : 0;
        $salesMaster->circle = $data['circle'] ?? null;
        $salesMaster->discom = $data['discom'] ?? null;
        $salesMaster->reference = $data['reference'] ?? null;
        $salesMaster->bom_id = ! empty($data['bom_id']) ? $data['bom_id'] : 0;
        $salesMaster->file_type = ! empty($data['file_type']) ? $data['file_type'] : 'C';
        $salesMaster->total_amount = $this->numberOrZero($data['total_amount'] ?? 0);
        $salesMaster->pending_amonut = $this->numberOrZero($data['total_amount'] ?? 0);
        $salesMaster->agent_sales_person_id = $data['agent_sales_person_id'] ?? null;
        $salesMaster->remark = $data['remark'] ?? null;
        $salesMaster->save();

        $this->applyCommission($salesMaster);
        $this->saveDocument($salesMaster, $data);

        return $salesMaster;
    }

    private function saveTechnicalSpecifications(SalesQuatation $quotation, array $data): void
    {
        $technical = is_array($data['technical'] ?? null) ? $data['technical'] : [];
        $resDescriptions = is_array($data['res_itemDescription'] ?? null) ? $data['res_itemDescription'] : [];
        $descriptions = count($resDescriptions) > 0 ? $resDescriptions : ($technical['description'] ?? []);
        $resQty = is_array($data['res_qty'] ?? null) ? $data['res_qty'] : [];
        $resSize = is_array($data['res_size'] ?? null) ? $data['res_size'] : [];
        $resMake = is_array($data['res_make'] ?? null) ? $data['res_make'] : [];
        $resType = is_array($data['res_type'] ?? null) ? $data['res_type'] : [];

        if (! is_array($descriptions)) {
            return;
        }

        foreach ($descriptions as $index => $description) {
            if (trim((string) $description) === '') {
                continue;
            }

            SalesQuatationTechnicalSpecification::create([
                'sales_quatation_id' => $quotation->id,
                'itemDescription' => $description,
                'qty' => $resQty[$index] ?? $technical['qty'][$index] ?? '',
                'size' => $resSize[$index] ?? $technical['size'][$index] ?? '',
                'make' => $resMake[$index] ?? $technical['make'][$index] ?? '',
                'type' => $resType[$index] ?? $technical['type'][$index] ?? '',
            ]);
        }
    }

    private function applyCommission(SalesMaster $salesMaster): void
    {
        if (empty($salesMaster->agent_sales_person_id) || empty($salesMaster->register_kw)) {
            return;
        }

        $agentUserId = AgentSalesPerson::where('id', (int) $salesMaster->agent_sales_person_id)->value('user_id');
        $referenceDate = $salesMaster->master_create_date;

        $mainSlab = UserCommission::where('user_id', $agentUserId)
            ->where('sub_agent_id', 0)
            ->where('effective_date', '<=', $referenceDate)
            ->orderBy('effective_date', 'desc')
            ->first();
        $salesMaster->commission_amount = $mainSlab ? (float) $mainSlab->commission : 0.0;

        $subAgent = CompanyProfile::where('user_id', $agentUserId)->value('id');
        $subSlab = UserCommission::where('sub_agent_id', $subAgent)
            ->where('effective_date', '<=', $referenceDate)
            ->orderBy('effective_date', 'desc')
            ->first();
        $salesMaster->sub_commission_amount = $subSlab ? (float) $subSlab->commission : 0.0;

        $salesMaster->save();
    }

    private function saveDocument(SalesMaster $salesMaster, array $data): void
    {
        $documents = $data['document'] ?? null;

        if (is_array($documents) && count($documents) > 0) {
            foreach ($documents as $document) {
                if (! is_array($document)) {
                    continue;
                }

                $file = $document['image'] ?? null;
                $name = trim((string) ($document['name'] ?? ''));

                if (! $file || $name === '') {
                    continue;
                }

                $this->storeDocument($salesMaster, $file, $name);
            }

            return;
        }

        $file = $data['document_file'] ?? null;
        $name = trim((string) ($data['document_name'] ?? ''));

        if (! $file || $name === '') {
            return;
        }

        $this->storeDocument($salesMaster, $file, $name);
    }

    private function storeDocument(SalesMaster $salesMaster, $file, string $name): void
    {
        $directory = 'upload/document/';

        if (! file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $filename = 'img-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        Document::create([
            'sales_master_id' => $salesMaster->id,
            'name' => $name,
            'image' => $filename,
        ]);
    }

    private function multiValue($value): string
    {
        if (is_array($value)) {
            return implode(',', array_filter($value, function ($item) {
                return $item !== null && $item !== '';
            }));
        }

        return (string) $value;
    }

    private function numberOrZero($value): float
    {
        return $value === '' || $value === null ? 0.0 : (float) $value;
    }
}
