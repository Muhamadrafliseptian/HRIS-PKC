import sys
import json
import traceback
import calendar
from zk import ZK
from datetime import datetime

MAX_RECORD = 3000

def get_attendance(ip, port, periode=None, start_time=None):
    conn = None

    try:
        START_DATE = datetime(2000, 1, 1)
        END_DATE   = datetime(2100, 12, 31, 23, 59, 59)

        # ================= PERIODE =================
        if periode:
            try:
                year, month = map(int, periode.split("-"))
                START_DATE = datetime(year, month, 1)
                last_day = calendar.monthrange(year, month)[1]
                END_DATE = datetime(year, month, last_day, 23, 59, 59)
            except:
                pass

        # ================= CONNECT =================
        zk = ZK(ip, port=port, timeout=5, password=0, force_udp=False, ommit_ping=True)
        conn = zk.connect()

        attendances = conn.get_attendance()

        cutoff = None
        if start_time:
            try:
                cutoff = datetime.strptime(start_time, "%Y-%m-%d %H:%M:%S")
            except:
                cutoff = None

        result = []
        count = 0

        for att in reversed(attendances):

            if not (START_DATE <= att.timestamp <= END_DATE):
                continue

            if cutoff and att.timestamp < cutoff:
                continue

            result.append({
                "user_id": str(att.user_id),
                "timestamp": att.timestamp.strftime("%Y-%m-%d %H:%M:%S"),
                "status": att.status,
            })

            count += 1
            if count >= MAX_RECORD:
                break

        print(json.dumps({
            "success": True,
            "data": result
        }))

    except Exception as e:
        print(json.dumps({
            "success": False,
            "error": str(e),
            "trace": traceback.format_exc()
        }))
        sys.exit(1)

    finally:
        if conn:
            try:
                conn.disconnect()
            except:
                pass


if __name__ == "__main__":
    ip = sys.argv[1]
    port = int(sys.argv[2])
    periode = sys.argv[3] if len(sys.argv) > 3 else None
    start_time = sys.argv[4] if len(sys.argv) > 4 else None

    get_attendance(ip, port, periode, start_time)