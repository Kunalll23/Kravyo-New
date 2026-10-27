import os
import subprocess

DIAGRAMS_DIR = r"c:\xampp\htdocs\Kravyo\diagrams"
CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
os.makedirs(DIAGRAMS_DIR, exist_ok=True)

def render_svg_to_png(svg_content, html_filename, png_filename, width=1050, height=1300):
    html_path = os.path.join(DIAGRAMS_DIR, html_filename)
    png_path = os.path.join(DIAGRAMS_DIR, png_filename)
    
    html_content = f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body {{
    margin: 0;
    padding: 10px;
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
    print(f"Generated: {png_filename}")

DEFS = """
<defs>
  <marker id="arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
    <path d="M 0 1.5 L 9 5 L 0 8.5 z" fill="#000000" />
  </marker>
</defs>
"""

def draw_start_node(cx, cy):
    return f'<circle cx="{cx}" cy="{cy}" r="11" fill="#000000" stroke="#000000" stroke-width="1.3" />'

def draw_end_node(cx, cy):
    return f"""
    <circle cx="{cx}" cy="{cy}" r="14" fill="#ffffff" stroke="#000000" stroke-width="1.3" />
    <circle cx="{cx}" cy="{cy}" r="8" fill="#000000" stroke="#000000" stroke-width="1" />
    """

def draw_merge(cx, cy, size=24):
    half = size / 2
    points = f"{cx},{cy - half} {cx + half},{cy} {cx},{cy + half} {cx - half},{cy}"
    return f'<polygon points="{points}" fill="#ffffff" stroke="#000000" stroke-width="1.3" />'

def draw_fork_bar(x1, y, x2):
    return f'<line x1="{x1}" y1="{y}" x2="{x2}" y2="{y}" stroke="#000000" stroke-width="6" stroke-linecap="round" />'

def draw_join_bar(x1, y, x2):
    return f'<line x1="{x1}" y1="{y}" x2="{x2}" y2="{y}" stroke="#000000" stroke-width="6" stroke-linecap="round" />'

def draw_action(cx, cy, w, h, text1, text2=None, text3=None):
    x = cx - w / 2
    y = cy - h / 2
    if text3:
        t_html = f"""
        <text x="{cx}" y="{cy - 12}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" fill="#000000">{text1}</text>
        <text x="{cx}" y="{cy + 2}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" fill="#000000">{text2}</text>
        <text x="{cx}" y="{cy + 16}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" fill="#000000">{text3}</text>
        """
    elif text2:
        t_html = f"""
        <text x="{cx}" y="{cy - 3}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" fill="#000000">{text1}</text>
        <text x="{cx}" y="{cy + 12}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" fill="#000000">{text2}</text>
        """
    else:
        t_html = f"""
        <text x="{cx}" y="{cy + 4}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" fill="#000000">{text1}</text>
        """
    return f"""
    <rect x="{x}" y="{y}" width="{w}" height="{h}" rx="8" ry="8" fill="#ffffff" stroke="#000000" stroke-width="1.3" />
    {t_html}
    """

def draw_decision(cx, cy, w, h, text1, text2=None):
    p1 = f"{cx},{cy - h/2}"
    p2 = f"{cx + w/2},{cy}"
    p3 = f"{cx},{cy + h/2}"
    p4 = f"{cx - w/2},{cy}"
    points = f"{p1} {p2} {p3} {p4}"
    if text2:
        t_html = f"""
        <text x="{cx}" y="{cy - 2}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" font-weight="bold" fill="#000000">{text1}</text>
        <text x="{cx}" y="{cy + 12}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" font-weight="bold" fill="#000000">{text2}</text>
        """
    else:
        t_html = f"""
        <text x="{cx}" y="{cy + 4}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" font-weight="bold" fill="#000000">{text1}</text>
        """
    return f"""
    <polygon points="{points}" fill="#ffffff" stroke="#000000" stroke-width="1.3" />
    {t_html}
    """

def draw_arrow(x1, y1, x2, y2, label=None, label_side="right"):
    line_html = f'<line x1="{x1}" y1="{y1}" x2="{x2}" y2="{y2}" stroke="#000000" stroke-width="1.2" marker-end="url(#arrow)" />'
    if label:
        mid_x = (x1 + x2) / 2
        mid_y = (y1 + y2) / 2
        offset_x = 12 if label_side == "right" else -12
        anchor = "start" if label_side == "right" else "end"
        line_html += f'<text x="{mid_x + offset_x}" y="{mid_y}" text-anchor="{anchor}" font-family="Arial, Helvetica, sans-serif" font-size="10" font-weight="bold" fill="#000000">[{label}]</text>'
    return line_html

def draw_path(d_str, label=None, lx=0, ly=0, has_arrow=True):
    marker = 'marker-end="url(#arrow)"' if has_arrow else ''
    html = f'<path d="{d_str}" fill="none" stroke="#000000" stroke-width="1.2" {marker} />'
    if label:
        html += f'<text x="{lx}" y="{ly}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="10" font-weight="bold" fill="#000000">[{label}]</text>'
    return html

# =========================================================================
# 1. 3.3.1 Customer Registration and Login Activity Diagram (Guard & Merge)
# =========================================================================
def build_act_1():
    w, h = 1050, 1140
    cx = 480
    cx_left = 240
    cx_right = 720
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}
    <text x="{cx}" y="{h - 30}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#000000">Figure : 3.3.1 Customer Registration and Login Activity Diagram</text>
    
    {draw_start_node(cx, 40)}
    {draw_arrow(cx, 51, cx, 80)}
    {draw_action(cx, 105, 220, 48, "Open Kravyo Web Application")}
    {draw_arrow(cx, 129, cx, 165)}
    {draw_decision(cx, 190, 120, 50, "User Status?")}
    
    <!-- Left Branch: New User (Guarded) -->
    {draw_path(f"M {cx-60} 190 L {cx_left} 190 L {cx_left} 250", "New User", 345, 180)}
    {draw_action(cx_left, 275, 230, 48, "Enter Registration Details", "(Name, Email, Phone, Password)")}
    {draw_arrow(cx_left, 299, cx_left, 335)}
    {draw_action(cx_left, 360, 230, 48, "Validate Form Input &", "Send Email Verification OTP")}
    {draw_arrow(cx_left, 384, cx_left, 420)}
    {draw_action(cx_left, 445, 210, 48, "Submit Verification OTP")}
    {draw_arrow(cx_left, 469, cx_left, 505)}
    {draw_decision(cx_left, 530, 120, 50, "OTP Valid?")}
    
    <!-- OTP Retry Loop -->
    {draw_path(f"M {cx_left+60} 530 L 380 530 L 380 445 L {cx_left+105} 445", "Invalid", 345, 520)}
    
    {draw_arrow(cx_left, 555, cx_left, 595, "Valid", "left")}
    {draw_action(cx_left, 620, 230, 48, "Create Verified Customer Account")}
    {draw_arrow(cx_left, 644, cx_left, 680)}
    {draw_action(cx_left, 705, 230, 48, "Save Delivery Address &", "Location Preferences")}
    
    <!-- Right Branch: Existing User (Guarded) -->
    {draw_path(f"M {cx+60} 190 L {cx_right} 190 L {cx_right} 250", "Existing User", 610, 180)}
    {draw_action(cx_right, 275, 210, 48, "Enter Email & Password")}
    {draw_arrow(cx_right, 299, cx_right, 340)}
    {draw_decision(cx_right, 365, 130, 50, "Forgot Password?")}
    
    <!-- Forgot Password to side box & loopback -->
    {draw_path("M 785 365 L 920 365 L 920 425", "Yes", 845, 355)}
    {draw_action(920, 450, 170, 48, "Verify Reset OTP &", "Set New Password")}
    {draw_path(f"M 920 474 L 920 505 L 1015 505 L 1015 230 L {cx_right} 230 L {cx_right} 250")}
    
    <!-- Normal Login Validation -->
    {draw_arrow(cx_right, 390, cx_right, 435, "No", "left")}
    {draw_action(cx_right, 460, 210, 48, "Verify Email and Password")}
    {draw_arrow(cx_right, 484, cx_right, 525)}
    {draw_decision(cx_right, 550, 130, 50, "Credentials Valid?")}
    
    <!-- Invalid Password retry loop -->
    {draw_path(f"M {cx_right-65} 550 L 550 550 L 550 275 L {cx_right-105} 275", "Invalid", 585, 540)}
    
    <!-- Convergence into Merge Node (UML Merge Diamond) -->
    {draw_path(f"M {cx_left} 729 L {cx_left} 780 L {cx-14} 780")}
    {draw_path(f"M {cx_right} 575 L {cx_right} 780 L {cx+14} 780", "Valid", cx_right+25, 620)}
    {draw_merge(cx, 780, 28)}
    
    {draw_arrow(cx, 794, cx, 825)}
    {draw_action(cx, 850, 230, 48, "Authenticate User Session")}
    {draw_arrow(cx, 874, cx, 910)}
    {draw_action(cx, 935, 240, 48, "Display Customer Homepage &", "Personalized Food Feed")}
    {draw_arrow(cx, 959, cx, 995)}
    {draw_end_node(cx, 1010)}
    
    </svg>"""
    return svg

# =========================================================================
# 2. 3.3.2 Subscription Plan Selection (Guard + Fork & Join)
# =========================================================================
def build_act_2():
    w, h = 900, 1180
    cx = 450
    lx = 270
    rx = 630
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}
    <text x="{cx}" y="{h - 30}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#000000">Figure : 3.3.2 Subscription Plan Selection and Customization Activity Diagram</text>
    
    {draw_start_node(cx, 35)}
    {draw_arrow(cx, 46, cx, 75)}
    {draw_action(cx, 100, 240, 48, "Browse Verified Home Kitchen", "Tiffin Subscriptions")}
    {draw_arrow(cx, 124, cx, 160)}
    {draw_action(cx, 185, 230, 48, "Select Plan Type", "(Weekly or Monthly Package)")}
    {draw_arrow(cx, 209, cx, 245)}
    {draw_action(cx, 270, 240, 48, "Configure Meal Schedule", "(Lunch, Dinner, or Both)")}
    {draw_arrow(cx, 294, cx, 330)}
    {draw_action(cx, 355, 240, 48, "Select Delivery Address &", "Enter Cooking Preferences")}
    {draw_arrow(cx, 379, cx, 415)}
    {draw_action(cx, 440, 230, 48, "Review Plan Cost &", "Subscription Start Date")}
    {draw_arrow(cx, 464, cx, 500)}
    {draw_action(cx, 525, 230, 48, "Submit Subscription Payment", "(Online, UPI, or COD)")}
    {draw_arrow(cx, 549, cx, 585)}
    {draw_decision(cx, 610, 140, 50, "Payment Successful?")}
    
    <!-- Payment Retry Loop -->
    {draw_path(f"M {cx+70} 610 L 630 610 L 630 525 L {cx+115} 525", "Failed", 590, 600)}
    
    {draw_arrow(cx, 635, cx, 675, "Success", "right")}
    {draw_action(cx, 700, 240, 48, "Confirm Subscription Agreement")}
    {draw_arrow(cx, 724, cx, 765)}
    
    <!-- FORK BAR: Concurrent Background Logistics & Customer Confirmation -->
    {draw_fork_bar(lx - 50, 765, rx + 50)}
    <text x="{cx}" y="756" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="bold" fill="#444444">[FORK: Concurrent Operational Setup]</text>
    
    <!-- Thread A: Kitchen Logistics -->
    {draw_arrow(lx, 765, lx, 805)}
    {draw_action(lx, 830, 230, 48, "Generate 30 Daily Delivery Tasks", "in Relational Database")}
    {draw_arrow(lx, 854, lx, 895)}
    {draw_action(lx, 920, 230, 48, "Notify Home Chef of New", "Recurring Tiffin Schedule")}
    {draw_arrow(lx, 944, lx, 990)}
    
    <!-- Thread B: Customer Profile Activation -->
    {draw_arrow(rx, 765, rx, 805)}
    {draw_action(rx, 830, 230, 48, "Activate Subscription Record", "in Customer Account")}
    {draw_arrow(rx, 854, rx, 895)}
    {draw_action(rx, 920, 230, 48, "Dispatch Confirmation Email &", "Digital Invoice to Customer")}
    {draw_arrow(rx, 944, rx, 990)}
    
    <!-- JOIN BAR: Synchronization -->
    {draw_join_bar(lx - 50, 990, rx + 50)}
    <text x="{cx}" y="1005" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="bold" fill="#444444">[JOIN: Threads Synchronized]</text>
    
    {draw_arrow(cx, 990, cx, 1030)}
    {draw_action(cx, 1055, 250, 48, "Subscription Operational &", "Active on Kravyo Platform")}
    {draw_arrow(cx, 1079, cx, 1115)}
    {draw_end_node(cx, 1130)}
    
    </svg>"""
    return svg

# =========================================================================
# 3. 3.3.3 Payment Processing Activity Diagram (Guard + Merge + Fork & Join)
# =========================================================================
def build_act_3():
    w, h = 960, 1220
    cx = 480
    lx = 200
    rx = 760
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}
    <text x="{cx}" y="{h - 30}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#000000">Figure : 3.3.3 Payment Processing Activity Diagram</text>
    
    {draw_start_node(cx, 35)}
    {draw_arrow(cx, 46, cx, 75)}
    {draw_action(cx, 100, 250, 48, "Customer Confirms Items in Cart &", "Selects Delivery Address")}
    {draw_arrow(cx, 124, cx, 160)}
    {draw_decision(cx, 185, 140, 50, "Within 15 km Radius?")}
    
    <!-- Outside Delivery Radius (Guarded) -->
    {draw_arrow(cx+70, 185, rx-110, 185, "No", "right")}
    {draw_action(rx, 185, 220, 48, "Display Delivery Unavailable Alert", "(Outside Service Range)")}
    {draw_path(f"M {rx} 161 L {rx} 100 L {cx+125} 100")}
    
    <!-- Inside Delivery Radius: Payment Method Selection -->
    {draw_arrow(cx, 210, cx, 246, "Yes", "left")}
    {draw_action(cx, 270, 230, 48, "Review Order Total &", "Select Payment Method")}
    {draw_arrow(cx, 294, cx, 330)}
    {draw_decision(cx, 355, 140, 50, "Payment Method?")}
    
    <!-- Branch A: Cash on Delivery (Guarded) -->
    {draw_path(f"M {cx-70} 355 L {lx} 355 L {lx} 420", "COD", 290, 345)}
    {draw_action(lx, 445, 190, 48, "Select Cash on Delivery", "(Pay upon Food Arrival)")}
    {draw_path(f"M {lx} 469 L {lx} 530 L {cx-14} 530", has_arrow=True)}
    
    <!-- Branch B: Scan & Pay via UPI QR (Guarded) -->
    {draw_arrow(cx, 380, cx, 420, "UPI QR", "left")}
    {draw_action(cx, 445, 220, 48, "Scan Dynamic UPI QR Code &", "Approve Payment on Phone")}
    {draw_arrow(cx, 469, cx, 516)}
    
    <!-- Branch C: Pay Online via Gateway (Guarded) -->
    {draw_path(f"M {cx+70} 355 L {rx} 355 L {rx} 420", "Online", 630, 345)}
    {draw_action(rx, 445, 210, 48, "Enter Card or NetBanking Details", "via Payment Gateway")}
    {draw_path(f"M {rx} 469 L {rx} 530 L {cx+14} 530", has_arrow=True)}
    
    <!-- UML Merge Node for Alternative Payment Methods -->
    {draw_merge(cx, 530, 28)}
    
    {draw_arrow(cx, 544, cx, 575)}
    {draw_decision(cx, 600, 140, 50, "Payment Verified?")}
    
    <!-- Payment Failure Retry Loop -->
    {draw_path(f"M {cx+70} 600 L 595 600 L 595 270 L {cx+115} 270", "No", 575, 590)}
    
    <!-- Order Verified: Enter Concurrency -->
    {draw_arrow(cx, 625, cx, 665, "Yes", "left")}
    {draw_action(cx, 690, 240, 48, "Confirm Order & Generate", "Official Order Receipt")}
    {draw_arrow(cx, 714, cx, 755)}
    
    <!-- FORK BAR: Concurrently execute Customer OTP/Tracking & Kitchen Alert -->
    {draw_fork_bar(lx, 755, rx)}
    <text x="{cx}" y="746" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="bold" fill="#444444">[FORK: Simultaneous Customer & Kitchen Notification]</text>
    
    <!-- Thread 1: Customer Lane -->
    {draw_arrow(320, 755, 320, 795)}
    {draw_action(320, 820, 230, 48, "Issue 4-Digit Handover OTP &", "Open Live Route Tracking Map")}
    {draw_arrow(320, 844, 320, 890)}
    
    <!-- Thread 2: Home Chef Lane -->
    {draw_arrow(640, 755, 640, 795)}
    {draw_action(640, 820, 230, 48, "Broadcast Real-Time WebSocket Ping &", "Trigger Audio Chime in Kitchen")}
    {draw_arrow(640, 844, 640, 890)}
    
    <!-- JOIN BAR: Synchronization -->
    {draw_join_bar(lx, 890, rx)}
    <text x="{cx}" y="905" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="bold" fill="#444444">[JOIN: Threads Synchronized]</text>
    
    {draw_arrow(cx, 890, cx, 935)}
    {draw_action(cx, 960, 250, 48, "Order Active in Live Pipeline &", "Ready for Kitchen Preparation")}
    {draw_arrow(cx, 984, cx, 1020)}
    {draw_end_node(cx, 1035)}
    
    </svg>"""
    return svg

# =========================================================================
# 4. 3.3.4 Order Processing & Kitchen Management (PRIMARY FORK & JOIN)
# =========================================================================
def build_act_4():
    w, h = 1120, 1420
    cx = 500
    rx_reject = 940
    lx_cook = 270
    rx_dispatch = 730
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}
    <text x="{cx}" y="{h - 30}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#000000">Figure : 3.3.4 Order Processing and Kitchen Management Activity Diagram</text>
    
    {draw_start_node(cx, 35)}
    {draw_arrow(cx, 46, cx, 75)}
    {draw_action(cx, 100, 240, 48, "Home Chef Receives Incoming Order", "with Alert Notification")}
    {draw_arrow(cx, 124, cx, 160)}
    {draw_decision(cx, 185, 120, 50, "Accept Order?")}
    
    <!-- Chef Rejection Branch with Auto-Refund (Guarded) -->
    {draw_path(f"M {cx+60} 185 L {rx_reject} 185 L {rx_reject} 240", "No", 750, 175)}
    {draw_action(rx_reject, 265, 210, 48, "Chef Rejects Order &", "Cancels Ticket")}
    {draw_arrow(rx_reject, 289, rx_reject, 325)}
    {draw_action(rx_reject, 350, 220, 48, "Initiate Instant Automated", "Refund to Customer")}
    {draw_arrow(rx_reject, 374, rx_reject, 410)}
    {draw_action(rx_reject, 435, 210, 48, "Send Cancellation Notice", "to Customer")}
    {draw_arrow(rx_reject, 459, rx_reject, 495)}
    {draw_end_node(rx_reject, 510)}
    
    <!-- Chef Acceptance Branch (Guarded) -->
    {draw_arrow(cx, 210, cx, 246, "Yes", "left")}
    {draw_action(cx, 270, 230, 48, "Accept Order & Initiate", "Kitchen Preparation")}
    {draw_arrow(cx, 294, cx, 335)}
    
    <!-- ================================================================= -->
    <!-- FORK BAR: CONCURRENT KITCHEN COOKING & COURIER DISPATCH            -->
    <!-- ================================================================= -->
    {draw_fork_bar(lx_cook - 70, 335, rx_dispatch + 70)}
    <text x="{cx}" y="326" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="bold" fill="#444444">[FORK: Parallel Kitchen Cooking & Fleet Courier Dispatch]</text>
    
    <!-- THREAD 1: Kitchen Cooking Operations (Left Lane) -->
    {draw_arrow(lx_cook, 335, lx_cook, 375)}
    {draw_action(lx_cook, 400, 240, 48, "Chef Prepares Culinary Dishes", "per Custom Spice & Dietary Badges")}
    {draw_arrow(lx_cook, 424, lx_cook, 465)}
    {draw_action(lx_cook, 490, 240, 48, "Pack Meals in Sealed Thermal Containers &", "Mark Order Ready for Pickup")}
    {draw_arrow(lx_cook, 514, lx_cook, 580)}
    
    <!-- THREAD 2: Logistics Cascade Courier Dispatch (Right Lane) -->
    {draw_arrow(rx_dispatch, 335, rx_dispatch, 375)}
    {draw_action(rx_dispatch, 400, 240, 48, "System Initiates 4-Stage Cascade", "Courier Dispatch Engine")}
    {draw_arrow(rx_dispatch, 424, rx_dispatch, 455)}
    {draw_decision(rx_dispatch, 480, 150, 50, "Courier on Same Route?")}
    
    <!-- Corridor Batching (Guarded) -->
    {draw_path(f"M {rx_dispatch-75} 480 L 610 480 L 610 520", "Yes", 645, 470)}
    {draw_action(610, 545, 170, 46, "Batch Assign Order to", "En-Route Courier")}
    {draw_path("M 610 568 L 610 580 L 716 580", has_arrow=True)}
    
    <!-- Targeted Broadcast (Guarded) -->
    {draw_path(f"M {rx_dispatch+75} 480 L 850 480 L 850 520", "No", 815, 470)}
    {draw_action(850, 545, 170, 46, "Cascade Offer to Nearest", "Idle Delivery Partner")}
    {draw_path("M 850 568 L 850 580 L 744 580", has_arrow=True)}
    
    <!-- Merge Node for Courier Selection (UML Merge Diamond) -->
    {draw_merge(rx_dispatch, 580, 28)}
    {draw_arrow(rx_dispatch, 594, rx_dispatch, 620)}
    {draw_action(rx_dispatch, 645, 240, 48, "Courier Accepts Mission &", "Navigates to Kitchen Location")}
    {draw_arrow(rx_dispatch, 669, rx_dispatch, 715)}
    
    <!-- Route connection for Kitchen Lane down to Join Bar -->
    {draw_path(f"M {lx_cook} 514 L {lx_cook} 715", has_arrow=True)}
    
    <!-- ================================================================= -->
    <!-- JOIN BAR: SYNCHRONIZATION (Food Ready AND Courier Arrived)         -->
    <!-- ================================================================= -->
    {draw_join_bar(lx_cook - 70, 715, rx_dispatch + 70)}
    <text x="{cx}" y="730" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="bold" fill="#444444">[JOIN: Food Packed AND Courier Arrived at Kitchen]</text>
    
    <!-- Merged Post-Join Execution -->
    {draw_arrow(cx, 715, cx, 760)}
    {draw_action(cx, 785, 250, 48, "Courier Collects Package from Chef &", "Marks Out for Delivery")}
    {draw_arrow(cx, 809, cx, 845)}
    {draw_action(cx, 870, 240, 48, "Stream Real-Time GPS Tracking", "to Customer Device")}
    {draw_arrow(cx, 894, cx, 930)}
    {draw_action(cx, 955, 250, 48, "Courier Arrives at Customer Drop &", "Prompts for 4-Digit Handover OTP")}
    {draw_arrow(cx, 979, cx, 1015)}
    {draw_decision(cx, 1040, 120, 50, "OTP Correct?")}
    
    <!-- OTP Retry Loop -->
    {draw_path(f"M {cx+60} 1040 L 660 1040 L 660 955 L {cx+125} 955", "No", 625, 1030)}
    
    {draw_arrow(cx, 1065, cx, 1105, "Yes", "left")}
    {draw_action(cx, 1130, 250, 48, "Complete Delivery Handover &", "Credit Payout to Chef and Courier")}
    {draw_arrow(cx, 1154, cx, 1190)}
    {draw_action(cx, 1215, 230, 48, "Customer Submits Food Rating", "and Service Review")}
    {draw_arrow(cx, 1239, cx, 1275)}
    {draw_end_node(cx, 1290)}
    
    </svg>"""
    return svg

