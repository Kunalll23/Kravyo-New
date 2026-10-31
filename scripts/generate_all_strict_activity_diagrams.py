import os
import subprocess

DIAGRAMS_DIR = r"c:\xampp\htdocs\Kravyo\diagrams"
CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
os.makedirs(DIAGRAMS_DIR, exist_ok=True)

def render_svg_to_png(svg_content, html_filename, png_filename, width=1000, height=1250):
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
    print(f"Generated simplified diagram: {png_filename}")

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

def draw_decision(cx, cy, size=28):
    half = size / 2
    points = f"{cx},{cy - half} {cx + half},{cy} {cx},{cy + half} {cx - half},{cy}"
    return f'<polygon points="{points}" fill="#ffffff" stroke="#000000" stroke-width="1.3" />'

def draw_merge(cx, cy, size=28):
    half = size / 2
    points = f"{cx},{cy - half} {cx + half},{cy} {cx},{cy + half} {cx - half},{cy}"
    return f'<polygon points="{points}" fill="#ffffff" stroke="#000000" stroke-width="1.3" />'

def draw_fork_bar(x1, y, x2, label=None):
    html = f'<line x1="{x1}" y1="{y}" x2="{x2}" y2="{y}" stroke="#000000" stroke-width="6" stroke-linecap="round" />'
    if label:
        html += f'\n    <text x="{x1 + 8}" y="{y - 8}" text-anchor="start" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="bold" fill="#444444">[{label}]</text>'
    return html

def draw_join_bar(x1, y, x2, label=None):
    html = f'<line x1="{x1}" y1="{y}" x2="{x2}" y2="{y}" stroke="#000000" stroke-width="6" stroke-linecap="round" />'
    if label:
        html += f'\n    <text x="{x1 + 8}" y="{y + 16}" text-anchor="start" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="bold" fill="#444444">[{label}]</text>'
    return html

def draw_action(cx, cy, w, h, text1, text2=None):
    x = cx - w / 2
    y = cy - h / 2
    if text2:
        t_html = f"""
        <text x="{cx}" y="{cy - 2}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" fill="#000000">{text1}</text>
        <text x="{cx}" y="{cy + 13}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" fill="#000000">{text2}</text>
        """
    else:
        t_html = f"""
        <text x="{cx}" y="{cy + 4}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" fill="#000000">{text1}</text>
        """
    return f"""
    <rect x="{x}" y="{y}" width="{w}" height="{h}" rx="8" ry="8" fill="#ffffff" stroke="#000000" stroke-width="1.3" />
    {t_html}
    """

def draw_arrow(x1, y1, x2, y2, label=None, label_side="right"):
    line_html = f'<line x1="{x1}" y1="{y1}" x2="{x2}" y2="{y2}" stroke="#000000" stroke-width="1.2" marker-end="url(#arrow)" />'
    if label:
        mid_x = (x1 + x2) / 2
        mid_y = (y1 + y2) / 2
        offset_x = 10 if label_side == "right" else -10
        anchor = "start" if label_side == "right" else "end"
        line_html += f'<text x="{mid_x + offset_x}" y="{mid_y}" text-anchor="{anchor}" font-family="Arial, Helvetica, sans-serif" font-size="10" font-weight="bold" fill="#000000">[{label}]</text>'
    return line_html

def draw_path(d_str, label=None, lx=0, ly=0, has_arrow=True, anchor="middle"):
    marker = 'marker-end="url(#arrow)"' if has_arrow else ''
    html = f'<path d="{d_str}" fill="none" stroke="#000000" stroke-width="1.2" {marker} />'
    if label:
        html += f'<text x="{lx}" y="{ly}" text-anchor="{anchor}" font-family="Arial, Helvetica, sans-serif" font-size="10" font-weight="bold" fill="#000000">[{label}]</text>'
    return html

