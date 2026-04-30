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

    <!-- HEADER -->
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
    <h3>LAPORAN REKAPITULASI KEHADIRAN</h3>

    <!-- PERIODE -->
    <div class="sub-header">
        Periode: {{ $start_date ?? '-' }} s/d {{ $end_date ?? '-' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Total Hari</th>
                <th>Hadir</th>
                <th>Terlambat</th>
                <th>Pulang Cepat</th>

                <th>DLAW</th>
                <th>DLAK</th>
                <th>DLP</th>

                <th>IJIN1</th>
                <th>IJIN2</th>
                <th>I</th>

                <th>Sakit</th>
                <th>Cuti</th>
            </tr>
        </thead>

        <tbody>
            @foreach($data as $row)
                <tr>
                    <td style="text-align:left;">
                        {{ $row['name'] }}
                    </td>

                    <td>{{ $row['total_hari'] }}</td>
                    <td>{{ $row['hadir'] }}</td>

                    <td class="badge-orange">{{ $row['terlambat'] }}</td>
                    <td>{{ $row['pulang_cepat'] }}</td>

                    <td>{{ $row['DLAW'] }}</td>
                    <td>{{ $row['DLAK'] }}</td>
                    <td>{{ $row['DLP'] }}</td>

                    <td>{{ $row['IJIN1'] }}</td>
                    <td>{{ $row['IJIN2'] }}</td>
                    <td>{{ $row['I'] }}</td>

                    <td>{{ $row['sakit'] }}</td>
                    <td>{{ $row['cuti'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>