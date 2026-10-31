import os
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.dml.color import RGBColor
from pptx.enum.shapes import MSO_SHAPE

def build_human_academic_presentation():
    prs = Presentation()
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)
    blank_layout = prs.slide_layouts[6]
    
    # Human Academic Palette (Clean, Traditional, High-Contrast University Viva Style)
    NAVY_PRIMARY  = RGBColor(16, 44, 87)    # #102C57 (Academic Deep Blue)
    ORANGE_ACCENT = RGBColor(227, 86, 42)   # #E3562A (Kravyo Food Brand Accent)
    SLATE_GRAY    = RGBColor(71, 85, 105)   # #475569 (Readable Body Text)
    DARK_TEXT     = RGBColor(15, 23, 42)    # #0F172A (Headings)
    BORDER_LIGHT  = RGBColor(203, 213, 225) # #CBD5E1 (Clean Table Borders)
    BG_CARD       = RGBColor(248, 250, 252) # #F8FAFC (Subtle Card Background)
    WHITE         = RGBColor(255, 255, 255)
    GREEN_SUCCESS = RGBColor(21, 128, 61)   # #15803D (Pass Green)
    
    def add_academic_header(slide, title_text, chapter_context):
        # Top banner with clean academic line
        top_bar = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0), Inches(0), Inches(13.333), Inches(1.05))
        top_bar.fill.solid()
        top_bar.fill.fore_color.rgb = NAVY_PRIMARY
        top_bar.line.fill.background()
        
        # Bottom orange rule
        rule = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0), Inches(1.03), Inches(13.333), Inches(0.04))
        rule.fill.solid()
        rule.fill.fore_color.rgb = ORANGE_ACCENT
        rule.line.fill.background()
        
        tf = top_bar.text_frame
        tf.word_wrap = True
        tf.margin_left = Inches(0.8)
        tf.margin_top = Inches(0.12)
        
        p_sub = tf.paragraphs[0]
        r_sub = p_sub.add_run()
        r_sub.text = chapter_context.upper()
        r_sub.font.name = "Arial"
        r_sub.font.size = Pt(9.5)
        r_sub.font.bold = True
        r_sub.font.color.rgb = ORANGE_ACCENT
        
        p_title = tf.add_paragraph()
        r_title = p_title.add_run()
        r_title.text = title_text
        r_title.font.name = "Arial"
        r_title.font.size = Pt(21)
        r_title.font.bold = True
        r_title.font.color.rgb = WHITE
        
        # Academic Footer
        tx_foot = slide.shapes.add_textbox(Inches(0.8), Inches(7.15), Inches(11.733), Inches(0.3))
        tff = tx_foot.text_frame
        p_f = tff.paragraphs[0]
        r_f = p_f.add_run()
        r_f.text = "Kravyo Cloud Kitchen Platform  |  BCA Major Project (Sem V & VI)  |  Team BCA5_18  |  UTU - SRIMCA"
        r_f.font.name = "Calibri"
        r_f.font.size = Pt(9.5)
        r_f.font.color.rgb = RGBColor(148, 163, 184)

    # =========================================================================
    # SLIDE 1: OFFICIAL UNIVERSITY TITLE SLIDE
    # =========================================================================
    s1 = prs.slides.add_slide(blank_layout)
    bg1 = s1.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0), Inches(0), Inches(13.333), Inches(7.5))
    bg1.fill.solid()
    bg1.fill.fore_color.rgb = NAVY_PRIMARY
    bg1.line.fill.background()
    
    top_strip = s1.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0), Inches(0), Inches(13.333), Inches(0.15))
    top_strip.fill.solid()
    top_strip.fill.fore_color.rgb = ORANGE_ACCENT
    top_strip.line.fill.background()
    
    tb1 = s1.shapes.add_textbox(Inches(1.0), Inches(0.7), Inches(11.333), Inches(3.4))
    tf1 = tb1.text_frame
    tf1.word_wrap = True
    
    p = tf1.paragraphs[0]
    p.alignment = PP_ALIGN.CENTER
    r = p.add_run()
    r.text = "UKA TARSADIA UNIVERSITY\nSHRIMAD RAJCHANDRA VIDYAPEETH"
    r.font.name = "Arial"
    r.font.size = Pt(14)
    r.font.bold = True
    r.font.color.rgb = ORANGE_ACCENT
    
    p = tf1.add_paragraph()
    p.alignment = PP_ALIGN.CENTER
    r = p.add_run()
    r.text = "Department of Computer Science  •  Bachelor of Computer Applications (BCA)"
    r.font.name = "Calibri"
    r.font.size = Pt(12)
    r.font.color.rgb = RGBColor(226, 232, 240)
    p.space_after = Pt(16)
    
    p = tf1.add_paragraph()
    p.alignment = PP_ALIGN.CENTER
    r = p.add_run()
    r.text = "A Major Project Presentation On\n"
    r.font.name = "Calibri"
    r.font.size = Pt(13)
    r.font.italic = True
    r.font.color.rgb = RGBColor(203, 213, 225)
    
    r_main = p.add_run()
    r_main.text = "KRAVYO: A CLOUD KITCHEN PLATFORM\nFOR HOMEMAKERS AND SMALL FOOD BUSINESSES"
    r_main.font.name = "Arial"
    r_main.font.size = Pt(24)
    r_main.font.bold = True
    r_main.font.color.rgb = WHITE
    
    # 3 Real Academic Panels (Student Enrollment, Guide & Project Details)
    w_card = Inches(3.64)
    h_card = Inches(2.5)
    y_card = Inches(4.35)
    
    # Panel 1: Students
    p1 = s1.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), y_card, w_card, h_card)
    p1.fill.solid()
    p1.fill.fore_color.rgb = RGBColor(27, 58, 107)
    p1.line.color.rgb = RGBColor(41, 76, 133)
    tf = p1.text_frame
    tf.margin_left = Inches(0.25)
    tf.margin_top = Inches(0.2)
    p = tf.paragraphs[0]
    r = p.add_run()
    r.text = "SUBMITTED BY (TEAM: BCA5_18)"
    r.font.bold = True
    r.font.size = Pt(11)
    r.font.color.rgb = ORANGE_ACCENT
    
    rolls = [
        "202407100510063",
        "202407100510077",
        "202407100510079",
        "202407100510121"
    ]
    for roll in rolls:
        p = tf.add_paragraph()
        p.space_before = Pt(3)
        r = p.add_run()
        r.text = f"• Enrollment No: {roll}"
        r.font.size = Pt(10.5)
        r.font.color.rgb = WHITE

    # Panel 2: Guide
    p2 = s1.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(4.84), y_card, w_card, h_card)
    p2.fill.solid()
    p2.fill.fore_color.rgb = RGBColor(27, 58, 107)
    p2.line.color.rgb = RGBColor(41, 76, 133)
    tf = p2.text_frame
    tf.margin_left = Inches(0.25)
    tf.margin_top = Inches(0.2)
    p = tf.paragraphs[0]
    r = p.add_run()
    r.text = "PROJECT GUIDANCE"
    r.font.bold = True
    r.font.size = Pt(11)
    r.font.color.rgb = RGBColor(96, 165, 250)
    
    guidance = [
        ("Internal Guide:", "Faculty Project Guide"),
        ("Designation:", "Assistant Professor"),
        ("Department:", "Department of Computer Science"),
        ("Institute:", "Shrimad Rajchandra Vidyapeeth")
    ]
    for lbl, val in guidance:
        p = tf.add_paragraph()
        p.space_before = Pt(2)
        r1 = p.add_run()
        r1.text = f"{lbl} "
        r1.font.bold = True
        r1.font.size = Pt(9.5)
        r1.font.color.rgb = RGBColor(203, 213, 225)
        r2 = p.add_run()
        r2.text = val
        r2.font.size = Pt(10)
        r2.font.color.rgb = WHITE

    # Panel 3: Course Info
    p3 = s1.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(8.88), y_card, w_card, h_card)
    p3.fill.solid()
    p3.fill.fore_color.rgb = RGBColor(27, 58, 107)
    p3.line.color.rgb = RGBColor(41, 76, 133)
    tf = p3.text_frame
    tf.margin_left = Inches(0.25)
    tf.margin_top = Inches(0.2)
    p = tf.paragraphs[0]
    r = p.add_run()
    r.text = "ACADEMIC DETAILS"
    r.font.bold = True
    r.font.size = Pt(11)
    r.font.color.rgb = RGBColor(74, 222, 128)
    
    acad = [
        "Program: Bachelor of Computer Applications",
        "Semester: V & VI",
        "Course: Major Project (Dissertation & Viva)",
        "Database: 22 Relational InnoDB Tables",
        "Academic Year: 2026 - 2027"
    ]
    for ac in acad:
        p = tf.add_paragraph()
        p.space_before = Pt(3)
        r = p.add_run()
        r.text = f"• {ac}"
        r.font.size = Pt(10)
        r.font.color.rgb = WHITE

    # =========================================================================
    # SLIDE 2: SYSTEM DEFINITION, PROBLEM STATEMENT & OBJECTIVES
    # =========================================================================
    s2 = prs.slides.add_slide(blank_layout)
    add_academic_header(s2, "System Definition & Project Objectives", "1.1 & 1.2 Foundation")
    
    # 3 Structured Boxes
    box_w = Inches(3.64)
    box_h = Inches(5.6)
    
    # Box 1: Problem Statement
    b1 = s2.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(1.3), box_w, box_h)
    b1.fill.solid()
    b1.fill.fore_color.rgb = BG_CARD
    b1.line.color.rgb = BORDER_LIGHT
    tf = b1.text_frame
    tf.word_wrap = True
    tf.margin_left = Inches(0.2)
    tf.margin_top = Inches(0.2)
    p = tf.paragraphs[0]
    r = p.add_run()
    r.text = "PROBLEM STATEMENT"
    r.font.bold = True
    r.font.size = Pt(12)
    r.font.color.rgb = ORANGE_ACCENT
    
    probs = [
        ("Commercial Commission Monopoly:", "Aggregators like Swiggy and Zomato take 25% to 35% commission per order, making it impossible for home cooks to survive economically."),
        ("Exclusion of Homemakers:", "Talented domestic home chefs lack legal onboarding, automated order dispatch systems, and hygienic verification tools."),
        ("Absence of Dietary Customization:", "Commercial restaurants do not accommodate strict Jain, diabetic, low-oil, or customized spice preferences."),
        ("Food Waste at Night:", "Excess fresh food cooked by kitchens is discarded at closing time with zero dynamic redistribution mechanism.")
    ]
    for title, body in probs:
        p = tf.add_paragraph()
        p.space_before = Pt(8)
        r1 = p.add_run()
        r1.text = title + "\n"
        r1.font.bold = True
        r1.font.size = Pt(10)
        r1.font.color.rgb = DARK_TEXT
        r2 = p.add_run()
        r2.text = body
        r2.font.size = Pt(9.5)
        r2.font.color.rgb = SLATE_GRAY

    # Box 2: Proposed Kravyo Solution
    b2 = s2.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(4.84), Inches(1.3), box_w, box_h)
    b2.fill.solid()
    b2.fill.fore_color.rgb = BG_CARD
    b2.line.color.rgb = BORDER_LIGHT
    tf = b2.text_frame
    tf.word_wrap = True
    tf.margin_left = Inches(0.2)
    tf.margin_top = Inches(0.2)
    p = tf.paragraphs[0]
    r = p.add_run()
    r.text = "THE KRAVYO SOLUTION"
    r.font.bold = True
    r.font.size = Pt(12)
    r.font.color.rgb = NAVY_PRIMARY
    
    sol = [
        ("Fair 10% Platform Fee:", "Lowers vendor cost barriers, providing homemakers with a sustainable living and direct bank settlements."),
        ("FSSAI Hygiene Certification:", "Ensures every home kitchen is officially registered with government food safety verification before receiving orders."),
        ("15 km Haversine Geofencing:", "Strict distance mathematical guard ensuring hot, fresh home-cooked delivery without quality degradation."),
        ("Zero-Waste Flash Sale Deals:", "Near-closing-hour surplus meals published at 40-50% discount with real-time countdown timers to eliminate waste.")
    ]
    for title, body in sol:
        p = tf.add_paragraph()
        p.space_before = Pt(8)
        r1 = p.add_run()
        r1.text = title + "\n"
        r1.font.bold = True
        r1.font.size = Pt(10)
        r1.font.color.rgb = DARK_TEXT
        r2 = p.add_run()
        r2.text = body
        r2.font.size = Pt(9.5)
        r2.font.color.rgb = SLATE_GRAY

    # Box 3: Specific Project Objectives
    b3 = s2.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(8.88), Inches(1.3), box_w, box_h)
    b3.fill.solid()
    b3.fill.fore_color.rgb = BG_CARD
    b3.line.color.rgb = BORDER_LIGHT
    tf = b3.text_frame
    tf.word_wrap = True
    tf.margin_left = Inches(0.2)
    tf.margin_top = Inches(0.2)
    p = tf.paragraphs[0]
    r = p.add_run()
    r.text = "CORE PROJECT OBJECTIVES"
    r.font.bold = True
    r.font.size = Pt(12)
    r.font.color.rgb = GREEN_SUCCESS
    
    objs = [
        ("1. Multi-Portal Separation:", "Implement 4 isolated role portals (Customer, Chef, Delivery, Admin) with zero cross-tenant session leaks."),
        ("2. Automated Courier Cascade:", "Develop a 4-stage dispatch algorithm prioritizing the nearest idle driver with automatic radius expansion."),
        ("3. Tiffin Batch Schedules:", "Allow customers to book 7-day weekly and 30-day monthly meal plans with corridor milk-run batch delivery."),
        ("4. Automated Instant Refunds:", "Integrate server-side REST API triggers to refund Razorpay transactions instantly upon chef cancellation.")
    ]
    for title, body in objs:
        p = tf.add_paragraph()
        p.space_before = Pt(8)
        r1 = p.add_run()
        r1.text = title + "\n"
        r1.font.bold = True
        r1.font.size = Pt(10)
        r1.font.color.rgb = DARK_TEXT
        r2 = p.add_run()
        r2.text = body
        r2.font.size = Pt(9.5)
        r2.font.color.rgb = SLATE_GRAY

    # =========================================================================
    # SLIDE 3: SYSTEM REQUIREMENTS & HARDWARE / SOFTWARE SPECIFICATIONS
    # =========================================================================
    s3 = prs.slides.add_slide(blank_layout)
    add_academic_header(s3, "System Requirements & Specifications", "1.4 & 1.5 Environment")
    
    # Left Box: Hardware Requirements
    h_w = Inches(5.66)
    b_hw = s3.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(1.35), h_w, Inches(5.5))
    b_hw.fill.solid()
    b_hw.fill.fore_color.rgb = BG_CARD
    b_hw.line.color.rgb = BORDER_LIGHT
    tf = b_hw.text_frame
    tf.word_wrap = True
    tf.margin_left = Inches(0.25)
    tf.margin_top = Inches(0.25)
    p = tf.paragraphs[0]
    r = p.add_run()
    r.text = "HARDWARE REQUIREMENTS (TABLE 1.1 & 1.2)"
    r.font.bold = True
    r.font.size = Pt(12)
    r.font.color.rgb = NAVY_PRIMARY
    
    hw_data = [
        ("Server-Side Specifications:", [
            "Processor: Dual-Core Intel Core i3 / AMD Ryzen 3 or higher",
            "RAM: Minimum 4 GB RAM (8 GB Recommended for live testing)",
            "Storage: 20 GB free disk space (SSD recommended for MySQL speed)",
            "Network: High-speed broadband connection (minimum 10 Mbps uplink)"
        ]),
        ("Client-Side Specifications:", [
            "User Terminal: Desktop, Laptop, Tablet, or Smartphone",
            "Screen Resolution: 1280x720 minimum (Responsive down to 360px mobile)",
            "Input Devices: Standard Keyboard, Mouse, or Touchscreen Display",
            "Client RAM: Minimum 2 GB RAM (for Chrome DOM rendering)"
        ])
    ]
    for grp_title, grp_items in hw_data:
        p = tf.add_paragraph()
        p.space_before = Pt(10)
        r = p.add_run()
        r.text = grp_title
        r.font.bold = True
        r.font.size = Pt(10.5)
        r.font.color.rgb = ORANGE_ACCENT
        for item in grp_items:
            p = tf.add_paragraph()
            p.space_before = Pt(3)
            r = p.add_run()
            r.text = f"• {item}"
            r.font.size = Pt(9.5)
            r.font.color.rgb = SLATE_GRAY

    # Right Box: Software Specifications
    b_sw = s3.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(6.86), Inches(1.35), h_w, Inches(5.5))
    b_sw.fill.solid()
    b_sw.fill.fore_color.rgb = BG_CARD
    b_sw.line.color.rgb = BORDER_LIGHT
    tf = b_sw.text_frame
    tf.word_wrap = True
    tf.margin_left = Inches(0.25)
    tf.margin_top = Inches(0.25)
    p = tf.paragraphs[0]
    r = p.add_run()
    r.text = "SOFTWARE SPECIFICATIONS & TECH STACK"
    r.font.bold = True
    r.font.size = Pt(12)
    r.font.color.rgb = NAVY_PRIMARY
    
    sw_data = [
        ("Operating System: ", "Microsoft Windows 10 / 11 64-bit (or Ubuntu Linux 22.04 LTS)"),
        ("Local Web Server: ", "Apache HTTP Server 2.4.58 (via XAMPP Stack with mod_rewrite)"),
        ("Backend Language: ", "PHP 8.4 (Custom Model-View-Controller Object-Oriented Architecture)"),
        ("Database Engine: ", "MySQL 8.0 / MariaDB 10.4 (InnoDB Engine with Foreign Keys)"),
        ("Frontend Technology: ", "HTML5, CSS3, Bootstrap 5.3, Vanilla JavaScript (ES6+ Modules)"),
        ("Live Communication: ", "Pusher WebSockets API (Real-time live driver coordinate broadcasting)"),
        ("Payment Gateway: ", "Razorpay Payment Gateway (Client-side Checkout + Server-side REST Refunds)"),
        ("Mapping Service: ", "Google Maps JavaScript API v3, Geocoding API, Routes API with Server Cache"),
        ("Testing Suite: ", "Selenium WebDriver 4.18, PHPUnit 10.x Test Runner, MySQL Workbench 8.0")
    ]
    for k, v in sw_data:
        p = tf.add_paragraph()
        p.space_before = Pt(5)
        r1 = p.add_run()
        r1.text = k
        r1.font.bold = True
        r1.font.size = Pt(9.5)
        r1.font.color.rgb = DARK_TEXT
        r2 = p.add_run()
        r2.text = v
        r2.font.size = Pt(9)
        r2.font.color.rgb = SLATE_GRAY

    # =========================================================================
    # SLIDE 4: LISTING OF FEATURES
    # =========================================================================
    s4 = prs.slides.add_slide(blank_layout)
    add_academic_header(s4, "Listing of System Features (Functional Scope)", "1.3 Feature Matrix")
    
    # 2 Columns of Feature Cards
    feats_left = [
        ("1. Multi-Tenant Role Isolation", "Four distinct authentication contexts (Customer, Chef, Delivery, Admin) run concurrently using distinct cookie keys (KRAVYO_CUSTOMER, KRAVYO_CHEF, etc.) with zero session leakage."),
        ("2. Haversine 15 km Radial Geofencing", "Protects fresh meal temperature and texture by strictly rejecting discovery and ordering for locations beyond 15 km from the kitchen using spherical distance trigonometry."),
        ("3. Dietary & Cooking Customization", "Customers can customize spice levels (Mild, Medium, Spicy), request less oil, and filter strictly for Jain or Diabetic-friendly home-cooked dishes."),
        ("4. Recurring Weekly & Monthly Subscriptions", "Subscribers choose daily meal schedules (Breakfast: 8-9am, Lunch: 12-1pm, Dinner: 7-8pm) with automated recurring delivery generation."),
        ("5. Zero-Waste Dynamic Food Clearance", "Chefs publish unsold portions at 40-50% discount near closing time; meals feature real-time countdown timers and automatic expiry sweeps.")
    ]
    feats_right = [
        ("6. 4-Stage Courier Cascade Dispatch", "Smart dispatch algorithm searches for nearest idle driver within 4 km, automatically expanding to 8 km and city broadcast if unaccepted in 45 seconds."),
        ("7. Real-Time Pusher Live GPS Tracking", "Streams courier live coordinates (watchPosition) directly to the customer tracking map using WebSockets without resource-draining page reloads."),
        ("8. 4-Digit Handshake Delivery OTP", "Single-use confidential numeric token generated upon checkout; driver must input and verify OTP before order is marked 'Delivered'."),
        ("9. Multi-Tiffin Milk-Run Corridor Batching", "Groups up to 6 customer tiffins along the same geographic delivery corridor, providing riders with turn-by-turn waypoints and empty dabba recovery."),
        ("10. Automated Instant Razorpay Refunds", "When a chef rejects an order or courier times out, a server-to-server REST API trigger executes an instant 100% refund without human intervention.")
    ]
    
    col_w = Inches(5.66)
    for idx, (t, d) in enumerate(feats_left):
        y = Inches(1.35) + idx * Inches(1.1)
        c = s4.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), y, col_w, Inches(0.98))
        c.fill.solid()
        c.fill.fore_color.rgb = BG_CARD
        c.line.color.rgb = BORDER_LIGHT
        tf = c.text_frame
        tf.word_wrap = True
        tf.margin_left = Inches(0.2)
        tf.margin_top = Inches(0.12)
        p = tf.paragraphs[0]
        r1 = p.add_run()
        r1.text = t
        r1.font.bold = True
        r1.font.size = Pt(10.5)
        r1.font.color.rgb = ORANGE_ACCENT
        p2 = tf.add_paragraph()
        r2 = p2.add_run()
        r2.text = d
        r2.font.size = Pt(9)
        r2.font.color.rgb = SLATE_GRAY
        
    for idx, (t, d) in enumerate(feats_right):
        y = Inches(1.35) + idx * Inches(1.1)
        c = s4.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(6.86), y, col_w, Inches(0.98))
        c.fill.solid()
        c.fill.fore_color.rgb = BG_CARD
        c.line.color.rgb = BORDER_LIGHT
        tf = c.text_frame
        tf.word_wrap = True
        tf.margin_left = Inches(0.2)
        tf.margin_top = Inches(0.12)
        p = tf.paragraphs[0]
        r1 = p.add_run()
        r1.text = t
        r1.font.bold = True
        r1.font.size = Pt(10.5)
        r1.font.color.rgb = NAVY_PRIMARY
        p2 = tf.add_paragraph()
        r2 = p2.add_run()
        r2.text = d
        r2.font.size = Pt(9)
        r2.font.color.rgb = SLATE_GRAY

    # =========================================================================
    # SLIDE 5: COMPARATIVE STUDY OF FEATURES
    # =========================================================================
    s5 = prs.slides.add_slide(blank_layout)
    add_academic_header(s5, "Comparative Study of Current Semester System Features", "1.3 Evaluation Matrix")
    
    # Table Matrix
    headers = ["Feature / Capability", "Kravyo Platform", "Commercial Aggregators (Swiggy / Zomato)", "Traditional Offline Tiffin"]
    data = [
        ["Commission Rate", "10% Flat Rate (Homemaker-Friendly)", "25% to 35% High Margin", "0% (No platform or discovery)"],
        ["Target Vendor Base", "Verified Domestic Home Chefs", "Commercial Restaurants & Eateries", "Local unorganized home cooks"],
        ["Dietary Customization", "Granular (Spice, Less Oil, Jain, Diabetic)", "Limited to general cooking notes", "Informal verbal requests"],
        ["Recurring Subscriptions", "Weekly & Monthly Automated Batch Plans", "Ad-hoc daily re-ordering only", "Fixed inflexible monthly cash plans"],
        ["Surplus Food Clearance", "Real-Time Zero-Waste Deals with Timer", "None (Surplus food discarded)", "Manual personal distribution"],
        ["Logistics & Dispatch", "4-Stage Cascade + Multi-Stop Milk Runs", "Point-to-point single order dispatch", "Manual self-delivery by cook"],
        ["Delivery Handshake", "Cryptographic 4-Digit Handshake OTP", "Optional or PIN-less delivery", "Verbal trust / no verification"],
        ["Refund Automation", "Instant Server-Side Razorpay API Trigger", "Manual support ticket investigation", "Informal verbal credit negotiation"]
    ]
    
    rows = len(data) + 1
    cols = 4
    t_shape = s5.shapes.add_table(rows, cols, Inches(0.8), Inches(1.35), Inches(11.733), Inches(5.4))
    tbl = t_shape.table
    tbl.columns[0].width = Inches(2.3)
    tbl.columns[1].width = Inches(3.3)
    tbl.columns[2].width = Inches(3.3)
    tbl.columns[3].width = Inches(2.833)
    
    for c_idx, h_text in enumerate(headers):
        cell = tbl.cell(0, c_idx)
        cell.fill.solid()
        cell.fill.fore_color.rgb = NAVY_PRIMARY
        p = cell.text_frame.paragraphs[0]
        r = p.add_run()
        r.text = h_text
        r.font.bold = True
        r.font.size = Pt(10)
        r.font.color.rgb = WHITE
        
    for r_idx, row_vals in enumerate(data, 1):
        for c_idx, val in enumerate(row_vals):
            cell = tbl.cell(r_idx, c_idx)
            cell.fill.solid()
            if c_idx == 1:
                cell.fill.fore_color.rgb = RGBColor(254, 243, 199) # Highlight Kravyo
            elif r_idx % 2 == 1:
                cell.fill.fore_color.rgb = RGBColor(248, 250, 252)
            else:
                cell.fill.fore_color.rgb = WHITE
                
            p = cell.text_frame.paragraphs[0]
            r = p.add_run()
            r.text = val
            r.font.size = Pt(9.5)
            if c_idx == 1:
                r.font.bold = True
                r.font.color.rgb = RGBColor(180, 83, 9)
            else:
                r.font.color.rgb = DARK_TEXT

    # =========================================================================
    # SLIDE 6: SYSTEM PROCESS FLOW
    # =========================================================================
    s6 = prs.slides.add_slide(blank_layout)
    add_academic_header(s6, "End-to-End System Process Flow", "1.1 Operational Workflow")
    
    # Left: Clear Steps
    tx = s6.shapes.add_textbox(Inches(0.8), Inches(1.35), Inches(5.5), Inches(5.5))
    tf = tx.text_frame
    tf.word_wrap = True
    
    flow_steps = [
        ("Step 1: Customer Discovery & Cart Assembly", "Customer enters delivery address, selects verified kitchen within 15 km, specifies dietary preferences (Spice, Less Oil, Jain), and authorizes payment via Razorpay or COD."),
        ("Step 2: Kitchen Order Reception & Audio Chime", "Kitchen receives real-time order alert with continuous Web Audio chime (beep.wav). Home chef clicks 'Accept Order' and starts cooking."),
        ("Step 3: 4-Stage Cascade Courier Dispatch", "Kravyo pings nearest idle courier within 4 km. If unaccepted in 45s, radius cascades to 8 km, then city-wide broadcast. Driver accepts via modal."),
        ("Step 4: Pickup & Real-Time GPS Tracking", "Courier collects sealed dabba from home kitchen. Customer tracks live courier coordinates on Google Maps streamed via Pusher WebSockets."),
        ("Step 5: 4-Digit Handshake OTP Verification", "Upon arrival, courier requests customer's secret 4-digit OTP. System verifies code, updates order to 'Delivered', and releases chef & rider payouts.")
    ]
    for s_title, s_desc in flow_steps:
        p = tf.add_paragraph()
        p.space_before = Pt(8)
        r1 = p.add_run()
        r1.text = s_title + "\n"
        r1.font.bold = True
        r1.font.size = Pt(10.5)
        r1.font.color.rgb = ORANGE_ACCENT
        r2 = p.add_run()
        r2.text = s_desc
        r2.font.size = Pt(9.5)
        r2.font.color.rgb = SLATE_GRAY

    # Right: Embedded Real Diagram
    img_flow = r"c:\xampp\htdocs\Kravyo\diagrams\system_process_flow.png"
    if os.path.exists(img_flow):
        s6.shapes.add_picture(img_flow, Inches(6.5), Inches(1.4), width=Inches(6.0))

    # =========================================================================
    # SLIDE 7: SYSTEM DESIGN & 22-TABLE DATABASE SCHEMA
    # =========================================================================
    s7 = prs.slides.add_slide(blank_layout)
    add_academic_header(s7, "System Architecture & Database Schema", "3.1 & 3.7 Technical Design")
    
    # Left: MVC Architecture
    b_l = s7.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(1.35), Inches(5.66), Inches(5.5))
    b_l.fill.solid()
    b_l.fill.fore_color.rgb = BG_CARD
    b_l.line.color.rgb = BORDER_LIGHT
    tf = b_l.text_frame
    tf.word_wrap = True
    tf.margin_left = Inches(0.25)
    tf.margin_top = Inches(0.2)
    p = tf.paragraphs[0]
    r = p.add_run()
    r.text = "CUSTOM LIGHTWEIGHT PHP MVC ARCHITECTURE"
    r.font.bold = True
    r.font.size = Pt(12)
    r.font.color.rgb = NAVY_PRIMARY
    
    arch = [
        ("Front Controller (index.php): ", "Single entry point routing all requests through Router.php regex mapping engine."),
        ("Middleware Session Guards: ", "Enforces role authentication (Middleware::chefAuth, customerAuth, etc.) preventing unauthorized access."),
        ("Multi-Tenant Session Cookie Namespaces: ", "KRAVYO_CUSTOMER, KRAVYO_CHEF, KRAVYO_DELIVERY, KRAVYO_ADMIN cookies run simultaneously."),
        ("Service Layer Decoupling: ", "DispatchService.php handles cascade logic; RazorpayService.php handles instant refund execution."),
        ("Singleton Database Wrapper: ", "core/Database.php encapsulates PDO with prepared statements, preventing SQL injection.")
    ]
    for k, v in arch:
        p = tf.add_paragraph()
        p.space_before = Pt(8)
        r1 = p.add_run()
        r1.text = k
        r1.font.bold = True
        r1.font.size = Pt(10)
        r1.font.color.rgb = DARK_TEXT
        r2 = p.add_run()
        r2.text = v
        r2.font.size = Pt(9.5)
        r2.font.color.rgb = SLATE_GRAY

    # Right: 22 Database Tables
    b_r = s7.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(6.86), Inches(1.35), Inches(5.66), Inches(5.5))
    b_r.fill.solid()
    b_r.fill.fore_color.rgb = BG_CARD
    b_r.line.color.rgb = BORDER_LIGHT
    tf = b_r.text_frame
    tf.word_wrap = True
    tf.margin_left = Inches(0.25)
    tf.margin_top = Inches(0.2)
    p = tf.paragraphs[0]
    r = p.add_run()
    r.text = "22 NORMALIZED INNODB DATABASE TABLES"
    r.font.bold = True
    r.font.size = Pt(12)
    r.font.color.rgb = GREEN_SUCCESS
    
    tbl_grps = [
        ("User & Authentication (5 Tables):", "customers, chefs, delivery_users, admins, pending_registrations"),
        ("Catalogue & Core Ordering (5 Tables):", "kitchens, categories, menu_items, orders, order_items"),
        ("Tiffin Subscriptions & Batches (5 Tables):", "tiffin_subscriptions, customer_subscriptions, subscription_deliveries, delivery_runs, delivery_run_stops"),
        ("Surplus Food Rescue (1 Table):", "zero_waste_items (Near-closing discounts with auto-expiry)"),
        ("Logistics & Communication (3 Tables):", "delivery_partners, delivery_offers, notifications"),
        ("Audit & Customer Feedback (3 Tables):", "addresses (with is_deleted soft-delete), reviews, customer_reports")
    ]
    for k, v in tbl_grps:
        p = tf.add_paragraph()
        p.space_before = Pt(8)
        r1 = p.add_run()
        r1.text = k + "\n"
        r1.font.bold = True
        r1.font.size = Pt(10)
        r1.font.color.rgb = DARK_TEXT
        r2 = p.add_run()
        r2.text = v
        r2.font.size = Pt(9.5)
        r2.font.color.rgb = SLATE_GRAY

    # =========================================================================
    # SLIDE 8: ACTOR DASHBOARDS - CUSTOMER & HOME CHEF
    # =========================================================================
    s8 = prs.slides.add_slide(blank_layout)
    add_academic_header(s8, "Actor Dashboards — Customer & Home Chef", "5.1 & 5.2 Implementation")
    
    # Left: Customer Dashboard
    img_cust = r"c:\xampp\htdocs\Kravyo\diagrams\dashboard_customer.png"
    if os.path.exists(img_cust):
        s8.shapes.add_picture(img_cust, Inches(0.8), Inches(1.35), width=Inches(5.66))
    tx1 = s8.shapes.add_textbox(Inches(0.8), Inches(5.15), Inches(5.66), Inches(1.8))
    tf1 = tx1.text_frame
    tf1.word_wrap = True
    p = tf1.paragraphs[0]
    r = p.add_run()
    r.text = "Customer Discovery & Food Feed (Figure 5.1)"
    r.font.bold = True
    r.font.size = Pt(11)
    r.font.color.rgb = ORANGE_ACCENT
    p2 = tf1.add_paragraph()
    r2 = p2.add_run()
    r2.text = "• City selector & Haversine geocoded nearby kitchens (<=15 km)\n• Real-time zero-waste surplus meal ticker with live countdown\n• Categorized browsing by dietary tags (Jain, Diabetic, Healthy)"
    r2.font.size = Pt(9.5)
    r2.font.color.rgb = SLATE_GRAY

    # Right: Chef Dashboard
    img_chef = r"c:\xampp\htdocs\Kravyo\diagrams\dashboard_chef.png"
    if os.path.exists(img_chef):
        s8.shapes.add_picture(img_chef, Inches(6.86), Inches(1.35), width=Inches(5.66))
    tx2 = s8.shapes.add_textbox(Inches(6.86), Inches(5.15), Inches(5.66), Inches(1.8))
    tf2 = tx2.text_frame
    tf2.word_wrap = True
    p = tf2.paragraphs[0]
    r = p.add_run()
    r.text = "Home Chef Kitchen Operations Console (Figure 5.2)"
    r.font.bold = True
    r.font.size = Pt(11)
    r.font.color.rgb = NAVY_PRIMARY
    p2 = tf2.add_paragraph()
    r2 = p2.add_run()
    r2.text = "• Real-time order queue with continuous Web Audio chime alert\n• 1-click state transitions ('Accept' -> 'Preparing' -> 'Ready')\n• Daily batch preparation roster and financial net earning ledger"
    r2.font.size = Pt(9.5)
    r2.font.color.rgb = SLATE_GRAY

    # =========================================================================
    # SLIDE 9: ACTOR DASHBOARDS - DELIVERY PARTNER & MASTER ADMIN
    # =========================================================================
    s9 = prs.slides.add_slide(blank_layout)
    add_academic_header(s9, "Actor Dashboards — Delivery & Master Admin", "5.3 & 5.4 Implementation")
    
    # Left: Delivery Dashboard
    img_del = r"c:\xampp\htdocs\Kravyo\diagrams\dashboard_delivery.png"
    if os.path.exists(img_del):
        s9.shapes.add_picture(img_del, Inches(0.8), Inches(1.35), width=Inches(5.66))
    tx1 = s9.shapes.add_textbox(Inches(0.8), Inches(5.15), Inches(5.66), Inches(1.8))
    tf1 = tx1.text_frame
    tf1.word_wrap = True
    p = tf1.paragraphs[0]
    r = p.add_run()
    r.text = "Delivery Partner Logistics Terminal (Figure 5.3)"
    r.font.bold = True
    r.font.size = Pt(11)
    r.font.color.rgb = GREEN_SUCCESS
    p2 = tf1.add_paragraph()
    r2 = p2.add_run()
    r2.text = "• Online/Offline duty toggle streaming live GPS coordinates\n• Cascade dispatch claim modal with earning estimate & timer\n• Multi-tiffin milk-run corridor route manifest & OTP handover pad"
    r2.font.size = Pt(9.5)
    r2.font.color.rgb = SLATE_GRAY

    # Right: Admin Dashboard
    img_adm = r"c:\xampp\htdocs\Kravyo\diagrams\dashboard_admin.png"
    if os.path.exists(img_adm):
        s9.shapes.add_picture(img_adm, Inches(6.86), Inches(1.35), width=Inches(5.66))
    tx2 = s9.shapes.add_textbox(Inches(6.86), Inches(5.15), Inches(5.66), Inches(1.8))
    tf2 = tx2.text_frame
    tf2.word_wrap = True
    p = tf2.paragraphs[0]
    r = p.add_run()
    r.text = "Master Administrator Platform Console (Figure 5.4)"
    r.font.bold = True
    r.font.size = Pt(11)
    r.font.color.rgb = NAVY_PRIMARY
    p2 = tf2.add_paragraph()
    r2 = p2.add_run()
    r2.text = "• Platform-wide GMV, order volume, and chef approval queue\n• Live dispatch monitor with manual rider re-assignment override\n• Automated instant Razorpay refund triggers for customer disputes"
    r2.font.size = Pt(9.5)
    r2.font.color.rgb = SLATE_GRAY

    # =========================================================================
    # SLIDE 10: USER-WISE OPERATIONAL & ANALYTICAL REPORTS
    # =========================================================================
    s10 = prs.slides.add_slide(blank_layout)
    add_academic_header(s10, "User-Wise Operational & Analytical Reports", "Chapter 5 Reports")
    
    rep_cards = [
        ("Customer Tax Invoice", "report_customer_invoice.png", "Figure 5.5", [
            "Customer & Kitchen GST details",
            "Itemized breakdown with spice notes",
            "5% GST & delivery fee calculation",
            "Official printable receipt format"
        ]),
        ("Daily Chef Batch Prep Roster", "report_chef_roster.png", "Figure 5.6", [
            "Aggregated daily tiffin meal counts",
            "Breakfast, Lunch, Dinner split",
            "Jain & dietary counter highlights",
            "Corridor delivery run assignment"
        ]),
        ("Platform Financial Settlement Audit", "report_admin_gmv.png", "Figure 5.7", [
            "Gross Merchandise Value (GMV)",
            "10% Platform fee calculation",
            "Chef net payout ledger",
            "Razorpay refund settlement logs"
        ])
    ]
    
    col_w = Inches(3.64)
    for idx, (title, img_f, fig_id, bullets) in enumerate(rep_cards):
        x = Inches(0.8) + idx * Inches(4.04)
        c = s10.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, x, Inches(1.35), col_w, Inches(5.5))
        c.fill.solid()
        c.fill.fore_color.rgb = BG_CARD
        c.line.color.rgb = BORDER_LIGHT
        
        r_img_path = os.path.join(r"c:\xampp\htdocs\Kravyo\diagrams", img_f)
        if os.path.exists(r_img_path):
            s10.shapes.add_picture(r_img_path, x + Inches(0.15), Inches(1.5), width=Inches(3.34))
            
        tx = s10.shapes.add_textbox(x + Inches(0.15), Inches(4.2), Inches(3.34), Inches(2.5))
        tf = tx.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        r = p.add_run()
        r.text = f"{title}\n({fig_id})"
        r.font.bold = True
        r.font.size = Pt(11)
        r.font.color.rgb = ORANGE_ACCENT if idx == 0 else (NAVY_PRIMARY if idx == 1 else GREEN_SUCCESS)
        
        for b in bullets:
            p = tf.add_paragraph()
            r = p.add_run()
            r.text = f"• {b}"
            r.font.size = Pt(9.5)
            r.font.color.rgb = SLATE_GRAY

    # =========================================================================
    # SLIDE 11: QUALITY ASSURANCE & TESTING SPECIFICATIONS (3 TOOLS)
    # =========================================================================
    s11 = prs.slides.add_slide(blank_layout)
    add_academic_header(s11, "Quality Assurance & Testing Specifications", "6.2 Table 6.1 (3 Core Tools)")
    
    tool_w = Inches(11.733)
    tool_h = Inches(1.65)
    
    tools = [
        ("1. SELENIUM WEBDRIVER 4.18", NAVY_PRIMARY, "Automated GUI, Usability & End-to-End Regression Testing",
         "Automates real Google Chrome browser sessions (options: --headless=new, --window-size=1280,840) to simulate multi-step user workflows: customer registration, login authentication, dietary modal spice customizer, checkout validation, and 4-digit OTP handshake confirmation without human manual clicking."),
        
        ("2. PHPUNIT 10.x & PHP CLI TEST RUNNER", GREEN_SUCCESS, "Headless Unit, Structural & White-Box Testing",
         "Executes 38 automated headless assertions across backend business logic: Bcrypt cost-10 salt hashing, 15 km Haversine spherical distance formulas, 4-digit numeric OTP generation, 4-stage cascade dispatch courier priority, and 10% platform fee financial split arithmetic in 0.370 seconds."),
        
        ("3. MYSQL WORKBENCH 8.0 & DBUNIT", ORANGE_ACCENT, "Database Schema, Foreign Key & Rollback Testing",
         "Validates 22 normalized InnoDB tables, foreign key referential integrity (ON DELETE CASCADE), spatial coordinates precision (DECIMAL(10,8)), soft-deletion safety (is_deleted = 1 on addresses), and transactional rollback on simulated payment failures.")
    ]
    
    for idx, (t_title, t_col, t_cat, t_desc) in enumerate(tools):
        y = Inches(1.35) + idx * Inches(1.85)
        c = s11.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), y, tool_w, tool_h)
        c.fill.solid()
        c.fill.fore_color.rgb = BG_CARD
        c.line.color.rgb = BORDER_LIGHT
        tf = c.text_frame
        tf.word_wrap = True
        tf.margin_left = Inches(0.25)
        tf.margin_top = Inches(0.18)
        
        p = tf.paragraphs[0]
        r = p.add_run()
        r.text = t_title
        r.font.bold = True
        r.font.size = Pt(12)
        r.font.color.rgb = t_col
        
        p = tf.add_paragraph()
        r1 = p.add_run()
        r1.text = "Testing Category: "
        r1.font.bold = True
        r1.font.size = Pt(10)
        r1.font.color.rgb = DARK_TEXT
        r2 = p.add_run()
        r2.text = t_cat
        r2.font.size = Pt(10)
        r2.font.italic = True
        r2.font.color.rgb = ORANGE_ACCENT
        
        p = tf.add_paragraph()
        p.space_before = Pt(3)
        r1 = p.add_run()
        r1.text = "Operational Scope in Kravyo: "
        r1.font.bold = True
        r1.font.size = Pt(9.5)
        r1.font.color.rgb = DARK_TEXT
        r2 = p.add_run()
        r2.text = t_desc
        r2.font.size = Pt(9.5)
        r2.font.color.rgb = SLATE_GRAY

    # =========================================================================
    # SLIDE 12: AUTOMATED TESTING EXECUTION & REAL SCREENSHOT (FIGURE 6.1)
    # =========================================================================
    s12 = prs.slides.add_slide(blank_layout)
    add_academic_header(s12, "Automated Test Suite Execution (Figure 6.1)", "6.3 Live System Verification")
    
    # Left: Real Live Screenshot
    test_img = r"c:\xampp\htdocs\Kravyo\diagrams\automated_testing_execution.png"
    if os.path.exists(test_img):
        s12.shapes.add_picture(test_img, Inches(0.8), Inches(1.35), width=Inches(7.2))
        
    # Right: Academic Metrics Box
    m_box = s12.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(8.2), Inches(1.35), Inches(4.333), Inches(5.4))
    m_box.fill.solid()
    m_box.fill.fore_color.rgb = BG_CARD
    m_box.line.color.rgb = BORDER_LIGHT
    tf = m_box.text_frame
    tf.word_wrap = True
    tf.margin_left = Inches(0.25)
    tf.margin_top = Inches(0.25)
    
    p = tf.paragraphs[0]
    r = p.add_run()
    r.text = "EXECUTION BENCHMARKS"
    r.font.bold = True
    r.font.size = Pt(12)
    r.font.color.rgb = GREEN_SUCCESS
    
    metrics = [
        ("Total Automated Tests: ", "12 / 12 Test Cases (100% Pass)"),
        ("Assertion Verifications: ", "38 Assertions (0 Failures)"),
        ("Execution Latency: ", "0.370 seconds (High Efficiency)"),
        ("Peak Memory Usage: ", "0.56 MB (Zero Resource Leaks)"),
        ("Browser Automation: ", "Selenium WebDriver 4.18 (Chrome)"),
        ("CLI Assertion Engine: ", "PHPUnit 10.x & PHP CLI Runner"),
        ("Database Verification: ", "22 Normalized InnoDB Tables Verified")
    ]
    for k, v in metrics:
        p = tf.add_paragraph()
        p.space_before = Pt(8)
        r1 = p.add_run()
        r1.text = k + "\n"
        r1.font.bold = True
        r1.font.size = Pt(10)
        r1.font.color.rgb = DARK_TEXT
        r2 = p.add_run()
        r2.text = v
        r2.font.size = Pt(9.5)
        r2.font.color.rgb = NAVY_PRIMARY
        
    p = tf.add_paragraph()
    p.space_before = Pt(14)
    r = p.add_run()
    r.text = "STATUS: ALL 12 AUTOMATED UNIT & INTEGRATION ASSERTIONS VERIFIED [100% SUCCESS]"
    r.font.bold = True
    r.font.size = Pt(9.5)
    r.font.color.rgb = GREEN_SUCCESS

    # =========================================================================
    # SLIDE 12B: AUTOMATED FUNCTIONAL TESTING EXECUTION (FIGURE 6.2)
    # =========================================================================
    s12b = prs.slides.add_slide(blank_layout)
    add_academic_header(s12b, "Automated Functional Testing Execution (Figure 6.2)", "6.4.1 Functional Verification Proof")
    
    # Left: Real Live Functional Screenshot
    func_img = r"c:\xampp\htdocs\Kravyo\diagrams\selenium_functional_testing_real.png"
    if os.path.exists(func_img):
        s12b.shapes.add_picture(func_img, Inches(0.8), Inches(1.35), width=Inches(7.2))
        
    # Right: Academic Metrics Box for Functional Testing
    f_box = s12b.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(8.2), Inches(1.35), Inches(4.333), Inches(5.4))
    f_box.fill.solid()
    f_box.fill.fore_color.rgb = BG_CARD
    f_box.line.color.rgb = BORDER_LIGHT
    ftf = f_box.text_frame
    ftf.word_wrap = True
    ftf.margin_left = Inches(0.25)
    ftf.margin_top = Inches(0.25)
    
    fp = ftf.paragraphs[0]
    fr = fp.add_run()
    fr.text = "FUNCTIONAL TEST RESULTS"
    fr.font.bold = True
    fr.font.size = Pt(12)
    fr.font.color.rgb = GREEN_SUCCESS
    
    func_metrics = [
        ("Total Functional Tests: ", "8 / 8 Test Cases (100% Pass)"),
        ("Testing Harness Tool: ", "Selenium IDE (Mozilla Firefox)"),
        ("FT01: Customer Registration: ", "DOM Input Elements Verified (PASS)"),
        ("FT02: Kitchen FSSAI Form: ", "Kitchen Profile Form Verified (PASS)"),
        ("FT03: Cart Customization: ", "Spice & Portion Attributes (PASS)"),
        ("FT04: Single-Kitchen Guard: ", "Cross-Vendor Cart Barrier (PASS)"),
        ("FT05: Tiffin Subscription: ", "7-Day Meal Scheduler (PASS)"),
        ("FT06: Zero-Waste Clearance: ", "Flash Discount Timers (PASS)"),
        ("FT07: Chef Order Workflow: ", "Audio Alert & Order Actions (PASS)"),
        ("FT08: Courier Handshake OTP: ", "4-Digit Handshake Verification (PASS)")
    ]
    for k, v in func_metrics:
        fp = ftf.add_paragraph()
        fp.space_before = Pt(4)
        fr1 = fp.add_run()
        fr1.text = k + "\n"
        fr1.font.bold = True
        fr1.font.size = Pt(9.5)
        fr1.font.color.rgb = DARK_TEXT
        fr2 = fp.add_run()
        fr2.text = v
        fr2.font.size = Pt(9)
        fr2.font.color.rgb = NAVY_PRIMARY
        
    fp = ftf.add_paragraph()
    fp.space_before = Pt(8)
    fr = fp.add_run()
    fr.text = "STATUS: 8/8 FUNCTIONAL FLOWS VERIFIED [0 FAILURES]"
    fr.font.bold = True
    fr.font.size = Pt(9.5)
    fr.font.color.rgb = GREEN_SUCCESS

    # =========================================================================
    # SLIDE 13: TEST CASES REPORT ACROSS TESTING TYPES
    # =========================================================================
    s13 = prs.slides.add_slide(blank_layout)

    add_academic_header(s13, "Detailed Test Case Reports by Testing Dimension", "6.4 Academic Test Dimensions")
    
    test_dims = [
        ("1. FUNCTIONAL TESTING (FT-01 to FT-08)", NAVY_PRIMARY, [
            "FT-01: Customer Registration & Session Cookie Set (Pass)",
            "FT-02: Kitchen FSSAI License Document Upload (Pass)",
            "FT-03: Cart Dietary & Spice Customization (Pass)",
            "FT-04: Single-Kitchen Restriction Warning Modal (Pass)",
            "FT-05: Recurring Tiffin Daily Delivery Scheduling (Pass)",
            "FT-06: Zero-Waste Clearance Countdown Timer (Pass)",
            "FT-07: Chef Order Reception & Web Audio Alert (Pass)",
            "FT-08: Delivery 4-Digit Handshake OTP Verification (Pass)"
        ]),
        ("2. USABILITY & UI TESTING (UT-01 to UT-06)", ORANGE_ACCENT, [
            "UT-01: Mobile Touch Target Accessibility (Pass)",
            "UT-02: Dietary Badge Contrast (Jain/Diabetic) (Pass)",
            "UT-03: Chef Web Audio Chime Audibility (Pass)",
            "UT-04: Live Google Maps Pin Drag/Zoom (Pass)",
            "UT-05: Countdown Expiry Timer Readability (Pass)",
            "UT-06: Single-Kitchen Warning Modal Clarity (Pass)"
        ]),
        ("3. DATABASE INTEGRITY TESTING (DT-01 to DT-06)", GREEN_SUCCESS, [
            "DT-01: 22 Normalized InnoDB Schema Verification (Pass)",
            "DT-02: Foreign Key Cascade & Set Null Integrity (Pass)",
            "DT-03: Address Soft-Delete Safety (is_deleted = 1) (Pass)",
            "DT-04: Spatial Coordinates Precision DECIMAL(10,8) (Pass)",
            "DT-05: Multi-Tiffin Milk-Run Corridor Sequence (Pass)",
            "DT-06: Atomic Transaction Rollback on Failure (Pass)"
        ]),
        ("4. CONTROL FLOW & WHITE-BOX (DF-01 to DF-06)", DARK_TEXT, [
            "DF-01: 4-Stage Cascade Nearest Idle Courier Priority (Pass)",
            "DF-02: Order State Machine Strict Progression (Pass)",
            "DF-03: Haversine 15 km Radial Geofence Logic (Pass)",
            "DF-04: Zero-Waste Dynamic Cutoff Expiry Sweep (Pass)",
            "DF-05: Multi-Tenant Cookie Namespace Isolation (Pass)",
            "DF-06: 10% Platform Fee & GST Penney Arithmetic (Pass)"
        ])
    ]
    
    for idx, (t_name, t_col, cases) in enumerate(test_dims):
        c_x = idx // 2
        r_y = idx % 2
        x = Inches(0.8) if c_x == 0 else Inches(6.86)
        y = Inches(1.35) if r_y == 0 else Inches(4.3)
        
        box = s13.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, x, y, Inches(5.66), Inches(2.75))
        box.fill.solid()
        box.fill.fore_color.rgb = BG_CARD
        box.line.color.rgb = BORDER_LIGHT
        tf = box.text_frame
        tf.word_wrap = True
        tf.margin_left = Inches(0.2)
        tf.margin_top = Inches(0.15)
        
        p = tf.paragraphs[0]
        r = p.add_run()
        r.text = t_name
        r.font.bold = True
        r.font.size = Pt(11)
        r.font.color.rgb = t_col
        
        for c_text in cases:
            p = tf.add_paragraph()
            p.space_before = Pt(2)
            r = p.add_run()
            r.text = f"• {c_text}"
            r.font.size = Pt(9)
            r.font.color.rgb = DARK_TEXT

    # =========================================================================
    # SLIDE 14: LIMITATIONS & FUTURE ENHANCEMENTS
    # =========================================================================
    s14 = prs.slides.add_slide(blank_layout)
    add_academic_header(s14, "Project Limitations & Future Scope", "Project Reflection")
    
    # Left: Limitations
    c_lim = s14.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(1.35), Inches(5.66), Inches(5.4))
    c_lim.fill.solid()
    c_lim.fill.fore_color.rgb = BG_CARD
    c_lim.line.color.rgb = BORDER_LIGHT
    tf = c_lim.text_frame
    tf.word_wrap = True
    tf.margin_left = Inches(0.25)
    tf.margin_top = Inches(0.25)
    p = tf.paragraphs[0]
    r = p.add_run()
    r.text = "CURRENT SYSTEM LIMITATIONS"
    r.font.bold = True
    r.font.size = Pt(12)
    r.font.color.rgb = ORANGE_ACCENT
    
    lims = [
        ("Regional Pilot Scope: ", "Platform currently configured with active geocoded clusters in Surat and Bardoli; requires localized expansion for national scalability."),
        ("Web-First Client Architecture: ", "Mobile users access Kravyo via responsive web browser rather than standalone native Android/iOS APKs."),
        ("Third-Party API Free Tier Limits: ", "Google Maps API routes and Pusher WebSockets require commercial paid tier scaling under massive concurrent user spikes."),
        ("Courier Cold-Start Scenarios: ", "In sparsely populated sub-districts, cascade dispatch relies on fallback city broadcast if zero idle couriers are within 8 km.")
    ]
    for k, v in lims:
        p = tf.add_paragraph()
        p.space_before = Pt(10)
        r1 = p.add_run()
        r1.text = k + "\n"
        r1.font.bold = True
        r1.font.size = Pt(10)
        r1.font.color.rgb = DARK_TEXT
        r2 = p.add_run()
        r2.text = v
        r2.font.size = Pt(9.5)
        r2.font.color.rgb = SLATE_GRAY

    # Right: Future Scope
    c_fut = s14.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(6.86), Inches(1.35), Inches(5.66), Inches(5.4))
    c_fut.fill.solid()
    c_fut.fill.fore_color.rgb = BG_CARD
    c_fut.line.color.rgb = BORDER_LIGHT
    tf = c_fut.text_frame
    tf.word_wrap = True
    tf.margin_left = Inches(0.25)
    tf.margin_top = Inches(0.25)
    p = tf.paragraphs[0]
    r = p.add_run()
    r.text = "FUTURE ENHANCEMENTS"
    r.font.bold = True
    r.font.size = Pt(12)
    r.font.color.rgb = GREEN_SUCCESS
    
    futs = [
        ("AI Dynamic Pricing Engine: ", "Deploying Scikit-learn predictive models to calibrate zero-waste discounts dynamically based on real-time historical consumer demand curves."),
        ("EV Fleet Route Optimization: ", "Clustering milk-run deliveries for electric two-wheelers with charging depot stopovers for zero-emission logistics."),
        ("Native Flutter Mobile Applications: ", "Developing cross-platform iOS and Android apps with background geolocation push notifications."),
        ("IoT Kitchen Safety Integration: ", "Connecting smart Bluetooth food thermometers and hygiene sensors for automated live FSSAI hygiene audit scores.")
    ]
    for k, v in futs:
        p = tf.add_paragraph()
        p.space_before = Pt(10)
        r1 = p.add_run()
        r1.text = k + "\n"
        r1.font.bold = True
        r1.font.size = Pt(10)
        r1.font.color.rgb = DARK_TEXT
        r2 = p.add_run()
        r2.text = v
        r2.font.size = Pt(9.5)
        r2.font.color.rgb = SLATE_GRAY

    # =========================================================================
    # SLIDE 15: CONCLUSION & REFERENCES
    # =========================================================================
    s15 = prs.slides.add_slide(blank_layout)
    add_academic_header(s15, "Conclusion & Academic References", "Summary & Bibliography")
    
    # Left: Conclusion
    c_c = s15.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(1.35), Inches(5.66), Inches(5.4))
    c_c.fill.solid()
    c_c.fill.fore_color.rgb = BG_CARD
    c_c.line.color.rgb = BORDER_LIGHT
    tf = c_c.text_frame
    tf.word_wrap = True
    tf.margin_left = Inches(0.25)
    tf.margin_top = Inches(0.25)
    p = tf.paragraphs[0]
    r = p.add_run()
    r.text = "PROJECT CONCLUSION"
    r.font.bold = True
    r.font.size = Pt(12)
    r.font.color.rgb = NAVY_PRIMARY
    
    concls = [
        ("Fulfillment of Degree Requirements:", "Successfully designed, developed, and verified Kravyo in partial fulfillment of the BCA curriculum at Uka Tarsadia University."),
        ("Empowering Domestic Homemakers:", "Established a fair, equitable cloud kitchen model with 10% commission, legal FSSAI onboarding, and daily batch preparation rosters."),
        ("Technical Robustness:", "Engineered 22 normalized InnoDB tables, isolated 4-portal session cookies, Haversine 15km geofencing, and automated Razorpay refund triggers."),
        ("100% Quality Assurance Benchmark:", "Verified across 38 automated test assertions in 0.370s with zero regressions using Selenium WebDriver and PHPUnit.")
    ]
    for k, v in concls:
        p = tf.add_paragraph()
        p.space_before = Pt(8)
        r1 = p.add_run()
        r1.text = k + "\n"
        r1.font.bold = True
        r1.font.size = Pt(10)
        r1.font.color.rgb = DARK_TEXT
        r2 = p.add_run()
        r2.text = v
        r2.font.size = Pt(9.5)
        r2.font.color.rgb = SLATE_GRAY

    # Right: Academic References
    c_r = s15.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(6.86), Inches(1.35), Inches(5.66), Inches(5.4))
    c_r.fill.solid()
    c_r.fill.fore_color.rgb = BG_CARD
    c_r.line.color.rgb = BORDER_LIGHT
    tf = c_r.text_frame
    tf.word_wrap = True
    tf.margin_left = Inches(0.25)
    tf.margin_top = Inches(0.25)
    p = tf.paragraphs[0]
    r = p.add_run()
    r.text = "BIBLIOGRAPHY & REFERENCES"
    r.font.bold = True
    r.font.size = Pt(12)
    r.font.color.rgb = NAVY_PRIMARY
    
    refs = [
        "1. Pressman, R. S., & Maxim, B. R. (2020). Software Engineering: A Practitioner's Approach (9th ed.). McGraw-Hill Education.",
        "2. Elmasri, R., & Navathe, S. B. (2016). Fundamentals of Database Systems (7th ed.). Pearson.",
        "3. The PHP Documentation Group. (2026). PHP: Hypertext Preprocessor Official Manual. https://www.php.net/docs.php",
        "4. Oracle Corporation. (2026). MySQL 8.0 Reference Manual: InnoDB Storage Engine & Spatial Extensions. https://dev.mysql.com/doc/",
        "5. SeleniumHQ. (2026). Selenium WebDriver 4.18 Documentation & Python Bindings. https://www.selenium.dev/documentation/",
        "6. Food Safety and Standards Authority of India (FSSAI). (2026). Food Safety Compliance System (FoSCoS). Government of India."
    ]
    for ref in refs:
        p = tf.add_paragraph()
        p.space_before = Pt(8)
        r = p.add_run()
        r.text = ref
        r.font.size = Pt(9)
        r.font.color.rgb = SLATE_GRAY

    # =========================================================================
    # SLIDE 16: VIVA VOCE & Q&A
    # =========================================================================
    s16 = prs.slides.add_slide(blank_layout)
    bg16 = s16.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0), Inches(0), Inches(13.333), Inches(7.5))
    bg16.fill.solid()
    bg16.fill.fore_color.rgb = NAVY_PRIMARY
    bg16.line.fill.background()
    
    top_bar = s16.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0), Inches(0), Inches(13.333), Inches(0.15))
    top_bar.fill.solid()
    top_bar.fill.fore_color.rgb = ORANGE_ACCENT
    top_bar.line.fill.background()
    
    tx = s16.shapes.add_textbox(Inches(1.5), Inches(1.8), Inches(10.333), Inches(4.0))
    tf = tx.text_frame
    tf.word_wrap = True
    
    p = tf.paragraphs[0]
    p.alignment = PP_ALIGN.CENTER
    r = p.add_run()
    r.text = "THANK YOU!"
    r.font.name = "Arial"
    r.font.size = Pt(44)
    r.font.bold = True
    r.font.color.rgb = WHITE
    
    p = tf.add_paragraph()
    p.alignment = PP_ALIGN.CENTER
    p.space_before = Pt(12)
    r = p.add_run()
    r.text = "Open for Questions & Live System Demonstration"
    r.font.name = "Arial"
    r.font.size = Pt(22)
    r.font.color.rgb = ORANGE_ACCENT
    
    p = tf.add_paragraph()
    p.alignment = PP_ALIGN.CENTER
    p.space_before = Pt(28)
    r = p.add_run()
    r.text = "KRAVYO: A Multi-Tenant Cloud Kitchen & Meal Subscription Platform\nBachelor of Computer Applications (BCA)  •  Academic Year 2026 - 2027\nDepartment of Computer Science  •  Shrimad Rajchandra Vidyapeeth, Uka Tarsadia University"
    r.font.name = "Calibri"
    r.font.size = Pt(13)
    r.font.color.rgb = RGBColor(203, 213, 225)

    out_file = r"c:\xampp\htdocs\Kravyo\Kravyo_Project_Presentation_Final.pptx"
    prs.save(out_file)
    print(f"[SUCCESS] Academic Human Presentation created: {out_file} ({os.path.getsize(out_file)} bytes)")
    try:
        main_file = r"c:\xampp\htdocs\Kravyo\Kravyo_Project_Presentation.pptx"
        prs.save(main_file)
        print(f"[SUCCESS] Also overwritten main presentation: {main_file}")
    except Exception as e:
        print("[NOTE] Main file was locked by PowerPoint. Saved to Kravyo_Project_Presentation_Final.pptx")

if __name__ == '__main__':
    build_human_academic_presentation()
