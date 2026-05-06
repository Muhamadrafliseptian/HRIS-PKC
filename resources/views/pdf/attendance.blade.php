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
    </style>
</head>

<body>

    <!-- HEADER INSTANSI -->
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

    @foreach($logs as $userId => $items)

        <div class="employee-title">
            Nama:
            {{ optional($items->first()->dtbiouser)->name ?? 'User ' . $userId }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Device</th>
                    <th>Unit Kerja</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $log)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($log->scan_time)->format('Y-m-d') }}</td>
                        <td>{{ \Carbon\Carbon::parse($log->scan_time)->format('H:i:s') }}</td>

                        <td>
                            {{
                    optional($log->dtbiouser)->biometricUser
                    ? $log->dtbiouser->biometricUser
                        ->pluck('device.name')
                        ->filter()
                        ->join(', ')
                    : '-'
                                    }}
                        </td>

                        <td>
                            {{ optional($log->dtbiouser->dtbranch)->name ?? '-' }}
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            Total Scan: {{ count($items) }}
        </div>

    @endforeach

</body>

</html>