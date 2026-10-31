import os
import subprocess
import math

DIAGRAMS_DIR = r"c:\xampp\htdocs\Kravyo\diagrams"
CHROME_PATH = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
os.makedirs(DIAGRAMS_DIR, exist_ok=True)

def render_svg_to_png(svg_content, html_filename, png_filename, width=1180, height=1480):
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
    print(f"Generated clean UML diagram with separated routing: {png_filename}")

# Standard Draw.io UML Markers
UML_DEFS = """
<defs>
  <!-- Standard Open Arrowhead for UML relationships -->
  <marker id="uml_arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="9" markerHeight="9" orient="auto-start-reverse">
    <path d="M 0 1.5 L 9 5 L 0 8.5" fill="none" stroke="#000000" stroke-width="1.3" />
  </marker>
</defs>
"""

def draw_drawio_actor(x, y, name):
    return f"""
    <!-- Actor: {name} -->
    <g id="actor_{name.replace(' ', '_')}">
      <circle cx="{x}" cy="{y - 40}" r="15" stroke="#000000" stroke-width="1.5" fill="#ffffff" />
      <line x1="{x}" y1="{y - 25}" x2="{x}" y2="{y + 15}" stroke="#000000" stroke-width="1.5" />
      <line x1="{x - 25}" y1="{y - 12}" x2="{x + 25}" y2="{y - 12}" stroke="#000000" stroke-width="1.5" />
      <line x1="{x}" y1="{y + 15}" x2="{x - 20}" y2="{y + 50}" stroke="#000000" stroke-width="1.5" />
      <line x1="{x}" y1="{y + 15}" x2="{x + 20}" y2="{y + 50}" stroke="#000000" stroke-width="1.5" />
      <text x="{x}" y="{y + 70}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="bold" fill="#000000">{name}</text>
    </g>
    """

def draw_drawio_ellipse(cx, cy, rx, ry, text1, text2=None):
    if text2:
        text_content = f"""
        <text x="{cx}" y="{cy - 3}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="12" fill="#000000">{text1}</text>
        <text x="{cx}" y="{cy + 13}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" fill="#000000">{text2}</text>
        """
    else:
        text_content = f"""
        <text x="{cx}" y="{cy + 4}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="12" fill="#000000">{text1}</text>
        """
    return f"""
    <ellipse cx="{cx}" cy="{cy}" rx="{rx}" ry="{ry}" fill="#ffffff" stroke="#000000" stroke-width="1.3" />
    {text_content}
    """

def draw_drawio_assoc(x1, y1, x2, y2):
    return f'<line x1="{x1}" y1="{y1}" x2="{x2}" y2="{y2}" stroke="#000000" stroke-width="1.2" />'

def draw_drawio_rel(base_cx, base_cy, base_rx, base_ry,
                    target_cx, target_cy, target_rx, target_ry,
                    stereotype="<<extend>>",
                    port_angle_deg=0,
                    target_port_angle_deg=None,
                    label_t=0.50,
                    label_y_offset=0):
    """
    Standard UML Direction Rules:
    - <<include>>: Base Use Case -> Included Use Case (Arrow points RIGHT towards target)
    - <<extend>>:  Extension Use Case -> Base Use Case (Arrow points LEFT towards base)
    
    Port angle routing:
    Routes arrows independently along the perimeter of the ellipses to prevent line & label collision.
    """
    # Departure point on base ellipse
    angle_rad = math.radians(port_angle_deg)
    x1 = base_cx + base_rx * math.cos(angle_rad)
    y1 = base_cy + base_ry * math.sin(angle_rad)
        
    # Arrival point on target ellipse
    if target_port_angle_deg is None:
        t_angle = math.atan2(target_cy - y1, target_cx - x1)
        x2 = target_cx - target_rx * math.cos(t_angle)
        y2 = target_cy - target_ry * math.sin(t_angle)
    else:
        t_rad = math.radians(target_port_angle_deg)
        x2 = target_cx - target_rx * math.cos(t_rad)
        y2 = target_cy - target_ry * math.sin(t_rad)
        
    mid_x = x1 + label_t * (x2 - x1)
    mid_y = y1 + label_t * (y2 - y1) + label_y_offset
    box_w = 68 if "extend" in stereotype else 72
    clean_stereo = stereotype.replace('<<','').replace('>>','')
    
    if "extend" in stereotype:
        # Arrowhead points to BASE (x1, y1)
        line_svg = f'<line x1="{x2:.1f}" y1="{y2:.1f}" x2="{x1:.1f}" y2="{y1:.1f}" stroke="#000000" stroke-width="1.2" stroke-dasharray="4,3" marker-end="url(#uml_arrow)" />'
    else:
        # <<include>>: Arrowhead points to TARGET (x2, y2)
        line_svg = f'<line x1="{x1:.1f}" y1="{y1:.1f}" x2="{x2:.1f}" y2="{y2:.1f}" stroke="#000000" stroke-width="1.2" stroke-dasharray="4,3" marker-end="url(#uml_arrow)" />'
        
    return f"""
    {line_svg}
    <rect x="{mid_x - box_w/2:.1f}" y="{mid_y - 8:.1f}" width="{box_w}" height="16" fill="#ffffff" />
    <text x="{mid_x:.1f}" y="{mid_y + 4:.1f}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="10" font-style="italic" fill="#000000">&lt;&lt;{clean_stereo}&gt;&gt;</text>
    """

