import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
import time
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.keys import Keys
import pymysql
import mysql.connector
import random
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import TimeoutException
import requests







# # ====== Page Object Class (OOP: Abstraction & Encapsulation) ======
class DriverFormPage:
    def __init__(self, driver):
        self.driver = driver
        self.url = "http://localhost/SE/driver_input.html"

    def open(self):
        self.driver.get(self.url)

    def fill_form(self, name, route, point_no, phone, driver_id):
        self.driver.find_element(By.ID, "Name").clear()
        self.driver.find_element(By.ID, "Name").send_keys(name)
        self.driver.find_element(By.ID, "Route").clear()
        self.driver.find_element(By.ID, "Route").send_keys(route)
        self.driver.find_element(By.ID, "Point_no").clear()
        self.driver.find_element(By.ID, "Point_no").send_keys(point_no)
        self.driver.find_element(By.ID, "Phone").clear()
        self.driver.find_element(By.ID, "Phone").send_keys(phone)
        self.driver.find_element(By.ID, "Driver_ID").clear()
        self.driver.find_element(By.ID, "Driver_ID").send_keys(driver_id)

    def submit(self):
        self.driver.find_element(By.XPATH, "//input[@type='submit']").click()

    def get_page_source(self):
        return self.driver.page_source

    def click_back_button(self):
        self.driver.find_element(By.XPATH, "//button[text()='Back']").click()

    def is_title_displayed(self):
        return "Driver Information Form" in self.get_page_source()

    def is_container_displayed(self):
        return self.driver.find_element(By.CLASS_NAME, "container").is_displayed()

    def set_window_size(self, width, height):
        self.driver.set_window_size(width, height)

    def maximize_window(self):
        self.driver.maximize_window()

    def is_submit_button_displayed(self):
        return self.driver.find_element(By.XPATH, "//input[@type='submit']").is_displayed()

    def is_submit_button_displayed(self):
        WebDriverWait(self.driver, 10).until(
            EC.visibility_of_element_located((By.XPATH, "//input[@type='submit']"))
        )
        return self.driver.find_element(By.XPATH, "//input[@type='submit']").is_displayed()
    def is_submit_button_enabled(self):
        WebDriverWait(self.driver, 10).until(
            EC.presence_of_element_located((By.XPATH, "//input[@type='submit']"))
        )
        return self.driver.find_element(By.XPATH, "//input[@type='submit']").is_enabled()

    def get_all_input_placeholders(self):
        fields = {
            "driver_id": "Driver_ID",
            "name": "Name",
            "license": "Route"
        }
        return {key: self.driver.find_element(By.ID, fid).get_attribute("placeholder") for key, fid in fields.items()}

    def are_form_elements_aligned_properly(self):
        name_elem = self.driver.find_element(By.ID, "Name")
        route_elem = self.driver.find_element(By.ID, "Route")
        return abs(name_elem.location['x'] - route_elem.location['x']) < 5

    def submit_empty_form(self):
        self.driver.find_element(By.ID, "Name").clear()
        self.driver.find_element(By.ID, "Route").clear()
        self.driver.find_element(By.ID, "Point_no").clear()
        self.driver.find_element(By.ID, "Phone").clear()
        self.driver.find_element(By.ID, "Driver_ID").clear()
        self.submit()

    def get_error_message_style(self):
        error = self.driver.find_element(By.CLASS_NAME, "error")  # Update if your class name is different
        return {"color": error.value_of_css_property("color")}

    def is_logo_displayed(self):
        try:
            return self.driver.find_element(By.ID, "logo").is_displayed()
        except:
            return False


    def focus_on_field(self, field_id):
        self.driver.find_element(By.ID, field_id).click()

    def press_tab(self):
        self.driver.switch_to.active_element.send_keys(Keys.TAB)

    def get_focused_element_name(self):
        return self.driver.switch_to.active_element.get_attribute("id")


