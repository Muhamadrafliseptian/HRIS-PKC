import React, { useEffect, useState } from "react";
import { Head, usePage } from "@inertiajs/react";
import { Card, Table, Breadcrumb, Tag } from "antd";
import Main from "../../../../layout/Main";
import "../../../../../css/main.css";

import dayjs from "dayjs";
import utc from "dayjs/plugin/utc";
import timezone from "dayjs/plugin/timezone";

dayjs.extend(utc);
dayjs.extend(timezone);

function IndexPreview() {
  const { data: logs, start_date, end_date } = usePage().props;

  const [data, setData] = useState([]);

  // 🔥 NORMALIZER (INI KUNCI UTAMA)
  const normalize = (item) => {
    let checkIn = item.check_in;
    let checkOut = item.check_out;
    let status = item.status;
    let early = item.early_out_minutes;

    // 🔥 1. check_in == check_out → belum pulang
    if (checkIn && checkOut && checkIn === checkOut) {
      checkOut = null;
      status = "no_checkout";
      early = 0;
    }

    // 🔥 2. early negatif → reset
    if (early < 0) {
      early = 0;
    }

    // 🔥 3. tidak ada check_in → absent
    if (!checkIn) {
      status = "absent";
    }

    // 🔥 detect OFF dari jam kerja
    const isOff =
      !item.jam_kerja ||
      item.jam_kerja === "-" ||
      item.jam_kerja.toLowerCase().includes("off");

    // 🔥 kalau OFF → override semua
    if (isOff) {
      return {
        ...item,
        status: "off",
        check_in: null,
        check_out: null,
        late_minutes: 0,
        early_out_minutes: 0,
        total_work_minutes: 0,
      };
    }

    return {
      ...item,
      check_in: checkIn,
      check_out: checkOut,
      early_out_minutes: early,
      status,
    };
  };


  useEffect(() => {
    if (!logs) return;

    const flat = Object.entries(logs || {}).flatMap(([employeeId, items]) =>
      (items || []).map((item) =>
        normalize({
          ...item,
          employee_id: employeeId,
        })
      )
    );

    setData(flat);
  }, [logs]);

  // 🔥 STATUS TAG
  const getStatusTag = (status) => {
    switch (status) {
      case "present":
        return <Tag color="green">HADIR</Tag>;
      case "late":
        return <Tag color="orange">TELAT</Tag>;
      case "partial":
        return <Tag color="gold">SEBAGIAN</Tag>;
      case "absent":
        return <Tag color="red">TIDAK HADIR</Tag>;
      case "off":
        return <Tag color="red">LIBUR</Tag>;
      case "no_checkout":
        return <Tag color="volcano">BELUM PULANG</Tag>;
      default:
        return <Tag>UNKNOWN</Tag>;
    }
  };

  // 🔥 HELPER FORMAT
  const formatTime = (val) =>
    val
      ? dayjs(val).tz("Asia/Jakarta").format("HH:mm:ss")
      : "-";

  const formatDate = (val) =>
    val
      ? dayjs(val).tz("Asia/Jakarta").format("DD MMM YYYY")
      : "-";

  const isOffRow = (row) => row.status === "off";

  // 🔥 TABLE COLUMN
  const columns = [
    {
      title: "Nama",
      dataIndex: "employee_name",
    },
    {
      title: "Tanggal",
      dataIndex: "date",
      render: (val) => formatDate(val),
    },
    {
      title: "Jam Kerja",
      dataIndex: "jam_kerja",
      render: (val, row) =>
        isOffRow(row) ? <Tag color="red">OFF</Tag> : val,
    },
    {
      title: "Check In",
      dataIndex: "check_in",
      render: (val, row) =>
        isOffRow(row) ? "-" : formatTime(val),
    },
    {
      title: "Check Out",
      dataIndex: "check_out",
      render: (val, row) => {
        if (isOffRow(row)) return "-";
        if (row.status === "no_checkout")
          return <Tag color="orange">Belum Absen</Tag>;
        return formatTime(val);
      },
    },
    {
      title: "Telat (menit)",
      dataIndex: "late_minutes",
      render: (val, row) =>
        isOffRow(row) ? "-" : val,
    },
    {
      title: "Pulang Cepat",
      dataIndex: "early_out_minutes",
      render: (val, row) =>
        isOffRow(row) ? "-" : val,
    },
    {
      title: "Total Kerja",
      dataIndex: "total_work_minutes",
      render: (val, row) =>
        isOffRow(row) ? "-" : val,
    },
  ];

  return (
    <div>
      <Head title="Preview Kehadiran" />

      <Breadcrumb
        items={[
          { title: "Report" },
          { title: "Attendance" },
          { title: "Kehadiran Preview" },
        ]}
      />

      <Card
        title={`Preview Kehadiran (${start_date} - ${end_date})`}
        style={{ marginTop: 12 }}
      >
        <Table
          dataSource={data}
          columns={columns}
          rowKey={(row) => `${row.employee_id}_${row.date}`}
          pagination={{ pageSize: 20 }}
          scroll={{ x: true }}
          className="custom-table"
          rowClassName={(record) =>
            record.status === "off" ? "row-off" : ""
          }
        />
      </Card>
    </div>
  );
}

IndexPreview.layout = (page) => <Main>{page}</Main>;

export default IndexPreview;