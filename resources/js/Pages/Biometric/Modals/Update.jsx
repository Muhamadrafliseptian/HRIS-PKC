import { Col, Form, Modal, Row } from 'antd'
import React, { useEffect, useState } from 'react'
import { FormSelect, FormText } from '../../../components/Form'
import { useResponsive } from '../../../Helpers/ResponsiveHelpers';
import { usePage } from '@inertiajs/react';
import { PrimaryButton, DangerButton } from '../../../components/Button';
import { LoadingComponent } from '../../../components/Loading';
import { errorHandler } from '../../../components/Handler';
import { showSuccess } from '../../../components/Alert';
import { transferUsers } from '../../../services/api/biometric/biometric';

export default function Update(props) {
  const [open, setOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [utils, setUtils] = useState({
    branchs: [],
    devices: [],
    categories: []
  });
  const [form, setForm] = useState({
    id_origin: "",
    id_destination: "",
    port: "",
    user_id: "",
  });
  const [error, setError] = useState({
    id_origin: "",
    id_destination: "",
    port: "",
    user_id: "",
  });
  const pages = usePage().props
  const data = props.data

  const { isDesktop, isMobile, isTablet } = useResponsive()
  useEffect(() => {
    if (props.open) {
      setOpen(true);
      setUtils({
        branchs: pages.branchs,
        categories: pages.categories,
        devices: pages.devices
      });
    }
  }, [props]);

  const handleClose = () => {
    setOpen(false);
    setForm({
      id_origin: "",
      id_destination: "",
      port: "",
      user_id: "",
    })
    props.handleClose("update");
  };

  const handleChangeForm = (field, value) => {
    setForm((prev) => ({
      ...prev,
      [field]: value,
    }));
  };

  const Submit = async () => {
    try {
      let formData = new FormData();
      formData.append("id_origin", data?.device?.id);
      formData.append("id_destination", form.id_destination);
      formData.append("user_id", data?.user_id);

      setLoading(true);
      let response = await transferUsers(formData);
      setLoading(false);

      if (response.data.status) {
        showSuccess(response.data.message)
        props.handleUpdate();
        handleClose();
      }
    } catch (e) {
      setLoading(false);
      errorHandler(e, setError);
    }
  };

  return (
    <div>
      <Modal
        title="Mutasi User"
        centered={true}
        width={isMobile || isTablet ? "100%" : "60%"}
        open={open}
        onCancel={handleClose}
        style={{ fontWeight: "600" }}
        footer={false}
        id="create"
      >
        <Form layout='vertical'>
          <Row gutter={12}>
            <Col span={24}>
              <FormSelect label={"Devices Destination"}
                options={utils.devices}
                rules={[{ required: true }]}
                value={form.id_destination}
                onChange={(e) =>
                  handleChangeForm(
                    "id_destination",
                    e,
                  )
                }
                disabled={loading}
                search={true}
                error={error.id_destination}
              />
            </Col>
          </Row>
          <Row gutter={24}>
            <Col
              span={24}
              style={{
                display: "flex",
                justifyContent: "end",
                gap: 8,
              }}
            >
              <DangerButton
                type={"button"}
                label={"Close"}
                onClick={handleClose}
                disabled={loading}
                isDanger={true}
              />
              <PrimaryButton
                htmlType={"submit"}
                label={"Submit"}
                onClick={Submit}
                disabled={loading}
              />
            </Col>
          </Row>
        </Form>
      </Modal>
      {loading ? <LoadingComponent /> : null}
    </div>
  )
}