# ====== Test Class (OOP: Inheritance & Composition) ======
class TestDriverForm:
    @classmethod
    def setup_class(cls):
        cls.driver = webdriver.Chrome()
        cls.page = DriverFormPage(cls.driver)
        cls.page.maximize_window()
        cls.page.open()
        time.sleep(1)

    @classmethod
    def teardown_class(cls):
        cls.driver.quit()

    # ------------------ UI TESTING ------------------

    def test_ui_elements_present(self):
        assert self.page.is_title_displayed()

    
    def test_submit_button_visible_and_enabled(self):
        assert self.page.is_submit_button_displayed()
        assert self.page.is_submit_button_enabled()

    def test_form_field_placeholders(self):
        placeholders = self.page.get_all_input_placeholders()
        expected_placeholders = {
            "driver_id": "Enter Driver ID",
            "name": "Enter Name",
            "license": "Enter License Number"
        }
        for field, expected in expected_placeholders.items():
            assert placeholders[field] == expected

    def test_form_layout_alignment(self):
        assert self.page.are_form_elements_aligned_properly()

    def test_error_message_style(self):
        self.page.submit_empty_form()
        error_style = self.page.get_error_message_style()
        
        red_values = ["red", "rgb(255, 0, 0)", "rgba(255, 0, 0, 1)"]
        assert error_style["color"] in red_values

    def test_logo_or_branding_visible(self):
        assert self.page.is_logo_displayed()


    def test_focus_moves_to_next_field_on_tab(self):
        # Focus on the "Name" field first
        self.page.focus_on_field("Name")

        # Simulate pressing the Tab key to move focus to the next field
        self.page.driver.find_element(By.ID, "Name").send_keys(Keys.TAB)

        # Get the name or ID of the currently focused field
        focused_field = self.page.get_focused_element_name()

        # Assert that the focus moved to either "Route" or the next field in order
        assert focused_field in ["Route", "Point_no", "Phone","Driver_ID"]  # depending on actual DOM order


    # ------------------ FUNCTIONAL TESTING ------------------

    def test_valid_form_submission(self):
        self.page.open()
        self.page.fill_form("Ali Khan", "Route A", "11", "1234567890", "DRV999")
        self.page.submit()
        time.sleep(2)
        assert "New driver added successfully" in self.page.get_page_source()

    def test_duplicate_driver_id(self):
        self.page.open()
        self.page.fill_form("Test Driver", "Route B", "4", "0987654321", "DRV999")
        self.page.submit()
        time.sleep(2)
        assert "already exists" in self.page.get_page_source()

    def test_numeric_name_input(self):
        self.page.open()
        self.page.fill_form("12345", "Route Y", "5", "9876543210", "DRV301")
        self.page.submit()
        time.sleep(2)
        assert "Invalid name" in self.page.get_page_source() or "New driver" in self.page.get_page_source()

    def test_special_characters_in_name(self):
        self.page.open()
        self.page.fill_form("Ali@#Khan", "Route C", "2", "1112223334", "DRV302")
        self.page.submit()
        time.sleep(2)
        assert "Invalid name" in self.page.get_page_source() or "New driver" in self.page.get_page_source()

    def test_long_phone_number(self):
        self.page.open()
        self.page.fill_form("Test Long", "Route D", "1", "1234567890123456", "DRV303")
        self.page.submit()
        time.sleep(2)
        assert "Invalid phone" in self.page.get_page_source() or "New driver" in self.page.get_page_source()

    def test_whitespace_input(self):
        self.page.open()
        self.page.fill_form("   ", "   ", "   ", "   ", "   ")
        self.page.submit()
        time.sleep(2)
        assert "Invalid" in self.page.get_page_source() or self.driver.current_url.endswith("driver_input.html")

    # ------------------ USABILITY TESTING ------------------

    def test_invalid_phone(self):
        self.page.open()
        self.page.fill_form("Sara", "Route B", "5", "123", "DRV304")
        self.page.submit()
        time.sleep(2)
        assert "Invalid phone" in self.page.get_page_source()

    def test_empty_fields(self):
        self.page.open()
        self.page.fill_form("", "", "", "", "")
        self.page.submit()
        time.sleep(1)
        assert self.driver.current_url.endswith("driver_input.html")

    # ------------------ RESPONSIVENESS TESTING ------------------

    def test_responsive_layout(self):
        self.page.set_window_size(375, 667)
        time.sleep(1)
        assert self.page.is_container_displayed()
        self.page.maximize_window()

