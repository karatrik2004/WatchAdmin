@extends('layouts.admin')
@section('title') Create Shipping Invoice @endsection

@section('inline-css')
<style>
    #watch-rows .watch-row { background: #fafafa; border: 1px solid #dee2e6; border-radius: 6px; padding: 10px 12px; margin-bottom: 8px; }
    #watch-rows .description-preview { font-size: 12px; color: #333; white-space: pre-line; line-height: 1.5; }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Create shipping invoice</h4>
                <a href="{{ route('admin.shipping-invoices.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
            </div>
            <div class="card-body">

                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif
                @if(session('alert-error'))
                <div class="alert alert-danger mb-0">{{ session('alert-error') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.shipping-invoices.store') }}" id="invoice-form">
                    @csrf
                    <input type="hidden" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}">

                    <div class="mb-3">
                        <label class="form-label" for="deal-dropdown">Deal</label>
                        <select id="deal-dropdown" class="form-select">
                            <option value="">— Select a deal —</option>
                        </select>
                    </div>

                    <div id="watch-rows"></div>
                    <p id="no-items-msg" class="text-muted small py-2">No deals added.</p>

                    <button type="submit" class="btn btn-primary">Generate</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('inline-js')
<script>
(function () {
    'use strict';
    var added = [];
    var catalog = [];
    var rows = document.getElementById('watch-rows');
    var noMsg = document.getElementById('no-items-msg');
    var dd = document.getElementById('deal-dropdown');

    function reindexItems() {
        [].forEach.call(rows.querySelectorAll('.watch-row'), function (row, i) {
            row.querySelectorAll('[name^="items["]').forEach(function (el) {
                if (el.name) { el.name = el.name.replace(/items\[[0-9]+\]/, 'items[' + i + ']'); }
            });
        });
    }

    function updateEmpty() {
        noMsg.classList.toggle('d-none', rows.children.length > 0);
    }

    function refreshOptions() {
        if (!dd) { return; }
        [].forEach.call(dd.querySelectorAll('option'), function (o) {
            if (!o.value) { return; }
            o.disabled = added.indexOf(parseInt(o.value, 10)) >= 0;
        });
    }

    function addRow(d) {
        if (added.indexOf(d.id) >= 0) { return; }
        added.push(d.id);
        var i = rows.querySelectorAll('.watch-row').length;
        var row = document.createElement('div');
        row.className = 'watch-row';

        var wrap = document.createElement('div');
        wrap.className = 'd-flex flex-wrap align-items-start gap-2 justify-content-between';

        var prev = document.createElement('div');
        prev.className = 'description-preview flex-grow-1';
        prev.textContent = d.description;

        var priceWrap = document.createElement('div');
        priceWrap.className = 'text-end text-nowrap';
        var ccy = (d.currency || 'USD').toUpperCase();
        var pr = document.createElement('input');
        pr.type = 'number';
        pr.step = '0.01';
        pr.min = '0';
        pr.className = 'form-control form-control-sm d-inline-block text-end js-unit-price';
        pr.name = 'items[' + i + '][unit_price]';
        pr.value = Number(d.unit_price).toFixed(2);
        pr.setAttribute('data-currency', ccy);
        pr.style.maxWidth = '9rem';
        priceWrap.appendChild(document.createTextNode('Sale (' + ccy + ' $): '));
        priceWrap.appendChild(pr);

        var rm = document.createElement('button');
        rm.type = 'button';
        rm.className = 'btn btn-sm btn-outline-danger';
        rm.setAttribute('title', 'Remove');
        rm.appendChild(document.createTextNode('×'));
        var dealId = d.id;
        rm.onclick = function () {
            added = added.filter(function (x) { return x !== dealId; });
            row.remove();
            reindexItems();
            updateEmpty();
            refreshOptions();
        };

        var hidDeal = document.createElement('input');
        hidDeal.type = 'hidden';
        hidDeal.name = 'items[' + i + '][deal_id]';
        hidDeal.value = d.id;

        var ta = document.createElement('textarea');
        ta.className = 'd-none';
        ta.name = 'items[' + i + '][description]';
        ta.appendChild(document.createTextNode(d.description || ''));

        var hidGst = document.createElement('input');
        hidGst.type = 'hidden';
        hidGst.name = 'items[' + i + '][gst_type]';
        hidGst.value = d.gst_type || '';

        wrap.appendChild(prev);
        wrap.appendChild(priceWrap);
        wrap.appendChild(rm);
        row.appendChild(wrap);
        row.appendChild(hidDeal);
        row.appendChild(ta);
        row.appendChild(hidGst);

        rows.appendChild(row);
        reindexItems();
        updateEmpty();
        refreshOptions();
    }

    function load() {
        return fetch("{{ route('admin.shipping-invoices.search-deals') }}?limit=100&term=")
            .then(function (r) { return r.json(); })
            .then(function (data) {
                catalog = data;
                while (dd.options.length > 1) { dd.remove(1); }
                data.forEach(function (x) {
                    var o = document.createElement('option');
                    o.value = x.id;
                    o.textContent = x.label + ' — ' + (x.currency || 'USD') + ' $' + Number(x.unit_price).toFixed(2);
                    dd.appendChild(o);
                });
                refreshOptions();
            });
    }

    dd.addEventListener('change', function () {
        if (!this.value) { return; }
        var d = catalog.find(function (x) { return String(x.id) === String(this.value); }, this);
        this.value = '';
        if (d) { addRow(d); }
    });

    document.getElementById('invoice-form').addEventListener('submit', function (e) {
        if (!rows.children.length) { e.preventDefault(); }
    });

    updateEmpty();
    load().catch(function () {});
}());
</script>
@endsection
