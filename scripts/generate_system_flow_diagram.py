import os
import subprocess

DIAGRAMS_DIR = r"c:\xampp\htdocs\Kravyo\diagrams"
CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
os.makedirs(DIAGRAMS_DIR, exist_ok=True)

def render_svg_to_png(svg_content, html_filename, png_filename, width=1300, height=1400):
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
        <text x="{cx}" y="{cy - 3}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" {weight} fill="#000000">{text1}</text>
        <text x="{cx}" y="{cy + 12}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" {weight} fill="#000000">{text2}</text>
        """
    else:
        t_html = f"""
        <text x="{cx}" y="{cy + 4}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" {weight} fill="#000000">{text1}</text>
        """
    return f"""
    <rect x="{x}" y="{y}" width="{w}" height="{h}" rx="{rx}" ry="{rx}" fill="{fill}" stroke="#000000" stroke-width="1.3" />
    {t_html}
    """

def draw_decision(cx, cy, w, h, text1, text2=None):
    pts = f"{cx},{cy-h/2} {cx+w/2},{cy} {cx},{cy+h/2} {cx-w/2},{cy}"
    if text2:
        t_html = f"""
        <text x="{cx}" y="{cy - 3}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="10.5" fill="#000000">{text1}</text>
        <text x="{cx}" y="{cy + 11}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="10.5" fill="#000000">{text2}</text>
        """
    else:
        t_html = f"""
        <text x="{cx}" y="{cy + 4}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="10.5" fill="#000000">{text1}</text>
        """
    return f"""
    <polygon points="{pts}" fill="#ffffff" stroke="#000000" stroke-width="1.3" />
    {t_html}
    """

def draw_circle_start(cx, cy, r=12):
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
        l_html = f'<text x="{lx}" y="{ly}" font-family="Arial, Helvetica, sans-serif" font-size="9.5" font-weight="bold" fill="#000000" text-anchor="{anchor}">{label}</text>'
    return f'<line x1="{x1}" y1="{y1}" x2="{x2}" y2="{y2}" stroke="#000000" stroke-width="1.2" marker-end="url(#arrow)" />{l_html}'

def draw_line(x1, y1, x2, y2):
    return f'<line x1="{x1}" y1="{y1}" x2="{x2}" y2="{y2}" stroke="#000000" stroke-width="1.2" />'

def build_system_flow_diagram():
    w, h = 1200, 1380
    
    # 4 Swimlane X coordinates
    # Swimlane 1: Customer (left: 40 to 310) -> center: 175
    # Swimlane 2: Home Chef (320 to 590) -> center: 455
    # Swimlane 3: System Engine / Dispatch (600 to 870) -> center: 735
    # Swimlane 4: Delivery Partner & Admin (880 to 1160) -> center: 1020
    s1 = 175
    s2 = 455
    s3 = 735
    s4 = 1020
    
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}
    
    <!-- Title -->
    <text x="600" y="32" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="16" font-weight="bold" fill="#000000">Kravyo Platform: End-to-End System Process Flow</text>
    <text x="600" y="50" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" fill="#444444">(Cross-Actor Operational Flow: Customer, Home Chef, Smart Dispatch Engine, Courier & Admin Settlement)</text>
    
    <!-- Swimlane Headers -->
    <!-- Lane 1: Customer -->
    <rect x="40" y="70" width="270" height="38" rx="6" fill="#f1f3f5" stroke="#000000" stroke-width="1.3"/>
    <text x="{s1}" y="94" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="12" font-weight="bold">1. CUSTOMER</text>
    
    <!-- Lane 2: Home Chef -->
    <rect x="320" y="70" width="270" height="38" rx="6" fill="#f1f3f5" stroke="#000000" stroke-width="1.3"/>
    <text x="{s2}" y="94" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="12" font-weight="bold">2. HOME CHEF (KITCHEN)</text>
    
    <!-- Lane 3: System / Dispatch Engine -->
    <rect x="600" y="70" width="270" height="38" rx="6" fill="#f1f3f5" stroke="#000000" stroke-width="1.3"/>
    <text x="{s3}" y="94" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="12" font-weight="bold">3. BACKEND & DISPATCH</text>
    
    <!-- Lane 4: Delivery Partner & Admin -->
    <rect x="880" y="70" width="280" height="38" rx="6" fill="#f1f3f5" stroke="#000000" stroke-width="1.3"/>
    <text x="{s4}" y="94" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="12" font-weight="bold">4. COURIER & SETTLEMENT</text>
    
    <!-- Swimlane vertical divider lines (dashed) -->
    <line x1="315" y1="110" x2="315" y2="1320" stroke="#cccccc" stroke-width="1" stroke-dasharray="4,4" />
    <line x1="595" y1="110" x2="595" y2="1320" stroke="#cccccc" stroke-width="1" stroke-dasharray="4,4" />
    <line x1="875" y1="110" x2="875" y2="1320" stroke="#cccccc" stroke-width="1" stroke-dasharray="4,4" />
    
    <!-- ========================================== -->
    <!-- STAGE 1: ORDER CREATION (CUSTOMER) -->
    <!-- ========================================== -->
    {draw_circle_start(s1, 135)}
    {draw_arrow(s1, 147, s1, 175)}
    
    {draw_box(s1, 198, 220, 44, "Browse Nearby Verified Kitchens", "(Diet Filter: Veg/Jain/Diabetic)")}
    {draw_arrow(s1, 220, s1, 245)}
    
    {draw_box(s1, 268, 220, 44, "Customize Cooking Preferences", "(Spice, Oil Level, Jain, Custom Notes)")}
    {draw_arrow(s1, 290, s1, 315)}
    
    {draw_box(s1, 338, 220, 44, "Select Delivery Address &", "Validate 15 km Radius (Haversine)")}
    {draw_arrow(s1, 360, s1, 385)}
    
    {draw_box(s1, 408, 220, 44, "Multi-Channel Checkout", "(COD / Dynamic UPI QR / Razorpay)")}
    {draw_arrow(s1, 430, s1, 455)}
    
    {draw_box(s1, 478, 220, 44, "Order Placed & Secret", "4-Digit Handover OTP Generated")}
    
    <!-- Line from Customer to Chef: Order Dispatched -->
    {draw_line(s1, 500, s1, 520)}
    {draw_line(s1, 520, s2, 520)}
    {draw_arrow(s2, 520, s2, 545, label="Order Notify (WebSocket)", label_side="right")}
    
    <!-- ========================================== -->
    <!-- STAGE 2: KITCHEN OPERATIONS (HOME CHEF) -->
    <!-- ========================================== -->
    {draw_box(s2, 568, 220, 44, "Incoming Order Chime Alert", "(Continuous beep.wav via Web Audio)")}
    {draw_arrow(s2, 590, s2, 615)}
    
    {draw_decision(s2, 642, 160, 52, "Chef Accepts", "Order?")}
    
    <!-- Chef Rejects -> Instant Refund -->
    <line x1="{s2 - 80}" y1="642" x2="{s1}" y2="642" stroke="#000000" stroke-width="1.2" />
    <text x="{(s2 - 80 + s1) / 2}" y="632" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="10" font-weight="bold" fill="#000000">No: Reject Order</text>
    {draw_arrow(s1, 642, s1, 691)}
    {draw_box(s1, 715, 220, 44, "Automated Instant Refund", "via Razorpay REST API to Source")}
    {draw_arrow(s1, 737, s1, 765)}
    {draw_circle_end(s1, 777)}
    
    <!-- Chef Accepts -> Cook -->
    {draw_arrow(s2, 668, s2, 715, label="Yes: Accept", label_side="right")}
    {draw_box(s2, 738, 220, 44, "Kitchen Preparation &", "Portion Packaging with Dietary Tags")}
    {draw_arrow(s2, 760, s2, 790)}
    
    {draw_box(s2, 812, 220, 44, "Mark Order 'Ready for Pickup'", "in Chef Order Management Console")}
    
    <!-- Line from Chef to Backend Dispatch Engine -->
    {draw_line(s2, 834, s2, 855)}
    {draw_line(s2, 855, s3, 855)}
    {draw_arrow(s3, 855, s3, 880, label="Trigger DispatchService", label_side="right")}
    
    <!-- ========================================== -->
    <!-- STAGE 3: SMART CASCADE DISPATCH (BACKEND) -->
    <!-- ========================================== -->
    {draw_box(s3, 902, 230, 44, "4-Stage Smart Cascade Dispatch", "Stage 1: Active Corridor Batching")}
    {draw_arrow(s3, 924, s3, 948)}
    
    {draw_box(s3, 970, 230, 44, "Stage 2: 25-Second Targeted Ping", "to Nearest Idle Online Courier")}
    {draw_arrow(s3, 992, s3, 1016)}
    
    {draw_decision(s3, 1042, 160, 50, "Courier Accepts", "in 25s?")}
    
    <!-- Courier Timeout / Reject -> Cascade to 2nd nearest -->
    {draw_line(s3 - 80, 1042, 608, 1042)}
    {draw_line(608, 1042, 608, 970)}
    <text x="604" y="1010" text-anchor="end" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="bold" fill="#000000">No: Cascade / Timeout</text>
    {draw_arrow(608, 970, s3 - 115, 970)}
    
    <!-- Courier Accepts -> Handoff to Courier Lane -->
    {draw_line(s3 + 80, 1042, s4, 1042)}
    {draw_arrow(s4, 1042, s4, 1070, label="Yes: Lock Rider", label_side="right")}
    
    <!-- =================================================== -->
    <!-- STAGE 4: COURIER TRANSIT & VERIFIED OTP HANDOVER -->
    <!-- =================================================== -->
    {draw_box(s4, 1092, 230, 44, "Turn-by-Turn Navigation to Kitchen", "(Google Routes API v2 & Collect Package)")}
    {draw_arrow(s4, 1114, s4, 1138)}
    
    {draw_box(s4, 1160, 230, 44, "Live GPS Streaming via WebSockets", "(Pusher + Animated Bike on Customer Map)")}
    {draw_arrow(s4, 1182, s4, 1206)}
    
    {draw_box(s4, 1228, 230, 44, "Doorstep Arrival & 4-Digit OTP", "Handshake Verification via Rider Portal")}
    {draw_arrow(s4, 1250, s4, 1275)}
    
    <!-- STAGE 5: SETTLEMENT & REVIEW -->
    {draw_box(s4, 1298, 230, 44, "Delivery Complete, 10% Platform Cut,", "Net Vendor Payout & Customer Review")}
    {draw_arrow(s4, 1320, s4, 1345)}
    {draw_circle_end(s4, 1357)}
    
    </svg>"""
    
    render_svg_to_png(svg, "system_process_flow.html", "system_process_flow.png", width=1220, height=1400)

if __name__ == "__main__":
    build_system_flow_diagram()