#---------BACKEND AND DATABASE TESTING-----------------------------------
# ---------- CONFIGURATION ----------
URL = "http://localhost/SE/driver_input.html"
DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "point_management"
}

DUMMY_DRIVER = {
    "Name": "TestDriver",
    "Route": "RouteA",
    "Point_no": "26",
    "Phone": "0300123456",
    "Driver_ID": "D0010"
}


# ---------- DATABASE HELPERS ----------
def db_connect():
    return pymysql.connect(**DB_CONFIG)


def driver_exists(driver_id):
    conn = db_connect()
    with conn.cursor() as cursor:
        cursor.execute("SELECT * FROM driver WHERE Driver_ID = %s", (driver_id,))
        result = cursor.fetchone()
    conn.close()
    return result is not None


def delete_driver(driver_id):
    conn = db_connect()
    with conn.cursor() as cursor:
        cursor.execute("DELETE FROM driver WHERE Driver_ID = %s", (driver_id,))
    conn.commit()
    conn.close()


# ---------- TEST CASES ----------
@pytest.fixture(scope="module")
def browser():
    driver = webdriver.Chrome()
    yield driver
    driver.quit()


def test_insert_driver_valid(browser):
    # Cleanup: Remove any existing driver with the given Driver_ID before running the test
    delete_driver(DUMMY_DRIVER["Driver_ID"])  

    # Open the page in the browser
    browser.get(URL)

    # Fill in the form fields
    browser.find_element(By.NAME, "Name").send_keys(DUMMY_DRIVER["Name"])
    browser.find_element(By.NAME, "Route").send_keys(DUMMY_DRIVER["Route"]) 
    browser.find_element(By.NAME, "Point_no").send_keys(DUMMY_DRIVER["Point_no"])
    browser.find_element(By.NAME, "Phone").send_keys(DUMMY_DRIVER["Phone"])
    browser.find_element(By.NAME, "Driver_ID").send_keys(DUMMY_DRIVER["Driver_ID"])
    # Use XPATH to find the submit button
    browser.find_element(By.XPATH, '//input[@type="submit"]').click()


    # Optionally, wait for some result or confirmation (e.g., page change or success message)
    # time.sleep(1)  # Not ideal for waiting, instead use WebDriverWait as needed

    # Verify that the driver was added (you might want to replace this with an actual verification)
    assert driver_exists(DUMMY_DRIVER["Driver_ID"]) is True

def test_duplicate_driver_id():
    # Try inserting duplicate Driver_ID
    conn = db_connect()
    with conn.cursor() as cursor:
        try:
            cursor.execute(
                "INSERT INTO driver (Name, Route, Point_no, Phone, Driver_ID) VALUES (%s, %s, %s, %s, %s)",
                (DUMMY_DRIVER["Name"], DUMMY_DRIVER["Route"], DUMMY_DRIVER["Point_no"],
                 DUMMY_DRIVER["Phone"], DUMMY_DRIVER["Driver_ID"])
            )
            conn.commit()
            success = True
        except pymysql.err.IntegrityError:
            success = False
    conn.close()
    assert success is False, "Duplicate Driver_ID was allowed!"


def test_sql_injection_protection():
    malicious_id = "' OR 1=1 --"
    conn = db_connect()
    with conn.cursor() as cursor:
        cursor.execute("SELECT * FROM driver WHERE Driver_ID = %s", (malicious_id,))
        result = cursor.fetchall()
    conn.close()
    assert len(result) == 0, "SQL Injection vulnerability detected!"


