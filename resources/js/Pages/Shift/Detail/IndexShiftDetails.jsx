import React from "react";
import Main from "../../../layout/Main";
import { usePage } from "@inertiajs/react";
import { Card, Table, Tag } from "antd";

function IndexShiftDetails() {
    const { shift } = usePage().props;

    const columns = [
        {
            title: "Segment",
            dataIndex: "order",
            key: "order",
        },
        {
            title: "Jam Masuk",
            dataIndex: "clock_in",
            key: "clock_in",
        },
        {
            title: "Jam Keluar",
            dataIndex: "clock_out",
            key: "clock_out",
        },
        {
            title: "Cross Day",
            dataIndex: "is_cross_day",
            key: "is_cross_day",
            render: (val) => (
                <Tag color={val ? "orange" : "green"}>
                    {val ? "Ya" : "Tidak"}
                </Tag>
            ),
        },
        {
            title: "Type",
            dataIndex: "segment_type",
            key: "segment_type",
            render: (val) => (
                <Tag color="blue">
                    {val || "work"}
                </Tag>
            ),
        },
    ];

    return (
        <Card
            title={`Detail Shift - ${shift?.[0]?.shift?.code} (${shift?.[0]?.shift?.name})`}
            style={{ marginTop: "12px" }}
        >
            <Table
                columns={columns}
                dataSource={shift}
                rowKey="id"
                pagination={false}
                bordered
            />
        </Card>
    );
}

IndexShiftDetails.layout = (page) => <Main children={page} />;

export default IndexShiftDetails;