# =========================================================================
# 1. Customer Registration & Login Activity Diagram
# =========================================================================
def build_act_1():
    w, h = 1020, 1100
    cx = 480
    cx_left = 240
    cx_right = 720
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}

    {draw_start_node(cx, 40)}
    {draw_arrow(cx, 51, cx, 80)}
    {draw_action(cx, 102, 200, 44, "Open Kravyo App")}
    {draw_arrow(cx, 124, cx, 171)}
    {draw_decision(cx, 185, 28)}
    
    <!-- Left Branch: New User -->
    {draw_path(f"M {cx-14} 185 L {cx_left} 185 L {cx_left} 235", "New User", 270, 175, anchor="start")}
    {draw_action(cx_left, 258, 200, 44, "Enter Signup Details")}
    {draw_arrow(cx_left, 280, cx_left, 315)}
    {draw_action(cx_left, 338, 200, 44, "Validate Form & Send OTP")}
    {draw_arrow(cx_left, 360, cx_left, 395)}
    {draw_action(cx_left, 418, 190, 44, "Submit Email OTP")}
    {draw_arrow(cx_left, 440, cx_left, 476)}
    {draw_decision(cx_left, 490, 28)}
    
    <!-- OTP Retry Loop -->
    {draw_path(f"M {cx_left+14} 490 L 380 490 L 380 418 L {cx_left+95} 418", "OTP Invalid", 265, 480, anchor="start")}
    
    {draw_arrow(cx_left, 504, cx_left, 550, "OTP Verified", "left")}
    {draw_action(cx_left, 572, 210, 44, "Create Customer Account")}
    {draw_arrow(cx_left, 594, cx_left, 630)}
    {draw_action(cx_left, 652, 210, 44, "Save Address & Preferences")}
    
    <!-- Right Branch: Existing User -->
    {draw_path(f"M {cx+14} 185 L {cx_right} 185 L {cx_right} 235", "Existing User", 520, 175, anchor="start")}
    {draw_action(cx_right, 258, 190, 44, "Enter Email & Password")}
    {draw_arrow(cx_right, 280, cx_right, 326)}
    {draw_decision(cx_right, 340, 28)}
    
    <!-- Forgot Password Branch -->
    {draw_path("M 734 340 L 910 340 L 910 400", "Forgot Password", 750, 330, anchor="start")}
    {draw_action(910, 422, 160, 44, "Verify OTP & Reset Password")}
    {draw_path(f"M 910 444 L 910 475 L 1000 475 L 1000 258 L {cx_right+95} 258", "Reset Done", 995, 248, anchor="end")}
    
    <!-- Standard Login Branch -->
    {draw_arrow(cx_right, 354, cx_right, 400, "Standard Login", "left")}
    {draw_action(cx_right, 422, 190, 44, "Verify Credentials")}
    {draw_arrow(cx_right, 444, cx_right, 496)}
    {draw_decision(cx_right, 510, 28)}
    
    <!-- Invalid Credentials retry loop -->
    {draw_path(f"M {cx_right-14} 510 L 530 510 L 530 258 L {cx_right-95} 258", "Invalid", 690, 500, anchor="end")}
    
    <!-- JOIN BAR -->
    {draw_arrow(cx_left, 674, cx_left, 740)}
    {draw_arrow(cx_right, 524, cx_right, 740, "Valid", "right")}
    {draw_join_bar(80, 740, cx_right + 30, "User Verified")}
    
    {draw_arrow(cx, 740, cx, 795)}
    {draw_action(cx, 818, 200, 44, "Start User Session")}
    {draw_arrow(cx, 840, cx, 875)}
    {draw_action(cx, 898, 210, 44, "Show Customer Feed")}
    {draw_arrow(cx, 920, cx, 955)}
    {draw_end_node(cx, 970)}
    
    </svg>"""
    return svg

# =========================================================================
# 2. Tiffin Meal Subscription Life Cycle
# =========================================================================
def build_act_2():
    w, h = 900, 1120
    cx = 450
    lx = 260
    rx = 640
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}

    {draw_start_node(cx, 40)}
    {draw_arrow(cx, 51, cx, 80)}
    {draw_action(cx, 102, 220, 44, "Browse Tiffin Plans")}
    {draw_arrow(cx, 124, cx, 155)}
    {draw_action(cx, 178, 220, 44, "Choose Dietary Preference")}
    {draw_arrow(cx, 200, cx, 235)}
    {draw_action(cx, 258, 220, 44, "Set Schedule & Timings")}
    {draw_arrow(cx, 280, cx, 315)}
    {draw_action(cx, 338, 220, 44, "Select Delivery Address")}
    {draw_arrow(cx, 360, cx, 395)}
    {draw_action(cx, 418, 220, 44, "Calculate Plan Total")}
    {draw_arrow(cx, 440, cx, 475)}
    {draw_action(cx, 498, 220, 44, "Proceed to Payment")}
    {draw_arrow(cx, 520, cx, 566)}
    {draw_decision(cx, 580, 28)}
    
    <!-- Payment Failure Branch -->
    {draw_path(f"M {cx+14} 580 L 660 580 L 660 498 L {cx+110} 498", "Payment Failed", 475, 570, anchor="start")}
    
    <!-- Payment Success Branch -->
    {draw_arrow(cx, 594, cx, 640, "Paid", "left")}
    {draw_action(cx, 662, 220, 44, "Activate Subscription")}
    {draw_arrow(cx, 684, cx, 725)}
    
    <!-- FORK BAR -->
    {draw_fork_bar(120, 725, rx + 50, "Setup Delivery Operations")}
    
    <!-- Thread A: Kitchen Roster -->
    {draw_arrow(lx, 725, lx, 765)}
    {draw_action(lx, 788, 220, 44, "Add to Kitchen Daily Roster")}
    {draw_arrow(lx, 810, lx, 850)}
    
    <!-- Thread B: Delivery Schedules -->
    {draw_arrow(rx, 725, rx, 765)}
    {draw_action(rx, 788, 220, 44, "Schedule Daily Deliveries")}
    {draw_arrow(rx, 810, rx, 850)}
    
    <!-- JOIN BAR -->
    {draw_join_bar(120, 850, rx + 50, "Plan Synchronized")}
    
    {draw_arrow(cx, 850, cx, 895)}
    {draw_action(cx, 918, 220, 44, "Show Confirmation Summary")}
    {draw_arrow(cx, 940, cx, 975)}
    {draw_end_node(cx, 990)}
    
    </svg>"""
    return svg

