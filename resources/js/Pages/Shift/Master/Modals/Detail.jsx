import { Modal, Tag, Card, Row, Col, Divider } from "antd";
import React, { useEffect, useState } from "react";
import dayjs from "dayjs";
import "dayjs/locale/id";
dayjs.locale("id");

function Detail(props) {
    const [open, setOpen] = useState(false);
    const [data, setData] = useState({});
    useEffect(() => {
        if (props.open) {
            setOpen(true);
            setData(props.data || {});
        }
    }, [props]);
    const handleClose = () => {
        setOpen(false);
        props.handleClose && props.handleClose("detail");
    };
    const formatTime = (t) => (t ? dayjs(t, "HH:mm:ss").format("HH:mm") : "-");

    const InfoCard = ({ label, value }) => (
        <Card
            size="small"
            style={{
                background: "#fafafa",
                borderRadius: 10,
                border: "1px solid #f0f0f0",
                minHeight: 70,
            }}
        >
            <div style={{ fontSize: 12, color: "#888" }}>{label}</div>
            <div style={{ fontWeight: 600, fontSize: 15 }}>{value}</div>
        </Card>
    );

    return (
        <Modal
            title={<span style={{ fontWeight: 600 }}>Detail Shift</span>}
            centered
            open={open}
            onCancel={handleClose}
            maskClosable={false}
            footer={false}
            width={700}
        >
            <Row gutter={[16, 16]}>
                <Col span={12}>
                    <InfoCard label="Nama Shift" value={data.name ?? "-"} />
                </Col>
                <Col span={12}>
                    <InfoCard label="Cabang" value={data.branch?.name ?? "-"} />
                </Col>
                <Col span={6}>
                    <InfoCard
                        label="Jam Masuk"
                        value={formatTime(data.clock_in)}
                    />
                </Col>
                <Col span={6}>
                    <InfoCard
                        label="Jam Keluar"
                        value={formatTime(data.clock_out)}
                    />
                </Col>
                <Col span={6}>
                    <InfoCard
                        label="Absen Beda Hari"
                        value={
                            data.is_cross_day ? (
                                <Tag color="orange">Ya</Tag>
                            ) : (
                                <Tag color="default">Tidak</Tag>
                            )
                        }
                    />
                </Col>
                <Col span={6}>
                    <InfoCard
                        label="Shift Default"
                        value={
                            data.is_default ? (
                                <Tag color="green">Ya</Tag>
                            ) : (
                                <Tag color="default">Tidak</Tag>
                            )
                        }
                    />
                </Col>

            </Row>

            <Divider style={{ fontWeight: 600 }}>Toleransi Waktu</Divider>

            <Row gutter={[16, 16]}>
                <Col span={12}>
                    <InfoCard
                        label="Absen Masuk Paling Awal"
                        value={formatTime(data.tolerance_before_in)}
                    />
                </Col>
                <Col span={12}>
                    <InfoCard
                        label="Toleransi Terlambat"
                        value={formatTime(data.tolerance_after_in)}
                    />
                </Col>
                <Col span={12}>
                    <InfoCard
                        label="Absen Pulang Lebih Awal"
                        value={formatTime(data.tolerance_before_out)}
                    />
                </Col>
                <Col span={12}>
                    <InfoCard
                        label="Toleransi Setelah Pulang"
                        value={formatTime(data.tolerance_after_out)}
                    />
                </Col>
                <Col span={12}>
                    <InfoCard
                        label="Status"
                        value={
                            data.is_active ? (
                                <Tag color="blue">Aktif</Tag>
                            ) : (
                                <Tag color="red">Tidak Aktif</Tag>
                            )
                        }
                    />
                </Col>
            </Row>
        </Modal>
    );
}

export default Detail;
