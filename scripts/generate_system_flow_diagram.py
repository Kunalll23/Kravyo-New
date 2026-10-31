import os
import subprocess

DIAGRAMS_DIR = r"c:\xampp\htdocs\Kravyo\diagrams"
CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
os.makedirs(DIAGRAMS_DIR, exist_ok=True)

def render_svg_to_png(svg_content, html_filename, png_filename, width=1280, height=2060):
    html_path = os.path.join(DIAGRAMS_DIR, html_filename)
    png_path = os.path.join(DIAGRAMS_DIR, png_filename)
    
    html_content = f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body {{
    margin: 0;
    padding: 15px;
    background: #ffffff;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: Arial, Helvetica, sans-serif;
  }}
  svg {{
    background: #ffffff;
  }}
</style>
</head>
<body>
{svg_content}
</body>
</html>"""
    
    with open(html_path, "w", encoding="utf-8") as f:
        f.write(html_content)
        
    cmd = [
        CHROME_PATH,
        "--headless",
        "--disable-gpu",
        "--hide-scrollbars",
        f"--window-size={width},{height}",
        f"--screenshot={png_path}",
        html_path
    ]
    subprocess.run(cmd, check=True)
    print(f"Generated: {png_filename} ({os.path.getsize(png_path)} bytes)")

DEFS = """
<defs>
  <marker id="arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
    <path d="M 0 1.5 L 9 5 L 0 8.5 z" fill="#000000" />
  </marker>
