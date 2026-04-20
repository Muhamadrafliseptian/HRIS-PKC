import React, { useEffect, useState } from "react";
import { Head, usePage } from "@inertiajs/react";
import { Table, Card, Form, Row, Col, Breadcrumb } from "antd";
import Main from "../../layout/Main";
import "../../../css/main.css";
import { PrimaryButton } from "../../components/Button";
import { FormSearch, FormSelect } from "../../components/Form";
import { showError } from "../../components/Alert";
import Import from "./Modals/Import";
import { readEmployees } from "../../services/api/employee/employee";

function Index() {
  const pages = usePage().props
  const [utils, setUtils] = useState({
    branchs: [],
    categories: [],
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
      status: pages.status
    })
  }, [])

  useEffect(() => {
    readEmployee()
  }, [])

  const readEmployee = async () => {
    try {
      let response = await readEmployees()
      if (response.data.status) {
        setUsers(response.data.params)
      }
    } catch (err) {
      showError(err.response.data.message)
    }
  }

  const columns = [
    {
      title: "Name",
      render: (data) => <p className="tableSetUp">{data.name}</p>,
    },
    {
      title: "NRK",
      render: (data) => <p className="tableSetUp">{data.user_id}</p>,
    },
    {
      title: "Branch",
      render: (data) => <p className="tableSetUp">{data.dtbranch?.name}</p>,
    },
    {
      title: "Employee Status",
      render: (data) => <p className="tableSetUp">{data.dtstatus?.name}</p>,
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
      <Import open={modal.import.open}
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
              />
            </Col>
            <Col xs={24} sm={24} md={12} lg={4} xl={4}>
              <FormSelect
                options={utils.categories}
                label={"Category"}
              />
            </Col>
            <Col xs={24} sm={24} md={12} lg={4} xl={4}>
              <FormSelect
                label={"Status"}
                options={utils.status}
              />
            </Col>
            <Col
              xs={24} sm={24} md={12} lg={4} xl={4}
              style={{ display: "flex", justifyContent: "end" }}
            >
              <FormSearch
                label={"Search"}
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
        />
      </Card>
    </div>
  );
}

Index.layout = (page) => <Main children={page} />;

export default Index;