# =========================================================================
# 5. 3.3.5 Daily Menu and Inventory Management (Guard & Merge)
# =========================================================================
def build_act_5():
    w, h = 950, 1140
    cx = 475
    lx = 240
    rx = 710
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}
    <text x="{cx}" y="{h - 30}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#000000">Figure : 3.3.5 Daily Menu and Inventory Management Activity Diagram</text>
    
    {draw_start_node(cx, 40)}
    {draw_arrow(cx, 51, cx, 80)}
    {draw_action(cx, 105, 220, 48, "Home Chef Logs in to", "Seller Dashboard")}
    {draw_arrow(cx, 129, cx, 165)}
    {draw_action(cx, 190, 220, 48, "Open Menu Management Section")}
    {draw_arrow(cx, 214, cx, 250)}
    {draw_decision(cx, 275, 130, 50, "Action Required?")}
    
    <!-- Add New Dish Branch (Guarded) -->
    {draw_path(f"M {cx-65} 275 L {lx} 275 L {lx} 335", "Add New Dish", 330, 265)}
    {draw_action(lx, 360, 210, 48, "Enter Dish Details, Category,", "Price & Preparation Time")}
    {draw_arrow(lx, 384, lx, 420)}
    {draw_action(lx, 445, 210, 48, "Select Dietary Preferences", "(Vegetarian, Jain, Spicy Level)")}
    {draw_arrow(lx, 469, lx, 505)}
    {draw_action(lx, 530, 210, 48, "Upload Dish Photo &", "Set Daily Available Portions")}
    {draw_path(f"M {lx} 554 L {lx} 605 L {cx-14} 605", has_arrow=True)}
    
    <!-- Edit Existing Dish Branch (Guarded) -->
    {draw_path(f"M {cx+65} 275 L {rx} 275 L {rx} 335", "Edit Dish", 620, 265)}
    {draw_action(rx, 360, 210, 48, "Select Existing Dish", "from Active Menu")}
    {draw_arrow(rx, 384, rx, 420)}
    {draw_action(rx, 445, 210, 48, "Update Price, Description", "or Portion Quantity")}
    {draw_arrow(rx, 469, rx, 505)}
    {draw_action(rx, 530, 210, 48, "Toggle Stock Status", "(In Stock or Sold Out)")}
    {draw_path(f"M {rx} 554 L {rx} 605 L {cx+14} 605", has_arrow=True)}
    
    <!-- UML Merge Node -->
    {draw_merge(cx, 605, 28)}
    
    {draw_arrow(cx, 619, cx, 650)}
    {draw_decision(cx, 675, 130, 50, "Confirm Changes?")}
    
    <!-- Discard Branch (Guarded) -->
    {draw_path(f"M {cx+65} 675 L {rx} 675 L {rx} 735", "No", 620, 665)}
    {draw_action(rx, 760, 200, 45, "Discard Updates & Revert Form")}
    {draw_arrow(rx, 783, rx, 820)}
    {draw_end_node(rx, 835)}
    
    <!-- Save Branch (Guarded) -->
    {draw_arrow(cx, 700, cx, 740, "Yes", "left")}
    {draw_action(cx, 765, 230, 48, "Save Dish Information")}
    {draw_arrow(cx, 789, cx, 825)}
    {draw_action(cx, 850, 240, 48, "Publish Updates to Public", "Customer Storefront")}
    {draw_arrow(cx, 874, cx, 910)}
    {draw_action(cx, 935, 260, 48, "Automatically Decrement Portions on Orders;", "Mark Sold Out when Zero")}
    {draw_arrow(cx, 959, cx, 995)}
    {draw_end_node(cx, 1010)}
    
    </svg>"""
    return svg

# =========================================================================
# 6. 3.3.6 Home Chef Onboarding and FSSAI Verification (Guard + Fork & Join)
# =========================================================================
def build_act_6():
    w, h = 1000, 1170
    cx = 380
    rx = 760
    lx_f = 250
    rx_f = 510
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}
    <text x="{w/2}" y="{h - 30}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#000000">Figure : 3.3.6 Home Chef Onboarding and FSSAI Verification Activity Diagram</text>
    
    {draw_start_node(cx, 40)}
    {draw_arrow(cx, 51, cx, 80)}
    {draw_action(cx, 105, 230, 48, "Submit Home Chef Application", "with Contact Details")}
    {draw_arrow(cx, 129, cx, 165)}
    {draw_action(cx, 190, 240, 48, "Enter Kitchen Profile, Story &", "Cooking Location Address")}
    {draw_arrow(cx, 214, cx, 250)}
    {draw_action(cx, 275, 240, 48, "Upload 14-Digit FSSAI Certificate", "and Kitchen Hygiene Documents")}
    {draw_arrow(cx, 299, cx, 335)}
    {draw_action(cx, 360, 240, 48, "Submit Profile for Administrative Review")}
    {draw_arrow(cx, 384, cx, 420)}
    {draw_action(cx, 445, 240, 48, "Platform Administrator Audits", "FSSAI Validity and Food Safety")}
    {draw_arrow(cx, 469, cx, 505)}
    {draw_decision(cx, 530, 140, 50, "Documents Approved?")}
    
    <!-- Rejection Loop (Guarded) -->
    {draw_path(f"M {cx+70} 530 L {rx} 530 L {rx} 590", "No", 575, 520)}
    {draw_action(rx, 615, 220, 48, "Record Rejection Reason", "(e.g., Expired or Mismatched FSSAI)")}
    {draw_arrow(rx, 639, rx, 675)}
    {draw_action(rx, 700, 220, 48, "Send Correction Notice to Chef", "with Re-Upload Instructions")}
    {draw_path(f"M {rx} 724 L {rx} 775 L 910 775 L 910 275 L {cx+120} 275")}
    
    <!-- Approval Branch (Guarded) -->
    {draw_arrow(cx, 555, cx, 595, "Yes", "left")}
    {draw_action(cx, 620, 240, 48, "Approve Kitchen Application")}
    {draw_arrow(cx, 644, cx, 680)}
    
    <!-- FORK BAR: Badge assignment and Credentials dispatch in parallel -->
    {draw_fork_bar(lx_f - 40, 680, rx_f + 40)}
    <text x="{cx}" y="672" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="bold" fill="#444444">[FORK: Parallel Activation Tasks]</text>
    
    {draw_arrow(lx_f, 680, lx_f, 720)}
    {draw_action(lx_f, 745, 210, 48, "Award Verified Kitchen Badge &", "Food Safety Trust Seal")}
    {draw_arrow(lx_f, 769, lx_f, 810)}
    
    {draw_arrow(rx_f, 680, rx_f, 720)}
    {draw_action(rx_f, 745, 210, 48, "Send Onboarding Welcome Notice &", "Portal Login Confirmation")}
    {draw_arrow(rx_f, 769, rx_f, 810)}
    
    <!-- JOIN BAR -->
    {draw_join_bar(lx_f - 40, 810, rx_f + 40)}
    <text x="{cx}" y="824" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="bold" fill="#444444">[JOIN: Setup Synchronized]</text>
    
    {draw_arrow(cx, 810, cx, 850)}
    {draw_action(cx, 875, 250, 48, "Activate Kitchen Storefront to", "Publish Menu and Accept Orders")}
    {draw_arrow(cx, 899, cx, 935)}
    {draw_end_node(cx, 950)}
    
    </svg>"""
    return svg