# =========================================================================
# 3. Cart Checkout & Payment Verification
# =========================================================================
def build_act_3():
    w, h = 960, 1140
    cx = 480
    lx = 220
    rx = 740
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}

    {draw_start_node(cx, 40)}
    {draw_arrow(cx, 51, cx, 80)}
    {draw_action(cx, 102, 210, 44, "Review Cart Items")}
    {draw_arrow(cx, 124, cx, 155)}
    {draw_action(cx, 178, 210, 44, "Select Delivery Address")}
    {draw_arrow(cx, 200, cx, 235)}
    {draw_action(cx, 258, 210, 44, "Calculate Bill Total")}
    {draw_arrow(cx, 280, cx, 321)}
    {draw_decision(cx, 335, 28)}
    
    <!-- Path 1: UPI -->
    {draw_path(f"M {cx-14} 335 L {lx} 335 L {lx} 385", "UPI QR", 240, 325, anchor="start")}
    {draw_action(lx, 408, 180, 44, "Generate UPI QR Code")}
    {draw_arrow(lx, 430, lx, 480)}
    
    <!-- Path 2: Card / Netbanking -->
    {draw_arrow(cx, 349, cx, 385, "Card / Netbanking", "left")}
    {draw_action(cx, 408, 180, 44, "Authorize Online Payment")}
    {draw_arrow(cx, 430, cx, 480)}
    
    <!-- Path 3: COD -->
    {draw_path(f"M {cx+14} 335 L {rx} 335 L {rx} 385", "Cash on Delivery", 630, 325, anchor="start")}
    {draw_action(rx, 408, 180, 44, "Select Cash on Delivery")}
    {draw_arrow(rx, 430, rx, 480)}
    
    <!-- JOIN BAR for Payment Methods -->
    {draw_join_bar(80, 480, rx + 40, "Payment Synchronized")}
    {draw_arrow(cx, 480, cx, 526)}
    {draw_decision(cx, 540, 28)}
    
    <!-- Payment Failure Retry Loop -->
    {draw_path(f"M {cx+14} 540 L 870 540 L 870 258 L {cx+105} 258", "Payment Failed", 508, 530, anchor="start")}
    
    <!-- Order Verified -->
    {draw_arrow(cx, 554, cx, 600, "Payment Confirmed", "left")}
    {draw_action(cx, 622, 210, 44, "Confirm Order & Receipt")}
    {draw_arrow(cx, 644, cx, 680)}
    
    <!-- FORK BAR -->
    {draw_fork_bar(100, 680, rx - 40, "Notify Customer & Kitchen")}
    
    <!-- Thread 1: Customer Lane -->
    {draw_arrow(lx + 90, 680, lx + 90, 720)}
    {draw_action(lx + 90, 742, 180, 44, "Send Confirmation SMS/Email")}
    {draw_arrow(lx + 90, 764, lx + 90, 805)}
    
    <!-- Thread 2: Kitchen Lane -->
    {draw_arrow(rx - 90, 680, rx - 90, 720)}
    {draw_action(rx - 90, 742, 180, 44, "Notify Kitchen of Order")}
    {draw_arrow(rx - 90, 764, rx - 90, 805)}
    
    <!-- JOIN BAR -->
    {draw_join_bar(100, 805, rx - 40, "Notifications Sent")}
    
    {draw_arrow(cx, 805, cx, 846)}
    {draw_action(cx, 868, 210, 44, "Open Live Order Tracking")}
    {draw_arrow(cx, 890, cx, 925)}
    {draw_end_node(cx, 940)}
    
    </svg>"""
    return svg

# =========================================================================
# 4. Order Processing & Kitchen Management
# =========================================================================
def build_act_4():
    w, h = 1180, 1340
    cx = 460
    lx_cook = 220
    rx_dispatch = 690
    rx_reject = 970
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}

    {draw_start_node(cx, 35)}
    {draw_arrow(cx, 46, cx, 75)}
    {draw_action(cx, 98, 220, 44, "Kitchen Receives Order Alert")}
    {draw_arrow(cx, 120, cx, 161)}
    {draw_decision(cx, 175, 28)}
    
    <!-- Chef Rejection Branch -->
    {draw_path(f"M {cx+14} 175 L {rx_reject} 175 L {rx_reject} 225", "Order Rejected", 500, 165, anchor="start")}
    {draw_action(rx_reject, 248, 180, 44, "Reject Order")}
    {draw_arrow(rx_reject, 270, rx_reject, 305)}
    {draw_action(rx_reject, 328, 180, 44, "Process Instant Refund")}
    {draw_arrow(rx_reject, 350, rx_reject, 385)}
    {draw_action(rx_reject, 408, 180, 44, "Notify Customer")}
    {draw_arrow(rx_reject, 430, rx_reject, 1170)}
    
    <!-- Chef Acceptance Branch -->
    {draw_arrow(cx, 189, cx, 230, "Order Accepted", "left")}
    {draw_action(cx, 252, 210, 44, "Accept & Start Preparation")}
    {draw_arrow(cx, 274, cx, 310)}
    
    <!-- FORK BAR -->
    {draw_fork_bar(60, 310, rx_dispatch + 140, "Cooking & Fleet Dispatch")}
    
    <!-- THREAD 1: Cooking Operations (Left) -->
    {draw_arrow(lx_cook, 310, lx_cook, 350)}
    {draw_action(lx_cook, 372, 190, 44, "Cook Meal to Specifications")}
    {draw_arrow(lx_cook, 394, lx_cook, 430)}
    {draw_action(lx_cook, 452, 190, 44, "Pack Meal & Mark Ready")}
    {draw_arrow(lx_cook, 474, lx_cook, 655)}
    
    <!-- THREAD 2: Courier Dispatch (Right) -->
    {draw_arrow(rx_dispatch, 310, rx_dispatch, 350)}
    {draw_action(rx_dispatch, 372, 200, 44, "Initiate Courier Dispatch")}
    {draw_arrow(rx_dispatch, 394, rx_dispatch, 436)}
    {draw_decision(rx_dispatch, 450, 28)}
    
    <!-- Corridor Batching -->
    {draw_path(f"M {rx_dispatch-14} 450 L 600 450 L 600 490", "Courier On-Route", 610, 440, anchor="end")}
    {draw_action(600, 512, 150, 44, "Batch Assign Courier")}
    {draw_arrow(600, 534, 600, 550)}
    
    <!-- Nearest Idle -->
    {draw_path(f"M {rx_dispatch+14} 450 L 780 450 L 780 490", "No Courier on Route", 710, 440, anchor="start")}
    {draw_action(780, 512, 150, 44, "Broadcast to Nearest Idle")}
    {draw_arrow(780, 534, 780, 550)}
    
    <!-- JOIN BAR for Courier Selection -->
    {draw_join_bar(470, 550, 820, "Courier Assigned")}
    {draw_arrow(rx_dispatch, 550, rx_dispatch, 590)}
    {draw_action(rx_dispatch, 612, 200, 44, "Courier Navigates to Kitchen")}
    {draw_arrow(rx_dispatch, 634, rx_dispatch, 655)}
    
    <!-- JOIN BAR -->
    {draw_join_bar(50, 655, rx_dispatch + 140, "Food Packed & Ready")}
    
    {draw_arrow(cx, 655, cx, 695)}
    {draw_action(cx, 718, 220, 44, "Handover Order to Courier")}
    {draw_arrow(cx, 740, cx, 775)}
    {draw_action(cx, 798, 220, 44, "Transport Order to Customer")}
    {draw_arrow(cx, 820, cx, 855)}
    {draw_action(cx, 878, 220, 44, "Arrive at Doorstep & Ask OTP")}
    {draw_arrow(cx, 900, cx, 941)}
    {draw_decision(cx, 955, 28)}
    
    <!-- OTP Invalid Retry Loop -->
    {draw_path(f"M {cx+14} 955 L 660 955 L 660 878 L {cx+110} 878", "OTP Invalid", 510, 945, anchor="start")}
    
    <!-- OTP Valid -->
    {draw_arrow(cx, 969, cx, 1010, "OTP Verified", "left")}
    {draw_action(cx, 1032, 220, 44, "Mark Order Delivered")}
    {draw_arrow(cx, 1054, cx, 1090)}
    {draw_action(cx, 1112, 220, 44, "Settle Payouts & Prompt Review")}
    {draw_arrow(cx, 1134, cx, 1170)}
    
    <!-- JOIN BAR -->
    {draw_join_bar(180, 1170, rx_reject + 30, "Order Lifecycle Complete")}
    {draw_arrow(cx, 1170, cx, 1215)}
    {draw_end_node(cx, 1230)}
    
    </svg>"""
    return svg

