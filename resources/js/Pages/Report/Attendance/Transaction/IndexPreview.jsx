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

  const exceptionStatuses = [
    "S",
    "CT",
    "I",
    "IJIN1",
    "IJIN2",
    "DLAW",
    "DLAK",
    "DLP",
  ];

  // 🔥 NORMALIZER
  const normalize = (item) => {

    let checkIn = item.check_in;
    let checkOut = item.check_out;
    let status = item.status;
    let early = item.early_out_minutes;

    if (checkIn && checkOut && checkIn === checkOut) {

      checkOut = null;

      status = "no_checkout";

      early = 0;
    }

    if (early < 0) {
      early = 0;
    }

    if (!checkIn && !exceptionStatuses.includes(status)) {
      status = "absent";
    }

    const isOff =
      !item.jam_kerja ||
      item.jam_kerja === "-" ||
      item.jam_kerja.toLowerCase().includes("off");

    if (isOff && !exceptionStatuses.includes(status)) {

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

    const flat = Object.entries(logs || {}).flatMap(
      ([employeeId, items]) =>
        (items || []).map((item) =>
          normalize({
            ...item,
            employee_id: employeeId,
          })
        )
    );

    setData(flat);

  }, [logs]);

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
        return <Tag color="default">LIBUR</Tag>;

      case "no_checkout":
        return <Tag color="volcano">BELUM PULANG</Tag>;

      case "S":
        return <Tag color="blue">SAKIT</Tag>;

      case "CT":
        return <Tag color="cyan">CUTI</Tag>;

      case "I":
        return <Tag color="purple">IZIN</Tag>;

      case "IJIN1":
        return <Tag color="magenta">IJIN 1</Tag>;

      case "IJIN2":
        return <Tag color="geekblue">IJIN 2</Tag>;

      case "DLAW":
        return <Tag color="gold">DLAW</Tag>;

      case "DLAK":
        return <Tag color="orange">DLAK</Tag>;

      case "DLP":
        return <Tag color="red">DLP</Tag>;

      default:
        return <Tag>UNKNOWN</Tag>;
    }
  };

  const formatTime = (val) =>
    val
      ? dayjs(val)
          .tz("Asia/Jakarta")
          .format("HH:mm:ss")
      : "-";

  const formatDate = (val) =>
    val
      ? dayjs(val)
          .tz("Asia/Jakarta")
          .format("DD MMM YYYY")
      : "-";

  const isOffRow = (row) => row.status === "off";

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
      title: "Status",
      dataIndex: "status",
      render: (val) => getStatusTag(val),
    },
    {
      title: "Jam Kerja",
      dataIndex: "jam_kerja",
      render: (val, row) =>
        isOffRow(row)
          ? <Tag color="default">OFF</Tag>
          : val,
    },
    {
      title: "Check In",
      dataIndex: "check_in",
      render: (val, row) =>
        isOffRow(row)
          ? "-"
          : formatTime(val),
    },
    {
      title: "Check Out",
      dataIndex: "check_out",
      render: (val, row) => {

        if (isOffRow(row)) {
          return "-";
        }

        if (row.status === "no_checkout") {
          return <Tag color="orange">Belum Absen</Tag>;
        }

        return formatTime(val);
      },
    },
    {
      title: "Cabang Masuk",
      dataIndex: "in_branch_name",
      render: (val, row) =>
        isOffRow(row)
          ? "-"
          : (val || "-"),
    },
    {
      title: "Device Masuk",
      dataIndex: "in_device",
      render: (val, row) =>
        isOffRow(row)
          ? "-"
          : (val || "-"),
    },
    {
      title: "Cabang Pulang",
      dataIndex: "out_branch_name",
      render: (val, row) =>
        isOffRow(row)
          ? "-"
          : (val || "-"),
    },
    {
      title: "Device Pulang",
      dataIndex: "out_device",
      render: (val, row) =>
        isOffRow(row)
          ? "-"
          : (val || "-"),
    },
    {
      title: "Telat (menit)",
      dataIndex: "late_minutes",
      render: (val, row) =>
        isOffRow(row)
          ? "-"
          : val,
    },
    {
      title: "Pulang Cepat",
      dataIndex: "early_out_minutes",
      render: (val, row) =>
        isOffRow(row)
          ? "-"
          : val,
    },
    {
      title: "Total Kerja",
      dataIndex: "total_work_minutes",
      render: (val, row) =>
        isOffRow(row)
          ? "-"
          : val,
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
          rowKey={(row) =>
            `${row.employee_id}_${row.date}`
          }
          pagination={{
            pageSize: 20,
          }}
          scroll={{ x: true }}
          className="custom-table"
          rowClassName={(record) =>
            record.status === "off"
              ? "row-off"
              : ""
          }
        />

      </Card>

    </div>
  );
}

IndexPreview.layout = (page) => <Main>{page}</Main>;

export default IndexPreview;