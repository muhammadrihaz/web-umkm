<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SalesReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, SalesReportService $service): View
    {
        [$start, $end] = $this->dates($request);

        return view('admin.reports.index', ['summary' => $service->summary($start, $end), 'orders' => $service->orders($start, $end)->paginate(30)->withQueryString(), 'start' => $start, 'end' => $end]);
    }

    public function csv(Request $request, SalesReportService $service): StreamedResponse
    {
        [$start, $end] = $this->dates($request);

        return response()->streamDownload(function () use ($service, $start, $end): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Nomor Pesanan', 'Tanggal UTC', 'Pelanggan', 'Status', 'Metode', 'Subtotal', 'Ongkir', 'Total', 'Dibayar UTC']);
            $service->orders($start, $end)->chunk(500, function ($orders) use ($output): void {
                foreach ($orders as $order) {
                    fputcsv($output, array_map($this->escapeCsv(...), [$order->order_number, $order->created_at?->toIso8601String(), $order->user->name, $order->order_status->value, $order->payment_method, $order->subtotal, $order->delivery_fee, $order->total, $order->paid_at?->toIso8601String()]));
                }
            });
            fclose($output);
        }, "laporan-{$start}-{$end}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{string, string} */
    private function dates(Request $request): array
    {
        return [$request->string('start', Carbon::now(config('pangkonan.display_timezone'))->startOfMonth()->toDateString())->toString(), $request->string('end', Carbon::now(config('pangkonan.display_timezone'))->toDateString())->toString()];
    }

    private function escapeCsv(mixed $value): string
    {
        $text = (string) $value;

        return preg_match('/^[=+\-@]/', $text) ? "'{$text}" : $text;
    }
}
