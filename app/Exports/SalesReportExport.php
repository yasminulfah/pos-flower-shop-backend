<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $source;

    public function __construct($source)
    {
        $this->source = $source;
    }

    public function collection()
    {
        ini_set('memory_limit', '512M');
        
        return Order::with(['orderItems.productVariant.product'])
            ->where('source', $this->source)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID Order',
            'Tanggal',
            'Nama Customer',
            'Total Bayar',
            'Status',
            'Items Produk (Qty)'
        ];
    }

    public function map($order): array
    {
        $items = $order->orderItems->map(function($item) {
        if (!$item->productVariant) return 'Unknown Variant';
        
        $productName = $item->productVariant->product->product_name ?? 'Unknown Product';
        $variantName = $item->productVariant->variant_name ?? '';
        
        return $productName . ' (' . $variantName . ') x ' . $item->quantity;
            })->implode(', ');

        return [
            $order->id,
            $order->created_at->format('Y-m-d H:i:s'),
            $order->customer_name ?? 'Guest',
            $order->grand_total,
            ucfirst($order->status),
            $items
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}