</defs>
"""

def draw_box(cx, cy, w, h, text1, text2=None, is_bold=False, rx=6, fill="#ffffff"):
    x = cx - w / 2
    y = cy - h / 2
    weight = 'font-weight="bold"' if is_bold else ""
    if text2:
        t_html = f"""
        <text x="{cx}" y="{cy - 3}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="10.5" {weight} fill="#000000">{text1}</text>
        <text x="{cx}" y="{cy + 12}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="10.5" {weight} fill="#000000">{text2}</text>
        """
    else:
        t_html = f"""
        <text x="{cx}" y="{cy + 4}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="10.5" {weight} fill="#000000">{text1}</text>
        """
    return f"""
    <rect x="{x}" y="{y}" width="{w}" height="{h}" rx="{rx}" ry="{rx}" fill="{fill}" stroke="#000000" stroke-width="1.3" />
    {t_html}
    """

def draw_decision_empty(cx, cy, size=28):
    """Strict UML 2.x Decision Node: completely empty diamond with no text inside"""
    half = size / 2
    pts = f"{cx},{cy-half} {cx+half},{cy} {cx},{cy+half} {cx-half},{cy}"
    return f'<polygon points="{pts}" fill="#ffffff" stroke="#000000" stroke-width="1.3" />'

def draw_circle_start(cx, cy, r=11):
    return f'<circle cx="{cx}" cy="{cy}" r="{r}" fill="#000000" stroke="#000000" stroke-width="1.2" />'

def draw_circle_end(cx, cy, r=12):
    return f"""
    <circle cx="{cx}" cy="{cy}" r="{r}" fill="#ffffff" stroke="#000000" stroke-width="1.3" />
    <circle cx="{cx}" cy="{cy}" r="{r-4}" fill="#000000" />
    """

def draw_arrow(x1, y1, x2, y2, label=None, label_side="right"):
    l_html = ""
    if label:
        lx = (x1 + x2) / 2 + (8 if label_side == "right" else -8)
        ly = (y1 + y2) / 2
        anchor = "start" if label_side == "right" else "end"
        l_html = f'<text x="{lx}" y="{ly}" font-family="Arial, Helvetica, sans-serif" font-size="9.5" font-weight="bold" fill="#000000" text-anchor="{anchor}">[{label}]</text>'
    return f'<line x1="{x1}" y1="{y1}" x2="{x2}" y2="{y2}" stroke="#000000" stroke-width="1.2" marker-end="url(#arrow)" />{l_html}'

def draw_line(x1, y1, x2, y2):
    return f'<line x1="{x1}" y1="{y1}" x2="{x2}" y2="{y2}" stroke="#000000" stroke-width="1.2" />'

def draw_path(d_str, label=None, lx=0, ly=0, anchor="middle", has_arrow=True):
    marker = 'marker-end="url(#arrow)"' if has_arrow else ''
    html = f'<path d="{d_str}" fill="none" stroke="#000000" stroke-width="1.2" {marker} />'
    if label:
        html += f'\n    <text x="{lx}" y="{ly}" text-anchor="{anchor}" font-family="Arial, Helvetica, sans-serif" font-size="9.5" font-weight="bold" fill="#000000">[{label}]</text>'
    return html

def build_system_flow_diagram():
    w, h = 1260, 2040
    
    # 4 Swimlane X coordinates
    # Lane 1: Customer (center: 180)
    # Lane 2: Home Chef (center: 460)
    # Lane 3: Backend & Dispatch (center: 740)
    # Lane 4: Courier & Settlement (center: 1040)
    s1 = 180
    s2 = 460
    s3 = 740
    s4 = 1040
    
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}
    
    <!-- Title -->
    <text x="630" y="30" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="16" font-weight="bold" fill="#000000">Figure 1.1: Kravyo End-to-End System Process Flow Diagram across All 4 Actors</text>
    <text x="630" y="48" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" fill="#444444">(Full Operational Architecture: On-Demand Food Orders &amp; Scheduled Multi-Tiffin Milk-Run Batch Logistics)</text>
    
    <!-- Swimlane Headers -->
    <rect x="40" y="65" width="280" height="36" rx="6" fill="#f1f3f5" stroke="#000000" stroke-width="1.3"/>
    <text x="{s1}" y="88" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="12" font-weight="bold">1. CUSTOMER PORTAL (/)</text>
    
    <rect x="330" y="65" width="260" height="36" rx="6" fill="#f1f3f5" stroke="#000000" stroke-width="1.3"/>
    <text x="{s2}" y="88" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="12" font-weight="bold">2. HOME CHEF (/chef)</text>
    
    <rect x="600" y="65" width="280" height="36" rx="6" fill="#f1f3f5" stroke="#000000" stroke-width="1.3"/>
    <text x="{s3}" y="88" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="12" font-weight="bold">3. BACKEND &amp; DISPATCH ENGINE</text>
    
    <rect x="890" y="65" width="310" height="36" rx="6" fill="#f1f3f5" stroke="#000000" stroke-width="1.3"/>
    <text x="{s4}" y="88" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="12" font-weight="bold">4. COURIER &amp; SETTLEMENT (/delivery &amp; /admin)</text>
    
    <!-- Section Header A -->
    <rect x="40" y="112" width="1160" height="26" fill="#e9ecef" stroke="#adb5bd" stroke-width="1"/>
    <text x="630" y="129" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" font-weight="bold" fill="#212529">PART A: ON-DEMAND INSTANT FOOD ORDER LIFECYCLE (STAGES 1 TO 5)</text>
    
    <!-- Swimlane dividers for Part A only (does not cross headers or Part B) -->
    <line x1="325" y1="138" x2="325" y2="1420" stroke="#d0d7de" stroke-width="1.2" stroke-dasharray="5,5" />
    <line x1="595" y1="138" x2="595" y2="1420" stroke="#d0d7de" stroke-width="1.2" stroke-dasharray="5,5" />
    <line x1="885" y1="138" x2="885" y2="1420" stroke="#d0d7de" stroke-width="1.2" stroke-dasharray="5,5" />
    
    <!-- ========================================== -->
    <!-- STAGE 1: ORDER CREATION (CUSTOMER) -->
    <!-- ========================================== -->
    {draw_circle_start(s1, 160)}
    {draw_arrow(s1, 171, s1, 195)}
    
    {draw_box(s1, 218, 230, 44, "Browse Verified Nearby Kitchens", "(Filters: Veg, Jain, Diabetic, Distance)")}
    {draw_arrow(s1, 240, s1, 265)}
    
    {draw_box(s1, 288, 230, 44, "Customize Cooking Health Badges", "(Spice, Oil Level, Jain, Custom Instructions)")}
    {draw_arrow(s1, 310, s1, 335)}
    
    {draw_box(s1, 358, 230, 44, "Select Delivery Address &", "Validate 15 km Radius (Haversine)")}
    {draw_arrow(s1, 380, s1, 405)}
    
    {draw_box(s1, 428, 230, 44, "Multi-Channel Checkout", "(COD / Dynamic NPCI UPI QR / Razorpay)")}
    {draw_arrow(s1, 450, s1, 475)}
    
    {draw_box(s1, 498, 230, 44, "Order Placed & Confidential", "4-Digit Handover OTP Generated")}
    
    <!-- Customer to Chef: WebSocket Broadcast -->
    {draw_path(f"M {s1} 520 L {s1} 545 L {s2} 545 L {s2} 568", "Order Alert via WebSockets", 345, 537, anchor="start")}
    
    <!-- ========================================== -->
    <!-- STAGE 2: KITCHEN OPERATIONS (HOME CHEF) -->
    <!-- ========================================== -->
    {draw_box(s2, 592, 230, 44, "Incoming Order Chime Alert", "(Continuous beep.wav via Web Audio API)")}
    {draw_arrow(s2, 614, s2, 646)}
    
    {draw_decision_empty(s2, 660, 28)}
    
    <!-- Chef Rejects -> Instant Razorpay Refund -->
    {draw_path(f"M {s2-14} 660 L {s1} 660 L {s1} 695", "Order Rejected", 435, 652, anchor="end")}
    {draw_box(s1, 720, 230, 44, "Automated Instant Refund", "via Razorpay REST API to Source")}
    {draw_arrow(s1, 742, s1, 770)}
    {draw_circle_end(s1, 782)}
    
    <!-- Chef Accepts -> Kitchen Preparation -->
    {draw_arrow(s2, 674, s2, 715, label="Order Accepted", label_side="right")}
    {draw_box(s2, 738, 230, 44, "Cook Culinary Dishes per Badges &", "Pack in Sealed Containers")}
    {draw_arrow(s2, 760, s2, 788)}
    
    {draw_box(s2, 810, 230, 42, "Mark Order 'Ready for Pickup'", "in Chef Seller Console")}
    
    <!-- Chef to Backend Dispatch Engine -->
    {draw_path(f"M {s2} 831 L {s2} 850 L {s3} 850 L {s3} 875", "Trigger Dispatch", 615, 842, anchor="start")}
    
    <!-- ========================================== -->
    <!-- STAGE 3: SMART CASCADE DISPATCH (BACKEND) -->
    <!-- ========================================== -->
    {draw_box(s3, 898, 240, 44, "4-Stage Cascade Dispatch Engine", "Stage 1: Active Corridor Batching Check")}
    {draw_arrow(s3, 920, s3, 945)}
    
    {draw_box(s3, 968, 240, 44, "Stage 2: Targeted 25-Second Ping", "to Nearest Idle Courier (within 5 km)")}
    {draw_arrow(s3, 990, s3, 1026)}
    
    {draw_decision_empty(s3, 1040, 28)}
    
    <!-- Courier Timeout / Reject -> Loop back to cascade -->
    {draw_path(f"M {s3-14} 1040 L 608 1040 L 608 968 L {s3-120} 968", "Timeout / Retry", 722, 1032, anchor="end")}
    
    <!-- Courier Accepts -> Handoff to Courier Lane -->
    {draw_path(f"M {s3+14} 1040 L {s4} 1040 L {s4} 1068", "Offer Accepted", 760, 1032, anchor="start")}
    
    <!-- =================================================== -->
    <!-- STAGE 4: COURIER TRANSIT & VERIFIED OTP HANDOVER -->
    <!-- =================================================== -->
    {draw_box(s4, 1092, 250, 44, "Navigate to Kitchen & Pick Up Meal", "(Google Routes API v2 Navigation)")}
    {draw_arrow(s4, 1114, s4, 1140)}
    
    {draw_box(s4, 1162, 250, 44, "Live GPS Streaming over WebSockets", "(HTML5 watchPosition to Customer Screen)")}
    {draw_arrow(s4, 1184, s4, 1210)}
    
    {draw_box(s4, 1232, 250, 44, "Doorstep Arrival & Prompt for", "Confidential 4-Digit Delivery OTP")}
    {draw_arrow(s4, 1254, s4, 1286)}
    
    {draw_decision_empty(s4, 1300, 28)}
    
    <!-- OTP Invalid loop -->
    {draw_path(f"M {s4+14} 1300 L 1180 1300 L 1180 1232 L {s4+125} 1232", "OTP Invalid", 1060, 1292, anchor="start")}
    
    <!-- OTP Verified -> Stage 5 -->
    {draw_arrow(s4, 1314, s4, 1342, label="OTP Verified", label_side="right")}
    
    <!-- ========================================== -->
    <!-- STAGE 5: SETTLEMENT & REVIEW -->
    <!-- ========================================== -->
    {draw_box(s4, 1364, 250, 42, "Delivery Complete, 10% Platform Cut,", "Vendor Net Payout & Customer Rating")}
    {draw_arrow(s4, 1385, s4, 1408)}
    {draw_circle_end(s4, 1420)}
    
    <!-- ========================================================================= -->
    <!-- SECTION HEADER B: SCHEDULED MULTI-TIFFIN MILK-RUN BATCH LOGISTICS (STAGE 6) -->
    <!-- ========================================================================= -->
    <rect x="40" y="1455" width="1160" height="26" fill="#e9ecef" stroke="#adb5bd" stroke-width="1"/>
    <text x="630" y="1472" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" font-weight="bold" fill="#212529">PART B: SCHEDULED MULTI-TIFFIN MILK-RUN BATCH LOGISTICS &amp; DOORSTEP HANDOVER (STAGE 6)</text>
    
    <!-- Swimlane dividers for Part B only -->
    <line x1="325" y1="1481" x2="325" y2="1980" stroke="#d0d7de" stroke-width="1.2" stroke-dasharray="5,5" />
    <line x1="595" y1="1481" x2="595" y2="1980" stroke="#d0d7de" stroke-width="1.2" stroke-dasharray="5,5" />
    <line x1="885" y1="1481" x2="885" y2="1980" stroke="#d0d7de" stroke-width="1.2" stroke-dasharray="5,5" />
    
    <!-- Milk Run Flow Start: Midnight Cron in Backend -->
    {draw_circle_start(s3, 1505)}
    {draw_arrow(s3, 1516, s3, 1538)}
    
    {draw_box(s3, 1560, 250, 42, "Midnight Cron: Scan Active Subscriptions", "Generate subscription_deliveries Roster")}
    {draw_arrow(s3, 1581, s3, 1608)}
    
    {draw_box(s3, 1630, 250, 42, "Cluster by Corridor & Meal Window", "Generate Consolidated delivery_runs Batches")}
    
    <!-- Split after Corridor Clustering: to Chef and to Courier TSP -->
    {draw_path(f"M {s3} 1651 L {s3} 1665 L {s2} 1665 L {s2} 1705", "Daily Prep Roster", 475, 1657, anchor="start")}
    {draw_box(s2, 1728, 230, 44, "Home Chef Prepares Batch Meals &", "Loads Thermal Carrying Carriers")}
    
    <!-- Continue TSP in Backend -->
    {draw_path(f"M {s3} 1665 L {s3} 1705")}
    {draw_box(s3, 1728, 250, 44, "Execute Nearest-Neighbor TSP Heuristic", "optimizeDeliverySequence() (Haversine)")}
    
    <!-- Rider claims batch -->
    {draw_path(f"M {s3} 1750 L {s3} 1770 L {s4} 1770 L {s4} 1795", "Claim Batch Run", 905, 1762, anchor="start")}
    {draw_box(s4, 1818, 250, 44, "Rider Inspects Available Runs on /delivery/runs", "Claims Batch & Navigates to Kitchen Cluster")}
    {draw_arrow(s4, 1840, s4, 1865)}
    
    {draw_box(s4, 1888, 250, 44, "Phase 1: Pick Up Tiffins from Kitchens", "Phase 2: Traverse Sequential Drop-Off Waypoints")}
    
    <!-- Customer Broadcast: In lane 2 center with NO collisions -->
    {draw_path(f"M {s4-125} 1888 L {s4-145} 1888 L {s4-145} 1925 L {s1} 1925 L {s1} 1948", "Real-Time Leg Broadcast", 460, 1917, anchor="middle")}
    {draw_box(s1, 1970, 240, 42, "Queue-Aware Live Tracking Map", "(e.g. 'You are Stop #3, Rider at Stop #2')")}
    
    <!-- Doorstep Drop in Lane 4 -->
    {draw_arrow(s4, 1910, s4, 1948)}
    {draw_box(s4, 1970, 250, 42, "Fulfill Contactless Doorstep Drop (No OTP)", "Bulk Fixed Payout Credited to Courier Wallet")}
    {draw_arrow(s4, 1991, s4, 2012)}
    {draw_circle_end(s4, 2024)}
    
    </svg>"""
    return svg

def main():
    print("Rendering Kravyo End-to-End System Process Flow Diagram with ZERO collisions...")
    svg = build_system_flow_diagram()
    render_svg_to_png(svg, "system_process_flow.html", "system_process_flow.png", width=1280, height=2060)
    print("SUCCESS: system_process_flow.png generated with clean UML layout.")

if __name__ == "__main__":
    main()
