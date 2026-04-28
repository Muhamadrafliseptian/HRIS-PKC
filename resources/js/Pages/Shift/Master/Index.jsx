import Main from "../../../layout/Main";
import { Head, router, usePage } from "@inertiajs/react";
import { Card, Col, Dropdown, Form, Row, Table, Tag } from "antd";
import React, { useEffect, useState } from "react";
import "../../../../css/main.css";
import { PrimaryButton } from "../../../components/Button";
import Create from "./Modals/Create";
import {
    changeStatusMasterShifts,
    readMasterShifts,
} from "../../../services/api/shift/shift"
import Update from "./Modals/Update";
import Detail from "./Modals/Detail";
import Swal from "sweetalert2";
import { showError, showSuccess } from "../../../components/Alert";

function Index() {
    const [datas, setDatas] = useState([]);
    const pages = usePage().props;
    const [loading, setLoading] = useState(false);
    const [modals, setModals] = useState({
        create: { open: false, data: null },
        update: { open: false, data: null },
        detail: { open: false, data: null },
    });
    const [utilities, setUtilities] = useState({
        branchs: [],
    });
    const [pagination, setPagination] = useState({
        current: 1,
        pageSize: 10,
        total: 0,
    });
    const [filters, setFilters] = useState({
        branch: "",
    });
    const handleTableChange = (pagination) => {
        try {
            setPagination(pagination);
        } catch (e) {
            showError(e);
        }
    };
    useEffect(() => {
        setUtilities({
            branchs: pages.branchs,
        });
    }, []);
    const toggleModal = (what, data = null) => {
        setModals((prevModals) => {
            let updatedModals = { ...prevModals };
            updatedModals[what].open = !prevModals[what].open;
            if (data !== null) {
                updatedModals[what].data = data;
            }
            return updatedModals;
        });
    };
    const renderUtilityButton = () => {
        return (
            <div
                style={{ display: "flex", justifyContent: "end", gap: "12px" }}
            >
                <PrimaryButton
                    label={"Create"}
                    icon={"ti ti-plus"}
                    onClick={() => toggleModal("create")}
                    disabled={loading}
                />
            </div>
        );
    };

    useEffect(() => {
        getShifts();
    }, [
        filters.branch,
        pagination.current,
        pagination.pageSize,
    ]);

    const getShifts = async () => {
        try {
            setLoading(true);
            let formData = new FormData();
            formData.append("branch", filters.branch);
            formData.append("page", pagination.current);
            formData.append("per_page", pagination.pageSize);
            let response = await readMasterShifts(formData);
            setLoading(false);

            if (response.data.status) {
                setDatas(response.data.params.shifts);
                setPagination((prev) => ({
                    ...prev,
                    total: response.data.params.meta.total,
                }));
            }
        } catch (err) {
            setLoading(false);
        }
    };
    const getMenuItems = (data) => {
        let dropdown = [];

        let detail_data = {
            key: "detail",
            label: "Detail",
            icon: <i className="ti ti-eye"></i>,
            onClick: () => router.get(`/manage/shift/detail/${data.id}`)
        };

        let edit_data = {
            key: "edit",
            label: "Edit",
            icon: <i className="ti ti-edit"></i>,
            onClick: () => toggleModal("update", data),
        };

        let change_status = {
            key: "status",
            label: "Change Status",
            icon: <i className="ti ti-refresh"></i>,
            onClick: () => updateStatus(data),
        };

        dropdown.push(detail_data);
        if (data.is_active == 1) {
            dropdown.push(edit_data);
        }
        dropdown.push(change_status);

        return dropdown;
    };

    const columns = [
        {
            title: "Code",
            render: (data) => <p className="tableSetUp">{data.code}</p>,
        },
        {
            title: "Nama",
            render: (data) => <p className="tableSetUp">{data.name}</p>,
        },
        {
            title: "Category",
            render: (data) => <p className="tableSetUp">{data?.category?.name}</p>,
        },
        {
            width: "120px",
            title: "Status",
            render: (data) => (
                <Tag
                    bordered={false}
                    color={data.is_active == 1 ? "green" : "error"}
                >
                    {data.is_active == 1 ? "Aktif" : "Non Aktif"}
                </Tag>
            ),
        },
        {
            width: "70px",
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

    const updateStatus = async (data) => {
        let message = "Anda Yakin Ubah Status?";
        Swal.fire({
            title: "Perhatian !",
            html: message,
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#FF9800",
            confirmButtonText: "Ya",
            cancelButtonText: "Batalkan",
            cancelButtonColor: "#ddd",
            closeOnConfirm: false,
            showLoaderOnConfirm: true,
            allowOutsideClick: () => !Swal.isLoading(),
            allowEscapeKey: () => !Swal.isLoading(),
            preConfirm: async (e) => {
                try {
                    let formData = new FormData();
                    formData.append("id", data.id);
                    formData.append("branch", data.branch.id);
                    formData.append(
                        "is_active",
                        !data.is_active == true ? 1 : 0
                    );
                    let response = await changeStatusMasterShifts(formData);
                    if (response.data.status == false) {
                        Swal.showValidationMessage(response.message);
                        Swal.hideLoading();
                    } else {
                        Swal.close();
                        getShifts();
                        showSuccess(response.data.message);
                    }
                } catch (e) {
                    showError(e.response.data.message);
                }
            },
        });
    };

    const handleChangeFilter = (field, value) => {
        try {
            setPagination((prev) => ({
                ...prev,
                current: 1,
            }));
            setFilters((prev) => ({
                ...prev,
                [field]: value == undefined ? "" : value,
            }));
        } catch (e) {
            setLoading(false);
        }
    };
    return (
        <>
            <Head title="Master Shifts" />
            <Create
                open={modals.create.open}
                handleClose={toggleModal}
                handleUpdate={getShifts}
            />
            <Update
                open={modals.update.open}
                handleClose={toggleModal}
                data={modals.update.data}
                handleUpdate={getShifts}
            />
            <Detail
                open={modals.detail.open}
                data={modals.detail.data}
                handleClose={toggleModal}
            />
            <Card
                title="Master Shifts"
                style={{ marginTop: "12px" }}
                extra={renderUtilityButton()}
            >
                <Table
                    style={{ width: "100%" }}
                    className="custom-table"
                    columns={columns}
                    dataSource={datas}
                    showExpandColumn={false}
                    loading={loading}
                    rowKey="id"
                    bordered={false}
                    scroll={{ x: "max-content" }}
                    pagination={pagination}
                    onChange={handleTableChange}
                />
            </Card>
        </>
    );
}
Index.layout = (page) => <Main children={page} />;

export default Index;
