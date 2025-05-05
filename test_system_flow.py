import time
import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import Select


@pytest.fixture(scope="module")
def browser():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")
    driver = webdriver.Chrome(options=options)
    driver.implicitly_wait(10)
    yield driver
    driver.quit()

def test_student_full_flow(browser):
    # Load page with explicit wait for AOS to complete
    browser.get("http://localhost/SE/index.html")
    
    # Wait for AOS animations to complete
    time.sleep(3)  # Adjust for animation loading time
    
    # Wait for buttons to be visible
    student_btn = WebDriverWait(browser, 30).until(
        EC.visibility_of_element_located((By.XPATH, "//button[text()='Student']"))
    )
    admin_btn = WebDriverWait(browser, 30).until(
        EC.visibility_of_element_located((By.XPATH, "//button[text()='Admin']"))
    )

    # Debug: Print button visibility
    print(f"Student button is visible: {student_btn.is_displayed()}")
    print(f"Admin button is visible: {admin_btn.is_displayed()}")
    
    # Take a screenshot for verification
    browser.save_screenshot("before_click.png")
    
    # Click the student button using JavaScript as fallback
    browser.execute_script("arguments[0].click();", student_btn)
    
    # Verify navigation to student_login.html
    WebDriverWait(browser, 10).until(
        EC.url_contains("student_login.html")
    )
    
    # Fill in the login form
    student_id = "K214659"  # Example student ID
    password = "password123"   # Example password
    
    # Locate the ID and password fields
    id_field = WebDriverWait(browser, 10).until(
        EC.visibility_of_element_located((By.ID, "input-id"))
    )
    pass_field = WebDriverWait(browser, 10).until(
        EC.visibility_of_element_located((By.ID, "input-pass"))
    )
    
    # Fill the form with student credentials
    id_field.send_keys(student_id)
    pass_field.send_keys(password)
    
    # Submit the form by clicking the login button
    login_button = browser.find_element(By.CLASS_NAME, "login__button")
    login_button.click()
    
    # Verify navigation to student.html (after successful login)
    WebDriverWait(browser, 50).until(
        EC.url_contains("student.html")
    )

    # Debug: Verify that we are on student.html
    print("Successfully navigated to student.html")

    # Now, click the "Go to Real-Time Tracking" button
    tracking_btn = WebDriverWait(browser, 10).until(
        EC.visibility_of_element_located((By.XPATH, "//button[text()='Go to Real-Time Tracking']"))
    )
    
    # Take a screenshot before clicking the button
    browser.save_screenshot("before_tracking_click.png")
    
    # Click the button to navigate to real_time_tracking.html
    tracking_btn.click()
    
    # Wait for navigation to real_time_tracking.html
    WebDriverWait(browser, 10).until(
        EC.url_contains("real_time_tracking.html")
    )
    
    # Debug: Verify successful navigation
    print("Successfully navigated to real_time_tracking.html")

    # Optionally, take a screenshot after navigation to real-time tracking page
    browser.save_screenshot("after_tracking_click.png")


