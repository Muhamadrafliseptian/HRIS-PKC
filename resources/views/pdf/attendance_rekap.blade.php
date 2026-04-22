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

        .header-table td {
            border: none;
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

        .employee-title {
            margin-top: 15px;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        th {
            background: #f0f0f0;
        }

        .summary {
            margin-bottom: 10px;
            font-size: 10px;
        }

        .late {
            color: red;
            font-weight: bold;
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

    <!-- JUDUL -->
    <h3>LAPORAN LOG KEHADIRAN</h3>

    <!-- PERIODE -->
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
                    <th>Jam Kerja</th>
                    <th>Masuk</th>
                    <th>Pulang</th>
                    <th>Terlambat (menit)</th>
                    <th>Pulang Awal (menit)</th>
                    <th>Lembur (menit)</th>
                    <th>Total Kerja (menit)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalLate = 0;
                @endphp

                @foreach($items as $row)
                    @php
                        $lateDisplay = '-';

                        if (!$row->is_holiday && !is_null($row->late_minutes)) {
                            if ($row->late_minutes < -100) {
                                $lateDisplay = 'TIDAK ABSEN';
                            } else {
                                $lateValue = abs($row->late_minutes);

                                // hanya hitung kalau benar-benar telat
                                if ($lateValue > 0) {
                                    $totalLate += $lateValue;
                                }

                                $lateDisplay = $lateValue;
                            }
                        }
                    @endphp

                    <tr>
                        <!-- HARI -->
                        <td>
                            {{ \Carbon\Carbon::parse($row->date)->locale('id')->translatedFormat('l') }}
                        </td>

                        <!-- TANGGAL -->
                        <td>{{ \Carbon\Carbon::parse($row->date)->format('Y-m-d') }}</td>

                        <!-- JAM KERJA -->
                        <td>{{ $row->jam_kerja ?? '-' }}</td>

                        <!-- MASUK -->
                        <td>
                            @if($row->is_holiday)
                                LIBUR
                            @elseif(!$row->check_in)
                                TIDAK ABSEN
                            @else
                                {{ \Carbon\Carbon::parse($row->check_in)->format('H:i:s') }}
                            @endif
                        </td>

                        <!-- PULANG -->
                        <td>
                            @if($row->is_holiday)
                                LIBUR
                            @elseif(!$row->check_out)
                                TIDAK ABSEN
                            @else
                                {{ \Carbon\Carbon::parse($row->check_out)->format('H:i:s') }}
                            @endif
                        </td>

                        <!-- TERLAMBAT -->
                        <td
                            class="{{ ($lateDisplay !== '-' && $lateDisplay !== 'TIDAK ABSEN' && $lateDisplay > 0) ? 'late' : '' }}">
                            {{ $lateDisplay }}
                        </td>

                        <!-- PULANG AWAL -->
                        <td>
                            @if($row->is_holiday)
                                -
                            @elseif(is_null($row->early_out_minutes))
                                -
                            @elseif($row->early_out_minutes < -100)
                                TIDAK ABSEN
                            @else
                                {{ abs($row->early_out_minutes) }}
                            @endif
                        </td>

                        <!-- LEMBUR -->
                        <td>-</td>

                        <!-- TOTAL KERJA -->
                        <td>
                            {{ $row->total_work_minutes ?? 0 }}
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