# =========================================================================
# 1. CUSTOMER USE CASE DIAGRAM
# =========================================================================
def build_customer_diagram():
    w, h = 1160, 1460
    box_x, box_y, box_w, box_h = 240, 35, 840, 1360
    actor_x, actor_y = 110, 680
    
    col1_x = 430
    col2_x = 860
    base_rx, base_ry = 105, 25
    
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {UML_DEFS}
    <rect x="{box_x}" y="{box_y}" width="{box_w}" height="{box_h}" fill="#ffffff" stroke="#000000" stroke-width="1.5" />
    <text x="{box_x + box_w/2}" y="{box_y + 28}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="15" font-weight="bold" fill="#000000">Kravyo</text>
    
    {draw_drawio_actor(actor_x, actor_y, "Customer")}
    """
    
    # Base Use Cases perfectly centered with respect to their connected secondary use cases:
    cases = [
        (135, "Register / Login", None),
        (320, "Browse & Search Food", None),
        (460, "Customize Meal", None),
        (555, "Manage Cart", None),
        (745, "Place Online Order", None),
        (935, "Track Order Live", None),
        (1030, "Manage Subscriptions", None),
        (1125, "Claim Zero Waste Deals", None),
        (1215, "Submit Review", None),
        (1305, "File Dispute / Report", None),
    ]
    
    for cy, l1, l2 in cases:
        svg += draw_drawio_assoc(actor_x + 25, actor_y - 10, col1_x - base_rx, cy)
        svg += draw_drawio_ellipse(col1_x, cy, base_rx, base_ry, l1, l2)
        
    # Relationships with 90-95px vertical spacing and distinct perimeter routing:
    # 1. Register / Login (cy=135) -> Verify Email OTP (cy=90), Forgot Password (cy=180) [90px separation]
    svg += draw_drawio_ellipse(col2_x, 90, 100, 24, "Verify Email OTP", None)
    svg += draw_drawio_rel(col1_x, 135, base_rx, base_ry, col2_x, 90, 100, 24,
                           "<<extend>>", port_angle_deg=-40, target_port_angle_deg=-15, label_t=0.42, label_y_offset=-2)
    
    svg += draw_drawio_ellipse(col2_x, 180, 100, 24, "Forgot Password", None)
    svg += draw_drawio_rel(col1_x, 135, base_rx, base_ry, col2_x, 180, 100, 24,
                           "<<extend>>", port_angle_deg=40, target_port_angle_deg=15, label_t=0.60, label_y_offset=2)
    
    # 2. Browse & Search Food (cy=320) -> View AI Recommendations (cy=275), Filter Veg/Jain/Diabetic (cy=365) [90px separation]
    svg += draw_drawio_ellipse(col2_x, 275, 105, 24, "View AI Recommendations", None)
    svg += draw_drawio_rel(col1_x, 320, base_rx, base_ry, col2_x, 275, 105, 24,
                           "<<extend>>", port_angle_deg=-40, target_port_angle_deg=-15, label_t=0.42, label_y_offset=-2)
    
    svg += draw_drawio_ellipse(col2_x, 365, 105, 24, "Filter Veg/Jain/Diabetic", None)
    svg += draw_drawio_rel(col1_x, 320, base_rx, base_ry, col2_x, 365, 105, 24,
                           "<<extend>>", port_angle_deg=40, target_port_angle_deg=15, label_t=0.60, label_y_offset=2)
    
    # 3. Customize Meal (cy=460) -> Select Spice/Oil/Jain (cy=460) [perfectly horizontal]
    svg += draw_drawio_ellipse(col2_x, 460, 105, 24, "Select Spice/Oil/Jain", None)
    svg += draw_drawio_rel(col1_x, 460, base_rx, base_ry, col2_x, 460, 105, 24,
                           "<<extend>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 4. Manage Cart (cy=555) -> Apply Coupon (cy=555) [perfectly horizontal]
    svg += draw_drawio_ellipse(col2_x, 555, 95, 24, "Apply Coupon", None)
    svg += draw_drawio_rel(col1_x, 555, base_rx, base_ry, col2_x, 555, 95, 24,
                           "<<extend>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 5. Place Online Order (cy=745) -> Make Razorpay Payment (cy=650), Receive Delivery OTP (cy=745), Cancel Order (cy=840)
    # Generous 95px separation between each target, distinct perimeter departure angles (-45°, 0°, +45°), staggered labels (t=0.38, 0.52, 0.66)
    svg += draw_drawio_ellipse(col2_x, 650, 105, 24, "Make Razorpay Payment", None)
    svg += draw_drawio_rel(col1_x, 745, base_rx, base_ry, col2_x, 650, 105, 24,
                           "<<include>>", port_angle_deg=-45, target_port_angle_deg=-16, label_t=0.38, label_y_offset=-2)
    
    svg += draw_drawio_ellipse(col2_x, 745, 105, 24, "Receive Delivery OTP", None)
    svg += draw_drawio_rel(col1_x, 745, base_rx, base_ry, col2_x, 745, 105, 24,
                           "<<include>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.52, label_y_offset=0)
    
    svg += draw_drawio_ellipse(col2_x, 840, 95, 24, "Cancel Order", None)
    svg += draw_drawio_rel(col1_x, 745, base_rx, base_ry, col2_x, 840, 95, 24,
                           "<<extend>>", port_angle_deg=45, target_port_angle_deg=16, label_t=0.66, label_y_offset=2)
    
    # 6. Track Order Live (cy=935) -> Live WebSockets Map (cy=935) [perfectly horizontal]
    svg += draw_drawio_ellipse(col2_x, 935, 105, 24, "Live WebSockets Map", None)
    svg += draw_drawio_rel(col1_x, 935, base_rx, base_ry, col2_x, 935, 105, 24,
                           "<<include>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 7. Manage Subscriptions (cy=1030) -> Pause/Resume Delivery (cy=1030) [perfectly horizontal]
    svg += draw_drawio_ellipse(col2_x, 1030, 100, 24, "Pause/Resume Delivery", None)
    svg += draw_drawio_rel(col1_x, 1030, base_rx, base_ry, col2_x, 1030, 100, 24,
                           "<<extend>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 8. Claim Zero Waste Deals (cy=1125) -> View Expiry Timer (cy=1125) [perfectly horizontal]
    svg += draw_drawio_ellipse(col2_x, 1125, 95, 24, "View Expiry Timer", None)
    svg += draw_drawio_rel(col1_x, 1125, base_rx, base_ry, col2_x, 1125, 95, 24,
                           "<<extend>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 9. File Dispute / Report (cy=1305) -> Attach Defect Photo (cy=1305) [perfectly horizontal]
    svg += draw_drawio_ellipse(col2_x, 1305, 95, 24, "Attach Defect Photo", None)
    svg += draw_drawio_rel(col1_x, 1305, base_rx, base_ry, col2_x, 1305, 95, 24,
                           "<<extend>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)

    svg += "</svg>"
    return svg

# =========================================================================
# 2. HOME CHEF USE CASE DIAGRAM
# =========================================================================
def build_chef_diagram():
    w, h = 1160, 1160
    box_x, box_y, box_w, box_h = 240, 35, 840, 1050
    actor_x, actor_y = 110, 535
    
    col1_x = 430
    col2_x = 860
    base_rx, base_ry = 105, 25
    
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {UML_DEFS}
    <rect x="{box_x}" y="{box_y}" width="{box_w}" height="{box_h}" fill="#ffffff" stroke="#000000" stroke-width="1.5" />
    <text x="{box_x + box_w/2}" y="{box_y + 28}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="15" font-weight="bold" fill="#000000">Kravyo</text>
    
    {draw_drawio_actor(actor_x, actor_y, "Home Chef")}
    """
    
    # Base Use Cases centered with respect to connected secondary use cases:
    cases = [
        (90, "Register / Login", None),
        (238, "Manage Kitchen Profile", None),
        (385, "Manage Menu", None),
        (532, "Manage Orders", None),
        (680, "Update Preparation Status", None),
        (780, "Manage Subscriptions", None),
        (880, "List Discounted Meals", None),
        (980, "View Dashboard", None),
    ]
    
    for cy, l1, l2 in cases:
        svg += draw_drawio_assoc(actor_x + 25, actor_y - 10, col1_x - base_rx, cy)
        svg += draw_drawio_ellipse(col1_x, cy, base_rx, base_ry, l1, l2)
        
    # Relationships with 95-100px vertical spacing and distinct perimeter routing:
    # 1. Register / Login (cy=90) -> Forgot Password (cy=90)
    svg += draw_drawio_ellipse(col2_x, 90, 95, 24, "Forgot Password", None)
    svg += draw_drawio_rel(col1_x, 90, base_rx, base_ry, col2_x, 90, 95, 24,
                           "<<extend>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 2. Manage Kitchen Profile (cy=238) -> Apply Hygiene Verification (cy=190), Toggle Kitchen Open/Close (cy=285) [95px separation]
    svg += draw_drawio_ellipse(col2_x, 190, 105, 24, "Apply Hygiene Verification", None)
    svg += draw_drawio_rel(col1_x, 238, base_rx, base_ry, col2_x, 190, 105, 24,
                           "<<extend>>", port_angle_deg=-42, target_port_angle_deg=-15, label_t=0.42, label_y_offset=-2)
    
    svg += draw_drawio_ellipse(col2_x, 285, 105, 24, "Toggle Kitchen Open/Close", None)
    svg += draw_drawio_rel(col1_x, 238, base_rx, base_ry, col2_x, 285, 105, 24,
                           "<<extend>>", port_angle_deg=42, target_port_angle_deg=15, label_t=0.60, label_y_offset=2)
    
    # 3. Manage Menu (cy=385) -> Assign Dietary Tags (cy=385)
    svg += draw_drawio_ellipse(col2_x, 385, 105, 24, "Assign Dietary Tags", None)
    svg += draw_drawio_rel(col1_x, 385, base_rx, base_ry, col2_x, 385, 105, 24,
                           "<<extend>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 4. Manage Orders (cy=532) -> Audio Alert (beep.wav) (cy=485), Reject Orders (cy=580) [95px separation]
    svg += draw_drawio_ellipse(col2_x, 485, 105, 24, "Audio Alert (beep.wav)", None)
    svg += draw_drawio_rel(col1_x, 532, base_rx, base_ry, col2_x, 485, 105, 24,
                           "<<include>>", port_angle_deg=-42, target_port_angle_deg=-15, label_t=0.42, label_y_offset=-2)
    
    svg += draw_drawio_ellipse(col2_x, 580, 95, 24, "Reject Orders", None)
    svg += draw_drawio_rel(col1_x, 532, base_rx, base_ry, col2_x, 580, 95, 24,
                           "<<extend>>", port_angle_deg=42, target_port_angle_deg=15, label_t=0.60, label_y_offset=2)
    
    # 5. Update Prep Status (cy=680) -> Mark Ready for Pickup (cy=680)
    svg += draw_drawio_ellipse(col2_x, 680, 105, 24, "Mark Ready for Pickup", None)
    svg += draw_drawio_rel(col1_x, 680, base_rx, base_ry, col2_x, 680, 105, 24,
                           "<<include>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 6. Manage Subscriptions (cy=780) -> Daily Production Forecast (cy=780)
    svg += draw_drawio_ellipse(col2_x, 780, 105, 24, "Daily Production Forecast", None)
    svg += draw_drawio_rel(col1_x, 780, base_rx, base_ry, col2_x, 780, 105, 24,
                           "<<include>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 7. List Discounted Meals (cy=880) -> Set Expiry Timer (cy=880)
    svg += draw_drawio_ellipse(col2_x, 880, 100, 24, "Set Expiry Timer", None)
    svg += draw_drawio_rel(col1_x, 880, base_rx, base_ry, col2_x, 880, 100, 24,
                           "<<include>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 8. View Dashboard (cy=980) -> View Reviews & Ratings (cy=980)
    svg += draw_drawio_ellipse(col2_x, 980, 105, 24, "View Reviews & Ratings", None)
    svg += draw_drawio_rel(col1_x, 980, base_rx, base_ry, col2_x, 980, 105, 24,
                           "<<extend>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)

    svg += "</svg>"
    return svg

# =========================================================================
# 3. DELIVERY PARTNER USE CASE DIAGRAM
# =========================================================================
def build_delivery_diagram():
    w, h = 1160, 1120
    box_x, box_y, box_w, box_h = 240, 35, 840, 1010
    actor_x, actor_y = 110, 510
    
    col1_x = 430
    col2_x = 860
    base_rx, base_ry = 105, 25
    
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {UML_DEFS}
    <rect x="{box_x}" y="{box_y}" width="{box_w}" height="{box_h}" fill="#ffffff" stroke="#000000" stroke-width="1.5" />
    <text x="{box_x + box_w/2}" y="{box_y + 28}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="15" font-weight="bold" fill="#000000">Kravyo</text>
    
    {draw_drawio_actor(actor_x, actor_y, "Delivery Partner")}
    """
    
    cases = [
        (90, "Register / Login", None),
        (195, "Manage Shift Duty", None),
        (352, "Receive Dispatch Offer", None),
        (510, "Navigate Route", None),
        (615, "Confirm Kitchen Pickup", None),
        (720, "Stream Live GPS Location", None),
        (825, "Deliver Order", None),
        (930, "View Earnings History", None),
    ]
    
    for cy, l1, l2 in cases:
        svg += draw_drawio_assoc(actor_x + 25, actor_y - 10, col1_x - base_rx, cy)
        svg += draw_drawio_ellipse(col1_x, cy, base_rx, base_ry, l1, l2)
        
    # Relationships with 105px vertical spacing and distinct perimeter routing:
    # 1. Register / Login (cy=90) -> Upload Driving License (cy=90)
    svg += draw_drawio_ellipse(col2_x, 90, 100, 24, "Upload Driving License", None)
    svg += draw_drawio_rel(col1_x, 90, base_rx, base_ry, col2_x, 90, 100, 24,
                           "<<include>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 2. Manage Shift Duty (cy=195) -> Toggle Online / Offline (cy=195)
    svg += draw_drawio_ellipse(col2_x, 195, 100, 24, "Toggle Online / Offline", None)
    svg += draw_drawio_rel(col1_x, 195, base_rx, base_ry, col2_x, 195, 100, 24,
                           "<<extend>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 3. Receive Dispatch Offer (cy=352) -> Accept 25s Ping Offer (cy=300), Decline / Timeout Offer (cy=405) [105px separation]
    svg += draw_drawio_ellipse(col2_x, 300, 105, 24, "Accept 25s Ping Offer", None)
    svg += draw_drawio_rel(col1_x, 352, base_rx, base_ry, col2_x, 300, 105, 24,
                           "<<extend>>", port_angle_deg=-42, target_port_angle_deg=-15, label_t=0.42, label_y_offset=-2)
    
    svg += draw_drawio_ellipse(col2_x, 405, 105, 24, "Decline / Timeout Offer", None)
    svg += draw_drawio_rel(col1_x, 352, base_rx, base_ry, col2_x, 405, 105, 24,
                           "<<extend>>", port_angle_deg=42, target_port_angle_deg=15, label_t=0.60, label_y_offset=2)
    
    # 4. Navigate Route (cy=510) -> Google Routes Navigation (cy=510)
    svg += draw_drawio_ellipse(col2_x, 510, 105, 24, "Google Routes Navigation", None)
    svg += draw_drawio_rel(col1_x, 510, base_rx, base_ry, col2_x, 510, 105, 24,
                           "<<include>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 5. Confirm Kitchen Pickup (cy=615) -> Update Out for Delivery (cy=615)
    svg += draw_drawio_ellipse(col2_x, 615, 105, 24, "Update Out for Delivery", None)
    svg += draw_drawio_rel(col1_x, 615, base_rx, base_ry, col2_x, 615, 105, 24,
                           "<<include>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 6. Stream Live GPS Location (cy=720) -> Broadcast Pusher GPS (cy=720)
    svg += draw_drawio_ellipse(col2_x, 720, 105, 24, "Broadcast Pusher GPS", None)
    svg += draw_drawio_rel(col1_x, 720, base_rx, base_ry, col2_x, 720, 105, 24,
                           "<<include>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 7. Deliver Order (cy=825) -> Verify Customer OTP (cy=825)
    svg += draw_drawio_ellipse(col2_x, 825, 105, 24, "Verify Customer OTP", None)
    svg += draw_drawio_rel(col1_x, 825, base_rx, base_ry, col2_x, 825, 105, 24,
                           "<<include>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 8. View Earnings History (cy=930) -> View Trip Incentives (cy=930)
    svg += draw_drawio_ellipse(col2_x, 930, 100, 24, "View Trip Incentives", None)
    svg += draw_drawio_rel(col1_x, 930, base_rx, base_ry, col2_x, 930, 100, 24,
                           "<<extend>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)

    svg += "</svg>"
    return svg

# =========================================================================
# 4. ADMINISTRATOR USE CASE DIAGRAM
# =========================================================================
def build_admin_diagram():
    w, h = 1160, 1160
    box_x, box_y, box_w, box_h = 240, 35, 840, 1050
    actor_x, actor_y = 110, 530
    
    col1_x = 430
    col2_x = 860
    base_rx, base_ry = 105, 25
    
    svg = f"""<svg width="{w}" height="{h}" viewBox="0 0 {w} {h}" xmlns="http://www.w3.org/2000/svg">
    {UML_DEFS}
    <rect x="{box_x}" y="{box_y}" width="{box_w}" height="{box_h}" fill="#ffffff" stroke="#000000" stroke-width="1.5" />
    <text x="{box_x + box_w/2}" y="{box_y + 28}" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="15" font-weight="bold" fill="#000000">Kravyo</text>
    
    {draw_drawio_actor(actor_x, actor_y, "Administrator")}
    """
    
    cases = [
        (90, "Admin Login", None),
        (238, "Manage Users & Kitchens", None),
        (432, "Manage Delivery Partners", None),
        (580, "Manage Food & Categories", None),
        (728, "Handle Disputes & Complaints", None),
        (875, "Monitor Orders & Fleet", None),
        (975, "Generate Analytics", None),
    ]
    
    for cy, l1, l2 in cases:
        svg += draw_drawio_assoc(actor_x + 25, actor_y - 10, col1_x - base_rx, cy)
        svg += draw_drawio_ellipse(col1_x, cy, base_rx, base_ry, l1, l2)
        
    # Relationships with 95-100px vertical spacing and distinct perimeter routing:
    # 1. Admin Login (cy=90) -> Change Password (cy=90)
    svg += draw_drawio_ellipse(col2_x, 90, 95, 24, "Change Password", None)
    svg += draw_drawio_rel(col1_x, 90, base_rx, base_ry, col2_x, 90, 95, 24,
                           "<<extend>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 2. Manage Users & Kitchens (cy=238) -> Approve Hygiene Badges (cy=190), Review FSSAI License (cy=285) [95px separation]
    svg += draw_drawio_ellipse(col2_x, 190, 105, 24, "Approve Hygiene Badges", None)
    svg += draw_drawio_rel(col1_x, 238, base_rx, base_ry, col2_x, 190, 105, 24,
                           "<<extend>>", port_angle_deg=-42, target_port_angle_deg=-15, label_t=0.42, label_y_offset=-2)
    
    svg += draw_drawio_ellipse(col2_x, 285, 105, 24, "Review FSSAI License", None)
    svg += draw_drawio_rel(col1_x, 238, base_rx, base_ry, col2_x, 285, 105, 24,
                           "<<include>>", port_angle_deg=42, target_port_angle_deg=15, label_t=0.60, label_y_offset=2)
    
    # 3. Manage Delivery Partners (cy=432) -> Approve Driver License (cy=385), Suspend Rider Account (cy=480) [95px separation]
    svg += draw_drawio_ellipse(col2_x, 385, 100, 24, "Approve Driver License", None)
    svg += draw_drawio_rel(col1_x, 432, base_rx, base_ry, col2_x, 385, 100, 24,
                           "<<extend>>", port_angle_deg=-42, target_port_angle_deg=-15, label_t=0.42, label_y_offset=-2)
    
    svg += draw_drawio_ellipse(col2_x, 480, 100, 24, "Suspend Rider Account", None)
    svg += draw_drawio_rel(col1_x, 432, base_rx, base_ry, col2_x, 480, 100, 24,
                           "<<extend>>", port_angle_deg=42, target_port_angle_deg=15, label_t=0.60, label_y_offset=2)
    
    # 4. Manage Food & Categories (cy=580) -> Moderate Reviews (cy=580)
    svg += draw_drawio_ellipse(col2_x, 580, 95, 24, "Moderate Reviews", None)
    svg += draw_drawio_rel(col1_x, 580, base_rx, base_ry, col2_x, 580, 95, 24,
                           "<<extend>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 5. Handle Disputes & Complaints (cy=728) -> Audit Order Timestamps (cy=680), Process Razorpay Refund (cy=775) [95px separation]
    svg += draw_drawio_ellipse(col2_x, 680, 105, 24, "Audit Order Timestamps", None)
    svg += draw_drawio_rel(col1_x, 728, base_rx, base_ry, col2_x, 680, 105, 24,
                           "<<include>>", port_angle_deg=-42, target_port_angle_deg=-15, label_t=0.42, label_y_offset=-2)
    
    svg += draw_drawio_ellipse(col2_x, 775, 105, 24, "Process Razorpay Refund", None)
    svg += draw_drawio_rel(col1_x, 728, base_rx, base_ry, col2_x, 775, 105, 24,
                           "<<extend>>", port_angle_deg=42, target_port_angle_deg=15, label_t=0.60, label_y_offset=2)
    
    # 6. Monitor Orders & Fleet (cy=875) -> Live Fleet Map (cy=875)
    svg += draw_drawio_ellipse(col2_x, 875, 95, 24, "Live Fleet Map", None)
    svg += draw_drawio_rel(col1_x, 875, base_rx, base_ry, col2_x, 875, 95, 24,
                           "<<extend>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)
    
    # 7. Generate Analytics (cy=975) -> Export Revenue CSV (cy=975)
    svg += draw_drawio_ellipse(col2_x, 975, 100, 24, "Export Revenue CSV", None)
    svg += draw_drawio_rel(col1_x, 975, base_rx, base_ry, col2_x, 975, 100, 24,
                           "<<extend>>", port_angle_deg=0, target_port_angle_deg=0, label_t=0.50)

    svg += "</svg>"
    return svg

def main():
    print("Re-rendering all 4 diagrams with generous spacing and non-overlapping routes...")
    render_svg_to_png(build_customer_diagram(), "use_case_customer.html", "use_case_customer.png", 1180, 1480)
    render_svg_to_png(build_chef_diagram(), "use_case_chef.html", "use_case_chef.png", 1180, 1180)
    render_svg_to_png(build_delivery_diagram(), "use_case_delivery.html", "use_case_delivery.png", 1180, 1140)
    render_svg_to_png(build_admin_diagram(), "use_case_admin.html", "use_case_admin.png", 1180, 1180)
    print("All 4 diagrams updated successfully with zero collision!")

if __name__ == "__main__":
    main()