# Cleanup inserted test record
def test_cleanup():
    delete_driver(DUMMY_DRIVER["Driver_ID"])



def test_insert_null_driver_name():
    conn = db_connect()
    with conn.cursor() as cursor:
        try:
            cursor.execute(
                "INSERT INTO driver (Name, Route, Point_no, Phone, Driver_ID) VALUES (%s, %s, %s, %s, %s)",
                (None, DUMMY_DRIVER["Route"], DUMMY_DRIVER["Point_no"],
                 DUMMY_DRIVER["Phone"], "DNULL001")
            )
            conn.commit()
            success = True
        except pymysql.err.IntegrityError:
            success = False
    conn.close()
    delete_driver("DNULL001")
    assert success is False, "NULL values for Name should not be allowed!"


def test_bulk_insert_performance():
    start_time = time.time()
    conn = db_connect()
    with conn.cursor() as cursor:
        for i in range(100):  # insert 100 test drivers
          cursor.execute(
            "INSERT INTO driver (Name, Route, Point_no, Phone, Driver_ID) VALUES (%s, %s, %s, %s, %s)",
            (f"PerfDriver{i}", "RouteX", str(900 + i), f"0300123{i:04d}", f"PERF{i:03d}")
           )

        conn.commit()
        conn.close()
    duration = time.time() - start_time
    assert duration < 5, f"Bulk insert took too long: {duration:.2f} seconds"

    # Cleanup
    conn = db_connect()
    with conn.cursor() as cursor:
        cursor.execute("DELETE FROM driver WHERE Driver_ID LIKE 'PERF%'")
    conn.commit()
    conn.close()

def test_driver_update_integrity():
    driver_id = "DUPD001"
    delete_driver(driver_id)  # Cleanup before insert
    conn = db_connect()
    with conn.cursor() as cursor:
        cursor.execute(
            "INSERT INTO driver (Name, Route, Point_no, Phone, Driver_ID) VALUES (%s, %s, %s, %s, %s)",
            ("OldName", "RouteOld", "22", "0300000000", driver_id)
        )
        conn.commit()

        cursor.execute(
            "UPDATE driver SET Name = %s, Phone = %s WHERE Driver_ID = %s",
            ("NewName", "0399999999", driver_id)
        )
        conn.commit()

        cursor.execute("SELECT Name, Phone FROM driver WHERE Driver_ID = %s", (driver_id,))
        result = cursor.fetchone()
        print("Fetched result:", result)  # Debug
    conn.close()
    delete_driver(driver_id)

    assert result == ("NewName", int("0399999999")), "Update integrity failed!"


def test_special_characters_in_name():
    special_name = "O'Reilly & Sons"
    special_id = "DSPEC001"
    conn = db_connect()
    with conn.cursor() as cursor:
        try:
            cursor.execute(
                "INSERT INTO driver (Name, Route, Point_no, Phone, Driver_ID) VALUES (%s, %s, %s, %s, %s)",
                (special_name, "Route!", "3", "0321123456", special_id)
            )
            conn.commit()

            # Fetch and verify
            cursor.execute("SELECT Name FROM driver WHERE Driver_ID = %s", (special_id,))
            result = cursor.fetchone()
        finally:
            delete_driver(special_id)
    conn.close()
    assert result and result[0] == special_name, "Special characters not handled correctly!"


#----------------INTEGRATION TESING----------------------------------------------------------
print("Integration testing starts from here")
# def fill_form(driver, name, route, point, phone, driver_id):
#         driver.find_element(By.ID, "Name").clear()
#         driver.find_element(By.ID, "Name").send_keys(name)
#         driver.find_element(By.ID, "Route").clear()
#         driver.find_element(By.ID, "Route").send_keys(route)
#         driver.find_element(By.ID, "Point_no").clear()
#         driver.find_element(By.ID, "Point_no").send_keys(point)
#         driver.find_element(By.ID, "Phone").clear()
#         driver.find_element(By.ID, "Phone").send_keys(phone)
#         driver.find_element(By.ID, "Driver_ID").clear()
#         driver.find_element(By.ID, "Driver_ID").send_keys(driver_id)
#         driver.find_element(By.CSS_SELECTOR, 'input[type="submit"]').click()
#         time.sleep(1)

