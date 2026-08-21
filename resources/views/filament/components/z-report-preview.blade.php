@php
    $escPosService = new \App\Services\EscPosService();
    $rawText = $text ?? $escPosService->generateZReportText($record);
@endphp

<div class="flex flex-col items-center justify-center space-y-4 py-1">
    <!-- Header Controls -->
    <div class="w-full max-w-[320px] flex items-center justify-between">
        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Struk Thermal 58mm/80mm</span>
        <button 
            type="button"
            onclick="printThermalZReport()" 
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-lg shadow-xs transition"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak Struk</span>
        </button>
    </div>

    <!-- Clean Vertical Thermal Receipt Slip -->
    <div class="w-full max-w-[320px] bg-white text-black p-4 rounded shadow-sm border border-gray-200 overflow-x-auto">
<pre id="thermal-z-report-paper" style="font-family: 'Courier New', Courier, monospace; white-space: pre; font-size: 12px; line-height: 1.35; margin: 0; padding: 0; color: #000; text-align: left;">{{ $rawText }}</pre>
    </div>
</div>

<script>
function printThermalZReport() {
    const printContent = document.getElementById('thermal-z-report-paper').innerText;
    const printWindow = window.open('', '_blank', 'width=400,height=600');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Cetak Z-Report Shift</title>
            <style>
                @page { margin: 0; size: auto; }
                body {
                    font-family: 'Courier New', Courier, monospace;
                    font-size: 12px;
                    line-height: 1.3;
                    white-space: pre;
                    margin: 10px;
                    color: black;
                    background: white;
                }
            </style>
        </head>
        <body>${printContent}</body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
}
</script>
