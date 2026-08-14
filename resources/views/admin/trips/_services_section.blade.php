@php
    $oldServices = old('services');
    $initialServices = [];
    if (is_array($oldServices)) {
        foreach ($oldServices as $os) {
            $initialServices[] = [
                'id' => $os['id'] ?? null,
                'service_ids' => array_map('intval', $os['service_ids'] ?? []),
                'amount' => $os['amount'] ?? 0,
                'advance' => $os['advance'] ?? 0,
                'advance_bank_id' => $os['advance_bank_id'] ?? null,
                'due_date' => $os['due_date'] ?? null,
                'note' => $os['note'] ?? null,
            ];
        }
    } elseif (isset($existingServices) && $existingServices->count() > 0) {
        foreach ($existingServices as $ps) {
            $initialServices[] = [
                'id' => $ps->id,
                'service_ids' => array_map('intval', $ps->service_ids ?? []),
                'amount' => (float)$ps->amount,
                'advance' => (float)$ps->advance,
                'advance_bank_id' => $ps->advance_bank_id,
                'due_date' => optional($ps->due_date)->format('Y-m-d'),
                'note' => $ps->note,
            ];
        }
    }

    $serviceNameMap = $services->mapWithKeys(fn($s) => [$s->id => $s->name]);
    $bankMap = $banks->mapWithKeys(fn($b) => [$b->id => $b->bank_name . ' - ' . $b->account_number]);
@endphp

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-tools me-2"></i> Services</span>
        <button type="button" class="btn btn-sm btn-primary" id="openServiceModalBtn">
            <i class="bi bi-plus-lg me-1"></i> Add Service
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Services</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Advance</th>
                        <th class="text-end">Balance</th>
                        <th>Due Date</th>
                        <th class="text-center" style="width:110px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="servicesTbody">
                    <tr id="noServicesRow"><td colspan="6" class="text-center text-muted py-3">No services added.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="servicesHiddenInputs"></div>