# Setup and teardown using pytest fixtures
@pytest.fixture
def driver():
    options = webdriver.ChromeOptions()
    options.add_argument("--headless")  # Optional: run headless
    driver = webdriver.Chrome(options=options)
    driver.get("http://localhost/SE/driver_input.html")
    yield driver
    driver.quit()




# Utility function to fill the form
@pytest.fixture
def fill_form():
    def _fill_form(driver, name, route, point, phone, driver_id):
        driver.find_element(By.ID, "Name").clear()
        driver.find_element(By.ID, "Name").send_keys(name)
        driver.find_element(By.ID, "Route").clear()
        driver.find_element(By.ID, "Route").send_keys(route)
        driver.find_element(By.ID, "Point_no").clear()
        driver.find_element(By.ID, "Point_no").send_keys(point)
        driver.find_element(By.ID, "Phone").clear()
        driver.find_element(By.ID, "Phone").send_keys(phone)
        driver.find_element(By.ID, "Driver_ID").clear()
        driver.find_element(By.ID, "Driver_ID").send_keys(driver_id)
        driver.find_element(By.CSS_SELECTOR, 'input[type="submit"]').click()
        time.sleep(1)
    return _fill_form  # Return the function, not call it


# Functional + Integration + Security test cases
def test_valid_submission(driver,fill_form):
    unique_id = random.randint(1000, 9999)
    fill_form(driver, f"John Doe {unique_id}", f"R-{unique_id}", "5", "1234567890", f"DR{unique_id}")
    time.sleep(1)
    assert "New driver added successfully" in driver.page_source

def test_duplicate_driver_id(driver,fill_form):
    fill_form(driver, "John", "R-101", "6", "1234567890", "DR001")  # Same as before
    time.sleep(1)
    assert "already exists" in driver.page_source

def test_sql_injection_in_name(driver,fill_form):
    fill_form(driver, "' OR '1'='1", "R-102", "7", "1234567890", "DR002")
    time.sleep(1)
    assert "New driver added" not in driver.page_source

def test_xss_attack_in_name(driver,fill_form):
    fill_form(driver, "<script>alert('XSS')</script>", "R-103", "8", "1234567890", "DR003")
    time.sleep(1)
    assert "<script>" not in driver.page_source.lower()

def test_unexpected_extra_field(driver,fill_form):
    driver.execute_script("""
        let extra = document.createElement('input');
        extra.setAttribute('name', 'hack_field');
        extra.setAttribute('value', 'malicious');
        document.getElementById('driverForm').appendChild(extra);
    """)
    fill_form(driver, "Tom", "R-104", "9", "1234567890", "DR004")
    time.sleep(1)
    assert "New driver added successfully" in driver.page_source

def test_get_instead_of_post():
    payload = {
        "Name": "Test",
        "Route": "R1",
        "Point_no": "1",
        "Phone": "1234567890",
        "Driver_ID": "DR999"
    }
    response = requests.get("http://localhost/SE/driver_input.php", params=payload)
    assert response.text.strip() == ""

def test_large_input_fields(driver,fill_form):
    large_text = "A" * 500
    fill_form(driver, large_text, large_text, "10", "1234567890", "DR005")
    time.sleep(1)
    assert "New driver added successfully" in driver.page_source or "Error" in driver.page_source

def test_invalid_phone_format(driver,fill_form):
    fill_form(driver, "InvalidPhone", "R-105", "11", "abcd123", "DR006")
    time.sleep(1)
    assert "Invalid phone number" in driver.page_source