# =========================================================================
# 5. Chef Menu Catalog & Portion Inventory
# =========================================================================
def build_act_5():
    w, h = 970, 1080
    cx = 450
    lx = 240
    rx = 710
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}

    {draw_start_node(cx, 40)}
    {draw_arrow(cx, 51, cx, 80)}
    {draw_action(cx, 102, 210, 44, "Open Menu Management")}
    {draw_arrow(cx, 124, cx, 155)}
    {draw_action(cx, 178, 210, 44, "View Menu & Daily Stock")}
    {draw_arrow(cx, 200, cx, 246)}
    {draw_decision(cx, 260, 28)}
    
    <!-- Branch A: Add New Dish -->
    {draw_path(f"M {cx-14} 260 L {lx} 260 L {lx} 315", "Add New Dish", 280, 250, anchor="start")}
    {draw_action(lx, 338, 190, 44, "Upload Photo & Dish Info")}
    {draw_arrow(lx, 360, lx, 395)}
    {draw_action(lx, 418, 190, 44, "Set Price, Category & Tags")}
    {draw_arrow(lx, 440, lx, 475)}
    {draw_action(lx, 498, 190, 44, "Set Daily Portion Limit")}
    {draw_arrow(lx, 520, lx, 570)}
    
    <!-- Branch B: Edit Existing Dish -->
    {draw_path(f"M {cx+14} 260 L {rx} 260 L {rx} 315", "Edit Dish", 510, 250, anchor="start")}
    {draw_action(rx, 338, 190, 44, "Select Dish from Catalog")}
    {draw_arrow(rx, 360, rx, 395)}
    {draw_action(rx, 418, 190, 44, "Toggle Availability Status")}
    {draw_arrow(rx, 440, rx, 475)}
    {draw_action(rx, 498, 190, 44, "Update Remaining Stock")}
    {draw_arrow(rx, 520, rx, 570)}
    
    <!-- JOIN BAR -->
    {draw_join_bar(80, 570, rx + 40, "Menu Item Prepared")}
    {draw_arrow(cx, 570, cx, 616)}
    {draw_decision(cx, 630, 28)}
    
    <!-- Discard Branch -->
    {draw_path(f"M {cx+14} 630 L {rx} 630 L {rx} 685", "Discard", 510, 620, anchor="start")}
    {draw_action(rx, 708, 180, 44, "Discard Form Changes")}
    {draw_arrow(rx, 730, rx, 765)}
    
    <!-- Save & Validate Branch -->
    {draw_arrow(cx, 644, cx, 685, "Save & Publish", "left")}
    {draw_action(cx, 708, 200, 44, "Validate Food Guidelines")}
    {draw_arrow(cx, 730, cx, 765)}
    
    <!-- JOIN BAR -->
    {draw_join_bar(190, 765, rx + 40, "Changes Confirmed")}
    {draw_arrow(cx, 765, cx, 810)}
    {draw_action(cx, 832, 210, 44, "Save Menu to Database")}
    {draw_arrow(cx, 854, cx, 890)}
    {draw_action(cx, 912, 210, 44, "Update Live Customer Menu")}
    {draw_arrow(cx, 934, cx, 970)}
    {draw_end_node(cx, 985)}
    
    </svg>"""
    return svg

# =========================================================================
# 6. Chef Kitchen Onboarding & FSSAI Verification
# =========================================================================
def build_act_6():
    w, h = 1000, 1120
    cx = 380
    rx = 760
    lx_f = 220
    rx_f = 540
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}

    {draw_start_node(cx, 40)}
    {draw_arrow(cx, 51, cx, 80)}
    {draw_action(cx, 102, 220, 44, "Submit Kitchen Application")}
    {draw_arrow(cx, 124, cx, 155)}
    {draw_action(cx, 178, 220, 44, "Enter Kitchen & Hygiene Info")}
    {draw_arrow(cx, 200, cx, 235)}
    {draw_action(cx, 258, 220, 44, "Upload FSSAI & ID Documents")}
    {draw_arrow(cx, 280, cx, 315)}
    {draw_action(cx, 338, 220, 44, "Queue for Admin Review")}
    {draw_arrow(cx, 360, cx, 395)}
    {draw_action(cx, 418, 220, 44, "Admin Reviews Application")}
    {draw_arrow(cx, 440, cx, 486)}
    {draw_decision(cx, 500, 28)}
    
    <!-- Rejection Branch -->
    {draw_path(f"M {cx+14} 500 L {rx} 500 L {rx} 555", "Deficient / Rejected", 420, 490, anchor="start")}
    {draw_action(rx, 578, 200, 44, "Log Rejection Reason")}
    {draw_arrow(rx, 600, rx, 635)}
    {draw_action(rx, 658, 200, 44, "Send Correction Notice")}
    {draw_path(f"M {rx} 680 L {rx} 730 L 920 730 L 920 258 L {cx+110} 258")}
    
    <!-- Approval Branch -->
    {draw_arrow(cx, 514, cx, 555, "Approved", "left")}
    {draw_action(cx, 578, 220, 44, "Approve Kitchen Application")}
    {draw_arrow(cx, 600, cx, 635)}
    
    <!-- FORK BAR -->
    {draw_fork_bar(90, 635, rx_f + 40, "Kitchen Activation")}
    
    {draw_arrow(lx_f, 635, lx_f, 675)}
    {draw_action(lx_f, 698, 190, 44, "Assign Verified Badge")}
    {draw_arrow(lx_f, 720, lx_f, 760)}
    
    {draw_arrow(rx_f, 635, rx_f, 675)}
    {draw_action(rx_f, 698, 190, 44, "Send Welcome Confirmation")}
    {draw_arrow(rx_f, 720, rx_f, 760)}
    
    <!-- JOIN BAR -->
    {draw_join_bar(90, 760, rx_f + 40, "Setup Synchronized")}
    
    {draw_arrow(cx, 760, cx, 805)}
    {draw_action(cx, 828, 220, 44, "Activate Kitchen Profile")}
    {draw_arrow(cx, 850, cx, 885)}
    {draw_end_node(cx, 900)}
    
    </svg>"""
    return svg