# Now, let's go back to student.html
    # This could be done by either using the browser's back functionality or by directly navigating back.
    browser.back()  # Navigate back to the previous page (student.html)

    # Wait for the student.html page to load again
    WebDriverWait(browser, 50).until(
        EC.url_contains("student.html")
    )

    # Debug: Verify that we are back on student.html
    print("Successfully navigated back to student.html")

    # Now, click the "View Routes" button
    routes_btn = WebDriverWait(browser, 10).until(
        EC.visibility_of_element_located((By.XPATH, "//button[text()='View Routes']"))
    )
    
    # Take a screenshot before clicking the button
    browser.save_screenshot("before_routes_click.png")
    
    # Click the button to navigate to routes.html
    routes_btn.click()
    
    # Wait for navigation to routes.html
    WebDriverWait(browser, 15).until(
        EC.url_contains("routes.html")
    )
    
    # Debug: Verify successful navigation to routes.html
    print("Successfully navigated to routes.html")

    # Optionally, take a screenshot after navigation to routes.html
    browser.save_screenshot("after_routes_click.png")
    
     # Navigate back to student.html
    browser.back()
    WebDriverWait(browser, 30).until(
        EC.url_contains("student.html")
    )
    print("Successfully navigated back to student.html from routes.html")

    # Click the "Fee Challan" button
    fee_btn = WebDriverWait(browser, 30).until(
        EC.visibility_of_element_located((By.XPATH, "//button[text()='Fee Challan']"))
    )
    browser.save_screenshot("before_fee_click.png")
    fee_btn.click()

    # Wait for navigation to fee.html
    WebDriverWait(browser, 20).until(
        EC.url_contains("fee.html")
    )
    print("Successfully navigated to fee.html")

    # Fill in the Fee Challan form
    name_field = WebDriverWait(browser, 10).until(
        EC.visibility_of_element_located((By.ID, "name"))
    )
    id_field = browser.find_element(By.ID, "student-id")
    point_field = browser.find_element(By.ID, "point-number")

    # Fill the form
    name_field.send_keys("Filter Test Paid 6298")
    id_field.send_keys("K124361")
    point_field.send_keys("P3000")

    # Submit the form
    submit_btn = browser.find_element(By.XPATH, "//button[@type='submit']")
    browser.save_screenshot("before_challan_submit.png")
    submit_btn.click()

    # Wait for navigation to fee.php (this may be quick or you may want to validate the new page)
    WebDriverWait(browser, 50).until(
        EC.url_contains("fee.php")
    )
    print("Successfully submitted the form and navigated to fee.php")
    browser.save_screenshot("after_fee_submit.png")

        # Navigate back to index.html
    browser.get("http://localhost/SE/index.html")
    WebDriverWait(browser, 60).until(
        EC.url_contains("index.html")
    )
    print("Navigated back to index.html")

    # Wait for Admin button and click it
    admin_btn = WebDriverWait(browser, 30).until(
        EC.element_to_be_clickable((By.XPATH, "//button[text()='Admin']"))
    )
    browser.save_screenshot("before_admin_click.png")
    admin_btn.click()

    # Verify redirection to admin_login.html
    WebDriverWait(browser, 30).until(
        EC.url_contains("admin_login.html")
    )
    print("Successfully navigated to admin_login.html")
    browser.save_screenshot("after_admin_login.png")
    # Wait for email field and enter the admin email
    email_field = WebDriverWait(browser, 30).until(
        EC.presence_of_element_located((By.ID, "input-email"))
    )
    email_field.clear()
    email_field.send_keys("k214947@nu.edu.pk")

    # Wait for password field and enter the password
    password_field = WebDriverWait(browser, 30).until(
        EC.presence_of_element_located((By.ID, "input-pass"))
    )
    password_field.clear()
    password_field.send_keys("password123")

    # Optional: tick "Remember me"
    remember_checkbox = browser.find_element(By.ID, "input-check")
    if not remember_checkbox.is_selected():
        remember_checkbox.click()

    # Submit the form
    login_button = browser.find_element(By.ID, "login-button")
    browser.save_screenshot("before_login_submit.png")
    login_button.click()

    # Wait for redirection to admin.html
    WebDriverWait(browser, 30).until(
        EC.url_contains("admin.html")
    )
    print("Successfully logged in and redirected to admin.html")
    browser.save_screenshot("after_admin_login_redirect.png")

    # Now on admin.html — wait for "Add New Student" button and click it
    add_student_button = WebDriverWait(browser, 30).until(
        EC.element_to_be_clickable((By.XPATH, "//button[text()='Add New Student']"))
    )

    # Optional: take a screenshot before clicking the button
    browser.save_screenshot("before_click_add_student.png")

    # Click the button to go to student_input.html
    add_student_button.click()

    # Wait for redirection to student_input.html
    WebDriverWait(browser, 30).until(
        EC.url_contains("student_input.html")
    )

    # Optional: Screenshot after navigation
    browser.save_screenshot("after_redirect_student_input.png")
    print("Successfully navigated to student_input.html")

        # Wait for the Student ID field and fill it
    student_id = WebDriverWait(browser, 30).until(
        EC.presence_of_element_located((By.ID, "Student_ID"))
    )
    student_id.send_keys("K213375")

    # Fill Name
    browser.find_element(By.ID, "Name").send_keys("sanjana")

    # Fill Point_no
    browser.find_element(By.ID, "Point_no").send_keys("3B")

    # Fill Phone
    browser.find_element(By.ID, "Phone").send_keys("1234567899")

    # Select Fee_Status ("Paid")
    fee_status = Select(browser.find_element(By.ID, "Fee_Status"))
    fee_status.select_by_visible_text("Paid")

    # Fill Driver_ID
    browser.find_element(By.ID, "Driver_ID").send_keys("D8877")

    # Optional: take a screenshot before submitting
    browser.save_screenshot("before_submit_student_form.png")

    # Submit the form
    browser.find_element(By.CSS_SELECTOR, 'input[type="submit"]').click()

    # Wait for redirection to fetch_data_student.html
    WebDriverWait(browser, 10).until(
        EC.url_contains("fetch_data_student.html")
    )

    # Final screenshot after redirect
    browser.save_screenshot("after_redirect_fetch_data_student.png")
    print("Form submitted and redirected to fetch_data_student.html")

    browser.get("http://localhost/SE/admin.html")
    WebDriverWait(browser, 60).until(
        EC.url_contains("admin.html")
    )
    print("on admin.html for driver input. ")

    # Click the "Add new driver" button (assuming it has id="add-driver-btn")
    add_driver_button = WebDriverWait(browser, 30).until(
        EC.element_to_be_clickable((By.XPATH, "//button[text()='Add New Driver']"))
    )
    add_driver_button.click()

    # Wait for redirection to driver_input.html
    WebDriverWait(browser, 30).until(
        EC.url_contains("driver_input.html")
    )
    print("Redirected to driver_input.html")
    browser.save_screenshot("driver_input_form.png")

    # Fill out the form fields
    WebDriverWait(browser, 30).until(EC.presence_of_element_located((By.ID, "Name"))).send_keys("random")
    browser.find_element(By.ID, "Route").send_keys("habibuni")
    browser.find_element(By.ID, "Point_no").send_keys("66")
    browser.find_element(By.ID, "Phone").send_keys("1234567899")
    browser.find_element(By.ID, "Driver_ID").send_keys("DRV55")

    # Submit the form
    browser.save_screenshot("before_driver_submit.png")
    browser.find_element(By.CSS_SELECTOR, "input[type='submit']").click()

    # Wait for redirection to driver_input.php (if applicable)
    WebDriverWait(browser, 30).until(
        EC.url_contains("driver_input.php")
    )
    print("Driver form submitted and redirected to driver_input.php")
    browser.save_screenshot("after_driver_submit.png")

    #Go back to admin.html
    browser.get("http://localhost/SE/admin.html")
    WebDriverWait(browser, 30).until(
        EC.url_contains("admin.html")
    )
    print("Back on admin.html after driver input.")

    # Click the "Student Data" button (make sure the button has id="Student")
    student_data_button = WebDriverWait(browser, 30).until(
        EC.element_to_be_clickable((By.XPATH, "//button[text()='Student Data']"))
    )
    student_data_button.click()

    # Wait for redirection to fetch_data_student.html
    WebDriverWait(browser, 30).until(
        EC.url_contains("fetch_data_student.html")
    )
    browser.save_screenshot("after_student_data_click.png")
    print("Redirected to fetch_data_student.html")

        # Go back to admin.html
    browser.get("http://localhost/SE/admin.html")
    WebDriverWait(browser, 20).until(
        EC.url_contains("admin.html")
    )
    print("Back on admin.html to fetch driver data.")

    # Click the "Driver Data" button (ensure it has id="Driver")
    driver_data_button = WebDriverWait(browser, 20).until(
        EC.element_to_be_clickable((By.XPATH, "//button[text()='Driver Data']"))
    )
    driver_data_button.click()

    # Wait for redirection to fetch_data_driver.html
    WebDriverWait(browser, 20).until(
        EC.url_contains("fetch_data_driver.html")
    )
    browser.save_screenshot("after_driver_data_click.png")
    print("Redirected to fetch_data_driver.html")



# def test_invalid_login(browser):
#     # Edge Case: Invalid login attempt
#     browser.get("http://localhost/SE/student_login.html")
#     browser.find_element(By.ID, "input-id").send_keys("invalid")
#     browser.find_element(By.ID, "input-pass").send_keys("wrongpass")
#     browser.find_element(By.CSS_SELECTOR, ".login__button").click()

#     # Verify error message appears
#     error_msg = WebDriverWait(browser, 5).until(
#         EC.visibility_of_element_located((By.ID, "error-message"))
#     )
#     assert "Invalid ID or Password" in error_msg.text
#     assert "student_login.html?error=1" in browser.current_url