def test_missing_required_fields(driver,fill_form):
    fill_form(driver, "", "", "", "", "")
    time.sleep(1)
    assert "required" in driver.page_source.lower()

def test_non_integer_point(driver,fill_form):
    fill_form(driver, "TestName", "R-106", "ABC", "1234567890", "DR007")
    time.sleep(1)
    assert "Error" in driver.page_source or "New driver" not in driver.page_source

def test_html_escape(driver,fill_form):
    fill_form(driver, "&lt;Test&gt;", "R-107", "3", "1234567890", "DR008")
    time.sleep(1)
    assert "New driver added successfully" in driver.page_source

def test_script_tag_in_driver_id(driver,fill_form):
    fill_form(driver, "Normal", "R-108", "4", "1234567890", "<script>1</script>")
    time.sleep(1)
    assert "New driver" not in driver.page_source

def test_repeated_submission(driver,fill_form):
    fill_form(driver, "Repeat", "R-109", "24", "1234567890", "DR009")
    time.sleep(1)
    driver.get("http://localhost/SE/driver_input.html")
    time.sleep(1)
    fill_form(driver, "Repeat", "R-109", "24", "1234567890", "DR009")
    time.sleep(1)
    assert "already exists" in driver.page_source

def test_boundary_point_number(driver,fill_form):
    fill_form(driver, "BoundaryTest", "R-110", "20", "1234567890", "DR010")
    time.sleep(1)
    assert "New driver added" in driver.page_source

def test_point_above_max(driver,fill_form):
    fill_form(driver, "OverLimit", "R-111", "21", "1234567890", "DR011")
    time.sleep(1)
    assert "Error" in driver.page_source or "New driver" not in driver.page_source

# Conditional coverage tests
def test_individual_field_validation(driver,fill_form):
    """Test validation for each field individually"""
    test_cases = [
        ("", "Route 66", "10", "1234567890", "DRV123", "nameError"),
        ("John Doe", "", "10", "1234567890", "DRV123", "routeError"),
        ("John Doe", "Route 66", "", "1234567890", "DRV123", "pointError"),
        ("John Doe", "Route 66", "10", "", "DRV123", "phoneError"),
        ("John Doe", "Route 66", "10", "1234567890", "", "driverIdError"),
    ]
    
    for name, route, point, phone, driver_id, expected_error in test_cases:
        driver.get("http://localhost/SE/driver_input.html")
        fill_form(driver, name, route, point, phone, driver_id)
        assert driver.find_element(By.ID, expected_error).is_displayed()
        
        other_errors = ["nameError", "routeError", "pointError", "phoneError", "driverIdError"]
        other_errors.remove(expected_error)
        for error_id in other_errors:
            assert not driver.find_element(By.ID, error_id).is_displayed()

# def test_valid_phone(driver):
#     fill_form(driver, "Jane", "R-2", "32", "0987654321", "D102")
#     time.sleep(1)
#     assert "New driver added successfully" in driver.page_source

def test_invalid_phone_number(driver, fill_form):
    """Test phone number validation"""
    fill_form(driver, "John Doe", "Route 66", "10", "12345", "DRV123")
    assert "Invalid phone number" in driver.page_source

#path coverage
def test_path_valid(driver,fill_form):
    fill_form(driver, "Bob", "R-3", "35", "9999999999", "D104")
    time.sleep(1)
    assert "New driver added successfully" in driver.page_source

def test_path_duplicate(driver,fill_form):
    fill_form(driver, "Bob", "R-3", "6", "9999999999", "D104")
    time.sleep(1)
    assert "already exists" in driver.page_source

def test_path_invalid_phone(driver,fill_form):
    driver.get("http://localhost/SE/driver_input.html")
    fill_form(driver, "Bob", "R-3", "36", "abc", "D105")
    time.sleep(1)
    assert "Invalid phone number. Please enter a 10-digit phone number."