# =========================================================================
# 7. 3.3.7 Delivery Partner Onboarding, Duty & Mission (Guard + Fork & Join)
# =========================================================================
def build_act_7():
    w, h = 900, 1220
    cx = 450
    lx_w = 270
    rx_w = 630
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}
    <text x="{cx}" y="{h - 30}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#000000">Figure : 3.3.7 Delivery Partner Onboarding, Duty & Mission Activity Diagram</text>
    
    {draw_start_node(cx, 35)}
    {draw_arrow(cx, 46, cx, 75)}
    {draw_action(cx, 100, 240, 48, "Submit Delivery Partner Profile,", "Vehicle Details & Driving License")}
    {draw_arrow(cx, 124, cx, 160)}
    {draw_action(cx, 185, 240, 48, "Administrator Verifies License &", "Activates Delivery Account")}
    {draw_arrow(cx, 209, cx, 245)}
    {draw_action(cx, 270, 230, 48, "Delivery Partner Logs In &", "Toggles Duty Status to Online")}
    {draw_arrow(cx, 294, cx, 330)}
    {draw_action(cx, 355, 240, 48, "Receive Delivery Request Alert", "for Nearby Home Kitchen")}
    {draw_arrow(cx, 379, cx, 415)}
    {draw_decision(cx, 440, 130, 50, "Accept Delivery?")}
    
    <!-- Decline / Timeout Loop (Guarded) -->
    {draw_path(f"M {cx+65} 440 L 645 440 L 645 355 L {cx+120} 355", "Decline / Timeout", 575, 430)}
    
    {draw_arrow(cx, 465, cx, 505, "Accept", "left")}
    {draw_action(cx, 530, 240, 48, "Follow Navigation Route", "to Home Kitchen")}
    {draw_arrow(cx, 554, cx, 590)}
    {draw_action(cx, 615, 240, 48, "Collect Prepared Food Package &", "Mark Out for Delivery")}
    {draw_arrow(cx, 639, cx, 675)}
    {draw_action(cx, 700, 250, 48, "Stream Real-Time GPS Coordinates", "to Customer Tracking Screen")}
    {draw_arrow(cx, 724, cx, 760)}
    {draw_action(cx, 785, 240, 48, "Arrive at Customer Location &", "Request 4-Digit Handover OTP")}
    {draw_arrow(cx, 809, cx, 845)}
    {draw_decision(cx, 870, 120, 50, "OTP Correct?")}
    
    <!-- OTP Retry Loop (Guarded) -->
    {draw_path(f"M {cx+60} 870 L 590 870 L 590 785 L {cx+120} 785", "Invalid", 555, 860)}
    
    <!-- OTP Verified: Concurrency -->
    {draw_arrow(cx, 895, cx, 935, "Valid", "left")}
    
    <!-- FORK BAR: Payout to courier and Notification to customer in parallel -->
    {draw_fork_bar(lx_w - 40, 935, rx_w + 40)}
    <text x="{cx}" y="926" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="bold" fill="#444444">[FORK: Handover Settlement & Notification]</text>
    
    {draw_arrow(lx_w, 935, lx_w, 975)}
    {draw_action(lx_w, 1000, 230, 48, "Credit Trip Fee & Distance", "Incentive to Partner Wallet")}
    {draw_arrow(lx_w, 1024, lx_w, 1065)}
    
    {draw_arrow(rx_w, 935, rx_w, 975)}
    {draw_action(rx_w, 1000, 230, 48, "Send Delivery Confirmation SMS &", "Prompt Customer Review")}
    {draw_arrow(rx_w, 1024, rx_w, 1065)}
    
    <!-- JOIN BAR -->
    {draw_join_bar(lx_w - 40, 1065, rx_w + 40)}
    <text x="{cx}" y="1078" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="bold" fill="#444444">[JOIN: Handover Finalized]</text>
    
    {draw_arrow(cx, 1065, cx, 1105)}
    {draw_action(cx, 1130, 240, 48, "Complete Delivery Mission &", "Reset Courier Status to Idle")}
    {draw_arrow(cx, 1154, cx, 1190)}
    {draw_end_node(cx, 1205)}
    
    </svg>"""
    return svg

# =========================================================================
# 8. 3.3.8 Dispute Resolution & Refund (Guard + Merge + Fork & Join)
# =========================================================================
def build_act_8():
    w, h = 1020, 1180
    cx = 380
    rx = 780
    lx_r = 240
    rx_r = 520
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}
    <text x="{w/2}" y="{h - 30}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#000000">Figure : 3.3.8 Dispute Resolution and Order Refund Activity Diagram</text>
    
    {draw_start_node(cx, 40)}
    {draw_arrow(cx, 51, cx, 80)}
    {draw_action(cx, 105, 230, 48, "Customer Accesses Order History &", "Clicks Report an Issue")}
    {draw_arrow(cx, 129, cx, 165)}
    {draw_action(cx, 190, 240, 48, "Select Issue Category & Upload", "Photo Evidence of Food Defect")}
    {draw_arrow(cx, 214, cx, 250)}
    {draw_action(cx, 275, 240, 48, "Submit Grievance to Platform", "Support Queue")}
    {draw_arrow(cx, 299, cx, 335)}
    {draw_action(cx, 360, 250, 48, "Administrator Reviews Claim Details,", "Timestamps and Preparation Notes")}
    {draw_arrow(cx, 384, cx, 420)}
    {draw_decision(cx, 445, 130, 50, "Grievance Valid?")}
    
    <!-- Dismissal Branch (Guarded) -->
    {draw_path(f"M {cx+65} 445 L {rx} 445 L {rx} 510", "No", 575, 435)}
    {draw_action(rx, 535, 210, 48, "Dismiss Claim with", "Detailed Justification")}
    {draw_arrow(rx, 559, rx, 595)}
    {draw_action(rx, 620, 210, 48, "Send Written Explanation", "to Customer")}
    {draw_path(f"M {rx} 644 L {rx} 805 L {cx+14} 805", has_arrow=True)}
    
    <!-- Valid Refund Branch (Guarded) -->
    {draw_arrow(cx, 470, cx, 510, "Yes", "left")}
    {draw_action(cx, 535, 240, 48, "Approve Customer Claim for Refund")}
    {draw_arrow(cx, 559, cx, 595)}
    
    <!-- FORK BAR: Razorpay refund and Kitchen deduction concurrently -->
    {draw_fork_bar(lx_r - 40, 595, rx_r + 40)}
    <text x="{cx}" y="586" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="bold" fill="#444444">[FORK: Simultaneous Financial Adjustments]</text>
    
    {draw_arrow(lx_r, 595, lx_r, 635)}
    {draw_action(lx_r, 660, 220, 48, "Execute Instant Razorpay REST API", "Refund to Source Account")}
    {draw_arrow(lx_r, 684, lx_r, 725)}
    
    {draw_arrow(rx_r, 595, rx_r, 635)}
    {draw_action(rx_r, 660, 220, 48, "Apply Quality Penalty Deduction", "to Kitchen Settlement Ledger")}
    {draw_arrow(rx_r, 684, rx_r, 725)}
    
    <!-- JOIN BAR -->
    {draw_join_bar(lx_r - 40, 725, rx_r + 40)}
    <text x="{cx}" y="738" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="bold" fill="#444444">[JOIN: Accounts Reconciled]</text>
    
    {draw_arrow(cx, 725, cx, 791)}
    
    <!-- UML Merge Node combining Valid Refund path and Dismissal path -->
    {draw_merge(cx, 805, 28)}
    
    {draw_arrow(cx, 819, cx, 850)}
    {draw_action(cx, 875, 240, 48, "Send Resolution Notification", "to Customer and Home Chef")}
    {draw_arrow(cx, 899, cx, 935)}
    {draw_action(cx, 960, 220, 48, "Close Support Ticket")}
    {draw_arrow(cx, 984, cx, 1020)}
    {draw_end_node(cx, 1035)}
    
    </svg>"""
    return svg

