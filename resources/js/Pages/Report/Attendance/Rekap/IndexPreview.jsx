import React from "react";
import { Head, usePage } from "@inertiajs/react";
import { Card, Table, Breadcrumb, Tag } from "antd";
import Main from "../../../../layout/Main";
import "../../../../../css/main.css"

function IndexPreview() {
  const { data, start_date, end_date } = usePage().props;

  // =========================
  // STATUS HELPERS
  // =========================
  const getStatusTag = (val) => {
    switch (val) {
      case "present":
        return <Tag color="green">HADIR</Tag>;
      case "late":
        return <Tag color="orange">TELAT</Tag>;
      case "partial":
        return <Tag color="gold">SEBAGIAN</Tag>;
      case "absent":
        return <Tag color="red">TIDAK HADIR</Tag>;
      default:
        return <Tag>{val}</Tag>;
    }
  };

  const getExceptionTag = (label, val, color = "blue") => {
    if (!val) return null;
    return (
      <Tag color={color}>
        {label}: {val}
      </Tag>
    );
  };

  // =========================
  // TABLE COLUMN
  // =========================
  const columns = [
    {
      title: "Nama",
      dataIndex: "name",
    },
    {
      title: "Total Hari",
      dataIndex: "total_hari",
    },
    {
      title: "Hadir",
      dataIndex: "hadir",
    },
    {
      title: "Terlambat",
      dataIndex: "terlambat",
      render: (val) => <Tag color="orange">{val}</Tag>,
    },
    {
      title: "Pulang Cepat",
      dataIndex: "pulang_cepat",
    },

    // =====================
    // DINAS LUAR (FLAT)
    // =====================
    {
      title: "DLAW",
      dataIndex: "DLAW",
      render: (v) => v || 0,
    },
    {
      title: "DLAK",
      dataIndex: "DLAK",
      render: (v) => v || 0,
    },
    {
      title: "DLP",
      dataIndex: "DLP",
      render: (v) => v || 0,
    },

    // =====================
    // IZIN (FLAT)
    // =====================
    {
      title: "IJIN1",
      dataIndex: "IJIN1",
      render: (v) => v || 0,
    },
    {
      title: "IJIN2",
      dataIndex: "IJIN2",
      render: (v) => v || 0,
    },
    {
      title: "I",
      dataIndex: "I",
      render: (v) => v || 0,
    },

    {
      title: "Izin Total",
      dataIndex: "izin_total",
      render: (v) => <Tag color="purple">{v || 0}</Tag>,
    },

    // =====================
    // LAINNYA
    // =====================
    {
      title: "Sakit",
      dataIndex: "sakit",
      render: (val) => <Tag color="red">{val || 0}</Tag>,
    },
    {
      title: "Cuti",
      dataIndex: "cuti",
      render: (val) => <Tag color="cyan">{val || 0}</Tag>,
    },
  ];

  return (
    <div>
      <Head title="Preview Rekap Kehadiran" />

      <Breadcrumb
        items={[
          { title: "Report" },
          { title: "Attendance" },
          { title: "Rekap Preview" },
        ]}
      />

      <Card
        title={`Rekap Kehadiran (${start_date} - ${end_date})`}
        style={{ marginTop: 12 }}
      >
        <Table
          dataSource={data}
          columns={columns}
          rowKey={(row) => row.employee_id}
          pagination={{ pageSize: 20 }}
          scroll={{ x: true }}
          className="custom-table"
        />
      </Card>
    </div>
  );
}

IndexPreview.layout = (page) => <Main>{page}</Main>;

export default IndexPreview;