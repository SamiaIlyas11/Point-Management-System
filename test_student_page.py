# test_student_page.py

import time
import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By

# =========================
# Page Object (POM) Class
# =========================
class StudentPage:
    def __init__(self, driver):
        self.driver = driver
        self.url = "http://localhost/SE/fetch_data_student.html"  # <-- change if needed

    def load(self):
        self.driver.get(self.url)

    def get_all_rows(self):
        return self.driver.find_elements(By.XPATH, "//table/tbody/tr")

    def get_all_ids(self):
        return [row.find_elements(By.TAG_NAME, "td")[0].text for row in self.get_all_rows()]

    def get_all_fee_statuses(self):
        return [td.text.strip() for td in self.driver.find_elements(By.XPATH, "//table/tbody/tr/td[5]")]

    def delete_student(self, student_id):
        input_box = self.driver.find_element(By.ID, "Student_ID")
        input_box.clear()
        input_box.send_keys(student_id)
        self.driver.find_element(By.XPATH, "//form/button").click()


# =========================
# Pytest Fixture
# =========================
@pytest.fixture(scope="module")
def driver():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")
    driver = webdriver.Chrome(options=options)
    yield driver
    driver.quit()


# =========================
# Test Cases
# =========================

def test_fetch_all_students(driver):
    page = StudentPage(driver)
    page.load()
    rows = page.get_all_rows()
    assert len(rows) > 0, "No students fetched from the database"

def test_pending_students_filter(driver):
    page = StudentPage(driver)
    page.load()
    statuses = page.get_all_fee_statuses()
    assert "Pending" in statuses, "No Pending students found, but expected some"

def test_student_deletion(driver):
    student_id = "K274990"  # ⚠️ Make sure this exists in your DB
    page = StudentPage(driver)
    page.load()
    page.delete_student(student_id)

    time.sleep(2)
    driver.refresh()
    time.sleep(1)
    page.load()

    ids = page.get_all_ids()
    assert student_id not in ids, f"Student {student_id} was not deleted"
