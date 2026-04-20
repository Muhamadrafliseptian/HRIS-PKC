import React, { useEffect, useState } from "react";
import { Table, Button, Card, Space, message, Form, Row, Col, Tag, Tooltip, Modal, Select, DatePicker, Input } from "antd";
import { checkPullStatus, pullAttendances, readAttendances, storeAttendanceException } from "../../services/api/attendances/attendances";
import Main from "../../layout/Main";
import "../../../css/main.css";
import { LoadingComponent } from '../../components/Loading';
import { Head, usePage } from "@inertiajs/react";
import { FormSelect } from "../../components/Form";
import dayjs from "dayjs";
import { PrimaryButton } from '../../components/Button'
function Index() {
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [daysInMonth, setDaysInMonth] = useState(0);
  const [monthLabel, setMonthLabel] = useState("");
  const pages = usePage().props;
  const [utils, setUtils] = useState({ branchs: [], statuses: [], periods: "", shifts: [] });
  const [modalOpen, setModalOpen] = useState(false);
  const [selectedCell, setSelectedCell] = useState(null);
  const [form] = Form.useForm();

  const [filters, setFilters] = useState({
    branch: "",
    periode: dayjs().format("YYYY-MM"),
    shift: "",
  })
  const handleChangeFilter = (field, value) => {
    try {
      setFilters((prev) => ({
        ...prev,
        [field]: value == undefined ? "" : value,
      }));
    } catch (e) {
      setLoading(false);
    }
  };
  const handlePull = async () => {
    try {
      let formData = new FormData()
      formData.append("branch", filters.branch)
      await pullAttendances(formData);
      startPolling();
    } catch (err) {
      message.error("Sync gagal ❌");
    }
  };

  const startPolling = () => {
    const interval = setInterval(async () => {
      try {
        setLoading(true);
        const res = await checkPullStatus();
        const status = res.data.params.status;
        if (status === "done") {
          clearInterval(interval);
          setLoading(false);
          message.success("Sync berhasil ✅");
          window.location.reload()
        } else if (status === "failed") {
          clearInterval(interval);
          setLoading(false);
          message.error("Sync gagal ❌");
        }
      } catch (err) {
        clearInterval(interval);
        setLoading(false);
        message.error("Gagal cek status");
      }
    }, 3000);
  };

  useEffect(() => {
    handleRead();
    setUtils({
      branchs: pages?.branchs,
      statuses: pages?.status,
      shifts: pages?.shifts,
      periods: pages?.periods
    });
  }, [filters.branch, filters.periode]);

  const handleRead = async () => {
    try {
      let formData = new FormData()
      formData.append("branch", filters.branch)
      formData.append("periode", filters.periode)
      setLoading(true);
      const res = await readAttendances(formData);
      setLoading(false);
      const params = res.data.params;
      setData(params.data ?? []);
      setDaysInMonth(params.days_in_month ?? 0);
      setMonthLabel(params.month ?? "");
    } catch (err) {
      setLoading(false);
      message.error("Load data gagal ❌");
    }
  };

  const renderCell = (dayData) => {
    if (!dayData || dayData.length === 0) {
      return <span style={{ color: "#ccc" }}>-</span>;
    }

    return (
      <div style={{ display: "flex", flexDirection: "column", gap: 2 }}>
        {dayData.map((item, i) => {
          if (item.type === "exception") {
            return (
              <div
                key={i}
                style={{
                  background: "#e6f4ff",
                  fontSize: 10,
                  padding: 2,
                  borderRadius: 4,
                  textAlign: "center"
                }}
              >
                {item.status}
              </div>
            );
          }
          let bgColor = "#f6ffed";
          if (item.is_late && item.is_early_out) bgColor = "#fff1f0";
          else if (item.is_late || item.is_early_out) bgColor = "#fffbe6";
          return (
            <div
              key={i}
              style={{
                background: bgColor,
                borderRadius: 4,
                padding: 2,
                fontSize: 10,
                textAlign: "center",
              }}
            >
              <div style={{ fontWeight: 700 }}>{item.shift ?? "-"}</div>
              <div>{item.check_in ?? "-"}</div>
              <div>{item.check_out ?? "-"}</div>
            </div>
          );
        })}
      </div>
    );
  };

  const handleCellClick = (record, day) => {
    const date = dayjs(filters.periode + "-" + String(day).padStart(2, "0")).format("YYYY-MM-DD");

    setSelectedCell({
      employee_id: record.employee_id,
      nama: record.nama,
      branch: record.branch,
      date,
    });

    form.setFieldsValue({
      start_date: dayjs(date),
      end_date: dayjs(date),
    });

    setModalOpen(true);
  };

  const handleSubmit = async (values) => {
    try {
      const payload = {
        employee_id: selectedCell.employee_id,
        start_date: values.start_date.format("YYYY-MM-DD"),
        end_date: values.end_date.format("YYYY-MM-DD"),
        status: values.status,
        note: values.note,
      };

      await storeAttendanceException(payload);

      message.success("Berhasil simpan");
      setModalOpen(false);
      form.resetFields();
      handleRead();
    } catch (err) {
      message.error("Gagal simpan");
    }
  };

  const fixedColumns = [
    {
      title: "NRK",
      dataIndex: "user_id",
      key: "user_id",
      fixed: "left",
      width: 80,
    },
    {
      title: "Nama",
      dataIndex: "nama",
      key: "nama",
      fixed: "left",
      width: 160,
      render: (text) => text || "-",
    },
    {
      title: "Unit Kerja",
      dataIndex: "branch",
      key: "branch",
      fixed: "left",
      width: 120,
      render: (text) => text || "-",
    },
  ];

  const dayColumns = Array.from({ length: daysInMonth }, (_, i) => {
    const day = i + 1;
    return {
      title: <div style={{ textAlign: "center", fontSize: 12 }}>{day}</div>,
      key: `day_${day}`,
      width: 64,
      align: "center",
      render: (_, record) => (
        <div onClick={() => handleCellClick(record, day)}>
          {renderCell(record.attendance?.[day])}
        </div>
      ),
    };
  });

  const columns = [...fixedColumns, ...dayColumns];

  return (
    <Card
      title={
        <Space>
          <span>Attendance</span>
          {monthLabel && (
            <Tag color="blue" style={{ fontWeight: 400 }}>
              {monthLabel}
            </Tag>
          )}
        </Space>
      }
      extra={(
        <PrimaryButton
          onClick={handlePull} loading={loading}
          label={"Sync Attendance"} />
      )}
      style={{ margin: 20 }}
    >
      <Head title="Attendance" />

      <Form layout="vertical" style={{ marginBottom: 12 }}>
        <Row gutter={12}>
          <Col xs={24} sm={12} md={8} lg={4}>
            <FormSelect label="Pilih Periode" options={utils.periods}
              value={filters.periode}
              onChange={(e) =>
                handleChangeFilter("periode", e)
              }
            />
          </Col>
          <Col xs={24} sm={12} md={8} lg={4}>
            <FormSelect label="Unit Kerja" options={utils.branchs}
              value={filters.branch}
              onChange={(e) =>
                handleChangeFilter("branch", e)
              } />
          </Col>
        </Row>
      </Form>

      <Table
        columns={columns}
        dataSource={data}
        rowKey="user_id"
        bordered
        loading={loading}
        pagination={{ pageSize: 20 }}
        scroll={{ x: "max-content" }}
        size="small"
        className="custom-table"
      />

      {loading ? <LoadingComponent /> : null}

      <Modal
        title="Input note Absensi"
        open={modalOpen}
        onCancel={() => setModalOpen(false)}
        onOk={() => form.submit()}
        okText="Simpan"
        cancelText="Batal"
      >
        <Card
          size="small"
          style={{
            marginBottom: 16,
            background: "#fafafa",
            border: "1px solid #f0f0f0",
          }}
        >
          <Row gutter={[8, 4]}>
            <Col span={24}>
              <b>Nama:</b> {selectedCell?.nama ?? "-"}
            </Col>
            <Col span={24}>
              <b>Unit Kerja:</b> {selectedCell?.branch ?? "-"}
            </Col>
            <Col span={24}>
              <b>Tanggal:</b>{" "}
              {selectedCell?.date
                ? dayjs(selectedCell.date).format("DD MMMM YYYY")
                : "-"}
            </Col>
          </Row>
        </Card>
        <Form
          form={form}
          layout="vertical"
          onFinish={handleSubmit}
        >
          <Row gutter={12}>
            <Col span={12}>
              <Form.Item
                name="start_date"
                label="Tanggal Mulai"
                rules={[{ required: true, message: "Wajib diisi" }]}
              >
                <DatePicker
                  style={{ width: "100%" }}
                  disabledDate={(current) =>
                    current && current < form.getFieldValue("start_date")
                  }
                />
              </Form.Item>
            </Col>

            <Col span={12}>
              <Form.Item
                name="end_date"
                label="Tanggal Selesai"
                rules={[{ required: true, message: "Wajib diisi" }]}
              >
                <DatePicker style={{ width: "100%" }} />
              </Form.Item>
            </Col>
          </Row>
          <Form.Item
            name="status"
            label="Status Kehadiran"
            rules={[{ required: true, message: "Pilih status" }]}
          >
            <Select
              placeholder="Pilih status"
              options={[
                { label: "Dinas Luar Awal (DLAW)", value: "DLAW" },
                { label: "Dinas Luar Akhir (DLAK)", value: "DLAK" },
                { label: "Ijin Awal (IJIN1)", value: "IJIN1" },
                { label: "Ijin Akhir (IJIN2)", value: "IJIN2" },
                { label: "Sakit (S)", value: "S" },
                { label: "Ijin (I)", value: "I" },
                { label: "Dinas Luar Penuh (DLP)", value: "DLP" },
                { label: "Cuti Tahunan (CT)", value: "CT" },
              ]}
            />
          </Form.Item>
          <Form.Item name="note" label="note Tambahan">
            <Input.TextArea
              rows={3}
              placeholder="Contoh: Dinas luar ke kantor pusat"
            />
          </Form.Item>
        </Form>
      </Modal>
    </Card>
  );
}

Index.layout = (page) => <Main children={page} />;
export default Index;