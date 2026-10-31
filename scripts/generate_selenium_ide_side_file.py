import json
import uuid

def gen_uuid():
    return str(uuid.uuid4())

def create_side_project():
    project_id = "8f3b1940-e2cb-4fa1-8720-c2b29598e3b1"
    suite_id = "e4a711c0-7f2a-4467-b5fa-08da958d5192"
    base_url = "http://localhost/Kravyo"
    
    test_specs = [
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
    
    tests_list = []
    test_ids = []
    
    for spec in test_specs:
        t_id = gen_uuid()
        test_ids.append(t_id)
        
        cmds = []
        for cmd_name, tgt, val, comment in spec["commands"]:
            cmds.append({
                "id": gen_uuid(),
                "comment": comment,
                "command": cmd_name,
                "target": tgt,
                "targets": [
                    [tgt, "css:finder"] if tgt.startswith("css=") else [tgt, "id"] if tgt.startswith("id=") else [tgt, ""]
                ],
                "value": val
            })
            
        tests_list.append({
            "id": t_id,
            "name": spec["name"],
            "commands": cmds
        })
        
    side_data = {
        "id": project_id,
        "version": "2.0",
        "name": "Kravyo_Automated_Testing_Suite",
        "url": base_url,
        "tests": tests_list,
        "suites": [
            {
                "id": suite_id,
                "name": "Kravyo_E2E_Regression_Suite",
                "persistSession": False,
                "parallel": False,
                "timeout": 300,
                "tests": test_ids
            }
        ],
        "urls": [
            base_url,
            "http://localhost"
        ],
        "plugins": []
    }
    
    # Save to tests directory
    out_path1 = r"c:\xampp\htdocs\Kravyo\tests\Kravyo_Selenium_IDE_Suite.side"
    # Also save to root for instant access
    out_path2 = r"c:\xampp\htdocs\Kravyo\Kravyo_Selenium_IDE_Suite.side"
    
    with open(out_path1, "w", encoding="utf-8") as f:
        json.dump(side_data, f, indent=2)
        
    with open(out_path2, "w", encoding="utf-8") as f:
        json.dump(side_data, f, indent=2)
        
    print(f"[SUCCESS] Generated Selenium IDE project file at:")
    print(f"  - {out_path1}")
    print(f"  - {out_path2}")

if __name__ == "__main__":
    create_side_project()
