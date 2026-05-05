import React, { useState, useEffect } from 'react';
import { Modal, Button, Checkbox, Spin } from 'antd';
import { useResponsive } from '../../../Helpers/ResponsiveHelpers';
import { usePage } from "@inertiajs/react";
import { changePermission } from '../../../services/api/users/users'
import { showError, showSuccess } from '../../../components/Alert';
import { LoadingComponent } from '../../../components/Loading';

function Permission(props) {
    const pages = usePage().props;
    const [open, setOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [menus, setMenus] = useState([]);
    const { isMobile, isTablet } = useResponsive();
    const [updated, setUpdated] = useState(false);

    useEffect(() => {
        if (props.open) {
            setOpen(true);
            let user_permissions = props.data.permission != null ? props.data.permission.split(",") : [];
            let permissions = [];
            pages.menus.map((item, index) => {
                let having = user_permissions.includes(String(item.id));
                item.having = having;
                if (item.childs.length > 0) {
                    item.childs.map((itm, idx) => {
                        let having = user_permissions.includes(String(itm.id));
                        itm.having = having;
                    });
                }
                permissions.push(item);
            });
            setMenus(permissions);
        }
    }, [props]);

    const handleChangePermission = async (value, id) => {
        try {
            let formData = new FormData();
            formData.append('permission', value == true ? 1 : 0);
            formData.append('menu', id);
            formData.append('user', props.data.id);
            setLoading(true);
            let response = await changePermission(formData);
            setLoading(false);

            if (response.data.status) {
                showSuccess(response.data.message, 'create');
                let user_permissions = response.data.params.current_perms;
                let permissions = [];
                pages.menus.map((item, index) => {
                    let having = user_permissions.includes(String(item.id));
                    item.having = having;
                    if (item.childs.length > 0) {
                        item.childs.map((itm, idx) => {
                            let having = user_permissions.includes(String(itm.id));
                            itm.having = having;
                        });
                    }
                    permissions.push(item);
                });
                setUpdated(true);
                setMenus(permissions);
            } else {
                showError(response.data.message);
            }
        } catch (e) {
            setLoading(false);
            showError(e.response.data.message);
        }
    }

    const handleClose = () => {
        if (updated) {
            props.handleUpdate();
        }
        setUpdated(false);
        setOpen(false);
        props.handleClose('permission', null);
    }

    return (
        <>
            <Modal
                title="Permission"
                centered={true}
                width={isMobile || isTablet ? '90%' : '60%'}
                onClose={() => { }}
                open={open}
                onCancel={handleClose}
                maskClosable={false}
                style={{ fontWeight: '600' }}
                footer={false}
                id="permission">

                {loading ?
                    <LoadingComponent />
                    : null}

                <ul style={{ listStyle: "none", padding: 0, margin: 0 }}>
                    {menus.map((item, index) => (
                        <li key={index} style={{ marginBottom: "8px" }}>
                            {/* Parent Item */}
                            <div
                                style={{
                                    display: "flex",
                                    alignItems: "center",
                                    justifyContent: 'space-between',
                                    padding: "12px",
                                    borderRadius: "6px",
                                    fontWeight: "bold",
                                    transition: "0.3s",
                                    border: "1px solid #ddd",
                                }}
                            >
                                <span>{item.label}</span>
                                <Checkbox checked={item.having} onChange={(e) => handleChangePermission(e.target.checked, item.id)} />
                            </div>

                            {/* Child Items */}
                            {item.childs.length > 0 && (
                                <ul
                                    style={{
                                        listStyle: "none",
                                        paddingLeft: "20px",
                                        marginTop: "6px",
                                    }}
                                >
                                    {item.childs.map((itm, idx) => (
                                        <li
                                            key={idx}
                                            style={{
                                                padding: "10px",
                                                background: "#ffffff",
                                                borderRadius: "5px",
                                                marginBottom: "5px",
                                                border: "1px solid #ddd",
                                                fontSize: "14px",
                                                transition: "0.3s",
                                            }}>
                                            <div
                                                style={{
                                                    display: "flex",
                                                    alignItems: "center",
                                                    justifyContent: 'space-between',
                                                }}
                                            >
                                                <span>{itm.label}</span>
                                                <Checkbox checked={itm.having} onChange={(e) => handleChangePermission(e.target.checked, itm.id)} />
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </li>
                    ))}
                </ul>
            </Modal>
        </>
    )
}

export default Permission;