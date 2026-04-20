import { Spin, Typography } from "antd";
const { Text, Paragraph } = Typography;

export const LoadingComponent = ({ text = "Loading..." }) => {
    return (
        <>
            <div
                style={{
                    position: "fixed",
                    top: 0,
                    left: 0,
                    width: "100vw",
                    height: "100vh",
                    zIndex: 9999,
                    backgroundColor: "rgba(0, 0, 0, 0.3)",
                    display: "flex",
                    flexDirection: "column",
                    alignItems: "center",
                    justifyContent: "center",
                }}
            >
                <Spin size="large" />
                <h5
                    style={{
                        marginTop: "10px",
                        color: "white",
                        fontWeight: "600",
                        fontSize: "18px",
                    }}
                >
                    {text}
                </h5>
            </div>
        </>
    );
};