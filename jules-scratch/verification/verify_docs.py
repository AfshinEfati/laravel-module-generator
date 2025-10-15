from playwright.sync_api import Page, expect

def test_docs_navigation(page: Page):
    """
    This test verifies that the new "Features" section and its pages
    are correctly added to the documentation site.
    """
    # 1. Arrange: Go to the documentation homepage.
    page.goto("http://localhost:3000/en")

    # 2. Act: Find the "Features" section and click on the "Generating Modules" link.
    features_link = page.get_by_role("link", name="Generating Modules")
    features_link.click()

    # 3. Assert: Confirm the navigation was successful.
    expect(page).to_have_title("Generating Modules · Laravel Module Generator")
    expect(page.get_by_role("heading", name="Generating Modules")).to_be_visible()

    # 4. Screenshot: Capture the final result for visual verification.
    page.screenshot(path="jules-scratch/verification/docs-screenshot-en.png")

    # 5. Verify Persian translation
    page.get_by_role("link", name="فارسی").click()
    expect(page).to_have_title("تولید ماژول‌ها · Laravel Module Generator")
    expect(page.get_by_role("heading", name="تولید ماژول‌ها")).to_be_visible()
    page.screenshot(path="jules-scratch/verification/docs-screenshot-fa.png")