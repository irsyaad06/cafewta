<div class="text-left w-full" style="margin-top: 20px;">
    @php
        $filters = $this->tableFilters ?? [];
        $dateFrom = data_get($filters, 'rentang_tanggal.date_from');
        $dateUntil = data_get($filters, 'rentang_tanggal.date_until');

        $strDateFrom = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->translatedFormat('d F Y') : now()->startOfMonth()->translatedFormat('d F Y');
        $strDateUntil = $dateUntil ? \Carbon\Carbon::parse($dateUntil)->translatedFormat('d F Y') : now()->endOfMonth()->translatedFormat('d F Y');
        
        $monthFrom = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->translatedFormat('F Y') : now()->translatedFormat('F Y');
        $monthUntil = $dateUntil ? \Carbon\Carbon::parse($dateUntil)->translatedFormat('F Y') : now()->translatedFormat('F Y');

        if ($monthFrom === $monthUntil) {
            $periodText = "bulan " . $monthFrom;
        } else {
            $periodText = "periode " . $monthFrom . ' - ' . $monthUntil;
        }
    @endphp
    <p class="text-sm text-gray-500 mb-4 dark:text-gray-400">
        Data yang ditampilkan adalah data pada <strong>{{ $periodText }}</strong>. <br>
        <span class="text-xs">({{ $strDateFrom }} s/d {{ $strDateUntil }})</span>
    </p>
    <x-filament-actions::actions :actions="[$this->getAction('exportExcel'), $this->getAction('exportPdf')]" />
</div>