# =========================================================================
# 7. Delivery Partner Cascade Dispatch & OTP Handover
# =========================================================================
def build_act_7():
    w, h = 940, 1160
    cx = 440
    lx_w = 260
    rx_w = 620
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}

    {draw_start_node(cx, 40)}
    {draw_arrow(cx, 51, cx, 80)}
    {draw_action(cx, 102, 220, 44, "Rider Goes Online")}
    {draw_arrow(cx, 124, cx, 155)}
    {draw_action(cx, 178, 220, 44, "Broadcast Live GPS Ping")}
    {draw_arrow(cx, 200, cx, 235)}
    {draw_action(cx, 258, 220, 44, "Kitchen Marks Order Ready")}
    {draw_arrow(cx, 280, cx, 315)}
    {draw_action(cx, 338, 220, 44, "Send Offer with 30s Timer")}
    {draw_arrow(cx, 360, cx, 406)}
    {draw_decision(cx, 420, 28)}
    
    <!-- Decline / Timeout Loop -->
    {draw_path(f"M {cx+14} 420 L 680 420 L 680 338 L {cx+110} 338", "Declined / Timed Out", 470, 410, anchor="start")}
    
    {draw_arrow(cx, 434, cx, 480, "Offer Accepted", "left")}
    {draw_action(cx, 502, 220, 44, "Navigate to Kitchen")}
    {draw_arrow(cx, 524, cx, 555)}
    {draw_action(cx, 578, 220, 44, "Pickup Package & Start Transit")}
    {draw_arrow(cx, 600, cx, 635)}
    {draw_action(cx, 658, 220, 44, "Stream GPS to Customer")}
    {draw_arrow(cx, 680, cx, 715)}
    {draw_action(cx, 738, 220, 44, "Arrive at Doorstep & Ask OTP")}
    {draw_arrow(cx, 760, cx, 806)}
    {draw_decision(cx, 820, 28)}
    
    <!-- OTP Retry Loop -->
    {draw_path(f"M {cx+14} 820 L 600 820 L 600 738 L {cx+110} 738", "OTP Invalid", 470, 810, anchor="start")}
    
    <!-- OTP Verified -->
    {draw_arrow(cx, 834, cx, 880, "OTP Verified", "left")}
    
    <!-- FORK BAR -->
    {draw_fork_bar(110, 880, rx_w + 40, "Settlement & Alerts")}
    
    {draw_arrow(lx_w, 880, lx_w, 920)}
    {draw_action(lx_w, 942, 200, 44, "Credit Rider Payout")}
    {draw_arrow(lx_w, 964, lx_w, 1005)}
    
    {draw_arrow(rx_w, 880, rx_w, 920)}
    {draw_action(rx_w, 942, 200, 44, "Send Receipt & Review Prompt")}
    {draw_arrow(rx_w, 964, rx_w, 1005)}
    
    <!-- JOIN BAR -->
    {draw_join_bar(110, 1005, rx_w + 40, "Order Handover Complete")}
    
    {draw_arrow(cx, 1005, cx, 1045)}
    {draw_action(cx, 1068, 220, 44, "Complete Order & Set Idle")}
    {draw_arrow(cx, 1090, cx, 1125)}
    {draw_end_node(cx, 1140)}
    
    </svg>"""
    return svg

# =========================================================================
# 8. Customer Grievance, Dispute & Refund Flow
# =========================================================================
def build_act_8():
    w, h = 1000, 1120
    cx = 380
    rx = 740
    lx_r = 220
    rx_r = 540
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}

    {draw_start_node(cx, 40)}
    {draw_arrow(cx, 51, cx, 80)}
    {draw_action(cx, 102, 220, 44, "Customer Reports Issue")}
    {draw_arrow(cx, 124, cx, 155)}
    {draw_action(cx, 178, 220, 44, "Select Dispute Category")}
    {draw_arrow(cx, 200, cx, 235)}
    {draw_action(cx, 258, 220, 44, "Upload Photo & Description")}
    {draw_arrow(cx, 280, cx, 315)}
    {draw_action(cx, 338, 220, 44, "Create Support Ticket")}
    {draw_arrow(cx, 360, cx, 406)}
    {draw_decision(cx, 420, 28)}
    
    <!-- Dismissed Branch -->
    {draw_path(f"M {cx+14} 420 L {rx} 420 L {rx} 475", "Dismissed", 420, 410, anchor="start")}
    {draw_action(rx, 498, 190, 44, "Log Rejection Note")}
    {draw_arrow(rx, 520, rx, 555)}
    {draw_action(rx, 578, 190, 44, "Notify Customer of Closure")}
    {draw_arrow(rx, 600, rx, 745)}
    
    <!-- Valid Refund Branch -->
    {draw_arrow(cx, 434, cx, 475, "Approved", "left")}
    {draw_action(cx, 498, 210, 44, "Approve Claim for Refund")}
    {draw_arrow(cx, 520, cx, 555)}
    
    <!-- FORK BAR -->
    {draw_fork_bar(90, 555, rx_r + 40, "Financial Adjustment")}
    
    {draw_arrow(lx_r, 555, lx_r, 595)}
    {draw_action(lx_r, 618, 190, 44, "Process Refund to Customer")}
    {draw_arrow(lx_r, 640, lx_r, 680)}
    
    {draw_arrow(rx_r, 555, rx_r, 595)}
    {draw_action(rx_r, 618, 190, 44, "Deduct Penalty from Kitchen")}
    {draw_arrow(rx_r, 640, rx_r, 680)}
    
    <!-- JOIN BAR -->
    {draw_join_bar(90, 680, rx_r + 40, "Reconciled")}
    {draw_arrow(cx, 680, cx, 745)}
    
    <!-- JOIN BAR -->
    {draw_join_bar(120, 745, rx + 40, "Dispute Processed")}
    {draw_arrow(cx, 745, cx, 790)}
    {draw_action(cx, 812, 220, 44, "Send Resolution Notice")}
    {draw_arrow(cx, 834, cx, 870)}
    {draw_action(cx, 892, 200, 44, "Close Support Ticket")}
    {draw_arrow(cx, 914, cx, 950)}
    {draw_end_node(cx, 965)}
    
    </svg>"""
    return svg