#Branch Coverage:
# test_branch_coverage.py
def test_form_validation_passes(driver, fill_form):
    """Test when form validation passes"""
    fill_form(driver, "John Doe", "Route 66", "88", "1234567890", "DRV123")
    assert "driver_input.php" in driver.current_url
    assert "New driver added successfully" in driver.page_source

def test_form_validation_fails(driver):
    """Test when form validation fails"""
    driver.find_element(By.CSS_SELECTOR, 'input[type="submit"]').click()
    assert "driver_input.php" not in driver.current_url
    assert "Name is required" in driver.page_source
#line coverage 
# def test_line_valid_form_submission(driver):
#     """Test successful form submission with all valid data"""
#     driver.get("http://localhost/SE/driver_input.html")
#     fill_form(driver, "John Doe", "Route 66", "44", "1234567890", "DRV123")
#     assert "New driver added successfully" in driver.page_source

# def test_empty_form_submission(driver):
#     """Test form submission with all fields empty"""
#     driver.find_element(By.CSS_SELECTOR, 'input[type="submit"]').click()
#     errors = ["nameError", "routeError", "pointError", "phoneError", "driverIdError"]
#     for error_id in errors:
#         assert driver.find_element(By.ID, error_id).is_displayed()
#     assert "driver_input.php" not in driver.current_url

#BlackBox testing
# test_black_box.py
def test_field_length_handling(driver, fill_form):
    """Test handling of long input strings"""
    long_string = "A" * 255
    fill_form(driver, long_string, long_string, "55", "1234567890", long_string)
    assert "New driver added successfully" in driver.page_source

# def test_point_no_validation(driver, fill_form):
#     """Test backend validation for point_no (1-100)"""
#     # Test valid values
#     for value in ["1", "50", "100"]:
#         driver.get("http://localhost/SE/driver_input.html")
#         test_id = f"DRV{hash(value)}"[:8]
#         fill_form(driver, "Valid Name", "Valid Route", value, "1234567890", test_id)
#         assert "New driver added successfully" in driver.page_source
    
   

#--------------------------BVA------------------------------------------
@pytest.mark.parametrize("value,expected", [
    ("1", True),  # Valid
    ("100", True),  # Valid
    ("0", False),  # Too low
    ("101", False),  # Too high
    ("50", True),  # Valid
    ("-5", False),  # Invalid
    # ("abc", False),  # Not a number
    ("", False)  # Empty
])
def test_point_no_boundary_values(driver, fill_form, value, expected):
    """Parameterized test for point number validation"""
    driver.get("http://localhost/SE/driver_input.html")
    test_id = f"DRV{hash(value)}"[:8]
    fill_form(driver, "Valid Name", "Valid Route", value, "1234567890", test_id)
    
    if expected:
        assert "New driver added successfully" in driver.page_source
    else:
        assert "New driver added successfully" not in driver.page_source
        if value == "":
            assert "Point number is required" in driver.page_source
        else:
            assert "Point number must be between 1 and 100" in driver.page_source
        
#-------------------EQUIVALENCE CLASS TESTING------------------------------------
@pytest.fixture
def clean_test_data():
    """Fixture to clean up test data before each test"""
    conn = pymysql.connect(
        host="localhost",
        user="root",
        password="",
        database="point_management"
    )
    try:
        with conn.cursor() as cursor:
            cursor.execute("DELETE FROM driver WHERE Driver_ID LIKE 'DRV%'")
            conn.commit()
    finally:
        conn.close()

@pytest.mark.parametrize("phone,expected_class", [
    ("1234567890", "valid"),      # Valid 10 digits
    ("9876543210", "valid"),      # Another valid
    ("12345", "invalid"),         # Too short
    ("123456789012", "invalid"),  # Too long
    ("abcdefghij", "invalid"),    # Non-numeric
    ("123 456 789", "invalid"),   # Contains spaces
    ("", "invalid")               # Empty
])


