<?php

namespace App\Exports;

use App\Models\ActivityLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ActivityLogExport implements FromCollection, WithHeadings
{
    protected $startDate;
    protected $endDate;
    protected $search;

    public function __construct($startDate = null, $endDate = null, $search = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->search = $search;
    }

    public function collection(): Collection
    {
        return ActivityLog::with('user')
            ->when($this->startDate && $this->endDate, fn($q) =>
                $q->whereBetween('created_at', [
                    now()->parse($this->startDate)->startOfDay(),
                    now()->parse($this->endDate)->endOfDay()
                ])
            )
            ->when($this->search, fn($q) =>
                $q->where('module', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
            )
            ->latest()
            ->get()
            ->map(function ($log) {
                return [
                    'Tanggal'     => $log->created_at->format('d-m-Y H:i'),
                    'User'        => $log->user->name ?? '-',
                    'Aksi'        => $log->action,
                    'Modul'       => $log->module,
                    'Keterangan'  => $log->description,
                    'IP Address'  => $log->ip_address,
                ];
            });
    }

    public function headings(): array
    {
        return ['Tanggal', 'User', 'Aksi', 'Modul', 'Keterangan', 'IP Address'];
    }
}