# =========================================================================
# 9. Platform Commission Settlement & Payouts Flow
# =========================================================================
def build_act_9():
    w, h = 980, 1120
    cx = 460
    rx = 740
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}

    {draw_start_node(cx, 40)}
    {draw_arrow(cx, 51, cx, 80)}
    {draw_action(cx, 102, 220, 44, "Order Delivered & Paid")}
    {draw_arrow(cx, 124, cx, 155)}
    {draw_action(cx, 178, 220, 44, "Calculate Gross Order Value")}
    {draw_arrow(cx, 200, cx, 235)}
    {draw_action(cx, 258, 220, 44, "Deduct Commission & Delivery")}
    {draw_arrow(cx, 280, cx, 315)}
    {draw_action(cx, 338, 220, 44, "Calculate Net Chef Payout")}
    {draw_arrow(cx, 360, cx, 395)}
    {draw_action(cx, 418, 220, 44, "Credit Chef Settlement Balance")}
    {draw_arrow(cx, 440, cx, 475)}
    {draw_action(cx, 498, 220, 44, "Open Financial Dashboard")}
    {draw_arrow(cx, 520, cx, 566)}
    {draw_decision(cx, 580, 28)}
    
    <!-- Export CSV Branch -->
    {draw_path(f"M {cx+14} 580 L {rx} 580 L {rx} 635", "Export CSV", 500, 570, anchor="start")}
    {draw_action(rx, 658, 190, 44, "Export Bank Payout CSV")}
    {draw_arrow(rx, 680, rx, 715)}
    {draw_action(rx, 738, 190, 44, "Audit & Reconcile Payouts")}
    {draw_arrow(rx, 760, rx, 785)}
    
    <!-- Analytics Display Branch -->
    {draw_arrow(cx, 594, cx, 635, "View Analytics", "left")}
    {draw_action(cx, 658, 210, 44, "Aggregate GMV & Revenue")}
    {draw_arrow(cx, 680, cx, 715)}
    {draw_action(cx, 738, 210, 44, "Display Revenue Charts")}
    {draw_arrow(cx, 760, cx, 785)}
    
    <!-- JOIN BAR -->
    {draw_join_bar(150, 785, rx + 30, "Reconciliation Complete")}
    {draw_arrow(cx, 785, cx, 830)}
    {draw_action(cx, 852, 220, 44, "Generate Financial Report")}
    {draw_arrow(cx, 874, cx, 910)}
    {draw_end_node(cx, 925)}
    
    </svg>"""
    return svg

# =========================================================================
# 10. Multi-Tiffin Milk-Run Batch Routing Flow
# =========================================================================
def build_act_10():
    w, h = 980, 1160
    cx = 480
    lx = 200
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}

    {draw_start_node(cx, 40)}
    {draw_arrow(cx, 51, cx, 80)}
    {draw_action(cx, 102, 230, 44, "Cron Triggers Shift Window")}
    {draw_arrow(cx, 124, cx, 155)}
    {draw_action(cx, 178, 230, 44, "Fetch Scheduled Deliveries")}
    {draw_arrow(cx, 200, cx, 235)}
    {draw_action(cx, 258, 230, 44, "Cluster Stops by Corridor")}
    {draw_arrow(cx, 280, cx, 315)}
    {draw_action(cx, 338, 230, 44, "Generate Multi-Stop Route")}
    {draw_arrow(cx, 360, cx, 395)}
    {draw_action(cx, 418, 230, 44, "Assign Route to Delivery Partner")}
    {draw_arrow(cx, 440, cx, 475)}
    {draw_action(cx, 498, 230, 44, "Rider Accepts & Navigates Drops")}
    {draw_arrow(cx, 520, cx, 555)}
    {draw_action(cx, 578, 230, 44, "Deliver Meal & Verify OTP")}
    {draw_arrow(cx, 600, cx, 635)}
    {draw_action(cx, 658, 230, 44, "Advance to Next Stop")}
    {draw_arrow(cx, 680, cx, 726)}
    {draw_decision(cx, 740, 28)}
    
    <!-- Loop Back Branch -->
    {draw_path(f"M {cx-14} 740 L {lx} 740 L {lx} 498 L {cx-115} 498", "More Stops", 230, 730, anchor="start")}
    
    <!-- Final Stop Completed -->
    {draw_arrow(cx, 754, cx, 795, "Run Complete", "left")}
    {draw_action(cx, 818, 230, 44, "Complete Run & Settle Payout")}
    {draw_arrow(cx, 840, cx, 875)}
    {draw_action(cx, 898, 230, 44, "Send Delivery Summaries")}
    {draw_arrow(cx, 920, cx, 955)}
    {draw_end_node(cx, 970)}
    
    </svg>"""
    return svg

