@if(!empty($prefillData))
@php $technicalSpecifications = $prefillData; @endphp
@else
@php $technicalSpecifications = technicalSpecifications(); @endphp
@endif

@php $sr_no = 3; @endphp
@if(count($technicalSpecifications) > 0)

<table class="table table-sm table-bordered tech_{{ $type }}_table">
    <thead>
        <tr>
            <th class="text-center" style="width: 20px;">#</th>
            <th class="text-center" style="min-width: 250px;">Item Description</th>
            <th class="text-center" style="width: 100px;">Qty</th>
            <th class="text-center" style="width: 150px;">Size</th>
            <th class="text-center" style="width: 300px;">Make</th>
            <th style="width: 15px;"></th>
        </tr>
    </thead>
    <tbody>
        @php $notesArr = []; @endphp
        @foreach($technicalSpecifications as $techKey => $techValue)
        @if ($techValue['type'] !== 'note') <!-- Skip the 'note' type items -->
        <tr class="tech_{{ $type }} tech_{{ $type }}_tr_{{ $sr_no }}">
            <td class="sr_no text-center">{{ $sr_no }}</td>
            <td>
                <input type="hidden" name="{{ $type }}_type[]" value="{{ $techValue['type'] }}" />
                @if ($techValue['type'] == 'structure')
                <textarea class="editor" name="{{ $type }}_itemDescription[]">{{ $techValue['itemDescription'] }}</textarea>
                @else
                <input type="text" class="form-control" name="{{ $type }}_itemDescription[]" value="{{ $techValue['itemDescription'] }}">
                @endif
            </td>
            <td><input type="text" class="form-control" name="{{ $type }}_qty[]" value="{{ $techValue['qty'] }}"></td>

           
            <td><input type="text" class="form-control  {{ ($techValue['type'] == 'structure') ? $type.'_structure_size' : '' }}" name="{{ $type }}_size[]" value="{{ $techValue['size'] }}"></td>
           

            <td><input type="text" class="form-control" name="{{ $type }}_make[]" value="{{ $techValue['make'] }}"></td>
            <td>
                @if ($techValue['type'] != 'structure')
                <button type="button" class="badge badge-light-danger border-0 variant-delete" data-type="tech_{{ $type }}" data-id="{{ $sr_no }}">
                    <i data-feather='trash-2'></i>
                </button>
                @endif

                @php $sr_no++; @endphp
            </td>
        </tr>
        @else
        @php $notesArr[] = $techValue; @endphp
        @endif

    @endforeach

    </tbody>


    <tfoot>
        @foreach($notesArr as $$techKey => $techValue)
        <tr>
            <td colspan="6">
                <button class="badge badge-light-success border-0 add-more-{{ $type }} m-0 float-end mt-1" type="button">
                    <i data-feather="plus" class="me-0"></i> Add More
                </button>
                <br>
                <h5><b>Note :</b></h5>
                <input type="hidden" name="{{ $type }}_type[]" value="{{ $techValue['type'] }}" />
                <textarea class="editor" name="{{ $type }}_itemDescription[]">{{ $techValue['itemDescription'] }}</textarea>
                <input type="hidden" name="{{ $type }}_qty[]" value="{{ $techValue['qty'] }}" />
                <input type="hidden" name="{{ $type }}_size[]" value="{{ $techValue['size'] }}" />
                <input type="hidden" name="{{ $type }}_make[]" value="{{ $techValue['make'] }}" />
            </td>
        </tr>
        @endforeach
    </tfoot>

</table>
@endif