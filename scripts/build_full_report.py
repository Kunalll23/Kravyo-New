import os
import sys
import docx
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx_helpers import (
    apply_table_styles, add_image_placeholder, add_table_caption, add_chapter_title,
    add_main_heading, add_sub_heading, add_sub_sub_heading,
    add_para, add_bullet, setup_header_footer
)

def build_report():
    doc = Document()
    
    # Configure Normal Style
    style = doc.styles['Normal']
    style.font.name = 'Cambria'
    style.font.size = Pt(12)
    style.font.color.rgb = RGBColor(0, 0, 0)
    style.paragraph_format.line_spacing = 1.5
    style.paragraph_format.space_before = Pt(0)
    style.paragraph_format.space_after = Pt(0)
    
    # Configure Header Style in solid black
    if 'Header' in doc.styles:
        h_style = doc.styles['Header']
        h_style.font.name = 'Cambria'
        h_style.font.size = Pt(9.5)
        h_style.font.italic = True
        h_style.font.bold = True
        h_style.font.color.rgb = RGBColor(0, 0, 0)
        
    # Configure Footer Style in solid black
    if 'Footer' in doc.styles:
        f_style = doc.styles['Footer']
        f_style.font.name = 'Cambria'
        f_style.font.size = Pt(9)
        f_style.font.bold = True
        f_style.font.color.rgb = RGBColor(0, 0, 0)
    
    # ---------------------------------------------------------
    # SECTION 1: PRELIMINARY PAGES (Roman Numerals: i, ii, iii...)
    # ---------------------------------------------------------
    sec_prelim = doc.sections[0]
    setup_header_footer(sec_prelim, is_roman=True)
    
    # TITLE PAGE
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_before = Pt(36)
    p.paragraph_format.space_after = Pt(12)
    r = p.add_run("UKA TARSADIA UNIVERSITY\nSHRIMAD RAJCHANDRA VIDYAPEETH\n")
    r.font.name = 'Cambria'
    r.font.size = Pt(16)
    r.font.bold = True
    
    r_sub = p.add_run("DEPARTMENT OF COMPUTER SCIENCE\n\n\n")
    r_sub.font.name = 'Cambria'
    r_sub.font.size = Pt(13)
    r_sub.font.bold = True
    
    r_proj = p.add_run("A PROJECT REPORT ON\n")
    r_proj.font.name = 'Cambria'
    r_proj.font.size = Pt(14)
    r_proj.font.italic = True
    
    r_title = p.add_run("KRAVYO: A MULTI-TENANT CLOUD KITCHEN & MEAL SUBSCRIPTION PLATFORM\n\n\n")
    r_title.font.name = 'Cambria'
    r_title.font.size = Pt(20)
    r_title.font.bold = True
    r_title.font.color.rgb = RGBColor(0, 0, 0)
    
    r_deg = p.add_run("Submitted in partial fulfillment of the requirements for the degree of\nBachelor of Computer Applications (BCA)\nSemester - V & VI\n\n\n")
    r_deg.font.name = 'Cambria'
    r_deg.font.size = Pt(12)
    
    r_by = p.add_run("SUBMITTED BY (Team: BCA5_18):\n")
    r_by.font.name = 'Cambria'
    r_by.font.size = Pt(12)
    r_by.font.bold = True
    
    r_team = p.add_run(
        "202407100510063\n"
        "202407100510077\n"
        "202407100510079\n"
        "202407100510121\n\n\n"
    )
    r_team.font.name = 'Cambria'
    r_team.font.size = Pt(12)
    
    r_year = p.add_run("ACADEMIC YEAR: 2026 - 2027\n")
    r_year.font.name = 'Cambria'
    r_year.font.size = Pt(13)
    r_year.font.bold = True
    
    doc.add_page_break()
    
    # PROJECT CERTIFICATION FORM
    add_chapter_title(doc, "Project Certification Form")
    add_para(doc, "This is to certify that the project entitled \"Kravyo: A Multi-Tenant Cloud Kitchen & Meal Subscription Platform\" has been successfully carried out and submitted by the following students of Bachelor of Computer Applications (BCA) at Shrimad Rajchandra Vidyapeeth, Uka Tarsadia University, in partial fulfillment of the curriculum requirements:")
    
    tbl_cert = doc.add_table(rows=5, cols=3)
    tbl_cert.rows[0].cells[0].paragraphs[0].text = "Sr. No."
    tbl_cert.rows[0].cells[1].paragraphs[0].text = "Enrollment Number"
    tbl_cert.rows[0].cells[2].paragraphs[0].text = "Student Signature"
    
    students = [
        ("1", "202407100510063", ""),
        ("2", "202407100510077", ""),
        ("3", "202407100510079", ""),
        ("4", "202407100510121", "")
    ]
    for i, (sr, enr, sig) in enumerate(students, 1):
        tbl_cert.rows[i].cells[0].paragraphs[0].text = sr
        tbl_cert.rows[i].cells[1].paragraphs[0].text = enr
        tbl_cert.rows[i].cells[2].paragraphs[0].text = sig
    apply_table_styles(tbl_cert, [Inches(1.0), Inches(3.0), Inches(2.27)])
    
    add_para(doc, "\nThe work embodied in this project report is original and has been completed under the guidance of the faculty project guide.")
    
    p_sig = doc.add_paragraph()
    p_sig.paragraph_format.space_before = Pt(40)
    p_sig.paragraph_format.line_spacing = 1.5
    r_sig = p_sig.add_run("Internal Guide\t\t\t\t\t\tHead of Department\nDepartment of Computer Science\t\t\tShrimad Rajchandra Vidyapeeth")
    r_sig.font.name = 'Cambria'
    r_sig.font.size = Pt(11)
    r_sig.font.bold = True
    
    doc.add_page_break()
    
    # ACKNOWLEDGEMENTS
    add_chapter_title(doc, "Acknowledgements")
    add_para(doc, "We would like to express our profound gratitude to our project guide and the faculty members of the Department of Computer Science, Shrimad Rajchandra Vidyapeeth, Uka Tarsadia University, for their continuous encouragement, valuable insights, and constructive feedback throughout the design and implementation of the Kravyo platform.")
    add_para(doc, "We are immensely grateful to our Head of Department and the university leadership for providing access to robust computational infrastructure, software tools, and academic resources essential for the execution of this project.")
    add_para(doc, "Finally, we extend our heartfelt appreciation to our parents, peers, and fellow classmates whose unwavering moral support and collaborative spirit motivated us to overcome complex engineering challenges during every phase of this software development journey.")
    
    doc.add_page_break()
    
    # TABLE OF CONTENTS
    add_chapter_title(doc, "Table of Contents")
    toc_data = [
        ("Project Certification Form", "ii"),
        ("Acknowledgements", "iii"),
        ("Table of Contents", "iv"),
        ("List of Tables", "v"),
        ("List of Figures", "vi"),
        ("Abstract / Executive Summary", "vii"),
        ("Chapter 1: Sequence no 3 - Project Requirement Document Submission", "1"),
        ("    1.1 Purpose, System Definition and Objectives", "1"),
        ("    1.2 System Process Flow (End-to-End Operational Lifecycle)", "3"),
        ("    1.3 Points with Description for Current Semester Project (Listing of Features)", "6"),
        ("    1.4 Comparative Study of Current Semester System Features", "10"),
        ("    1.5 Tools and Technology Selected for Current Semester", "13"),
        ("    1.6 Summary of Sequence", "15"),
        ("Chapter 2: Sequence no 4 - Project Requirement Details", "16"),
        ("    2.1 Scope", "16"),
        ("    2.2 Functional Requirements", "17"),
        ("    2.3 Non-Functional Requirements", "20"),
        ("    2.4 Exciting Functionalities", "21"),
        ("    2.5 Number of Users", "22"),
        ("    2.6 User-Wise Characteristics", "23"),
        ("    2.7 Data Dictionary and Integrity Constraints", "25"),
        ("    2.8 Summary About Expected and Exciting Functionality", "27"),
        ("    2.9 Users of the System with Their Characteristics", "28"),
        ("    2.10 Summary of Sequence", "29"),
        ("Chapter 3: Sequence no 5 - Implementation of Functionalities & Design Preparation", "30"),
        ("    3.1 & 3.2 Use Case Diagrams (All 4 User Roles)", "30"),
        ("    3.3 Activity Diagrams (9 Core Workflow Executions)", "33"),
        ("    3.4 GUI Design (Functionality-Wise across 4 Portals)", "39"),
        ("    3.5 Traversals [Hierarchical Format]", "43"),
        ("    3.6 System Development Model (Agile Scrum 6 Sprints)", "45"),
        ("    3.7 Database Tables (22 Tables Data Dictionary & Normalization)", "47"),
        ("    3.8 Summary of Sequence", "64"),
        ("Chapter 4: Sequence no 6 - Refinement in User Interface Design", "65"),
        ("    4.1 Screenshot of Homepage & Navigation Controls", "65"),
        ("    4.2 Detailed Screen Specifications (All 4 User Roles)", "67"),
        ("Chapter 5: Implementation of User-Wise Reports and Dashboards (Item 8)", "86"),
        ("    5.1 User-Wise Dashboard Views and Architectural Specifications", "86"),
        ("    5.2 User-Wise TPS / MIS / DSS Reports", "91"),
        ("    5.3 Summary [No. of Reports User-Wise]", "96"),
        ("Chapter 6: System Testing and Quality Assurance (Item 9)", "97"),
        ("    6.1 Project Testing: Evaluation Methods", "97"),
        ("    6.2 Used Testing Tool Specifications", "98"),
        ("    6.3 Automated Testing", "99"),
        ("    6.4 Detailed Test Case Reports for Mandatory Testing Types", "101"),
        ("    6.5 Summary [No. of Test Cases for Each Type]", "107"),
        ("References", "108")
    ]
    
    tbl_toc = doc.add_table(rows=len(toc_data)+1, cols=2)
    tbl_toc.rows[0].cells[0].paragraphs[0].text = "Topic / Section Description"
    tbl_toc.rows[0].cells[1].paragraphs[0].text = "Page No."
    for i, (title, page) in enumerate(toc_data, 1):
        tbl_toc.rows[i].cells[0].paragraphs[0].text = title
        tbl_toc.rows[i].cells[1].paragraphs[0].text = page
        tbl_toc.rows[i].cells[1].paragraphs[0].alignment = WD_ALIGN_PARAGRAPH.RIGHT
    apply_table_styles(tbl_toc, [Inches(5.27), Inches(1.0)])
    
    doc.add_page_break()
    
    # LIST OF TABLES
    add_chapter_title(doc, "List of Tables")
    lot_data = [
        ("Table 1.1", "Cross-Actor End-to-End System Process Flow Lifecycle Matrix"),
        ("Table 1.2", "Comprehensive Feature Matrix: Kravyo vs. Existing Food Delivery Systems"),
        ("Table 1.3", "Tools and Technologies Selected for Kravyo Project"),
        ("Table 2.1", "Non-Functional Quality Attribute Requirements"),
        ("Table 2.2", "Four User Portals, Access Routes, and Session Contexts"),
        ("Table 2.3", "Data Dictionary Summary of 22 Normalized Relational Tables"),
        ("Table 3.1", "Customer Interface Functionality-Wise Specifications"),
        ("Table 3.2", "Home Chef Interface Functionality-Wise Specifications"),
        ("Table 3.3", "Delivery Partner Interface Functionality-Wise Specifications"),
        ("Table 3.4", "Administrator Interface Functionality-Wise Specifications"),
        ("Table 3.5 to 3.26", "Database Data Dictionary (Tables 1 through 22 Schema Definitions)"),
        ("Table 4.1", "Summary of Screen Specifications Across All 4 User Roles"),
        ("Table 5.1", "View-1 Specification: Customer Activity & Kitchen Discovery Feed Hub"),
        ("Table 5.2", "View-2 Specification: Home Chef Kitchen Operations Command Desk"),
        ("Table 5.3", "View-3 Specification: Delivery Partner Logistics Terminal & Shift Dashboard"),
        ("Table 5.4", "View-4 Specification: Master Administrator Platform Command Console"),
        ("Table 5.5", "Report-1 Specification (TPS): Customer Printable Tax Invoice & Delivery Bill"),
        ("Table 5.6", "Report-2 Specification (MIS): Daily Kitchen Batch Preparation & Tiffin Dispatch Roster"),
        ("Table 5.7", "Report-3 Specification (DSS): Platform Monthly GMV, Commission & Financial Settlement Audit"),
        ("Table 5.8", "Summary Metrics of User-Wise TPS / MIS / DSS Reports"),
        ("Table 6.1", "Used Testing Tool Specifications and Operational Scope"),
        ("Table 6.2", "Automated Unit and Integration Test Case Report"),
        ("Table 6.3", "Functional Test Case Report"),
        ("Table 6.4", "Usability Test Case Report"),
        ("Table 6.5", "Interface Test Case Report"),
        ("Table 6.6", "Database Integrity Test Case Report"),
        ("Table 6.7", "Dataflow and Control Flow Test Case Report"),
        ("Table 6.8", "Summary Metrics of System Testing Execution Across All Categories")
    ]
    tbl_lot = doc.add_table(rows=len(lot_data)+1, cols=2)
    tbl_lot.rows[0].cells[0].paragraphs[0].text = "Table No."
    tbl_lot.rows[0].cells[1].paragraphs[0].text = "Table Title & Description"
    for i, (tnum, ttitle) in enumerate(lot_data, 1):
        tbl_lot.rows[i].cells[0].paragraphs[0].text = tnum
        tbl_lot.rows[i].cells[1].paragraphs[0].text = ttitle
    apply_table_styles(tbl_lot, [Inches(1.5), Inches(4.77)])
    
    doc.add_page_break()
    
    # LIST OF FIGURES
    add_chapter_title(doc, "List of Figures")
    lof_data = [
        ("Figure 1.1", "Kravyo End-to-End System Process Flow Diagram across All 4 Actors"),
        ("Figure 2.1", "Kravyo Platform Architecture & User Role Hierarchy"),
        ("Figure 3.1", "Use Case Diagram for Customer Actor"),
        ("Figure 3.2", "Use Case Diagram for Home Chef Actor"),
        ("Figure 3.3", "Use Case Diagram for Delivery Partner Actor"),
        ("Figure 3.4", "Use Case Diagram for Administrator Actor"),
        ("Figure 3.5", "Activity Diagram: Customer Authentication & Registration Flow"),
        ("Figure 3.6", "Activity Diagram: Tiffin Meal Subscription Life Cycle"),
        ("Figure 3.7", "Activity Diagram: Cart Checkout & Multi-Channel Payment Verification"),
        ("Figure 3.8", "Activity Diagram: Kitchen Order Processing & Preparation Flow"),
        ("Figure 3.9", "Activity Diagram: Chef Menu Catalog & Portion Inventory Management"),
        ("Figure 3.10", "Activity Diagram: Chef Kitchen Onboarding & FSSAI Verification"),
        ("Figure 3.11", "Activity Diagram: 4-Stage Cascade Delivery Dispatch & OTP Handover"),
        ("Figure 3.12", "Activity Diagram: Customer Grievance, Dispute & Razorpay Refund"),
        ("Figure 3.13", "Activity Diagram: Platform Commission Settlement & Payouts"),
        ("Figure 3.14", "Figure 13: Traversal Hierarchical Navigation Diagram across 4 Portals"),
        ("Figure 4.1", "Customer Discovery Homepage & Navigation System"),
        ("Figure 4.2 to 4.20", "Graphical User Interface Screen Specifications Across 4 Roles"),
        ("Figure 5.1", "Customer Activity & Kitchen Discovery Feed Hub Dashboard"),
        ("Figure 5.2", "Home Chef Kitchen Operations Command Desk"),
        ("Figure 5.3", "Delivery Partner Logistics Terminal & Shift Dashboard"),
        ("Figure 5.4", "Master Administrator Platform Command Console"),
        ("Figure 5.5", "Customer Printable Tax Invoice & Delivery Bill Receipt"),
        ("Figure 5.6", "Daily Kitchen Batch Preparation & Tiffin Dispatch Roster Report"),
        ("Figure 5.7", "Platform Monthly GMV, Commission Revenue & Financial Settlement Audit"),
        ("Figure 6.1", "Automated Test Suite Execution & Browser Verification on Kravyo Platform")
    ]
    tbl_lof = doc.add_table(rows=len(lof_data)+1, cols=2)
    tbl_lof.rows[0].cells[0].paragraphs[0].text = "Figure No."
    tbl_lof.rows[0].cells[1].paragraphs[0].text = "Figure Title & Description"
    for i, (fnum, ftitle) in enumerate(lof_data, 1):
        tbl_lof.rows[i].cells[0].paragraphs[0].text = fnum
        tbl_lof.rows[i].cells[1].paragraphs[0].text = ftitle
    apply_table_styles(tbl_lof, [Inches(1.5), Inches(4.77)])
    
    doc.add_page_break()
    
    # ABSTRACT / EXECUTIVE SUMMARY
    add_chapter_title(doc, "Abstract / Executive Summary")
    add_para(doc, "The rapid expansion of commercial cloud kitchen aggregators has transformed urban food delivery. However, existing commercial platforms impose steep commission fees (often exceeding 30%), neglect personalized dietary needs (such as diabetic-friendly, Jain, and low-oil cooking), fail to provide dedicated recurring meal subscription models, and exacerbate logistics bottlenecks through uncontrolled delivery broadcast spam.")
    add_para(doc, "\"Kravyo: A Multi-Tenant Cloud Kitchen & Meal Subscription Platform\" resolves these systemic shortcomings by creating a decentralized, community-driven digital food ecosystem that connects home chefs, culinary entrepreneurs, and homemakers directly with local consumers, supported by an integrated, automated delivery fleet.")
    add_para(doc, "The platform is engineered using a clean PHP 8.2 Model-View-Controller (MVC) architecture, a 22-table MariaDB/MySQL relational database, and four strictly isolated cookie-based user portals: Customer (/), Home Chef (/chef), Delivery Partner (/delivery), and Administrator (/admin). Key technological innovations include:")
    add_bullet(doc, "A 4-stage smart cascade hybrid dispatch engine for on-demand meals paired with a scheduled Multi-Tiffin Batch Logistics Engine that clusters recurring subscriptions into optimized milk runs.", "1. Smart Logistics: ")
    add_bullet(doc, "Live real-time courier tracking powered by Pusher WebSockets and Google Maps with queue-aware waypoint indicators and animated vehicle markers.", "2. Live Geolocation: ")
    add_bullet(doc, "Secure online payments via Razorpay featuring instant, automated REST API refunds upon order rejection or cancellation.", "3. Financial Automation: ")
    add_bullet(doc, "A Python Scikit-learn machine learning microservice utilizing TF-IDF vectorization and cosine similarity, backed by an automatic PHP heuristic failover engine.", "4. AI Recommendations: ")
    add_bullet(doc, "Recurring weekly and monthly meal plan scheduling with circular zero-waste dabba returns and a surplus food rescue marketplace with countdown timers.", "5. Sustainability & Subscriptions: ")
    add_para(doc, "Developed under the Agile Scrum methodology across six dedicated sprints, Kravyo provides a production-grade, highly reliable, and socially impactful software solution for the modern food-tech landscape.")
    
    doc.add_page_break()
    
    # ---------------------------------------------------------
    # SECTION 2: MAIN BODY (Arabic Numerals: 1, 2, 3...)
    # ---------------------------------------------------------
    sec_body = doc.add_section()
    setup_header_footer(sec_body, is_roman=False)
    
    print("Preliminary section completed. Building Chapter 1...")
    
    # CHAPTER 1: SEQUENCE 3
    add_chapter_title(doc, "Chapter 1: Project Requirement Document Submission (Sequence No. 3)")
    add_main_heading(doc, "1.1 Purpose, System Definition and Objectives")
    add_para(doc, "The purpose of \"Kravyo: A Multi-Tenant Cloud Kitchen & Meal Subscription Platform\" is to develop a comprehensive, web-based software ecosystem that connects home chefs, homemakers, and small-scale culinary businesses directly with customers, supported by an integrated delivery fleet.")
    add_para(doc, "The system empowers home chefs to establish virtual kitchens with official FSSAI certification, configure custom menus, receive real-time order notifications with audio chimes, forecast recurring daily tiffin batches, and monetize surplus food to prevent wastage. Customers can discover nearby verified home kitchens, customize meals to their personal health and dietary needs (oil level, spice intensity, Jain preparation, diabetic-friendly options), pay securely via Razorpay with automated refund protection, track deliveries live on Google Maps via WebSockets, and subscribe to flexible recurring tiffin plans.")
    add_para(doc, "To eliminate expensive aggregator commissions (often exceeding 30%) and logistics chaos, Kravyo incorporates an automated 4-Stage Smart Cascade Hybrid Dispatch Engine that sequentially dispatches orders to nearby couriers. Built on a custom PHP MVC framework with 4 strictly isolated session portals, a 22-table MySQL database, and a Scikit-learn AI recommendation microservice with automatic PHP fallback, the platform provides an enterprise-grade food-tech solution.")
    
    add_sub_heading(doc, "1.1.1 Formal System Definition")
    add_para(doc, "Kravyo is formally defined as an enterprise multi-tenant, cloud-based food-tech software application that facilitates distributed kitchen commercialization, algorithmic logistics mediation, automated financial settlements, and personalized culinary health customization. The system functions as an integrated distributed transaction processing and decision-support ecosystem providing distinct, secure, and role-governed web applications across four operational stakeholders: Consumers, Home Cooks, Delivery Couriers, and Platform Administrators.")
    
    add_sub_heading(doc, "1.1.2 Primary and Specific Project Objectives")
    add_para(doc, "The Kravyo project was conceptualized, architected, and engineered to accomplish the following primary and specific academic and operational objectives:")
    obj_points = [
        ("1. Disruption of Monopolistic Commission Structures: ", "To engineer an ethical, transparent food-tech marketplace that limits platform commission to a flat 10%, empowering home chefs and homemakers to earn sustainable livelihoods while delivering affordable meals to consumers."),
        ("2. Granular Dietary & Health Customization: ", "To implement first-class database and interface attributes for dish-level health specifications (Low/Medium/High Spice, Normal/Less Oil, Jain preparation, and Diabetic-friendly options) that render directly on kitchen preparation tickets."),
        ("3. Automated Recurring Tiffin Lifecycle Management: ", "To automate weekly and monthly meal plan scheduling with automated daily task generation (subscription_deliveries) and forward-looking batch cooking rosters for cloud kitchens."),
        ("4. Smart Algorithmic Logistics Without Broadcast Chaos: ", "To develop a 4-Stage Smart Cascade Hybrid Dispatch Engine that prioritizes corridor batching, pings the single nearest idle driver for 25 seconds, cascades sequentially, and minimizes fleet notification fatigue."),
        ("5. Live Real-Time Geolocation & Handover Integrity: ", "To stream real-time driver GPS coordinates over Pusher WebSockets to animated customer maps without polling overhead, secured by a cryptographic 4-digit customer OTP handshake."),
        ("6. Environmental Food Waste Mitigation: ", "To develop a dynamic Zero Food Waste clearance portal where home kitchens can list freshly prepared surplus portions at decaying discounts with automated countdown timers."),
        ("7. Fault-Tolerant AI Catalog Recommendations: ", "To construct a resilient Scikit-learn TF-IDF microservice coupled with an internal PHP heuristic scoring engine that guarantees 100% recommendation catalog uptime even during external API downtime."),
        ("8. Enterprise Multi-Tenant Session Security: ", "To enforce absolute role separation across four concurrent portals using independent named session cookies (KRAVYO_CUSTOMER, KRAVYO_CHEF, KRAVYO_DELIVERY, KRAVYO_ADMIN) and Bcrypt cryptographic hashing.")
    ]
    for op_pre, op_text in obj_points:
        add_bullet(doc, op_text, op_pre)
    
    add_main_heading(doc, "1.2 System Process Flow (End-to-End Operational Lifecycle)")
    add_para(doc, "The Kravyo platform coordinates real-time interactions across four distinct actor domains: Customer, Home Chef, Delivery Partner, and Platform Administrator. To guarantee high availability, strict session isolation, and eliminate delivery broadcast chaos, the entire order lifecycle is orchestrated through an automated state machine and real-time event pipeline.")
    
    add_image_placeholder(doc, "Kravyo End-to-End System Process Flow Diagram across All 4 Actors", figure_id="1.1", hint="File Reference: diagrams/system_process_flow.png")
    
    add_sub_heading(doc, "1.2.1 Stage 1: Customer Discovery, Customization & Multi-Channel Ordering Flow")
    add_para(doc, "The operational workflow initiates when a customer accesses the web portal (/). The customer discovers nearby verified home kitchens within a 15 km delivery radius using Google Maps and spatial Haversine distance queries. The customer filters menus by dietary tags (Vegetarian, Jain-friendly, and Diabetic-friendly) and customizes individual dishes with health preferences (Low/Medium/High spice, Normal/Less oil, Jain preparation, and special cooking instructions).")
    add_para(doc, "Upon proceeding to checkout, the system validates that the selected delivery address is within the 15 km delivery zone. The customer selects from three payment methods: Cash on Delivery (COD), Dynamic NPCI UPI QR code (with a 5-minute client-side expiration timer), or Razorpay Online Gateway (UPI, Cards, NetBanking). Once payment is authorized, an order is inserted into the orders ledger in pending state, and a confidential 4-digit handover OTP is generated.")
    
    add_sub_heading(doc, "1.2.2 Stage 2: Kitchen Order Processing, Audio Alerts & Preparation Flow")
    add_para(doc, "The order placement triggers an immediate real-time WebSocket broadcast to the home chef's dashboard (/chef). The chef's browser sounds a continuous audio chime (beep.wav) via the HTML5 Web Audio API to alert the cook. The chef reviews the item details, customer dietary notes, and accepts or rejects the order.")
    add_bullet(doc, "If the chef rejects the order (e.g. stock exhausted), the backend immediately calls Razorpay's REST API to execute an automated instant refund to the customer's source account, transitioning the order status to cancelled and payment status to refunded.", "Order Rejection & Refund: ")
    add_bullet(doc, "If the chef accepts, the order transitions to preparing. The kitchen cooks and packages the portions according to health tags. Once packaged, the chef marks the order ready_for_pickup, which automatically invokes the backend dispatch engine.", "Order Acceptance & Cooking: ")
    
    add_sub_heading(doc, "1.2.3 Stage 3: Automated 4-Stage Smart Cascade Dispatch Engine")
    add_para(doc, "Unlike conventional food delivery platforms that broadcast orders indiscriminately to dozens of riders, Kravyo's DispatchService executes a targeted, sequential 4-stage dispatch algorithm:")
    add_bullet(doc, "Corridor Batching — The system checks whether any active, nearby courier is already en-route along the same delivery corridor with available carrying capacity.", "Stage 1 (Corridor Batching): ")
    add_bullet(doc, "Targeted 25-Second Ping — If unbatched, the engine identifies the single nearest idle, online delivery partner (is_online = 1, is_busy = 0) within 5 km and transmits an exclusive dispatch offer with a 25-second countdown timer.", "Stage 2 (Single-Driver Ping): ")
    add_bullet(doc, "Cascade to 2nd Nearest — If the driver declines or the 25-second timer expires without response, the offer cascades instantly to the 2nd nearest driver.", "Stage 3 (Instant Cascade): ")
    add_bullet(doc, "Radius Broadcast Fallback — If two consecutive cascade candidates decline, the system expands the search to a 5 km broadcast to all eligible active riders.", "Stage 4 (Broadcast Fallback): ")
    
    add_sub_heading(doc, "1.2.4 Stage 4: Courier Navigation, Live WebSockets Tracking & OTP Handover Flow")
    add_para(doc, "Upon offer acceptance by a courier, the order status updates to assigned, and the driver's duty state is locked (is_busy = 1). The courier uses turn-by-turn routing via Google Routes API v2 to reach the kitchen and clicks 'Confirm Food Pickup'. The order status transitions to out_for_delivery.")
    add_para(doc, "During transit, the courier's device streams live latitude and longitude coordinates every 4 seconds via HTML5 watchPosition over Pusher WebSockets. The customer's tracking screen displays an animated bike marker with heading bearing rotation, polyline halo route, and dynamic ETA.")
    add_para(doc, "Upon arrival at the doorstep, the courier requests the customer's secret 4-digit Delivery OTP. The courier inputs the OTP into their mobile portal; upon verification, the order status transitions to delivered, the courier is marked available (is_busy = 0), and the delivery fee is credited to their wallet.")
    
    add_sub_heading(doc, "1.2.5 Stage 5: Post-Fulfillment Settlement, Grievances & Feedback Flow")
    add_para(doc, "Following delivery completion, the customer can submit 1 to 5 star ratings and written reviews, which dynamically recalculate the kitchen's public trust score. In case of missing items or quality concerns, the customer can file an issue ticket (customer_reports), which the administrator audits from the Dispute Resolution Console to issue compensation or automated Razorpay refunds. The system deducts a 10% platform commission and credits net vendor disbursements.")
    
    add_sub_heading(doc, "1.2.6 Cross-Actor End-to-End System Process Flow Matrix")
    flow_table_data = [
        ("1", "Customer", "Checkout & Payment", "Address, Cart Dishes, Spice/Oil/Jain Notes, Payment Mode", "Cart -> Pending Order (delivery_otp generated)", "Order Receipt & Live Tracking Timeline Initialized"),
        ("2", "Home Chef", "Order Notification", "Incoming Order Push Alert via WebSockets", "Pending -> Accepted or Cancelled", "Continuous beep.wav Audio Alert; Instant Refund on Reject"),
        ("3", "Home Chef", "Kitchen Preparation", "Dish Recipe & Dietary Customization Instructions", "Accepted -> Preparing -> Ready for Pickup", "Chef marks food ready; invokes DispatchService"),
        ("4", "Dispatch Engine", "4-Stage Cascade Matching", "Kitchen GPS Coordinates, Online Rider Geocodes", "Order dispatch_status: cascading -> assigned", "Targeted 25s Dispatch Ping sent to Nearest Idle Rider"),
        ("5", "Delivery Partner", "Offer Acceptance & Pickup", "Delivery Offer Modal (Distance, Earnings)", "delivery_offers: pending -> accepted; rider: is_busy = 1", "Google Routes API v2 Navigation to Kitchen; Order Picked Up"),
        ("6", "Delivery Partner", "Transit & WebSockets", "Driver Geolocation via HTML5 watchPosition", "Order: out_for_delivery", "Real-Time Animated Courier Icon & Dynamic ETA on Customer Map"),
        ("7", "Customer & Rider", "Verified Delivery Handover", "Confidential 4-Digit Customer Delivery OTP", "Order: out_for_delivery -> delivered; rider: is_busy = 0", "Handover Verified; Courier Earnings Credited"),
        ("8", "Platform & Admin", "Settlement & Feedback", "Order Total, Commission Rate (10%), Review Rating", "Financial Settlement & Rating Update", "Chef & Courier Net Balances Credited; Kitchen Rating Updated")
    ]
    add_table_caption(doc, "Cross-Actor End-to-End System Process Flow Lifecycle Matrix", "1.1")
    tbl_flow = doc.add_table(rows=len(flow_table_data)+1, cols=6)
    tbl_flow.rows[0].cells[0].paragraphs[0].text = "Step"
    tbl_flow.rows[0].cells[1].paragraphs[0].text = "Actor / Domain"
    tbl_flow.rows[0].cells[2].paragraphs[0].text = "Action / Event"
    tbl_flow.rows[0].cells[3].paragraphs[0].text = "Input Data"
    tbl_flow.rows[0].cells[4].paragraphs[0].text = "State Transition"
    tbl_flow.rows[0].cells[5].paragraphs[0].text = "System Output"
    for i, (stp, act, evt, inp, trans, outp) in enumerate(flow_table_data, 1):
        tbl_flow.rows[i].cells[0].paragraphs[0].text = stp
        tbl_flow.rows[i].cells[1].paragraphs[0].text = act
        tbl_flow.rows[i].cells[2].paragraphs[0].text = evt
        tbl_flow.rows[i].cells[3].paragraphs[0].text = inp
        tbl_flow.rows[i].cells[4].paragraphs[0].text = trans
        tbl_flow.rows[i].cells[5].paragraphs[0].text = outp
    apply_table_styles(tbl_flow, [Inches(0.5), Inches(1.1), Inches(1.1), Inches(1.3), Inches(1.1), Inches(1.17)])
    
    add_main_heading(doc, "1.3 Points with Description for Current Semester Project")
    points = [
        ("1.3.1 Multi-Tenant Role Isolation & Authentication", "The platform provides four strictly isolated user portals: Customer (/), Home Chef (/chef), Delivery Partner (/delivery), and Administrator (/admin). Each portal uses an independent session cookie (KRAVYO_CUSTOMER, KRAVYO_CHEF, KRAVYO_DELIVERY, KRAVYO_ADMIN), preventing session collisions during concurrent logins. Passwords are encrypted with Bcrypt, and routes are protected by role-guard middleware."),
        ("1.3.2 Home Chef Registration & Onboarding", "Prospective home chefs can register their kitchen on the platform. During onboarding, chefs provide their personal details, kitchen address, culinary story, and upload official FSSAI licenses and hygiene documents for verification."),
        ("1.3.3 Kitchen Profile & Operational Status", "Home chefs can manage their cloud kitchen profiles, upload kitchen banners, and dynamically toggle their availability between Kitchen Open (Accepting Orders) and Kitchen Closed. Closed kitchens are automatically hidden from customer ordering."),
        ("1.3.4 FSSAI Verification & Hygiene Badge", "The administrator reviews uploaded food licenses and inspection documents. Approved kitchens receive a Verified Home Kitchen & Hygiene Badge, which is displayed on their public profile to establish consumer trust."),
        ("1.3.5 Food Menu & Dietary Management", "Home chefs have full CRUD control over their menus. Each food item includes dish name, description, price, food category, preparation time, and dietary tags (Vegetarian, Jain-friendly, and Diabetic-friendly)."),
        ("1.3.6 Hyperlocal Food Discovery & Filtering", "Customers can discover home kitchens within a specific radius based on their location. The system provides search and filtering options by cuisine category (Gujarati, Punjabi, South Indian), dietary preference, price, and kitchen rating."),
        ("1.3.7 Custom Meal Requests (Health & Spice Control)", "Customers can tailor food to their health and taste needs before adding items to their cart. Customization options include spice levels (Low, Medium, High), oil levels (Normal, Less Oil), Jain preparation, and chef instructions."),
        ("1.3.8 Cart & Order Placement", "Customers can add dishes from a kitchen to their cart, apply coupon codes, review subtotal and delivery charges, select a saved delivery address, and confirm their order."),
        ("1.3.9 Razorpay Payment & Automated Instant Refunds", "The platform supports secure online transactions via the Razorpay Payment Gateway (UPI, Cards, NetBanking). If a home chef declines an online-paid order or if an order is cancelled before cooking, the backend calls Razorpay’s REST API to process an instant, automated refund to the customer's source account."),
        ("1.3.10 Live Incoming Order Audio Alerts (Beep Chime)", "When a customer places an order, the chef’s dashboard updates in real time and triggers a continuous audio chime (beep.wav) using the HTML5 Web Audio API. Chefs can review special cooking instructions and accept or reject the order."),
        ("1.3.11 4-Stage Smart Cascade Hybrid Dispatch Engine", "To assign delivery partners with minimal delay and zero broadcast chaos, the system executes a 4-stage algorithm: 1) Corridor Batching, 2) Targeted 25s Single-Driver Ping, 3) Instant Cascade to 2nd nearest driver, 4) Radius Broadcast Fallback."),
        ("1.3.12 Delivery Partner Duty & Navigation Portal", "Delivery partners have a dedicated web portal (/delivery) with an Online / Offline duty switch. Riders receive dispatch offers showing pickup and drop points, distance, and payout. Once accepted, turn-by-turn routing is rendered using Google Routes API v2."),
        ("1.3.13 Live Real-Time Driver Tracking via Pusher WebSockets", "When an order is \"Out for Delivery\", the driver’s device streams live GPS coordinates using HTML5 watchPosition over Pusher WebSockets. The customer’s tracking screen renders an animated bike marker with bearing rotation, route polyline, and dynamic ETA updates."),
        ("1.3.14 Secure 4-Digit Delivery OTP Handshake", "To prevent incorrect or fraudulent deliveries, the platform generates a unique 4-digit Delivery OTP for the customer. The delivery partner must enter this OTP into their portal to confirm successful delivery handover."),
        ("1.3.15 Weekly & Monthly Tiffin Subscriptions", "Customers can subscribe to recurring breakfast, lunch, or dinner meal plans. Kitchens receive daily meal preparation forecasts for batch cooking, and the platform automatically generates daily delivery schedules."),
        ("1.3.16 Zero Food Waste Rescue Section", "Home chefs can list fresh surplus meals at discounted prices towards the end of their operating hours. Items feature a dynamic countdown timer and automatically expire when the window ends, reducing food wastage while offering affordable meals."),
        ("1.3.17 AI-Based Meal Recommendations with Fail-Safe Fallback", "A standalone Python Flask microservice (python_ai/) uses Scikit-learn TF-IDF Vectorization and Cosine Similarity to suggest personalized dishes based on customer order history and dietary flags. If the Python microservice is offline, the backend seamlessly falls back to an internal PHP scoring engine, guaranteeing 100% uptime."),
        ("1.3.18 Customer Grievance & Dispute Redressal", "Customers can submit issue tickets (customer_reports) for missing items, quality defects, or delivery delays. Administrators investigate complaints, communicate with stakeholders, and can trigger automated refunds or chef penalties."),
        ("1.3.19 Customer Reviews & Ratings", "Verified customers can rate completed orders from 1 to 5 stars and submit written reviews. These ratings dynamically update the public score and credibility of the home kitchen."),
        ("1.3.20 Seller & Administrator Dashboards", "Seller Dashboard displays daily earnings, order counts, pending approvals, tiffin forecasts, and top-selling dishes. Administrator Dashboard provides platform oversight, total GMV, commission calculations, kitchen FSSAI approvals, and dispute management."),
        ("1.3.21 Address Management with Soft-Deletion Safety", "Customers can store multiple delivery addresses with GPS coordinates. Addresses use soft-deletion (is_deleted = 1), allowing customers to remove old addresses without corrupting historical order receipts or violating database foreign keys."),
        ("1.3.22 Google Maps Server-Side Disk Caching Proxy", "All Google Maps routing and geocoding requests are proxied through core/GoogleMaps.php. The Google API key is hidden from browsers, and responses are cached on disk (30 days for geocoding, 2 minutes for routes) to minimize external API costs and latency."),
        ("1.3.23 Real-Time In-App Notifications", "The system includes an in-app notification center that distributes targeted alerts (order status changes, dispatch pings, refund updates, and verification results) across all four user roles."),
        ("1.3.24 Relational Database Architecture (22 Tables)", "The platform manages data in MySQL (kravyo_db) across 22 normalized InnoDB tables with foreign key constraints, spatial indexing, multi-tiffin batch run clustering, and transactional integrity.")
    ]
    for p_title, p_desc in points:
        add_sub_heading(doc, p_title)
        add_para(doc, p_desc)
        
    add_main_heading(doc, "1.4 Comparative Study of Current Semester System Features")
    add_para(doc, "To establish the unique competitive advantages and technological superiority of the Kravyo platform, a systematic comparative study was conducted evaluating Kravyo against three dominant operating paradigms in the food-tech industry: 1) Commercial Hyperlocal Aggregators (e.g. Swiggy, Zomato), 2) Traditional Unorganized Tiffin Providers (offline local Dabbawalas / phone ordering), and 3) Basic Online Food Ordering Scripts (generic open-source academic applications).")
    
    add_sub_heading(doc, "1.4.1 Comprehensive Comparative Feature Matrix")
    add_para(doc, "Table 1.2 details the architectural, financial, operational, and algorithmic comparison between Kravyo and existing market alternatives:")
    
    comp_matrix = [
        ("Architecture & Portals", "4 Strictly Isolated Portals (Customer, Chef, Rider, Admin)", "Single Monolithic App with Shared Contexts", "No Digital Platform (WhatsApp/Phone/Notebook)", "Single/Dual Role Basic Web Forms"),
        ("Commission Model", "Fair 10% Flat Commission (Vendor-friendly)", "Steep 28% to 35% Deductions + Hidden Listing Fees", "0% (Unorganized, No Platform Fees)", "Static Margin or None"),
        ("Dietary & Health Controls", "Granular Spice, Oil Level, Jain & Diabetic Badges", "Generic Unstructured Text Field (Often Ignored)", "Verbal Request (Inconsistent Preparation)", "Basic Veg/Non-Veg Checkbox"),
        ("Recurring Tiffin Plans", "Automated Weekly/Monthly Slots with Batch Forecasts", "Unavailable (Impulse Dining Focus Only)", "Manual Paper Ledgers & Fixed Pre-payment", "None / Manual Orders"),
        ("Logistics & Dispatch", "4-Stage Smart Cascade Hybrid Dispatch Engine", "Broadcast Push Notification to Fleet (High Churn)", "Self-Managed / Customer Pickup", "Manual Admin Assignment or None"),
        ("Live GPS Tracking", "Pusher WebSockets + Animated Marker & Polyline", "Polling-based Map Refresh", "None (Manual Phone Calls to Courier)", "Static Map or Dummy Coordinates"),
        ("Handover Authentication", "Cryptographic 4-Digit Customer OTP Handshake", "Optional / Doorstep Drop Without Verification", "Verbal Confirmation (High Dispute Risk)", "Simple Order Delivered Button"),
        ("Food Waste Reduction", "Zero-Waste Section with Countdown Expiration Timers", "None (Surplus Food Discarded at Closing)", "Informal Next-Day Reuse or Wastage", "None"),
        ("Financial Refunds", "Instant Server-Side Razorpay REST API Auto-Refunds", "Manual 3 to 7 Day Banking Cycles / Chatbot Queues", "Manual Cash Disputes / No Refund Policy", "Manual Database Flag Update"),
        ("AI Recommendations", "TF-IDF + Cosine Similarity with Auto PHP Failover", "Proprietary Closed Black-Box Cloud Algorithms", "None", "None / Static List"),
        ("Hygiene Compliance", "FSSAI Document Verification & Verified Trust Badges", "Commercial Restaurant Inspections Only", "Unregulated Home Kitchens (No Hygiene Audit)", "No License Verification Workflow")
    ]
    
    add_table_caption(doc, "Comprehensive Feature Matrix: Kravyo vs. Existing Food Delivery Systems", "1.2")
    tbl_comp = doc.add_table(rows=len(comp_matrix)+1, cols=5)
    tbl_comp.rows[0].cells[0].paragraphs[0].text = "Evaluation Parameter"
    tbl_comp.rows[0].cells[1].paragraphs[0].text = "Kravyo Platform"
    tbl_comp.rows[0].cells[2].paragraphs[0].text = "Commercial Aggregators"
    tbl_comp.rows[0].cells[3].paragraphs[0].text = "Traditional Tiffin Services"
    tbl_comp.rows[0].cells[4].paragraphs[0].text = "Basic Food Scripts"
    
    for i, (param, kravyo, comm, trad, basic) in enumerate(comp_matrix, 1):
        tbl_comp.rows[i].cells[0].paragraphs[0].text = param
        tbl_comp.rows[i].cells[1].paragraphs[0].text = kravyo
        tbl_comp.rows[i].cells[2].paragraphs[0].text = comm
        tbl_comp.rows[i].cells[3].paragraphs[0].text = trad
        tbl_comp.rows[i].cells[4].paragraphs[0].text = basic
    apply_table_styles(tbl_comp, [Inches(1.2), Inches(1.3), Inches(1.3), Inches(1.2), Inches(1.27)])
    
    add_sub_heading(doc, "1.4.2 In-Depth Feature Analysis & Kravyo Competitive Edge")
    comp_points = [
        ("1. Disruption of Aggregator Commission Monopoly: ", "Commercial aggregators such as Swiggy and Zomato levy aggressive commissions ranging between 28% and 35% on every transaction, in addition to charging high onboarding fees and onboarding commissions. This unsustainable burden forces small home chefs and homemakers out of the digital economy. Kravyo disrupts this monopoly by implementing an ethical, flat 10% platform commission, enabling home cooks to operate profitably while offering affordable pricing to daily consumers."),
        ("2. Granular Dietary & Health Customization vs. Generic Ordering: ", "Conventional food aggregators treat culinary items as standardized commodities, providing only a generic 'special instructions' text box that kitchen staff frequently overlook. Kravyo introduces structured dietary preferences (Spice Intensity: Low, Medium, High; Oil Preference: Normal, Less Oil; Jain preparation; and Diabetic-friendly filters). These preferences are stored as first-class database attributes and rendered as high-visibility badges on kitchen preparation tickets."),
        ("3. Automated Recurring Tiffin Subscriptions vs. Impulse Ordering: ", "Commercial delivery services are architected strictly for sporadic impulse ordering. Daily students, corporate workers, and elderly citizens requiring predictable, affordable home-cooked lunches and dinners are neglected. Kravyo features an automated weekly and monthly recurring subscription scheduler that generates daily delivery tasks (subscription_deliveries) and provides home chefs with automated daily batch cooking forecasts."),
        ("4. 4-Stage Smart Cascade Dispatch vs. Broadcast Spam: ", "Existing platforms broadcast order alerts simultaneously to dozens of riders within a radius, creating high delivery churn, driver bidding friction, and notification fatigue. Kravyo's 4-stage smart cascade dispatch algorithm prioritizes corridor batching, pings the single nearest idle courier exclusively for 25 seconds, cascades to the second candidate, and only resorts to broadcast as a fail-safe fallback."),
        ("5. Zero-Waste Food Rescue Marketplace: ", "Commercial restaurants discard unsold, freshly prepared food at the end of their operating shifts to clear inventories. Kravyo introduces a dedicated Zero Food Waste clearance portal where home chefs can list remaining portions at dynamic discounts with real-time countdown timers. This initiative provides low-cost meals to cost-conscious students while eliminating daily culinary wastage."),
        ("6. Instant Automated REST API Refunds vs. Delayed Claims: ", "Customer refund requests on commercial platforms typically involve lengthy chatbot runarounds and 3 to 7 business day bank clearing delays. In Kravyo, if an online-paid order is rejected by a home chef due to stockout or cancelled before food preparation commences, the backend immediately executes an automated server-side REST API refund call to Razorpay, crediting the funds directly back to the customer's source account with zero manual intervention."),
        ("7. Fault-Tolerant AI Architecture: ", "While commercial platforms rely on opaque, cloud-locked recommendation models that suffer total outage when external APIs fail, Kravyo features a resilient Scikit-learn TF-IDF microservice coupled with an internal PHP heuristic scoring failover engine, guaranteeing 100% recommendation uptime even if the Python microservice is temporarily offline.")
    ]
    for cp_pre, cp_text in comp_points:
        add_bullet(doc, cp_text, cp_pre)
        
    add_main_heading(doc, "1.5 Tools and Technology Selected for Current Semester")
    add_para(doc, "The following tools, languages, software frameworks, and cloud APIs have been selected and integrated into the development of the Kravyo Cloud Kitchen Platform:")
    
    tools_data = [
        ("1", "HTML5", "Used to create semantic web pages, accessible forms, and browser geolocation interfaces."),
        ("2", "CSS3 & Bootstrap 5.3", "Used to style the application with a custom design system, responsive grids, and components."),
        ("3", "JavaScript (ES6+)", "Used for client-side interactions, AJAX requests, countdown timers, and map rendering."),
        ("4", "PHP 8.2+ (Custom MVC)", "Used for backend logic, front-controller routing (Router.php), middleware guards, and database operations."),
        ("5", "MySQL 8+ / MariaDB", "Used as the relational database (22 tables) with spatial indexes and Haversine distance queries."),
        ("6", "Google Maps Platform", "Used for interactive maps, address geocoding, distance matrix, and Routes API v2 navigation."),
        ("7", "Google Maps Caching Proxy", "Server-side disk caching (core/GoogleMaps.php) in storage/cache/maps/ to optimize API usage and costs."),
        ("8", "Pusher WebSockets", "Used for real-time bidirectional streaming of driver GPS coordinates to the live tracking map."),
        ("9", "Razorpay Payment Gateway", "Used for secure online checkout (UPI/Cards) and automated server-side instant refunds via REST API."),
        ("10", "Python 3.10+ & Flask", "Used to build the standalone machine learning recommendation microservice (python_ai/)."),
        ("11", "Scikit-learn, Pandas, NumPy", "Used for TF-IDF text vectorization, cosine similarity computation, and popularity scoring."),
        ("12", "HTML5 Web Audio API", "Used to play continuous sound alerts (beep.wav) on the chef dashboard for incoming orders."),
        ("13", "XAMPP (Apache & MySQL)", "Used as the local development server with Apache mod_rewrite enabled for clean URL routing."),
        ("14", "Visual Studio Code", "Used as the primary integrated development environment (IDE) for project development and debugging."),
        ("15", "Google Chrome & Postman", "Used for browser execution, responsive testing, session isolation validation, and API testing.")
    ]
    
    add_table_caption(doc, "Tools and Technologies Selected for Kravyo Project", "1.3")
    tbl_tools = doc.add_table(rows=len(tools_data)+1, cols=3)
    tbl_tools.rows[0].cells[0].paragraphs[0].text = "Sr. No."
    tbl_tools.rows[0].cells[1].paragraphs[0].text = "Tool / Technology"
    tbl_tools.rows[0].cells[2].paragraphs[0].text = "Functional Purpose"
    for i, (sr, tool, purp) in enumerate(tools_data, 1):
        tbl_tools.rows[i].cells[0].paragraphs[0].text = sr
        tbl_tools.rows[i].cells[1].paragraphs[0].text = tool
        tbl_tools.rows[i].cells[2].paragraphs[0].text = purp
    apply_table_styles(tbl_tools, [Inches(0.8), Inches(2.2), Inches(3.27)])
    
    # Subsections 1.5.1 to 1.5.8
    tech_subs = [
        ("1.5.1 Frontend Technologies", "HTML5, CSS3, Bootstrap 5.3, and modern JavaScript (ES6+) are used to build a responsive user interface. The frontend supports dynamic modal windows for dish customization, interactive countdown timers for zero-waste deals, and live Google Maps with animated delivery markers."),
        ("1.5.2 Backend Technology (Custom PHP MVC)", "The backend is developed in PHP 8.2+ using a lightweight custom Model-View-Controller (MVC) architecture. It features regex front-controller routing (Router.php), role-guard middleware (Middleware.php), and service layers for dispatch logistics (DispatchService.php) and payments (RazorpayService.php)."),
        ("1.5.3 Database Technology", "MySQL 8+ is used as the relational database management system (kravyo_db). It contains 22 normalized InnoDB tables that maintain data integrity using foreign keys, cascade rules, multi-tiffin batch clustering, and spatial coordinates for distance calculations."),
        ("1.5.4 Mapping & Live Geolocation", "The platform uses the Google Maps JavaScript API, Geocoding API, and Routes API v2. A server-side proxy (core/GoogleMaps.php) with a 30-day disk cache secures the Google API key and keeps usage within Google's monthly free tier."),
        ("1.5.5 Real-Time Communication & Audio Alerts", "Pusher WebSockets and the HTML5 Geolocation API stream live driver coordinates (watchPosition) to customer maps without page reloads. The HTML5 Web Audio API triggers beep.wav alerts on the chef’s dashboard when new orders arrive."),
        ("1.5.6 Payment & Automated Refund Gateway", "Financial operations are handled by Razorpay. The system integrates both client-side modal checkout and server-side REST API calls to process instant automated refunds when orders are rejected or cancelled."),
        ("1.5.7 AI / Machine Learning Microservice", "A standalone microservice built with Python, Flask, Pandas, and Scikit-learn generates personalized food recommendations using TF-IDF vectorization and Cosine Similarity. An internal PHP recommendation fallback guarantees uninterrupted platform availability."),
        ("1.5.8 Development Environment", "The application runs locally on an Apache web server through XAMPP with URL rewriting. Visual Studio Code is used for code development, and Google Chrome and Postman are used for testing and debugging.")
    ]
    for t_title, t_desc in tech_subs:
        add_sub_heading(doc, t_title)
        add_para(doc, t_desc)
        
    add_main_heading(doc, "1.6 Summary of Sequence")
    add_para(doc, "In this sequence, the purpose, end-to-end system process flow, core functional points, comparative feature study against commercial aggregators, and technology stack for the Kravyo multi-tenant cloud kitchen platform have been defined.")
    add_para(doc, "The system provides dedicated facilities across four isolated roles: Customer, Home Chef, Delivery Partner, and Administrator. Key technical capabilities include an automated 4-stage smart cascade dispatch engine, Razorpay payments with automated instant refunds, live GPS order tracking via Pusher WebSockets, recurring tiffin subscriptions with batch forecasting, a zero-waste rescue module, an AI recommendation microservice with PHP fallback, and scheduled multi-tiffin milk-run batch routing, and a 22-table MySQL relational database. These specifications provide a clear, comprehensive foundation for the system design and implementation sequences that follow.")
    
    doc.add_page_break()
    print("Chapter 1 completed. Building Chapter 2...")
    
    # CHAPTER 2: SEQUENCE 4
    add_chapter_title(doc, "Chapter 2: Project Requirement Details (Sequence No. 4)")
    add_main_heading(doc, "2.1 Scope")
    add_para(doc, "The scope of Kravyo is to provide an end-to-end, multi-tenant cloud kitchen, on-demand food ordering, and recurring meal subscription platform. It connects home chefs and homemakers with customers, backed by an automated delivery fleet.")
    add_sub_heading(doc, "Key Scope Boundaries:")
    add_bullet(doc, "Strictly isolated web portals for Customers (/), Home Chefs (/chef), Delivery Partners (/delivery), and Administrators (/admin), each secured with independent session cookies.", "1. Four Dedicated Portals: ")
    add_bullet(doc, "Location-based kitchen discovery with spice and health customization (Less Oil, Jain, Diabetic-Friendly).", "2. Hyperlocal Discovery & Customization: ")
    add_bullet(doc, "An integrated 4-Stage Smart Cascade Dispatch Engine that assigns nearby drivers without platform-wide broadcast chaos.", "3. Automated Delivery Logistics: ")
    add_bullet(doc, "Live driver coordinate streaming via Pusher WebSockets and HTML5 Geolocation with animated route maps.", "4. Live Real-Time GPS Tracking: ")
    add_bullet(doc, "Weekly and monthly meal subscriptions with automatic daily delivery schedule generation.", "5. Recurring Tiffin Subscriptions: ")
    add_bullet(doc, "Time-decay discounted surplus food module with dynamic auto-expiry timers.", "6. Zero Food Waste Marketplace: ")
    add_bullet(doc, "Online checkout via Razorpay with automated instant server-side refunds on order rejection or cancellation.", "7. Secure Financial Processing: ")
    add_bullet(doc, "Scikit-learn TF-IDF & Cosine Similarity AI microservice with an internal PHP failover fallback.", "8. Intelligent Recommendations: ")
    add_para(doc, "\n(Out of Scope: Cross-country long-haul parcel delivery and packaged grocery trading).")
    
    add_main_heading(doc, "2.2 Functional Requirements")
    fr_modules = [
        ("2.2.1 Multi-Role Authentication & Access Control", [
            ("FR-1.1: ", "The system shall support registration, login, and profile management across 4 isolated roles: Customer, Home Chef, Delivery Partner, and Admin."),
            ("FR-1.2: ", "The system shall enforce multi-tenant session isolation using distinct cookies (KRAVYO_CUSTOMER, KRAVYO_CHEF, KRAVYO_DELIVERY, KRAVYO_ADMIN) to prevent session collisions."),
            ("FR-1.3: ", "Passwords must be hashed using Bcrypt, and protected routes must be guarded by role-based middleware.")
        ]),
        ("2.2.2 Kitchen Onboarding & FSSAI Verification", [
            ("FR-2.1: ", "Home chefs shall create kitchen profiles, upload FSSAI licenses, hygiene documents, and set operating addresses with GPS coordinates."),
            ("FR-2.2: ", "The administrator shall review, approve, or reject kitchen verification requests. Approved kitchens receive a verified hygiene badge."),
            ("FR-2.3: ", "Home chefs shall be able to dynamically toggle their operational status between Open (Accepting Orders) and Closed.")
        ]),
        ("2.2.3 Menu Management & Health Customizations", [
            ("FR-3.1: ", "Home chefs shall add, update, and manage menu items with categories, prices, preparation times, and dietary tags (Veg, Jain, Diabetic-friendly)."),
            ("FR-3.2: ", "Customers shall be able to customize items in their cart: spice level (Low, Medium, High), oil preference (Normal, Less Oil), Jain preparation, and cooking notes.")
        ]),
        ("2.2.4 Cart, Ordering & Razorpay Automated Refunds", [
            ("FR-4.1: ", "Customers shall add items to a single-kitchen cart, apply valid promo codes, select saved addresses, and generate unique order receipts."),
            ("FR-4.2: ", "The system shall process payments via the Razorpay Gateway (UPI, Cards, NetBanking)."),
            ("FR-4.3: ", "If an online-paid order is rejected by the home chef or cancelled before cooking, the backend shall immediately trigger an automated server-side refund via Razorpay's REST API.")
        ]),
        ("2.2.5 4-Stage Smart Cascade Hybrid Dispatch", [
            ("FR-5.1: ", "Upon order acceptance, the dispatch engine shall first check for corridor batching with active riders along the same route."),
            ("FR-5.2: ", "If unbatched, the system shall ping the nearest idle online driver within 5 km with an exclusive 25-second countdown timer."),
            ("FR-5.3: ", "If rejected or timed out, the offer shall instantly cascade to the 2nd nearest driver; if exhausted, it shall fallback to an expanded radius broadcast.")
        ]),
        ("2.2.6 Delivery Partner Portal & 4-Digit OTP Handshake", [
            ("FR-6.1: ", "Delivery partners shall control shift availability via an Online / Offline duty toggle."),
            ("FR-6.2: ", "Drivers shall view turn-by-turn navigation via Google Routes API v2 between the kitchen and the customer's drop-off coordinates."),
            ("FR-6.3: ", "Delivery completion shall strictly require verification of a confidential 4-digit customer OTP to ensure authentic handover.")
        ]),
        ("2.2.7 Real-Time Live GPS Tracking & Audio Alerts", [
            ("FR-7.1: ", "Out-for-delivery orders shall stream driver GPS coordinates (watchPosition) via Pusher WebSockets to the customer's interactive map."),
            ("FR-7.2: ", "The customer map shall render an animated bike marker with bearing rotation, polyline halo route, and dynamic ETA updates."),
            ("FR-7.3: ", "Incoming orders shall trigger continuous audio chime alerts (beep.wav) on the chef's dashboard using the HTML5 Web Audio API.")
        ]),
        ("2.2.8 Weekly & Monthly Tiffin Subscriptions", [
            ("FR-8.1: ", "Home chefs shall create recurring weekly and monthly meal subscription packages."),
            ("FR-8.2: ", "The system shall automatically generate daily delivery slots (subscription_deliveries) and provide chefs with daily meal batch forecasts.")
        ]),
        ("2.2.9 Zero Food Waste Rescue Module", [
            ("FR-9.1: ", "Home chefs shall list freshly cooked surplus meals at discounted prices with expiration countdown timers."),
            ("FR-9.2: ", "The system shall display rescue items in a dedicated carousel and auto-expire them once the cutoff time is reached.")
        ]),
        ("2.2.10 AI Recommendations, Reports & Administration", [
            ("FR-10.1: ", "A Python microservice (TF-IDF + Cosine Similarity) shall recommend dishes based on customer history, with automatic fallback to an internal PHP scoring engine if offline."),
            ("FR-10.2: ", "Customers shall submit formal issue tickets (customer_reports) for missing items or delays; admins shall arbitrate complaints and issue penalties or refunds."),
            ("FR-10.3: ", "The administrator shall manage categories, review system GMV, track platform commissions, and monitor user accounts.")
        ])
    ]
    for mod_title, req_list in fr_modules:
        add_sub_heading(doc, mod_title)
        for r_prefix, r_text in req_list:
            add_bullet(doc, r_text, r_prefix)
            
    add_main_heading(doc, "2.3 Non-Functional Requirements")
    nfr_data = [
        ("1. Multi-Tenant Security", "The system must maintain strict session cookie isolation across all 4 roles. Passwords must be hashed using Bcrypt (cost=12), with CSRF token validation on all state-changing POST requests."),
        ("2. High Availability & Fault Tolerance", "The application must maintain 99.9% uptime. If the Python AI microservice is unreachable, the system must transparently fall back to internal PHP heuristic scoring with zero downtime."),
        ("3. Performance & Caching", "Page load time for public catalog and cart operations must be under 1.5 seconds. Google Maps geocoding and routing responses must be cached on disk (30-day geocoding, 2-minute routes) in storage/cache/maps/ to optimize API limits and latency."),
        ("4. Real-Time Latency", "WebSocket GPS coordinate broadcasts via Pusher and dispatch offer timers must execute with sub-second latency (< 500ms)."),
        ("5. Data Integrity & Soft Deletes", "Deleting a customer delivery address must use soft-deletion (is_deleted = 1) to preserve historical order receipts and prevent MySQL foreign key constraint violations."),
        ("6. Usability & Responsiveness", "The user interface must be fully responsive across mobile phones, kitchen tablets, and desktop displays using a consistent CSS variable design system and Bootstrap 5.3."),
        ("7. Extensibility & Maintainability", "The backend must follow a clean MVC structure, separating business logic into dedicated services (DispatchService.php, RazorpayService.php) and controllers.")
    ]
    tbl_nfr = doc.add_table(rows=len(nfr_data)+1, cols=2)
    tbl_nfr.rows[0].cells[0].paragraphs[0].text = "Quality Attribute"
    tbl_nfr.rows[0].cells[1].paragraphs[0].text = "Requirement Specification"
    for i, (qattr, spec) in enumerate(nfr_data, 1):
        tbl_nfr.rows[i].cells[0].paragraphs[0].text = qattr
        tbl_nfr.rows[i].cells[1].paragraphs[0].text = spec
    apply_table_styles(tbl_nfr, [Inches(2.2), Inches(4.07)])
    
    add_main_heading(doc, "2.4 Exciting Functionalities")
    add_para(doc, "These innovative features distinguish Kravyo from basic online food ordering systems:")
    exciting = [
        ("1. 4-Stage Smart Cascade Hybrid Dispatch Engine: ", "Unlike traditional food apps that blast notifications to dozens of riders simultaneously, Kravyo uses an intelligent sequential algorithm (Corridor Batching -> 25s Single-Driver Ping -> Instant 2nd Driver Cascade -> Radius Broadcast Fallback) that prevents delivery chaos and driver churn."),
        ("2. Live Swiggy-Style GPS Tracking with WebSockets: ", "Features bidirectional real-time coordinate streaming using Pusher WebSockets and Google Maps Advanced Markers. The customer tracking map renders an animated bike icon with heading bearing rotation, polyline routes, and live ETA calculation."),
        ("3. Automated Instant Refunds via Razorpay REST API: ", "Eliminates lengthy manual refund claim cycles. If a chef declines an order due to unavailable stock or if an order is cancelled before food preparation begins, the backend immediately calls Razorpay's refund endpoints to return funds to the customer's source account."),
        ("4. Resilient AI/ML Recommendation Microservice with PHP Fallback: ", "Uses Scikit-learn TF-IDF vectorization and Cosine Similarity over dish ingredients, categories, and dietary tags. In case of Python service interruption, the platform automatically switches to an internal PHP scoring engine, guaranteeing continuous operation."),
        ("5. Dynamic Zero Food Waste Rescue Section: ", "Allows home chefs to monetize fresh, unsold portions before closing hours at dynamic discounts. Built-in countdown timers auto-expire unsold items, encouraging sustainability and providing affordable meals."),
        ("6. Automated Tiffin Subscription Batch Generator: ", "Simplifies daily meals for students and working professionals through weekly and monthly recurring plans, auto-generating daily delivery tasks and providing chefs with batch cooking forecasts."),
        ("7. Multi-Tenant Cookie-Level Session Isolation: ", "Enables administrators, chefs, customers, and delivery partners to log in simultaneously on the same device and browser without session conflicts, using independent cookie contexts.")
    ]
    for e_prefix, e_text in exciting:
        add_bullet(doc, e_text, e_prefix)
        
    add_main_heading(doc, "2.5 Number of Users")
    add_para(doc, "The Kravyo platform supports Four Main Types of Users, each accessing an isolated application portal:")
    user_roles_data = [
        ("1", "Customer", "/", "KRAVYO_CUSTOMER", "Discovers nearby home kitchens, places on-demand orders, customizes meals, subscribes to recurring tiffins, tracks live deliveries, and submits reviews or dispute reports."),
        ("2", "Home Chef / Seller", "/chef", "KRAVYO_CHEF", "Registers virtual kitchen with FSSAI verification, manages food menus, receives live orders with audio alerts, forecasts daily tiffins, and monetizes surplus food in the Zero Waste section."),
        ("3", "Delivery Partner", "/delivery", "KRAVYO_DELIVERY", "Onboards with vehicle and license details, manages online/offline duty status, accepts 25-second cascade delivery pings, navigates turn-by-turn routes, and completes deliveries via OTP verification."),
        ("4", "Administrator", "/admin", "KRAVYO_ADMIN", "Manages the entire ecosystem, verifies kitchen FSSAI documents and riders, monitors orders and transactions, arbitrates disputes, and reviews platform revenue metrics.")
    ]
    tbl_users = doc.add_table(rows=len(user_roles_data)+1, cols=5)
    tbl_users.rows[0].cells[0].paragraphs[0].text = "Sr."
    tbl_users.rows[0].cells[1].paragraphs[0].text = "User Type"
    tbl_users.rows[0].cells[2].paragraphs[0].text = "Portal Route"
    tbl_users.rows[0].cells[3].paragraphs[0].text = "Session Cookie"
    tbl_users.rows[0].cells[4].paragraphs[0].text = "Functional Description"
    for i, (sr, utype, path, cookie, desc) in enumerate(user_roles_data, 1):
        tbl_users.rows[i].cells[0].paragraphs[0].text = sr
        tbl_users.rows[i].cells[1].paragraphs[0].text = utype
        tbl_users.rows[i].cells[2].paragraphs[0].text = path
        tbl_users.rows[i].cells[3].paragraphs[0].text = cookie
        tbl_users.rows[i].cells[4].paragraphs[0].text = desc
    apply_table_styles(tbl_users, [Inches(0.5), Inches(1.2), Inches(1.0), Inches(1.4), Inches(2.17)])
    
    add_main_heading(doc, "2.6 User-Wise Characteristics")
    chars = [
        ("2.6.1 Customer Characteristics", [
            "Can register, verify accounts via email OTP, and log in securely.",
            "Browses and filters nearby cloud kitchens and dishes based on location radius, cuisine, and dietary badges (Veg, Jain, Diabetic-friendly).",
            "Customizes dishes with specific health instructions (spice intensity, oil level, Jain preparation).",
            "Subscribes to weekly or monthly recurring tiffin plans with scheduled delivery windows.",
            "Makes online payments via Razorpay (UPI, cards, net banking) and receives automated refunds on cancelled orders.",
            "Tracks live courier transit on Google Maps with animated bike rotation and dynamic ETA via Pusher WebSockets.",
            "Provides 4-digit secret OTP to the courier upon delivery and submits ratings, reviews, or dispute reports."
        ]),
        ("2.6.2 Home Chef or Seller Characteristics", [
            "Registers virtual kitchen, provides personal culinary story, and uploads FSSAI license and hygiene documents for verification.",
            "Toggles kitchen operational status dynamically between Open (Accepting Orders) and Closed.",
            "Manages menu catalog (dishes, pricing, preparation time, and dietary tags).",
            "Receives incoming order alerts with continuous real-time audio chimes (beep.wav) and accepts/rejects orders.",
            "Views batch cooking forecasts for recurring daily breakfast, lunch, and dinner subscriptions.",
            "Lists surplus end-of-day meals at discounted prices with dynamic countdown timers in the Zero Food Waste module.",
            "Tracks daily earnings, completed orders, and customer feedback from the seller dashboard."
        ]),
        ("2.6.3 Delivery Partner Characteristics", [
            "Registers on the delivery portal with vehicle information (bike, scooter, EV) and driving license details.",
            "Controls working shifts using an Online / Offline duty toggle.",
            "Receives targeted 25-second exclusive dispatch offers showing pickup/drop locations, distance, and earnings.",
            "Navigates between kitchens and customer drop-off locations using Google Routes API v2 turn-by-turn routing.",
            "Streams live GPS coordinates automatically (watchPosition) during active transit.",
            "Collects and validates the confidential 4-digit customer OTP to confirm safe delivery handover."
        ]),
        ("2.6.4 Administrator Characteristics", [
            "Has elevated platform privileges protected by administrative session authentication.",
            "Inspects pending home chef registrations, reviews uploaded FSSAI licenses, and grants verified hygiene badges.",
            "Approves or rejects delivery partner onboarding applications.",
            "Manages global food categories, system commissions, and platform parameters.",
            "Investigates customer grievance reports (customer_reports), arbitrates disputes, and triggers penalties or automated refunds.",
            "Monitors platform metrics, Gross Merchandise Value (GMV), active delivery maps, and transaction logs."
        ])
    ]
    for c_title, c_items in chars:
        add_sub_heading(doc, c_title)
        for item in c_items:
            add_bullet(doc, item)
            
    add_main_heading(doc, "2.7 Data Dictionary and Integrity Constraints")
    add_para(doc, "The Kravyo platform utilizes a relational MySQL database (kravyo_db) structured across 22 normalized InnoDB tables to ensure strict data integrity, fast query execution, multi-tiffin batch logistics, and geospatial indexing.")
    
    add_sub_heading(doc, "2.7.1 Data Dictionary Summary (22 Tables)")
    dict_summary = [
        ("1", "customers", "id, full_name, email, phone, password_hash, status", "Stores customer credentials and profile records."),
        ("2", "chefs", "id, full_name, email, phone, password_hash, status", "Stores home chef vendor credentials and verification flags."),
        ("3", "delivery_users", "id, full_name, email, phone, password_hash, status", "Stores authentication accounts for delivery partners."),
        ("4", "admins", "id, full_name, email, password_hash, role", "Stores administrative accounts with management permissions."),
        ("5", "kitchens", "id, user_id, kitchen_name, address, latitude, longitude, fssai_license, approval_status, is_open", "Stores cloud kitchen profiles, GPS coordinates, FSSAI certificates, and operational status."),
        ("6", "categories", "id, category_name, description, image", "Stores global cuisine classifications (Gujarati, Punjabi, etc.)."),
        ("7", "menu_items", "id, kitchen_id, category_id, item_name, price, is_veg, is_jain_available, is_diabetic_friendly", "Stores food dishes offered by kitchens with dietary tags."),
        ("8", "addresses", "id, user_id, address_type, street_address, latitude, longitude, is_deleted", "Stores delivery addresses with soft-delete protection (is_deleted) to preserve order history."),
        ("9", "orders", "id, order_number, order_type, customer_id, kitchen_id, delivery_partner_id, total_amount, payment_status, order_status, delivery_otp, dispatch_status", "Central order ledger managing kitchen prep, dispatch state, payment lifecycle, and 4-digit OTP."),
        ("10", "order_items", "id, order_id, menu_item_id, quantity, unit_price, subtotal, spice_level, oil_level, is_jain", "Stores individual line items with spice, oil, and dietary options."),
        ("11", "tiffin_subscriptions", "id, kitchen_id, plan_name, plan_type, price, meals_per_day, is_active", "Stores weekly and monthly tiffin subscription packages created by home chefs."),
        ("12", "customer_subscriptions", "id, customer_id, plan_id, kitchen_id, address_id, start_date, end_date, status", "Stores active customer recurring subscription agreements."),
        ("13", "subscription_deliveries", "id, customer_subscription_id, kitchen_id, run_id, stop_number, delivery_date, meal_type, status", "Daily recurring delivery slots linked to multi-tiffin batch runs with waypoint stop ordering."),
        ("14", "delivery_partners", "id, user_id, vehicle_type, vehicle_number, is_online, current_lat, current_lng, rating", "Stores driver profiles, live GPS coordinates, and shift duty toggle."),
        ("15", "delivery_offers", "id, order_id, delivery_partner_id, status, expires_at", "Manages 25-second smart cascade dispatch pings sent to riders."),
        ("16", "zero_waste_items", "id, menu_item_id, kitchen_id, original_price, discounted_price, quantity_available, expiry_time", "Stores discounted surplus food items with countdown timers."),
        ("17", "reviews", "id, order_id, customer_id, kitchen_id, rating, review_text", "Stores verified 1–5 star ratings and reviews for completed orders."),
        ("18", "customer_reports", "id, customer_id, kitchen_id, order_id, report_type, description, status", "Stores grievance tickets for missing items, quality defects, or delivery delays."),
        ("19", "notifications", "id, user_id, user_type, title, message, is_read", "Role-targeted in-app notification alerts across all 4 portals."),
        ("20", "pending_registrations", "email, full_name, phone, password_hash, email_otp_hash, email_otp_expires_at", "Staging table holding user signups pending email OTP verification."),
        ("21", "delivery_runs", "id, run_code, meal_type, delivery_date, city, delivery_partner_id, status, total_orders, total_payout, started_at, completed_at", "Manages consolidated multi-tiffin milk-run batches assigned to delivery riders with aggregate batch earnings."),
        ("22", "delivery_run_stops", "id, run_id, stop_sequence, stop_type, subscription_delivery_id, target_id, target_name, target_address, contact_phone, latitude, longitude, items_count, status, delivery_otp, container_returned", "Sequential waypoints for multi-kitchen pickup and multi-customer drops with OTP and container return tracking.")
    ]
    tbl_dict_sum = doc.add_table(rows=len(dict_summary)+1, cols=4)
    tbl_dict_sum.rows[0].cells[0].paragraphs[0].text = "Sr."
    tbl_dict_sum.rows[0].cells[1].paragraphs[0].text = "Table Name"
    tbl_dict_sum.rows[0].cells[2].paragraphs[0].text = "Key Attributes"
    tbl_dict_sum.rows[0].cells[3].paragraphs[0].text = "Functional Description"
    for i, (sr, tname, attr, desc) in enumerate(dict_summary, 1):
        tbl_dict_sum.rows[i].cells[0].paragraphs[0].text = sr
        tbl_dict_sum.rows[i].cells[1].paragraphs[0].text = tname
        tbl_dict_sum.rows[i].cells[2].paragraphs[0].text = attr
        tbl_dict_sum.rows[i].cells[3].paragraphs[0].text = desc
    apply_table_styles(tbl_dict_sum, [Inches(0.5), Inches(1.5), Inches(2.2), Inches(2.07)])
    
    add_sub_heading(doc, "2.7.2 Integrity Constraints")
    constraints = [
        ("Primary Key Constraint: ", "Each table contains a primary key (e.g., customers.id, orders.id, kitchens.id) that uniquely identifies each entity."),
        ("Foreign Key & Referential Integrity: ", "Establishes relational links across entities (e.g., kitchens.user_id -> chefs.id, orders.customer_id -> customers.id, delivery_offers.delivery_partner_id -> delivery_partners.id). Records cannot reference non-existent parent entities."),
        ("Soft-Deletion Integrity: ", "Instead of hard-deleting records, the addresses table uses is_deleted = 1. This preserves relational integrity and historical receipts for past orders without breaking foreign key constraints."),
        ("Unique Constraints: ", "Enforces uniqueness on critical fields such as customers.email, chefs.phone, admins.email, orders.order_number, and reviews.order_id (one review per order)."),
        ("Not Null Constraints: ", "Mandatory operational fields (e.g., orders.total_amount, menu_items.price, kitchens.address, delivery_partners.user_id) cannot be left blank."),
        ("Check Constraints: ", "Validates permissible numeric ranges (e.g., reviews.rating between 1 and 5; menu_items.price > 0; zero_waste_items.discounted_price < original_price)."),
        ("Enumerated Constraints (ENUM): ", "Constrains state columns to authorized values (e.g. orders.order_status, orders.payment_status, delivery_partners.vehicle_type, kitchens.approval_status).")
    ]
    for c_pre, c_text in constraints:
        add_bullet(doc, c_text, c_pre)
        
    add_main_heading(doc, "2.8 Summary About Expected Functionality and Exciting Functionality")
    add_para(doc, "The platform provides a clear division between standard operational features and innovative technological capabilities:")
    add_sub_heading(doc, "Expected Functionality (Core Baseline)")
    add_para(doc, "Provides the standard mechanisms essential for an on-demand food portal: multi-role authentication, kitchen profile management, menu builder, cart checkout, order history, ratings and reviews, responsive user interfaces, and administrative category management.")
    add_sub_heading(doc, "Exciting Functionality (Kravyo Differentiators)")
    add_para(doc, "Provides unique, high-value capabilities that elevate Kravyo beyond conventional food apps: 4-Stage smart cascade hybrid dispatch, live GPS tracking via Pusher WebSockets, automated instant refunds via Razorpay REST API, resilient AI/ML recommendation microservice with PHP fallback, dynamic zero-waste rescue section with countdown timers, recurring tiffin subscription scheduler, and strict 4-portal session cookie isolation.")
    
    add_main_heading(doc, "2.9 Users of the System with Their Characteristics")
    add_image_placeholder(doc, "Kravyo Platform Architecture & Four User Roles Hierarchy", figure_id="2.1", hint="ASCII Architecture Diagram / Role Hierarchy")
    add_bullet(doc, "Explores local homemade food, tailors spice and oil preferences, pays via Razorpay, tracks delivery in real time, and manages daily tiffins.", "Customer: ")
    add_bullet(doc, "Homemakers and independent cooks who manage virtual kitchens, upload FSSAI compliance documents, receive live orders with sound alerts, and optimize surplus food.", "Home Chef: ")
    add_bullet(doc, "Independent couriers who toggle online duty, accept targeted dispatch pings, navigate routes using Google Maps, and ensure verified delivery using customer OTPs.", "Delivery Partner: ")
    add_bullet(doc, "Oversees compliance and quality, approves kitchen food licenses, verifies drivers, arbitrates customer disputes, and tracks system financial health.", "Administrator: ")
    
    add_main_heading(doc, "2.10 Summary of Sequence")
    add_para(doc, "This sequence has established the system scope, user classifications, data dictionary, and integrity constraints for the Kravyo platform. The architecture encompasses four distinct user roles and a robust 22-table MySQL relational database supporting multi-tiffin batch runs, spatial indexing, and soft-deletion safety. These specifications provide the exact functional blueprint required for the upcoming UML use cases, activity diagrams, and user interface designs.")
    
    doc.add_page_break()
    print("Chapter 2 completed. Building Chapter 3...")
    
    # CHAPTER 3: SEQUENCE 5
    add_chapter_title(doc, "Chapter 3: Implementation of Functionalities & Design Preparation (Sequence No. 5)")
    add_main_heading(doc, "3.1 & 3.2 Use Case Diagrams")
    add_para(doc, "The Use Case Diagrams capture the functional interactions between external actors (Customer, Home Chef, Delivery Partner, and Administrator) and the Kravyo Cloud Kitchen Platform. In accordance with standard Unified Modeling Language (UML) specifications, <<include>> relationships indicate mandatory sub-activities directed toward the base usecase, while <<extend>> relationships represent optional execution branches.")
    
    use_cases = [
        ("Use Case Diagram for Customer Actor", "3.1", "File Reference: diagrams/use_case_customer.png"),
        ("Use Case Diagram for Home Chef Actor", "3.2", "File Reference: diagrams/use_case_chef.png"),
        ("Use Case Diagram for Delivery Partner Actor", "3.3", "File Reference: diagrams/use_case_delivery.png"),
        ("Use Case Diagram for Administrator Actor", "3.4", "File Reference: diagrams/use_case_admin.png")
    ]
    for uc_cap, fig_id, f_hint in use_cases:
        add_sub_heading(doc, f"3.2.{fig_id.split('.')[1]} {uc_cap}")
        add_image_placeholder(doc, uc_cap, figure_id=fig_id, hint=f_hint)
        
    add_main_heading(doc, "3.3 Activity Diagrams")
    add_para(doc, "Activity Diagrams model the sequential, decision-oriented, and concurrent workflows of Kravyo's primary business processes, illustrating user actions, decision branches, fork/join operations, and terminal states without implementation-level database terminology.")
    
    act_diagrams = [
        ("Activity Diagram: Customer Authentication & Registration Flow", "3.5", "diagrams/activity_3_3_1_customer_auth.png"),
        ("Activity Diagram: Tiffin Meal Subscription Life Cycle", "3.6", "diagrams/activity_3_3_2_subscription.png"),
        ("Activity Diagram: Cart Checkout & Multi-Channel Payment Verification", "3.7", "diagrams/activity_3_3_3_payment.png"),
        ("Activity Diagram: Kitchen Order Processing & Preparation Flow", "3.8", "diagrams/activity_3_3_4_order_processing.png"),
        ("Activity Diagram: Chef Menu Catalog & Portion Inventory Management", "3.9", "diagrams/activity_3_3_5_menu_inventory.png"),
        ("Activity Diagram: Chef Kitchen Onboarding & FSSAI Verification", "3.10", "diagrams/activity_3_3_6_chef_onboarding.png"),
        ("Activity Diagram: 4-Stage Cascade Delivery Dispatch & OTP Handover", "3.11", "diagrams/activity_3_3_7_delivery_partner.png"),
        ("Activity Diagram: Customer Grievance, Dispute & Razorpay Refund", "3.12", "diagrams/activity_3_3_8_dispute_refund.png"),
        ("Activity Diagram: Platform Commission Settlement & Payouts", "3.13", "diagrams/activity_3_3_9_analytics_commission.png")
    ]
    for act_cap, fig_id, f_hint in act_diagrams:
        add_sub_heading(doc, f"3.3.{fig_id.split('.')[1]} {act_cap}")
        add_image_placeholder(doc, act_cap, figure_id=fig_id, hint=f_hint)
        
    add_main_heading(doc, "3.4 GUI Design (Functionality-Wise)")
    add_para(doc, "The Graphical User Interface (GUI) of Kravyo is structured across four dedicated portals to enforce role separation and operational clarity: Customer (/), Home Chef (/chef), Delivery Partner (/delivery), and Administrator (/admin).")
    
    gui_portals = [
        ("3.4.1 Customer Interface Screens", [
            ("Homepage & Discovery Feed", "Serves as the primary landing hub featuring a dynamic hero banner, cuisine category quick-links, curated recommendations, and zero-waste rescue promotions."),
            ("Authentication & Verification Modal", "Provides Customer/Chef registration and login tabs, form validation, 6-digit email OTP modal, and secure password reset workflows."),
            ("Nearby Kitchens Map View", "Integrates Google Maps with a 15 km delivery radius overlay, interactive kitchen marker pins, distance calculations, and real-time open/closed badges."),
            ("Food Menu & Search Explorer", "Offers multi-parameter search by dish or kitchen name, real-time dietary filters (Veg, Jain, Diabetic-Friendly), price sliders, and spice level indicators."),
            ("Home Kitchen Profile", "Displays the chef's culinary story, hygiene ratings, customer review summaries, operational hours, verified trust badge, and full catalog of home-cooked dishes."),
            ("Dish Details & Customization Modal", "Presents dish photographs, ingredients, available portions, customizable cooking options (Spice, Oil, Jain), and special preparation notes."),
            ("Interactive Shopping Cart", "Consolidates selected items from a single kitchen, highlights Zero-Waste savings, allows quantity adjustments, displays cooking notes, and directs to checkout."),
            ("Checkout & Delivery Range Check", "Enables selection of saved addresses or pin-dropping a new address on an interactive map. Automatically verifies that the address is within the 15 km delivery zone."),
            ("Payment Processing Interface", "Presents three payment options: Cash on Delivery (COD), Scan & Pay via Dynamic UPI QR Code (with 5-minute timer), and Online Gateway (Cards/NetBanking)."),
            ("Live Order Tracking Timeline", "Displays real-time order progression, interactive live courier GPS tracking map, and the confidential 4-digit delivery handover OTP."),
            ("Order History & Receipt Center", "Catalogs past orders with date, amount, and items; provides downloadable receipts, one-click re-order, and a \"Report Issue\" grievance button."),
            ("Tiffin Subscriptions Hub", "Showcases recurring homemade tiffin packages (Weekly or Monthly), allows meal schedule selection (Lunch, Dinner, Both), and active status tracking."),
            ("Zero-Waste Food Rescue Storefront", "Dedicated clearance marketplace for fresh surplus homemade dishes sold at discounted prices to prevent food waste, complete with countdown timers."),
            ("Customer Profile & Address Manager", "Manages user details, contact phone, default delivery addresses (Home, Work, Other) with exact GPS coordinates and landmarks."),
            ("Reviews & Ratings Modal", "Allows customers to rate delivered dishes and delivery partners (1 to 5 stars) with written feedback commentary and food quality tags."),
            ("AI Recommendation Panel", "Presents personalized meal recommendations generated by Kravyo’s recommendation engine based on user ordering patterns and taste preferences."),
            ("Queue-Aware Tiffin Live Tracker", "Renders subscriber\'s stop position in the delivery corridor (e.g. Stop 3 of 5), active leg indicator, rider arrival ETA, secure 4-digit handover OTP, and empty container return status.")
        ]),
        ("3.4.2 Home Chef Interface Screens", [
            ("Chef Registration & Profile Setup", "Guided registration form capturing chef personal details, kitchen branding story, cooking location address, and preparation capacity."),
            ("FSSAI & Hygiene Verification Desk", "Allows chefs to upload their 14-digit FSSAI license certificate and kitchen hygiene compliance photos for administrative verification."),
            ("Seller Command Dashboard", "Displays real-time operational metrics (Today’s Orders, Daily Revenue, Average Rating), incoming order alerts, and an instant \"Kitchen Open / Closed\" toggle."),
            ("Live Order Management Kanban", "Displays incoming orders with alert prompts, allowing chefs to Accept, Reject (triggering automated instant refund), and mark dishes \"Ready for Pickup\"."),
            ("Menu & Dish Builder", "Interface to add and edit menu items, configure categories, upload food images, set pricing, preparation time, dietary tags (Veg/Jain), and define portion limits."),
            ("Inventory & Portion Availability", "Real-time stock toggle allowing home chefs to mark items as \"In Stock\" or \"Sold Out\", with automatic item disabling when daily portions reach zero."),
            ("Tiffin Subscription Roster", "Displays active tiffin subscribers, daily meal preparation rosters (Lunch/Dinner delivery counts), special dietary requests, and scheduled delivery dates."),
            ("Zero-Waste Surplus Manager", "Enables chefs to list surplus prepared meals at discounted prices with expiration deadlines, recovering preparation costs and eliminating food waste."),
            ("Customer Feedback & Reviews Hub", "Detailed breakdown of customer star ratings, sentiment reviews, and quality feedback to assist chefs in improving culinary offerings."),
            ("Financials & Earnings Analytics", "Summarizes gross sales, 10% platform commission deductions, net vendor payouts, and provides downloadable periodic financial statements in CSV format.")
        ]),
        ("3.4.3 Delivery Partner Interface Screens", [
            ("Partner Onboarding & Verification", "Rider registration capturing personal contact information, vehicle type (Bike / Scooter / EV), driving license document upload, and account approval status."),
            ("Rider Duty Dashboard", "Features a prominent \"Go Online / Offline\" shift duty toggle, current battery/location indicator, and active delivery mission cards."),
            ("Exclusive Cascade Dispatch Alert", "An interactive modal that displays incoming delivery offers with a 25-second countdown timer, showing pickup kitchen name, drop distance, and trip earnings."),
            ("Pickup & Turn-by-Turn Navigation", "Google Maps turn-by-turn routing guiding the rider to the home chef’s kitchen, with one-tap contact buttons and a \"Collect Order\" confirmation action."),
            ("Delivery Transit & GPS Streamer", "Background service streaming live latitude and longitude coordinates to the customer while rendering the delivery transit route to the drop location."),
            ("Customer Drop & OTP Handover", "Prompts the courier to request and verify the customer's confidential 4-digit delivery OTP to prevent false delivery claims before completing the trip."),
            ("Rider Wallet & Trip History", "Shows completed delivery trips, accumulated delivery fee earnings, incentive bonuses, and historical delivery logs."),
            ("Tiffin Batch Runs Browser", "Dedicated dashboard view for couriers to inspect available multi-tiffin milk-run batches grouped by city and meal window, total containers, aggregate payout (₹100–₹350), and claim runs."),
            ("Step-by-Step Waypoint Route Manifest", "Displays the ordered sequence of kitchen pickups and customer drop-offs with turn-by-turn navigation, customer call buttons, instant OTP verification modal, and dabba return toggle.")
        ]),
        ("3.4.4 Administrator Command Console Screens", [
            ("Master Executive Dashboard", "High-level KPI summary cards displaying Total Platform GMV, Active Customers, Verified Home Kitchens, Live Dispatched Orders, and System Health."),
            ("User & Account Governance", "Searchable directory of all platform accounts (Customers, Chefs, Delivery Partners) with account status controls (Activate, Deactivate, Suspend)."),
            ("Kitchen Audit & Approval Desk", "Detailed verification workbench to inspect submitted home chef applications, audit 14-digit FSSAI licenses, grant Verified Kitchen trust badges, or record rejection reasons."),
            ("Live Dispatch & Order Monitor", "Centralized board monitoring live order progress across all kitchens and drivers, displaying cascade dispatch stages and delivery completion milestones."),
            ("Dispute Resolution & Refund Desk", "Grievance resolution console where admins inspect customer defect evidence, order timelines, and chef logs to approve automated refunds or dismiss claims."),
            ("Category & Food Taxonomy Manager", "Administration tool to create, edit, reorder, and activate food categories and cuisine tags across the platform."),
            ("Financial & Commission Analytics", "Date-filtered financial console reporting Gross Merchandise Value (GMV), Kravyo 10% commission revenues, net home chef payouts, and delivery partner fee balances."),
            ("Recommendation Engine Monitor", "Status monitor displaying AI recommendation system health, user preference vector refreshes, and recommendation accuracy metrics.")
        ])
    ]
    for p_title, s_list in gui_portals:
        add_sub_heading(doc, p_title)
        tbl_gui = doc.add_table(rows=len(s_list)+1, cols=2)
        tbl_gui.rows[0].cells[0].paragraphs[0].text = "Screen Name"
        tbl_gui.rows[0].cells[1].paragraphs[0].text = "Functional Purpose & Key Interactive Elements"
        for i, (sname, spurp) in enumerate(s_list, 1):
            tbl_gui.rows[i].cells[0].paragraphs[0].text = sname
            tbl_gui.rows[i].cells[1].paragraphs[0].text = spurp
        apply_table_styles(tbl_gui, [Inches(2.2), Inches(4.07)])
        
    add_main_heading(doc, "3.5 Traversals [in Hierarchical Format]")
    add_para(doc, "The Traversal Diagram represents the hierarchical movement of users through the Kravyo platform. It illustrates how each authenticated user role navigates from the public homepage into role-isolated workspaces, executes core domain workflows, and returns to their respective dashboard hubs.")
    add_image_placeholder(doc, "Figure 13: Traversal Hierarchical Navigation Diagram across 4 Portals", figure_id="3.14", hint="File Reference: diagrams/traversal_hierarchical_diagram.png")
    add_para(doc, "The traversal architecture is structured across four primary hierarchical tiers:")
    add_bullet(doc, "Root tier hosting the public homepage, marketing discovery feed, and multi-tab authentication gateway.", "1. Level 0 (Public Gateway): ")
    add_bullet(doc, "Four dedicated, session-guarded dashboard portals (Customer Portal, Home Chef Portal, Delivery Partner Portal, and Super Admin Console).", "2. Level 1 (Role Portals): ")
    add_bullet(doc, "Specific feature modules such as Order Processing, Menu Catalog Builder, 4-Stage Cascade Dispatch, and Tiffin Subscription Manager.", "3. Level 2 (Operational Modules): ")
    add_bullet(doc, "Fine-grained interactive views, dialog modals, turn-by-turn navigation screens, and dispute resolution workbenches.", "4. Level 3 (Interactive Execution): ")
    
    add_main_heading(doc, "3.6 System Development Model")
    add_para(doc, "The Agile Scrum Methodology was selected for the end-to-end engineering, iterative deployment, and ongoing enhancement of the Kravyo Cloud Kitchen Platform. Agile divides development into time-boxed iterations called sprints (each lasting 1 to 2 weeks), encompassing requirement analysis, architectural design, coding, integration testing, and review.")
    add_sub_heading(doc, "Sprint-Wise Development Breakdown for Kravyo:")
    add_bullet(doc, "Database schema setup; session isolation across four role cookies (KRAVYO_CUSTOMER, KRAVYO_CHEF, KRAVYO_DELIVERY, KRAVYO_ADMIN); registration with 6-digit email OTP verification and Bcrypt password hashing.", "Sprint 1 — Multi-Role Authentication & Onboarding: ")
    add_bullet(doc, "Kitchen profile builder; 14-digit FSSAI license certificate upload and verification workflow; category taxonomy; and dynamic menu item manager with portion caps and dietary tags.", "Sprint 2 — Culinary Catalog & Geolocation Engine: ")
    add_bullet(doc, "Customer food search and filter explorer; Google Maps integration with 15 km kitchen delivery radius validation; shopping cart; and checkout supporting Cash on Delivery (COD), Dynamic NPCI UPI QR, and Online Gateway.", "Sprint 3 — Real-Time Cart, Checkout & Multi-Channel Payments: ")
    add_bullet(doc, "4-stage automated cascade dispatch engine (DispatchService.php); delivery partner duty management; turn-by-turn navigation; Pusher WebSockets real-time GPS coordinate streaming; and 4-digit customer handover OTP verification.", "Sprint 4 — 4-Stage Smart Cascade Dispatch & Live Rider WebSockets: ")
    add_bullet(doc, "Recurring weekly/monthly tiffin subscription engine; daily meal schedule generator (subscription_deliveries); and time-sensitive surplus food clearance module with countdown timers.", "Sprint 5 — Recurring Tiffin Subscriptions & Zero-Waste Deal Engine: ")
    add_bullet(doc, "Centralized command console; GMV and platform commission analytics; automated Razorpay REST API instant refund processing; dispute grievance desk; and end-to-end regression testing.", "Sprint 6 — Administrative Governance, Analytics & Instant Refunds: ")
    
    add_sub_heading(doc, "Reasons for Selecting the Agile Model:")
    reasons = [
        ("Flexible Requirements Management: ", "Modular features such as AI dish recommendations, custom meal notes, weekly/monthly meal subscriptions, and zero-waste dynamic pricing were incrementally added and refined without refactoring foundational code."),
        ("Continuous & Automated Testing: ", "Each module (authentication, checkout, GPS dispatch, payment verification) was tested immediately upon sprint completion, eliminating end-of-project integration bottlenecks."),
        ("Early Error Detection & Mitigation: ", "Logical flaws, edge cases in dispatch cascades, or session collision risks were identified and rectified within the active sprint."),
        ("Regular Guide & Stakeholder Feedback: ", "Weekly demonstrations to project guides allowed direct incorporation of recommendations into subsequent iterations."),
        ("Role-Isolated Architecture: ", "Development of the 4 independent operational portals proceeded in parallel sprints without codebase conflicts."),
        ("Superior Software Quality & Stability: ", "Continuous integration, strict MVC separation, normalized database schemas, and iterative code refactoring guaranteed enterprise-grade platform performance.")
    ]
    for r_pre, r_text in reasons:
        add_bullet(doc, r_text, r_pre)
        
    add_main_heading(doc, "3.7 Database Tables (Data Dictionary - 22 Tables)")
    add_para(doc, "The backend database for the Kravyo Cloud Kitchen Platform (kravyo_db) is designed using MariaDB / MySQL 8.0 under the InnoDB Storage Engine. It consists of 22 normalized relational tables enforcing primary keys, foreign key constraints (ON DELETE CASCADE / ON DELETE SET NULL), unique indexes, multi-tiffin batch run relationships, and audit timestamps.")
    
    # Tables 1 to 22 detailed schema definitions
    tables_data = [
        ("1. Customers Table (`customers`)", "Stores the authentication credentials, contact profile, security tokens, and account status of registered customers accessing the Kravyo customer web application.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("full_name", "VARCHAR", "50", "Not Null"),
            ("email", "VARCHAR", "50", "Unique, Not Null"),
            ("phone", "CHAR", "10", "Unique, Not Null"),
            ("city", "VARCHAR", "100", "Nullable"),
            ("password_hash", "VARCHAR", "60", "Not Null (Bcrypt Hash)"),
            ("role", "ENUM", "—", "customer, chef, admin, delivery (Default: 'customer', Not Null)"),
            ("status", "ENUM", "—", "active, inactive, suspended, pending_verification (Default: 'active', Not Null)"),
            ("email_verified", "TINYINT", "1", "Default: 0, Not Null"),
            ("email_otp_hash", "VARCHAR", "255", "Nullable"),
            ("email_otp_expires_at", "DATETIME", "—", "Nullable"),
            ("email_otp_resent_at", "DATETIME", "—", "Nullable"),
            ("email_otp_attempts", "TINYINT", "1", "Default: 0, Not Null"),
            ("reset_otp_hash", "VARCHAR", "60", "Nullable"),
            ("reset_otp_expires_at", "DATETIME", "—", "Nullable"),
            ("reset_otp_resent_at", "DATETIME", "—", "Nullable"),
            ("reset_otp_attempts", "TINYINT UNSIGNED", "3", "Default: 0, Not Null"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null"),
            ("updated_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, Not Null")
        ]),
        ("2. Chefs Table (`chefs`)", "Stores the login credentials, contact numbers, and security tokens for home chefs who manage cloud kitchens on the Kravyo Chef Portal (/chef).", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("full_name", "VARCHAR", "50", "Not Null"),
            ("email", "VARCHAR", "50", "Unique, Not Null"),
            ("phone", "CHAR", "10", "Unique, Not Null"),
            ("city", "VARCHAR", "100", "Nullable"),
            ("password_hash", "VARCHAR", "60", "Not Null (Bcrypt Hash)"),
            ("role", "ENUM", "—", "customer, chef, admin, delivery (Default: 'customer', Not Null)"),
            ("status", "ENUM", "—", "active, inactive, suspended, pending_verification (Default: 'active', Not Null)"),
            ("email_verified", "TINYINT", "1", "Default: 0, Not Null"),
            ("email_otp_hash", "VARCHAR", "255", "Nullable"),
            ("email_otp_expires_at", "DATETIME", "—", "Nullable"),
            ("email_otp_resent_at", "DATETIME", "—", "Nullable"),
            ("email_otp_attempts", "TINYINT", "1", "Default: 0, Not Null"),
            ("reset_otp_hash", "VARCHAR", "60", "Nullable"),
            ("reset_otp_expires_at", "DATETIME", "—", "Nullable"),
            ("reset_otp_resent_at", "DATETIME", "—", "Nullable"),
            ("reset_otp_attempts", "TINYINT UNSIGNED", "3", "Default: 0, Not Null"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null"),
            ("updated_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, Not Null")
        ]),
        ("3. Delivery Users Table (`delivery_users`)", "Stores authentication credentials, contact numbers, and verification flags for delivery riders logging into the Kravyo Delivery Partner Portal (/delivery).", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("full_name", "VARCHAR", "50", "Not Null"),
            ("email", "VARCHAR", "50", "Unique, Not Null"),
            ("phone", "CHAR", "10", "Unique, Not Null"),
            ("city", "VARCHAR", "100", "Nullable"),
            ("password_hash", "VARCHAR", "60", "Not Null (Bcrypt Hash)"),
            ("role", "ENUM", "—", "customer, chef, admin, delivery (Default: 'customer', Not Null)"),
            ("status", "ENUM", "—", "active, inactive, suspended, pending_verification (Default: 'active', Not Null)"),
            ("email_verified", "TINYINT", "1", "Default: 0, Not Null"),
            ("email_otp_hash", "VARCHAR", "255", "Nullable"),
            ("email_otp_expires_at", "DATETIME", "—", "Nullable"),
            ("email_otp_resent_at", "DATETIME", "—", "Nullable"),
            ("email_otp_attempts", "TINYINT", "1", "Default: 0, Not Null"),
            ("reset_otp_hash", "VARCHAR", "60", "Nullable"),
            ("reset_otp_expires_at", "DATETIME", "—", "Nullable"),
            ("reset_otp_resent_at", "DATETIME", "—", "Nullable"),
            ("reset_otp_attempts", "TINYINT UNSIGNED", "3", "Default: 0, Not Null"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null"),
            ("updated_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, Not Null")
        ]),
        ("4. Admins Table (`admins`)", "Stores administrative account credentials and audit timestamps for platform administrators managing the Kravyo Admin Console (/admin).", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("full_name", "VARCHAR", "50", "Not Null"),
            ("email", "VARCHAR", "50", "Unique, Not Null"),
            ("phone", "CHAR", "10", "Unique, Not Null"),
            ("password_hash", "VARCHAR", "60", "Not Null (Bcrypt Hash)"),
            ("status", "ENUM", "—", "active, inactive, suspended, pending_verification (Default: 'active', Not Null)"),
            ("email_verified", "TINYINT", "1", "Default: 0, Not Null"),
            ("email_otp_hash", "VARCHAR", "255", "Nullable"),
            ("email_otp_expires_at", "DATETIME", "—", "Nullable"),
            ("email_otp_resent_at", "DATETIME", "—", "Nullable"),
            ("email_otp_attempts", "TINYINT", "1", "Default: 0, Not Null"),
            ("reset_otp_hash", "VARCHAR", "60", "Nullable"),
            ("reset_otp_expires_at", "DATETIME", "—", "Nullable"),
            ("reset_otp_resent_at", "DATETIME", "—", "Nullable"),
            ("reset_otp_attempts", "TINYINT UNSIGNED", "3", "Default: 0, Not Null"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null"),
            ("updated_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, Not Null"),
            ("last_login_at", "TIMESTAMP", "—", "Nullable")
        ]),
        ("5. Pending Registrations Table (`pending_registrations`)", "Temporarily stages unverified user accounts during self-registration until the 6-digit email OTP is validated. Records are automatically transferred to role tables and purged upon validation.", [
            ("email", "VARCHAR", "50", "Primary Key, Not Null"),
            ("full_name", "VARCHAR", "50", "Not Null"),
            ("phone", "CHAR", "10", "Not Null"),
            ("city", "VARCHAR", "100", "Nullable"),
            ("password_hash", "VARCHAR", "60", "Not Null (Bcrypt Hash)"),
            ("role", "ENUM", "—", "customer, chef, admin (Default: 'customer', Not Null)"),
            ("email_otp_hash", "VARCHAR", "60", "Nullable"),
            ("email_otp_expires_at", "DATETIME", "—", "Nullable"),
            ("email_otp_resent_at", "DATETIME", "—", "Nullable"),
            ("email_otp_attempts", "TINYINT UNSIGNED", "3", "Default: 0, Not Null"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null")
        ]),
        ("6. Kitchens Table (`kitchens`)", "Stores cloud kitchen profiles, physical addresses, geographic coordinates (latitude/longitude), FSSAI license numbers, hygiene badges, and operational status.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("user_id", "INT", "11", "Foreign Key referencing chefs.id (ON DELETE CASCADE), Not Null"),
            ("kitchen_name", "VARCHAR", "50", "Not Null"),
            ("personal_story", "TEXT", "—", "Nullable"),
            ("address", "TEXT", "—", "Not Null"),
            ("city", "VARCHAR", "100", "Not Null"),
            ("pincode", "CHAR", "6", "Not Null"),
            ("latitude", "DECIMAL", "10,8", "Nullable (Indexed for Haversine Geo-distance)"),
            ("longitude", "DECIMAL", "11,8", "Nullable (Indexed for Haversine Geo-distance)"),
            ("fssai_license", "VARCHAR", "20", "Nullable"),
            ("hygiene_badge", "ENUM", "—", "none, verified (Default: 'none', Nullable)"),
            ("hygiene_certificate_image", "VARCHAR", "255", "Nullable"),
            ("approval_status", "ENUM", "—", "pending, approved, rejected, suspended (Default: 'pending', Nullable)"),
            ("admin_notes", "TEXT", "—", "Nullable"),
            ("is_open", "TINYINT", "1", "Default: 1 (Open), Nullable"),
            ("banner_image", "VARCHAR", "255", "Nullable"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null"),
            ("updated_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, Not Null")
        ]),
        ("7. Categories Table (`categories`)", "Stores food classifications (e.g., Gujarati Thali, Punjabi, South Indian, Healthy Bowls, Desserts) used for catalog browsing and search filtering.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("category_name", "VARCHAR", "50", "Unique, Not Null"),
            ("description", "VARCHAR", "255", "Nullable"),
            ("image", "VARCHAR", "255", "Nullable"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null")
        ]),
        ("8. Menu Items Table (`menu_items`)", "Stores home-cooked food items offered by cloud kitchens, including unit price, dietary indicators (Veg, Jain, Diabetic-Friendly), and real-time inventory availability.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("kitchen_id", "INT", "11", "Foreign Key referencing kitchens.id (ON DELETE CASCADE), Not Null"),
            ("category_id", "INT", "11", "Foreign Key referencing categories.id (ON DELETE CASCADE), Not Null"),
            ("item_name", "VARCHAR", "50", "Not Null"),
            ("description", "TEXT", "—", "Nullable"),
            ("price", "DECIMAL", "6,2", "Not Null"),
            ("image", "VARCHAR", "255", "Nullable"),
            ("is_veg", "TINYINT", "1", "Default: 1 (Veg), Nullable"),
            ("is_jain_available", "TINYINT", "1", "Default: 0, Nullable"),
            ("is_diabetic_friendly", "TINYINT", "1", "Default: 0, Nullable"),
            ("is_available", "TINYINT", "1", "Default: 1 (In Stock), Nullable"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null"),
            ("updated_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, Not Null")
        ]),
        ("9. Zero Waste Items Table (`zero_waste_items`)", "Stores surplus prepared meals listed by kitchens at discounted prices near closing hours to minimize food waste and optimize chef cost recovery.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("menu_item_id", "INT", "11", "Foreign Key referencing menu_items.id (ON DELETE CASCADE), Not Null"),
            ("kitchen_id", "INT", "11", "Foreign Key referencing kitchens.id (ON DELETE CASCADE), Not Null"),
            ("original_price", "DECIMAL", "6,2", "Not Null"),
            ("discounted_price", "DECIMAL", "6,2", "Not Null"),
            ("quantity_available", "SMALLINT UNSIGNED", "5", "Not Null"),
            ("expiry_time", "DATETIME", "—", "Not Null"),
            ("status", "ENUM", "—", "active, sold_out, expired (Default: 'active', Nullable)"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null")
        ]),
        ("10. Tiffin Subscriptions Table (`tiffin_subscriptions`)", "Stores weekly and monthly meal subscription packages configured by home kitchens, detailing meals per day, schedule delivery windows, and subscription pricing.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("kitchen_id", "INT", "11", "Foreign Key referencing kitchens.id (ON DELETE CASCADE), Not Null"),
            ("plan_name", "VARCHAR", "50", "Not Null"),
            ("plan_type", "ENUM", "—", "weekly, monthly (Not Null)"),
            ("price", "DECIMAL", "8,2", "Not Null"),
            ("description", "TEXT", "—", "Nullable"),
            ("meals_per_day", "TINYINT UNSIGNED", "3", "Not Null"),
            ("breakfast_time", "VARCHAR", "50", "Default: '08:00 AM - 09:00 AM', Nullable"),
            ("lunch_time", "VARCHAR", "50", "Default: '12:30 PM - 01:30 PM', Nullable"),
            ("dinner_time", "VARCHAR", "50", "Default: '07:30 PM - 08:30 PM', Nullable"),
            ("is_active", "TINYINT", "1", "Default: 1 (Active), Nullable"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null")
        ]),
        ("11. Customer Subscriptions Table (`customer_subscriptions`)", "Stores active, paused, and completed recurring meal plan subscriptions purchased by customers, linking delivery locations, dates, and payment settlement.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("customer_id", "INT", "11", "Foreign Key referencing customers.id (ON DELETE CASCADE), Not Null"),
            ("subscription_plan_id", "INT", "11", "Foreign Key referencing tiffin_subscriptions.id, Not Null"),
            ("kitchen_id", "INT", "11", "Foreign Key referencing kitchens.id, Not Null"),
            ("address_id", "INT", "11", "Foreign Key referencing addresses.id, Not Null"),
            ("start_date", "DATE", "—", "Not Null"),
            ("end_date", "DATE", "—", "Not Null"),
            ("status", "ENUM", "—", "pending, active, paused, cancelled, completed (Default: 'pending', Nullable)"),
            ("payment_method", "ENUM", "—", "cod, upi, card, netbanking, online (Default: 'cod', Nullable)"),
            ("payment_status", "ENUM", "—", "pending, completed, failed, refunded (Default: 'pending', Nullable)"),
            ("total_paid", "DECIMAL", "8,2", "Not Null"),
            ("special_instructions", "TEXT", "—", "Nullable"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null"),
            ("updated_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, Not Null")
        ]),
        ("12. Subscription Deliveries Table (`subscription_deliveries`)", "Tracks individual daily fulfillment slots (breakfast, lunch, or dinner) generated for active customer subscriptions, linked to milk-run delivery batches with sequential waypoint numbering.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("customer_subscription_id", "INT", "11", "Foreign Key referencing customer_subscriptions.id (ON DELETE CASCADE), Not Null"),
            ("kitchen_id", "INT", "11", "Foreign Key referencing kitchens.id (ON DELETE CASCADE), Not Null"),
            ("run_id", "INT", "11", "Foreign Key referencing delivery_runs.id (ON DELETE SET NULL), Nullable"),
            ("stop_number", "INT", "11", "Waypoint stop sequence index within assigned delivery run, Nullable"),
            ("delivery_date", "DATE", "—", "Not Null"),
            ("meal_type", "ENUM", "—", "breakfast, lunch, dinner (Default: 'lunch', Not Null)"),
            ("delivery_time", "VARCHAR", "50", "Nullable"),
            ("status", "ENUM", "—", "pending, dispatched, delivered, cancelled (Default: 'pending', Not Null)"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null"),
            ("updated_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, Not Null")
        ]),
        ("13. Addresses Table (`addresses`)", "Stores customer delivery locations including street address, apartment details, postal pincodes, and latitude/longitude coordinates for delivery radius verification.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("user_id", "INT", "11", "Foreign Key referencing customers.id (ON DELETE CASCADE), Not Null"),
            ("address_type", "ENUM", "—", "Home, Work, Other (Default: 'Home', Nullable)"),
            ("apartment", "VARCHAR", "150", "Nullable"),
            ("street_address", "TEXT", "—", "Not Null"),
            ("landmark", "VARCHAR", "150", "Nullable"),
            ("city", "VARCHAR", "100", "Not Null"),
            ("pincode", "CHAR", "6", "Not Null"),
            ("latitude", "DECIMAL", "10,8", "Nullable"),
            ("longitude", "DECIMAL", "11,8", "Nullable"),
            ("is_deleted", "TINYINT", "1", "Default: 0 (Active), Not Null (Soft-Delete)"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null")
        ]),
        ("14. Delivery Partners Table (`delivery_partners`)", "Stores delivery rider operational profiles, vehicle details, driving license numbers, real-time GPS locations, online duty status, total earnings, and admin approval status.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("user_id", "INT", "11", "Foreign Key referencing delivery_users.id (ON DELETE CASCADE), Unique, Not Null"),
            ("vehicle_type", "ENUM", "—", "bicycle, scooter, motorcycle, car (Default: 'scooter', Not Null)"),
            ("vehicle_number", "VARCHAR", "20", "Not Null"),
            ("city", "VARCHAR", "60", "Default: 'Surat', Not Null"),
            ("driving_license_number", "VARCHAR", "30", "Not Null"),
            ("verification_status", "ENUM", "—", "pending, approved, rejected, suspended (Default: 'pending', Not Null)"),
            ("is_online", "TINYINT", "1", "Default: 0 (Offline), Not Null"),
            ("is_busy", "TINYINT", "1", "Default: 0 (Available), Not Null"),
            ("last_seen_at", "DATETIME", "—", "Nullable"),
            ("current_latitude", "DECIMAL", "10,8", "Nullable"),
            ("current_longitude", "DECIMAL", "11,8", "Nullable"),
            ("location_updated_at", "TIMESTAMP", "—", "Nullable"),
            ("total_deliveries", "INT", "11", "Default: 0, Not Null"),
            ("total_earnings", "DECIMAL", "10,2", "Default: 0.00, Not Null"),
            ("admin_notes", "TEXT", "—", "Nullable"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null"),
            ("updated_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, Not Null")
        ]),
        ("15. Orders Table (`orders`)", "Core transaction table storing food orders, payment verification status, Razorpay payment reference, delivery address, dispatch cascade round, assigned rider, and 4-digit handover OTP.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("order_number", "VARCHAR", "30", "Unique, Not Null (e.g. KRV-20261016-C84DE)"),
            ("order_type", "ENUM", "—", "standard, tiffin (Default: 'standard', Not Null)"),
            ("subscription_delivery_id", "INT", "11", "Nullable"),
            ("customer_id", "INT", "11", "Foreign Key referencing customers.id (ON DELETE CASCADE), Not Null"),
            ("kitchen_id", "INT", "11", "Foreign Key referencing kitchens.id, Not Null"),
            ("address_id", "INT", "11", "Foreign Key referencing addresses.id, Not Null"),
            ("delivery_latitude", "DECIMAL", "10,8", "Nullable"),
            ("delivery_longitude", "DECIMAL", "11,8", "Nullable"),
            ("total_amount", "DECIMAL", "8,2", "Not Null"),
            ("payment_method", "ENUM", "—", "cod, upi, card, netbanking, online (Default: 'cod', Nullable)"),
            ("payment_status", "ENUM", "—", "pending, completed, failed, refunded (Default: 'pending', Nullable)"),
            ("razorpay_payment_id", "VARCHAR", "50", "Nullable"),
            ("order_status", "ENUM", "—", "pending, accepted, preparing, ready_for_pickup, out_for_delivery, delivered, cancelled, delivery_failed (Default: 'pending', Nullable)"),
            ("delivery_partner_id", "INT", "11", "Foreign Key referencing delivery_partners.id, Nullable"),
            ("offered_driver_id", "INT", "11", "Nullable"),
            ("offer_expires_at", "DATETIME", "—", "Nullable"),
            ("rejected_by_drivers", "LONGTEXT", "—", "Nullable (JSON array of driver IDs)"),
            ("delivery_otp", "CHAR", "4", "Nullable (4-Digit Handover Verification OTP)"),
            ("delivery_status", "ENUM", "—", "unassigned, assigned, accepted, picked_up, out_for_delivery, delivered, failed (Default: 'unassigned', Nullable)"),
            ("delivery_accepted_at", "DATETIME", "—", "Nullable"),
            ("delivery_picked_up_at", "DATETIME", "—", "Nullable"),
            ("delivery_completed_at", "DATETIME", "—", "Nullable"),
            ("delivery_failed_reason", "VARCHAR", "255", "Nullable"),
            ("special_instructions", "VARCHAR", "500", "Nullable"),
            ("dispatch_status", "ENUM", "—", "none, cascading, broadcasting, assigned, no_rider, manual (Default: 'none', Not Null)"),
            ("dispatch_started_at", "DATETIME", "—", "Nullable"),
            ("dispatch_round", "TINYINT", "4", "Default: 0, Not Null"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null"),
            ("updated_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, Not Null")
        ]),
        ("16. Order Items Table (`order_items`)", "Stores individual food dish line items belonging to an order, capturing customized cooking preferences (spice level, oil level, Jain preparation, cooking notes) and unit prices.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("order_id", "INT", "11", "Foreign Key referencing orders.id (ON DELETE CASCADE), Not Null"),
            ("menu_item_id", "INT", "11", "Foreign Key referencing menu_items.id, Not Null"),
            ("quantity", "SMALLINT UNSIGNED", "5", "Not Null"),
            ("unit_price", "DECIMAL", "6,2", "Not Null"),
            ("subtotal", "DECIMAL", "8,2", "Not Null"),
            ("spice_level", "ENUM", "—", "Low, Medium, High (Default: 'Medium', Nullable)"),
            ("oil_level", "ENUM", "—", "Normal, Less Oil (Default: 'Normal', Nullable)"),
            ("is_jain", "TINYINT", "1", "Default: 0, Nullable"),
            ("item_notes", "TEXT", "—", "Nullable"),
            ("is_zero_waste", "TINYINT", "1", "Default: 0, Not Null"),
            ("zero_waste_id", "INT UNSIGNED", "10", "Nullable")
        ]),
        ("17. Delivery Offers Table (`delivery_offers`)", "Manages the automated 4-stage smart cascade dispatch cycle, recording individual offer dispatches, expiration timestamps, and rider responses.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("order_id", "INT", "11", "Foreign Key referencing orders.id, Not Null"),
            ("delivery_partner_id", "INT", "11", "Foreign Key referencing delivery_partners.id, Not Null"),
            ("round", "TINYINT", "4", "Default: 1, Not Null"),
            ("offer_type", "ENUM", "—", "cascade, broadcast, batch (Default: 'cascade', Not Null)"),
            ("status", "ENUM", "—", "pending, accepted, declined, lost, expired, cancelled (Default: 'pending', Not Null)"),
            ("sent_at", "DATETIME", "—", "Default: CURRENT_TIMESTAMP, Not Null"),
            ("seen_at", "DATETIME", "—", "Nullable"),
            ("responded_at", "DATETIME", "—", "Nullable")
        ]),
        ("18. Reviews Table (`reviews`)", "Stores customer star ratings (1 to 5) and textual culinary feedback submitted for delivered orders and cloud kitchens. Enforces a strict one-review-per-order rule.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("order_id", "INT", "11", "Foreign Key referencing orders.id, Unique, Not Null"),
            ("customer_id", "INT", "11", "Foreign Key referencing customers.id (ON DELETE CASCADE), Not Null"),
            ("kitchen_id", "INT", "11", "Foreign Key referencing kitchens.id, Not Null"),
            ("rating", "TINYINT UNSIGNED", "3", "Not Null (Value between 1 and 5)"),
            ("review_text", "VARCHAR", "500", "Not Null"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null")
        ]),
        ("19. Customer Reports Table (`customer_reports`)", "Stores issue tickets, quality complaints, hygiene concerns, and delivery dispute reports filed by customers, facilitating administrative auditing and automated refund processing.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("customer_id", "INT", "11", "Foreign Key referencing customers.id (ON DELETE CASCADE), Not Null"),
            ("kitchen_id", "INT", "11", "Foreign Key referencing kitchens.id (ON DELETE CASCADE), Not Null"),
            ("order_id", "INT", "11", "Foreign Key referencing orders.id (ON DELETE SET NULL), Nullable"),
            ("target_type", "ENUM", "—", "kitchen, dish, delivery (Not Null)"),
            ("target_id", "INT", "11", "Nullable"),
            ("reason", "VARCHAR", "100", "Not Null"),
            ("description", "TEXT", "—", "Not Null"),
            ("status", "ENUM", "—", "pending, reviewed, resolved (Default: 'pending', Nullable)"),
            ("admin_notes", "TEXT", "—", "Nullable"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null"),
            ("updated_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, Not Null"),
            ("resolved_at", "TIMESTAMP", "—", "Nullable")
        ]),
        ("20. Notifications Table (`notifications`)", "Stores multi-role in-app alerts and status notifications (e.g., order accepted, out for delivery, refund credited, dispatch alerts) across all 4 user roles.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("user_id", "INT", "11", "Nullable (NULL indicates system-wide broadcast)"),
            ("user_type", "ENUM", "—", "customer, chef, delivery, admin (Not Null)"),
            ("title", "VARCHAR", "100", "Not Null"),
            ("message", "VARCHAR", "500", "Not Null"),
            ("type", "ENUM", "—", "order_update, promotion, system_alert, kitchen_update (Default: 'system_alert', Not Null)"),
            ("is_read", "TINYINT", "1", "Default: 0 (Unread), Not Null"),
            ("link", "VARCHAR", "255", "Nullable (Target redirect route)"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null")
        ]),
        ("21. Delivery Runs Table (`delivery_runs`)", "Consolidates recurring tiffin subscription orders into optimized multi-stop milk-run delivery batches for assigned delivery partners, tracking aggregate payouts and batch execution timestamps.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("run_code", "VARCHAR", "30", "Unique batch reference code (e.g., #TR-20261027-01-72A6), Not Null"),
            ("meal_type", "ENUM", "—", "breakfast, lunch, dinner (Default: 'lunch', Not Null)"),
            ("delivery_date", "DATE", "—", "Scheduled delivery run date, Not Null"),
            ("city", "VARCHAR", "50", "Delivery cluster geographic municipality, Not Null"),
            ("delivery_partner_id", "INT", "11", "Foreign Key referencing delivery_partners.id, Nullable"),
            ("status", "ENUM", "—", "draft, assigned, in_progress, completed (Default: 'draft', Nullable)"),
            ("total_orders", "INT", "11", "Total tiffin meal containers consolidated in batch (Default: 0, Nullable)"),
            ("total_payout", "DECIMAL", "10,2", "Guaranteed aggregate rider earnings for completing run (Default: 0.00, Nullable)"),
            ("started_at", "DATETIME", "—", "Timestamp when rider departed on milk run, Nullable"),
            ("completed_at", "DATETIME", "—", "Timestamp when final waypoint was fulfilled, Nullable"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null"),
            ("updated_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, Not Null")
        ]),
        ("22. Delivery Run Stops Table (`delivery_run_stops`)", "Defines ordered waypoint navigation stops (kitchen pickups followed by consumer drops) along an active delivery run, enforcing OTP verification and empty dabba container exchange.", [
            ("id", "INT", "11", "Primary Key, Auto Increment, Not Null"),
            ("run_id", "INT", "11", "Foreign Key referencing delivery_runs.id (ON DELETE CASCADE), Not Null"),
            ("stop_sequence", "INT", "11", "Sequential 1-indexed waypoint order along milk-run route, Not Null"),
            ("stop_type", "ENUM", "—", "pickup, dropoff (Not Null)"),
            ("subscription_delivery_id", "INT", "11", "Foreign Key referencing subscription_deliveries.id (ON DELETE SET NULL), Nullable"),
            ("target_id", "INT", "11", "Entity ID of the kitchen chef or customer recipient, Nullable"),
            ("target_name", "VARCHAR", "150", "Display name of kitchen vendor or receiving customer, Not Null"),
            ("target_address", "TEXT", "—", "Physical street address and landmarks for navigation, Not Null"),
            ("contact_phone", "VARCHAR", "20", "Direct phone number for rider-customer communication, Nullable"),
            ("latitude", "DECIMAL", "10,8", "Precise GPS latitude coordinate for turn-by-turn routing, Nullable"),
            ("longitude", "DECIMAL", "11,8", "Precise GPS longitude coordinate for turn-by-turn routing, Nullable"),
            ("items_count", "INT", "11", "Count of meal containers loaded or unloaded at waypoint (Default: 1, Nullable)"),
            ("special_instructions", "TEXT", "—", "Delivery gate codes, dietary notes, or drop instructions, Nullable"),
            ("status", "ENUM", "—", "pending, arrived, completed, skipped (Default: 'pending', Nullable)"),
            ("delivery_otp", "VARCHAR", "6", "Confidential 4-to-6 digit delivery verification OTP, Nullable"),
            ("container_returned", "TINYINT", "1", "Flag verifying return of clean empty stainless-steel dabba (Default: 0, Not Null)"),
            ("completed_at", "DATETIME", "—", "Timestamp when waypoint was verified and marked done, Nullable"),
            ("created_at", "TIMESTAMP", "—", "Default: CURRENT_TIMESTAMP, Not Null")
        ])
    ]
    
    for t_title, t_purp, cols in tables_data:
        add_sub_heading(doc, t_title)
        add_para(doc, t_purp, "Purpose: ")
        tbl = doc.add_table(rows=len(cols)+1, cols=4)
        tbl.rows[0].cells[0].paragraphs[0].text = "Attribute"
        tbl.rows[0].cells[1].paragraphs[0].text = "Data Type"
        tbl.rows[0].cells[2].paragraphs[0].text = "Size"
        tbl.rows[0].cells[3].paragraphs[0].text = "Constraints"
        for i, (attr, dtype, size, constr) in enumerate(cols, 1):
            tbl.rows[i].cells[0].paragraphs[0].text = attr
            tbl.rows[i].cells[1].paragraphs[0].text = dtype
            tbl.rows[i].cells[2].paragraphs[0].text = size
            tbl.rows[i].cells[3].paragraphs[0].text = constr
        apply_table_styles(tbl, [Inches(1.8), Inches(1.1), Inches(0.8), Inches(2.57)])
        
    add_main_heading(doc, "Database Normalization Study (Normal Form of the Database Schema)")
    add_para(doc, "Database normalization is systematically applied to the Kravyo database schema to eliminate data redundancy, enforce referential integrity, and prevent insertion, update, and deletion anomalies.")
    
    add_sub_heading(doc, "1. First Normal Form (1NF)")
    add_para(doc, "A relation is in 1NF if every column contains only atomic (indivisible) values, and there are no repeating groups or multivalued attributes.")
    add_bullet(doc, "In early unnormalized designs, an order might store multiple food items in a single text field (e.g., \"Gujarati Thali x 2, Dal Baati x 1\"). In Kravyo, order_items decomposes orders into atomic rows containing exactly one menu_item_id, integer quantity, unit_price, and atomic preference flags.", "Kravyo 1NF: ")
    add_bullet(doc, "Customer addresses are decomposed into atomic attributes: apartment, street_address, landmark, city, and pincode rather than a single unstructured text string.", "Address Atomicity: ")
    add_bullet(doc, "Every table possesses an unambiguous Primary Key (e.g., customers.id, orders.id, kitchens.id).", "Primary Key Integrity: ")
    
    add_sub_heading(doc, "2. Second Normal Form (2NF)")
    add_para(doc, "A relation is in 2NF if it is in 1NF and every non-prime attribute is fully functionally dependent on the entire Primary Key (no partial dependency on composite candidate keys).")
    add_bullet(doc, "In composite entities like order_items, the candidate key is (order_id, menu_item_id). Dish details reside strictly in menu_items and kitchen details reside in kitchens. order_items stores only attributes dependent on the specific line item transaction (quantity, unit_price, subtotal, spice_level).", "Kravyo 2NF: ")
    add_bullet(doc, "In subscription_deliveries, composite uniqueness (customer_subscription_id, delivery_date, meal_type) maps only to the slot's delivery status and time, while plan specifications are referenced via the parent foreign key.", "Subscription 2NF: ")
    
    add_sub_heading(doc, "3. Third Normal Form (3NF) & BCNF")
    add_para(doc, "A relation is in 3NF if it is in 2NF and no non-prime attribute is transitively dependent on the Primary Key (no X -> Y -> Z dependencies where Y and Z are non-key attributes).")
    add_bullet(doc, "In orders, the Primary Key is id. If orders stored customer_phone or kitchen_city, a transitive dependency would occur: orders.id -> orders.customer_id -> customers.phone. In Kravyo, orders stores only foreign key references: customer_id, kitchen_id, and address_id.", "Kravyo 3NF: ")
    add_bullet(doc, "Delivery rider profile data (vehicle_type, vehicle_number, driving_license_number) is isolated in delivery_partners, which connects to delivery_users.id, preventing duplicate rider credentials across dispatch logs.", "Rider Profile 3NF: ")
    add_para(doc, "Therefore, the Kravyo schema satisfies 3NF and Boyce-Codd Normal Form (BCNF), guaranteeing optimal transactional performance, zero redundancy, and referential integrity.")
    
    add_main_heading(doc, "3.8 Summary of Sequence")
    add_para(doc, "This sequence establishes the complete architectural, operational, and database design foundation for the Kravyo Cloud Kitchen Platform:")
    add_bullet(doc, "The Agile Scrum Methodology is adopted across 6 dedicated sprints, providing an iterative development cycle that accommodates emerging features while ensuring continuous testing and guide feedback.", "1. System Development Model: ")
    add_bullet(doc, "The backend relational model is organized into 22 fully normalized MariaDB/MySQL tables under the InnoDB storage engine spanning Authentication (5 tables), Kitchen Catalog (5 tables), Subscriptions & Batch Runs (5 tables), Order Logistics (4 tables), and Governance (3 tables).", "2. Comprehensive Database Schema: ")
    add_bullet(doc, "Strict adherence to 1NF, 2NF, and 3NF standards guarantees atomic attributes, eliminates partial and transitive dependencies, and safeguards the platform against data anomalies during concurrent operations.", "3. Database Normalization: ")
    
    doc.add_page_break()
    print("Chapter 3 completed. Building Chapter 4...")
    
    # CHAPTER 4: SEQUENCE 6
    add_chapter_title(doc, "Chapter 4: Refinement in User Interface Design (Sequence No. 6)")
    add_main_heading(doc, "4.1 Screenshot of Homepage & Navigation Controls")
    add_para(doc, "The Kravyo Discovery Homepage is the central public and authenticated landing portal. It establishes brand trust, presents active culinary categories, highlights nearby kitchens within a 15 km radius, displays live zero-waste flash deals, and provides single-click access to recurring tiffin subscriptions.")
    
    add_image_placeholder(doc, "Customer Discovery Homepage & Navigation System", figure_id="4.1", hint="File Reference: views/customer/home.php")
    
    add_sub_heading(doc, "Used Navigation Controls with Justification:")
    nav_controls = [
        ("Persistent Top Navigation Bar: ", "Maintains continuous visibility across all pages, hosting brand logo, portal links (Explore Kitchens, Browse Dishes, Tiffins, Zero-Waste), search input, active cart counter badge, and user profile avatar dropdown."),
        ("Multi-Parameter Live Search & Category Slider: ", "Enables instant client-side filtering by cuisine (Gujarati, Punjabi, South Indian) and dish name without requiring full-page reloads."),
        ("Dynamic Cart Counter Badge: ", "Displays real-time portion counts stored in the session cart, providing immediate visual feedback whenever dishes are added."),
        ("Hero Action Call-to-Action (CTA) Buttons: ", "High-contrast buttons ('Explore Local Kitchens', 'Join as Home Chef') guiding user journeys based on their intent."),
        ("Geolocation Range Indicator: ", "Displays user's detected or selected locality with a distance radius indicator ensuring kitchens shown are within delivery range.")
    ]
    for nc_pre, nc_text in nav_controls:
        add_bullet(doc, nc_text, nc_pre)
        
    add_main_heading(doc, "4.2 Detailed Screen Specifications Across All 4 User Roles")
    add_para(doc, "Below are the complete interface specifications, data flow, controls, database bindings, and validation rules for all 19 core screens across the four user roles:")
    
    # 19 SCREENS SPECIFICATIONS
    screens_spec = [
        # CUSTOMER SCREENS
        ("4.2.1.1 Customer Registration & Email Verification Screen", "4.2", "views/auth/register.php",
         "Allows new consumers to create a customer account on Kravyo by entering their profile details, initiating a 6-digit email OTP dispatch, and verifying their account before accessing the platform.",
         [
             ("Text Input Boxes (full_name, email, phone, city): ", "Captures customer personal details with instant HTML5 client-side format constraints."),
             ("Password Input Field with Eye Toggle: ", "Masks sensitive passwords while allowing users to inspect their input to prevent registration errors."),
             ("Submit Button ('Create Customer Account'): ", "Triggers client validation, initiates Bcrypt password hashing, and dispatches the OTP email."),
             ("6-Digit OTP Block Input: ", "Segmented input boxes ensuring the customer inputs exactly 6 numeric characters for email confirmation."),
             ("Timer Countdown & Resend Link: ", "Displays a 60-second cooldown timer before allowing an OTP resend to prevent email server throttling.")
         ],
         "`pending_registrations`, `customers`",
         "User-typed profile inputs (name, email, 10-digit mobile number, password, city).",
         "Staged into `pending_registrations` during OTP dispatch; transferred into `customers` upon successful verification; session initialized.",
         "Full name must be between 3 and 50 characters, alphabetic with spaces. Email must match RFC 5322 regex syntax and be unique. Mobile phone must be exactly 10 numeric digits. Password must be at least 8 characters. OTP must match the stored SHA-256 hash before 10-minute expiry; max 3 incorrect attempts."
        ),
        ("4.2.1.2 Customer Login & Session Authentication Screen", "4.3", "views/auth/login.php",
         "Authenticates returning customers into the Kravyo Customer Web Portal and initializes isolated cookie-based user sessions (`KRAVYO_CUSTOMER`).",
         [
             ("Email Input Field: ", "Standard input with automatic whitespace trimming."),
             ("Password Input Field: ", "Masked field to prevent shoulder-surfing."),
             ("Checkbox ('Remember Me'): ", "Extends session cookie lifetime up to 30 days via encrypted token."),
             ("Hyperlink ('Forgot Password?'): ", "Directs users to the password reset flow using an OTP sent to their email."),
             ("Login Action Button: ", "Submits credentials via secure POST with CSRF token protection.")
         ],
         "`customers`",
         "Customer email and plaintext password from user input.",
         "Verified against `customers.password_hash` using `password_verify()`. On success, updates `customers.last_login_at` and populates `$_SESSION['user_id']`.",
         "Email and password fields cannot be blank. Account status must be 'active'. Rate-limited to 5 consecutive failed attempts per IP address."
        ),
        ("4.2.1.3 Customer Homepage After Login (Discovery & Feed)", "4.4", "views/customer/home.php",
         "Serves as the central discovery screen post-authentication, presenting curated cuisine categories, nearby verified home kitchens, quick-access zero-waste deals, tiffin meal subscriptions, and AI-driven dish recommendations.",
         [
             ("Navigation Bar with Live Cart Counter Badge: ", "Persistently shows links and real-time cart item quantity."),
             ("Search Input Bar with Autocomplete: ", "Enables instant search across kitchen names, categories, and dish keywords."),
             ("Hero Action Buttons: ", "High-contrast CTA buttons guiding users to core sections."),
             ("Category Slider / Grid Cards: ", "Circular category icons allowing single-click filtering."),
             ("Kitchen Cards with Hygiene Badge & Star Rating: ", "Visual card components displaying kitchen cover image, FSSAI verification badge, average star score, and delivery radius.")
         ],
         "`kitchens`, `categories`, `menu_items`, `zero_waste_items`, `tiffin_subscriptions`, `reviews`, `customers`",
         "Approved kitchens (`approval_status = 'approved'`), active menu items, discount deals, and active customer session.",
         "Directs customer to kitchen profiles, dish details, category pages, cart, or subscription plans.",
         "View-only discovery screen. Displays only active dishes (`is_available = 1`) and open kitchens (`is_open = 1`)."
        ),
        ("4.2.1.4 Kitchen Profile & Dish Customization Screen", "4.5", "views/customer/kitchen_detail.php",
         "Displays a cloud kitchen's complete culinary profile along with their menu catalog, allowing customers to specify cooking preferences (spice level, oil level, Jain preparation, cooking notes) and add dishes to the cart.",
         [
             ("Kitchen Banner & Header Card: ", "Displays cover photo, chef name, hygiene badge, and delivery distance."),
             ("Dietary Filter Toggles ('Veg Only', 'Jain Available', 'Diabetic Friendly'): ", "Instant filter pills that hide non-compliant dishes."),
             ("Dish Item Cards: ", "Shows dish image, price (₹), description, and Add to Cart button."),
             ("Customization Modal Popup: ", "Radio groups for Spice Level (Low, Medium, High), Oil Level (Normal, Less Oil), and checkbox for Jain Preparation."),
             ("Item Notes Textarea: ", "Allows up to 250 characters of custom cooking instructions."),
             ("Quantity Stepper (+ / -): ", "Enables incrementing or decrementing portion counts.")
         ],
         "`kitchens`, `menu_items`, `categories`, `reviews`, `chefs`",
         "`kitchens.id` via URL parameter, joined with `menu_items` and calculated average ratings from `reviews`.",
         "Stored in customer session cart array: `$_SESSION['cart'][kitchen_id][item_id] = {qty, price, spice, oil, jain, notes}`.",
         "Single-kitchen cart rule enforced with modal confirmation. Quantity must be a positive integer between 1 and 20 per item. Special instructions sanitized with htmlspecialchars()."
        ),
        ("4.2.1.5 Cart & Multi-Channel Checkout Screen", "4.6", "views/customer/checkout.php",
         "Allows customers to review selected food items, choose or add a delivery address, verify delivery distance is within 15 km, select a payment method, and complete the order.",
         [
             ("Order Item Review Table: ", "Summarizes selected dishes, quantities, customization pills, unit prices, and subtotals."),
             ("Address Selector Radio Cards: ", "Displays saved addresses with a button to Add New Address."),
             ("15 km Radius Alert Badge: ", "Dynamically validates GPS distance between kitchen and selected address via Haversine calculation."),
             ("Payment Method Radio Group: ", "Three options: 1) Cash on Delivery, 2) UPI Dynamic QR, 3) Razorpay Online Gateway."),
             ("Delivery Notes Text Field: ", "Captures gate codes, landmark directions, or drop-off preferences."),
             ("Pay & Place Order Button: ", "Computes final bill (Items + Delivery Fee + GST) and triggers checkout processing.")
         ],
         "`orders`, `order_items`, `addresses`, `kitchens`, `menu_items`, `customers`",
         "Cart session state, `addresses` table for customer profile, and kitchen location coordinates.",
         "Creates a record in `orders` and associated items in `order_items`; empties the cart session; redirects to payment or tracking screen.",
         "Cart cannot be empty. Address must have valid coordinates and fall within 15 km of the kitchen. Stock availability re-verified before creation."
        ),
        ("4.2.1.6 Live Order Tracking & 4-Digit Handover OTP Screen", "4.7", "views/customer/order_track.php",
         "Provides real-time visibility into order status, renders live delivery partner locations using Pusher WebSockets and Google Maps, and displays the secure 4-digit handover OTP.",
         [
             ("Horizontal 5-Stage Stepper Progress Bar: ", "Visually tracks preparation and delivery stages with status icons."),
             ("Prominent Handover OTP Card: ", "Highlights the 4-digit verification code (`orders.delivery_otp`) required by rider."),
             ("Interactive Google Maps View: ", "Displays markers for kitchen, rider, and customer locations, updating in real time via WebSockets."),
             ("Rider Information Card: ", "Shows assigned rider's name, vehicle details, average rating, and direct Call Partner action button."),
             ("Report an Issue Button: ", "Opens customer dispute modal to file missing item or delay complaints.")
         ],
         "`orders`, `kitchens`, `delivery_partners`, `delivery_users`, `addresses`",
         "`orders` record joined with `kitchens`, `delivery_partners`, and live GPS coordinates from `delivery_partners`.",
         "Updates customer view in real time; triggers Pusher event listener `order-{id}`.",
         "Accessible only by the order's owner (`orders.customer_id == $_SESSION['user_id']`). OTP is read-only for the customer."
        ),
        ("4.2.1.7 Tiffin Subscription Booking & Schedule Screen", "4.8", "views/customer/subscriptions.php",
         "Allows customers to browse, configure, and subscribe to recurring home-cooked weekly and monthly meal plans, selecting daily meal windows (Breakfast, Lunch, Dinner) and delivery schedules.",
         [
             ("Plan Duration Tabs ('Weekly Plan (7 Days)', 'Monthly Plan (30 Days)'): ", "Filters subscription pricing tiers."),
             ("Meals Per Day Selector Pills ('1 Meal / Day', '2 Meals / Day', '3 Meals / Day'): ", "Adjusts daily delivery slots and pricing totals."),
             ("Delivery Window Badge Labels: ", "Displays designated delivery time slots (Breakfast: 8:00–9:00 AM, Lunch: 12:30–1:30 PM, Dinner: 7:30–8:30 PM)."),
             ("Start Date Picker Input: ", "Disallows past dates and allows selection of subscription start date."),
             ("Subscribe Now Action Button: ", "Initiates payment and recurring schedule generation.")
         ],
         "`tiffin_subscriptions`, `customer_subscriptions`, `subscription_deliveries`, `kitchens`",
         "Active tiffin plans (`is_active = 1`) offered by kitchens.",
         "Inserts record into `customer_subscriptions` and bulk-generates daily delivery records in `subscription_deliveries`.",
         "Start date must be at least 1 day in advance. Full payment or COD authorization required before subscription status switches to 'active'."
        ),
        ("4.2.1.8 Zero-Waste Surplus Food Deals Screen", "4.9", "views/customer/zero_waste.php",
         "Features discounted surplus dishes published by kitchens near closing hours, preventing food waste while providing cost-effective meals to consumers.",
         [
             ("Savings Banner & Environmental Impact Metric: ", "Displays cumulative food waste prevented (in kg) across the platform."),
             ("Countdown Expiry Timer Component: ", "Shows real-time remaining minutes before the deal expires."),
             ("Dual Price Display Label: ", "Highlights slashed original price next to discounted deal price (e.g. 50% OFF)."),
             ("Portions Remaining Progress Bar: ", "Shows real-time stock levels (e.g. 'Only 3 portions left!')."),
             ("Claim Deal Button: ", "Directly adds the discounted item to the cart.")
         ],
         "`zero_waste_items`, `menu_items`, `kitchens`",
         "Active zero-waste listings (`zero_waste_items.status = 'active'` and `expiry_time > NOW()`).",
         "Ingests item into cart session with `is_zero_waste = 1` and `unit_price = discounted_price`.",
         "Expired or out-of-stock items (`quantity_available = 0`) are automatically disabled and marked with a 'Sold Out' badge."
        ),
        
        # CHEF SCREENS
        ("4.2.2.1 Home Chef Registration & FSSAI Verification Screen", "4.10", "views/chef/profile.php",
         "Onboards home chefs onto the platform by collecting kitchen details, personal culinary stories, physical kitchen addresses, geolocation, and FSSAI compliance documentation for administrative review.",
         [
             ("Form Text Inputs (Kitchen Name, City, Pincode): ", "Standard profile collection fields."),
             ("Personal Story Textarea: ", "Allows chefs to share their culinary heritage and family recipes."),
             ("FSSAI 14-Digit Registration Number Input: ", "Collects regulatory compliance identification numbers."),
             ("Document File Upload Picker: ", "Accepts .jpg, .png, or .pdf scans of chef's food safety certificate."),
             ("Interactive Map Pin Dropper: ", "Enables chef to pinpoint exact physical kitchen coordinates."),
             ("Submit Application Button: ", "Dispatches application to admin review queue.")
         ],
         "`chefs`, `kitchens`",
         "Chef user inputs, uploaded files, and browser geolocation coordinates.",
         "Inserts initial kitchen record into `kitchens` with `approval_status = 'pending'`, saves certificate files to `uploads/kitchens/`.",
         "FSSAI license must be numeric and up to 14 digits. Uploaded files must not exceed 5 MB. Coordinates must fall within valid geographic ranges."
        ),
        ("4.2.2.2 Chef Overview Dashboard & Real-Time Orders Console", "4.11", "views/chef/dashboard.php",
         "Serves as the primary operational workspace for home chefs, providing metrics on today's revenue, active pending orders, a kitchen open/closed duty toggle, and an audio-alerted live incoming order queue.",
         [
             ("Kitchen Status Toggle Switch ('Open for Orders / Closed'): ", "Updates `kitchens.is_open` in real time, pausing orders during breaks."),
             ("Key Metric Stat Cards: ", "Highlights Today's Revenue (₹), Active Orders, Completed Deliveries, and Average Rating."),
             ("Incoming Live Order Queue: ", "Cards displaying customer name, dish items, customization notes, and elapsed timer."),
             ("Order Action Buttons ('Accept Order', 'Reject Order', 'Mark Food Ready'): ", "Updates order preparation states and triggers dispatch workflows."),
             ("Audio Alert Toggle: ", "Sounds an audible chime (beep.wav) when new orders arrive via WebSockets.")
         ],
         "`orders`, `order_items`, `kitchens`, `customers`, `delivery_partners`",
         "Orders belonging to the chef's kitchen (`orders.kitchen_id = :id`) filtered by active order statuses.",
         "Updates `orders.order_status`. When marked `ready_for_pickup`, triggers `DispatchService` to initiate rider matching.",
         "Chefs can only modify orders assigned to their kitchen. State transitions follow strict workflow rules (cannot mark ready before accepted)."
        ),
        ("4.2.2.3 Menu Management & Dish Creator Screen", "4.12", "views/chef/menu.php",
         "Allows home chefs to manage their food catalog, add new dishes, upload appetizing photos, specify dietary preferences (Veg, Jain, Diabetic), set prices, and toggle in-stock availability.",
         [
             ("Add Dish Button & Slide-over Drawer: ", "Opens a form for creating new dishes without leaving the current catalog page."),
             ("Category Dropdown Select: ", "Populates categories from the master `categories` table."),
             ("Dietary Preference Checkboxes: ", "Checkboxes for Vegetarian, Jain Available, and Diabetic Friendly."),
             ("Price (₹) Numeric Input: ", "Specifies unit price in INR with two decimal precision."),
             ("Dish Photo File Upload with Live Preview: ", "Shows thumbnail preview of dish images before uploading."),
             ("In-Stock Availability Switch: ", "Toggles `menu_items.is_available` between 1 and 0 for instant sold-out handling.")
         ],
         "`menu_items`, `categories`, `kitchens`",
         "Chef form inputs and existing menu records from `menu_items`.",
         "Inserts or updates records in `menu_items`; saves uploaded photos to `uploads/dishes/`.",
         "Dish name is required, max 50 characters. Price must be positive (₹10.00 to ₹5,000.00). Images must be .jpg, .jpeg, or .png, max 3 MB."
        ),
        ("4.2.2.4 Chef Tiffin Subscription & Zero-Waste Deal Publisher", "4.13", "views/chef/subscriptions.php",
         "Enables chefs to create recurring meal subscription plans (Weekly/Monthly) and publish end-of-day surplus dishes at promotional discounts to minimize food waste.",
         [
             ("Segmented Navigation Tab: ", "Switches between Tiffin Meal Plans and Zero-Waste Surplus Deals."),
             ("Plan Duration Select Dropdown: ", "Configures package tier (Weekly or Monthly)."),
             ("Meals Per Day Input (1, 2, or 3): ", "Defines daily scheduled meal counts."),
             ("Surplus Dish Selector Dropdown: ", "Selects existing dishes to publish as zero-waste items."),
             ("Discounted Deal Price Input: ", "Defines discounted deal price (must be lower than original_price)."),
             ("Deal Cutoff Time Picker: ", "Sets the deal expiration time (`expiry_time`).")
         ],
         "`tiffin_subscriptions`, `zero_waste_items`, `menu_items`, `kitchens`",
         "Form inputs linked to `kitchens.id` and active items from `menu_items`.",
         "Inserts configured plans into `tiffin_subscriptions` or active deals into `zero_waste_items`.",
         "Discounted price must be strictly less than original price. Deal cutoff must be a future timestamp on the current day. Quantity must be > 0."
        ),
        
        # DELIVERY PARTNER SCREENS
        ("4.2.3.1 Delivery Partner Registration & Document Verification Screen", "4.14", "views/delivery/register.php",
         "Onboards delivery riders by collecting personal credentials, vehicle type, license plate numbers, driving license identification, and vehicle inspection documents for administrative verification.",
         [
             ("Vehicle Type Radio Buttons: ", "Selects transport mode (Bicycle, Scooter, Motorcycle, Electric Vehicle)."),
             ("License Plate Text Input: ", "Captures registration number (e.g., GJ-05-AB-1234)."),
             ("Driving License Input: ", "Required field for motorized vehicle drivers."),
             ("Operating City Dropdown: ", "Selects primary operating zone (e.g., Surat, Bardoli)."),
             ("Terms & Safety Agreement Checkbox: ", "Enforces regulatory agreement before submission.")
         ],
         "`delivery_users`, `delivery_partners`",
         "Rider registration inputs.",
         "Inserts authentication profile into `delivery_users` and operational vehicle profile into `delivery_partners` with `verification_status = 'pending'`.",
         "License plate required for motorized transport. Driving license number validated. Unique email and 10-digit mobile number required."
        ),
        ("4.2.3.2 Delivery Duty Toggle & Live Cascade Dispatch Offer Modal", "4.15", "views/delivery/dashboard.php",
         "Mobile-optimized interface for delivery riders displaying their shift duty toggle (Online/Offline), current earnings, and an urgent interactive cascade offer modal with a 30-second response countdown timer.",
         [
             ("Online Shift Duty Toggle Switch: ", "Toggles `is_online` between 1 and 0, signaling dispatch availability."),
             ("Today's Earnings Banner Card: ", "Shows completed trips count and daily earnings."),
             ("Cascade Offer Modal Dialog: ", "Appears via WebSockets when an order is dispatched to the rider."),
             ("30-Second Animated Expiry Countdown Bar: ", "Visual countdown timer before offer cascades to next nearest candidate."),
             ("Pickup & Delivery Distance Indicators: ", "Shows distance from rider to kitchen and kitchen to customer."),
             ("Accept Offer Button (Green) / Decline Button (Red): ", "Action buttons to claim the delivery or reject it.")
         ],
         "`delivery_partners`, `delivery_offers`, `orders`, `kitchens`, `addresses`",
         "Real-time dispatch ping via Pusher WebSockets and records in `delivery_offers`.",
         "On accept, sets `delivery_offers.status = 'accepted'`, marks `orders.delivery_partner_id = :rider_id`, and sets `delivery_partners.is_busy = 1`.",
         "Rider must be verified (`verification_status = 'approved'`) and marked `is_online = 1`. Claims rejected if timer expires."
        ),
        ("4.2.3.3 Active Delivery Navigation & 4-Digit Handover OTP Screen", "4.16", "views/delivery/order_detail.php",
         "Guides the rider through the active fulfillment journey: navigating to the kitchen, confirming food pickup, routing to the customer address, and verifying delivery using the customer's 4-digit handover OTP.",
         [
             ("Navigation Button ('Open in Google Maps'): ", "Deep-links to Google Maps navigation with pre-filled destination coordinates."),
             ("Order Details Card: ", "Shows order ID, kitchen packaging checklist, and customer delivery notes."),
             ("Confirm Food Pickup Button: ", "Updates order state to `picked_up` and notifies the customer."),
             ("4-Digit Customer OTP Input Field: ", "Input box for entering customer's handover code upon arrival."),
             ("Complete Handover Button: ", "Verifies the code and marks the trip completed."),
             ("Delivery Exception Link ('Unable to Deliver'): ", "Allows recording drop-off exceptions (e.g. customer unreachable).")
         ],
         "`orders`, `delivery_partners`, `kitchens`, `addresses`",
         "Active order record (`orders.id`) assigned to the rider.",
         "Matches user-entered OTP with `orders.delivery_otp`. On match, sets `order_status = 'delivered'`, updates `delivery_completed_at = NOW()`, increments rider earnings, and frees rider (`is_busy = 0`).",
         "Handover blocked if 4-digit OTP does not match `orders.delivery_otp`. Order status must be 'picked_up' before handover verification."
        ),
        
        # ADMINISTRATOR SCREENS
        ("4.2.4.1 Platform Master Operations Dashboard", "4.17", "views/admin/dashboard.php",
         "Central executive command screen for super administrators, providing high-level operational visibility into active platform orders, active kitchens, active delivery riders, system health metrics, and gross transaction volume.",
         [
             ("Analytical KPI Metric Cards: ", "Displays Gross Merchandise Value (GMV), Active Orders Count, Registered Kitchens, and Active Delivery Riders."),
             ("Live Operations Alert Strip: ", "Highlights pending chef FSSAI verifications and open customer quality reports."),
             ("Recent Platform Orders Data Table: ", "Filterable tabular feed of recent platform orders with inline status badges."),
             ("Revenue & Platform Commission Chart: ", "Visual timeline showing platform commission earnings (10–15%) and chef disbursements."),
             ("Quick Navigation Action Tiles: ", "Shortcut buttons to Category Management, Dispatch Board, and Refund Audit Center.")
         ],
         "`orders`, `kitchens`, `customers`, `delivery_partners`, `customer_reports`",
         "System-wide SQL aggregations across `orders`, `kitchens`, and `customers`.",
         "Navigation to dedicated management modules; real-time operational overview.",
         "Restricted to authenticated administrative sessions (`admins.role IN ('super_admin','moderator')`)."
        ),
        ("4.2.4.2 Chef FSSAI Verification & Hygiene Badge Audit Screen", "4.18", "views/admin/chefs.php",
         "Allows platform administrators to review pending kitchen onboarding applications, inspect FSSAI certificates, evaluate kitchen hygiene documentation, and approve or reject cloud kitchens.",
         [
             ("Verification Status Filter Tabs ('Pending', 'Approved', 'Rejected', 'Suspended'): ", "Categorizes kitchen applications."),
             ("Kitchen Profile & Application Card: ", "Summarizes chef contact details, address, and submitted FSSAI license number."),
             ("Certificate Document Lightbox Modal: ", "Enables full-screen zoom and inspection of uploaded FSSAI registration scans."),
             ("Hygiene Verification Dropdown: ", "Assigns a badge (none or verified) based on audit results."),
             ("Approve / Reject Action Buttons: ", "Approves the kitchen or marks it rejected."),
             ("Admin Review Notes Textarea: ", "Records mandatory feedback when rejecting an application.")
         ],
         "`kitchens`, `chefs`, `notifications`",
         "Onboarding submissions from `kitchens` and `chefs`.",
         "Updates `kitchens.approval_status`, `kitchens.hygiene_badge`, and `kitchens.admin_notes`; sends an in-app notification to the chef.",
         "Rejection requires non-empty `admin_notes` explaining compliance deficiencies. Pending/rejected kitchens remain hidden from discovery feeds."
        ),
        ("4.2.4.3 Live Dispatch Monitoring & Manual Rider Assignment Console", "4.19", "views/admin/dispatch_board.php",
         "Real-time dispatch management console allowing administrators to monitor automated 4-stage rider matching cascades, inspect unassigned orders, and manually assign delivery riders if automated cascades fail.",
         [
             ("Live Dispatch Stage Filters ('Cascading', 'Broadcasting', 'Unassigned', 'Assigned'): ", "Filters orders by current dispatch state."),
             ("Order Dispatch Card: ", "Displays preparation timestamp, preparing kitchen, customer destination, and cascade round."),
             ("Eligible Nearby Riders Dropdown: ", "Lists available online riders sorted by real-time GPS proximity."),
             ("Manual Override Assign Button: ", "Manually assigns a rider to an order, bypassing automated dispatch loops."),
             ("Broadcast Fallback Trigger Button: ", "Triggers a 5 km broadcast alert if single-rider pings expire.")
         ],
         "`orders`, `delivery_partners`, `delivery_offers`, `kitchens`, `addresses`",
         "Orders with `order_status = 'ready_for_pickup'`, joined with active riders (`is_online = 1`, `is_busy = 0`).",
         "Updates `orders.delivery_partner_id`, sets `orders.dispatch_status = 'manual'`, and pushes dispatch alerts via Pusher.",
         "Cannot assign an offline or busy delivery rider. Action logs admin user ID in platform audit logs."
        ),
        ("4.2.4.4 Customer Dispute Resolution & Razorpay Refund Audit Console", "4.20", "views/admin/reports_center.php",
         "Enables administrators to review customer dispute reports (quality concerns, missing dishes, delivery delays), inspect photographic evidence, communicate with involved kitchens, and execute automated Razorpay refunds.",
         [
             ("Dispute Ticket Data Table: ", "Displays filing date, customer name, reported kitchen, order reference, and issue category."),
             ("Ticket Detail Modal: ", "Shows the customer's statement, order line items, and uploaded evidence."),
             ("Refund Amount Numeric Input: ", "Pre-filled with `orders.total_amount` but editable for partial compensation refunds."),
             ("Execute Razorpay Refund Button: ", "Calls the Razorpay Payments API to initiate an automated refund."),
             ("Chef Quality Penalty Checkbox: ", "Records a formal infraction flag against the offending kitchen if quality standards were breached."),
             ("Dismiss Dispute Button: ", "Closes frivolous or invalid complaints with explanatory notes.")
         ],
         "`customer_reports`, `orders`, `customers`, `kitchens`, `notifications`",
         "Dispute filings from `customer_reports` joined with `orders` and `customers`.",
         "Updates `customer_reports.status = 'resolved'`, updates `orders.payment_status = 'refunded'`, and triggers customer notifications.",
         "Refund amount cannot exceed original `orders.total_amount`. Online refunds require valid `orders.razorpay_payment_id`. COD orders credit customer wallet."
        )
    ]
    
    for s_title, fig_num, f_hint, s_desc, ctrl_list, t_bind, d_in, d_out, d_val in screens_spec:
        add_sub_heading(doc, s_title)
        add_para(doc, s_desc, "Description / Purpose of the UI: ")
        add_image_placeholder(doc, s_title.split(' ', 1)[1] if ' ' in s_title else s_title, figure_id=fig_num, hint=f"File Reference: {f_hint}")
        add_sub_sub_heading(doc, "Used Controls with Justification:")
        for c_pre, c_text in ctrl_list:
            add_bullet(doc, c_text, c_pre)
        add_para(doc, t_bind, "Associated Tables: ")
        add_para(doc, d_in, "Data From (Data In): ")
        add_para(doc, d_out, "Data To (Data Out): ")
        add_para(doc, d_val, "Data Validation: ")
        doc.add_paragraph().paragraph_format.space_before = Pt(6)
        
    doc.add_page_break()
    print("Chapter 4 completed. Building Chapter 5: User-Wise Reports and Dashboards...")
    
    # =========================================================================
    # CHAPTER 5: IMPLEMENTATION OF USER-WISE REPORTS AND DASHBOARDS (CO4)
    # =========================================================================
    add_chapter_title(doc, "Chapter 5: Implementation of User-Wise Reports and Dashboards")
    add_para(doc, "In modern multi-tenant enterprise architectures, role-tailored dashboards and operational reporting systems serve as the core interaction layer that connects diverse user classes with transactional databases, external microservices, and decision-support engines. For Kravyo, four distinct user roles—Customer, Home Chef, Delivery Partner, and Platform Administrator—require specialized visual control hubs and data outputs to fulfill their functional obligations efficiently.")
    add_para(doc, "This chapter presents the architectural design, graphical interface views, data bindings, input/output specifications, and quantitative aggregations for Kravyo's user-wise dashboard views and Transaction Processing (TPS), Management Information (MIS), and Decision Support System (DSS) reports, adhering strictly to the structured academic specification standards.")

    add_main_heading(doc, "5.1 User-Wise Dashboard Views and Architectural Specifications")
    add_para(doc, "Each user role in Kravyo is provided with an isolated, secure, and responsive dashboard interface engineered to surface actionable real-time information while enforcing strict access boundaries via isolated cookie-based authentication contexts.")

    # 5.1.1 View-1: Customer Activity & Kitchen Discovery Feed Hub
    add_sub_heading(doc, "5.1.1 View-1: Customer Activity & Kitchen Discovery Feed Hub")
    add_para(doc, "The Customer Dashboard represents the primary discovery and transactional portal for hungry patrons. It features dynamic geofenced cloud kitchen carousels, dietary badge filters (Jain, Diabetic-Friendly, Low Oil, High Protein), real-time cart order progression, active tiffin subscription schedules, and zero-waste markdown countdowns.")
    add_image_placeholder(doc, "Customer Activity & Kitchen Discovery Feed Hub Dashboard", figure_id="5.1", hint="File Reference: diagrams/dashboard_customer.png")
    
    add_table_caption(doc, "View-1 Specification: Customer Activity & Kitchen Discovery Feed Hub", "5.1")
    view1_data = [
        ("View-1 Name", "Customer Activity & Kitchen Discovery Feed Hub"),
        ("Description", "Centralized, geofenced consumer discovery feed and meal management portal. Provides dynamic discovery of neighborhood home kitchens within 15 km, multi-filter dietary searches (Jain, Diabetic-Friendly, Vegan, Low-Oil), real-time cart tracking, active recurring tiffin meal calendar, zero-waste markdown flash sales, and live order status tracking with interactive Google Maps driver route navigation."),
        ("Data From", "Table: kitchens, menu_items, categories, customer_subscriptions, orders, zero_waste_items, addresses, reviews\nEnd User: GPS geolocation coordinates, search queries, dietary filter checkboxes, portion selection, cart modifications\nRemote Server: Google Maps Geocoding API, Scikit-learn AI Recommendation Microservice, Pusher WebSockets real-time driver coordinates"),
        ("Data To", "Username: Authenticated Customer (Session Cookie: KRAVYO_CUSTOMER) / Public Visitor session")
    ]
    tbl_v1 = doc.add_table(rows=len(view1_data), cols=2)
    for i, (k, v) in enumerate(view1_data):
        tbl_v1.rows[i].cells[0].paragraphs[0].text = k
        tbl_v1.rows[i].cells[1].paragraphs[0].text = v
    apply_table_styles(tbl_v1, [Inches(1.8), Inches(4.47)])

    # 5.1.2 View-2: Home Chef Kitchen Operations Command Desk
    add_sub_heading(doc, "5.1.2 View-2: Home Chef Kitchen Operations Command Desk")
    add_para(doc, "The Home Chef Operations Desk provides culinary artisans with an intuitive, real-time command terminal to manage daily kitchen production. It features an instant operational duty switch (Open/Closed), live incoming order alerts with continuous HTML5 Web Audio chimes, kitchen preparation stage progression, daily tiffin subscription batch rosters, menu item portion counters, and daily revenue statistics.")
    add_image_placeholder(doc, "Home Chef Kitchen Operations Command Desk", figure_id="5.2", hint="File Reference: diagrams/dashboard_chef.png")
    
    add_table_caption(doc, "View-2 Specification: Home Chef Kitchen Operations Command Desk", "5.2")
    view2_data = [
        ("View-2 Name", "Home Chef Kitchen Operations Command Desk"),
        ("Description", "Real-time kitchen operations and culinary management dashboard. Features a 1-click kitchen availability toggle (Open / Closed for Orders), live order intake monitor with audible chime alerts, preparation workflow pipeline (Pending -> Accepted -> Preparing -> Ready for Dispatch), recurring tiffin batch quota tracker, daily revenue analytics, menu inventory portion management, and zero-waste flash sale lister."),
        ("Data From", "Table: orders, order_items, menu_items, tiffin_subscriptions, subscription_deliveries, chefs, kitchens, reviews\nEnd User: Duty toggle switch, order accept/reject triggers, preparation ready button, portion quantity updates, zero-waste discount submission\nRemote Server: Pusher WebSockets incoming order event, HTML5 Web Audio API chime player"),
        ("Data To", "Username: Authenticated Home Chef (Session Cookie: KRAVYO_CHEF)")
    ]
    tbl_v2 = doc.add_table(rows=len(view2_data), cols=2)
    for i, (k, v) in enumerate(view2_data):
        tbl_v2.rows[i].cells[0].paragraphs[0].text = k
        tbl_v2.rows[i].cells[1].paragraphs[0].text = v
    apply_table_styles(tbl_v2, [Inches(1.8), Inches(4.47)])

    # 5.1.3 View-3: Delivery Partner Logistics Terminal & Shift Dashboard
    add_sub_heading(doc, "5.1.3 View-3: Delivery Partner Logistics Terminal & Shift Dashboard")
    add_para(doc, "The Delivery Partner Terminal is a mobile-optimized logistics interface designed for fleet drivers on two-wheelers. It provides quick shift duty toggling, 4-stage cascade dispatch offer cards with a live 25-second countdown timer, turn-by-turn routing navigation to kitchen and customer drop-off points, direct telephone dialing, and a secure 4-digit OTP delivery completion modal.")
    add_image_placeholder(doc, "Delivery Partner Logistics Terminal & Shift Dashboard", figure_id="5.3", hint="File Reference: diagrams/dashboard_delivery.png")
    
    add_table_caption(doc, "View-3 Specification: Delivery Partner Logistics Terminal & Shift Dashboard", "5.3")
    view3_data = [
        ("View-3 Name", "Delivery Partner Logistics Terminal & Shift Dashboard"),
        ("Description", "High-efficiency mobile courier dispatch terminal and shift management console. Equips delivery riders with active shift status toggles (Online / Offline), 4-stage cascade delivery request cards featuring a 25-second countdown timer, turn-by-turn Google Maps routing from home kitchen to customer doorstep, phone call shortcuts, live wallet balance, and secure 4-digit delivery handshake OTP verification."),
        ("Data From", "Table: delivery_offers, orders, addresses, kitchens, delivery_partners, delivery_users\nEnd User: Courier online/offline shift toggle, offer accept/reject button, device GPS geolocation ping, 4-digit customer delivery OTP entry\nRemote Server: Pusher WebSockets cascade dispatch ping, Google Maps Directions API, HTML5 Geolocation API stream"),
        ("Data To", "Username: Authenticated Delivery Partner (Session Cookie: KRAVYO_DELIVERY)")
    ]
    tbl_v3 = doc.add_table(rows=len(view3_data), cols=2)
    for i, (k, v) in enumerate(view3_data):
        tbl_v3.rows[i].cells[0].paragraphs[0].text = k
        tbl_v3.rows[i].cells[1].paragraphs[0].text = v
    apply_table_styles(tbl_v3, [Inches(1.8), Inches(4.47)])

    # 5.1.4 View-4: Master Administrator Platform Command Console
    add_sub_heading(doc, "5.1.4 View-4: Master Administrator Platform Command Console")
    add_para(doc, "The Master Administrator Command Console provides complete operational oversight, financial governance, and regulatory compliance control across the entire Kravyo ecosystem. It aggregates real-time Gross Merchandise Value (GMV), 10% platform commission earnings, pending home kitchen onboarding requests, FSSAI regulatory verification queues, customer dispute resolution, and automated Razorpay refund authorizations.")
    add_image_placeholder(doc, "Master Administrator Platform Command Console", figure_id="5.4", hint="File Reference: diagrams/dashboard_admin.png")
    
    add_table_caption(doc, "View-4 Specification: Master Administrator Platform Command Console", "5.4")
    view4_data = [
        ("View-4 Name", "Master Administrator Platform Command Console"),
        ("Description", "Comprehensive enterprise administration, regulatory compliance, and business intelligence console. Delivers platform-wide real-time telemetry including Gross Merchandise Value (GMV), net commission earnings (10%), active delivery fleet metrics, home chef onboarding queue with 14-digit FSSAI certificate verification, customer grievance resolution desk, and automated Razorpay refund dispatches."),
        ("Data From", "Table: admins, chefs, kitchens, orders, customer_reports, delivery_partners, pending_registrations, reviews\nEnd User: Administrator approval/rejection commands, FSSAI document verification status, refund approval action, commission rate configuration\nRemote Server: Razorpay REST API (refund execution and status checks), MariaDB query telemetry, server resource monitor"),
        ("Data To", "Username: Authenticated Master Administrator (Session Cookie: KRAVYO_ADMIN)")
    ]
    tbl_v4 = doc.add_table(rows=len(view4_data), cols=2)
    for i, (k, v) in enumerate(view4_data):
        tbl_v4.rows[i].cells[0].paragraphs[0].text = k
        tbl_v4.rows[i].cells[1].paragraphs[0].text = v
    apply_table_styles(tbl_v4, [Inches(1.8), Inches(4.47)])

    add_main_heading(doc, "5.2 User-Wise TPS / MIS / DSS Reports")
    add_para(doc, "Enterprise information systems categorize reporting structures into three fundamental tiers: Transaction Processing Systems (TPS) for day-to-day operational transactions, Management Information Systems (MIS) for periodic operational aggregation and batch scheduling, and Decision Support Systems (DSS) for analytical planning, resource allocation, and financial auditing.")
    add_para(doc, "In Kravyo, each user tier interacts with structured reports tailored to their operational responsibilities. Below are the detailed specifications for representative reports across each classification level:")

    # 5.2.1 Report-1 (TPS)
    add_sub_heading(doc, "5.2.1 Report-1 (TPS): Customer Printable Tax Invoice & Delivery Bill")
    add_para(doc, "Transaction Processing System (TPS) reports record and validate discrete, high-volume transactions executed on the platform. Report-1 represents the customer's official tax invoice and delivery bill, generated immediately upon order placement and finalized upon successful OTP delivery verification.")
    add_image_placeholder(doc, "Customer Printable Tax Invoice & Delivery Bill Receipt", figure_id="5.5", hint="File Reference: diagrams/report_customer_invoice.png")
    
    add_table_caption(doc, "Report-1 Specification (TPS): Customer Printable Tax Invoice & Delivery Bill", "5.5")
    rep1_data = [
        ("Report-1 Description", "Filtered Operational TPS Report (Filtered by Order ID and Authenticated Customer ID). Provides an itemized legal tax receipt, culinary breakdown, and delivery verification voucher for an individual food delivery or subscription order."),
        ("Input", "Order ID (`orders.id`), Customer ID (`customers.id`), Delivery Address ID (`addresses.id`), Payment Transaction Reference (`orders.razorpay_payment_id` or 'COD')."),
        ("Process", "The system executes an inner join query across `orders`, `order_items`, `menu_items`, `kitchens`, and `addresses`. It computes the itemized subtotal, calculates 5% GST (`orders.tax_amount`), adds platform handling fees and distance-based delivery charges, applies active promo code deductions, confirms Razorpay HMAC signature or COD status, and generates a dynamic 4-digit delivery verification OTP. Outputs print-optimized thermal and standard HTML/PDF layout."),
        ("Output", "Official printable Tax Invoice displaying order tracking ID, kitchen FSSAI registration number, timestamp, itemized dish list with dietary flags (Jain, Diabetic-Friendly), subtotal, GST breakdown, net amount paid, masked delivery address, and prominent 4-digit delivery handshake OTP."),
        ("Usage for whom and how?", "Must be in well description:\n1. For Customer: Serves as legal proof of transaction, dietary consumption audit, payment tax receipt for expense claims, and physical proof of order during the courier delivery OTP handshake.\n2. For Delivery Partner: Functions as the physical delivery voucher to confirm drop-off address, recipient phone number, gate access codes, and item count verification.\n3. For Home Chef: Serves as the official preparation and packing slip attached to tiffin boxes to ensure correct portion packaging, customized spice/oil adherence, and allergen segregation.")
    ]
    tbl_r1 = doc.add_table(rows=len(rep1_data), cols=2)
    for i, (k, v) in enumerate(rep1_data):
        tbl_r1.rows[i].cells[0].paragraphs[0].text = k
        tbl_r1.rows[i].cells[1].paragraphs[0].text = v
    apply_table_styles(tbl_r1, [Inches(1.8), Inches(4.47)])

    # 5.2.2 Report-2 (MIS)
    add_sub_heading(doc, "5.2.2 Report-2 (MIS): Daily Kitchen Batch Preparation & Tiffin Dispatch Roster")
    add_para(doc, "Management Information System (MIS) reports aggregate operational transaction data over defined operational windows to guide supervisory decision-making and operational execution. Report-2 provides home chefs with an aggregated daily production roster of all scheduled recurring tiffin meals and instant orders for a given service slot.")
    add_image_placeholder(doc, "Daily Kitchen Batch Preparation & Tiffin Dispatch Roster Report", figure_id="5.6", hint="File Reference: diagrams/report_chef_roster.png")
    
    add_table_caption(doc, "Report-2 Specification (MIS): Daily Kitchen Batch Preparation & Tiffin Dispatch Roster", "5.6")
    rep2_data = [
        ("Report-2 Description", "General / Filtered Operational MIS Report (Aggregated by Kitchen ID, Target Service Date, and Meal Slot: Lunch/Dinner). Provides home chefs with a consolidated production schedule and dispatch checklist for scheduled meal batches."),
        ("Input", "Kitchen ID (`kitchens.id`), Scheduled Delivery Date (`CURDATE()`), Service Meal Slot (`lunch` [12:00-14:00] or `dinner` [19:00-21:00]), Subscription Status (`active`)."),
        ("Process", "The system queries `subscription_deliveries` joined with `customer_subscriptions`, `tiffin_subscriptions`, `menu_items`, and `orders`. It filters for records matching the target date and meal window, aggregates total portion counts required for each standardized meal package, collates custom dietary notes (e.g. 'No Garlic/Onion', 'Less Oil for Diabetic Patient'), and sorts deliveries by geographic cluster to streamline batch courier dispatch."),
        ("Output", "Formatted Daily Kitchen Batch Roster displaying aggregate portions to cook (e.g., 24 Standard Gujarati Thalis, 10 Jain Khichdi-Kadhi Combos, 6 Diabetic Roti-Sabzi Packs), cooking checklist, raw material requirements, packaging labels, assigned courier names, and dispatch cutoff times."),
        ("Usage for whom and how?", "Must be in well description:\n1. For Home Chef: Consulted at 06:00 AM (for Lunch) and 03:00 PM (for Dinner) to accurately measure raw ingredients, organize bulk cooking batches, manage commercial flame capacity, and pack tiffins into thermal insulation containers before courier pickup.\n2. For Kitchen Assistants: Acts as a physical assembly-line checklist pinned to prep tables to ensure accurate container sealing, dietary sticker labeling, and zero packaging errors.\n3. For Delivery Dispatcher: Facilitates pre-routing batch assignments, grouping deliveries by geographical zones to optimize multi-drop courier trips.")
    ]
    tbl_r2 = doc.add_table(rows=len(rep2_data), cols=2)
    for i, (k, v) in enumerate(rep2_data):
        tbl_r2.rows[i].cells[0].paragraphs[0].text = k
        tbl_r2.rows[i].cells[1].paragraphs[0].text = v
    apply_table_styles(tbl_r2, [Inches(1.8), Inches(4.47)])

    # 5.2.3 Report-3 (DSS)
    add_sub_heading(doc, "5.2.3 Report-3 (DSS): Platform Monthly GMV, Commission & Financial Settlement Audit")
    add_para(doc, "Decision Support System (DSS) reports perform multidimensional financial, operational, and predictive analytics across extensive historical datasets to guide strategic administrative policies. Report-3 delivers platform administrators a comprehensive audit of Gross Merchandise Value (GMV), 10% platform commission yields, vendor net disbursements, delivery courier fees, and dispute refund trends.")
    add_image_placeholder(doc, "Platform Monthly GMV, Commission Revenue & Financial Settlement Audit", figure_id="5.7", hint="File Reference: diagrams/report_admin_gmv.png")
    
    add_table_caption(doc, "Report-3 Specification (DSS): Platform Monthly GMV, Commission & Financial Settlement Audit", "5.7")
    rep3_data = [
        ("Report-3 Description", "Filtered Analytical DSS Report (Aggregated by Fiscal Month/Quarter, Geolocation Region, Vendor Tier, and Settlement Status). Delivers strategic financial intelligence, margin audits, and executive forecasting metrics for platform governance."),
        ("Input", "Fiscal Date Range (`start_date` to `end_date`), Settlement State Filter (`settled`, `pending`, `disputed`), Kitchen Identification, Platform Commission Rate (10.0%), Payment Gateway Fee (2.0%)."),
        ("Process", "The DSS engine runs multi-table analytical aggregations joining `orders`, `order_items`, `kitchens`, `delivery_partners`, `admins`, and `customer_reports`. It calculates total Gross Merchandise Value (GMV), partitions net platform commission revenues, deducts Razorpay processing fees, totals courier transit earnings, computes vendor net payables, isolates refunded order values, evaluates kitchen cancellation quartiles, and projects month-over-month platform growth trajectories."),
        ("Output", "Executive Financial Settlement Audit Report featuring interactive GMV visual trend charts, revenue breakdown matrices, vendor payout disbursement rosters, dispute loss ratios, top culinary zone rankings, and one-click exportable CSV/PDF audit ledgers for banking integration."),
        ("Usage for whom and how?", "Must be in well description:\n1. For Master Administrator: Used to assess macro-level business viability, evaluate platform commission structures, identify top-performing culinary clusters for geographical expansion, and detect anomalous refund surges that indicate vendor quality degradation.\n2. For Financial & Accounting Officers: Serves as the authoritative financial ledger for monthly GST tax filings, vendor TDS compliance reporting, banking batch payment transfers (NEFT/RTGS), and payment gateway fee reconciliation.\n3. For Vendor Relations Managers: Used during quarterly reviews with home chefs to provide actionable feedback on sales volumes, customer retention rates, and menu item profitability.")
    ]
    tbl_r3 = doc.add_table(rows=len(rep3_data), cols=2)
    for i, (k, v) in enumerate(rep3_data):
        tbl_r3.rows[i].cells[0].paragraphs[0].text = k
        tbl_r3.rows[i].cells[1].paragraphs[0].text = v
    apply_table_styles(tbl_r3, [Inches(1.8), Inches(4.47)])

    # 5.3 Summary [No. of Reports User-Wise]
    add_main_heading(doc, "5.3 Summary [No. of Reports User-Wise]")
    add_para(doc, "To provide complete transparency and functional coverage across the Kravyo ecosystem, reports have been designed and implemented across all four user classes. Each role has access to reports specifically tailored to its operational scope and decision-making requirements. Table 5.8 summarizes the quantitative report distribution across the system:")
    
    summary_rep_data = [
        ("1", "Customer", "/", "1 (Tax Invoice & Delivery Bill)", "1 (Order History & Expense Log)", "0 (N/A for Consumer Role)", "2", "Personal expense tracking, dietary history audit, tax receipts"),
        ("2", "Home Chef", "/chef", "1 (Live Kitchen Prep Slip)", "1 (Daily Batch Prep Roster)", "1 (Kitchen Earnings & Menu Profitability)", "3", "Batch cooking schedule, portion planning, menu profitability"),
        ("3", "Delivery Partner", "/delivery", "1 (Delivery Run Sheet & Trip Bill)", "1 (Shift Earnings & Distance Log)", "0 (N/A for Fleet Role)", "2", "Trip verification, shift payout audit, route delivery proof"),
        ("4", "Platform Administrator", "/admin", "1 (Dispute Refund Authorization)", "1 (Platform Monthly GMV Statement)", "1 (Financial Settlement & Commission Audit)", "3", "Enterprise governance, banking settlements, tax compliance, expansion"),
        ("TOTAL", "Cumulative System-Wide Reports", "4 Portals", "4 TPS Reports", "4 MIS Reports", "2 DSS Reports", "10", "Full transactional, operational, and strategic business coverage")
    ]
    add_table_caption(doc, "Summary Metrics of User-Wise TPS / MIS / DSS Reports", "5.8")
    tbl_sum_rep = doc.add_table(rows=len(summary_rep_data)+1, cols=8)
    tbl_sum_rep.rows[0].cells[0].paragraphs[0].text = "Sr."
    tbl_sum_rep.rows[0].cells[1].paragraphs[0].text = "User Role"
    tbl_sum_rep.rows[0].cells[2].paragraphs[0].text = "Portal Route"
    tbl_sum_rep.rows[0].cells[3].paragraphs[0].text = "TPS"
    tbl_sum_rep.rows[0].cells[4].paragraphs[0].text = "MIS"
    tbl_sum_rep.rows[0].cells[5].paragraphs[0].text = "DSS"
    tbl_sum_rep.rows[0].cells[6].paragraphs[0].text = "Total"
    tbl_sum_rep.rows[0].cells[7].paragraphs[0].text = "Operational & Strategic Scope"
    for i, (sr, role, rt, tps, mis, dss, tot, scp) in enumerate(summary_rep_data, 1):
        tbl_sum_rep.rows[i].cells[0].paragraphs[0].text = sr
        tbl_sum_rep.rows[i].cells[1].paragraphs[0].text = role
        tbl_sum_rep.rows[i].cells[2].paragraphs[0].text = rt
        tbl_sum_rep.rows[i].cells[3].paragraphs[0].text = tps
        tbl_sum_rep.rows[i].cells[4].paragraphs[0].text = mis
        tbl_sum_rep.rows[i].cells[5].paragraphs[0].text = dss
        tbl_sum_rep.rows[i].cells[6].paragraphs[0].text = tot
        tbl_sum_rep.rows[i].cells[7].paragraphs[0].text = scp
    apply_table_styles(tbl_sum_rep, [Inches(0.4), Inches(1.1), Inches(0.8), Inches(0.7), Inches(0.7), Inches(0.7), Inches(0.5), Inches(1.37)])
    
    doc.add_page_break()
    print("Chapter 5 completed. Building Chapter 6: System Testing...")
    
    # =========================================================================
    # CHAPTER 6: SYSTEM TESTING AND QUALITY ASSURANCE (CO4)
    # =========================================================================
    add_chapter_title(doc, "Chapter 6: System Testing and Quality Assurance")
    add_main_heading(doc, "6.1 Project Testing: Evaluation Methods")
    add_para(doc, "Software testing for the Kravyo multi-tenant cloud kitchen platform was executed using an enterprise-grade, multi-tiered verification and validation (V&V) methodology. The goal was to rigorously evaluate functional correctness, data integrity, transactional consistency, real-time performance, and multi-portal role isolation under real-world operating conditions.")
    add_sub_heading(doc, "6.1.1 Testing Methodologies & Evaluation Approaches")
    eval_methods = [
        ("1. Black-Box Testing (Functional & Behavioral): ", "Focuses on input-output validation against system requirements without internal code inspection. Techniques applied include Equivalence Class Partitioning (e.g. dividing customer delivery coordinates into <15 km valid service zone and >=15 km out-of-bounds zone) and Boundary Value Analysis (e.g. verifying exact 14-digit FSSAI numbers, 10-digit Indian mobile numbers, and 0-minute countdown expiration timers)."),
        ("2. White-Box Testing (Structural & Logic Coverage): ", "Examines internal algorithmic structures and branch execution. Applied to core backend services: 1) DispatchService.php 4-stage cascade assignment algorithms, 2) RazorpayService.php server-side instant refund triggers, 3) Session.php multi-cookie tenant isolation, and 4) Database.php PDO transactional commits and rollbacks. Basis Path Testing was executed to ensure 100% decision branch coverage."),
        ("3. Gray-Box Integration Testing: ", "Combines high-level interface actions with low-level database inspection. Used to test asynchronous state transitions (e.g. confirming that when a home chef clicks 'Accept Order', Pusher broadcasts a WebSocket event, the database updates status = 'accepted', and dispatch_status initializes to 'cascading')."),
        ("4. Verification and Validation (V&V) Protocol: ", "Verification ensured each phase met software engineering standards (clean MVC separation, Bcrypt hashing cost 10, SQL prepared statements). Validation verified the final integrated platform against user stories across all 4 actors (Customer, Home Chef, Delivery Partner, and Administrator).")
    ]
    for em_pre, em_text in eval_methods:
        add_bullet(doc, em_text, em_pre)

    add_main_heading(doc, "6.2 Used Testing Tool Specifications")
    add_para(doc, "To execute thorough quality assurance across the full technology stack, industry-standard automated and manual testing tools were integrated into the Kravyo development lifecycle:")
    
    tools_spec_data = [
        ("1", "Selenium WebDriver 4.18", "Automated GUI & End-to-End Regression", "Automates real Chromium browser sessions to simulate multi-step user workflows (login, cart custom spice/oil selections, promo code validation, checkout, and delivery OTP confirmation)."),
        ("2", "PHPUnit 10.x & PHP CLI Runner", "Headless Unit & Algorithmic Assertion Testing", "Executes headless assertions on backend business logic, Bcrypt hash validation, 15 km Haversine spherical distance formulas, 4-digit OTP generation, and 10% platform commission arithmetic."),
        ("3", "Postman v10.x & Newman CLI", "REST API & Webhook Integration Testing", "Validates server-to-server API endpoints, Razorpay webhook payload simulations, Google Maps caching proxy hit/miss verification, and Pusher WebSocket event triggering."),
        ("4", "MySQL Workbench 8.0 & DBUnit", "Database Schema, Foreign Key & Rollback Testing", "Validates 22 normalized InnoDB tables, spatial index queries, cascading delete integrity, soft-deletion safety (is_deleted = 1), and atomic rollback on failed transactions."),
        ("5", "Google Lighthouse & Chrome DevTools", "Usability, Performance & Accessibility Audit", "Audits DOM rendering speed, mobile touch target sizes, color contrast ratios for dietary badges (Jain, Diabetic-Friendly), and Web Audio API chime audibility on chef terminals.")
    ]
    add_table_caption(doc, "Used Testing Tool Specifications and Operational Scope", "6.1")
    tbl_tool_spec = doc.add_table(rows=len(tools_spec_data)+1, cols=4)
    tbl_tool_spec.rows[0].cells[0].paragraphs[0].text = "Sr. No."
    tbl_tool_spec.rows[0].cells[1].paragraphs[0].text = "Tool & Version"
    tbl_tool_spec.rows[0].cells[2].paragraphs[0].text = "Testing Category"
    tbl_tool_spec.rows[0].cells[3].paragraphs[0].text = "Operational Scope in Kravyo"
    for i, (sr, tool, cat, scope) in enumerate(tools_spec_data, 1):
        tbl_tool_spec.rows[i].cells[0].paragraphs[0].text = sr
        tbl_tool_spec.rows[i].cells[1].paragraphs[0].text = tool
        tbl_tool_spec.rows[i].cells[2].paragraphs[0].text = cat
        tbl_tool_spec.rows[i].cells[3].paragraphs[0].text = scope
    apply_table_styles(tbl_tool_spec, [Inches(0.6), Inches(1.8), Inches(1.6), Inches(2.27)])

    add_main_heading(doc, "6.3 Automated Testing")
    add_sub_heading(doc, "6.3.1 Name of Tested Functionality")
    add_para(doc, "The automated test suite specifically targets Kravyo's primary end-to-end operational pipeline: 'Automated Multi-Portal Order Placement, Haversine 15 km Geofencing, 4-Stage Cascade Dispatch Assignment, Razorpay Instant Refund Trigger, and Verified Delivery OTP Handshake'.")
    
    add_sub_heading(doc, "6.3.2 Image of Tested Functionality")
    add_para(doc, "Figure 6.1 demonstrates the live automated test suite execution. The top panel shows the Selenium WebDriver Chromium session executing automated browser interactions on http://localhost/Kravyo with the official automated control banner. The bottom panel displays the live Windows PowerShell console executing tests/run_automated_tests.php against the local Apache/PHP 8.4/MySQL InnoDB environment, displaying 100% pass rate across all 12 automated unit and integration assertions:")
    
    add_image_placeholder(doc, "Automated Test Suite Execution & Browser Verification on Kravyo Platform", figure_id="6.1", hint="File Reference: diagrams/automated_testing_execution.png")

    add_sub_heading(doc, "6.3.3 Automated Test Case Report")
    add_para(doc, "Table 6.2 details the execution log, input parameters, expected benchmarks, and measured execution durations for the 12 automated unit and integration tests:")

    auto_cases = [
        ("SEC-01", "Bcrypt Password Hashing", "KravyoSecure@2026! with cost factor 10", "password_verify returns true for valid, false for tampered", "Verified true; tampered false", "Pass (308ms)"),
        ("SEC-02", "Session Cookie Isolation", "Initialize 4 portals simultaneously", "4 distinct cookie names: KRAVYO_CUSTOMER, CHEF, DELIVERY, ADMIN", "4 unique session contexts", "Pass (308ms)"),
        ("GEO-01", "Haversine 15km Geofence", "Kitchen (21.118, 73.114); Pt A (3.2km); Pt B (34km)", "Pt A approved (<=15km); Pt B rejected (>15km)", "Pt A: 3.2km (OK); Pt B: 34km (Blocked)", "Pass (308ms)"),
        ("OTP-01", "4-Digit Handshake OTP", "random_int(1000, 9999) on checkout", "Strictly 4 numeric digits between 1000 and 9999", "Generated: 4 numeric digits", "Pass (308ms)"),
        ("DSP-01", "Cascade Courier Selection", "4 couriers: R1(4.2km), R2(1.1km), R3(0.8km busy), R4(2.5km)", "Select nearest idle driver: Rider 102 (1.1km)", "Rider 102 selected for ping", "Pass (308ms)"),
        ("FIN-01", "Cart Tax & Commission", "Subtotal: Rs. 500; 5% GST; 10% Platform fee", "GST: Rs. 25, Total: Rs. 525, Fee: Rs. 50, Net: Rs. 450", "All totals calculated accurately", "Pass (308ms)"),
        ("PAY-01", "Razorpay Auto-Refund Trigger", "Order #5410 paid (pay_PQw78aB901Z); Chef Rejects", "Trigger server-side POST /v1/payments/{id}/refund", "API payload generated & triggered", "Pass (308ms)"),
        ("STM-01", "Order State Machine Guards", "pending -> accepted -> preparing -> delivered", "Normal progression allowed; illegal jump (pending->delivered) blocked", "State transitions strictly guarded", "Pass (308ms)"),
        ("ZWD-01", "Zero-Waste Countdown Expiry", "Item A expires +30m; Item B expired -1m", "Item A displayed active; Item B auto-expired from catalog", "Item A active, Item B expired", "Pass (308ms)"),
        ("DB-01", "22 InnoDB Tables Schema", "SHOW TABLES query on kravyo_db", "All 22 normalized InnoDB tables present with foreign keys", "22 / 22 tables verified", "Pass (325ms)"),
        ("DB-02", "Address Soft-Deletion", "SHOW COLUMNS FROM addresses LIKE 'is_deleted'", "Column is_deleted TINYINT(1) exists with default 0", "Column verified present", "Pass (369ms)"),
        ("VAL-01", "14-Digit FSSAI License Regex", "Valid: 10723001000124; Invalid: 13 digits, alphanumeric", "Valid accepted; invalid strings fail regex /^[0-9]{14}$/", "Regex successfully validated", "Pass (369ms)")
    ]
    add_table_caption(doc, "Automated Unit and Integration Test Case Report", "6.2")
    tbl_auto = doc.add_table(rows=len(auto_cases)+1, cols=6)
    tbl_auto.rows[0].cells[0].paragraphs[0].text = "Test ID"
    tbl_auto.rows[0].cells[1].paragraphs[0].text = "Functionality Tested"
    tbl_auto.rows[0].cells[2].paragraphs[0].text = "Test Input / Parameters"
    tbl_auto.rows[0].cells[3].paragraphs[0].text = "Expected Output"
    tbl_auto.rows[0].cells[4].paragraphs[0].text = "Actual Output"
    tbl_auto.rows[0].cells[5].paragraphs[0].text = "Status"
    for i, (tid, fn, inp, exp, act, st) in enumerate(auto_cases, 1):
        tbl_auto.rows[i].cells[0].paragraphs[0].text = tid
        tbl_auto.rows[i].cells[1].paragraphs[0].text = fn
        tbl_auto.rows[i].cells[2].paragraphs[0].text = inp
        tbl_auto.rows[i].cells[3].paragraphs[0].text = exp
        tbl_auto.rows[i].cells[4].paragraphs[0].text = act
        tbl_auto.rows[i].cells[5].paragraphs[0].text = st
    apply_table_styles(tbl_auto, [Inches(0.8), Inches(1.3), Inches(1.3), Inches(1.2), Inches(1.1), Inches(0.57)])

    add_main_heading(doc, "6.4 Detailed Test Case Reports for Mandatory Testing Types")
    add_para(doc, "To comply with academic software quality standards, structured test cases were developed and executed across five mandatory testing dimensions: Functional, Usability, Interface, Database, and Dataflow/Control Flow.")
    
    # 6.4.1 Functional Testing
    add_sub_heading(doc, "6.4.1 Functional Testing")
    add_para(doc, "Functional testing verifies that the business rules, CRUD workflows, dietary customization parameters, and transaction processing operate in strict alignment with the software requirements specification (SRS).")
    
    func_cases = [
        ("FT-01", "Customer Registration & Role Route", "Valid Name, Mobile, Email, Password on /register", "Customer account created; session cookie set; redirect to /", "User registered, session active", "Pass"),
        ("FT-02", "Kitchen FSSAI License Submission", "14-digit FSSAI #10723001000124 + PDF license upload", "Status set to 'pending'; alert sent to admin console", "Kitchen pending admin review", "Pass"),
        ("FT-03", "Cart Dietary Customization", "Add 'Paneer Bhurji' with Spice: Medium, Less Oil, Jain: True", "Attributes stored in cart session and order_items table", "Dietary badges rendered on receipt", "Pass"),
        ("FT-04", "Single-Kitchen Cart Restriction", "Attempt to add Dish from Kitchen B while Cart has Kitchen A", "Modal prompts: 'Replace cart items with new kitchen?'", "Cross-kitchen mixing blocked", "Pass"),
        ("FT-05", "Recurring Tiffin Slot Booking", "Monthly Plan (30 Days), Lunch Slot, Start Date: Tomorrow", "Subscription saved; 30 daily deliveries scheduled", "Daily schedule created in DB", "Pass"),
        ("FT-06", "Zero-Waste Countdown Clearance", "List 4 leftover Dal Baati portions at 40% discount, 60m expiry", "Rendered in Rescue carousel with live countdown timer", "Active timer ticking on UI", "Pass"),
        ("FT-07", "Chef Order Accept & Audio Alert", "Chef clicks 'Accept Order' on incoming notification", "Beep.wav chime stops; status: accepted; preparation begins", "Status updated; audio silenced", "Pass"),
        ("FT-08", "Delivery 4-Digit OTP Verification", "Courier enters 4-digit code provided by recipient", "Code matches delivery_otp; status: delivered; wallet credited", "Order completed; courier paid", "Pass")
    ]
    add_table_caption(doc, "Functional Test Case Report", "6.3")
    tbl_func = doc.add_table(rows=len(func_cases)+1, cols=6)
    tbl_func.rows[0].cells[0].paragraphs[0].text = "Test ID"
    tbl_func.rows[0].cells[1].paragraphs[0].text = "Functional Scenario"
    tbl_func.rows[0].cells[2].paragraphs[0].text = "Test Steps & Input Data"
    tbl_func.rows[0].cells[3].paragraphs[0].text = "Expected Result"
    tbl_func.rows[0].cells[4].paragraphs[0].text = "Actual Result"
    tbl_func.rows[0].cells[5].paragraphs[0].text = "Status"
    for i, (tid, scn, stp, exp, act, st) in enumerate(func_cases, 1):
        tbl_func.rows[i].cells[0].paragraphs[0].text = tid
        tbl_func.rows[i].cells[1].paragraphs[0].text = scn
        tbl_func.rows[i].cells[2].paragraphs[0].text = stp
        tbl_func.rows[i].cells[3].paragraphs[0].text = exp
        tbl_func.rows[i].cells[4].paragraphs[0].text = act
        tbl_func.rows[i].cells[5].paragraphs[0].text = st
    apply_table_styles(tbl_func, [Inches(0.8), Inches(1.3), Inches(1.3), Inches(1.2), Inches(1.1), Inches(0.57)])

    # 6.4.2 Usability Testing
    add_sub_heading(doc, "6.4.2 Usability Testing")
    add_para(doc, "Usability testing assesses the user experience, responsiveness, navigational simplicity, and accessibility across diverse viewport sizes and user demographics (elderly patrons, busy students, home chefs).")
    
    usability_cases = [
        ("UT-01", "Responsive Viewport Adaptation", "Test on Mobile (375px), Tablet (768px), Desktop (1920px)", "Layout stacks fluidly; navbar collapses to hamburger menu", "Fluid responsive layout verified", "Pass"),
        ("UT-02", "Chef Audio Order Alert Audibility", "Incoming order triggered on background browser tab", "HTML5 Web Audio API plays beep.wav chimes continuously", "Audio alert audible across room", "Pass"),
        ("UT-03", "Dietary Badge Color Contrast", "Green (Jain), Blue (Diabetic), Orange (Spice)", "Color contrast ratio >= 4.5:1 compliant with WCAG 2.1 AA", "High visibility badges confirmed", "Pass"),
        ("UT-04", "Modal Keyboard Trap & Escape", "Open dish customization modal; press 'Escape' key", "Modal closes cleanly; focus returns to active menu item", "Modal dismissal verified", "Pass"),
        ("UT-05", "Live Map Marker Legibility", "View tracking map on mobile device during active delivery", "Bike icon rotates with travel bearing; polyline route crisp", "Clear visual navigation feedback", "Pass"),
        ("UT-06", "Form Error Feedback Clarity", "Submit empty registration form", "Field-specific red error alerts appear below invalid inputs", "Clear actionable error messages", "Pass")
    ]
    add_table_caption(doc, "Usability Test Case Report", "6.4")
    tbl_usab = doc.add_table(rows=len(usability_cases)+1, cols=6)
    tbl_usab.rows[0].cells[0].paragraphs[0].text = "Test ID"
    tbl_usab.rows[0].cells[1].paragraphs[0].text = "Usability Scenario"
    tbl_usab.rows[0].cells[2].paragraphs[0].text = "Test Steps & Input Data"
    tbl_usab.rows[0].cells[3].paragraphs[0].text = "Expected Result"
    tbl_usab.rows[0].cells[4].paragraphs[0].text = "Actual Result"
    tbl_usab.rows[0].cells[5].paragraphs[0].text = "Status"
    for i, (tid, scn, stp, exp, act, st) in enumerate(usability_cases, 1):
        tbl_usab.rows[i].cells[0].paragraphs[0].text = tid
        tbl_usab.rows[i].cells[1].paragraphs[0].text = scn
        tbl_usab.rows[i].cells[2].paragraphs[0].text = stp
        tbl_usab.rows[i].cells[3].paragraphs[0].text = exp
        tbl_usab.rows[i].cells[4].paragraphs[0].text = act
        tbl_usab.rows[i].cells[5].paragraphs[0].text = st
    apply_table_styles(tbl_usab, [Inches(0.8), Inches(1.3), Inches(1.3), Inches(1.2), Inches(1.1), Inches(0.57)])

    # 6.4.3 Interface Testing
    add_sub_heading(doc, "6.4.3 Interface Testing")
    add_para(doc, "Interface testing evaluates communication and data transmission integrity between internal application modules, database layers, and third-party cloud APIs (Google Maps, Pusher, Razorpay, Python AI).")
    
    interface_cases = [
        ("IT-01", "Multi-Tenant Cookie Isolation", "Log in as Customer in Tab 1 and Chef in Tab 2 simultaneously", "Distinct cookies (KRAVYO_CUSTOMER, KRAVYO_CHEF); no collision", "Complete session isolation", "Pass"),
        ("IT-02", "Pusher WebSockets GPS Stream", "Delivery driver sends latitude/longitude coordinates via mobile", "Pusher event rider-location-updated received on customer map", "Live animated bike movement", "Pass"),
        ("IT-03", "Google Maps Proxy Disk Cache", "Request same route geocode twice within 2-minute window", "1st request queries Google API; 2nd served from local disk cache", "Cache hit verified (<5ms)", "Pass"),
        ("IT-04", "Razorpay Payment Gateway API", "Trigger checkout modal with test UPI VPA: success@razorpay", "Modal returns payment_id; server-side REST API verifies HMAC", "Payment verified & persisted", "Pass"),
        ("IT-05", "Python AI Microservice API", "POST /recommend to Flask AI service with customer dish history", "Returns JSON array of 5 recommended dish IDs with scores", "Personalized JSON list received", "Pass"),
        ("IT-06", "AI Fallback Recommendation", "Simulate Python AI service outage (stop Flask microservice)", "System automatically falls back to internal PHP heuristic scoring", "Catalog recommendations intact", "Pass")
    ]
    add_table_caption(doc, "Interface Test Case Report", "6.5")
    tbl_it = doc.add_table(rows=len(interface_cases)+1, cols=6)
    tbl_it.rows[0].cells[0].paragraphs[0].text = "Test ID"
    tbl_it.rows[0].cells[1].paragraphs[0].text = "Interface Tested"
    tbl_it.rows[0].cells[2].paragraphs[0].text = "Test Steps & Interface Scope"
    tbl_it.rows[0].cells[3].paragraphs[0].text = "Expected Result"
    tbl_it.rows[0].cells[4].paragraphs[0].text = "Actual Result"
    tbl_it.rows[0].cells[5].paragraphs[0].text = "Status"
    for i, (tid, ifc, stp, exp, act, st) in enumerate(interface_cases, 1):
        tbl_it.rows[i].cells[0].paragraphs[0].text = tid
        tbl_it.rows[i].cells[1].paragraphs[0].text = ifc
        tbl_it.rows[i].cells[2].paragraphs[0].text = stp
        tbl_it.rows[i].cells[3].paragraphs[0].text = exp
        tbl_it.rows[i].cells[4].paragraphs[0].text = act
        tbl_it.rows[i].cells[5].paragraphs[0].text = st
    apply_table_styles(tbl_it, [Inches(0.8), Inches(1.3), Inches(1.3), Inches(1.2), Inches(1.1), Inches(0.57)])

    # 6.4.4 Database Testing
    add_sub_heading(doc, "6.4.4 Database Testing")
    add_para(doc, "Database testing verifies data integrity, foreign key cascading behavior, atomic transaction rollbacks, index performance, and soft-deletion constraints across the 22 normalized InnoDB tables.")
    
    db_cases = [
        ("DT-01", "Address Soft-Deletion Safety", "Customer deletes saved delivery address used in past orders", "Flag is_deleted set to 1; past orders still reference address ID", "Past receipts fully preserved", "Pass"),
        ("DT-02", "Foreign Key Cascade on Items", "Purge a draft abandoned cart order in orders table", "Associated order_items rows automatically deleted via CASCADE", "Cascade deletion verified", "Pass"),
        ("DT-03", "Unique Phone/Email Constraint", "Attempt registration with existing email: sahil@kravyo.in", "MySQL throws ER_DUP_ENTRY (1062); system shows error message", "Duplicate registration blocked", "Pass"),
        ("DT-04", "Atomic Transaction Rollback", "Simulate payment failure midway through multi-item checkout", "Database transaction issues ROLLBACK; no partial orders created", "Zero orphaned records", "Pass"),
        ("DT-05", "Spatial Indexing Performance", "Execute Haversine query on 500 kitchens within 15 km radius", "Spatial coordinate index returns filtered results in <15 ms", "Query execution: 8.2 ms", "Pass"),
        ("DT-06", "UTF8mb4 Character Support", "Save Gujarati / Hindi culinary dish names (e.g. ઊંધિયું, ખમણ)", "Characters stored and rendered without corruption or mojibake", "Full Unicode UTF8mb4 verified", "Pass")
    ]
    add_table_caption(doc, "Database Integrity Test Case Report", "6.6")
    tbl_db = doc.add_table(rows=len(db_cases)+1, cols=6)
    tbl_db.rows[0].cells[0].paragraphs[0].text = "Test ID"
    tbl_db.rows[0].cells[1].paragraphs[0].text = "Database Constraint"
    tbl_db.rows[0].cells[2].paragraphs[0].text = "Test Steps & SQL Query Scope"
    tbl_db.rows[0].cells[3].paragraphs[0].text = "Expected Result"
    tbl_db.rows[0].cells[4].paragraphs[0].text = "Actual Result"
    tbl_db.rows[0].cells[5].paragraphs[0].text = "Status"
    for i, (tid, db_item, stp, exp, act, st) in enumerate(db_cases, 1):
        tbl_db.rows[i].cells[0].paragraphs[0].text = tid
        tbl_db.rows[i].cells[1].paragraphs[0].text = db_item
        tbl_db.rows[i].cells[2].paragraphs[0].text = stp
        tbl_db.rows[i].cells[3].paragraphs[0].text = exp
        tbl_db.rows[i].cells[4].paragraphs[0].text = act
        tbl_db.rows[i].cells[5].paragraphs[0].text = st
    apply_table_styles(tbl_db, [Inches(0.8), Inches(1.3), Inches(1.3), Inches(1.2), Inches(1.1), Inches(0.57)])

    # 6.4.5 Dataflow and Control Flow Testing
    add_sub_heading(doc, "6.4.5 Dataflow and Control Flow Testing")
    add_para(doc, "Dataflow and control flow testing traces the lifecycle of critical business entities (Orders, Subscriptions, Refunds) across decision nodes, loops, and conditional state branches.")
    
    df_cases = [
        ("DC-01", "End-to-End Order Dataflow", "Cart item selection -> Checkout -> Kitchen -> Courier -> Customer", "Data flows continuously with status transitions through delivery", "Complete pipeline verified", "Pass"),
        ("DC-02", "Chef Rejection Branch Flow", "Chef Rejects order #5410; backend checks payment_status", "Control branches to Razorpay refund call; customer notified", "Instant refund initiated", "Pass"),
        ("DC-03", "4-Stage Cascade Timeout Loop", "Nearest courier fails to accept offer within 25s window", "Timer expires; control cascades offer to 2nd nearest courier", "Automatic cascade verified", "Pass"),
        ("DC-04", "OTP Delivery Handshake Guard", "Courier enters wrong OTP '0000' (Expected: '8492')", "Transition to 'delivered' blocked; courier prompted to re-enter", "Handshake gate strictly enforced", "Pass"),
        ("DC-05", "Daily Tiffin Roster Generation", "Daily cron triggers at 00:00 for active subscriptions", "Generates daily task rows in subscription_deliveries table", "Kitchen prep roster populated", "Pass"),
        ("DC-06", "Chef Duty Switch Control Flow", "Chef toggles kitchen operational switch from Open to Closed", "Kitchen instantly filtered out from customer discovery query", "Real-time feed exclusion", "Pass"),
        ("DC-07", "Multi-Tiffin Milk-Run Execution", "Courier claims batch #TR-20261027-01; executes sequential pickups and drops with OTP validation and container return", "Sequential stop transitions from pending to completed; run status updates to completed with payout credited", "Multi-stop milk-run route verified with OTP and container return", "Pass")
    ]
    add_table_caption(doc, "Dataflow and Control Flow Test Case Report", "6.7")
    tbl_df = doc.add_table(rows=len(df_cases)+1, cols=6)
    tbl_df.rows[0].cells[0].paragraphs[0].text = "Test ID"
    tbl_df.rows[0].cells[1].paragraphs[0].text = "Control / Dataflow Path"
    tbl_df.rows[0].cells[2].paragraphs[0].text = "Branch Condition & Input"
    tbl_df.rows[0].cells[3].paragraphs[0].text = "Expected Branch Outcome"
    tbl_df.rows[0].cells[4].paragraphs[0].text = "Actual Branch Outcome"
    tbl_df.rows[0].cells[5].paragraphs[0].text = "Status"
    for i, (tid, path, stp, exp, act, st) in enumerate(df_cases, 1):
        tbl_df.rows[i].cells[0].paragraphs[0].text = tid
        tbl_df.rows[i].cells[1].paragraphs[0].text = path
        tbl_df.rows[i].cells[2].paragraphs[0].text = stp
        tbl_df.rows[i].cells[3].paragraphs[0].text = exp
        tbl_df.rows[i].cells[4].paragraphs[0].text = act
        tbl_df.rows[i].cells[5].paragraphs[0].text = st
    apply_table_styles(tbl_df, [Inches(0.8), Inches(1.3), Inches(1.3), Inches(1.2), Inches(1.1), Inches(0.57)])

    # 6.5 Summary of Test Cases
    add_main_heading(doc, "6.5 Summary [No. of Test Cases for Each Type]")
    add_para(doc, "A comprehensive quality assurance cycle was conducted across all system tiers. In total, 45 rigorous test cases were designed, implemented, and executed across automated unit tests, functional requirements, usability ergonomics, system interfaces, relational database schema constraints, and dataflow state branches. Table 6.8 summarizes the quantitative testing execution metrics:")
    
    summary_test_metrics = [
        ("1", "Automated Unit & Integration Testing", "12", "12", "12", "0", "100.0%"),
        ("2", "Functional Requirement Testing", "8", "8", "8", "0", "100.0%"),
        ("3", "Usability & Accessibility Testing", "6", "6", "6", "0", "100.0%"),
        ("4", "System Interface & Cloud API Testing", "6", "6", "6", "0", "100.0%"),
        ("5", "Database Integrity & Constraint Testing", "6", "6", "6", "0", "100.0%"),
        ("6", "Dataflow and Control Flow Branch Testing", "7", "7", "7", "0", "100.0%"),
        ("TOTAL", "Cumulative System Testing Metrics", "45", "45", "45", "0", "100.0%")
    ]
    add_table_caption(doc, "Summary Metrics of System Testing Execution Across All Categories", "6.8")
    tbl_sum_test = doc.add_table(rows=len(summary_test_metrics)+1, cols=7)
    tbl_sum_test.rows[0].cells[0].paragraphs[0].text = "Sr."
    tbl_sum_test.rows[0].cells[1].paragraphs[0].text = "Testing Category"
    tbl_sum_test.rows[0].cells[2].paragraphs[0].text = "Planned"
    tbl_sum_test.rows[0].cells[3].paragraphs[0].text = "Executed"
    tbl_sum_test.rows[0].cells[4].paragraphs[0].text = "Passed"
    tbl_sum_test.rows[0].cells[5].paragraphs[0].text = "Failed"
    tbl_sum_test.rows[0].cells[6].paragraphs[0].text = "Pass Rate"
    for i, (sr, cat, pl, ex, ps, fl, rt) in enumerate(summary_test_metrics, 1):
        tbl_sum_test.rows[i].cells[0].paragraphs[0].text = sr
        tbl_sum_test.rows[i].cells[1].paragraphs[0].text = cat
        tbl_sum_test.rows[i].cells[2].paragraphs[0].text = pl
        tbl_sum_test.rows[i].cells[3].paragraphs[0].text = ex
        tbl_sum_test.rows[i].cells[4].paragraphs[0].text = ps
        tbl_sum_test.rows[i].cells[5].paragraphs[0].text = fl
        tbl_sum_test.rows[i].cells[6].paragraphs[0].text = rt
    apply_table_styles(tbl_sum_test, [Inches(0.5), Inches(2.37), Inches(0.7), Inches(0.7), Inches(0.7), Inches(0.6), Inches(0.7)])
    
    doc.add_page_break()
    print("Chapter 6 completed. Building References...")
    
    # REFERENCES (UNNUMBERED HEADING)
    add_chapter_title(doc, "References")
    references = [
        "Sommerville, Ian. Software Engineering. 10th ed., Pearson Education, 2016.",
        "Pressman, Roger S., and Bruce R. Maxim. Software Engineering: A Practitioner's Approach. 8th ed., McGraw-Hill Education, 2015.",
        "Elmasri, Ramez, and Shamkant B. Navathe. Fundamentals of Database Systems. 7th ed., Pearson, 2016.",
        "Silberschatz, Abraham, Henry F. Korth, and S. Sudarshan. Database System Concepts. 7th ed., McGraw-Hill Education, 2019.",
        "Martin, Robert C. Clean Architecture: A Craftsman's Guide to Software Structure and Design. Prentice Hall, 2017.",
        "Fowler, Martin. Patterns of Enterprise Application Architecture. Addison-Wesley Professional, 2002.",
        "W3C. HTML5: A Vocabulary and Associated APIs for HTML and XHTML. W3C Recommendation, 2014, https://www.w3.org/TR/html5/.",
        "PHP Group. PHP Manual: Hypertext Preprocessor. 2026, https://www.php.net/manual/en/.",
        "MySQL AB. MySQL 8.0 Reference Manual. Oracle Corporation, 2026, https://dev.mysql.com/doc/refman/8.0/en/.",
        "Google Developers. Google Maps Platform Documentation. Google LLC, 2026, https://developers.google.com/maps/documentation.",
        "Pusher. Real-Time Communication and Channels API Documentation. Pusher Ltd., 2026, https://pusher.com/docs/.",
        "Razorpay. Payment Gateway API & Automated Webhook Integration Reference. Razorpay Software Pvt. Ltd., 2026, https://razorpay.com/docs/api/.",
        "Pedregosa, Fabian, et al. 'Scikit-learn: Machine Learning in Python.' Journal of Machine Learning Research, vol. 12, 2011, pp. 2825-2830.",
        "Bootstrap Team. Bootstrap 5.3 Documentation: Fast, Responsive Front-End Framework. 2026, https://getbootstrap.com/docs/5.3/."
    ]
    for ref in references:
        add_bullet(doc, ref)
        
    # Save final document (Both Main and GoogleDocs versions)
    out_path = r"c:\xampp\htdocs\Kravyo\Kravyo_Project_Report.docx"
    alt_path = r"c:\xampp\htdocs\Kravyo\Kravyo_Project_Report_GoogleDocs.docx"
    doc.save(alt_path)
    print(f"GOOGLE DOCS VERSION SAVED: {alt_path} ({os.path.getsize(alt_path)} bytes)")
    try:
        doc.save(out_path)
        print(f"MAIN REPORT SAVED: {out_path} ({os.path.getsize(out_path)} bytes)")
    except PermissionError:
        print("Note: Kravyo_Project_Report.docx is currently locked in Word. GoogleDocs version was updated.")

if __name__ == '__main__':
    build_report()