# =========================================================================
# 9. 3.3.9 Platform Analytics and Commission Management (Guard & Merge)
# =========================================================================
def build_act_9():
    w, h = 1000, 1150
    cx = 380
    rx = 760
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}
    <text x="{w/2}" y="{h - 30}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#000000">Figure : 3.3.9 Platform Analytics and Commission Management Activity Diagram</text>
    
    {draw_start_node(cx, 40)}
    {draw_arrow(cx, 51, cx, 80)}
    {draw_action(cx, 105, 220, 48, "Administrator Logs in to", "Admin Command Console")}
    {draw_arrow(cx, 129, cx, 165)}
    {draw_action(cx, 190, 230, 48, "Navigate to Financial &", "Performance Analytics")}
    {draw_arrow(cx, 214, cx, 250)}
    {draw_action(cx, 275, 240, 48, "Select Reporting Period", "(Daily, Weekly, Monthly, or Custom)")}
    {draw_arrow(cx, 299, cx, 335)}
    {draw_action(cx, 360, 240, 48, "Fetch Sales, Subscriptions &", "Delivery Transaction Data")}
    {draw_arrow(cx, 384, cx, 420)}
    {draw_action(cx, 445, 240, 48, "Calculate Gross Merchandise Value", "and Platform Commission")}
    {draw_arrow(cx, 469, cx, 505)}
    {draw_action(cx, 530, 240, 48, "Compute Net Kitchen Payouts &", "Delivery Partner Fee Balances")}
    {draw_arrow(cx, 554, cx, 590)}
    {draw_decision(cx, 615, 150, 50, "Export Financial Report?")}
    
    <!-- Export Branch (Guarded) -->
    {draw_path(f"M {cx+75} 615 L {rx} 615 L {rx} 675", "Yes", 585, 605)}
    {draw_action(rx, 700, 220, 48, "Generate Detailed Statement", "with Commission Breakdown")}
    {draw_arrow(rx, 724, rx, 760)}
    {draw_action(rx, 785, 220, 48, "Download Financial CSV Statement")}
    {draw_path(f"M {rx} 809 L {rx} 830 L {cx+14} 830", has_arrow=True)}
    
    <!-- On-screen view (Guarded) -->
    {draw_arrow(cx, 640, cx, 680, "No", "left")}
    {draw_action(cx, 705, 240, 48, "Render Performance Charts &", "Top Kitchen Growth Trends")}
    {draw_arrow(cx, 729, cx, 816)}
    
    <!-- UML Merge Node -->
    {draw_merge(cx, 830, 28)}
    
    {draw_arrow(cx, 844, cx, 875)}
    {draw_action(cx, 900, 240, 48, "Review Platform Revenue and Growth")}
    {draw_arrow(cx, 924, cx, 960)}
    {draw_end_node(cx, 975)}
    
    </svg>"""
    return svg

def main():
    print("Re-generating all 9 Activity Diagrams with textbook UML Guards, Merges, Forks, and Joins...")
    render_svg_to_png(build_act_1(), "activity_3_3_1_customer_auth.html", "activity_3_3_1_customer_auth.png", 1070, 1160)
    render_svg_to_png(build_act_2(), "activity_3_3_2_subscription.html", "activity_3_3_2_subscription.png", 920, 1200)
    render_svg_to_png(build_act_3(), "activity_3_3_3_payment.html", "activity_3_3_3_payment.png", 980, 1240)
    render_svg_to_png(build_act_4(), "activity_3_3_4_order_processing.html", "activity_3_3_4_order_processing.png", 1140, 1440)
    render_svg_to_png(build_act_5(), "activity_3_3_5_menu_inventory.html", "activity_3_3_5_menu_inventory.png", 970, 1160)
    render_svg_to_png(build_act_6(), "activity_3_3_6_chef_onboarding.html", "activity_3_3_6_chef_onboarding.png", 1020, 1190)
    render_svg_to_png(build_act_7(), "activity_3_3_7_delivery_partner.html", "activity_3_3_7_delivery_partner.png", 920, 1240)
    render_svg_to_png(build_act_8(), "activity_3_3_8_dispute_refund.html", "activity_3_3_8_dispute_refund.png", 1040, 1200)
    render_svg_to_png(build_act_9(), "activity_3_3_9_analytics_commission.html", "activity_3_3_9_analytics_commission.png", 1020, 1170)
    print("All 9 Activity Diagrams successfully refreshed with verified Guard, Fork, Join, and Merge nodes!")

if __name__ == "__main__":
    main()
