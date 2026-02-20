<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class OrderInvoiceController extends Controller
{
    use AuthorizesRequests;

    protected OrderService $service;

    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

    public function show(Request $request, int $order)
    {
        $this->authorize('order.view');

        $orderModel = $this->service->find($order);
        $print = $request->boolean('print');

        return view('admin.orders.invoice', [
            'order' => $orderModel,
            'print' => $print,
        ]);
    }

    public function pdf(int $order)
    {
        $this->authorize('order.view');

        $orderModel = $this->service->find($order);

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return response()
                ->view('admin.orders.invoice', [
                    'order' => $orderModel,
                    'print' => true,
                ], 200)
                ->header('X-Pdf-Generator', 'missing');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.orders.invoice-pdf', [
            'order' => $orderModel,
        ]);

        return $pdf->download('Invoice-' . $orderModel->order_no . '.pdf');
    }
}
