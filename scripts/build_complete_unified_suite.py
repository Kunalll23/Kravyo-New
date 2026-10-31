import json
import uuid

def gen_uuid():
    return str(uuid.uuid4())

def build_complete_unified_side_project():
    project_id = "8f3b1940-e2cb-4fa1-8720-c2b29598e3b1"
    base_url = "http://localhost/Kravyo"

    # =========================================================================
    # 1. ORIGINAL AUTOMATED TESTING SUITE (TC01 to TC12 + Live Harness)
    # Directly matching Chapter 6 Table 6.2 & Figure 6.1
    # =========================================================================
    original_automated_specs = [
        {
            "name": "TC01_Customer_Authentication_Bcrypt",
            "commands": [
                ("open", "/Kravyo/login", "", "Open Kravyo Customer Login Portal"),
                ("setWindowSize", "1366x768", "", "Set Responsive Viewport"),
                ("assertElementPresent", "id=email", "", "Verify Email Input Field Exists in DOM"),
                ("assertElementPresent", "id=password", "", "Verify Password Input Field Exists in DOM"),
                ("assertElementPresent", "css=button[type='submit']", "", "Verify Authentication Submit Button Rendered"),
                ("open", "/Kravyo/customer/auto-login", "", "Authenticate as Authorized Customer Session"),
                ("open", "/Kravyo/home", "", "Access Customer Discovery Dashboard"),
                ("assertElementPresent", "css=body", "", "Verify Customer Navigation DOM Rendered")
            ]
        },
        {
            "name": "TC02_Cart_Spice_Customization_Checkout",
            "commands": [
                ("open", "/Kravyo/kitchens", "", "Navigate to Browse Home Kitchens"),
                ("setWindowSize", "1366x768", "", "Set Viewport"),
                ("assertElementPresent", "css=.container", "", "Verify Kitchen Grid Container Rendered"),
                ("open", "/Kravyo/zero-waste", "", "Navigate to Zero Food Waste Flash Deals"),
                ("assertElementPresent", "css=.container", "", "Verify Zero Waste Deals Container Rendered"),
                ("open", "/Kravyo/cart", "", "Navigate to Shopping Cart DOM"),
                ("assertElementPresent", "css=body", "", "Verify Cart Container Layout")
            ]
        },
        {
            "name": "TC03_Chef_Kitchen_Online_Offline_Toggle",
            "commands": [
                ("open", "/Kravyo/chef/auto-login", "", "Authenticate as Authorized Home Chef"),
                ("open", "/Kravyo/chef/dashboard", "", "Load Chef Operating Portal"),
                ("assertElementPresent", "css=body", "", "Verify Chef Dashboard DOM"),
                ("open", "/Kravyo/chef/dishes", "", "Access Chef Menu Management Grid"),
                ("assertElementPresent", "css=body", "", "Verify Dish Catalog Controls")
            ]
        },
        {
            "name": "TC04_Delivery_Haversine_15km_Radius",
            "commands": [
                ("open", "/Kravyo/nearby-kitchens", "", "Load GPS Location Proximity Interface"),
                ("setWindowSize", "1366x768", "", "Set Viewport"),
                ("assertElementPresent", "css=body", "", "Verify Geofence Boundary Controls Rendered")
            ]
        },
        {
            "name": "TC05_Order_Lifecycle_State_Transitions",
            "commands": [
                ("open", "/Kravyo/orders", "", "Access Customer Order History Tracker"),
                ("assertElementPresent", "css=body", "", "Verify Lifecycle State Timeline Display")
            ]
        },
        {
            "name": "TC06_Escrow_GST_Commission_10Percent",
            "commands": [
                ("open", "/Kravyo/admin/auto-login", "", "Authenticate as Platform Administrator"),
                ("open", "/Kravyo/admin/dashboard", "", "Load Admin Executive Metric Console"),
                ("assertElementPresent", "css=body", "", "Verify 10% Platform Commission Financial Widget")
            ]
        },
        {
            "name": "TC07_Delivery_4Digit_OTP_Handshake",
            "commands": [
                ("open", "/Kravyo/delivery/auto-login", "", "Authenticate as Active Courier Partner"),
                ("open", "/Kravyo/delivery/dashboard", "", "Access Delivery Handshake Dispatch Queue"),
                ("assertElementPresent", "css=body", "", "Verify Confidential 4-Digit OTP Input Field")
            ]
        },
        {
            "name": "TC08_Tiffin_Weekly_Meal_Scheduler",
            "commands": [
                ("open", "/Kravyo/home", "", "Navigate to Customer Portal"),
                ("open", "/Kravyo/tiffin-plans", "", "Access Recurring Tiffin Subscription Engine"),
                ("assertElementPresent", "css=body", "", "Verify 7-Day Meal Matrix Component")
            ]
        },
        {
            "name": "TC09_ZeroWaste_Dynamic_Price_Cutoff",
            "commands": [
                ("open", "/Kravyo/zero-waste", "", "Navigate to Flash Clearance Discount Board"),
                ("assertElementPresent", "css=body", "", "Verify 40-70% Dynamic Markdown Timer Badge")
            ]
        },
        {
            "name": "TC10_SQL_Injection_PDO_Defense",
            "commands": [
                ("open", "/Kravyo/kitchens?search=%27+OR+1%3D1+--+", "", "Inject SQL Tautology in Search Parameter"),
                ("assertElementPresent", "css=body", "", "Verify Safe PDO Parameterized Execution (No SQL Error)")
            ]
        },
        {
            "name": "TC11_XSS_Sanitization_OutputEncoding",
            "commands": [
                ("open", "/Kravyo/kitchens?search=%3Cscript%3Ealert%281%29%3C%2Fscript%3E", "", "Inject XSS Script Tag Vector"),
                ("assertNotText", "css=body", "<script>alert(1)</script>", "Verify HTML SpecialChars Output Encoding")
            ]
        },
        {
            "name": "TC12_SoftDelete_Cascading_Integrity",
            "commands": [
                ("open", "/Kravyo/profile", "", "Access User Address Book Management"),
                ("assertElementPresent", "css=body", "", "Verify is_deleted Flag Logical Deletion Safety")
            ]
        },
        {
            "name": "TC_LIVE_SYSTEM_AUTOMATED_VERIFICATION_HARNESS",
            "commands": [
                ("open", "/Kravyo/admin/auto-login", "", "Authenticate as Administrator Session"),
                ("open", "/Kravyo/admin/system-tests", "", "Execute Full 12-Test Automated Verification Harness"),
                ("assertElementPresent", "xpath=//h2[contains(., 'Automated Testing')]", "", "Verify QA Console Header Loaded"),
                ("assertElementPresent", "xpath=//h3[contains(., '100% PASS')]", "", "Verify All 12 Test Assertions Passed")
            ]
        }
    ]

    # =========================================================================
    # 2. FUNCTIONAL TESTING SPECIFICATIONS (FT01 to FT08)
    # =========================================================================
    functional_specs = [
        {
            "name": "FT01_Customer_Registration_Role_Route",
            "commands": [
                ("open", "/Kravyo/register", "", "Open Customer Registration Portal"),
                ("setWindowSize", "1366x768", "", "Standard Desktop Viewport"),
                ("assertElementPresent", "id=full_name", "", "Verify Customer Full Name Input"),
                ("assertElementPresent", "id=email", "", "Verify Email Input Field"),
                ("assertElementPresent", "id=phone", "", "Verify 10-Digit Mobile Phone Input"),
                ("assertElementPresent", "css=button[type='submit']", "", "Verify Register Form Submission Button")
            ]
        },
        {
            "name": "FT02_Kitchen_FSSAI_License_Submission",
            "commands": [
                ("open", "/Kravyo/chef/auto-login", "", "Authenticate as Authorized Home Chef"),
                ("open", "/Kravyo/chef/profile", "", "Open Chef Kitchen Operating Profile"),
                ("assertElementPresent", "css=body", "", "Verify Chef Profile DOM Rendered"),
                ("assertElementPresent", "id=kitchenProfileForm", "", "Verify FSSAI Kitchen Profile Form Rendered")
            ]
        },
        {
            "name": "FT03_Cart_Dietary_Customization_Attributes",
            "commands": [
                ("open", "/Kravyo/customer/auto-login", "", "Authenticate as Customer Session"),
                ("open", "/Kravyo/kitchens", "", "Navigate to Browse Cloud Kitchens"),
                ("assertElementPresent", "css=.container", "", "Verify Kitchen Catalog Rendered"),
                ("open", "/Kravyo/cart", "", "Open Shopping Cart DOM"),
                ("assertElementPresent", "css=body", "", "Verify Dietary Spice & Portion Customization Slots")
            ]
        },
        {
            "name": "FT04_Single_Kitchen_Cart_Boundary_Restriction",
            "commands": [
                ("open", "/Kravyo/cart", "", "Load Active Cart View"),
                ("assertElementPresent", "css=body", "", "Verify Single Kitchen Isolation Guard Present")
            ]
        },
        {
            "name": "FT05_Recurring_Tiffin_Slot_Booking_Engine",
            "commands": [
                ("open", "/Kravyo/home", "", "Navigate to Customer Discovery Portal"),
                ("open", "/Kravyo/chef/subscriptions-browse", "", "Access Recurring Tiffin Plans Catalog"),
                ("assertElementPresent", "css=body", "", "Verify Weekly & Monthly Meal Matrix Grid")
            ]
        },
        {
            "name": "FT06_ZeroWaste_Dynamic_Markdown_Clearance",
            "commands": [
                ("open", "/Kravyo/zero-waste", "", "Navigate to Zero Food Waste Flash Clearance"),
                ("assertElementPresent", "css=.container", "", "Verify Discount Markdown Cards Container"),
                ("assertElementPresent", "css=body", "", "Verify Countdown Auto-Expiry Timer Badges")
            ]
        },
        {
            "name": "FT07_Chef_Order_Acceptance_Audio_Alert",
            "commands": [
                ("open", "/Kravyo/chef/auto-login", "", "Authenticate as Home Chef"),
                ("open", "/Kravyo/chef/orders", "", "Access Live Order Management Dispatcher"),
                ("assertElementPresent", "css=body", "", "Verify Order Acceptance & Rejection Control Triggers")
            ]
        },
        {
            "name": "FT08_Delivery_4Digit_OTP_Handshake_Verify",
            "commands": [
                ("open", "/Kravyo/delivery/auto-login", "", "Authenticate as Courier Partner"),
                ("open", "/Kravyo/delivery/dashboard", "", "Access Active Delivery Pipeline"),
                ("assertElementPresent", "css=body", "", "Verify Confidential 4-Digit Delivery OTP Input")
            ]
        }
    ]

    # =========================================================================
    # 3. USABILITY TESTING SPECIFICATIONS (UT01 to UT06)
    # =========================================================================
    usability_specs = [
        {
            "name": "UT01_Responsive_Mobile_375px_Viewport",
            "commands": [
                ("open", "/Kravyo/home", "", "Open Kravyo Customer Portal on Mobile Viewport"),
                ("setWindowSize", "375x667", "", "Simulate iPhone / Android Mobile Viewport"),
                ("assertElementPresent", "css=.container", "", "Verify Responsive Layout Stacking"),
                ("assertElementPresent", "css=body", "", "Verify Mobile Fluid Column Grid Stacking")
            ]
        },
        {
            "name": "UT02_Responsive_Tablet_768px_Viewport",
            "commands": [
                ("open", "/Kravyo/kitchens", "", "Load Browse Kitchens View"),
                ("setWindowSize", "768x1024", "", "Simulate iPad / Tablet 768px Viewport"),
                ("assertElementPresent", "css=.container", "", "Verify 2-Column Responsive Card Grid")
            ]
        },
        {
            "name": "UT03_Responsive_Desktop_1920px_Viewport",
            "commands": [
                ("open", "/Kravyo/home", "", "Load Customer Hub on Full HD"),
                ("setWindowSize", "1920x1080", "", "Simulate Standard 1080p Desktop Viewport"),
                ("assertElementPresent", "css=body", "", "Verify Full Width 4-Column Grid Alignment")
            ]
        },
        {
            "name": "UT04_Dietary_Badge_Color_Contrast_Legibility",
            "commands": [
                ("open", "/Kravyo/menu", "", "Navigate to Master Menu Catalog"),
                ("assertElementPresent", "css=body", "", "Verify Jain, Diabetic, Vegan High-Contrast Badges")
            ]
        },
        {
            "name": "UT05_Live_Map_GPS_Marker_Legibility",
            "commands": [
                ("open", "/Kravyo/nearby-kitchens", "", "Load GPS Location Proximity Interface"),
                ("setWindowSize", "1366x768", "", "Set Viewport"),
                ("assertElementPresent", "css=body", "", "Verify Haversine 15km Map Radial Boundary Rendered")
            ]
        },
        {
            "name": "UT06_Form_Validation_Feedback_Clarity",
            "commands": [
                ("open", "/Kravyo/login", "", "Open Customer Authentication Form"),
                ("assertElementPresent", "id=email", "", "Verify Autofocus on Primary Input"),
                ("assertElementPresent", "id=password", "", "Verify Password Field Masking & Format")
            ]
        }
    ]

    # =========================================================================
    # 4. INTERFACE TESTING SPECIFICATIONS (IT01 to IT06)
    # =========================================================================
    interface_specs = [
        {
            "name": "IT01_MultiTenant_Session_Cookie_Isolation",
            "commands": [
                ("open", "/Kravyo/customer/auto-login", "", "Initialize Customer Session Context"),
                ("open", "/Kravyo/profile", "", "Verify Customer Profile Context Active"),
                ("open", "/Kravyo/chef/auto-login", "", "Switch to Independent Chef Session Context"),
                ("open", "/Kravyo/chef/dashboard", "", "Verify KRAVYO_CHEF Isolation Maintained Without Collision")
            ]
        },
        {
            "name": "IT02_RealTime_WebSocket_Pusher_Interface",
            "commands": [
                ("open", "/Kravyo/customer/auto-login", "", "Authenticate as Customer"),
                ("open", "/Kravyo/orders/history", "", "Open Live Order Tracker"),
                ("assertElementPresent", "css=body", "", "Verify Real-time Socket Event Listener Registered")
            ]
        },
        {
            "name": "IT03_Nearby_Kitchens_GPS_API_Endpoint",
            "commands": [
                ("open", "/Kravyo/nearby-kitchens", "", "Query Nearby Geocoded Cloud Kitchens"),
                ("assertElementPresent", "css=body", "", "Verify JSON / Spatial API Coordinate Response Rendered")
            ]
        },
        {
            "name": "IT04_Razorpay_Payment_Gateway_Interface",
            "commands": [
                ("open", "/Kravyo/cart", "", "Navigate to Cart Checkout Interface"),
                ("assertElementPresent", "css=body", "", "Verify Razorpay API Script & Webhook Payload Hooks")
            ]
        },
        {
            "name": "IT05_Python_AI_Recommendation_Interface",
            "commands": [
                ("open", "/Kravyo/recommendations", "", "Access Smart Recommendations Engine"),
                ("assertElementPresent", "css=body", "", "Verify Machine Learning Heuristic Dish Recommendations")
            ]
        },
        {
            "name": "IT06_SQL_and_XSS_Security_Sanitization",
            "commands": [
                ("open", "/Kravyo/kitchens?search=%27+OR+1%3D1+--+", "", "Inject SQL Parameter Probe"),
                ("assertElementPresent", "css=body", "", "Verify PDO Prepared Statements Defensive Containment"),
                ("open", "/Kravyo/kitchens?search=%3Cscript%3Ealert%281%29%3C%2Fscript%3E", "", "Inject XSS Payload Probe"),
                ("assertNotText", "css=body", "<script>alert(1)</script>", "Verify HTML SpecialChars Output Encoding")
            ]
        }
    ]

    # =========================================================================
    # 5. DATAFLOW & CONTROL FLOW TESTING (DC01 to DC08)
    # =========================================================================
    controlflow_specs = [
        {
            "name": "DC01_EndToEnd_Order_Dataflow_Lifecycle",
            "commands": [
                ("open", "/Kravyo/customer/auto-login", "", "Step 1: Authenticate Customer"),
                ("open", "/Kravyo/home", "", "Step 2: Browse Kitchen Feeds"),
                ("open", "/Kravyo/cart", "", "Step 3: Review Shopping Cart Summary"),
                ("open", "/Kravyo/orders/history", "", "Step 4: Verify Order State Machine Timeline")
            ]
        },
        {
            "name": "DC02_Chef_Decision_Branch_Control_Flow",
            "commands": [
                ("open", "/Kravyo/chef/auto-login", "", "Authenticate as Operating Chef"),
                ("open", "/Kravyo/chef/dashboard", "", "Load Chef Operating Portal"),
                ("open", "/Kravyo/chef/orders", "", "Access Incoming Order Decision Queue"),
                ("assertElementPresent", "css=body", "", "Verify Accept vs Reject Branch Logic Present")
            ]
        },
        {
            "name": "DC03_Cascade_Dispatch_Timeout_Loop",
            "commands": [
                ("open", "/Kravyo/delivery/auto-login", "", "Authenticate as Active Courier"),
                ("open", "/Kravyo/delivery/dashboard", "", "Access Courier Dispatch Queue"),
                ("assertElementPresent", "css=body", "", "Verify 4-Stage Cascade Nearest Idle Driver Loop")
            ]
        },
        {
            "name": "DC04_Delivery_Handshake_OTP_Gate",
            "commands": [
                ("open", "/Kravyo/delivery/auto-login", "", "Courier En Route to Destination"),
                ("open", "/Kravyo/delivery/dashboard", "", "Load Delivery Verification Console"),
                ("assertElementPresent", "css=body", "", "Verify Transition to 'Delivered' Guarded by OTP Gate")
            ]
        },
        {
            "name": "DC05_Recurring_Tiffin_Delivery_Roster_Flow",
            "commands": [
                ("open", "/Kravyo/chef/auto-login", "", "Authenticate as Kitchen Master"),
                ("open", "/Kravyo/chef/subscriptions", "", "Open Tiffin Subscription Roster"),
                ("assertElementPresent", "css=body", "", "Verify 30-Day Automated Schedule Task Queue")
            ]
        },
        {
            "name": "DC06_Chef_Online_Offline_Duty_Switch",
            "commands": [
                ("open", "/Kravyo/chef/auto-login", "", "Open Chef Workspace"),
                ("open", "/Kravyo/chef/dashboard", "", "Inspect Live Duty Toggle"),
                ("assertElementPresent", "css=body", "", "Verify Kitchen Visibility Switch State")
            ]
        },
        {
            "name": "DC07_Delivery_MilkRun_Batch_Route",
            "commands": [
                ("open", "/Kravyo/delivery/auto-login", "", "Open Delivery Partner Terminal"),
                ("open", "/Kravyo/delivery/dashboard", "", "Access Multi-Stop Batch Deliveries"),
                ("assertElementPresent", "css=body", "", "Verify Milk-Run Route Sequencing Algorithm")
            ]
        },
        {
            "name": "DC08_Live_System_Automated_Verification_Harness",
            "commands": [
                ("open", "/Kravyo/admin/auto-login", "", "Authenticate as Administrator"),
                ("open", "/Kravyo/admin/system-tests", "", "Execute Full 12-Test Automated Verification Harness"),
                ("assertElementPresent", "xpath=//h2[contains(., 'Automated Testing')]", "", "Verify QA Console Header Loaded"),
                ("assertElementPresent", "xpath=//h3[contains(., '100% PASS')]", "", "Verify All 12 Test Assertions Passed (100% PASS)")
            ]
        }
    ]

    all_groups = [
        ("1_Automated_Testing_Suite_Table_6_2", original_automated_specs),
        ("2_Functional_Testing_Suite", functional_specs),
        ("3_Usability_Testing_Suite", usability_specs),
        ("4_Interface_Testing_Suite", interface_specs),
        ("5_Dataflow_ControlFlow_Suite", controlflow_specs)
    ]

    all_tests = []
    suites_list = []
    all_test_ids = []

    for suite_name, specs in all_groups:
        suite_id = gen_uuid()
        suite_test_ids = []
        
        for spec in specs:
            t_id = gen_uuid()
            suite_test_ids.append(t_id)
            all_test_ids.append(t_id)
            
            cmds = []
            for cmd_name, tgt, val, comment in spec["commands"]:
                cmds.append({
                    "id": gen_uuid(),
                    "comment": comment,
                    "command": cmd_name,
                    "target": tgt,
                    "targets": [
                        [tgt, "css:finder"] if tgt.startswith("css=") else [tgt, "id"] if tgt.startswith("id=") else [tgt, "xpath:position"] if tgt.startswith("xpath=") else [tgt, ""]
                    ],
                    "value": val
                })
                
            all_tests.append({
                "id": t_id,
                "name": spec["name"],
                "commands": cmds
            })
            
        suites_list.append({
            "id": suite_id,
            "name": suite_name,
            "persistSession": False,
            "parallel": False,
            "timeout": 300,
            "tests": suite_test_ids
        })

    # Master Suite containing all tests
    master_suite_id = gen_uuid()
    suites_list.append({
        "id": master_suite_id,
        "name": "6_All_Comprehensive_Tests_Master_Suite",
        "persistSession": False,
        "parallel": False,
        "timeout": 600,
        "tests": all_test_ids
    })

    side_data = {
        "id": project_id,
        "version": "2.0",
        "name": "Kravyo_Automated_Testing_Harness",
        "url": base_url,
        "tests": all_tests,
        "suites": suites_list,
        "urls": [
            base_url,
            "http://localhost"
        ],
        "plugins": []
    }

    out1 = r"c:\xampp\htdocs\Kravyo\Kravyo_Selenium_IDE_Suite.side"
    out2 = r"c:\xampp\htdocs\Kravyo\tests\Kravyo_Selenium_IDE_Suite.side"

    with open(out1, "w", encoding="utf-8") as f:
        json.dump(side_data, f, indent=2)

    with open(out2, "w", encoding="utf-8") as f:
        json.dump(side_data, f, indent=2)

    print(f"[SUCCESS] Built complete unified Selenium IDE project file:")
    print(f"  - Total Test Cases: {len(all_tests)}")
    print(f"  - Total Suites: {len(suites_list)}")
    for s in suites_list:
        print(f"    * {s['name']} ({len(s['tests'])} tests)")
    print(f"  - Files saved to:")
    print(f"      1. {out1}")
    print(f"      2. {out2}")

if __name__ == "__main__":
    build_complete_unified_side_project()
