import React, { useEffect, useState } from "react";
import { Head, usePage } from "@inertiajs/react";
import { Table, Card, Form, Row, Col, Breadcrumb, Dropdown } from "antd";
import Main from "../../layout/Main";
import "../../../css/main.css";
import { PrimaryButton } from "../../components/Button";
import { FormSearch, FormSelect } from "../../components/Form";
import { showError } from "../../components/Alert";
import Import from "./Modals/Import";
import { readEmployees } from "../../services/api/employee/employee";
import { LoadingComponent } from "../../components/Loading";
import Detail from "./Modals/Detail";

function Index() {
  const pages = usePage().props
  const [loading, setLoading] = useState(false)
  const [utils, setUtils] = useState({
    branchs: [],
    categories: [],
    services: [],
    status: []
  })
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
      services: pages.services,
      status: pages.status
    })
  }, [])

  const [filters, setFilters] = useState({
    branch: "",
    status: "",
    category: "",
    search: "",
    service: ""
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

  useEffect(() => {
    readEmployee()
  }, [filters.branch, filters.status, filters.search, filters.service])

  const readEmployee = async () => {
    try {
      let formData = new FormData()
      formData.append('branch', filters.branch)
      formData.append('service', filters.service)
      formData.append('search', filters.search)
      formData.append('status', filters.status)
      setLoading(true)
      let response = await readEmployees(formData)
      setLoading(false)
      if (response.data.status) {
        setUsers(response.data.params)
      }
    } catch (err) {
      setLoading(false)
      showError(err.response.data.message)
    }
  }

  const getMenuItems = (data) => [
    {
      key: "detail",
      label: "Detail",
      icon: <i className="ti ti-edit"></i>,
      onClick: () => toggleModal("detail", data),
    },

    {
      key: "delete",
      label: "Delete",
      icon: <i className="ti ti-trash"></i>,
    },
  ];

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
      title: "Status",
      render: (data) => <p className="tableSetUp">{data.dtstatus?.name}</p>,
    },
    {
      title: "Services",
      render: (data) => <p className="tableSetUp">{data.dtservice?.name}</p>,
    },
    {
      title: "Unit Kerja",
      render: (data) => <p className="tableSetUp">{data.dtbranch?.name}</p>,
    },
    {
      width: "150px",
      title: <i className="ti ti-settings"></i>,
      align: "center",
      render: (data) => (
        <>
          <Dropdown
            menu={{ items: getMenuItems(data) }}
            trigger={["click"]}
          >
            <i
              className="ti ti-dots-vertical"
              style={{ cursor: "pointer", padding: "12px" }}
            ></i>
          </Dropdown>
        </>
      ),
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
    { title: <h5 style={{ fontWeight: "600" }}>Biometric</h5> },
    { title: <h5 style={{ fontWeight: "600" }}>Users</h5> },
  ];
  return (
    <div>
      <Head title="Biometric Users" />
      <Breadcrumb items={breadCrumbsItems} />
      <Detail open={modal.detail.open}
        data={modal.detail.data}
        handleClose={toggleModal}
        handleUpdate={readEmployee}
      />
      <Card title={"Biometric Users"}
        style={{ marginTop: "12px" }}
        extra={
          <PrimaryButton
            label={"Import"} icon={"ti ti-plus"}
            onClick={() => toggleModal('import')}
          />
        }
      >
        <Form layout="vertical">
          <Row gutter={12}>
            <Col xs={24} sm={24} md={12} lg={4} xl={4}>
              <FormSelect
                label={"Branch"}
                options={utils.branchs}
                disabled={loading}
                value={filters.branch}
                onChange={(e) =>
                  handleChangeFilter("branch", e)
                }
              />
            </Col>
            <Col xs={24} sm={24} md={12} lg={4} xl={4}>
              <FormSelect
                label={"Services"}
                options={utils.services}
                disabled={loading}
                value={filters.service}
                onChange={(e) =>
                  handleChangeFilter("service", e)
                }
              />
            </Col>
            <Col xs={24} sm={24} md={12} lg={4} xl={4}>
              <FormSelect
                value={filters.status}
                options={utils.categories}
                label={"Status"}
                onChange={(e) =>
                  handleChangeFilter("status", e)
                }
                disabled={loading}

              />
            </Col>
            <Col
              xs={24} sm={24} md={12} lg={4} xl={4}
              style={{ display: "flex", justifyContent: "end" }}
            >
              <FormSearch
                value={filters.search}
                label={"Search"}
                onChange={(e) =>
                  handleChangeFilter("search", e.target.value)
                }
                disabled={loading}
              />
            </Col>
          </Row>
        </Form>
        <Table
          columns={columns}
          dataSource={users}
          rowKey="uid"
          bordered
          style={{ width: "100%" }}
          className="custom-table"
          showExpandColumn={false}
          scroll={{ x: "max-content" }}
          loading={loading}
        />
      </Card>
      {loading ? <LoadingComponent /> : null}
    </div>
  );
}

Index.layout = (page) => <Main children={page} />;

export default Index;