def test_phone_equivalence_classes(driver, fill_form, clean_test_data, phone, expected_class):
    """Test phone number validation with robust error handling"""
    driver.get("http://localhost/SE/driver_input.html")
    test_id = f"DRV{random.randint(1000, 9999)}"
    
    fill_form(driver, "Valid Name", "Valid Route", "66", phone, test_id)
    
    if expected_class == "valid":
        assert "New driver added successfully" in driver.page_source
    else:
        # More robust error detection
        try:
            error_element = driver.find_element(By.ID, "phoneError")
            assert error_element.is_displayed(), "Error message should be visible"
            assert "phone" in error_element.text.lower(), "Error should mention phone validation"
        except NoSuchElementException:
            # If error element doesn't exist, check for validation in page
            assert "invalid phone" in driver.page_source.lower() or \
                   "phone number is required" in driver.page_source.lower(), \
                   "Page should show phone validation error"
            
#---------------------Decision Table Testing-----------------
@pytest.mark.parametrize("name,driver_id,point_no,expected_result", [
    # Rule 1: Both valid
    ("John Doe", f"DRV{random.randint(1000,9999)}", "50", "success"),
    # Rule 2: Valid name, empty ID
    ("John Doe", "", "75", "error"), 
    # Rule 3: Empty name, valid ID
    ("", f"DRV{random.randint(1000,9999)}", "25", "error"),
    # Rule 4: Both empty
    ("", "", "100", "error"),
    # Rule 5: Special chars in name
    ("John@Doe", f"DRE{random.randint(1000,9999)}", "1", "success"),
    # # Rule 6: Long ID
    # ("John Doe", "DRV123456789", "99", "error")
])
def test_decision_table(driver, fill_form, clean_test_data, name, driver_id, point_no, expected_result):
    """Robust decision table test handling both redirects and validation messages"""
    driver.get("http://localhost/SE/driver_input.html")
    phone = "1234567890"  # Always use valid phone number
    
    fill_form(driver, name, "Valid Route", point_no, phone, driver_id)
    
    try:
        # Wait for either success or error condition
        WebDriverWait(driver, 3).until(
            lambda d: (
                "driver_input.php" in d.current_url or
                any(msg in d.page_source for msg in [
                    "Name is required",
                    "Driver ID is required",
                    "Invalid input"
                ])
        ))
        
        if expected_result == "success":
            assert "New driver added successfully" in driver.page_source
        elif expected_result == "error":
            assert any(msg in driver.page_source 
                      for msg in ["Name is required", "Driver ID is required"])
        else:
            assert False, "Unexpected test case"
            
    except TimeoutException:
        # If we timeout, check if we're still on the form page with errors
        if "driver_input.html" in driver.current_url:
            error_elements = driver.find_elements(By.CSS_SELECTOR, ".error")
            assert any(el.is_displayed() for el in error_elements), \
                   "Expected validation errors to be visible"
        else:
            raise AssertionError("Test timed out waiting for expected outcome")

#---------------------Regression Testing--------------------------------
# test_regression.py
def test_basic_happy_path(driver, fill_form):
    """Regression test for basic successful submission"""
    fill_form(driver, "Regression Test", "Route 99", "77", "9876543210", "REGR123")
    assert "New driver added successfully" in driver.page_source

def test_validation_persistence(driver, fill_form):
    """Test that validation errors persist until corrected"""
    # First submit with empty form
    driver.find_element(By.CSS_SELECTOR, 'input[type="submit"]').click()
    assert driver.find_element(By.ID, "nameError").is_displayed()
    
    # Fill only the name field and submit again
    driver.find_element(By.ID, "Name").send_keys("John Doe")
    driver.find_element(By.CSS_SELECTOR, 'input[type="submit"]').click()
    
    # Verify name error is gone but others remain
    assert not driver.find_element(By.ID, "nameError").is_displayed()
    assert driver.find_element(By.ID, "routeError").is_displayed()