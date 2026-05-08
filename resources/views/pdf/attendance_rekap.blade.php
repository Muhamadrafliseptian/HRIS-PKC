<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid black;
            margin-bottom: 10px;
        }

        h3 {
            text-align: center;
            margin-bottom: 5px;
        }

        .sub-header {
            text-align: center;
            margin-bottom: 15px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-size: 10px;
        }

        th {
            background: #f0f0f0;
        }

        .employee-title {
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 12px;
        }

        .badge-red {
            color: red;
            font-weight: bold;
        }

        .badge-orange {
            color: orange;
            font-weight: bold;
        }

        .badge-green {
            color: green;
            font-weight: bold;
        }

        .badge-blue {
            color: blue;
            font-weight: bold;
        }

        .badge-cyan {
            color: #0891b2;
            font-weight: bold;
        }

        .badge-purple {
            color: purple;
            font-weight: bold;
        }

        .badge-default {
            color: #666;
            font-weight: bold;
        }

        .summary {
            margin-bottom: 20px;
            font-size: 11px;
        }
    </style>
</head>

<body>

    <table class="header-table">
        <tr>
            <td width="10%" style="text-align:center;">
                <img src="{{ public_path('images/logo.png') }}" width="70">
            </td>
            <td width="90%" style="text-align:center;">
                <div style="font-size:14px; font-weight:bold;">
                    PEMERINTAH PROVINSI DAERAH KHUSUS IBUKOTA JAKARTA
                </div>
                <div style="font-size:13px; font-weight:bold;">
                    BADAN KEPEGAWAIAN DAERAH
                </div>
                <div style="font-size:10px;">
                    Jl. Medan Merdeka Selatan No. 8-9, Jakarta Pusat 10110
                </div>
                <div style="font-size:10px;">
                    Telp: (021) 3823030 | Email: bkd@jakarta.go.id
                </div>
            </td>
        </tr>
    </table>

    <h3>LAPORAN LOG KEHADIRAN</h3>

    <div class="sub-header">
        Periode: {{ $start_date ?? '-' }} s/d {{ $end_date ?? '-' }}
    </div>

    @foreach($data as $employeeId => $items)

        <div class="employee-title">
            Nama: {{ $items->first()->employee_name ?? '-' }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>Hari</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Jam Kerja</th>

                    <th>Masuk</th>
                    <th>Cabang Masuk</th>

                    <th>Pulang</th>
                    <th>Cabang Pulang</th>

                    <th>Terlambat</th>
                    <th>Pulang Cepat</th>
                    <th>Total Kerja</th>
                </tr>
            </thead>

            <tbody>

                @php
                    $totalLate = 0;

                    $exceptionStatuses = [
                        'S',
                        'CT',
                        'I',
                        'IJIN1',
                        'IJIN2',
                        'DLAW',
                        'DLAK',
                        'DLP'
                    ];
                @endphp

                @foreach($items as $row)

                    @php

                        $status = $row->status ?? 'unknown';

                        $isOff = strtolower($status) === 'off';

                        $isException = in_array($status, $exceptionStatuses);

                        $checkIn = $row->check_in;
                        $checkOut = $row->check_out;

                        if ($checkIn && $checkOut && $checkIn == $checkOut) {
                            $status = 'no_checkout';
                            $checkOut = null;
                        }

                        if (!$checkIn && !$isException && !$isOff) {
                            $status = 'absent';
                        }

                        $statusLabel = match ($status) {

                            'present' => 'HADIR',
                            'late' => 'TELAT',
                            'partial' => 'SEBAGIAN',
                            'absent' => 'TIDAK HADIR',
                            'off' => 'LIBUR',
                            'no_checkout' => 'BELUM PULANG',

                            'S' => 'SAKIT',
                            'CT' => 'CUTI',
                            'I' => 'IZIN',
                            'IJIN1' => 'IJIN 1',
                            'IJIN2' => 'IJIN 2',
                            'DLAW' => 'DLAW',
                            'DLAK' => 'DLAK',
                            'DLP' => 'DLP',

                            default => 'UNKNOWN'
                        };

                        $statusClass = match ($status) {

                            'present' => 'badge-green',
                            'late' => 'badge-orange',
                            'partial' => 'badge-orange',
                            'absent' => 'badge-red',
                            'off' => 'badge-default',
                            'no_checkout' => 'badge-orange',

                            'S' => 'badge-blue',
                            'CT' => 'badge-cyan',
                            'I' => 'badge-purple',
                            'IJIN1' => 'badge-purple',
                            'IJIN2' => 'badge-purple',

                            'DLAW' => 'badge-orange',
                            'DLAK' => 'badge-orange',
                            'DLP' => 'badge-red',

                            default => ''
                        };

                        $lateMinutes = max(0, $row->late_minutes ?? 0);

                        if ($lateMinutes > 0) {
                            $totalLate += $lateMinutes;
                        }

                        $earlyOut = max(0, $row->early_out_minutes ?? 0);

                    @endphp

                    <tr>

                        <td>
                            {{ \Carbon\Carbon::parse($row->date)->locale('id')->translatedFormat('l') }}
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse($row->date)->format('Y-m-d') }}
                        </td>

                        <td class="{{ $statusClass }}">
                            {{ $statusLabel }}
                        </td>

                        <td>
                            {{ $isOff ? 'OFF' : ($row->jam_kerja ?? '-') }}
                        </td>

                        <td>

                            @if($isOff)
                                -
                            @elseif(!$checkIn)
                                -
                            @else
                                {{ \Carbon\Carbon::parse($checkIn)->format('H:i:s') }}
                            @endif

                        </td>

                        <td>
                            {{ $isOff ? '-' : ($row->in_branch_name ?? '-') }}
                        </td>

                        <td>

                            @if($isOff)
                                -
                            @elseif($status === 'no_checkout')
                                BELUM ABSEN
                            @elseif(!$checkOut)
                                -
                            @else
                                {{ \Carbon\Carbon::parse($checkOut)->format('H:i:s') }}
                            @endif

                        </td>

                        <td>
                            {{ $isOff ? '-' : ($row->out_branch_name ?? '-') }}
                        </td>

                        <td>
                            {{ $isOff ? '-' : $lateMinutes }}
                        </td>

                        <td>
                            {{ $isOff ? '-' : $earlyOut }}
                        </td>

                        <td>
                            {{ $isOff ? '-' : ($row->total_work_minutes ?? 0) }}
                        </td>

                    </tr>

                @endforeach

            </tbody>
        </table>

        <div class="summary">
            <b>Total Terlambat:</b> {{ $totalLate }} menit
        </div>

    @endforeach

</body>

</html>