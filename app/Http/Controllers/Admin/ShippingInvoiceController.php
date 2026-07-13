<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\ShippingInvoice;
use App\Models\ShippingInvoiceItem;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ShippingInvoiceController extends Controller
{
    /**
     * List all saved shipping invoices
     */
    public function index(Request $request)
    {
        $query = ShippingInvoice::withCount('items')
            ->orderByDesc('invoice_date')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('invoice_number', 'like', "%{$s}%")
                  ->orWhere('notes', 'like', "%{$s}%");
        }

        $invoices = $query->paginate(20)->withQueryString();

        return view('admin.shipping-invoices.index', compact('invoices'));
    }

    /**
     * Show the create form
     */
    public function create()
    {
        return view('admin.shipping-invoices.create');
    }

    /**
     * AJAX: search sold deals for the deal selector
     */
    public function searchDeals(Request $request)
    {
        $term   = trim($request->get('term', ''));
        $except = array_filter((array) $request->get('except', []));
        $limit  = min(100, max(1, (int) $request->get('limit', 20)));

        $query = Deal::with(['watchBrandDetail', 'dealBuyerDetail'])
            ->whereIn('deal_status',['3','4'])
            ->where('status', 1);

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('model_number', 'like', "%{$term}%")
                  ->orWhere('serial_number', 'like', "%{$term}%")
                  ->orWhereHas('watchBrandDetail', fn($b) => $b->where('name', 'like', "%{$term}%"));
            });
        }

        if (!empty($except)) {
            $query->whereNotIn('id', $except);
        }

        $deals = $query->orderBy('updated_at', 'desc')->limit($limit)->get();

        return response()->json($deals->map(function ($deal) {
            $brand = $deal->watchBrandDetail->name ?? '';
            $label = implode(' | ', array_filter([
                $brand,
                $deal->model_number,
                $deal->serial_number,
            ]));

            $description = implode("\n", array_filter([
                $brand           ? 'Brand: '     . $brand                        : null,
                $deal->model_number  ? 'Model: '     . $deal->model_number       : null,
                $deal->material_watch ? 'Reference: ' . $deal->material_watch    : null,
                $deal->serial_number  ? 'Serial: '    . $deal->serial_number     : null,
                $deal->condition      ? 'Condition: ' . $deal->condition         : null,
                $deal->year           ? 'Year: '      . $deal->year              : null,
                isset($deal->full_set) ? 'Full Set: '  . ($deal->full_set == '1' ? 'Yes' : 'No') : null,
                $deal->dial ? 'Dial: ' . $deal->dial    : null,
            ]));

            $price    = (float) (optional($deal->dealBuyerDetail)->buyer_sale_price ?? $deal->sale_price ?? 0);
            $gstType  = optional($deal->dealBuyerDetail)->gst_type;
            $currency = strtoupper(optional($deal->dealBuyerDetail)->buyer_currency ?? 'usd');

            return [
                'id'          => $deal->id,
                'label'       => $label,
                'description' => $description,
                'unit_price'  => $price,
                'gst_type'    => $gstType,
                'currency'    => $currency,
            ];
        }));
    }

    /**
     * Save a new shipping invoice
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_date'         => 'required|date',
            'items'                => 'required|array|min:1',
            'items.*.deal_id'      => 'required|integer|exists:deals,id',
            'items.*.description'  => 'required|string',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.gst_type'     => 'nullable|string',
            'notes'                => 'nullable|string|max:5000',
        ]);

        $dealIds   = collect($request->items)->pluck('deal_id')->unique()->values()->all();
        $dealsById = Deal::with('dealBuyerDetail')->whereIn('id', $dealIds)->get()->keyBy('id');

        foreach ($request->items as $item) {
            if (! $dealsById->get($item['deal_id'])) {
                return back()->withInput()->with('alert-error', 'One or more deals are no longer available.');
            }
        }

        $lineCurrencies = collect($request->items)->map(function (array $row) use ($dealsById) {
            $d = $dealsById->get($row['deal_id']);

            return strtoupper(optional($d->dealBuyerDetail)->buyer_currency ?? 'USD');
        })->unique()->values();
        $totalAmount = $lineCurrencies->count() > 1
            ? null
            : round((float) collect($request->items)->sum('unit_price'), 2);

        $invoice = null;
        DB::transaction(function () use ($request, $totalAmount, $dealsById, &$invoice) {
            $invoiceNumber = ShippingInvoice::generateInvoiceNumber();

            $invoice = ShippingInvoice::create([
                'invoice_number' => $invoiceNumber,
                'invoice_date'   => $request->invoice_date,
                'total_amount'   => $totalAmount,
                'notes'          => $request->input('notes'),
            ]);

            foreach ($request->items as $item) {
                $deal = $dealsById->get($item['deal_id']);
                $ccy   = strtoupper(optional($deal->dealBuyerDetail)->buyer_currency ?? 'USD');
                ShippingInvoiceItem::create([
                    'shipping_invoice_id' => $invoice->id,
                    'deal_id'             => $item['deal_id'],
                    'currency'            => $ccy,
                    'description'         => $item['description'],
                    'unit_price'          => $item['unit_price'],
                    'gst_type'            => $item['gst_type'] ?? null,
                    'amount'              => $item['unit_price'],
                ]);
            }
        });

        if ($invoice === null) {
            return back()->withInput()->with('alert-error', 'Could not create the invoice. Please try again.');
        }

        $invoice->load(['items.deal.watchBrandDetail', 'items.deal.dealBuyerDetail']);
        $pdfService = app(InvoicePdfService::class);
        $pdfError   = null;
        try {
            $pdfService->storeSavedShippingInvoicePdf($invoice);
        } catch (\Throwable $e) {
            report($e);
            $pdfError = 'Invoice saved, but the PDF file could not be written. Ensure the public/shipping-invoices directory is writable.';
        }

        $redirect = redirect()
            ->route('admin.shipping-invoices.show', $invoice->id)
            ->with('alert-success', 'Shipping invoice saved. PDF is stored in public/shipping-invoices.');

        if ($pdfError !== null) {
            $redirect->with('alert-error', $pdfError);
        }

        return $redirect;
    }

    /**
     * View a saved shipping invoice
     */
    public function show($id)
    {
        $invoice = ShippingInvoice::with(['items.deal.watchBrandDetail', 'items.deal.dealBuyerDetail'])->findOrFail($id);

        return view('admin.shipping-invoices.show', compact('invoice'));
    }

    /**
     * Delete a shipping invoice
     */
    public function destroy($id)
    {
        $invoice = ShippingInvoice::findOrFail($id);
        $this->deleteSavedShippingInvoicePdfFile($invoice);
        $invoice->delete();

        return redirect()
            ->route('admin.shipping-invoices.index')
            ->with('alert-success', 'Shipping invoice deleted.');
    }

    /**
     * Download PDF for a saved shipping invoice
     */
    public function download($id)
    {
        $invoice = ShippingInvoice::with(['items.deal.watchBrandDetail', 'items.deal.dealBuyerDetail'])->findOrFail($id);

        $absolute = $this->savedShippingInvoicePdfAbsolutePath($invoice);

        if (! $absolute) {
            try {
                app(InvoicePdfService::class)->storeSavedShippingInvoicePdf($invoice);
                $invoice->refresh();
                $absolute = $this->savedShippingInvoicePdfAbsolutePath($invoice);
            } catch (\Throwable $e) {
                report($e);
                return redirect()
                    ->back()
                    ->with('alert-error', 'Could not read or build the PDF file. Check that public/shipping-invoices is writable.');
            }
        }

        if (! $absolute) {
            return redirect()
                ->back()
                ->with('alert-error', 'PDF file is still missing. Check permissions on public/shipping-invoices.');
        }

        $downloadName = 'shipping_invoice_' . str_replace(['/', '\\'], '', $invoice->invoice_number) . '.pdf';

        return response()->download($absolute, $downloadName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Prefers public/{pdf_path}; falls back to legacy storage/app/{pdf_path}.
     */
    protected function savedShippingInvoicePdfAbsolutePath(ShippingInvoice $invoice): ?string
    {
        if (! $invoice->pdf_path) {
            return null;
        }
        $inPublic = public_path($invoice->pdf_path);
        if (is_file($inPublic)) {
            return $inPublic;
        }
        $legacy = storage_path('app/' . $invoice->pdf_path);
        if (is_file($legacy)) {
            return $legacy;
        }

        return null;
    }

    protected function deleteSavedShippingInvoicePdfFile(ShippingInvoice $invoice): void
    {
        if (! $invoice->pdf_path) {
            return;
        }
        $inPublic = public_path($invoice->pdf_path);
        if (is_file($inPublic)) {
            File::delete($inPublic);
        }
        $legacy = storage_path('app/' . $invoice->pdf_path);
        if (is_file($legacy)) {
            File::delete($legacy);
        }
    }
}