# =========================================================================
# 11. Zero-Waste Surplus Food Rescue Flow
# =========================================================================
def build_act_11():
    w, h = 980, 1160
    cx = 480
    lx = 200
    rx = 760
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {DEFS}

    {draw_start_node(cx, 40)}
    {draw_arrow(cx, 51, cx, 75)}
    {draw_action(cx, 98, 220, 44, "Open Zero-Waste Publisher")}
    {draw_arrow(cx, 120, cx, 150)}
    {draw_action(cx, 172, 230, 44, "Select Dish & Set Discount")}
    {draw_arrow(cx, 194, cx, 236)}
    {draw_decision(cx, 250, 28)}
    
    <!-- Invalid Parameters Loop -->
    {draw_path(f"M {cx-14} 250 L {lx} 250 L {lx} 172 L {cx-115} 172", "Invalid", 230, 240, anchor="start")}
    
    <!-- Valid Parameters -->
    {draw_arrow(cx, 264, cx, 305, "Valid", "right")}
    {draw_action(cx, 328, 220, 44, "Publish Flash Deal")}
    {draw_arrow(cx, 350, cx, 385)}
    {draw_action(cx, 408, 220, 44, "Start Live Countdown Timer")}
    {draw_arrow(cx, 430, cx, 471)}
    {draw_decision(cx, 485, 28)}
    
    <!-- Claimed Branch -->
    {draw_path(f"M {cx+14} 485 L {rx} 485 L {rx} 535", "Claimed", 520, 475, anchor="start")}
    {draw_action(rx, 558, 190, 44, "Customer Adds Deal to Cart")}
    {draw_arrow(rx, 580, rx, 615)}
    {draw_action(rx, 638, 190, 44, "Checkout & Reduce Stock")}
    {draw_arrow(rx, 660, rx, 695)}
    
    <!-- Expired Branch -->
    {draw_arrow(cx, 499, cx, 535, "Expired", "left")}
    {draw_action(cx, 558, 190, 44, "Timer Reaches 00:00")}
    {draw_arrow(cx, 580, cx, 615)}
    {draw_action(cx, 638, 190, 44, "Auto-Expire Deal")}
    {draw_arrow(cx, 660, cx, 695)}
    
    <!-- JOIN BAR -->
    {draw_join_bar(150, 695, rx + 30, "Deal Lifecycle Complete")}
    {draw_arrow(cx, 695, cx, 735)}
    {draw_action(cx, 758, 220, 44, "Update Food Saved Metrics")}
    {draw_arrow(cx, 780, cx, 815)}
    {draw_end_node(cx, 830)}
    
    </svg>"""
    return svg

def main():
    print("Rendering ALL 11 Simplified Activity Diagrams...")
    render_svg_to_png(build_act_1(), "activity_3_3_1_customer_auth.html", "activity_3_3_1_customer_auth.png", 1020, 1100)
    render_svg_to_png(build_act_2(), "activity_3_3_2_subscription.html", "activity_3_3_2_subscription.png", 900, 1120)
    render_svg_to_png(build_act_3(), "activity_3_3_3_payment.html", "activity_3_3_3_payment.png", 960, 1140)
    render_svg_to_png(build_act_4(), "activity_3_3_4_order_processing.html", "activity_3_3_4_order_processing.png", 1180, 1340)
    render_svg_to_png(build_act_5(), "activity_3_3_5_menu_inventory.html", "activity_3_3_5_menu_inventory.png", 970, 1080)
    render_svg_to_png(build_act_6(), "activity_3_3_6_chef_onboarding.html", "activity_3_3_6_chef_onboarding.png", 1000, 1120)
    render_svg_to_png(build_act_7(), "activity_3_3_7_delivery_partner.html", "activity_3_3_7_delivery_partner.png", 940, 1160)
    render_svg_to_png(build_act_8(), "activity_3_3_8_dispute_refund.html", "activity_3_3_8_dispute_refund.png", 1000, 1120)
    render_svg_to_png(build_act_9(), "activity_3_3_9_analytics_commission.html", "activity_3_3_9_analytics_commission.png", 980, 1120)
    render_svg_to_png(build_act_10(), "activity_3_3_10_milk_run.html", "activity_3_3_10_milk_run.png", 980, 1160)
    render_svg_to_png(build_act_11(), "activity_3_3_11_zero_waste.html", "activity_3_3_11_zero_waste.png", 980, 1160)
    print("ALL 11 SIMPLIFIED ACTIVITY DIAGRAMS SUCCESSFULLY GENERATED!")

if __name__ == "__main__":
    main()
