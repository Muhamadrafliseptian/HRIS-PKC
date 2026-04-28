import React, { useEffect, useState } from "react";
import { Head, router, usePage } from "@inertiajs/react";
import { Table, Card, Form, Row, Col, Breadcrumb, Button, Dropdown, Menu } from "antd";
import Main from "../../../../layout/Main";
import "../../../../../css/main.css";
import { LoadingComponent } from "../../../../components/Loading"
import { FormDateRangePicker, FormSearch, FormSelect } from "../../../../components/Form";
import { showError } from "../../../../components/Alert";
import { readEmployees } from "../../../../services/api/employee/employee";
import axios from "axios";
import { DownOutlined } from "@ant-design/icons";
function Index() {
  const pages = usePage().props
  const [loading, setLoading] = useState(false)
  const [selectedRowKeys, setSelectedRowKeys] = useState([]);
  const [filters, setFilters] = useState({
    branch: "",
    employee_status: "",
    device_id: "",
    service: "",
    search: '',
    start_date: "",
    end_date: "",
  })
  const [utils, setUtils] = useState({
    branchs: [],
    categories: [],
    devices: [],
    services: []
  })
  const handleDateRange = (dates) => {
    if (!dates) {
      setFilters((prev) => ({
        ...prev,
        start_date: "",
        end_date: "",
      }));
      return;
    }

    setFilters((prev) => ({
      ...prev,
      start_date: dates[0].format("YYYY-MM-DD"),
      end_date: dates[1].format("YYYY-MM-DD"),
    }));
  };
  const rowSelection = {
    selectedRowKeys,
    onChange: (selectedKeys) => {
      setSelectedRowKeys(selectedKeys);
    },
  };
  const [users, setUsers] = useState([])
  const [modal, setModal] = useState({
    detail: { open: false, data: null },
    create: { open: false, data: null },
    update: { open: false, data: null },
    import: { open: false, data: null },
  });

  useEffect(() => {
    setUtils({
      branchs: pages.branchs,
      categories: pages.categories,
      devices: pages.devices,
      services: pages.services
    })
  }, [])

  useEffect(() => {
    readEmployee()
  }, [filters.branch, filters.device_id, filters.employee_status,
  filters.start_date,
  filters.service,
  filters.end_date
  ])

  const readEmployee = async () => {
    try {
      let formData = new FormData
      formData.append('branch', filters.branch)
      formData.append('employee_status', filters.employee_status)
      formData.append('employee_services', filters.service)
      formData.append('search', filters.search)
      formData.append('device_id', filters.device_id)

      setLoading(true)
      let response = await readEmployees(formData)
      setLoading(false)
      if (response.data.status) {
        setUsers(response.data.params)
      }
    } catch (err) {
      showError(err.response.data.message)
      setLoading(false)

    }
  }

  const items = [
    {
      key: "download",
      label: "⬇ Download",
      children: [
        { key: "log", label: "Log" },
        { key: "kehadiran", label: "Kehadiran" },
        // { key: "rekap", label: "Rekap" },
      ],
    },
    {
      key: "preview",
      label: "👁 Preview",
      children: [
        { key: "preview_log", label: "Log" },
        { key: "preview_kehadiran", label: "Kehadiran" },
        // { key: "preview_rekap", label: "Rekap" },
      ],
    },
  ];

  const handleMenuClick = ({ key }) => {
    if (key.startsWith("preview_")) {
      handlePreview(key.replace("preview_", ""));
    } else {
      handleDownload(key);
    }
  };

  const handlePreview = (type) => {
    router.get(`/report/attendance/log/preview`, {
      ...filters,
      type: type,
      ids: JSON.stringify(selectedRowKeys),
    });
  };

  const handleDownload = async (type) => {
    try {
      setLoading(true);

      let formData = new FormData();
      formData.append("type", type);
      formData.append("ids", JSON.stringify(selectedRowKeys));
      formData.append("branch", filters.branch);
      formData.append("employee_status", filters.employee_status);
      formData.append("employee_services", filters.service);
      formData.append("device_id", filters.device_id);
      formData.append("start_date", filters.start_date);
      formData.append("end_date", filters.end_date);
      formData.append("type", type);

      const res = await axios.post(
        "/report/attendance/log/download",
        formData,
        { responseType: "blob" }
      );

      const url = window.URL.createObjectURL(new Blob([res.data]));
      const link = document.createElement("a");
      link.href = url;
      link.setAttribute("download", `${type}_attendance.pdf`);
      document.body.appendChild(link);
      link.click();

      setLoading(false);
    } catch (err) {
      setLoading(false);
      showError(err?.response?.data?.message);
    }
  };

  const columns = [
    {
      title: "NRK",
      render: (data) => <p className="tableSetUp">{data.user_id}</p>,
    },
    {
      title: "Name",
      render: (data) => <p className="tableSetUp">{data.name}</p>,
    },
    {
      title: "Unit Kerja",
      render: (data) => <p className="tableSetUp">{data.dtbranch?.name}</p>,
    },
  ];

  const toggleModal = (what, data = null) => {
    setModal((prevModals) => {
      let updatedModals = { ...prevModals };
      updatedModals[what].open = !prevModals[what].open;
      if (data !== null) {
        updatedModals[what].data = data;
      }
      return updatedModals;
    });
  };
  const breadCrumbsItems = [
    { title: <h5 style={{ fontWeight: "600" }}>Report</h5> },
    { title: <h5 style={{ fontWeight: "600" }}>Attendance</h5> },
    { title: <h5 style={{ fontWeight: "600" }}>Log</h5> },
  ];

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

  return (
    <div>
      <Head title="Report Attendance Log" />
      <Breadcrumb items={breadCrumbsItems} />
      <Card title={"Report Attendance Log"}
        style={{ marginTop: "12px" }}
        extra={(
          <Dropdown
            menu={{ items, onClick: handleMenuClick }}
            trigger={["click"]}
          >
            <Button
              type="primary"
              disabled={selectedRowKeys.length === 0}
            >
              Action <DownOutlined />
            </Button>
          </Dropdown>
        )}
      >
        <Form layout="vertical">
          <Row gutter={12}>
            <Col xs={24} sm={24} md={12} lg={4} xl={4}>
              <FormDateRangePicker
                label={"Rentang Tanggal"}
                onChange={handleDateRange} />
            </Col>
            <Col xs={24} sm={24} md={12} lg={4} xl={4}>
              <FormSelect
                label={"Unit Kerja"}
                disabled={loading}
                options={utils.branchs}
                value={filters.branch}
                onChange={(e) => handleChangeFilter("branch", e)}
              />
            </Col>
            <Col xs={24} sm={24} md={12} lg={4} xl={4}>
              <FormSelect
                options={utils.services}
                disabled={loading}
                label={"Tipe Pegawai"}
                value={filters.service}
                onChange={(e) => handleChangeFilter('service', e)}
              />
            </Col>
            <Col xs={24} sm={24} md={12} lg={4} xl={4}>
              <FormSelect
                label={"Devices"}
                disabled={loading}
                value={filters.device_id}
                onChange={(e) => handleChangeFilter('device_id', e)}
                options={utils.devices}
              />
            </Col>
            <Col
              xs={24} sm={24} md={12} lg={4} xl={4}
              style={{ display: "flex", justifyContent: "end" }}
            >
              <FormSearch
                value={filters.search}
                disabled={loading}
                label={"Search"}
                onChange={(e) => handleChangeFilter('search', e.target.value)}
              />
            </Col>
          </Row>
        </Form>
        <Table
          columns={columns}
          dataSource={users}
          rowKey="id"
          bordered
          rowSelection={rowSelection}
          style={{ width: "100%" }}
          className="custom-table"
          showExpandColumn={false}
          scroll={{ x: "max-content" }}
        />
      </Card>
      {loading ? <LoadingComponent /> : null}
    </div>
  );
}

Index.layout = (page) => <Main children={page} />;

export default Index;