@push('modals')
<div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-tools me-2"></i><span id="serviceModalTitle">Add Service</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalEditIndex" value="">
                <input type="hidden" id="modalEditId" value="">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Services <span class="text-danger">*</span></label>
                        <select class="form-select" id="modalSvcSelect" multiple>
                            @foreach($services as $svc)
                                <option value="{{ $svc->id }}">{{ $svc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" id="modalAmount" min="1" step="1">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Advance</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" class="form-control" id="modalAdvance" min="0" step="1" value="0">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bank <span class="text-danger d-none" id="modalBankStar">*</span></label>
                        <select class="form-select" id="modalBank">
                            <option value="">Select Bank</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_number }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="modalDueDate">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Note</label>
                        <input type="text" class="form-control" id="modalNote" maxlength="500">
                    </div>
                </div>
                <div class="alert alert-danger mt-3 d-none" id="modalErrorBox"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" id="modalSaveBtn"><i class="bi bi-check-lg me-1"></i> Save</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
$(function() {
    var serviceNames = @json($serviceNameMap);
    var bankMap      = @json($bankMap);
    var services     = @json($initialServices);

    var $tbody  = $('#servicesTbody');
    var $hidden = $('#servicesHiddenInputs');
    var $modal  = $('#serviceModal');

    function showModal() {
        $modal.css({ display: 'block' }).attr('aria-hidden', 'false');
        $('body').addClass('modal-open').css('overflow', 'hidden');
        if (!$('.modal-backdrop.service-modal-backdrop').length) {
            var $bd = $('<div class="modal-backdrop fade service-modal-backdrop"></div>').appendTo('body');
            $bd[0].offsetHeight;
            $bd.addClass('show');
        }
        $modal[0].offsetHeight;
        $modal.addClass('show');
    }
    function hideModal() {
        $modal.removeClass('show').attr('aria-hidden', 'true');
        $('.modal-backdrop.service-modal-backdrop').removeClass('show');
        setTimeout(function() {
            $modal.css({ display: 'none' });
            $('body').removeClass('modal-open').css('overflow', '');
            $('.modal-backdrop.service-modal-backdrop').remove();
        }, 200);
    }
    $(document).on('click', '#serviceModal [data-bs-dismiss="modal"], #serviceModal .btn-close', function() { hideModal(); });
    $(document).on('click', '.modal-backdrop.service-modal-backdrop', function() { hideModal(); });
    $(document).on('keydown', function(e) { if (e.key === 'Escape' && $modal.hasClass('show')) hideModal(); });

    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
            return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
        });
    }
    function fmt(n) {
        return '₹' + (parseFloat(n)||0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function formatDate(d) {
        if (!d) return '-';
        var p = d.split('-');
        return p.length === 3 ? (p[2] + '-' + p[1] + '-' + p[0]) : d;
    }

    function render() {
        if (services.length === 0) {
            $tbody.html('<tr id="noServicesRow"><td colspan="6" class="text-center text-muted py-3">No services added.</td></tr>');
            $hidden.empty();
            return;
        }

        var html = '';
        services.forEach(function(s, i) {
            var svcBadges = (s.service_ids || []).map(function(id) {
                var name = serviceNames[id];
                return name ? '<span class="badge bg-info me-1">' + escapeHtml(name) + '</span>' : '';
            }).join('');
            var balance = Math.max(0, (parseFloat(s.amount)||0) - (parseFloat(s.advance)||0));
            html += '<tr>' +
                '<td>' + svcBadges + (s.note ? '<small class="d-block text-muted">' + escapeHtml(s.note) + '</small>' : '') + '</td>' +
                '<td class="text-end">' + fmt(s.amount) + '</td>' +
                '<td class="text-end">' + fmt(s.advance) + '</td>' +
                '<td class="text-end ' + (balance > 0 ? 'text-danger' : 'text-success') + '"><strong>' + fmt(balance) + '</strong></td>' +
                '<td>' + escapeHtml(formatDate(s.due_date)) + '</td>' +
                '<td class="text-center">' +
                    '<button type="button" class="btn btn-sm btn-outline-primary me-1 edit-svc" data-i="' + i + '" title="Edit"><i class="bi bi-pencil"></i></button>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger remove-svc" data-i="' + i + '" title="Remove"><i class="bi bi-trash"></i></button>' +
                '</td>' +
            '</tr>';
        });
        $tbody.html(html);

        var inputs = '';
        services.forEach(function(s, i) {
            var prefix = 'services[' + i + ']';
            if (s.id) inputs += '<input type="hidden" name="' + prefix + '[id]" value="' + escapeHtml(s.id) + '">';
            (s.service_ids || []).forEach(function(svcId) {
                inputs += '<input type="hidden" name="' + prefix + '[service_ids][]" value="' + escapeHtml(svcId) + '">';
            });
            inputs += '<input type="hidden" name="' + prefix + '[amount]" value="' + escapeHtml(s.amount) + '">';
            inputs += '<input type="hidden" name="' + prefix + '[advance]" value="' + escapeHtml(s.advance || 0) + '">';
            if (s.advance_bank_id) inputs += '<input type="hidden" name="' + prefix + '[advance_bank_id]" value="' + escapeHtml(s.advance_bank_id) + '">';
            if (s.due_date) inputs += '<input type="hidden" name="' + prefix + '[due_date]" value="' + escapeHtml(s.due_date) + '">';
            if (s.note) inputs += '<input type="hidden" name="' + prefix + '[note]" value="' + escapeHtml(s.note) + '">';
        });
        $hidden.html(inputs);
    }

    function initModalSvcSelect2(selectedIds) {
        var $svc = $('#modalSvcSelect');
        if ($.fn.select2 && $svc.hasClass('select2-hidden-accessible')) $svc.select2('destroy');
        $svc.val(selectedIds || []);
        if ($.fn.select2) {
            $svc.select2({
                placeholder: 'Select Services',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5',
                dropdownParent: $('#serviceModal'),
            });
        }
        $svc.val(selectedIds || []).trigger('change.select2');
    }

    function toggleModalBankRequired() {
        var adv = parseFloat($('#modalAdvance').val()) || 0;
        $('#modalBankStar').toggleClass('d-none', !(adv > 0));
    }

    function openModal(editIdx) {
        $('#modalErrorBox').addClass('d-none').text('');
        if (editIdx == null || editIdx === '') {
            $('#serviceModalTitle').text('Add Service');
            $('#modalEditIndex').val('');
            $('#modalEditId').val('');
            initModalSvcSelect2([]);
            $('#modalAmount').val('');
            $('#modalAdvance').val('0');
            $('#modalBank').val('');
            $('#modalDueDate').val('');
            $('#modalNote').val('');
        } else {
            var s = services[editIdx];
            $('#serviceModalTitle').text('Edit Service');
            $('#modalEditIndex').val(editIdx);
            $('#modalEditId').val(s.id || '');
            initModalSvcSelect2((s.service_ids || []).map(String));
            $('#modalAmount').val(s.amount);
            $('#modalAdvance').val(s.advance || 0);
            $('#modalBank').val(s.advance_bank_id || '');
            $('#modalDueDate').val(s.due_date || '');
            $('#modalNote').val(s.note || '');
        }
        toggleModalBankRequired();
        showModal();
    }

    function saveModal() {
        var svcIds = $('#modalSvcSelect').val() || [];
        var amount = parseFloat($('#modalAmount').val()) || 0;
        var advance = parseFloat($('#modalAdvance').val()) || 0;
        var bankId = $('#modalBank').val();
        var dueDate = $('#modalDueDate').val();
        var note = $('#modalNote').val();

        var errs = [];
        if (!svcIds.length) errs.push('At least one service is required.');
        if (amount < 1) errs.push('Amount must be at least 1.');
        if (advance > 0 && !bankId) errs.push('Bank is required when advance is greater than 0.');
        if (errs.length) {
            $('#modalErrorBox').removeClass('d-none').html(errs.join('<br>'));
            return;
        }

        var data = {
            id: $('#modalEditId').val() || null,
            service_ids: svcIds.map(function(x) { return parseInt(x); }),
            amount: amount,
            advance: advance,
            advance_bank_id: bankId ? parseInt(bankId) : null,
            due_date: dueDate || null,
            note: note || null,
        };

        var idx = $('#modalEditIndex').val();
        if (idx === '') services.push(data);
        else services[parseInt(idx)] = data;

        render();
        hideModal();
    }

    $(document).on('click', '#openServiceModalBtn', function() { openModal(); });
    $tbody.on('click', '.edit-svc', function() { openModal($(this).data('i')); });
    $tbody.on('click', '.remove-svc', function() {
        const idx = $(this).data('i');
        window.koAlert.confirmDelete('Remove this service?').then(ok => {
            if (!ok) return;
            services.splice(idx, 1);
            render();
        });
    });
    $(document).on('input change', '#modalAdvance', toggleModalBankRequired);
    $(document).on('click', '#modalSaveBtn', saveModal);

    render();
});
</script>
@endpush
